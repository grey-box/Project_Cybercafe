-- User roles (lookup)
CREATE TABLE User_Role (
  role_id         VARCHAR(50)    PRIMARY KEY,
  role_name       VARCHAR(100)   NOT NULL,
  role_description VARCHAR(255),
  permission_set  TEXT
);

-- Main user table
CREATE TABLE "User" (
  user_id             VARCHAR(50)     PRIMARY KEY,
  full_name           VARCHAR(200)    NOT NULL,
  email               VARCHAR(200)    UNIQUE NOT NULL,
  phone_number        VARCHAR(30),
  access_code         VARCHAR(50),
  user_role           VARCHAR(50)     NOT NULL
    REFERENCES User_Role(role_id),
  user_status         VARCHAR(50),
  account_expiry_date DATE,
  account_creation_date DATE         NOT NULL,
  last_login_timestamp TIMESTAMP
);

-- Track changes to user status over time
CREATE TABLE User_Status_History (
  status_id    SERIAL         PRIMARY KEY,
  user_id      VARCHAR(50)    NOT NULL
    REFERENCES "User"(user_id),
  status_type  VARCHAR(50)    NOT NULL,
  timestamp    TIMESTAMP      NOT NULL,
  reason       VARCHAR(255)
);

-- Service tiers / queues
CREATE TABLE Speed_Queue (
  queue_id          VARCHAR(50)   PRIMARY KEY,
  queue_name        VARCHAR(100)  NOT NULL,
  upload_speed_limit INTEGER,
  download_speed_limit INTEGER,
  bandwidth_quota   INTEGER
);

-- Track user balances (bandwidth, monetary)
CREATE TABLE User_Balance (
  balance_id           SERIAL       PRIMARY KEY,
  user_id              VARCHAR(50)  NOT NULL
    REFERENCES "User"(user_id),
  speed_queue_id       VARCHAR(50)  NOT NULL
    REFERENCES Speed_Queue(queue_id),
  bytes_remaining      BIGINT,
  monetary_balance     DECIMAL(10,2),
  last_update_timestamp TIMESTAMP
);

-- Internet sessions (login/logout)
CREATE TABLE Internet_Session (
  session_id       VARCHAR(50)   PRIMARY KEY,
  user_id          VARCHAR(50)   NOT NULL
    REFERENCES "User"(user_id),
  ip_address       VARCHAR(45),
  mac_address      VARCHAR(17),
  host_name        VARCHAR(100),
  login_timestamp  TIMESTAMP     NOT NULL,
  logout_timestamp TIMESTAMP,
  session_length   INTEGER,
  speed_queue_id   VARCHAR(50)   NOT NULL
    REFERENCES Speed_Queue(queue_id)
);

-- Historical payment attempts
CREATE TABLE Payment_History (
  history_id     SERIAL       PRIMARY KEY,
  user_id        VARCHAR(50)  NOT NULL
    REFERENCES "User"(user_id),
  timestamp      TIMESTAMP    NOT NULL,
  amount_due     DECIMAL(10,2),
  amount_paid    DECIMAL(10,2),
  payment_status VARCHAR(50)
);

-- Actual payments
CREATE TABLE Payment (
  payment_id            VARCHAR(50)    PRIMARY KEY,
  user_id               VARCHAR(50)    NOT NULL
    REFERENCES "User"(user_id),
  payment_datetime      TIMESTAMP      NOT NULL,
  payment_method        VARCHAR(50),
  amount_charged        DECIMAL(10,2),
  transaction_ref_number VARCHAR(100),
  invoice_number        VARCHAR(50)
);

-- Reports generated by the system
CREATE TABLE Report (
  report_id       VARCHAR(50)    PRIMARY KEY,
  report_type     VARCHAR(100),
  generation_time TIMESTAMP      NOT NULL,
  parameters      TEXT
);

-- Map users to reports (many-to-many)
CREATE TABLE User_Report_Mapping (
  mapping_id    SERIAL       PRIMARY KEY,
  user_id       VARCHAR(50)  NOT NULL
    REFERENCES "User"(user_id),
  report_id     VARCHAR(50)  NOT NULL
    REFERENCES Report(report_id)
);

-- System status snapshots
CREATE TABLE System_Status (
  status_id        SERIAL      PRIMARY KEY,
  hotspot_status   VARCHAR(50),
  local_ip_address VARCHAR(45),
  last_refresh_time TIMESTAMP,
  uptime           INTEGER,
  restart_count    INTEGER,
  last_reboot_time TIMESTAMP,
  software_version VARCHAR(50)
);

-- Logs of maintenance events
CREATE TABLE Maintenance_Log (
  log_id      SERIAL       PRIMARY KEY,
  status_id   INTEGER      NOT NULL
    REFERENCES System_Status(status_id),
  event_type  VARCHAR(100),
  timestamp   TIMESTAMP    NOT NULL,
  description TEXT
);

-- Web‐site access per session
CREATE TABLE Website_Access_Log (
  log_id       SERIAL       PRIMARY KEY,
  session_id   VARCHAR(50)   NOT NULL
    REFERENCES Internet_Session(session_id),
  url          VARCHAR(2083),
  access_time  TIMESTAMP     NOT NULL,
  blocked      BOOLEAN
);

-- URL‐based restrictions
CREATE TABLE URL_Restriction (
  restriction_id SERIAL     PRIMARY KEY,
  url            VARCHAR(2083) NOT NULL,
  is_blocked     BOOLEAN       NOT NULL
);

-- Device‐based restrictions
CREATE TABLE Device_Restriction (
  restriction_id SERIAL     PRIMARY KEY,
  mac_address    VARCHAR(17) NOT NULL,
  reason         VARCHAR(255)
);

-- Traffic data per session
CREATE TABLE Traffic_Data (
  traffic_id        SERIAL     PRIMARY KEY,
  session_id        VARCHAR(50) NOT NULL
    REFERENCES Internet_Session(session_id),
  received_bytes    BIGINT,
  transmitted_bytes BIGINT
);

-- Membership levels (e.g., Silver, Gold)
CREATE TABLE Membership_Level (
  level_id    VARCHAR(50)   PRIMARY KEY,
  level_name  VARCHAR(100),
  description TEXT,
  benefits    TEXT,
  speed_amount DECIMAL(10,2),
  plan_id     VARCHAR(50)   NOT NULL
    REFERENCES Service_Plan(plan_id)
);

-- Subscription or service plans
CREATE TABLE Service_Plan (
  plan_id                VARCHAR(50)   PRIMARY KEY,
  plan_name              VARCHAR(100),
  upload_speed_limit     INTEGER,
  bandwidth_quota        INTEGER,
  monthly_price          DECIMAL(10,2)
);

-- Join table for many-to-many Service_Plan ↔ Membership_Level,
-- if the ER indicated “affected by” as many-to-many.
CREATE TABLE ServicePlan_Membership (
  plan_id   VARCHAR(50) NOT NULL
    REFERENCES Service_Plan(plan_id),
  level_id  VARCHAR(50) NOT NULL
    REFERENCES Membership_Level(level_id),
  PRIMARY KEY (plan_id, level_id)
);
