<?php
/**
 * ACF fields for the contact page template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'caia_register_contact_page_acf_fields' );

function caia_register_contact_page_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_caia_contact_page',
			'title' => 'Nội dung trang Liên hệ',
			'fields' => array(
				array(
					'key'   => 'field_caia_contact_banner_tab',
					'label' => 'Banner',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_caia_contact_banner_title',
					'label'         => 'Tiêu đề banner',
					'name'          => 'contact_banner_title',
					'type'          => 'text',
					'default_value' => 'Liên hệ',
				),
				array(
					'key'           => 'field_caia_contact_banner_image',
					'label'         => 'Ảnh banner',
					'name'          => 'contact_banner_image',
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'instructions'  => 'Chọn ảnh nền banner giống trang Tin tức. Để trống sẽ hiển thị tiêu đề trên nền trắng.',
				),
				array(
					'key'   => 'field_caia_contact_intro_tab',
					'label' => 'Thông tin liên hệ',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_caia_contact_title',
					'label'         => 'Tiêu đề phần thông tin',
					'name'          => 'contact_title',
					'type'          => 'text',
					'default_value' => 'Công ty TNHH Thọ Lập Hưng Yên',
				),
				array(
					'key'          => 'field_caia_contact_description',
					'label'        => 'Mô tả ngắn',
					'name'         => 'contact_description',
					'type'         => 'textarea',
					'rows'         => 3,
				),
				array(
					'key'          => 'field_caia_contact_info_items',
					'label'        => 'Các trường thông tin liên hệ',
					'name'         => 'contact_info_items',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Thêm thông tin',
					'sub_fields'   => array(
						array(
							'key'           => 'field_caia_contact_info_icon',
							'label'         => 'Class icon',
							'name'          => 'icon_class',
							'type'          => 'text',
							'default_value' => 'fa-solid fa-location-dot',
							'instructions'  => 'Ví dụ: fa-solid fa-phone, fa-solid fa-envelope, fa-solid fa-location-dot.',
						),
						array(
							'key'      => 'field_caia_contact_info_label',
							'label'    => 'Tiêu đề',
							'name'     => 'label',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'      => 'field_caia_contact_info_content',
							'label'    => 'Nội dung',
							'name'     => 'content',
							'type'     => 'textarea',
							'rows'     => 2,
							'required' => 1,
						),
						array(
							'key'          => 'field_caia_contact_info_link',
							'label'        => 'Liên kết (tùy chọn)',
							'name'         => 'link',
							'type'         => 'text',
							'instructions' => 'Nhận tel:0776666722, mailto:email@example.com hoặc đường dẫn Google Maps.',
						),
					),
				),
				array(
					'key'   => 'field_caia_contact_map_tab',
					'label' => 'Bản đồ',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_caia_contact_map_title',
					'label'         => 'Tiêu đề bản đồ',
					'name'          => 'contact_map_title',
					'type'          => 'text',
					'default_value' => 'Vị trí của chúng tôi',
				),
				array(
					'key'          => 'field_caia_contact_map_url',
					'label'        => 'Mã nhúng Google Maps',
					'name'         => 'contact_map_url',
					'type'         => 'textarea',
					'rows'         => 5,
					'instructions' => 'Dán toàn bộ mã <iframe ...></iframe> từ Google Maps hoặc chỉ riêng giá trị src bắt đầu bằng https://www.google.com/maps/embed?...',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-lien-he.php',
					),
				),
			),
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => array( 'the_content' ),
			'active'                => true,
		)
	);
}
