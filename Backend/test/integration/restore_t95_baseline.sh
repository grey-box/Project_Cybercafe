#!/usr/bin/env bash
set -euo pipefail

# Restore T95 baseline-ish state by removing ONLY CyberCafe-owned artifacts.
# Safe for Android: does NOT flush built-in chains or touch bw_*, fw_*, tetherctrl_*, oem_*.

log()  { printf "%s\n" "$*"; }
warn() { printf "WARNING: %s\n" "$*" >&2; }
die()  { printf "ERROR: %s\n" "$*" >&2; exit 2; }

require_root() {
    [[ "$(id -u)" -eq 0 ]] || die "Must run as root (use: su)"
}

have_cmd() { command -v "$1" >/dev/null 2>&1; }

# Best-effort delete: converts an exact `iptables -S` "-A ..." rule into "-D ..."
delete_rule_exact() {
    local table="$1" rule="$2"
    local del="${rule/-A /-D }"
    iptables -t "$table" $del >/dev/null 2>&1 || true
}

# Delete any rules in a table that jump to a given chain name
delete_jump_rules_to_chain() {
    local table="$1" chain="$2"
    iptables -t "$table" -S 2>/dev/null | grep -E -- "-j ${chain}(\s|$)" | while read -r r; do
    delete_rule_exact "$table" "$r"
    log "Deleted jump to ${chain}: (table=$table) ${r}"
  done
}

flush_delete_chain() {
    local table="$1" chain="$2"
    iptables -t "$table" -F "$chain" >/dev/null 2>&1 || return 0
    iptables -t "$table" -X "$chain" >/dev/null 2>&1 || true
    log "Removed chain: $chain (table=$table)"
}

# Delete all chains that match a prefix, and remove jump rules to them first.
delete_prefixed_chains() {
    local table="$1" prefix="$2"
    local chains
    chains="$(iptables -t "$table" -S 2>/dev/null | awk '$1=="-N"{print $2}' | grep -E "^${prefix}" || true)"

    [[ -z "$chains" ]] && return 0

    while read -r ch; do
        [[ -z "$ch" ]] && continue
        delete_jump_rules_to_chain "$table" "$ch"
        flush_delete_chain "$table" "$ch"
    done <<< "$chains"
}

# Delete specific CyberCafe rules that are inserted directly into built-in chains.
delete_cybercafe_rules_best_effort() {
    local hs_if="${HS_INTERFACE:-}"
    local local_ip="${LOCAL_IP:-}"

    if [[ -z "$hs_if" ]]; then
        warn "HS_INTERFACE not set. Skipping deletion of interface-specific DNAT/DROP rules."
        warn "If needed, run with: HS_INTERFACE=<iface> (and optionally LOCAL_IP=<ip>)"
        return 0
    fi

  # NAT PREROUTING: only delete CyberCafe redirect rules on HS_INTERFACE to port 80.
  iptables -t nat -S 2>/dev/null \
    | grep -E '^-A PREROUTING ' \
    | grep -E -- "-i ${hs_if} " \
    | grep -E 'DNAT|--to-destination' \
    | grep -E ':80(\s|$)' \
    | while read -r r; do
        delete_rule_exact nat "$r"
        log "Deleted CyberCafe nat PREROUTING DNAT rule: $r"
      done

  # FILTER FORWARD: delete the two CyberCafe DROP rules that block traffic on HS_INTERFACE
  iptables -t filter -S 2>/dev/null \
    | grep -E '^-A FORWARD ' \
    | grep -E -- "(-i ${hs_if} |-o ${hs_if} )" \
    | grep -E ' -j DROP(\s|$)' \
    | while read -r r; do
        # If LOCAL_IP is known, prefer matching the exact "! -s LOCAL_IP" rule;
        # otherwise, still remove DROP rules scoped to HS_INTERFACE (CyberCafe-owned)
        if [[ -n "$local_ip" ]]; then
          if echo "$r" | grep -q -- "! -s ${local_ip}"; then
            delete_rule_exact filter "$r"
            log "Deleted CyberCafe FORWARD DROP (egress) rule: $r"
            continue
          fi
        fi
        # Also remove the ingress drop rule (-i HS_INTERFACE -j DROP)
        if echo "$r" | grep -q -- "-i ${hs_if}"; then
          delete_rule_exact filter "$r"
          log "Deleted CyberCafe FORWARD DROP (ingress) rule: $r"
        fi
      done
}

restore_tc_best_effort() {
    local hs_if="${HS_INTERFACE:-}"
    [[ -z "$hs_if" ]] && { warn "HS_INTERFACE not set; skipping tc qdisc cleanup"; return 0; }

    if ip link show "$hs_if" >/dev/null 2>&1; then
        tc qdisc del dev "$hs_if" root >/dev/null 2>&1 || true
        log "Deleted tc root qdisc on ${hs_if} (best-effort)"
    else
        warn "Interface ${hs_if} not present; skipping tc cleanup"
    fi
}

main() {
    require_root
    have_cmd iptables || die "iptables not found"
    have_cmd tc || warn "tc not found (will skip tc cleanup)"

    # Optional: source config if run from repo root
    if [[ -f "./cybercafe.conf" ]]; then
      # shellcheck disable=SC1091
      . ./cybercafe.conf || true
    fi

    # Best-effort LOCAL_IP fetch if HS_INTERFACE is set and ifconfig exists (matches your setup script style)
    if [[ -n "${HS_INTERFACE:-}" ]] && have_cmd ifconfig; then
        LOCAL_IP="${LOCAL_IP:-$(ifconfig "$HS_INTERFACE" 2>/dev/null | grep 'inet addr' | awk '{print $2}' | cut -d: -f2 || true)}"
    fi

    log "=== Restoring T95 baseline-ish state ==="
    log "HS_INTERFACE=${HS_INTERFACE:-<unset>}  LOCAL_IP=${LOCAL_IP:-<unset>}"

    # 1) Stop captive portal server
    pkill lighttpd >/dev/null 2>&1 || true
    log "Stopped lighttpd (best-effort)"

    # 2) Remove status file if known
    if [[ -n "${STATUS_PATH:-}" ]] && [[ -e "${STATUS_PATH}" ]]; then
        rm -f -- "${STATUS_PATH}" || true
        log "Removed STATUS_PATH: ${STATUS_PATH}"
    fi

    # 3) Remove mangle chains iptmon_tx / iptmon_rx + their references
    delete_jump_rules_to_chain mangle iptmon_tx
    delete_jump_rules_to_chain mangle iptmon_rx
    flush_delete_chain mangle iptmon_tx
    flush_delete_chain mangle iptmon_rx

    # 4) Remove CyberCafe direct rules (DNAT + FORWARD DROP) scoped to HS_INTERFACE
    delete_cybercafe_rules_best_effort

    # 5) Remove mirror + per-user chains (and any jump rules pointing to them)
    delete_prefixed_chains filter "CYBERCAFE-MIRROR-"
    delete_prefixed_chains nat    "CYBERCAFE-MIRROR-"
    delete_prefixed_chains mangle "CYBERCAFE-MIRROR-"

    delete_prefixed_chains filter "cybercafe-user-"
    delete_prefixed_chains nat    "cybercafe-user-"
    delete_prefixed_chains mangle "cybercafe-user-"

    # 6) tc cleanup (only on HS_INTERFACE)
    if have_cmd tc; then
        restore_tc_best_effort
    fi

    log "=== Restore complete ==="
    log "Next: run preflight again to confirm known-ready state."
}

main "$@"
