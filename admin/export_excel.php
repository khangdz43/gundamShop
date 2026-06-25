<?php
/**
 * Xuất Excel - Admin
 * Hỗ trợ xuất: orders, users, revenue
 */
require_once '../includes/auth.php';
requireEmployee();

$type = $_GET['type'] ?? 'orders';

function xlsRow($cells) {
    $row = '<tr>';
    foreach ($cells as $cell) {
        $row .= '<td>' . htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8') . '</td>';
    }
    $row .= '</tr>';
    return $row;
}

function xlsHeader($cells) {
    $row = '<tr>';
    foreach ($cells as $cell) {
        $row .= '<th style="background:#1f5fff;color:white;font-weight:bold;padding:8px;">' . htmlspecialchars($cell) . '</th>';
    }
    $row .= '</tr>';
    return $row;
}

$filename = '';
$title    = '';
$html     = '';

ob_start();

if ($type === 'orders') {
    requirePermission('orders');
    $filename = 'don-hang-' . date('Y-m-d') . '.xls';
    $title    = 'Báo cáo Đơn hàng - Gundam Store - ' . date('d/m/Y H:i');
    
    $orders = $conn->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);
    
    $statusLabel = ['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã hủy'];
    
    echo '<h2>' . $title . '</h2>';
    echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial;font-size:12px;">';
    echo xlsHeader(['Mã đơn','Khách hàng','Tên người nhận','SĐT','Email','Địa chỉ','Tạm tính','Phí ship','Tổng tiền','Thanh toán','Trạng thái','Ngày đặt']);
    
    $total_rev = 0;
    foreach ($orders as $o) {
        echo xlsRow([
            $o['order_code'],
            $o['username'],
            $o['full_name'],
            $o['phone'],
            $o['email'],
            $o['address'],
            number_format($o['subtotal'], 0, ',', '.') . '₫',
            number_format($o['shipping_fee'], 0, ',', '.') . '₫',
            number_format($o['total'], 0, ',', '.') . '₫',
            $o['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản',
            $statusLabel[$o['status']] ?? $o['status'],
            date('d/m/Y H:i', strtotime($o['created_at']))
        ]);
        if ($o['status'] !== 'cancelled') $total_rev += $o['total'];
    }
    echo '<tr><td colspan="8" style="font-weight:bold;">TỔNG DOANH THU (trừ đơn hủy)</td>';
    echo '<td style="font-weight:bold;color:#1f5fff;">' . number_format($total_rev,0,',','.') . '₫</td>';
    echo '<td colspan="3"></td></tr>';
    echo '</table>';

} elseif ($type === 'users') {
    requireAdmin();
    $filename = 'users-' . date('Y-m-d') . '.xls';
    $title    = 'Danh sách Người dùng - Gundam Store - ' . date('d/m/Y H:i');
    
    $users = $conn->query("SELECT id, username, email, full_name, phone, role, is_active, created_at FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    
    $posLabel = ['admin'=>'Quản trị viên','order_manager'=>'QL Đơn hàng','return_manager'=>'QL Đổi trả','staff'=>'Nhân viên'];
    
    echo '<h2>' . $title . '</h2>';
    echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial;font-size:12px;">';
    echo xlsHeader(['ID','Tên đăng nhập','Họ tên','Email','SĐT','Loại TK','Chức vụ','Trạng thái','Ngày tạo']);
    
    foreach ($users as $u) {
        echo xlsRow([
            $u['id'],
            $u['username'],
            $u['full_name'] ?? '',
            $u['email'],
            $u['phone'] ?? '',
            $u['role'] === 'admin' ? 'Admin' : ($u['role'] === 'employee' ? 'Nhân viên' : 'Khách hàng'),
            $posLabel[$u['position'] ?? ''] ?? '-',
            $u['is_active'] ? 'Hoạt động' : 'Bị khóa',
            date('d/m/Y', strtotime($u['created_at']))
        ]);
    }
    echo '</table>';

} elseif ($type === 'revenue') {
    requirePermission('orders');
    $filename = 'doanh-thu-' . date('Y-m') . '.xls';
    $title    = 'Báo cáo Doanh thu theo tháng - Gundam Store - ' . date('d/m/Y H:i');
    
    $revenue = $conn->query("
        SELECT 
            DATE_FORMAT(created_at,'%Y-%m') as month,
            COUNT(*) as total_orders,
            SUM(CASE WHEN status != 'cancelled' THEN 1 ELSE 0 END) as success_orders,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
            COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total ELSE 0 END), 0) as revenue,
            COALESCE(SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END), 0) as delivered_revenue
        FROM orders 
        GROUP BY month 
        ORDER BY month DESC
    ")->fetch_all(MYSQLI_ASSOC);
    
    echo '<h2>' . $title . '</h2>';
    echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial;font-size:12px;">';
    echo xlsHeader(['Tháng','Tổng đơn','Đơn thành công','Đơn hủy','Doanh thu (trừ hủy)','Doanh thu đã giao']);
    
    $grandTotal = 0;
    foreach ($revenue as $r) {
        echo xlsRow([
            $r['month'],
            $r['total_orders'],
            $r['success_orders'],
            $r['cancelled_orders'],
            number_format($r['revenue'], 0, ',', '.') . '₫',
            number_format($r['delivered_revenue'], 0, ',', '.') . '₫',
        ]);
        $grandTotal += $r['revenue'];
    }
    echo '<tr>';
    echo '<td colspan="4" style="font-weight:bold;">TỔNG CỘNG</td>';
    echo '<td style="font-weight:bold;color:#1f5fff;">' . number_format($grandTotal,0,',','.') . '₫</td>';
    echo '<td></td></tr>';
    echo '</table>';

} else {
    die('Loại xuất không hợp lệ');
}

$content = ob_get_clean();

// Xuất file Excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta charset="UTF-8">';
echo '<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
table { border-collapse: collapse; }
th { background: #1f5fff; color: white; padding: 8px; font-weight: bold; }
td { padding: 6px 8px; }
h2 { color: #1f5fff; margin-bottom: 16px; }
</style>';
echo '</head><body>';
echo $content;
echo '</body></html>';
exit;
