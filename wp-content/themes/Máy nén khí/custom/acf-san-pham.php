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
			'title' => 'Thông số kỹ thuật',
			'fields' => array(
				array(
					'key'          => 'field_caia_product_specifications',
					'label'        => 'Bảng thông số',
					'name'         => 'product_specifications',
					'type'         => 'repeater',
					'instructions' => 'Bấm "Thêm hàng" để thêm dòng thông số (hiện ở cột phải cạnh Mô tả sản phẩm). Có thể kéo thả để đổi thứ tự.',
					'layout'       => 'table',
					'button_label' => 'Thêm hàng',
					'sub_fields'   => array(
						array(
							'key'      => 'field_caia_product_specification_name',
							'label'    => 'Tên thông số',
							'name'     => 'name',
							'type'     => 'text',
							'required' => 1,
							'wrapper'  => array( 'width' => '45' ),
						),
						array(
							'key'      => 'field_caia_product_specification_value',
							'label'    => 'Giá trị',
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

	echo '<p class="price caia-product-price"><span class="caia-product-price__label">Giá:</span> ' . wp_kses_post( $price_html ) . '</p>';
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

/** Rows of the technical-specification repeater, empty rows removed. */
function caia_get_product_specification_rows() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$rows = get_field( 'product_specifications', get_the_ID() );
	if ( ! is_array( $rows ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$rows,
			function( $row ) {
				return '' !== trim( (string) ( $row['name'] ?? '' ) ) || '' !== trim( (string) ( $row['value'] ?? '' ) );
			}
		)
	);
}

/** Replaces WooCommerce tabs: description on the left, sticky technical specifications on the right. */
function caia_render_product_body() {
	$description = trim( (string) get_post_field( 'post_content', get_the_ID() ) );
	$specs       = caia_get_product_specification_rows();

	if ( '' === $description && empty( $specs ) ) {
		return;
	}

	echo '<div class="product-body' . ( '' !== $description && ! empty( $specs ) ? ' product-body--with-specs' : '' ) . '">';

	if ( '' !== $description ) {
		echo '<div class="product-body__main"><details class="product-accordion product-accordion--description" open>';
		echo '<summary>Mô tả sản phẩm</summary>';
		echo '<div class="product-accordion__content product-description__content">';
		echo apply_filters( 'the_content', $description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></details></div>';
	}

	if ( ! empty( $specs ) ) {
		echo '<aside class="product-body__aside" aria-label="Thông số kỹ thuật"><details class="product-accordion product-accordion--specs" open>';
		echo '<summary>Thông số kỹ thuật</summary>';
		echo '<div class="product-accordion__content product-specifications"><table class="product-specifications__table"><tbody>';
		foreach ( $specs as $row ) {
			echo '<tr><th scope="row">' . esc_html( $row['name'] ?? '' ) . '</th><td>' . esc_html( $row['value'] ?? '' ) . '</td></tr>';
		}
		echo '</tbody></table></div></details></aside>';
	}

	echo '</div>';
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'woocommerce_after_single_product_summary', 'caia_render_product_body', 10 );
