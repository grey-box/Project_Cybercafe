#!/usr/bin/env bash
set -Eeuo pipefail

source "$(dirname "$0")/helpers/assert.sh"
source "../Cybercafe_setupFunctions.sh"

test_shutdown_prints_banner() {
  output="$(shutdown_infrastructure 2>&1 || true)"
  assert_contains "$output" "Beginning shutdown_infrastructure"
}

test_shutdown_prints_banner
