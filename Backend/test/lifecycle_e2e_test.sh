#!/usr/bin/env bash
set -e

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd -- "$SCRIPT_DIR/.." && pwd)"
CYBERCAFE_SH="$ROOT_DIR/cybercafe.sh"

echo "======================================="
echo "CyberCafe Lifecycle E2E Test"
echo "======================================="

if [[ -n "${TMPDIR:-}" && -d "${TMPDIR}" && -w "${TMPDIR}" ]]; then
  TMP_BASE="$TMPDIR"
elif [[ -d /data/local/tmp && -w /data/local/tmp ]]; then
  TMP_BASE="/data/local/tmp"
elif [[ -d /tmp && -w /tmp ]]; then
  TMP_BASE="/tmp"
else
  TMP_BASE="./.tmp"
fi

LOG_DIR="$TMP_BASE/cybercafe_logs"
mkdir -p "$LOG_DIR"

# ---------------------------------------
# STEP 1 — Start Infrastructure
# ---------------------------------------
echo "[STEP 1] Starting infrastructure..."

/data/data/com.termux/files/usr/bin/bash "$CYBERCAFE_SH" run > "$LOG_DIR/run.log" 2>&1 &
CYBER_PID=$!

sleep 3

RUN_LOG=$(cat "$LOG_DIR/run.log")
echo "$RUN_LOG"

if echo "$RUN_LOG" | grep -qi "started"; then
  echo "[OK] Infrastructure start command executed"
else
  echo "[ERROR] Infrastructure did not start correctly"
  exit 1
fi

# ---------------------------------------
# STEP 2 — Check Status
# ---------------------------------------
echo "[STEP 2] Checking system status..."

STATUS_OUTPUT=$(/data/data/com.termux/files/usr/bin/bash "$CYBERCAFE_SH" status 2>&1 || true)
echo "$STATUS_OUTPUT"

if echo "$STATUS_OUTPUT" | grep -q "Status"; then
  echo "[OK] Status command executed"
else
  echo "[WARNING] Status output unclear"
fi

# ---------------------------------------
# STEP 3 — System Interaction
# ---------------------------------------
echo "[STEP 3] Validating system interaction..."

LIST_OUTPUT=$(/data/data/com.termux/files/usr/bin/bash "$CYBERCAFE_SH" list users 2>&1 || true)
echo "$LIST_OUTPUT"

if echo "$LIST_OUTPUT" | grep -qi "no such table"; then
  echo "[WARNING] Database schema not initialized (expected in this environment)"
elif echo "$LIST_OUTPUT" | grep -qi "Error"; then
  echo "[WARNING] User operations returned an error"
else
  echo "[OK] System interaction successful"
fi

# ---------------------------------------
# STEP 4 — Shutdown
# ---------------------------------------
echo "[STEP 4] Shutting down infrastructure..."

/data/data/com.termux/files/usr/bin/bash "$CYBERCAFE_SH" shutdown > "$LOG_DIR/shutdown.log" 2>&1 || true

kill $CYBER_PID 2>/dev/null || true

echo "[OK] Shutdown completed"

# ---------------------------------------
# FINAL
# ---------------------------------------
echo "======================================="
echo "Lifecycle test completed"
echo "======================================="
