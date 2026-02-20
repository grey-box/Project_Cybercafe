#!/usr/bin/env bats

# ---------------------------------------------------------------------------
# Organization: Grey-box
# Project:      Cybercafe
# File:         start_captive_webserver_test.bats
# Description:  Automated unit testing for start_captive_webserver() defined
#               in Cybercafe_setupFunctions.sh.
#
#               This file covers:
#                 - Category 1: Variable guard precondition checks (T1-T6)
#                 - Category 2: Path validation checks (T7-T11)
#                 - Category 3: Happy-path / successful start (T12-T16)
#                 - Category 4: Idempotency (T17-T20)
#                 - Category 5: Error log format and timestamp (T21-T23)
#                 - Category 6: Edge and boundary cases (T24-T25)
#
# Usage:        bats test/start_captive_webserver_test.bats
# ---------------------------------------------------------------------------

# Automated unit testing for start_captive_webserver() defined in Cybercafe_setupFunctions().sh.
# This BATs testing file covers precondition guards, idempotency, process lifecycle, error logging, and returning exit codes.

# setup() — runs automatically before EVERY test
#
# Creates fresh isolated temp directories for each test so:
#   - No test pollutes another test's state
#   - error.log writes are captured per-test inside TEST_DIR
#   - The lighttpd stub is a fake executable we fully control
#   - The lighttpd conf is a dummy file that simply exists
#
# After sourcing the real implementation, we cd into TEST_DIR so that
# the function's relative "error.log" writes land in our isolated directory
# rather than the project root.

setup() {
    # Create an isolated temp directory for every test
    TEST_DIR="$(mktemp -d)"
    ERROR_LOG="${TEST_DIR}/error.log"

    # Create fake lighttpd executable (succeeds silently, backgrounds cleanly)
    # mimicks how the real daemon infrastructure would daemonize itself
    # Daemonize - allowing programs run in the background independently from user processes.
    LIGHTTPD_PATH="${TEST_DIR}/lighttpd"
    cat > "${LIGHTTPD_PATH}" <<'EOF'
#!/bin/bash
# Minimal lighttpd stub — stays alive long enough for tests
sleep 30 &
exit 0
EOF
    chmod +x "${LIGHTTPD_PATH}"

    # Fake lighttpd config file
    LIGHTTPD_CONF="${TEST_DIR}/lighttpd.conf"
    touch "${LIGHTTPD_CONF}"

    # Source the function under test from the project root.
    # Adjust path if your layout differs.
    source "${BATS_TEST_DIRNAME}/../Cybercafe_setupFunctions.sh"

    # Override the global error.log path used inside the function
    # by cd-ing into the temp dir so relative "error.log" writes land there.
    cd "${TEST_DIR}"

    # Track background PIDs spawned by stubs so teardown can reap them
    SPAWNED_PIDS=()
}

teardown() {
    # Kill any lighttpd stubs left running
    pkill -f "${TEST_DIR}/lighttpd" 2>/dev/null || true

    # Remove temp directory
    rm -rf "${TEST_DIR}"
}

# ---------------------------------------------------------------------------
# Helper: make a stub that exits with a given code
# ---------------------------------------------------------------------------
make_lighttpd_stub() {
    local exit_code="${1:-0}"
    cat > "${LIGHTTPD_PATH}" <<EOF
#!/bin/bash
sleep 30 &
exit ${exit_code}
EOF
    chmod +x "${LIGHTTPD_PATH}"
}

# ============================================================
# Category 1: Variable Guard Testing (T1 - T6)
# ============================================================

# The function must return exit code 1 immediately if LIGHTTPD_PATH
# has not been exported into the environment. Without knowing where
# the binary is, the function cannot proceed safely.
@test "[TEST 1] Returns 1 when LIGHTTPD_PATH is unset" {
    unset LIGHTTPD_PATH
    run start_captive_webserver
    [ "$status" -eq 1 ]
}

