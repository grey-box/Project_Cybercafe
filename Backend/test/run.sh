#!/usr/bin/env bash
set -uo pipefail

# Resolve Backend/ and test/ regardless of current working directory
TEST_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd -- "$TEST_DIR/.." && pwd)"

# Normalize PATH so the runner works on:
# - laptops
# - Android emulators with Termux
# - T95 boxes after entering `su`
for d in \
  /data/data/com.termux/files/usr/bin \
  /data/data/com.termux/files/bin \
  /system/bin \
  /system/xbin \
  /usr/bin \
  /bin
do
  [[ -d "$d" ]] && PATH="$d:$PATH"
done
export PATH

# Pick a writable temp base that works in su on Android
if [[ -d /tmp && -w /tmp ]]; then
  TMP_BASE="/tmp"
elif [[ -d /data/local/tmp && -w /data/local/tmp ]]; then
  TMP_BASE="/data/local/tmp"
else
  TMP_BASE="$ROOT_DIR/.tmp"
  mkdir -p "$TMP_BASE"
fi

# Bats uses TMPDIR as the base temp dir
export TMPDIR="$TMP_BASE"
export BATS_TMPDIR="$TMP_BASE"

# Resolve tools after PATH normalization
BASH_BIN="$(command -v bash || true)"
BATS_BIN="$(command -v bats || true)"

if [[ -z "$BASH_BIN" ]]; then
  echo "ERROR: bash not found in PATH=$PATH" >&2
  exit 2
fi

total_tests=0
passed_tests=0
failed_tests=0
declare -a passed_names=()
declare -a failed_names=()

run_one() {
  local t="$1"
  local rc=0

  echo "==> $t"

  case "$t" in
    *.bats)
      if [[ -z "$BATS_BIN" ]]; then
        echo "ERROR: bats not found in PATH=$PATH" >&2
        rc=2
      else
        TMPDIR="$TMPDIR" "$BATS_BIN" "$t" || rc=$?
      fi
      ;;
    *)
      TMPDIR="$TMPDIR" "$BASH_BIN" "$t" || rc=$?
      ;;
  esac

  total_tests=$((total_tests + 1))

  if [[ "$rc" -eq 0 ]]; then
    passed_tests=$((passed_tests + 1))
    passed_names+=("$(basename "$t")")
    echo "PASS: $(basename "$t")"
  else
    failed_tests=$((failed_tests + 1))
    failed_names+=("$(basename "$t")")
    echo "FAIL: $(basename "$t") (exit $rc)"
  fi

  echo
}

print_summary() {
  echo "===================="
  echo "Test File Summary"
  echo "===================="
  echo "Total:  $total_tests"
  echo "Passed: $passed_tests"
  echo "Failed: $failed_tests"
  echo

  echo "Passed files:"
  if [[ "${#passed_names[@]}" -eq 0 ]]; then
    echo "  (none)"
  else
    local name
    for name in "${passed_names[@]}"; do
      echo "  - $name"
    done
  fi
  echo

  echo "Failed files:"
  if [[ "${#failed_names[@]}" -eq 0 ]]; then
    echo "  (none)"
  else
    local name
    for name in "${failed_names[@]}"; do
      echo "  - $name"
    done
  fi
}

main() {
  cd "$ROOT_DIR"

  local found=0
  local t

  if [[ "$#" -gt 0 ]]; then
    for t in "$@"; do
      if [[ -f "$t" ]]; then
        found=1
        run_one "$t"
      elif [[ -f "$TEST_DIR/$t" ]]; then
        found=1
        run_one "$TEST_DIR/$t"
      else
        echo "ERROR: test file not found: $t" >&2
        failed_tests=$((failed_tests + 1))
        total_tests=$((total_tests + 1))
        failed_names+=("$t")
      fi
    done
  else
    for t in "$TEST_DIR"/*_test.bats "$TEST_DIR"/*_test.sh; do
      [[ -e "$t" ]] || continue
      found=1
      run_one "$t"
    done
  fi

  if [[ "$found" -eq 0 ]]; then
    echo "No test files found in $TEST_DIR" >&2
    exit 1
  fi

  print_summary

  if [[ "$failed_tests" -ne 0 ]]; then
    exit 1
  fi
}

main "$@"
