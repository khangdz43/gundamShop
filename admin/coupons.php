<?php
require_once '../includes/auth.php';
requireAdmin();
ensureCouponsTable($conn);

$basePath = '../';
$flash = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            setFlash('coupon', 'Đã xóa mã giảm giá');
            redirect('coupons.php');
        }
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("UPDATE coupons SET is_active = IF(is_active=1,0,1) WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        setFlash('coupon', 'Đã cập nhật trạng thái mã');
        redirect('coupons.php');
    }

    $code = strtoupper(trim($_POST['code'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $discountType = in_array($_POST['discount_type'] ?? '', ['percent', 'fixed'], true) ? $_POST['discount_type'] : 'percent';
    $discountValue = (float)($_POST['discount_value'] ?? 0);
    $minOrder = (float)($_POST['min_order'] ?? 0);
    $maxUses = ($_POST['max_uses'] ?? '') !== '' ? (int)$_POST['max_uses'] : null;
    $startsAt = trim($_POST['starts_at'] ?? '') ?: null;
    $expiresAt = trim($_POST['expires_at'] ?? '') ?: null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($code === '') $errors[] = 'Vui lòng nhập mã giảm giá';
    if ($discountValue <= 0) $errors[] = 'Giá trị giảm phải lớn hơn 0';
    if ($discountType === 'percent' && $discountValue > 100) $errors[] = 'Giảm % tối đa 100';

    if (empty($errors)) {
        if ($maxUses === null) {
            $stmt = $conn->prepare("INSERT INTO coupons (code, description, discount_type, discount_value, min_order, max_uses, starts_at, expires_at, is_active) VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?)");
            $stmt->bind_param('sssddssi', $code, $description, $discountType, $discountValue, $minOrder, $startsAt, $expiresAt, $isActive);
        } else {
            $stmt = $conn->prepare("INSERT INTO coupons (code, description, discount_type, discount_value, min_order, max_uses, starts_at, expires_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssddissi', $code, $description, $discountType, $discountValue, $minOrder, $maxUses, $startsAt, $expiresAt, $isActive);
        }
        if ($stmt->execute()) {
            $stmt->close();
            setFlash('coupon', 'Phát hành mã giảm giá thành công: ' . $code);
            redirect('coupons.php');
        }
        $errors[] = 'Mã đã tồn tại hoặc lỗi lưu: ' . $stmt->error;
        $stmt->close();
    }
}

$coupons = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$flash = getFlash('coupon');
$pageTitle = __('coupons') . ' - Admin';
include '../includes/header.php';
?>

<div class="container">
    <?php if ($flash): ?><div class="alert alert-success"><?php echo htmlspecialchars($flash['message']); ?></div><?php endif; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>

    <h1 class="page-title"><i class="fas fa-ticket-alt"></i> <?php echo __('coupons'); ?></h1>

    <div class="checkout-grid">
        <div class="card">
            <h2 style="margin-top:0"><i class="fas fa-plus-circle"></i> Phát hành mã mới</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label>Mã giảm giá *</label>
                    <input type="text" name="code" class="form-control" placeholder="VD: GUNDAM10" required style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <input type="text" name="description" class="form-control" placeholder="Giảm 10% cho đơn từ 500k">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label>Loại giảm</label>
                        <select name="discount_type" class="form-control">
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Số tiền cố định (₫)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Giá trị *</label>
                        <input type="number" name="discount_value" class="form-control" min="1" step="0.01" required>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label>Đơn tối thiểu (₫)</label>
                        <input type="number" name="min_order" class="form-control" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>Số lần dùng tối đa</label>
                        <input type="number" name="max_uses" class="form-control" min="1" placeholder="Không giới hạn">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label>Bắt đầu</label>
                        <input type="datetime-local" name="starts_at" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Hết hạn</label>
                        <input type="datetime-local" name="expires_at" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" checked> Kích hoạt ngay
                    </label>
                </div>
                <button type="submit" class="btn btn-red" style="width:100%"><i class="fas fa-paper-plane"></i> Phát hành mã</button>
            </form>
        </div>

        <div class="card" style="overflow-x:auto;">
            <h2 style="margin-top:0"><i class="fas fa-list"></i> Danh sách mã (<?php echo count($coupons); ?>)</h2>
            <?php if (empty($coupons)): ?>
                <p style="color:var(--text-muted);">Chưa có mã giảm giá nào.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Giảm</th>
                        <th>Đã dùng</th>
                        <th>Hết hạn</th>
                        <th>TT</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $c): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($c['code']); ?></strong>
                            <?php if ($c['description']): ?><br><small style="color:var(--text-muted)"><?php echo htmlspecialchars($c['description']); ?></small><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($c['discount_type'] === 'percent'): ?>
                                <?php echo (float)$c['discount_value']; ?>%
                            <?php else: ?>
                                <?php echo formatPrice($c['discount_value']); ?>
                            <?php endif; ?>
                            <?php if ((float)$c['min_order'] > 0): ?><br><small>Từ <?php echo formatPrice($c['min_order']); ?></small><?php endif; ?>
                        </td>
                        <td><?php echo (int)$c['used_count']; ?><?php echo $c['max_uses'] ? ' / ' . (int)$c['max_uses'] : ''; ?></td>
                        <td><?php echo $c['expires_at'] ? date('d/m/Y', strtotime($c['expires_at'])) : '—'; ?></td>
                        <td>
                            <span class="status-badge <?php echo $c['is_active'] ? 'status-confirmed' : 'status-cancelled'; ?>">
                                <?php echo $c['is_active'] ? 'Active' : 'Off'; ?>
                            </span>
                        </td>
                        <td style="white-space:nowrap;">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <button type="submit" class="btn btn-gray btn-sm" title="Bật/tắt"><i class="fas fa-power-off"></i></button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa mã này?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <button type="submit" class="btn btn-red btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
