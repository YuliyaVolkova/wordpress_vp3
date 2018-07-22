<?php get_header(); ?>

    <div class="content">
        <h1 class="title-page">
            <?php printf(__('Найдено по запросу: %s'), get_search_query()); ?>
        </h1>
        <div class="posts-list">
            <?php if (have_posts()) :
                while (have_posts()) :
                    the_post(); ?>
                    <div class="post-wrap" id="post-<?php the_ID(); ?>">
                        <div class="post-thumbnail">
                            <img src="<?= turnews_thumbnail(); ?>" alt="Image поста" class="post-thumbnail__image">
                        </div>
                        <div class="post-content">
                            <?php if (get_post_type() == 'actions') : ?>
                                <h3>Акция</h3>
                            <?php endif; ?>
                            <div class="post-content__post-info">
                                <div class="post-date">
                                    <?php echo get_the_date(); ?>
                                </div>
                            </div>
                            <div class="post-content__post-text">
                                <div class="post-title">
                                    <?php the_title(); ?>
                                </div>
                                <div>
                                    <?php the_excerpt(); ?>
                                </div>
                            </div>
                            <div class="post-content__post-control">
                                <a href="<?php the_permalink(); ?>" class="btn-read-post">
                                    Читать далее >>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
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
