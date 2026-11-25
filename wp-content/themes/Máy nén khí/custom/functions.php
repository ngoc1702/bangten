<?php

// Bật HTML5
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

// Thêm ACF Trang chủ Configuration
include('acf-trangchu.php');
include('acf-lien-he.php');
include('acf-hoi-dap.php');
include('acf-tin-tuc.php');
include('acf-san-pham.php');
include('product-page.php');
include('product-category-page.php');
include('product-catalog.php');

/**
 * Site scripts: shared behaviour everywhere, slick only where the homepage sliders live.
 * Both load in the footer so they never block rendering.
 */
add_action( 'wp_enqueue_scripts', 'caia_enqueue_site_scripts' );
function caia_enqueue_site_scripts() {
	$dir  = get_stylesheet_directory();
	$url  = get_stylesheet_directory_uri();
	$deps = array( 'jquery' );

	if ( is_page_template( 'page-trangchu.php' ) ) {
		wp_enqueue_script( 'caia-slick', $url . '/custom/js/slick.js', array( 'jquery' ), filemtime( $dir . '/custom/js/slick.js' ), true );
		$deps[] = 'caia-slick';
	}

	wp_enqueue_script( 'caia-site', $url . '/assets/js/site.js', $deps, filemtime( $dir . '/assets/js/site.js' ), true );
}

/** Re-register the child stylesheet with a file-time version so edits bust the cache. */
add_action( 'wp_enqueue_scripts', 'caia_enqueue_child_style', 100 );
function caia_enqueue_child_style() {
	$path = get_stylesheet_directory() . '/style.css';
	wp_dequeue_style( 'caia' );
	wp_deregister_style( 'caia' );
	wp_enqueue_style( 'caia', get_stylesheet_uri(), array(), filemtime( $path ) );
}

/**
 * The site has no cart or checkout: drop WooCommerce's cart scripts and block styles,
 * and keep its stylesheets only on WooCommerce pages. Hover zoom is disabled, so skip its script too.
 */
