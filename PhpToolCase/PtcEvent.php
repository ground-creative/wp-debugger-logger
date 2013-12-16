<?php

	namespace PhpToolCase;

	/**
	* PHP TOOLCASE EVENT DISPATCHER CLASS
	* PHP version 5.3
	* @category 	Library
	* @version	0.9.2
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	class PtcEvent
	{
		/** 
		* Alias of PtcEvent::getEvents( ). See @ref getting_events
		*/
		public static function getEvent( $name = null ){ return static::getEvents( $name ); }
		/**
		* Registers the component with a constant for ptc helpers functions. See @ref register_component
		*/
		public static function register( )
		{
			$namespace = @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
			if ( !defined( '_PTCEVENT_' . $namespace ) ) // declare the class namespace
			{
				@define( '_PTCEVENT_' . $namespace , get_called_class( ) ); 
			}
		}
		/**
		* Adds a listener to an event. See @ref adding_events
		* @param	string		$event	the event name, ex: "event.sub_event"
		* @param	mixed		$callback	a valid callback ( closure , function , class  )
		* @param	numeric	$priority	a numeric value, higher values will execute first
		* @return	true if event was added succefully, throws an error otherwise
		*/
		public static function listen( $event , $callback , $priority = 0 )
		{
			static::register( $class = null );
			$event = explode( '.' , $event );
			if ( sizeof( $event ) < 1 )
			{
				trigger_error( 'Invalid format, all event names must use "."!' , E_USER_ERROR );
				return false;
			}
			if ( $callback instanceof Closure || is_callable( $callback ) ){ $call = $callback; }
			else
			{
				$try = explode( '@' , $callback );
				if ( @class_exists( $try[ 0 ] ) )
				{
					$method = ( sizeof( $try ) > 1 ) ? $try[ 1 ] : 'handle';
					$call = array( new $try[ 0 ] , $method );
				}
				else	// no valid callback found
				{
					trigger_error( $callback . ' is not a valid callback parameter!' , E_USER_ERROR );
					return false;
				}
			}
			if ( $event[ 1 ] === '*' ) // add a wildcard
			{
				$debug = ' wildcard to ';
				if ( !array_key_exists( $event[ 0 ] , static::$_wildCards ) )
				{
					static::$_wildCards[ $event[ 0 ] ] = array( );
					$debug =' new wildcard ';
				}
				static::$_wildCards[ $event[ 0 ] ][  ] = $call;
				static::_debug( array( $call ) , 'added ' . $debug . 
							'<b>"' . $event[ 0 ] . '.*"</b>' , 'Event Manager' );
				return true;
			}
			static::_addEvent( $event , $call , $priority ); // add event
			return true;
		}
		/**
		* Returs the current events. See @ref getting_events
		* @param	string		$name		previously added event name
		*/
		public static function getEvents( $name = null )
		{
			if ( $name && !array_key_exists( $name , static::$_events ) ){ return false; }
			return ( $name ) ? static::$_events[ $name ] : static::$_events;
		}
		/**
		* Removes previously added event listeners. See @ref removing_events
		* @param	string		$event	the name of the event
		* @param	numeric	$key		the numeric key for the event
		*/
		public static function remove( $event  , $key = null )
		{
			$event = explode( '.' , $event );
			if ( array_key_exists( 1 , $event ) )
			{ 
				if ( is_numeric( $key ) ) 
				{ 
					if ( !array_key_exists( $key , static::$_events[ $event[ 0 ] ][ $event[ 1 ] ] ) )
					{
						trigger_error( $key . ' not found in  <b>' . $event[ 0 ] . 
										'.' . $event[ 1 ] . '</b>!' , E_USER_WARNING );
						return false;
					}
					static::_debug( static::$_events[ $event[ 0 ] ][ $event[ 1 ] ][ $key ] , 
									'removing event <b>' . $event[ 0 ] . '.' . $event[ 1 ] . 
												'[ ' . $key . ' ]</b>' , 'Event Manager' );
					unset( static::$_events[ $event[ 0 ] ][ $event[ 1 ] ][ $key ] ); 
				}
				else
				{
					$debug = array_pop( static::$_events[ $event[ 0 ] ][ $event[ 1 ] ] ); 
					static::_debug( $debug , 'removing last event from <b>' . 
									$event[ 0 ] . '.' . $event[ 1 ] . '</b>' , 'Event Manager' );
				} 
				return true;
				
			}
			if ( @empty( static::$_events[ $event[ 0 ] ][ $event[ 1 ] ] ) )
			{ 
				unset( static::$_events[ $event[ 0 ] ][ $event[ 1 ] ] ); 
			}
			if ( @empty( static::$_events[ $event[ 0 ] ] ) ){ unset( static::$_events[ $event[ 0 ] ] ); }
		}
		/**
		* Fires an event. See @ref firing_events 
		* @param	string		$event	the event name to fire
		* @param	array		$data		an array with the data you wish to pass to the listeners
		*/
		public static function fire( $event , $data )
		{
			static::register( );
			$main = $event;
			$event = explode( '.' , $event );
			if ( !array_key_exists( $event[ 0 ] , static::$_events ) || sizeof( $event ) < 1 || 
						!array_key_exists( $event[ 1 ] , static::$_events[ $event[ 0 ] ] ) )
			{
				trigger_error( 'No listeners defined named "' . $main . '"!' , E_USER_WARNING );
				return false;
			}
			$events = static::$_events[ $event[ 0 ] ][ $event[ 1 ] ];
			$events = array_reverse( $events ); // reverse the array before sorting priority
			uasort( $events , function ( $a , $b ){ return $b[ 'priority' ] - $a[ 'priority' ]; } );
			$events = array_map( function ( $i ){ return $i[ 'callback' ]; }, $events );
			if ( array_key_exists( $event[ 0 ] , static::$_wildCards ) ) // run wildcards before events
			{
				$a = 0;
				foreach ( static::$_wildCards[ $event[ 0 ] ] as $wildcard )
				{
					$data = ( is_array( $data ) ) ? $data : array( $data );
					static::_debug( array( 'callback' => $wildcard , 'data' => array( $data , $main ) ) , 
					'firing wildcard <b>' . $event[ 0 ] . '.' . $event[ 1 ] . '[ ' . $a . ' ]</b>' , 'Event Manager' );
					$a++;
					if ( false === static::_run( $wildcard , array( $data , $main ) ) ){ return; }
				}
			}
			$a = 0;
			foreach ( $events as $sub_event )	// run events
			{ 
				static::_debug( array( 'callback' => $sub_event , 'data' => $data ) , 
					'firing event <b>' . $event[ 0 ] . '.' . $event[ 1 ] . '[ ' . $a . ' ]</b>' , 'Event Manager' );
				$a++;
				if ( false === static::_run( $sub_event , $data ) ){ return; } 
			}
			return;
		}
		/**
		* Property that holds the events
		*/
		protected static $_events = array( );
		/**
		* Property that holds the wildcards
		*/
		protected static $_wildCards = array( );
		/**
		* Adds events to the class
		* @param	string		$event	some name for the event
		* @param	mixed		$call		some valid callback
		* @param	numeric	$priority	the priority for the event
		*/
		protected static function _addEvent( $event , $call , $priority = 0 )
		{
			$debug = '1 more event to ';
			if ( !array_key_exists( $event[ 0 ] , static::$_events ) )
			{
				static::$_events[ $event[ 0 ] ] = array( );
				$debug = 'new event '; 
			}
			if ( !array_key_exists( $event[ 1 ] , static::$_events[ $event[ 0 ] ] ) )
			{
				static::$_events[ $event[ 0 ] ][ $event[ 1 ] ] = array( );
				$debug = 'new event '; 
			}
			static::$_events[ $event[ 0 ] ][ $event[ 1 ] ][ ] = 
							array( 'priority' => $priority , 'callback' => $call );
			static::_debug( @end( array_values( static::$_events[ $event[ 0 ] ][ $event[ 1 ] ] ) ) , 
				'added ' . $debug . '<b>"' . $event[ 0 ] . '.' . $event[ 1 ] . '"</b>' , 'Event Manager' );
		}
		/**
		* Runs an event with call_user_func_array
		* @param	string		$event	a valid callback
		* @param	mixed		$data		arguments for the event callbacks
		*/
		protected static function _run( $event , $data ) 
		{
			$data = ( is_array( $data ) ) ? $data : array( $data );
			return call_user_func_array( $event , $data );
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
											array( $string , $statement , $category ) );
		}
	}