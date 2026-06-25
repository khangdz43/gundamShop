<?php
session_start();
include "../config/db.php";

// Chỉ admin mới xem được
if (!isset($_SESSION["username"]) || $_SESSION["username"] != "admin") {
    die("Bạn không có quyền truy cập trang này.");
}

$message = "";
$success = false;
$product = null;
$product_id = 0;

// Lấy ID sản phẩm cần chỉnh sửa
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Lấy thông tin sản phẩm từ database
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    } else {
        $message = "Không tìm thấy sản phẩm!";
    }
} else {
    $message = "Thiếu thông tin sản phẩm!";
}

// Xử lý form cập nhật sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $product_id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_sale = isset($_POST['is_sale']) ? 1 : 0;
    $stock = max(0, (int)($_POST['stock'] ?? 50));
    $old_price = isset($_POST['old_price']) && !empty($_POST['old_price']) ? mysqli_real_escape_string($conn, $_POST['old_price']) : NULL;
    
    // Lấy thông tin sản phẩm hiện tại
    $sql_current = "SELECT image FROM products WHERE id = $product_id";
    $result_current = mysqli_query($conn, $sql_current);
    $current_product = mysqli_fetch_assoc($result_current);
    $current_image = $current_product['image'];
    
    // Xử lý upload ảnh mới
    $image_name = $current_image; // Giữ ảnh cũ nếu không upload ảnh mới
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Xóa ảnh cũ nếu không phải ảnh mặc định
            if ($current_image != "models_default_img.jpeg" && file_exists("../assets/images/" . $current_image)) {
                unlink("../assets/images/" . $current_image);
            }
            
            // Tạo tên file độc nhất
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_path = '../assets/images/' . $image_name;
            
            // Upload file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Thành công
            } else {
                $message = "Lỗi khi upload ảnh!";
                $image_name = $current_image; // Giữ lại ảnh cũ nếu upload thất bại
            }
        } else {
            $message = "Chỉ chấp nhận file ảnh (JPEG, JPG, PNG, GIF, WEBP)!";
        }
    }
    
    // Nếu không có lỗi, cập nhật database
    if (empty($message)) {
        $sql = "UPDATE products SET 
                name = '$name', 
                price = '$price', 
                category = '$category', 
                image = '$image_name', 
                description = '$description', 
                old_price = " . ($old_price ? "'$old_price'" : "NULL") . ", 
                type = '$type', 
                stock = '$stock',
                is_featured = '$is_featured', 
                is_sale = '$is_sale' 
                WHERE id = $product_id";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Cập nhật sản phẩm thành công!";
            $success = true;
            
            // Lấy lại thông tin sản phẩm mới
            $sql = "SELECT * FROM products WHERE id = $product_id";
            $result = mysqli_query($conn, $sql);
            $product = mysqli_fetch_assoc($result);
        } else {
            $message = "Lỗi: " . mysqli_error($conn);
        }
    }
}

