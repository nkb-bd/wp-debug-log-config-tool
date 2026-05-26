# First-Run Growth Plan

This plan turns the plugin's first-run experience into a safer, clearer log-manager workflow before introducing the advanced debugging toolkit.

## Goal

Make Debug Log Manager Tool easier to trust and adopt by ensuring installation, activation, and deactivation do not change a site's debug constants automatically.

The plugin should communicate this clearly:

> Install safely. Nothing changes until you choose.

## Core Rule

The plugin must not modify debug constants during activation or deactivation.

This includes:

- `WP_DEBUG`
- `WP_DEBUG_LOG`
- `SCRIPT_DEBUG`
- `WP_DEBUG_DISPLAY`
- `SAVEQUERIES`

Config changes should happen only after an explicit user action inside the plugin UI, such as clicking a settings toggle or admin-bar debug toggle.

Existing installs must upgrade smoothly. If a site already has plugin-created options, generated log paths, or debug constants that were set by an older version, the new version should preserve and report that state without silently undoing it.

## Growth Hypothesis

The plugin is feature-rich, but the first-run path can feel riskier than simpler alternatives. A conservative first-run experience should improve trust, reduce hesitation, and make the WordPress.org page easier to understand.

The public positioning should lead with simple log management:

1. Install safely.
2. See current debug/logging status.
3. Enable logging only when the user chooses.
4. View, search, filter, copy, and clear logs.
5. Use advanced tools only when needed.

Advanced features remain important, but they should be framed as optional power tools:

- Safe Mode
- Query Inspector
- Terminal
- Notifications
- Stack trace formatting
- Admin-bar debug toggle

## GitHub Parent Issue

### Title

`Growth: make first-run safe and reposition around simple log management`

### Body

## What to build

Make the plugin's first-run experience conservative and trust-first. Activation and deactivation must not auto-change WordPress debug constants. The default experience should start as a simple debug log manager, then offer advanced debugging tools as opt-in workflows.

## Why

The plugin currently has a broader feature set than many simple log viewers, but that can make adoption feel risky. The biggest growth improvement is to make the first-run path safer and easier to understand:

- No automatic debug-constant changes on activation.
- No automatic debug-constant restore/mutation on deactivation.
- No read endpoint silently writing missing constants.
- Clear secure/custom log-path story.
- WordPress.org copy that sells simple log management first.

## Acceptance criteria

- [ ] Activation does not modify `WP_DEBUG`, `WP_DEBUG_LOG`, `SCRIPT_DEBUG`, `WP_DEBUG_DISPLAY`, or `SAVEQUERIES`.
- [ ] Deactivation does not restore, overwrite, remove, or otherwise mutate those constants.
- [ ] Debug constants change only after explicit user action.
- [ ] The first screen leads with log management, not terminal/database tools.
- [ ] Terminal/database tools are clearly advanced and opt-in.
- [ ] The readme explains the safe first-run behavior in plain language.
- [ ] The secure/custom log-path story is clear in UI and WordPress.org copy.
- [ ] Release notes explain the trust and onboarding improvement.
- [ ] Existing installs keep working after upgrade without unexpected debug/log-path changes.

## Suggested sub-issues

1. Stop activation and deactivation from auto-changing debug constants.
2. Make config changes explicit in the UI.
3. Separate log viewing from debug configuration.
4. Clarify secure log path and custom log location story.
5. Rewrite WordPress.org page around simple log-manager value.
6. Normalize product naming across plugin, UI, and package metadata.
7. Prepare trust-focused release notes and screenshots.
8. Rewrite GitHub README for developer and contributor trust.
9. Create GitHub Pages product/docs site.

## Sub-Issue 1: Stop Activation And Deactivation From Auto-Changing Debug Constants

### Title

`Stop activation and deactivation from auto-changing debug constants`

### Type

AFK

### What to build

Remove all automatic debug-constant mutation from activation and deactivation.

Current code paths to inspect:

- `app/Classes/Activator.php`
- `app/Classes/DeActivator.php`
- `app/Controllers/ConfigController.php`

