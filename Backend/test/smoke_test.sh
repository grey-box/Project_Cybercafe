#!/usr/bin/env bash

# Purpose:
# - Fast “sanity” test that Cybercafe_setupFunctions.sh can be sourced and key functions run
# - Runs shutdown_infrastructure in DRY_RUN mode using PATH mocks (FAKEBIN)
# - Verifies expected output + proves mocked system commands were invoked
#
# This test MUST NOT touch real system state (no real iptables/tc changes).
# It is safe to run on a dev machine and in CI.

set -Eeuo pipefail

# Example to demonstrate usage of helpers in a smoke test:
# - env.sh gives us a per-test temp directory + mockable PATH (FAKEBIN) + safe defaults
# - assert.sh gives us simple assertion functions with clear failure messages (like unit tests)
source "$(dirname "$0")/helpers/env.sh"
source "$(dirname "$0")/helpers/assert.sh"

# Source functions first (some scripts reset PATH)
source "$CYBERCAFE_BACKEND_DIR/Cybercafe_setupFunctions.sh"

# Re-apply our FAKEBIN at the fron of PATH in case sourcing reset it
export PATH="$FAKEBIN:$PATH"

# Create lightweight mocks so the test never touches real system state
# The mock commands log what was called and what succeed
cat > "$FAKEBIN/iptables" <<'EOF'
#!/usr/bin/env bash
echo "iptables $*" >> "$MOCK_CALL_LOG"
exit 0
EOF
chmod +x "$FAKEBIN/iptables"

cat > "$FAKEBIN/tc" <<'EOF'
#!/usr/bin/env bash
echo "tc $*" >> "$MOCK_CALL_LOG"
exit 0
EOF
chmod +x "$FAKEBIN/tc"

cat > "$FAKEBIN/ip" <<'EOF'
#!/usr/bin/env bash
echo "ip $*" >> "$MOCK_CALL_LOG"
# Pretend interface exists so step 6 doesn't log "Device does not exist"
exit 0
EOF
chmod +x "$FAKEBIN/ip"

cat > "$FAKEBIN/pkill" <<'EOF'
#!/usr/bin/env bash
echo "pkill $*" >> "$MOCK_CALL_LOG"
exit 0
EOF
chmod +x "$FAKEBIN/pkill"

cat > "$FAKEBIN/ifconfig" <<'EOF'
#!/usr/bin/env bash
echo "ifconfig $*" >> "$MOCK_CALL_LOG"
# Output that matches the grep/awk pipeline in shutdown_infrastructure
echo "inet addr:192.168.1.50  Bcast:192.168.1.255  Mask:255.255.255.0"
exit 0
EOF
chmod +x "$FAKEBIN/ifconfig"

# Safe defaults for this test
export DRY_RUN=true
export HS_INTERFACE=wlan0
export STATUS_PATH="$TEST_TMPDIR/status"
: > "$STATUS_PATH"


test_shutdown() {
  output="$(shutdown_infrastructure 2>&1 || true)"

  # Contract: banner prints
  assert_contains "$output" "Beginning shutdown_infrastructure (dry-run=true)"

  # Contract: in DRY_RUN, commands are printed (not executed)
  assert_contains "$output" "pkill lighttpd"

  # Safety: prove we used mocks for commands that are executed even in DRY_RUN
  # (iptables -L/-S and ifconfig and ip are called outside run_cmd)
  calls="$(cat "$MOCK_CALL_LOG")"
  assert_contains "$calls" "iptables -t mangle -L"
  assert_contains "$calls" "iptables -t nat -S"
  assert_contains "$calls" "ifconfig wlan0"
  assert_contains "$calls" "ip link show wlan0"
}

test_shutdown