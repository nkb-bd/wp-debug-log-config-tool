# Debug Log Manager Tool

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/debug-log-config-tool)](https://wordpress.org/plugins/debug-log-config-tool/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/rating/debug-log-config-tool)](https://wordpress.org/plugins/debug-log-config-tool/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/debug-log-config-tool)](https://wordpress.org/plugins/debug-log-config-tool/)

**Contributors:** pyrobd
**Tags:** debug, debug log, developer, tools
**Requires at least:** 5.6
**Tested up to:** 6.8
**Stable tag:** 3.0.5
**Requires PHP:** 5.6
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Debug Log Manager Tool is a WordPress debugging console for reading and managing debug logs first, with optional tools for changing debug constants, inspecting stack traces, running guarded terminal commands, and isolating plugin conflicts from your dashboard.

## Description

A comprehensive debugging toolkit for WordPress developers, agencies, and site administrators. The plugin opens safely as a log-management tool first: activation, deactivation, and smooth upgrades do not automatically change your debug constants or rewrite existing log paths. Debug configuration changes only happen when an authorized admin explicitly uses the plugin UI or admin-bar controls.

### 🎥 Quick Demo

[youtube https://youtu.be/moJPyyVfm3A]

### ✨ Key Features

- **Modern Debug Log Viewer**: View, search, filter, group, and clear debug log entries from a compact WordPress admin interface.
- **Readable Timeline Rows**: Scan log entries with severity labels, occurrence counts, plugin/source hints, exact timestamps, and copy-as-JSON actions.
- **Explicit Debug Configuration**: Toggle WP_DEBUG, WP_DEBUG_LOG, WP_DEBUG_DISPLAY, SCRIPT_DEBUG, and SAVEQUERIES only when you choose to save those settings.
- **Stack Trace Analysis**: Format PHP stack traces and database call chains so long fatal errors are easier to read.
- **Last Fatal Snapshot**: Capture the latest fatal error during shutdown and show it in the log screen when wp-admin is available again.
- **Large Log File Preview**: Open the current debug log file inside a modal and safely read the latest 1 MB of large files.
- **Query Inspector**: Examine database queries when SAVEQUERIES is enabled, including SQL, caller, execution time, and stack trace details.
- **Optional WP-CLI Style Terminal**: Run guarded WordPress-style commands directly from the browser with command history and auto-completion when terminal features are enabled.
- **Terminal Settings**: Enable or disable terminal access and database commands from a dedicated settings screen.
- **Safe Mode**: Temporarily isolate plugin conflicts by keeping selected plugins active and restoring the previous state when Safe Mode is turned off.
- **Email Notifications**: Configure notification email settings, daily summaries, and test email delivery for debug activity.
- **Admin Bar Debug Toggle**: Quickly enable or disable WP_DEBUG from the WordPress admin bar.
- **Dashboard Widget**: See recent debug log activity from the WordPress dashboard.
- **Custom Log Path Support**: Use filters to customize the debug log location.
- **Hardened Admin Routes**: AJAX actions use nonce and capability checks, with guarded file path handling for debug log access.

### 🔧 Debug Constants Available

The plugin can manage these constants from the admin UI. It does not enable, disable, or restore them automatically during activation, deactivation, or upgrades.

| Constant | First-run behavior | Description |
|----------|---------------|-------------|
| **WP_DEBUG** | Preserves current value | Enables WordPress debug mode |
| **WP_DEBUG_LOG** | Preserves current value | Saves all errors to a debug log destination |
| **SCRIPT_DEBUG** | Preserves current value | Uses development versions of core JS and CSS files |
| **WP_DEBUG_DISPLAY** | Preserves current value | Controls whether debug messages display on screen |
| **SAVEQUERIES** | Preserves current value | Saves database queries for analysis |

### 🛠️ Developer Tools

- **Terminal Commands**: Optionally use WP-CLI style commands like `wp core version` or `wp plugin list`
- **Database Explorer**: Optionally run SELECT queries and view results in a formatted table
- **Stack Trace Analysis**: Visualize error stack traces for easier debugging
- **Hook Inspector**: View all registered hooks and their callbacks
- **Environment Detection**: Using Laravel Mix to automatically hide development features in production
- **Copy JSON**: Copy structured log entries for support tickets or developer handoff
- **File Viewer**: Inspect the debug log file without leaving the WordPress admin screen

> **Developer API**: Use filters like `wp_dlct_log_file_path` and `dlct_debug_file_path` to customize log path behavior.

### 🛡️ Safe First-Run Behavior

- Activating the plugin does not automatically add, change, or remove debug constants.
- Deactivating the plugin does not restore, overwrite, or remove existing debug constants.
- Upgrades preserve the site's current debug constants and existing debug log path.
- Configuration changes happen only after an authorized admin uses the plugin UI or admin-bar controls.
- The default workflow is log management first; terminal and database tools are optional advanced features.

For a shorter GitHub Pages guide, see [docs/index.md](docs/index.md).

### 🚀 Improvements

We're constantly working to improve Debug Log Manager Tool. Here are some features we're planning to add in future releases:

#### Developer Tools
- **Code Snippets Runner**: Securely run PHP code snippets for testing (admin only)
- **Theme Template Debugger**: See which template files are being used on each page
- **Shortcode Analyzer**: Debug shortcodes and their rendered output
- **Cron Job Manager**: View, add, edit, and delete WordPress cron jobs
- **Transients Manager**: View and clean up transients in the database

#### Performance Tools
- **Memory Usage Profiling**: Track memory usage across different parts of your site
- **Page Load Time Analysis**: Measure and optimize page load performance
- **Asset Loading Monitor**: See which scripts and styles are loaded on each page

#### Enhanced Debugging
- **REST API Debugger**: Monitor and log REST API requests and responses
- **AJAX Request Logger**: Track AJAX requests for easier debugging
- **Conditional Debugging**: Enable debug logging only for specific pages or conditions

#### UI Improvements
- **Dark Mode**: Dark theme for the debugging interface
- **Customizable Dashboard**: Personalize which debug widgets appear
- **Export/Import Settings**: Save and load your debug configurations

Want to contribute or suggest features? Visit our [GitHub repository](https://github.com/nkb-bd/wp-debug-log-config-tool).


## Installation

1. Upload the plugin files to the `/wp-content/plugins/debug-log-config-tool` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to Tools-> Debug Logs screen to see the debug logs or access it from the top navbar.

On first run, review the existing debug log before changing configuration. The plugin will not change `wp-config.php` constants unless you explicitly use the debug settings UI or admin-bar controls.


## Frequently Asked Questions

### Do I need file manager/ftp or modify wp-config.php file?

No. You can inspect logs from the WordPress dashboard. If you want the plugin to change debug constants, use the debug settings screen and save the change explicitly.

### Does activation change WP_DEBUG or other debug constants?

No. Activation is safe by default and does not automatically change `WP_DEBUG`, `WP_DEBUG_LOG`, `WP_DEBUG_DISPLAY`, `SCRIPT_DEBUG`, or `SAVEQUERIES`.

### Does deactivation restore or overwrite debug constants?

No. Deactivation does not auto-restore previous values or overwrite constants. Existing site configuration remains under your control.

### What happens during upgrades?

Smooth upgrades preserve the current constants and log path. The plugin continues to read and manage logs without forcing a new debug configuration.

### Can it help after a fatal error?

Yes. The plugin stores a small last-fatal-error snapshot during WordPress shutdown and shows it in the log screen when wp-admin can load again. It does not expose a public emergency log viewer.

### Can I see full debug in dashboard?

Yes you can see a simple log in dashboard widget and nicely formatted view in the plugin

### What does safe mode do?
Safe mode will deactivate all the plugin except the selected one. When you turn safe mode off it will restore all the previous activated plugin.

## Screenshots
1. **Debug log timeline with filters, grouped events, and file tools**
2. **Terminal and database debugging commands**
3. **Safe Mode and settings screens**

## Changelog
### 3.0.5
- Added last fatal error snapshots so the log screen can show the most recent fatal captured during shutdown.
- Added read-only terminal commands for inspecting options, posts, users, and metadata without arbitrary PHP execution.
- Improved Query Log so missing or unchanged debug log files no longer hide collected database queries.
- Improved Query Log sorting and labels to show the most expensive queries first with execution time, duplicate count, and duplicate total time.
- Hardened first-run and upgrade behavior so activation, deactivation, settings reads, and log reads do not silently change debug constants or create a new debug log path.
- Defaulted terminal access to disabled for fresh installs while preserving saved terminal settings.
- Refreshed README and WordPress.org copy around safe first-run log management and optional advanced tools.

### 3.0.4
- Refreshed the plugin directory banner and icon with a softer visual style.

### 3.0.3
- Refreshed plugin directory assets and feature documentation.

### 3.0.2
- Improved debug log time labels so older entries show exact dates instead of misleading relative age text.
- Normalized the plugin name spelling on public plugin metadata.

### 3.0.1
- Maintenance release with refreshed plugin metadata.

### 3.0.0
- Hardened AJAX route verification to fail closed for invalid requests, missing permissions, and bad nonces.
- Added a guarded debug log file viewer endpoint with wp-content path containment and latest 1 MB reads for large files.
- Improved debug log migration safety by checking file containment, readability, and writability without overwriting the original default log.
- Reworked the admin interface with compact navigation, cleaner WordPress admin page spacing, and suppressed third-party notices on the plugin screen.
- Improved debug log readability with structured timeline rows, stack trace formatting, database call-chain formatting, safer text rendering, and cleaner expand/collapse behavior.
- Refined Safe Mode, Notifications, Terminal, Terminal Settings, and Overview screens for a more consistent settings-page experience.
- Fixed missing terminal settings routes, unresolved PrimeVue component registrations, Safe Mode submit state handling, admin-bar debug toggle styling, modal z-index, loader sizing, and empty feature icons.

### 1.0.0
- Initial Version

### 1.4.4
- Fixed Refresh Log
- Added dashboard widget

### 1.4
- Clean UI
- Refresh Log
- Email Notification

### 1.4.2
- New Constants
- Removed database dependency

### 1.4.5
- Fixed refresh

### 1.5
- Fixed Vulnerability of debug log file. Generating random file for debug.
- Added a new safe mode which will turn off all plugins excluding selected ones.

### 1.5.2
- Added query logs

### 1.5.3
- Fix footer text on all page

### 2.0.0
- Added WP-CLI style command structure in terminal (e.g., `wp core version` instead of `wp-version`)
- Added database commands with WP-CLI syntax (`wp db query`, `wp db tables`, etc.)
- Added terminal settings page to enable/disable terminal and database features
- Added super admin restriction for database commands
- Added support for SQL queries with proper security measures
- Added stack trace visualization for better error analysis
- Enhanced developer profile in support page
- Improved UI for support and notification pages
- Added command auto-completion for WP-CLI style commands
- Added support for colon syntax in commands (e.g., `wp:db:query` instead of `wp db query`)
- Added environment detection to hide development features in production
- Reorganized terminal commands into logical categories (core, plugin, theme, db, etc.)
- Updated help command to show commands by category with organized sections
- Improved error messages for better debugging and troubleshooting
- Enhanced security for terminal commands (preventing SQL injection, restricting destructive commands)
- Fixed issue with SQL query execution where quotes were included in the query
- Fixed terminal command parsing for complex arguments
- Fixed case sensitivity issues in command validation
