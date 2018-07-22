<?php

class SlpagesService extends slpages
{
	public function init()
	{
		$this->registerAutoUpdate();
	}

	public static function compatibility()
	{
		global $wp_version;

		// Check wordpress version
		if ( version_compare( self::wp_version_required, $wp_version, '>' ) )
		{
			return __( 'slpages plugin requires Wordpress minimum version of ' . self::wp_version_required, 'slpages' );
		}

		if ( version_compare( self::php_version_required, phpversion(), '>' ) )
		{
			return __( 'slpages requires PHP minimum version of ' . self::php_version_required, 'slpages' );
		}

		return true;
	}

	public function checkForPluginUpdate( $option, $cache = true )
	{ 
		$response = $this->getCachedService( 'update-check', self::cached_service_lifetime );

		if ( !$response )
		{
			return $option;
		}

		$current_version = $this->pluginGet( 'Version' );

		if ( $current_version == $response->data[ 'current-version' ] )
		{
			return $option;
		}

		if ( version_compare( $current_version, $response->data[ 'current-version' ], '>' ) )
		{
			return $option; // you have the latest version
		}

		$plugin_path = 'slpages/slpages.php';

		if( empty( $option->response[ $plugin_path ] ) )
		{
			$option->response[ $plugin_path ] = new stdClass();
		}

		$option->response[ $plugin_path ]->url = 'https://sunnylandingpages.com'; //$response->data[ 'download-url' ];
		$option->response[ $plugin_path ]->slug = 'slpages';
		$option->response[ $plugin_path ]->package = $response->data[ 'download-url' ];
		$option->response[ $plugin_path ]->new_version = $response->data[ 'current-version' ];
		$option->response[ $plugin_path ]->id = "0";

		return $option;
	}

	/**
	 * Exclude from WP updates
	 **/
	public static function excludeUpdates( $r, $url )
	{
		if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check') )
		{
			return $r; // Not a plugin update request. Bail immediately.
		}

		if( $r && $r['body'] && $r['body']['plugins'] )
		{
			$plugins = unserialize( $r['body']['plugins'] );

			if( !$plugins )
			{
				return null;
			}

			unset( $plugins->plugins[ 'slpages' ] );
			unset( $plugins->active[ array_search( 'slpages', $plugins->active ) ] );

			$r[ 'body' ][ 'plugins' ] = serialize( $plugins );

			return $r;
		}
	}

	public function registerAutoUpdate()
	{
		// plugin update information
		add_filter( 'plugins_api', array( &$this, 'updateInformation' ), 10, 3 );

		// exclude from official updates
		add_filter( 'http_request_args', array( &$this, 'excludeUpdates' ), 5, 2 );

		// check for update twice a day (same schedule as normal WP plugins)
		add_action( 'lp_check_event', array( &$this, 'checkForUpdates' ) );
		add_filter( "transient_update_plugins", array( &$this, 'checkForPluginUpdate' ) );
		add_filter( "site_transient_update_plugins", array( &$this, 'checkForPluginUpdate' ) );

		// check and schedule next update
		if ( !wp_next_scheduled( 'lp_check_event' ) )
		{
			wp_schedule_event( current_time( 'timestamp' ), 'twicedaily', 'lp_check_event' );
		}
		// remove cron task upon deactivation
		register_deactivation_hook( __FILE__, array( &$this, 'checkForDeactivation' ) );
	}

	public function updateInformation( $false, $action, $args )
	{
		// Check if this plugins API is about this plugin
		if ( empty( $args ) || !isset( $args->slug ) || $args->slug != 'slpages' )
		{
			return $false;
		}

		$response = $this->getCachedService( 'update-check', self::cached_service_lifetime );

		if ( !$response )
		{
			return $false;
		}

		$info_response = new stdClass();
		$info_response->slug = 'slpages';
		$info_response->plugin_name = 'slpages';
		$info_response->sections = $response->data[ 'sections' ];
		$info_response->version = $response->data[ 'current-version' ];
		$info_response->author = $response->data[ 'author' ];
		$info_response->tested = $response->data[ 'tested' ];
		$info_response->homepage = $response->data[ 'homepage' ];
		$info_response->downloaded = $response->data[ 'downloaded' ];

		return $info_response;
	}

