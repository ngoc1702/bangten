<?php
/**
 * Footer widgets with form fields, so editing content can never break the footer layout.
 * Area "Toàn bộ - Nội dung trước chân trang" uses them in order: Thông tin → Liên kết → Kết nối.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paths saved as "/wp-content/..." or "/lien-he" resolve against the address the site is
 * opened on (maynenkhi.test, localhost/maynenkhi, ...), so images and links never break.
 */
function caia_site_url( $url ) {
	$url = trim( (string) $url );
	return preg_match( '#^/(?!/)#', $url ) ? home_url( $url ) : $url;
}

// Same for menu links and src/href in Text widgets (e.g. the header hotline icon).
add_filter( 'nav_menu_link_attributes', function( $atts ) {
	if ( ! empty( $atts['href'] ) ) {
		$atts['href'] = caia_site_url( $atts['href'] );
	}
	return $atts;
} );
add_filter( 'widget_text_content', function( $text ) {
	return preg_replace_callback( '#\b(src|href)="(/(?!/)[^"]*)"#', function( $m ) {
		return $m[1] . '="' . esc_url( home_url( $m[2] ) ) . '"';
	}, $text );
}, 5 );

/** Small helpers shared by the footer widget forms. */
trait Caia_Footer_Widget_Form {
	protected function text_field( $instance, $key, $label, $placeholder = '' ) {
		$value = $instance[ $key ] ?? '';
		printf(
			'<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s" placeholder="%5$s"></p>',
			esc_attr( $this->get_field_id( $key ) ),
			esc_html( $label ),
			esc_attr( $this->get_field_name( $key ) ),
			esc_attr( $value ),
			esc_attr( $placeholder )
		);
	}

	protected function select_field( $instance, $key, $label, $options ) {
		$value = $instance[ $key ] ?? '';
		printf( '<p><label for="%1$s">%2$s</label><select class="widefat" id="%1$s" name="%3$s">', esc_attr( $this->get_field_id( $key ) ), esc_html( $label ), esc_attr( $this->get_field_name( $key ) ) );
		foreach ( $options as $option => $option_label ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $option ), selected( $value, $option, false ), esc_html( $option_label ) );
		}
		echo '</select></p>';
	}
}

class Caia_Footer_Info_Widget extends WP_Widget {
	use Caia_Footer_Widget_Form;

	const ROWS = 6;

	public static function icons() {
		return array(
			''                   => '— Không icon —',
			'fa-solid fa-store'  => 'Công ty / Cửa hàng',
			'fa-solid fa-location-dot' => 'Địa chỉ',
			'fa-solid fa-phone'  => 'Điện thoại',
			'fa-solid fa-mobile-screen' => 'Di động',
			'fa-regular fa-envelope' => 'Email',
			'fa-regular fa-comment-dots' => 'Zalo / Tin nhắn',
			'fa-solid fa-globe'  => 'Website',
			'fa-regular fa-clock' => 'Giờ làm việc',
			'fa-solid fa-file-invoice' => 'Mã số thuế',
		);
	}

	public function __construct() {
		parent::__construct( 'caia_footer_info', 'Chân trang: Thông tin', array( 'description' => 'Tên công ty và các dòng thông tin (icon, nội dung, liên kết). Dòng để trống nội dung sẽ tự ẩn.' ) );
	}