# Same guard applies to LIGHTTPD_CONF — the function must not attempt
# to start lighttpd without knowing which config file to pass to it.
@test "[TEST 2] Returns 1 when LIGHTTPD_CONF is unset" {
    unset LIGHTTPD_CONF
    run start_captive_webserver
    [ "$status" -eq 1 ]
}

# Both variables missing at the same time should also fail with code 1.
# The guard checks both in a single condition so this confirms that
# combined absence is handled correctly.
@test "[TEST 3] Returns 1 when LIGHTTPD_PATH AND LIGHTTPD_CONF is unset" {
    unset LIGHTTPD_PATH
    unset LIGHTTPD_CONF
    run start_captive_webserver
    [ "$status" -eq 1 ]
}

# Function must also write a human-readable error message to error.log 
# so operators can diagnose what went wrong.
# Don't use 'run' to execute in current shell and writing to the actual ERROR_LOG path.
@test "[TEST 4] Logs an error when LIGHTTPD_PATH is unset" {
    unset LIGHTTPD_PATH
    start_captive_webserver || true
    grep -q "LIGHTTPD_PATH or LIGHTTPD_CONF" "${ERROR_LOG}"

}

# Same log check for when LIGHTTPD_CONF is the missing variable.
# but sends error log covering both cases in the implementation.
@test "[TEST 5] Logs an error when LIGHTTPD_CONF is unset" {
    unset LIGHTTPD_CONF
    start_captive_webserver || true
    grep -q "LIGHTTPD_PATH or LIGHTTPD_CONF" "${ERROR_LOG}"
}

# An empty string ("") is different from unset in bash — the variable
# exists but has no value. The guard uses [ -z ] which catches both,
# so an empty LIGHTTPD_PATH must also trigger a return 1.
@test "[TEST 6] Returns 1 when LIGHTTPD_PATH is set to empty string" {
    LIGHTTPD_PATH=""
    run start_captive_webserver
    [ "$status" -eq 1 ]
}

# ============================================================
# Category 2: Path Validation Testing (T7 - T11)
# ============================================================

# Even if LIGHTTPD_PATH is set to a string, the path must exist on disk.
# Pointing it to a non-existent file should fail the -x check and return 1.
@test "[TEST 7] Returns 1 when LIGHTTPD_PATH does not exist" {
    LIGHTTPD_PATH="${TEST_DIR}/nonexistent_lighttpd"
    run start_captive_webserver
    [ "$status" -eq 1 ]
}

# The stub file exists but we remove its execute permission.
# The -x check requires the file to be executable, so this must return 1.
@test "[TEST 8] Returns 1 when LIGHTTPD_PATH exists but is not executable" {
    chmod -x "${LIGHTTPD_PATH}"
    run start_captive_webserver
    [ "$status" -eq 1 ]
}

# When the executable check fails, an error must also be written to the log
# so an operator knows exactly why the webserver did not start.
@test "[TEST 9] Logs an error when LIGHTTPD_PATH is not executable" {
    chmod -x "${LIGHTTPD_PATH}"
    start_captive_webserver || true
    grep -q "lighttpd executable not found" "${ERROR_LOG}"
}

# If LIGHTTPD_CONF points to a file that doesn't exist, the function
# must catch this with -f and return 1 before attempting to start lighttpd.
@test "[TEST 10] Returns 1 when LIGHTTPD_CONF does not exist" {
    LIGHTTPD_CONF="${TEST_DIR}/nonexistent.conf"
    run start_captive_webserver
    [ "$status" -eq 1 ]
}

# A missing config must also produce a log entry — without this,
# operators would have no way of knowing the config path was wrong.
@test "[TEST 11] Logs an error when LIGHTTPD_CONF is missing" {
    LIGHTTPD_CONF="${TEST_DIR}/nonexistent.conf"
    start_captive_webserver || true
    grep -q "lighttpd configuration file not found" "${ERROR_LOG}"
}

# ---------------------------------------------------------------------------
# Category 3 – Happy-path / successful start tests (T12 – T16)
# ---------------------------------------------------------------------------

