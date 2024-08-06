#!/bin/bash

# Use the test database path if provided, otherwise use the default path
DB_PATH="${DB_PATH:-./Database/CyberCafe.db}"

function update_usage {
    local mac_address=$1
    local bytes_used=$2

    sqlite3 $DB_PATH <<EOF
UPDATE session_details
SET bytes_remaining = bytes_remaining - $bytes_used
WHERE mac_address = '$mac_address' AND session_end IS NULL;
EOF
}

while true; do
    # Extract data usage from iptables for both tx and rx
    for chain in iptmon_tx iptmon_rx; do
        # Store the iptables output in a variable
        iptables_output=$(iptables -L -v -x -n -t mangle $chain)
        
        # Process the iptables output line by line
        echo "$iptables_output" | grep "MAC" | while read -r line; do
            bytes=$(echo $line | awk '{print $2}')
            mac=$(echo $line | awk '{print $10}')

            # Update the database
            update_usage $mac $bytes
        done
    done

    # Clear the iptables counters to avoid double-counting
    iptables -Z -t mangle iptmon_tx
    iptables -Z -t mangle iptmon_rx

    # Sleep for half a minute before the next check
    sleep 30
done
