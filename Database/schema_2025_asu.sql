PRAGMA foreign_keys = ON;

/* =========================
   Roles & Users
   ========================= */

CREATE TABLE IF NOT EXISTS User_Role (
  role_id TEXT PRIMARY KEY,
  role_name TEXT NOT NULL,
  role_description TEXT,
  permission_set TEXT
);

CREATE TABLE IF NOT EXISTS User (
  user_id TEXT PRIMARY KEY,
  full_name TEXT NOT NULL,
  email TEXT UNIQUE NOT NULL,
  phone_number TEXT,
  access_code TEXT,
  user_role TEXT NOT NULL,
  account_expiry_date TEXT,
  account_creation_date TEXT NOT NULL,
  last_login_timestamp DATETIME,
  FOREIGN KEY (user_role) REFERENCES User_Role(role_id)
);

/* =========================
   User Status
   ========================= */

CREATE TABLE IF NOT EXISTS User_Status_Lookup (
  status_code TEXT PRIMARY KEY, -- ACTIVE, SUSPENDED, BANNED, EXPIRED, PENDING
  description TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS User_Status_History (
  status_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT NOT NULL,
  status_code TEXT NOT NULL,
  changed_by_user_id TEXT,
  changed_reason TEXT,
  changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES User(user_id),
  FOREIGN KEY (status_code) REFERENCES User_Status_Lookup(status_code),
  FOREIGN KEY (changed_by_user_id) REFERENCES User(user_id)
);

CREATE INDEX IF NOT EXISTS idx_user_status_hist_user_time
  ON User_Status_History(user_id, changed_at DESC);

CREATE VIEW IF NOT EXISTS User_Current_Status AS
SELECT h.user_id, h.status_code, h.changed_at
FROM User_Status_History h
JOIN (
  SELECT user_id, MAX(changed_at) AS max_changed_at
  FROM User_Status_History
  GROUP BY user_id
) last ON last.user_id = h.user_id AND last.max_changed_at = h.changed_at;

/* =========================
   Queues & Plans
   ========================= */

CREATE TABLE IF NOT EXISTS Speed_Queue (
  queue_id TEXT PRIMARY KEY,
  queue_name TEXT NOT NULL,
  upload_speed_limit INTEGER,
  download_speed_limit INTEGER,
  bandwidth_quota INTEGER
);

CREATE TABLE IF NOT EXISTS Service_Plan (
  plan_id TEXT PRIMARY KEY,
  plan_name TEXT NOT NULL,
  upload_speed_limit INTEGER,
  bandwidth_quota INTEGER,
  monthly_price DECIMAL(10,2)
);

/* =========================
   Balance, Ledgers, Payments
   ========================= */

CREATE TABLE IF NOT EXISTS User_Balance (
  user_id TEXT NOT NULL,
  speed_queue_id TEXT NOT NULL,
  monetary_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  last_update_timestamp DATETIME,
  PRIMARY KEY (user_id, speed_queue_id),
  FOREIGN KEY (user_id) REFERENCES User(user_id),
  FOREIGN KEY (speed_queue_id) REFERENCES Speed_Queue(queue_id)
);

CREATE TABLE IF NOT EXISTS Monetary_Ledger (
  entry_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT NOT NULL,
  speed_queue_id TEXT,
  amount_delta DECIMAL(10,2) NOT NULL,
  reason TEXT,
  ref_payment_id TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES User(user_id),
  FOREIGN KEY (speed_queue_id) REFERENCES Speed_Queue(queue_id),
  FOREIGN KEY (ref_payment_id) REFERENCES Payment(payment_id)
);

CREATE TABLE IF NOT EXISTS Byte_Quota_Ledger (
  entry_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT NOT NULL,
  speed_queue_id TEXT NOT NULL,
  bytes_delta INTEGER NOT NULL,
  reason TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES User(user_id),
  FOREIGN KEY (speed_queue_id) REFERENCES Speed_Queue(queue_id)
);

CREATE TABLE IF NOT EXISTS Payment_History (
  history_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT NOT NULL,
  timestamp DATETIME NOT NULL,
  amount_due DECIMAL(10,2),
  amount_paid DECIMAL(10,2),
  payment_status TEXT,
  FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE IF NOT EXISTS Payment (
  payment_id TEXT PRIMARY KEY,
  user_id TEXT NOT NULL,
  payment_datetime DATETIME NOT NULL,
  payment_method TEXT,
  amount_charged DECIMAL(10,2) NOT NULL,
  transaction_ref_number TEXT,
  invoice_number TEXT,
  FOREIGN KEY (user_id) REFERENCES User(user_id)
);

/* =========================
   Sessions & Traffic
   ========================= */

CREATE TABLE IF NOT EXISTS Internet_Session (
  session_id TEXT PRIMARY KEY,
  user_id TEXT NOT NULL,
  ip_address TEXT,
  mac_address TEXT,
  host_name TEXT,
  login_timestamp DATETIME NOT NULL,
  logout_timestamp DATETIME, -- NULL = active
  speed_queue_id TEXT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES User(user_id),
  FOREIGN KEY (speed_queue_id) REFERENCES Speed_Queue(queue_id)
);

CREATE VIEW IF NOT EXISTS Internet_Session_With_Length AS
SELECT s.*,
       CASE
         WHEN s.logout_timestamp IS NOT NULL
           THEN CAST((JULIANDAY(s.logout_timestamp) - JULIANDAY(s.login_timestamp)) * 86400 AS INTEGER)
         ELSE NULL
       END AS session_length_seconds
FROM Internet_Session s;

CREATE VIEW IF NOT EXISTS Active_Session AS
SELECT * FROM Internet_Session WHERE logout_timestamp IS NULL;

CREATE TABLE IF NOT EXISTS Traffic_Data (
  session_id TEXT PRIMARY KEY,
  received_bytes INTEGER NOT NULL DEFAULT 0,
  transmitted_bytes INTEGER NOT NULL DEFAULT 0,
  last_updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES Internet_Session(session_id)
);

/* =========================
   Reporting
   ========================= */

CREATE TABLE IF NOT EXISTS Report_Run (
  run_id TEXT PRIMARY KEY,
  user_id TEXT NOT NULL,
  report_type TEXT NOT NULL,
  parameters TEXT,
  generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  share_flag INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES User(user_id)
);

/* =========================
   System (Events)
   ========================= */

CREATE TABLE IF NOT EXISTS System_Event (
  event_id INTEGER PRIMARY KEY AUTOINCREMENT,
  event_type TEXT NOT NULL,
  description TEXT,
  details TEXT,
  occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_system_event_time
  ON System_Event(occurred_at DESC);

CREATE VIEW IF NOT EXISTS System_Current AS
SELECT e.*
FROM System_Event e
JOIN (
  SELECT MAX(occurred_at) AS last_time
  FROM System_Event
  WHERE event_type IN ('SNAPSHOT','CONFIG_CHANGE')
) t ON t.last_time = e.occurred_at;

/* =========================
   Restrictions
   ========================= */

CREATE TABLE IF NOT EXISTS URL_Restriction (
  url TEXT PRIMARY KEY,
  is_blocked INTEGER NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_by_user_id TEXT,
  FOREIGN KEY (created_by_user_id) REFERENCES User(user_id)
);

CREATE TABLE IF NOT EXISTS Device_Restriction (
  restriction_id INTEGER PRIMARY KEY AUTOINCREMENT,
  mac_address TEXT NOT NULL,
  reason TEXT
);