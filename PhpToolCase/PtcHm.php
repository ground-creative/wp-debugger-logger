<?php
	/**
	* PHP TOOLCASE HANDYMAN CLASS
	* PHP version 5
	* @category 	Libraries
	* @package  	PhpToolCase
	* @version	0.8.3
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/
	class PtcHandyMan			
	{
		public function __construct($options=null,$debugOptions=null)
		{
			if(class_exists('PtcDebug',true) && $debugOptions){ PtcDebug::load($debugOptions); }
		}
		/**
		* Alias of {@link addPaths()}
		*/
		public static function addPath($path)
		{
			self::addPaths($path);
		}
		/**
		* Adds path to directory with classes, to autoload them when needed 
		* @param	string|array 	$paths	full path/s to directory with classes
		*/
		public static function addPaths($paths)
		{
			if(!is_array($paths)){ $paths=array($paths); }
			foreach($paths as $k=>$v)
			{
				$path=realpath($v);
				if($path)
				{	
					if(in_array($path,self::$_directories))
					{
						if(class_exists('PtcDebug',true))	// debug
						{ 
							PtcDebug::bufferLog($path,'Path already exists!','Autoloader'); 
						}
						unset($paths[$k]);
						continue; 
					}
					self::$_directories[]=$path; 
				}
			}
			if(class_exists('PtcDebug',true) && $paths)		// debug
			{ 
				$paths=(sizeof($paths)==1) ? $paths[0] : $paths;
				PtcDebug::bufferLog($paths,'Added path(s) to autoloader','Autoloader'); 
			}
		}
		/**
		* Returns the current included paths for the the autoloader
		*/
		public function getPaths()
		{
			return self::$_directories;
		}
		/**
		* Paths for diretories to autoload classes
		* @var 	array 
		*/
		protected static $_directories=array();
		
		protected static $_separators=array('.', '-', '', '_');
		
		protected static $_namingConventions=array('{CLASS}','{CLASS}{SEP}class','{CLASS}{SEP}inc',
								'class{SEP}{CLASS}','class.{CLASS}','class{SEP}wp{SEP}{CLASS}');




		// THIS NEEDS TO BE TESTED UNDER WIN ENVIRONMENT
		/**
		* Load classes automatically with namespaces support based on folder structure
		* @param	string 	$class	the name of the class to autoload
		*/
		public static function load($class)
		{
			$namespace=null;
			$class_name=$class;
			if(preg_match('|\\\\|',$class))
			{ 
				$folders=explode('\\',$class);
				$class=array_pop($folders);
				foreach($folders as $k=>$v){ $namespace.=$v.'/'; }
				$class_name=str_replace('/','\\',$namespace.$class);
			}
			foreach(self::$_directories as $path) 
			{
				foreach(self::$_separators as $sep)
				{
					foreach(self::$_namingConventions as $convention)
					{
						$filename=str_replace(array('{SEP}','{CLASS}'),array($sep,$class),$convention);
						$filename=$filename.'.php';
						$try_path=$path.'/'.$namespace;
						if(file_exists($try_path.$filename))
						{
							if(class_exists('PtcDebug',true))				// debug
							{ 
								$msg=array('file'=>$try_path.$filename,'class'=>$class_name);
								PtcDebug::bufferLog($msg,'Included new class','Autoloader');
							}					
							require_once($try_path.$filename); return; 
						}
						else if(strtoupper(substr(PHP_OS,0,3))!=='WIN')		// no windows support for now 
						{
							$files=glob($try_path.'*'); 					// get all files in this directory
							foreach($files as $file) 
							{
								$msg=array('file'=>$file,'class'=>$class_name);
								if($file==$try_path.strtolower($filename))	// try filename lowercase
								{ 
									if(class_exists('PtcDebug',true))		// debug
									{
										PtcDebug::bufferLog($msg,'Included new class','Autoloader'); 
									}
									require_once($file); return; 
								}
								/* try replacing "_" with other separators in class name with lowercase */
								else if($sep!='_')
								{
									$replaced=$try_path.str_replace('_',$sep,$filename);
									if($file==$replaced || $file==strtolower($replaced))
									{
										if(class_exists('PtcDebug',true))	// debug
										{
											PtcDebug::bufferLog($msg,'Included new class','Autoloader'); 
										}
										require_once($file); return; 
									}
								}
							}
						}
					}
				}
			}
		}
	}
	PtcHandyMan::addPath(dirname(__FILE__));			// add this path
	spl_autoload_register(array('PtcHandyMan','load'));	// register the autoloader
 ?>