Activation should not call logic that writes default debug constants into `wp-config.php`.

Deactivation should not restore previous debug constants, overwrite current debug constants, or remove debug constants. If a user intentionally changed constants after activation, deactivation must not undo those choices.

Plugin-owned cleanup can still happen if it does not touch debug constants. For example, notification cleanup or plugin-only option cleanup is acceptable.

For existing installs, do not remove old plugin options during this change. Treat them as migration inputs for read-only status and log-path resolution.

### Acceptance criteria

- [ ] Activation does not write or update `WP_DEBUG`.
- [ ] Activation does not write or update `WP_DEBUG_LOG`.
- [ ] Activation does not write or update `SCRIPT_DEBUG`.
- [ ] Activation does not write or update `WP_DEBUG_DISPLAY`.
- [ ] Activation does not write or update `SAVEQUERIES`.
- [ ] Deactivation does not restore previous debug constants.
- [ ] Deactivation does not remove or overwrite current debug constants.
- [ ] Existing `wp-config.php` values remain untouched unless the user explicitly changes settings in the UI.
- [ ] Existing plugin-created log-path options are preserved during upgrade.
- [ ] Existing sites with `WP_DEBUG_LOG` already pointing to a plugin-generated path continue to read that path.
- [ ] Plugin-only cleanup still runs where appropriate.
- [ ] Changelog mentions that activation/deactivation no longer changes debug constants automatically.

### Blocked by

None - can start immediately.

## Sub-Issue 2: Make Config Changes Explicit In The UI

### Title

`Make debug config changes explicit in the UI`

### Type

AFK

### What to build

Ensure debug constants change only from explicit user actions.

The settings UI and admin-bar toggle can still change config, but they must be clear actions. A user should know which constant will be changed before it happens.

Important code path to inspect:

- `app/Controllers/SettingsController.php`

The current `get_settings` read path should not silently write missing constants. Reading settings should only read and report current state.

### Acceptance criteria

- [ ] `get_settings` does not write missing constants into config.
- [ ] Settings toggles are explicit write actions.
- [ ] Admin-bar debug toggle remains an explicit write action.
- [ ] UI copy explains what will change before saving where needed.
- [ ] Errors are shown if config cannot be written.
- [ ] A user can inspect current settings without mutating `wp-config.php`.

### Blocked by

Sub-Issue 1.

## Sub-Issue 3: Separate Log Viewing From Debug Configuration

### Title

`Separate log viewing from debug configuration`

### Type

AFK

### What to build

Make the default plugin experience log-first.

Users should be able to open the plugin after activation and understand:

- Whether debug logging is enabled.
- Where the current log file is expected to be.
- Whether a readable log exists.
- What action they can take next.

If logging is not enabled, show a clear call to action instead of silently enabling anything.

### Acceptance criteria

- [ ] User can open the plugin without changing config.
- [ ] Log screen shows current debug/logging status.
- [ ] If logging is off, the UI shows a clear enable action.
- [ ] Enable action explains the exact constants it will change.
- [ ] The advanced tools are not the main first-run focus.
- [ ] No background setup mutates `wp-config.php`.
- [ ] Existing users with plugin-managed logging see their current state, not a forced reset.

### Blocked by

Sub-Issue 1 and Sub-Issue 2.

## Smooth Upgrade Policy

This work must be backwards-compatible for existing users. The safety change should affect automatic future side effects, not erase or surprise-change current installations.

### Upgrade principles

- Preserve existing `wp-config.php` constants exactly as they are during plugin update, activation, and deactivation.
- Preserve existing plugin options such as generated log path state unless a separate migration explicitly needs to change them.
- Keep reading logs from an existing plugin-generated `WP_DEBUG_LOG` path if that is already configured.
- Do not delete or regenerate an existing log file path just because the new version uses a read-only resolver.
- Do not disable terminal/database features for existing users who explicitly enabled them before upgrade.
- Apply safer defaults only to fresh installs or unset options.
- If an old state is ambiguous, show a UI notice and ask the user to confirm the next action instead of guessing.

### Required upgrade scenarios to test

