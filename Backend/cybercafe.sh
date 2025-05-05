#Organization: Grey-box
#Project: Cybercafe
#File: commandLine
#Description: Acts as the interface between the administrator and the Cybercafe backend.

##INCLUDES##
. ./Cybercafe_setupFunctions.sh
. ./Cybercafe_internetSessionFunctions.sh

##COMMANDS##
function command_run
{
	#test to see if daemon is already running
	ps -eo name,cmdline | grep "bash ./Cybercafe_daemon.sh" | grep -v grep > /dev/null 2>&1 # if this returns 0 it implies that the script exists and is running
	if [[ $? -eq 0 ]]; then
		echo "Cybercafe infrastructure already running."
	else
        printf "%s" "$(date +%T)" \
		&& echo " Running Cybercafe..."
		nohup bash ./Cybercafe_daemon.sh & #run CyberCafe daemon as a seperate process
		#$DAEMON_SCRIPT_PID = $(($(ps -eo pid,name,cmdline | grep "bash ./CyberCafe_Daemon.sh" | grep -v grep | awk '{print $1}')))
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
	ps -eo name,cmdline | grep "bash ./Cybercafe_daemon.sh" | grep -v grep > /dev/null 2>&1 # if this returns 0 it implies that the script exists and is running
	if [[ $? -eq 0 ]]; then
		echo "Status: Running"
		echo ""
		echo "Process Info:"
		ps -o user,pid,ppid,uid,stime,stat,name,cmdline | head -n 1
		ps -eo user,pid,ppid,uid,stime,stat,name,cmdline | grep "bash ./Cybercafe_daemon.sh" | grep -v grep
	else
		echo "Status: Stopped"
	fi
	echo ""
	echo "Hotspot Interface Info:"
	ip add show dev $HS_INTERFACE
	echo ""
}

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
	echo ""
	iptables -t nat -L PREROUTING -vxn
	echo ""
	iptables -t mangle -L FORWARD -vxn
	echo ""
	iptables -t mangle -L POSTROUTING -vxn
	echo ""
	iptables -t mangle -L iptmon_tx -vxn
	echo ""
	iptables -t mangle -L iptmon_rx -vxn
	echo ""
	iptables -t filter -L FORWARD -vxn
	echo ""
	iptables -t filter -L OUTPUT -vxn
	echo ""
}

function command_errorlog
{
	cat error.log | tail -n 30
}

function command_shutdown
{
	ps -eo stat,name,cmdline | grep "bash ./Cybercafe_daemon.sh" | grep -v grep > /dev/null 2>&1 # if this returns 0 it implies that the script exists and is running
	if [[ $? -eq 0 ]]; then
		printf "%s" "$(date +%T)"
		echo ""
		echo "Shutting down"
		touch shutdown.confirmed
		while true; do
			sleep 1
			ps -eo stat,name,cmdline | grep "bash ./Cybercafe_daemon.sh" | grep -v grep > /dev/null 2>&1 # if this returns 1 it implies that the script has stopped
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

function command_exit
{
	loop=false
}

function display_help
{
	echo "Valid commands:"
	echo "run				-		Starts Cybercafe infrastructure."
	echo "status			-		Show status info on Cybercafe infrastructure."
	echo "list (table)		-		List database info such as (sessions,users,lanes,rules)"
	echo "errorlog			-		Prints the most recent errors recorded to errorlog.txt"
	echo "shutdown			-		Sends the signal to system to shutdown cleanly."
	echo "kill				-		Kills all Cybercafe scripts (not recommended)."
	echo "exit				-		Leave this command line."
	echo "help				-		Display this page."
}

##PROGRAM##
loop=true
while $loop; do
	read -p "CyberCafe>> " user_command
	if [[ $user_command == 'run' ]]; then
		command_run
	elif [[ $user_command == 'status' ]]; then
		command_status
	elif [[ $user_command == 'list' ]]; then
		echo "Must include table to list."
	elif [[ $user_command == 'list sessions' ]]; then
		command_list_sessions
	elif [[ $user_command == 'list users' ]]; then
		command_list_users
	elif [[ $user_command == 'list lanes' ]]; then
		command_list_lanes
	elif [[ $user_command == 'list rules' ]]; then
		command_list_rules
	elif [[ $user_command == 'errorlog' ]]; then
		command_errorlog
	elif [[ $user_command == 'shutdown' ]]; then
		command_shutdown
	elif [[ $user_command == 'kill' ]]; then
		command_kill
	elif [[ $user_command == 'exit' ]]; then
		command_exit
	elif [[ $user_command == 'help' ]]; then
		display_help
	else
		echo "Invalid command. Try 'help' for more options."
	fi
done
