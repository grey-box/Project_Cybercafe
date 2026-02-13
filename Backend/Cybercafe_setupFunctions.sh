#Organization: Grey-box
#Project: Cybercafe
#File: setupFunctions
#Description: Contains all the bash functions necessary to setup the Cybercafe architecture. This script is included by the daemon script which actually calls the setup.

##VARIABLES##
LOCAL_IP=''
# The status of the hotspot interface on the device
HS_STATUS='down'
HS_STATUS_PREV='down'

# Between increments of this value we will not perform a more expensive check of
# the status of the CyberCafe infrastructure. Once passed, we perform the checks
# then reset the counter. In seconds.
REFRESH_TIME=3600

# Path to the file used to indicate the time we last did an expensive check of
# the hotspot/CyberCafe infrastructure status. If the file doesn't exist, it's
# assumed we're not ready to act as a CyberCafe router.
# Android Test
# STATUS_PATH="/data/data/com.android.myapplication/files/tmp/cybercafe.confirmed"

# Windows Test
STATUS_PATH="./tmp/cybercafe.confirmed"

# When 'true', we perform the expensive CyberCafe infrastructure check.
TIME_TO_REFRESH=false

##FUNCTIONS##
function check_hotspot_status
#Checks whether the device's hotspot feature has been turned on or off
{
	# Save previous status from last check so we capture previous state for Daemon to know change (From John's)
	HS_STATUS_PREV=$HS_STATUS 

	# From Chris's with better probing method to grab a specific IP pattern for the hotspot
	ip add show dev wlan0 | grep 192\.168\.43\. > /dev/null 2>> error.log # Does it appear the hotspot is active?
	wlan_ip_status=$?

	# Set current status of the infrastructrure 
	if [[ $wlan_ip_status -eq 0 ]]; then
		HS_STATUS='up'

	#To detect any drift/small crashes or glitches when hotspot goes down, but our state var. is still 'up'.
    elif [[ $wlan_ip_status -ne 0 && $HS_STATUS == 'up' ]]; then
        # Assume hotspot has recently gone done.
        HS_STATUS='down'
        TIME_TO_REFRESH=true
	else
		HS_STATUS='down'
		TIME_TO_REFRESH=false
	fi

	# Timestamp Guard (From Chris's) to indicate refreshing if the file is not found or stale
	# Stale: If the File's age > REFRESH_TIME, then set TIME_TO_REFRESH=true

	# HS_STATUS is down: TIME_TO_REFRESH is false, HS_STATUS=down
    # HS_STATUS is up, but the STATUS_PATH does not exist: Indicate refresh
    # HS_STATUS is up, and STATUS_PATH exists: Check STATUS_PATH age
        # STATUS_PATH age > REFRESH_TIME: Indicate refresh
        # STATUS_PATH age < REFRESH_TIME: Indicate no refresh


    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
        TIME_TO_REFRESH=true

    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
        # If the CyberCafe status file exists and the hotspot is up
        # calculate how old it is (in s)
		cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc)

		# if it's older than REFRESH_TIME, indicate time to refresh
		if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
			TIME_TO_REFRESH=true
		else
			TIME_TO_REFRESH=false
		fi
	else 
		# Hotspot is down, no need for refresh to save resources
		TIME_TO_REFRESH=false
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

	# Ensure captive portal HTTPD server is running
	if ! start_captive_webserver; then
		echo "$(date -Is) Error in Cybercafe_setupFunction.sh: Line ${LINENO} - Failed to start captive webserver" >> error.log
	fi
}


