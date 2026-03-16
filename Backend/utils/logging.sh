#!/usr/bin/env sh
# Backend/utils/logging.sh
# Minimal structured logging for CyberCafe

LOG_FILE="/var/log/cybercafe.log"
# fallback to project-local file if /var/log is not writable
if [ ! -w "$(dirname "$LOG_FILE")" ]; then
  LOG_FILE="$(pwd)/Backend/cybercafe.log"
fi

_timestamp() {
  date "+%Y-%m-%d %H:%M:%S"
}

# _log LEVEL FUNCTION MESSAGE
_log() {
  LEVEL="$1"
  FUNCTION_NAME="$2"
  MESSAGE="$3"
  printf "[%s] [%s] %s: %s\n" "$(_timestamp)" "$FUNCTION_NAME" "$LEVEL" "$MESSAGE" | tee -a "$LOG_FILE"
}

log_info()  { _log "INFO"  "$1" "$2"; }
log_warn()  { _log "WARN"  "$1" "$2"; }
log_error() { _log "ERROR" "$1" "$2"; }
