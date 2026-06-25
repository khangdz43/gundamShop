<?php
/**
 * Seed Data Import Script
 * Run this file to populate the database with sample data
 */

require_once __DIR__ . '/../config/db.php';

try {
    // Read the seed SQL file
    $sqlFile = __DIR__ . '/seed_full_data.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Seed file không tồn tại: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && strpos($stmt, '--') !== 0;
        }
    );
    
    $count = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $count++;
                echo "✓ Thực thi câu lệnh SQL thứ {$count}<br>";
            } catch (PDOException $e) {
                // Skip if table already exists or duplicate key errors
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate entry') !== false ||
                    strpos($e->getMessage(), 'FOREIGN KEY constraint fails') !== false) {
                    echo "⚠ Bỏ qua: " . $e->getMessage() . "<br>";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✓ Nhập dữ liệu thành công!</h2>";
    echo "<p>Đã nhập {$count} câu lệnh SQL</p>";
    echo "<p>Dữ liệu mẫu bao gồm:</p>";
    echo "<ul>";
    echo "<li>25 người dùng (bao gồm 3 admin)</li>";
    echo "<li>10 danh mục sản phẩm</li>";
    echo "<li>60+ sản phẩm Gundam (HG, RG, MG, PG, SD, MGEX)</li>";
    echo "<li>20 đơn hàng mẫu</li>";
    echo "<li>28 chi tiết đơn hàng</li>";
    echo "<li>15 đánh giá sản phẩm</li>";
    echo "<li>19 mục giỏ hàng</li>";
    echo "<li>5 phiên chat</li>";
    echo "<li>10 thông báo</li>";
    echo "</ul>";
    echo "<p><strong>Mật khẩu mặc định:</strong> password123 (cho tất cả tài khoản)</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
