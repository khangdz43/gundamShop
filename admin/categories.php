<?php
require_once '../includes/auth.php';
requirePermission('products');

$basePath = '../';
$message = '';
$success = false;
$editingCategory = null;

if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    if ($deleteId > 0) {
        $check_sql = "SELECT COUNT(*) as used_count FROM products WHERE category_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('i', $deleteId);
        $check_stmt->execute();
        $used = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if (!empty($used['used_count'])) {
            $message = 'Không thể xóa danh mục đang được sử dụng bởi sản phẩm.';
        } else {
            $delete_stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $delete_stmt->bind_param('i', $deleteId);
            if ($delete_stmt->execute()) {
                header('Location: categories.php?message=' . urlencode('Xóa danh mục thành công') . '&success=1');
                exit;
            }
            $delete_stmt->close();
            $message = 'Lỗi khi xóa danh mục.';
        }
    }
}

if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    if ($editId > 0) {
        $edit_stmt = $conn->prepare("SELECT id, name, slug FROM categories WHERE id = ?");
        $edit_stmt->bind_param('i', $editId);
        $edit_stmt->execute();
        $editingCategory = $edit_stmt->get_result()->fetch_assoc();
        $edit_stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

    if ($name === '') {
        $message = 'Vui lòng nhập tên danh mục.';
    } else {
        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $name)));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
        }

        if ($action === 'edit' && $categoryId > 0) {
            $check_stmt = $conn->prepare("SELECT id FROM categories WHERE id != ? AND (LOWER(name) = LOWER(?) OR LOWER(slug) = LOWER(?))");
            $check_stmt->bind_param('iss', $categoryId, $name, $slug);
        } else {
            $check_stmt = $conn->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) OR LOWER(slug) = LOWER(?)");
            $check_stmt->bind_param('ss', $name, $slug);
        }
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if ($existing) {
            $message = 'Danh mục này đã tồn tại.';
        } else {
            if ($action === 'edit' && $categoryId > 0) {
                $update_stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
                $update_stmt->bind_param('ssi', $name, $slug, $categoryId);
                if ($update_stmt->execute()) {
                    header('Location: categories.php?message=' . urlencode('Cập nhật danh mục thành công') . '&success=1');
                    exit;
                }
                $update_stmt->close();
                $message = 'Lỗi khi cập nhật danh mục.';
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                $insert_stmt->bind_param('ss', $name, $slug);
                if ($insert_stmt->execute()) {
                    header('Location: categories.php?message=' . urlencode('Thêm danh mục thành công!') . '&success=1');
                    exit;
                }
                $insert_stmt->close();
                $message = 'Lỗi khi thêm danh mục.';
            }
        }
    }
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $success = isset($_GET['success']) && $_GET['success'] == '1';
}

$categories = [];
$categories_result = $conn->query("SELECT id, name, slug, created_at FROM categories ORDER BY name ASC");
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục - Gundam Store</title>
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

        .main-content {
            padding-top: 120px;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }

        .admin-content-header {
            margin-bottom: 24px;
        }

        .admin-title {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
        }

        .admin-subtitle {
            color: var(--text-gray);
            font-size: 1rem;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.16);
            color: #7ee081;
            border-color: rgba(40, 167, 69, 0.35);
        }

        .alert-error {
            background: rgba(225, 6, 0, 0.16);
            color: #ff8f88;
            border-color: rgba(225, 6, 0, 0.35);
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
        }

        .panel {
            background: var(--card-bg);
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        .panel h2 {
            color: white;
            font-size: 1.2rem;
            margin-top: 0;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #333;
            background: #1b1b1b;
            color: white;
            box-sizing: border-box;
        }

        .btn-admin {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }

        .btn-primary:hover {
            background: #4f7bff;
        }

        .category-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .category-table th,
        .category-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #262626;
            text-align: left;
            color: var(--text-gray);
        }

        .category-table th {
            color: white;
            background: #121212;
            font-weight: 700;
        }

        .category-table tr:hover td {
            background: rgba(31, 95, 255, 0.05);
            color: #f1f1f1;
        }

        .category-table td {
            vertical-align: middle;
        }

        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-danger {
            background: #e10600;
            color: white;
        }

        .btn-danger:hover {
            background: #ff3b1f;
        }

        @media (max-width: 900px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }

            .category-table {
                min-width: 100%;
            }
        }

        @media (max-width: 900px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include '../includes/admin_nav.php'; ?>

<div class="main-content">
    <div class="admin-container">
        <div class="admin-content-header">
            <h1 class="admin-title">Quản lý danh mục sản phẩm</h1>
            <p class="admin-subtitle">Tạo danh mục mới để gắn cho các model trong cửa hàng.</p>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="admin-grid">
            <div class="panel">
                <h2><i class="fas fa-plus"></i> <?php echo $editingCategory ? 'Cập nhật danh mục' : 'Thêm danh mục mới'; ?></h2>
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo $editingCategory ? 'edit' : 'add'; ?>">
                    <?php if ($editingCategory): ?>
                        <input type="hidden" name="category_id" value="<?php echo (int)$editingCategory['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="categoryName">Tên danh mục</label>
                        <input type="text" id="categoryName" name="name" class="form-control" placeholder="Ví dụ: RX-78" required
                               value="<?php echo htmlspecialchars($editingCategory['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="categorySlug">Slug</label>
                        <input type="text" id="categorySlug" name="slug" class="form-control" placeholder="Để trống để tự tạo"
                               value="<?php echo htmlspecialchars($editingCategory['slug'] ?? ''); ?>">
                    </div>
                    <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                        <button type="submit" class="btn-admin btn-primary">
                            <i class="fas fa-save"></i> <?php echo $editingCategory ? 'Cập nhật' : 'Lưu danh mục'; ?>
                        </button>
                        <?php if ($editingCategory): ?>
                            <a href="categories.php" class="btn-admin btn-secondary" style="background:#333;color:white;">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="panel">
                <h2><i class="fas fa-list"></i> Danh mục hiện có</h2>
                <?php if (!empty($categories)): ?>
                    <div style="overflow-x:auto;">
                        <table class="category-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên danh mục</th>
                                    <th>Slug</th>
                                    <th>Ngày tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo (int)$category['id']; ?></td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                        <td><?php echo !empty($category['created_at']) ? date('d/m/Y', strtotime($category['created_at'])) : '—'; ?></td>
                                        <td class="action-group">
                                            <a href="categories.php?edit_id=<?php echo (int)$category['id']; ?>" class="btn-admin btn-secondary" style="padding:8px 12px; font-size:0.9rem;">
                                                <i class="fas fa-edit"></i> Sửa
                                            </a>
                                            <a href="categories.php?delete_id=<?php echo (int)$category['id']; ?>" class="btn-admin btn-danger" onclick="return confirm('Xác nhận xóa danh mục này? Nếu danh mục đang được gắn với sản phẩm, xóa sẽ không thực hiện được.');" style="padding:8px 12px; font-size:0.9rem;">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-gray);">Chưa có danh mục nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
