#Greybox - Cybercafe
#Script: UserModify_IptablesRules_AtomicScript.sh
#Desc: Add and remove user rules to the iptables of the device to allow outside access and record data usage

DEFAULT_INSERT_LINE=2
HS_INTERFACE='wlan1'
user_ip=$2

#Argument Validation
if [[ $# != 2 ]]; then
	echo "Invalid number of arguments."
	echo "Usage: ./UserModify_IptablesRules_AtomicScript  [+/-] [user_ip]"
	exit 1
fi

#Adding Rule for Use
if [[ $1 == + ]]; then
	iptables -t nat -C PREROUTING -p tcp -s ${user_ip} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>&1
	if [[ $? -ne 0 ]]; then
		#rules to add
		iptables -t nat -I PREROUTING $DEFAULT_INSERT_LINE -p tcp -s ${user_ip} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>&1
		#record tcp packets incoming to this user
		iptables -t mangle -A iptmon_rx -p tcp -d ${user_ip} -i ${HS_INTERFACE} > /dev/null 2>&1
		#record tcp packets outgoing from this user
		iptables -t mangle -A iptmon_tx -p tcp -s ${user_ip} -i ${HS_INTERFACE} > /dev/null 2>&1
	fi
	exit 0
fi

#Remove Rule for User
if [[ $1 == - ]]; then
	iptables -t nat -C PREROUTING -p tcp -s ${user_ip} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>&1
	if [[ $? -eq 0 ]]; then
		#rules to remove
		iptables -t nat -D PREROUTING -p tcp -s ${user_ip} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>&1
		#record tcp packets incoming to this user
		iptables -t mangle -D iptmon_rx -p tcp -d ${user_ip} -i ${HS_INTERFACE} > /dev/null 2>&1
		#record tcp packets outgoing from this user
		iptables -t mangle -D iptmon_tx -p tcp -s ${user_ip} -i ${HS_INTERFACE} > /dev/null 2>&1
	fi
	exit 0
fi

echo "Invalid Syntax"
echo "Usage: ./UserModify_IptablesRules_AtomicScript  [+/-] [user_ip]"
exit 1
