<?php

class SlpagesApiCallException extends Exception {}

class SlpagesApi extends slpages {

	public function SlpagesApiCall( $service, $data = null, $endpoint = '' )
	{		
		if ( $service === null )
		{
			$service_parts = parse_url( $_SERVER[ 'REQUEST_URI' ] );
			parse_str( $url_parts[ 'query' ], $service_parts );

			if ( isset( $service_parts[ 'url' ] ) )
			{
				$service = $service_parts[ 'url' ];
			}
		}

		$url = self::endpoint . '/user/' . $endpoint . $service;
		
		if($service == 'update-check'){
			return false;
		}
		
		if($service == 'api/authenticate'){
			$url = self::endpoint . '/' . $endpoint . $service;
		}
		
		if($service == 'server/mypages'){
			$url = self::endpoint . '/' . $service;
		}
		
		if($service == 'server/allmypages'){
			$url = self::endpoint . '/' . $service;
		}
		
		if($service == 'server/profile'){
			$url = self::endpoint . '/' . $service;
		}
		
		if($service == 'server/updatepage'){
			$url = self::endpoint . '/' . $endpoint . $service;
		}
		
		if($service == 'server/updatenewpage'){
			$url = self::endpoint . '/' . $endpoint . $service;
		}

		if($service == 'my_stats'){
			$url = self::endpoint . '/server/my_stats';
		}
		
		if($service == 'get-pages'){
			$url = self::endpoint . '/server/pagelist';
		}
		
		slpagesIO::writeDiagnostics( $url, 'API call URL' );
		$current_ver = self::getInstance()->includes[ 'service' ]->pluginGet( 'Version' );

		$body = array
		(
			'service-type' => 'Wordpress',
			'service' => $_SERVER[ 'SERVER_NAME' ],
			'version' => $current_ver,
			'user_id' => get_option( 'slpages.user_id' ),
			'data' => $data
		);
		
		
		slpagesIO::writeDiagnostics( $body, 'API call message body' );

		$response = wp_remote_post
		(
			$url,
			array
			(
				'method' => 'POST',
				'timeout' => 70,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $body,
				'cookies' => array()
			)
		);
		
		slpagesIO::writeDiagnostics( $response, 'API Response' );

		if ( is_wp_error( $response ) )
		{
			$error_message = $response->get_error_message();
			slpagesIO::writeDiagnostics( $error_message, 'API error message' );

			if ( !empty( $error_message ) )
			{
				throw new SlpagesApiCallException( $error_message );
			}
			else
			{
				throw new SlpagesApiCallException( '500 Internal Server Error' );
			}
		}

		$res = json_decode( $response[ 'body' ], true );

		if ( !is_array( $res ) && !is_object( $res ) )
		{
			slpagesIO::writeDiagnostics( 'json_decode( $response[ \'body\' ], true ) unsuccessfull - SlpagesApiCall aborted' );

			throw new SlpagesApiCallException( $url.' slpages Services returned empty response.' );
		}

		$data = new stdClass();

		foreach ( $res as $key => $val )
		{
			$data->$key = $val;
		}

		if ( $service == 'update-check' )
		{
			set_site_transient( 'slpages_latest_version', $data, 60 * 60 * 12 );
		}

		return $data;
	}

	public function fixHtmlHead( $html )
	{
		$cross_origin_proxy_services = get_option( 'slpages.cross_origin_proxy_services' );

		if ( $cross_origin_proxy_services )
		{
			$html = str_replace( 'PROXY_SERVICES', str_replace( array( 'http://', 'http://' ), array( '//', '//' ), home_url() ) ."/slpages-proxy-services?url=", $html );
		}

		return $html;
	}

	public function fixNoindex( $html )
	{
		$search_array = array(
			'<meta name="iy453p9485yheisruhs5" content="" />',
			'<meta name="robots" content="noindex, nofollow" />'
		);

		if( strpos( $html, $search_array[ 0 ] ) !== false )
		{
			return str_replace( $search_array, '', $html );
		}

		return $html;
	}

	public function disableCloudFlareScriptReplace( $html )
	{
		$pattern = '/(<script )(type="text\/javascript")?(.*?)>/';

		return preg_replace( $pattern, "$1$2 data-cfasync=\"false\" $3>", $html );
	}

