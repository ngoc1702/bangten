<?php
/** Editable question groups for the FAQ page template. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'acf/init', 'caia_register_faq_fields' );
function caia_register_faq_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) { return; }
	acf_add_local_field_group( array(
		'key' => 'group_caia_faq', 'title' => 'Nội dung Hỏi đáp',
		'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-hoi-dap.php' ) ) ),
		'fields' => array(
			array( 'key' => 'field_caia_faq_tab_intro', 'label' => '1. Tiêu đề và giới thiệu', 'type' => 'tab' ),
			array( 'key' => 'field_caia_faq_title', 'name' => 'faq_title', 'label' => 'Tiêu đề hiển thị', 'type' => 'text', 'default_value' => 'Câu hỏi thường gặp' ),
			array( 'key' => 'field_caia_faq_image', 'name' => 'faq_hero_image', 'label' => 'Ảnh banner', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Tải ảnh để dùng làm nền banner. Để trống sẽ hiển thị tiêu đề trên nền trắng.' ),
			array( 'key' => 'field_caia_faq_intro', 'name' => 'faq_intro', 'label' => 'Giới thiệu', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
			array( 'key' => 'field_caia_faq_tab_questions', 'label' => '2. Nhóm câu hỏi', 'type' => 'tab' ),
			array( 'key' => 'field_caia_faq_open_first', 'name' => 'faq_open_first', 'label' => 'Mở sẵn câu hỏi đầu tiên', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1 ),
			array(
				'key' => 'field_caia_faq_groups', 'name' => 'faq_groups', 'label' => 'Các nhóm câu hỏi', 'type' => 'repeater', 'layout' => 'block',
				'button_label' => 'Thêm nhóm câu hỏi', 'collapsed' => 'field_caia_faq_group_title',
				'instructions' => 'Kéo thả để đổi thứ tự nhóm và câu hỏi. Nhóm chưa có câu hỏi đầy đủ sẽ tự ẩn.',
				'sub_fields' => array(
					array( 'key' => 'field_caia_faq_group_title', 'name' => 'title', 'label' => 'Tên nhóm', 'type' => 'text', 'required' => 1 ),
					array(
						'key' => 'field_caia_faq_items', 'name' => 'items', 'label' => 'Câu hỏi và trả lời', 'type' => 'repeater', 'layout' => 'block',
						'button_label' => 'Thêm câu hỏi', 'collapsed' => 'field_caia_faq_question',
						'sub_fields' => array(
							array( 'key' => 'field_caia_faq_question', 'name' => 'question', 'label' => 'Câu hỏi', 'type' => 'text', 'required' => 1 ),
							array( 'key' => 'field_caia_faq_answer', 'name' => 'answer', 'label' => 'Trả lời', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0, 'required' => 1 ),
						),
					),
				),
			),
		),
	) );
}
