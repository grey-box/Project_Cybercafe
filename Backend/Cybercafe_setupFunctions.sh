#Organization: Grey-box
#Project: Cybercafe
#Description: Contains all the bash functions necessary to setup the Cybercafe architecture. This script is included by the daemon script which actually calls the setup.

##INCLUDES##

##VARIABLES##
LOCAL_IP=''

# The status of the hotspot interface on the device
HS_STATUS='down'
HS_STATUS_PREV='down'

# Interface of the hotspot
HS_INTERFACE='wlan1'

# Base directory of lighttpd and important files
APP_PATH='/data/data/com.termux/files/usr' # application path

function check_hotspot_status
#Checks whether the device's hotspot feature has been turned on or off
{
	HS_STATUS_PREV=$HS_STATUS #save previous status from last check
	ip add show dev $HS_INTERFACE | grep UP &> /dev/null # Does it appear the hotspot is active?
	if [[ $? -eq 0 ]]; then
		HS_STATUS='up'
	else
		HS_STATUS='down'
	fi
}


function setup_infrastructure
#Setup any necessary infrastructure for the Cybercafe system
{
	# We're in hotspot mode but unsure if we're properly configured for CyberCafe operation
	LOCAL_IP=$(ifconfig $HS_INTERFACE | grep 'inet addr' | awk '{print $2}' | cut -d: -f2)

	#check if iptmon_tx table exists
	iptables -t mangle -C FORWARD -j iptmon_tx > /dev/null &> /dev/null
	if [[ $? -ne 0 ]]; then
		iptables -t mangle -N iptmon_tx > /dev/null &> /dev/null

		iptables -t mangle -A FORWARD -j iptmon_tx &> /dev/null
	fi

	## iptmon_rx
	iptables -t mangle -C POSTROUTING -j iptmon_rx &> /dev/null
	if [[ $? -ne 0 ]]; then
		iptables -t mangle -N iptmon_rx &> /dev/null

		iptables -t mangle -A POSTROUTING -j iptmon_rx &> /dev/null
	fi

	# Default iptables redirect

	iptables -t nat -C PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 &> /dev/null
	if [[ $? -ne 0 ]]; then
		iptables -t nat -A PREROUTING -p udp -d ${LOCAL_IP} --dport 53 -j RETURN > /dev/null &> /dev/null
		iptables -t nat -A PREROUTING -p udp -s 0.0.0.0/32 -d 255.255.255.255/32 --dport 67 -j RETURN &> /dev/null
		iptables -t nat -A PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 &> /dev/null
	fi

	# Traffic Control
#	tc qdisc show dev $HS_INTERFACE | grep htb > /dev/null
#	if [[ $? -ne 0 ]]; then
#		tc qdisc add dev $HS_INTERFACE root handle 1: htb default 10
#		tc class add dev $HS_INTERFACE parent 1: classid 1:1 htb rate 15mbps ceil 15mbps
#		tc class add dev $HS_INTERFACE parent 1:1 classid 1:10 htb rate 15mbps ceil 15mbps
#		tc class add dev $HS_INTERFACE parent 1:1 classid 1:20 htb rate 100kbps ceil 100kbps
#	fi

	# Check captive portal HTTPD server
	if ! pgrep lighttpd &> /dev/null; then
        start_captive_webserver
    fi
}


function shutdown_infrastructure
#remove any necessary infrastructure for running the CyberCafe system
{
    pkill lighttpd
	
    LOCAL_IP=''
    HS_STATUS='down'

    iptables -t mangle -D FORWARD -j iptmon_tx &> /dev/null
    iptables -t mangle -D PREROUTING -j iptmon_rx &> /dev/null
    while true; do
        iptables -t mangle -D iptmon_rx 1 &> /dev/null
        if [[ $? == 1 ]]; then
            break
        fi
    done
    iptables -t mangle -X iptmon_rx &> /dev/null

    while true; do
        iptables -t mangle -D iptmon_tx 1 &> /dev/null
        if [[ $? == 1 ]]; then
            break
        fi
    done
    iptables -t mangle -X iptmon_tx &> /dev/null

    while true; do
	iptables -t nat -D PREROUTING 2 &> /dev/null
	if [[ $? == 1 ]]; then
	    break
	fi
    done

#   tc qdisc delete dev $HS_INTERFACE root

}

function start_captive_webserver
#starts the lighttpd webserver that acts as a captive web portal for sign in and such
{
    $APP_PATH/bin/lighttpd -f $APP_PATH/etc/lighttpd/lighttpd.conf &> /dev/null
}