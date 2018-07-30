<?php
add_theme_support( 'post-thumbnails' );
add_theme_support( 'custom-logo' );

register_nav_menus(array(
	'top'    => 'Верхнее меню',    //Название месторасположения меню в шаблоне
	'bottom' => 'Нижнее меню',      //Название другого месторасположения меню в шаблоне
));

if ( ! function_exists( 'get_menu_footer_with_tur_classes' ) ) {
	function get_menu_footer_with_tur_classes() {
		$menu = wp_nav_menu(array(
			'menu'            => 'footer_menu',
			'theme_location'  => 'bottom',
			'container_class' => 'bottom-menu',
			'menu_class'      => 'b-menu__list',
			'echo'            => 0,
		));
		// добавляем ко всем пунктам класс my__class
		$menu = str_replace( 'class="menu-item', 'class="b-menu__list__item', $menu );
		return $menu;
	}
}

if ( ! function_exists( 'tur_news_thumbnail' ) ) {
	function tur_news_thumbnail() {
		$thumb = get_the_post_thumbnail_url();
		if ( empty( $thumb ) ) {
			return '/img/post_thumb/thumb_1.jpg';
		} else {
			return $thumb;
		}
	}
}

if ( ! function_exists( 'base_pagination' ) ) {
	function base_pagination() {
		global $wp_query;

		$big = 999999999; // This needs to be an unlikely integer

		return paginate_links( array(
			'base'      => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
			'current'   => max( 1, get_query_var( 'paged' ) ),
			'total'     => $wp_query->max_num_pages,
			'mid_size'  => 1,
			'prev_text' => '<i class="icon icon-angle-double-left"></i>',
			'next_text' => '<i class="icon icon-angle-double-right"></i>',
		) );
	}
}

if ( ! function_exists( 'right_sidebar_widgets_init' ) ) {
	function right_sidebar_widgets_init() {
		register_sidebar(array(
			'name'          => __( 'Правая колонка' ),
			'id'            => 'sidebar-right-my',
			'description'   => __( 'Правая колонка' ),
			'before_widget' => '<div id="%1$s" class="sidebar__sidebar-item">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="sidebar-item__title">',
			'after_title'   => '</h3>',
		));
	}
	add_action( 'widgets_init', 'right_sidebar_widgets_init' );
}

// for actions single page
if ( ! function_exists( 'categories_action' ) ) {
	function categories_action( $id, $taxonomy ) {
		$terms = get_the_terms( $id, $taxonomy );
		if ( null === $terms ) {
			return null;
		}   $str_terms = '';
		$terms_name_ar = [];
		foreach ( $terms as $term ) {
			if ( in_array( $term->name, $terms_name_ar, true ) ) {
				continue;
			}
			$terms_name_ar[] = $term->name;
			$str_terms       = $str_terms . '<span class="postmetadata_item"><a class="btn-read-post" href="' . get_term_link( $term, $taxonomy ) . '">' . $term->name . '</a></span>';
		}
		return $str_terms;
	}
}

add_filter( 'get_the_archive_title', function ( $title ) {
	if ( is_category() ) {
		$title = single_cat_title( '', false );
	} elseif ( is_tag() ) {
		$title = single_tag_title( '', false );
	} elseif ( is_author() ) {
		$title = '<span class="vcard">' . get_the_author() . '</span>';
	} elseif ( is_tax( 'departure' ) ) {
		$title = single_term_title( '', false );
	} elseif ( is_day() ) {
		$title = get_the_date( _x( 'F j, Y', 'daily archives date format' ) );
	} elseif ( is_month() ) {
		$title = get_the_date( _x( 'F Y', 'monthly archives date format' ) );
	} elseif ( is_year() ) {
		$title = get_the_date( _x( 'Y', 'yearly archives date format' ) );
	}
	return $title;
});

/**
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @param string $title Default title text for current view.
 * @param string $sep   Optional separator.
 * @return string Filtered title.
 */
if ( ! function_exists( 'wp_docs_filter_wp_title' ) ) {
	function wp_docs_filter_wp_title( $title, $sep ) {
		global $paged, $page;

		if ( is_feed() ) {
			return $title;
		}

		// Add the site name.
		$title .= get_bloginfo( 'name' );

		// Add the site description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title = "$title $sep $site_description";
		}
		// Add a page number if necessary.
		if ( $paged >= 2 || $page >= 2 ) {
			$title = "$title $sep " . sprintf( 'Page %s', max( $paged, $page ) );
		}
		return $title;
	}
	add_filter( 'wp_title', 'wp_docs_filter_wp_title', 10, 2 );
}

//for include styles in header template

if ( ! function_exists( 'theme_slug_enqueue_style' ) ) {
	function theme_slug_enqueue_style() {
		wp_enqueue_style( 'tur-libs', get_stylesheet_directory_uri() . '/css/libs.min.css', false );
		wp_enqueue_style( 'tur-main', get_stylesheet_directory_uri() . '/css/main.css', false );
		wp_enqueue_style( 'tur-media', get_stylesheet_directory_uri() . '/css/media.css', false );
	}
	add_action( 'wp_enqueue_scripts', 'theme_slug_enqueue_style' );
}

//short-code function for add current Year from admin-panel
if ( ! function_exists( 'currentYear' ) ) {
	function current_year() {
		return date( 'Y' );
	}
	add_shortcode( 'year', 'current_year' );
}
