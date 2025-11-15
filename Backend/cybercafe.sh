#Organization: Grey-box
#Project: Cybercafe
#File: Control File
#Description: Acts as the interface between the administrator/developer and the Cybercafe backend.

##VARIABLES##
BASE_PATH="/data/data/com.termux/files/usr/var/www/backend"

##INCLUDES##
. $BASE_PATH/cybercafe.conf
. $BASE_PATH/Cybercafe_setupFunctions.sh
. $BASE_PATH/Cybercafe_internetSessionFunctions.sh

##COMMANDS##
function command_run
{
	#test to see if daemon is already running
	ps -eo name,cmdline | grep "bash ${BASE_PATH}/Cybercafe_daemon.sh" | grep -v grep > /dev/null 2>&1 # if this returns 0 it implies that the script exists and is running
	if [[ $? -eq 0 ]]; then
		echo "Cybercafe infrastructure already running."
	else
        printf "%s" "$(date +%T)" \
		&& echo " Running Cybercafe..."
		nohup bash $BASE_PATH/Cybercafe_daemon.sh & #run CyberCafe daemon as a seperate process
        printf "%s" "$(date +%T)" \
        && echo " Cybercafe started."
		
		#Send warning if hotspot is down
		ip add show dev $HS_INTERFACE | grep UP > /dev/null # Does it appear the hotspot is active?
		if [[ $? -ne 0 ]]; then
			echo "Warning the designated hotspot interface is not currently up."
		fi
	fi
}

function command_status
{	
	echo ""
    printf "%s" "$(date '+%Y-%m-%d %H:%M:%S')"
	echo ""
	ps -eo name,cmdline | grep "bash ${BASE_PATH}/Cybercafe_daemon.sh" | grep -v grep > /dev/null 2>&1 # if this returns 0 it implies that the script exists and is running
	if [[ $? -eq 0 ]]; then
		echo "Status: Running"
		echo ""
		echo "Process Info:"
		ps -o user,pid,ppid,uid,stime,stat,name,cmdline | head -n 1
		ps -eo user,pid,ppid,uid,stime,stat,name,cmdline | grep "bash ${BASE_PATH}/Cybercafe_daemon.sh" | grep -v grep
	else
		echo "Status: Stopped"
	fi
	echo ""
	echo "Hotspot Interface Info:"
	ip add show dev $HS_INTERFACE
	echo ""
}

function command_list
{
	function command_list_sessions
	{
		sqlite3 "${DATABASE_PATH}" -header "SELECT * FROM internet_sessions"
	}
	function command_list_users
	{
		sqlite3 "${DATABASE_PATH}" -header "SELECT * FROM users"
	}
	function command_list_lanes
	{
		sqlite3 "${DATABASE_PATH}" -header "SELECT * FROM data_lanes"
	}
	function command_list_rules
	{
		echo "NAT:"
		iptables -t nat -L PREROUTING -vxn
		echo ""
		echo "MANGLE:"
		iptables -t mangle -L FORWARD -vxn
		echo ""
		iptables -t mangle -L POSTROUTING -vxn
		echo ""
		iptables -t mangle -L iptmon_tx -vxn
		echo ""
		iptables -t mangle -L iptmon_rx -vxn
		echo ""
		echo "FILTER:"
		iptables -t filter -L FORWARD -vxn
		echo ""
	}
	if [[ ${args[1]} == 'sessions' ]]; then
		command_list_sessions
	elif [[ ${args[1]} == 'users' ]]; then
		command_list_users
	elif [[ ${args[1]} == 'lanes' ]]; then
		command_list_lanes
	elif [[ ${args[1]} == 'rules' ]]; then
		command_list_rules
	else
		echo "Invalid list reference use 'help' for more information"
	fi
}
function command_errorlog
{
	cat $BASE_PATH/error.log | tail -n 30
}

function command_shutdown
{
	ps -eo stat,name,cmdline | grep "bash ${BASE_PATH}/Cybercafe_daemon.sh" | grep -v grep > /dev/null 2>&1 # if this returns 0 it implies that the script exists and is running
	if [[ $? -eq 0 ]]; then
		printf "%s" "$(date +%T)"
		echo ""
		echo "Shutting down"
		touch shutdown.confirmed
		while true; do
			sleep 1
			ps -eo stat,name,cmdline | grep "bash ${BASE_PATH}/Cybercafe_daemon.sh" | grep -v grep > /dev/null 2>&1 # if this returns 1 it implies that the script has stopped
			if [[ $? -eq 1 ]]; then
				rm shutdown.confirmed
				printf "%s" "$(date +%T)"
				echo " Shutdown complete"
				break
			fi
		done
	else
		echo "Cybercafe Daemon isn't running"
	fi
}

function command_kill
{
	killall -9 Cybercafe_daemon.sh
	clear_internet_sessions
	shutdown_infrastructure
}

function command_help
{
	echo "Valid commands:"
	echo "run				-		Starts Cybercafe infrastructure."
	echo "status			-		Show status info on Cybercafe infrastructure."
	echo "list (info)		-		List database info such as (sessions,users,lanes,rules)"
	echo "errorlog			-		Prints the most recent errors recorded to errorlog.txt"
	echo "shutdown			-		Sends the signal to system to shutdown cleanly."
	echo "kill				-		Kills all Cybercafe scripts (not recommended)."
	echo "exit				-		Leave this command line."
	echo "help				-		Display this page."
}

##FUNCTIONS##
function welcome_text
{
	echo -e "Grey-box Cybercafe Prototype 2025\n"
}

##PROGRAM##
if [[ $# -gt 0 ]]; then
	trap 'echo "Error: Line ${LINENO}" >> error.log' ERR
	#run non-interactive mode
	args=("$@")
	argc=$(($#))
	if [[ ${args[0]} == 'run' ]]; then
		command_run
	elif [[ ${args[0]} == 'status' ]]; then
		command_status
	elif [[ ${args[0]} == 'list' ]]; then
		command_list
	elif [[ ${args[0]} == 'errorlog' ]]; then
		command_errorlog
	elif [[ ${args[0]} == 'shutdown' ]]; then
		command_shutdown
	elif [[ ${args[0]} == 'kill' ]]; then
		command_kill
	elif [[ ${args[0]} == 'exit' ]]; then
		:
	elif [[ ${args[0]} == 'help' ]]; then
		command_help
	else
		echo "Unrecognized command use 'help' for more information."
	fi
else
	#run interactive mode
	welcome_text
	while true; do
		trap 'echo "Error: Line ${LINENO}" >> error.log' ERR
		echo -n ">> "
		read user_input
		args=()
		for i in $user_input; do
			args+=($i)
		done
		argc=$((${#args[@]}))
		if [[ ${args[0]} == 'run' ]]; then
			command_run
		elif [[ ${args[0]} == 'status' ]]; then
			command_status
		elif [[ ${args[0]} == 'list' ]]; then
			command_list
		elif [[ ${args[0]} == 'errorlog' ]]; then
			command_errorlog
		elif [[ ${args[0]} == 'shutdown' ]]; then
			command_shutdown
		elif [[ ${args[0]} == 'kill' ]]; then
			command_kill
		elif [[ ${args[0]} == 'exit' ]]; then
			break;
		elif [[ ${args[0]} == 'help' ]]; then
			command_help
		else
			echo "Unrecognized command use 'help' for more information."
		fi
	done
fi