- Fresh install with no debug constants.
- Existing install with default `wp-content/debug.log`.
- Existing install with plugin-generated randomized log path.
- Existing install with custom `WP_DEBUG_LOG` path.
- Existing install where `WP_DEBUG` is true before upgrade.
- Existing install where `WP_DEBUG` is false before upgrade.
- Existing install where terminal was enabled before upgrade.
- Existing install where database commands were enabled before upgrade.
- Deactivation after upgrade.
- Reactivation after upgrade.

### Release note wording

Use wording like:

> This release makes activation and deactivation safer. The plugin no longer changes debug constants automatically during activation or deactivation. Existing settings and log paths are preserved, and future config changes happen only when an admin explicitly chooses them.

## Sub-Issue 4: Clarify Secure Log Path And Custom Log Location Story

### Title

`Clarify secure log path and custom log location story`

### Type

AFK

### What to build

Make the log-path behavior easy to understand in both UI and documentation.

The user should not need to read code to answer:

- Where is my debug log?
- Is it the default `wp-content/debug.log`?
- Is it randomized?
- Is it custom?
- Is it controlled by a filter?
- Is the file publicly exposed?

### Acceptance criteria

- [ ] UI shows the active log path clearly.
- [ ] UI explains when no readable log file exists.
- [ ] Readme explains custom/secure log-path behavior.
- [ ] Readme answers whether the default debug log may be exposed.
- [ ] Filter-based customization is documented for developers.
- [ ] Documentation matches the actual code path.

### Blocked by

Sub-Issue 3.

## Sub-Issue 5: Rewrite WordPress.org Page Around Simple Log-Manager Value

### Title

`Rewrite WordPress.org page around simple log-manager value`

### Type

HITL

### What to build

Rework `readme.txt` so the public WordPress.org page sells the simple log-manager job before the advanced toolkit.

Recommended order:

1. Safe install promise.
2. Simple debug log viewing.
3. Debug logging toggles as explicit actions.
4. Search, filter, copy, clear, and file preview.
5. Secure/custom log-path story.
6. Advanced tools.
7. Developer hooks and filters.

Advanced features should be presented as optional:

- Query Inspector
- Safe Mode
- Terminal
- Notifications
- Stack trace analysis

### Acceptance criteria

- [ ] Short description is understandable in under 10 seconds.
- [ ] The first feature group is log management.
- [ ] The page clearly says activation does not change debug constants.
- [ ] Terminal/database features are not presented as the first reason to install.
- [ ] The comparison against simple log managers is implied without attacking competitors.
- [ ] Screenshots and text tell the same story.

### Blocked by

Sub-Issue 1, Sub-Issue 3, and Sub-Issue 4.

## Sub-Issue 6: Normalize Product Naming Across Visible Surfaces

### Title

`Normalize product naming across plugin, UI, and package metadata`

### Type

AFK

### What to build

Pick one public product name and align visible surfaces without changing the WordPress.org slug.

Surfaces to inspect:

- `plugin.php`
- `readme.txt`
- `readme.md`
- `package.json`
- Vue UI brand text
- Screenshot captions
- Changelog wording

Avoid slug-breaking or update-breaking changes.

### Acceptance criteria

- [ ] Public name is consistent across plugin header and readme.
- [ ] UI brand does not conflict with the public name.
- [ ] Package metadata no longer carries confusing old names where visible.
- [ ] WordPress.org slug remains stable.
- [ ] Changelog uses the selected name consistently.

### Blocked by

None - can start immediately.

## Sub-Issue 7: Prepare Trust-Focused Release Notes And Screenshots

### Title

`Prepare trust-focused release notes and screenshots`

### Type

HITL

### What to build

Prepare release-facing materials around the safer first-run workflow.

The release should explain the user benefit, not just the technical change:

- Activation no longer changes debug constants.
- Deactivation no longer restores or mutates debug constants.
- Log viewing is the first-run focus.
- Advanced tools remain available when needed.

### Acceptance criteria

