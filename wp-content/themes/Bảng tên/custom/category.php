<?php
/**
 * Render standard post category archives with the homepage article-card design.
 */

add_filter(
	'genesis_site_layout',
	function() {
		return 'full-width-content';
	}
);

add_filter(
	'body_class',
	function( $classes ) {
		$classes[] = 'article-category-archive';
		return $classes;
	}
);

remove_action( 'genesis_before_loop', 'caia_archive_heading', 5 );
remove_action( 'genesis_before_content', 'caia_archive_heading', 5 );
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'caia_render_article_category_archive' );

/** Prioritize category-featured posts without excluding posts lacking metadata. */
function caia_order_category_featured_posts( $orderby, $query ) {
	if ( ! $query->get( 'caia_category_featured_first' ) ) {
		return $orderby;
	}

	global $wpdb;

	return "COALESCE((SELECT MAX(CAST(featured_meta.meta_value AS SIGNED))
		FROM {$wpdb->postmeta} AS featured_meta
		WHERE featured_meta.post_id = {$wpdb->posts}.ID
		AND featured_meta.meta_key = 'gf_featured_by_cat_order'), 0) DESC,
		{$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
}
add_filter( 'posts_orderby', 'caia_order_category_featured_posts', 10, 2 );

/** Display 12 posts, category-featured first then newest, with pagination. */
function caia_render_article_category_archive() {
	$term = get_queried_object();

	if ( ! $term instanceof WP_Term || 'category' !== $term->taxonomy ) {
		return;
	}

	$paged = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 12,
			'paged'               => $paged,
			'cat'                 => $term->term_id,
			'ignore_sticky_posts' => true,
			'caia_category_featured_first' => true,
		)
	);
	?>
	<main class="home-featured-posts article-category-archive__content">
		<div class="wrap">
			<header class="article-category-archive__header">
				<h1 class="home-featured-posts__title"><?php echo esc_html( single_cat_title( '', false ) ); ?></h1>
				<?php if ( category_description( $term->term_id ) ) : ?>
					<div class="article-category-archive__description"><?php echo wp_kses_post( category_description( $term->term_id ) ); ?></div>
				<?php endif; ?>
			</header>

			<?php if ( $query->have_posts() ) : ?>
				<div class="home-featured-posts__grid">
					<?php while ( $query->have_posts() ) : ?>
						<?php $query->the_post(); ?>
						<article class="home-featured-posts__card">
							<a class="home-featured-posts__image-link" href="<?php the_permalink(); ?>">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large', array( 'class' => 'home-featured-posts__image', 'loading' => 'lazy' ) ); ?>
								<?php else : ?>
									<span class="article-category-archive__image-placeholder" aria-hidden="true"></span>
								<?php endif; ?>
							</a>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="home-featured-posts__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '...' ) ); ?></p>
							<a class="home-featured-posts__read-more" href="<?php the_permalink(); ?>">Đọc thêm</a>
						</article>
					<?php endwhile; ?>
				</div>

				<?php
				$pagination = paginate_links(
					array(
						'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'current'   => $paged,
						'total'     => $query->max_num_pages,
						'mid_size'  => 1,
						'prev_text' => 'Trước',
						'next_text' => 'Sau',
					)
				);
				?>
				<?php if ( $pagination ) : ?>
					<nav class="article-category-pagination" aria-label="Phân trang bài viết">
						<div class="nav-links"><?php echo wp_kses_post( $pagination ); ?></div>
					</nav>
				<?php endif; ?>
			<?php else : ?>
				<p class="article-category-archive__empty">Chưa có bài viết trong chuyên mục này.</p>
			<?php endif; ?>
		</div>
	</main>
	<?php

	wp_reset_postdata();
}