function shutdown_infrastructure
#remove any necessary infrastructure for running the CyberCafe system
{
    # preserve existing trap / error logging
	trap 'echo -e "$(date) Error in Cybercafe_setupFunction.sh: Line ${LINENO}\n" >> error.log' ERR > /dev/null 2>> error.log

    # dry-run support (export DRY_RUN=true to simulate)
    DRY_RUN=${DRY_RUN:-false}
    run_cmd() {
      if [[ "${DRY_RUN}" == "true" ]]; then
        echo "[DRY-RUN] $*"
      else
        # execute and append stderr to error.log so we keep original behavior
        eval "$@" 2>> error.log || true
      fi
    }

    log() { echo "[shutdown_infrastructure] $*"; }
    warn() { echo "[shutdown_infrastructure][WARN] $*" >&2; }

	#Get hotspot ip for dismantling iptable rules (best-effort)
	LOCAL_IP=$(ifconfig $HS_INTERFACE | grep 'inet addr' | awk '{print $2}' | cut -d: -f2) > /dev/null 2>> error.log || LOCAL_IP=''

    log "Beginning shutdown_infrastructure (dry-run=${DRY_RUN})"

	# 1) Stop captive server (safe)
    log "Stopping captive portal webserver (pkill lighttpd)"
    run_cmd "pkill lighttpd > /dev/null || true"

	# 2) Remove status file if present (if config defines STATUS_PATH)
    : "${STATUS_PATH:=${STATUS_PATH:-}}"
    if [[ -n "${STATUS_PATH}" ]] && [[ -e "${STATUS_PATH}" ]]; then
      log "Removing status path: ${STATUS_PATH}"
      run_cmd "rm -f -- '${STATUS_PATH}' || true"
    else
      log "No STATUS_PATH present or file missing; skipping"
    fi

	# 3) Remove mangle references (Chris: iptmon_tx, iptmon_rx)
    if command -v iptables >/dev/null 2>&1; then
      log "Attempting to remove mangle table references to iptmon_tx / iptmon_rx"

      # remove FORWARD -j iptmon_tx (if present)
      run_cmd "iptables -t mangle -D FORWARD -i ${HS_INTERFACE} -j iptmon_tx > /dev/null 2>> error.log || true"
      run_cmd "iptables -t mangle -D POSTROUTING -o ${HS_INTERFACE} -j iptmon_rx > /dev/null 2>> error.log || true"

      # flush & delete chains safely (will ignore if not present)
      for c in iptmon_rx iptmon_tx; do
        # flush chain if exists
        if iptables -t mangle -L "${c}" > /dev/null 2>> error.log; then
          log "Flushing and deleting chain: ${c}"
          run_cmd "iptables -t mangle -F ${c} > /dev/null 2>> error.log || true"
          run_cmd "iptables -t mangle -X ${c} > /dev/null 2>> error.log || true"
        else
          log "Chain ${c} not present; skipping"
        fi
      done
    else
      warn "iptables not found; skipping mangle cleanup"
    fi

	# 4) Remove NAT PREROUTING redirect rules (Chris)
    if command -v iptables >/dev/null 2>&1; then
      log "Cleaning NAT PREROUTING redirect rules (best-effort)"
      # Attempt to find PREROUTING DNAT entries and delete them
      # We parse iptables-save style output and convert -A to -D for deletion
      iptables -t nat -S 2>> error.log | grep -i "PREROUTING" | grep -E "DNAT|--to-destination" 2>> error.log | while read -r r; do
        delcmd=$(echo "$r" | sed 's/^-A/-D/')
        log "Deleting nat rule: $delcmd"
        run_cmd "iptables -t nat $delcmd > /dev/null 2>> error.log || true"
      done

      # also attempt to delete the specific rules added by John's setup (tcp redirect & FORWARD DROP) if present
      run_cmd "iptables -t nat -D PREROUTING -p tcp -i ${HS_INTERFACE} -j DNAT --to-destination ${LOCAL_IP}:80 > /dev/null 2>> error.log || true"
      run_cmd "iptables -t filter -D FORWARD -p all -i ${HS_INTERFACE} -j DROP > /dev/null 2>> error.log || true"
      run_cmd "iptables -t filter -D FORWARD -p all -o ${HS_INTERFACE} ! -s ${LOCAL_IP} -j DROP > /dev/null 2>> error.log || true"
    fi

	# 5) Remove John's mirrored chains and per-user chains (best-effort)
    if command -v iptables >/dev/null 2>&1; then
      MIRROR_PREFIX="${MIRROR_PREFIX:-CYBERCAFE-MIRROR-}"
      USER_CHAIN_PREFIX="${USER_CHAIN_PREFIX:-cybercafe-user-}"

      log "Removing mirror chains with prefix ${MIRROR_PREFIX} (if any)"
      iptables -S 2>> error.log | awk '{print $2}' | grep -E "^${MIRROR_PREFIX}" 2>> error.log | sort -u | while read -r ch; do
        [[ -z "$ch" ]] && continue
        log "Found mirror chain: $ch -- flushing & deleting"
        run_cmd "iptables -F ${ch} > /dev/null 2>> error.log || true"
        run_cmd "iptables -X ${ch} > /dev/null 2>> error.log || true"
      done

      log "Removing per-user chains with prefix ${USER_CHAIN_PREFIX} (if any)"
      iptables -S 2>> error.log | awk '{print $2}' | grep -E "^${USER_CHAIN_PREFIX}" 2>> error.log | sort -u | while read -r uch; do
        [[ -z "$uch" ]] && continue
        log "Found user chain: $uch -- flushing & deleting"
        run_cmd "iptables -F ${uch} > /dev/null 2>> error.log || true"
        run_cmd "iptables -X ${uch} > /dev/null 2>> error.log || true"
      done
    fi

	# 6) Remove qdisc on hotspot interface (Chris)
    if command -v tc >/dev/null 2>&1; then
      if ip link show "${HS_INTERFACE}" > /dev/null 2>> error.log; then
        log "Deleting qdisc on ${HS_INTERFACE}"
        run_cmd "tc qdisc del dev ${HS_INTERFACE} root > /dev/null 2>> error.log || true"
      else
        log "Interface ${HS_INTERFACE} not present; skipping tc cleanup"
      fi
    else
      warn "tc not found; skipping qdisc cleanup"
    fi

	# 7) Reset runtime variables to safe defaults
    LOCAL_IP=''
    HS_STATUS='down'
	HS_STATUS_PREV='down'

    log "shutdown_infrastructure completed (dry-run=${DRY_RUN})"
}

