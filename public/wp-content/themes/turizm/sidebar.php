<?php $tag_args = array(
	'smallest' => 0.9,
	'largest'  => 1.1,
	'unit'     => 'rem',
	'taxonomy' => [ 'post_tag' ],
	'format'   => 'array',
	'echo'     => false,
);

$category_args = array(
	'taxonomy' => 'departure',
	'style'    => 'list',
	'title_li' => '',
	'orderby'  => 'ID',
	'echo'     => false,
);

$tags       = wp_tag_cloud( $tag_args );
$categories = wp_list_categories( $category_args );
?>

<div class="sidebar">
	<?php if ( $tags ) : ?>
		<div class="sidebar__sidebar-item">
			<div class="sidebar-item__title">Теги</div>
			<div class="sidebar-item__content">
				<ul class="tags-list">
					<?php foreach ( $tags as $tag ) : ?>
						<li class="tags-list__item">
							<?php echo $tag; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( $categories ) : ?>
		<div class="sidebar__sidebar-item">
			<div class="sidebar-item__title">Категории</div>
			<ul class="category-list">
				<?php echo $categories; ?>
			</ul>
		</div>
	<?php endif; ?>
	<div class="sidebar__sidebar-item">
		<div class="sidebar-item__title">Новости месяца</div>
		<ul class="category-list">
			<?php get_calendar(); ?>
		</ul>
	</div>
	<?php if ( is_active_sidebar( 'sidebar-right-my' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-right-my' ); ?>
	<?php endif; ?>
</div>