// Runs at print time because WooCommerce enqueues some of these after wp_enqueue_scripts.
add_action( 'wp_print_styles', 'caia_trim_woocommerce_assets', 100 );
add_action( 'wp_print_scripts', 'caia_trim_woocommerce_assets', 100 );
add_action( 'wp_print_footer_scripts', 'caia_trim_woocommerce_assets', 1 );
function caia_trim_woocommerce_assets() {
	if ( is_admin() || ! function_exists( 'is_woocommerce' ) ) {
		return;
	}

	// Handles cover both older names and the wc- prefixed ones used since WooCommerce 9.
	$scripts = array( 'wc-add-to-cart', 'wc-cart-fragments', 'zoom', 'wc-zoom' );
	$styles  = array( 'wc-blocks-style' );

	if ( ! ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		$scripts = array_merge( $scripts, array( 'woocommerce', 'jquery-blockui', 'wc-jquery-blockui', 'js-cookie', 'wc-js-cookie', 'sourcebuster-js', 'wc-order-attribution' ) );
		$styles  = array_merge( $styles, array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-inline' ) );
	}

	foreach ( $scripts as $handle ) {
		wp_dequeue_script( $handle );
	}
	foreach ( $styles as $handle ) {
		wp_dequeue_style( $handle );
	}
}

add_action( 'wp_enqueue_scripts', 'caia_enqueue_home_page_style', 120 );
function caia_enqueue_home_page_style() {
	if ( ! is_page_template( 'page-trangchu.php' ) && ! is_page_template( 'page-blog.php' ) && ! is_search() && ! caia_is_product_catalog() && ! ( function_exists( 'is_product' ) && is_product() ) ) {
		return;
	}
	$path = get_stylesheet_directory() . '/assets/css/home.css';
	wp_enqueue_style( 'caia-home', get_stylesheet_directory_uri() . '/assets/css/home.css', array( 'caia' ), filemtime( $path ) );
}

// Load shared phone layouts after each page's component styles.
add_action( 'wp_enqueue_scripts', 'caia_enqueue_mobile_styles', 200 );
function caia_enqueue_mobile_styles() {
	$path = get_stylesheet_directory() . '/assets/css/mobile.css';
	wp_enqueue_style( 'caia-mobile', get_stylesheet_directory_uri() . '/assets/css/mobile.css', array( 'caia' ), filemtime( $path ) );
}

/** Uploaded banner shared by the FAQ, news and contact templates. */
function caia_render_page_banner( $title, $image_id = 0 ) {
	?>
	<header class="page-banner">
		<?php if ( $image_id ) : ?><?php echo wp_get_attachment_image( $image_id, 'full', false, array( 'class' => 'page-banner__image', 'alt' => '', 'aria-hidden' => 'true', 'loading' => 'eager' ) ); ?><?php endif; ?>
		<div class="page-banner__inner">
			<h1><?php echo esc_html( $title ); ?></h1>
			<nav class="page-banner__breadcrumb" aria-label="Đường dẫn"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a><span aria-hidden="true">»</span><span aria-current="page"><?php echo esc_html( $title ); ?></span></nav>
		</div>
	</header>
	<?php
}

add_action( 'wp_enqueue_scripts', 'caia_enqueue_scroll_reveal_assets', 140 );
/**
 * Load subtle scroll reveal effects across the public website.
 */
function caia_enqueue_scroll_reveal_assets() {
	$reveal_css_path = get_stylesheet_directory() . '/assets/css/scroll-reveal.css';
	$reveal_js_path  = get_stylesheet_directory() . '/assets/js/scroll-reveal.js';

	if ( file_exists( $reveal_css_path ) ) {
		wp_enqueue_style(
			'caia-scroll-reveal-style',
			get_stylesheet_directory_uri() . '/assets/css/scroll-reveal.css',
			array( 'caia' ),
			filemtime( $reveal_css_path )
		);
	}

	if ( file_exists( $reveal_js_path ) ) {
		wp_enqueue_script(
			'caia-scroll-reveal',
			get_stylesheet_directory_uri() . '/assets/js/scroll-reveal.js',
			array(),
			filemtime( $reveal_js_path ),
			true
		);
	}
}



//Cho phép upload ảnh định dạng Svg
add_filter('upload_mimes', 'caia_mime_types', 1, 1);
function caia_mime_types($mime_types){  
	$mime_types['svg'] = 'image/svg+xml';
	$mime_types['json'] = 'application/json';
	$mime_types['webp'] = 'image/webp';
	return $mime_types;
}

add_filter( 'wp_check_filetype_and_ext', 'caia_disable_real_mime_check', 10, 4 );
function caia_disable_real_mime_check( $data, $file, $filename, $mimes ) {
	$wp_filetype = wp_check_filetype( $filename, $mimes );
	$ext = $wp_filetype['ext'];
	$type = $wp_filetype['type'];
	$proper_filename = $data['proper_filename'];
	return compact( 'ext', 'type', 'proper_filename' );
}

// Xóa các kích thước mặc định trong Wordpress
add_filter( 'intermediate_image_sizes_advanced', 'prefix_remove_default_images' );
function prefix_remove_default_images( $sizes ){
	unset( $sizes['medium']);
	unset( $sizes['large']);
	unset( $sizes['medium_large']);
	unset( $sizes['1536x1536']);
	unset( $sizes['2048x2048']);
	return $sizes;
}

// Ẩn hiển thị các kích thước ảnh mặc định
add_filter( 'intermediate_image_sizes', function( $sizes ){
    return array_filter( $sizes, function( $val ){
        return 'medium' !== $val && 'medium_large' !== $val && 'large' !== $val && '1536x1536' !== $val && '2048x2048' !== $val;
    });
});

// Đặt kích thước mặc định cho website
update_option( 'thumbnail_size_w', 750 );
update_option( 'thumbnail_size_h', 395 );

// Thêm kích thước ảnh sản phẩm
add_image_size('product-image',640,640,true);
add_image_size('product-avatar',300,300,true);

add_filter( 'image_size_names_choose', 'caia_custom_sizes' );
function caia_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
		'product-image' => __( 'Kích thước 640x640' ),
    ));
}

// Bỏ toàn bộ thẻ H4 khỏi tiêu đề widget
add_filter( 'genesis_register_widget_area_defaults', 'caia_change_all_widget_titles' );
function caia_change_all_widget_titles( $defaults ) { 
	$defaults['before_title'] = '<div class="widget-title widgettitle">';
	$defaults['after_title'] = "</div>";
	return $defaults;
}

// Thêm thẻ đóng mở cho tiêu đề của widget
add_filter( 'widget_title', 'caia_add_html_widget_title' );
function caia_add_html_widget_title( $title ) {	
	$title = str_replace( '[span]', '<span>', $title );
	$title = str_replace( '[/span]', '</span>', $title );	
	return $title;
}


