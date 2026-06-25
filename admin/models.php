<?php
require_once '../includes/auth.php';
requireAdmin();

$basePath = '../';

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Lấy thông tin ảnh để xóa
    $sql_img = "SELECT image FROM products WHERE id = $id";
    $result_img = mysqli_query($conn, $sql_img);
    $product_img = mysqli_fetch_assoc($result_img);
    
    // Xóa ảnh nếu không phải ảnh mặc định
    if ($product_img['image'] != "models_default_img.jpeg" && file_exists("../assets/images/" . $product_img['image'])) {
        unlink("../assets/images/" . $product_img['image']);
    }
    
    // Xóa sản phẩm từ database
    $delete_sql = "DELETE FROM products WHERE id = $id";
    mysqli_query($conn, $delete_sql);
    
    // Thông báo thành công
    header("Location: models.php?message=Xóa sản phẩm thành công&success=true");
    exit();
}

// Lấy danh sách sản phẩm
$sql = "SELECT * FROM products ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

// Kiểm tra thông báo từ URL
$notification = "";
$notification_type = "";
if (isset($_GET['message'])) {
    $notification = $_GET['message'];
    $notification_type = isset($_GET['success']) ? 'success' : 'error';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Models - Gundam Store</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1f5fff;
            --primary-red: #e10600;
            --dark-bg: #0d0d0d;
            --card-bg: #111;
            --text-light: #f0f0f0;
            --text-gray: #aaa;
        }
        
        body {
            background: #000;
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }
        
        .admin-container {
            max-width: 1600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-blue);
        }
        
        .admin-title {
            font-size: 2rem;
            font-weight: 800;
            color: white;
        }
        
        .admin-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn-admin {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background: #4f7bff;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(31, 95, 255, 0.3);
        }
        
        .btn-secondary {
            background: #333;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #444;
            transform: scale(1.05);
        }
        
        .btn-danger {
            background: var(--primary-red);
            color: white;
        }
        
        .btn-danger:hover {
            background: #ff3b1f;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(225, 6, 0, 0.3);
        }
        
        .models-table {
            width: 100%;
            background: var(--card-bg);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .models-table th {
            background: linear-gradient(135deg, #222 0%, #1a1a1a 100%);
            color: white;
            padding: 18px 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-blue);
            position: relative;
            font-size: 0.95rem;
        }
        
        .models-table th:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary-blue);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .models-table th:hover:after {
            transform: scaleX(1);
        }
        
        .models-table td {
            padding: 18px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-gray);
            vertical-align: middle;
            transition: all 0.3s ease;
        }
        
        .models-table tr {
            transition: all 0.3s ease;
        }
        
        .models-table tr:hover {
            background: linear-gradient(90deg, rgba(31, 95, 255, 0.08) 0%, rgba(31, 95, 255, 0.02) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .models-table tr:hover td {
            color: white;
        }
        
        .models-table tr:last-child td {
            border-bottom: none;
        }
        
        .product-image-small {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #333;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
        }
        
        .models-table tr:hover .product-image-small {
            border-color: var(--primary-blue);
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(31, 95, 255, 0.3);
        }
        
        .price-cell {
            font-weight: bold;
            text-align: right;
        }
        
        .original-price {
            color: #aaa;
            text-decoration: line-through;
            font-size: 0.9rem;
            display: block;
        }
        
        .sale-price {
            color: var(--primary-red);
            font-size: 1.1rem;
            font-weight: 800;
            display: block;
        }
        
        .normal-price {
            color: var(--primary-blue);
            font-weight: 800;
        }
        
        .sale-badge {
            background: var(--primary-red);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 5px;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .type-badge {
            background: linear-gradient(135deg, rgba(31, 95, 255, 0.2) 0%, rgba(31, 95, 255, 0.1) 100%);
            color: var(--primary-blue);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            border: 1px solid rgba(31, 95, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .models-table tr:hover .type-badge {
            background: linear-gradient(135deg, rgba(31, 95, 255, 0.3) 0%, rgba(31, 95, 255, 0.2) 100%);
            border-color: var(--primary-blue);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            min-width: 70px;
            justify-content: center;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.2);
        }
        
        .btn-edit:hover {
            background: linear-gradient(135deg, #218838 0%, #1e9e8a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(220, 53, 69, 0.2);
        }
        
        .btn-delete:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        .no-products {
            text-align: center;
            padding: 60px 40px;
            color: var(--text-gray);
            font-size: 1.1rem;
            background: var(--card-bg);
            border-radius: 15px;
            border: 2px dashed #333;
        }
        
        .admin-content-header {
            background: linear-gradient(135deg, rgba(31, 95, 255, 0.15) 0%, rgba(0,0,0,0.9) 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 2px solid var(--primary-blue);
            box-shadow: 0 10px 30px rgba(31, 95, 255, 0.1);
        }
        
        .admin-stats {
            display: flex;
            gap: 20px;
            margin-top: 25px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            flex: 1;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-blue);
            box-shadow: 0 10px 20px rgba(31, 95, 255, 0.1);
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .model-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }
        
        .status-dot:after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .models-table tr:hover .status-dot:after {
            opacity: 1;
        }
        
        .status-active {
            background: #28a745;
        }
        
        .status-active:after {
            border: 2px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-sale {
            background: var(--primary-red);
        }
        
        .status-sale:after {
            border: 2px solid rgba(225, 6, 0, 0.3);
        }
        
        .status-normal {
            background: #6c757d;
        }
        
        .status-normal:after {
            border: 2px solid rgba(108, 117, 125, 0.3);
        }
        
        .main-content {
            padding-top: 120px;
        }
        
        .discount-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #333;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 5px;
            display: inline-block;
        }
        
        /* Chiều rộng các cột */
        .models-table th:nth-child(1),
        .models-table td:nth-child(1) {
            width: 50px;
            text-align: center;
        }
        
        .models-table th:nth-child(2),
        .models-table td:nth-child(2) {
            width: 90px;
            text-align: center;
        }
        
        .models-table th:nth-child(3),
        .models-table td:nth-child(3) {
            width: 250px;
        }
        
        .models-table th:nth-child(4),
        .models-table td:nth-child(4) {
            width: 100px;
        }
        
        .models-table th:nth-child(5),
        .models-table td:nth-child(5) {
            width: 150px;
            text-align: right;
        }
        
        .models-table th:nth-child(6),
        .models-table td:nth-child(6) {
            width: 150px;
            text-align: right;
        }
        
        .models-table th:nth-child(7),
        .models-table td:nth-child(7) {
            width: 110px;
            text-align: center;
        }
        
        .models-table th:nth-child(8),
        .models-table td:nth-child(8) {
            width: 120px;
        }
        
        .models-table th:nth-child(9),
        .models-table td:nth-child(9) {
            width: 100px;
        }
        
        .models-table th:nth-child(10),
        .models-table td:nth-child(10) {
            width: 160px;
        }
        
        /* Notification styles */
        .notification-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }
        
        .notification {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 2px solid;
            animation: slideIn 0.5s ease, slideOut 0.5s ease 4s forwards;
            transform: translateX(120%);
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification-success {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        
        .notification-error {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }
        
        .notification-icon {
            font-size: 1.5rem;
        }
        
        .notification-success .notification-icon {
            color: #28a745;
        }
        
        .notification-error .notification-icon {
            color: #dc3545;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .notification-message {
            font-size: 0.9rem;
            color: var(--text-gray);
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(120%);
            }
            to {
                transform: translateX(0);
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(120%);
            }
        }
        
        /* Delete Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: linear-gradient(135deg, var(--card-bg) 0%, #0a0a0a 100%);
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            border: 2px solid var(--primary-red);
            box-shadow: 0 20px 50px rgba(225, 6, 0, 0.3);
            text-align: center;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-content.show {
            transform: scale(1);
        }
        
        .modal-icon {
            font-size: 4rem;
            color: var(--primary-red);
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        .modal-title {
            color: white;
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 15px;
        }
        
        .modal-message {
            color: var(--text-gray);
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .modal-product-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: left;
        }
        
        .modal-product-name {
            color: white;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .modal-product-details {
            display: flex;
            gap: 20px;
            font-size: 0.9rem;
            color: var(--text-gray);
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-modal {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            min-width: 120px;
            justify-content: center;
        }
        
        .btn-modal-cancel {
            background: #333;
            color: white;
        }
        
        .btn-modal-cancel:hover {
            background: #444;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.1);
        }
        
        .btn-modal-delete {
            background: linear-gradient(135deg, var(--primary-red) 0%, #c82333 100%);
            color: white;
        }
        
        .btn-modal-delete:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(225, 6, 0, 0.3);
        }
    </style>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16x16.png">
    <link rel="manifest" href="../assets/images/site.webmanifest">
</head>
<body data-base-path="../">

<?php include '../includes/admin_nav.php'; ?>

<!-- Notification Container -->
<div class="notification-container" id="notificationContainer">
    <?php if (!empty($notification)): ?>
        <div class="notification notification-<?php echo $notification_type; ?> show">
            <div class="notification-icon">
                <?php if ($notification_type == 'success'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php else: ?>
                    <i class="fas fa-exclamation-circle"></i>
                <?php endif; ?>
            </div>
            <div class="notification-content">
                <div class="notification-title">
                    <?php echo $notification_type == 'success' ? 'Thành công!' : 'Lỗi!'; ?>
                </div>
                <div class="notification-message"><?php echo $notification; ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-content" id="modalContent">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="modal-title">Xác nhận xóa</div>
        <div class="modal-message">
            Bạn có chắc chắn muốn xóa sản phẩm này không?<br>
            Hành động này sẽ không thể hoàn tác và sẽ xóa vĩnh viễn sản phẩm.
        </div>
        <div class="modal-product-info" id="modalProductInfo">
            <!-- Thông tin sản phẩm sẽ được thêm bằng JavaScript -->
        </div>
        <div class="modal-buttons">
            <button type="button" class="btn-modal btn-modal-cancel" onclick="hideDeleteModal()">
                <i class="fas fa-times"></i> Hủy bỏ
            </button>
            <button type="button" class="btn-modal btn-modal-delete" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Xóa vĩnh viễn
            </button>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="admin-container">
        <!-- Content Header -->
        <div class="admin-content-header">
            <h1 class="admin-title">Quản lý Models</h1>
            <p style="color: var(--text-gray); margin-top: 10px; font-size: 1.1rem;">Quản lý và theo dõi tất cả mô hình Gundam trong cửa hàng</p>
            
            <div class="admin-stats">
                <?php
                // Lấy thống kê
                $total_models = mysqli_num_rows($result);
                $sale_count_sql = "SELECT COUNT(*) as count FROM products WHERE is_sale = 1 AND old_price IS NOT NULL";
                $sale_result = mysqli_query($conn, $sale_count_sql);
                $sale_count = mysqli_fetch_assoc($sale_result)['count'];
                $featured_count_sql = "SELECT COUNT(*) as count FROM products WHERE is_featured = 1";
                $featured_result = mysqli_query($conn, $featured_count_sql);
                $featured_count = mysqli_fetch_assoc($featured_result)['count'];
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_models; ?></div>
                    <div class="stat-label">Tổng Models</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $sale_count; ?></div>
                    <div class="stat-label">Đang giảm giá</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $featured_count; ?></div>
                    <div class="stat-label">Sản phẩm nổi bật</div>
                </div>
            </div>
        </div>
        
        <!-- Header với nút hành động -->
        <div class="admin-header">
            <h2 style="color: white; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-list"></i> Danh sách Models
            </h2>
            <div class="admin-actions">
                <a href="add_model.php" class="btn-admin btn-primary">
                    <i class="fas fa-plus"></i> Thêm Model mới
                </a>
                <a href="../index.php" class="btn-admin btn-secondary">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
            </div>
        </div>
        
        <!-- Products Table -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="models-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên Model</th>
                        <th>Loại</th>
                        <th>Giá gốc</th>
                        <th>Giá khi sale</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($result, 0); // Reset result pointer
                    while ($row = mysqli_fetch_assoc($result)): 
                        $is_sale = $row['is_sale'] && $row['old_price'];
                        $discount = 0;
                        if ($is_sale && $row['old_price'] > 0) {
                            $discount = round(100 - ($row['price'] / $row['old_price'] * 100));
                        }
                    ?>
                    <tr>
                        <td><span style="color: #888; font-weight: bold;">#<?php echo $row["id"]; ?></span></td>
                        <td>
                            <img src="../assets/images/<?php echo $row['image']; ?>" 
                                 alt="<?php echo $row['name']; ?>" 
                                 class="product-image-small"
                                 onerror="this.src='../assets/images/models_default_img.jpeg'">
                        </td>
                        <td>
                            <strong style="color: white; font-size: 1.05rem;"><?php echo $row["name"]; ?></strong><br>
                            <small style="color: #888; font-size: 0.9rem;"><?php echo $row["category"]; ?></small>
                        </td>
                        <td><span class="type-badge"><?php echo $row["type"]; ?></span></td>
                        
                        <!-- Cột Giá gốc -->
                        <td class="price-cell">
                            <?php if($is_sale): ?>
                                <span class="original-price">
                                    <?php echo number_format($row['old_price'], 0, ',', '.') . ' ₫'; ?>
                                </span>
                            <?php else: ?>
                                <span class="normal-price">
                                    <?php echo number_format($row['price'], 0, ',', '.') . ' ₫'; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Cột Giá khi sale -->
                        <td class="price-cell">
                            <?php if($is_sale): ?>
                                <span class="sale-price">
                                    <?php echo number_format($row['price'], 0, ',', '.') . ' ₫'; ?>
                                </span>
                                <?php if($discount > 0): ?>
                                    <span class="discount-badge">-<?php echo $discount; ?>%</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #888; font-style: italic; font-size: 0.9rem;">Không sale</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Cột Số lượng -->
                        <td style="text-align: center;">
                            <?php 
                            $stock = (int)$row['stock'];
                            if ($stock === 0) {
                                echo '<span style="color:#ff3333; font-weight:bold;"><i class="fas fa-times-circle"></i> Hết</span>';
                            } elseif ($stock < 10) {
                                echo '<span style="color:#ff9800; font-weight:bold;">' . $stock . ' (Sắp hết)</span>';
                            } else {
                                echo '<span style="color:#28a745; font-weight:bold;">' . $stock . '</span>';
                            }
                            ?>
                        </td>
                        
                        <td>
                            <div class="model-status">
                                <?php if($row['is_featured']): ?>
                                    <span class="status-dot status-active"></span>
                                    <span style="font-size: 0.9rem;">Nổi bật</span>
                                <?php elseif($is_sale): ?>
                                    <span class="status-dot status-sale"></span>
                                    <span style="font-size: 0.9rem;">Sale</span>
                                <?php else: ?>
                                    <span class="status-dot status-normal"></span>
                                    <span style="font-size: 0.9rem;">Thường</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_model.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <button type="button" 
                                        class="btn-action btn-delete" 
                                        onclick="showDeleteModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo $row['type']; ?>', '<?php echo number_format($row['price'], 0, ',', '.'); ?>')">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-robot" style="font-size: 5rem; color: #333; margin-bottom: 20px; opacity: 0.5;"></i>
                <h3 style="color: white; margin-bottom: 10px;">Không có model nào</h3>
                <p style="font-size: 1.1rem; margin-bottom: 30px;">Hãy thêm model mới để bắt đầu</p>
                <a href="add_model.php" class="btn-admin btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Thêm Model đầu tiên
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer_text">
        <p>Gundam Store HUMG © 2025 - All Rights Reserved</p>
        <p style="margin-top: 10px; font-size: 12px; color: #888;">
            Địa chỉ: Trường Đại học Mỏ - Địa chất | Hotline: 0969 946 335 | Email: gundamstore@humg.vn
        </p>
    </div>
</footer>

<script>
    // Biến toàn cục để lưu ID sản phẩm cần xóa
    let productToDelete = null;
    let productName = '';
    
    // Hiển thị modal xác nhận xóa
    function showDeleteModal(id, name, type, price) {
        productToDelete = id;
        productName = name;
        
        // Cập nhật thông tin sản phẩm trong modal
        document.getElementById('modalProductInfo').innerHTML = `
            <div class="modal-product-name">${name}</div>
            <div class="modal-product-details">
                <div><strong>Loại:</strong> ${type}</div>
                <div><strong>Giá:</strong> ${price} ₫</div>
            </div>
        `;
        
        // Hiển thị modal với hiệu ứng
        const modal = document.getElementById('deleteModal');
        const modalContent = document.getElementById('modalContent');
        modal.style.display = 'flex';
        
        setTimeout(() => {
            modalContent.classList.add('show');
        }, 10);
    }
    
    // Ẩn modal
    function hideDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const modalContent = document.getElementById('modalContent');
        
        modalContent.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Xác nhận xóa sản phẩm
    function confirmDelete() {
        if (productToDelete) {
            window.location.href = 'models.php?delete=' + productToDelete;
        }
    }
    
    // Đóng modal khi click ra ngoài
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideDeleteModal();
        }
    });
    
    // Đóng modal bằng phím ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideDeleteModal();
        }
    });
</script>

<script src="../assets/app.js"></script>
</body>
</html>
