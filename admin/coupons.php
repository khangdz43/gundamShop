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

    // Nhận toàn bộ dữ liệu phân rã Thời gian + Giờ giấc từ Request
    $startDay   = $_POST['start_day'] ?? '';
    $startMonth = $_POST['start_month'] ?? '';
    $startYear  = $_POST['start_year'] ?? '';
    $startHour  = $_POST['start_hour'] ?? '';
    $startMin   = $_POST['start_min'] ?? '';

    // Mặc định giây bắt đầu là 00 nếu không được truyền
    $startSec   = '00'; 

    $endDay     = $_POST['end_day'] ?? '';
    $endMonth   = $_POST['end_month'] ?? '';
    $endYear    = $_POST['end_year'] ?? '';
    $endHour    = $_POST['end_hour'] ?? '';
    $endMin     = $_POST['end_min'] ?? '';

    // Mặc định giây kết thúc là 59 để tối ưu biên độ thời gian trong ngày
    $endSec     = '59'; 

    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($code === '') $errors[] = 'Vui lòng nhập mã giảm giá';
    if ($discountValue <= 0) $errors[] = 'Giá trị giảm phải lớn hơn 0';
    if ($discountType === 'percent' && $discountValue > 100) $errors[] = 'Giảm % tối đa 100';

    // Xử lý đóng gói chuỗi DATETIME hoàn chỉnh (YYYY-MM-DD HH:MM:SS)
    $startsAt = null;
    if ($startDay && $startMonth && $startYear && $startHour !== '' && $startMin !== '') {
        $startsAtStr = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $startYear, $startMonth, $startDay, $startHour, $startMin, $startSec);
        if (strtotime($startsAtStr)) {
            $startsAt = $startsAtStr;
        }
    }

    $expiresAt = null;
    if ($endDay && $endMonth && $endYear && $endHour !== '' && $endMin !== '') {
        $expiresAtStr = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $endYear, $endMonth, $endDay, $endHour, $endMin, $endSec);
        if (strtotime($expiresAtStr)) {
            $expiresAt = $expiresAtStr;
        }
    }

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
                
                <div class="form-group">
                    <label>Chọn nhanh thời gian áp dụng</label>
                    <select id="couponTimePreset" class="form-control">
                        <option value="">Tùy chỉnh thủ công</option>
                        <option value="today">Hôm nay</option>
                        <option value="7days">7 ngày</option>
                        <option value="30days">30 ngày</option>
                        <option value="90days">90 ngày</option>
                        <option value="365days">1 năm</option>
                    </select>
                </div>

                <!-- BỘ Ô SELECT TÍNH CẢ GIỜ VÀ PHÚT: BẮT ĐẦU -->
                <div class="form-group">
                    <label style="font-weight:600; color:var(--text-color);">Thời gian bắt đầu hiệu lực</label>
                    <div style="display:flex; gap:6px; flex-wrap: wrap;">
                        <select name="start_day" class="form-control" style="flex:1; min-width:65px;">
                            <option value="">Ngày</option>
                            <?php for($d=1; $d<=31; $d++) echo "<option value='$d'>".sprintf('%02d',$d)."</option>"; ?>
                        </select>
                        <select name="start_month" class="form-control" style="flex:1; min-width:65px;">
                            <option value="">Tháng</option>
                            <?php for($m=1; $m<=12; $m++) echo "<option value='$m'>".sprintf('%02d',$m)."</option>"; ?>
                        </select>
                        <select name="start_year" class="form-control" style="flex:1.2; min-width:80px;">
                            <option value="">Năm</option>
                            <?php for($y=2024; $y<=2032; $y++) echo "<option value='$y'>$y</option>"; ?>
                        </select>
                        <span style="align-self:center; font-weight:bold; padding:0 2px;">-</span>
                        <select name="start_hour" class="form-control" style="flex:1; min-width:65px;">
                            <option value="">Giờ</option>
                            <?php for($h=0; $h<=23; $h++) echo "<option value='$h'>".sprintf('%02d',$h)."</option>"; ?>
                        </select>
                        <select name="start_min" class="form-control" style="flex:1; min-width:65px;">
                            <option value="">Phút</option>
                            <?php for($i=0; $i<=55; $i+=5) echo "<option value='$i'>".sprintf('%02d',$i)."</option>"; // Bước nhảy 5 phút cho gọn ?>
                        </select>
                    </div>
                </div>

                <!-- BỘ Ô SELECT TÍNH CẢ GIỜ VÀ PHÚT: HẾT HẠN -->
                <div class="form-group">
                    <label style="font-weight:600; color:var(--text-color);">Thời gian hết hạn mã</label>
                    <div style="display:flex; gap:6px; flex-wrap: wrap;">
                        <select name="end_day" class="form-control" style="flex:1; min-width:65px;">
                            <option value="">Ngày</option>
                            <?php for($d=1; $d<=31; $d++) echo "<option value='$d'>".sprintf('%02d',$d)."</option>"; ?>
                        </select>
                        <select name="end_month" class="form-control" style="flex:1; min-width:65px;">
                            <option value="">Tháng</option>
                            <?php for($m=1; $m<=12; $m++) echo "<option value='$m'>".sprintf('%02d',$m)."</option>"; ?>
                        </select>
                        <select name="end_year" class="form-control" style="flex:1.2; min-width:80px;">
                            <option value="">Năm</option>
                            <?php for($y=2024; $y<=2032; $y++) echo "<option value='$y'>$y</option>"; ?>
                        </select>
                        <span style="align-self:center; font-weight:bold; padding:0 2px;">-</span>
                        <select name="end_hour" class="form-control" style="flex:1; min-width:65px;">
                            <option value="">Giờ</option>
                            <?php for($h=0; $h<=23; $h++) echo "<option value='$h'>".sprintf('%02d',$h)."</option>"; ?>
                        </select>
                        <select name="end_min" class="form-control" style="flex:1; min-width:65px;">
                            <option value="">Phút</option>
                            <?php for($i=0; $i<=59; $i++) echo "<option value='$i'>".sprintf('%02d',$i)."</option>"; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" checked> Kích hoạt ngay
                    </label>
                </div>
                <button type="submit" class="btn btn-blue" style="width:100%"><i class="fas fa-paper-plane"></i> Phát hành mã</button>
            </form>
        </div>

        <!-- DANH SÁCH MÃ GIẢM GIÁ -->
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
                        <th>Hiệu lực</th>
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
                        <td>
                            <div><strong>Bắt đầu:</strong> <?php echo $c['starts_at'] ? date('d/m/Y H:i:s', strtotime($c['starts_at'])) : '—'; ?></div>
                            <div><strong>Hết hạn:</strong> <?php echo $c['expires_at'] ? date('d/m/Y H:i:s', strtotime($c['expires_at'])) : '—'; ?></div>
                        </td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const presetSelect = document.getElementById('couponTimePreset');
    
    // Ánh xạ Dom tới các ô Select options thời gian bắt đầu
    const sDay   = document.querySelector('select[name="start_day"]');
    const sMonth = document.querySelector('select[name="start_month"]');
    const sYear  = document.querySelector('select[name="start_year"]');
    const sHour  = document.querySelector('select[name="start_hour"]');
    const sMin   = document.querySelector('select[name="start_min"]');
    
    // Ánh xạ Dom tới các ô Select options thời gian hết hạn
    const eDay   = document.querySelector('select[name="end_day"]');
    const eMonth = document.querySelector('select[name="end_month"]');
    const eYear  = document.querySelector('select[name="end_year"]');
    const eHour  = document.querySelector('select[name="end_hour"]');
    const eMin   = document.querySelector('select[name="end_min"]');

    if (!presetSelect || !sDay || !sMonth || !sYear || !sHour || !sMin || !eDay || !eMonth || !eYear || !eHour || !eMin) return;

    // Cập nhật giá trị đồng bộ lên UI bao gồm cả Giờ và Phút
    const updateSelectFields = (startDate, endDate) => {
        if (!startDate || !endDate) {
            sDay.value = ''; sMonth.value = ''; sYear.value = ''; sHour.value = ''; sMin.value = '';
            eDay.value = ''; eMonth.value = ''; eYear.value = ''; eHour.value = ''; eMin.value = '';
            return;
        }
        // Điền mốc bắt đầu
        sDay.value   = startDate.getDate();
        sMonth.value = startDate.getMonth() + 1;
        sYear.value  = startDate.getFullYear();
        sHour.value  = startDate.getHours();
        sMin.value   = Math.floor(startDate.getMinutes() / 5) * 5; // Làm tròn theo bước nhảy option 5 phút

        // Điền mốc kết thúc
        eDay.value   = endDate.getDate();
        eMonth.value = endDate.getMonth() + 1;
        eYear.value  = endDate.getFullYear();
        eHour.value  = endDate.getHours();
        eMin.value   = endDate.getMinutes();
    };

    presetSelect.addEventListener('change', function() {
        const preset = this.value;
        if (!preset) {
            updateSelectFields(null, null);
            return;
        }

        const now = new Date();
        const start = new Date(now);
        const end = new Date(now);

        // Quy ước chuẩn: Thời gian bắt đầu tính từ đầu ngày hôm nay (00:00:00)
        start.setHours(0, 0, 0, 0);

        switch (preset) {
            case 'today':
                end.setHours(23, 59, 0, 0);
                break;
            case '7days':
                end.setDate(end.getDate() + 7);
                end.setHours(23, 59, 0, 0);
                break;
            case '30days':
                end.setDate(end.getDate() + 30);
                end.setHours(23, 59, 0, 0);
                break;
            case '90days':
                end.setDate(end.getDate() + 90);
                end.setHours(23, 59, 0, 0);
                break;
            case '365days':
                end.setDate(end.getDate() + 365);
                end.setHours(23, 59, 0, 0);
                break;
        }

        updateSelectFields(start, end);
    });
});
</script>

<?php include '../includes/footer.php'; ?>