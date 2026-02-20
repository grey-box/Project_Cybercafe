-- Fixture: Seed Internet Sessions
-- Based on CyberCafe_Database_Schema.sql

INSERT INTO users (user_id, username, password, user_level, lane_id, status) VALUES 
(101, 'testuser1', 'pass', 1, 1, 'ACTIVE'),
(102, 'testuser2', 'pass', 1, 1, 'ACTIVE');


-- Active Session 1
INSERT INTO internet_sessions (table_index, user_id, session_id, ip, session_tx, session_rx, session_access, datetime_created, datetime_sinceLastRequest, pending_deletion) VALUES
(0, 101, 'sess1', '192.168.1.101', 500, 1000, 1, '2023-01-01 10:00:00', '2023-01-01 10:05:00', 0);

-- Active Session 2 (Pending Deletion)
INSERT INTO internet_sessions (table_index, user_id, session_id, ip, session_tx, session_rx, session_access, datetime_created, datetime_sinceLastRequest, pending_deletion) VALUES
(1, 102, 'sess2', '192.168.1.102', 200, 400, 1, '2023-01-01 11:00:00', '2023-01-01 11:05:00', 1);

-- Initial User Data Usage (Required for remove_session logic)
INSERT INTO user_data_usage (user_id, session_number, session_entry_index, entry_datetime, interval_bytes_tx, interval_bytes_rx) VALUES
(101, 1, 0, '2023-01-01 10:00:00', 0, 0),
(102, 1, 0, '2023-01-01 11:00:00', 0, 0);
