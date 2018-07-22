<?php

class SlpagesEdit extends slpages
{
	const UPDATE_OK = 1;
	const UPDATE_FAILED = 2;

	public function init()
	{	
		if ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'slpages_post' )
			add_action( 'init', array( &$this, 'slpagesCheckAllPageCallback' ) );
	
		add_action( 'admin_head', array( &$this,'slp_action_javascript') ); 
		add_action( 'add_meta_boxes', array( &$this, 'addCustomMetaBox' ) );
		
		add_action( 'before_delete_post', array( &$this, 'trashslpagesPost' ) );
		add_filter( 'post_updated_messages', array( &$this, 'slpagesPostUpdatedMessage' ), 1, 1 );
		add_action( 'load-edit.php', array( &$this, 'slpagesCleanup') );
		add_action( 'init', array( &$this, 'registerInvalidPosttype' ) );
		add_filter( 'bulk_actions-edit-slpages_post', array(&$this, 'removeBulkActions' ) );
		add_action( 'quick_edit_custom_box',  array( &$this,'slpages_add_to_bulk_quick_edit_custom_box'), 10, 2 );
		add_action( 'manage_posts_custom_column', array( &$this,'slpages_populating_my_posts_columns'), 10, 2 );
		add_action( 'admin_print_scripts-edit.php', array( &$this,'slpages_enqueue_edit_scripts') );
		
