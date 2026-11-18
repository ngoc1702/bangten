<?php
/**
 * ACF fields and frontend output for product technical specifications.
 */

add_action( 'acf/init', 'caia_register_product_specification_fields' );

function caia_register_product_specification_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_caia_product_specifications',
			'title' => 'Đặc điểm kỹ thuật',
			'fields' => array(
				array(
					'key'          => 'field_caia_product_specifications',
					'label'        => 'Bảng thông số',
					'name'         => 'product_specifications',
					'type'         => 'repeater',
					'instructions' => 'Bấm "Thêm hàng" để thêm đặc điểm và thông số. Có thể kéo thả để đổi thứ tự.',
					'layout'       => 'table',
					'button_label' => 'Thêm hàng',
					'sub_fields'   => array(
						array(
							'key'      => 'field_caia_product_specification_name',
							'label'    => 'Tên đặc điểm',
							'name'     => 'name',
							'type'     => 'text',
							'required' => 1,
							'wrapper'  => array( 'width' => '45' ),
						),
						array(
							'key'      => 'field_caia_product_specification_value',
							'label'    => 'Thông số',
							'name'     => 'value',
							'type'     => 'text',
							'required' => 1,
							'wrapper'  => array( 'width' => '55' ),
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}

// Product details show information without quotation or purchase controls.
add_action( 'wp', 'caia_remove_single_product_purchase_controls' );
function caia_remove_single_product_purchase_controls() {
	if ( ! is_product() ) {
		return;
	}

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
}

/** Use WooCommerce's album slider and selectable thumbnails without hover zoom. */
function caia_setup_product_gallery() {
	add_theme_support( 'wc-product-gallery-slider' );
	remove_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'after_setup_theme', 'caia_setup_product_gallery', 100 );

// Use an uncropped source so album thumbnails show the complete product too.
add_filter( 'woocommerce_gallery_thumbnail_size', 'caia_product_album_thumbnail_size' );
function caia_product_album_thumbnail_size( $size ) {
	return is_product() ? 'woocommerce_single' : $size;
}

/** Always show the product name in the right-hand product summary. */
function caia_render_single_product_title() {
	echo '<h1 class="product_title entry-title">' . esc_html( get_the_title() ) . '</h1>';
}

/** Show WooCommerce's price or a contact label when no price is set. */
function caia_render_single_product_price() {
	global $product;

	$price_html = $product instanceof WC_Product ? $product->get_price_html() : '';
	if ( empty( $price_html ) ) {
		$price_html = '<span class="price">Liên hệ</span>';
	}

	echo '<p class="price caia-product-price">' . wp_kses_post( $price_html ) . '</p>';
}

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary', 'caia_render_single_product_title', 5 );
add_action( 'woocommerce_single_product_summary', 'caia_render_single_product_price', 11 );

/** Remove theme and WooCommerce share controls from product detail pages only. */
function caia_remove_single_product_share_buttons() {
	global $caia_social;

	if ( is_object( $caia_social ) ) {
		$priority = has_filter( 'the_content', array( $caia_social, 'add_native_share_button_at_bottom' ) );

		if ( false !== $priority ) {
			remove_filter( 'the_content', array( $caia_social, 'add_native_share_button_at_bottom' ), $priority );
		}
	}

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
}
add_action( 'wp', 'caia_remove_single_product_share_buttons' );

/** Replaces WooCommerce tabs with a compact, accessible product-description accordion. */
function caia_render_product_description_accordion() {
	$product_id  = get_the_ID();
	$description = get_post_field( 'post_content', $product_id );

	if ( trim( $description ) === '' ) {
		return;
	}

	echo '<details class="product-accordion product-accordion--description" open>';
	echo '<summary>Mô tả sản phẩm</summary>';
	echo '<div class="product-accordion__content product-description__content">';
	echo apply_filters( 'the_content', $description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';
	echo '</details>';
}

/** Displays the technical-specification table alongside the product images. */
function caia_render_product_specifications() {
	if ( ! function_exists( 'have_rows' ) || ! have_rows( 'product_specifications' ) ) {
		return;
	}

	echo '<div class="product-specifications"><table class="product-specifications__table"><tbody>';

	while ( have_rows( 'product_specifications' ) ) {
		the_row();
		$name  = get_sub_field( 'name' );
		$value = get_sub_field( 'value' );

		if ( '' === trim( (string) $name ) && '' === trim( (string) $value ) ) {
			continue;
		}

		echo '<tr>';
		echo '<th scope="row">' . esc_html( $name ) . '</th>';
		echo '<td>' . esc_html( $value ) . '</td>';
		echo '</tr>';
	}

	echo '</tbody></table></div>';
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'woocommerce_after_single_product_summary', 'caia_render_product_description_accordion', 10 );
add_action( 'woocommerce_single_product_summary', 'caia_render_product_specifications', 25 );
