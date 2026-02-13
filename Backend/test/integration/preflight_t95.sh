#!/usr/bin/env bash
set -euo pipefail

# T95 Preflight Validater
# Purpose: Verify the T95 is in a known ready state before running system level tests.
# Exit Codes:
#     0 - Ready
#     2 - Not Ready

log() {
    printf "%s\n" "$*";
}
warn() {
    printf "WARNING: %s\n" "$*" >&2; 
}
die() {
    printf "ERROR: %s\n" "$*" >&2; exit 2;
}

require_root() {
    # On Andriod/Termux, EUID may not be set consistently, id -u is unreliable
    local uid
    uid="$(id -u)"
    if [[ "$uid" -ne 0 ]]; then
        echo "ERROR: This script must be run as root."
        echo "Run the following:"
        echo ""
        echo "  su"
        echo "  /data/data/com.termux/files/usr/bin/bash Backend/test/integration/preflight_t95.sh"
        echo ""
        die "Must run as root"
    fi
}

require_cmd() {
    command -v "$1" >/dev/null 2>&1 || die "Missing required command: $1"
}

require_bin() {
    local p="$1"
    [[ -x "$p" ]] || die "Missing required binary: $p"
}

check_iface() {
    local iface="$1"
    ip link show "$iface" >/dev/null 2>&1 || die "Expected network interface not found: $iface"
    # Warn if the interface is not up, but don't fail since it may be expected to be down
    if ! ip link show "$iface" | grep -q "state UP"; then
        warn "Network interface $iface exists butis not up"
    fi
}

check_connectivity() {
    # Outbound internet is useful for installs/updates; tests might not require it.
    # We'll warn if missing rather than fail hard
    if command -v ping >/dev/null 2>&1; then
        if ! ping -c 1 -W 2 8.8.8.8 >/dev/null 2>&1; then
            warn "Outbound connectivity ping to 8.8.8.8 failed"
        fi
    else
        warn "ping not available; skipping outbound connectivity check"
    fi
}

main() {
    log "=== T95 Preflight Checks ==="
    require_root
    # Required commands for CyberCafe integration readiness
    require_cmd ip || require_bin /system/bin/ip
    require_cmd ss
    require_bin /system/bin/iptables
    require_bin /system/bin/tc
    require_bin /system/bin/sqlite3
    
    # Expected primary interface on your T95 baseline
    check_iface "eth0"
    
    # Nonfatal checks
    check_connectivity
    
    log "=== Preflight PASSED: T95 appears integration ready ==="
    exit 0
}

main "$@"
