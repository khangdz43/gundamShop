<?php
require_once '../includes/auth.php';
requirePermission('orders');

$basePath = '../';

// 1. Nhận tham số lọc, tìm kiếm, sắp xếp và phân trang
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['search'] ?? '');
$sortBy       = $_GET['sort_by'] ?? 'date_desc';
$page         = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit        = 15; 

// Nhận các thành phần cấu trúc Ngày/Tháng/Năm từ bộ lọc nâng cao
$fromDay   = $_GET['from_day'] ?? '';
$fromMonth = $_GET['from_month'] ?? '';
$fromYear  = $_GET['from_year'] ?? '';

$toDay     = $_GET['to_day'] ?? '';
$toMonth   = $_GET['to_month'] ?? '';
$toYear    = $_GET['to_year'] ?? '';

// Khởi tạo chuỗi ngày tháng rỗng để tí nữa đưa vào câu lệnh SQL WHERE
$fromDate = '';
$toDate   = '';

// Logic hợp nhất dữ liệu (Data Consolidation): Cho phép lọc ngày theo năm và tháng mà không cần chọn đủ cả 3 thành phần
$fromYearInt  = (int)$fromYear;
$fromMonthInt = (int)$fromMonth;
$fromDayInt   = (int)$fromDay;

if ($fromYear !== '') {
    $fromMonthInt = $fromMonthInt ?: 1;
    if ($fromMonthInt < 1) {
        $fromMonthInt = 1;
    } elseif ($fromMonthInt > 12) {
        $fromMonthInt = 12;
    }
    $fromDayInt = $fromDayInt ?: 1;
    $maxFromDay = cal_days_in_month(CAL_GREGORIAN, $fromMonthInt, $fromYearInt);
    if ($fromDayInt < 1) {
        $fromDayInt = 1;
    } elseif ($fromDayInt > $maxFromDay) {
        $fromDayInt = $maxFromDay;
    }
    $fromDate = sprintf('%04d-%02d-%02d', $fromYearInt, $fromMonthInt, $fromDayInt);
}

$toYearInt  = (int)$toYear;
$toMonthInt = (int)$toMonth;
$toDayInt   = (int)$toDay;

if ($toYear !== '') {
    $toMonthInt = $toMonthInt ?: 12;
    if ($toMonthInt < 1) {
        $toMonthInt = 1;
    } elseif ($toMonthInt > 12) {
        $toMonthInt = 12;
    }
    $maxToDay = cal_days_in_month(CAL_GREGORIAN, $toMonthInt, $toYearInt);
    $toDayInt = $toDayInt ?: $maxToDay;
    if ($toDayInt < 1) {
        $toDayInt = 1;
    } elseif ($toDayInt > $maxToDay) {
        $toDayInt = $maxToDay;
    }
    $toDate = sprintf('%04d-%02d-%02d', $toYearInt, $toMonthInt, $toDayInt);
}

