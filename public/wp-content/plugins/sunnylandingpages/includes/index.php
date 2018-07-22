<?php

class SlpagesIndex extends slpages
{
	public function init()
	{
		if( !self::getInstance()->includes[ 'main' ]->getUserId() )
		{
			add_action( 'admin_menu', array( $this,'wpdocs_register_my_custom_menu_page'),5 );
			
		}
		else{
			add_action( 'admin_menu', array( $this,'wpdocs_register_my_custom_menu_page_list'),5 );
		}
		
		if ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'slpages_post' )
		{			
			add_action('admin_head', array( &$this,'hide_that_stuff'));
			add_action( 'admin_head', array( &$this,'addAdminJS') );
			if( !self::getInstance()->includes[ 'main' ]->getUserId() )
			{
				slpagesIO::addNotice( __( sprintf( 'To get started, connect your sunnylandingpages.com account on the <a href="%s"> Settings page</a> </br><a target="_blank" href="https://sunnylandingpages.com/blog/how-to-build-a-landing-page-for-wordpress/">Read complete steps </a> or <a target="_blank" href="https://www.youtube.com/watch?v=Xk2oAURYbsQ">view the video guide</a> on how to create and publish a landing page to your wordpress site.', SLPAGES_PLUGIN_SETTINGS_URI ) ), 'notice-warning' );
				self::getInstance()->includes[ 'admin' ]->removeEditPage();
				return false;
			}			
		}
	}
	
	public function addAdminJS() { ?>
		<script type="text/javascript" >
		jQuery(document).ready(function($) {
			var slp_plugin_uri = "<?php echo admin_url( SLPAGES_PLUGIN_SETTINGS_URI ); ?>";
			jQuery("#posts-filter").prepend("<div class='login-box' style='float: right;margin-left: 10px;'><form method='post' action='"+slp_plugin_uri+"'><input type='hidden' name='action' value='disconnect'><input type='submit' class='button button-primary' value='Disconnect'></form></div>");
		});
		</script> <?php
	}
	
	public function remove_submenus() {
	  if(!current_user_can('activate_plugins')) {
		global $submenu;
		unset($submenu['edit.php?post_type=slpages_post'][10]); // Removes 'Add New'.
		}
	}
	
	public function hide_that_stuff() {
		if(isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'slpages_post'){
			  echo '<style type="text/css">
				#favorite-actions {display:none;}
				.add-new-h2{display:none;}
				.tablenav{display:none;}
				.page-title-action{display:none;}
				td[data-colname="Page ID"],td[data-colname="Post Type"],td[data-colname="Page Name"],td[data-colname="Custom url"]{display: none;}
				</style>';
				;
		}
	}
	
	/**
	 * Register a custom menu page.
	 */
	public function wpdocs_register_my_custom_menu_page() {
		add_menu_page(
			__( 'Sunny Pages', 'textdomain' ),
			'Sunny Pages',
			'manage_options',
			'options-general.php?page=sunnylandingpages/slpages.php',
			'',
			SLPAGES_PLUGIN_URI . 'assets/img/slpages-logo-16x16.png',
			26
		);
	}
	
	/**
	 * Register a custom menu page.
	 */
	public function wpdocs_register_my_custom_menu_page_list() {
		add_menu_page(
			__( 'Sunny Pages', 'textdomain' ),
			'Sunny Pages',
			'manage_options',
			'edit.php?post_type=slpages_post',
			'',
			SLPAGES_PLUGIN_URI . 'assets/img/slpages-logo-16x16.png',
			26
		);
	}

	public function removeQuickEdit( $actions )
	{
		global $post;

		if ( $post->post_type == 'slpages_post' )
		{
			unset( $actions[ 'inline hide-if-no-js' ] );
		}

		return $actions;
	}
}
