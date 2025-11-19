<?php
declare(strict_types=1);

/**
 * Shared read helpers for dashboard and profile pages.
 * Each function expects a valid PDO connection (SQLite).
 */

function fetchAllowedSites(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT url, created_at FROM url_restriction WHERE is_blocked = 0 ORDER BY url'
    );
    return $stmt->fetchAll() ?: [];
}

function fetchBlockedSites(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT url, created_at FROM url_restriction WHERE is_blocked = 1 ORDER BY url'
    );
    return $stmt->fetchAll() ?: [];
}

function fetchDashboardActiveSessions(PDO $pdo, int $limit = 5): array
{
    $sql = <<<SQL
    SELECT
        u.full_name,
        s.user_id,
        s.session_id,
        s.host_name,
        s.login_timestamp,
        s.logout_timestamp,
        COALESCE(t.last_updated_at, s.logout_timestamp, s.login_timestamp) AS last_activity,
        COALESCE(t.received_bytes, 0) + COALESCE(t.transmitted_bytes, 0) AS total_bytes
    FROM internet_session AS s
    INNER JOIN user AS u ON u.user_id = s.user_id
    LEFT JOIN traffic_data AS t ON t.session_id = s.session_id
    ORDER BY last_activity DESC
    LIMIT :limit;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll() ?: [];

    foreach ($rows as &$row) {
        $duration = max(
            1,
            strtotime((string)$row['last_activity']) - strtotime((string)$row['login_timestamp'])
        );
        $row['avg_kb_s'] = round(($row['total_bytes'] / $duration) / 1024, 1);
        $row['last_activity_label'] = $row['logout_timestamp'] === null
            ? 'Active now'
            : date('Y-m-d H:i', strtotime((string)$row['logout_timestamp']));
    }

    return $rows;
}

function fetchDashboardBandwidthUsage(PDO $pdo): array
{
    $sql = <<<SQL
    SELECT
        u.full_name,
        ROUND(
            (SUM(COALESCE(t.received_bytes, 0) + COALESCE(t.transmitted_bytes, 0)) / (1024.0 * 1024 * 1024)),
            2
        ) AS total_gb
    FROM internet_session AS s
    INNER JOIN user AS u ON u.user_id = s.user_id
    LEFT JOIN traffic_data AS t ON t.session_id = s.session_id
    GROUP BY u.user_id, u.full_name
    ORDER BY total_gb DESC;
    SQL;

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll() ?: [];
}

function fetchDashboardEventLog(PDO $pdo, int $limit = 5): array
{
    $sql = <<<SQL
    SELECT event_type, description, occurred_at
    FROM system_event
    ORDER BY occurred_at DESC
    LIMIT :limit;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll() ?: [];
}

function fetchActiveDeviceCount(PDO $pdo): int
{
    $stmt = $pdo->query(
        "SELECT COUNT(DISTINCT mac_address) AS total_devices
         FROM internet_session
         WHERE logout_timestamp IS NULL
            OR datetime(logout_timestamp) >= datetime('now', '-1 day')"
    );
    $row = $stmt->fetch();
    return $row ? (int)$row['total_devices'] : 0;
}

function fetchUserProfile(PDO $pdo, string $userId): ?array
{
    $sql = <<<SQL
    SELECT
        u.user_id,
        u.full_name,
        u.email,
        u.phone_number,
        u.access_code,
        u.user_role,
        u.account_creation_date,
        u.account_expiry_date,
        u.last_login_timestamp,
        r.role_name,
        r.role_description,
        status.status_code AS current_status,
        status.changed_at  AS status_changed_at
    FROM user AS u
    INNER JOIN user_role AS r ON r.role_id = u.user_role
    LEFT JOIN user_current_status AS status ON status.user_id = u.user_id
    WHERE u.user_id = :user_id
    LIMIT 1;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch();

    return $result ?: null;
}

function fetchUserBalances(PDO $pdo, string $userId): array
{
    $sql = <<<SQL
    SELECT
        b.speed_queue_id,
        b.monetary_balance,
        b.last_update_timestamp,
        q.queue_name,
        q.upload_speed_limit,
        q.download_speed_limit,
        q.bandwidth_quota
    FROM user_balance AS b
    INNER JOIN speed_queue AS q ON q.queue_id = b.speed_queue_id
    WHERE b.user_id = :user_id;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll() ?: [];
}

function fetchUserSessions(PDO $pdo, string $userId): array
{
    $sql = <<<SQL
    SELECT
        s.session_id,
        s.host_name,
        s.mac_address,
        s.login_timestamp,
        s.logout_timestamp,
        COALESCE(t.received_bytes, 0) AS received_bytes,
        COALESCE(t.transmitted_bytes, 0) AS transmitted_bytes,
        COALESCE(t.last_updated_at, s.logout_timestamp, s.login_timestamp) AS last_activity
    FROM internet_session AS s
    LEFT JOIN traffic_data AS t ON t.session_id = s.session_id
    WHERE s.user_id = :user_id
    ORDER BY s.login_timestamp DESC;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $rows = $stmt->fetchAll() ?: [];

    foreach ($rows as &$row) {
        $row['total_gb'] = round(
            ($row['received_bytes'] + $row['transmitted_bytes']) / (1024 * 1024 * 1024),
            3
        );
    }

    return $rows;
}

function fetchRecentPayments(PDO $pdo, int $limit = 5): array
{
    $sql = <<<SQL
    SELECT
        p.payment_id,
        p.user_id,
        u.full_name,
        p.payment_datetime,
        p.payment_method,
        p.amount_charged
    FROM payment AS p
    INNER JOIN user AS u ON u.user_id = p.user_id
    ORDER BY p.payment_datetime DESC
    LIMIT :limit;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll() ?: [];
}