/** Montserrat is the only web font in use; one variable request covers weights 300–800. */
add_action( 'wp_head', 'caia_add_font_website', 1 );
function caia_add_font_website() {
	?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300..800;1,400..600&display=swap" rel="stylesheet">
	<?php
}

genesis_register_sidebar( 
	array(
		'id'			=> 'header-menusub',
		'name'			=> 'Toàn bộ - Thanh trên header',
	)
);

add_action( 'widgets_init', 'caia_register_header_main_widget_area' );
function caia_register_header_main_widget_area() {
	genesis_register_widget_area(
		array(
			'id'          => 'header-main',                               
			'name'        => __( 'Header Main', 'genesis' ),
			'description' => __( 'The main header menu widget area.', 'genesis' ),
		)
	);
}

add_action( 'widgets_init', 'caia_order_header_widget_areas', 100 );
function caia_order_header_widget_areas() {
	if ( ! is_admin() ) {
		return;
	}

	global $wp_registered_sidebars;

	$ordered_sidebars = array();

	foreach ( array( 'header-menusub', 'header-right', 'header-main' ) as $sidebar_id ) {
		if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
			$ordered_sidebars[ $sidebar_id ] = $wp_registered_sidebars[ $sidebar_id ];
		}
	}

	$wp_registered_sidebars = $ordered_sidebars + $wp_registered_sidebars;
}

function caia_has_header_menusub() {
	return is_active_sidebar( 'header-menusub' );
}

add_filter( 'body_class', function( $classes ) {
	$classes[] = 'caia-header-layout';
	if ( is_active_sidebar( 'header-main' ) ) {
		$classes[] = 'has-header-main';
	}

	if ( caia_has_header_menusub() ) {
		$classes[] = 'has-header-menusub';
	}

	return $classes;
});

add_action( 'genesis_before_header', function() {
    if ( is_active_sidebar( 'header-menusub' ) ) {
        echo '<div class="header-menusub-widget-area"><div class="wrap">';
        dynamic_sidebar( 'header-menusub' );
        echo '</div></div>';
    }
}, 1 ); 

// Use the saved logo, search and hotline widgets as the middle header row.
remove_action( 'genesis_header', 'genesis_do_header' );
add_action( 'genesis_header', 'caia_render_three_row_header' );
function caia_render_three_row_header() {
	echo '<div class="header-middle"><div class="header-middle-widgets">';
	if ( ! dynamic_sidebar( 'header-right' ) ) {
		do_action( 'genesis_site_title' );
		get_search_form();
	}
	echo '</div></div>';

	if ( is_active_sidebar( 'header-main' ) ) {
		echo '<nav class="header-main-widget-area" aria-label="Danh mục sản phẩm">';
		dynamic_sidebar( 'header-main' );
		echo '</nav>';
	}
}

// Make the hotline saved in the Text widget callable without changing its content.
add_filter( 'widget_text_content', 'caia_header_hotline_link', 20, 3 );
function caia_header_hotline_link( $text, $instance, $widget ) {
	$sidebars = wp_get_sidebars_widgets();
	if ( ! in_array( $widget->id, $sidebars['header-right'] ?? array(), true ) ) {
		return $text;
	}

	return preg_replace_callback(
		'~(<p\b[^>]*>)\s*(\+?\d[\d\s.()-]{7,}\d)\s*(</p>)~u',
		function( $matches ) {
			$phone = preg_replace( '/[^\d+]/', '', $matches[2] );
			return $matches[1] . '<a href="' . esc_url( 'tel:' . $phone ) . '">' . esc_html( trim( $matches[2] ) ) . '</a>' . $matches[3];
		},
		$text
	);
}

add_action( 'wp_enqueue_scripts', 'caia_enqueue_header_assets', 160 );
function caia_enqueue_header_assets() {
	foreach ( array( 'css/header.css', 'js/header.js' ) as $asset ) {
		$path = get_stylesheet_directory() . '/assets/' . $asset;
		$url  = get_stylesheet_directory_uri() . '/assets/' . $asset;
		if ( substr( $asset, -4 ) === '.css' ) {
			wp_enqueue_style( 'caia-header', $url, array( 'caia' ), filemtime( $path ) );
		} else {
			wp_enqueue_script( 'caia-header', $url, array( 'jquery' ), filemtime( $path ), true );
		}
	}
}

