<?php

class SlpagesMain extends slpages
{
	public function init()
	{
		add_action( 'init', array( &$this, 'slpagesPostRegister' ) );
		add_filter( 'display_post_states', array( &$this, 'customPostState' ) );
		add_filter( 'posts_join', array( &$this, 'slpagesSearchJoin' ) );
		add_filter( 'posts_where', array( &$this, 'slpagesSearchWhere' ) );
		add_filter( 'posts_orderby', array( &$this, 'slpagesSearchOrderBy' ) );
		add_filter( 'posts_distinct', array( &$this, 'slpagesSearchDistinct' ) );
	}
	
	public function slpagesPostRegister()
	{
		slpages::getInstance()->includes[ 'service' ]->silentUpdateCheck();

		$labels = array
		(
			'name'					=> _x( 'Sunny pages', 'Post type general name', 'slpages' ),
			'singular_name'			=> _x( 'slpages', 'Post type singular name', 'slpages' ),
			//'add_new'				=> _x( 'Add New', 'slpages', 'slpages' ),
			//'add_new_item'			=> __( 'Add New Sunny landing pages', 'slpages' ),
			'edit_item'				=> __( 'Edit Sunny landing pages', 'slpages' ),
			'new_item'				=> __( 'New Sunny landing pages', 'slpages' ),
			'view_item'				=> __( 'View Sunny landing pages', 'slpages' ),
			'search_items'			=> __( 'Search Sunny landing pages', 'slpages' ),
			'not_found'				=> __( 'Nothing found', 'slpages' ),
			'not_found_in_trash'	=> __( 'Nothing found in Trash', 'slpages' ),
			'parent_item_colon'		=> ''
		);

		$capabilities = array
		(
			''
		);

		$args = array
		(
			'labels'				=> $labels,
			'description'			=> __( 'Allows you to have slpages on your WordPress site.', 'slpages' ),
			'public'				=> false,
			'publicly_queryable'	=> true,
			'show_ui'				=> true,
			'query_var'				=> true,
			'menu_icon'				=> SLPAGES_PLUGIN_URI . 'assets/img/16-logo.png',
			'capability_type'		=> 'page',
			// 'capabilities' => array(
				// 'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
			  // ),
			'menu_position'			=> null,
			'rewrite'				=> false,
			'can_export'			=> false,
			'hierarchical'			=> false,
			'has_archive'			=> false,
			'show_in_menu'			=> false,
			'supports'				=> array( 'slpages_my_selected_page', 'slpages_slug', 'slpages_name', 'slpages_url' ),
			'register_meta_box_cb'	=> array( &slpages::getInstance()->includes[ 'edit' ], 'removeMetaBoxes' )
		);

		register_post_type( 'slpages_post', $args );
	}

	public static function getUserId()
	{
		return get_option( 'slpages.user_id' );
	}

	public function customPostState( $states )
	{
		global $post;

		$show_custom_state = null !== get_post_meta( $post->ID, 'slpages_my_selected_page' );

		if ( $show_custom_state )
		{
			$states = array();
		}

		return $states;
	}

	public static function isPageModeActive( $new_edit = null )
	{
		global $pagenow;

		// make sure we are on the backend
		if ( !is_admin() )
		{
			return false;
		}

		if ( $new_edit == "edit" )
		{
			return in_array( $pagenow, array( 'post.php' ) );
		}
		elseif ( $new_edit == "new" )
		{
			// check for new post page
			return in_array( $pagenow, array( 'post-new.php' ) );
		}
		else
		{
			// check for either new or edit
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}
	}

	public static function isSlpagesSearch()
	{
		global $pagenow;
		
		if ( is_admin() && $pagenow == 'edit.php' && isset( $_GET[ 'post_type' ] ) && isset( $_GET[ 's' ] ) && $_GET[ 'post_type' ] == 'slpages_post' && $_GET[ 's' ] != '' )
		{
			return true;
		}

		return false;
	}

	function slpagesSearchJoin( $join )
	{
		global $pagenow, $wpdb;

		if ( self::isSlpagesSearch() )
		{
			$join .='LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
		}

		return $join;
	}

	function slpagesSearchWhere( $where )
	{
		global $pagenow, $wpdb;
		$keyword = isset( $_GET[ 's' ] ) ? filter_var( $_GET[ 's' ], FILTER_SANITIZE_STRING ) : '';

		if( self::isSlpagesSearch() )
		{
			$pattern = "/\(wp_posts\.post_title\s+LIKE\s+(\'[^\']+\')\)\s+OR\s+\(wp_posts\.post_content\s+LIKE\s+(\'[^\']+\')\)/";
			$new_where = "( ( {$wpdb->postmeta}.meta_key = 'slpages_name' AND {$wpdb->postmeta}.meta_value LIKE '%{$keyword}%' ) OR ( {$wpdb->postmeta}.meta_key = 'slpages_slug' AND {$wpdb->postmeta}.meta_value LIKE '%{$keyword}%' ) OR {$wpdb->posts}.ID = '{$keyword}' )";
			$where = preg_replace( $pattern, $new_where, $where );
		}

		return $where;
	}

	function slpagesSearchOrderBy( $orderby )
	{
		global $pagenow, $wpdb;

		if( self::isSlpagesSearch() )
		{
			$orderby = "{$wpdb->posts}.post_date DESC";
		}

		return $orderby;
	}

	function slpagesSearchDistinct()
	{
		return "DISTINCT";
	}
}