<?php
	global $post;
	$post_types_ar = [ 'post' ];
	$paged         = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$page_title    = get_the_title( '227' );

if ( is_home() && is_front_page() ) {
	$post_types_ar = [ 'post', 'actions' ];
}
switch ( $post->ID ) {
	case ( 206 ):
		$post_types_ar = [ 'actions' ];
		$page_title    = get_the_title();
		break;
	case ( 208 ):
		$post_types_ar = [ 'post' ];
		$page_title    = get_the_title();
		break;
}
	$args = [
		'post_type'      => $post_types_ar,
		'posts_per_page' => 10,
		'paged'          => $paged,
	];

	query_posts( $args );
	$pages_links = base_pagination();
	?>

<?php get_header(); ?>
	<div class="content">
		<h1 class="title-page"><?php echo $page_title; ?></h1>
		<div class="posts-list">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					get_template_part( 'content' );
				endwhile;
			else :
				?>
				<p><?php _e( 'Ничего не найдено.' ); ?></p>
			<?php endif; ?>
		</div>
		<!--Display the pagination if more than one page is found -->
		<?php if ( $pages_links ) : ?>
			<div class="pagination"><?php echo $pages_links; ?></div>
			<?php
		endif;
		wp_reset_query();
		wp_reset_postdata();
?>
	</div>
	<?php get_sidebar();
	get_footer();
	?>
