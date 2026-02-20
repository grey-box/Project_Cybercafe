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

have_cmd() {
    command -v "$1" >/dev/null 2>&1;
}

require_cmd_or_bin() {
    local cmd="$1" bin="$2"
    if have_cmd "$cmd"; then
        return 0
    fi
    [[ -x "$bin" ]] || die "Missing required command/binary: $cmd ($bin not found)"
}

check_iface() {
    local iface="$1"
    ip link show "$iface" >/dev/null 2>&1 || die "Expected network interface not found: $iface"
    # Warn if the interface is not up, but don't fail since it may be expected to be down
    if ! ip link show "$iface" | grep -q "state UP"; then
        warn "Network interface $iface exists butis not up"
    fi
}

check_iptables_access() {
    # Confirms iptables works and tables are readable (permissions + binary sanity)
    iptables -S >/dev/null 2>&1 || die "iptables filter table not readable (permission or binary issue)"
    iptables -t nat -S >/dev/null 2>&1 || die "iptables nat table not readable (permission or binary issue)"
}

check_tc_access() {
    # Confirms tc works and qdisc can be read (permissions + binary sanity)
    tc qdisc show dev eth0 >/dev/null 2>&1 || die "tc cannot read qdisc for eth0"
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

warn_if_no_hotspot_iface() {
    # We do NOT fail here because baseline may not have hotspot enabled yet.
    # We just warn that hotspot dependent tests are gated.
    if ip link show wlan0 >/dev/null 2>&1 || ip link show ap0 >/dev/null 2>&1; then
        return 0
    fi
    warn "No obvious hotspot/Wi-Fi interface found (wlan0/ap0). Hotspot dependent tests are gated."
}


main() {
    log "=== T95 Preflight Checks ==="
    require_root
    # Required commands for CyberCafe integration readiness
    require_cmd_or_bin ip      /system/bin/ip
    require_cmd_or_bin ss      /system/bin/ss
    require_cmd_or_bin iptables /system/bin/iptables
    require_cmd_or_bin tc      /system/bin/tc
    require_cmd_or_bin sqlite3 /system/bin/sqlite3

    check_iface "eth0"
    check_iptables_access
    check_tc_access
    check_connectivity
    warn_if_no_hotspot_iface

    log "=== Preflight PASSED: T95 appears integration ready ==="
    exit 0
}

main "$@"
