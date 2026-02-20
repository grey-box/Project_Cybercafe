# remove_session Behavior Contract

## Purpose
Safely and idempotently remove a session from the SQLite session store.

Integration tests run repeatedly. This function must be reliable and safe to call multiple times.

---

## Function Signature

remove_session --db <db_path> --session-id <id>

---

## Required Parameters

--db (required): Path to SQLite database
--session-id (required): ID of session to remove

---

## Success Definition

Success means:

If session exists:
- Session row is deleted
- Exit code 0

If session does NOT exist:
- No-op (safe)
- Exit code 0

Database remains valid and consistent.

---

## Expected DB Changes

DELETE FROM sessions WHERE session_id = ?

No other tables are modified.

---

## Idempotency Guarantee

Calling:

remove_session X
remove_session X

Produces the same final database state as calling it once.

---

## Failure Modes

Missing DB file -> Exit code 1
Invalid arguments -> Exit code 2
SQLite error -> Exit code 3

Errors must log a helpful message to stderr.

---

## Logging Expectations

On success: no output
On no-op: optional info log
On failure: descriptive error message
