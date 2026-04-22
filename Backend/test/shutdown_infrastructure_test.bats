#!/usr/bin/env bats

setup() {
  # Use DRY_RUN so no real system state is touched
  export DRY_RUN=true

  # Minimal required environment
  export HS_INTERFACE="wlan0"
  export STATUS_PATH="./tmp/test_shutdown_status"

  mkdir -p ./tmp
  touch "$STATUS_PATH"

  # Source the code under test
  source "$BATS_TEST_DIRNAME/../Cybercafe_setupFunctions.sh"
}

teardown() {
  rm -rf ./tmp || true
}

@test "shutdown_infrastructure runs cleanly in dry-run mode" {
  run shutdown_infrastructure

  [ "$status" -eq 0 ]
  [[ "$output" == *"Beginning shutdown_infrastructure"* ]]
  [[ "$output" == *"shutdown_infrastructure completed"* ]]
}

@test "shutdown_infrastructure is idempotent (can run twice)" {
  run shutdown_infrastructure
  [ "$status" -eq 0 ]

  run shutdown_infrastructure
  [ "$status" -eq 0 ]
  [[ "$output" == *"shutdown_infrastructure completed"* ]]
}

@test "shutdown_infrastructure does not fail when resources are missing" {
  rm -f "$STATUS_PATH"

  run shutdown_infrastructure

  [ "$status" -eq 0 ]
  [[ "$output" == *"shutdown_infrastructure completed"* ]]
}

@test "shutdown_infrastructure succeeds even if STATUS_PATH directory is missing" {
  rm -rf ./tmp

  run shutdown_infrastructure

  [ "$status" -eq 0 ]
  [[ "$output" == *"shutdown_infrastructure completed"* ]]
}

@test "shutdown_infrastructure prints dry-run indication" {
  run shutdown_infrastructure

  [ "$status" -eq 0 ]
  [[ "$output" == *"dry-run"* ]]
}

@test "shutdown_infrastructure produces some output (not silent)" {
  run shutdown_infrastructure

  [ "$status" -eq 0 ]
  [ -n "$output" ]
}
