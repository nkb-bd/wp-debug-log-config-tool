===  Debug Log - Manger Tool ===
Contributors: pyrobd
Donate link:
Tags: debug, debug log, developer, tools
Requires at least: 5.6
Tested up to: 6.6.1
Stable tag: 1.5.3
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The "Debug Log Config Tool" simplifies debugging. Toggle logging,queries , view levels, clear logs from dashboard.

== Description ==

A simple debug helper plugin. Check **Debug Log** from the dashboard. With Query Log & Email notification . It will trigger an **email notification** using wp-cron if there is any data in the log file.
Using WP_DEBUG_DISPLAY along with WP_DEBUG, debug messages can be controlled to show or not. By default,it is off so users will not see any debug info.

Use SAVEQUERIES to toggle saving database queries and view database queries from logs

[youtube https://youtu.be/moJPyyVfm3A]

* Enable or disable debug constants with a single click.

* See different highlighted log levels.
* Set custom log file paths for loading debug logs.
>  **apply_filters('wp_debuglog_log_file_path', $file);**
* Accessible from the dashboard's WordPress admin top nav bar.
* Simplifies the debugging process by managing to log debug without modifying the wp-config.php file directly.
* Use safe mode to quickly deactivate all plugins except selected ones, and undo the proccess with just a click.
* Use Query Logs to check all queries from **$wpdb->queries**, just toggle `SAVEQUERIES` from settings.

Debug Options Available

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

= Do I need file manager/ftp or modify wp-config.php fie  ? =

No, just activate the plugin and turn off/on debug mode from plugin settings

= Can I see full debug in dashboard?  =

Yes you can see a simple log in dashboard widget and nicely formatted view in the plugin

= What does safe mode do?  =
Safe mode will deactivate all the plugin except the selected one. When you turn safe mode off it will restore all the previous activated plugin.

== Screenshots ==
1. ** Plugin Settings **
1. ** Debug Log **

== Changelog ==
= 1.0.0 =
- Initial Version

= 1.4.4 =
- Fixed Refresh Log
- Added dashboard widget

= 1.4 =
- Clean UI
- Refresh Log
- Email Notification

= 1.4.2 =
- New Constants
- Removed database dependency

= 1.4.5 =
- Fixed refresh

= 1.5 =
- Fixed Vulnerability of debug log file. Generating random file for debug.
- Added a new safe mode which will turn off all plugins excluding selected ones.

= 1.5.2 =
- Added query logs

= 1.5.3 =
- Fix footer text on all page


