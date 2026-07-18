<?php
require_once '../includes/auth.php';
requireEmployee();

$basePath = '../';

// 1. Gán giá trị và chuẩn hóa Input
$search       = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');

// Lấy 6 giá trị thời gian từ bộ lọc select options
$startDay   = $_GET['start_day'] ?? '';
$startMonth = $_GET['start_month'] ?? '';
$startYear  = $_GET['start_year'] ?? '';

$endDay     = $_GET['end_day'] ?? '';
$endMonth   = $_GET['end_month'] ?? '';
$endYear    = $_GET['end_year'] ?? '';

$whereClauses = [];
$params = [];
$paramTypes = "";

// 2. Xây dựng Dynamic Query an toàn bằng Prepared Statements
if ($search !== '') {
    $whereClauses[] = "(o.order_code LIKE ? OR u.username LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $paramTypes .= "ss";
}

// Khớp 5 trạng thái thực tế
if ($statusFilter !== 'all' && $statusFilter !== '') {
    $whereClauses[] = "o.status = ?";
    $params[] = $statusFilter;
    $paramTypes .= "s";
}

// Xử lý biến đổi logic 6 ô select thành khoảng ngày (Date Range Validation)
$startDate = null;
$endDate = null;

if ($startDay && $startMonth && $startYear) {
    // Đảm bảo tạo ra chuỗi định dạng YYYY-MM-DD hợp lệ
    $startDateStr = sprintf('%04d-%02d-%02d', $startYear, $startMonth, $startDay);
    if (strtotime($startDateStr)) {
        $whereClauses[] = "DATE(o.created_at) >= ?";
        $params[] = $startDateStr;
        $paramTypes .= "s";
        $startDate = $startDateStr;
    }
}

if ($endDay && $endMonth && $endYear) {
    $endDateStr = sprintf('%04d-%02d-%02d', $endYear, $endMonth, $endDay);
    if (strtotime($endDateStr)) {
        $whereClauses[] = "DATE(o.created_at) <= ?";
        $params[] = $endDateStr;
        $paramTypes .= "s";
        $endDate = $endDateStr;
    }
}

$whereSql = empty($whereClauses) ? '' : ' WHERE ' . implode(' AND ', $whereClauses);

// 3. TỐI ƯU STATS: Conditional Aggregation
$statsSql = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN o.status != 'cancelled' THEN o.total ELSE 0 END) as total_revenue,
        SUM(CASE WHEN o.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN o.status != 'completed' THEN 1 ELSE 0 END) as uncompleted_orders
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id
" . $whereSql;

$stmtStats = $conn->prepare($statsSql);
if (!empty($paramTypes)) {
    $stmtStats->bind_param($paramTypes, ...$params);
}
$stmtStats->execute();
$orderStatsRow = $stmtStats->get_result()->fetch_assoc();

$stats = [
    'orders'   => $orderStatsRow['total_orders'] ?? 0,
    'pending'  => $orderStatsRow['pending_orders'] ?? 0,
    'revenue'  => $orderStatsRow['total_revenue'] ?? 0
];
$soldCount   = $orderStatsRow['completed_orders'] ?? 0;
$unsoldCount = $orderStatsRow['uncompleted_orders'] ?? 0;

// Các chỉ số tĩnh độc lập
$r = $conn->query("SELECT COUNT(*) as c FROM products WHERE status='active'");
$stats['products'] = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'");
$stats['users'] = $r->fetch_assoc()['c'];

$reviewStats = getAverageReviewRating($conn);

// 4. Kết quả danh sách đơn hàng lọc (Tối đa 20 dòng)
$recentOrdersSql = "
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    " . $whereSql . " 
    ORDER BY o.created_at DESC 
    LIMIT 20
";

