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
        // 1. Đếm số thông báo chưa đọc theo notification_users
        $stmt = $conn->prepare("
            SELECT COUNT(*) as c
            FROM notification_users nu
            WHERE nu.user_id = ? AND nu.is_read = 0
        ");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $unreadCount = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        // 2. Lấy 10 thông báo gần nhất của user từ notification_users JOIN notifications
        $stmt = $conn->prepare("
            SELECT n.id, n.title, n.message, n.type, n.created_at,
                   nu.is_read
            FROM notification_users nu
            JOIN notifications n ON nu.notification_id = n.id
            WHERE nu.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT 10
        ");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $notifs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Format danh sách
        $list = [];
        foreach ($notifs as $row) {
            $list[] = [
                'id'         => (int)$row['id'],
                'title'      => $row['title'],
                'message'    => $row['message'],
                'is_read'    => (int)$row['is_read'],
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

    // Cập nhật is_read = 1 trong notification_users cho user này
    $stmt = $conn->prepare("
        UPDATE notification_users
        SET is_read = 1, read_at = NOW()
        WHERE user_id = ? AND notification_id = ?
    ");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => $conn->error], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt->bind_param("ii", $userId, $notifId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0 || true) { // trả success dù row không tồn tại
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Notification not found'], JSON_UNESCAPED_UNICODE);
    }
    exit;

} elseif ($action === 'mark_all_read') {
    try {
        $stmt = $conn->prepare("
            UPDATE notification_users
            SET is_read = 1, read_at = NOW()
            WHERE user_id = ? AND is_read = 0
        ");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true, 'unread_count' => 0], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Action not found'], JSON_UNESCAPED_UNICODE);
    exit;
}
