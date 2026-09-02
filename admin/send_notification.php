<?php
require_once '../includes/auth.php';
requireAdmin();

$basePath = '../';
$message = "";
$success = false;

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target'])) {
    $target = $_POST['target']; // 'all' or specific user ID
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {
        $message = "Vui lòng nhập tiêu đề và nội dung thông báo.";
    } else {
        // Khởi động Transaction để bảo đảm dữ liệu ghi đồng bộ vào cả 2 bảng
        $conn->begin_transaction();

        try {
            // Bước 1: Chèn nội dung cốt lõi vào bảng notifications
            $type = ($target === 'all') ? 'system' : 'personal';
            $stmt = $conn->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $content, $type);
            $stmt->execute();
            
            // Lấy ra ID tự động tăng vừa được sinh ra của thông báo này
            $notificationId = $conn->insert_id;
            $stmt->close();

            // Bước 2: Phân phối thông báo dựa trên Target được chọn
            if ($target === 'all') {
                // Lấy danh sách ID của tất cả người dùng có role là 'user'
                $res = $conn->query("SELECT id FROM users WHERE role = 'user'");
                $allUsers = $res->fetch_all(MYSQLI_ASSOC);

                if (!empty($allUsers)) {
                    $stmtLink = $conn->prepare("INSERT IGNORE INTO notification_users (user_id, notification_id) VALUES (?, ?)");
                    foreach ($allUsers as $u) {
                        $stmtLink->bind_param("ii", $u['id'], $notificationId);
                        $stmtLink->execute();
                    }
                    $stmtLink->close();
                }
                $message = "Đã phát hành thông báo hệ thống hàng loạt thành công!";
            } elseif ($target === 'vip') {
                // Gửi cho nhóm khách VIP có >= 5 đơn hàng
                $vipQuery = "SELECT u.id FROM users u JOIN orders o ON o.user_id = u.id WHERE u.role = 'user' AND o.status NOT IN ('cancelled') GROUP BY u.id HAVING COUNT(o.id) >= 5";
                $res = $conn->query($vipQuery);
                $vipUsers = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

                if (!empty($vipUsers)) {
                    $stmtLink = $conn->prepare("INSERT IGNORE INTO notification_users (user_id, notification_id) VALUES (?, ?)");
                    foreach ($vipUsers as $u) {
                        $stmtLink->bind_param("ii", $u['id'], $notificationId);
                        $stmtLink->execute();
                    }
                    $stmtLink->close();
                }
                $message = "Đã gửi thông báo VIP cho " . count($vipUsers) . " khách hàng.";
            } else {
                // Gửi đích danh cho 1 tài khoản người dùng cụ thể
                $targetUserId = (int)$target;
                $stmtLink = $conn->prepare("INSERT IGNORE INTO notification_users (user_id, notification_id) VALUES (?, ?)");
                $stmtLink->bind_param("ii", $targetUserId, $notificationId);
                $stmtLink->execute();
                $stmtLink->close();
                
                $message = "Đã gửi thông báo đích danh thành công!";
            }

            // Toàn bộ tiến trình không lỗi -> Xác nhận lưu vĩnh viễn vào ổ đĩa
            $conn->commit();
            $success = true;

        } catch (Exception $e) {
            // Có lỗi xảy ra trong block try -> Hoàn tác mọi thay đổi để tránh rác database
            $conn->rollback();
            $message = "Lỗi hệ thống khi phân phối thông báo: " . $e->getMessage();
        }
    }
}

// Handle delete notification request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification_id'])) {
    $deleteId = (int)$_POST['delete_notification_id'];
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM notification_users WHERE notification_id = ?");
        $stmt->bind_param("i", $deleteId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $message = "Xóa thông báo đã gửi thành công.";
        $success = true;
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Lỗi khi xóa thông báo: " . $e->getMessage();
        $success = false;
    }
}

// Fetch user list for selection dropdown
$usersResult = $conn->query("SELECT id, username, full_name FROM users WHERE role = 'user' ORDER BY username ASC");
$users = $usersResult ? $usersResult->fetch_all(MYSQLI_ASSOC) : [];

// Parse notification filters
$filterType = $_GET['filter_type'] ?? '';
$searchTerm = trim($_GET['search'] ?? '');
$whereClauses = [];
if ($filterType && in_array($filterType, ['system', 'personal'], true)) {
    $whereClauses[] = "n.type = '" . $conn->real_escape_string($filterType) . "'";
}
if ($searchTerm !== '') {
    $escapedSearch = $conn->real_escape_string($searchTerm);
    $whereClauses[] = "(n.title LIKE '%$escapedSearch%' OR n.message LIKE '%$escapedSearch%')";
}
$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = ' WHERE ' . implode(' AND ', $whereClauses);
}

