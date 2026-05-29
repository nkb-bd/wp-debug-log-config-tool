# Changelog

All notable changes to the Debug Log Manager Tool plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.6] - 2026-05-29

### Fixed
- AJAX router dispatch now returns a JSON 404 for unknown or empty actions instead of throwing an uncaught exception that surfaced as a critical PHP error in wp-admin.
- Admin-bar debug toggle assets (inline style + inline jQuery script) are scoped to pages that actually render the toggle, fixing Vue 2 conflicts with JetEngine and Crocoblock editor screens.

### Changed
- Bumped `Tested up to` to 7.0, `Requires at least` to 6.0, and `Requires PHP` to 7.4 in `readme.txt`.
- Refreshed `readme.txt` tags and description copy to lead with the queries users actually search for on WordPress.org.
- Added `Requires at least`, `Requires PHP`, and `Tested up to` to the `plugin.php` header so the in-WordPress plugin updater agrees with the readme.

## [3.0.5] - 2026-05-27

### Added
- Added a last fatal error snapshot so the log screen can surface the most recent fatal error captured by WordPress shutdown handling.
- Added GitHub Pages starter documentation for the safe first-run log-management workflow.
- Added read-only Terminal commands for inspecting options, posts, users, and metadata without arbitrary PHP execution.

### Changed
- Made activation and deactivation safe by no longer changing WordPress debug constants automatically.
- Changed settings and log loading paths so reading current state does not silently write missing debug constants or create a new debug log path.
- Changed Query Log ordering so the most expensive database queries appear first with execution time, duplicate count, and duplicate total time shown in milliseconds.
- Reframed README and WordPress.org copy around safe first-run log management, smooth upgrades, and optional advanced tools.
- Defaulted terminal access to disabled for fresh installs while preserving explicitly saved terminal settings.

### Fixed
- Fixed Query Log responses so database query rows are still returned when the debug log file path is missing or unchanged.

## [3.0.4] - 2026-05-22

### Changed
- Refreshed the plugin directory banner and icon with a softer visual style.

## [3.0.3] - 2026-05-22

### Changed
- Refreshed plugin directory assets and feature documentation.

## [3.0.2] - 2026-05-20

### Changed
- Show exact date and time for older debug log entries instead of relative age labels.
- Normalized public plugin name spelling in plugin metadata.

## [3.0.1] - 2026-05-19

### Changed
- Refreshed plugin metadata for the next maintenance release.

## [2.0.0] - 2023-11-15

### Added
- WP-CLI style command structure in terminal (e.g., `wp core version` instead of `wp-version`)
- Database commands with WP-CLI syntax (`wp db query`, `wp db tables`, etc.)
- Terminal settings page to enable/disable terminal and database features
- Super admin restriction for database commands
- Support for SQL queries with proper security measures
- Enhanced developer profile in support page
- Improved UI for support and notification pages
- Command auto-completion for WP-CLI style commands
- Support for colon syntax in commands (e.g., `wp:db:query`)
- Environment detection to hide development features in production

### Changed
- Reorganized terminal commands into logical categories
- Updated help command to show commands by category
- Improved error messages for better debugging
- Enhanced security for terminal commands
- Updated UI components with modern design elements

### Fixed
- Issue with SQL query execution where quotes were included in the query
- Terminal command parsing for complex arguments
- Case sensitivity issues in command validation
- Security improvements for database access

## [1.2.0] - 2023-10-01

### Added
- Debug Terminal feature for executing WordPress commands
- Auto-refresh functionality for real-time log monitoring
- Stack trace viewer for PHP error analysis
- Safe Mode for disabling problematic plugins
- Email notifications for debug log events

### Changed
- Improved UI for log viewing
- Enhanced filtering options for log entries
- Updated settings page with more configuration options

### Fixed
- Various bug fixes and performance improvements

## [1.1.0] - 2023-08-15

### Added
- Support for custom log file locations
- Advanced filtering options
- Export functionality for log data

### Changed
- Improved error handling
- Enhanced UI for better user experience

## [1.0.0] - 2023-07-01

### Added
- Initial release
- Basic log viewing functionality
- Simple filtering options
- Configuration settings
