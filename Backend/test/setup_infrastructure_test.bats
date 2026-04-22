#!/usr/bin/env bats

# ---------------------------------------------------------
# SETUP
# ---------------------------------------------------------
setup() {

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

  ifconfig() {
    echo "inet addr:192.168.43.1  Bcast:192.168.43.255  Mask:255.255.255.0"
  }

  iptables() {
    echo "iptables $*" >> "$IPTABLES_LOG"
    return 0
  }

  export -f ifconfig
  export -f iptables

  source "${BATS_TEST_DIRNAME}/../Cybercafe_setupFunctions.sh"

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

# =========================================================
# ORIGINAL CORE TESTS
# =========================================================

@test "LOCAL_IP extraction works correctly" {
  setup_infrastructure
  [ "$LOCAL_IP" = "192.168.43.1" ]
}

@test "Attempts to create iptmon_tx chain" {
  setup_infrastructure
  run grep -- "iptmon_tx" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

@test "Attempts to create iptmon_rx chain" {
  setup_infrastructure
  run grep -- "iptmon_rx" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

@test "Inserts NAT PREROUTING redirect rule" {
  setup_infrastructure
  run grep -- "PREROUTING" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

@test "Inserts FORWARD rule reference appears if executed" {
  setup_infrastructure
  run grep -- "FORWARD" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

@test "Function is idempotent (runs twice safely)" {
  run setup_infrastructure
  [ "$status" -eq 0 ]
  run setup_infrastructure
  [ "$status" -eq 0 ]
}

@test "Captive webserver is invoked" {
  setup_infrastructure
  run grep -- "webserver_started" webserver_invoked.log
  [ "$status" -eq 0 ]
}

# =========================================================
# ADDITIONAL SAFE TESTS
# =========================================================

@test "LOCAL_IP variable is not empty" {
  setup_infrastructure
  [ -n "$LOCAL_IP" ]
}

@test "iptables mangle table is referenced" {
  setup_infrastructure
  run grep -- "-t mangle" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

@test "iptables nat table is referenced" {
  setup_infrastructure
  run grep -- "-t nat" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

@test "DNAT rule contains LOCAL_IP" {
  setup_infrastructure
  run grep -- "$LOCAL_IP:80" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

@test "iptmon_tx appears in log before completion" {
  setup_infrastructure
  tx_line=$(grep -n "iptmon_tx" "$IPTABLES_LOG" | head -n1 | cut -d: -f1)
  [ -n "$tx_line" ]
}

@test "iptmon_rx appears in log" {
  setup_infrastructure
  run grep -- "iptmon_rx" "$IPTABLES_LOG"
  [ "$status" -eq 0 ]
}

@test "At least one iptables command executed" {
  setup_infrastructure
  count=$(wc -l < "$IPTABLES_LOG")
  [ "$count" -ge 1 ]
}

@test "Webserver invocation log file exists" {
  setup_infrastructure
  [ -f webserver_invoked.log ]
}