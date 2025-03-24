#Organization: Grey-box
#Project: Cybercafe
#File: globalVariables
#Description: Contains all global variables and configuration values that are needed by other scripts of the backend.

##INCLUDES##

##VARIABLES##
DATABASE_PATH='/data/data/com.termux/files/usr/var/www/database/CyberCafe_Database.db'

function clear_internet_sessions
{
	#Purpose: used to delete all existing internet sessions stored in the database for when the system needs to be shutdown (i.e. shutdown_infra)
	# 1. Get user session data from sqlite database
	# 2. Save data from internet_sessions table into user_data_usage table and format it
	
	I=$((0)) #iterator value
	INDEX_LIMIT=$(($(sqlite3 $DATABASE_PATH "SELECT MAX(table_index) FROM internet_sessions;")+1)) #the maximum number of internet session entries
	while [ $I -lt $INDEX_LIMIT ]
	do
		remove_session $I
		I=$(($I+1)) #increment iterator
	done
}

#Desc: used along with remove_session to remove user access
function delete_user_iptable_rules
{
	# Argument1: $1 -> user_ip
	iptables -t mangle -D iptmon_rx -d ${1} &> /dev/null # delete rules since this user is effectively logged out
	iptables -t mangle -D iptmon_tx -s ${1} &> /dev/null
	iptables -t nat -D PREROUTING -p all -s ${1} -i ${HS_INTERFACE} -j RETURN &> /dev/null #delete access outside of the captive portal using iptables
}

#Desc: cleanly remove a given session and save all necessary data
function remove_session
{
	# Argument1: $1 -> internet_session table index (i.e. what internet session are we saving/deleting)
	RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT * FROM internet_sessions WHERE table_index=${1}")
	if [[ $RESPONSE == '' ]]; then
		return 1 #session doesn't exists
	else
		
		#1. Get all the data
		#Necessary data from internet_session row
		DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
		SESSION_TX=$(echo $RESPONSE | cut -f 5 -d '|')
		SESSION_RX=$(echo $RESPONSE | cut -f 6 -d '|')
		USER_ID=$(echo $RESPONSE | cut -f 2 -d '|')
		USER_IP=$(echo $RESPONSE | cut -f 4 -d '|')
		#
		
		#Calculate what session number this will be saved under in user_data_usage for this user
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT MAX(session_number) FROM user_data_usage WHERE user_id='${USER_ID}'")
		if [[ $RESPONSE == '' ]]; then #this implies that this is the very first session for this user
			SESSION_NUMBER=$((0))
			ENTRY_INDEX=$((0))
		else
			SESSION_NUMBER=$(($RESPONSE))
		fi
		#Calculate what entry number this will be saved under in user_data_usage for this user on this session
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT MAX(session_entry_index) FROM user_data_usage WHERE user_id='${USER_ID}' AND session_number=${SESSION_NUMBER}")
		if [[ $RESPONSE == '' ]]; then #this implies that this is the very first entry for this session
			ENTRY_INDEX=$((0))
			INTERVAL_TX=$SESSION_TX
			INTERVAL_RX=$SESSION_RX
		else
			ENTRY_INDEX=$(($RESPONSE+1))
			RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=${USER_ID} AND session_number=${SESSION_NUMBER}")
			INTERVAL_TX=$(($SESSION_TX-$RESPONSE)) #The final data entry is the final interval which is the (total usage - sum of previous entries)
			RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=${USER_ID} AND session_number=${SESSION_NUMBER}")
			INTERVAL_RX=$(($SESSION_RX-$RESPONSE)) #same as above
		fi
		
		#2. Create entry
		sqlite3 $DATABASE_PATH "INSERT INTO user_data_usage (user_id,session_number,session_entry_index,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${SESSION_NUMBER},${ENTRY_INDEX},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})" &> /dev/null
		#3. Delete internet session
		sqlite3 $DATABASE_PATH "DELETE FROM internet_sessions WHERE table_index=${1}" &> /dev/null # delete entry from internet_sessions so that the session doesn't exist anymore
		#4. Delete iptable rules
		delete_user_iptable_rules $USER_IP
	fi
}

