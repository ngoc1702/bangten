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

if (!function_exists('thanh_hung_get_related_posts')) {
    /** Posts from the same category first, topped up with the latest posts. */
    function thanh_hung_get_related_posts($post_id, $limit = 5)
    {
        $exclude = array($post_id);
        $related = array();

        $category_ids = wp_get_post_categories($post_id);
        if ($category_ids) {
            $same = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'category__in' => $category_ids,
                'post__not_in' => $exclude,
                'ignore_sticky_posts' => true,
                'no_found_rows' => true,
            ));
            foreach ($same as $item) {
                $related[] = $item;
                $exclude[] = $item->ID;
            }
        }

        if (count($related) < $limit) {
            $recent = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $limit - count($related),
                'post__not_in' => $exclude,
                'ignore_sticky_posts' => true,
                'no_found_rows' => true,
            ));
            $related = array_merge($related, $recent);
        }

        return $related;
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
            // Parent product categories only (no children, no "Chưa phân loại").
            $product_cats = taxonomy_exists('product_cat') ? get_terms(array(
                'taxonomy'   => 'product_cat',
                'parent'     => 0,
                'hide_empty' => false,
                'exclude'    => array((int) get_option('default_product_cat')),
                'orderby'    => 'menu_order',
                'order'      => 'ASC',
            )) : array();
            $related_posts = thanh_hung_get_related_posts($post_id, 5);
            ?>
            <main class="single-blog-page">
                <div class="single-blog-wrap">
                    <div class="single-blog-layout">
                        <article class="single-blog-article" itemscope itemtype="https://schema.org/BlogPosting">
                            <meta itemprop="mainEntityOfPage" content="<?php echo esc_url(get_permalink()); ?>">
                            <meta itemprop="datePublished" content="<?php echo esc_attr(get_the_date('c')); ?>">
                            <meta itemprop="dateModified" content="<?php echo esc_attr(get_the_modified_date('c')); ?>">

                            <header class="single-blog-header">
                                <h1 itemprop="headline"><?php the_title(); ?></h1>

                                <div class="single-blog-meta">
                                    <span>
                                        <i class="fa-regular fa-calendar-days" aria-hidden="true"></i>
                                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d/m/Y')); ?></time>
                                    </span>
                                </div>
                                <?php if (has_post_thumbnail()) : ?>
                                    <meta itemprop="image" content="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full')); ?>">
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

                        <aside class="single-blog-sidebar" aria-label="Thanh bên">
                            <?php if ($product_cats && !is_wp_error($product_cats)) : ?>
                                <section class="single-side-box single-cats-box">
                                    <h2 class="single-side-title">Danh mục sản phẩm</h2>
                                    <ul class="single-cats-list">
                                        <?php foreach ($product_cats as $product_cat) : ?>
                                            <li><a href="<?php echo esc_url(get_term_link($product_cat)); ?>"><?php echo esc_html($product_cat->name); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                            <?php endif; ?>

                            <?php if ($related_posts) : ?>
                                <section class="single-side-box single-related-box">
                                    <h2 class="single-side-title">Tin liên quan</h2>
                                    <div class="single-related-list">
                                        <?php foreach ($related_posts as $related_post) : ?>
                                            <article class="single-related-item">
                                                <a class="single-related-thumb" href="<?php echo esc_url(get_permalink($related_post)); ?>" tabindex="-1" aria-hidden="true">
                                                    <?php if (has_post_thumbnail($related_post)) : ?>
                                                        <?php echo get_the_post_thumbnail($related_post, 'thumbnail', array('loading' => 'lazy', 'alt' => '')); ?>
                                                    <?php endif; ?>
                                                </a>
                                                <div class="single-related-body">
                                                    <h3><a href="<?php echo esc_url(get_permalink($related_post)); ?>"><?php echo esc_html(get_the_title($related_post)); ?></a></h3>
                                                    <!-- <time class="single-related-date" datetime="<?php echo esc_attr(get_the_date('c', $related_post)); ?>"><?php echo esc_html(get_the_date('d/m/Y', $related_post)); ?></time> -->
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                        </aside>
                    </div>
                </div>
            </main>
            <?php
        endwhile;
    }
}

genesis();
