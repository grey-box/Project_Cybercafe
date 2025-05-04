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
	ip add show dev $HS_INTERFACE | grep UP > /dev/null 2>> error.log # Does it appear the hotspot is active?
	if [[ $? -eq 0 ]]; then
		HS_STATUS='up'
	else
		HS_STATUS='down'
	fi
}


function setup_infrastructure
#Setup any necessary infrastructure for the Cybercafe system
{
	trap 'echo -e "$(date) Error in Cybercafe_setupFunction.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log
	# We're in hotspot mode but unsure if we're properly configured for CyberCafe operation
	LOCAL_IP=$(ifconfig $HS_INTERFACE | grep 'inet addr' | awk '{print $2}' | cut -d: -f2) > /dev/null 2>> error.log

	#check if iptmon_tx table exists
	iptables -t mangle -C FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
	if [[ $? -ne 0 ]]; then
		iptables -t mangle -N iptmon_tx > /dev/null 2>> error.log

		iptables -t mangle -A FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
	fi

	## iptmon_rx
	iptables -t mangle -C POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log
	if [[ $? -ne 0 ]]; then
		iptables -t mangle -N iptmon_rx > /dev/null 2>> error.log
		iptables -t mangle -I iptmon_rx 1 -s ${LOCAL_IP} -j RETURN > /dev/null 2>> error.log #if data is coming from captive website then don't count it

		iptables -t mangle -A POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log
	fi

	# Default iptables redirect

	iptables -t nat -C PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
	if [[ $? -ne 0 ]]; then
		iptables -t nat -I PREROUTING 1 -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
		iptables -t nat -I PREROUTING 1 -p udp -i ${HS_INTERFACE} -s 0.0.0.0/32 -d 255.255.255.255/32 --dport 67 -j RETURN > /dev/null 2>> error.log
		iptables -t nat -I PREROUTING 1 -p udp -i ${HS_INTERFACE} -d ${LOCAL_IP} --dport 53 -j RETURN > /dev/null 2>> error.log
		iptables -t filter -I FORWARD 1 -p all -i ${HS_INTERFACE} -j DROP > /dev/null 2>> error.log #block requests that whos destination is outside the (host) network
		iptables -t filter -I OUTPUT 1 -p all -o ${HS_INTERFACE} ! -s ${LOCAL_IP} -j DROP > /dev/null 2>> error.log  #block traffic that is not from host from being directed on hotspot interface
		#iptables -t filter -I FORWARD 1 -p all -i ${HS_INTERFACE} -d ${LOCAL_IP} -j ACCEPT > /dev/null 2>> error.log
		#iptables -t filter -I FORWARD 1 -p all -i ${HS_INTERFACE} -s ${LOCAL_IP} -j ACCEPT > /dev/null 2>> error.log
	fi

	# Check captive portal HTTPD server
	if ! pgrep lighttpd > /dev/null 2>> error.log; then
        start_captive_webserver
    fi
}


function shutdown_infrastructure
#remove any necessary infrastructure for running the CyberCafe system
{
	trap 'echo -e "$(date) Error in Cybercafe_setupFunction.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log
    pkill lighttpd > /dev/null 2>> error.log
	
	LOCAL_IP=$(ifconfig $HS_INTERFACE | grep 'inet addr' | awk '{print $2}' | cut -d: -f2) > /dev/null 2>> error.log

    iptables -t mangle -D FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
    iptables -t mangle -D POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log
    while true; do
        iptables -t mangle -D iptmon_rx 1 > /dev/null 2>> error.log
        if [[ $? == 1 ]]; then
            break
        fi
    done
    iptables -t mangle -X iptmon_rx > /dev/null 2>> error.log

    while true; do
        iptables -t mangle -D iptmon_tx 1 > /dev/null 2>> error.log
        if [[ $? == 1 ]]; then
            break
        fi
    done
    iptables -t mangle -X iptmon_tx > /dev/null 2>> error.log
	
	iptables -t nat -C PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
	if [[ $? -eq 0 ]]; then
		iptables -t nat -D PREROUTING -p udp -i ${HS_INTERFACE} -d ${LOCAL_IP} --dport 53 -j RETURN > /dev/null 2>> error.log
		iptables -t nat -D PREROUTING -p udp -i ${HS_INTERFACE} -s 0.0.0.0/32 -d 255.255.255.255/32 --dport 67 -j RETURN > /dev/null 2>> error.log
		iptables -t nat -D PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
		iptables -t filter -D FORWARD -p all -i ${HS_INTERFACE} -j DROP > /dev/null 2>> error.log
		iptables -t filter -D OUTPUT -p all -o ${HS_INTERFACE} ! -s ${LOCAL_IP} -j DROP > /dev/null 2>> error.log
		#iptables -t filter -D FORWARD -p all -i ${HS_INTERFACE} -d ${LOCAL_IP} -j ACCEPT > /dev/null 2>> error.log
		#iptables -t filter -D FORWARD -p all -i ${HS_INTERFACE} -s ${LOCAL_IP} -j ACCEPT > /dev/null 2>> error.log
	fi
	
    LOCAL_IP=''
    HS_STATUS='down'
}

function start_captive_webserver
#starts the lighttpd webserver that acts as a captive web portal for sign in and such
{
    lighttpd -f lighttpd.conf > /dev/null 2>> error.log
}