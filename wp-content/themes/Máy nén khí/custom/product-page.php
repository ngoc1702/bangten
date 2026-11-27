<?php
/** Product detail layout and widget-managed contact/policy sections. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Caia_Product_Contact_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'caia_product_contact', 'Liên hệ sản phẩm', array( 'description' => 'Thay số điện thoại gọi và Zalo trên tất cả trang sản phẩm.' ) );
	}

	public function widget( $args, $instance ) {
		$phone = trim( (string) ( $instance['phone'] ?? '' ) );
		$zalo = preg_replace( '/\D/', '', (string) ( $instance['zalo'] ?? '' ) );
		$phone_href = preg_replace( '/[^\d+]/', '', $phone );
		if ( ! $phone_href && ! $zalo ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="product-contact__buttons">';
		if ( $zalo ) {
			echo '<a class="product-contact__button product-contact__button--zalo" href="' . esc_url( 'https://zalo.me/' . $zalo ) . '" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-comment-dots" aria-hidden="true"></i>' . esc_html( ! empty( $instance['zalo_label'] ) ? $instance['zalo_label'] : 'Zalo' ) . '</a>';
		}
		if ( $phone_href ) {
			echo '<a class="product-contact__button product-contact__button--phone" href="' . esc_url( 'tel:' . $phone_href ) . '"><i class="fa-solid fa-phone" aria-hidden="true"></i>' . esc_html( ! empty( $instance['phone_label'] ) ? $instance['phone_label'] : $phone ) . '</a>';
		}
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$fields = array( 'phone' => 'Số điện thoại gọi', 'zalo' => 'Số điện thoại Zalo', 'zalo_label' => 'Chữ nút Zalo', 'phone_label' => 'Chữ nút gọi (để trống dùng số điện thoại)' );
		foreach ( $fields as $key => $label ) {
			$value = $instance[ $key ] ?? ( $key === 'zalo_label' ? 'Zalo' : '' );
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $label ); ?></label><input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>"></p>
			<?php
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		foreach ( array( 'phone', 'zalo', 'zalo_label', 'phone_label' ) as $key ) {
			$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] ?? '' );
		}
		return $instance;
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'Caia_Product_Contact_Widget' );
	genesis_register_widget_area( array( 'id' => 'product-contact', 'name' => 'Sản phẩm — Nút liên hệ', 'description' => 'Thêm widget “Liên hệ sản phẩm” để thay số gọi và Zalo.' ) );
	genesis_register_widget_area( array( 'id' => 'product-commitments', 'name' => 'Sản phẩm — Cam kết & Chính sách', 'description' => 'Mỗi widget Hình ảnh là một thẻ cam kết. Thay ảnh và tiêu đề, kéo thả để đổi thứ tự.' ) );
} );

add_action( 'wp_enqueue_scripts', function() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$path = get_stylesheet_directory() . '/assets/css/product.css';
	wp_enqueue_style( 'caia-product', get_stylesheet_directory_uri() . '/assets/css/product.css', array( 'caia-home' ), filemtime( $path ) );
}, 170 );

add_action( 'wp', 'caia_configure_product_detail', 20 );
function caia_configure_product_detail() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	// Price (or "Liên hệ") sits right under the category line.
	remove_action( 'woocommerce_single_product_summary', 'caia_render_single_product_price', 11 );
	add_action( 'woocommerce_single_product_summary', 'caia_render_single_product_price', 9 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	remove_theme_support( 'wc-product-gallery-zoom' );
	add_action( 'woocommerce_before_single_product_summary', 'caia_product_overview_open', 1 );
	add_action( 'woocommerce_after_single_product_summary', 'caia_product_overview_close', 1 );
	add_action( 'woocommerce_single_product_summary', 'caia_product_categories', 8 );
	add_action( 'woocommerce_single_product_summary', 'caia_product_attributes', 26 );
	add_action( 'woocommerce_single_product_summary', 'caia_product_contact', 40 );
	add_action( 'woocommerce_after_single_product_summary', 'caia_product_commitments', 15 );
	add_action( 'woocommerce_after_single_product_summary', 'caia_product_related', 20 );
}

function caia_product_overview_open() { echo '<div class="product-overview">'; }
function caia_product_overview_close() { echo '</div>'; }

function caia_product_categories() {
	$categories = wc_get_product_category_list( get_the_ID(), ', ' );
	if ( $categories ) {
		echo '<p class="product-detail__categories"><strong>Danh mục:</strong> ' . wp_kses_post( $categories ) . '</p>';
	}
}

function caia_product_attributes() {
	global $product;
	if ( $product instanceof WC_Product && ( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) ) {
		echo '<div class="product-detail__attributes">';
		wc_display_product_attributes( $product );
		echo '</div>';
	}
}

function caia_product_contact() {
	if ( is_active_sidebar( 'product-contact' ) ) {
		echo '<div class="product-contact-area">';
		dynamic_sidebar( 'product-contact' );
		echo '</div>';
	}
}

function caia_product_section_heading( $title ) {
	echo '<h2 class="home-products__heading">' . esc_html( $title ) . '</h2><img class="home-section-heading__ornament" src="' . esc_url( caia_ornament_url() ) . '" width="200" height="68" alt="" aria-hidden="true">';
}

function caia_product_commitments() {
	if ( ! is_active_sidebar( 'product-commitments' ) ) {
		return;
	}
	echo '<section class="product-commitments home-products"><header class="home-products__header">';
	caia_product_section_heading( 'Cam kết & Chính sách' );
	echo '</header><div class="product-commitments__grid">';
	dynamic_sidebar( 'product-commitments' );
	echo '</div></section>';
}

// Use the original icon instead of a cropped media thumbnail in policy widgets.
add_filter( 'widget_media_image_instance', function( $instance, $args ) {
	if ( ( $args['id'] ?? '' ) === 'product-commitments' ) {
		$instance['size'] = 'full';
		$instance['image_classes'] = trim( ( $instance['image_classes'] ?? '' ) . ' product-commitments__icon' );
	}
	return $instance;
}, 10, 2 );

add_filter( 'wp_get_attachment_image_attributes', function( $attributes ) {
	if ( in_array( 'product-commitments__icon', explode( ' ', $attributes['class'] ?? '' ), true ) ) {
		// Keep later content filters from restoring lower-resolution thumbnail candidates.
		$attributes['srcset'] = $attributes['src'];
		unset( $attributes['sizes'] );
	}
	return $attributes;
} );

function caia_product_related() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$current_id = $product->get_id();
	$items = array_filter( array_map( 'wc_get_product', wc_get_related_products( $current_id, 6 ) ), function( $item ) use ( $current_id ) {
		return $item instanceof WC_Product && $item->get_id() !== $current_id && $item->get_status() === 'publish' && $item->is_visible();
	} );
	$items = array_slice( $items, 0, 4 );
	// Top up with the newest products from the same categories when WooCommerce finds fewer than 4.
	if ( count( $items ) < 4 ) {
		$have  = array_merge( array( $current_id ), array_map( function( $i ) { return $i->get_id(); }, $items ) );
		$extra = wc_get_products( array( 'status' => 'publish', 'visibility' => 'catalog', 'limit' => 4 - count( $items ), 'exclude' => $have, 'category' => wp_list_pluck( wc_get_product_terms( $current_id, 'product_cat' ), 'slug' ), 'orderby' => 'date', 'order' => 'DESC' ) );
		$items = array_merge( array_values( $items ), $extra );
	}
	if ( ! $items ) {
		return;
	}
	echo '<section class="product-related home-products"><header class="home-products__header">';
	caia_product_section_heading( 'Sản phẩm tương tự' );
	echo '</header><div class="home-products__grid">';
	foreach ( $items as $item ) {
		caia_render_product_card( $item );
	}
	echo '</div></section>';
}

/** Shared homepage, related-product and search card. */
function caia_render_product_card( $item, $term = null, $heading_tag = 'h3' ) {
	if ( ! $item instanceof WC_Product ) { return; }
	// $term is kept for existing callers; the card no longer shows the category.
	$heading_tag = $heading_tag === 'h2' ? 'h2' : 'h3';
	?>
	<article class="home-products__card">
		<a class="home-products__image-link" href="<?php echo esc_url( $item->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $item->get_name() ); ?>"><?php echo $item->get_image( 'woocommerce_single', array( 'class' => 'home-products__image', 'loading' => 'lazy' ) ); ?></a>
		<div class="home-products__body">
			<<?php echo $heading_tag; ?> class="home-products__title"><a href="<?php echo esc_url( $item->get_permalink() ); ?>"><?php echo esc_html( $item->get_name() ); ?></a></<?php echo $heading_tag; ?>>
			<p class="home-products__price">
				<?php if ( $item->get_price() !== '' ) : ?>
					<?php echo wp_kses_post( $item->get_price_html() ); ?>
				<?php else : ?>
					<span class="home-products__contact">Liên hệ</span>
				<?php endif; ?>
			</p>
		</div>
	</article>
	<?php
}

add_filter( 'woocommerce_single_product_carousel_options', function( $options ) {
	$options['directionNav'] = true;
	$options['controlNav'] = 'thumbnails';
	$options['slideshow'] = false;
	$options['prevText'] = '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i><span class="screen-reader-text">Ảnh trước</span>';
	$options['nextText'] = '<i class="fa-solid fa-chevron-right" aria-hidden="true"></i><span class="screen-reader-text">Ảnh tiếp theo</span>';
	return $options;
} );

add_filter( 'woocommerce_short_description', function( $content ) {
	return function_exists( 'is_product' ) && is_product() ? force_balance_tags( $content ) : $content;
}, 20 );

add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false' );
