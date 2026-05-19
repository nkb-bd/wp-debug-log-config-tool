# Plugin Audit Report — Debug Log Manager Tool
**Branch:** detached@8f0f3c1 | **Date:** 2026-05-19 | **Auditor:** Codex (5-workstream + Pass 6 verification)

---

## 1) Executive Summary

- Overall risk level: High until log rendering is escaped and the admin AJAX guard is made fail-closed.
- Severity snapshot:

| Severity | Count |
|---|---|
| CRITICAL | 0 |
| HIGH | 1 |
| MEDIUM | 5 |
| SUGGESTION | 6 |

- Top 3 risks:
  - Debug log rows render raw log content with `v-html`, creating a stored admin XSS path when attacker-controlled text reaches `debug.log`.
  - The shared AJAX guard returns on failed capability instead of terminating, so route safety depends on every controller remembering to verify again.
  - The current UI can be upgraded into the Fly/Grafana-style console, but first needs broken routes/components and async action gaps fixed.
- Audit scope notes:
  - Code evidence is from this worktree: `/Users/lukmannakib/.codex/worktrees/a2d0/debug-log-config-tool`.
  - In-app browser verification used `https://forms.test/wp-admin/admin.php?page=dlct_logs`, where plugin version `2.1.0` is active. This is useful for visual direction, but not a perfect runtime match for this worktree, whose header is `2.0.1`.
  - Browser routes checked: Logs, Settings, Safe Mode, Notification, Terminal, Terminal Settings, Support. Screenshot capture itself timed out in the in-app browser, but DOM and visible state snapshots loaded successfully.

## Fix Pass — 2026-05-19

- Fixed: raw log `v-html` rendering in `LogView.vue` was replaced with text rendering.
- Fixed: terminal output `v-html` rendering in `DebugTerminal.vue` and `TerminalEmulator.vue` was replaced with text rendering.
- Fixed: shared AJAX route verification now fails closed for invalid context, missing capability, and invalid nonce.
- Fixed: terminal settings routes are now registered in `app/routes.php`.
- Fixed: Safe Mode submit no longer uses `this.state` inside `script setup`.
- Fixed: missing PrimeVue `Divider` and `Chip` registrations were added.
- Fixed: default debug-log migration now checks path containment/readability/writability and no longer overwrites the original default log.
- UI pass: the app now uses a left-sidebar admin shell and a redesigned Logs screen with screenshot-style header actions, tabs, stats, timeline rows, severity chips, and copy JSON controls.
- Verification: PHP syntax checks passed and `npm run prod` compiled successfully.

## 2) Table of Contents

