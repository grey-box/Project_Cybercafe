# CyberCafe 2025 Schema & Dynamic UI Setup

## Overview
- Adopted the 2025 ASU schema for the CyberCafe database and created deterministic seed data that exercises every table.
- Wired the guest, user, owner, and admin website experiences so each role now renders live information directly from the seeded SQLite database.
- Added reusable PDO helpers to keep page controllers lean while sharing query logic.

## Database Seeding (`Database/populateDB.sh`)
- Usage: `./populateDB.sh [--force] [--db /custom/path.sqlite]` – defaults to rebuilding `Database/CyberCafeTest.db`.
- The script replaces the target database (prompts unless `--force`), loads `schema_2025_asu.sql`, and inserts reference data:
  - Role catalogue limited to `admin`, `owner`, `user`, and `guest` so the sample logins align with the UI.
  - Representative users with history, balances, quota ledger entries, sessions, traffic samples, URL/device restrictions, reports, and system events.
  - Financial records (payments, monetary ledger, payment history) tied to the same IDs the UI surfaces.
  - Sample credential pairs:
    - Admin — `admin@example.com` / `adminpass`
    - Owner — `owner@example.com` / `ownerpass`
    - User — `user@example.com` / `userpass`
    - Guest — `guest@example.com` / `guestpass` (guest portal only)
- Run the script whenever you need a clean dataset. Example:
  ```bash
  cd /Users/jjoseph/Desktop/Project_Cybercafe
  ./Database/populateDB.sh --force
  ```

## Web Pages Now Pulling Live Data
- Shared helpers in `Website/config/data_helpers.php` centralise read-only queries (sessions, quotas, events, payments, etc.), all using prepared statements.
- `Website/php_views/guest_user/guest_homepage.php` shows allowed/restricted sites from `url_restriction`, including created dates and visit actions.
- `Website/php_views/user/user_profile.php` accepts `?user=<user_id>` (defaults to `user.mia`) and loads profile metadata, access code, role/status, queue speeds, balance, and recent sessions.
- `Website/php_views/owner/odashboard.php` visualises recent sessions, aggregate bandwidth usage, top-device usage bars, plus system events pulled from `internet_session`, `traffic_data`, and `system_event`.
- `Website/php_views/final_admin/adashboard.php` extends the owner view with device usage bars and financial insights (recent payments, negative-balance alerts) for administrative review.

## Verifying the Setup
- After seeding, start a local PHP server from the `Website` directory (for example: `php -S 127.0.0.1:8000`).
- Browse to the relevant role pages to confirm charts, tables, and alerts populate with the seeded records.
- If you introduce new tables or columns, update `schema_2025_asu.sql`, rerun the seeder, and extend `data_helpers.php` so the dashboards stay in sync.
