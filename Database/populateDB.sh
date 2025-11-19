#!/usr/bin/env bash
set -euo pipefail

#
# Seed the CyberCafe SQLite database with rich test data for the 2025 schema.
# Usage:
#   ./populateDB.sh [--force] [--db /path/to/db.sqlite]
#   ./populateDB.sh my_test.db --force
#

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEFAULT_DB="$SCRIPT_DIR/CyberCafeTest.db"
SCHEMA_FILE="$SCRIPT_DIR/schema_2025_asu.sql"

FORCE=0
DB_FILE=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --force|-f)
      FORCE=1
      ;;
    --db)
      shift
      DB_FILE="${1:-}"
      if [[ -z "$DB_FILE" ]]; then
        echo "error: --db requires a path argument" >&2
        exit 1
      fi
      ;;
    *)
      if [[ -z "$DB_FILE" ]]; then
        DB_FILE="$1"
      else
        echo "error: unexpected argument '$1'" >&2
        exit 1
      fi
      ;;
  esac
  shift
done

if [[ -z "$DB_FILE" ]]; then
  DB_FILE="$DEFAULT_DB"
fi

if [[ ! -f "$SCHEMA_FILE" ]]; then
  echo "error: schema file not found at $SCHEMA_FILE" >&2
  exit 1
fi

if ! command -v sqlite3 >/dev/null 2>&1; then
  echo "error: sqlite3 command not found; please install SQLite3 first" >&2
  exit 1
fi