- [ ] Screenshot 1 shows the core log-manager value.
- [ ] Screenshot 2 shows explicit debug settings or secure log path.
- [ ] Advanced tools appear after the core flow.
- [ ] Changelog includes a trust-focused entry.
- [ ] Release notes mention safer activation/deactivation behavior.
- [ ] WordPress.org assets match the new positioning.

### Blocked by

Sub-Issue 1, Sub-Issue 3, Sub-Issue 4, and Sub-Issue 5.

## Sub-Issue 8: Rewrite GitHub README For Developer And Contributor Trust

### Title

`Rewrite GitHub README for safe first-run and contributor trust`

### Type

AFK for README structure and copy, HITL for final product positioning approval.

### What to build

Rewrite `readme.md` for GitHub visitors. This file should be more technical and contributor-friendly than `readme.txt`, but it must still lead with the same safe first-run promise.

The current GitHub README should not continue to say that constants are restored on deactivation after this plan is implemented. That message conflicts with the new core rule.

Recommended README structure:

1. Product name and short description.
2. Safe first-run promise: activation/deactivation do not change debug constants.
3. What the plugin does: log viewing first, advanced tools second.
4. Install and local development notes.
5. Architecture notes for config safety and log-path handling.
6. Contributor workflow.
7. Links to WordPress.org, roadmap, first-run growth plan, pro-feature plan, and GitHub Pages.
8. Security notes for logs, config writes, terminal, and database commands.

### Acceptance criteria

- [ ] `readme.md` states that activation and deactivation do not auto-change debug constants.
- [ ] `readme.md` removes or rewrites any old claim that constants are restored automatically on deactivation.
- [ ] The first feature group is debug log viewing and log management.
- [ ] Advanced tools are described as opt-in or advanced workflows.
- [ ] Smooth upgrade behavior is documented for contributors.
- [ ] Local development/build commands are documented from the actual repo scripts.
- [ ] Links point to `.github/ROADMAP.md`, `.github/PRO-FEATURES.md`, and `.github/FIRST-RUN-GROWTH-PLAN.md`.
- [ ] README tone is suitable for GitHub: technical, precise, and contributor-friendly.
- [ ] WordPress.org-specific marketing duplication is avoided where possible.
- [ ] README does not present planned safety behavior as already shipped until implementation is complete.

### Blocked by

Sub-Issue 1, Sub-Issue 2, Sub-Issue 3, and Sub-Issue 6.

## Sub-Issue 9: Create GitHub Pages Product/Docs Site

### Title

`Create GitHub Pages product and docs site`

### Type

HITL for visual/copy approval, AFK for simple static implementation after approval.

### What to build

Create a lightweight GitHub Pages site for the plugin. Keep it low-maintenance and aligned with the WordPress.org page, but use it as a richer home for docs, screenshots, and contributor context.

Recommended approach:

- Use a simple static site under `/docs` so GitHub Pages can publish from the repository's `docs` folder.
- Keep the first version static HTML/CSS or simple Markdown, with no custom build step unless there is a strong reason.
- Link to WordPress.org as the primary install/download destination.
- Link back to GitHub for source, issues, roadmap, and contribution.

Recommended Pages structure:

1. Landing section: "Install safely. Nothing changes until you choose."
2. Core flow: view logs, understand status, enable logging explicitly.
3. Secure/custom log path explanation.
4. Advanced tools section: Safe Mode, Query Inspector, Terminal, Notifications.
5. FAQ: activation, deactivation, fatal errors, log privacy, terminal safety.
6. Screenshots or GIF-ready placeholders.
7. Links: WordPress.org, GitHub, roadmap, support/issues.

### Acceptance criteria

- [ ] GitHub Pages source lives in `/docs` or another clearly documented Pages source.
- [ ] The site leads with the safe first-run promise.
- [ ] The site explains that activation/deactivation do not auto-change debug constants.
- [ ] The site explains the simple log-manager workflow before advanced tools.
- [ ] The site links to WordPress.org for install/download.
- [ ] The site links to GitHub issues and planning docs.
- [ ] The site has FAQ coverage for log path safety, fatal errors, terminal/database commands, and smooth upgrades.
- [ ] The site can be maintained without adding heavy frontend tooling.
- [ ] Any screenshots used match the new positioning.
- [ ] The site does not document unreleased safety behavior as current until the related implementation issues are complete.
- [ ] No Node, custom Jekyll theme dependency, or CI deploy pipeline is required for the first version.

