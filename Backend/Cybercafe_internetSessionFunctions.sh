#Organization: Grey-box
#Project: Cybercafe
#File: globalVariables
#Description: Contains all global variables and configuration values that are needed by other scripts of the backend.

##VARIABLES##
DATABASE_PATH='/data/data/com.termux/files/usr/var/www/database/CyberCafe_Database.db'

##FUNCTIONS##
##Removal Functions##
function clear_internet_sessions
{
	trap 'echo -e "$(date) Error in Cybercafe_internetSessionFunctions.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log
	#Purpose: used to delete all existing internet sessions stored in the database for when the system needs to be shutdown (i.e. shutdown_infra)
	# 1. Get user session data from sqlite database
	# 2. Save data from internet_sessions table into user_data_usage table and format it
	
	I=0 #iterator value
	INDEX_LIMIT=$(($(sqlite3 "${DATABASE_PATH}" "SELECT MAX(table_index) FROM internet_sessions;")+1)) > /dev/null 2>> error.log #the maximum number of internet session entries
	while [ $I -lt $INDEX_LIMIT ]
	do
		remove_session $I
		I=$(($I+1)) #increment iterator
	done
}

function delete_user_iptable_rules #used along with remove_session to remove user access
{
	# Argument1: $1 -> user_ip
	iptables -t mangle -D iptmon_rx -o ${HS_INTERFACE} -d ${1} > /dev/null 2>> error.log # delete rules since this user is effectively logged out
	iptables -t mangle -D iptmon_tx -i ${HS_INTERFACE} -s ${1} > /dev/null 2>> error.log
	iptables -t nat -D PREROUTING -p all -s ${1} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>> error.log #delete access outside of the captive portal using iptables
	iptables -t filter -D FORWARD -p all -s ${USER_IP} -i ${HS_INTERFACE} -j ACCEPT > /dev/null 2>> error.log
	iptables -t filter -D OUTPUT -p all -d ${USER_IP} -o ${HS_INTERFACE} -j ACCEPT > /dev/null 2>> error.log
}


function remove_session #cleanly remove a given session and save all necessary data
{
	trap 'echo -e "$(date) Error in Cybercafe_internetSessionFunctions.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log
	# Argument1: $1 -> internet_session table index (i.e. what internet session are we saving/deleting)
	RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT * FROM internet_sessions WHERE table_index=${1}") > /dev/null 2>> error.log
	if [[ $RESPONSE == '' ]]; then
		return 1 #session doesn't exists
	else
		
		#1. Get all the data
		#Necessary data from internet_session row
		RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT * FROM internet_sessions WHERE table_index=${I}") > /dev/null 2>> error.log
		DATETIME=$(date '+%Y-%m-%d %H:%M:%S') > /dev/null 2>> error.log
		SESSION_TX=$(echo $RESPONSE | cut -f 5 -d '|') > /dev/null 2>> error.log
		SESSION_RX=$(echo $RESPONSE | cut -f 6 -d '|') > /dev/null 2>> error.log
		USER_ID=$(echo $RESPONSE | cut -f 2 -d '|') > /dev/null 2>> error.log
		USER_IP=$(echo $RESPONSE | cut -f 4 -d '|') > /dev/null 2>> error.log
		#
		#Calculate what session number this will be saved under in user_data_usage for this user
		RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT MAX(session_number) FROM user_data_usage WHERE user_id='${USER_ID}'") > /dev/null 2>> error.log
		if [[ $RESPONSE != '' ]]; then
			#**Note: php creates the first entry for any given session**
			SESSION_NUMBER=$(($RESPONSE))
			#Calculate what entry number this will be saved under in user_data_usage for this user on this session
			RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT MAX(session_entry_index) FROM user_data_usage WHERE user_id='${USER_ID}' AND session_number=${SESSION_NUMBER}") > /dev/null 2>> error.log
			ENTRY_INDEX=$(($RESPONSE+1))
			RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=${USER_ID} AND session_number=${SESSION_NUMBER}") > /dev/null 2>> error.log
			INTERVAL_TX=$(($SESSION_TX-$RESPONSE)) #The next interval entry is [total usage for this session - sum of previous entries associated with this user's session]
			RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=${USER_ID} AND session_number=${SESSION_NUMBER}") > /dev/null 2>> error.log
			INTERVAL_RX=$(($SESSION_RX-$RESPONSE)) #same as above
		
			#2. Create entry
			sqlite3 "${DATABASE_PATH}" "INSERT INTO user_data_usage (user_id,session_number,session_entry_index,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${SESSION_NUMBER},${ENTRY_INDEX},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})" > /dev/null 2>> error.log
		fi
		#3. Delete internet session
		sqlite3 "${DATABASE_PATH}" "DELETE FROM internet_sessions WHERE table_index=${1}" > /dev/null 2>> error.log # delete entry from internet_sessions so that the session doesn't exist anymore
		#4. Delete iptable rules
		delete_user_iptable_rules $USER_IP
	fi
}

