---
title: Debug Log Manager Tool - Safe WordPress Debug Log Viewer
description: Safe WordPress debugging from wp-admin. View debug logs, inspect fatal errors, and change debug constants only when you choose.
---

# Debug Log Manager Tool

Safe WordPress debugging from one admin screen.

Debug Log Manager Tool is a WordPress plugin for viewing debug logs, inspecting fatal errors, and managing debug constants from wp-admin. It is designed to be safe on first run: it starts as a log-management tool and only changes WordPress debug configuration when an authorized admin explicitly asks it to.

[Download on WordPress.org](https://wordpress.org/plugins/debug-log-config-tool/) · [View source on GitHub](https://github.com/nkb-bd/wp-debug-log-config-tool) · [More by nkb-bd](https://github.com/nkb-bd)

## Why Use It

- View WordPress debug logs without FTP or file manager access.
- Inspect the latest fatal error snapshot when wp-admin becomes available again.
- Search, filter, group, copy, and clear log entries from the dashboard.
- Review `WP_DEBUG`, `WP_DEBUG_LOG`, `SCRIPT_DEBUG`, `WP_DEBUG_DISPLAY`, and `SAVEQUERIES` before changing anything.
- Keep advanced tools such as terminal, database inspection, Safe Mode, and notifications optional.

## Safe First Run

- Activation does not automatically add, change, or remove debug constants.
- Deactivation does not automatically restore, overwrite, or remove debug constants.
- Upgrades preserve the site's existing debug constants and debug log path.
- Debug configuration changes happen only after an explicit action in the plugin UI or admin-bar controls.
- The recommended first step is to inspect the current log, not to change configuration.

## Log Management First

Use the debug log screen to:

- View recent log entries.
- Search and filter log output.
- Group repeated events.
- Copy structured log details for support or developer handoff.
- Preview large log files without downloading them over FTP.
- Clear logs when you intentionally choose to do so.
- Review the latest fatal error snapshot if WordPress captured one during shutdown.

This keeps the default workflow focused on understanding the current site state before changing debug settings.

## Debug Configuration

The plugin can help manage common WordPress debug constants:

- `WP_DEBUG`
- `WP_DEBUG_LOG`
- `WP_DEBUG_DISPLAY`
- `SCRIPT_DEBUG`
- `SAVEQUERIES`

These settings are not changed automatically. To update them, open the plugin's debug settings screen or admin-bar controls and choose the values you want.

## Upgrades

Smooth upgrades are intended to preserve existing configuration. If your site already has custom debug constants or a custom log path, the plugin should continue using that current state instead of replacing it during an update.

## Fatal Errors

The plugin keeps fatal-error handling conservative. It can store a small last-fatal-error snapshot during WordPress shutdown and show that snapshot in the log screen when wp-admin is available again.

It does not create a public emergency log viewer. Logs and fatal details stay behind normal WordPress admin authentication.

## Optional Advanced Tools

Advanced tools are available for development and troubleshooting workflows:

- Stack trace formatting.
- Query inspection when `SAVEQUERIES` is enabled.
- Optional guarded terminal commands.
- Optional database explorer tools.
- Safe Mode for temporary plugin-conflict isolation.

Enable and use advanced tools only when they fit the troubleshooting task. The normal path is to review logs first, then change settings only when needed.

## Links

- [WordPress.org plugin page](https://wordpress.org/plugins/debug-log-config-tool/)
- [GitHub repository](https://github.com/nkb-bd/wp-debug-log-config-tool)
- [nkb-bd on GitHub](https://github.com/nkb-bd)

## SEO Keywords

WordPress debug log viewer, WordPress debug plugin, WP_DEBUG manager, fatal error snapshot, WordPress debug constants, WordPress Safe Mode debugging, wp-admin log viewer.
