<?php
/**
 * Helpers for product-category landing pages backed by regular WordPress pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Clip thumbnail zoom within the image frame in standard WooCommerce loops. */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'caia_render_product_loop_thumbnail', 10 );
function caia_render_product_loop_thumbnail() {
	echo '<span class="product-card-image">' . woocommerce_get_product_thumbnail() . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/** Get the product category whose slug matches the current page slug. */
function caia_get_product_category_for_current_page() {
	$queried_object = get_queried_object();
	if ( $queried_object instanceof WP_Term && 'product_cat' === $queried_object->taxonomy ) {
		return $queried_object;
	}

	if ( ! is_page() ) {
		return null;
	}

	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) {
		return null;
	}

	$term = get_term_by( 'slug', $page->post_name, 'product_cat' );

	return $term instanceof WP_Term ? $term : null;
}

/** Add a stable body class so category-page CSS does not affect cart or checkout. */
function caia_product_category_page_body_class( $classes ) {
	if ( caia_get_product_category_for_current_page() ) {
		$classes[] = 'product-category-landing';
	}

	return $classes;
}
add_filter( 'body_class', 'caia_product_category_page_body_class' );

/** Remove the loop action that prints the Read more/add-to-cart button. */
function caia_prepare_product_category_landing_loop() {
	if ( caia_get_product_category_for_current_page() ) {
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	}
}
add_action( 'wp', 'caia_prepare_product_category_landing_loop' );

/** Ensure the matching product-category name appears above the page content. */
function caia_render_product_category_page_title() {
	$term = caia_get_product_category_for_current_page();
	if ( ! $term ) {
		return;
	}
	?>
	<header class="product-category-landing__header">
		<h1 class="product-category-landing__title"><?php echo esc_html( $term->name ); ?></h1>
	</header>
	<?php
}
add_action( 'genesis_before_entry_content', 'caia_render_product_category_page_title' );

/** Show a matching heading immediately before the WooCommerce product loop. */
function caia_render_product_category_products_title() {
	if ( ! caia_get_product_category_for_current_page() ) {
		return;
	}

	echo '<h2 class="product-category-landing__title product-category-products-title">Các sản phẩm</h2>';
}
add_action( 'woocommerce_before_shop_loop', 'caia_render_product_category_products_title', 5 );
