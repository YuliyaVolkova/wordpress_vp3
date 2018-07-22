<?php

class SlpagesAdmin extends slpages
{
	var $error_message;

	public function init()
	{
		add_action( 'admin_enqueue_scripts', array( &$this, 'customizeAdministration' ), 11 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'slpagesPostUpgradeTasks' ) );
		add_action( 'admin_menu', array( &$this, 'pluginOptionsMenu' ), 11 );
		add_filter( 'plugin_action_links', array( &$this, 'addPluginActionLink' ), 10, 2 );
		add_action( 'init', array( &$this, 'setCrossOriginProxyServicesIfNotExists' ), 10 );
		add_action( 'admin_notices', array( &$this,'SlpagesAdminNotices' ) );
		add_action( 'admin_init', array( &$this, 'slpagesDownloadLog' ), 1 );
	}

	public function slpagesDownloadLog()
	{
		if ( is_admin() && slpagesIO::getVar( 'option', '', 'get' ) == 'downloadslpagesLog' )
		{
			try
			{
				$output = self::getInstance()->includes[ 'log' ]->getLogHTML( false );
				$gzoutput = gzencode( $output );
				$sitename = get_bloginfo( 'name' );
				$filename = sanitize_title( $sitename . '-slpages-diagnostics-' . date("Ymd-His") ) . '.idd';
				ob_clean();
				header( "Content-type: application/x-gzip" );
				header( "Content-Encoding: gzip");
				header( "Content-Length: " . strlen( $gzoutput ) );
				header( "Content-Disposition: attachment; filename=" . $filename );
				header( "Cache-Control: no-cache, no-store, max-age=0, must-revalidate" );
				header( "Pragma: no-cache" );
				header( "Expires: 0" );
				echo $gzoutput;
				ob_end_flush();

				exit();
			}
			catch( Exception $e )
			{
				slpagesIO::addNotice( $e->getMessage(), 'error' );
			}
		}
	}

	public function slpagesPostUpgradeTasks()
	{
		$slpages_db_version = floatval( get_option( 'slpages_db_version', 0 ) );
		$slpages_plugin_version = floatval( self::getInstance()->includes[ 'service' ]->pluginGet( 'Version' ) );

		if( $slpages_db_version < $slpages_plugin_version )
		{
			$slpages_posts = self::getInstance()->includes[ 'page' ]->getMyPosts();
			$front_id = self::getInstance()->includes[ 'page' ]->getFrontslpages();
			$page_404_id = self::getInstance()->includes[ 'page' ]->get404slpages();

			$success = true;
			$do_update = false;

			foreach( $slpages_posts as $post_id => $slpages_post )
			{
				$do_update = false;
				$slpages_id = slpagesIO::getVar( 'slpages_my_selected_page', 0, $slpages_post );

				if( $slpages_id && self::getInstance()->includes[ 'page' ]->is404Page( $post_id ) && !$this->checkRandomPattern( slpagesIO::getVar( 'slpages_slug', '', $slpages_post ) ) )
				{
					$slug = self::getInstance()->includes[ 'page' ]->getRandomSlug();
					$do_update = true;
				}
				else if( self::getInstance()->includes[ 'page' ]->isFrontPage( $post_id ) && slpagesIO::getVar( 'slpages_slug', '', $slpages_post ) != '' )
				{
					$slug = '';
					$do_update = true;
				}

				if( $do_update )
				{
					try
					{
						self::getInstance()->includes[ 'edit' ]->updatePageDetails
						(
							array
							(
								'user_id' => get_option( 'slpages.user_id' ),
								'plugin_hash' => get_option( 'slpages.plugin_hash' ),
								'page_id' => $slpages_id,
								'url' => str_replace( 'http://', '', str_replace( 'https', 'http', get_option( 'home' ) . '/'. rtrim( $slug, '/' ) ) ),
								'secure' => is_ssl()
							)
						);

						update_post_meta( $post_id, 'slpages_slug', $slug );
					}
					catch( slpagesApiCallException $e )
					{
						slpagesIO::addNotice( $e->getMessage(), 'error' );
						$success = false;
					}
				}
			}

			if( $success )
			{
				update_option( 'slpages_db_version', $slpages_plugin_version );
			}
			else
			{
				slpagesIO::addNotice( sprintf( __( 'There was an error during automatic page update. Try refreshing this page, and contact our <a target="_blank" href="%s">Customer Support</a> team if the problem persists.' ), esc_url( self::slpages_support_link ) ), 'error' );
			}
		}
	}

	private function checkRandomPattern( $slug = '' )
	{

		$prefix = slpagesPage::RANDOM_PREFIX;
		$sufix_length = slpagesPage::RANDOM_SUFIX_LENGTH;
		$sufix_set = slpagesPage::RANDOM_SUFIX_SET;
		$pattern = '/^' . $prefix . '[' . $sufix_set . ']{' . $sufix_length . '}$/';

		return preg_match( $pattern, $slug );
	}

	public function SlpagesAdminNotices()
	{
		$notices = get_option( 'slpages_notices' );

		if ( !empty( $notices ) )
		{
			foreach ( $notices as $notice )
			{
				echo $notice;
			}

			delete_option( 'slpages_notices' );
		}
	}

	public function setCrossOriginProxyServicesIfNotExists()
	{
		$cross_origin_proxy_services = get_option( 'slpages.cross_origin_proxy_services' );

		if ( $cross_origin_proxy_services === false )
		{
			update_option( 'slpages.cross_origin_proxy_services', 1 );
		}
	}

	public function redirection( $location )
	{
		echo '<script>slpages_redirection( "' . $location . '" );</script>';
	}

	public function removeEditPage()
	{
		echo '<script>slpages_remove_edit();</script>';
	}

	public function showMessage( $not_error, $message )
	{
		$this->error_message = $message;

		if ( $not_error )
		{
			add_action( 'admin_notices', array( &$this, 'getMessageHTML' ) );
		}
		else
		{
			add_action( 'admin_notices', array( &$this, 'getErrorMessageHTML' ) );
		}
	}

	public function getErrorMessageHTML()
	{
		$form = self::getInstance()->includes[ 'view' ];
		$form->init( SLPAGES_PLUGIN_DIR .'/includes/templates/slpages/error.php' );
		$form->error_class = 'error';
		$form->msg = $this->error_message;
		echo $form->fetch();
	}

	public function getMessageHTML()
	{
		$form = self::getInstance()->includes[ 'view' ];
		$form->init( SLPAGES_PLUGIN_DIR .'/includes/templates/slpages/error.php' );
		$form->error_class = 'updated';
		$form->msg = $this->error_message;
		echo $form->fetch();
	}

	public function customizeAdministration()
	{
		global $post_type;
	}

	public function getUrlVersion()
	{
		return '?url-version=' . slpages::getInstance()->includes[ 'service' ]->pluginGet( 'Version' );
	}

	public function showSettingsPage()
	{
		$user_id = get_option( 'slpages.user_id' );
		$form = slpages::getInstance()->includes[ 'view' ];
		$form->init( SLPAGES_PLUGIN_DIR .'/includes/templates/slpages/settings.php' );
		$form->plugin_file = plugin_basename( SLPAGES_PLUGIN_FILE );
		$form->user_id = $user_id;
		$form->error = false;
		$form->diagnostics_download_link = get_admin_url() . '?option=downloadslpagesLog';

		if( $_POST && !$user_id && $_POST[ 'email' ] )
		{
			try
			{
				$response = slpages::getInstance()->includes[ 'api' ]->slpagesApiCall
				(
					'api/authenticate',
					array
					(
						'email' => base64_encode( trim( $_POST[ 'email' ] ) ),
						'password' => base64_encode( trim( $_POST[ 'password' ] ) )
					)
				);
			}
			catch( slpagesApiCallException $e )
			{
				$form->error = $e->getMessage();
			}

			if( isset( $response->error ) && $response->error )
			{
				$form->error = $response->error_message;
			}

			if( isset( $response->success ) && $response->success )
			{
				add_option( 'slpages.user_id', false );
				add_option( 'slpages.plugin_hash', false );
				update_option( 'slpages.user_id', $response->data[ 'user_id' ] );
				update_option( 'slpages.plugin_hash', $response->data[ 'plugin_hash' ] );
				$user_id = $form->user_id = $response->data[ 'user_id' ];
			}
		}

		if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'disconnect' )
		{
			update_option( 'slpages.user_id', false );
			update_option( 'slpages.plugin_hash', false );
			$form->user_id = null;
		}

		if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'cross_origin_proxy_services' )
		{
			update_option( 'slpages.cross_origin_proxy_services', $_POST[ 'cross_origin_proxy_services' ] );
		}

		if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'diagnostics' )
		{
			$diagnostics = slpagesIO::getVar( 'diagnostics', '0', 'post' );
			update_option( 'slpages.diagnostics', $diagnostics );

			if( isset( $_POST[ 'clear_log' ] ) && $_POST[ 'clear_log' ] == 1 )
			{
				slpages::getInstance()->includes[ 'log' ]->clear();
			}
		}

		if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'custom_params' )
		{
			$custom_params_arr = explode( '|', $_POST[ 'custom_params' ] );

			foreach( $custom_params_arr as &$param )
			{
				$param = trim( $param );
			}

			update_option( 'slpages.custom_params', implode( '|', $custom_params_arr ) );
		}

		$form->cross_origin_proxy_services = get_option( 'slpages.cross_origin_proxy_services' );
		$form->diagnostics = get_option( 'slpages.diagnostics' );
		$form->log_counter = slpages::getInstance()->includes[ 'log' ]->getEntryCounter();
		$form->custom_params = get_option( 'slpages.custom_params' );

		if( $user_id )
		{
			try
			{
				$response = slpages::getInstance()->includes[ 'api' ]->slpagesApiCall
				(
					'server/profile',
					array
					(
						'user_id' => $user_id,
						'plugin_hash' => get_option( 'slpages.plugin_hash' )
					)
				);

				$form->user = isset( $response->user ) ? $response->user : null;
			}
			catch( slpagesApiCallException $e )
			{
				$form->error = $e->getMessage();
				slpagesIO::writeDiagnostics( $e->getMessage() );
			}
		}

		echo $form->fetch();
	}

	public function pluginOptionsMenu()
	{
		add_options_page( 'slpages', 'Sunny Landing Pages', 'administrator', SLPAGES_PLUGIN_FILE, array( &$this, 'showSettingsPage' ) );
	}

	/**
	 * Add a link to the settings page from the plugins page
	 */
	public function addPluginActionLink( $links, $file )
	{
		static $this_plugin;

		if( empty( $this_plugin ) ) $this_plugin = plugin_basename( SLPAGES_PLUGIN_FILE );

		if ( $file == $this_plugin )
		{
			$settings_link = '<a href="' . admin_url( 'options-general.php?page=' . $this_plugin ) . '">' . __('Settings', 'slpages') . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	public function formatError( $message, $error_code = null )
	{
		$form = self::getInstance()->includes[ 'view' ];
		$form->init( SLPAGES_PLUGIN_DIR .'/includes/templates/slpages/error-formatted.php' );
		$form->message = $message;
		$form->error_code = $error_code;
		$form->slpages_support_link = self::slpages_support_link;
		$form->slpages_dashboard_link = self::slpages_dashboard_link;
		return $form->fetch();
	}
}