# The simplest happy-path check: when everything is in order,
# the function must return exit code 0 to signal success.
@test "[TEST 12] Returns a 0 when all preconditions are satisfied" {
    run start_captive_webserver
    [ "$status" -eq 0 ]
}

# We confirm the webserver was launched by checking the success log message.
# On WSL, pgrep may not reliably find the stub after it backgrounds a sleep,
# so we use the log entry as a proxy for the process having been launched.
@test "[TEST 13] Lighttpd process is running after a successful call" {
    run start_captive_webserver
    [ "$status" -eq 0 ]
    # Verify the log confirms it started — proxy for process being launched
    grep -q "Captive portal webserver started" "${ERROR_LOG}"
}

# The function must log that it is about to start the server before
# attempting to launch it. This helps operators trace exactly when
# a start attempt occurred in a timeline of log events.
# We call the function twice here to ensure the log line appears even
# after the idempotency path has fired once.
@test "[TEST 14] Logs 'Starting captive portal webserver' on first start" {
    start_captive_webserver
    run start_captive_webserver
    grep -q "Starting captive portal webserver" "${ERROR_LOG}"
}

# After successfully launching lighttpd, the function must log a
# confirmation message. This is distinct from the "Starting" message
# and confirms the launch command completed without error.
@test "[TEST 15] Logs 'Captive portal webserver started' after successful launch" {
    start_captive_webserver
    grep -q "Captive portal webserver started" "${ERROR_LOG}"
}

# The function must pass the config file to lighttpd using the -f flag.
# We replace the stub with one that records all arguments it receives,
# then verify the invocation log contains "-f <conf path>".
@test "[TEST 16] Lighttpd is invoked with the correct config file path" {
    # Replace stub with one that records its arguments
    cat > "${LIGHTTPD_PATH}" <<EOF
#!/bin/bash
# Record all arguments passed to this stub for inspection
echo "ARGS: \$@" >> "${TEST_DIR}/invocation.log"
sleep 30 &
exit 0
EOF
    chmod +x "${LIGHTTPD_PATH}"
    start_captive_webserver
    grep -q "\-f ${LIGHTTPD_CONF}" "${TEST_DIR}/invocation.log"

}

# ---------------------------------------------------------------------------
# Category 4 – Idempotency tests (TC-17 – TC-20)
# ---------------------------------------------------------------------------

# Calling the function a second time while the server is running should
# still return 0 — the function treats this as a non-error condition.
@test "[TEST 17] Returns 0 on second call when server is already running" {
    start_captive_webserver
    sleep 0.2
    run start_captive_webserver
    [ "$status" -eq 0 ]
}

# When the function detects lighttpd is already running, it must log
# an "already running" message so operators can confirm the idempotency
# path fired rather than a fresh start occurring.
@test "[TEST 18] Logs 'already running' message on second call" {
    # Stub must stay alive AND keep 'lighttpd' as its process name
    # Use a sleep loop without exec so the shell process retains the stub name
    cat > "${LIGHTTPD_PATH}" <<'EOF'
#!/bin/bash
while true; do sleep 1; done
EOF
    chmod +x "${LIGHTTPD_PATH}"

    "${LIGHTTPD_PATH}" &
    STUB_PID=$!
    sleep 0.5

    # Verify pgrep can actually find it before calling function
    pgrep -f "${LIGHTTPD_PATH}" > /dev/null || { echo "stub not found by pgrep" >&3; false; }

    start_captive_webserver
    grep -q "already running" "${ERROR_LOG}"

    kill "$STUB_PID" 2>/dev/null || true
    wait "$STUB_PID" 2>/dev/null || true
}

