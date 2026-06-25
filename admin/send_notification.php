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
        if ($target === 'all') {
            // Mass notification: user_id = NULL
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (NULL, ?, ?)");
            $stmt->bind_param("ss", $title, $content);
            if ($stmt->execute()) {
                $message = "Đã gửi thông báo hàng loạt thành công!";
                $success = true;
            } else {
                $message = "Lỗi khi gửi thông báo: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Specific user notification: user_id = (int)$target
            $targetUserId = (int)$target;
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $targetUserId, $title, $content);
            if ($stmt->execute()) {
                $message = "Đã gửi thông báo thành công cho người dùng!";
                $success = true;
            } else {
                $message = "Lỗi khi gửi thông báo: " . $conn->error;
            }
            $stmt->close();
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
