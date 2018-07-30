<?php
$prev = get_previous_post();
$next = get_next_post();
?>

<?php get_header(); ?>
<div class="content" id="post- <?php the_ID(); ?>" >
<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<div class="article-title title-page">
			<?php the_title(); ?>
		</div>
		<?php if ( get_post_type() === 'actions' ) : ?>
			<h3>Акция</h3>
		<?php endif; ?>
		<div class="article-image">
			<img src="<?php echo esc_url( tur_news_thumbnail() ); ?>" alt="Image поста">
		</div>
		<?php
		if ( get_post_type() === 'actions' ) :
			$cat_actions = categories_action( get_the_ID(), 'departure' );
			if ( null !== $cat_actions ) :
				?>
				<div class="postmetadata">
					<?php
						echo $cat_actions;
					?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<div class="article-info">
			<div class="post-date"><?php echo get_the_date(); ?></div>
		</div>
		<div class="article-text">
			<?php the_content(); ?>
		</div>
		<?php
	endwhile;
	else :
		?>
		<p><?php esc_html( 'Ничего не найдено.' ); ?></p>
	<?php endif; ?>
	<div class="article-pagination">
		<?php if ( ! empty( $prev ) ) : ?>
			<div class="article-pagination__block pagination-prev-left">
				<a href="<?php the_permalink( $prev->ID ); ?>" class="article-pagination__link">
					<i class="icon icon-angle-double-left"></i>
					<?php if ( get_post_type() === 'actions' ) : ?>
						Предыдущая акция
					<?php elseif ( get_post_type() === 'post' ) : ?>
						Предыдущая статья
					<?php endif; ?>
				</a>
				<div class="wrap-pagination-preview pagination-prev-left">
					<div class="preview-article__img">
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $prev->ID ) ); ?>" class="preview-article__image">
					</div>
					<div class="preview-article__content">
						<div class="preview-article__info">
							<a href="<?php the_permalink( $prev->ID ); ?>" class="post-date">
								<?php echo $prev->post_date; ?>
							</a>
						</div>
						<div class="preview-article__text">
							<?php echo $prev->post_title; ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		endif; if ( ! empty( $next ) ) :
	?>
			<div class="article-pagination__block pagination-prev-right">
				<a href="<?php the_permalink( $next->ID ); ?>" class="article-pagination__link">
					<?php if ( get_post_type() === 'actions' ) : ?>
						Следующая акция
					<?php elseif ( get_post_type() === 'post' ) : ?>
						Следующая статья
					<?php endif; ?>
					<i class="icon icon-angle-double-right"></i>
				</a>
				<div class="wrap-pagination-preview pagination-prev-right">
					<div class="preview-article__img">
						<img src="<?php echo esc_url( get_the_post_thumbnail_url( $next->ID ) ); ?>" class="preview-article__image">
					</div>
					<div class="preview-article__content">
						<div class="preview-article__info">
							<a href="<?php the_permalink( $next->ID ); ?>" class="post-date">
								<?php echo $next->post_date; ?>
							</a>
						</div>
						<div class="preview-article__text">
							<?php echo $next->post_title; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php get_sidebar();
	get_footer();
?>
