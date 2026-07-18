<?php
require_once '../includes/auth.php';
requireAdmin();

$basePath = '../';
$message = "";
$success = false;

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    // Chuẩn bị câu lệnh chèn hàng loạt vào bảng liên kết trung gian
                    $stmtLink = $conn->prepare("INSERT INTO notification_users (user_id, notification_id) VALUES (?, ?)");
                    foreach ($allUsers as $u) {
                        $stmtLink->bind_param("ii", $u['id'], $notificationId);
                        $stmtLink->execute();
                    }
                    $stmtLink->close();
                }
                $message = "Đã phát hành thông báo hệ thống hàng loạt thành công!";
            } else {
                // Gửi đích danh cho 1 tài khoản người dùng cụ thể
                $targetUserId = (int)$target;
                $stmtLink = $conn->prepare("INSERT INTO notification_users (user_id, notification_id) VALUES (?, ?)");
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

// Fetch user list for selection dropdown
$usersResult = $conn->query("SELECT id, username, full_name FROM users WHERE role = 'user' ORDER BY username ASC");
$users = $usersResult->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Gửi thông báo - Gundam Store';
include '../includes/header.php';
?>

<div class="container" style="max-width: 800px; margin: 40px auto;">
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
                    <optgroup label="Người dùng cụ thể">
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>">
                                <?php echo htmlspecialchars($u['username']); ?> 
                                <?php echo $u['full_name'] ? '(' . htmlspecialchars($u['full_name']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <div class="form-group">
                <label for="title" class="required">Tiêu đề thông báo</label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="VD: Chương trình khuyến mãi hè 2026...">
            </div>

            <div class="form-group">
                <label for="content" class="required">Nội dung thông báo</label>
                <textarea id="content" name="content" class="form-control" rows="6" required placeholder="Nhập nội dung thông báo gửi đến người dùng..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                <a href="index.php" class="btn btn-gray">Hủy bỏ</a>
                <button type="submit" class="btn btn-blue"><i class="fas fa-paper-plane"></i> Gửi thông báo</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>