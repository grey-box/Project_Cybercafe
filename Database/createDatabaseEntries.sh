DATABASE_PATH=./CyberCafe_Database.db


TABLE_INDEX=$((0))
USER_ID=$((1))
SESSION_ID="a838fjdlkc908sdjfk3jnk2wjnef"
USER_IP="192.168.1.131"
SESSION_TX=$((1029423))
SESSION_RX=$((34423))
SESSION_ACCESS=$((1))
DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
DATETIME_LASTREQUEST=$(date '+%Y-%m-%d %H:%M:%S')
sqlite3 $DATABASE_PATH "INSERT INTO internet_sessions VALUES (${TABLE_INDEX},${USER_ID},'${SESSION_ID}','${USER_IP}',${SESSION_TX},${SESSION_RX},${SESSION_ACCESS},'${DATETIME}','${DATETIME_LASTREQUEST}',0)"
echo $?
echo "sqlite3 $DATABASE_PATH \"INSERT INTO internet_sessions VALUES (${TABLE_INDEX},${USER_ID},'${SESSION_ID}','${USER_IP}',${SESSION_TX},${SESSION_RX},${SESSION_ACCESS},'${DATETIME}','${DATETIME_LASTREQUEST}',0)\""
exit

I=$((0))
while [[ $I -lt 8 ]]; do
USER_ID=$((1))
DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
INTERVAL_TX=$((1+$RANDOM%10000))
INTERVAL_RX=$((1+$RANDOM%10000))
SESSION_NUM=$((5))
sqlite3 $DATABASE_PATH "INSERT INTO user_data_usage (user_id,session_entry_index,session_number,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${I},${SESSION_NUM},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})"
echo $?
echo "sqlite3 $DATABASE_PATH \"INSERT INTO user_data_usage (user_id,entry_index,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${I},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})"
I=$(($I+1))
done

I=$((0))
while [[ $I -lt 14 ]]; do
USER_ID=$((1))
DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
INTERVAL_TX=$((1+$RANDOM%10000))
INTERVAL_RX=$((1+$RANDOM%10000))
SESSION_NUM=$((6))
sqlite3 $DATABASE_PATH "INSERT INTO user_data_usage (user_id,session_entry_index,session_number,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${I},${SESSION_NUM},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})"
echo $?
echo "sqlite3 $DATABASE_PATH \"INSERT INTO user_data_usage (user_id,entry_index,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${I},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})"
I=$(($I+1))
done

I=$((0))
while [[ $I -lt 23 ]]; do
USER_ID=$((2))
DATETIME=$(date '+%Y-%m-%d %H:%M:%S')
INTERVAL_TX=$((1+$RANDOM%10000))
INTERVAL_RX=$((1+$RANDOM%10000))
SESSION_NUM=$((3))
sqlite3 $DATABASE_PATH "INSERT INTO user_data_usage (user_id,session_entry_index,session_number,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${I},${SESSION_NUM},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})"
echo $?
echo "sqlite3 $DATABASE_PATH \"INSERT INTO user_data_usage (user_id,entry_index,entry_datetime,interval_bytes_tx,interval_bytes_rx) VALUES (${USER_ID},${I},'${DATETIME}',${INTERVAL_TX},${INTERVAL_RX})"
I=$(($I+1))
done

exit
