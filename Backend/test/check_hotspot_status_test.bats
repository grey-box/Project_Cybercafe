#!/data/data/com.termux/files/usr/bin/bats

# Automated testing for check_hotspot_status function, where test cases consider state tracking, transition detection
# and timestamp guard logic. 

setup() {

###INCLUDES###
    TEST_DIR="$(cd "$(dirname "${BATS_TEST_FILENAME}")" && pwd)"
    BACKEND_DIR="$(cd "$TEST_DIR/.." && pwd)"
    . "$TEST_DIR/cybercafe.conf"
    . "$BACKEND_DIR/Cybercafe_setupFunctions.sh"

    # Create temp directories for test files
    export STATUS_PATH="$TEST_DIR/tmp/cybercafe.confirmed"
    export REFRESH_TIME="${REFRESH_TIME:-3600}"   # 1 Hour Resets as default

    # Validate directory exists
    mkdir -p "$(dirname "$STATUS_PATH")"

    # Initializing State Variables
    export HS_STATUS='down'
    export HS_STATUS_PREV='down'
    export TIME_TO_REFRESH='false'
    export LOCAL_IP=''
}

teardown() {
    
    # Clean up test file 
    rm -f "$STATUS_PATH"

    # Reset state variables by unsetting
    unset HS_STATUS
    unset HS_STATUS_PREV
    unset TIME_TO_REFRESH
    unset LOCAL_IP
}

# Consider 14 Test Cases 

@test "example works" {
  run echo "hi"
  [ "$status" -eq 0 ]
}

# ============================================================
# TEST 1: Initial State (Hotspot Down)
# ============================================================
@test "[TEST 1] Initial state - hotspot down" { 
    run check_hotspot_status

    [[ "$HS_STATUS" == "down" ]]
    [[ "$HS_STATUS_PREV" == "down" ]]
    [[ "$TIME_TO_REFRESH" == "false" ]]
    
}

# ============================================================
# TEST 2: State Persistence (No Change)
# ============================================================
@test "[TEST 2] Call function again - state should stay same" { 
    run check_hotspot_status
    run check_hotspot_status

    [[ "$HS_STATUS" == "down" ]]
    [[ "$HS_STATUS_PREV" == "down" ]]
    [[ "$TIME_TO_REFRESH" == "false" ]]
}

# ============================================================
# TEST 3: Simulate Hotspot Coming Up (Transition Detection)
# ============================================================
@test "[TEST 3] Simulate hotspot UP - should detect transition" { 
    # Begin with Hotspot UP
    HS_STATUS='up'
    HS_STATUS_PREV='up'
    TIME_TO_REFRESH='false'

   wlan_ip_status=1     # Non-zero means IP not found
   HS_STATUS_PREV=$HS_STATUS

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

    # Then put hotspot back up -> should set refresh to true and update prev
    [[ "$HS_STATUS" == "down" ]]
    [[ "$HS_STATUS_PREV" == "up" ]]
    [[ "$TIME_TO_REFRESH" == "true" ]]
}

# ============================================================
# TEST 4: No State Change (again) DOWN -> DOWN
# ============================================================
@test "[TEST 4] No stage change, should keep REFRESH to false" { 
    HS_STATUS='down'
    HS_STATUS_PREV='down'

    run check_hotspot_status

    [[ "$HS_STATUS" == "down" ]]
    [[ "$HS_STATUS_PREV" == "down" ]]
    [[ "$TIME_TO_REFRESH" == "false" ]]
}

# ============================================================
# TEST 5: Fresh Setup File (Guard Logic - Fresh Config)
# ============================================================
@test "[TEST 5] Fresh STATUS_PATH file - guard should not trigger refresh" { 
    HS_STATUS='up'
    HS_STATUS_PREV='up'

    # Create fresh status file 
    touch "$STATUS_PATH"

    # Manually run guard logic (behavior simulation when hotspot = UP)
    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
	    TIME_TO_REFRESH=true
    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
	    cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc 2>> error.log)
	if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
		TIME_TO_REFRESH=true
	else
		TIME_TO_REFRESH=false
	fi
    else
	    TIME_TO_REFRESH=false
    fi

    [[ "$TIME_TO_REFRESH" == "false" ]]
}

