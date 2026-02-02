#!/usr/bin/env bash

# Test environment bootstrap for CyberCafe Backend tests
# Goals:
# - isolate tests in a temp directory
# - support PATH-based mocking (fakebin)
# - set safe defaults (DRY_RUN=true)
# - avoid relying on current working directory
# - cleanup automatically unless KEEP_TEST_TMP=1

# Usage: 
# - Source this file in test scripts: source ./env.sh

set -Eeuo pipefail

# Resolve Backend/ directory from this file's location
# env.sh lives in Backend/test/helpers/
BACKEND_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TEST_DIR="$BACKEND_DIR/test"

# Provide repo root too (one level up from Backend/)
REPO_ROOT_DIR="$(cd "$BACKEND_DIR/.." && pwd)"

export CYBERCAFE_BACKEND_DIR="$BACKEND_DIR"
export CYBERCAFE_TEST_DIR="$TEST_DIR"
export CYBERCAFE_REPO_ROOT_DIR="$REPO_ROOT_DIR"

# Create a per-test temp directory (one sandbox per test process)
TEST_TMPDIR="${TEST_TMPDIR:-$(mktemp -d "${TMPDIR:-/tmp}/cybercafe_test.XXXXXX")}"
export TEST_TMPDIR

# Where our mock binaries live (prepared to PATH)
FAKEBIN="$TEST_TMPDIR/fakebin"
mkdir -p "$FAKEBIN"
export FAKEBIN
export PATH="$FAKEBIN:$PATH"

# Common scratch locations (tests can use these by default)
TEST_LOGDIR="$TEST_TMPDIR/logs"
mkdir -p "$TEST_LOGDIR"
export TEST_LOGDIR

# Safe defaults for scripts under test (override in a test if needed)
export DRY_RUN="${DRY_RUN:-true}"
export HS_INTERFACE="${HS_INTERFACE:-wlan0}"

# Default status/state paths (override per-test if scripts require different variables)
export STATUS_PATH="${STATUS_PATH:-$TEST_TMPDIR/status}"
: > "$STATUS_PATH" || true # create empty status file

# Optional: capture all mock command invocations in one place if mocks choose to use it
export MOCK_CALL_LOG="${MOCK_CALL_LOG:-$TEST_TMPDIR/mock_calls.log}"
: > "$MOCK_CALL_LOG" || true # create empty mock call log

# Cleanup unless user explicitly asks to keep temp directorys for debugging
cleanup_test_env() {
  if [[ "${KEEP_TEST_TMP:-0}" == "1" ]]; then
    echo "NOTE: KEEP_TEST_TMP=1 set; preserving TEST_TMPDIR: $TEST_TMPDIR" >&2
    return 0
  fi
  rm -rf "$TEST_TMPDIR" 2>/dev/null || true
}
trap cleanup_test_env EXIT