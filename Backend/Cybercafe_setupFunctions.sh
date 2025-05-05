#Organization: Grey-box
#Project: Cybercafe
#File: setupFunctions
#Description: Contains all the bash functions necessary to setup the Cybercafe architecture. This script is included by the daemon script which actually calls the setup.

##VARIABLES##
LOCAL_IP=''
# The status of the hotspot interface on the device
HS_STATUS='down'
HS_STATUS_PREV='down'

##FUNCTIONS##
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
	#trap will catch any errors that occur and write the line number to the error.log file
	trap 'echo -e "$(date) Error in Cybercafe_setupFunction.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log
	#This will grab the ip address of the given hotspot interface so that it can be used for setup
	LOCAL_IP=$(ifconfig $HS_INTERFACE | grep 'inet addr' | awk '{print $2}' | cut -d: -f2) > /dev/null 2>> error.log

	#check if iptmon_tx table exists
	#this table will be used to record all transmitted data by adding rules for each user
	iptables -t mangle -C FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
	if [[ $? -ne 0 ]]; then
		iptables -t mangle -N iptmon_tx > /dev/null 2>> error.log
		#packets that come in on the hotspot interface (-i flag) and are destined for a different host than the hotspot host will be forwarded to iptmon_tx
		iptables -t mangle -A FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
	fi

	#create iptmon_rx
	iptables -t mangle -C POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log
	if [[ $? -ne 0 ]]; then
		iptables -t mangle -N iptmon_rx > /dev/null 2>> error.log
		#if data is coming from the host itself then don't count it towards the rx total
		iptables -t mangle -I iptmon_rx 1 -s ${LOCAL_IP} -j RETURN > /dev/null 2>> error.log
		#
		iptables -t mangle -A POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log
	fi

	#special rules that service the cybercafe system by blocking certain traffic over hotspot interface
	iptables -t nat -C PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log #checks to see that rules don't exist
	if [[ $? -ne 0 ]]; then
		#redirects all tcp traffic to captive webserver port so that 'sign-in' notification is displayed to user when device does http checks
		#also blocks typically web navigation
		#Note: Since the protocol is tcp it won't mess up any important DNS or other services
		iptables -t nat -I PREROUTING 1 -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
		
		#deprecated rules
		##safeguard: allows solicitation to DNS server (note this will show up as rule #2 on PREROUTING if uncommeneted)
		#iptables -t nat -I PREROUTING 1 -p all -i ${HS_INTERFACE} -s 0.0.0.0/32 -d 255.255.255.255/32 --dport 67 -j RETURN > /dev/null 2>> error.log
		##safeguard: allows domain name resolution for things like google.com (note this will show up as rule #1 on PREROUTING if uncommeneted)
		#iptables -t nat -I PREROUTING 1 -p all -i ${HS_INTERFACE} -d ${LOCAL_IP} --dport 53 -j RETURN > /dev/null 2>> error.log
		#
		
		#blocks requests incoming on the hotspot interface that is not destined for the hotspot host (exceptions will be made on a user basis once they sign in)
		iptables -t filter -I FORWARD 1 -p all -i ${HS_INTERFACE} -j DROP > /dev/null 2>> error.log
		#in the uncommon event that traffic going out onto the hotspot interface that isn't from the host (exceptions will be made on a user basis once they sign in)
		iptables -t filter -I FORWARD 1 -p all -o ${HS_INTERFACE} ! -s ${LOCAL_IP} -j DROP > /dev/null 2>> error.log
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
	#kill the captive server so that no additional requests can be pushed through while the system is going down
    pkill lighttpd > /dev/null 2>> error.log
	#Get hotspot ip for dismantling iptable rules
	LOCAL_IP=$(ifconfig $HS_INTERFACE | grep 'inet addr' | awk '{print $2}' | cut -d: -f2) > /dev/null 2>> error.log

	#references to iptmon tables need to be removed first
    iptables -t mangle -D FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log
    iptables -t mangle -D POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log
	#flush system defined tables
	iptables -t mangle -F iptmon_rx > /dev/null 2>> error.log
	iptables -t mangle -X iptmon_rx > /dev/null 2>> error.log
	iptables -t mangle -F iptmon_tx > /dev/null 2>> error.log
	iptables -t mangle -X iptmon_tx > /dev/null 2>> error.log
	
	#check to see if the special redirection rules made by the cybercafe system still exist
	iptables -t nat -C PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
	if [[ $? -eq 0 ]]; then
		#remove special redirection rules that are used to service the cybercafe system
		iptables -t nat -D PREROUTING -p udp -i ${HS_INTERFACE} -d ${LOCAL_IP} --dport 53 -j RETURN > /dev/null 2>> error.log
		iptables -t nat -D PREROUTING -p udp -i ${HS_INTERFACE} -s 0.0.0.0/32 -d 255.255.255.255/32 --dport 67 -j RETURN > /dev/null 2>> error.log
		iptables -t nat -D PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log
		iptables -t filter -D FORWARD -p all -i ${HS_INTERFACE} -j DROP > /dev/null 2>> error.log
		iptables -t filter -D FORWARD -p all -o ${HS_INTERFACE} ! -s ${LOCAL_IP} -j DROP > /dev/null 2>> error.log
	fi
	
	#reset variables
    LOCAL_IP=''
    HS_STATUS='down'
	HS_STATUS_PREV='down'
}

function start_captive_webserver
#starts the lighttpd webserver that acts as a captive web portal for sign in and such
{
    ${LIGHTTPD_PATH} -f lighttpd.conf > /dev/null 2>> error.log
}