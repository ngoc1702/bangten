<?php
/**
 * Template Name: Trang chủ ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'genesis_site_layout', function() { return 'full-width-content'; }, 100 );
add_filter( 'body_class', function( $classes ) {
	$classes[] = 'caia-homepage';
	return $classes;
} );

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'caia_render_home_page' );
function caia_render_home_page() {
	while ( have_posts() ) {
		the_post();
		echo '<div class="home-page"><h1 class="home-page__title">' . esc_html( get_the_title() ) . '</h1>';
		caia_render_home_banner( get_the_ID() );
		caia_render_home_categories( get_the_ID() );
		caia_render_home_product_sections( get_the_ID() );
		caia_render_home_featured_news( get_the_ID() );
		caia_render_home_reviews( get_the_ID() );
		echo '</div>';
	}
}

genesis();