function start_captive_webserver
#starts the lighttpd webserver that acts as a captive web portal for sign in and such
{
	#Make sure required variables are set
	if [ -z "${LIGHTTPD_PATH:-}" ] || [ -z "${LIGHTTPD_CONF:-}" ]; then
		echo "$(date -Is) Error in Cybercafe_setupFunction.sh: Line ${LINENO} - LIGHTTPD_PATH or LIGHTTPD_CONF_PATH variable not set" >> error.log
		return 1
	fi

	#Make sure paths are valid
	if [ ! -x "${LIGHTTPD_PATH}" ]; then
		echo "$(date -Is) Error in Cybercafe_setupFunction.sh: Line ${LINENO} - lighttpd executable not found at LIGHTTPD_PATH: ${LIGHTTPD_PATH}" >> error.log
		return 1
	fi
	if [ ! -f "${LIGHTTPD_CONF}" ]; then
		echo "$(date -Is) Error in Cybercafe_setupFunction.sh: Line ${LINENO} - lighttpd configuration file not found at LIGHTTPD_CONF_PATH: ${LIGHTTPD_CONF}" >> error.log
		return 1
	fi

	#Idempotency check: ensure server is not already running
	if pgrep lighttpd > /dev/null 2>> error.log; then
		echo "$(date -Is) Captive portal webserver already running." >> error.log
		return 0
	fi

	#Start webserver in background, minimal logging
	echo "$(date -Is) Starting captive portal webserver..." >> error.log
	"${LIGHTTPD_PATH}" -f "${LIGHTTPD_CONF}" > /dev/null 2>> error.log &

	echo "$(date -Is) Captive portal webserver started." >> error.log
	return 0
}