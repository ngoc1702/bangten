<?php
/** Shared shop/category catalog with taxonomy filters and WooCommerce ordering. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function caia_is_product_catalog() {
	return function_exists( 'is_shop' ) && ( is_shop() || caia_get_product_category_for_current_page() instanceof WP_Term );
}

add_filter( 'genesis_site_layout', function( $layout ) {
	return caia_is_product_catalog() ? 'full-width-content' : $layout;
}, 200 );
add_filter( 'body_class', function( $classes ) {
	if ( caia_is_product_catalog() ) { $classes[] = 'caia-product-catalog'; }
	return $classes;
} );
// Run after WooCommerce has adapted its archive to the Genesis page template.
add_filter( 'template_include', function( $template ) {
	if ( caia_is_product_catalog() ) {
		remove_action( 'genesis_loop', 'genesis_do_loop' );
		add_action( 'genesis_loop', 'caia_render_catalog_page' );
	}
	return $template;
}, 100 );
add_action( 'wp_enqueue_scripts', function() {
	if ( ! caia_is_product_catalog() ) { return; }
	$dir = get_stylesheet_directory();
	$url = get_stylesheet_directory_uri();
	wp_enqueue_style( 'caia-catalog', $url . '/assets/css/catalog.css', array( 'caia-home' ), filemtime( $dir . '/assets/css/catalog.css' ) );
	wp_enqueue_script( 'caia-catalog', $url . '/assets/js/catalog.js', array(), filemtime( $dir . '/assets/js/catalog.js' ), true );
}, 180 );

function caia_catalog_order_options() {
	return array(
		'menu_order' => 'Sắp xếp mặc định', 'date' => 'Sắp xếp theo mới nhất',
		'popularity' => 'Sắp xếp theo mức độ phổ biến', 'rating' => 'Sắp xếp theo đánh giá',
		'price' => 'Sắp xếp theo giá: thấp đến cao', 'price-desc' => 'Sắp xếp theo giá: cao đến thấp',
	);
}

function caia_catalog_selected_slugs( $key ) {
	$values = isset( $_GET[ $key ] ) ? (array) wp_unslash( $_GET[ $key ] ) : array();
	return array_values( array_unique( array_map( 'sanitize_title', array_filter( $values, 'is_string' ) ) ) );
}

function caia_catalog_state() {
	$term = caia_get_product_category_for_current_page();
	// On an archive, the URL determines the category. Filter parameters only narrow it.
	if ( ! $term instanceof WP_Term && isset( $_GET['catalog_category'] ) && is_string( $_GET['catalog_category'] ) ) {
		$selected = get_term_by( 'slug', sanitize_title( wp_unslash( $_GET['catalog_category'] ) ), 'product_cat' );
		$term = $selected instanceof WP_Term ? $selected : null;
	}
	$orderby = isset( $_GET['orderby'] ) && is_string( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : get_option( 'woocommerce_default_catalog_orderby', 'menu_order' );
	if ( ! isset( caia_catalog_order_options()[ $orderby ] ) ) { $orderby = 'menu_order'; }
	$page = isset( $_GET['catalog_page'] ) && is_scalar( $_GET['catalog_page'] ) ? max( 1, absint( $_GET['catalog_page'] ) ) : max( 1, absint( get_query_var( 'paged' ) ) );
	return array( 'term' => $term, 'materials' => caia_catalog_selected_slugs( 'materials' ), 'sizes' => caia_catalog_selected_slugs( 'sizes' ), 'orderby' => $orderby, 'page' => $page );
}

function caia_catalog_tax_query( $state, $ignore = '' ) {
	$tax = array( 'relation' => 'AND' );
	$visibility = wc_get_product_visibility_term_ids();
	$excluded = array_filter( array( $visibility['exclude-from-catalog'] ?? 0, get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' ? ( $visibility['outofstock'] ?? 0 ) : 0 ) );
	if ( $excluded ) { $tax[] = array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => $excluded, 'operator' => 'NOT IN' ); }
	if ( $state['term'] instanceof WP_Term ) {
		$tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => array( $state['term']->term_id ), 'include_children' => true );
	}
	foreach ( array( 'materials' => 'pa_chat-lieu', 'sizes' => 'pa_kich-thuoc' ) as $key => $taxonomy ) {
		if ( $key !== $ignore && $state[ $key ] && taxonomy_exists( $taxonomy ) ) {
			$tax[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $state[ $key ], 'operator' => 'IN' );
		}
	}
	return $tax;
}

/** Count each facet in the current category and other selected facets. */
function caia_catalog_facet_counts( $taxonomy, $state, $ignore ) {
	global $wpdb;
	$tax_query = new WP_Tax_Query( caia_catalog_tax_query( $state, $ignore ) );
	$sql = $tax_query->get_sql( $wpdb->posts, 'ID' );
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT facets.term_id, COUNT(DISTINCT {$wpdb->posts}.ID) AS amount
		 FROM {$wpdb->posts}
		 INNER JOIN {$wpdb->term_relationships} AS facet_rel ON {$wpdb->posts}.ID = facet_rel.object_id
		 INNER JOIN {$wpdb->term_taxonomy} AS facets ON facet_rel.term_taxonomy_id = facets.term_taxonomy_id
		 {$sql['join']}
		 WHERE {$wpdb->posts}.post_type = 'product' AND {$wpdb->posts}.post_status = 'publish'
		 AND facets.taxonomy = %s {$sql['where']} GROUP BY facets.term_id",
		$taxonomy
	), ARRAY_A );
	return array_column( $rows, 'amount', 'term_id' );
}