### Non-goals

- Do not create a separate product brand from the plugin.
- Do not make GitHub Pages the primary download source.
- Do not add a complex docs framework unless the repo already adopts one later.
- Do not expose logs, config values, or site-specific debug information through GitHub Pages.
- Do not duplicate the full WordPress.org readme.
- Do not make Terminal, database commands, or Safe Mode the primary positioning.

### Blocked by

Sub-Issue 5, Sub-Issue 6, Sub-Issue 7, and Sub-Issue 8.

## Suggested Implementation Order

1. Stop activation and deactivation from auto-changing debug constants.
2. Make config changes explicit in the UI.
3. Separate log viewing from debug configuration.
4. Clarify secure log path and custom log location story.
5. Normalize product naming across visible surfaces.
6. Rewrite WordPress.org page around simple log-manager value.
7. Rewrite GitHub README for developer and contributor trust.
8. Prepare trust-focused release notes and screenshots.
9. Create GitHub Pages product and docs site.

## Non-Goals

- Do not remove advanced tools.
- Do not remove settings toggles.
- Do not change the WordPress.org slug.
- Do not make terminal/database features the default onboarding path.
- Do not add a pro upsell to this first-run safety work.

## Success Metrics

- More users understand the plugin from the WordPress.org page.
- Fewer users worry that activation changes production debug behavior.
- Support questions about unexpected debug config changes decrease.
- Active installs move beyond the current 3,000+ plateau.
- Reviews mention safe/easy setup, not only powerful tooling.

## Architecture Review Notes

These are the major improvement gaps found during an architecture pass. The main theme is that several modules are currently shallow: callers must understand too much about config mutation, log-path creation, file IO, and WordPress request timing to use them safely.

### Candidate 1: Create A Read-Only Debug Status Module

#### Files

- `app/Controllers/SettingsController.php`
- `app/Controllers/ConfigController.php`
- `app/Classes/DLCT_Bootstrap.php`

#### Problem

Reading current debug status can write missing constants. That makes the read interface unsafe because callers cannot inspect state without knowing whether config may be mutated.

This hurts the first-run growth goal: the plugin cannot honestly promise "nothing changes until you choose" while read paths can still write.

#### Solution

Create a small read-only debug status module whose interface only reports current config state:

- constant exists or not
- current value
- config file writable or not
- recommended next action

All writes should move behind explicit command-style actions such as "enable debug logging" or "update setting".

#### Benefits

- Better locality: read behavior and write behavior stop being mixed.
- Better leverage: UI, admin-bar status, onboarding, and tests can all use one read-only interface.
- Better test surface: tests can assert that status reads never mutate `wp-config.php`.

### Candidate 2: Split Log Path Resolution From Log Path Creation

#### Files

- `app/Controllers/LogController.php`
- `app/Controllers/ConfigController.php`

#### Problem

`LogController::__construct()` calls `setRandomLogPath()`, and `setRandomLogPath()` can:

- generate a new path
- update WordPress options
- create a file
- write `WP_DEBUG_LOG`
- copy old logs

That makes `LogController` a shallow module with a dangerous interface. Creating the controller can change the site.

#### Solution

Split this into two modules:

- read-only log path resolver: tells the app where logs are now
- explicit log path setup action: creates or changes log path only after user confirmation

The resolver must never write options, create files, or change constants.

#### Benefits

- Better locality: log-path side effects live in one explicit setup action.
- Better leverage: log viewer, onboarding, secure-path copy, and docs can all depend on the same read-only resolver.
- Better test surface: one test can prove "constructing log viewer does not mutate config."

### Candidate 3: Add Fatal-Safe Log Access

#### Files

- `plugin.php`
- `app/Classes/DLCT_Bootstrap.php`
- `app/Controllers/LogController.php`
- new module to be decided