##Check Internet Sessions Functions##
function check_against_usage_limits
{
	# Argument1: $1 -> index for database table internet_sessions
	USER_ID=$(sqlite3 "${DATABASE_PATH}" "SELECT user_id FROM internet_sessions WHERE table_index=${I}") > /dev/null 2>> error.log
	RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT user_level FROM users WHERE user_id=${USER_ID}") > /dev/null 2>> error.log
	STATUS=$(sqlite3 "${DATABASE_PATH}" "SELECT status FROM users WHERE user_id=${USER_ID}") > /dev/null 2>> error.log
	if [[ $STATUS == 'DISABLED' ]]; then #if the user is disabled then skip all other checks and return 1 (i.e. over limits)
		return 1
	fi
	if [[ $REPONSE == '0' ]]; then #if the user is an admin then skip all other checks and return 0 (i.e. not over limits)
		return 0
	else
		#1. Sum of usage today, this week, this month
		#SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=1 AND entry_datetime>=datetime(datetime(),'localtime','-7 days');
		TOTAL_TX_DAY=$(($(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=${USER_ID} AND entry_datetime>=datetime(datetime(),'localtime','-1 days')"))) > /dev/null 2>> error.log
		TOTAL_RX_DAY=$(($(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=${USER_ID} AND entry_datetime>=datetime(datetime(),'localtime','-1 days')"))) > /dev/null 2>> error.log
		TOTAL_TX_WEEK=$(($(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=${USER_ID} AND entry_datetime>=datetime(datetime(),'localtime','-7 days')"))) > /dev/null 2>> error.log
		TOTAL_RX_WEEK=$(($(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=${USER_ID} AND entry_datetime>=datetime(datetime(),'localtime','-7 days')"))) > /dev/null 2>> error.log
		TOTAL_TX_MONTH=$(($(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=${USER_ID} AND entry_datetime>=datetime(datetime(),'localtime','-30 days')"))) > /dev/null 2>> error.log
		TOTAL_RX_MONTH=$(($(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=${USER_ID} AND entry_datetime>=datetime(datetime(),'localtime','-30 days')"))) > /dev/null 2>> error.log
		if [[ $TOTAL_TX_DAY == '' ]]; then
			TOTAL_TX_DAY=0
		fi
		if [[ $TOTAL_RX_DAY == '' ]]; then
			TOTAL_RX_DAY=0
		fi
		if [[ $TOTAL_TX_WEEK == '' ]]; then
			TOTAL_TX_WEEK=0
		fi
		if [[ $TOTAL_RX_WEEK == '' ]]; then
			TOTAL_RX_WEEK=0
		fi
		if [[ $TOTAL_TX_MONTH == '' ]]; then
			TOTAL_TX_MONTH=0
		fi
		if [[ $TOTAL_RX_MONTH == '' ]]; then
			TOTAL_RX_MONTH=0
		fi
		#2. Check the lane of the user
		LANE_ID=$(sqlite3 "${DATABASE_PATH}" "SELECT lane_id FROM users WHERE user_id=${USER_ID}") > /dev/null 2>> error.log
		if [[ $LANE_ID == '' ]]; then
			LANE_ID=0
		fi
		#3. Do logical comparisions
		LANE_DAILY_LIMIT=$(($(sqlite3 "${DATABASE_PATH}" "SELECT bytelimit_daily FROM data_lanes WHERE lane_id=${LANE_ID}"))) > /dev/null 2>> error.log
		LANE_WEEKLY_LIMIT=$(($(sqlite3 "${DATABASE_PATH}" "SELECT bytelimit_weekly FROM data_lanes WHERE lane_id=${LANE_ID}"))) > /dev/null 2>> error.log
		LANE_MONTHLY_LIMIT=$(($(sqlite3 "${DATABASE_PATH}" "SELECT bytelimit_monthly FROM data_lanes WHERE lane_id=${LANE_ID}"))) > /dev/null 2>> error.log
		if [[ $(($TOTAL_TX_DAY+$TOTAL_RX_DAY)) -gt $LANE_DAILY_LIMIT ]]; then
			return 1
		elif [[ $(($TOTAL_TX_WEEK+$TOTAL_RX_WEEK)) -gt $LANE_WEEKLY_LIMIT ]]; then
			return 1
		elif [[ $(($TOTAL_TX_MONTH+$TOTAL_RX_MONTH)) -gt $LANE_MONTHLY_LIMIT ]]; then
			return 1
		else
			return 0
		fi
		#4. Return boolean value 1 (over limits) 0 (not over limits)
	fi
}

function check_internet_sessions
{
	trap 'echo -e "$(date) Error in Cybercafe_internetSessionFunctions.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log
	# Purpose: main function used to periodically update internet session data in the sqlite database and remove sessions based certain criteria.
	I=0 #iterator value
	INDEX_LIMIT=$(($(sqlite3 "${DATABASE_PATH}" "SELECT MAX(table_index) FROM internet_sessions")+1)) > /dev/null 2>> error.log #the maximum number of internet session entries
	while [ $I -lt $INDEX_LIMIT ]
	do
		# 1. Get user session data from sqlite database if it doesn't exist then skip
		RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT * FROM internet_sessions WHERE table_index=${I}") > /dev/null 2>> error.log
		if [[ $RESPONSE == '' ]]; then
			I=$(($I+1))
			continue
		fi
		
		# 2. If deletion pending bit is 1 then skip to remove_session
		PENDING_DELETION=$(echo $RESPONSE | cut -f 10 -d '|') > /dev/null 2>> error.log
		if [[ $PENDING_DELETION == '1' ]]; then
			remove_session $I
			I=$(($I+1))
			continue
		fi
		
		# 3. Check that a rule exists to record data usage (iptables)
		USER_IP=$(echo $RESPONSE | cut -f 4 -d '|') > /dev/null 2>> error.log
		iptables -t mangle -C iptmon_rx -o ${HS_INTERFACE} -d ${USER_IP} > /dev/null 2>> error.log
		if [[ $? -eq 1 ]]; then
			#rule doesn't exist but should
			iptables -t mangle -A iptmon_rx -o ${HS_INTERFACE} -d ${USER_IP} > /dev/null 2>> error.log
			iptables -t mangle -A iptmon_tx -i ${HS_INTERFACE} -s ${USER_IP} > /dev/null 2>> error.log
		fi
		
		# 4. Update session_tx and rx according to iptables onto 'internet_sessions' database table
		SESSION_ACCESS=$(echo $RESPONSE | cut -f 7 -d '|')
		SESSION_TX=$(($(iptables -t mangle -L iptmon_tx -vxn | grep $USER_IP | awk '{print $2}'))) > /dev/null 2>> error.log
		SESSION_RX=$(($(iptables -t mangle -L iptmon_rx -vxn | grep $USER_IP | awk '{print $2}'))) > /dev/null 2>> error.log
		sqlite3 "${DATABASE_PATH}" "UPDATE internet_sessions SET session_tx=${SESSION_TX},session_rx=${SESSION_RX} WHERE table_index=${I}" > /dev/null 2>> error.log
		
		#5. Send data entry for this check to user_data_usage
		#Necessary data from internet_session row
		RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT * FROM internet_sessions WHERE table_index=${I}") > /dev/null 2>> error.log
		DATETIME=$(date '+%Y-%m-%d %H:%M:%S') > /dev/null 2>> error.log
		SESSION_TX=$(echo $RESPONSE | cut -f 5 -d '|') > /dev/null 2>> error.log
		SESSION_RX=$(echo $RESPONSE | cut -f 6 -d '|') > /dev/null 2>> error.log
		USER_ID=$(echo $RESPONSE | cut -f 2 -d '|') > /dev/null 2>> error.log
		USER_IP=$(echo $RESPONSE | cut -f 4 -d '|') > /dev/null 2>> error.log
		#
		#Calculate what session number this will be saved under in user_data_usage for this user
		RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT MAX(session_number) FROM user_data_usage WHERE user_id='${USER_ID}'") > /dev/null 2>> error.log
		if [[ $RESPONSE != '' ]]; then
			#Note: php creates the first entry for any given session**
			SESSION_NUMBER=$(($RESPONSE))
			#Calculate what entry number this will be saved under in user_data_usage for this user on this session
			RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT MAX(session_entry_index) FROM user_data_usage WHERE user_id='${USER_ID}' AND session_number=${SESSION_NUMBER}") > /dev/null 2>> error.log
			ENTRY_INDEX=$(($RESPONSE+1))
			RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=${USER_ID} AND session_number=${SESSION_NUMBER}") > /dev/null 2>> error.log
			INTERVAL_TX=$(($SESSION_TX-$RESPONSE)) #The next interval entry is [total usage for this session - sum of previous entries associated with this user's session]
			RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=${USER_ID} AND session_number=${SESSION_NUMBER}") > /dev/null 2>> error.log
			INTERVAL_RX=$(($SESSION_RX-$RESPONSE)) #same as above
			#create entry for this check in database
			sqlite3 "${DATABASE_PATH}" "INSERT INTO user_data_usage (user_id,session_number,session_entry_index,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${SESSION_NUMBER},${ENTRY_INDEX},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})" > /dev/null 2>> error.log
		fi
		
		# 6. update 'datetime_sinceLastRequest' based on metrics from user_data_usage table
		#if [[ $ENTRY_INDEX -ne 0 && $USER_ID != '' ]]; then #no need to check on the first entry for a session
		#	RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT MAX(entry_datetime) FROM user_data_usage WHERE user_id=${USER_ID} AND interval_bytes_tx+interval_bytes_rx!=0 AND (SELECT datetime_sinceLastRequest FROM internet_sessions WHERE user_id=${USER_ID})<entry_datetime") > /dev/null 2>> error.log
		#	if [[ $RESPONSE != '' ]]; then
		#		sqlite3 "${DATABASE_PATH}" "UPDATE internet_sessions SET datetime_sinceLastRequest='${RESPONSE}' WHERE table_index=${I}" > /dev/null 2>> error.log
		#	else
		#		# 7. If session age or session idle time becomes to great then save that sessions data and delete the session
		#		#Note: if the previous if statment triggers then we can assume it isn't idle or aged out because it was just updated
		#		RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT timediff((SELECT datetime_created FROM internet_sessions WHERE table_index=${I}),datetime(datetime(),'localtime'))") > /dev/null 2>> error.log
		#		if [[ $RESPONSE != '' ]]; then
		#			SESSION_AGE=86400*$((10#$(echo $RESPONSE | awk '{print $1}' | cut -f 4 -d '-')))+3600*$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 1 -d ':')))+60*$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 2 -d ':')))+$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 3 -d ':' | cut -f 1 -d '.'))) > /dev/null 2>> error.log
		#			RESPONSE=$(sqlite3 "${DATABASE_PATH}" "SELECT timediff((SELECT datetime_sinceLastRequest FROM internet_sessions WHERE table_index=${I}),datetime(datetime(),'localtime'))")
		#			SESSION_IDLETIME=86400*$((10#$(echo $RESPONSE | awk '{print $1}' | cut -f 4 -d '-')))+3600*$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 1 -d ':')))+60*$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 2 -d ':')))+$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 3 -d ':' | cut -f 1 -d '.'))) > /dev/null 2>> error.log
		#			
		#			if [[ ${SESSION_AGE} -gt 43200 ]] || [[ ${SESSION_IDLETIME} -gt 3600 ]]; then #if session is older than 12 hours or has been idle for more than 1hr then delete session
		#				remove_session $I
		#			fi
		#		fi
		#	fi
		#fi
		
		#8. Calculate current usage and determine if given user is outside their limits
		check_against_usage_limits $I
		if [[ $? -eq 1 ]]; then #user is over limits, so do necessary updates
			sqlite3 "${DATABASE_PATH}" "UPDATE internet_sessions SET session_access='0' WHERE table_index=${I}" > /dev/null 2>> error.log
		else
			sqlite3 "${DATABASE_PATH}" "UPDATE internet_sessions SET session_access='1' WHERE table_index=${I}" > /dev/null 2>> error.log
		fi
		
		#9. Check user access entry for user to see if PREROUTING iptable rules need to be updated for this user
		SESSION_ACCESS=$(($(sqlite3 "${DATABASE_PATH}" "SELECT session_access FROM internet_sessions WHERE user_id=${USER_ID}")))
		iptables -t nat -C PREROUTING -s ${USER_IP} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>> error.log
		if [[ $? -eq 0 && $SESSION_ACCESS == '0' ]]; then #rules exists but shouldn't
			iptables -t nat -D PREROUTING -p all -s ${USER_IP} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>> error.log
			iptables -t filter -D FORWARD -p all -s ${USER_IP} -i ${HS_INTERFACE} -j ACCEPT > /dev/null 2>> error.log
			iptables -t filter -D OUTPUT -p all -d ${USER_IP} -o ${HS_INTERFACE} -j ACCEPT > /dev/null 2>> error.log
		fi
		iptables -t nat -C PREROUTING -s ${USER_IP} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>> error.log
		if [[ $? -eq 1 && $SESSION_ACCESS == '1' ]]; then #rule doesn't exist but should
			iptables -t nat -I PREROUTING 1 -p all -s ${USER_IP} -i ${HS_INTERFACE} -j RETURN > /dev/null 2>> error.log
			iptables -t filter -I FORWARD 1 -p all -s ${USER_IP} -i ${HS_INTERFACE} -j ACCEPT > /dev/null 2>> error.log #this rule will allow requests outside of the network for this user
			iptables -t filter -I OUTPUT 1 -p all -d ${USER_IP} -o ${HS_INTERFACE} -j ACCEPT > /dev/null 2>> error.log
		fi
		
		
		I=$(($I+1)) #increment iterator
	done
}