<?php
/** Shared shop/category catalog with category + attribute checkbox filters and WooCommerce ordering. */
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

/** WooCommerce attribute taxonomies, keyed by their URL parameter (filter_{slug}). */
function caia_catalog_attribute_filters() {
	$filters = array();
	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
		if ( taxonomy_exists( $taxonomy ) ) {
			$filters[ 'filter_' . $attribute->attribute_name ] = array( 'taxonomy' => $taxonomy, 'label' => $attribute->attribute_label );
		}
	}
	return $filters;
}

function caia_catalog_state() {
	// Checked categories win; otherwise an archive URL selects its own category.
	$categories = array();
	foreach ( caia_catalog_selected_slugs( 'categories' ) as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term instanceof WP_Term ) { $categories[] = $term; }
	}
	if ( ! isset( $_GET['categories'] ) ) {
		$archive = caia_get_product_category_for_current_page();
		if ( $archive instanceof WP_Term ) { $categories = array( $archive ); }
	}
	$attributes = array();
	foreach ( caia_catalog_attribute_filters() as $key => $filter ) {
		$attributes[ $key ] = caia_catalog_selected_slugs( $key );
	}
	$orderby = isset( $_GET['orderby'] ) && is_string( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : get_option( 'woocommerce_default_catalog_orderby', 'menu_order' );
	if ( ! isset( caia_catalog_order_options()[ $orderby ] ) ) { $orderby = 'menu_order'; }
	$page = isset( $_GET['catalog_page'] ) && is_scalar( $_GET['catalog_page'] ) ? max( 1, absint( $_GET['catalog_page'] ) ) : max( 1, absint( get_query_var( 'paged' ) ) );
	return array(
		'categories' => $categories,
		'term'       => count( $categories ) === 1 ? $categories[0] : null,
		'attributes' => $attributes,
		'orderby'    => $orderby,
		'page'       => $page,
	);
}

function caia_catalog_tax_query( $state, $ignore = '' ) {
	$tax = array( 'relation' => 'AND' );
	$visibility = wc_get_product_visibility_term_ids();
	$excluded = array_filter( array( $visibility['exclude-from-catalog'] ?? 0, get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' ? ( $visibility['outofstock'] ?? 0 ) : 0 ) );
	if ( $excluded ) { $tax[] = array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => $excluded, 'operator' => 'NOT IN' ); }
	if ( $ignore !== 'categories' && $state['categories'] ) {
		$tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => wp_list_pluck( $state['categories'], 'term_id' ), 'include_children' => true, 'operator' => 'IN' );
	}
	foreach ( caia_catalog_attribute_filters() as $key => $filter ) {
		if ( $key !== $ignore && ! empty( $state['attributes'][ $key ] ) ) {
			$tax[] = array( 'taxonomy' => $filter['taxonomy'], 'field' => 'slug', 'terms' => $state['attributes'][ $key ], 'operator' => 'IN' );
		}
	}
	return $tax;
}

/** Count each facet within the other selected filters. */
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

function caia_catalog_checkbox( $name, $term, $count, $chosen, $extra = '', $disable_empty = true ) {
	$empty = ! $count && ! $chosen;
	?>
	<label class="catalog-filter-option<?php echo $empty ? ' is-unavailable' : ''; ?><?php echo $chosen ? ' is-checked' : ''; ?>">
		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>"<?php checked( $chosen ); ?><?php disabled( $disable_empty && $empty ); ?><?php echo $extra; // Pre-escaped data attributes. ?>>
		<span class="catalog-filter-option__box" aria-hidden="true"></span>
		<span class="catalog-filter-option__name"><?php echo esc_html( $term->name ); ?></span>
		<small aria-label="<?php echo esc_attr( $count . ' sản phẩm' ); ?>"><?php echo esc_html( $count ); ?></small>
	</label>
	<?php
}

function caia_catalog_category_group( $terms, $state ) {
	if ( is_wp_error( $terms ) || ! $terms ) { return; }
	$counts = caia_catalog_facet_counts( 'product_cat', $state, 'categories' );
	$chosen_ids = wp_list_pluck( $state['categories'], 'term_id' );
	$render = function( $parent ) use ( &$render, $terms, $counts, $chosen_ids ) {
		$children = array_filter( $terms, function( $term ) use ( $parent ) { return (int) $term->parent === $parent; } );
		if ( ! $children ) { return; }
		echo $parent ? '<div class="catalog-filter-options catalog-filter-options--children">' : '';
		foreach ( $children as $term ) {
			$url = get_term_link( $term );
			if ( is_wp_error( $url ) ) { continue; }
			// Categories stay clickable even when empty: they also act as navigation.
			caia_catalog_checkbox( 'categories', $term, absint( $counts[ $term->term_id ] ?? 0 ), in_array( $term->term_id, $chosen_ids, true ), ' data-catalog-category data-url="' . esc_url( $url ) . '"', false );
			$render( (int) $term->term_id );
		}
		echo $parent ? '</div>' : '';
	};
	?>
	<fieldset class="catalog-filter-group catalog-filter-group--categories">
		<legend class="screen-reader-text">Danh mục sản phẩm</legend>
		<div class="catalog-filter-options"><?php $render( 0 ); ?></div>
	</fieldset>
	<?php
}

