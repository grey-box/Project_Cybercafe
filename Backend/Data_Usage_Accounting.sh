#!/bin/bash

DB_PATH="/path/to/your/database.db"

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
    # Extract data usage from iptables
    iptables -L -v -x -n -t mangle | grep iptmon_tx | while read -r line; do
        # Parse the line to get the MAC address and bytes used
        bytes=$(echo $line | awk '{print $2}')
        mac=$(echo $line | awk '{print $11}')

        # Update the database
        update_usage $mac $bytes
    done

    iptables -L -v -x -n -t mangle | grep iptmon_rx | while read -r line; do
        bytes=$(echo $line | awk '{print $2}')
        mac=$(echo $line | awk '{print $11}')

        # Update the database
        update_usage $mac $bytes
    done

    # Clear the iptables counters to avoid double-counting
    iptables -Z -t mangle iptmon_tx
    iptables -Z -t mangle iptmon_rx

    # Sleep for half a minute before the next check
    sleep 30
done

