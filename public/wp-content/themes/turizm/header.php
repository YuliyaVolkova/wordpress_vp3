<?php
$custom_logo_id = get_theme_mod( 'custom_logo' );
$logo           = wp_get_attachment_image_src( $custom_logo_id, 'full' );
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<?php wp_head(); ?>
</head>
<body>
<div class="wrapper">
	<header class="main-header">
		<div class="top-header">
			<div class="top-header__wrap">
				<div class="logotype-block">
					<div class="logo-wrap">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
							<?php if ( has_custom_logo() ) : ?>
								<img src="<?php echo esc_url( $logo[0] ); ?>" alt="Логотип" class="logo-wrap__logo-img">
							<?php else : ?>
								<h1><?php get_bloginfo( 'name' ); ?></h1>
							<?php endif; ?>
						</a>
					</div>
				</div>
				<?php wp_nav_menu( array( 'menu' => 'header_menu' ) ); ?>
			</div>
		</div>
		<div class="bottom-header">
			<div class="search-form-wrap">
				<?php get_search_form(); ?>
			</div>
		</div>
	</header>
	<!-- header_end-->
	<div class="main-content">
		<div class="content-wrapper">
