=== Debug Log Manager Tool ===
Contributors: pyrobd
Donate link:
Tags: debug, log, developer, tools,remote debug
Requires at least: 5.6
Tested up to: 6.8
Stable tag: 3.0.2
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Debug Log Manager Tool simplifies debugging. Toggle logging, queries, view levels, and clear logs from the dashboard.

== Description ==

A comprehensive debugging toolkit for WordPress developers and site administrators. This plugin gives you complete control over WordPress debugging without editing wp-config.php files or using FTP.

= Quick Demo =

[youtube https://youtu.be/D4K5zsLnILw]

= Key Features =

* **WP-CLI Style Terminal**: Execute WordPress commands directly from your browser with syntax highlighting and auto-completion
* **Database Tools**: Run SQL queries, view table structures, and optimize your database (super admin only)
* **Debug Constants Manager**: Toggle all WordPress debug constants with a single click
* **Log Viewer**: View, filter, and analyze debug logs with syntax highlighting and error categorization
* **Query Inspector**: Examine database queries with SAVEQUERIES support
* **Email Notifications**: Get alerts when new errors appear in your logs
* **Safe Mode**: Quickly disable all plugins except selected ones for troubleshooting
* **Custom Log Paths**: Set custom log file locations with filter support

= Debug Constants Available =

* **WP_DEBUG** - Default Value: true - Enables WordPress debug mode
* **WP_DEBUG_LOG** - Default Value: true - Saves all errors to a debug.log file
* **SCRIPT_DEBUG** - Default Value: false - Uses development versions of core JS and CSS files
* **WP_DEBUG_DISPLAY** - Default Value: false - Controls whether debug messages display on screen
* **SAVEQUERIES** - Default Value: false - Saves database queries for analysis

= Developer Tools =

* **Terminal Commands**: Use WP-CLI style commands like `wp core version` or `wp plugin list`
* **Database Explorer**: Run SELECT queries and view results in a formatted table
* **Stack Trace Analysis**: Visualize error stack traces for easier debugging
* **Hook Inspector**: View all registered hooks and their callbacks
* **Environment Detection**: Development features are automatically hidden in production

> **Developer API**: Apply custom filters like `apply_filters('wp_debuglog_log_file_path', $file);` to extend functionality

Please note: Constant values will be restored on plugin deactivation as it was before activating the plugin.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/debug-log-config-tool` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to Tools-> Debug Logs screen to see the debug logs or access it from the top navbar.


== Frequently Asked Questions ==

= Do I need file manager/FTP or modify wp-config.php? =

No, just activate the plugin and turn off/on debug mode from plugin settings

= Can I see full debug in dashboard?  =

Yes you can see a simple log in dashboard widget and nicely formatted view in the plugin

= What does safe mode do?  =
Safe mode will deactivate all the plugin except the selected one. When you turn safe mode off it will restore all the previous activated plugin.

== Screenshots ==
1. ** Plugin Settings **
1. ** Debug Log **

== Changelog ==

= 3.0.2 =
- Improved debug log time labels so older entries show exact dates instead of misleading relative age text.
- Normalized the plugin name spelling on public plugin metadata.

= 3.0.1 =
- Maintenance release with refreshed plugin metadata.

= 3.0.0 =
- Hardened AJAX route verification to fail closed for invalid requests, missing permissions, and bad nonces.
- Added a guarded debug log file viewer endpoint with wp-content path containment and latest 1 MB reads for large files.
- Improved debug log migration safety by checking file containment, readability, and writability without overwriting the original default log.
- Reworked the admin interface with a compact top navigation, cleaner WordPress admin page spacing, and suppressed third-party notices on the plugin screen.
- Improved debug log readability with structured timeline rows, stack trace formatting, database call-chain formatting, safer text rendering, and cleaner expand/collapse behavior.
- Refined Safe Mode, Notifications, Terminal, Terminal Settings, and Overview screens for a more consistent settings-page experience.
- Fixed missing terminal settings routes, unresolved PrimeVue component registrations, Safe Mode submit state handling, admin-bar debug toggle styling, modal z-index, loader sizing, and empty feature icons.

= 2.0.1 =
- Fix typo
- Fix memory issue

= 2.0.0 =
- Added WP-CLI style command structure in terminal (e.g., `wp core version`)
- Added database commands with WP-CLI syntax (`wp db query`, `wp db tables`, etc.)
- Added terminal settings page to enable/disable terminal and database features
- Added super admin restriction for database commands
- Added support for SQL queries with proper security measures
- Added stack trace visualization for better error analysis
- Help command to show commands by category with organized sections
- Enhanced security for terminal commands (preventing SQL injection, restricting destructive commands)
- Quick Debug Toggle from admin bar (WP_DEBUG)

= 1.5.3 =
- Fix footer text on all page

= 1.5.2 =
- Added query logs

= 1.5 =
- Fixed Vulnerability of debug log file. Generating random file for debug.
- Added a new safe mode which will turn off all plugins excluding selected ones.

= 1.4.5 =
- Fixed refresh

= 1.4.2 =
- New Constants
- Removed database dependency

= 1.4.4 =
- Fixed Refresh Log
- Added dashboard widget
- Clean UI
- Refresh Log
- Email Notification

= 1.0.0 =
- Initial Version