// Hàm tạo URL giữ nguyên các tham số lọc hiện tại
function buildFilterUrl($newParams) {
    $params = $_GET;
    foreach ($newParams as $k => $v) {
        if ($v === null) {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    return 'orders.php?' . http_build_query($params);
}

// 2. Thống kê số lượng đơn hàng theo từng trạng thái (tổng số tuyệt đối trong hệ thống)
$statusCounts = [
    'all'        => 0,
    'pending'    => 0,
    'processing' => 0,
    'shipped'    => 0,
    'completed'  => 0,
    'cancelled'  => 0
];
$countsQuery = $conn->query("SELECT status, COUNT(*) as c FROM orders GROUP BY status");
if ($countsQuery) {
    while ($row = $countsQuery->fetch_assoc()) {
        $st = $row['status'];
        $c  = (int)$row['c'];
        if (isset($statusCounts[$st])) {
            $statusCounts[$st] = $c;
            $statusCounts['all'] += $c;
        }
    }
    $countsQuery->free();
}

// 3. Xây dựng câu lệnh truy vấn SQL động theo bộ lọc
$whereClauses = [];
$params = [];
$types = '';

if ($statusFilter && in_array($statusFilter, ['pending','processing','shipped','completed','cancelled'], true)) {
    $whereClauses[] = "o.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($search !== '') {
    $whereClauses[] = "(o.order_code LIKE ? OR o.full_name LIKE ? OR o.phone LIKE ? OR o.email LIKE ? OR u.username LIKE ?)";
    $searchWildcard = '%' . $search . '%';
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $types .= 'sssss';
}

// Chỉ nạp vào SQL điều kiện nếu chuỗi ngày sau khi gộp đã chuẩn chỉ
if ($fromDate !== '') {
    $whereClauses[] = "DATE(o.created_at) >= ?";
    $params[] = $fromDate;
    $types .= 's';
}

if ($toDate !== '') {
    $whereClauses[] = "DATE(o.created_at) <= ?";
    $params[] = $toDate;
    $types .= 's';
}

$whereSql = "";
if (!empty($whereClauses)) {
    $whereSql = " WHERE " . implode(" AND ", $whereClauses);
}

// 4. Đếm tổng số đơn hàng thỏa mãn bộ lọc để phân trang
$countSql = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id" . $whereSql;
$stmtCount = $conn->prepare($countSql);
if (!empty($params)) {
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$totalFilteredOrders = (int)($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);
$stmtCount->close();

$totalPages = max(1, ceil($totalFilteredOrders / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// 5. Xác định thứ tự sắp xếp (Sorting)
$sortSql = "ORDER BY o.created_at DESC"; 
if ($sortBy === 'date_asc') {
    $sortSql = "ORDER BY o.created_at ASC";
} elseif ($sortBy === 'total_desc') {
    $sortSql = "ORDER BY o.total DESC";
} elseif ($sortBy === 'total_asc') {
    $sortSql = "ORDER BY o.total ASC";
}

// 6. Lấy danh sách đơn hàng thực tế bằng Prepared Statement
$mainSql = "SELECT o.*, u.username, " .
           "(SELECT r.status FROM order_returns r WHERE r.order_id = o.id ORDER BY r.created_at DESC LIMIT 1) AS return_status " .
           "FROM orders o " .
           "JOIN users u ON o.user_id = u.id " .
           $whereSql . " " . $sortSql . " LIMIT ? OFFSET ?";
$stmtMain = $conn->prepare($mainSql);

$mainParams = $params;
$mainParams[] = $limit;
$mainParams[] = $offset;
$mainTypes = $types . 'ii';

$stmtMain->bind_param($mainTypes, ...$mainParams);
$stmtMain->execute();
$orders = $stmtMain->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtMain->close();

$pageTitle = __('admin_orders_page');
include '../includes/header.php';
?>

<div class="container">
    <h1 class="page-title"><?php echo __('admin_orders_title'); ?></h1>

    <!-- 1. Thanh trạng thái (Tabs) -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:25px;justify-content:center">
        <a href="<?php echo buildFilterUrl(['status' => null, 'page' => 1]); ?>" 
           class="btn <?php echo !$statusFilter ? 'btn-blue' : 'btn-gray'; ?> btn-sm" style="display:flex;align-items:center;gap:6px;">
            <?php echo __('all'); ?> 
            <span style="background:rgba(255,255,255,0.25);padding:2px 8px;border-radius:20px;font-size:0.75rem;font-weight:bold;">
                <?php echo $statusCounts['all']; ?>
            </span>
        </a>
        <?php foreach (['pending','processing','shipped','completed','cancelled'] as $s): ?>
        <a href="<?php echo buildFilterUrl(['status' => $s, 'page' => 1]); ?>" 
           class="btn <?php echo $statusFilter === $s ? 'btn-blue' : 'btn-gray'; ?> btn-sm" style="display:flex;align-items:center;gap:6px;">
            <?php echo getOrderStatusLabel($s); ?> 
            <span style="background:rgba(255,255,255,0.25);padding:2px 8px;border-radius:20px;font-size:0.75rem;font-weight:bold;">
                <?php echo $statusCounts[$s]; ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- 2. Form Tìm kiếm & Bộ lọc nâng cao cấu trúc Select Option (Optimized Date Selector) -->
    <div class="card" style="margin-bottom:25px;padding:20px;">
        <form method="GET" action="orders.php" style="display:flex; flex-direction:column; gap:15px;">
            
            <?php if ($statusFilter): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <?php endif; ?>
            
            <!-- Hàng 1: Tìm kiếm từ khóa và Sắp xếp -->
            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:15px; flex-wrap:wrap;">
                <div class="form-group" style="margin:0;">
                    <label style="font-size:0.85rem;color:var(--text-muted);font-weight:600;margin-bottom:6px;display:block;">Từ khóa tìm kiếm</label>
                    <input type="text" name="search" class="form-control" placeholder="Mã đơn, tên, sđt, username..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="form-group" style="margin:0;">
                    <label style="font-size:0.85rem;color:var(--text-muted);font-weight:600;margin-bottom:6px;display:block;">Sắp xếp theo</label>
                    <select name="sort_by" class="form-control">
                        <option value="date_desc" <?php echo $sortBy === 'date_desc' ? 'selected' : ''; ?>>Ngày đặt: Mới nhất</option>
                        <option value="date_asc" <?php echo $sortBy === 'date_asc' ? 'selected' : ''; ?>>Ngày đặt: Cũ nhất</option>
                        <option value="total_desc" <?php echo $sortBy === 'total_desc' ? 'selected' : ''; ?>>Tổng tiền: Giảm dần</option>
                        <option value="total_asc" <?php echo $sortBy === 'total_asc' ? 'selected' : ''; ?>>Tổng tiền: Tăng dần</option>
                    </select>
                </div>
            </div>

            <!-- Hàng 2: Bộ chọn Ngày/Tháng/Năm ba thành phần (Component-based Dropdowns) -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:15px;">
                
                <!-- BỘ LỌC TỪ NGÀY -->
                <div class="form-group" style="margin:0;">
                    <label style="font-size:0.85rem;color:var(--text-muted);font-weight:600;margin-bottom:6px;display:block;">Từ ngày</label>
                    <div style="display:flex; gap:6px;">
                        <!-- Chọn Ngày -->
                        <select name="from_day" class="form-control" style="flex:1;">
                            <option value="">Ngày</option>
                            <?php for($d=1; $d<=31; $d++): ?>
                                <option value="<?php echo $d; ?>" <?php echo (int)$fromDay === $d ? 'selected' : ''; ?>><?php echo sprintf('%02d', $d); ?></option>
                            <?php endfor; ?>
                        </select>
                        <!-- Chọn Tháng -->
                        <select name="from_month" class="form-control" style="flex:1;">
                            <option value="">Tháng</option>
                            <?php for($m=1; $m<=12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo (int)$fromMonth === $m ? 'selected' : ''; ?>><?php echo sprintf('Thg %02d', $m); ?></option>
                            <?php endfor; ?>
                        </select>
                        <!-- Chọn Năm -->
                        <select name="from_year" class="form-control" style="flex:1.2;">
                            <option value="">Năm</option>
                            <?php for($y=2020; $y<=2030; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo (int)$fromYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- BỘ LỌC ĐẾN NGÀY -->
                <div class="form-group" style="margin:0;">
                    <label style="font-size:0.85rem;color:var(--text-muted);font-weight:600;margin-bottom:6px;display:block;">Đến ngày</label>
                    <div style="display:flex; gap:6px;">
                        <!-- Chọn Ngày -->
                        <select name="to_day" class="form-control" style="flex:1;">
                            <option value="">Ngày</option>
                            <?php for($d=1; $d<=31; $d++): ?>
                                <option value="<?php echo $d; ?>" <?php echo (int)$toDay === $d ? 'selected' : ''; ?>><?php echo sprintf('%02d', $d); ?></option>
                            <?php endfor; ?>
                        </select>
                        <!-- Chọn Tháng -->
                        <select name="to_month" class="form-control" style="flex:1;">
                            <option value="">Tháng</option>
                            <?php for($m=1; $m<=12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo (int)$toMonth === $m ? 'selected' : ''; ?>><?php echo sprintf('Thg %02d', $m); ?></option>
                            <?php endfor; ?>
                        </select>
                        <!-- Chọn Năm -->
                        <select name="to_year" class="form-control" style="flex:1.2;">
                            <option value="">Năm</option>
                            <?php for($y=2020; $y<=2030; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo (int)$toYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Nút Hành Động -->
            <div style="display:flex; justify-content: flex-end; gap:10px; margin-top:5px;">
                <button type="submit" class="btn btn-blue" style="width:120px; justify-content:center;"><i class="fas fa-search"></i> Lọc</button>
                <?php if ($search || $fromDay || $toDay || $sortBy !== 'date_desc'): ?>
                    <a href="orders.php<?php echo $statusFilter ? '?status=' . urlencode($statusFilter) : ''; ?>" class="btn btn-gray" style="width:120px; justify-content:center; text-decoration:none; display:inline-flex; align-items:center;">Xóa lọc</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- 3. Bảng dữ liệu Đơn hàng -->
    <div class="card" style="overflow-x:auto;padding:0;">
        <table class="data-table" style="margin:0;">
            <thead>
                <tr>
                    <th><?php echo __('order_code'); ?></th>
                    <th><?php echo __('customer'); ?></th>
                    <th><?php echo __('phone'); ?></th>
                    <th><?php echo __('total'); ?></th>
                    <th><?php echo __('payment'); ?></th>
                    <th><?php echo __('status'); ?></th>
                    <th><?php echo __('date'); ?></th>
                    <th style="text-align:center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-gray)"><i class="fas fa-box-open" style="font-size:2rem;margin-bottom:10px;display:block;opacity:0.5;"></i><?php echo __('no_orders_admin'); ?></td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($o['order_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($o['full_name']); ?><br><small style="color:var(--text-gray)">@<?php echo htmlspecialchars($o['username']); ?></small></td>
                        <td><?php echo htmlspecialchars($o['phone']); ?></td>
                        <td><strong style="color:var(--primary-blue);"><?php echo formatPrice($o['total']); ?></strong></td>
                        <td><span style="font-size:0.85rem;font-weight:600;background:rgba(255,255,255,0.05);padding:3px 8px;border-radius:4px;border:1px solid var(--border-color);"><?php echo $o['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản'; ?></span></td>
                        <td>
                            <?php if (!empty($o['return_status']) && $o['return_status'] === 'pending'): ?>
                                <span class="status-badge status-pending">Yêu cầu đổi trả</span>
                            <?php else: ?>
                                <span class="status-badge <?php echo getOrderStatusClass($o['status']); ?>"><?php echo getOrderStatusLabel($o['status']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($o['created_at'])); ?></td>
                        <td style="text-align:center;"><a href="order_detail.php?id=<?php echo $o['id']; ?>" class="btn btn-blue btn-sm"><?php echo __('detail'); ?></a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 4. Thanh điều hướng phân trang -->
    <?php if ($totalFilteredOrders > 0): ?>
    <div class="pagination" style="display:flex;justify-content:space-between;align-items:center;margin-top:25px;margin-bottom:45px;flex-wrap:wrap;gap:12px;">
        <div style="color:var(--text-muted);font-size:0.9rem;">
            Hiển thị từ <strong><?php echo $offset + 1; ?></strong> đến <strong><?php echo min($offset + $limit, $totalFilteredOrders); ?></strong> trong tổng số <strong><?php echo $totalFilteredOrders; ?></strong> đơn hàng thỏa mãn lọc.
        </div>
        <div style="display:flex;gap:6px;">
            <?php if ($page > 1): ?>
                <a href="<?php echo buildFilterUrl(['page' => 1]); ?>" class="btn btn-gray btn-sm" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;padding:0;">&laquo;</a>
                <a href="<?php echo buildFilterUrl(['page' => $page - 1]); ?>" class="btn btn-gray btn-sm" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;padding:0;">&lsaquo;</a>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="<?php echo buildFilterUrl(['page' => $i]); ?>" 
                   class="btn <?php echo $page === $i ? 'btn-blue' : 'btn-gray'; ?> btn-sm" 
                   style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;padding:0;font-weight:600;">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?php echo buildFilterUrl(['page' => $page + 1]); ?>" class="btn btn-gray btn-sm" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;padding:0;">&rsaquo;</a>
                <a href="<?php echo buildFilterUrl(['page' => $totalPages]); ?>" class="btn btn-gray btn-sm" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;padding:0;">&raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>