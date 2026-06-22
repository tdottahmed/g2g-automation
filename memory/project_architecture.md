---
name: project-architecture
description: g2g automation split: Laravel API server + local Node.js Playwright runner, no PHP-spawned processes
metadata:
  type: project
---

Posting is now fully decoupled: Laravel is hosted live and exposes a REST API (`/api/automation/*`), and a standalone Node.js runner runs locally on the operator's machine.

**Why:** User wants Laravel on a live server with the Playwright bot running locally, so no PHP process can spawn Node.js directly.

**How to apply:** Never suggest triggering posting via artisan commands or symfony/process from PHP. All posting is triggered by `node runner.js` (or `npm run watch`) from `scripts/automation/`.

The runner is a standalone npm package at `scripts/automation/package.json` — can be copied out of the Laravel repo entirely. It needs its own `.env` with `LARAVEL_API_URL` and `API_KEY`.

Key files added:
- `scripts/automation/runner.js` — main entry (run/watch/status modes)
- `scripts/automation/api-client.js` — HTTP client for Laravel API
- `scripts/automation/utils/form-filler.js` — shared Playwright form logic
- `routes/api.php` — pending/success/failed/heartbeat endpoints
- `app/Http/Middleware/ApiKeyMiddleware.php` — `X-Api-Key` header check
- `app/Http/Controllers/Api/AutomationApiController.php` — API handler

Dashboard at `automation/dashboard` shows: stats, templates per user (with active toggle + queue-post), recent logs. No posting buttons.
