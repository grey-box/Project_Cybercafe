#!/bin/bash
#Organization: Grey-box
#Project: Cybercafe
#File: setupFunctions
#Description: Contains all the bash functions necessary to setup the Cybercafe architecture. This script is included by the daemon script which actually calls the setup.

##VARIABLES##
LOCAL_IP=''
HS_STATUS='down'
HS_STATUS_PREV='down'

##FUNCTIONS##

function check_hotspot_status {
    HS_STATUS_PREV=$HS_STATUS
    ip add show dev $HS_INTERFACE | grep UP > /dev/null 2>> error.log
    if [[ $? -eq 0 ]]; then
        HS_STATUS='up'
    else
        HS_STATUS='down'
    fi
}

function setup_infrastructure {
    trap 'echo -e "$(date) Error in Cybercafe_setupFunctions.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log

    LOCAL_IP=$(ifconfig $HS_INTERFACE | grep 'inet addr' | awk '{print $2}' | cut -d: -f2) > /dev/null 2>> error.log

    ############################################################
    # iptmon_tx
    ############################################################
    iptables -t mangle -C FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
    if [[ $? -ne 0 ]]; then
        iptables -t mangle -N iptmon_tx > /dev/null 2>> error.log
        iptables -t mangle -A FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
    fi

    ############################################################
    # iptmon_rx
    ############################################################
    iptables -t mangle -C POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log
    if [[ $? -ne 0 ]]; then
        iptables -t mangle -N iptmon_rx > /dev/null 2>> error.log
        iptables -t mangle -I iptmon_rx 1 -s ${LOCAL_IP} -j RETURN > /dev/null 2>> error.log
        iptables -t mangle -A POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log
    fi

    ############################################################
    # NAT redirect rules (captive portal)
    ############################################################
    iptables -t nat -C PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
    if [[ $? -ne 0 ]]; then
        iptables -t nat -I PREROUTING 1 -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log

        iptables -t nat -A PREROUTING -p udp -i ${HS_INTERFACE} -d ${LOCAL_IP} --dport 53 -j RETURN > /dev/null 2>> error.log
        iptables -t nat -A PREROUTING -p udp -i ${HS_INTERFACE} -s 0.0.0.0/32 -d 255.255.255.255/32 --dport 67 -j RETURN > /dev/null 2>> error.log

        iptables -t filter -I FORWARD 1 -p all -i ${HS_INTERFACE} -j DROP > /dev/null 2>> error.log
        iptables -t filter -I FORWARD 1 -p all -o ${HS_INTERFACE} ! -s ${LOCAL_IP} -j DROP > /dev/null 2>> error.log
    fi

    ############################################################
    # TRAFFIC CONTROL (from daemon)
    ############################################################
    tc qdisc show dev ${HS_INTERFACE} | grep htb > /dev/null 2>> error.log
    if [[ $? -ne 0 ]]; then
        tc qdisc add dev ${HS_INTERFACE} root handle 1: htb default 10 > /dev/null 2>> error.log
        tc class add dev ${HS_INTERFACE} parent 1: classid 1:1 htb rate 15mbps ceil 15mbps > /dev/null 2>> error.log
        tc class add dev ${HS_INTERFACE} parent 1:1 classid 1:10 htb rate 15mbps ceil 15mbps > /dev/null 2>> error.log
        tc class add dev ${HS_INTERFACE} parent 1:1 classid 1:20 htb rate 100kbps ceil 100kbps > /dev/null 2>> error.log
    fi

    ############################################################
    # START CAPTIVE PORTAL
    ############################################################
    if ! pgrep lighttpd > /dev/null 2>> error.log; then
        start_captive_webserver
    fi
}


function shutdown_infrastructure {
    trap 'echo -e "$(date) Error in Cybercafe_setupFunctions.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log

    pkill lighttpd > /dev/null 2>> error.log

    LOCAL_IP=$(ifconfig $HS_INTERFACE | grep 'inet addr' | awk '{print $2}' | cut -d: -f2) > /dev/null 2>> error.log

    iptables -t mangle -D FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
    iptables -t mangle -D POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log

    iptables -t mangle -F iptmon_rx > /dev/null 2>> error.log
    iptables -t mangle -X iptmon_rx > /dev/null 2>> error.log
    iptables -t mangle -F iptmon_tx > /dev/null 2>> error.log
    iptables -t mangle -X iptmon_tx > /dev/null 2>> error.log

    iptables -t nat -C PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
    if [[ $? -eq 0 ]]; then
        iptables -t nat -D PREROUTING -p udp -i ${HS_INTERFACE} -d ${LOCAL_IP} --dport 53 -j RETURN > /dev/null 2>> error.log
        iptables -t nat -D PREROUTING -p udp -i ${HS_INTERFACE} -s 0.0.0.0/32 -d 255.255.255.255/32 --dport 67 -j RETURN > /dev/null 2>> error.log
        iptables -t nat -D PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
        iptables -t filter -D FORWARD -p all -i ${HS_INTERFACE} -j DROP > /dev/null 2>> error.log
        iptables -t filter -D FORWARD -p all -o ${HS_INTERFACE} ! -s ${LOCAL_IP} -j DROP > /dev/null 2>> error.log
    fi

    LOCAL_IP=''
    HS_STATUS='down'
    HS_STATUS_PREV='down'
}

function start_captive_webserver {
    ${LIGHTTPD_PATH} -f lighttpd.conf > /dev/null 2>> error.log
}

