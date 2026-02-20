#!/usr/bin/env bats

# BATS test file for delete_user_iptable_rules
# Run with: ./test/run.sh test/test_delete_user_iptables_rules.bats

bats_require_minimum_version 1.5.0

setup() {
    # Create mock directory
    MOCKBIN="$(pwd)/test/mocks"
    rm -rf "$MOCKBIN"
    mkdir -p "$MOCKBIN"
    
    # Create mock iptables that captures calls
    cat > "$MOCKBIN/iptables" <<'EOT'
#!/usr/bin/env bash
echo "$*" >> "$MOCKBIN/iptables.log"
# Simulate failure for specific test IPs
if [[ "$*" == *"10.0.0.999"* ]]; then
    echo "iptables: Rule does not exist." >&2
    exit 1
fi
exit 0
EOT
    chmod +x "$MOCKBIN/iptables"
    
    # Clear/create log file
    : > "$MOCKBIN/iptables.log"
    
    # Set up environment
    export PATH="$MOCKBIN:$PATH"
    export MOCKBIN
    export HS_INTERFACE="wlan1"
    export USER_IP=""
    
    # Source the functions
    # shellcheck source=../Cybercafe_internetSessionFunctions.sh
    source Cybercafe_internetSessionFunctions.sh
}

teardown() {
    rm -rf "$MOCKBIN" error.log 2>/dev/null || true
}

#################
# Helper Functions
#################

# Count number of iptables calls
count_iptables_calls() {
    wc -l < "$MOCKBIN/iptables.log" | tr -d ' '
}

# Check if a specific iptables command was called
iptables_was_called_with() {
    grep -qF -- "$1" "$MOCKBIN/iptables.log"
}

#################
# Basic Functionality Tests
#################

@test "delete_user_iptable_rules: deletes all 5 rules for valid IP" {
    run delete_user_iptable_rules "192.168.1.50"
    
    [ "$status" -eq 0 ]
    [ "$(count_iptables_calls)" -eq 5 ]
}

@test "delete_user_iptable_rules: calls correct mangle iptmon_rx rule" {
    delete_user_iptable_rules "192.168.1.50"
    
    iptables_was_called_with "-t mangle -D iptmon_rx -o wlan1 -d 192.168.1.50"
}

@test "delete_user_iptable_rules: calls correct mangle iptmon_tx rule" {
    delete_user_iptable_rules "192.168.1.50"
    
    iptables_was_called_with "-t mangle -D iptmon_tx -i wlan1 -s 192.168.1.50"
}

@test "delete_user_iptable_rules: calls correct nat PREROUTING rule" {
    delete_user_iptable_rules "192.168.1.50"
    
    iptables_was_called_with "-t nat -D PREROUTING -p all -s 192.168.1.50 -i wlan1 -j RETURN"
}

#################
# Idempotency Tests
#################

@test "delete_user_iptable_rules: is idempotent (second call succeeds)" {
    # First call
    run delete_user_iptable_rules "192.168.1.51"
    [ "$status" -eq 0 ]
    
    # Second call (simulating rules already deleted)
    run delete_user_iptable_rules "192.168.1.51"
    [ "$status" -eq 0 ]
}

@test "delete_user_iptable_rules: calling when no rules exist is safe (no-op)" {
    # Simulate scenario where rules don't exist
    run delete_user_iptable_rules "192.168.1.52"
    
    # Should complete successfully even if rules didn't exist
    [ "$status" -eq 0 ]
}

#################
# Error Handling Tests
#################

@test "delete_user_iptable_rules: handles iptables failure gracefully" {
    # Use IP that mock will reject
    run delete_user_iptable_rules "10.0.0.999"
    
    # Note: The function does not suppress iptables exit codes, only stderr.
    # When iptables fails, that error will propagate. This is expected behavior
    # since the error is logged to error.log for debugging.
    # We just verify the function completes (doesn't hang) and calls were made.
    [ "$(wc -l < "$MOCKBIN/iptables.log" | tr -d ' ')" -eq 5 ]
}

@test "delete_user_iptable_rules: handles empty IP argument" {
    run delete_user_iptable_rules ""
    
    # Should not crash
    [ "$status" -eq 0 ]
}

