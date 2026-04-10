#!/usr/bin/env bash
set -Eeuo pipefail
# -E: The ERR trap is inherited by shell functions.
# -e: Exit immediately if a command exits with a non-zero status.
# -u: Treat unset variables as an error when substituting.
# -o pipefail: the return value of a pipeline is the status of

# Resovle repo root (Backend/) regardless of where this script is called from
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TEST_DIR="$ROOT_DIR/test"

usage() {
    cat << 'EOF'
Usage:
    test/run.sh                    Run all tests
    test/run.sh path/to/test.sh    Run a specific test script
    test/run.sh --filter text      Run tests whose path contains 'text'
EOF
}

FILTER=""
SINGLE_FILE=""

# Minimal argument parsing
while [[ $# -gt 0 ]]; do
    case "$1" in
        -h|--help) usage; exit 0;;
        --filter) FILTER="${2:-}"; shift 2;; # next argument is the filter string
        *) SINGLE_FILE="$1"; shift;;         # treat any other arg as a file path
    esac
done

# Collect tests into an array safely (handles spaces in paths)
declare -a TESTS=()

# If a file is specified, run only that file
if [[ -n "$SINGLE_FILE" ]]; then
    # Allow file path relative to repo root or absolute
    if [[ -f "$SINGLE_FILE" ]]; then
        TESTS+=("$SINGLE_FILE")
    elif [[ -f "$ROOT_DIR/$SINGLE_FILE" ]]; then
        TESTS+=("$ROOT_DIR/$SINGLE_FILE")  
    else
        echo "ERROR: Test file not found: $SINGLE_FILE" >&2
        exit 2
    fi
else
    if [[ ! -d "$TEST_DIR" ]]; then
        echo "ERROR: Test directory not found: $TEST_DIR" >&2
        exit 2
    fi
    # Find tests named *_test.sh under Backend/test/
    # mapfile reads lines into an array without splitting on spaces
    TESTS=()
    TMP_FILE="$(mktemp)"

    {
    find "$TEST_DIR" -type f -name "*_test.sh"
    find "$TEST_DIR" -type f -name "*.bats"
    } | sort > "$TMP_FILE"

    while IFS= read -r file; do
    TESTS+=("$file")
    done < "$TMP_FILE"

    rm -f "$TMP_FILE"
fi

# Optional filter (still safe: operates line-by-line)
if [[ -n "$FILTER" ]]; then
    declare -a FILTERED=()
    for t in "${TESTS[@]}"; do
        if [[ "$t" == *"$FILTER"* ]]; then
            FILTERED+=("$t")
        fi
    done
    TESTS=("${FILTERED[@]}")
fi

if (( ${#TESTS[@]} == 0 )); then
    echo "No tests found."
    exit 0
fi

# run a single test based on file type
run_one_test() {
    local t="$1"
    case "$t" in
        *.bats)
            if command -v bats >/dev/null 2>&1; then
                bats "$t"
            else
                echo "ERROR: Found .bats test but bats is not installed:" >&2
                echo "  ${t#"$ROOT_DIR"/}" >&2
                echo "Install bats or convert this test to *_test.sh" >&2
                return 2
            fi
            ;;
        *)
            bash "$t"
            ;;
    esac
}

pass=0
fail=0
failures=()

echo "Running tests..."
echo "================"

# Run each test script in its own Bash process
# Pass/fail is determined by the script's exit code (0=pass, non-0=fail)
for t in "${TESTS[@]}"; do
    echo "Running test: $t"
    if run_one_test "$t"; then
        echo "PASS: ${t#"$ROOT_DIR"/}"
        pass=$((pass + 1))
    else
        rc=$?
        echo "FAIL: ${t#"$ROOT_DIR"/} (exit=$rc)"
        fail=$((fail + 1))
        failures+=("${t#"$ROOT_DIR"/}")
    fi
    echo "----------------"
done

echo
echo "Summary: $pass passed, $fail failed."

# If anything failed, exit non-zero so CI fails
if (( fail > 0 )); then
    echo "Failed tests:"
    for f in "${failures[@]}"; do
        echo " - $f"
    done
    exit 1
fi