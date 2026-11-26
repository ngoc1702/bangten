<?php
/**
 * Homepage CMS and section output, ordered as displayed on the page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'caia_register_home_page_fields' );
function caia_register_home_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_caia_home_page',
			'title' => 'Nội dung trang chủ',
			'fields' => array(
				array(
					'key' => 'field_caia_home_tab_banner', 'label' => '1. Banner', 'type' => 'tab', 'placement' => 'top',
				),
				array(
					'key' => 'field_caia_home_banner_images', 'name' => 'home_banner_images',
					'label' => 'Ảnh banner', 'type' => 'gallery', 'return_format' => 'id',
					'preview_size' => 'medium', 'insert' => 'append', 'library' => 'all',
					'mime_types' => 'jpg,jpeg,png,webp',
					'instructions' => 'Chỉ cần tải ảnh lên và kéo thả để đổi thứ tự. Banner có nút trước/sau, chấm điều hướng và tự chuyển sau 5 giây. Nên dùng ảnh ngang cùng tỷ lệ, ví dụ 1920 × 600px. Để trống để ẩn banner.',
				),
				array(
					'key' => 'field_caia_home_tab_categories', 'label' => '2. Danh mục sản phẩm', 'type' => 'tab', 'placement' => 'top',
				),
				array(
					'key' => 'field_caia_home_categories_enabled', 'name' => 'home_categories_enabled',
					'label' => 'Hiển thị danh mục sản phẩm', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
				),
				array(
					'key' => 'field_caia_home_categories_title', 'name' => 'home_categories_title',
					'label' => 'Tiêu đề section', 'type' => 'text', 'default_value' => 'Danh mục sản phẩm',
				),
				array(
					'key' => 'field_caia_home_categories_items', 'name' => 'home_categories_items',
					'label' => 'Danh mục hiển thị', 'type' => 'taxonomy', 'taxonomy' => 'product_cat',
					'field_type' => 'multi_select', 'return_format' => 'id', 'multiple' => 1,
					'add_term' => 0, 'save_terms' => 0, 'load_terms' => 0, 'allow_null' => 1,
					'instructions' => 'Để trống để tự hiển thị các danh mục cha, trừ Chưa phân loại. Ảnh lấy từ Sản phẩm → Danh mục → Hình thu nhỏ. Có thể chọn riêng các danh mục muốn hiển thị.',
				),
				array(
					'key' => 'field_caia_home_tab_products', 'label' => '3. Sản phẩm theo danh mục', 'type' => 'tab', 'placement' => 'top',
				),
				array(
					'key' => 'field_caia_home_product_sections', 'name' => 'home_product_sections',
					'label' => 'Các danh mục hiển thị', 'type' => 'repeater', 'layout' => 'block',
					'button_label' => 'Thêm danh mục', 'collapsed' => 'field_caia_home_product_category',
					'instructions' => 'Mỗi hàng tạo một section, hiển thị ngay sau Danh mục sản phẩm. Kéo thả để đổi thứ tự. Tab ngoài website tự lấy các danh mục con trực tiếp; nếu không có danh mục con, hiển thị sản phẩm của danh mục đã chọn. Tab và section chưa có sản phẩm sẽ tự ẩn, tự hiện lại khi có sản phẩm.',
					'sub_fields' => array(
						array(
							'key' => 'field_caia_home_product_category', 'name' => 'category',
							'label' => 'Danh mục cha', 'type' => 'taxonomy', 'taxonomy' => 'product_cat',
							'field_type' => 'select', 'return_format' => 'id', 'required' => 1,
							'add_term' => 0, 'save_terms' => 0, 'load_terms' => 0,
						),
						array(
							'key' => 'field_caia_home_product_heading', 'name' => 'heading',
							'label' => 'Tiêu đề section', 'type' => 'text',
							'instructions' => 'Để trống để dùng tên danh mục.',
						),
						array(
							'key' => 'field_caia_home_product_count', 'name' => 'count',
							'label' => 'Số sản phẩm mỗi tab', 'type' => 'number',
							'default_value' => 4, 'min' => 1, 'max' => 24, 'step' => 1,
						),
					),
				),
				array(
					'key' => 'field_caia_home_tab_stats', 'label' => '4. Con số nổi bật', 'type' => 'tab', 'placement' => 'top',
				),
				array(
					'key' => 'field_caia_home_stats_enabled', 'name' => 'home_stats_enabled',
					'label' => 'Hiển thị con số nổi bật', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
				),
				array(
					'key' => 'field_caia_home_stats_title', 'name' => 'home_stats_title',
					'label' => 'Tiêu đề section', 'type' => 'text', 'default_value' => 'Những con số nổi bật',
				),
				array(
					'key' => 'field_caia_home_stats_items', 'name' => 'home_stats_items',
					'label' => 'Các con số', 'type' => 'repeater', 'layout' => 'table',
					'button_label' => 'Thêm con số', 'max' => 6,
					'instructions' => 'Kéo thả để đổi thứ tự. Icon nên là ảnh PNG/SVG nét mảnh, nền trong suốt, khoảng 64 × 64px. Để trống icon để dùng icon mặc định.',
					'sub_fields' => array(
						array( 'key' => 'field_caia_home_stat_icon', 'name' => 'icon', 'label' => 'Icon', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail', 'mime_types' => 'png,svg,webp', 'wrapper' => array( 'width' => 20 ) ),
						array( 'key' => 'field_caia_home_stat_number', 'name' => 'number', 'label' => 'Con số', 'type' => 'text', 'required' => 1, 'placeholder' => '20+', 'wrapper' => array( 'width' => 30 ) ),
						array( 'key' => 'field_caia_home_stat_label', 'name' => 'label', 'label' => 'Mô tả', 'type' => 'text', 'required' => 1, 'placeholder' => 'Năm kinh nghiệm', 'wrapper' => array( 'width' => 50 ) ),
					),
				),
				array(
					'key' => 'field_caia_home_tab_commitment', 'label' => '5. Cam kết chất lượng', 'type' => 'tab', 'placement' => 'top',
				),
				array(
					'key' => 'field_caia_home_commitment_enabled', 'name' => 'home_commitment_enabled',
					'label' => 'Hiển thị section cam kết', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
				),
				array(
					'key' => 'field_caia_home_commitment_subtitle', 'name' => 'home_commitment_subtitle',
					'label' => 'Dòng chữ nhỏ', 'type' => 'text', 'default_value' => 'Công ty TNHH Thọ Lập Hưng Yên',
				),
				array(
					'key' => 'field_caia_home_commitment_title', 'name' => 'home_commitment_title',
					'label' => 'Câu cam kết', 'type' => 'textarea', 'rows' => 2, 'new_lines' => '',
					'default_value' => 'Cam kết tập trung tất cả nguồn lực để đảm bảo thực hiện chính sách chất lượng',
				),
				array(
					'key' => 'field_caia_home_commitment_image', 'name' => 'home_commitment_image',
					'label' => 'Ảnh nền', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium',
					'mime_types' => 'jpg,jpeg,png,webp',
					'instructions' => 'Nên dùng PNG nền trong suốt, ngang khoảng 3000px trở lên, phần trên khoảng 1/4 ảnh để trống (chữ nằm ở đó). Ảnh hiển thị đủ chiều ngang, không bị cắt hay kéo giãn; chiều cao section tự tính theo tỷ lệ ảnh.',
				),
				array(
					'key' => 'field_caia_home_tab_news', 'label' => '6. Tin tức nổi bật', 'type' => 'tab', 'placement' => 'top',
				),
				array(
					'key'           => 'field_caia_home_news_enabled',
					'label'         => 'Hiển thị section tin tức nổi bật',
					'name'          => 'home_news_enabled',
					'type'          => 'true_false',
					'default_value' => 1,
					'ui'            => 1,
				),
				array(
					'key'           => 'field_caia_home_news_title',
					'label'         => 'Tiêu đề section',
					'name'          => 'home_news_title',
					'type'          => 'text',
					'default_value' => 'Tin tức & kiến thức khí nén',
				),
				array(
					'key'           => 'field_caia_home_news_description',
					'label'         => 'Mô tả section',
					'name'          => 'home_news_description',
					'type'          => 'textarea',
					'rows'          => 2,
					'default_value' => 'Cập nhật tin tức, kinh nghiệm chọn mua, vận hành và bảo dưỡng máy nén khí cho nhà xưởng, nhà máy.',
				),
				array(
					'key'           => 'field_caia_home_news_posts',
					'label'         => 'Chọn bài viết nổi bật',
					'name'          => 'home_news_posts',
					'type'          => 'relationship',
					'instructions'  => 'Chọn các bài viết muốn hiển thị. Kéo thả trong cột đã chọn để sắp xếp thứ tự. Slider tự chuyển sau 5 giây khi số bài nhiều hơn số ô đang hiển thị.',
					'post_type'     => array( 'post' ),
					'post_status'   => array( 'publish' ),
					'filters'       => array( 'search', 'taxonomy' ),
					'elements'      => array( 'featured_image' ),
					'return_format' => 'id',
				),
				array(
					'key'           => 'field_caia_home_news_link',
					'label'         => 'Nút xem tất cả bài viết',
					'name'          => 'home_news_link',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Chọn đường dẫn và nhập chữ trên nút. Để trống để dùng trang Blog với nhãn “Xem tất cả bài viết”.',
				),
				array(
					'key' => 'field_caia_home_tab_reviews', 'label' => '7. Đánh giá khách hàng', 'type' => 'tab', 'placement' => 'top',
				),
				...caia_home_review_fields(),
			),
			'location' => array(
				array(
					array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-trangchu.php' ),
				),
				array(
					array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}

function caia_home_review_fields() {
	return array(
		array(
			'key' => 'field_caia_home_reviews_enabled', 'name' => 'home_reviews_enabled',
			'label' => 'Hiển thị section đánh giá', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
		),
		array(
			'key' => 'field_caia_home_reviews_title', 'name' => 'home_reviews_title',
			'label' => 'Tiêu đề section', 'type' => 'text', 'default_value' => 'Khách hàng nói về Thọ Lập',
		),
		array(
			'key' => 'field_caia_home_reviews_intro', 'name' => 'home_reviews_intro',
			'label' => 'Mô tả section', 'type' => 'textarea', 'rows' => 2,
			'default_value' => 'Phản hồi từ các nhà xưởng, nhà máy đã tin chọn giải pháp máy nén khí của Thọ Lập Hưng Yên.',
		),
		array(
			'key' => 'field_caia_home_reviews_visual_type', 'name' => 'home_reviews_visual_type',
			'label' => 'Hình bên trái', 'type' => 'select', 'default_value' => 'image',
			'choices' => array( 'image' => 'Ảnh thường / SVG tĩnh', 'animation' => 'SVG động (Lottie)', 'none' => 'Ẩn hình bên trái' ),
			'return_format' => 'value',
		),
		array(
			'key' => 'field_caia_home_reviews_animation_file', 'name' => 'home_reviews_animation_file',
			'label' => 'Tệp SVG động (Lottie .json)', 'type' => 'file', 'return_format' => 'id',
			'library' => 'all', 'mime_types' => 'json', 'required' => 1,
			'instructions' => 'Chọn hoặc tải tệp Lottie .json lên. Xóa tệp đang chọn để đổi sang hình động khác.',
			'conditional_logic' => array( array( array( 'field' => 'field_caia_home_reviews_visual_type', 'operator' => '==', 'value' => 'animation' ) ) ),
		),
		array(
			'key' => 'field_caia_home_reviews_image', 'name' => 'home_reviews_image',
			'label' => 'Ảnh bên trái / ảnh dự phòng', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'required' => 1,
			'mime_types' => 'jpg,jpeg,png,webp,gif,svg',
			'instructions' => 'Với ảnh thường: đây là hình hiển thị. Với SVG động: đây là ảnh dự phòng khi hình động chưa tải hoặc người xem bật giảm chuyển động. Có thể chọn SVG tĩnh từ Thư viện.',
			'conditional_logic' => array( array( array( 'field' => 'field_caia_home_reviews_visual_type', 'operator' => '!=', 'value' => 'none' ) ) ),
		),
		array(
			'key' => 'field_caia_home_reviews_image_ai', 'name' => 'home_reviews_image_ai',
			'label' => 'Ảnh lớn là ảnh minh họa AI', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
			'conditional_logic' => array( array( array( 'field' => 'field_caia_home_reviews_visual_type', 'operator' => '==', 'value' => 'image' ) ) ),
		),
		array(
			'key' => 'field_caia_home_reviews_items', 'name' => 'home_reviews_items',
			'label' => 'Danh sách đánh giá', 'type' => 'repeater', 'layout' => 'block',
			'button_label' => 'Thêm đánh giá', 'collapsed' => 'field_caia_home_review_name',
			'instructions' => 'Kéo thả để đổi thứ tự. Các nhận xét có sẵn là nội dung mẫu và chân dung AI. Thay bằng phản hồi thực tế và ảnh khách hàng khi có.',
			'sub_fields' => array(
				array(
					'key' => 'field_caia_home_review_name', 'name' => 'name',
					'label' => 'Tên hiển thị', 'type' => 'text', 'required' => 1,
				),
				array(
					'key' => 'field_caia_home_review_detail', 'name' => 'detail',
					'label' => 'Thông tin bên dưới tên', 'type' => 'text',
				),
				array(
					'key' => 'field_caia_home_review_quote', 'name' => 'quote',
					'label' => 'Nội dung đánh giá', 'type' => 'textarea', 'rows' => 5, 'required' => 1,
				),
				array(
					'key' => 'field_caia_home_review_avatar', 'name' => 'avatar',
					'label' => 'Ảnh chân dung', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail',
				),
				array(
					'key' => 'field_caia_home_review_is_sample', 'name' => 'is_sample',
					'label' => 'Đánh giá mẫu', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
					'instructions' => 'Giữ bật với nhận xét viết mẫu. Tắt khi thay bằng phản hồi thực tế của khách hàng.',
				),
				array(
					'key' => 'field_caia_home_review_avatar_ai', 'name' => 'avatar_ai',
					'label' => 'Ảnh chân dung AI', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1,
				),
			),
		),
	);
}

add_filter( 'acf/validate_value/key=field_caia_home_reviews_animation_file', 'caia_validate_home_review_animation', 10, 2 );
function caia_validate_home_review_animation( $valid, $value ) {
	if ( $valid !== true || ! $value ) {
		return $valid;
	}
	$path = get_attached_file( absint( $value ) );
	$data = $path && is_readable( $path ) ? json_decode( file_get_contents( $path ), true ) : null;
	if ( ! is_array( $data ) || empty( $data['layers'] ) || empty( $data['w'] ) || empty( $data['h'] ) || empty( $data['fr'] ) || ! isset( $data['ip'], $data['op'] ) || $data['op'] <= $data['ip'] ) {
		return 'Tệp đã chọn chưa phải hình động Lottie hợp lệ. Vui lòng chọn tệp .json xuất từ Lottie.';
	}
	return true;
}

function caia_render_home_banner( $page_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}
	$image_ids = array_values( array_unique( array_filter(
		array_map( 'absint', (array) get_field( 'field_caia_home_banner_images', $page_id ) ),
		'wp_attachment_is_image'
	) ) );
	if ( ! $image_ids ) {
		return;
	}
	// Ratio of the first banner image drives the banner height in CSS.
	$first = wp_get_attachment_image_src( $image_ids[0], 'full' );
	$ratio = ( $first && $first[1] && $first[2] ) ? round( $first[1] / $first[2], 4 ) : 2.4;
	?>
	<section class="home-hero" aria-label="Banner trang chủ" style="--hero-ratio: <?php echo esc_attr( $ratio ); ?>;">
		<div class="home-hero__slider">
			<?php foreach ( $image_ids as $index => $image_id ) : ?>
				<div class="home-hero__slide">
					<?php echo wp_get_attachment_image( $image_id, 'full', false, array(
						'class' => 'home-hero__image',
						'loading' => $index === 0 ? 'eager' : 'lazy',
						'fetchpriority' => $index === 0 ? 'high' : 'auto',
						'sizes' => '100vw',
					) ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

function caia_render_home_categories( $page_id ) {
	if ( ! function_exists( 'get_field' ) || ! taxonomy_exists( 'product_cat' ) || get_field( 'field_caia_home_categories_enabled', $page_id ) === false ) {
		return;
	}
	$title = trim( (string) get_field( 'field_caia_home_categories_title', $page_id ) ) ?: 'Danh mục sản phẩm';
	$selected = array_filter( array_map( 'absint', (array) get_field( 'field_caia_home_categories_items', $page_id ) ) );
	$args = array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'menu_order', 'order' => 'ASC' );
	if ( $selected ) {
		$args['include'] = $selected;
		$args['orderby'] = 'include';
	} else {
		$args['parent'] = 0;
		$args['exclude'] = array( absint( get_option( 'default_product_cat' ) ) );
	}
	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}
	?>
	<section class="home-categories" aria-labelledby="home-categories-title">
		<div class="wrap">
			<header class="home-categories__header">
				<h2 id="home-categories-title" class="home-categories__heading"><?php echo esc_html( $title ); ?></h2>
				<img class="home-section-heading__ornament" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/img-heading.png' ); ?>" width="200" height="68" alt="" aria-hidden="true" decoding="async">
			</header>
			<ul class="home-categories__grid" role="list">
				<?php foreach ( $terms as $term ) :
					$url = get_term_link( $term );
					if ( is_wp_error( $url ) ) { continue; }
					$image_id = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
					?>
					<li class="home-categories__item">
						<a class="home-categories__link" href="<?php echo esc_url( $url ); ?>">
							<span class="home-categories__content">
								<span class="home-categories__name"><?php echo esc_html( $term->name ); ?></span>
								<span class="home-categories__more" aria-hidden="true">Xem thêm +</span>
							</span>
							<span class="home-categories__media">
								<?php if ( $image_id && wp_attachment_is_image( $image_id ) ) : ?>
									<?php echo wp_get_attachment_image( $image_id, 'medium_large', false, array( 'class' => 'home-categories__image', 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 599px) 50vw, 240px' ) ); ?>
								<?php else : ?>
									<i class="fa-solid fa-box-open" aria-hidden="true"></i>
								<?php endif; ?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

function caia_render_home_stats( $page_id ) {
	if ( ! function_exists( 'get_field' ) || get_field( 'field_caia_home_stats_enabled', $page_id ) === false ) {
		return;
	}
	$items = array_values( array_filter( (array) get_field( 'field_caia_home_stats_items', $page_id ), function( $item ) {
		return is_array( $item ) && trim( (string) ( $item['number'] ?? '' ) ) !== '';
	} ) );
	if ( ! $items ) {
		return;
	}
	$title         = trim( (string) get_field( 'field_caia_home_stats_title', $page_id ) ) ?: 'Những con số nổi bật';
	$default_icons = array( 'fa-screwdriver-wrench', 'fa-hands-holding-circle', 'fa-gears', 'fa-truck-fast', 'fa-award', 'fa-headset' );
	?>
	<section class="home-stats" aria-labelledby="home-stats-title">
		<div class="wrap">
			<h2 id="home-stats-title" class="home-stats__heading"><?php echo esc_html( $title ); ?></h2>
			<ul class="home-stats__grid" role="list" style="--home-stats-count: <?php echo count( $items ); ?>">
				<?php foreach ( $items as $index => $item ) :
					$icon_id = absint( $item['icon'] ?? 0 );
					?>
					<li class="home-stats__item">
						<span class="home-stats__icon" aria-hidden="true">
							<?php if ( $icon_id && wp_attachment_is_image( $icon_id ) ) : ?>
								<?php echo wp_get_attachment_image( $icon_id, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<i class="fa-solid <?php echo esc_attr( $default_icons[ $index % count( $default_icons ) ] ); ?>"></i>
							<?php endif; ?>
						</span>
						<strong class="home-stats__number"><?php echo esc_html( $item['number'] ); ?></strong>
						<span class="home-stats__label"><?php echo esc_html( $item['label'] ?? '' ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

function caia_render_home_commitment( $page_id ) {
	if ( ! function_exists( 'get_field' ) || get_field( 'field_caia_home_commitment_enabled', $page_id ) === false ) {
		return;
	}
	// Fields not yet saved in the CMS return null: fall back to the same defaults the admin form shows.
	$title    = get_field( 'field_caia_home_commitment_title', $page_id );
	$title    = trim( (string) ( $title ?? 'Cam kết tập trung tất cả nguồn lực để đảm bảo thực hiện chính sách chất lượng' ) );
	$subtitle = get_field( 'field_caia_home_commitment_subtitle', $page_id );
	$subtitle = trim( (string) ( $subtitle ?? 'Công ty TNHH Thọ Lập Hưng Yên' ) );
	if ( $title === '' ) {
		return;
	}
	$image_id = absint( get_field( 'field_caia_home_commitment_image', $page_id ) );
	$image    = $image_id ? wp_get_attachment_image_src( $image_id, 'full' ) : false;
	$style    = '';
	if ( $image ) {
		// Section height follows the photo's real ratio so the full-width image is never cropped or stretched.
		$ratio = ( $image[1] && $image[2] ) ? round( $image[1] / $image[2], 4 ) : 2;
		$style = sprintf( ' style="background-image: url(\'%s\'); --commitment-ratio: %s;"', esc_url( $image[0] ), esc_attr( $ratio ) );
	}
	?>
	<section class="home-commitment<?php echo $image ? ' has-image' : ''; ?>" aria-labelledby="home-commitment-title"<?php echo $style; ?>>
		<div class="wrap">
			<div class="home-commitment__quote">
				<?php if ( $subtitle !== '' ) : ?>
					<p class="home-commitment__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
				<h2 id="home-commitment-title" class="home-commitment__title"><?php echo esc_html( $title ); ?></h2>
				<i class="fa-solid fa-quote-right home-commitment__mark" aria-hidden="true"></i>
			</div>
		</div>
	</section>
	<?php
}

function caia_render_home_product_sections( $page_id ) {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'wc_get_products' ) ) {
		return;
	}
	$sections = (array) get_field( 'field_caia_home_product_sections', $page_id );
	foreach ( $sections as $index => $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}
		$parent = get_term( absint( $section['category'] ?? 0 ), 'product_cat' );
		if ( ! $parent instanceof WP_Term ) {
			continue;
		}
		$children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent->term_id, 'hide_empty' => false, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
		$terms = ! is_wp_error( $children ) && $children ? $children : array( $parent );
		$limit = max( 1, min( 24, absint( $section['count'] ?? 0 ) ?: 4 ) );
		$product_args = array( 'status' => 'publish', 'visibility' => 'catalog', 'limit' => $limit, 'orderby' => 'date', 'order' => 'DESC' );
		if ( get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' ) {
			$product_args['stock_status'] = array( 'instock', 'onbackorder' );
		}
		$tabs = array();
		foreach ( $terms as $term ) {
			$url = get_term_link( $term );
			if ( ! is_wp_error( $url ) ) {
				$products = wc_get_products( array_merge( $product_args, array( 'category' => array( $term->slug ) ) ) );
				if ( $products ) {
					$tabs[] = array( 'term' => $term, 'url' => $url, 'products' => $products );
				}
			}
		}
		$has_child_tabs = ! empty( $tabs ) && $tabs[0]['term']->term_id !== $parent->term_id;
		// Keep products assigned directly to the parent when its child categories are empty.
		if ( ! $tabs && ! is_wp_error( $children ) && $children ) {
			$url = get_term_link( $parent );
			$products = wc_get_products( array_merge( $product_args, array( 'category' => array( $parent->slug ) ) ) );
			if ( ! is_wp_error( $url ) && $products ) {
				$tabs[] = array( 'term' => $parent, 'url' => $url, 'products' => $products );
			}
		}
		if ( ! $tabs ) {
			continue;
		}
		$heading = trim( (string) ( $section['heading'] ?? '' ) ) ?: $parent->name;
		$prefix = 'home-products-' . absint( $page_id ) . '-' . absint( $index );
		?>
		<section class="home-products" aria-labelledby="<?php echo esc_attr( $prefix . '-heading' ); ?>">
			<div class="wrap">
				<header class="home-products__header">
					<h2 class="home-products__heading" id="<?php echo esc_attr( $prefix . '-heading' ); ?>"><?php echo esc_html( $heading ); ?></h2>
					<a class="home-products__button" href="<?php echo esc_url( $tabs[0]['url'] ); ?>" aria-label="<?php echo esc_attr( 'Xem tất cả sản phẩm trong ' . $tabs[0]['term']->name ); ?>">Xem tất cả<i class="fa-solid fa-arrow-right home-products__button-icon" aria-hidden="true"></i></a>
				</header>
				<?php if ( $has_child_tabs ) : ?>
					<div class="home-products__tabs" role="tablist" aria-label="<?php echo esc_attr( 'Danh mục con của ' . $parent->name ); ?>">
						<?php foreach ( $tabs as $tab_index => $tab ) : ?>
							<button type="button" class="home-products__tab<?php echo $tab_index === 0 ? ' is-active' : ''; ?>" role="tab" id="<?php echo esc_attr( $prefix . '-tab-' . $tab['term']->term_id ); ?>" aria-controls="<?php echo esc_attr( $prefix . '-panel-' . $tab['term']->term_id ); ?>" aria-selected="<?php echo $tab_index === 0 ? 'true' : 'false'; ?>" tabindex="<?php echo $tab_index === 0 ? '0' : '-1'; ?>" data-category-url="<?php echo esc_url( $tab['url'] ); ?>" data-category-name="<?php echo esc_attr( $tab['term']->name ); ?>"><?php echo esc_html( $tab['term']->name ); ?></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php foreach ( $tabs as $tab_index => $tab ) :
					$term = $tab['term'];
					$products = $tab['products'];
					?>
					<div class="home-products__panel" id="<?php echo esc_attr( $prefix . '-panel-' . $term->term_id ); ?>"<?php if ( $has_child_tabs ) : ?> role="tabpanel" aria-labelledby="<?php echo esc_attr( $prefix . '-tab-' . $term->term_id ); ?>" tabindex="0"<?php endif; ?><?php echo $tab_index === 0 ? '' : ' hidden'; ?>>
							<div class="home-products__grid">
								<?php foreach ( $products as $product ) : ?>
									<?php caia_render_product_card( $product, $term ); ?>
								<?php endforeach; ?>
							</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}

function caia_render_home_reviews( $page_id ) {
	if ( ! function_exists( 'get_field' ) || ! get_field( 'field_caia_home_reviews_enabled', $page_id ) ) {
		return;
	}
	$items = array_values( array_filter( (array) get_field( 'field_caia_home_reviews_items', $page_id ), function( $item ) {
		return is_array( $item ) && trim( (string) ( $item['quote'] ?? '' ) ) !== '';
	} ) );
	if ( ! $items ) {
		return;
	}
	$title    = get_field( 'field_caia_home_reviews_title', $page_id );
	$intro    = get_field( 'field_caia_home_reviews_intro', $page_id );
	$image_id = absint( get_field( 'field_caia_home_reviews_image', $page_id ) );
	$visual_type = get_field( 'field_caia_home_reviews_visual_type', $page_id ) ?: 'image';
	$animation_id = absint( get_field( 'field_caia_home_reviews_animation_file', $page_id ) );
	$animation_url = $animation_id ? wp_get_attachment_url( $animation_id ) : '';
	$animation_path = $animation_id ? get_attached_file( $animation_id ) : '';
	$has_animation = $visual_type === 'animation' && $animation_url;
	$has_visual = $visual_type !== 'none' && ( $image_id || $has_animation );
	$is_svg = $image_id && get_post_mime_type( $image_id ) === 'image/svg+xml';
	if ( $has_animation && $animation_path && is_readable( $animation_path ) ) {
		$animation_url = add_query_arg( 'ver', filemtime( $animation_path ), $animation_url );
	}
	if ( $has_animation ) {
		wp_enqueue_script( 'caia-lottie-light', get_stylesheet_directory_uri() . '/assets/js/vendor/lottie-light-5.12.2.min.js', array(), '5.12.2', true );
		wp_enqueue_script( 'caia-review-animation', get_stylesheet_directory_uri() . '/assets/js/review-animation.js', array( 'caia-lottie-light' ), filemtime( get_stylesheet_directory() . '/assets/js/review-animation.js' ), true );
	}
	?>
	<section class="home-reviews" aria-label="Đánh giá khách hàng">
		<div class="wrap home-reviews__layout<?php echo $has_visual ? '' : ' home-reviews__layout--no-image'; ?>">
			<?php if ( $has_visual ) : ?>
				<figure class="home-reviews__visual">
					<?php if ( $has_animation || $is_svg ) : ?>
						<div class="home-reviews__image-frame home-reviews__image-frame--animation" role="img" aria-label="Minh họa khách hàng đánh giá dịch vụ">
							<?php if ( $image_id ) : ?>
								<img class="home-reviews__image" src="<?php echo esc_url( wp_get_attachment_url( $image_id ) ); ?>" alt="" aria-hidden="true" loading="lazy">
							<?php endif; ?>
							<?php if ( $has_animation ) : ?>
								<div class="home-reviews__animation" data-src="<?php echo esc_url( $animation_url ); ?>" aria-hidden="true"></div>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<div class="home-reviews__image-frame"><?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'home-reviews__image', 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
			<div class="home-reviews__content">
				<header class="home-reviews__header">
					<?php if ( $title ) : ?>
						<h2 class="home-reviews__heading"><?php echo esc_html( $title ); ?></h2>
						<img class="home-section-heading__ornament" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/img-heading.png' ); ?>" width="200" height="68" alt="" aria-hidden="true" decoding="async">
					<?php endif; ?>
					<?php if ( $intro ) : ?><p class="home-reviews__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
				</header>
				<div class="home-reviews__slider" aria-label="Cảm nhận khách hàng">
					<?php foreach ( $items as $item ) : ?>
						<div class="home-reviews__slide">
							<div class="home-reviews__bubble">
								<blockquote class="home-reviews__quote"><p><?php echo nl2br( esc_html( $item['quote'] ) ); ?></p></blockquote>
							</div>
							<div class="home-reviews__author">
								<?php if ( ! empty( $item['avatar'] ) ) : ?><?php echo wp_get_attachment_image( absint( $item['avatar'] ), 'full', false, array( 'class' => 'home-reviews__avatar', 'loading' => 'lazy', 'alt' => $item['name'] ?? '' ) ); ?><?php endif; ?>
								<div><p class="home-reviews__name"><?php echo esc_html( $item['name'] ?? '' ); ?></p><?php if ( ! empty( $item['detail'] ) ) : ?><p class="home-reviews__detail"><?php echo esc_html( $item['detail'] ); ?></p><?php endif; ?></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

function caia_render_home_featured_news( $page_id ) {
	if ( ! function_exists( 'get_field' ) || get_field( 'field_caia_home_news_enabled', $page_id ) === false ) {
		return;
	}

	$post_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) get_field( 'field_caia_home_news_posts', $page_id ) ) ) ) );
	if ( ! $post_ids ) {
		return;
	}

	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'post__in'            => $post_ids,
			'orderby'             => 'post__in',
			'posts_per_page'      => count( $post_ids ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( ! $posts ) {
		return;
	}

	$title       = trim( (string) get_field( 'field_caia_home_news_title', $page_id ) );
	$description = trim( (string) get_field( 'field_caia_home_news_description', $page_id ) );
	$link        = get_field( 'field_caia_home_news_link', $page_id );
	$blog_page   = get_page_by_path( 'blog' );
	$link_url    = ! empty( $link['url'] ) ? $link['url'] : ( $blog_page ? get_permalink( $blog_page ) : '' );
	$link_title  = ! empty( $link['title'] ) ? $link['title'] : 'Xem tất cả bài viết';
	$link_target = ! empty( $link['target'] ) && $link['target'] === '_blank' ? '_blank' : '_self';
	?>
	<section class="home-news" aria-label="Tin tức nổi bật">
		<div class="wrap home-news__inner">
			<?php if ( $title || $description ) : ?>
				<header class="home-news__header">
					<?php if ( $title ) : ?>
						<h2 class="home-news__heading"><?php echo esc_html( $title ); ?></h2>
						<img class="home-section-heading__ornament" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/img-heading.png' ); ?>" width="200" height="68" alt="" aria-hidden="true" decoding="async">
					<?php endif; ?>
					<?php if ( $description ) : ?><p class="home-news__description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
				</header>
			<?php endif; ?>
			<div class="home-news__slider" aria-label="Các bài viết nổi bật">
				<?php foreach ( $posts as $news_post ) : ?>
					<?php caia_render_news_card( $news_post ); ?>
				<?php endforeach; ?>
			</div>
			<?php if ( $link_url ) : ?>
				<div class="home-news__footer"><a class="home-news__button" href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>"<?php echo $link_target === '_blank' ? ' rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $link_title ); ?><i class="fa-solid fa-arrow-right home-news__button-icon" aria-hidden="true"></i></a></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/** Shared article markup keeps homepage and news-grid cards consistent. */
function caia_render_news_card( $news_post, $heading_tag = 'h3' ) {
	$news_post = get_post( $news_post );
	if ( ! $news_post ) { return; }
	$post_id = $news_post->ID;
	$heading_tag = $heading_tag === 'h2' ? 'h2' : 'h3';
	$excerpt = $news_post->post_excerpt ?: $news_post->post_content;
	$excerpt = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $excerpt ) ), 55, '…' );
	?>
	<article class="home-news__card">
		<a class="home-news__image-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'large', array( 'class' => 'home-news__image', 'loading' => 'lazy' ) ); ?>
			<?php else : ?><span class="home-news__image home-news__image--empty" aria-hidden="true"></span><?php endif; ?>
		</a>
		<div class="home-news__body">
			<<?php echo $heading_tag; ?> class="home-news__title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></<?php echo $heading_tag; ?>>
			<p class="home-news__date"><i class="fa-regular fa-calendar-days" aria-hidden="true"></i><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y', $post_id ) ); ?></time></p>
			<p class="home-news__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		</div>
	</article>
	<?php
}
