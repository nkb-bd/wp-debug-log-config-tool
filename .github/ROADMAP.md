# Debug Log Config Tool Roadmap

This file tracks planned product direction for GitHub contributors and maintainers. It is intentionally excluded from the WordPress.org deploy package through `.distignore`.

## Product Direction

Debug Log Config Tool should move beyond a simple log viewer and become a WordPress incident/debugging console for developers, agencies, and support teams.

The free plugin should remain useful for everyday debugging:

- Modern debug log viewer with search, filters, counts, and readable long-message handling.
- Debug constants/settings management for common WordPress debugging flags.
- Safe Mode for isolating plugin conflicts while preserving the previous active-plugin state.
- Debug file preview inside the admin UI.
- Basic terminal tools for controlled WordPress debugging commands.
- Basic email notification controls for important debug events.

The paid/pro opportunity is workflow automation around repeated incidents:

- Turn noisy repeated log lines into grouped incidents.
- Make stack traces easier to read and act on.
- Attribute errors to plugins, themes, WordPress core, or server paths.
- Notify only when something new or important happens.
- Generate support-ready reports that can be shared safely.

## Planned Free Features

### Log Viewer Refinements

- Keep dense, readable log rows inspired by developer tools rather than generic WordPress settings pages.
- Improve long-message expansion so only genuinely long entries open by default.
- Add better wrapping and stack trace formatting for fatal errors, warnings, and database errors.
- Keep copy JSON and view file actions available without taking over the row layout.

### Debug File Tools

- Open the active debug log file in an admin modal.
- Show file size and latest-read range when the file is very large.
- Add clear, download, and copy actions with capability and nonce checks.

### Safe Mode

- Preserve plugin state before isolation.
- Let admins select which plugins stay active.
- Restore the previous state cleanly when Safe Mode is disabled.
- Keep the UI compact enough to fit inside WordPress admin without wasting horizontal space.

### Notifications

- Support basic email settings.
- Keep notification cards compact and readable.
- Add clearer validation and test-email states.

## Planned Pro Features

See [PRO-FEATURES.md](./PRO-FEATURES.md) for the monetization-focused plan.

