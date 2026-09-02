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

$startYearInt  = (int)$startYear;
$startMonthInt = (int)$startMonth;
$startDayInt   = (int)$startDay;
if ($startYear !== '') {
    $startMonthInt = $startMonthInt ?: 1;
    if ($startMonthInt < 1) {
        $startMonthInt = 1;
    } elseif ($startMonthInt > 12) {
        $startMonthInt = 12;
    }
    $startDayInt = $startDayInt ?: 1;
    $maxStartDay = cal_days_in_month(CAL_GREGORIAN, $startMonthInt, $startYearInt);
    if ($startDayInt < 1) {
        $startDayInt = 1;
    } elseif ($startDayInt > $maxStartDay) {
        $startDayInt = $maxStartDay;
    }
    $startDateStr = sprintf('%04d-%02d-%02d', $startYearInt, $startMonthInt, $startDayInt);
    $whereClauses[] = "DATE(o.created_at) >= ?";
    $params[] = $startDateStr;
    $paramTypes .= "s";
    $startDate = $startDateStr;
}

$endYearInt  = (int)$endYear;
$endMonthInt = (int)$endMonth;
$endDayInt   = (int)$endDay;
if ($endYear !== '') {
    $endMonthInt = $endMonthInt ?: 12;
    if ($endMonthInt < 1) {
        $endMonthInt = 1;
    } elseif ($endMonthInt > 12) {
        $endMonthInt = 12;
    }
    $maxEndDay = cal_days_in_month(CAL_GREGORIAN, $endMonthInt, $endYearInt);
    $endDayInt = $endDayInt ?: $maxEndDay;
    if ($endDayInt < 1) {
        $endDayInt = 1;
    } elseif ($endDayInt > $maxEndDay) {
        $endDayInt = $maxEndDay;
    }
    $endDateStr = sprintf('%04d-%02d-%02d', $endYearInt, $endMonthInt, $endDayInt);
    $whereClauses[] = "DATE(o.created_at) <= ?";
    $params[] = $endDateStr;
    $paramTypes .= "s";
    $endDate = $endDateStr;
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

// 4. Dữ liệu tăng trưởng theo tháng cho biểu đồ đường
$growthSql = "
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') as month_key,
        COUNT(*) as order_count,
        SUM(CASE WHEN o.status != 'cancelled' THEN o.total ELSE 0 END) as revenue
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
" . $whereSql . "
    GROUP BY YEAR(o.created_at), MONTH(o.created_at)
    ORDER BY YEAR(o.created_at), MONTH(o.created_at)
";

$stmtGrowth = $conn->prepare($growthSql);
if (!empty($paramTypes)) {
    $stmtGrowth->bind_param($paramTypes, ...$params);
}
$stmtGrowth->execute();
$growthRows = $stmtGrowth->get_result()->fetch_all(MYSQLI_ASSOC);

$growthLabels = [];
$growthOrderValues = [];
$growthRevenueValues = [];

if (!empty($growthRows)) {
    foreach ($growthRows as $row) {
        $growthLabels[] = date('m/Y', strtotime($row['month_key'] . '-01'));
        $growthOrderValues[] = (int) $row['order_count'];
        $growthRevenueValues[] = (float) $row['revenue'];
    }
} else {
    $date = new DateTime('first day of this month');
    for ($i = 5; $i >= 0; $i--) {
        $monthDate = (clone $date)->modify("-$i months");
        $growthLabels[] = $monthDate->format('m/Y');
        $growthOrderValues[] = 0;
        $growthRevenueValues[] = 0;
    }
}

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

    <div style="display:grid; grid-template-columns: 1.4fr 0.9fr; gap:20px; margin-top:20px; align-items:stretch;">
        <!-- BIỂU ĐỒ TĂNG TRƯỞNG -->
        <div class="card" style="margin-top: 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0 0 6px 0;"><i class="fas fa-chart-line"></i> Tăng trưởng theo tháng</h2>
                    <p style="margin:0; color:var(--text-muted);">Theo dõi xu hướng đơn hàng và doanh thu trong khoảng thời gian đã lọc.</p>
                </div>
                <div style="padding:6px 12px; border-radius:999px; background:rgba(31,95,255,0.12); color:#4f46e5; font-weight:600; font-size:0.85rem;">
                    Đơn hàng + Doanh thu
                </div>
            </div>
            <div style="height:320px; margin-top:16px;">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        <!-- BIỂU ĐỒ TRÒN TỶ LỆ -->
        <div class="card" style="margin-top: 0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
            <h2 style="margin:0 0 10px 0; width:100%; text-align:left;"><i class="fas fa-chart-pie"></i> Tỷ lệ đơn hàng đã lọc</h2>
            <div style="width:100%; max-width:260px; margin:10px auto;">
                <canvas id="statusChart"></canvas>
            </div>
            <div style="width:100%; text-align:center; color:var(--text-muted); font-size:0.9rem;">
                Hoàn thành: <strong><?php echo (int)$soldCount; ?></strong> · Chưa hoàn thành: <strong><?php echo (int)$unsoldCount; ?></strong>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const getChartColor = () => document.documentElement.classList.contains('light-theme') ? '#334155' : '#f0f0f0';
    const getBorderColor = () => document.documentElement.classList.contains('light-theme') ? '#e2e8f0' : '#111';
    const getGridColor = () => document.documentElement.classList.contains('light-theme') ? 'rgba(15, 23, 42, 0.12)' : 'rgba(255,255,255,0.12)';

    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const labels = <?php echo json_encode($growthLabels); ?>;
    const orderData = <?php echo json_encode($growthOrderValues); ?>;
    const revenueData = <?php echo json_encode($growthRevenueValues); ?>;

    const growthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Đơn hàng',
                    data: orderData,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.16)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Doanh thu',
                    data: revenueData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.16)',
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'y1',
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    labels: {
                        color: getChartColor(),
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: getChartColor()
                    },
                    grid: {
                        color: getGridColor()
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    ticks: {
                        color: getChartColor()
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                },
                x: {
                    ticks: {
                        color: getChartColor()
                    },
                    grid: {
                        color: getGridColor()
                    }
                }
            }
        }
    });

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const completed = <?php echo (int)$soldCount; ?>;
    const uncompleted = <?php echo (int)$unsoldCount; ?>;

    if (completed === 0 && uncompleted === 0) {
        statusCtx.font = '16px sans-serif';
        statusCtx.fillStyle = '#aaa';
        statusCtx.textAlign = 'center';
        statusCtx.fillText('Không có dữ liệu', 130, 130);
    } else {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn thành', 'Chưa hoàn thành'],
                datasets: [{
                    data: [completed, uncompleted],
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
    }

    const themeToggleBtn = document.querySelector('.theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            setTimeout(() => {
                growthChart.options.plugins.legend.labels.color = getChartColor();
                growthChart.options.scales.y.ticks.color = getChartColor();
                growthChart.options.scales.y.grid.color = getGridColor();
                growthChart.options.scales.y1.ticks.color = getChartColor();
                growthChart.options.scales.x.ticks.color = getChartColor();
                growthChart.options.scales.x.grid.color = getGridColor();
                growthChart.update();
            }, 100);
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>