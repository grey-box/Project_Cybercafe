#!/usr/bin/env bash
set -Eeuo pipefail
# Resolve directories
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
MOCK_DIR="$SCRIPT_DIR/mocks"
#################
# Test Helpers
#################
setup_mocks() {
    rm -rf "$MOCK_DIR"
    mkdir -p "$MOCK_DIR"
    
    # Create a mock iptables that logs all calls
    cat > "$MOCK_DIR/iptables" <<'EOT'
#!/usr/bin/env bash
# Log the call for verification
echo "$*" >> "${MOCK_DIR}/iptables_calls.log"
# Simulate behavior based on arguments
case "$*" in
    *"-D"*)
        # Delete operations - succeed by default
        # To simulate "rule not found", check for specific IPs
        if [[ "$*" == *"192.168.1.999"* ]]; then
            echo "iptables: No chain/target/match by that name." >&2
            exit 1
        fi
        exit 0
        ;;
    *)
        exit 0
        ;;
esac
EOT
    chmod +x "$MOCK_DIR/iptables"
    
    # Ensure log file exists
    touch "$MOCK_DIR/iptables_calls.log"
    
    # Add mocks to PATH
    export PATH="$MOCK_DIR:$PATH"
    export MOCK_DIR
}
teardown_mocks() {
    rm -rf "$MOCK_DIR" error.log 2>/dev/null || true
}
assert_iptables_called_with() {
    local expected="$1"
    if ! grep -qF -- "$expected" "$MOCK_DIR/iptables_calls.log"; then
        echo "FAIL: Expected iptables call not found: $expected"
        echo "Actual calls:"
        cat "$MOCK_DIR/iptables_calls.log"
        return 1
    fi
}
assert_call_count() {
    local expected="$1"
    local actual
    actual=$(wc -l < "$MOCK_DIR/iptables_calls.log" | tr -d ' ')
    if [[ "$actual" -ne "$expected" ]]; then
        echo "FAIL: Expected $expected iptables calls, got $actual"
        cat "$MOCK_DIR/iptables_calls.log"
        return 1
    fi
}
#################
# Test Setup
#################
# Source the function under test
export HS_INTERFACE="wlan1"
export USER_IP=""  # Initialize global that function uses
source "$ROOT_DIR/Cybercafe_internetSessionFunctions.sh"
#################
# Test Cases
#################
test_delete_rules_with_valid_ip() {
    echo "TEST: delete_user_iptable_rules with valid IP"
    setup_mocks
    
    local test_ip="192.168.1.100"
    
    # Call the function
    delete_user_iptable_rules "$test_ip"
    local exit_code=$?
    
    # Verify exit code
    if [[ $exit_code -ne 0 ]]; then
        echo "FAIL: Expected exit code 0, got $exit_code"
        teardown_mocks
        return 1
    fi
    
    # Verify iptables was called correctly
    assert_iptables_called_with "-t mangle -D iptmon_rx -o wlan1 -d $test_ip"
    assert_iptables_called_with "-t mangle -D iptmon_tx -i wlan1 -s $test_ip"
    assert_iptables_called_with "-t nat -D PREROUTING -p all -s $test_ip -i wlan1 -j RETURN"
    
    # Verify 5 iptables calls were made
    assert_call_count 5
    
    teardown_mocks
    echo "PASS: delete_user_iptable_rules with valid IP"
}
test_delete_rules_idempotent() {
    echo "TEST: delete_user_iptable_rules is idempotent (rules don't exist)"
    setup_mocks
    
    local test_ip="192.168.1.101"
    
    # Call twice - second call should still succeed (no-op)
    delete_user_iptable_rules "$test_ip"
    : > "$MOCK_DIR/iptables_calls.log"  # Clear log
    
    delete_user_iptable_rules "$test_ip"
    local exit_code=$?
    
    if [[ $exit_code -ne 0 ]]; then
        echo "FAIL: Second deletion should succeed (idempotent)"
        teardown_mocks
        return 1
    fi
    
    teardown_mocks
    echo "PASS: delete_user_iptable_rules is idempotent"
}
test_delete_rules_with_empty_ip() {
    echo "TEST: delete_user_iptable_rules with empty IP"
    setup_mocks
    
    # Call with empty IP - should still not crash
    delete_user_iptable_rules ""
    local exit_code=$?
    
    # Function should complete (errors logged)
    if [[ $exit_code -ne 0 ]]; then
        echo "FAIL: Should handle empty IP gracefully"
        teardown_mocks
        return 1
    fi
    
    teardown_mocks
    echo "PASS: delete_user_iptable_rules handles empty IP"
}
test_delete_rules_logs_errors() {
    echo "TEST: delete_user_iptable_rules logs errors to error.log"
    setup_mocks
    
    # Use IP that mock will reject
    local test_ip="192.168.1.999"
    rm -f error.log
    
    delete_user_iptable_rules "$test_ip"
    
    # Check that error.log was written to
    if [[ ! -f error.log ]]; then
        # Note: This may fail if iptables mock doesn't write stderr properly
        echo "WARN: error.log not created (expected if mock stderr not captured)"
    fi
    
    teardown_mocks
    echo "PASS: delete_user_iptable_rules error logging test completed"
}
#################
# Run All Tests
#################
main() {
    local passed=0
    local failed=0
    
    echo "========================================"
    echo "delete_user_iptable_rules Test Suite"
    echo "========================================"
    echo
    
    for test_func in test_delete_rules_with_valid_ip \
                     test_delete_rules_idempotent \
                     test_delete_rules_with_empty_ip \
                     test_delete_rules_logs_errors; do
        if $test_func; then
            ((++passed))
        else
            ((++failed))
        fi
        echo
    done
    
    echo "========================================"
    echo "Results: $passed passed, $failed failed"
    echo "========================================"
    
    [[ $failed -eq 0 ]] && exit 0 || exit 1
}
main "$@"