function caia_catalog_base_url() {
	// WooCommerce can set is_page() on an archive while keeping the queried term.
	// A term ID must never be passed to get_permalink() as if it were a page ID.
	$object = get_queried_object();
	if ( $object instanceof WP_Term && $object->taxonomy === 'product_cat' ) {
		$url = get_term_link( $object );
		return is_wp_error( $url ) ? wc_get_page_permalink( 'shop' ) : $url;
	}
	if ( ! is_shop() && $object instanceof WP_Post && $object->post_type === 'page' ) {
		return get_permalink( $object->ID );
	}
	return wc_get_page_permalink( 'shop' );
}

function caia_catalog_category_options( $terms, $active, $parent = 0, $depth = 0 ) {
	foreach ( $terms as $term ) {
		if ( (int) $term->parent !== $parent ) { continue; }
		$url = get_term_link( $term );
		if ( is_wp_error( $url ) ) { continue; }
		echo '<option value="' . esc_attr( $term->slug ) . '" data-url="' . esc_url( $url ) . '"' . selected( $active ? $active->term_id : 0, $term->term_id, false ) . '>' . esc_html( str_repeat( '— ', $depth ) . $term->name ) . '</option>';
		caia_catalog_category_options( $terms, $active, $term->term_id, $depth + 1 );
	}
}

function caia_catalog_filter_group( $key, $taxonomy, $label, $state ) {
	if ( ! taxonomy_exists( $taxonomy ) ) { return; }
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'orderby' => 'name' ) );
	if ( is_wp_error( $terms ) || ! $terms ) { return; }
	if ( $key === 'sizes' ) { usort( $terms, function( $a, $b ) { return strnatcasecmp( $a->name, $b->name ); } ); }
	$counts = caia_catalog_facet_counts( $taxonomy, $state, $key );
	?>
	<fieldset class="catalog-filter-group">
		<legend><?php echo esc_html( $label ); ?></legend>
		<div class="catalog-filter-options<?php echo $key === 'sizes' ? ' catalog-filter-options--sizes' : ''; ?>">
			<?php foreach ( $terms as $term ) :
				$count = absint( $counts[ $term->term_id ] ?? 0 );
				$chosen = in_array( $term->slug, $state[ $key ], true );
				?>
				<label class="catalog-filter-option<?php echo ! $count && ! $chosen ? ' is-unavailable' : ''; ?>">
					<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>"<?php checked( $chosen ); ?><?php disabled( ! $count && ! $chosen ); ?>>
					<span><?php echo esc_html( $term->name ); ?></span><small aria-label="<?php echo esc_attr( $count . ' sản phẩm' ); ?>"><?php echo esc_html( $count ); ?></small>
				</label>
			<?php endforeach; ?>
		</div>
	</fieldset>
	<?php
}

