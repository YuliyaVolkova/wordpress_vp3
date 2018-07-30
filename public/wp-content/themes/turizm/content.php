<div class="post-wrap" id="post-<?php the_ID(); ?>">
	<div class="post-thumbnail">
		<img src="<?php echo tur_news_thumbnail(); ?>" alt="Image поста" class="post-thumbnail__image">
	</div>
	<div class="post-content">
		<?php if ( get_post_type() === 'actions' ) : ?>
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