// Xử lý xóa sản phẩm
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Lấy thông tin ảnh để xóa
    $sql_img = "SELECT image FROM products WHERE id = $product_id";
    $result_img = mysqli_query($conn, $sql_img);
    $product_img = mysqli_fetch_assoc($result_img);
    
    // Xóa ảnh nếu không phải ảnh mặc định
    if ($product_img['image'] != "models_default_img.jpeg" && file_exists("../assets/images/" . $product_img['image'])) {
        unlink("../assets/images/" . $product_img['image']);
    }
    
    // Xóa sản phẩm từ database
    $sql_delete = "DELETE FROM products WHERE id = $product_id";
    if (mysqli_query($conn, $sql_delete)) {
        header("Location: models.php?message=Xóa sản phẩm thành công&success=true");
        exit();
    } else {
        $message = "Lỗi khi xóa sản phẩm: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Model - Gundam Store</title>
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
        }
        
        .admin-container {
            max-width: 1200px;
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
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background: #4f7bff;
            transform: scale(1.05);
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
            background: #ff3333;
            transform: scale(1.05);
        }
        
        /* Điều chỉnh vị trí container để không bị navbar che */
        .main-content {
            padding-top: 120px;
        }
        
        /* Form styles */
        .edit-form-container {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 30px;
            border: 2px solid #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .form-col {
            flex: 1;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: white;
        }
        
        .required::after {
            content: " *";
            color: var(--primary-red);
        }
        
        .form-control {
            width: 95%;
            padding: 12px 15px;
            background: #222;
            border: 2px solid #333;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(31, 95, 255, 0.2);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .price-group {
            display: flex;
            gap: 20px;
        }
        
        .price-input {
            flex: 1;
        }
        
        .price-hint {
            font-size: 0.9rem;
            color: var(--text-gray);
            margin-top: 5px;
        }
        
        /* Current image styles */
        .current-image-container {
            margin-bottom: 20px;
            padding: 15px;
            background: #222;
            border-radius: 8px;
            border: 2px solid #333;
        }
        
        .current-image-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: white;
        }
        
        .current-image-preview {
            max-width: 200px;
            border-radius: 8px;
            border: 2px solid #444;
        }
        
        /* File upload styles */
        .file-upload-container {
            background: #222;
            border: 2px dashed #444;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .file-upload-container:hover {
            border-color: var(--primary-blue);
            background: rgba(31, 95, 255, 0.1);
        }
        
        .file-upload-icon {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 15px;
        }
        
        .file-upload-text {
            margin-bottom: 10px;
            color: white;
        }
        
        .file-upload-hint {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .file-input {
            display: none;
        }
        
        .file-selected {
            margin-top: 15px;
            padding: 10px;
            background: #333;
            border-radius: 5px;
            text-align: left;
        }
        
        .file-selected-name {
            color: var(--primary-blue);
            font-weight: bold;
            word-break: break-all;
        }
        
        .file-preview {
            margin-top: 20px;
            max-width: 300px;
            margin: 20px auto 0;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #444;
            display: none;
        }
        
        /* Message styles */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: bold;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 2px solid #28a745;
            color: #28a745;
        }
        
        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 2px solid #dc3545;
            color: #dc3545;
        }
        
        .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            border: 2px solid #ffc107;
            color: #ffc107;
        }
        
        /* Form buttons */
        .form-buttons {
            display: flex;
            gap: 15px;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        
        .btn-left-group {
            display: flex;
            gap: 15px;
        }
        
        .btn-right-group {
            display: flex;
            gap: 15px;
        }
        
        .btn-submit {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: #4f7bff;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #666;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #777;
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: var(--primary-red);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-delete:hover {
            background: #ff3333;
            transform: translateY(-2px);
        }
        
        /* Delete confirmation modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            border: 2px solid var(--primary-red);
        }
        
        .modal-title {
            color: var(--primary-red);
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .modal-message {
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-col {
                margin-bottom: 20px;
            }
            
            .price-group {
                flex-direction: column;
                gap: 15px;
            }
            
            .checkbox-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-buttons {
                flex-direction: column;
            }
            
            .btn-left-group, .btn-right-group {
                width: 100%;
                flex-direction: column;
            }
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

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="admin-container">
        <!-- Content Header -->
        <div class="admin-header">
            <h1 class="admin-title">Chỉnh sửa Model</h1>
            <div class="admin-actions">
                <a href="models.php" class="btn-admin btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <a href="add_model.php" class="btn-admin btn-primary">
                    <i class="fas fa-plus"></i> Thêm mới
                </a>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
                <?php if ($success): ?>
                    <br><a href="models.php" style="color: #28a745; text-decoration: underline;">Xem danh sách models</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($product): ?>
        <!-- Edit Form -->
        <div class="edit-form-container">
            <form method="POST" action="" enctype="multipart/form-data" id="editModelForm">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="update" value="1">
                
                <div class="form-row">
                    <div class="form-col">
                        <!-- Thông tin cơ bản -->
                        <div class="form-group">
                            <label for="name" class="required">Tên Model</label>
                            <input type="text" id="name" name="name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($product['name']); ?>"
                                   placeholder="VD: RX-78-2 Gundam (HG 1/144)">
                        </div>
                        
                        <div class="form-group">
                            <label for="category" class="required">Danh mục</label>
                            <input type="text" id="category" name="category" class="form-control" required 
                                   value="<?php echo htmlspecialchars($product['category']); ?>"
                                   placeholder="VD: Gundam, Zaku, etc.">
                        </div>
                        
                        <div class="form-group">
                            <label for="type" class="required">Loại</label>
                            <select id="type" name="type" class="form-control" required>
                                <option value="">-- Chọn loại --</option>
                                <option value="HG" <?php echo $product['type'] == 'HG' ? 'selected' : ''; ?>>High Grade (HG)</option>
                                <option value="MG" <?php echo $product['type'] == 'MG' ? 'selected' : ''; ?>>Master Grade (MG)</option>
                                <option value="RG" <?php echo $product['type'] == 'RG' ? 'selected' : ''; ?>>Real Grade (RG)</option>
                                <option value="PG" <?php echo $product['type'] == 'PG' ? 'selected' : ''; ?>>Perfect Grade (PG)</option>
                                <option value="SD" <?php echo $product['type'] == 'SD' ? 'selected' : ''; ?>>Super Deformed (SD)</option>
                                <option value="MGEX" <?php echo $product['type'] == 'MGEX' ? 'selected' : ''; ?>>Master Grade Extreme (MGEX)</option>
                                <option value="Other" <?php echo $product['type'] == 'Other' ? 'selected' : ''; ?>>Khác (Other)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock" class="required">Số lượng tồn kho</label>
                            <input type="number" id="stock" name="stock" class="form-control" min="0" required 
                                   value="<?php echo htmlspecialchars($product['stock']); ?>"
                                   placeholder="VD: 50">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <!-- Giá cả -->
                        <div class="form-group">
                            <label for="price" class="required">Giá bán (VNĐ)</label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   min="0" step="1000" required 
                                   value="<?php echo $product['price']; ?>"
                                   placeholder="350000">
                        </div>
                        
                        <div class="form-group">
                            <label for="old_price">Giá gốc (VNĐ)</label>
                            <div class="price-group">
                                <div class="price-input">
                                    <input type="number" id="old_price" name="old_price" class="form-control" 
                                        min="0" step="1000" 
                                        value="<?php echo $product['old_price'] ? htmlspecialchars($product['old_price']) : ''; ?>"
                                        placeholder="520000">
                                    <div class="price-hint">Để trống nếu không có giảm giá</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                        <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                    <span>Sản phẩm nổi bật</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" id="is_sale" name="is_sale" value="1" 
                                        <?php echo $product['is_sale'] ? 'checked' : ''; ?>>
                                    <span>Đang giảm giá</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mô tả -->
                <div class="form-group">
                    <label for="description" class="required">Mô tả sản phẩm</label>
                    <textarea id="description" name="description" class="form-control" required 
                            placeholder="Mô tả chi tiết về sản phẩm..."><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <!-- Ảnh hiện tại -->
                <div class="form-group">
                    <label>Ảnh hiện tại</label>
                    <div class="current-image-container">
                        <div class="current-image-title">
                            Ảnh đang sử dụng:
                        </div>
                        <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="Current Image" 
                             class="current-image-preview"
                             onerror="this.src='../assets/images/models_default_img.jpeg'">
                        <div class="price-hint" style="margin-top: 10px;">
                            Đường dẫn: ../assets/images/<?php echo htmlspecialchars($product['image']); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Upload ảnh mới -->
                <div class="form-group">
                    <label for="image">Hình ảnh mới (nếu muốn thay đổi)</label>
                    <div class="file-upload-container" onclick="document.getElementById('image').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-text">
                            <strong>Click để chọn ảnh mới</strong>
                        </div>
                        <div class="file-upload-hint">
                            Chấp nhận: JPEG, JPG, PNG, GIF, WEBP (Tối đa 5MB)<br>
                            <em>Bỏ trống nếu muốn giữ ảnh hiện tại</em>
                        </div>
                        <input type="file" id="image" name="image" class="file-input" accept="image/*">
                        
                        <div id="fileSelected" class="file-selected" style="display: none;">
                            <div>Đã chọn: <span class="file-selected-name" id="fileName"></span></div>
                        </div>
                        
                        <div class="file-preview">
                            <img id="previewImage" class="preview-image" src="" alt="Preview">
                        </div>
                    </div>
                </div>
                
                <!-- Form buttons -->
                <div class="form-buttons">
                    <div class="btn-left-group">
                        <button type="button" class="btn-delete" onclick="showDeleteModal()">
                            <i class="fas fa-trash"></i> Xóa sản phẩm
                        </button>
                    </div>
                    <div class="btn-right-group">
                        <button type="button" class="btn-cancel" onclick="window.location='models.php'">
                            <i class="fas fa-times"></i> Hủy bỏ
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php elseif(!empty($message)): ?>
            <div class="alert alert-error">
                <?php echo $message; ?>
                <br>
                <a href="models.php" style="color: #dc3545; text-decoration: underline;">Quay lại danh sách models</a>
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

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-title">
            <i class="fas fa-exclamation-triangle"></i> Xác nhận xóa
        </div>
        <div class="modal-message">
            Bạn có chắc chắn muốn xóa sản phẩm "<strong><?php echo $product ? htmlspecialchars($product['name']) : ''; ?></strong>" không?<br>
            <span style="color: var(--primary-red);">Hành động này không thể hoàn tác!</span>
        </div>
        <div class="modal-buttons">
            <button type="button" class="btn-cancel" onclick="hideDeleteModal()">
                <i class="fas fa-times"></i> Hủy bỏ
            </button>
            <button type="button" class="btn-delete" onclick="deleteProduct()">
                <i class="fas fa-trash"></i> Xóa sản phẩm
            </button>
        </div>
    </div>
</div>

<script>
    // Xử lý hiển thị file đã chọn và preview ảnh
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Hiển thị tên file
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSelected').style.display = 'block';
            
            // Hiển thị preview ảnh
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('previewImage');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
            
            // Tự động check "Đang giảm giá" nếu có giá gốc
            if (document.getElementById('old_price').value) {
                document.getElementById('is_sale').checked = true;
            }
        } else {
            document.getElementById('fileSelected').style.display = 'none';
            document.getElementById('previewImage').style.display = 'none';
        }
    });
    
    // Tự động check "Đang giảm giá" khi nhập giá gốc
    document.getElementById('old_price').addEventListener('input', function() {
        if (this.value) {
            document.getElementById('is_sale').checked = true;
        } else {
            document.getElementById('is_sale').checked = false;
        }
    });
    
    // Tự động bỏ check "Đang giảm giá" nếu bỏ giá gốc
    document.getElementById('is_sale').addEventListener('change', function() {
        if (!this.checked) {
            document.getElementById('old_price').value = '';
        }
    });
    
    // Validation before submit
    document.getElementById('editModelForm').addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('price').value);
        const oldPrice = document.getElementById('old_price').value ? parseFloat(document.getElementById('old_price').value) : null;
        
        // Kiểm tra giá sale phải nhỏ hơn giá gốc
        if (oldPrice && price >= oldPrice) {
            e.preventDefault();
            alert('Giá bán phải nhỏ hơn giá gốc khi có giảm giá!');
            return false;
        }
        
        // Kiểm tra file ảnh có được chọn không
        const fileInput = document.getElementById('image');
        if (fileInput.files.length > 0) {
            // Kiểm tra kích thước file (5MB)
            const file = fileInput.files[0];
            if (file.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('File ảnh quá lớn! Vui lòng chọn file nhỏ hơn 5MB.');
                return false;
            }
        }
        
        return true;
    });
    
    // Delete modal functions
    function showDeleteModal() {
        document.getElementById('deleteModal').style.display = 'flex';
    }
    
    function hideDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
    
    function deleteProduct() {
        window.location.href = 'edit_model.php?action=delete&id=<?php echo $product_id; ?>';
    }
    
    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideDeleteModal();
        }
    });
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideDeleteModal();
        }
    });
</script>

<script src="../assets/app.js"></script>
</body>
</html>