function check_against_usage_limits
{
	# Argument1: $1 -> internet_session table index
	USER_ID=$(sqlite3 $DATABASE_PATH "SELECT user_id FROM internet_sessions WHERE table_index=${I}")
	RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT user_level FROM users WHERE user_id=${USER_ID}")
	if [[ $REPONSE == '0' ]]; then #if the user is an admin then skip all other checks and return 1 (true)
		return 1
	else
		#1. Sum of usage today, this week, this month
		TOTAL_TX_DAY=$(($(sqlite3 $DATABASE_PATH "SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=${USER_ID} AND entry_datetime>=(SELECT CURRENT_DATE AS today)")))
		TOTAL_RX_DAY=$(($(sqlite3 $DATABASE_PATH "SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=${USER_ID} AND entry_datetime>=(SELECT CURRENT_DATE AS today)")))
		if [[ $TOTAL_TX_DAY == '' ]]; then
			TOTAL_TX_DAY=$((0))
		fi
		if [[ $TOTAL_RX_DAY == '' ]]; then
			TOTAL_RX_DAY=$((0))
		fi
		#2. Check the lane of the user
		LANE_ID=$(sqlite3 $DATABASE_PATH "SELECT lane_id FROM users WHERE user_id=${USER_ID}")
		if [[ $LANE_ID == '' ]]; then
			LANE_ID=$((0))
		fi
		#SELECT lane_id FROM users WHERE user_id=${USER_ID};
		#3. Do logical comparisions
		LANE_DAILY_LIMIT=$(($(sqlite3 $DATABASE_PATH "SELECT bytelimit_daily FROM data_lanes WHERE lane_id=${LANE_ID}")))
		if [[ $(($TOTAL_TX_DAY+$TOTAL_RX_DAY)) -gt $LANE_DAILY_LIMIT ]]; then
			return 1
		else
			return 0
		fi
		#4. Return boolean value 1 (over limits) 0 (not over limits)
	fi
}

