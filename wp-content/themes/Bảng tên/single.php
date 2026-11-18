<?php

add_filter('genesis_site_layout', function () {
    return 'full-width-content';
});

add_filter('body_class', function ($classes) {
    if (!is_singular('product')) {
        $classes[] = 'single-blog-post';
    }

    return $classes;
});

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_after_header', 'thanh_hung_render_single_blog_post');

if (!function_exists('thanh_hung_single_remove_caia_extras')) {
    function thanh_hung_single_remove_caia_extras()
    {
        global $caia_rating, $caia_social;

        if (is_object($caia_rating)) {
            $rating_priority = has_filter('the_content', array($caia_rating, 'add_rating_content_bottom'));

            if ($rating_priority !== false) {
                remove_filter('the_content', array($caia_rating, 'add_rating_content_bottom'), $rating_priority);
            }
        }

        if (is_object($caia_social)) {
            $social_priority = has_filter('the_content', array($caia_social, 'add_native_share_button_at_bottom'));

            if ($social_priority !== false) {
                remove_filter('the_content', array($caia_social, 'add_native_share_button_at_bottom'), $social_priority);
            }
        }
    }
}

if (!function_exists('thanh_hung_get_featured_posts')) {
    function thanh_hung_get_featured_posts($post_id, $limit = 6)
    {
        $featured_posts = array();
        $exclude_ids = array($post_id);

        $featured_query = new WP_Query(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'post__not_in' => $exclude_ids,
            'meta_key' => 'gf_featured_order',
            'meta_value' => 0,
            'meta_compare' => '>',
            'orderby' => array(
                'meta_value_num' => 'ASC',
                'date' => 'DESC',
            ),
            'ignore_sticky_posts' => true,
        ));

        foreach ($featured_query->posts as $featured_post) {
            $featured_posts[] = $featured_post;
            $exclude_ids[] = $featured_post->ID;
        }

        wp_reset_postdata();

        if (count($featured_posts) < $limit) {
            $recent_query = new WP_Query(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $limit - count($featured_posts),
                'post__not_in' => $exclude_ids,
                'ignore_sticky_posts' => true,
            ));

            foreach ($recent_query->posts as $recent_post) {
                $featured_posts[] = $recent_post;
            }

            wp_reset_postdata();
        }

        return $featured_posts;
    }
}

if (!function_exists('thanh_hung_render_single_blog_post')) {
    function thanh_hung_render_single_blog_post()
    {
        if (is_singular('product')) {
            if (!have_posts()) {
                return;
            }

            while (have_posts()) : the_post();
                ?>
                <main class="single-product-page">
                    <?php the_content(); ?>
                </main>
                <?php
            endwhile;

            return;
        }

        thanh_hung_single_remove_caia_extras();

        if (!have_posts()) {
            return;
        }

        while (have_posts()) : the_post();
            $post_id = get_the_ID();
            $categories = get_the_category($post_id);
            $featured_posts = array();
            ?>
            <main class="single-blog-page">
                <div class="single-blog-wrap">
                    <div class="single-blog-layout">
                        <article class="single-blog-article" itemscope itemtype="https://schema.org/BlogPosting">
                            <meta itemprop="mainEntityOfPage" content="<?php echo esc_url(get_permalink()); ?>">
                            <meta itemprop="datePublished" content="<?php echo esc_attr(get_the_date('c')); ?>">
                            <meta itemprop="dateModified" content="<?php echo esc_attr(get_the_modified_date('c')); ?>">

                            <header class="single-blog-hero">
                                <div class="single-blog-header">
                                <?php if ($categories) : ?>
                                    <div class="single-blog-categories">
                                        <?php foreach ($categories as $category) : ?>
                                            <span><?php echo esc_html($category->name); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <h1 itemprop="headline"><?php the_title(); ?></h1>

                                <div class="single-blog-meta">
                                    <span>
                                        <i class="fa-solid fa-calendar-days"></i>
                                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d/m/Y')); ?></time>
                                    </span>
                                </div>
                                </div>
                            <?php if (has_post_thumbnail()) : ?>
                                <figure class="single-blog-hero__image" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                                    <?php the_post_thumbnail('full', array('loading' => 'eager')); ?>
                                    <meta itemprop="url" content="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full')); ?>">
                                </figure>
                            <?php endif; ?>
                            </header>

                            <div class="single-blog-content" itemprop="articleBody">
                                <?php
                                $article_content = apply_filters('the_content', get_the_content());
                                $article_content = preg_replace('/<h1(\s[^>]*)?>/i', '<h2$1>', $article_content);
                                $article_content = preg_replace('/<\/h1>/i', '</h2>', $article_content);
                                echo $article_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                ?>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    document.querySelectorAll('.single-blog-content table').forEach(function(table) {
                                        if (table.closest('.wp-block-table, .single-table-scroll')) {
                                            return;
                                        }

                                        var wrapper = document.createElement('div');
                                        wrapper.className = 'single-table-scroll';
                                        table.parentNode.insertBefore(wrapper, table);
                                        wrapper.appendChild(table);
                                    });
                                });
                            </script>
                        </article>

                        <aside class="single-blog-sidebar" aria-label="Bài viết nổi bật">
                            <section class="single-related-box">
                                <h2>Bài viết nổi bật</h2>

                                <?php if ($featured_posts) : ?>
                                    <div class="single-related-list">
                                        <?php foreach ($featured_posts as $index => $featured_post) : ?>
                                            <?php
                                            $featured_categories = get_the_category($featured_post->ID);
                                            $featured_category = !empty($featured_categories) ? $featured_categories[0] : null;
                                            ?>
                                            <article class="single-related-item">
                                                <span class="single-related-number" aria-hidden="true"><?php echo esc_html($index + 1); ?></span>
                                                <div class="single-related-body">
                                                    <?php if ($featured_category) : ?>
                                                        <span class="single-related-category">
                                                            <?php echo esc_html($featured_category->name); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <h3>
                                                        <a href="<?php echo esc_url(get_permalink($featured_post)); ?>">
                                                            <?php echo esc_html(get_the_title($featured_post)); ?>
                                                        </a>
                                                    </h3>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </aside>
                    </div>
                </div>
            </main>
            <?php
        endwhile;
    }
}

genesis();
