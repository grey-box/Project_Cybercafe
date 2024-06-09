# Tests if the Data_Usage_Accounting.sh script updates the database correctly for a user that exists.
#!/bin/bash

# Create a temporary SQLite database for testing
TEST_DB_PATH="/tmp/test_database.db"
touch /tmp/test_database.db
chmod 666 /tmp/test_database.db

sqlite3 $TEST_DB_PATH <<EOF
CREATE TABLE IF NOT EXISTS session_details (
    mac_address TEXT,
    bytes_remaining INTEGER,
    session_end TEXT
);
INSERT INTO session_details (mac_address, bytes_remaining, session_end) VALUES ('00:11:22:33:44:55', 1000, NULL);
EOF

# Mock iptables output
MOCK_IPTABLES_TX="Chain iptmon_tx (1 references)
    pkts      bytes target     prot opt in     out     source               destination
       0        0            all  --  any    any     0.0.0.0/0            0.0.0.0/0            MAC 00:11:22:33:44:55
       0      500            all  --  any    any     0.0.0.0/0            0.0.0.0/0            MAC 00:11:22:33:44:55"

MOCK_IPTABLES_RX="Chain iptmon_rx (1 references)
    pkts      bytes target     prot opt in     out     source               destination
       0        0            all  --  any    any     0.0.0.0/0            0.0.0.0/0            MAC 00:11:22:33:44:55
       0      300            all  --  any    any     0.0.0.0/0            0.0.0.0/0            MAC 00:11:22:33:44:55"


# Override the iptables command to return the mock output
function iptables {
    if [[ $1 == "-L" && $2 == "-v" && $3 == "-x" && $4 == "-n" && $5 == "-t" && $6 == "mangle" ]]; then
        if [[ $7 == "iptmon_tx" ]]; then
            echo "$MOCK_IPTABLES_TX"
        elif [[ $7 == "iptmon_rx" ]]; then
            echo "$MOCK_IPTABLES_RX"
        fi
    elif [[ $1 == "-Z" && $2 == "-t" && $3 == "mangle" ]]; then
        # Do nothing for zeroing counters
        return
    fi
}

# Export the mock iptables function
export -f iptables

# Run the Data_Usage_Accounting.sh script with the test database in the background
DB_PATH=$TEST_DB_PATH source ./Backend/Data_Usage_Accounting.sh &

# Give the script some time to process 
sleep 5

# Find the process ID of the Data_Usage_Accounting.sh script
PID=$(pgrep -f "./Backend/Data_Usage_Accounting.sh")

# Kill the script if it's running
if [[ -n "$PID" ]]; then
    kill "$PID"
    echo "Debug: Killed Data_Usage_Accounting.sh with PID $PID"
else
    echo "Debug: Data_Usage_Accounting.sh not running"
fi

# Check the database to see if the bytes_remaining was updated correctly
RESULT=$(sqlite3 $TEST_DB_PATH "SELECT bytes_remaining FROM session_details WHERE mac_address = '00:11:22:33:44:55';")


# Expected result is 200 (1000 - 500 - 300)
if [[ $RESULT -eq 200 ]]; then
    echo "Test passed: bytes_remaining is $RESULT"
else
    echo "Test failed: bytes_remaining is $RESULT"
fi

# Clean up
rm $TEST_DB_PATH
