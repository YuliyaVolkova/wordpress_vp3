<?php
global $wp_query;
    $args = array_merge($wp_query->query_vars, array(
        'posts_per_page' => 10,
        'paged' => get_query_var('paged')
    ));
query_posts($args);
?>

<?php get_header(); ?>

    <div class="content">
        <h1 class="title-page">
            <?php printf(__('Найдено по запросу: %s'), get_search_query()); ?>
        </h1>
        <div class="posts-list">
            <?php if (have_posts()) :
                while (have_posts()) :
                    the_post();
                    get_template_part('content');
                endwhile;
            else : ?>
                <p><?php _e('Ничего не найдено.'); ?></p>
            <?php endif; ?>
        </div>
        <?php base_pagination();
        wp_reset_query();
        wp_reset_postdata();
        ?>
    </div>
<?php get_sidebar();
    get_footer();
?>
