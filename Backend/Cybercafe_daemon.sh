#!/bin/bash
#Organization: Grey-box
#Project: Cybercafe
#File: daemon
#Description: Main script that is run at start. This script is invoked by Cybercafe_commandLine.sh

###INCLUDES###
. ./Cybercafe_setupFunctions.sh
. ./Cybercafe_internetSessionFunctions.sh
			

### START OF PROGRAM EXECUTION ###
while true; do
	check_hotspot_status

	#Case 1: Hotspot up and was up in previous check
    if [[ $HS_STATUS == 'up' ]] && [[ $HS_STATUS_PREV == 'up' ]]; then
        printf "%s" "$(date +%T)" \
        && echo ": Status normal"
		check_internet_sessions
	
	#Case 2: Hotspot up and was down in previous check
    elif [[ $HS_STATUS == 'up' ]] && [[ $HS_STATUS_PREV == 'down' ]]; then
        printf "%s" "$(date +%T)" \
        && echo ": Hotspot up"
        setup_infrastructure
	
	#Case 3: Hotspot down and was up in previous check
    elif [[ $HS_STATUS == 'down' ]] && [[ $HS_STATUS_PREV == 'up' ]]; then
        # Assume hotspot has recently gone down. Clean up.
        printf "%s" "$(date +%T)" \
        && echo ": Hotspot went down"
		clear_internet_sessions
        shutdown_infrastructure
		
	#Case 4: Hotspot down and was down in previous check
    else
        printf "%s" "$(date +%T)" \
        && echo ": Hotspot down"
    fi

	if [[ -f ./shutdown.confirmed ]]; then
		#clear_internet_sessions
		shutdown_infrastructure
		exit
	fi
	
	sleep 2
done
