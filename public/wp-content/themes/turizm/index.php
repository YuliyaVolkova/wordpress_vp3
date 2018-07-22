<?php
    global $post;
    $postTypesAr = ['post'];
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $pageTitle = get_the_title('227');

    if (is_home() && is_front_page()) {
        $postTypesAr = ['post', 'actions'];
    }
    switch($post->ID) {
        case(206) :
            $postTypesAr = ['actions'];
            $pageTitle = get_the_title();
             break;
        case(208) :
            $postTypesAr = ['post'];
            $pageTitle = get_the_title();
            break;
    }
    $args = [
        'post_type' => $postTypesAr,
        'posts_per_page' => 10,
        'paged' => $paged
    ];
    query_posts($args);
?>

<?php get_header(); ?>
    <div class="content">
        <h1 class="title-page"><?php echo $pageTitle; ?></h1>
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