function caia_render_catalog_page() {
	$state = caia_catalog_state();
	$ordering = WC()->query->get_catalog_ordering_args( $state['orderby'] );
	$query = new WP_Query( array_merge( array(
		'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 16,
		'paged' => $state['page'], 'tax_query' => caia_catalog_tax_query( $state ),
	), $ordering ) );
	WC()->query->remove_ordering_args();
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'exclude' => array( absint( get_option( 'default_product_cat' ) ) ), 'orderby' => 'menu_order' ) );
	$title = $state['term'] instanceof WP_Term ? $state['term']->name : 'Sản phẩm';
	?>
	<div class="catalog-page">
		<aside class="catalog-sidebar" aria-label="Bộ lọc sản phẩm">
			<details class="catalog-filters" open>
				<summary>Bộ lọc sản phẩm <i class="fa-solid fa-sliders" aria-hidden="true"></i></summary>
				<form id="catalog-filter-form" method="get" action="<?php echo esc_url( caia_catalog_base_url() ); ?>">
					<div class="catalog-filter-group">
						<label for="catalog-category">Phân loại</label>
						<select id="catalog-category" name="catalog_category" data-catalog-category>
							<option value="" data-url="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Chọn danh mục</option>
							<?php if ( ! is_wp_error( $terms ) ) { caia_catalog_category_options( $terms, $state['term'] ); } ?>
						</select>
					</div>
					<?php caia_catalog_filter_group( 'materials', 'pa_chat-lieu', 'Chất liệu', $state ); ?>
					<?php caia_catalog_filter_group( 'sizes', 'pa_kich-thuoc', 'Kích thước', $state ); ?>
					<div class="catalog-filter-actions"><button type="submit">Áp dụng</button><a href="<?php echo esc_url( caia_catalog_base_url() ); ?>">Xóa bộ lọc</a></div>
				</form>
			</details>
		</aside>
		<section class="catalog-results home-products" aria-labelledby="catalog-title">
			<h1 id="catalog-title" class="screen-reader-text"><?php echo esc_html( $title ); ?></h1>
			<div class="catalog-toolbar">
				<p class="screen-reader-text" role="status"><?php echo esc_html( $query->found_posts . ' sản phẩm' ); ?></p>
				<label class="screen-reader-text" for="catalog-orderby">Sắp xếp sản phẩm</label>
				<select id="catalog-orderby" name="orderby" form="catalog-filter-form">
					<?php foreach ( caia_catalog_order_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $state['orderby'], $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php if ( $query->have_posts() ) : ?>
				<div class="catalog-grid">
					<?php foreach ( $query->posts as $post ) { caia_render_product_card( wc_get_product( $post->ID ), $state['term'], 'h2' ); } ?>
				</div>
				<?php
				$pagination = paginate_links( array(
					'base' => str_replace( '999999999', '%#%', add_query_arg( 'catalog_page', 999999999 ) ),
					'format' => '', 'current' => $state['page'], 'total' => $query->max_num_pages,
					'prev_text' => 'Trước', 'next_text' => 'Sau',
				) );
				if ( $pagination ) : ?><nav class="catalog-pagination" aria-label="Phân trang sản phẩm"><?php echo wp_kses_post( $pagination ); ?></nav><?php endif; ?>
			<?php else : ?>
				<div class="catalog-empty"><p>Không có sản phẩm phù hợp với bộ lọc đã chọn.</p><a href="<?php echo esc_url( caia_catalog_base_url() ); ?>">Xóa bộ lọc</a></div>
			<?php endif; ?>
		</section>
	</div>
	<?php
}
