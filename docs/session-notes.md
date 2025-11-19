# Project CyberCafe – Session Notes (JJ & Codex)

> Casual recap of everything we touched so I can pass it along to classmates.  
> Dates/times omitted – this is just the single working session.

---

## 1. Database Work

- Swapped the old `Final_CyberCafe_Schema.sql` workflow to the new schema file `Database/schema_2025_asu.sql`.
- Rebuilt `Database/populateDB.sh` so it:
  - Takes `--db` and `--force` flags.
  - Always wipes the target DB and re-runs the schema file.
  - Seeds consistent demo data using straight SQL (no bash loops).
- Locked the roles down to **admin**, **owner**, **user**, **guest** (removed manager/member extras).
- Created matching sample accounts:
  - `admin@example.com` / `adminpass`
  - `owner@example.com` / `ownerpass`
  - `user@example.com` / `userpass`
  - `guest@example.com` / `guestpass`
- Every table in the schema gets some demo rows:
  - Role + status lookups
  - Users, balances, quota ledger, monetary ledger, payments, payment history
  - Sessions + traffic data
  - Reports, system events, URL + device restrictions
- After rewriting the script we ran `./Database/populateDB.sh --force` to regenerate `CyberCafeTest.db`.

---

## 2. PHP Helper Layer

- Added `Website/config/data_helpers.php`.  
  It’s just a bunch of read-only PDO helper functions for:
  - Allowed + blocked URLs
  - Active sessions (with avg KB/s + last-activity labels)
  - Bandwidth totals per user
  - System event feed
  - Active device count
  - User profile info, balances, sessions
  - Recent payments + negative balance alerts
  - **New** device usage helper (top 5 devices ranked by GB used) for dashboard charts.

---

## 3. Guest Pages

- `Website/php_views/guest_user/guest_homepage.php`
  - Replaced the static “example.com” list with live allowed + blocked URLs from the DB.
  - Prints added-on dates and visit buttons (only for allowed sites).
- Guest login page already creates a temporary guest session, so no change needed there.

---

## 4. Logged-In User Pages

- `Website/php_views/user/user_profile.php`
  - Default profile now points at `user.mia` (the seeded standard user).
  - Shows role, status, access code, queue speeds, balance, last status update.
  - The “Assigned Devices” table is populated from actual sessions/traffic data.
- Any `?user=<id>` query string pulls a different user (as long as they exist in the DB).

---

## 5. Owner Dashboard (odashboard.php)

- Replaced the entire static file with a dynamic one:
  - Line chart still shows bandwidth totals by user (now live).
  - “Recent Sessions” table lists the five hottest sessions with avg KB/s + timestamps.
  - “Active/Online Devices” card now renders a bar chart (Chart.js) using the new `fetchDeviceUsage` helper.
  - Device card shows a message if there’s no traffic yet.
  - Latest system events table reads straight from `system_event`.
- Quick Links + layout stay the same, just the data is live now.

---

## 6. Admin Dashboard (adashboard.php)

- Same improvements as the owner view **plus**:
  - Recent payments table (amount, method, timestamp).
  - Balance alerts table (any users at \$0 or negative).
  - Reused the device usage bar chart so both Owner/Admin see the same visualization.

---

## 7. Docs & Notes

- `docs/data-seeding-and-dynamic-pages.md`
  - Updated to reflect the new defaults (roles, credentials, device chart info).
  - Added quick instructions for reseeding + launching the PHP dev server.
- **This file** (`docs/session-notes.md`) is the informal blow-by-blow.

---

## 8. Debugging Conversations

- Login wasn’t working initially because the old seed data used uppercase role IDs and random access codes.  
  Fix = seed lowercase roles + simple passwords that match the UI instructions.
- We chased a question about device charts vs. user tables:
  - The chart only shows devices that have actual traffic entries in the DB.
  - The table in the owner UI is still hard-coded (placeholder array), so those names don’t appear anywhere else.
  - To sync them we’d need to either insert those table users into the DB with sessions, or rewrite the table to query the DB.

---

## 9. Quick How-To Share with Classmates

1. `cd /Users/jjoseph/Desktop/Project_Cybercafe`
2. `./Database/populateDB.sh --force`
3. `php -S 127.0.0.1:8000 -t Website`
4. Visit `http://127.0.0.1:8000/`
5. Log in with the accounts listed above and poke around each role dashboard.

That’s everything we changed this session. Let me know if we need screen grabs or a video walkthrough. 😊