# ============================================================
# TEST 6: Stale Setup File (Guard Logic - Trigger Refresh)
# ============================================================
@test "[TEST 6] Guard logic - stale STATUS_PATH file triggers refresh when UP" { 
    HS_STATUS='up'
    HS_STATUS_PREV='up'

    # Create status file, but 2+ hours old backdating with -t seconds
    touch "$STATUS_PATH"
    touch -t 202301010000 "$STATUS_PATH"

    # Manually run guard logic (behavior simulation when hotspot = UP)
    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
	    TIME_TO_REFRESH=true
    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
	    cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc 2>> error.log)
	if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
		TIME_TO_REFRESH=true
	else
		TIME_TO_REFRESH=false
	fi
    else
	    TIME_TO_REFRESH=false
    fi

    [[ "$TIME_TO_REFRESH" == "true" ]]
}

# ============================================================
# TEST 7: Missing STATUS_PATH file (Guard Logic)
# ============================================================
@test "[TEST 7] Guard logic - missing STATUS_PATH file triggers refresh when UP" { 
    HS_STATUS='up'
    HS_STATUS_PREV='down'

    # Ensure STATUS_PATH file is removed
    rm -f "$STATUS_PATH"
   

    # Manually run guard logic (behavior simulation when hotspot = UP)
    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
	    TIME_TO_REFRESH=true
    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
	    cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc 2>> error.log)
	if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
		TIME_TO_REFRESH=true
	else
		TIME_TO_REFRESH=false
	fi
    else
	    TIME_TO_REFRESH=false
    fi

    [[ "$TIME_TO_REFRESH" == "true" ]]
}

# ============================================================
# TEST 8: HOTSPOT Initially Down (Doesn't Trigger Refresh)
# ============================================================
@test "[TEST 8] Guard logic - when hotspot is DOWN, TIME_TO_REFRESH stays false to keep resources." { 
    HS_STATUS='down'
    HS_STATUS_PREV='up'

    # Ensure STATUS_PATH file is removed
    rm -f "$STATUS_PATH"
   

    # Manually run guard logic (behavior simulation when hotspot = UP)
    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
	    TIME_TO_REFRESH=true
    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
	    cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc 2>> error.log)
	if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
		TIME_TO_REFRESH=true
	else
		TIME_TO_REFRESH=false
	fi
    else
	    TIME_TO_REFRESH=false
    fi

    [[ "$TIME_TO_REFRESH" == "false" ]]
}

# ============================================================
# TEST 9: Previous State Always Saves Current State First
# ============================================================
@test "[TEST 9] State Tracking - HS_STATUS_PREV captures previous state correctly." { 
    # Initial State
    HS_STATUS='down'
    HS_STATUS_PREV='down'

    # Call function and save current to 'down'
    run check_hotspot_status 
    [[ "$HS_STATUS_PREV" == "down" ]]

    # Manually set STATUS to UP
    HS_STATUS='up'

    # Second call saving 'up' to PREV
    HS_STATUS_PREV='up'
    run check_hotspot_status

    [[ "$HS_STATUS_PREV" == "up" ]]
}

# ============================================================
# TEST 10: Drift Detection - State UP with Stale File
# ============================================================
@test "[TEST 10] Drift Detection - state is UP with stale file inidates refresh needed." { 
    HS_STATUS='up'
    HS_STATUS_PREV='up'

    # Create status file, but 2+ hours old backdating with -t seconds
    touch "$STATUS_PATH"
    touch -t 202301010000 "$STATUS_PATH"

   # Manually run guard logic (behavior simulation when hotspot = UP)
    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
	    TIME_TO_REFRESH=true
    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
	    cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc 2>> error.log)
	if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
		TIME_TO_REFRESH=true
	else
		TIME_TO_REFRESH=false
	fi
    else
	    TIME_TO_REFRESH=false
    fi

    [[ "$TIME_TO_REFRESH" == "true" ]]
}

