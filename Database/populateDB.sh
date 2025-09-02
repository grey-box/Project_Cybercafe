#!/bin/bash
mapfile -t FIRSTNAME < firstNames.txt
mapfile -t LASTNAME < lastNames.txt
mapfile -t WEBSITE < websiteList.txt


#Database Name
echo "Enter the database name (default: example.db):"
read db_name

if [ -z "$db_name" ]; then
    db_name="example.db"
fi

if [ -f "$db_name" ]; then
    echo "Database '$db_name' already exists."

else
    echo "Database '$db_name' does not exist. Creating a new one..."
    sqlite3 "$db_name" < Final_CyberCafe_Schema.sql   
fi

echo "Enter the number of users (default: 100):"
read num_users

if [ -z "$num_users" ]; then
    num_users=100
fi


# Check if SQLite3 is installed
if ! command -v sqlite3 &> /dev/null; then
    echo "SQLite3 is not installed. Please install it first."
    exit 1
fi


user_role_array=("Admin" "Owner" "Regular")
user_status_array=("Active" "Suspended" "Banned")
membership_level_array=("Member Level 1" "Member Level 2" "Member Level 3")


for ((i=1; i<num_users; i++))
do

    first_name="${FIRSTNAME[RANDOM % ${#FIRSTNAME[@]}]}"
    last_name="${LASTNAME[RANDOM % ${#LASTNAME[@]}]}"
    full_name="$first_name $last_name"
    email="${first_name}.${last_name}@gmail.com"
    userID="userID_${first_name}_${i}"
    phone_number=$(printf "1800%06d" "$i")
    access_code=$(printf "%06d" "$i")
    user_role="${user_role_array[RANDOM % ${#user_role_array[@]}]}"
    user_status="${user_status_array[RANDOM % ${#user_status_array[@]}]}"
    account_expiry_date=$(date '+%Y-%m-%d %H:%M:%S')
    account_creation_date=$(date '+%Y-%m-%d %H:%M:%S')
    last_login_timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    membership_level="${membership_level_array[RANDOM % ${#membership_level_array[@]}]}"


    levelID="levelID_${first_name}_${i}"
    levelName=${membership_level}

    description="The Membership Description is ${membership_level}"
    if [ "$membership_level" == "Member Level 1" ]; then
        benefits="The Membership Benefits is Basic"
        speedAmount=10
    elif [ "$membership_level" == "Member Level 2" ]; then
        benefits="The Membership Benefits is Good"
        speedAmount=100
    else
        benefits="The Membership Benefits is Great"
        speedAmount=1000
    fi

    roleID="roleID_${first_name}_${i}"
    roleName=$user_role

    if [ "$user_role" == "Admin" ]; then
        roleDescription="Admin Role Controls EveryThing"
        permissionSet=0
    elif [ "$user_role" == "Owner" ]; then
        roleDescription="Device Owner"
        permissionSet=1
    else
        roleDescription="Regular user"
        permissionSet=2
    fi

    sqlite3 "$db_name" <<EOF
    INSERT INTO user (
      userID, fullName, email, phoneNumber, accessCode, userRole, userStatus,
      accountExpiryDate, accountCreationDate, lastLoginTimestamp
    ) VALUES (
      '$userID', '$full_name', '$email', '$phone_number', '$access_code',
      '$user_role', '$user_status', '$account_expiry_date', '$account_creation_date',
      '$last_login_timestamp'
    );

    INSERT INTO membership_level (
      levelID, levelName, description, benefits, speedAmount
    ) VALUES (
        '$levelID', '$levelName', '$description', '$benefits', '$speedAmount'
    );

    INSERT INTO user_role (
      roleID, roleName, roleDescription, permissionSet
    ) VALUES (
        '$roleID', '$roleName', '$roleDescription', '$permissionSet'
    );


EOF
# ADD USER_BALANCE, SPEED_QUEUE, SERVICE PLAN



for ((j=0; j<$((RANDOM % 50 + 1)); j++))
do

