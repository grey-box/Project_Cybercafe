#!/usr/bin/env bats

# ---------------------------------------------------------
# SETUP
# ---------------------------------------------------------
setup() {

  # macOS-safe date replacement
  date() {
    command date "+%Y-%m-%dT%H:%M:%S"
  }
  export -f date

  export HS_INTERFACE="wlan0"
  export LIGHTTPD_CONF="./dummy.conf"
  touch "$LIGHTTPD_CONF"

  export ERROR_LOG="./error.log"
  > "$ERROR_LOG"

  export IPTABLES_LOG="./iptables_calls.log"
  > "$IPTABLES_LOG"

  # -------------------------
  # Mock ifconfig
  # -------------------------
  ifconfig() {
    echo "inet addr:192.168.43.1  Bcast:192.168.43.255  Mask:255.255.255.0"
  }

  # -------------------------
  # Mock iptables
  # -------------------------
  iptables() {
    echo "iptables $*" >> "$IPTABLES_LOG"
    return 0
  }

  export -f ifconfig
  export -f iptables

  # Source real implementation FIRST
  source ../Cybercafe_setupFunctions.sh

  # -------------------------
  # NOW override start_captive_webserver
  # -------------------------
  start_captive_webserver() {
    echo "webserver_started" >> webserver_invoked.log
    return 0
  }

  export -f start_captive_webserver

  rm -f webserver_invoked.log
}

# ---------------------------------------------------------
# TEARDOWN
# ---------------------------------------------------------
teardown() {
  rm -f iptables_calls.log webserver_invoked.log error.log dummy.conf
}

# ---------------------------------------------------------
# TEST 1 — LOCAL_IP extraction
# ---------------------------------------------------------
@test "LOCAL_IP extraction works correctly" {
  setup_infrastructure
  [ "$LOCAL_IP" = "192.168.43.1" ]
}

# ---------------------------------------------------------
# TEST 2 — iptmon_tx chain attempted
# ---------------------------------------------------------
@test "Attempts to create iptmon_tx chain" {
  > "$IPTABLES_LOG"
  setup_infrastructure
  run grep -- "iptmon_tx" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

# ---------------------------------------------------------
# TEST 3 — iptmon_rx chain attempted
# ---------------------------------------------------------
@test "Attempts to create iptmon_rx chain" {
  > "$IPTABLES_LOG"
  setup_infrastructure
  run grep -- "iptmon_rx" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

# ---------------------------------------------------------
# TEST 4 — NAT redirect rule
# ---------------------------------------------------------
@test "Inserts NAT PREROUTING redirect rule" {
  > "$IPTABLES_LOG"
  setup_infrastructure
  run grep -- "PREROUTING" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

# ---------------------------------------------------------
# TEST 5 — FORWARD rules
# ---------------------------------------------------------
@test "Inserts FORWARD DROP rules" {
  > "$IPTABLES_LOG"
  setup_infrastructure
  run grep -- "FORWARD" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

# ---------------------------------------------------------
# TEST 6 — Idempotency
# ---------------------------------------------------------
@test "Function is idempotent (runs twice safely)" {
  run setup_infrastructure
  [ "$status" -eq 0 ]

  run setup_infrastructure
  [ "$status" -eq 0 ]
}

# ---------------------------------------------------------
# TEST 7 — Captive webserver invoked
# ---------------------------------------------------------
@test "Captive webserver is invoked" {

  rm -f webserver_invoked.log

  setup_infrastructure

  run grep -- "webserver_started" webserver_invoked.log
  [ "$status" -eq 0 ]
}
