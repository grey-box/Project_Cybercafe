#!/usr/bin/env sh
# Backend/utils/net_helpers.sh
# Minimal network helper wrappers for CyberCafe

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
. "${SCRIPT_DIR}/logging.sh" 2>/dev/null || true

# create_chain <caller_fn> <CHAIN_SUFFIX>
create_chain() {
  FN="$1"
  CHAIN_SUFFIX="$2"
  FULL="CYBERCAFE_${CHAIN_SUFFIX}"
  # only create if does not exist
  iptables -L "$FULL" -n >/dev/null 2>&1 || {
    iptables -N "$FULL" >/dev/null 2>&1 && log_info "$FN" "Created chain $FULL" || log_warn "$FN" "Failed to create chain $FULL (insufficient privileges?)"
  }
}

# add_rule <caller_fn> "<iptables args string>"
# ARGS should be the arguments after 'iptables', e.g. "-t nat -I PREROUTING 1 -p tcp ..."
add_rule() {
  FN="$1"
  ARGS="$2"
  if [ "${DRY_RUN:-}" = "true" ]; then
    log_info "$FN" "[DRY-RUN] iptables $ARGS"
    return 0
  fi
  eval iptables $ARGS >/dev/null 2>&1
  if [ $? -eq 0 ]; then
    log_info "$FN" "Inserted iptables rule: $ARGS"
    return 0
  else
    log_error "$FN" "Failed to insert iptables rule: $ARGS"
    return 1
  fi
}