	public function widget( $args, $instance ) {
		$logo  = trim( (string) ( $instance['company'] ?? '' ) );
		$rows  = array();
		for ( $i = 1; $i <= self::ROWS; $i++ ) {
			$text = trim( (string) ( $instance[ "text_$i" ] ?? '' ) );
			if ( $text !== '' ) {
				$rows[] = array( 'icon' => (string) ( $instance[ "icon_$i" ] ?? '' ), 'text' => $text, 'link' => trim( (string) ( $instance[ "link_$i" ] ?? '' ) ) );
			}
		}
		if ( $logo === '' && ! $rows ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $logo !== '' ) {
			printf( '<a class="footer-brand footer-brand--text" href="%s">%s</a>', esc_url( home_url( '/' ) ), esc_html( $logo ) );
		}
		if ( $rows ) {
			echo '<ul class="footer-info">';
			foreach ( $rows as $row ) {
				$icon = array_key_exists( $row['icon'], self::icons() ) && $row['icon'] !== '' ? '<span class="footer-info__icon"><i class="' . esc_attr( $row['icon'] ) . '" aria-hidden="true"></i></span>' : '';
				$text = esc_html( $row['text'] );
				if ( $row['link'] !== '' ) {
					$external = preg_match( '#^https?://#i', $row['link'] ) && ! str_starts_with( $row['link'], home_url() );
					$text     = sprintf( '<a href="%s"%s>%s</a>', esc_url( caia_site_url( $row['link'] ), array( 'http', 'https', 'tel', 'mailto' ) ), $external ? ' target="_blank" rel="noopener noreferrer"' : '', $text );
				}
				echo '<li>' . $icon . '<span>' . $text . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</ul>';
		}
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$this->text_field( $instance, 'company', 'Tên công ty (hiện đầu chân trang)', 'CÔNG TY TNHH ...' );
		echo '<p class="description">Liên kết có thể là trang web, <code>tel:0989125675</code> để gọi, hoặc <code>mailto:ten@email.com</code>.</p>';
		for ( $i = 1; $i <= self::ROWS; $i++ ) {
			echo '<hr><strong>Dòng ' . (int) $i . '</strong>';
			$this->select_field( $instance, "icon_$i", 'Icon', self::icons() );
			$this->text_field( $instance, "text_$i", 'Nội dung' );
			$this->text_field( $instance, "link_$i", 'Liên kết (không bắt buộc)' );
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array( 'company' => sanitize_text_field( $new_instance['company'] ?? '' ) );
		for ( $i = 1; $i <= self::ROWS; $i++ ) {
			$icon = (string) ( $new_instance[ "icon_$i" ] ?? '' );
			$instance[ "icon_$i" ] = array_key_exists( $icon, self::icons() ) ? $icon : '';
			$instance[ "text_$i" ] = sanitize_text_field( $new_instance[ "text_$i" ] ?? '' );
			$instance[ "link_$i" ] = esc_url_raw( $new_instance[ "link_$i" ] ?? '', array( 'http', 'https', 'tel', 'mailto' ) );
		}
		return $instance;
	}
}

class Caia_Footer_Links_Widget extends WP_Widget {
	use Caia_Footer_Widget_Form;

	public function __construct() {
		parent::__construct( 'caia_footer_links', 'Chân trang: Liên kết', array( 'description' => 'Hiển thị một menu thành 2 cột liên kết. Sửa các mục trong Giao diện → Menu.' ) );
	}

	public function widget( $args, $instance ) {
		$menu = wp_get_nav_menu_object( absint( $instance['menu'] ?? 0 ) );
		if ( ! $menu ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_nav_menu( array(
			'menu'            => $menu,
			'container'       => 'nav',
			'container_aria_label' => 'Liên kết chân trang',
			'menu_class'      => 'footer-links',
			'depth'           => 1,
			'fallback_cb'     => false,
		) );
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$options = array( '' => '— Chọn menu —' );
		foreach ( wp_get_nav_menus() as $menu ) {
			$options[ $menu->term_id ] = $menu->name;
		}
		$this->select_field( $instance, 'menu', 'Menu', $options );
	}

	public function update( $new_instance, $old_instance ) {
		return array( 'menu' => absint( $new_instance['menu'] ?? 0 ) );
	}
}

class Caia_Footer_Connect_Widget extends WP_Widget {
	use Caia_Footer_Widget_Form;

	public static function networks() {
		return array( 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'zalo' => 'Zalo' );
	}

	public function __construct() {
		parent::__construct( 'caia_footer_connect', 'Chân trang: Mạng xã hội & nút', array( 'description' => 'Icon mạng xã hội (hiện theo thứ tự trong form, mạng nào không có icon sẽ ẩn) và nút liên hệ.' ) );
	}

	public function widget( $args, $instance ) {
		$items = array();
		foreach ( self::networks() as $key => $name ) {
			$icon = trim( (string) ( $instance[ "{$key}_icon" ] ?? '' ) );
			if ( $icon !== '' ) {
				$items[] = array( 'name' => $name, 'icon' => $icon, 'url' => trim( (string) ( $instance[ "{$key}_url" ] ?? '' ) ) );
			}
		}
		$button_text = trim( (string) ( $instance['button_text'] ?? '' ) );
		$button_link = trim( (string) ( $instance['button_link'] ?? '' ) );
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="footer-connect">';
		if ( $items ) {
			echo '<div class="footer-social">';
			$label = trim( (string) ( $instance['label'] ?? '' ) );
			if ( $label !== '' ) {
				echo '<span class="footer-social__label">' . esc_html( $label ) . '</span>';
			}
			foreach ( $items as $item ) {
				$img = sprintf( '<img src="%s" alt="%s" width="30" height="30" loading="lazy">', esc_url( caia_site_url( $item['icon'] ) ), esc_attr( $item['name'] ) );
				if ( $item['url'] !== '' ) {
					printf( '<a class="footer-social__item" href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( caia_site_url( $item['url'] ) ), $img ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo '<span class="footer-social__item">' . $img . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			echo '</div>';
		}
		if ( $button_text !== '' && $button_link !== '' ) {
			printf( '<a class="footer-contact-button" href="%s"><span>%s</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>', esc_url( caia_site_url( $button_link ) ), esc_html( $button_text ) );
		}
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$this->text_field( $instance, 'label', 'Chữ trước icon', 'Theo dõi' );
		foreach ( self::networks() as $key => $name ) {
			echo '<hr><strong>' . esc_html( $name ) . '</strong>';
			$this->text_field( $instance, "{$key}_url", 'Liên kết trang' );
			$this->text_field( $instance, "{$key}_icon", 'Đường dẫn ảnh icon (để trống để ẩn)' );
		}
		echo '<hr>';
		$this->text_field( $instance, 'button_text', 'Chữ trên nút', 'Liên hệ' );
		$this->text_field( $instance, 'button_link', 'Liên kết nút', '/lien-he' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array(
			'label'       => sanitize_text_field( $new_instance['label'] ?? '' ),
			'button_text' => sanitize_text_field( $new_instance['button_text'] ?? '' ),
			'button_link' => esc_url_raw( $new_instance['button_link'] ?? '' ),
		);
		foreach ( array_keys( self::networks() ) as $key ) {
			$instance[ "{$key}_url" ]  = esc_url_raw( $new_instance[ "{$key}_url" ] ?? '' );
			$instance[ "{$key}_icon" ] = esc_url_raw( $new_instance[ "{$key}_icon" ] ?? '' );
		}
		return $instance;
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'Caia_Footer_Info_Widget' );
	register_widget( 'Caia_Footer_Links_Widget' );
	register_widget( 'Caia_Footer_Connect_Widget' );
} );
