# Planned Pro Feature Ideas

This file captures paid-feature opportunities discovered from competitor review. It is GitHub-only planning content and is excluded from the WordPress.org deploy package through `.distignore`.

## Core Pro Wedge

The strongest Pro feature is **smart incident grouping**.

Most competitors can show a log. The paid value should be turning raw debug noise into a smaller list of actionable incidents that a developer, agency, or support team can understand quickly.

## Feature Ideas

### 1. Smart Incident Grouping

Group repeated errors into one incident with:

- Occurrence count.
- First seen and last seen timestamps.
- Severity trend.
- Main file and line.
- Likely source plugin, theme, WordPress core, or server path.
- Related stack trace frames.
- Mark resolved, ignore pattern, and reopen on recurrence.

### 2. Readable Stack Trace Mode

Convert long fatal/warning traces into structured frames:

- Plugin or theme frame.
- File and line.
- Function or method.
- Vendor/core frames collapsed by default.
- Highlight the likely application frame that caused the issue.

### 3. Plugin and Theme Attribution

Detect source ownership from file paths:

- `wp-content/plugins/{slug}` -> plugin name.
- `wp-content/themes/{slug}` -> theme name.
- `wp-includes` / `wp-admin` -> WordPress core.
- Server paths outside WordPress -> hosting/server issue.

This makes errors readable for non-SSH users and support teams.

### 4. Error Watch Rules

Allow admins to control alert noise:

- Alert only for fatal errors.
- Ignore known warnings or notices.
- Notify if an error repeats more than a threshold in a time window.
- Notify only when a new incident signature appears.

### 5. Debug Session Report

Create a one-click support workflow:

- Enable logging.
- Clear or checkpoint the current log.
- Reproduce the issue.
- Generate a sanitized report with environment, active plugins, theme, recent errors, and stack traces.

### 6. Shareable Sanitized Reports

Generate reports that remove sensitive data:

- Local absolute paths can be shortened.
- Emails, tokens, and secrets can be redacted.
- Plugin/theme/version context is preserved.
- Output can be copied or downloaded.

### 7. Safe Mode Plus

Expand Safe Mode into a conflict-finder:

- Binary-search plugin conflicts.
- Test a target admin/frontend URL between plugin sets.
- Restore the previous plugin state automatically.
- Save session notes for support teams.

### 8. Timeline View

Show error events next to site changes:

- Plugin activations/deactivations.
- Plugin/theme/core updates.
- Debug setting changes.
- Safe Mode sessions.
- New incident creation and resolution.

### 9. Integrations

Start with low-maintenance integrations:

- Slack webhook alerts.
- Discord webhook alerts.
- Generic webhook.
- Sentry/Grafana external links where configured.

## Suggested Free vs Pro Split

Free:

- Beautiful log viewer.
- Search and filters.
- Debug constants.
- View file modal.
- Terminal tools.
- Basic notifications.
- Basic Safe Mode.

Pro:

- Incident grouping.
- Stack trace cleaner.
- Source attribution.
- Ignore/watch rules.
- Slack, Discord, and webhook alerts.
- Debug session report.
- Safe Mode Plus conflict finder.

## Competitor Notes

- Query Monitor is broad and developer-heavy, especially around DB queries, hooks, scripts, styles, HTTP calls, and PHP errors.
- WP Debugging is simple and focused on debug constants.
- Error Log Monitor covers dashboard viewing, email alerts, clearing logs, and large-log handling.
- Error Log Monitor Pro validates the paid market for grouping, stack traces, and request context.
- SiteIntelix and LogWatch show that modern grouped log UI is now table stakes.
- The gap is a focused incident workflow that is readable inside WordPress admin and useful to agencies/support teams.

