<?php

	use PhpToolCase\PtcForm;
	use PhpToolCase\PtcDb;
	use PtcDebug\optionsForm;
	//use PhpToolCase\PtcDebug;
	
	class WP_PtcDebug extends PtcDebug
	{
		public function __contruct( ){ /* does nothing */ }
		
		// get class default options
		public static function getDefaultOptions( ){ return parent::$_defaultOptions; }
		
		// get options from the db
		public static function getOptions( $returnArray =  false )
		{
			global $wpdb;
			if ( $returnArray ) { PtcDb::setFetchMode( \PDO::FETCH_ASSOC ); }
			return PtcDb::table( $wpdb->prefix . 'ptcdebug' )->row( );
		}
		
		// make this plugin always load before others
		public static function thisPluginFirst( ) 
		{
			$wp_path_to_this_file = preg_replace( '/(.*)plugins\/(.*)$/' , WP_PLUGIN_DIR . "/$2" , __FILE__ );
			$this_plugin = plugin_basename( trim( $wp_path_to_this_file ) );
			$active_plugins = get_option( 'active_plugins' );
			$this_plugin_key = array_search( $this_plugin, $active_plugins );
			if ( $this_plugin_key )
			{
				array_splice( $active_plugins , $this_plugin_key , 1 );
				array_unshift( $active_plugins , $this_plugin );
				update_option( 'active_plugins' , $active_plugins );
			}
		}
		
		// load the debugger if requested
		public static function load( $options = null )
		{
			if ( is_super_admin( ) ) 
			{
				global $wpdb;
				$debugOptions = self::getOptions( true );
				if ( $debugOptions[ 'allowed_ips' ] )
				{
					$allowed_ips = explode( ',' , $debugOptions[ 'allowed_ips' ] );
					$debugOptions[ 'allowed_ips' ] = $allowed_ips;
				}
				else{ $debugOptions[ 'allowed_ips' ] = null; }
				if ( $debugOptions[ 'exclude_categories' ] )
				{
					$exclude_categories = explode( ',' , $debugOptions[ 'exclude_categories' ] );
					$debugOptions[ 'exclude_categories' ] = $exclude_categories;
				}
				else{ $debugOptions[ 'exclude_categories' ] = null; }
				unset( $debugOptions[ 'id' ] );
				foreach ( $debugOptions as $k => $v )
				{
					if ( $k == 'set_time_limit' ){ $debugOptions[ $k ] = ( float )$v; continue; }
					if ( $k == 'error_reporting' ){ $debugOptions[ $k ] = ( int )$v; continue; }
					if ( $v === '0' ){ $debugOptions[ $k ] = false; }
					else if ( $v === '1' ){ $debugOptions[ $k ] = true; }
				}
				parent::load( $debugOptions );
			}
		}
		
		public static function install( )
		{
			global $wpdb;
			$table_exists = PtcDb::run( 'SHOW TABLES LIKE ?' , array( $wpdb->prefix . 'ptcdebug' ) );			
			if ( !$table_exists )
			{
				$sql = "CREATE TABLE IF NOT EXISTS `wp_ptcdebug` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `url_key` varchar(255) NOT NULL,
					  `url_pass` varchar(255) NOT NULL,
					  `replace_error_handler` tinyint(1) NOT NULL,
					  `error_reporting` varchar(255) NOT NULL,
					  `catch_exceptions` tinyint(1) NOT NULL,
					  `check_referer` tinyint(1) NOT NULL,
					  `die_on_error` tinyint(1) NOT NULL,
					  `debug_console` tinyint(1) NOT NULL,
					  `allowed_ips` longtext,
					  `session_start` tinyint(1) NOT NULL,
					  `show_interface` tinyint(1) NOT NULL,
					  `set_time_limit` varchar(255) NULL,
					  `memory_limit` varchar(255) NULL,
					  `show_messages` tinyint(1) NOT NULL,
					  `show_globals` tinyint(1) NOT NULL,
					  `show_sql` tinyint(1) NOT NULL,
					  `show_w3c` tinyint(1) NOT NULL,
					  `minified_html` tinyint(1) NOT NULL,
					  `trace_depth` decimal(10,0) NOT NULL,
					  `max_dump_depth` decimal(10,0) NOT NULL,
					  `panel_top` varchar(255) NOT NULL,
				          `panel_right` varchar(255) NOT NULL,
					  `default_category` varchar(255) NOT NULL,
					  `enable_inspector` tinyint(1) NOT NULL,
					  `code_coverage` varchar(255) NOT NULL,
					  `trace_functions` varchar(255) NOT NULL,
					  `exclude_categories` longtext,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;";
				
				// create the table
				PtcDb::run( $sql );
				
				// get class options
				$defaultOptions = self::getDefaultOptions( );

				// set panel top and right options
				$defaultOptions[ 'panel_top' ] = '28px';
				$defaultOptions[ 'panel_right' ] = '3px';				

				// format option values that are equal to an array, use a coma as delimiter
				$defaultOptions[ 'exclude_categories' ] = self::_formatOption( $defaultOptions[ 'exclude_categories' ] );
				$defaultOptions[ 'allowed_ips' ] = self::_formatOption( $defaultOptions[ 'allowed_ips' ] );
				
				// save options to table
				PtcDb::table( $wpdb->prefix . 'ptcdebug' )
					->insert( $defaultOptions )
					->run( );
			}
		}
		
		public static function wpQueries( ) 
		{
			global $wpdb;
			if ( @$wpdb->queries )
			{
				foreach( $wpdb->queries as $k => $v )
				{ 
					$query = $v[ 0 ];
					unset( $v[ 0 ] );
					sort( $v );
					\ptc_log_sql( $v , $query , 'WP Queries' ); 
				}
			}
		}
		
		public static function admin( )
		{
			if ( is_super_admin( ) ) 
			{
				add_options_page( 'PhpToolCase Debugger & Logger' , 'PtcDebug' , 
					'manage_options' , 'ptc-debug' , array( 'WP_PtcDebug' , 'adminPage' ) );
			}
		}
		
		public static function adminPage( ) 
		{
			wp_enqueue_style( 'uiCss' , 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( "qtipCss" , "http://cdnjs.cloudflare.com/ajax/libs/qtip2/2.0.0/jquery.qtip.min.css" ); 	
			//wp_enqueue_script("jquery","https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"); 			
			//wp_enqueue_script("jquery-ui","https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"); 
			wp_enqueue_script( "qtipJs" , "http://cdnjs.cloudflare.com/ajax/libs/qtip2/2.0.0/jquery.qtip.min.js" ); 
			wp_enqueue_script( 'validatorJs' , 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js' );
			wp_enqueue_script( 'validatorDefaults' , plugin_dir_url( dirname( __FILE__ ) ) .
							end( @explode( DIRECTORY_SEPARATOR , dirname( __FILE__ ) ) ) . '/js/ptc-forms-validator.js' );	
							
			$form = new optionsForm( );
			$form->render( array( 'id' => 'ptcOptions' , 'style' => 'margin-left:20px;' ) );
		}
		
		// format option values that are equal to an array, use a coma as delimiter
		protected static function _formatOption( $option )
		{
			if ( $option )
			{
				$option = is_array ( $option ) ? $option : array( $option );
				return implode( ',' , $option );
			}
			return;
		}
		
		// override parent fucntion to add execution timing to WP QUERIES
		protected static function _sortBuffer()
		{
			global $wpdb;
			if ( @static::$_buffer )
			{
				foreach ( static::$_buffer as $k => $arr )
				{
					$type = $arr[ 'type' ];
					if ( $type == 'sql' && $arr[ 'errno' ] == 'WP Queries' && @$arr[ 'errstr' ][ 1 ] )
					{
						$arr[ 'time' ] = round( $arr[ 'errstr'  ][ 1 ] * 1000 , 3 ) . ' ms';
					}
					$buffer[ $type ][ ] = $arr;
				}
				return @static::$_buffer = $buffer;
			}
		}
	}