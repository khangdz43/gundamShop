<?php
require_once '../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Verify user is logged in
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = getUserId();
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    try {
        // 1. Đếm số thông báo chưa đọc (cá nhân + broadcast)
        $stmt = $conn->prepare("
            SELECT COUNT(*) as c
            FROM notifications n
            WHERE (n.user_id = ? AND n.is_read = 0)
               OR (n.user_id IS NULL AND n.id NOT IN (
                      SELECT nr.notification_id FROM notification_reads nr WHERE nr.user_id = ?
                  ))
        ");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $unreadCount = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        // 2. Lấy 10 thông báo gần nhất (cá nhân + broadcast)
        $stmt = $conn->prepare("
            SELECT n.id, n.title, n.message, n.user_id, n.is_read, n.created_at,
                   (SELECT COUNT(*) FROM notification_reads nr
                    WHERE nr.notification_id = n.id AND nr.user_id = ?) AS broadcast_read
            FROM notifications n
            WHERE n.user_id = ? OR n.user_id IS NULL
            ORDER BY n.created_at DESC
            LIMIT 10
        ");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $notifs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Format danh sách
        $list = [];
        foreach ($notifs as $row) {
            // Broadcast: kiểm tra notification_reads; Cá nhân: kiểm tra is_read
            $isRead = ($row['user_id'] === null)
                ? ((int)$row['broadcast_read'] > 0 ? 1 : 0)
                : (int)$row['is_read'];
            $list[] = [
                'id'         => (int)$row['id'],
                'title'      => $row['title'],
                'message'    => $row['message'],
                'is_read'    => $isRead,
                'created_at' => date('d/m/Y H:i', strtotime($row['created_at']))
            ];
        }

        echo json_encode([
            'unread_count'  => $unreadCount,
            'notifications' => $list
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }

} elseif ($action === 'mark_read') {
    $notifId = (int)($_POST['notification_id'] ?? 0);
    if ($notifId <= 0) {
        echo json_encode(['error' => 'Invalid notification ID'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Kiểm tra loại thông báo (broadcast hay cá nhân)
    $stmt = $conn->prepare("SELECT user_id FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $notifId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        if ($result['user_id'] === null) {
            // Broadcast: ghi vào notification_reads
            $stmt = $conn->prepare("INSERT IGNORE INTO notification_reads (user_id, notification_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $notifId);
            $stmt->execute();
            $stmt->close();
        } else {
            // Cá nhân: cập nhật is_read = 1
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $notifId, $userId);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Notification not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Action not found'], JSON_UNESCAPED_UNICODE);
    exit;
}
