<?
	class WP_PtcDebug extends PtcDebug
	{
		public function __contruct(){ /* do nothing */ }
		
		public static function getDefaultOptions(){ return parent::$_defaultOptions; }
		
		public static function getOptions()
		{
			global $wpdb;
			$db=new PtcDb();
			return $debugOptions=$db->readTable($wpdb->prefix .'ptcdebug');
		}
		
		public static function thisPluginFirst() 
		{
			$wp_path_to_this_file=preg_replace('/(.*)plugins\/(.*)$/',WP_PLUGIN_DIR."/$2",__FILE__);
			$this_plugin=plugin_basename(trim($wp_path_to_this_file));
			$active_plugins=get_option('active_plugins');
			$this_plugin_key=array_search($this_plugin, $active_plugins);
			if($this_plugin_key)
			{
				array_splice($active_plugins,$this_plugin_key,1);
				array_unshift($active_plugins,$this_plugin);
				update_option('active_plugins',$active_plugins);
			}
		}
		
		public static function load()
		{
			if(is_super_admin()) 
			{
				$debugOptions=self::getOptions();
				if($debugOptions[0]['allowed_ips'])
				{
					$allowed_ips=explode(',',$debugOptions[0]['allowed_ips']);
					$debugOptions[0]['allowed_ips']=$allowed_ips;
				}
				unset($debugOptions[0]['id']);
				foreach($debugOptions[0] as $k=>$v)
				{
					if($k=='set_time_limit'){ $debugOptions[0][$k]=(float)$v; continue; }
					if($k=='error_reporting'){ $debugOptions[0][$k]=(int)$v; continue; }
					if($v==='0'){ $debugOptions[0][$k]=false; }
					else if($v==='1'){ $debugOptions[0][$k]=true; }
				}
				$debugOptions[0]['panel_top']='28px;';
				$debugOptions[0]['panel_right']='3px;';
				parent::load($debugOptions[0]);
			}
		}
		
		public static function install()
		{
			global $wpdb;
			$table_name=$wpdb->prefix .'ptcdebug';
			$db=new PtcDb();
			$db->dbConnect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME,DB_CHARSET);
			$checktable=$db->executeSql("SHOW TABLES LIKE '".$table_name."'");
			$table_exists=$db->countRows();
			if(!$table_exists)
			{
				$sql="CREATE TABLE IF NOT EXISTS `wp_ptcdebug` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `url_key` varchar(255) NOT NULL,
					  `url_pass` varchar(255) NOT NULL,
					  `replace_error_handler` tinyint(1) NOT NULL,
					  `error_reporting` varchar(255) NOT NULL,
					  `catch_exceptions` tinyint(1) NOT NULL,
					  `check_referer` tinyint(1) NOT NULL,
					  `die_on_error` tinyint(1) NOT NULL,
					  `debug_console` tinyint(1) NOT NULL,
					  `allowed_ips` longtext NOT NULL,
					  `session_start` tinyint(1) NOT NULL,
					  `show_interface` tinyint(1) NOT NULL,
					  `enable_inspector` tinyint(1) NOT NULL,
					  `declare_ticks` tinyint(1) NOT NULL,
					  `set_time_limit` varchar(255) NULL,
					  `memory_limit` varchar(255) NULL,
					  `show_messages` tinyint(1) NOT NULL,
					  `show_globals` tinyint(1) NOT NULL,
					  `show_sql` tinyint(1) NOT NULL,
					  `trace_depth` decimal(10,0) NOT NULL,
					  `max_dump_depth` decimal(10,0) NOT NULL,
					  `default_category` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;";
				$db->executeSql($sql);
				$defaultOptions=self::getDefaultOptions();
				unset($defaultOptions['panel_right']);
				unset($defaultOptions['panel_top']);
				$db->insertRow($table_name,$defaultOptions);
			}
		}
		public static function wpQueries() 
		{
			global $wpdb;
			if(@$wpdb->queries)
			{
				foreach($wpdb->queries as $k=>$v)
				{ 
					$query=$v[0];
					unset($v[0]);
					sort($v);
					log_sql($v,$query,'WP Queries'); 
				}
			}
		}
		
		public static function admin()
		{
			if(is_super_admin()) 
			{
				add_options_page('PhpToolCase Debugger & Logger','PtcDebug',
				'manage_options','ptc-debug',array('WP_PtcDebug','adminPage'));
			}
		}
		
		public static function adminPage() 
		{
			global $wpdb;
			wp_enqueue_style('uiCss','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css');
			wp_enqueue_style("qtipCss","http://cdnjs.cloudflare.com/ajax/libs/qtip2/2.0.0/jquery.qtip.min.css"); 	
			//wp_enqueue_script("jquery","https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"); 			
			//wp_enqueue_script("jquery-ui","https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"); 
			wp_enqueue_script("qtipJs","http://cdnjs.cloudflare.com/ajax/libs/qtip2/2.0.0/jquery.qtip.min.js"); 
			wp_enqueue_script('validatorJs','http://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js');
			wp_enqueue_script('validatorDefaults',plugin_dir_url(dirname(__FILE__)).
													end(@explode('/',dirname(__FILE__))).'/js/ptc-forms-validator.js');		
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
			echo '<script type="text/javascript">
					jQuery(document).ready(function() 
					{ 
						jQuery("#ptcOptions").validate();
					});
				</script>';
			$data=self::getOptions();	// we always need the row id
			$data=$data[0];
			if(@$_POST['change_settings'])
			{ 
				$_POST['id']=$data['id'];
				$data=$_POST; 
			}
			
			$form=new PtcForms(array
			(
				'spacer_height'		=>	'10px',
				'print_form'			=>	false,
				'add_class_validator'	=>	true
			));
			
			$form->addElement(array
			(
				'name'		=>		'url_key',
				'label'		=>		'Url key:',
				'validate'		=>		'required',
				'attributes'	=>		array('style'=>'width:200px;'),
				'value'		=>		$data['url_key']
			));
			
			$form->addElement(array
			(
				'name'		=>		'url_pass',
				'label'		=>		'Url pass:',
				'validate'		=>		'required',
				'attributes'	=>		array('style'=>'width:200px;'),
				'value'		=>		$data['url_pass']
			));
			
			$replace_err_hand=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'replace_error_handler',
				'label'			=>		'Replace error handler:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$replace_err_hand['attributes['.$data['replace_error_handler'].']']=array('checked'=>true);
			$form->addElement($replace_err_hand);
			
			$catch_exceptions=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'catch_exceptions',
				'label'			=>		'Catch exceptions:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$catch_exceptions['attributes['.$data['catch_exceptions'].']']=array('checked'=>true);
			$form->addElement($catch_exceptions);
			
			$check_referer=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'check_referer',
				'label'			=>		'Check referer:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$check_referer['attributes['.$data['check_referer'].']']=array('checked'=>true);
			$form->addElement($check_referer);
			
			$die_on_error=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'die_on_error',
				'label'			=>		'Die on error:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$die_on_error['attributes['.$data['die_on_error'].']']=array('checked'=>true);
			$form->addElement($die_on_error);
			
			$debug_console=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'debug_console',
				'label'			=>		'Debug console:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$debug_console['attributes['.$data['debug_console'].']']=array('checked'=>true);
			$form->addElement($debug_console);
			
			$form->addElement(array
			(
				'type'		=>		'textarea',
				'name'		=>		'allowed_ips',
				'label'		=>		'Allowed ip\'s (separated by comas):',
				'value'		=>		$data['allowed_ips'],
				'attributes'	=>		array('cols'=>40,'rows'=>5)
			));
			
			$session_start=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'session_start',
				'label'			=>		'Use session_start():',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$session_start['attributes['.$data['session_start'].']']=array('checked'=>true);
			$form->addElement($session_start);
			
			$show_interface=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'show_interface',
				'label'			=>		'Show interface:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$show_interface['attributes['.$data['show_interface'].']']=array('checked'=>true);
			$form->addElement($show_interface);
			
			$enable_inspector=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'enable_inspector',
				'label'			=>		'Enable inspector:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$enable_inspector['attributes['.$data['enable_inspector'].']']=array('checked'=>true);
			$form->addElement($enable_inspector);
			
			$declare_ticks=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'declare_ticks',
				'label'			=>		'Declare ticks:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$declare_ticks['attributes['.$data['declare_ticks'].']']=array('checked'=>true);
			$form->addElement($declare_ticks);	
	
			/*
			$form->addElement(array
			(
				'name'		=>		'error_reporting',
				'label'		=>		'Error reporting level:',
				'validate'		=>		array('number','required'),
				'attributes'	=>		array('style'=>'width:200px;'),
				'value'		=>		$data['error_reporting']
			));*/
			
			$form->addElement(array
			(
				'name'		=>		'set_time_limit',
				'label'		=>		'Execution time limit:',
				'validate'		=>		'number',
				'attributes'	=>		array('style'=>'width:200px;'),
				'value'		=>		$data['set_time_limit']
			));
				
			/*$form->addElement(array
			(
				'name'		=>		'memory_limit',
				'label'		=>		'Execution memory limit:',
				'validate'		=>		'required',
				'attributes'	=>		array('style'=>'width:200px;'),
				'value'		=>		$data['memory_limit']
			));*/
			
			$show_messages=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'show_messages',
				'label'			=>		'Show messages panel:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$show_messages['attributes['.$data['show_messages'].']']=array('checked'=>true);
			$form->addElement($show_messages);
			
			$show_globals=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'show_globals',
				'label'			=>		'Show globals variable:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$show_globals['attributes['.$data['show_globals'].']']=array('checked'=>true);
			$form->addElement($show_globals);
			
			$show_sql=array
			(
				'type'			=>		'radiogroup',
				'name'			=>		'show_sql',
				'label'			=>		'Show sql panel:',
				'validate'			=>		'required',
				'values'			=>		array(1=>'Yes',0=>'No'),
				'attributes'		=>		array('cols'=>2),
				'labelOptions[]'	=>		array('align'=>'right')
			);
			$show_sql['attributes['.$data['show_sql'].']']=array('checked'=>true);
			$form->addElement($show_sql);
			
			$form->addElement(array
			(
				'name'		=>		'trace_depth',
				'label'		=>		'Backtrace depth:',
				'validate'		=>		array('required','number'),
				'attributes'	=>		array('style'=>'width:200px;'),
				'value'		=>		$data['trace_depth']
			));
			
			$form->addElement(array
			(
				'name'		=>		'max_dump_depth',
				'label'		=>		'Maximum dump depth:',
				'validate'		=>		array('required','number'),
				'attributes'	=>		array('style'=>'width:200px;'),
				'value'		=>		$data['max_dump_depth']
			));
			
			
			$form->addElement(array
			(
				'name'		=>		'default_category',
				'label'		=>		'Default category:',
				'validate'		=>		'required',
				'attributes'	=>		array('style'=>'width:200px;'),
				'value'		=>		$data['default_category']
			));
			
			$form->addElement(array
			(
				'type'    =>        'custom',
				'name'    =>        'spacer1',
				'value'    =>        $form->addSpacer('10px')
			));
			    
			$form->addElement(array
			(
				'type'		=>		'submit',
				'name'		=>		'change_settings',
				'value'		=>		'Change Settings',
				'attributes'	=>		array('class'=>'button button-primary')
			));
			
			echo '<div style="width:1100px;">';
			echo '<div style="float:left;">';
			echo '<div class="wrap">';
			echo '<h1 style="margin-left:20px">PhpToolCase Debugger & Logger</h1>';
			echo '</div>';
			
			if(@$_POST['change_settings'])
			{
				$validate=$form->validate();
				if(!$validate['isValid'])
				{	
					echo '<div style="padding:5px;width:500px;text-align:center;" class="ui-state-error ui-corner-all">';
					echo 'Some fields are not valid, please review the form!</div>';
				}
				else
				{
					// write changes to db
					unset($_POST['change_settings']);
					foreach($_POST as $k=>$v)
					{
						if(preg_match('|_ptcgen|',$k)){ unset($_POST[$k]); }
					}
					$db=new PtcDb();
					$db->updateRow($wpdb->prefix.'ptcdebug',$_POST,$data['id']);
					echo '<script>alert("Options have been saved!")</script>';
				}
			}
			echo '<br><br>';
			$html=$form->render(array('id'=>'ptcOptions','style'=>'margin-left:20px;'));
			$html_final=str_replace('style="width:59%;" cols="2"','style="width:29%;" cols="2"',$html);
			echo $html_final;
			echo '</div>';
			echo '<div>';
			echo '<div style="height:70px;"><!-- --></div><br>';
			echo 'WP_DEBUG: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>'.
												$var=(WP_DEBUG) ? 'TRUE</b>' : 'FALSE</b>';
			echo '<br><br>';
			if(!WP_DEBUG)
			{ 
				echo '<span style="margin-top:5px;padding:5px;" class="ui-state-error ui-corner-all">';
				echo 'Set WP_DEBUG constant to true in wp-config.php for worpress debugging features</span>'; 
				echo '<br><br>';
			}
			echo 'SAVEQUERIES: &nbsp;&nbsp;&nbsp;&nbsp;<b>';
			if(@SAVEQUERIES===true || @SAVEQUERIES===false)
			{ 
				$var=(SAVEQUERIES) ? 'TRUE</b>' : 'FALSE</b>'; 
			}
			else{ $var='NULL</b>'; }
			echo $var;
			echo '<br><br>';
			if(@SAVEQUERIES!==true)
			{ 
				echo '<span style="margin-top:5px;padding:5px;" class="ui-state-error ui-corner-all">';
				echo 'Set SAVEQUERIES constant to true in wp-config.php to show wp sql queries!<span>'; 
			}
			echo '</div></div>';
		}
	}
?>