#### Problem

The current log viewer depends on normal WordPress admin loading, plugin boot, AJAX, nonce verification, and Vue assets. If a fatal error prevents wp-admin from loading normally, the user may not be able to reach the log viewer when they need it most.

This is a strong product gap because the urgent user job is:

> The site is broken; show me the fatal error fast.

#### Possible solution directions

Option A: Recovery Mode helper

- Detect when WordPress Recovery Mode is active.
- Make the plugin's log viewer highly visible in Recovery Mode.
- Keep this inside normal WordPress security.

Option B: Fatal snapshot

- Register a lightweight shutdown handler.
- On fatal error, store a small "last fatal error" snapshot in a plugin-owned option or safe file.
- Show that snapshot at the top of the plugin screen when admin becomes reachable again.

Option C: Optional emergency viewer

- Let admins explicitly generate an emergency log-viewer link or file.
- It must be disabled by default, time-limited, token-protected, and easy to revoke.
- This is higher risk and needs a security review before implementation.

Recommended first version: Option B, then Option A.

Avoid starting with Option C unless there is a clear security design, because exposing logs outside normal WordPress authentication can leak paths, secrets, emails, tokens, and stack traces.

#### Benefits

- Better locality: fatal capture is isolated from normal log parsing and UI.
- Better leverage: support teams get a durable "last fatal" signal even if the log viewer cannot fully load during the failed request.
- Better test surface: fatal snapshot behavior can be tested separately from the Vue log viewer.

### Candidate 4: Extract Log Parsing Behind A Parser Module

#### Files

- `app/Controllers/LogController.php`

#### Problem

Log reading, file access, parsing, grouping, stack trace handling, test log generation, query-log handling, log-path setup, and config writes are concentrated in one controller.

The parser logic is valuable, but it is buried behind a controller interface that also performs request verification and IO.

#### Solution

Move parsing into a deeper parser module:

- input: raw log text or lines
- output: structured log entries and parse failures
- no WordPress request verification
- no config writes
- no file writes

#### Benefits

- Better locality: parser bugs are fixed in one place.
- Better leverage: normal viewer, fatal snapshot, large-file preview, and future report generation can reuse the same parser.
- Better test surface: parser can be tested with sample log lines without booting WordPress.

### Candidate 5: Make Advanced Tools Opt-In Behind A Capability Module

#### Files

- `app/Controllers/TerminalSettingsController.php`
- `app/Controllers/TerminalController.php`
- `resources/views/TerminalSettingsView.vue`
- `resources/views/TerminalView.vue`

#### Problem

Terminal and database commands are powerful, but they increase anxiety for first-time users. Their current settings are not strongly separated from the core log-manager job.

#### Solution

Create an advanced tools capability module that answers:

- is terminal enabled?
- are database commands enabled?
- who can use them?
- what warnings must the UI show?

Default should be conservative and opt-in.

#### Benefits

- Better locality: advanced access rules live in one place.
- Better leverage: terminal UI, settings UI, and route checks can all use the same decision.
- Better test surface: permission and default-state tests become direct.

## Fatal-Safe Log Access Issue Draft

### Title

`Add fatal-safe last error snapshot for broken-site debugging`

### Type

AFK for snapshot capture, HITL for any emergency external viewer.

### What to build

Add a conservative first version of fatal-safe debugging by capturing the last fatal error during shutdown and showing it in the plugin when admin access is available again.

This should not expose logs publicly and should not create an unauthenticated viewer.

### Acceptance criteria

- [ ] Fatal error capture uses a lightweight shutdown handler.
- [ ] Captured snapshot stores only the latest fatal error details needed for debugging.
- [ ] Snapshot does not expose a public unauthenticated URL.
- [ ] Plugin UI shows the last fatal snapshot clearly when available.
- [ ] User can clear the captured snapshot.
- [ ] Normal log viewer still works independently.
- [ ] Implementation does not change debug constants automatically.
- [ ] Any future emergency external viewer is treated as a separate security-reviewed issue.

### Blocked by

Sub-Issue 1 and Candidate 2.
