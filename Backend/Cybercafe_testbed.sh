#!/bin/bash
#Organization: Grey-box
#Project: Cybercafe
#File: testbed
#Description: Used to test functions contained in the different Cybercafe libraries

###INCLUDES###
. ./Cybercafe_setupFunctions.sh
. ./Cybercafe_internetSessionFunctions.sh

PS4='Line[$LINENO]: '

display_information()
{
	echo ""
	echo "---Stage ${1}---"
	echo -e "\t---Iptables---"
	iptables -t nat -L PREROUTING -vxn
	iptables -t mangle -L iptmon_tx -vxn
	iptables -t mangle -L iptmon_rx -vxn
	echo -e "\t---sqlite3---"
	echo -e "\t\t---internet_sessions---"
	sqlite3 $DATABASE_PATH -header "SELECT * FROM internet_sessions"
	echo -e "\t\t---user_data_usage---"
	sqlite3 $DATABASE_PATH -header "SELECT * FROM user_data_usage"
	read -p "Press any key to continue..."
}

DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
DATETIME_LASTREQUEST=$(date '+%Y-%m-%d %H:%M:%S')
sqlite3 $DATABASE_PATH "INSERT INTO internet_sessions VALUES (0,1,'a838fjdlkc908sdjfk3jnk2wjnef','192.168.1.45',100,100,1,'${DATETIME}','${DATETIME_LASTREQUEST}')"
sqlite3 $DATABASE_PATH "INSERT INTO internet_sessions VALUES (1,2,'b838fjdlkc908sdjfk3jnk2wjnef','192.168.1.46',100,100,0,'${DATETIME}','${DATETIME_LASTREQUEST}')"
sqlite3 $DATABASE_PATH "INSERT INTO internet_sessions VALUES (2,3,'c838fjdlkc908sdjfk3jnk2wjnef','192.168.1.47',100,100,0,'${DATETIME}','${DATETIME_LASTREQUEST}')"
echo ""
display_information '1'

setup_infrastructure

display_information '2'

check_internet_sessions

display_information '3'

clear_internet_sessions

display_information '4'

shutdown_infrastructure

sqlite3 $DATABASE_PATH "DELETE FROM internet_sessions WHERE 1=1"
sqlite3 $DATABASE_PATH "DELETE FROM user_data_usage WHERE 1=1"