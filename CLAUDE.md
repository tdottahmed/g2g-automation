# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this project does

This is a Laravel 11 admin panel that automates posting game account offers on [g2g.com](https://www.g2g.com). Admins configure g2g.com account credentials (`UserAccount`) and offer templates (`OfferTemplate`) for Clash of Clans accounts. The Laravel queue dispatches `PostOfferTemplate` jobs, which invoke a Node.js/Playwright script to drive g2g.com's browser UI and submit offers.

## Commands

### Local development (all services at once)
```bash
composer run dev
# Runs: php artisan serve + queue:listen + pail + npm run dev (via concurrently)
```

### Individual services
```bash
php artisan serve              # Laravel HTTP server
php artisan queue:listen --tries=1  # Queue worker
npm run dev                    # Vite frontend watcher
npm run build                  # Production frontend build
```

### Production queue (PM2)
```bash
pm2 start ecosystem.config.cjs
```

### Database
```bash
php artisan migrate
php artisan db:seed            # Runs UserRolePermissionSeeder + OfferSchedulerSeeder
```

### Automation commands
```bash
php artisan offer:automation --user_account_id=1   # Dispatch jobs for one user's active templates
php artisan offer:automation --all                  # Dispatch jobs for all users' active templates
php artisan offer:automation-run                    # Scheduled variant — respects ApplicationSetup windows/intervals
```

### Tests & linting
```bash
./vendor/bin/pest                        # Run all tests
./vendor/bin/pest --filter TestName      # Run a single test
./vendor/bin/pint                        # Format PHP (Laravel Pint)
```

## Architecture

### Laravel side (`app/`)

**Models:**
- `UserAccount` — stores g2g.com login email/password and cookie-file state (`is_generated_cookies`, `cookies_file_path`)
- `OfferTemplate` — one listing per g2g.com offer; fields map directly to g2g form fields (`th_level`, `king_level`, `queen_level`, `warden_level`, `champion_level`, `price`, `delivery_method` JSON, `medias` JSON, `offers_to_generate`, `is_active`, `last_posted_at`)
- `OfferAutomationLog` — per-run execution log linked to a template (use `logSuccess`/`logFailed` static methods)
- `ApplicationSetup` — key/value config table; keys include `scheduler_windows` (JSON) and `schedule_interval_minutes`

**Controllers** (`app/Http/Controllers/Admin/`):
- `OfferAutomationController` — HTTP endpoints to trigger automation per-user, per-template, or all-users; calls Artisan commands internally
- `OfferTemplateController` — CRUD + `toggleStatus` for templates
- `UserAccountController` — CRUD for g2g.com accounts

**Job:**
- `PostOfferTemplate` — receives an array of template IDs and one user account ID. Loads templates in chunks of 5, groups by user, calls `processUserTemplates()`, which invokes `scripts/automation/post-offers.js` via `symfony/process`. Passes template data as base64-encoded JSON in `argv[2]`. Retries 3×, timeout 600 s.

**Commands:**
- `offer:automation` (`OfferAutomation`) — on-demand; accepts `--user_account_id` or `--all`; dispatches `PostOfferTemplate` jobs
- `offer:automation-run` (`RunOfferAutomation`) — scheduler-aware; reads `ApplicationSetup` windows and `schedule_interval_minutes`; respects `last_posted_at` and `offers_to_generate`

**Scheduler** (`routes/console.php`): Runs `offer:automation` every minute (`everyMinute()->evenInMaintenanceMode()`).

**Roles/permissions:** Spatie Laravel Permission. All admin routes require `role:super-admin|admin|staff|user`.

### API for local runner (`routes/api.php`)

All routes are prefixed `/api/` and require the `X-Api-Key` header matching `API_AUTOMATION_KEY` in Laravel's `.env`.

| Method | Path | Purpose |
|--------|------|---------|
| `GET`  | `/api/automation/heartbeat` | Connectivity check |
| `GET`  | `/api/automation/pending` | Returns users + templates ready to post now |
| `POST` | `/api/automation/{id}/success` | Mark template posted; accepts `details` JSON body |
| `POST` | `/api/automation/{id}/failed` | Log failure; accepts `error` (string) + `details` |

The `pending` endpoint applies the same scheduling logic as `RunOfferAutomation`: checks `ApplicationSetup` windows/interval, `last_posted_at`, and `offers_to_generate`. Forced templates (`offers_to_generate > 0`) bypass time windows.

### Playwright/Node.js side (`scripts/automation/`)

There are two entry points:

**`runner.js` — new API-driven local runner (preferred going forward)**
1. Reads config from `scripts/automation/.env` (`LARAVEL_API_URL`, `API_KEY`, `COOKIES_DIR`, `HEADLESS`, etc.)
2. Calls `GET /api/automation/pending` to get what needs posting now
3. For each user group: launches one Chromium session, loads/refreshes auth, posts all templates sequentially
4. Reports each template result back to the Laravel API immediately after posting
5. Cookie files are stored locally in `COOKIES_DIR/{email-prefix}.json` (auto-created)

```bash
node runner.js             # run once and exit
node runner.js --watch     # poll every WATCH_INTERVAL_SECONDS
node runner.js --status    # connectivity/auth check only
```

**`post-offers.js` — legacy PHP-spawned entry point**
- Receives base64-encoded JSON array via `process.argv[2]` (dispatched by `PostOfferTemplate` job)
- Cookie files expected at the project root as `{email-prefix}.json`

**Shared utilities:**
- `utils/form-filler.js` — `fillOfferForm`, `submitFormAndAddNew`, `submitForm` (shared by both entry points)
- `utils/auth.js` — `saveAuthState`, `loadAuthState`, `isLoggedIn`, `loginWithOTP`
- `utils/sell.js` — `navigateToAccountsSection`, `clickContinueButton`
- `utils/index.js` — `humanDelay`, `rl`
- `api-client.js` — `fetchPending`, `reportSuccess`, `reportFailed`, `heartbeat`

### Frontend

Tailwind CSS v3 + Alpine.js + Vite. Custom Blade view components live in `app/View/Components/` and follow a design-system structure (`DataDisplay/`, `DataEntry/`, `Action/`, `Layouts/`). The admin layout is `Layouts/Admin/Master`.

### Queue

Uses the `database` driver (see `.env.example`). PM2 manages the worker in production via `ecosystem.config.cjs`. Queue max memory restart: 256 MB.
