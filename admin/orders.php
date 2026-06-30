<?php
require_once '../includes/auth.php';
requireEmployee();

$basePath = '../';
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id";
$params = [];
$types = '';

if ($statusFilter && in_array($statusFilter, ['pending','confirmed','shipping','delivered','cancelled'])) {
    $sql .= " WHERE o.status = ?";
    $params[] = $statusFilter;
    $types = 's';
}
$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = __('admin_orders_page');
include '../includes/header.php';
?>

<div class="container">
    <h1 class="page-title"><?php echo __('admin_orders_title'); ?></h1>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;justify-content:center">
        <a href="orders.php" class="btn <?php echo !$statusFilter ? 'btn-blue' : 'btn-gray'; ?> btn-sm"><?php echo __('all'); ?></a>
        <?php foreach (['pending','confirmed','shipping','delivered','cancelled'] as $s): ?>
        <a href="orders.php?status=<?php echo $s; ?>" class="btn <?php echo $statusFilter === $s ? 'btn-blue' : 'btn-gray'; ?> btn-sm"><?php echo getOrderStatusLabel($s); ?></a>
        <?php endforeach; ?>
    </div>

    <div class="card" style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?php echo __('order_code'); ?></th>
                    <th><?php echo __('customer'); ?></th>
                    <th><?php echo __('phone'); ?></th>
                    <th><?php echo __('total'); ?></th>
                    <th><?php echo __('payment'); ?></th>
                    <th><?php echo __('status'); ?></th>
                    <th><?php echo __('date'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;color:var(--text-gray)"><?php echo __('no_orders_admin'); ?></td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($o['order_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($o['full_name']); ?><br><small style="color:var(--text-gray)">@<?php echo htmlspecialchars($o['username']); ?></small></td>
                        <td><?php echo htmlspecialchars($o['phone']); ?></td>
                        <td><?php echo formatPrice($o['total']); ?></td>
                        <td><?php echo $o['payment_method'] === 'cod' ? 'COD' : 'CK'; ?></td>
                        <td><span class="status-badge <?php echo getOrderStatusClass($o['status']); ?>"><?php echo getOrderStatusLabel($o['status']); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($o['created_at'])); ?></td>
                        <td><a href="order_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-blue btn-sm"><?php echo __('detail'); ?></a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
