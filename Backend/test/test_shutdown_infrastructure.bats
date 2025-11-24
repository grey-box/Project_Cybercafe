#!/usr/bin/env bats
setup() {
  MOCKBIN="$(pwd)/Backend/test/mocks"
  rm -rf "$MOCKBIN"
  mkdir -p "$MOCKBIN"

  cat > "$MOCKBIN/iptables" <<'EOT'
#!/usr/bin/env bash
echo "[MOCK iptables] $*"
exit 0
EOT
  chmod +x "$MOCKBIN/iptables"

  cat > "$MOCKBIN/tc" <<'EOT'
#!/usr/bin/env bash
echo "[MOCK tc] $*"
exit 0
EOT
  chmod +x "$MOCKBIN/tc"

  cat > "$MOCKBIN/pkill" <<'EOT'
#!/usr/bin/env bash
echo "[MOCK pkill] $*"
exit 0
EOT
  chmod +x "$MOCKBIN/pkill"

  export PATH="$MOCKBIN:$PATH"
  export DRY_RUN=true
  export HS_INTERFACE="${HS_INTERFACE:-wlan0}"
  export STATUS_PATH="$(pwd)/Backend/test_status_file"
  touch "$STATUS_PATH"

  source Backend/Cybercafe_setupFunctions.sh
}

teardown() {
  rm -rf Backend/test/mocks Backend/test_status_file || true
}

@test "shutdown_infrastructure runs and prints starting message (dry-run mocked)" {
  run shutdown_infrastructure
  [ "$status" -eq 0 ]
  echo "$output" | grep -q "Beginning shutdown_infrastructure (dry-run=true)"
}
