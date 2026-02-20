#!/usr/bin/env bash
set -Eeuo pipefail

# Resolve directories
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
FIXTURE_DIR="$SCRIPT_DIR/fixtures"
MOCK_DIR="$SCRIPT_DIR/mocks"
TMP_DIR="$SCRIPT_DIR/tmp"

# Source the function under test (will be sourced in tests to allow mocking)
# We don't source it here yet because we need to setup variables first

#################
# Test Helpers
#################

setup_env() {
    rm -rf "$MOCK_DIR" "$TMP_DIR"
    mkdir -p "$MOCK_DIR" "$TMP_DIR"

    # 1. Setup Mock Database
    export DATABASE_PATH="$TMP_DIR/test_db.sqlite"
    
    # Initialize DB with schema
    if [ ! -f "$ROOT_DIR/../Database/CyberCafe_Database_Schema.sql" ]; then
        echo "Error: Schema file not found at $ROOT_DIR/../Database/CyberCafe_Database_Schema.sql"
        exit 1
    fi
    sqlite3 "$DATABASE_PATH" < "$ROOT_DIR/../Database/CyberCafe_Database_Schema.sql"
    
    # Seed DB
    if [ ! -f "$FIXTURE_DIR/seed_sessions.sql" ]; then
        echo "Error: Seed file not found at $FIXTURE_DIR/seed_sessions.sql"
        exit 1
    fi
    sqlite3 "$DATABASE_PATH" < "$FIXTURE_DIR/seed_sessions.sql"

    # 2. Setup Mock iptables
    cat > "$MOCK_DIR/iptables" <<'EOT'
#!/usr/bin/env bash
echo "iptables $*" >> "${MOCK_DIR}/iptables_calls.log"
exit 0
EOT
    chmod +x "$MOCK_DIR/iptables"
    
    # 3. Setup Error Log
    touch "error.log"

    # 4. Export mocks to PATH
    export PATH="$MOCK_DIR:$PATH"
    export MOCK_DIR
}

teardown_env() {
    rm -rf "$MOCK_DIR" "$TMP_DIR" "error.log" 2>/dev/null || true
}

assert_db_count() {
    local table="$1"
    local expected="$2"
    local count
    count=$(sqlite3 "$DATABASE_PATH" "SELECT COUNT(*) FROM $table;")
    if [[ "$count" -ne "$expected" ]]; then
        echo "FAIL: Expected $expected rows in $table, found $count"
        return 1
    fi
}

assert_user_data_usage_exists() {
    local user_id="$1"
    local count
    count=$(sqlite3 "$DATABASE_PATH" "SELECT COUNT(*) FROM user_data_usage WHERE user_id=$user_id;")
    if [[ "$count" -eq 0 ]]; then
        echo "FAIL: No user_data_usage found for user_id $user_id"
        return 1
    fi
}

#################
# Tests
#################

test_clear_multiple_sessions() {
    echo "TEST: clear_internet_sessions with multiple sessions"
    setup_env

    # Verify initial state
    assert_db_count "internet_sessions" 2

    # Run SUT
    # We need to source the file inside the test or ensure variables are set before sourcing
    # The script uses HS_INTERFACE
    export HS_INTERFACE="wlan0"
    
    # Verify we can source the file without executing code (it only defines functions)
    source "$ROOT_DIR/Cybercafe_internetSessionFunctions.sh"

    clear_internet_sessions

    # Verify Final State
    # 1. internet_sessions should be empty
    assert_db_count "internet_sessions" 0
    
    # 2. user_data_usage should have entries for the cleared sessions
    assert_user_data_usage_exists 101 # user from seed
    assert_user_data_usage_exists 102 # user from seed

    # 3. iptables should have been called
    if [[ ! -f "$MOCK_DIR/iptables_calls.log" ]]; then
         echo "FAIL: iptables was not called"
         teardown_env
         return 1
    fi
    
    local ipt_calls
    ipt_calls=$(wc -l < "$MOCK_DIR/iptables_calls.log")
    if [[ "$ipt_calls" -lt 1 ]]; then
        echo "FAIL: Expected iptables calls, got $ipt_calls"
        teardown_env
        return 1
    fi

    echo "PASS"
    teardown_env
}

test_clear_idempotency() {
    echo "TEST: clear_internet_sessions idempotency (empty DB)"
    setup_env
    
    # Clear DB first manually to simulate empty state
    sqlite3 "$DATABASE_PATH" "DELETE FROM internet_sessions;"
    
    export HS_INTERFACE="wlan0"
    source "$ROOT_DIR/Cybercafe_internetSessionFunctions.sh"

    # Run SUT - should not fail
    if ! clear_internet_sessions; then
        echo "FAIL: clear_internet_sessions returned non-zero on empty DB"
        teardown_env
        return 1
    fi

    # Verify still empty
    assert_db_count "internet_sessions" 0
    
    echo "PASS"
    teardown_env
}

# Run tests
test_clear_multiple_sessions
test_clear_idempotency
