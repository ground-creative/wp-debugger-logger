<?php

	namespace PhpToolCase;

	/**
	* PHP TOOLCASE HANDYMAN CLASS
	* PHP version 5.3
	* @category 	Library
	* @version	0.9.2
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	class PtcHandyMan			
	{
		/**
		* Alias of @ref addDirs()
		*/
		public static function addDir( $path ) { static::addDirs( $path ); }
		/**
		* Alias of @ref addFiles()
		*/
		public static function addFile( $file ){ static::addFiles( $file ); }
		/**
		* Alias of @ref addSeparator()
		*/
		public static function addSeparator( $sep ) { static::addSeparators( $sep ); }
		/**
		* Alias of @ref addConventions()
		*/
		public static function addConvention( $conventions ) 
		{ 
			static::addConventions( $conventions ); 
		}
		/**
		* Alias of @ref addAppPaths()
		*/
		public static function addAppPath( $path ) { static::addAppPaths( $path ); }
		/**
		* Alias of @ref getAppPaths()
		*/
		public static function getAppPath( $type = null ) { return static::getAppPaths( $type ); }
		/**
		* Adds path(s) to the PtcHandyMan::$_dirs property to load classes when needed. 
		* See @ref add_dirs and @ref namespace_dirs
		* @param	string|array		$paths	full path(s) to directories with classes
		*/
		public static function addDirs( $paths )
		{
			if ( !is_array( $paths ) ) { $paths = array( $paths ); }
			foreach ( $paths as $k => $v )
			{
				$path = realpath( $v );		// get real os path
				if ( $path )				// if path exists
				{	
					$result = ( !is_int( $k ) ) ? static::_addNamespaceDirectory( 
						str_replace( '\\' , '' , $k ) , $path ) : static::_addDirectory( $path );
					if ( !$result ) { unset( $paths[ $k ] ); continue; }
				}
				else						// need to trigger error if path is not found
				{
					trigger_error( 'The path "' . $v . 
								'" does not exists or is not accessible!' , E_USER_ERROR );
				}
			}
			if ( $paths ) // debug
			{ 
				static::_debug( $paths , 'Added path(s) to autoloader' , 'Autoloader' ); 
			} 
		}
		/**
		* Adds class file(s) to the autoloader. See @ref add_files
		* @param	string		$files		full path of the class file		
		*/
		public static function addFiles( $files )
		{
			if ( !is_array( $files ) ) { $files = array( $files ); }
			foreach ( $files as $k => $v )
			{
				$file = realpath( $v );		// get real OS path
				if( $file )					// path exists
				{	
					// remove first '\'  if present
					$key = ( substr( $k , 0 , 1 ) == '\\' ) ? substr( $k , 1 ) : $k; 
					// check if class  name exists  already
					if ( @array_key_exists( $key , @static::$_dirs[ 'files' ] ) ) 
					{
						trigger_error( 'Cannot redeclare "' . $k . '" as class name!' , E_USER_ERROR );
						unset( $files[ $k ] );
						continue;
					} // maybe we should check if the file is already present as well?
					@static::$_dirs[ 'files' ][ $key ] = $file;
				}
				else { trigger_error( 'the file "' . $v . '" cannot be found!' , E_USER_ERROR ); } // file not found
			}
			if ( $files )	{ static::_debug( $files , 'Added file(s) to autoloader' , 'Autoloader' ); } // debug
		}
		/** 
		* Registers the autoloader to load classes when needed. See @ref hm_getting_started
		* @param	bool		$addThisPath			adds the path where the class resides as a directory
		* @param	bool		$useHelpers			load the ptc-helpers.php file if found, and registers the event class
		* @param	bool		$registerAutoLoader		registers the load method with the spl utilities
		*/
		public static function register( $addThisPath = true , $useHelpers = true , $registerAutoLoader = true )
		{
			$this_class = get_called_class( );
			if ( $addThisPath ) { static::addDir( dirname( __FILE__ ) ); }	// add this path
			if ( $registerAutoLoader ) { spl_autoload_register( array( $this_class , 'load' ) ); }
			if ( $useHelpers && file_exists( dirname( __FILE__ ) . '/ptc-helpers.php' ) ) // add helpers if found
			{ 
				require_once( dirname( __FILE__ ) . '/ptc-helpers.php' ); 
			}
			if ( $useHelpers && file_exists( dirname( __FILE__ ) . '/PtcEvent.php' ) ) 
			{ 
				__NAMESPACE__ . PtcEvent::register( ); // register PtcEvent with helpers
			}
			$namespace = @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
			@define( '_PTCHANDYMAN_' . $namespace , $this_class ); 		// declare the class namespace
			static::_debug( 'Autoloader registerd' , '' , 'Autoloader' );
		}
		/**
		* Retrieves the separators used by the autoloader
		*/
		public static function getSeparators( ){ return static::$_separators; }
		/**
		* Retrieves the naming conventions used by the autoloader
		*/		
		public static function getConventions( ){ return static::$_namingConventions; }
		/**
		* Adds a separator to the  PtcHandyMan::$_separators property for the autoloader. 
		* See @ref using_separators
		* @param	array|string		$sep		the separator(s) to be added
		*/
		public static function addSeparators( $sep )
		{
			$seps = ( is_array( $sep ) ) ? $sep : array( $sep );
			foreach ( $seps as $k => $v )
			{
				if ( in_array( $v , static::$_separators ) )
				{
					static::_debug( 'Separator "' . $v . ' " already present!' , '' , 'Autoloader' );	
					continue;
				}
				static::$_separators[ ] = $v; 
			}
		}
		/**
		* Adds naming convention(s) to the PtcHandyMan::$_namingConventions property for the autoloader.
		* See @ref using_separators
		* @param	array|string		$conventions		the naming convention(s) to be added
		*/
		public static function addConventions( $conventions )
		{
			$conventions = ( is_array( $conventions ) ) ? $conventions : array( $conventions );
			foreach ( $conventions as $k => $v)
			{
				if ( in_array( $v, static::$_namingConventions ) )
				{
					static::_debug( 'Separator "' . $v . ' " already present!' , '' , 'Autoloader' );	
					continue;
				}
				static::$_namingConventions[ ] = $v; 
			}
		}
		/**
		* Returns the current included paths for  the autoloader. See @ref getDirs
		* @param	string		$type		directories , ns , files
		* @return	the PtcHandyMan::$_dirs property based on the $type argument
		*/
		public static function getDirs( $type = null )
		{ 
			if ( $type && !in_array( $type , array( 'directories' , 'ns' , 'files' ) ) ) // wrong parameter
			{ 
				trigger_error( 'No directories are present with the "' . 
								$type . '" parameter!' , E_USER_WARNING );
				return;
			}
			else if ( $type && !@static::$_dirs[ $type ] ) { return false; } // no values in array
			if ( @static::$_dirs[ $type ] ) {  return static::$_dirs[ $type ]; } // return value
			return @static::$_dirs;								// return all values
		}
		/**
		* Helper method to retrieve paths for the application. See @ref usingAddedPath
		* @param	string		$name		the path to return stored in the PtcHandyMan::$_appPaths property
		* @return	the PtcHandyMan::$_appPaths property based on the $name argument
		*/
		public static function getAppPaths( $name = null )
		{
			if ( empty( static::$_appPaths ) ) { static::_buildAppPaths( ); } // build paths once
			if ( !$name ) { return static::$_appPaths; } // return all application paths by default
			if ( !@array_key_exists( $name , static::$_appPaths ) )
			{
				trigger_error( 'No paths found with the option "' . $name . '"!' , E_USER_WARNING );
				return;
			}
			return static::$_appPaths[ $name ];
		}
		/**
		* Adds paths to the PtcHandyMan::$_appPaths property for later usage. See @ref addingAppPath 
		* @param	array 		$paths		array of paths to add
		*/
		public static function addAppPaths( $paths )
		{
			if ( empty( static::$_appPaths ) ) { static::_buildAppPaths( ); } // build paths once
			foreach ( $paths as $k => $v )
			{
				if ( !$path = @realpath( $v ) )
				{
					trigger_error( 'The file or path "' . $v .
								'" does not exists or is not accessible!' , E_USER_ERROR ); 
					return;
				}
				@$add_paths[ $k ] = $path; 
			}
			static::$_appPaths = array_merge( static::$_appPaths , $add_paths );
		}
		/**
		* Retrieves inaccessible properties from a class or object. See @ref read_properties
		* @param	mixed		$object		the name of the class or the initialized object
		* @param	string		$propertyName	the name of the property
		* @return	the value of the property if found.
		*/
		public static function getProperty( $object , $propertyName )
		{
			if ( !$object ){ return null; }
			if ( is_string( $object ) )	// static property
			{
				if ( !class_exists( $object ) ){ return null; }
				$reflection = new \ReflectionProperty( $object , $propertyName );
				if ( !$reflection ){ return null; }
				$reflection->setAccessible( true );
				return $reflection->getValue( );
			}
			$class = new \ReflectionClass( $object );
			if ( !$class ){ return null; }
			if( !$class->hasProperty( $propertyName ) ) // check if property exists
			{
				trigger_error( 'Property "' . $propertyName . '" not found in class "' . 
									get_class( $object ) . '"!' , E_USER_WARNING );
				return null;
			}
			$property = $class->getProperty( $propertyName );
			$property->setAccessible( true );
			return $property->getValue( $object );
		}		
		/**
		* Load classes automatically with namespaces support based on folder structure. 
		* See @ref adding_dirs
		* @param	string 	$class		the name of the class to autoload
		* @return	true if a file has been loaded, false otherwise.
		*/
		public static function load( $class )
		{
			/* check files array first  if class matches any name */
			if ( @array_key_exists( $class , @static::$_dirs[ 'files' ] ) )
			{
				$msg = array( 'file' => static::$_dirs[ 'files' ][ $class ] , 'class' => $class );
				static::_debug( $msg , 'Included class file' , 'Autoloader' );
				require_once( static::$_dirs[ 'files' ][ $class ] );
				return true;
			}
			else if ( strpos( $class , '\\' ) )	// try namespace first
			{
				$folders = explode( '\\' , $class );
				if ( @static::$_dirs[ 'ns' ][ $folders[ 0 ] ] )
				{
					$path = static::$_dirs[ 'ns' ][ $folders[ 0 ] ];
					unset( $folders[ 0 ] );
					$class_name = end( $folders );
					if ( sizeof( $folders ) > 1 )
					{
						array_pop( $folders );
						foreach ( $folders as $k => $v ) { $path .= DIRECTORY_SEPARATOR . $v; }
					}
					if ( !@realpath( $path ) )
					{
						trigger_error( 'The path "' . $path . 
									'" does not exists or is not accessible!' , E_USER_ERROR ); 
						return false;
					}
					if ( $found = static::_loadClass( $class_name , $path , $class ) ) { return true; }
				}
			}
			else if ( @static::$_dirs[ 'directories' ] )	// try all paths in directories array
			{
				foreach ( static::$_dirs[ 'directories' ] as $k => $v ) 
				{ 
					if ( $found = static::_loadClass( $class , $v ) ) { return true; } 
				}
			}
			return false;
		}
		/**
		* Paths for directories to autoload classes
		*/
		protected static $_dirs = array();
		/**
		* Separators for naming conventions
		*/
		protected static $_separators = array( );
		/**
		* Naming conventions to attempt to load with the autoloader
		*/		
		protected static $_namingConventions = array( );
		/**
		* Application paths property
		*/
		protected static $_appPaths = array( );
		/**
		* Classes autoloader engine, tries various possibilities for file names
		* @param	string		$class		the class name without namespaces
		* @param	string		$path		the full path to the file
		* @param	string		$namespace	the class name with namespace if present
		* @return	true if a file is found, false otherwise
		*/
		protected static function _loadClass( $class , $path , $namespace = null )
		{
			$class_name = ( $namespace ) ? $namespace : $class;
			$path = $path . DIRECTORY_SEPARATOR;
			if ( file_exists( $path . $class . '.php' ) )	// try the file
			{
				static::_debug( array( 'file' => $path . $class . '.php' , 'class' => $class_name ),
												'Included class file' , 'Autoloader' ); // debug
				require_once(  $path . $class . '.php');
				return true;
			}
			else if ( file_exists( $path . $new_file = strtolower( $class ) . '.php' ) )	// try the file lowercase
			{
				static::_debug( array( 'file' => $path . $new_file , 'class' => $class_name ),
												'Included class file' , 'Autoloader' ); // debug
				require_once(  $path . $new_file );
				return true;
			}
			else{ return static::_guessFileName( $class , $path , $namespace ); } // use an engine to guess the file
			return false;
		}
		/**
		* Tries to guess the filename to load based on the naming conventions and the separator properties
		* @param	string		$class		the class name without namespaces
		* @param	string		$path		the full path to the file
		* @param	string		$namespace	the class name with namespace if present
		* @return	true if a file is found, false otherwise
		*/
		protected static function _guessFileName( $class , $path , $namespace = null )
		{
			$class_name = ( $namespace ) ? $namespace : $class;
			foreach ( static::$_separators as $sep )	// try separators and naming conventions combinations
			{
				foreach ( static::$_namingConventions as $convention )
				{
					$filename = str_replace( array( '{SEP}' , '{CLASS}' ) , 
										array( $sep , $class ) , $convention );
					if ( file_exists( $path . $filename  . '.php') ) // try new filename
					{
						static::_debug( array( 'file' => $path . $filename . '.php' , 
								'class' => $class_name ) , 'Included class file' , 'Autoloader' ); // debug
						require_once(  $path . $filename .'.php');
						return true;
					}
					else if ( file_exists( $path .  $new_file = strtolower( $filename ) . '.php') ) // try lower case
					{
						static::_debug( array( 'file' => $path . $new_file  , 'class' => $class_name ) , 
												'Included class file' , 'Autoloader' ); // debug
						require_once(  $path . $new_file );
						return true;
					}
					else if ( $sep != '_')	// try replacing "_" with other separators in class name with lowercase
					{
						$replaced = str_replace( '_' , $sep , $filename ); // try new file name
						if ( file_exists( $path . $replaced . '.php' ) )
						{
							static::_debug( array( 'file' =>$path . $replaced . '.php' , 
									'class' => $class_name ) , 'Included class file','Autoloader');	// debug
							require_once( $path . $replaced . '.php'); 
							return true; 
						}
						else if ( file_exists( $file = $path . strtolower( $replaced ) . '.php' ) )	// lowercase
						{
							static::_debug( array( 'file' => $file , 
								'class' => $class_name ) , 'Included class file' , 'Autoloader' ); // debug
							require_once( $file ); 
							return true; 
						}
					}
				}
			}
			return false;
		}
		/**
		* Builds application paths for the first time when PtcHandyMan::getAppPath( ) is called.
		*/
		protected static function _buildAppPaths( )
		{
			static::$_appPaths = array
			(
				'docRoot' 	=> 	@$_SERVER[ 'DOCUMENT_ROOT' ] , // the document root folder
				'handyMan'	=>	dirname( __FILE__ ) // the directory where this file resides
			);
		}
		/**
		* Adds a directory to the PtcHandyMan::$_dirs array to autoload classes
		* @param	string		$directory		the full path to the directory
		* @return	false if directory is already present, true otherwise
		*/
		protected static function _addDirectory( $directory )
		{
			if ( @in_array( $directory , @static::$_dirs[ 'directories' ] ) ) // check if dir is already present
			{
					static::_debug( $directory , 'Path already exists!' , 'Autoloader' );	// debug
					return false;
			}
			@static::$_dirs[ 'directories' ][ ] = $directory;
			return true;
		}
		/**
		* Adds a directory to the PtcHandyMan::$_dirs array to load classes that use namespaces
		* @param	string		$namespace		the namespace for the path toa utoload classes
		* @param	string		$directory		the full path to the directory
		* @return	true if directory is not present, triggers an error otherwise 
		*/		
		protected static function _addNamespaceDirectory( $namespace , $directory )
		{
			if ( @array_key_exists( $namespace , @static::$_dirs[ 'ns' ] ) ) // check if dir is already present
			{
				trigger_error( 'Cannot redeclare namespace "' . $namespace . '"!' , E_USER_ERROR );
				return false;
			}
			@static::$_dirs[ 'ns' ][ $namespace ] = $directory;
			return true;
		}
		/**
		* Send messsages to the PtcDebug class if present
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement		some statement if required
		* @param	string		$category		a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ) { return false; }
			return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 'bufferLog' ) ,  
											array( $string , $statement , $category )  );
		}
	}