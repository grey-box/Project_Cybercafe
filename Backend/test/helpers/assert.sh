#!/usr/bin/env bash

# Simple assertion helper functions for bash test scripts
# Each assertion prints a clear message and exits non-zero on failure
# Intended to be used with: set -Eeuo pipefail
# Usage: 
# - Source this file in test scripts: source ./assert.sh

# Print a failure message and exit
_assert_fail() {
    echo "ASSERTION FAILED: $*" >&2
    exit 1
}

# Assert that two strings are equal
# Usage: assert_equal "expected" "$actual"
assert_equal() {
    local expected="$1"
    local actual="$2"
    local msg="${3:-Expected '$expected', but got '$actual'}"
    
    [[ "$expected" == "$actual" ]] || _assert_fail "$msg"
}

# Assert two strings are NOT equal
# Usage: assert_not_equal "not_expected" "$actual"
assert_not_equal() {
    local not_expected="$1"
    local actual="$2"
    local msg="${3:-Did not expect '$not_expected', but got it}"
    
    [[ "$not_expected" != "$actual" ]] || _assert_fail "$msg"
}

# Assert a string contains a substring
# Usage: assert_contains "$string" "substring"
assert_contains() {
    local string="$1"
    local substring="$2"
    local msg="${3:-Expected '$string' to contain '$substring'}"
    
    [[ "$string" == *"$substring"* ]] || _assert_fail "$msg"
}

# Assert a command/status succeeded (exit code 0
# Usage: assert_success command "$status"
assert_success() {
    local status="$1"
    local msg="${2:-Expected success (exit code 0), but got $status}"   
    
    [[ "$status" -eq 0 ]] || _assert_fail "$msg"
}

# Assert a command/status failed (non-zero exit code)
# Usage: assert_failure "$status"
assert_failure() {
    local status="$1"
    local msg="${2:-Expected failure (non-zero exit code), but got $status}"    
    
    [[ "$status" -ne 0 ]] || _assert_fail "$msg"
}   

# Assert that a file exists
# Usage: assert_file_exists "filepath"
assert_file_exists() {
    local filepath="$1"
    local msg="${2:-Expected file '$filepath' to exist, but it does not}"    
    
    [[ -f "$filepath" ]] || _assert_fail "$msg"
}   

# Assert that a directory exists
# Usage: assert_dir_exists "dirpath"
assert_dir_exists() {
    local dirpath="$1"
    local msg="${2:-Expected directory '$dirpath' to exist, but it does not}"    
    
    [[ -d "$dirpath" ]] || _assert_fail "$msg"
}   