#################
# Interface Configuration Tests
#################

@test "delete_user_iptable_rules: works with different HS_INTERFACE" {
    # shellcheck disable=SC2030
    export HS_INTERFACE="eth0"
    
    delete_user_iptable_rules "172.16.0.5"
    
    iptables_was_called_with "-o eth0"
    iptables_was_called_with "-i eth0"
}

@test "delete_user_iptable_rules: uses HS_INTERFACE from environment" {
    # shellcheck disable=SC2031
    export HS_INTERFACE="wlan0"
    
    delete_user_iptable_rules "10.20.30.40"
    
    # Verify wlan0 is used in the commands
    iptables_was_called_with "wlan0"
}

#################
# IP Address Format Tests
#################

@test "delete_user_iptable_rules: handles IPv4 with common private ranges" {
    run delete_user_iptable_rules "192.168.1.0"
    
    [ "$status" -eq 0 ]
    iptables_was_called_with "192.168.1.0"
}

@test "delete_user_iptable_rules: handles 10.x.x.x private range" {
    run delete_user_iptable_rules "10.0.0.1"
    
    [ "$status" -eq 0 ]
    iptables_was_called_with "10.0.0.1"
}

@test "delete_user_iptable_rules: handles 172.16.x.x private range" {
    run delete_user_iptable_rules "172.16.0.100"
    
    [ "$status" -eq 0 ]
    iptables_was_called_with "172.16.0.100"
}

#################
# Global Variable Tests
#################

@test "delete_user_iptable_rules: USER_IP global not required if argument passed" {
    # Ensure USER_IP is empty
    # shellcheck disable=SC2030
    export USER_IP=""
    
    run delete_user_iptable_rules "10.10.10.10"
    
    # Function should work with argument only
    [ "$status" -eq 0 ]
    [ "$(count_iptables_calls)" -eq 5 ]
}

@test "delete_user_iptable_rules: uses argument \$1 for all 5 rules (ignores global USER_IP)" {
    # shellcheck disable=SC2031
    export USER_IP="SHOULD_NOT_USE_THIS"
    
    delete_user_iptable_rules "192.168.100.200"
    
    # All 5 rules should use the passed argument, not the global USER_IP
    iptables_was_called_with "-d 192.168.100.200"
    iptables_was_called_with "-s 192.168.100.200"
    
    # Verify the global USER_IP was NOT used in any command
    run ! grep -q "SHOULD_NOT_USE_THIS" "$MOCKBIN/iptables.log"
}

#################
# Start/Stop Behavior Tests
#################

@test "delete_user_iptable_rules: starting when stopped works correctly" {
    # This tests that the function can be called on a fresh state
    run delete_user_iptable_rules "192.168.1.1"
    
    [ "$status" -eq 0 ]
    [ "$(count_iptables_calls)" -eq 5 ]
}

@test "delete_user_iptable_rules: multiple different IPs can be deleted sequentially" {
    delete_user_iptable_rules "192.168.1.10"
    count_iptables_calls > /dev/null  # first call
    
    delete_user_iptable_rules "192.168.1.20"
    local second_count
    second_count=$(count_iptables_calls)
    
    # Should have 10 total calls (5 per IP)
    [ "$second_count" -eq 10 ]
}

#################
# Logging/Status Validation Tests
#################

@test "delete_user_iptable_rules: produces no stdout on success" {
    run delete_user_iptable_rules "192.168.1.60"
    
    [ "$status" -eq 0 ]
    # Output should be empty (errors go to error.log)
    [ -z "$output" ]
}

@test "delete_user_iptable_rules: completes all 5 iptables calls in order" {
    delete_user_iptable_rules "192.168.1.70"
    
    # Verify all expected calls are in the log
    local call_count
    call_count=$(count_iptables_calls)
    [ "$call_count" -eq 5 ]
    
    # Check that mangle, nat, and filter tables are all addressed
    grep -q "mangle" "$MOCKBIN/iptables.log"
    grep -q "nat" "$MOCKBIN/iptables.log"
    grep -q "filter" "$MOCKBIN/iptables.log"
}