	public function getCachedService( $url, $lifetime = null )
	{
		$hash = 'slpages.cached-service.'. md5( $url );

		if( $lifetime )
		{
			$cached_response_object = get_option( $hash, false );

			if( $cached_response_object && !is_object( $cached_response_object ) )
			{
				$cached_response_object = unserialize( $cached_response_object );
			}
		}

		if( !$cached_response_object || time() - $cached_response_object->time - $lifetime > 0  )
		{
			try
			{
				$response = self::getInstance()->includes[ 'api' ]->slpagesApiCall( $url );

				$cached_response_object = new stdClass();
				$cached_response_object->response = $response;
				$cached_response_object->time = time();

				add_option( $hash, false );
				update_option
				(
					$hash,
					serialize( $cached_response_object ),
					null,
					false
				);
			}
			catch( slpagesApiCallException $e )
			{
			}
		}

		return $cached_response_object->response;
	}

	public function pluginGet( $variable )
	{
		if( self::getInstance()->plugin_details === false )
		{
			if ( !function_exists( 'get_plugins' ) )
			{
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$data = get_plugins( '/slpages' );
			self::getInstance()->plugin_details = $data[ 'slpages.php' ];
		}

		return self::getInstance()->plugin_details[ $variable ];
	}

	protected function checkForUpdates( $full = false )
	{
		if ( defined( 'WP_INSTALLING' ) )
		{
			return false;
		}

		$response = $this->getCachedService( 'update-check', self::cached_service_lifetime );

		if( $full === true )
		{
			return $response;
		}

		if( !$response )
		{
			return false;
		}

		$current_version = $this->pluginGet( 'Version' );

		if ( $current_version == $response->data[ 'current-version' ] )
		{
			return false;
		}

		if ( version_compare( $current_version, $response->data[ 'current-version' ], '>' ) )
		{
			return false;
		}

		return $response->data[ 'current-version' ];
	}

	public function silentUpdateCheck()
	{
		return;
		$response = $this->checkForUpdates( true );

		if ( !$response )
		{
			slpagesIO::addNotice( __( 'Error while checking for update. Can\'t reach Sunny Landing Pages server. Please check your connection.' ), 'error' ) ;
			return;
		}

		if ( isset( $response->result ) && $response->result == 'ok' )
		{
			slpagesIO::addNotice( $response->message, 'error' );
			return;
		}

		$vew_version = $response->data[ 'current-version' ];
		$url = $response->data[ 'download-url' ];
		$current_version = $this->pluginGet( 'Version' );

		if ( $current_version == $vew_version || version_compare( $current_version, $vew_version, '>' ) )
		{
			return;
		}

		$plugin_file = 'slpages/slpages.php';
		$upgrade_url = wp_nonce_url
		(
			'update.php?action=upgrade-plugin&amp;plugin=' . urlencode( $plugin_file ),
			'upgrade-plugin_' . $plugin_file
		);

		$message = 'There is a new version of Sunny Landing Pages plugin available! ( ' . $vew_version . ' )<br>You can <a href="' . $upgrade_url . '">update</a> to the latest version automatically or <a href="' . $url . '">download</a> the update and install it manually.';
		self::getInstance()->includes[ 'admin' ]->showMessage( true, $message );
	}

	public function isServicesRequest()
	{
		if( strpos( $_SERVER[ 'REQUEST_URI' ], 'slpages-proxy-services' ) !== false )
		{
			return true;
		}

		return false;
	}

	public function stripSlashesGpc( &$value )
	{
		$value = stripslashes( $value );
	}

	public function processProxyServices()
	{
		$url = $_GET[ 'url' ];
		$url = self::endpoint . $url;
		array_walk_recursive( $_POST, array( $this, 'stripSlashesGpc' ) );
		slpagesIO::writeDiagnostics( $url, 'Proxy Services Endpoint' );

		if ( isset( $_POST ) && !empty( $_POST ) )
		{
			$_POST[ 'user_ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
		}

		$data = array(
				'method' => $_POST ? 'POST' : 'GET',
				'timeout' => 70,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $_POST,
				'cookies' => array()
		);

		$response = wp_remote_post
		(
			$url,
			$data
		);

		slpagesIO::writeDiagnostics( $data, 'Proxy Services Data' );
		slpagesIO::writeDiagnostics( $response, 'API response' );

		if ( is_wp_error( $response ) )
		{
			$error_message = $response->get_error_message();

			if ( !empty( $error_message ) )
			{
				throw new slpagesApiCallException( $error_message );
			}
			else
			{
				throw new slpagesApiCallException( '500 Internal Server Error' );
			}
		}

		ob_start();
		ob_end_clean();
		header( 'Content-Type: text/json; charset=UTF-8' );
		echo trim( slpagesIO::getVar( 'body', '', $response ) );
		status_header( 200 );

		exit;
	}
}