	public function getslpagesById( $page_id, $cookies = false )
	{ 
		$url_query = isset( $_SERVER[ 'QUERY_STRING' ] ) ? '&' . $_SERVER[ 'QUERY_STRING' ] : '';
		$url = self::endpoint . '/server/viewbyid/?id=' . $page_id . $url_query;

		if( $cookies )
		{
			$cookies_we_need = array( "slpages-variant-{$page_id}" );

			foreach( $cookies as $key => $value )
			{
				if( !in_array( $key, $cookies_we_need ) )
				{
					unset( $cookies[ $key ] );
				}
			}
		}

		slpagesIO::writeDiagnostics( $url, 'API url' );

		// Setting visitor id
		if($_SERVER['SERVER_NAME'] == 'localhost') {$_SERVER['REMOTE_ADDR'] = '127.0.0.1';}
		$uid = $page_id.substr(str_replace(".","",$_SERVER['REMOTE_ADDR']), -6);
		// Setting cookie for new visitor
		
		if (isset($_COOKIE["slp-visitor-id-".$page_id])) {
			$cid = $_COOKIE["slp-visitor-id-".$page_id];
			$c_pageid = substr($cid,0,-6);
			if($c_pageid == $page_id) { $uid = $cid;}
		}else {
			$cookie_name = "slp-visitor-id-".$page_id;
			$cookie_value = $uid;
			setcookie($cookie_name, $cookie_value, time() + (86400 * 30 * 12)); // 86400 = 1 day
		}

		// Getting UTM params
		$utm_campaign = isset($_GET['utm_campaign'])? $_GET['utm_campaign'] : '-';
		$utm_medium = isset($_GET['utm_medium'])? $_GET['utm_medium'] : '-';
		$utm_source = isset($_GET['utm_source'])? $_GET['utm_source'] : '-';

		$analytics = array();
		$analytics['client_ip'] = $_SERVER['REMOTE_ADDR'];
		$analytics['identd'] = "-";
		$analytics['http_authentication'] = $uid;
		$analytics['time'] = $_SERVER['REQUEST_TIME'];
		$analytics['client_request'] = $_SERVER['REQUEST_METHOD'];
		$analytics['server_name'] = $_SERVER['SERVER_NAME'];
		$analytics['request_uri'] = $utm_campaign."/".$utm_medium."/".$utm_source;
		$analytics['server_protocol'] = $_SERVER['SERVER_PROTOCOL'];
		$analytics['redirect_status'] = $page_id; //$_SERVER['REDIRECT_STATUS'];
		$analytics['response_size'] = $_SERVER['CONTENT_LENGTH'] ? $_SERVER['CONTENT_LENGTH'] : "-";
		$analytics['referer_url'] = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "-";
		$analytics['http_host'] = $_SERVER['HTTP_HOST'];
		$analytics['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		
		//print_r($analytics);
		//exit;
		$response = wp_remote_post
		(
			$url,
			array
			(
				'method' => 'POST',
				'timeout' => 70,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array
				(
					'useragent' => $_SERVER[ 'HTTP_USER_AGENT' ],
					'ip' => $_SERVER[ 'REMOTE_ADDR' ],
					'cookies' => $cookies,
					'custom' => isset( $_GET[ 'custom' ] ) ? $_GET[ 'custom' ] : null,
					'variant' => isset( $_GET[ 'variant' ] ) ? $_GET[ 'variant' ] : null,
					'tags' => $_GET,
					'analytics' => $analytics
				),
				'cookies' => array()
			)
		);
		
		slpagesIO::writeDiagnostics( $response, 'API Response' );

		if ( is_wp_error( $response ) )
		{
			throw new SlpagesApiCallException( $response->get_error_message(), 500 );
		}

		if ( isset( $response[ 'response' ][ 'code' ] ) && $response[ 'response' ][ 'code' ] == '500' )
		{
			throw new SlpagesApiCallException( null, 500 );
		}

		if( !empty( $response[ 'headers' ][ 'slpages-variant' ] ) )
		{
			setcookie
			(
				"slpages-variant-{$page_id}",
				$response[ 'headers' ][ 'slpages-variant' ],
				strtotime( '+12 month' )
			);
		}

		return $response;
	}

	public function getPageHtml( $id )
	{		
		$cache = get_site_transient( 'slpages_page_html_cache_' . $id );
		slpagesIO::writeDiagnostics( $cache, 'slpages_page_html_cache_' . $id );

		if ( $cache && !is_user_logged_in() )
		{
			$cache = $this->disableCloudFlareScriptReplace( $cache );
			$cache = $this->fixNoindex( $cache );
			return $this->fixHtmlHead( $cache );
		}

		try
		{
			$page = $this->getslpagesById( $id, $_COOKIE );
		}
		catch( SlpagesApiCallException $e )
		{
			return array
			(
				'body' => self::getInstance()->includes[ 'admin' ]->formatError( $e->getMessage(), $e->getCode() ),
				'status' => $page[ 'response' ][ 'code' ]
			);
		}

		if ( $page === false )
		{
			return array
			(
				'body' => self::getInstance()->includes[ 'admin' ]->formatError( __( 'Page not found!' ), 404 ),
				'status' => $page[ 'response' ][ 'code' ]
			);
		}
			
		$html = $this->disableCloudFlareScriptReplace( $page[ 'body' ] );
		$html = $this->fixHtmlHead( $html );
		$html = $this->fixNoindex( $html );
		
		return array
		(
			'body' => $html,
			'status' => $page[ 'response' ][ 'code' ]
		);
	}
}
