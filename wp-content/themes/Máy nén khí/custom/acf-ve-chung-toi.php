<?php
/**
 * CMS fields for the "Về chúng tôi" page template.
 * The text and images in the middle of the page come from the normal page editor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'caia_register_about_page_fields' );
function caia_register_about_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_caia_about_page',
			'title'    => 'Nội dung trang Về chúng tôi',
			'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-ve-chung-toi.php' ) ) ),
			'fields'   => array(
				array( 'key' => 'field_caia_about_tab_banner', 'label' => '1. Banner', 'type' => 'tab', 'placement' => 'top' ),
				array(
					'key' => 'field_caia_about_banner_title', 'name' => 'about_banner_title', 'label' => 'Tiêu đề banner',
					'type' => 'text', 'instructions' => 'Để trống để dùng tiêu đề của trang.',
				),
				array(
					'key' => 'field_caia_about_banner_image', 'name' => 'about_banner_image', 'label' => 'Ảnh banner',
					'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium',
					'instructions' => 'Ảnh ngang khoảng 1920 × 320px. Để trống sẽ dùng nền xanh chủ đạo.',
				),
				array( 'key' => 'field_caia_about_tab_content', 'label' => '2. Nội dung giới thiệu', 'type' => 'tab', 'placement' => 'top' ),
				array(
					'key' => 'field_caia_about_content_note', 'label' => 'Soạn nội dung', 'type' => 'message', 'new_lines' => 'wpautop', 'esc_html' => 0,
					'message' => 'Nội dung giữa trang (chữ, ảnh, danh sách, bảng…) được soạn ngay ở khung soạn thảo <strong>Nội dung</strong> của trang, phía trên các tab này. Có thể chèn ảnh bình thường.',
				),
				array( 'key' => 'field_caia_about_tab_values', 'label' => '3. Điểm nổi bật', 'type' => 'tab', 'placement' => 'top' ),
				array(
					'key' => 'field_caia_about_values_enabled', 'name' => 'about_values_enabled', 'label' => 'Hiển thị phần điểm nổi bật',
					'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
				),
				array(
					'key' => 'field_caia_about_values_image', 'name' => 'about_values_image', 'label' => 'Ảnh nền',
					'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium',
					'instructions' => 'Tùy chọn. Ảnh được phủ lớp xanh chủ đạo để chữ trắng luôn dễ đọc. Để trống để dùng nền xanh chủ đạo.',
				),
				array(
					'key' => 'field_caia_about_values_items', 'name' => 'about_values_items', 'label' => 'Các điểm nổi bật',
					'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Thêm điểm nổi bật', 'max' => 4,
					'collapsed' => 'field_caia_about_value_title',
					'instructions' => 'Kéo thả để đổi thứ tự. Tối đa 4 mục, hiển thị thành các cột đều nhau.',
					'sub_fields' => array(
						array(
							'key' => 'field_caia_about_value_icon_class', 'name' => 'icon_class', 'label' => 'Icon (Font Awesome)',
							'type' => 'text', 'default_value' => 'fa-solid fa-award', 'wrapper' => array( 'width' => 50 ),
							'instructions' => 'Ví dụ: fa-solid fa-award, fa-solid fa-tags, fa-solid fa-headset, fa-solid fa-truck-fast.',
						),
						array(
							'key' => 'field_caia_about_value_icon_image', 'name' => 'icon_image', 'label' => 'Hoặc ảnh icon',
							'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail', 'mime_types' => 'png,svg,webp', 'wrapper' => array( 'width' => 50 ),
							'instructions' => 'Nếu chọn ảnh, ảnh sẽ được dùng thay cho icon bên trái.',
						),
						array(
							'key' => 'field_caia_about_value_title', 'name' => 'title', 'label' => 'Tiêu đề',
							'type' => 'text', 'required' => 1, 'placeholder' => 'Sản phẩm chất lượng',
						),
						array(
							'key' => 'field_caia_about_value_text', 'name' => 'description', 'label' => 'Mô tả',
							'type' => 'textarea', 'rows' => 4,
						),
					),
				),
				array( 'key' => 'field_caia_about_tab_clients', 'label' => '4. Khách hàng', 'type' => 'tab', 'placement' => 'top' ),
				array(
					'key' => 'field_caia_about_clients_enabled', 'name' => 'about_clients_enabled', 'label' => 'Hiển thị phần khách hàng',
					'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
				),
				array(
					'key' => 'field_caia_about_clients_title', 'name' => 'about_clients_title', 'label' => 'Tiêu đề phần',
					'type' => 'text', 'default_value' => 'Khách hàng của chúng tôi',
				),
				array(
					'key' => 'field_caia_about_clients_logos', 'name' => 'about_clients_logos', 'label' => 'Logo khách hàng',
					'type' => 'gallery', 'return_format' => 'id', 'preview_size' => 'medium', 'insert' => 'append', 'library' => 'all',
					'mime_types' => 'png,jpg,jpeg,webp,svg',
					'instructions' => 'Tải tất cả logo lên và kéo thả để đổi thứ tự. Mỗi trang hiển thị 10 logo (5 cột × 2 hàng); nhiều hơn 10 logo sẽ có nút và chấm chuyển trang. Nên dùng logo PNG/SVG nền trong suốt, chiều ngang khoảng 300px.',
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