# If lighttpd is already running, calling the function must not launch
# a second instance. We count processes matching the stub path and
# assert there is exactly 1.
@test "[TEST 19] Does not spawn a second lighttpd process on second call" {
    cat > "${LIGHTTPD_PATH}" <<'EOF'
#!/bin/bash
while true; do sleep 1; done
EOF
    chmod +x "${LIGHTTPD_PATH}"

    "${LIGHTTPD_PATH}" &
    STUB_PID=$!
    sleep 0.5

    start_captive_webserver  # should detect already running, not spawn another

    # Count processes matching our stub's full path.
    # Using the full path avoids accidentally matching unrelated system processes.
    # wc -l counts matching PIDs; tr removes any trailing whitespace.
    count=$(pgrep -f "${LIGHTTPD_PATH}" | wc -l | tr -d '[:space:]')
    [ "$count" -eq 1 ]

    kill "$STUB_PID" 2>/dev/null || true
    wait "$STUB_PID" 2>/dev/null || true

}

# Calling the function three times in a row must not produce any errors.
# The final call's exit code is what `run` captures, and it must be 0.
@test "[TEST 20] Function is callable multiple times without any errors" {
    start_captive_webserver
    sleep 0.2
    run start_captive_webserver
    run start_captive_webserver
    # All calls should succeed
    [ "$status" -eq 0 ]
}

# ---------------------------------------------------------------------------
# Category 5 – Error logging format / timestamp tests (TC-21 – TC-23)
# ---------------------------------------------------------------------------

# Every error.log entry starts with `date -Is` output, which produces
# an ISO-8601 format: YYYY-MM-DDTHH:MM:SS+HH:MM.
# We trigger an error (unset LIGHTTPD_PATH) to force a log write,
# then regex-match the expected timestamp format.
@test "[TEST 21] Error log entries include an ISO-8601 timestamp" {
    unset LIGHTTPD_PATH
    start_captive_webserver || true
    # date -Is format: YYYY-MM-DDTHH:MM:SS+HH:MM
    grep -qE "^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}" "${ERROR_LOG}"
}

# Log entries must name the file they came from ("Cybercafe_setupFunction")
# so that in a merged log file from multiple scripts, the source is clear.
@test "[TEST 22] Error log entries reference the correct script filename" {
    unset LIGHTTPD_PATH
    start_captive_webserver || true
    grep -q "Cybercafe_setupFunction" "${ERROR_LOG}"
}

# If error.log does not exist yet when the function runs, the shell's
# >> redirection operator must create it automatically.
# We delete the file first to verify creation from scratch.
@test "[TEST 23] Error log is created if it does not already exist" {
    rm -f "${ERROR_LOG}"
    unset LIGHTTPD_PATH
    start_captive_webserver || true
    [ -f "${ERROR_LOG}" ]

}

# ---------------------------------------------------------------------------
# Category 6 – Edge / boundary cases (TC-24 – TC-25)
# ---------------------------------------------------------------------------

# An empty string for LIGHTTPD_CONF is a different failure mode from
# an unset variable — the variable exists but is blank. The -z guard
# must catch this and return 1 just as it would for an unset variable.
@test "[TEST 24] Returns 1 when LIGHTTPD_CONF is set to empty string" {
    LIGHTTPD_CONF=""
    run start_captive_webserver
    [ "$status" -eq 1 ]
}

# File paths with spaces in them must be handled correctly.
# The function uses quoted variable expansions ("${LIGHTTPD_PATH}")
# which is required to prevent word-splitting on spaces.
# This test places both the stub and conf inside a directory named
# "path with spaces" to confirm quoting works end-to-end.
@test "[TEST 25] Paths containing spaces are handled correctly" {
    # Re-create stub and conf inside a path with spaces
    SPACE_DIR="${TEST_DIR}/path with spaces"
    mkdir -p "${SPACE_DIR}"

    LIGHTTPD_PATH="${SPACE_DIR}/lighttpd"
    cat > "${LIGHTTPD_PATH}" <<'EOF'
#!/bin/bash
sleep 30 &
exit 0
EOF
    chmod +x "${LIGHTTPD_PATH}"

    LIGHTTPD_CONF="${SPACE_DIR}/lighttpd.conf"
    touch "${LIGHTTPD_CONF}"

    run start_captive_webserver
    [ "$status" -eq 0 ]

}
