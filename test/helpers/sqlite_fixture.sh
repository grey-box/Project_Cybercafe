#!/usr/bin/env bash

create_test_db() {
    DB_PATH="$1"

    sqlite3 "$DB_PATH" <<EOF
CREATE TABLE sessions (
    session_id TEXT PRIMARY KEY,
    user_id TEXT,
    device_id TEXT,
    created_at TEXT
);

INSERT INTO sessions VALUES
('s1', 'u1', 'd1', '2026-02-01'),
('s2', 'u2', 'd2', '2026-02-01');
EOF
}

count_sessions() {
    sqlite3 "$1" "SELECT COUNT(*) FROM sessions;"
}
