<?php
/**
 * Template Name: Về chúng tôi
 *
 * Short banner, free page content in the middle (normal editor), then a brand-blue highlights section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'genesis_site_layout', function() { return 'full-width-content'; }, 100 );
add_filter( 'body_class', function( $classes ) { $classes[] = 'caia-about-page'; return $classes; } );
remove_action( 'genesis_after_header', 'genesis_do_breadcrumbs', 5 );
add_action( 'genesis_after_header', 'caia_render_about_page_banner', 6 );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'caia_render_about_page' );

function caia_about_field( $name ) {
	return function_exists( 'get_field' ) ? get_field( $name, get_queried_object_id() ) : null;
}

function caia_render_about_page_banner() {
	$title = trim( (string) caia_about_field( 'about_banner_title' ) );
	caia_render_page_banner( $title ?: get_the_title( get_queried_object_id() ), absint( caia_about_field( 'about_banner_image' ) ) );
}

function caia_render_about_values() {
	if ( caia_about_field( 'about_values_enabled' ) === false ) {
		return;
	}
	$items = array_values( array_filter( (array) caia_about_field( 'about_values_items' ), function( $item ) {
		return is_array( $item ) && trim( (string) ( $item['title'] ?? '' ) ) !== '';
	} ) );
	if ( ! $items ) {
		return;
	}
	$image_id = absint( caia_about_field( 'about_values_image' ) );
	$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	$style    = '--about-count: ' . count( $items ) . ';';
	if ( $image ) {
		$style .= " background-image: url('" . esc_url( $image ) . "');";
	}
	?>
	<section class="about-values<?php echo $image ? ' has-image' : ''; ?>" style="<?php echo esc_attr( $style ); ?>" aria-label="Điểm nổi bật">
		<div class="wrap">
			<ul class="about-values__grid" role="list">
				<?php foreach ( $items as $item ) :
					$icon_id    = absint( $item['icon_image'] ?? 0 );
					$icon_class = trim( (string) ( $item['icon_class'] ?? '' ) );
					$icon_class = preg_match( '/^[a-z0-9 -]+$/i', $icon_class ) ? $icon_class : 'fa-solid fa-award';
					$title      = trim( (string) ( $item['title'] ?? '' ) );
					$text       = trim( (string) ( $item['description'] ?? '' ) );
					?>
					<li class="about-values__item">
						<span class="about-values__icon" aria-hidden="true">
							<?php if ( $icon_id && wp_attachment_is_image( $icon_id ) ) : ?>
								<?php echo wp_get_attachment_image( $icon_id, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<i class="<?php echo esc_attr( $icon_class ); ?>"></i>
							<?php endif; ?>
						</span>
						<h2 class="about-values__title"><?php echo esc_html( $title ); ?></h2>
						<?php if ( $text !== '' ) : ?><p class="about-values__text"><?php echo esc_html( $text ); ?></p><?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

function caia_render_about_clients() {
	if ( caia_about_field( 'about_clients_enabled' ) === false ) {
		return;
	}
	$items = array_values( array_filter( array_map( 'absint', (array) caia_about_field( 'about_clients_logos' ) ), 'wp_attachment_is_image' ) );
	if ( ! $items ) {
		return;
	}
	$title = trim( (string) caia_about_field( 'about_clients_title' ) );
	$pages = array_chunk( $items, 10 ); // 5 columns x 2 rows per slide
	?>
	<section class="about-clients" aria-label="<?php echo esc_attr( $title ?: 'Khách hàng' ); ?>">
		<div class="wrap">
			<?php if ( $title !== '' ) : ?>
				<header class="about-clients__header">
					<h2 class="about-clients__heading"><?php echo esc_html( $title ); ?></h2>
					<div class="about-clients__arrows" aria-label="Chuyển trang logo"></div>
				</header>
			<?php endif; ?>
			<div class="about-clients__slider" data-pages="<?php echo count( $pages ); ?>">
				<?php foreach ( $pages as $page_items ) : ?>
					<div class="about-clients__page">
						<?php foreach ( $page_items as $logo_id ) : ?>
							<div class="about-clients__item">
								<?php echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'alt' => trim( (string) get_post_meta( $logo_id, '_wp_attachment_image_alt', true ) ), 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

function caia_render_about_page() {
	while ( have_posts() ) {
		the_post();
		$content = trim( (string) get_the_content() );
		if ( $content !== '' ) {
			echo '<section class="about-content"><div class="about-content__inner single-blog-content">' . apply_filters( 'the_content', get_the_content() ) . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
	caia_render_about_values();
	caia_render_about_clients();
}

genesis();
