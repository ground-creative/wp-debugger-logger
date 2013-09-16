=== PhpToolCase Debugger & Logger ===

Contributors: ifsale
Tags: debug
Tested up to: 3.6
Stable tag: 0.5
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
