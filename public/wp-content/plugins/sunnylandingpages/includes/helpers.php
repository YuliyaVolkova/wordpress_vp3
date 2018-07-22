<?php
class SlpagesIO
{
	//source = post | get | request | server | cookie | session | globals
	public static function getVar( $value, $default = null, $source = null )
	{
		$ret = null;

		if( $source !== null )
		{
			switch( $source )
			{
				case 'request':
					$ret = isset( $_REQUEST[ $value ] ) ? $_REQUEST[ $value ] : $default;
				break;
				case 'get':
					$ret = isset( $_GET[ $value ] ) ? $_GET[ $value ] : $default;
				break;
				case 'post':
					$ret = isset( $_POST[ $value ] ) ? $_POST[ $value ] : $default;
				break;
				case 'server':
					$ret = isset( $_SERVER[ $value ] ) ? $_SERVER[ $value ] : $default;
				break;
				case 'cookie':
					$ret = isset( $_COOKIE[ $value ] ) ? $_COOKIE[ $value ] : $default;
				break;
				case 'session':
					$ret = isset( $_SESSION[ $value ] ) ? $_SESSION[ $value ] : $default;
				break;
				case 'globals':
					$ret = isset( $GLOBALS[ $value ] ) ? $GLOBALS[ $value ] : $default;
				break;
				default:

					if( is_array( $source ) && isset( $source[ $value ] ) )
					{
						$ret = $source[ $value ];
					}
					else
					{
						$ret = $default;
					}

				break;
			}
		}
		else
		{
			$ret = isset( $value ) ? $value : $default;
		}

		return $ret;
	}

	public static function getArrayVar( &$value, $default = false )
	{
		return isset( $value ) ? $value : $default;
	}

	//$level = updated | update-nag | error
	public static function addNotice( $notice, $level = 'updated' )
	{
		self::writeDiagnostics( $notice );
		$slpages = slpages::getInstance();
		$form = $slpages->includes[ 'view' ];

		$form->init( SLPAGES_PLUGIN_DIR .'/includes/templates/slpages/notice.php' );
		$form->level = $level;
		$form->notice = $notice;
		$notice_html = $form->fetch();
		$notices = get_option( 'slpages_notices', array() );

		if( !in_array( $notice_html, $notices ) )
		{
			$notices[] = $notice_html;
			
			return update_option( 'slpages_notices', $notices );
		}

		return false;
	}

	public static function writeDiagnostics( $value, $name = '' )
	{
		$slpages = slpages::getInstance();

		if( $slpages->includes[ 'log' ]->isDiagnosticMode() )
		{
			$slpages->includes[ 'log' ]->write( $value, $name );
		}
	}

	public static function writeLog( $value, $name = '' )
	{
		$slpages = slpages::getInstance();
		$slpages->includes[ 'log' ]->write( $value );
	}
}