function fetchBalanceAlerts(PDO $pdo, int $limit = 5): array
{
    $sql = <<<SQL
    SELECT
        b.user_id,
        u.full_name,
        b.monetary_balance,
        b.last_update_timestamp,
        q.queue_name
    FROM user_balance AS b
    INNER JOIN user AS u ON u.user_id = b.user_id
    INNER JOIN speed_queue AS q ON q.queue_id = b.speed_queue_id
    WHERE b.monetary_balance <= 0.00
    ORDER BY b.monetary_balance ASC, b.last_update_timestamp DESC
    LIMIT :limit;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll() ?: [];
}

function fetchDeviceUsage(PDO $pdo, int $limit = 5): array
{
    $sql = <<<SQL
    SELECT
        s.host_name,
        s.mac_address,
        ROUND(
            (COALESCE(t.received_bytes, 0) + COALESCE(t.transmitted_bytes, 0)) / (1024.0 * 1024 * 1024),
            2
        ) AS total_gb
    FROM internet_session AS s
    LEFT JOIN traffic_data AS t ON t.session_id = s.session_id
    ORDER BY total_gb DESC, s.login_timestamp DESC
    LIMIT :limit;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll() ?: [];

    return array_map(static function (array $row): array {
        $label = trim((string)($row['host_name'] ?? ''));
        if ($label === '') {
            $label = (string)($row['mac_address'] ?? '');
        }
        if ($label === '') {
            $label = 'Unknown Device';
        }
        return [
            'label' => $label,
            'total_gb' => (float)$row['total_gb'],
        ];
    }, $rows);
}

/**
 * Admin helpers
 * -------------
 * These helpers power admin views (user table, bandwidth usage, device list).
 */

function fetchAllUsersWithStatus(PDO $pdo): array
{
    $sql = <<<SQL
    SELECT
        u.user_id,
        u.full_name,
        u.email,
        u.user_role,
        COALESCE(s.status_code, 'UNKNOWN') AS status_code
    FROM user AS u
    LEFT JOIN user_current_status AS s ON s.user_id = u.user_id
    ORDER BY u.user_id;
    SQL;

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll() ?: [];
}

function fetchPerUserBandwidthSummary(PDO $pdo): array
{
    $sql = <<<SQL
    SELECT
        u.user_id,
        u.full_name,
        ROUND(
            (SUM(COALESCE(t.received_bytes, 0) + COALESCE(t.transmitted_bytes, 0)) / (1024.0 * 1024 * 1024)),
            2
        ) AS used_gb,
        MAX(COALESCE(q.bandwidth_quota, 0)) AS allocated_gb
    FROM user AS u
    LEFT JOIN internet_session AS s ON s.user_id = u.user_id
    LEFT JOIN traffic_data AS t ON t.session_id = s.session_id
    LEFT JOIN user_balance AS b ON b.user_id = u.user_id
    LEFT JOIN speed_queue AS q ON q.queue_id = b.speed_queue_id
    GROUP BY u.user_id, u.full_name
    ORDER BY used_gb DESC;
    SQL;

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll() ?: [];

    foreach ($rows as &$row) {
        $used = (float)($row['used_gb'] ?? 0.0);
        $allocated = (float)($row['allocated_gb'] ?? 0.0);

        if ($allocated > 0.0) {
            $percent = ($used / $allocated) * 100.0;
            $row['usage_percent'] = round($percent, 1);
        } else {
            $row['usage_percent'] = null;
        }

        $percentValue = $row['usage_percent'];
        if ($percentValue === null) {
            $row['usage_status'] = 'Unknown';
        } elseif ($percentValue >= 90.0) {
            $row['usage_status'] = 'Critical';
        } elseif ($percentValue >= 75.0) {
            $row['usage_status'] = 'Warning';
        } else {
            $row['usage_status'] = 'Normal';
        }
    }

    return $rows;
}

function fetchDeviceStatusList(PDO $pdo, int $limit = 50): array
{
    $sql = <<<SQL
    SELECT
        s.session_id,
        s.user_id,
        u.full_name,
        s.host_name,
        s.ip_address,
        s.login_timestamp,
        s.logout_timestamp,
        COALESCE(t.last_updated_at, s.logout_timestamp, s.login_timestamp) AS last_activity
    FROM internet_session AS s
    INNER JOIN user AS u ON u.user_id = s.user_id
    LEFT JOIN traffic_data AS t ON t.session_id = s.session_id
    ORDER BY last_activity DESC
    LIMIT :limit;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll() ?: [];

    $now = time();
    foreach ($rows as &$row) {
        $logout = $row['logout_timestamp'] ?? null;
        $lastActivity = $row['last_activity'] ?? null;

        if ($logout === null) {
            $row['status'] = 'Online';
        } else {
            $row['status'] = 'Offline';
        }

        if ($lastActivity !== null) {
            $timestamp = strtotime((string)$lastActivity) ?: $now;
            $diffSeconds = max(0, $now - $timestamp);

            if ($diffSeconds < 60) {
                $row['last_seen_label'] = 'Just now';
            } elseif ($diffSeconds < 3600) {
                $minutes = (int)floor($diffSeconds / 60);
                $row['last_seen_label'] = $minutes . ' mins ago';
            } elseif ($diffSeconds < 86400) {
                $hours = (int)floor($diffSeconds / 3600);
                $row['last_seen_label'] = $hours . ' hours ago';
            } else {
                $days = (int)floor($diffSeconds / 86400);
                $row['last_seen_label'] = $days . ' days ago';
            }
        } else {
            $row['last_seen_label'] = 'Unknown';
        }
    }

    return $rows;
}