// Fetch sent notifications
$notifications = [];
$notifResult = $conn->query("SELECT n.*, COUNT(nu.user_id) AS recipient_count FROM notifications n LEFT JOIN notification_users nu ON nu.notification_id = n.id $whereSql GROUP BY n.id ORDER BY n.created_at DESC");
if ($notifResult) {
    $notifications = $notifResult->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = 'Gửi thông báo - Gundam Store';
include '../includes/header.php';
?>

<!-- Đã mở rộng max-width lên 1200px cho rộng rãi -->
<div class="container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h1 class="page-title">GỬI THÔNG BÁO</h1>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>" style="margin-bottom: 20px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2 style="margin-top:0"><i class="fas fa-paper-plane"></i> Soạn thông báo</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="target">Người nhận</label>
                <select name="target" id="target" class="form-control" required>
                    <option value="all">Tất cả người dùng (Gửi hàng loạt - Mass Broadcast)</option>
                    <option value="vip">Khách VIP (Đã mua >= 5 đơn)</option>
                    <optgroup label="Người dùng cụ thể">
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>">
                                <?php echo htmlspecialchars($u['username']); ?> 
                                <?php echo $u['full_name'] ? '(' . htmlspecialchars($u['full_name']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
                <small style="display:block; margin-top:6px; color: var(--text-muted);">Khách VIP là những khách hàng mua ít nhất 5 đơn (không tính đơn đã hủy).</small>
            </div>

            <div class="form-group">
                <label for="title" class="required">Tiêu đề thông báo</label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="VD: Chương trình khuyến mãi hè 2026...">
            </div>

            <div class="form-group">
                <label for="content" class="required">Nội dung thông báo</label>
                <textarea id="content" name="content" class="form-control" rows="5" required placeholder="Nhập nội dung thông báo gửi đến người dùng..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                <a href="index.php" class="btn btn-gray">Hủy bỏ</a>
                <button type="submit" class="btn btn-blue"><i class="fas fa-paper-plane"></i> Gửi thông báo</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:30px;">
        <h2 style="margin-top:0"><i class="fas fa-bell"></i> Danh sách thông báo đã phát</h2>
        <form method="GET" action="" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-top:20px;">
            <div class="form-group" style="flex:1; min-width:220px;">
                <label for="search">Tìm kiếm</label>
                <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Tiêu đề, nội dung...">
            </div>
            <div class="form-group" style="width:200px;">
                <label for="filter_type">Loại thông báo</label>
                <select id="filter_type" name="filter_type" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="system" <?php echo $filterType === 'system' ? 'selected' : ''; ?>>Hệ thống</option>
                    <option value="personal" <?php echo $filterType === 'personal' ? 'selected' : ''; ?>>Đích danh</option>
                </select>
            </div>
            <div style="display:flex; gap:10px; margin-bottom:4px;">
                <button type="submit" class="btn btn-blue" style="height:40px; align-self:flex-end;">Lọc</button>
                <a href="send_notification.php" class="btn btn-gray" style="height:40px; align-self:flex-end;">Xóa bộ lọc</a>
            </div>
        </form>
        <?php if (empty($notifications)): ?>
            <p>Hiện chưa có thông báo nào được gửi.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <!-- Đã tăng min-width của bảng lên 1100px và phân bổ lại tỷ lệ cột -->
                <table class="data-table" style="width:100%; min-width:1100px; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th style="width: 220px;">Tiêu đề</th>
                            <th>Nội dung</th>
                            <th style="width: 100px; text-align: center;">Loại</th>
                            <th style="width: 110px; text-align: center;">Người nhận</th>
                            <th style="width: 150px; text-align: center;">Thời gian</th>
                            <th style="width: 90px; text-align: center;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notif): ?>
                        <tr>
                            <td>#<?php echo $notif['id']; ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($notif['title']); ?></td>
                            <td style="white-space: normal; word-break: break-word; color: var(--text-gray); line-height: 1.5;">
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; background: rgba(255,255,255,0.05);">
                                    <?php echo htmlspecialchars($notif['type']); ?>
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: bold;"><?php echo (int)$notif['recipient_count']; ?></td>
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.88rem;"><?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?></td>
                            <td style="text-align: center;">
                                <form method="POST" action="" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?');" style="margin:0;">
                                    <input type="hidden" name="delete_notification_id" value="<?php echo $notif['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding:6px 12px; font-size:0.85rem;">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>