function caia_catalog_attribute_group( $key, $filter, $state ) {
	$terms = get_terms( array( 'taxonomy' => $filter['taxonomy'], 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) || ! $terms ) { return; }
	usort( $terms, function( $a, $b ) { return strnatcasecmp( $a->name, $b->name ); } );
	$counts = caia_catalog_facet_counts( $filter['taxonomy'], $state, $key );
	$chosen = $state['attributes'][ $key ] ?? array();
	// Hide attributes that no product in the current scope uses.
	if ( ! array_filter( $counts ) && ! $chosen ) { return; }
	?>
	<fieldset class="catalog-filter-group">
		<legend><?php echo esc_html( $filter['label'] ); ?></legend>
		<div class="catalog-filter-options">
			<?php foreach ( $terms as $term ) {
				caia_catalog_checkbox( $key, $term, absint( $counts[ $term->term_id ] ?? 0 ), in_array( $term->slug, $chosen, true ) );
			} ?>
		</div>
	</fieldset>
	<?php
}

/**
 * URL for a filter combination: one category opens its own archive, none or several use the shop.
 * Mirrors the routing in catalog.js.
 */
function caia_catalog_view_url( $category_slugs, $attributes, $orderby ) {
	$url  = wc_get_page_permalink( 'shop' );
	$args = array();
	if ( count( $category_slugs ) === 1 ) {
		$link = get_term_link( $category_slugs[0], 'product_cat' );
		$url  = is_wp_error( $link ) ? $url : $link;
	} elseif ( $category_slugs ) {
		$args['categories'] = $category_slugs;
	}
	foreach ( $attributes as $key => $slugs ) {
		if ( $slugs ) { $args[ $key ] = $slugs; }
	}
	if ( $orderby !== get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) ) { $args['orderby'] = $orderby; }
	return $args ? $url . '?' . http_build_query( $args ) : $url;
}

/** Chips for every active filter, each linking to the same view without it. */
function caia_catalog_active_filters( $state ) {
	$chips = array();
	$category_slugs = wp_list_pluck( $state['categories'], 'slug' );
	foreach ( $state['categories'] as $term ) {
		$chips[] = array( $term->name, caia_catalog_view_url( array_values( array_diff( $category_slugs, array( $term->slug ) ) ), $state['attributes'], $state['orderby'] ) );
	}
	foreach ( caia_catalog_attribute_filters() as $key => $filter ) {
		foreach ( $state['attributes'][ $key ] as $slug ) {
			$term = get_term_by( 'slug', $slug, $filter['taxonomy'] );
			if ( ! $term instanceof WP_Term ) { continue; }
			$attributes = $state['attributes'];
			$attributes[ $key ] = array_values( array_diff( $attributes[ $key ], array( $slug ) ) );
			$chips[] = array( $term->name, caia_catalog_view_url( $category_slugs, $attributes, $state['orderby'] ) );
		}
	}
	if ( ! $chips ) { return; }
	?>
	<div class="catalog-active">
		<span class="catalog-active__label">Đang lọc:</span>
		<?php foreach ( $chips as $chip ) : ?>
			<a class="catalog-active__chip" href="<?php echo esc_url( $chip[1] ); ?>" aria-label="<?php echo esc_attr( 'Bỏ lọc ' . $chip[0] ); ?>"><?php echo esc_html( $chip[0] ); ?> <i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
		<?php endforeach; ?>
		<a class="catalog-active__clear" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Xóa tất cả</a>
	</div>
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
				<form id="catalog-filter-form" method="get" action="<?php echo esc_url( caia_catalog_base_url() ); ?>" data-shop-url="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
					<p class="catalog-filters__title"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Bộ lọc sản phẩm</p>
					<?php caia_catalog_category_group( $terms, $state ); ?>
					<?php foreach ( caia_catalog_attribute_filters() as $key => $filter ) { caia_catalog_attribute_group( $key, $filter, $state ); } ?>
					<div class="catalog-filter-actions"><button type="submit">Áp dụng</button><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Xóa bộ lọc</a></div>
				</form>
			</details>
		</aside>
		<section class="catalog-results home-products" aria-labelledby="catalog-title">
			<h1 id="catalog-title" class="screen-reader-text"><?php echo esc_html( $title ); ?></h1>
			<div class="catalog-toolbar">
				<p class="catalog-toolbar__count" role="status"><?php echo esc_html( sprintf( '%d sản phẩm', $query->found_posts ) ); ?></p>
				<label class="screen-reader-text" for="catalog-orderby">Sắp xếp sản phẩm</label>
				<select id="catalog-orderby" name="orderby" form="catalog-filter-form">
					<?php foreach ( caia_catalog_order_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $state['orderby'], $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php caia_catalog_active_filters( $state ); ?>
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
				<div class="catalog-empty"><p>Không có sản phẩm phù hợp với bộ lọc đã chọn.</p><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Xóa bộ lọc</a></div>
			<?php endif; ?>
		</section>
	</div>
	<?php
}