sessionID="sessionID_${first_name}_${j}"
ipAddress=$(printf "%d.%d.%d.%d\n" $((RANDOM%256)) $((RANDOM%256)) $((RANDOM%256)) $((RANDOM%256)))
macAddress=$(printf '%02x:%02x:%02x:%02x:%02x:%02x\n' $((RANDOM%256)) $((RANDOM%256)) $((RANDOM%256)) $((RANDOM%256)) $((RANDOM%256)) $((RANDOM%256)))
hostName="hostname_${first_name}${last_name}"
loginTimestamp=$(date '+%Y-%m-%d %H:%M:%S')
logoutTimestamp=$(date '+%Y-%m-%d %H:%M:%S')
sessionLength=$((RANDOM % 120 + 1))
speedQueueID="speedQueueID_${first_name}"


logID=$(( i * 100000 + RANDOM % 100000 ))

url="${WEBSITE[RANDOM % ${#WEBSITE[@]}]}"
accessTime=$(date '+%Y-%m-%d %H:%M:%S')
blocked=( RANDOM % 2 )

restrictionID=$((logID + 1000000))
isBlocked=( RANDOM % 2 )

balanceIDTemp="${i}${j}"
balanceID=$((balanceIDTemp))
bytesRemaining=$((RANDOM % 1000000))
monetaryBalance=$((RANDOM % 100000))
lastUpdateTimestamp=$(date '+%Y-%m-%d %H:%M:%S')

planID="${first_name}_plainID${j}"
planName=${membership_level}

queueID="${first_name}_queueID${j}"
queueName=${membership_level}

uploadSpeedLimit=${speedAmount}
downloadSpeedLimit=$(( speedAmount / 2 ))
bandwidthQuota=${speedAmount}

if [ "$membership_level" == "Member Level 1" ]; then
        monthlyPrice=100
    elif [ "$membership_level" == "Member Level 2" ]; then
        monthlyPrice=50
    else
        monthlyPrice=10
    fi




trafficIDTemp="${i}${j}"
trafficID=$((trafficIDTemp))
receivedBytes=$((RANDOM % 100))
transmittedBytes=$((RANDOM % 100))

sqlite3 "$db_name" <<EOF
INSERT INTO internet_session  (
      sessionID, userID, ipAddress, macAddress, hostname, loginTimestamp, logoutTimestamp, sessionLength, speedQueueID
    ) VALUES (
        '$sessionID', '$userID', '$ipAddress', '$macAddress', '$hostName', '$loginTimestamp'
        , '$logoutTimestamp', '$sessionLength', '$speedQueueID'
    );


    INSERT INTO website_access_log (
      logID, sessionID, url, accessTime, blocked
    ) VALUES (
        '$logID', '$sessionID', '$url', '$accessTime', '$blocked'
    );

    INSERT INTO url_restriction (
    restrictionID , url, isBlocked
    ) VALUES (
        '$restrictionID', '$url', '$isBlocked'
    );

    INSERT INTO user_balance (
      balanceID, userID, speedQueueID, bytesRemaining, monetaryBalance, lastUpdateTimestamp
    ) VALUES (
        '$balanceID','$userID', '$speedQueueID', '$byesRemaining', '$monetaryBalance', '$lastUpdateTimestamp'
    );

    INSERT INTO service_plan (
        planID, planName, uploadSpeedLimit, bandwidthQuota, monthlyPrice
    ) VALUES (
        '$planID','$planName', '$uploadSpeedLimit', '$bandwidthQuota', '$monthlyPrice'
    );

    
    INSERT INTO speed_queue (
        queueID, queueName, uploadSpeedLimit, downloadSpeedLimit, bandwidthQuota, planID
    ) VALUES (
        '$queueID','$queueName', '$uploadSpeedLimit', '$downloadSpeedLimit', '$bandwidthQuota', '$planID'
    );

    INSERT INTO traffic_data (
        trafficID, sessionID, receivedBytes, transmittedBytes
        ) VALUES (
            '$trafficID', '$sessionID', '$recievedBytes', '$transmittedBytes'
        );

EOF

reason="TESTING"

