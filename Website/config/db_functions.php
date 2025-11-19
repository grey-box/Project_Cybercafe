<?php

declare(strict_types=1);

require_once __DIR__ . '/paths.php';   

function getAllUsers(): array
{
    $db = get_db(); 

    $stmt = $db->query("SELECT full_name FROM user");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getUserQueueAndTime(): array
{
   $db = get_db();

    $sql = "
        SELECT 
            u.full_name,
            COALESCE(q.download_speed_limit || ' KB/s', '— KB/s')          AS speed,
            CASE 
                WHEN u.last_login_timestamp IS NULL THEN 'Never'
                WHEN u.last_login_timestamp > datetime('now', '-5 minutes') THEN 'Active Now.'
                ELSE 
                    CAST(ROUND((julianday('now') - julianday(u.last_login_timestamp)) * 1440) AS INTEGER)
                    || ' mins ago.'
            END AS time_ago
        FROM user u
        LEFT JOIN user_balance ub ON u.user_id = ub.user_id
        LEFT JOIN speed_queue q ON ub.speed_queue_id = q.queue_id
        ORDER BY u.last_login_timestamp DESC NULLS LAST
        LIMIT 10
    ";

    $rows = $db->query($sql)->fetchAll(PDO::FETCH_NUM); // ← IMPORTANT: FETCH_NUM = indexed array

    // Force everything to string and guarantee exactly 3 columns
    $activeUsers = [];
    foreach ($rows as $row) {
        $activeUsers[] = [
            (string)($row[0] ?? 'Unknown User'),
            (string)($row[1] ?? '— KB/s'),
            (string)($row[2] ?? 'Never')
        ];
    }

    return $activeUsers;
}


function getUserNameAndQuota(): array
{
    $db = get_db();

    $sql = "
        SELECT 
            u.full_name,
            COALESCE(
                CAST(q.bandwidth_quota AS TEXT) || ' MB',
                'Unlimited'
            ) AS quota_display
        FROM user u
        LEFT JOIN user_balance ub ON u.user_id = ub.user_id
        LEFT JOIN speed_queue q ON ub.speed_queue_id = q.queue_id
        ORDER BY u.full_name ASC
    ";

    $rows = $db->query($sql)->fetchAll(PDO::FETCH_NUM);  // ← indexed arrays only

    $result = [];
    foreach ($rows as $row) {
        $result[] = [
            (string)($row[0] ?? 'Unknown User'),
            (int)($row[1] ?? 0)
        ];
    }

    return $result;
}

function getUserIdNameAndCurrentStatus(): array
{
    $db = get_db();

    $sql = "
        SELECT 
            u.user_id,
            u.full_name,
            COALESCE(latest_status.status_code, 'PENDING') AS status_code
        FROM user u
        LEFT JOIN (
            -- Get the MOST RECENT status for each user
            SELECT 
                user_id,
                status_code,
                ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY changed_at DESC, status_id DESC) AS rn
            FROM user_status_history
        ) latest_status ON u.user_id = latest_status.user_id AND latest_status.rn = 1
        ORDER BY u.full_name ASC
    ";

    $rows = $db->query($sql)->fetchAll(PDO::FETCH_NUM);

    $result = [];
    foreach ($rows as $row) {
        $result[] = [
            (string)($row[0] ?? 'Unknown UserID'),
            (string)($row[1] ?? 'Unknown User'),
            (string)($row[2] ?? 'Unvalid')
        ];
    }

    return $result;
}


function getBlockedUrls(): array
{
    $db = get_db();

    $sql = "
        SELECT url
        FROM url_restriction 
        WHERE is_blocked = 1
        ORDER BY created_at DESC
    ";

    return $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
}

function blockUrl(string $url, ?string $created_by = null): bool
{
    try {
        $db = get_db();

        // Clean the URL
        $url = trim($url);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        $url = rtrim($url, '/');

        $stmt = $db->prepare("
            INSERT INTO url_restriction (url, is_blocked, created_by_user_id)
            VALUES (:url, 1, :by)
            ON CONFLICT(url) DO UPDATE SET
                is_blocked = 1,
                created_by_user_id = excluded.created_by_user_id
        ");

        $stmt->execute([
            ':url' => $url,
            ':by'  => $created_by
        ]);

        return true;

    } catch (Exception $e) {
        // This will show you the EXACT error
        echo "<pre style='background:red;color:white;padding:20px;'>";
        echo "BLOCK URL ERROR: " . $e->getMessage();
        echo "</pre>";
        return false;
    }
}

// Block a device by MAC address
// db_functions.php — ADD THIS EXACT CODE
function blockDevice(string $mac, ?string $reason = null): bool
{
    try {
        $db = get_db();
        $stmt = $db->prepare("
            INSERT OR IGNORE INTO device_restriction (mac_address, reason) 
            VALUES (:mac, :reason)
        ");
        return $stmt->execute([
            ':mac'    => strtoupper(trim($mac)),
            ':reason' => $reason ?: 'Blocked by admin'
        ]);
    } catch (Exception $e) {
        error_log("blockDevice error: " . $e->getMessage());
        return false;
    }
}

// Unblock a device
function unblockDevice(string $mac): bool
{
    try {
        get_db()->prepare("DELETE FROM device_restriction WHERE mac_address = ?")
                ->execute([strtoupper(trim($mac))]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Get all blocked devices
function getBlockedDevices(): array
{
    return get_db()
        ->query("SELECT mac_address, reason FROM device_restriction")
        ->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get user session summary — ONLY: User ID, Usage (MB), Session Time, Status
 * No full_name / user_name anymore
 */
function getUserSessionSummary(): array
{
    $db = get_db();

    $sql = "
        SELECT 
            s.user_id,

            -- Total traffic in MB
            COALESCE(ROUND((t.received_bytes + t.transmitted_bytes) / (1024.0 * 1024.0), 2), 0) AS usage_mb,

            -- Duration in seconds (active = now - login)
            CASE 
                WHEN s.logout_timestamp IS NOT NULL 
                THEN CAST((JULIANDAY(s.logout_timestamp) - JULIANDAY(s.login_timestamp)) * 86400 AS INTEGER)
                ELSE CAST((JULIANDAY('now') - JULIANDAY(s.login_timestamp)) * 86400 AS INTEGER)
            END AS duration_seconds,

            -- Active status
            (s.logout_timestamp IS NULL) AS is_active

        FROM internet_session s
        LEFT JOIN traffic_data t ON s.session_id = t.session_id
        GROUP BY s.user_id
        ORDER BY MAX(s.login_timestamp) DESC
    ";

    $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $summary = [];
    foreach ($rows as $row) {
        $seconds = (int)$row['duration_seconds'];
        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $summary[] = [
            'User ID'      => $row['user_id'],
            'Usage (MB)'   => number_format((float)$row['usage_mb'], 2),
            'Session Time' => ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm',
            'Status'       => $row['is_active'] ? 'Active' : 'Inactive'
        ];
    }

    return $summary;
}