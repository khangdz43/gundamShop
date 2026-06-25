<?php
require_once '../includes/auth.php';
requireAdmin();

$userId = (int)($_GET['id'] ?? 0);
$stmt   = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) redirect('users.php');

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $newPass   = $_POST['password'] ?? '';
    $role      = in_array($_POST['role'] ?? '', ['user','employee','admin']) ? $_POST['role'] : $user['role'];
    $position  = in_array($_POST['position'] ?? '', ['admin','order_manager','return_manager','staff']) ? $_POST['position'] : null;

    if ($role === 'user') $position = null;
    if ($role === 'admin') $position = 'admin';

    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $message = 'Username hoặc email đã được sử dụng';
        $msgType = 'error';
    } else {
        if (!empty($newPass)) {
            if (strlen($newPass) < 7) {
                $message = 'Mật khẩu phải có ít nhất 7 ký tự';
                $msgType = 'error';
            } else {
                $hash  = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("UPDATE users SET username=?, email=?, full_name=?, phone=?, password=?, role=?, position=? WHERE id=?");
                $stmt2->bind_param("sssssssi", $username, $email, $full_name, $phone, $hash, $role, $position, $userId);
                $stmt2->execute();
                $stmt2->close();
                $message = 'Cập nhật thành công!';
            }
        } else {
            $stmt2 = $conn->prepare("UPDATE users SET username=?, email=?, full_name=?, phone=?, role=?, position=? WHERE id=?");
            $stmt2->bind_param("ssssssi", $username, $email, $full_name, $phone, $role, $position, $userId);
            $stmt2->execute();
            $stmt2->close();
            $message = 'Cập nhật thành công!';
        }
        
        if ($message === 'Cập nhật thành công!') {
            $user = array_merge($user, compact('username', 'email', 'full_name', 'phone', 'role', 'position'));
        }
    }
    $stmt->close();
}

$basePath  = '../';
$pageTitle = 'Sửa User - Gundam Store';
include '../includes/header.php';
?>

<div class="container" style="max-width:580px;">
    <h1 class="page-title">SỬA TÀI KHOẢN</h1>
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Mật khẩu mới <small style="color:var(--text-muted);">(để trống nếu không đổi)</small></label>
                <input type="password" name="password" class="form-control" minlength="7">
            </div>

            <hr style="border-color:var(--border-color);margin:20px 0;">
            <h3 style="font-size:1rem;color:var(--text-muted);margin-bottom:16px;">
                <i class="fas fa-shield-alt" style="color:#1f5fff;"></i> Phân quyền
            </h3>

            <?php $isAdmin = ($user['role'] === 'admin'); ?>
            <?php if (!$isAdmin): ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Loại tài khoản</label>
                    <select name="role" class="form-control" id="roleSelect" onchange="updatePositionVis()">
                        <option value="user"     <?php echo $user['role']==='user'     ? 'selected' : ''; ?>>Khách hàng</option>
                        <option value="employee" <?php echo $user['role']==='employee' ? 'selected' : ''; ?>>Nhân viên</option>
                    </select>
                </div>
                <div class="form-group" id="positionGroup" style="<?php echo $user['role']!=='employee' ? 'display:none' : ''; ?>">
                    <label>Chức vụ</label>
                    <select name="position" class="form-control" onchange="updatePermPreview()">
                        <option value="order_manager"  <?php echo $user['position']==='order_manager'  ? 'selected' : ''; ?>>QL Đơn hàng</option>
                        <option value="return_manager" <?php echo $user['position']==='return_manager' ? 'selected' : ''; ?>>QL Đổi trả</option>
                        <option value="staff"          <?php echo $user['position']==='staff'          ? 'selected' : ''; ?>>Nhân viên</option>
                    </select>
                </div>
            </div>
            <div id="permPreview" style="background:rgba(31,95,255,0.05);border:1px solid rgba(31,95,255,0.2);border-radius:10px;padding:14px;margin-bottom:20px;font-size:0.85rem;">
                <strong style="color:var(--text-main);display:block;margin-bottom:8px;"><i class="fas fa-key" style="color:#1f5fff;"></i> Quyền truy cập:</strong>
                <span id="permList"></span>
            </div>
            <?php else: ?>
            <div style="background:rgba(31,95,255,0.08);border:1px solid rgba(31,95,255,0.3);border-radius:10px;padding:14px;margin-bottom:16px;">
                <i class="fas fa-crown" style="color:#1f5fff;"></i> <strong>Quản trị viên</strong> — Toàn quyền hệ thống
            </div>
            <input type="hidden" name="role" value="admin">
            <input type="hidden" name="position" value="admin">
            <?php endif; ?>
            
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-blue"><i class="fas fa-save"></i> Lưu thay đổi</button>
                <a href="users.php" class="btn btn-gray">Quay lại</a>
            </div>
        </form>
    </div>
</div>

<script>
var permMap = {
    'user':           [],
    'employee-order_manager':  ['Dashboard', 'Xem & cập nhật Đơn hàng'],
    'employee-return_manager': ['Dashboard', 'Xem & xử lý Đổi trả'],
    'employee-staff':          ['Dashboard', 'Xem Đơn hàng', 'Xem Đổi trả'],
};
function updatePositionVis() {
    var role = document.getElementById('roleSelect')?.value;
    var pg   = document.getElementById('positionGroup');
    if (pg) pg.style.display = role === 'employee' ? '' : 'none';
    updatePermPreview();
}
function updatePermPreview() {
    var role = document.getElementById('roleSelect')?.value;
    var pos  = document.querySelector('[name="position"]')?.value;
    if (!role) return;
    var key  = role === 'employee' ? 'employee-' + pos : role;
    var perms = permMap[key] || [];
    var list = perms.length > 0
        ? perms.map(function(p){ return '<span style="background:rgba(31,95,255,0.15);color:#7da7ff;padding:2px 8px;border-radius:12px;margin:2px;display:inline-block;">✓ ' + p + '</span>'; }).join(' ')
        : '<span style="color:#aaa;">Không có quyền admin</span>';
    var el = document.getElementById('permList');
    if (el) el.innerHTML = list;
}
document.addEventListener('DOMContentLoaded', function() {
    updatePositionVis();
});
document.querySelector('[name="position"]')?.addEventListener('change', updatePermPreview);
</script>

<?php include '../includes/footer.php'; ?>
