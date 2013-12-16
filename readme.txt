=== PhpToolCase Debugger & Logger ===

Contributors: ifsale
Tags: debug
Tested up to: 3.6
Stable tag: 0.7
Requires at least: 3.1

A PHP Debugger & Logger to speed up the process of plugin development. 


== Description ==

A wordpress plugin for the ptcdebug class to speed up wordpress plugins development.

Main Features:

* Shows super globals vars ($GLOBALS, $_POST, $_GET, $_COOKIE ...)
* Shows php version and loaded extensions
* Replaces php built in error handler
* Catches exceptions
* Logs sql queries
* Monitors code and sql queries execution time
* Inspects variables for changes
* Dumps of all types of variable
* File inspector with code highlighter to view source code
* Sends messages to js console(Chrome only) for ajax scripts
* Code coverage analysis to check which lines of script where executed
* Function calls tracing
* Can search files for a string recursively

When WP_DEBUG is enabled the default php error handler can be replaced..

When SAVEQUERIES is enabled the mysql queries from the wordpress core will be shown in the sql panel.

Live Demo & API DOCS: http://phptoolcase.com/guides/ptc-debug-guide.html

Wiki Pages: https://github.com/ifsale/wp-debugger-logger/wiki

GitHub Repository: https://github.com/ifsale/wp-debugger-logger

Wordpress Repository: http://wordpress.org/plugins/debugger-logger/


== Installation ==

Download a .zip file and place the exctracted folder in the plugins directory of your wordpress installation.

To activate the debugger & logger panel use ?debug=true in the url.

Visit http://phptoolcase.com/guides/ptc-debug-guide.html for user guides and api documentation.


== Changelog ==

= 0.7 =
Upgraded the plugin to the new library version
Added event listeners for the admin options form.

= 0.6 =
Added namespaces to all classes but the main debugger class to be able to use the functions in the globlal scope,
and prevent other plugins from using the library in the global scope, in case they use other versions,
Updated the library to the newer version

= 0.5 =
First realease for the plugin