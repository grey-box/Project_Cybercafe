#!/usr/bin/env bash
set -e

source test/helpers/sqlite_fixture.sh

TMP_DB=$(mktemp)

setup() {
    rm -f "$TMP_DB"
    create_test_db "$TMP_DB"
}

teardown() {
    rm -f "$TMP_DB"
}

test_remove_existing_session() {
    setup

    ./remove_session --db "$TMP_DB" --session-id s1

    COUNT=$(count_sessions "$TMP_DB")
    if [ "$COUNT" -ne 1 ]; then
        echo "Expected 1 session remaining"
        exit 1
    fi

    teardown
}

test_remove_nonexistent_session() {
    setup

    ./remove_session --db "$TMP_DB" --session-id does_not_exist

    COUNT=$(count_sessions "$TMP_DB")
    if [ "$COUNT" -ne 2 ]; then
        echo "Expected 2 sessions remaining"
        exit 1
    fi

    teardown
}

test_idempotency() {
    setup

    ./remove_session --db "$TMP_DB" --session-id s1
    ./remove_session --db "$TMP_DB" --session-id s1

    COUNT=$(count_sessions "$TMP_DB")
    if [ "$COUNT" -ne 1 ]; then
        echo "Idempotency failed"
        exit 1
    fi

    teardown
}

test_missing_db() {
    ./remove_session --db missing.db --session-id s1 && exit 1
}

echo "Running remove_session tests..."

test_remove_existing_session
test_remove_nonexistent_session
test_idempotency
test_missing_db

echo "All tests passed."