genesis_register_sidebar( 
	array(
		'id'			=> 'nhantuvan',
		'name'			=> 'Toàn bộ - Nhận tư vấn',
		'description' => 'Thêm widget Văn bản (tiêu đề và mô tả) trước, Fluent Forms Widget sau. Form gồm Họ tên và Số điện thoại trong một container 2 cột, Địa chỉ ở hàng tiếp theo và nút Nhận tư vấn.',
	)
);


genesis_register_sidebar( 
	array(
		'id'			=> 'content-bfooter',
		'description'	=> 'Dùng 3 widget Văn bản theo thứ tự: (1) Logo và thông tin Bửu Liên, (2) Liên kết, (3) Mạng xã hội và nút liên hệ. Có thể chèn ảnh để thay icon trong từng widget.',
		'name'			=> 'Toàn bộ - Nội dung trước chân trang',
	)
);

genesis_register_sidebar( 
	array(
		'id'			=> 'content-footer',
		'description' => 'Liên kết cuối trang và thông tin bản quyền. Sửa trực tiếp trong widget Văn bản.',
		'name'			=> 'Toàn bộ - Nội dung chân trang',
	)
);

genesis_register_sidebar( 
	array(
		'id'			=> 'content-fix',
		'name'			=> 'Toàn bộ - Nội dung cố định',
	)
);

add_action('genesis_before_footer','caia_add_contactus', 7);
function caia_add_contactus(){
	if( is_active_sidebar( 'nhantuvan' ) ){
		echo '<div class="nhantuvan section"><div class="wrap">';
			dynamic_sidebar( 'nhantuvan' );
		echo '</div></div>';
	}
}


remove_action('genesis_footer','genesis_do_footer');
add_action('genesis_footer','caia_add_content_footer');
function caia_add_content_footer(){
	if( is_active_sidebar( 'content-footer' ) ){
		dynamic_sidebar( 'content-footer' );
	}
}

add_action('genesis_before_footer','caia_add_content_after_footer2');
// Emphasize contact labels in footer Text widgets without changing their saved content.
add_filter( 'widget_text_content', 'caia_footer_contact_labels', 20, 3 );
function caia_footer_contact_labels( $text, $instance, $widget ) {
	$sidebars = wp_get_sidebars_widgets();
	if ( ! in_array( $widget->id, $sidebars['content-bfooter'] ?? array(), true ) ) {
		return $text;
	}

	return preg_replace(
		'/(<p\b[^>]*>\s*)(Trụ sở:|Email:|Hotline:|Các trạm bảo hành[^<:]*:)/u',
		'$1<strong>$2</strong>',
		$text
	);
}

function caia_add_content_after_footer2(){
	if( is_active_sidebar( 'content-bfooter' ) ){
		echo '<div class="before_footer section"><div class="wrap"><div class="wrap-section">';
			dynamic_sidebar( 'content-bfooter' );
		echo '</div></div></div>';
	}
}

add_action('genesis_after_footer','caia_add_content_fix');
function caia_add_content_fix(){
	if( is_active_sidebar( 'content-fix' ) ){
		echo '<div class="content-fix">';
			dynamic_sidebar( 'Toàn bộ - Nội dung cố định' );		
		echo '</div>';
	}
}


add_action( 'genesis_before', function() {
	// Xóa sidebar mặc định của Genesis trước khi render
	remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );
});

add_action( 'genesis_sidebar', function() {

	if ( is_singular( 'post' ) ) {
		genesis_widget_area(
			'sidebar',
			[ 'before' => '<aside class="sidebar primary-sidebar widget-area">', 'after' => '</aside>' ]
		);

	// Các trang khác, trừ sản phẩm
	} elseif ( ! is_singular( 'product' ) ) {
		genesis_widget_area(
			'sidebar',
			[ 'before' => '<aside class="sidebar primary-sidebar widget-area">', 'after' => '</aside>' ]
		);
	}
});

// Chỉnh hiển thị nút Next Page và Previous Page trong phân trang
add_filter ( 'genesis_next_link_text' , 'caia_next_page_link' );
function caia_next_page_link( $text ) {
    return '&#x000BB;';
}

add_filter ( 'genesis_prev_link_text' , 'caia_previous_page_link' );
function caia_previous_page_link( $text ) {
    return '&#x000AB;';
}

