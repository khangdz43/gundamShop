<?php
require_once 'includes/auth.php';
requireLogin();

$user = getCurrentUser($conn);
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';

    if (!empty($newPass)) {
        if (strlen($newPass) < 7) {
            $message = 'Mật khẩu mới phải có ít nhất 7 ký tự';
            $messageType = 'error';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!password_verify($currentPass, $row['password'])) {
                $message = 'Mật khẩu hiện tại không đúng';
                $messageType = 'error';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $fullName, $phone, $address, $hash, $user['id']);
                $stmt->execute();
                $stmt->close();
                $message = 'Cập nhật hồ sơ và mật khẩu thành công';
            }
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fullName, $phone, $address, $user['id']);
        $stmt->execute();
        $stmt->close();
        $message = 'Cập nhật hồ sơ thành công';
    }
    $user = getCurrentUser($conn);
}

$pageTitle = 'Hồ sơ - Gundam Store';
include 'includes/header.php';
?>

<div class="container" style="max-width:600px">
    <h1 class="page-title">HỒ SƠ CÁ NHÂN</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
            <hr style="border-color:var(--border-color);margin:24px 0">
            <h3 style="margin:0 0 16px;font-size:1rem;color:var(--text-gray)">Đổi mật khẩu (để trống nếu không đổi)</h3>
            <div class="form-group">
                <label>Mật khẩu hiện tại</label>
                <input type="password" name="current_password" class="form-control">
            </div>
            <div class="form-group">
                <label>Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control" minlength="7">
            </div>
            <button type="submit" class="btn btn-blue"><i class="fas fa-save"></i> Lưu thay đổi</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
