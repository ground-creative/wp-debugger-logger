<?php

	namespace PtcDebug;

	use PhpToolCase\PtcForm;
	use PhpToolCase\PtcDb;
		
	class optionsForm extends PtcForm
	{
		protected $_options = array
		(
			'spacer_height'		=>	'10px' ,
			'add_class_validator'	=>	true ,
			'debug_category'		=>	'Debug Options Form'
		);
		
		protected $_fieldValues = null;
		
		// add event listeners on initalization
		public function boot( )
		{ 
			$this->observe( );
			$this->_fieldValues = \WP_PtcDebug::getOptions( );
		}
		
		// configure the form fields
		public function formFields( )
		{
			$this->addElement( array
			(
				'type'		=>	'hidden' ,
				'name'		=>	'rec_id' ,
				'value'		=>	$this->_fieldValues->id ,
				'attributes'	=>	array( 'noAutoValue' => true )
			) );
			
			$this->addElement( array
			(
				'name'		=>	'url_key' ,
				'label'		=>	'Url key:' ,
				'validate'		=>	'required' ,
				'attributes'	=>	array( 'style' => 'width:200px;' ) ,
				'value'		=>	$this->_fieldValues->url_key
			) );
			
			$this->addElement( array
			(
				'name'		=>	'url_pass' ,
				'label'		=>	'Url pass:' ,
				'validate'		=>	'required' ,
				'attributes'	=>	array( 'style' => 'width:200px;' ) ,
				'value'		=>	$this->_fieldValues->url_pass
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'replace_error_handler' ,
				'label'			=>	'Replace error handler:' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->replace_error_handler . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'catch_exceptions' ,
				'label'			=>	'Catch exceptions:' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->catch_exceptions . ']'	=>	array( 'checked' => true )
			) );
				
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'check_referer' ,
				'label'			=>	'Check referer:' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->check_referer . ']'	=>	array( 'checked' => true )
			) );
	
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'die_on_error' ,
				'label'			=>	'Die on error:' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->die_on_error . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'debug_console' ,
				'label'			=>	'Debug console:' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->debug_console . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'		=>	'textarea' ,
				'name'		=>	'allowed_ips' ,
				'label'		=>	'Allowed ip\'s (separated by comas):' ,
				'attributes'	=>	array( 'cols' => 25 , 'rows' => 5 ) ,
				'value'		=>	$this->_fieldValues->allowed_ips
			) );
			
			$this->addElement( array
			(
				'type'		=>	'textarea' ,
				'name'		=>	'exclude_categories' ,
				'label'		=>	'Excluded categories:' ,
				'attributes'	=>	array( 'cols' => 25 , 'rows' => 5 ) ,
				'value'		=>	$this->_fieldValues->exclude_categories
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'session_start' ,
				'label'			=>	'Use session_start():' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->session_start . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup',
				'name'			=>	'show_interface',
				'label'			=>	'Show interface:',
				'validate'			=>	'required',
				'values'			=>	array(1=>'Yes',0=>'No'),
				'attributes'		=>	array('cols'=>2),
				'labelOptions[]'	=>	array('align'=>'right') ,
				'attributes[' . $this->_fieldValues->show_interface . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup',
				'name'			=>	'enable_inspector',
				'label'			=>	'Enable inspector:',
				'validate'			=>	'required',
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->enable_inspector . ']'	=>	array( 'checked' => true )
			) );
			
			/*$this->addElement(array
			(
				'name'		=>	'error_reporting',
				'label'		=>	'Error reporting level:',
				'validate'		=>	array('number','required'),
				'attributes'	=>	array('style'=>'width:200px;'),
				'value'		=>	$this->_fieldValues['error_reporting']
			) );*/
			
			$this->addElement(array
			(
				'name'		=>	'set_time_limit' ,
				'label'		=>	'Execution time limit:' ,
				'validate'		=>	'number' ,
				'attributes'	=>	array( 'style' => 'width:200px;' ) ,
				'value'		=>	$this->_fieldValues->set_time_limit
			) );
			
			/*$this->addElement(array
			(
				'name'		=>	'memory_limit',
				'label'		=>	'Execution memory limit:',
				'validate'		=>	'required',
				'attributes'	=>	array('style'=>'width:200px;'),
				'value'		=>	$this->_fieldValues['memory_limit']
			) );*/
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup',
				'name'			=>	'show_messages',
				'label'			=>	'Show messages panel:',
				'validate'			=>	'required',
				'values'			=>	array(1=>'Yes',0=>'No'),
				'attributes'		=>	array('cols'=>2),
				'labelOptions[]'	=>	array('align'=>'right') ,
				'attributes[' . $this->_fieldValues->show_messages . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'show_globals' ,
				'label'			=>	'Show globals variable:',
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->show_globals . ']'	=>	array( 'checked' => true )
			));
	
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'show_sql' ,
				'label'			=>	'Show sql panel:' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->show_sql . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup',
				'name'			=>	'show_w3c',
				'label'			=>	'Show w3c panel:',
				'validate'			=>	'required',
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' )  ,
				'attributes[' . $this->_fieldValues->show_w3c . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup',
				'name'			=>	'minified_html',
				'label'			=>	'Minified html:',
				'validate'			=>	'required',
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' ) ,
				'attributes'		=>	array( 'cols' => 2 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->show_w3c . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'name'		=>	'trace_depth' ,
				'label'		=>	'Backtrace depth:' ,
				'validate'		=>	array( 'required' , 'number' ) ,
				'attributes'	=>	array( 'style' => 'width:200px;' ) ,
				'value'		=>	$this->_fieldValues->trace_depth
			) );
			
			$this->addElement( array
			(
				'name'		=>	'max_dump_depth' ,
				'label'		=>	'Maximum dump depth:' ,
				'validate'		=>	array( 'required' , 'number' ) ,
				'attributes'	=>	array( 'style' => 'width:200px;' ) ,
				'value'		=>	$this->_fieldValues->max_dump_depth
			) );
			
			$this->addElement(array
			(
				'name'		=>	'default_category' ,
				'label'		=>	'Default category:' ,
				'validate'		=>	'required' ,
				'attributes'	=>	array( 'style' => 'width:200px;' ) ,
				'value'		=>	$this->_fieldValues->default_category
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'code_coverage' ,
				'label'			=>	'Enable code coverage:' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1=> 'Yes' , 0 => 'No' , 'full' => 'FULL' ) ,
				'attributes'		=>	array( 'cols' => 3 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->code_coverage . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'type'			=>	'radiogroup' ,
				'name'			=>	'trace_functions' ,
				'label'			=>	'Enable function tracing:' ,
				'validate'			=>	'required' ,
				'values'			=>	array( 1 => 'Yes' , 0 => 'No' , 'full' => 'FULL' ) ,
				'attributes'		=>	array( 'cols' => 3 ) ,
				'labelOptions[]'	=>	array( 'align' => 'right' ) ,
				'attributes[' . $this->_fieldValues->trace_functions . ']'	=>	array( 'checked' => true )
			) );
			
			$this->addElement( array
			(
				'name'		=>	'panel_top' ,
				'label'		=>	'Panel top position:' ,
				'validate'		=>	'required' ,
				'attributes'	=>	array( 'style' => 'width:200px;' ) ,
				'value'		=>	$this->_fieldValues->panel_top
			) );
			
			$this->addElement( array
			(
				'name'		=>	'panel_right' ,
				'label'		=>	'Panel right position:' ,
				'validate'		=>	'required' ,
				'attributes'	=>	array( 'style' => 'width:200px;' ) ,
				'value'		=>	$this->_fieldValues->panel_right
			) );
			
			$this->addElement( array
			(
				'type'	=>	'custom' ,
				'name'   	=>	'spacer1' ,
				'value'   	=>	$this->addSpacer( '10px' )
			) );
			    
			$this->addElement( array
			(
				'type'		=>	'submit' ,
				'name'		=>	'change_settings' ,
				'value'		=>	'Change Settings' ,
				'attributes'	=>	array( 'class' => 'button button-primary' , 'noAutoValue' => true )
			) );
		}
		
		public static function submit( $fieldName , $obj ) // form submit event, run validator here
		{	 
			$obj->validate( ); 
		} 
		
		public static function error( $result , $errMsg , $obj ) // form is not valid, add an error msg
		{
			$errMsg = '<div class="ui-state-error ui-corner-all" style="padding:5px;text-align:center;width:' . 
			$obj->getOption( 'form_width' ) . '">Something went wrong. Please review the form!</div><br>';
		}
		
		public static function valid( $result , $msg , $obj )
		{
			global $wpdb;
			$data = $obj->getValues( );
			unset( $data[ 'change_settings' ] );
			$rec_id = $data[ 'rec_id' ];
			unset( $data[ 'rec_id' ] );
			// write changes to db
			PtcDb::table( $wpdb->prefix . 'ptcdebug' )
				->update( $data , $rec_id )
				->run( );
			echo '<script>alert( "Options have been saved!" )</script>';
		}
		
		public static function built( $k , &$html )
		{
			$html = str_replace( 'style="width:59%;" cellpadding="0" cellspacing="0" cols="2"' ,
								'style="width:29%;" cellpadding="0" cellspacing="0" cols="2"' , $html );
			$html = str_replace( 'style="width:59%;" cellpadding="0" cellspacing="0" cols="3"' ,
								'style="width:48%;" cellpadding="0" cellspacing="0" cols="3"' , $html );
		}
		
		public static function rendering( &$container , $object )
		{
			/*
			add_action('admin_footer',function()
			{
				echo '<script type="text/javascript">
						jQuery(document).ready(function() 
						{ 
							jQuery("#ptcOptions").validate();
						});
				</script>';
			});*/
			$string = '<script type="text/javascript">
					jQuery(document).ready(function() 
					{ 
						jQuery("#ptcOptions").validate();
					});
				</script>';
			$string .= '<div style="width:1100px;">';
			$string .= '<div style="float:left;">';
			$string .= '<div class="wrap">';
			$string .= '<h1 style="margin-left:20px">PhpToolCase Debugger & Logger</h1>';
			$string .= '<a style="margin-left:20px" href="http://phptoolcase.com/guides/ptc-debug-guide.html"';
			$string .= ' target="_blank">Visit home page for usage and informations</a>';
			$string .= '</div>';
			$string .= '<br><br>' . $container;
			$string .= '</div><div>';
			$string .= '<div style="height:88px;"><!-- --></div><br>';
			$string .= 'WP_DEBUG: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>'.
												$var = ( WP_DEBUG ) ? 'TRUE</b>' : 'FALSE</b>';
			$string .= '<br><br>';
			if ( !WP_DEBUG )
			{ 
				$string .= '<span style="margin-top:5px;padding:5px;" class="ui-state-error ui-corner-all">';
				$string .= 'Set WP_DEBUG constant to true in wp-config.php for worpress debugging features</span>'; 
				$string .= '<br><br>';
			}
			$string .= 'SAVEQUERIES: &nbsp;&nbsp;&nbsp;&nbsp;<b>';
			if ( @SAVEQUERIES === true || @SAVEQUERIES === false )
			{ 
				$var = ( SAVEQUERIES ) ? 'TRUE</b>' : 'FALSE</b>'; 
			}
			else{ $var = 'NULL</b>'; }
			$string .= $var;
			$string .= '<br><br>';
			if ( @SAVEQUERIES !== true )
			{ 
				$string .= '<span style="margin-top:5px;padding:5px;" class="ui-state-error ui-corner-all">';
				$string .= 'Set SAVEQUERIES constant to true in wp-config.php to show wp sql queries<span>'; 
			}
			$string .= '</div></div>';
			$container = $string;
		}
	}