function check_internet_sessions
{
	# Purpose: main function used to periodically update internet session data in the sqlite database and remove sessions based certain criteria.
	I=$((0)) #iterator value
	INDEX_LIMIT=$(($(sqlite3 $DATABASE_PATH "SELECT MAX(table_index) FROM internet_sessions;")+1)) #the maximum number of internet session entries
	while [ $I -lt $INDEX_LIMIT ]
	do
		# 1. Get user session data from sqlite database if it doesn't exist then skip
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT * FROM internet_sessions WHERE table_index=${I}")
		if [[ $RESPONSE == '' ]]; then
			I=$(($I+1))
			continue
		fi
		
		# 2. Check that a rule exists to record data usage (iptables)
		USER_IP=$(echo $RESPONSE | cut -f 4 -d '|')
		iptables -t mangle -C iptmon_rx -d ${USER_IP} &> /dev/null
		if [[ $? -eq 1 ]] && [[ $RESPONSE != '' ]]; then
			#rule doesn't exist but should
			iptables -t mangle -A iptmon_rx -d ${USER_IP} &> /dev/null
			iptables -t mangle -A iptmon_tx -s ${USER_IP} &> /dev/null
		fi
		
		# 3. Update data usage according to ip table onto internet_session table
		SESSION_ACCESS=$(echo $RESPONSE | cut -f 7 -d '|')
		if [[ $SESSION_ACCESS == '1' ]]; then
			SESSION_TX=$(($(iptables -t mangle -L iptmon_tx -vxn | grep $USER_IP | awk '{print $2}')))
			SESSION_RX=$(($(iptables -t mangle -L iptmon_rx -vxn | grep $USER_IP | awk '{print $2}')))
			sqlite3 $DATABASE_PATH "UPDATE internet_sessions SET session_tx=${SESSION_TX},session_rx=${SESSION_RX} WHERE table_index=${I}" &> /dev/null
		fi
		
		# 4. update datetime since last request based on metrics from user_data_usage table
		USER_ID=$(sqlite3 $DATABASE_PATH "SELECT user_id FROM internet_sessions WHERE table_index=${I}")
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT MAX(entry_datetime) FROM user_data_usage WHERE user_id=${USER_ID} AND interval_bytes_tx+interval_bytes_rx!=0")
		if [[ $RESPONSE != '' ]]; then
			sqlite3 $DATABASE_PATH "UPDATE internet_sessions SET datetime_sinceLastRequest='${RESPONSE}' WHERE table_index=${I}" &> /dev/null
		fi
		
		# 5. If session age or session idle time becomes to great then save that sessions data and delete the session
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT timediff((SELECT datetime_created FROM internet_sessions WHERE table_index=${I}),datetime(datetime(),'localtime'))")
		SESSION_AGE=$((86400*$((10#$(echo $RESPONSE | awk '{print $1}' | cut -f 4 -d '-')))+3600*$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 1 -d ':')))+60*$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 2 -d ':')))+$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 3 -d ':' | cut -f 1 -d '.')))))
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT timediff((SELECT datetime_sinceLastRequest FROM internet_sessions WHERE table_index=${I}),datetime(datetime(),'localtime'))")
		SESSION_IDLETIME=$((86400*$((10#$(echo $RESPONSE | awk '{print $1}' | cut -f 4 -d '-')))+3600*$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 1 -d ':')))+60*$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 2 -d ':')))+$((10#$(echo $RESPONSE | awk '{print $2}' | cut -f 3 -d ':' | cut -f 1 -d '.')))))
		
		if [[ ${SESSION_AGE} -gt 43200 ]] || [[ ${SESSION_IDLETIME} -gt 3600 ]]; then #if session is older than 12 hours or has been idle for more than 1hr then delete session
			remove_session $I
			delete_user_iptable_rules $USER_IP
		fi
		
		#6. Send data entry for this check to user_data_usage
		#Necessary data from internet_session row
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT * FROM internet_sessions WHERE table_index=${I}")
		DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
		SESSION_TX=$(echo $RESPONSE | cut -f 5 -d '|')
		SESSION_RX=$(echo $RESPONSE | cut -f 6 -d '|')
		USER_ID=$(echo $RESPONSE | cut -f 2 -d '|')
		USER_IP=$(echo $RESPONSE | cut -f 4 -d '|')
		#
		
		#Calculate what session number this will be saved under in user_data_usage for this user
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT MAX(session_number) FROM user_data_usage WHERE user_id='${USER_ID}'")
		if [[ $RESPONSE == '' ]]; then #this implies that this is the very first session for this user
			SESSION_NUMBER=$((0))
			ENTRY_INDEX=$((0))
		else
			SESSION_NUMBER=$(($RESPONSE))
		fi
		#Calculate what entry number this will be saved under in user_data_usage for this user on this session
		RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT MAX(session_entry_index) FROM user_data_usage WHERE user_id='${USER_ID}' AND session_number=${SESSION_NUMBER}")
		if [[ $RESPONSE == '' ]]; then #this implies that this is the very first entry for this session
			ENTRY_INDEX=$((0))
			INTERVAL_TX=$SESSION_TX
			INTERVAL_RX=$SESSION_RX
		else
			ENTRY_INDEX=$(($RESPONSE+1))
			RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE user_id=${USER_ID} AND session_number=${SESSION_NUMBER}")
			INTERVAL_TX=$(($SESSION_TX-$RESPONSE)) #The next interval entry is [total usage for this session - sum of previous entries associated with this user's session]
			RESPONSE=$(sqlite3 $DATABASE_PATH "SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=${USER_ID} AND session_number=${SESSION_NUMBER}")
			INTERVAL_RX=$(($SESSION_RX-$RESPONSE)) #same as above
		fi
		#create entry for this check in database
		sqlite3 $DATABASE_PATH "INSERT INTO user_data_usage (user_id,session_number,session_entry_index,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${SESSION_NUMBER},${ENTRY_INDEX},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})" &> /dev/null
		
		#7. Calculate current usage and determine if given user is outside their limits
		check_against_usage_limits $I
		if [[ $? -eq 1 ]]; then #user is over limits, so do necessary updates
			sqlite3 $DATABASE_PATH "UPDATE internet_sessions SET session_access='0' WHERE table_index=${I}" &> /dev/null
		else
			sqlite3 $DATABASE_PATH "UPDATE internet_sessions SET session_access='1' WHERE table_index=${I}" &> /dev/null
		fi
		
		#8. Check user access entry for user to see if PREROUTING iptable rules need to be updated for this user
		iptables -t nat -C PREROUTING -s ${USER_IP} -i ${HS_INTERFACE} -j RETURN &> /dev/null
		if [[ $? -eq 0 && $SESSION_ACCESS == '0' ]]; then #rule exists but shouldn't
			iptables -t nat -D PREROUTING -p all -s ${USER_IP} -i ${HS_INTERFACE} -j RETURN &> /dev/null
		fi
		iptables -t nat -C PREROUTING -s ${USER_IP} -i ${HS_INTERFACE} -j RETURN &> /dev/null
		if [[ $? -eq 1 && $SESSION_ACCESS == '1' ]]; then #rule doesn't exist but should
			iptables -t nat -I PREROUTING 2 -p all -s ${USER_IP} -i ${HS_INTERFACE} -j RETURN &> /dev/null
		fi
		
		
		I=$(($I+1)) #increment iterator
	done
}
"$@"