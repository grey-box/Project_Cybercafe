PRAGMA foreign_keys = ON;

/* =========================
   Roles & Users
   ========================= */

CREATE TABLE IF NOT EXISTS user_role (
  role_id TEXT PRIMARY KEY,
  role_name TEXT NOT NULL,
  role_description TEXT,
  permission_set TEXT
);

CREATE TABLE IF NOT EXISTS user (
  user_id TEXT PRIMARY KEY,
  full_name TEXT NOT NULL,
  email TEXT UNIQUE NOT NULL,
  phone_number TEXT,
  access_code TEXT,
  user_role TEXT NOT NULL,
  account_expiry_date TEXT,
  account_creation_date TEXT NOT NULL,
  last_login_timestamp DATETIME,
  FOREIGN KEY (user_role) REFERENCES user_role(role_id)
);

/* =========================
   User Status
   ========================= */

CREATE TABLE IF NOT EXISTS user_status_lookup (
  status_code TEXT PRIMARY KEY, -- ACTIVE, SUSPENDED, BANNED, EXPIRED, PENDING
  description TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS user_status_history (
  status_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT NOT NULL,
  status_code TEXT NOT NULL,
  changed_by_user_id TEXT,
  changed_reason TEXT,
  changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(user_id),
  FOREIGN KEY (status_code) REFERENCES user_status_lookup(status_code),
  FOREIGN KEY (changed_by_user_id) REFERENCES user(user_id)
);

CREATE INDEX IF NOT EXISTS idx_user_status_hist_user_time
  ON user_status_history(user_id, changed_at DESC);

CREATE VIEW IF NOT EXISTS user_current_status AS
SELECT h.user_id, h.status_code, h.changed_at
FROM user_status_history h
JOIN (
  SELECT user_id, MAX(changed_at) AS max_changed_at
  FROM user_status_history
  GROUP BY user_id
) last ON last.user_id = h.user_id AND last.max_changed_at = h.changed_at;

/* =========================
   Queues & Plans
   ========================= */

CREATE TABLE IF NOT EXISTS speed_queue (
  queue_id TEXT PRIMARY KEY,
  queue_name TEXT NOT NULL,
  upload_speed_limit INTEGER,
  download_speed_limit INTEGER,
  bandwidth_quota INTEGER
);

CREATE TABLE IF NOT EXISTS service_plan (
  plan_id TEXT PRIMARY KEY,
  plan_name TEXT NOT NULL,
  upload_speed_limit INTEGER,
  bandwidth_quota INTEGER,
  monthly_price DECIMAL(10,2)
);

/* =========================
   Balance, Ledgers, Payments
   ========================= */

CREATE TABLE IF NOT EXISTS user_balance (
  user_id TEXT NOT NULL,
  speed_queue_id TEXT NOT NULL,
  monetary_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  last_update_timestamp DATETIME,
  PRIMARY KEY (user_id, speed_queue_id),
  FOREIGN KEY (user_id) REFERENCES user(user_id),
  FOREIGN KEY (speed_queue_id) REFERENCES speed_queue(queue_id)
);

CREATE TABLE IF NOT EXISTS monetary_ledger (
  entry_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT NOT NULL,
  speed_queue_id TEXT,
  amount_delta DECIMAL(10,2) NOT NULL,
  reason TEXT,
  ref_payment_id TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(user_id),
  FOREIGN KEY (speed_queue_id) REFERENCES speed_queue(queue_id),
  FOREIGN KEY (ref_payment_id) REFERENCES payment(payment_id)
);

CREATE TABLE IF NOT EXISTS byte_quota_ledger (
  entry_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT NOT NULL,
  speed_queue_id TEXT NOT NULL,
  bytes_delta INTEGER NOT NULL,
  reason TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(user_id),
  FOREIGN KEY (speed_queue_id) REFERENCES speed_queue(queue_id)
);

CREATE TABLE IF NOT EXISTS payment_history (
  history_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT NOT NULL,
  timestamp DATETIME NOT NULL,
  amount_due DECIMAL(10,2),
  amount_paid DECIMAL(10,2),
  payment_status TEXT,
  FOREIGN KEY (user_id) REFERENCES user(user_id)
);

CREATE TABLE IF NOT EXISTS payment (
  payment_id TEXT PRIMARY KEY,
  user_id TEXT NOT NULL,
  payment_datetime DATETIME NOT NULL,
  payment_method TEXT,
  amount_charged DECIMAL(10,2) NOT NULL,
  transaction_ref_number TEXT,
  invoice_number TEXT,
  FOREIGN KEY (user_id) REFERENCES user(user_id)
);

/* =========================
   Sessions & Traffic
   ========================= */

CREATE TABLE IF NOT EXISTS internet_session (
  session_id TEXT PRIMARY KEY,
  user_id TEXT NOT NULL,
  ip_address TEXT,
  mac_address TEXT,
  host_name TEXT,
  login_timestamp DATETIME NOT NULL,
  logout_timestamp DATETIME, -- NULL = active
  speed_queue_id TEXT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES user(user_id),
  FOREIGN KEY (speed_queue_id) REFERENCES speed_queue(queue_id)
);

CREATE VIEW IF NOT EXISTS internet_session_with_length AS
SELECT s.*,
       CASE
         WHEN s.logout_timestamp IS NOT NULL
           THEN CAST((JULIANDAY(s.logout_timestamp) - JULIANDAY(s.login_timestamp)) * 86400 AS INTEGER)
         ELSE NULL
       END AS session_length_seconds
FROM internet_session s;

CREATE VIEW IF NOT EXISTS active_session AS
SELECT * FROM internet_session WHERE logout_timestamp IS NULL;

CREATE TABLE IF NOT EXISTS traffic_data (
  session_id TEXT PRIMARY KEY,
  received_bytes INTEGER NOT NULL DEFAULT 0,
  transmitted_bytes INTEGER NOT NULL DEFAULT 0,
  last_updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES internet_session(session_id)
);

/* =========================
   Reporting
   ========================= */

CREATE TABLE IF NOT EXISTS report_run (
  run_id TEXT PRIMARY KEY,
  user_id TEXT NOT NULL,
  report_type TEXT NOT NULL,
  parameters TEXT,
  generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  share_flag INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES user(user_id)
);

/* =========================
   System (Events)
   ========================= */

CREATE TABLE IF NOT EXISTS system_event (
  event_id INTEGER PRIMARY KEY AUTOINCREMENT,
  event_type TEXT NOT NULL,
  description TEXT,
  details TEXT,
  occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_system_event_time
  ON system_event(occurred_at DESC);

CREATE VIEW IF NOT EXISTS system_current AS
SELECT e.*
FROM system_event e
JOIN (
  SELECT MAX(occurred_at) AS last_time
  FROM system_event
  WHERE event_type IN ('SNAPSHOT','CONFIG_CHANGE')
) t ON t.last_time = e.occurred_at;

/* =========================
   Restrictions
   ========================= */

CREATE TABLE IF NOT EXISTS url_restriction (
  url TEXT PRIMARY KEY,
  is_blocked INTEGER NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_by_user_id TEXT,
  FOREIGN KEY (created_by_user_id) REFERENCES user(user_id)
);

CREATE TABLE IF NOT EXISTS device_restriction (
  restriction_id INTEGER PRIMARY KEY AUTOINCREMENT,
  mac_address TEXT NOT NULL,
  reason TEXT
);

/* =========================
   Support
   ========================= */

CREATE TABLE IF NOT EXISTS support_ticket (
  ticket_id TEXT PRIMARY KEY,
  user_id   TEXT NOT NULL,
  title     TEXT NOT NULL,
  description TEXT,
  status    TEXT NOT NULL DEFAULT 'OPEN',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES user(user_id)
);

CREATE INDEX IF NOT EXISTS idx_support_ticket_user
  ON support_ticket(user_id, created_at DESC);

CREATE TABLE IF NOT EXISTS support_message (
  message_id INTEGER PRIMARY KEY AUTOINCREMENT,
  ticket_id  TEXT NOT NULL,
  sender_role TEXT NOT NULL,
  sender_user_id TEXT,
  body       TEXT NOT NULL,
  posted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES support_ticket(ticket_id),
  FOREIGN KEY (sender_user_id) REFERENCES user(user_id)
);

CREATE INDEX IF NOT EXISTS idx_support_message_ticket
  ON support_message(ticket_id, posted_at ASC);
