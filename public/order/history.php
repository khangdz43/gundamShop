<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if (isAdmin()) redirect('admin/orders.php');

$userId = getUserId();
$statusFilter = $_GET['status'] ?? '';
$monthFilter = $_GET['month'] ?? '';
$search = trim($_GET['search'] ?? '');

// Định nghĩa danh sách các trạng thái hợp lệ (Đã thêm 'returning')
$validStatuses = ['pending', 'processing', 'shipped', 'completed', 'returning', 'cancelled'];

// 1. Tối ưu hóa Over-fetching: Chỉ SELECT những trường thực sự cần hiển thị
$sql = "SELECT id, order_code, created_at, total, payment_method, status FROM orders WHERE user_id = ?";
$params = [$userId];
$types = 'i';

if ($statusFilter && in_array($statusFilter, $validStatuses, true)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

// Tối ưu hóa Index: Chuyển đổi lọc tháng từ DATE_FORMAT sang Range Query (Sargable)
if ($monthFilter && preg_match('/^\d{4}-\d{2}$/', $monthFilter)) {
    $startDate = $monthFilter . '-01 00:00:00';
    $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
    
    $sql .= " AND created_at >= ? AND created_at <= ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= 'ss';
}

if ($search !== '') {
    $sql .= " AND order_code LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// 2. Kỹ thuật Conditional Aggregation: Gộp 3 câu lệnh COUNT/SUM rời rạc vào duy nhất 1 câu Query
$firstDayOfThisMonth = date('Y-m-01 00:00:00');

$statsQuery = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status NOT IN ('cancelled', 'returning') THEN total ELSE 0 END) as total_spent,
        SUM(CASE WHEN status NOT IN ('cancelled', 'returning') AND created_at >= ? THEN total ELSE 0 END) as this_month_spent
    FROM orders 
    WHERE user_id = ?
";
$stmt = $conn->prepare($statsQuery);
$stmt->bind_param('si', $firstDayOfThisMonth, $userId);
$stmt->execute();
$statsResult = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalOrderCount = (int)($statsResult['total_orders'] ?? 0);
$totalSpent      = (float)($statsResult['total_spent'] ?? 0);
$thisMonthSpent  = (float)($statsResult['this_month_spent'] ?? 0);


// 3. Thống kê biểu đồ 6 tháng gần nhất (Loại bỏ các đơn huỷ và đơn đang hoàn trả khỏi doanh thu)
$sixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
           COUNT(*) as order_count,
           COALESCE(SUM(total), 0) as total
    FROM orders
    WHERE user_id = ? 
      AND status NOT IN ('cancelled', 'returning')
      AND created_at >= ?
    GROUP BY month
    ORDER BY month
");
$stmt->bind_param('is', $userId, $sixMonthsAgo);
$stmt->execute();
$monthlyStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$monthLabels = [
    '01' => 'T1', '02' => 'T2', '03' => 'T3', '04' => 'T4',
    '05' => 'T5', '06' => 'T6', '07' => 'T7', '08' => 'T8',
    '09' => 'T9', '10' => 'T10', '11' => 'T11', '12' => 'T12'
];

$pageTitle = __('my_orders') . ' - Gundam Store';
include __DIR__ . '/../../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">
    <h1 class="page-title"><?php echo strtoupper(__('my_orders')); ?></h1>

    <!-- Thống kê chi tiêu -->
    <div class="admin-stats" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-label"><i class="fas fa-wallet"></i> <?php echo __('total_spending'); ?></div>
            <div class="stat-value" style="font-size:1.3rem;"><?php echo formatPrice($totalSpent); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-calendar-alt"></i> <?php echo __('this_month'); ?></div>
            <div class="stat-value" style="font-size:1.3rem;"><?php echo formatPrice($thisMonthSpent); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-receipt"></i> <?php echo __('total_orders'); ?></div>
            <div class="stat-value"><?php echo $totalOrderCount; ?><?php if ($statusFilter || $monthFilter || $search): ?><small style="font-size:0.55em;color:var(--text-muted);display:block;"><?php echo sprintf(__('filter_results'), count($orders)); ?></small><?php endif; ?></div>
        </div>
    </div>

    <?php if (!empty($monthlyStats)): ?>
    <div class="card" style="margin-bottom:24px;">
        <h2 style="margin-top:0;font-size:1.1rem;"><i class="fas fa-chart-bar"></i> <?php echo __('monthly_spending'); ?></h2>
        <canvas id="spendingChart" height="100"></canvas>
    </div>
    <?php endif; ?>

    <!-- Bộ lọc -->
    <div class="card" style="margin-bottom:20px;padding:16px 20px;">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                <label style="font-size:0.85rem;color:var(--text-muted);"><?php echo __('search_order'); ?></label>
                <input type="text" name="search" class="form-control" placeholder="VD: GD250625..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group" style="margin:0;min-width:160px;">
                <label style="font-size:0.85rem;color:var(--text-muted);"><?php echo __('month'); ?></label>
                <select name="month" class="form-control">
                    <option value=""><?php echo __('all_months'); ?></option>
                    <?php for ($i = 0; $i < 12; $i++):
                        $m = date('Y-m', strtotime("-$i months"));
                        $parts = explode('-', $m);
                        $label = ($monthLabels[$parts[1]] ?? $parts[1]) . '/' . $parts[0];
                    ?>
                    <option value="<?php echo $m; ?>" <?php echo $monthFilter === $m ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:140px;">
                <label style="font-size:0.85rem;color:var(--text-muted);"><?php echo __('status'); ?></label>
                <select name="status" class="form-control">
                    <option value=""><?php echo __('all'); ?></option>
                    <?php foreach ($validStatuses as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo getOrderStatusLabel($s); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-blue btn-sm"><i class="fas fa-search"></i> <?php echo __('filter'); ?></button>
            <?php if ($search || $monthFilter || $statusFilter): ?>
            <a href="<?php echo getAppBasePath(); ?>orders.php" class="btn btn-gray btn-sm"><i class="fas fa-times"></i> <?php echo __('clear_filter'); ?></a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Thanh chuyển tab nhanh (Quick Filter) -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;justify-content:center;">
        <?php
        $filterParams = [];
        if ($search) $filterParams['search'] = $search;
        if ($monthFilter) $filterParams['month'] = $monthFilter;
        $basePath = getAppBasePath();
        $baseQuery = http_build_query($filterParams);
        $allUrl = $basePath . 'orders.php' . ($baseQuery ? '?' . $baseQuery : '');
        ?>
        <a href="<?php echo $allUrl; ?>" class="btn <?php echo !$statusFilter ? 'btn-blue' : 'btn-gray'; ?> btn-sm"><?php echo __('all'); ?></a>
        <?php foreach ($validStatuses as $s):
            $q = array_merge($filterParams, ['status' => $s]);
            $url = $basePath . 'orders.php?' . http_build_query($q);
        ?>
        <a href="<?php echo $url; ?>" class="btn <?php echo $statusFilter === $s ? 'btn-blue' : 'btn-gray'; ?> btn-sm"><?php echo getOrderStatusLabel($s); ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card" style="text-align:center;padding:50px">
            <i class="fas fa-receipt" style="font-size:3rem;color:var(--text-muted);margin-bottom:15px"></i>
            <p><?php echo ($statusFilter || $monthFilter || $search) ? __('no_orders_filter') : __('no_orders'); ?></p>
            <a href="<?php echo getAppBasePath(); ?>products.php" class="btn btn-blue" style="margin-top:15px"><?php echo __('shop_now'); ?></a>
        </div>
    <?php else: ?>
        <div class="card" style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo __('order_code'); ?></th>
                        <th><?php echo __('order_date'); ?></th>
                        <th><?php echo __('total'); ?></th>
                        <th><?php echo __('payment'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo formatPrice($order['total']); ?></td>
                        <td><?php echo $order['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản'; ?></td>
                        <td><span class="status-badge <?php echo getOrderStatusClass($order['status']); ?>"><?php echo getOrderStatusLabel($order['status']); ?></span></td>
                        <td><a href="<?php echo getAppBasePath(); ?>order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-blue btn-sm"><?php echo __('detail'); ?></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($monthlyStats)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isLight = document.documentElement.classList.contains('light-theme');
    const textColor = isLight ? '#334155' : '#e2e8f0';
    const gridColor = isLight ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.08)';

    const labels = <?php echo json_encode(array_map(function($m) use ($monthLabels) {
        $parts = explode('-', $m['month']);
        return ($monthLabels[$parts[1]] ?? $parts[1]) . '/' . $parts[0];
    }, $monthlyStats), JSON_UNESCAPED_UNICODE); ?>;

    const data = <?php echo json_encode(array_map(function($m) { return (float)$m['total']; }, $monthlyStats)); ?>;

    new Chart(document.getElementById('spendingChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Chi tiêu (₫)',
                data: data,
                backgroundColor: 'rgba(31, 95, 255, 0.7)',
                borderColor: '#1f5fff',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.parsed.y.toLocaleString('vi-VN') + ' ₫';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        callback: function(v) { return v.toLocaleString('vi-VN') + ' ₫'; }
                    },
                    grid: { color: gridColor }
                },
                x: {
                    ticks: { color: textColor },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>