// Hiển thị breadcrumb toàn website ngay dưới header. Việc bật/tắt từng loại
// trang vẫn được điều khiển tại Genesis > Theme Settings > Breadcrumbs.
function caia_move_genesis_breadcrumbs_below_header() {
	if ( ! function_exists( 'genesis_do_breadcrumbs' ) ) {
		return;
	}

	remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
	add_action( 'genesis_after_header', 'genesis_do_breadcrumbs', 5 );
}
add_action( 'wp', 'caia_move_genesis_breadcrumbs_below_header', 20 );

function caia_breadcrumb_body_class( $classes ) {
	if (
		function_exists( 'genesis_breadcrumbs_hidden_on_current_page' ) &&
		function_exists( 'genesis_breadcrumbs_disabled_on_current_page' ) &&
		! genesis_breadcrumbs_hidden_on_current_page() &&
		! genesis_breadcrumbs_disabled_on_current_page()
	) {
		$classes[] = 'caia-breadcrumb-visible';
	}

	return $classes;
}
add_filter( 'body_class', 'caia_breadcrumb_body_class' );

/** Remove the default "You are here:" text from Genesis breadcrumbs. */
function caia_breadcrumb_args( $args ) {
	$args['home'] = 'Trang chủ';

	if ( isset( $args['labels'] ) && is_array( $args['labels'] ) ) {
		$args['labels']['prefix'] = '';
	}

	return $args;
}
add_filter( 'genesis_breadcrumb_args', 'caia_breadcrumb_args' );

// Tùy biến breadcrumbs trong Genesis
// ✅ Shortcode hiển thị breadcrumb tùy chỉnh
function my_custom_breadcrumb_shortcode() {
    // Thiết lập tham số breadcrumb
    $args = array(
        'home' => '<span class="home">Trang chủ</span>',
        'sep'  => '<span aria-label="breadcrumb separator" class="label"> » </span>',
        'list_sep' => ', ',
        'prefix' => '<div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList"><div class="wrap"><div class="thanhdieuhuong">',
        'suffix' => '</div></div></div>',
        'heirarchial_attachments' => true,
        'heirarchial_categories'  => true,
        'labels' => array(
            'prefix' => '',
            'author' => '',
            'category' => '',
            'tag' => '',
            'date' => '',
            'search' => '',
            'tax' => '',
            'post_type' => '',
            '404' => '',
        ),
    );

    // Nếu bạn dùng Genesis
    if ( function_exists( 'genesis_breadcrumb' ) ) {
        ob_start();
        genesis_breadcrumb( $args );
        return ob_get_clean();
    }

    // Nếu không có Genesis, bạn có thể thay bằng breadcrumb tĩnh hoặc plugin khác
    return '<div class="breadcrumb"><a href="/">Trang chủ</a> » ...</div>';
}
add_shortcode( 'breadcrumb', 'my_custom_breadcrumb_shortcode' );



// Thiết kế lại form comment
add_filter( 'comment_form_defaults', 'rayno_comment_form_args' );
function rayno_comment_form_args($defaults) {
	global $user_identity, $id;
	$commenter = wp_get_current_commenter();
	$req       = get_option( 'require_name_email' );
	$aria_req  = ( $req ? ' aria-required="true"' : '' );
	$author = '<div class="popup-comment"><div class="box-comment"><span class="close-popup-comment">✕</span><p>Bạn vui lòng điền thêm thông tin!</p><p class="comment-form-author">' .
	          '<input id="author" name="author" type="text" class="author" placeholder="Họ và tên" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" tabindex="1"' . $aria_req . '/>' .
	          '</p>';
	$email = '<p class="comment-form-email">' .
	         '<input id="email" name="email" type="text" class="email" placeholder="Email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" tabindex="2"' . $aria_req . ' />' .
	         '</p>';
	$comment_field = '<p class="comment-form-comment">' .
	                 '<textarea id="comment" name="comment" cols="45" rows="8" class="form" tabindex="4" aria-required="true" placeholder="Nội dung bình luận"></textarea>' .
	                 '</p>';
	$args = array(
		'fields' => array(
		'author' => $author,
		'email'  => $email,
		),
		'comment_field'        => $comment_field,
		'title_reply'          => __( 'Bình luận của bạn', 'genesis' ),
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
	);
	$args = wp_parse_args( $args, $defaults );
	return apply_filters( 'raynoblog_comment_form_args', $args, $user_identity, $id, $commenter, $req, $aria_req );
}

