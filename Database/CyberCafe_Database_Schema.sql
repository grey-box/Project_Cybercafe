-- Internet Sessions --
CREATE TABLE internet_sessions(
table_index INTEGER NOT NULL, 
user_id INTEGER NOT NULL, 
session_id TEXT NOT NULL, 
ip TEXT NOT NULL, 
session_tx INTEGER NOT NULL, 
session_rx INTEGER NOT NULL, 
session_access INTEGER NOT NULL, 
datetime_created TEXT NOT NULL, 
datetime_sinceLastRequest TEXT NOT NULL, 
pending_deletion INTEGER NOT NULL, 
PRIMARY KEY (user_id), 
FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Users --
CREATE TABLE users(
user_id INTEGER NOT NULL, 
name TEXT,
email TEXT,
phone TEXT,
username TEXT NOT NULL, 
password TEXT NOT NULL, 
user_level INTEGER NOT NULL, 
lane_id INTEGER NOT NULL, 
status TEXT NOT NULL,
PRIMARY KEY (user_id)
);

-- User Data Usage --
CREATE TABLE user_data_usage(
user_id INTEGER NOT NULL, 
session_number INTEGER NOT NULL, 
session_entry_index INTEGER NOT NULL, 
entry_datetime TEXT NOT NULL, 
interval_bytes_tx INTEGER NOT NULL, 
interval_bytes_rx INTEGER NOT NULL, 
FOREIGN KEY (user_id) REFERENCES users(user_id));

-- Data Lanes --
CREATE TABLE data_lanes(
lane_id INTEGER NOT NULL, 
lane_name TEXT, 
bytelimit_daily BIGINT, 
bytelimit_weekly BIGINT, 
bytelimit_monthly BIGINT,
PRIMARY KEY (lane_id)
);

-- insert debug entries --
INSERT INTO users VALUES('0','','','','admin','8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918','0','1','ACTIVE');
INSERT INTO users VALUES('1','','','','user','04f8996da763b7a969b1028ee3007569eaf3a635486ddab211d512c85b9df8fb','1','2','ACTIVE');
INSERT INTO data_lanes VALUES(0,'no-limit','100000000000000000','100000000000000000','100000000000000000');
INSERT INTO data_lanes VALUES(1,'testlane1','1000000','1000000','1000000');
INSERT INTO data_lanes VALUES(2,'testlane2','5000000','5000000','5000000');