$stmtRecent = $conn->prepare($recentOrdersSql);
if (!empty($paramTypes)) {
    $stmtRecent->bind_param($paramTypes, ...$params);
}
$stmtRecent->execute();
$recentOrdersResult = $stmtRecent->get_result();
$recentOrders = $recentOrdersResult ? $recentOrdersResult->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Admin Dashboard - Gundam Store';
include '../includes/header.php';
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">
    <h1 class="page-title">DASHBOARD</h1>

    <!-- FORM FILTER GỒM 6 Ô SELECT TIỆN LỢI -->
    <form method="GET" class="card" style="margin-bottom:20px; display:flex; flex-direction:column; gap:16px;">
        
        <!-- Hàng 1: Tìm kiếm & Trạng thái -->
        <div style="display:flex; flex-wrap:wrap; gap:12px; width:100%;">
            <div style="flex:2; min-width:240px;">
                <label style="display:block; margin-bottom:6px; font-weight:600;">Tìm kiếm đơn hàng</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Mã đơn hoặc tên khách hàng...">
            </div>

            <div style="flex:1; min-width:180px;">
                <label style="display:block; margin-bottom:6px; font-weight:600;">Trạng thái đơn</label>
                <select name="status" class="form-control">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                    <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                    <option value="shipping" <?php echo $statusFilter === 'shipping' ? 'selected' : ''; ?>>Đang giao hàng</option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
            </div>
        </div>

        <!-- Hàng 2: Bộ lọc 6 ô Select Option cho Khoảng thời gian -->
        <div style="display:flex; flex-wrap:wrap; gap:16px; width:100%; align-items:end;">
            
            <!-- Nhóm Từ Ngày -->
            <div style="display:flex; gap:8px; flex:1; min-width:280px;">
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:gray;">Ngày bắt đầu</label>
                    <select name="start_day" class="form-control">
                        <option value="">Ngày</option>
                        <?php for($d=1; $d<=31; $d++): ?>
                            <option value="<?php echo $d; ?>" <?php echo (int)$startDay===$d ? 'selected' : ''; ?>><?php echo sprintf('%02d', $d); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:gray;">Tháng bắt đầu</label>
                    <select name="start_month" class="form-control">
                        <option value="">Tháng</option>
                        <?php for($m=1; $m<=12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo (int)$startMonth===$m ? 'selected' : ''; ?>><?php echo sprintf('%02d', $m); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div style="flex:1; min-width:80px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:gray;">Năm bắt đầu</label>
                    <select name="start_year" class="form-control">
                        <option value="">Năm</option>
                        <?php for($y=2024; $y<=2030; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo (int)$startYear===$y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div style="font-weight:bold; padding-bottom:10px; text-align:center;">đến</div>

            <!-- Nhóm Đến Ngày -->
            <div style="display:flex; gap:8px; flex:1; min-width:280px;">
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:gray;">Ngày kết thúc</label>
                    <select name="end_day" class="form-control">
                        <option value="">Ngày</option>
                        <?php for($d=1; $d<=31; $d++): ?>
                            <option value="<?php echo $d; ?>" <?php echo (int)$endDay===$d ? 'selected' : ''; ?>><?php echo sprintf('%02d', $d); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:gray;">Tháng kết thúc</label>
                    <select name="end_month" class="form-control">
                        <option value="">Tháng</option>
                        <?php for($m=1; $m<=12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo (int)$endMonth===$m ? 'selected' : ''; ?>><?php echo sprintf('%02d', $m); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div style="flex:1; min-width:80px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:gray;">Năm kết thúc</label>
                    <select name="end_year" class="form-control">
                        <option value="">Năm</option>
                        <?php for($y=2024; $y<=2030; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo (int)$endYear===$y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Nút Action -->
            <div style="display:flex; gap:8px; min-width:180px;">
                <button type="submit" class="btn btn-blue" style="flex:1;">Lọc dữ liệu</button>
                <a href="index.php" class="btn btn-gray">Đặt lại</a>
            </div>
        </div>
    </form>

    <!-- ALERT TRẠNG THÁI BỘ LỌC -->
    <?php if ($search !== '' || $statusFilter !== 'all' || $startDate || $endDate): ?>
    <div class="alert alert-info" style="margin-bottom:20px;">
        <strong>Đang hiển thị kết quả lọc:</strong>
        <?php echo $search !== '' ? ' Từ khóa: "' . htmlspecialchars($search) . '" |' : ''; ?>
        <?php echo $statusFilter !== 'all' ? ' Trạng thái: ' . htmlspecialchars($statusFilter) . ' |' : ''; ?>
        <?php echo $startDate ? ' Từ ngày: ' . date('d/m/Y', strtotime($startDate)) : ''; ?>
        <?php echo $endDate ? ' Đến ngày: ' . date('d/m/Y', strtotime($endDate)) : ''; ?>
    </div>
    <?php endif; ?>

    <!-- WIDGETS STATS -->
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-robot"></i> <?php echo __('stat_products'); ?></div>
            <div class="stat-value"><?php echo $stats['products']; ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label"><i class="fas fa-users"></i> <?php echo __('stat_customers'); ?></div>
            <div class="stat-value"><?php echo $stats['users']; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-shopping-bag"></i> <?php echo __('orders'); ?></div>
            <div class="stat-value"><?php echo $stats['orders']; ?></div>
        </div>
        <div class="stat-card red">
            <div class="stat-label"><i class="fas fa-clock"></i> <?php echo __('status_pending'); ?></div>
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label"><i class="fas fa-dollar-sign"></i> <?php echo __('revenue'); ?></div>
            <div class="stat-value" style="font-size:1.4rem"><?php echo formatPrice($stats['revenue']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-star"></i> <?php echo __('satisfaction'); ?></div>
            <div class="stat-value" style="font-size:1.4rem;">
                <?php echo $reviewStats['avg']; ?>/5
                <small style="display:block;font-size:0.55em;color:var(--text-muted);font-weight:400;">
                    <?php echo $reviewStats['count']; ?> <?php echo __('stars'); ?> · <?php echo __('reviews_avg'); ?>
                </small>
            </div>
        </div>
    </div>

    <!-- BẢNG DỮ LIỆU & BIỂU ĐỒ -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px; align-items: start;">
        <div class="card" style="margin-top: 0;">
            <h2 style="margin-top:0;display:flex;justify-content:space-between;align-items:center">
                Danh sách kết quả lọc (Tối đa 20 dòng)
                <a href="orders.php" class="btn btn-blue btn-sm"><?php echo __('view_all'); ?></a>
            </h2>
            <?php if (empty($recentOrders)): ?>
                <p style="color:var(--text-gray)">Không tìm thấy đơn hàng nào phù hợp với bộ lọc.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?php echo __('order_code_label'); ?></th>
                            <th><?php echo __('customer_short'); ?></th>
                            <th><?php echo __('total'); ?></th>
                            <th><?php echo __('status'); ?></th>
                            <th><?php echo __('date'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $o): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($o['order_code']); ?></td>
                            <td><?php echo htmlspecialchars($o['username']); ?></td>
                            <td><?php echo formatPrice($o['total']); ?></td>
                            <td><span class="status-badge <?php echo getOrderStatusClass($o['status']); ?>"><?php echo getOrderStatusLabel($o['status']); ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($o['created_at'])); ?></td>
                            <td><a href="order_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-blue btn-sm"><?php echo __('detail'); ?></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 0; display: flex; flex-direction: column; align-items: center;">
            <h2 style="margin-top:0; width: 100%; text-align: left;"><i class="fas fa-chart-pie"></i> Tỷ lệ đơn hàng hiện tại</h2>
            <div style="width: 100%; max-width: 250px; margin: 10px auto;">
                <canvas id="orderChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const chartLabels = {
        sold: <?php echo json_encode(__('chart_sold')); ?>,
        unsold: <?php echo json_encode(__('chart_unsold')); ?>,
        noData: <?php echo json_encode(__('chart_no_data')); ?>
    };
    const ctx = document.getElementById('orderChart').getContext('2d');
    const sold = <?php echo (int)$soldCount; ?>;
    const unsold = <?php echo (int)$unsoldCount; ?>;
    
    if (sold === 0 && unsold === 0) {
        ctx.font = "16px sans-serif";
        ctx.fillStyle = "#aaa";
        ctx.textAlign = "center";
        ctx.fillText(chartLabels.noData, 125, 125);
        return;
    }
    
    const getChartColor = () => document.documentElement.classList.contains('light-theme') ? '#334155' : '#f0f0f0';
    const getBorderColor = () => document.documentElement.classList.contains('light-theme') ? '#e2e8f0' : '#111';

    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [chartLabels.sold, chartLabels.unsold],
            datasets: [{
                data: [sold, unsold],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 1,
                borderColor: getBorderColor()
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: getChartColor(),
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    const themeToggleBtn = document.querySelector('.theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            setTimeout(() => {
                chart.data.datasets[0].borderColor = getBorderColor();
                chart.options.plugins.legend.labels.color = getChartColor();
                chart.update();
            }, 100);
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>