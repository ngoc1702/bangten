<?php
/**
 * Template Name: Tin tức ACF
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_filter( 'genesis_site_layout', function() { return 'full-width-content'; }, 100 );
add_filter( 'body_class', function( $classes ) { $classes[] = 'caia-news-page'; return $classes; } );
remove_action( 'genesis_after_header', 'genesis_do_breadcrumbs', 5 );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_after_header', 'caia_render_news_page_banner', 6 );
add_action( 'genesis_loop', 'caia_render_news_page' );

function caia_render_news_page_banner() {
	$id = get_queried_object_id();
	$title = function_exists( 'get_field' ) ? trim( (string) get_field( 'news_page_title', $id ) ) : '';
	$image = function_exists( 'get_field' ) ? absint( get_field( 'news_page_image', $id ) ) : 0;
	caia_render_page_banner( $title ?: get_the_title( $id ), $image );
}

function caia_news_page_categories() {
	$rows = function_exists( 'get_field' ) ? (array) get_field( 'news_page_categories', get_queried_object_id() ) : array();
	$categories = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) { continue; }
		$term = get_term( absint( $row['category'] ?? 0 ), 'category' );
		if ( $term instanceof WP_Term ) { $categories[$term->term_id] = $term; }
	}
	if ( ! $categories ) {
		$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'exclude' => array( (int) get_option( 'default_category' ) ) ) );
		$categories = is_wp_error( $terms ) ? array() : $terms;
	}
	return array_values( $categories );
}

function caia_render_news_page() {
	$url = get_permalink( get_queried_object_id() );
	$categories = caia_news_page_categories();
	$requested = isset( $_GET['chuyen_muc'] ) && is_string( $_GET['chuyen_muc'] ) ? sanitize_title( wp_unslash( $_GET['chuyen_muc'] ) ) : '';
	$active = $categories ? $categories[0] : null;
	foreach ( $categories as $term ) { if ( $term->slug === $requested ) { $active = $term; break; } }
	$paged = isset( $_GET['trang'] ) && is_scalar( $_GET['trang'] ) ? max( 1, absint( $_GET['trang'] ) ) : max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
	$args = array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 16, 'paged' => $paged, 'ignore_sticky_posts' => true );
	if ( $active ) { $args['cat'] = $active->term_id; }
	$query = new WP_Query( $args );
	?>
	<div class="news-page home-news">
		<div class="wrap">
			<?php if ( $categories ) : ?>
				<nav class="news-page__categories" aria-label="Danh mục bài viết">
					<?php foreach ( $categories as $term ) : ?>
						<a class="news-page__category<?php echo $active && $active->term_id === $term->term_id ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'chuyen_muc', $term->slug, $url ) ); ?>"<?php if ( $active && $active->term_id === $term->term_id ) : ?> aria-current="page"<?php endif; ?>><?php echo esc_html( $term->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
			<?php if ( $query->posts ) : ?>
				<div class="news-page__grid">
					<?php foreach ( $query->posts as $news_post ) { caia_render_news_card( $news_post, 'h2' ); } ?>
				</div>
			<?php else : ?>
				<p class="news-page__empty">Chưa có bài viết trong danh mục này.</p>
			<?php endif; ?>
			<?php
			$base_url = $active ? add_query_arg( 'chuyen_muc', $active->slug, $url ) : $url;
			$pagination = paginate_links( array(
				'base' => str_replace( '999999999', '%#%', add_query_arg( 'trang', 999999999, $base_url ) ),
				'format' => '', 'current' => $paged, 'total' => $query->max_num_pages,
				'mid_size' => 1, 'prev_text' => 'Trước', 'next_text' => 'Sau',
				'add_args' => $active ? array( 'chuyen_muc' => $active->slug ) : false,
			) );
			if ( $pagination ) : ?><nav class="news-page__pagination" aria-label="Phân trang bài viết"><?php echo wp_kses_post( $pagination ); ?></nav><?php endif; ?>
		</div>
	</div>
	<?php
}

genesis();