if (( j % 10 == 0 )); then
    sqlite3 "$db_name" <<EOF
    INSERT INTO device_restriction (
        restrictionID, macAddress, reason, queueID
    ) VALUES (
        '$restrictionID', '$macAddress', '$reason', '$queueID'
    );
EOF
fi

done

    for ((k=0; k<$((RANDOM % 50 + 1)); k++))
do

    historyIDTemp="${i}${k}"
    historyID=$((historyIDTemp))
    timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    reason="TESTING"

    sqlite3 "$db_name" <<EOF

    INSERT INTO user_status_history (
    historyID, userID, statusType, timestamp, reason
    ) VALUES (
        '$historyID','$userID','$statusType', '$timestamp', '$reason'
    );



EOF

done


for ((m=0; m<$((RANDOM % 50 + 1)); m++))
do

paymentID="${first_name}_paymentID${m}"
paymentDateTime=$(date '+%Y-%m-%d %H:%M:%S')
payment="CARD TESTING"
amountCharged=$((RANDOM % 100 + 1))
transactionRefNumber="${m}_${first_name}_transctionRefNumber"
invoiceNumber="${i}${m}_invoiceNumber"


historyIDTemp="${i}${m}000"
historyID=$((historyIDTemp))
timestamp=$(date '+%Y-%m-%d %H:%M:%S')
amountDue=$((RANDOM % 100 + 1))
amountPaid=$((RANDOM % 100 + 1))
paymentStatus="PAID TESTING"

reportID="${first_name}${m}"
reportType="TESTING REPORTTYPE"
generationTime=$(date '+%Y-%m-%d %H:%M:%S')
parameters="TESTING REPORT PARAMETERS"

mappingID="${i}${m}000000"


sqlite3 "$db_name" <<EOF

INSERT INTO payment (
    paymentID, userID, paymentDateTime, paymentMethod, amountCharged, transactionRefNumber, invoiceNumber
    ) VALUES (
        '$paymentID', '$userID', '$paymentDateTime', '$paymentMethod', '$amountCharged', '$transactionRefNumber', '$invoiceNumber'
    );

INSERT INTO payment_history (
    historyID, userID, timestamp, amountDue, amountPaid, paymentStatus
) VALUES (
    '$historyID', '$userID', '$timestamp', '$amountDue', '$amountPaid', '$paymentStatus'
);

INSERT INTO report (
    reportID, reportType, generationTime, parameters
) VALUES (
    '$reportID','$reportType','$generationTime','$parameters'
);

INSERT INTO user_report_mapping (
    mappingID, userID, reportID, reportType, generationTime
) VALUES (
    '$mappingID','$userID','$reportID','$reportType','$generationTime'
);

EOF

done



for ((n=0; n<$((RANDOM % 50 + 1)); n++))
do

logIDTemp="${i}${n}000000000"
logID=$((logIDTemp))
statusIDTemp="${i}${n}"
statusID=${statusIDTemp}
eventType="TESTING"
timestamp=$(date '+%Y-%m-%d %H:%M:%S')
description="TESTING MAINTENANCE LOG"


if (( n % 10 == 0 )); then
hostIPStatus="UP"
else
hostIPStatus="DOWN"
fi

restartCount=$((RANDOM % 100 + 1))
lastRebootTime=$(date '+%Y-%m-%d %H:%M:%S')
lastRefreshTime=$(date '+%Y-%m-%d %H:%M:%S')

if((n % 2 == 0 ));then
softwareVersion="WINDOWS"
else
softwareVersion="LINUX"
fi


sqlite3 "$db_name" <<EOF


    INSERT INTO maintenance_log (
        logID, statusID, eventType, timestamp, description
    ) VALUES (
    '$logID','$statusID','$eventType','$timestamp','$description'
);

INSERT INTO system_status (
        statusID, hostIPStatus, localIPAddress, lastRefreshTime, uptime,restartCount,lastRebootTime,softwareVersion
    ) VALUES (
    '$statusID','$hostIPStatus','$ipAddress','$lastRefreshTime','$uptime','$restartCount','$lastRebootTime','$softwareVersion'
);

EOF


done

done




