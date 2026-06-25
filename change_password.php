<?php
require_once 'includes/auth.php';

requireLogin();

$message = '';
$message_type = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId = (int)getUserId();

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        $message = 'Mật khẩu hiện tại không đúng.';
    } elseif (strlen($newPassword) < 7) {
        $message = 'Mật khẩu mới phải có ít nhất 7 ký tự.';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Mật khẩu xác nhận không khớp.';
    } else {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, remember_token = NULL, remember_expires = NULL WHERE id = ?");
        $stmt->bind_param('si', $hash, $userId);
        if ($stmt->execute()) {
            $message = 'Đổi mật khẩu thành công.';
            $message_type = 'success';
            clearRememberMe($conn, $userId);
        } else {
            $message = 'Không thể đổi mật khẩu lúc này. Vui lòng thử lại.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - Gundam Store</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
    <link rel="stylesheet" href="assets/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <i class="fas fa-shield-alt"></i>
            <span>Gundam Store HUMG</span>
        </div>
        <h1>Đổi mật khẩu</h1>
        <p class="auth-subtitle">Cập nhật mật khẩu định kỳ để bảo vệ tài khoản và đơn hàng của bạn.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="current_password">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="new_password">Mật khẩu mới</label>
                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Tối thiểu 7 ký tự" required minlength="7">
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="7">
            </div>
            <button type="submit" class="btn btn-blue" style="width:100%">Đổi mật khẩu</button>
        </form>

        <div class="auth-link">
            <p><a href="profile.php">Quay lại hồ sơ</a></p>
            <p><a href="index.php">Về trang chủ</a></p>
        </div>
    </div>
</div>
</body>
</html>