- [HIGH-01: Log entries render raw HTML in the admin UI](#high-01-log-entries-render-raw-html-in-the-admin-ui)
- [MEDIUM-01: Shared AJAX authorization guard is fail-open for controller routes](#medium-01-shared-ajax-authorization-guard-is-fail-open-for-controller-routes)
- [MEDIUM-02: Terminal settings screen calls routes that are not registered](#medium-02-terminal-settings-screen-calls-routes-that-are-not-registered)
- [MEDIUM-03: Safe Mode submit uses `this.state` inside script setup](#medium-03-safe-mode-submit-uses-thisstate-inside-script-setup)
- [MEDIUM-04: Default debug log migration reads and overwrites files without guards](#medium-04-default-debug-log-migration-reads-and-overwrites-files-without-guards)
- [MEDIUM-05: PrimeVue components are used without registration](#medium-05-primevue-components-are-used-without-registration)
- [SUGGESTION-01: Rebuild the admin shell to match the reference layout](#suggestion-01-rebuild-the-admin-shell-to-match-the-reference-layout)
- [SUGGESTION-02: Redesign Logs as the primary product surface](#suggestion-02-redesign-logs-as-the-primary-product-surface)
- [SUGGESTION-03: Move dangerous terminal features behind Pro or advanced gates](#suggestion-03-move-dangerous-terminal-features-behind-pro-or-advanced-gates)
- [SUGGESTION-04: Turn alerting and integrations into the clearest Pro path](#suggestion-04-turn-alerting-and-integrations-into-the-clearest-pro-path)
- [SUGGESTION-05: Replace Support with System Health and Diagnostics](#suggestion-05-replace-support-with-system-health-and-diagnostics)
- [SUGGESTION-06: Add UI state tests after the redesign](#suggestion-06-add-ui-state-tests-after-the-redesign)

## 3) Findings by Severity

### Critical

No confirmed Critical findings in this pass.

### High

#### HIGH-01: Log entries render raw HTML in the admin UI

- Area: Security
- Confidence: High
- File:line: `resources/views/LogView.vue:49`, `resources/views/LogView.vue:133`, `app/Controllers/LogController.php:268-287`, `app/Controllers/LogController.php:374-380`
- Evidence: The Vue table renders `slotProps.data.details` and dialog details through `v-html`; the PHP parser returns log text as `details` without escaping.
- Impact: Any plugin, theme, request path, or server error that writes attacker-controlled HTML into the debug log can execute script when an administrator opens the log viewer.
- Recommended fix: Treat log content as text by default. Escape server-side with `esc_html` or render client-side with mustache text binding. If highlighting is needed, tokenize known error labels into safe spans instead of rendering arbitrary HTML.
- Task statement: Replace all raw `v-html` log/detail rendering with safe text rendering and add a regression fixture containing `<img onerror=...>` in a log line.
- Verifier note: The route is admin-only, but the sink is confirmed and log content is not limited to trusted plugin output.

### Medium

#### MEDIUM-01: Shared AJAX authorization guard is fail-open for controller routes

- Area: Security
- Confidence: High
- File:line: `app/Classes/AjaxHandler.php:20-23`, `app/Classes/AjaxHandler.php:31-41`
- Evidence: `verify()` returns when `current_user_can()` fails, then `handleRequest()` still calls `Router::load(...)->direct(...)`.
- Impact: Current controllers mostly call `Helper::verifyRequest()`, but the shared guard does not actually protect the route layer. Any new route without a second controller-level check becomes exposed to any logged-in user with a nonce.
- Recommended fix: Make `AjaxHandler::verify()` fail closed with `wp_send_json_error(..., 403)` and `wp_die()` or return a boolean that `handleRequest()` must check before routing.
- Task statement: Patch the shared AJAX guard to terminate on failed capability and missing/invalid nonce, then add a test or manual route probe for a subscriber.

#### MEDIUM-02: Terminal settings screen calls routes that are not registered

- Area: Traceability
- Confidence: High
- File:line: `resources/views/TerminalSettingsView.vue:82-86`, `resources/views/TerminalSettingsView.vue:103-111`, `app/routes.php:4-14`, `app/Controllers/TerminalSettingsController.php:13-53`
- Evidence: The Vue screen calls `get_terminal_settings` and `update_terminal_settings`; the controller exists, but neither route is registered in `app/routes.php`.
- Impact: In this worktree, Terminal Settings cannot load or save through the plugin router.
- Recommended fix: Register GET and POST routes for `TerminalSettingsController@get` and `TerminalSettingsController@update`.
- Task statement: Add the missing terminal-settings routes and verify the screen loads without staying on the spinner.

#### MEDIUM-03: Safe Mode submit uses `this.state` inside script setup

- Area: Traceability
- Confidence: High
- File:line: `resources/views/SafeMode.vue:204-212`
- Evidence: `script setup` has no component `this`, but the request payload uses `safe_mode: this.state.isSafeMode`.
- Impact: Clicking the Safe Mode update button can throw before the request is sent, so the screen appears functional while the action is broken.
- Recommended fix: Use `state.isSafeMode` directly and always reset `update.isLoading` in a `finally` block.
- Task statement: Fix the Safe Mode submit payload and browser-test toggling from inactive to active and back.

#### MEDIUM-04: Default debug log migration reads and overwrites files without guards

- Area: Security
- Confidence: Med
- File:line: `app/Controllers/LogController.php:603-618`
- Evidence: `maybeCopyLogFromDefaultLogFile()` calls `file_get_contents($defaultLogPath)`, writes to `$currentLogPath`, then replaces the default log content with `Content Moved` without existence, readability, writability, size, or containment checks.
- Impact: On sites without a default `debug.log`, this can emit warnings. On sites with valuable logs, it destructively rewrites the default file.
- Recommended fix: Check `is_readable`, `is_writable`, path containment under `WP_CONTENT_DIR`, and preserve or rotate the old file instead of replacing it with a marker string.
- Task statement: Make log migration non-destructive and add tests or manual checks for missing, unreadable, large, and existing log files.

#### MEDIUM-05: PrimeVue components are used without registration

- Area: Traceability
- Confidence: High
- File:line: `resources/main.js:18-43`, `resources/views/NotificationView.vue:43`, `resources/views/TerminalSettingsView.vue:27`, `resources/views/supportView.vue:22`, `resources/views/supportView.vue:118-120`
- Evidence: `Divider` and `Chip` are used in Vue templates but are not imported or registered in `resources/main.js`.
- Impact: Depending on Vue/compiler config, these render as unresolved custom elements or produce warnings, leaving pages visually incomplete.
- Recommended fix: Import and register `primevue/divider` and `primevue/chip`, or replace those elements with local markup.
- Task statement: Register missing PrimeVue components and verify Notification, Terminal Settings, and Support in the browser console.

### Suggestion

#### SUGGESTION-01: Rebuild the admin shell to match the reference layout

- Area: Traceability
- Confidence: High
- File:line: `resources/components/Navbar.vue:1-17`, `resources/App.vue:1-13`
- Evidence: The current app uses a top `Menubar`; the reference uses a full-height left navigation, page title/action header, and one dense work surface.
- Impact: The product feels like a component demo instead of a focused debugging console.
- Recommended fix: Create an `AdminShell` with sidebar nav, route-aware page header, right-side actions, and a constrained content area.
- Task statement: Replace `Navbar` with a left sidebar shell and central route layout matching the screenshot’s navigation density.

#### SUGGESTION-02: Redesign Logs as the primary product surface

- Area: Optimization
- Confidence: High
- File:line: `resources/views/LogView.vue:6-123`
- Evidence: Browser verification showed the live Logs screen has the right data, but the table is visually ordinary and crowded by WordPress notices above it.
- Impact: The most valuable feature does not yet feel like a premium log-analysis tool.
- Recommended fix: Use the reference pattern: header with environment/log selector, action buttons, live/previous-start tabs, timeline rows, expandable structured details, copy JSON, severity badges, source chips, and sticky filters.
- Task statement: Convert Logs into the first polished screen before redesigning secondary pages.

#### SUGGESTION-03: Move dangerous terminal features behind Pro or advanced gates

- Area: Security
- Confidence: High
- File:line: `resources/views/TerminalView.vue:1-64`, `app/Controllers/TerminalController.php:10-54`, `app/Controllers/TerminalController.php:1592-1676`
- Evidence: The product includes browser-executed admin terminal commands and restricted shell commands.
- Impact: This is powerful but risky, and it is a natural paid feature because it needs audit logging, allowlists, role controls, and command history.
- Recommended fix: Keep a limited read-only command palette in Free; move shell/database command execution, command history, and saved snippets to Pro.
- Task statement: Define Free vs Pro terminal boundaries before redesigning the terminal page.

#### SUGGESTION-04: Turn alerting and integrations into the clearest Pro path

- Area: Traceability
- Confidence: High
- File:line: `resources/views/NotificationView.vue:1-76`, `app/Controllers/NotificationController.php:27-62`
- Evidence: Current notifications are daily email-oriented, while the screenshot’s action buttons point toward Grafana/Sentry-style workflows.
- Impact: There is a strong Pro opportunity in team debugging, not just local log viewing.
- Recommended fix: Free: daily email summary. Pro: instant alerts, Slack/Discord/webhook/Sentry/Grafana links, severity thresholds, quiet hours, per-plugin filters, and digest scheduling.
- Task statement: Add a Pro upsell and integration contract around Notification settings.

#### SUGGESTION-05: Replace Support with System Health and Diagnostics

- Area: Traceability
- Confidence: High
- File:line: `resources/views/supportView.vue:1-126`
- Evidence: Browser verification showed the Support page is mostly marketing/about content.
- Impact: In a debugging plugin, a diagnostics page would be more useful than an About page.
- Recommended fix: Show debug constants, log path status, file permissions, log size, WP/PHP versions, active theme, must-use plugins, cron status, and copyable support bundle.
- Task statement: Convert Support into a System Health screen with an exportable diagnostic bundle.

#### SUGGESTION-06: Add UI state tests after the redesign

- Area: Optimization
- Confidence: High
- File:line: `package.json:22`
- Evidence: `npm test` currently exits with `Error: no test specified`.
- Impact: Broken routes and unregistered components can ship without detection.
- Recommended fix: Add minimal route/component smoke tests or a browser smoke checklist covering every route.
- Task statement: Add a small test command that mounts the app shell and asserts all routes render without unresolved async spinners.

## 4) Browser-Observed Screen Notes

1. Logs: Data loads and filtering/search controls exist. Needs the strongest redesign. Use the screenshot pattern here: sidebar, title row, source selector, action buttons, live/previous tabs, expandable timeline rows, copy JSON.
2. Settings: Live `forms.test` shows constants and developer debug functions. In this worktree, the screen is only constants; if developer functions are intended, bring that source into this branch intentionally.
3. Safe Mode: Live screen loads after async settle and is understandable, but it is too explanatory/card-heavy. Make it a compact operational screen with selected/disabled plugin counts, search, and a high-friction apply confirmation.
4. Notification: Loads and saves email settings. Test Email is simulated in this worktree, so either wire it to a backend route or remove it until real.
5. Terminal: Loads and is usable. This should be visually separated as an advanced/dangerous tool and likely Pro-gated for command history/database/shell commands.
6. Terminal Settings: Live `forms.test` loads; this worktree is missing the backend route registration.
7. Support: Replace with diagnostics. The current page does not match the “machine logs/errors” product feel.

## 5) Prioritized Backlog (Quick Wins First)

1. [ ] Escape log rendering and remove raw `v-html` sinks.
2. [ ] Make `AjaxHandler::verify()` fail closed.
3. [ ] Register terminal settings routes.
4. [ ] Fix Safe Mode `this.state` submit bug.
5. [ ] Register missing PrimeVue components.
6. [ ] Build the new admin shell and redesign the Logs screen first.
7. [ ] Define the Pro line: alerts/integrations, terminal power features, saved filters, log retention/export, diagnostic bundles.

## 6) Needs Manual Verification

- Finding key: Browser screenshot capture
  - Area: Traceability
  - File:line: N/A
  - Why uncertain: The in-app browser loaded pages and DOM snapshots, but `Page.captureScreenshot` timed out twice.
  - Manual test to confirm: Re-run screenshot capture after disabling noisy third-party admin notices or from a cleaner local WP install.
