<?php
require_once '../includes/auth.php';
requireAdmin();

$basePath = '../';

// Xử lý form actions (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax_action'] === 'toggle_status') {
        $uid = (int)$_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }
    exit;
}

// Lấy danh sách người dùng
// Updated query without 'position' column
$sql = "SELECT id, username, email, full_name, phone, role, is_active, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Thống kê
$stats = [];
$r = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'"); $stats['users'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='employee'"); $stats['employees'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='admin'"); $stats['admins'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_active=1"); $stats['active'] = $r->fetch_assoc()['c'];

$positionLabels = [
    'admin'          => ['label' => 'Quản trị viên', 'color' => '#1f5fff', 'icon' => 'fas fa-crown'],
    'order_manager'  => ['label' => 'QL Đơn hàng',   'color' => '#28a745', 'icon' => 'fas fa-shopping-bag'],
    'return_manager' => ['label' => 'QL Đổi trả',    'color' => '#ffc107', 'icon' => 'fas fa-undo'],
    'staff'          => ['label' => 'Nhân viên',      'color' => '#17a2b8', 'icon' => 'fas fa-user-tie'],
];

$pageTitle = 'Quản lý User - Gundam Store';
include '../includes/header.php';
?>

<div class="container">
    <h1 class="page-title">QUẢN LÝ NGƯỜI DÙNG</h1>

    <!-- Stats -->
    <div class="admin-stats" style="margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-users"></i> Khách hàng</div>
            <div class="stat-value"><?php echo $stats['users']; ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label"><i class="fas fa-user-tie"></i> Nhân viên</div>
            <div class="stat-value"><?php echo $stats['employees']; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-crown"></i> Admin</div>
            <div class="stat-value"><?php echo $stats['admins']; ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label"><i class="fas fa-check-circle"></i> Đang hoạt động</div>
            <div class="stat-value"><?php echo $stats['active']; ?></div>
        </div>
    </div>

    <!-- Actions -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="add_user.php" class="btn btn-blue"><i class="fas fa-user-plus"></i> Thêm nhân viên</a>
            <a href="export_excel.php?type=users" class="btn btn-gray"><i class="fas fa-file-excel"></i> Xuất Excel</a>
        </div>
        <div>
            <input type="text" id="userSearch" placeholder="🔍 Tìm kiếm..." 
                   style="padding:9px 14px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;color:var(--text-main);font-size:0.9rem;width:220px;"
                   oninput="filterUsers(this.value)">
        </div>
    </div>

    <!-- Bảng phân quyền -->
    <div class="card" style="margin-bottom:20px;padding:16px 20px;">
        <h3 style="margin:0 0 12px;font-size:1rem;color:var(--text-muted);">
            <i class="fas fa-info-circle" style="color:#1f5fff;"></i> Bảng phân quyền chức vụ
        </h3>
        <div style="overflow-x:auto;">
        <table class="data-table" style="font-size:0.82rem;">
            <thead>
                <tr>
                    <th>Chức vụ</th><th>Dashboard</th><th>Sản phẩm</th><th>Đơn hàng</th><th>Đổi trả</th><th>QL Users</th><th>Gửi TB</th><th>AI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $permTable = [
                    ['Quản trị viên (admin)',    '✅','✅','✅','✅','✅','✅','✅'],
                    ['QL Đơn hàng (order_manager)','✅','❌','✅','❌','❌','❌','❌'],
                    ['QL Đổi trả (return_manager)','✅','❌','❌','✅','❌','❌','❌'],
                    ['Nhân viên (staff)',          '✅','❌','✅','✅','❌','❌','❌'],
                ];
                foreach ($permTable as $row):
                    $cols = array_slice($row, 1);
                    ?>
                <tr>
                    <td style="font-weight:600;color:var(--text-main);"><?php echo $row[0]; ?></td>
                    <?php foreach ($cols as $cell): ?>
                    <td style="text-align:center;font-size:1rem;"><?php echo $cell; ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card" style="overflow-x:auto;padding:0;">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Email</th>
                    <th>Vai trò / Chức vụ</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): 
                    $pos = $row['position'] ?? null;
                    $posInfo = $positionLabels[$pos] ?? null;
                ?>
                <tr class="user-row" data-search="<?php echo strtolower(htmlspecialchars($row['username'] . ' ' . $row['email'] . ' ' . $row['full_name'])); ?>">
                    <td style="color:var(--text-muted);">#<?php echo $row['id']; ?></td>
                    <td>
                        <strong style="color:var(--text-main);"><?php echo htmlspecialchars($row['username']); ?></strong>
                        <?php if ($row['full_name']): ?>
                        <br><small style="color:var(--text-muted);"><?php echo htmlspecialchars($row['full_name']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--text-muted);font-size:0.88rem;"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php if ($row['role'] === 'user'): ?>
                            <span style="color:#28a745;font-size:0.85rem;"><i class="fas fa-user"></i> Khách hàng</span>
                        <?php elseif ($posInfo): ?>
                            <span style="background:<?php echo $posInfo['color']; ?>22;color:<?php echo $posInfo['color']; ?>;padding:4px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;">
                                <i class="<?php echo $posInfo['icon']; ?>"></i> <?php echo $posInfo['label']; ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#ffc107;font-size:0.85rem;"><i class="fas fa-user-cog"></i> <?php echo ucfirst($row['role']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['role'] !== 'admin'): ?>
                        <span id="status-<?php echo $row['id']; ?>" 
                              onclick="toggleStatus(<?php echo $row['id']; ?>)"
                              style="cursor:pointer;padding:4px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;
                              background:<?php echo $row['is_active'] ? 'rgba(40,167,69,0.15)' : 'rgba(220,53,69,0.15)'; ?>;
                              color:<?php echo $row['is_active'] ? '#28a745' : '#dc3545'; ?>;">
                            <i class="fas fa-circle" style="font-size:0.5rem;"></i>
                            <?php echo $row['is_active'] ? 'Hoạt động' : 'Bị khóa'; ?>
                        </span>
                        <?php else: ?>
                        <span style="color:#1f5fff;font-size:0.78rem;"><i class="fas fa-shield-alt"></i> Admin</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--text-muted);font-size:0.82rem;"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-gray btn-sm" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($row['role'] !== 'admin'): ?>
                            <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm" 
                               style="background:rgba(220,53,69,0.15);color:#dc3545;" 
                               onclick="return confirm('Xóa người dùng này?')" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterUsers(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.user-row').forEach(function(row) {
        var search = row.dataset.search || '';
        row.style.display = search.includes(q) ? '' : 'none';
    });
}

function toggleStatus(userId) {
    var span = document.getElementById('status-' + userId);
    var isActive = span.textContent.trim().includes('Hoạt động');
    
    if (!confirm(isActive ? 'Khóa tài khoản này?' : 'Mở khóa tài khoản này?')) return;
    
    var basePath = document.body.dataset.basePath || '';
    var fd = new FormData();
    fd.append('ajax_action', 'toggle_status');
    fd.append('user_id', userId);
    
    fetch(basePath + 'admin/users.php', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (data.success) {
            var newActive = !isActive;
            span.style.background = newActive ? 'rgba(40,167,69,0.15)' : 'rgba(220,53,69,0.15)';
            span.style.color      = newActive ? '#28a745' : '#dc3545';
            span.innerHTML        = '<i class="fas fa-circle" style="font-size:0.5rem;"></i> ' + (newActive ? 'Hoạt động' : 'Bị khóa');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
