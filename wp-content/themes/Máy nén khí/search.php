<?php
/** Product search uses the same cards and responsive grid as the homepage. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'body_class', function( $classes ) { $classes[] = 'product-search-results'; return $classes; } );
add_filter( 'genesis_site_layout', function() { return 'full-width-content'; }, 100 );
remove_action( 'genesis_before_loop', 'genesis_do_search_title' );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'caia_render_product_search_results' );

function caia_render_product_search_results() {
	$search = get_search_query( false );
	$paged = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
	$args = array( 'post_type' => 'product', 'post_status' => 'publish', 's' => $search, 'posts_per_page' => 16, 'paged' => $paged, 'ignore_sticky_posts' => true );
	if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		$visibility = wc_get_product_visibility_term_ids();
		$excluded = array( $visibility['exclude-from-search'] );
		if ( get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' ) { $excluded[] = $visibility['outofstock']; }
		$args['tax_query'] = array( array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => array_filter( $excluded ), 'operator' => 'NOT IN' ) );
	}
	$results = new WP_Query( $args );
	?>
	<div class="product-search-page home-products">
		<header class="product-search-page__header">
			<h1>Kết quả tìm kiếm</h1>
			<p>Tìm thấy <?php echo esc_html( number_format_i18n( $results->found_posts ) ); ?> tác phẩm<?php if ( $search !== '' ) : ?> cho “<strong><?php echo esc_html( $search ); ?></strong>”<?php endif; ?>.</p>
		</header>
		<?php if ( $results->posts ) : ?>
			<div class="home-products__grid">
				<?php foreach ( $results->posts as $result ) {
					$item = function_exists( 'wc_get_product' ) ? wc_get_product( $result->ID ) : null;
					caia_render_product_card( $item, null, 'h2' );
				} ?>
			</div>
		<?php else : ?>
			<p class="product-search-page__empty">Không tìm thấy sản phẩm phù hợp. Vui lòng thử từ khóa khác.</p>
		<?php endif; ?>
		<?php
		$base = add_query_arg( array( 's' => $search, 'post_type' => 'product', 'paged' => 999999999 ), home_url( '/' ) );
		$pagination = paginate_links( array(
			'base' => str_replace( '999999999', '%#%', $base ), 'format' => '',
			'current' => $paged, 'total' => $results->max_num_pages,
			'mid_size' => 1, 'prev_text' => 'Trước', 'next_text' => 'Sau',
		) );
		if ( $pagination ) : ?><nav class="product-search-page__pagination" aria-label="Phân trang sản phẩm"><?php echo wp_kses_post( $pagination ); ?></nav><?php endif; ?>
	</div>
	<?php
}

add_action( 'genesis_after_content_sidebar_wrap', function() {
	if ( is_active_sidebar( 'content-anhhero' ) ) {
		echo '<div class="content-anhhero">';
		dynamic_sidebar( 'content-anhhero' );
		echo '</div>';
	}
} );
genesis();