		add_filter( 'manage_edit-slpages_post_columns', array( &$this, 'editPostsColumns' ) );
		add_action( 'save_post', array( &$this, 'saveCustomMeta' ), 1, 2 );
		add_action( 'save_post', array( &$this, 'validateCustomMeta' ), 20, 2 );
		add_action( 'save_post',array( &$this,'slpages_save_post'), 10, 2 );
	}
	
	public function slp_action_javascript() { ?>
		<script type="text/javascript" >
		jQuery(document).ready(function($) {
			jQuery('form#adv-settings fieldset.metabox-prefs').addClass('hidden');
			jQuery('.inline-edit-slpages_post fieldset.inline-edit-date').parent().addClass('hidden');
			jQuery('.inline-edit-slpages_post fieldset .inline-edit-status').parent().parent().parent().removeClass('inline-edit-col-right').addClass('inline-edit-col');
		});
		</script> <?php
		echo '<div class="SLPAGES-PLUGIN-SETTINGS-URI" style="display:none" data-attr-st-uri="'.admin_url( SLPAGES_PLUGIN_SETTINGS_URI ).'"></div>';
	}
	
	public function slpagesCheckAllPageCallback()
	{
		if( self::getInstance()->includes[ 'main' ]->getUserId() )
		{
			try
			{
				$response = self::getInstance()->includes[ 'api' ]->slpagesApiCall( 'get-pages',array
				(
					'user_id' => get_option( 'slpages.user_id' ),
					'plugin_hash' => get_option( 'slpages.plugin_hash' )
				) );
				
				$this->saveAutoCustomMeta($response);
			}
			catch( slpagesApiCallException $e )
			{
				$this->setUpdateStatus( self::UPDATE_FAILED );
				slpagesIO::addNotice( __( 'Page could not be created. ' ), 'error' );
			}
			return true;
		}
	}
	
	public function saveAutoCustomMeta( $slp_posts )
	{
		$slpages_post_type = 'slpages_post';
		$slpages_slug = '';
		$active_post_id = array();				
		
		foreach($slp_posts->data as $slp_posts_data){
			$slpages_post_id_by_meta = self::getInstance()->includes[ 'edit' ]->get_post_id_by_meta_key_and_value('slpages_my_selected_page',$slp_posts_data['id']);			
			$active_post_id[] = $slpages_post_id_by_meta;
		}
		
		$old_post_id = self::getInstance()->includes[ 'edit' ]->slpagesGetOldPost();
		
		$delete_post = array_diff($old_post_id,$active_post_id);
		
		if(!empty($delete_post))
			$this->slpagesSuperCleanup($delete_post);
		
		try{						
					
			if(!empty($slp_posts->data)){
				foreach($slp_posts->data as $slp_posts_data){
					$new =	$slp_posts_data['id'];
					$slp_new_screenshot = $slp_posts_data['screenshot'];
					$slpages_title = $slp_posts_data['title'];
					$slpages_wp_post_id = $slp_posts_data['wp_post_id'];
					$slpages_user_id = $slp_posts_data['subaccount'];
					$slpages_custom_url = $slp_posts_data['custom_url'];
					$slpages_page_stats = $slp_posts_data['page_stats'];
					
					$slpages_post_id_by_meta = self::getInstance()->includes[ 'edit' ]->get_post_id_by_meta_key_and_value('slpages_my_selected_page',$new);
					
					$slpages_custom_url_slug = explode('/',$slpages_custom_url);
						
					$url = get_site_url();

					$url = str_replace( array( 'http://', 'https://' ), '', $url );
					
					$match = strpos($slpages_custom_url, $url);
					$match = $match !== false && $match <= $url.length ? true : false;
					
					//$url = $url.'/';
					$slpages_custom_url_slug = str_replace($url.'/','',$slpages_custom_url);
					
					if($slpages_wp_post_id && $match)
						$slpages_post_status = 'publish';
					else
						$slpages_post_status = 'draft';										
					
					if(!$slpages_post_id_by_meta){
						$new_post_id = wp_insert_post(
														array(
															'post_title'=>$slpages_title,
															'post_type'=>'slpages_post', 
															'post_content'=>'', 
															'post_status'=>$slpages_post_status ,
															'comment_status'=>'closed',
															'ping_status'=>'closed',
															'post_name'=>$slpages_title,
															'post_author'  => get_current_user_id()
															)
														);
														
						if($new_post_id){
							add_post_meta( $new_post_id, 'slpages_my_selected_page', $new );
							add_post_meta( $new_post_id, 'slpages_name', $slpages_title );
							add_post_meta( $new_post_id, 'slpages_page_screenshot_url', $slp_new_screenshot );
							add_post_meta( $new_post_id, 'slpages_page_stats',  json_encode($slpages_page_stats));
							
							if($slpages_wp_post_id && !empty($slpages_custom_url_slug) &&  $match)
								add_post_meta( $new_post_id, 'slpages_slug', $slpages_custom_url_slug );
							if($slpages_wp_post_id &&  $match){
								add_post_meta( $new_post_id, 'slpages_slug', '' );
								$this->setFrontslpages( $new_post_id );
							}
							elseif($slpages_title){
								$fake_slug = preg_replace("/[^A-Za-z0-9]/", "", $slpages_title);
								add_post_meta( $new_post_id, 'slpages_slug', $fake_slug );
							}
						}
						$sync_wp_post_slpages[$new] = $new_post_id;
						$this->setUpdateStatus( self::UPDATE_OK );
					}
					else{
						
						update_post_meta( $slpages_post_id_by_meta, 'slpages_my_selected_page', $new );
						update_post_meta( $slpages_post_id_by_meta, 'slpages_name', $slpages_title );
						update_post_meta( $slpages_post_id_by_meta, 'slpages_page_screenshot_url', $slp_new_screenshot );
						update_post_meta( $slpages_post_id_by_meta, 'slpages_page_stats',  json_encode($slpages_page_stats));
						
						$this->setUpdateStatus( self::UPDATE_OK );
					}	
					
				}				
		
			}
			else{
				slpagesIO::addNotice( __( 'No pages are available for publishing. You have to:<br>- publish some pages in SunnyLandingPages app dashboard.' ), 'warning' );
			}
			
			
		}
		catch( slpagesApiCallException $e )
		{
			$this->setUpdateStatus( self::UPDATE_FAILED );
			slpagesIO::addNotice( __( 'Page could not be created. '), 'error' );
		}
		return true;
	}
	
	/**
	 * Get post id from meta key and value
	 * @param string $key
	 * @param mixed $value
	 * @return int|bool
	 * @author David M&aring;rtensson <david.martensson@gmail.com>
	 */
	function get_post_id_by_meta_key_and_value($key, $value) {
		global $wpdb;
		$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".esc_sql($key)."' AND meta_value='".esc_sql($value)."'");
		if (is_array($meta) && !empty($meta) && isset($meta[0])) {
			$meta = $meta[0];
		}		
		if (is_object($meta)) {
			return $meta->post_id;
		}
		else {
			return false;
		}
	}
	
	public function editPostsColumns( $columns )
	{
		self::getInstance()->includes[ 'edit' ]->editStylesAndScripts();
		$cols = array();
			
		$cols[ 'slpages_name' ] = __( 'Page Name', 'slpages' );
		$cols[ 'cb' ] = $columns[ 'cb' ];
		$cols[ 'slpages_post_preview' ] = __( 'Preview', 'slpages' );
		$cols[ 'slpages_post_name' ] = __( 'Landing Page Title', 'slpages' );
		//$cols[ 'slpages_post_stats' ] = '<span class="slpages-variation-stats-column-text">' . __( 'Variation Testing Stats', 'slpages' ) . '</span> <a href="#" class="slpages-hide-stats">(Hide Stats)</a>';
		$cols[ 'post_status' ] = __( 'Page Status', 'slpages' );
		$cols[ 'slpages_post_visits' ] = __( 'Visits', 'slpages' );
		$cols[ 'slpages_post_conversions' ] = __( 'Conversions', 'slpages' );
		$cols[ 'slpages_post_conversion_rate' ] = __( 'Conversion Rate', 'slpages' );
		$cols[ 'post_type' ] = __( 'Post Type', 'slpages' );
		$cols[ 'slpages_slug' ] = __( 'Custom url', 'slpages' );		
		$cols[ 'slpages_my_selected_page' ] = __( 'Page ID', 'slpages' );
		
		
		return $cols;
	}
	
	function slpages_save_post( $post_id, $post ) {
		self::getInstance()->includes[ 'edit' ]->saveCustomMeta( $post_id, $post );
	   return;
	   // don't save for autosave
	   if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		  return $post_id;

	   // dont save for revisions
	   if ( isset( $post->post_type ) && $post->post_type == 'revision' )
		  return $post_id;

	   switch( $post->post_type ) {

		  case 'slpages_post':

			 // release date
		 // Because this action is run in several places, checking for the array key keeps WordPress from editing
			 // data that wasn't in the form, i.e. if you had this post meta on your "Quick Edit" but didn't have it
			 // on the "Edit Post" screen.
		 if ( array_key_exists( 'slpages_slug', $_POST ) ){
			update_post_meta( $post_id, 'slpages_slug', $_POST[ 'slpages_slug' ] );
		 }
		 break;

	   }

	}
	
	function slpages_enqueue_edit_scripts() {
	   wp_enqueue_script( 'slpages-admin-edit', SLPAGES_PLUGIN_DIR . 'js/quick_edit.js', array( 'jquery', 'inline-edit-post' ), '', true );
	}
	
	function slpages_populating_my_posts_columns( $column_name, $post_id ) {
	   switch( $column_name ) {
		   case 'slpages_post_preview':
				$page_url = self::getInstance()->includes[ 'page' ]->getPageUrl( $post_id );
				$page_preview = self::getInstance()->includes[ 'page' ]->getPageScreenshot( $post_id );
				
				if ( !empty( $page_preview ) )
				{
					echo '<a href="' . $page_url . '" target="_blank"><img class="slpages-post-preview-image" src="' . $page_preview . '" /></a>';
				}
				else
				{
					echo '<img class="slpages-post-preview-image" src="' . SLPAGES_PLUGIN_URI . '/assets/img/wordpress-thumb.jpg" />';
				}
				echo '<div class="SLPAGES-PLUGIN-SETTINGS-URI" style="display:none" data-attr-st-uri="'.admin_url( SLPAGES_PLUGIN_SETTINGS_URI ).'"></div>';
				break;

			case 'slpages_post_name':
				$slpages_slug = get_post_meta( $post_id, 'slpages_slug', true );
				$page_url = self::getInstance()->includes[ 'page' ]->getPageUrl( $post_id );
				$page_edit_url = self::getInstance()->includes[ 'page' ]->getPageEditUrl( $post_id );
				$page_name = self::getInstance()->includes[ 'page' ]->getPageName( $post_id );
				$delete_link = get_delete_post_link( $post_id, null, true );
				$wp_post_id = url_to_postid( $page_url );

				if( $wp_post_id )
				{
					$wp_post_edit_url = get_edit_post_link( $wp_post_id );
					echo '<div class="error">' . sprintf( __( '<p>Sunny Landing Pages URL (<a href="%s">%s</a>) is duplicated. Sunny Landing Pages plugin will override post settings, Sunny Landing Pages will be displayed.</p><p>To avoid permalink overriding <a href="%s">edit the post</a> and change permalink or <a href="%s">edit Sunny Landing Pages</a> and change custom URL.' ), $page_url, $page_url, $wp_post_edit_url, $page_edit_url ) . '</p></div>';
					$additional_class = ' slpages-warning ';

				}

				$test_path = get_home_path() . $slpages_slug;

				if( $slpages_slug != '' && is_dir( $test_path ) )
				{
					echo '<div class="error"><p>' . sprintf( '<strong>' . __( 'Custom URL' ) . '</strong>' . __( ' is incorrect, it leads to an existing directory (%s). <a href="%s">Edit slpages</a> and change custom URL to prevent 403 server error. ' ), $test_path, $page_edit_url ) . '</p></div>';
					$additional_class = ' slpages-warning ';
				}
				//' . $page_edit_url .'
				echo '<div class="slpages-post-name ' . $additional_class . '"><strong><a href="#">' .  $page_name . '</a></strong></div>';
				echo '<div class="slpages-post-url">Landing Page URL: <a href="' . $page_url . '" target="_blank">' . $page_url . '</a></div>';
				$post_status_val = get_post_status( $post_id );
				// if($post_status_val == 'draft')
					// echo '<div class="slpages-error" >Not Publish</div>';			 
			
				echo '<div class="slpages-delete"><a class="submitdelete hidden" href="' . $delete_link . '">' . __( 'Delete from WP' ) . '</a></div>';
				echo '<div class="slpages-editinline"><a class="editinline" href="#">' . __( 'Quick Edit' ) . '</a></div>';
				
				break;

			case 'slpages_post_stats':
				$page_stats = get_post_meta( $post_id, 'slpages_page_stats', true );

				if( is_array( $page_stats ) )
				{
					$view = self::getInstance()->includes[ 'view' ];
					$view->init( SLPAGES_PLUGIN_DIR .'/includes/templates/slpages/index_page_stats.php' );
					$view->page_stats = $page_stats;
					echo $view->fetch();
				}
				else
				{
					echo '<div class="slpages-index-page-stats">' . __( 'Stats are not available at the moment' ) . '</div>';
				}
				
				echo '<div class="stat-loader">' . __( 'Refreshing stats' ) . '</div>';

				break;

			case 'slpages_post_visits':
				$page_stats = (array)json_decode(get_post_meta( $post_id, 'slpages_page_stats', true ));
				
				if( is_array( $page_stats ) && isset( $page_stats[ 'visits' ] ) )
				{
					echo $page_stats[ 'visits' ];	
				}
				else{
					echo 0;
				}
				break;

			case 'slpages_post_conversions':
				$page_stats = (array)json_decode(get_post_meta( $post_id, 'slpages_page_stats', true ));				
				if( is_array( $page_stats ) && isset( $page_stats[ 'conversion' ] ) )
				{
					echo $page_stats[ 'conversion' ];
				}
				else{
					echo 0;
				}
				
				break;

			case 'slpages_post_conversion_rate':
				$page_stats = (array)json_decode(get_post_meta( $post_id, 'slpages_page_stats', true ));

				if( is_array( $page_stats ) && isset( $page_stats[ 'conversion' ] ) && isset( $page_stats[ 'visits' ] ) )
				{
					echo self::getInstance()->includes[ 'page' ]->calcAvarageConversion( $page_stats[ 'visits' ], $page_stats[ 'conversion' ] ) . '%';
				}
				
				break;
		  case 'slpages_slug':
			 echo '<div id="slpages_slug-' . $post_id . '">' . get_post_meta( $post_id, 'slpages_slug', true ) . '</div>';			 
			 break;
		  case 'post_type':
			$isFrontPage = self::getInstance()->includes[ 'page' ]->isFrontPage( $post_id );
			$is_not_found_page = self::getInstance()->includes[ 'page' ]->is404Page( $post_id );
			$slpages_post_type = null;
			
			if ( $isFrontPage )
			{
				$slpages_post_type = 'home';
			}
			elseif( $is_not_found_page )
			{
				$slpages_post_type = '404';
			}
			 echo '<div id="slpages_post_type-' . $post_id . '">' . $slpages_post_type . '</div>';			 
			 break;
			 case 'slpages_my_selected_page':
			 echo '<div id="slpages_my_selected_page-' . $post_id . '">' . get_post_meta( $post_id, 'slpages_my_selected_page', true ) . '</div>';			 
			 break;
			 case 'slpages_name':
			 echo '<div id="slpages_name-' . $post_id . '">' . get_post_meta( $post_id, 'slpages_name', true ) . '</div>';			 
			 break;
			  case 'post_status':
			  $post_status_val = get_post_status( $post_id );
			if($post_status_val == 'draft')
				echo '<div class="slpages-error" id="slpages_post_status-' . $post_id . '"><strong>' . ucwords(get_post_status( $post_id )) . '</storng></div>';			 
			else
				echo '<div id="slpages_post_status-' . $post_id . '"><strong>' . ucwords(get_post_status( $post_id )) . '</storng></div>';
			 break;
			 
	   }
	}
	public function slpages_add_to_bulk_quick_edit_custom_box( $column_name, $post_type ) {
	   switch ( $post_type ) {
		  case 'slpages_post':
			 switch( $column_name ) {
				  case 'slpages_name':
				   ?><fieldset class="inline-edit-col-left inline-edit-col-forth inline-edit-col-top">
					  <div class="inline-edit-group">
						 <label>
							<span class="title">Page Title :</span>
							<span data-name="slpages_name" class="slp-page-title"></span>
							<input type="hidden" name="slpages_name" value="" />
						 </label>
					  </div>
				   </fieldset><?php
				   break;
				    case 'slpages_my_selected_page':
				   ?><fieldset class="inline-edit-col-left hidden ">
					  <div class="inline-edit-group">
						 <label>
							<span class="title">Page ID</span>
							<input type="hidden" name="slpages_my_selected_page" value="" />
						 </label>
					  </div>
				   </fieldset><?php
				   break;
				  case 'slpages_slug':
				   ?><fieldset class="inline-edit-col-left inline-edit-col-top inline-edit-col-42">
					  <div class="inline-edit-group">
						 <label>
							<span class="title" style="margin-top: -47px; text-transform: uppercase; font-weight: 600; word-wrap: normal;width: 40%;">Landing Page URL :</span>
							<span class="title" style="margin-top: 0px; word-wrap: normal;width: auto;"><?php echo get_site_url()."/";?></span>
							<input type="text" name="slpages_slug" value="" style="margin-left: auto;"/>
							<input type="text" name="slpages_slug_hidden" value="" class="hidden" disabled />
							<input type="hidden" name="slpages_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>" />
						 </label>
					  </div>
				   </fieldset><?php
				   break;
				    case 'post_type':
				   ?><fieldset class="inline-edit-col-left inline-edit-col-forth inline-edit-col-top">
					  <div class="inline-edit-group">
						 <label>
							<span class="title">Page Type</span>
							
							<span class="input-text-wrap">
									<select name="post-type">
										<option value="">Normal</option>
										<option value="home">Home</option>
										<option value="404">404</option>
									</select>
								</span>
							
						 </label>
					  </div>
				   </fieldset><?php
				   break;
			 }
			 break;

	   }
	}

	
	public function removeBulkActions( $actions )
	{
		unset( $actions[ 'untrash'] );
		unset( $actions[ 'edit'] );
		unset( $actions[ 'trash'] );

		return $actions;
	}

	public  function registerInvalidPosttype()
	{
		$args = array(
			'label' => __( 'slpages invalid' ),
			'public' => false,
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => false
		);

		register_post_status( 'slpages_invalid', $args );
	}

	public function slpagesGetOldPost(){
		$post_type = slpagesIO::getVar( 'post_type', 'post', 'request' );
		$post_id_array = array();

		if( $post_type != 'slpages_post' )
		{
			return;
		}

		global $wpdb;

		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'slpages_post'";
		$results = $wpdb->get_results( $sql, OBJECT );

		foreach( $results as $result )
		{
			$post_id_array[] = $result->ID;
		}
		return $post_id_array;
	}

	
	public function slpagesSuperCleanup($delete_post)
	{
		
		$post_type = slpagesIO::getVar( 'post_type', 'post', 'request' );

		if( $post_type != 'slpages_post' )
		{
			return;
		}
		
		foreach( $delete_post as $result )
		{
			wp_delete_post( $result, true );
			delete_post_meta($result, 'slpages_my_selected_page' );
			delete_post_meta($result, 'slpages_name' );
			delete_post_meta($result, 'slpages_page_screenshot_url' );
			delete_post_meta($result, 'slpages_page_stats' );
			delete_post_meta($result, 'slpages_slug' );
		}
	}
	
	public function slpagesCleanup()
	{
		$post_type = slpagesIO::getVar( 'post_type', 'post', 'request' );

		if( $post_type != 'slpages_post' )
		{
			return;
		}

		global $wpdb;

		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'slpages_post' AND post_status = 'slpages_invalid'";
		$results = $wpdb->get_results( $sql, OBJECT );

		foreach( $results as $result )
		{
			wp_delete_post( $result->ID, true );
		}
	}

	public function slpagesPostUpdatedMessage( $messages )
	{
		global $post;

		$post_url = self::getInstance()->includes[ 'page' ]->getPageUrl( $post->ID );

		if( $this->getUpdateStatus() != self::UPDATE_OK )
		{
			$messages[ 'slpages_post' ] = array(
				0 => '',
				1 => '',
				2 => '',
				3 => '',
				4 => '',
				5 => '',
				6 => '',
				7 => '',
				8 => '',
				9 => '',
				10 => '',
			);
		}
		else
		{
			$messages[ 'slpages_post' ] = array(
				0 => '',
				1 => sprintf( __( 'Page updated. <a target="_blank"href="%s">View page</a>' ), esc_url( $post_url ) ),
				2 => '',
				3 => '',
				4 => __( 'Page updated.' ),
				5 => '',
				6 => sprintf( __( 'Page published. <a target="_blank" href="%s">View page</a>' ), esc_url( $post_url ) ),
				7 => __( 'Page saved.' ),
				8 => sprintf( __( 'Page submitted. <a target="_blank" href="%s">Preview page</a>' ), esc_url( add_query_arg( 'preview', 'true', $post_url ) ) ),
				9 => '',
				10 => '',
			);
		}

		return $messages;
	}

	public function trashslpagesPost( $post_id )
	{
		global $post;

		if ( $post->post_type != 'slpages_post' )
		{
			return $post_id;
		}

		$page_id = get_post_meta( $post->ID, 'slpages_my_selected_page', true );
		$page = self::getInstance()->includes[ 'page' ]->getMyPage( $page_id );
		
		//page is created by current user and is not deleted in app
		if( $page != null )
		{
			
			$wp_page_url = str_replace( array( 'http://', 'https://' ), '', self::getInstance()->includes[ 'page' ]->getPageUrl( $post->ID ) );
			$app_page_url = $page->url;
			$page_configuration = unserialize( $page->configuration );
			$is_app_url_in_history = in_array( $app_page_url, $page_configuration->wp_url_history );
			$is_wp_url_in_history = in_array( $wp_page_url, $page_configuration->wp_url_history );

			if(
				$wp_page_url != '' &&
				$app_page_url != '' &&
				(
					$wp_page_url == $app_page_url ||
					(
						isset( $page_configuration->wp_url_history ) &&
						is_array( $page_configuration->wp_url_history ) &&
						( $is_app_url_in_history || $is_wp_url_in_history )
					)
				)
			)
			{

				$wp_preview_url = self::endpoint . 'server/viewbyid/' . $page_id;

				if( $app_page_url != $wp_page_url )
				{
					$force_url_change = false;
					$page_url = $app_page_url;
				}
				else
				{
					$force_url_change = true;
					$page_url = $wp_preview_url;
				}

				$data = array
				(
					'user_id' => get_option( 'slpages.user_id' ),
					'plugin_hash' => get_option( 'slpages.plugin_hash' ),
					'page_id' => $page_id,
					'url' => $page_url,
					'secure' => is_ssl(),
					'wp_post_id' => $post->ID,
					'wp_delete' => true,
					'force_url_change' => $force_url_change
				);

				try
				{
					$this->updatePageDetails( $data );
					//slpagesIO::addNotice( sprintf( __( 'Please delete the same page from your Sunny Landing Pages application\'s dashboard.' ), $page_id ) );
				}
				catch( slpagesApiCallException $e )
				{
					error_log( $e->getMessage() );
					slpagesIO::addNotice( __( 'There was a problem with changing page status in Sunny Landing Pages application:' ) . ' ' . $e->getMessage() , 'error' );
					wp_redirect( admin_url( 'edit.php?post_type=slpages_post' ) );
					exit();
				}
			}
		}
		//page is created by different app user or was deleted in app. It should be removed from WP, but no app update is required.
		else
		{
			slpagesIO::addNotice( sprintf( __( 'Page that you are removing from WordPress (Sunny Landing Pages ID: %s) doesn\'t exist in your Sunny Landing Pages application\'s dashboard. It could have been deleted from app or created by another user. Deleting this page from WordPress won\'t affect application\'s dashboard.' ), $page_id ) );
		}
	}

	// Add the Meta Box
	public function addCustomMetaBox()
	{
		self::getInstance()->includes[ 'service' ]->silentUpdateCheck();
		$this->editStylesAndScripts();

		add_meta_box
		(
			'slpages_meta_box',
			'Configure your Sunny Landing Pages',
			array( &$this, 'showCustomMetaBox' ),
			'slpages_post',
			'normal',
			'high'
		);
	}

	// The Callback
	public function showCustomMetaBox()
	{
		global $post;

		if( !self::getInstance()->includes[ 'main' ]->getUserId() )
		{
			//slpagesIO::addNotice( __( sprintf( 'To get started, connect your sunnylandingpages.com account on the <a href="%s"> Settings page</a> </br><a target="_blank" href="https://sunnylandingpages.com/blog/how-to-build-a-landing-page-for-wordpress/">Read complete steps </a> or <a target="_blank" href="https://www.youtube.com/watch?v=Xk2oAURYbsQ">view the video guide</a> on how to create and publish a landing page to your wordpress site.', SLPAGES_PLUGIN_SETTINGS_URI ) ), 'notice-warning' );
			self::getInstance()->includes[ 'admin' ]->removeEditPage();
			return false;
		}

		// Field Array
		$field = array
		(
			'label' => 'My Page',
			'desc'  => 'Select from your pages.',
			'id'    => 'slpages_my_selected_page',
			'type'  => 'select',
			'options' => array()
		);

		try
		{
			$pages = self::getInstance()->includes[ 'page' ]->loadMyPages();
		}
		catch( Exception $e )
		{
			self::getInstance()->includes[ 'admin' ]->error_message = $e->getMessage();
			self::getInstance()->includes[ 'admin' ]->getErrorMessageHTML();
			self::getInstance()->includes[ 'admin' ]->removeEditPage();
			return false;
		}

		if ( !$pages )
		{
			echo __( 'No pages pushed to your wordpress. Please go to your <a href="http://sunnylandingpages.com/account/dashboard" target="_blank">Sunny Landing Pages</a> and push some pages.' );
			return;
		}

		if ( $pages === false )
		{
			self::getInstance()->includes[ 'admin' ]->error_message = __( 'You haven\'t published any Sunny Landing Pages page to Wordpress yet' );
			self::getInstance()->includes[ 'admin' ]->getErrorMessageHTML();
			self::getInstance()->includes[ 'admin' ]->removeEditPage();
			return false;
		}

		foreach( $pages as $key => $page )
		{
			$field['options'][ $page->id ] = array
			(
				'label' => $page->title,
				'value' => $page->id
			);
		}

		$isFrontPage = self::getInstance()->includes[ 'page' ]->isFrontPage( $post->ID );
		$is_not_found_page = self::getInstance()->includes[ 'page' ]->is404Page( get_the_ID() );
		$meta = get_post_meta( $post->ID, 'slpages_my_selected_page', true );
		$meta_slug = get_post_meta( $post->ID, 'slpages_slug', true );
		$missing_slug = ( self::getInstance()->includes[ 'main' ]->isPageModeActive( 'edit' ) && $meta_slug == '' && !$isFrontPage );

		$delete_link = get_delete_post_link( $post->ID );

		$slpages_post_type = null;
		$redirect_method = 'http';

		if ( $isFrontPage )
		{
			$slpages_post_type = 'home';
		}
		elseif( $is_not_found_page )
		{
			$slpages_post_type = '404';
		}

		$form = self::getInstance()->includes[ 'view' ];
		$form->init( SLPAGES_PLUGIN_DIR .'/includes/templates/slpages/edit.php' );
		$form->slpages_post_type = $slpages_post_type;
		$form->user_id = self::getInstance()->includes[ 'main' ]->getUserId();
		$form->field = $field;
		$form->meta = $meta;
		$form->meta_slug = $meta_slug;
		$form->missing_slug = $missing_slug;
		$form->redirect_method = $redirect_method;
		$form->delete_link = $delete_link;
		$form->is_page_active_mode = self::getInstance()->includes[ 'main' ]->isPageModeActive('edit');
		$form->slpages_name = get_post_meta
		(
			$post->ID,
			'slpages_name',
			true
		);

		$form->plugin_file = plugin_basename( __FILE__ );
		echo $form->fetch();
	}

	public function updatePageDetails( $details )
	{
		self::getInstance()->includes[ 'api' ]->slpagesApiCall( 'server/updatenewpage', $details );
	}

	public function saveCustomMeta( $post_id, $post )
	{
		if ( !isset( $_POST[ 'slpages_meta_box_nonce' ] ) || !wp_verify_nonce( $_POST[ 'slpages_meta_box_nonce' ], basename( __FILE__ ) ) )
		{
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return $post_id;
		}

		if ( $post->post_type != 'slpages_post' )
		{
			return $post_id;
		}

		$old = get_post_meta( $post_id, 'slpages_my_selected_page', true );
		$new = slpagesIO::getVar( 'slpages_my_selected_page', 0, 'post' );
		$slpages_page_id = $new;
		$slpages_name = slpagesIO::getVar( 'slpages_name', '', 'post' );
		$slpages_post_type = slpagesIO::getVar( 'post-type', '', 'post' );
		$slpages_slug = slpagesIO::getVar( 'slpages_slug', '', 'post' );

		$front_page = false;
		$not_found_page = false;
		 
		switch ( $slpages_post_type )
		{
			case '':
			break;

			case 'home':
				$front_page = true;
				$slpages_slug = $_POST[ 'slpages_slug' ] = '';
			break;

			case '404':
				$not_found_page = true;
				$slpages_slug = $_POST[ 'slpages_slug' ] = self::getInstance()->includes[ 'page' ]->getRandomSlug();
			break;

			default:
			break;
		}

		if( !$this->checkPageData() )
		{
			$this->setUpdateStatus( self::UPDATE_FAILED );

			return $post_id;
		}

		try
		{
			$data = array
				(
					'user_id' => get_option( 'slpages.user_id' ),
					'plugin_hash' => get_option( 'slpages.plugin_hash' ),
					'page_id' => slpagesIO::getVar( 'slpages_my_selected_page', 0, 'post' ),
					'url' => str_replace( 'http://', '', str_replace( 'https', 'http', get_option( 'home' ) . '/'. rtrim( slpagesIO::getVar( 'slpages_slug', '', 'post' ), '/' ) ) ),
					'secure' => is_ssl(),
					'wp_post_id' => $post_id
				);
			$this->updatePageDetails($data);
			
			slpagesIO::writeDiagnostics( 'updatePageDetails successfull' );
			$wp_preview_url = self::endpoint . 'server/viewbyid/id?=' . $page_id;
			
			// HOME PAGE
			$old_front = self::getInstance()->includes[ 'page' ]->getFrontslpages();
			$old_front_page_id = get_post_meta( $old_front, 'slpages_my_selected_page', true );
			
			if ( $front_page )
			{
				$this->setFrontslpages( $post_id );
				
				if( $old_front_page_id && $old_front_page_id != $new )
				{
					$this->updatePageDetails
					(
						array
						(
							'user_id' => get_option( 'slpages.user_id' ),
							'plugin_hash' => get_option( 'slpages.plugin_hash' ),
							'page_id' => $old_front_page_id,
							'url' => $wp_preview_url,
							'secure' => is_ssl()
						)
					);
					
					slpagesIO::writeDiagnostics( 'Old homepage (' . $old_front_page_id . ') updated in app' );
				}
			}
			elseif ( $old_front == $post_id )
			{
				$this->setFrontslpages( false );
			}
			
			// 404 PAGE
			$old_nf = self::getInstance()->includes[ 'page' ]->get404slpages();
			$old_nf_page_id = get_post_meta( $old_nf, 'slpages_my_selected_page', true );
			
			if ( $not_found_page )
			{
				$this->set404slpages( $post_id );

				if( $old_nf_page_id && $old_nf_page_id != $new )
				{
					$this->updatePageDetails
					(
						array
						(
							'user_id' => get_option( 'slpages.user_id' ),
							'plugin_hash' => get_option( 'slpages.plugin_hash' ),
							'page_id' => $old_nf_page_id,
							'url' => $wp_preview_url,
							'secure' => is_ssl()
						)
					);
					
					slpagesIO::writeDiagnostics( 'Old 404 (' . $old_nf_page_id . ') updated in app' );
				}
			}
			elseif ( $old_nf == $post_id )
			{
				$this->set404slpages( false );
			}
			
			if ( $new && $new != $old )
			{
				update_post_meta( $post_id, 'slpages_my_selected_page', $new );
				update_post_meta( $post_id, 'slpages_name', $slpages_name );
			}

			$testreturn = $this->setPageScreenshot( $slpages_page_id );
			
			// Custom URL
			$old = get_post_meta( $post_id, 'slpages_slug', true );
			$new = trim( strip_tags( rtrim( $slpages_slug, '/' ) ) );

			if ( $new != $old )
			{
				update_post_meta( $post_id, 'slpages_slug', $new );
			}

			delete_site_transient( 'slpages_page_html_cache_' . $new );
			$this->setUpdateStatus( self::UPDATE_OK );
			//slpagesIO::addNotice( __( 'Page updated. ' ), 'success' );
			
		}
		catch( slpagesApiCallException $e )
		{
			$this->setUpdateStatus( self::UPDATE_FAILED );
			slpagesIO::addNotice( __( 'Page could not be updated. ' ), 'error' );
		}
	}

	public function removeMetaBoxes()
	{
		global $wp_meta_boxes;

		$boxes_for_display = array
		(
			'slpages_meta_box',
			'submitdiv'
		);

		foreach ( $wp_meta_boxes as $k => $v )
		{
			foreach ( $wp_meta_boxes[ $k ] as $j => $u )
			{
				foreach ( $wp_meta_boxes[ $k ][ $j ] as $l => $y )
				{
					foreach ( $wp_meta_boxes[ $k ][ $j ][ $l ] as $m => $y )
					{
						if ( !in_array( $m, $boxes_for_display) )
						{
							unset( $wp_meta_boxes[ $k ][ $j ][ $l ][ $m ] );
						}
					}
				}
			}
		}

		return;
	}

	// Validate the Data
	public function validateCustomMeta( $post_id, $post )
	{

		if ( !isset( $_POST[ 'slpages_meta_box_nonce' ] ) || !wp_verify_nonce( $_POST[ 'slpages_meta_box_nonce' ], basename( __FILE__ ) ) )
		{
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return $post_id;
		}

		if ( $post->post_type != 'slpages_post' )
		{
			return $post_id;
		}


		$slug = get_post_meta( $post_id, 'slpages_slug' );
		$isFrontPage = self::getInstance()->includes[ 'page' ]->isFrontPage( $post_id );
		$invalid_url = empty( $slug ) && !$isFrontPage;
		$post_status = slpagesIO::getVar( 'post_status', null, 'post' );

		// on attempting to publish - check for completion and intervene if necessary
		if ( ( isset( $_POST[ 'publish' ] ) || isset( $_POST[ 'save' ] ) ) && ( $post_status == 'publish' || $post_status == 'slpages_invalid' ) )
		{
			// don't allow publishing while any of these are incomplete
			$status = null;
			if ( $invalid_url || $this->getUpdateStatus() != self::UPDATE_OK )
			{
				$status = 'slpages_invalid';
			}
			else
			{
				$status = 'publish';
			}

			global $wpdb;

			$wpdb->update
			(
				$wpdb->posts,
				array
				(
					'post_status' => $status
				),
				array
				(
					'ID' => $post_id
				)
			);
		}
	}

	public function updateMetaValueByslpagesPageId( $slpages_page_id, $meta_key, $meta_value )
	{
		global $wpdb;

		if ( empty( $slpages_page_id ) || empty( $meta_key ) || empty( $meta_value ) )
		{
			return false;
		}

		$post_ids = self::getInstance()->includes[ 'page' ]->getPostIdsByslpagesPageId( $slpages_page_id );

		if ( !$post_ids )
		{
			return false;
		}

		foreach( $post_ids as $post )
		{
			update_post_meta( $post->post_id, $meta_key, $meta_value );
		}
	}

	public function setPageScreenshot( $slpages_page_id )
	{
		$page = self::getInstance()->includes[ 'page' ]->getMyPage( $slpages_page_id );

		if ( !isset( $page->configuration ) )
		{
			return false;
		}

		//todo
		$page_configuration = $page->configuration;
		
		if ( !isset( $page_configuration['screenshot'] ) )
		{
			return false;
		}
		
		$this->updateMetaValueByslpagesPageId( $slpages_page_id, 'slpages_page_screenshot_url', $page_configuration['screenshot'] );
	}

	public function checkPageData( $on_save_only = true, $add_notices = true )
	{

		if ( !$this->isSavePerformed() && $on_save_only )
		{
			return true;
		}

		global $post;

		$slpages_page_id = slpagesIO::getVar( 'slpages_my_selected_page', 0, 'post' );
		$slpages_name = slpagesIO::getVar( 'slpages_name', '', 'post' );
		$slpages_post_type = slpagesIO::getVar( 'post-type', '', 'post' );
		$slpages_slug = slpagesIO::getVar( 'slpages_slug', '', 'post' );
		$success = true;

		switch( $slpages_post_type )
		{
			//Normal Page
			case '':
				//check if url is correct
				$page_url = self::getInstance()->includes[ 'page' ]->getPageUrl( false, $slpages_slug );

				if( $success && filter_var( $page_url, FILTER_VALIDATE_URL ) === false )
				{
					if( $add_notices )
					{
						slpagesIO::addNotice( '<strong>' . __( 'Custom URL' ) . '</strong>' . __( ' is incorrect, please use valid URL for that field.' ), 'error' );
					}

					$success = false;

					break;

				}

				//check if no ditectory exists
				$test_path = get_home_path() . $slpages_slug;

				if( $success && is_dir( $test_path ) )
				{
					if( $add_notices )
					{
						slpagesIO::addNotice( sprintf( '<strong>' . __( 'Custom URL' ) . '</strong>' . __( ' is incorrect, it leads to an existing directory (%s).' ), $test_path ), 'error' );
					}

					$success = false;

					break;
				}

				//check if url is avalible (not taken by post or page)
				$wp_post_id = $this->getPostIdByUrl( $page_url );

				if( $success && $wp_post_id && $wp_post_id != $post->ID )
				{
					if( $add_notices )
					{
						$wp_post_edit_url = get_edit_post_link( $wp_post_id );
						slpagesIO::addNotice( sprintf( __( 'Selected <strong>Custom URL</strong> (%s) is already in use. You can <a href="%s">edit the post</a> and change permalink or change custom slpages URL.' ), $page_url, $wp_post_edit_url ), 'error' );
					}

					$success = false;

					break;
				}

				//check if slug is taken by WP category or tag
				$term_name = null;
				$term_id = $this->getTermIdByUrl( $page_url, $term_name );

				if( $success && $term_id )
				{
					if( $add_notices )
					{
						$wp_term_edit_url = get_admin_url() . '/edit-tags.php?action=edit&taxonomy=post_tag&tag_ID=' . $term_id . '&post_type=post';
						slpagesIO::addNotice( sprintf( __( 'Selected <strong>Custom URL</strong> (%s) is already in use by WordPress %s. You can <a href="%s">edit the %s</a> and change permalink or change custom slpages URL.' ), $page_url, $term_name, $wp_term_edit_url, $term_name ), 'error' );
					}

					$success = false;

					break;
				}

			break;

			case '404':
				//check if url is avalible
				$page_url = self::getInstance()->includes[ 'page' ]->getPageUrl( false, $slpages_slug );
				$wp_post_id = $this->getPostIdByUrl( $page_url );

				if( $wp_post_id && $wp_post_id != $post->ID )
				{
					if( $add_notices )
					{
						slpagesIO::addNotice( __( 'Sunny Landing Pages plugin has generated random page slug during save process, but it appears to be taken. Please try publishing the page once again to generate another page slug.' ), 'error' );
					}

					$success = false;
				}

			break;

			case 'home':

				if( $slpages_slug != '' )
				{
					if( $add_notices )
					{
						slpagesIO::addNotice( __( 'There was a problem during update. Please make sure that you have JavaScript enabled and try again.' ), 'error' );
					}

					$success = false;
				}

			break;
		}

		return $success;
	}

	public function setUpdateStatus( $status = self::UPDATE_OK )
	{
		update_option( 'slpages_last_save_status', $status );
	}

	public function getUpdateStatus()
	{
		return get_option( 'slpages_last_save_status' , 'undefined' );
	}

	private function isSavePerformed()
	{
		if ( ( isset( $_POST[ 'publish' ] ) || isset( $_POST[ 'save' ] ) ) && slpagesIO::getVar( 'post_status', '', 'post' ) == 'publish' )
		{
			return true;
		}
	}

	public function getPostIdByUrl( $url, $post_id = null, &$is_post = null)
	{
		//check if page or post with the same URL exist in WP
		$wp_post_id = url_to_postid( $url );

		if( $wp_post_id )
		{
			if( isset( $is_post ) )
			{
				$is_post = true;
			}

			return $wp_post_id;
		}

		//check if page with the same URL exist in slpages plugin
		global $wpdb;

		$slpages_slug = trim( str_replace( home_url(), '', $url ), '/' );
		$sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'slpages_slug' AND meta_value = '%s'", $slpages_slug );
		$results = $wpdb->get_results( $sql, ARRAY_A );

		if( !empty( $results ) )
		{
			if( isset( $is_post ) )
			{
				$is_post = false;
			}

			return isset( $results[ 0 ][ 'post_id' ] ) ? $results[ 0 ][ 'post_id' ] : 0;
		}

		return 0;
	}

	public function getTermIdByUrl( $url, &$term_name )
	{
		$matches = null;
		$slpages_slug = trim( str_replace( home_url(), '', $url ), '/' );
		$terms = array( 'category', 'tag' );

		foreach( $terms as $term )
		{
			$term_base = get_option( $term . '_base', '' );
			$pattern = '/^' . $term_base . '(.*)$/';

			if( preg_match( $pattern, $slpages_slug, $matches ) )
			{
				$term_slug = trim( $matches[ 1 ], '/' );

				if( $term == 'tag' )
				{
					$wp_term_name = 'post_tag';
				}
				else
				{
					$wp_term_name = $term;
				}

				$term_obj = get_term_by( 'slug', $term_slug, $wp_term_name );

				if( $term_obj !== false )
				{
					$term_name = $term;

					return $term_obj->term_id;
				}
			}
		}

		$term_name = '';

		return 0;
	}

	public static function setFrontslpages( $id )
	{
		update_option( 'slpages_front_page_id', $id );
	}

	public static function set404slpages( $id )
	{
		update_option( 'slpages_404_page_id', $id );
	}

	public static function setRedirectMethod( $val )
	{
		update_option( 'slpages_redirect_method', $val );
	}

	public function editStylesAndScripts()
	{
		global $post;

		if( $post->post_type != 'slpages_post')
		{
			return;
		}

		$js_files = scandir( SLPAGES_PLUGIN_DIR . '/assets/js' );
		$js_data = array
		(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		);

		if( is_admin() )
		{
			foreach( $js_files as $js_file )
			{
				if ( $js_file == '..' || $js_file == '.' || strpos( $js_file, '.js' ) === false )
				{
					continue;
				}

				wp_register_script( str_replace( '.js', '', $js_file ), SLPAGES_PLUGIN_URI . '/assets/js/' . $js_file, array( 'jquery' ) );
				wp_localize_script( str_replace( '.js', '', $js_file ), 'slpages', $js_data );
				wp_enqueue_script( str_replace( '.js', '', $js_file ) );
			}

			$css_files = scandir( SLPAGES_PLUGIN_DIR . '/assets/css' );

			wp_enqueue_style( 'admin_slpages', SLPAGES_PLUGIN_URI . '/assets/css/admin_slpages.css' );
		}
	}
}