// Sửa nút comment
add_filter( 'comment_form_defaults', 'caia_change_submit_comment' );
function caia_change_submit_comment( $defaults ){
    $defaults['label_submit'] = 'Gửi đi';
    return $defaults;
}

// Sửa chữ comment
add_filter( 'genesis_title_comments', 'caia_title_comments' );
function caia_title_comments() {
	echo '';
}

// Thay đổi chữ says
add_filter('comment_author_says_text', 'caia_change_says');
function caia_change_says($args){
	$args = 'đã bình luận';
	return $args;
}

// Sửa thẻ h4 ý kiến của bạn
add_filter( 'comment_form_defaults', 'caia_custom_reply_title' );
function caia_custom_reply_title( $defaults ){
	$defaults['title_reply_before'] = '<p id="reply-title" class="comment-reply-title">';
	$defaults['title_reply_after'] = '</p>';
	return $defaults;
}

// Thêm nút comment
add_action( 'comment_form_logged_in_after', 'additional_fields',1 );
add_action( 'comment_form_after_fields', 'additional_fields',1 );
function additional_fields (){
	if(!is_user_logged_in()){
		echo '<p class="comment-form-phone"><input id="author" name="phone" type="text" size="30" tabindex="4" placeholder="Số điện thoại"/></p>
		<p><input name="actionsubmit" type="hidden" value="1" /><input id="submit-commnent" name="submit-commnent" type="submit" value="Hoàn tất" /></p></div></div>';
	}
}

// Lưu nội dung comment 
add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {
	if ( ! empty( $_POST['phone'] ) ) {
		add_comment_meta( $comment_id, 'phone', wp_filter_nohtml_kses( wp_unslash( $_POST['phone'] ) ) );
	}
}

// Add the filter to check if the comment meta data has been filled or not
add_filter( 'preprocess_comment', 'verify_comment_meta_data', 1, 1 );
function verify_comment_meta_data( $commentdata ){
	$commentdata['phone'] = ( ! empty ( $_POST['phone'] ) ) ? sanitize_text_field( $_POST['phone'] ) : false;
	if ( ! $commentdata['phone'] && ! is_admin() ){
		wp_die( __( '<p>Lỗi: Vui lòng điền số điện thoại</p><a href="javascript:history.back()">« Quay lại</a>' ) );
	}	
    return $commentdata;
}

// Thêm nút trong trang quản trị 
add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box() {
    add_meta_box( 'title', __( 'Thông tin số điện thoại khách hàng' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}
 
function extend_comment_meta_box ( $comment ){
    $phone = get_comment_meta( $comment->comment_ID, 'phone', true );
    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    ?><p><label for="phone"><?php _e( 'Số điện thoại' ); ?></label><input type="text" name="phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat" /></p><?php
}

// Cập nhật khi thay đổi 
add_action( 'edit_comment', 'extend_comment_edit_metafields' );
function extend_comment_edit_metafields( $comment_id ){
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;
	if ( ( isset( $_POST['phone'] ) ) && ( $_POST['phone'] != '') ) : 
	$phone = wp_filter_nohtml_kses($_POST['phone']);
	update_comment_meta( $comment_id, 'phone', $phone );
	else :
	delete_comment_meta( $comment_id, 'phone');
	endif;
}

//Thêm cột số điện thoại trong admin
add_filter( 'manage_edit-comments_columns', 'myplugin_comment_columns' );
function myplugin_comment_columns( $columns ){
	return array_merge( $columns, array(
		'phone' => __( 'Số điện thoại' ),
	) );
}

add_filter( 'manage_comments_custom_column', 'myplugin_comment_column', 10, 2 );
function myplugin_comment_column( $column, $comment_ID ){
	switch ( $column ) {
		case 'phone':
			if ( $meta = get_comment_meta( $comment_ID, $column , true ) ) {
				echo $meta;
			} else {
				echo '-';
			}
		break;
	}
}

add_action( 'admin_head', 'my_column_width' );
function my_column_width() {
	echo '<style>th#phone{width:15%}</style>';
}

add_action( 'wp_enqueue_scripts', 'add_fontawesome_to_theme' );
function add_fontawesome_to_theme() {
	wp_enqueue_style( 'font-awesome', CHILD_URL . '/assets/fontawesome/css/all.min.css', array(), '6.5.0' );
}
