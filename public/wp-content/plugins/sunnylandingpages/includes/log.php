<?php
class SlpagesLogException extends Exception
{
}

class slpagesLog extends slpages
{
	var $logTableName = '';
	var $is_diagnostic_mode = false;
	var $rsa_pub = null;

	function __construct()
	{
		global $wpdb;

		$this->logTableName = $wpdb->prefix . 'slpages_log';
		$this->is_diagnostic_mode = get_option( 'slpages.diagnostics', false );
	}

	public function write( $value, $name = '', $add_caller = true )
	{
		global $wpdb;

		try
		{
			$this->checkLogTable();

			if ( is_array( $value ) || is_object( $value ) )
			{
				$value = print_r( $value, true );
			}

			$caller = '';

			if( $add_caller )
			{
				$trace = debug_backtrace();
				$trace_length = 3;
				$caller_arr = array();

				for( $i = 1; $i <= $trace_length; ++$i )
				{
					$caller = isset( $trace[ $i ] ) ? $trace[ $i ] : null;
					$caller_function = isset( $caller[ 'function' ] ) ? $caller[ 'function' ] : null;

					if( $caller_function == 'writeLog' || $caller_function == 'writeDiagnostics' )
					{
						$trace_length = 4;

						continue;
					}

					$caller_class = isset( $caller[ 'class' ] ) ? $caller[ 'class' ] . ' :: ' : null;

					if( $caller === null )
					{
						break;
					}

					$caller_arr[] = $caller_class . $caller_function;
				}
			}

			$caller = implode( "\r\n", $caller_arr );

			$data = array(
				'id' => 'NULL',
				'time' => current_time( 'mysql' ),
				'text' => $value,
				'caller' => $caller,
				'name' => $name
			);

			$wpdb->insert( $this->logTableName, $data );
		}
		catch ( Exception $e )
		{
			echo $e->getMessage();
		}
	}

	public function clear()
	{
		try
		{
			if( $this->checkLogTable( false ) )
			{
				global $wpdb;

				$sql = "DELETE FROM $this->logTableName";
				$wpdb->query( $sql );
			}
		}
		catch ( Exception $e )
		{
			echo $e->getMessage();
		}
	}

	public function read()
	{
		try
		{
			if( $this->checkLogTable( false ) )
			{
				global $wpdb;

				$sql = "SELECT * FROM $this->logTableName";
				$results = $wpdb->get_results( $sql );

				return $results;
			}
		}
		catch ( Exception $e )
		{
			throw $e;
		}
	}

	public function isDiagnosticMode()
	{
		return $this->is_diagnostic_mode;
	}

	public function getLogHTML()
	{
		if( current_user_can( 'manage_options' ) && $this->isDiagnosticMode() )
		{
			try
			{
				$plugins_html = $this->getPluginsHTML();
				$options_html = $this->getOptionsHTML();
				$phpinfo_html = $this->getPhpInfoHTML();
				$wp_options_html = $this->getWpOptionsHTML();
				$rows = $this->read();
				$form = self::getInstance()->includes[ 'view' ];
				$form->init( SLPAGES_PLUGIN_DIR . '/includes/templates/slpages/log.php' );
				$form->rows = $rows;
				$form->current_date = date( "Ymd_His" );
				$form->plugins_html = $plugins_html;
				$form->options_html = $options_html;
				$form->phpinfo_html = $phpinfo_html;
				$form->wp_options_html = $wp_options_html;
				$html = $form->fetch();

				return $html;

			} catch ( Exception $e )
			{
				throw $e;
			}
		}
		else
		{
			throw new Exception( __( 'slpages log can be downloaded only in diagnostic mode.' ) );
		}

	}

	public function getEntryCounter()
	{
		try
		{
			if( $this->checkLogTable( false ) )
			{
				global $wpdb;

				$sql = "SELECT COUNT(id) AS log_counter FROM $this->logTableName";
				$result = $wpdb->get_row( $sql );

				if( isset( $result->log_counter ) )
				{
					return $result->log_counter;
				}
				else
				{
					return 0;
				}
			}
		}
		catch ( Exception $e )
		{
			return 0;
		}
	}

	private function getPluginsHTML()
	{
		$all_plugins = get_plugins();
		$form = self::getInstance()->includes[ 'view' ];
		$form->init( SLPAGES_PLUGIN_DIR . '/includes/templates/slpages/log_plugins.php' );
		$form->rows = $all_plugins;

		return $form->fetch();
	}

	private function getOptionsHTML()
	{
		$necessary_options = array(
			'siteurl',
			'home',
			'permalink_structure',
			'blog_charset',
			'template',
			'db_version',
			'initial_db_version',
			'slpages.user_id',
			'slpages_404_page_id',
			'slpages_front_page_id',
			'slpages_db_version'
		);

		foreach( $necessary_options as $opt )
		{
			$options[ $opt ] = get_option( $opt, 'n/a' );
		}

		$form = self::getInstance()->includes[ 'view' ];
		$form->init( SLPAGES_PLUGIN_DIR .'/includes/templates/slpages/log_options.php' );
		$form->rows = $options;

		return $form->fetch();
	}

	private function getPhpInfoHTML()
	{
		ob_start();
		phpinfo( INFO_GENERAL | INFO_CREDITS | INFO_CONFIGURATION | INFO_MODULES | INFO_ENVIRONMENT | INFO_VARIABLES );
		$contents = ob_get_contents();
		ob_end_clean();
		$allowedTags = array(
			'table' => array(),
			'tbody' => array(),
			'thead' => array(),
			'tr' => array(),
			'td' => array(),
			'style' => array()
		);
		$contents = wp_kses( $contents, $allowedTags );
		$pattern = '/<style.*?style>/s';
		$contents = preg_replace( $pattern, '', $contents );
		$contents = '<div class="phpinfo">' . $contents . '</div>';

		return $contents;
	}

	private function getWpOptionsHTML()
	{
		$all_options = wp_load_alloptions();
		$json = json_encode( $all_options );
		$form = self::getInstance()->includes[ 'view' ];
		$form->init( SLPAGES_PLUGIN_DIR . '/includes/templates/slpages/log_wpoptions.php' );
		$form->wp_options_json = $json;

		return $form->fetch();
	}

	private function checkLogTable( $create = true )
	{
		global $wpdb;

		if( $wpdb->get_var( "SHOW TABLES LIKE '$this->logTableName'" ) == $this->logTableName )
		{
			return true;
		}
		else
		{
			if( $create )
			{
				$result = null;
				$charset_collate = $wpdb->get_charset_collate();
				$sql = "CREATE TABLE $this->logTableName(
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					text text NOT NULL,
					caller varchar(255) DEFAULT '' NOT NULL,
					name varchar(50) DEFAULT '' NOT NULL,
					UNIQUE KEY id (id)
					) $charset_collate;";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

				try
				{
					$result = dbDelta( $sql );

					if( empty( $result ) )
					{
						throw new SlpagesLogException( __( "Couldn't create {$this->logTableName} table" ) );
					}
				}
				catch( Exception $e)
				{
					throw new SlpagesLogException( $e->getMessage );
				}
			}
		}
	}
}
