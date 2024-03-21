#!/bin/bash

### GLOBAL VARIABLES ###
# The status of the hotspot functionality (not necessarily the CyberCafe
# infrastructure. Either 'up' or 'down'
HS_STATUS='down'

# This should be set to the hotspot's IP once the hotspot has been enabled. When
# disabled, this variable should be blank again.
LOCAL_IP=''

# Between increments of this value we will not perform a more expensive check of
# the status of the CyberCafe infrastructure. Once passed, we perform the checks
# then reset the counter. In seconds.
REFRESH_TIME=3600

# Path to the file used to indicate the time we last did an expensive check of
# the hotspot/CyberCafe infrastructure status. If the file doesn't exist, it's
# assumed we're not ready to act as a CyberCafe router.
STATUS_PATH="/data/data/com.android.myapplication/files/tmp/cybercafe.confirmed"

# When 'true', we perform the expensive CyberCafe infrastructure check.
TIME_TO_REFRESH=false


### FUNCTIONS ###
function check_hotspot_status {
	# Does it appear the hotspot is active?
	ip add show dev wlan0 | grep 192\.168\.43\. > /dev/null
	wlan_ip_status=$?

	if [[ $wlan_ip_status -eq 0 ]]; then
		HS_STATUS='up'

    elif [[ $wlan_ip_status -ne 0 && $HS_STATUS == 'up' ]]; then
        # Assume hotspot has recently gone done.
        HS_STATUS='down'
        TIME_TO_REFRESH=true
	else
		HS_STATUS='down'
		TIME_TO_REFRESH=false
	fi

    # HS_STATUS is down: TIME_TO_REFRESH is false, HS_STATUS=down
    # HS_STATUS is up, but the STATUS_PATH does not exist: Indicate refresh
    # HS_STATUS is up, and STATUS_PATH exists: Check STATUS_PATH age
        # STATUS_PATH age > REFRESH_TIME: Indicate refresh
        # STATUS_PATH age < REFRESH_TIME: Indicate no refresh


    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
        TIME_TO_REFRESH=true

    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
        # If the CyberCafe status file exists and the hotspot is up...
        # ... calculate how old it is in seconds...
		cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc)

		# ... and if it's older than what we allow (REFRESH_TIME), indicate it's time to refresh
		if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
			TIME_TO_REFRESH=true
		else
			TIME_TO_REFRESH=false
		fi
    fi
}


function setup_infra {
	# We're in hotspot mode but unsure if we're properly configured for CyberCafe operation
	LOCAL_IP=$(ip -4 addr show dev wlan0 | grep inet | awk '{print $2}' | cut -d '/' -f 1)
	# iptmon tables?
	## iptmon_tx
	iptables -t mangle -C FORWARD -j iptmon_tx > /dev/null 2>&1
	if [[ $? -ne 0 ]]; then
		echo "Creating table ipmon_tx"
		iptables -t mangle -N iptmon_tx

		echo "Adding iptmon_tx to FORWARD chain, 'mangle' table"
		iptables -t mangle -A FORWARD -j iptmon_tx
	fi

	## iptmon_rx
	iptables -t mangle -C FORWARD -j iptmon_rx > /dev/null 2>&1
	if [[ $? -ne 0 ]]; then
		echo "Creating table ipmon_rx"
		iptables -t mangle -N iptmon_rx

		echo "Adding iptmon_rx to FORWARD chain, 'mangle' table"
		iptables -t mangle -A FORWARD -j iptmon_rx
	fi

	# Default iptables redirect

	iptables -t nat -C PREROUTING -j DNAT --to-destination ${LOCAL_IP} > /dev/null 2>&1
	if [[ $? -ne 0 ]]; then
		echo "Creating default redirect rule"
		iptables -t nat -A PREROUTING -p udp -d ${LOCAL_IP} --dport 53 -j RETURN
		iptables -t nat -A PREROUTING -p udp -s 0.0.0.0/32 -d 255.255.255.255/32 --dport 67 -j RETURN
		iptables -t nat -A PREROUTING -j DNAT --to-destination ${LOCAL_IP}
	fi

	# Traffic Control
	tc qdisc show dev wlan0 | grep htb > /dev/null
	if [[ $? -ne 0 ]]; then
		echo "Creating tc qdisc HTB queues"
		tc qdisc add dev wlan0 root handle 1: htb default 10
		tc class add dev wlan0 parent 1: classid 1:1 htb rate 15mbps ceil 15mbps
		tc class add dev wlan0 parent 1:1 classid 1:10 htb rate 15mbps ceil 15mbps
		tc class add dev wlan0 parent 1:1 classid 1:20 htb rate 100kbps ceil 100kbps
	fi

	# Update the STATUS_PATH file
	rm -f ${STATUS_PATH} && touch ${STATUS_PATH}

	# Check captive portal HTTPD server
	if ! pgrep lighttpd > /dev/null; then
        start_captive_webserver
    fi
}


function shutdown_infra {
    printf "%s" "$(date +%T)" && echo ": Shutdown, I suppose"
    echo "Stopping captive portal webserver"
    pkill lighttpd

    echo "Putting variables back to default values"
    LOCAL_IP=''
    TIME_TO_REFRESH=false
    HS_STATUS='down'

    echo "Removing status path"
    rm -f $STATUS_PATH

    echo "Cleaning up iptables"
    iptables -t mangle -D FORWARD -j iptmon_tx
    iptables -t mangle -D FORWARD -j iptmon_rx
    while true; do
        iptables -t mangle -D iptmon_rx 1
        if [[ $? == 1 ]]; then
            break
        fi
    done
    iptables -t mangle -X iptmon_rx

    while true; do
        iptables -t mangle -D iptmon_tx 1
        if [[ $? == 1 ]]; then
            break
        fi
    done
    iptables -t mangle -X iptmon_tx

    while true; do
        iptables -t nat -D PREROUTING 2
        if [[ $? == 1 ]]; then
            break
        fi
    done

    echo "Cleaning up wlan0 qdisc"
    tc qdisc delete dev wlan0 root

    echo
    echo "Done."
}


function start_captive_webserver {
    set -o allexport
    source /data/data/com.android.myapplication/files/conf/lighttpd.env
    /data/data/com.android.myapplication/files/bin/lighttpd -f /data/data/com.android.myapplication/files/conf/lighttpd.conf
}

### START OF PROGRAM EXECUTION ###

# I'm undecided if this is a hack or not. The problem was if this script terminated
# without cleaning up we'd have a current $STATUS_PATH which, as currently configured,
# would let the script go from "Hotspot down" to "Everything good."
rm $STATUS_PATH

while true; do
	check_hotspot_status

    if [[ $HS_STATUS == 'up' ]] && ! $TIME_TO_REFRESH; then
        printf "%s" "$(date +%T)" \
        && echo ": Everything is fine, carry on."
    elif [[ $HS_STATUS == 'up' ]] && $TIME_TO_REFRESH; then
        # In hotspot mode, but status file has expired
        printf "%s" "$(date +%T)" \
        && echo ": In hotspot mode, but status file has expired." \
        && echo "Checking CyberCafe infrastructure."
        setup_infra
    elif [[ $HS_STATUS == 'down' ]] && $TIME_TO_REFRESH; then
        # Assume hotspot has recently gone down. Clean up.
        shutdown_infra
    else
        printf "%s" "$(date +%T)" \
        && echo ": Hotspot down. We wait..."
    fi

	sleep 60
done
