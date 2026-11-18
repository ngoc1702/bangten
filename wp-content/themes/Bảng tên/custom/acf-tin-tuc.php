<?php
/** Banner and ordered category selection for the news page. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_action( 'acf/init', 'caia_register_news_page_fields' );
function caia_register_news_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) { return; }
	acf_add_local_field_group( array(
		'key' => 'group_caia_news_page', 'title' => 'Nội dung trang Tin tức',
		'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-blog.php' ) ) ),
		'fields' => array(
			array( 'key' => 'field_caia_news_page_tab_banner', 'label' => '1. Banner', 'type' => 'tab' ),
			array( 'key' => 'field_caia_news_page_title', 'name' => 'news_page_title', 'label' => 'Tiêu đề hiển thị', 'type' => 'text', 'default_value' => 'Tin tức' ),
			array( 'key' => 'field_caia_news_page_image', 'name' => 'news_page_image', 'label' => 'Ảnh banner', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Tải ảnh để dùng làm nền banner. Để trống sẽ hiển thị tiêu đề trên nền trắng.' ),
			array( 'key' => 'field_caia_news_page_tab_categories', 'label' => '2. Danh mục bài viết', 'type' => 'tab' ),
			array(
				'key' => 'field_caia_news_page_categories', 'name' => 'news_page_categories', 'label' => 'Danh mục hiển thị', 'type' => 'repeater', 'layout' => 'table',
				'button_label' => 'Thêm danh mục', 'collapsed' => 'field_caia_news_page_category',
				'instructions' => 'Chọn danh mục và kéo thả để sắp xếp. Danh mục đầu tiên được chọn mặc định. Bài viết tự cập nhật theo danh mục; không cần chọn từng bài.',
				'sub_fields' => array( array( 'key' => 'field_caia_news_page_category', 'name' => 'category', 'label' => 'Danh mục', 'type' => 'taxonomy', 'taxonomy' => 'category', 'field_type' => 'select', 'return_format' => 'id', 'required' => 1, 'add_term' => 1, 'save_terms' => 0, 'load_terms' => 0 ) ),
			),
		),
	) );
}
