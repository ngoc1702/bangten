<?php
/**
 * Template Name: Liên hệ ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'genesis_site_layout',
	function() {
		return 'full-width-content';
	},
	100
);

add_filter(
	'body_class',
	function( $classes ) {
		$classes[] = 'page-template-contact';
		return $classes;
	}
);

remove_action( 'genesis_after_header', 'genesis_do_breadcrumbs', 5 );
add_action( 'genesis_after_header', 'caia_render_contact_page_banner', 6 );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'caia_render_contact_page' );

function caia_render_contact_page_banner() {
	$id = get_queried_object_id();
	$title = function_exists( 'get_field' ) ? trim( (string) get_field( 'contact_banner_title', $id ) ) : '';
	$image = function_exists( 'get_field' ) ? absint( get_field( 'contact_banner_image', $id ) ) : 0;
	caia_render_page_banner( $title ?: get_the_title( $id ), $image );
}

function caia_contact_icon_class( $icon_class ) {
	$icon_class = trim( (string) $icon_class );

	return $icon_class ? $icon_class : 'fa-solid fa-circle-info';
}

function caia_contact_map_src( $map_embed ) {
	$map_embed = trim( (string) $map_embed );

	if ( ! $map_embed ) {
		return '';
	}

	if ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\']/i', $map_embed, $matches ) ) {
		$map_embed = html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' );
	}

	return esc_url( $map_embed );
}

function caia_render_contact_page() {
	if ( ! have_posts() || ! function_exists( 'get_field' ) ) {
		return;
	}

	while ( have_posts() ) {
		the_post();

		$title       = trim( (string) get_field( 'contact_title' ) );
		$description = trim( (string) get_field( 'contact_description' ) );
		$info_items  = get_field( 'contact_info_items' );
		$map_title   = trim( (string) get_field( 'contact_map_title' ) );
		$map_url     = caia_contact_map_src( get_field( 'contact_map_url' ) );
		?>
		<div class="contact-page">
			<div class="wrap contact-page__layout<?php echo $map_url ? '' : ' contact-page__layout--no-map'; ?>">
				<section class="contact-page__details" aria-labelledby="contact-page-title">
					<?php if ( $title ) : ?>
						<h2 id="contact-page-title"><?php echo esc_html( $title ); ?></h2>
						<img class="contact-page__ornament" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/img-heading.png' ); ?>" width="200" height="68" alt="" aria-hidden="true" decoding="async">
					<?php endif; ?>
					<?php if ( $description ) : ?>
						<p class="contact-page__intro"><?php echo nl2br( esc_html( $description ) ); ?></p>
					<?php endif; ?>

					<?php if ( is_array( $info_items ) && $info_items ) : ?>
						<div class="contact-page__info-list">
							<?php foreach ( $info_items as $item ) : ?>
								<?php
								$label   = isset( $item['label'] ) ? trim( (string) $item['label'] ) : '';
								$content = isset( $item['content'] ) ? trim( (string) $item['content'] ) : '';
								$link    = isset( $item['link'] ) ? esc_url( $item['link'] ) : '';
								$is_external = (bool) preg_match( '#^https?://#i', $link );
								?>
								<?php if ( $label || $content ) : ?>
									<article class="contact-page__info-item">
										<i class="<?php echo esc_attr( caia_contact_icon_class( isset( $item['icon_class'] ) ? $item['icon_class'] : '' ) ); ?>" aria-hidden="true"></i>
										<div>
											<?php if ( $label ) : ?><h3><?php echo esc_html( $label ); ?></h3><?php endif; ?>
											<?php if ( $content ) : ?>
												<?php if ( $link ) : ?>
													<a href="<?php echo $link; ?>"<?php if ( $is_external ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?php echo nl2br( esc_html( $content ) ); ?></a>
												<?php else : ?>
													<p><?php echo nl2br( esc_html( $content ) ); ?></p>
												<?php endif; ?>
											<?php endif; ?>
										</div>
									</article>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>

				<?php if ( $map_url ) : ?>
				<section class="contact-page__map"<?php echo $map_title ? ' aria-labelledby="contact-map-title"' : ''; ?>>
					<?php if ( $map_title ) : ?>
						<h2 id="contact-map-title"><?php echo esc_html( $map_title ); ?></h2>
					<?php endif; ?>
					<?php if ( $map_url ) : ?>
						<div class="contact-page__map-frame">
							<iframe src="<?php echo $map_url; ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="<?php echo esc_attr( $map_title ? $map_title : 'Bản đồ' ); ?>"></iframe>
						</div>
					<?php endif; ?>
				</section>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

genesis();