if [[ "$DB_FILE" != /* ]]; then
  DB_FILE="$SCRIPT_DIR/$DB_FILE"
fi

if [[ -e "$DB_FILE" && $FORCE -eq 0 ]]; then
  if [[ -t 0 ]]; then
    read -r -p "Database '$DB_FILE' already exists. Overwrite? [y/N] " reply
    if [[ ! "$reply" =~ ^([yY][eE][sS]?|[yY])$ ]]; then
      echo "Aborting without changes."
      exit 0
    fi
  else
    echo "error: database '$DB_FILE' exists. Re-run with --force to overwrite." >&2
    exit 1
  fi
fi

rm -f "$DB_FILE"

echo "Creating schema in $DB_FILE..."
sqlite3 "$DB_FILE" < "$SCHEMA_FILE"

echo "Populating reference and sample data..."
sqlite3 "$DB_FILE" <<'SQL'
PRAGMA foreign_keys = ON;
BEGIN;

INSERT INTO user_role (role_id, role_name, role_description, permission_set) VALUES
  ('admin', 'Administrator', 'Full administrative access to the cyber cafe systems', '*'),
  ('owner', 'Owner', 'Business owner with full visibility and high-level controls', 'core,analytics,finance'),
  ('user',  'Registered User', 'Subscribed member with quota and billing privileges', 'member_portal'),
  ('guest', 'Guest User', 'Time limited guest access with restricted browsing', 'guest_portal');

INSERT INTO user_status_lookup (status_code, description) VALUES
  ('ACTIVE',    'Active and in good standing'),
  ('SUSPENDED', 'Temporarily blocked due to policy breaches or unpaid balance'),
  ('BANNED',    'Permanently blocked from the cyber cafe network'),
  ('EXPIRED',   'Account or pass has expired and needs renewal'),
  ('PENDING',   'Pending verification or manual review');

INSERT INTO speed_queue (queue_id, queue_name, upload_speed_limit, download_speed_limit, bandwidth_quota) VALUES
  ('QUEUE_PREMIUM',  'Premium Express Lane', 200, 800, 500000),
  ('QUEUE_STANDARD', 'Standard Member Lane', 100, 400, 250000),
  ('QUEUE_GUEST',    'Guest Courtesy Lane',   20,  80,  25000);

INSERT INTO service_plan (plan_id, plan_name, upload_speed_limit, bandwidth_quota, monthly_price) VALUES
  ('PLAN_PREMIUM',  'Premium Unlimited', 200, 500000, 99.99),
  ('PLAN_STANDARD', 'Standard Flex',    100, 250000, 59.99),
  ('PLAN_GUEST',    'Guest Day Pass',    20,  25000,  9.99);

INSERT INTO user (user_id, full_name, email, phone_number, access_code, user_role, account_expiry_date, account_creation_date, last_login_timestamp) VALUES
  ('admin.alex',   'Alex Administrator', 'admin@example.com', '+1-555-0100', 'adminpass', 'admin', '2026-12-31 23:59:59', '2023-01-05 09:30:00', '2025-02-15 10:45:00'),
  ('owner.olivia', 'Olivia Owner',       'owner@example.com', '+1-555-0101', 'ownerpass', 'owner', '2026-06-30 23:59:59', '2024-01-03 11:00:00', '2025-02-12 13:15:00'),
  ('user.mia',     'Mia Member',         'user@example.com',  '+1-555-0102', 'userpass',  'user',  NULL,                   '2024-08-15 09:55:00', '2025-02-17 19:45:00'),
  ('guest.gina',   'Gina Guest',         'guest@example.com', '+1-555-0103', 'guestpass', 'guest', '2024-08-21 12:00:00', '2024-08-20 11:00:00', NULL);

INSERT INTO user_status_history (user_id, status_code, changed_by_user_id, changed_reason, changed_at) VALUES
  ('admin.alex',   'ACTIVE',   'admin.alex',   'Initial administrator account provisioning', '2023-01-05 09:30:00'),
  ('owner.olivia', 'PENDING',  'admin.alex',   'Invitation sent to new owner',               '2024-01-03 11:00:00'),
  ('owner.olivia', 'ACTIVE',   'admin.alex',   'Owner verified business documents',          '2024-01-04 08:45:00'),
  ('user.mia',     'PENDING',  'owner.olivia', 'Signed up at kiosk',                         '2024-08-15 09:55:00'),
  ('user.mia',     'ACTIVE',   'owner.olivia', 'Government ID verified',                     '2024-08-15 10:30:00'),
  ('guest.gina',   'PENDING',  'owner.olivia', 'Issued temporary guest pass',                '2024-08-20 11:05:00'),
  ('guest.gina',   'EXPIRED',  'owner.olivia', 'Guest pass expired automatically',           '2024-08-21 12:00:00');

INSERT INTO user_balance (user_id, speed_queue_id, monetary_balance, last_update_timestamp) VALUES
  ('admin.alex',   'QUEUE_PREMIUM',  250.00, '2025-02-15 10:45:00'),
  ('owner.olivia', 'QUEUE_PREMIUM',  180.50, '2025-02-12 13:15:00'),
  ('user.mia',     'QUEUE_STANDARD',  15.20, '2025-02-17 19:45:00'),
  ('guest.gina',   'QUEUE_GUEST',      0.00, '2024-08-20 11:05:00');

INSERT INTO byte_quota_ledger (user_id, speed_queue_id, bytes_delta, reason, created_at) VALUES
  ('admin.alex',   'QUEUE_PREMIUM',  104857600, 'Monthly premium quota grant',       '2025-02-01 00:00:00'),
  ('owner.olivia', 'QUEUE_PREMIUM',   52428800, 'Owner courtesy boost',              '2025-02-10 09:00:00'),
  ('user.mia',     'QUEUE_STANDARD',  10485760, 'Plan renewal',                      '2025-02-17 19:45:00'),
  ('guest.gina',   'QUEUE_GUEST',      3145728, 'Guest complimentary quota',         '2024-08-20 11:05:00');

INSERT INTO payment (payment_id, user_id, payment_datetime, payment_method, amount_charged, transaction_ref_number, invoice_number) VALUES
  ('PAY-20240104-OO', 'owner.olivia', '2024-01-04 08:50:00', 'Credit Card', 199.99, 'REF-OO-240104', 'INV-OO-2401'),
  ('PAY-20250215-AA', 'admin.alex',   '2025-02-15 10:40:00', 'Corporate',   249.99, 'REF-AA-250215', 'INV-AA-2502'),
  ('PAY-20250217-UM', 'user.mia',     '2025-02-17 19:40:00', 'Debit Card',   59.99, 'REF-UM-250217', 'INV-UM-2502'),
  ('PAY-20240820-GG', 'guest.gina',   '2024-08-20 11:05:00', 'Cash',          9.99, 'REF-GG-240820', 'INV-GG-2408');

INSERT INTO monetary_ledger (user_id, speed_queue_id, amount_delta, reason, ref_payment_id, created_at) VALUES
  ('owner.olivia', 'QUEUE_PREMIUM',  199.99,  'Initial owner subscription',          'PAY-20240104-OO', '2024-01-04 08:50:00'),
  ('owner.olivia', 'QUEUE_PREMIUM',  -19.99,  'Promotional credit',                   NULL,              '2024-02-01 09:10:00'),
  ('user.mia',     'QUEUE_STANDARD',  59.99,  'Standard plan renewal',               'PAY-20250217-UM', '2025-02-17 19:40:00'),
  ('admin.alex',   'QUEUE_PREMIUM',  249.99,  'Corporate retainer',                  'PAY-20250215-AA', '2025-02-15 10:40:00'),
  ('guest.gina',   'QUEUE_GUEST',      9.99,  'Guest day pass purchase',             'PAY-20240820-GG', '2024-08-20 11:05:00');

INSERT INTO payment_history (user_id, timestamp, amount_due, amount_paid, payment_status) VALUES
  ('owner.olivia', '2025-02-01 00:00:00', 199.99, 199.99, 'PAID'),
  ('user.mia',     '2025-02-17 19:40:00',  59.99,  59.99, 'PAID'),
  ('guest.gina',   '2024-08-20 11:05:00',   9.99,   9.99, 'PAID');

INSERT INTO internet_session (session_id, user_id, ip_address, mac_address, host_name, login_timestamp, logout_timestamp, speed_queue_id) VALUES
  ('SESS-ADMIN-001',  'admin.alex',   '10.0.0.10', 'AA:10:00:00:00:01', 'admin-console',     '2025-02-15 08:00:00', '2025-02-15 12:15:00', 'QUEUE_PREMIUM'),
  ('SESS-OWNER-001',  'owner.olivia', '10.0.0.20', 'OO:20:00:00:00:01', 'owner-dashboard',   '2025-02-12 09:30:00', '2025-02-12 11:30:00', 'QUEUE_PREMIUM'),
  ('SESS-USER-001',   'user.mia',     '10.0.1.30', 'UM:30:00:00:00:01', 'member-laptop',     '2025-02-17 18:30:00', '2025-02-17 20:00:00', 'QUEUE_STANDARD'),
  ('SESS-GUEST-001',  'guest.gina',   '10.0.2.40', 'GG:40:00:00:00:01', 'guest-tablet',      '2024-08-20 11:10:00', NULL,                   'QUEUE_GUEST');

INSERT INTO traffic_data (session_id, received_bytes, transmitted_bytes, last_updated_at) VALUES
  ('SESS-ADMIN-001',  95000000,  12500000, '2025-02-15 12:15:00'),
  ('SESS-OWNER-001',  72000000,   8200000, '2025-02-12 11:30:00'),
  ('SESS-USER-001',  18000000,   4500000, '2025-02-17 20:00:00'),
  ('SESS-GUEST-001',   2500000,    600000, '2024-08-20 12:30:00');

INSERT INTO report_run (run_id, user_id, report_type, parameters, generated_at, share_flag) VALUES
  ('RUN-20250215-SUMMARY', 'admin.alex',  'SYSTEM_SUMMARY', '{"range":"LAST_7_DAYS"}',  '2025-02-15 12:30:00', 1),
  ('RUN-20250212-REVENUE', 'owner.olivia','REVENUE_DAILY',  '{"date":"2025-02-12"}',    '2025-02-12 12:00:00', 1),
  ('RUN-20250217-QUOTA',   'user.mia',    'QUOTA_ALERTS',   '{"threshold":"LOW"}',      '2025-02-17 19:50:00', 0);

INSERT INTO system_event (event_type, description, details, occurred_at) VALUES
  ('SNAPSHOT',      'Daily health snapshot',        'All systems operational',                '2025-02-15 06:00:00'),
  ('ALERT',         'Low quota warning',            'user.mia has less than 2GB remaining',   '2025-02-15 15:30:00'),
  ('CONFIG_CHANGE', 'Queue tuning applied',         'Adjusted QUEUE_STANDARD bandwidth',      '2025-02-14 09:45:00'),
  ('MAINTENANCE',   'Planned maintenance window',   'Network switch firmware upgrade',        '2025-02-10 01:00:00'),
  ('OUTAGE',        'Unexpected outage resolved',   'Power cycle completed in zone 2',        '2025-02-05 14:20:00');

INSERT INTO url_restriction (url, is_blocked, created_at, created_by_user_id) VALUES
  ('https://www.education-portal.org', 0, '2024-08-01 09:00:00', 'admin.alex'),
  ('https://www.streamingplus.tv',     1, '2024-08-02 12:15:00', 'owner.olivia'),
  ('https://www.onlinegaminghub.com',  1, '2024-08-03 14:25:00', 'owner.olivia'),
  ('https://www.local-library.gov',    0, '2024-08-04 10:30:00', 'owner.olivia');

INSERT INTO device_restriction (mac_address, reason) VALUES
  ('AA:BB:CC:DD:EE:01', 'Suspected malware activity'),
  ('AA:BB:CC:DD:EE:02', 'Reported stolen device'),
  ('AA:BB:CC:DD:EE:03', 'Exceeded guest device limit');

COMMIT;
SQL

echo "Database seed complete. Generated file: $DB_FILE"
