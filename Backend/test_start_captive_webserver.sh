#!/bin/bash
#Organization: Grey-box
#Project: Cybercafe
#File: test_start_captive_webserver.sh
#Description: Used to test start_captive_webserver function contained in Cybercafe_setupFunctions.sh

echo "--- Cybercafe Webserver Test Script ---"

#Load necessary config
if [ -f "./cybercafe.conf" ]; then
    echo "[+] Loading configuration file..."
    . ./cybercafe.conf
else
    echo "[!] Error: Could not find cybercafe.conf file!"
    exit 1
fi

#Load necessary implementation files
if [ -f "./Cybercafe_setupFunctions.sh" ]; then 
    echo "[+] Loading Cybercafe setup functions..."
    . ./Cybercafe_setupFunctions.sh
else
    echo "[!] Error: Could not find Cybercafe_setupFunctions.sh file!"
    exit 1
fi

#Print Paths
echo "[i] LIGHTTPD Path: $LIGHTTPD_PATH"
echo "[i] LIGHTTPD_CONF Path: $LIGHTTPD_CONF"

#Make sure paths exist
if [ ! -x "$LIGHTTPD_PATH" ]; then
    echo "[!] Error: lighttpd executable not found at $LIGHTTPD_PATH"
    exit 1
fi

if [ ! -f "$LIGHTTPD_CONF" ]; then
    echo "[!] Error: lighttpd configuration file not found at $LIGHTTPD_CONF"
    exit 1
fi

echo "[+] Starting captive webserver (1st time)..."
start_captive_webserver
RC1=$?

echo "[+] Starting captive webserver (2nd time, should be idempotent)..."
start_captive_webserver
RC2=$?

echo ""
echo "--- Test Results ---"
echo "First start_captive_webserver call return code: $RC1"
echo "Second start_captive_webserver call return code: $RC2"
echo ""

#Check if lighttpd process is running
if pgrep lighttpd > /dev/null 2>> error.log; then
    echo "[+] Captive webserver is running."
else
    echo "[!] Error: Captive webserver is not running."
    exit 1
fi

echo "Check error.log for any logged errors during the test."
echo "--- End of Test ---"