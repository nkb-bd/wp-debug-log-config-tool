===  Debug Log - Manger Tool ===
Contributors: pyrobd
Donate link:
Tags: debug ,wp config, debug log, developer, tools
Requires at least: 5.6
Tested up to: 6.4.3
Stable tag: 1.5.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The "Debug Log Config Tool" simplifies debug log management. Enable/disable logging, view levels, clear logs, and set admin notifications from the dashboard

== Description ==

A simple debug helper plugin. Check **Debug Log** from the dashboard.Email notification can also be set. It will trigger an **email notification** using wp-cron if there is any data in the log file.
Using WP_DEBUG_DISPLAY along with WP_DEBUG, debug messages can be controlled to show or not. By default,it is off so users will not see any debug info.


* Enable or disable debug constants with a single click.

* See different highlighted log levels.
* Set custom log file paths for loading debug logs.
>  **apply_filters('wp_debuglog_log_file_path', $file);**
* Accessible from the dashboard's WordPress admin top nav bar.
* Simplifies the debugging process by managing to log debug without modifying the wp-config.php file directly.


Constants Available

1. **WP_DEBUG** :: Default Value : true
2. **WP_DEBUG_LOG**:: Default Value : true
3. **SCRIPT_DEBUG** :: Default Value : false
4. **WP_DEBUG_DISPLAY** :: Default Value : false
5. **SAVEQUERIES**:: Default Value : false

Please note: Constant values will be restored on plugin deactivation as it was before activating the plugin.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/debug-log-config-tool` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to Tools-> Debug Logs screen to see the debug logs or access it from the top navbar.


== Frequently Asked Questions ==

= Do I need to edit wp-config.php fie ? =

No activate the plugin and turn off/on debug constants from dashboard

= Can I add more debug constants ?  =

Currently, you can use the filter  apply_filters('dlct_constants', $constants);


== Screenshots ==
1. ** Plugin Settings **
1. ** Debug Log **

== Changelog ==
= 1.0.0
 -Initial Version

= 1.4.4
- Fixed Refresh Log
- Added dashboard widget
= 1.4
 - Clean UI
 - Refresh Log
 - Email Notification
= 1.4.2
 - New Constants
 - Removed database dependency
= 1.4.5
 - Fixed refresh
= 1.5
 - Fixed Vulnerability of debug log file. Generating random file for debug.
 - Added a new safe mode which will turn off all plugins excluding selected ones.



