<?php
/**
 * Template Name: Hỏi đáp ACF
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'genesis_site_layout', function() { return 'full-width-content'; }, 100 );
add_filter( 'body_class', function( $classes ) { $classes[] = 'caia-faq-page'; return $classes; } );
remove_action( 'genesis_after_header', 'genesis_do_breadcrumbs', 5 );
add_action( 'genesis_after_header', 'caia_render_faq_hero', 6 );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'caia_render_faq_page' );

function caia_faq_field( $key ) {
	return function_exists( 'get_field' ) ? get_field( $key, get_queried_object_id() ) : null;
}

function caia_render_faq_hero() {
	$title = trim( (string) caia_faq_field( 'faq_title' ) ) ?: get_the_title( get_queried_object_id() );
	$image_id = absint( caia_faq_field( 'faq_hero_image' ) );
	caia_render_page_banner( $title, $image_id );
}

function caia_render_faq_page() {
	$intro = caia_faq_field( 'faq_intro' );
	$groups = (array) caia_faq_field( 'faq_groups' );
	$open_first = caia_faq_field( 'faq_open_first' ) !== false;
	$first = true;
	?>
	<div class="faq-page">
		<?php if ( $intro ) : ?><div class="faq-page__intro"><?php echo wp_kses_post( $intro ); ?></div><?php endif; ?>
		<?php foreach ( $groups as $index => $group ) :
			if ( ! is_array( $group ) ) { continue; }
			$items = array_filter( (array) ( $group['items'] ?? array() ), function( $item ) {
				return is_array( $item ) && trim( $item['question'] ?? '' ) !== '' && trim( wp_strip_all_tags( $item['answer'] ?? '' ) ) !== '';
			} );
			if ( ! $items || trim( $group['title'] ?? '' ) === '' ) { continue; }
			$heading_id = 'faq-group-' . absint( $index );
			?>
			<section class="faq-group" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
				<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $group['title'] ); ?></h2>
				<div class="faq-group__items">
					<?php foreach ( $items as $item ) : ?>
						<details class="faq-item"<?php echo $first && $open_first ? ' open' : ''; ?>>
							<summary><?php echo esc_html( $item['question'] ); ?></summary>
							<div class="faq-item__answer"><?php echo wp_kses_post( $item['answer'] ); ?></div>
						</details>
						<?php $first = false; ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
	<?php
}

genesis();
