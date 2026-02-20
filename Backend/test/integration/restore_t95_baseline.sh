#!/usr/bin/env bash
set -euo pipefail

# restore_t95_baseline.sh
#
# Safely restore the T95 to a baseline-ish state after CyberCafe testing.
# Only removes CyberCafe-owned artifacts.
# Does NOT flush or modify Android-managed chains (bw_*, fw_*, tetherctrl_*, oem_*).

log()  { printf "%s\n" "$*"; }
warn() { printf "WARNING: %s\n" "$*" >&2; }
die()  { printf "ERROR: %s\n" "$*" >&2; exit 2; }

require_root() {
  [[ "$(id -u)" -eq 0 ]] || die "Must run as root (use: su)"
}

# Ensure Android system binaries are reachable when running under su
export PATH="/system/bin:/system/xbin:$PATH"

delete_rule_exact() {
  local table="$1"
  local rule="$2"
  local del="${rule/-A /-D }"
  iptables -t "$table" $del >/dev/null 2>&1 || true
}

delete_jump_rules_to_chain() {
  local table="$1"
  local chain="$2"

  while IFS= read -r r; do
    [[ -z "$r" ]] && continue
    delete_rule_exact "$table" "$r"
    log "Deleted jump to ${chain}: (table=$table)"
  done < <(
    iptables -t "$table" -S 2>/dev/null \
      | grep -E -- "-j ${chain}(\s|$)" || true
  )
}

flush_delete_chain() {
  local table="$1"
  local chain="$2"

  iptables -t "$table" -F "$chain" >/dev/null 2>&1 || return 0
  iptables -t "$table" -X "$chain" >/dev/null 2>&1 || true
  log "Removed chain: $chain (table=$table)"
}

delete_prefixed_chains() {
  local table="$1"
  local prefix="$2"

  while IFS= read -r ch; do
    [[ -z "$ch" ]] && continue
    delete_jump_rules_to_chain "$table" "$ch"
    flush_delete_chain "$table" "$ch"
  done < <(
    iptables -t "$table" -S 2>/dev/null \
      | awk '$1=="-N"{print $2}' \
      | grep -E "^${prefix}" || true
  )
}

delete_cybercafe_direct_rules() {
  local hs_if="${HS_INTERFACE:-}"
  local local_ip="${LOCAL_IP:-}"

  if [[ -z "$hs_if" ]]; then
    warn "HS_INTERFACE not set; skipping DNAT/DROP rule cleanup"
    return 0
  fi

  # Remove nat PREROUTING DNAT rules to :80 on HS_INTERFACE
  while IFS= read -r r; do
    [[ -z "$r" ]] && continue
    delete_rule_exact nat "$r"
    log "Deleted nat PREROUTING rule on ${hs_if}"
  done < <(
    iptables -t nat -S 2>/dev/null \
      | grep -E '^-A PREROUTING ' \
      | grep -E -- "-i ${hs_if} " \
      | grep -E ':80(\s|$)' || true
  )

  # Remove filter FORWARD DROP rules involving HS_INTERFACE
  while IFS= read -r r; do
    [[ -z "$r" ]] && continue
    delete_rule_exact filter "$r"
    log "Deleted filter FORWARD DROP rule on ${hs_if}"
  done < <(
    iptables -t filter -S 2>/dev/null \
      | grep -E '^-A FORWARD ' \
      | grep -E -- "(-i ${hs_if} |-o ${hs_if} )" \
      | grep -E ' -j DROP(\s|$)' || true
  )
}

restore_tc_best_effort() {
  local hs_if="${HS_INTERFACE:-}"
  [[ -z "$hs_if" ]] && return 0

  if ip link show "$hs_if" >/dev/null 2>&1; then
    tc qdisc del dev "$hs_if" root >/dev/null 2>&1 || true
    log "Deleted tc root qdisc on ${hs_if} (best-effort)"
  fi
}

main() {
  require_root

  log "=== Restoring T95 baseline-ish state ==="
  log "HS_INTERFACE=${HS_INTERFACE:-<unset>} LOCAL_IP=${LOCAL_IP:-<unset>}"

  # Stop captive portal (if running)
  pkill lighttpd >/dev/null 2>&1 || true
  log "Stopped lighttpd (best-effort)"

  # Remove status file if known
  if [[ -n "${STATUS_PATH:-}" && -e "${STATUS_PATH}" ]]; then
    rm -f "${STATUS_PATH}" || true
    log "Removed STATUS_PATH"
  fi

  # Remove mangle accounting chains
  delete_jump_rules_to_chain mangle iptmon_tx
  delete_jump_rules_to_chain mangle iptmon_rx
  flush_delete_chain mangle iptmon_tx
  flush_delete_chain mangle iptmon_rx

  # Remove mirror + user chains
  delete_prefixed_chains filter "CYBERCAFE-MIRROR-"
  delete_prefixed_chains nat    "CYBERCAFE-MIRROR-"
  delete_prefixed_chains mangle "CYBERCAFE-MIRROR-"

  delete_prefixed_chains filter "cybercafe-user-"
  delete_prefixed_chains nat    "cybercafe-user-"
  delete_prefixed_chains mangle "cybercafe-user-"

  # Remove direct rules (DNAT + DROP)
  delete_cybercafe_direct_rules

  # Remove traffic shaping
  restore_tc_best_effort

  log "=== Restore complete ==="
  log "Run preflight again to confirm known-ready state."
}

main "$@"
