<?php
/*
Template Name: About Page
*/
?>

<?php get_header(); ?>
	<div class="content">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
					the_post();
				?>
				<h1 class="title-page"><?php the_title(); ?></h1>
				<div>
					<img src="<?php echo tur_news_thumbnail(); ?>" alt="Image" class="alignleft">
					<?php the_content(); ?>
				</div>
				<img src="<?php echo get_field( 'info-img_2' )['url']; ?>" class="alignright">
				<?php the_field( 'infoblock_plus' ); ?>
			<?php endwhile; else : ?>
				<p><?php _e( 'Ничего не найдено.' ); ?></p>
			<?php endif; ?>
	</div>
	<?php get_sidebar();
		get_footer();
	?>
