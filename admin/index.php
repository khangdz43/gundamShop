<?php
require_once '../includes/auth.php';
requireEmployee();

$basePath = '../';

// Stats
$stats = [];
$r = $conn->query("SELECT COUNT(*) as c FROM products WHERE status='active'");
$stats['products'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'");
$stats['users'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM orders");
$stats['orders'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status='pending'");
$stats['pending'] = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COALESCE(SUM(total),0) as t FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $r->fetch_assoc()['t'];

$reviewStats = getAverageReviewRating($conn);

// Recent orders
$recentOrders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Chart Stats
$soldCount = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'delivered'")->fetch_assoc()['c'];
$unsoldCount = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status != 'delivered'")->fetch_assoc()['c'];

$pageTitle = 'Admin Dashboard - Gundam Store';
include '../includes/header.php';
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container">
    <h1 class="page-title">DASHBOARD</h1>

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

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px; align-items: start;">
        <div class="card" style="margin-top: 0;">
            <h2 style="margin-top:0;display:flex;justify-content:space-between;align-items:center">
                <?php echo __('recent_orders'); ?>
                <a href="orders.php" class="btn btn-blue btn-sm"><?php echo __('view_all'); ?></a>
            </h2>
            <?php if (empty($recentOrders)): ?>
                <p style="color:var(--text-gray)"><?php echo __('no_recent_orders'); ?></p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr><th><?php echo __('order_code_label'); ?></th><th><?php echo __('customer_short'); ?></th><th><?php echo __('total'); ?></th><th><?php echo __('status'); ?></th><th><?php echo __('date'); ?></th><th></th></tr>
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
            <h2 style="margin-top:0; width: 100%; text-align: left;"><i class="fas fa-chart-pie"></i> <?php echo __('order_ratio'); ?></h2>
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

    // Lắng nghe sự kiện chuyển đổi theme để cập nhật biểu đồ thời gian thực
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
