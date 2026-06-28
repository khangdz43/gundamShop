<?php
require_once '../includes/auth.php';
requireAdmin();

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $email     = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $role      = in_array($_POST['role'] ?? '', ['user','employee','admin']) ? $_POST['role'] : 'user';
    $position  = in_array($_POST['position'] ?? '', ['admin','order_manager','return_manager','staff']) ? $_POST['position'] : null;

    // Nếu role là user thì xóa position
    if ($role === 'user') $position = null;
    // Nếu role là admin thì position = admin
    if ($role === 'admin') $position = 'admin';

    if (strlen($password) < 7) {
        $message = 'Mật khẩu phải có ít nhất 7 ký tự';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = 'Username hoặc email đã tồn tại';
        } else {
            $hash  = password_hash($password, PASSWORD_DEFAULT);
            // Insert with position column (now exists in DB)
            $stmt2 = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, role, position) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssssss", $username, $hash, $email, $full_name, $phone, $role, $position);
            if ($stmt2->execute()) {
                $message = 'Thêm nhân viên/user thành công!';
                $success = true;
            } else {
                $message = 'Lỗi khi thêm: ' . $conn->error;
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

$basePath  = '../';
$pageTitle = 'Thêm User - Gundam Store';
include '../includes/header.php';
?>

<div class="container" style="max-width:580px;">
    <h1 class="page-title">THÊM NHÂN VIÊN / USER</h1>
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $success ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập <span style="color:#e74c3c">*</span></label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name" class="form-control">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Email <span style="color:#e74c3c">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" placeholder="0xxx...">
                </div>
            </div>
            <div class="form-group">
                <label>Mật khẩu <span style="color:#e74c3c">*</span></label>
                <input type="password" name="password" class="form-control" required minlength="7" placeholder="Ít nhất 7 ký tự">
            </div>
            
            <hr style="border-color:var(--border-color);margin:20px 0;">
            <h3 style="font-size:1rem;color:var(--text-muted);margin-bottom:16px;"><i class="fas fa-shield-alt" style="color:#1f5fff;"></i> Phân quyền</h3>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Loại tài khoản</label>
                    <select name="role" class="form-control" id="roleSelect" onchange="updatePositionVis()">
                        <option value="user">Khách hàng</option>
                        <option value="employee">Nhân viên</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>
                <div class="form-group" id="positionGroup">
                    <label>Chức vụ nhân viên</label>
                    <select name="position" class="form-control">
                        <option value="order_manager">Quản lý Đơn hàng</option>
                        <option value="return_manager">Quản lý Đổi trả</option>
                        <option value="staff">Nhân viên</option>
                    </select>
                </div>
            </div>
            
            <!-- Bảng quyền preview -->
            <div id="permPreview" style="background:rgba(31,95,255,0.05);border:1px solid rgba(31,95,255,0.2);border-radius:10px;padding:14px;margin-bottom:20px;font-size:0.85rem;color:var(--text-muted);">
                <strong style="color:var(--text-main);display:block;margin-bottom:8px;"><i class="fas fa-key" style="color:#1f5fff;"></i> Quyền truy cập:</strong>
                <span id="permList" style="line-height:1.8;"></span>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-red"><i class="fas fa-user-plus"></i> Tạo tài khoản</button>
                <a href="users.php" class="btn btn-gray">Quay lại</a>
            </div>
        </form>
    </div>
</div>

<script>
var permMap = {
    'user':           { label: 'Khách hàng - Không có quyền admin', perms: [] },
    'employee-order_manager':  { label: 'Quản lý Đơn hàng', perms: ['Dashboard', 'Xem & cập nhật Đơn hàng'] },
    'employee-return_manager': { label: 'Quản lý Đổi trả',  perms: ['Dashboard', 'Xem & xử lý Yêu cầu đổi trả'] },
    'employee-staff':          { label: 'Nhân viên',         perms: ['Dashboard', 'Xem Đơn hàng', 'Xem Đổi trả'] },
    'admin':          { label: 'Quản trị viên',  perms: ['Toàn quyền: Dashboard, Sản phẩm, Đơn hàng, Đổi trả, Users, Thông báo, AI'] },
};

function updatePositionVis() {
    var role = document.getElementById('roleSelect').value;
    var pg   = document.getElementById('positionGroup');
    pg.style.display = role === 'employee' ? '' : 'none';
    updatePermPreview();
}

function updatePermPreview() {
    var role = document.getElementById('roleSelect').value;
    var pos  = document.querySelector('[name="position"]').value;
    var key  = role === 'employee' ? 'employee-' + pos : role;
    var info = permMap[key] || permMap['user'];
    var list = info.perms.length > 0 
        ? info.perms.map(function(p){ return '<span style="background:rgba(31,95,255,0.15);color:#7da7ff;padding:2px 8px;border-radius:12px;margin:2px;display:inline-block;">✓ ' + p + '</span>'; }).join(' ')
        : '<span style="color:#aaa;">Không có quyền admin</span>';
    document.getElementById('permList').innerHTML = list;
}

document.querySelector('[name="position"]').addEventListener('change', updatePermPreview);
document.addEventListener('DOMContentLoaded', function() {
    updatePositionVis();
    updatePermPreview();
});
</script>

<?php include '../includes/footer.php'; ?>