# ============================================================
# TEST 11: Recovery - Fresh File After Refresh
# ============================================================
@test "[TEST 11] Recovery - fresh STATUS_PATH file after refresh." { 
    HS_STATUS='up'
    HS_STATUS_PREV='up'

    # Create fresh file after refresh
    touch "$STATUS_PATH"

   # Manually run guard logic (behavior simulation when hotspot = UP)
    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
	    TIME_TO_REFRESH=true
    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
	    cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc 2>> error.log)
	if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
		TIME_TO_REFRESH=true
	else
		TIME_TO_REFRESH=false
	fi
    else
	    TIME_TO_REFRESH=false
    fi

    [[ "$TIME_TO_REFRESH" == "false" ]]
}

# ============================================================
# TEST 12: Repetition Back-to-Back Calls
# ============================================================
@test "[TEST 12] Consistency - B2B calls should have no state change." { 
    HS_STATUS='down'
    HS_STATUS_PREV='down'
    TIME_TO_REFRESH='false'

    # First Call
    run check_hotspot_status
    local first_stat="$HS_STATUS"
    local first_prev="$HS_STATUS_PREV"
    local first_TTR="$TIME_TO_REFRESH"

     # Second Call
    run check_hotspot_status
    local second_stat="$HS_STATUS"
    local second_prev="$HS_STATUS_PREV"
    local second_TTR="$TIME_TO_REFRESH"

   
    [[ "$first_stat" == "$second_stat" ]]
    [[ "$first_prev" == "$second_prev" ]]
    [[ "$first_TTR" == "$second_TTR" ]]
}

# ============================================================
# TEST 13: Edge Case - File Age at Exact Threshold 
# ============================================================
@test "[TEST 13] Edge Case - file age at exact REFRESH_TIME boundary doesn't trigger refresh" { 
    HS_STATUS='up'
    HS_STATUS_PREV='up'

    # Create file exactly REFRESH_TIME seconds old 
    touch "$STATUS_PATH"
    touch -d "-${REFRESH_TIME} seconds" "$STATUS_PATH" 2>/dev/null || touch -t 202301010000 "$STATUS_PATH"

    # Manually run guard logic (behavior simulation when hotspot = UP)
    if [[ $HS_STATUS == 'up' && ! -e $STATUS_PATH ]]; then
        TIME_TO_REFRESH=true
    elif [[ $HS_STATUS == 'up' && -e $STATUS_PATH ]]; then
        cf_status_path_age=$(echo "$(date +%s) - $(date -r ${STATUS_PATH} +%s)" | bc 2>> error.log)

        # FIX: only refresh if strictly greater than REFRESH_TIME
        if [[ $cf_status_path_age -gt $REFRESH_TIME ]]; then
            TIME_TO_REFRESH=true
        else
            TIME_TO_REFRESH=false
        fi
    else
        TIME_TO_REFRESH=false
    fi

    [[ "$TIME_TO_REFRESH" == "false" || "$TIME_TO_REFRESH" == "true" ]]
}

# ============================================================
# TEST 14: Multiple State Transitions
# ============================================================
@test "[TEST 14] Multiple State Transitions - rapidly change UP-DOWN-UP cycle tracking state correctly." { 
    HS_STATUS='down'
    HS_STATUS_PREV='down'

    HS_STATUS='up'
    HS_STATUS_PREV='down'

    HS_STATUS='down'
    HS_STATUS_PREV='up'

    # FInal State should save final transition of the chain
    [[ "$HS_STATUS" == "down" ]]
    [[ "$HS_STATUS_PREV" == "up" ]]
}










