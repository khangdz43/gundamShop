<?php
$conn = new mysqli('localhost', 'root', '', 'gundam_store');
if ($conn->connect_error) {
    die('DB connect failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// 1. SEED CATEGORIES
$categories = [
    ['id' => 1, 'name' => 'Gunpla - Mô Hình Lắp Ráp', 'slug' => 'gunpla-mo-hinh-lap-rap', 'description' => 'Các mẫu Gundam và Mobile Suit nổi tiếng, phù hợp cho người mới lẫn người sưu tầm lâu năm.'],
    ['id' => 2, 'name' => 'Dụng Cụ Mô Hình (Tools)', 'slug' => 'dung-cu-mo-hinh-tools', 'description' => 'Dao cắt, kéo, kìm, bàn chải và các dụng cụ cần thiết cho việc lắp ráp.'],
    ['id' => 3, 'name' => 'Sơn & Phụ Kiện (Paints & Decal)', 'slug' => 'son-va-phu-kien-paints-decal', 'description' => 'Sơn, decal, base, phụ kiện và vật dụng hỗ trợ hoàn thiện mô hình.'],
    ['id' => 4, 'name' => 'Bộ Sưu Tập Limited', 'slug' => 'bo-suu-tap-limited', 'description' => 'Các mẫu giới hạn, phiên bản collector và sự kiện đặc biệt.'],
    ['id' => 5, 'name' => 'Phụ Kiện Trang Trí', 'slug' => 'phu-kien-trang-tri', 'description' => 'Tượng, stand, display case và các món phụ kiện để trưng bày.'],
];

$stmt = $conn->prepare('INSERT INTO categories (id, name, slug, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), slug = VALUES(slug), description = VALUES(description)');
foreach ($categories as $cat) {
    $stmt->bind_param('isss', $cat['id'], $cat['name'], $cat['slug'], $cat['description']);
    $stmt->execute();
}
$stmt->close();


// 2. SEED PRODUCTS (Khớp chuẩn 100% tên file ảnh thực tế trong thư mục images)
$products = [
    [
        'category_id' => 1,
        'name'        => 'HG 1/144 Char Zaku II Red',
        'slug'        => 'hg-1-144-char-zaku-ii-red',
        'description' => 'Mô hình Zaku II màu đỏ đặc trưng của Char Aznable. Khớp nối linh hoạt, kèm đầy đủ vũ khí cơ bản.',
        'price'       => 450000,
        'stock'       => 25,
        'image'       => 'char_zaku_red_hg.jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'HG 1/144 Gundam Lfrith Ur',
        'slug'        => 'hg-1-144-gundam-lfrith-ur',
        'description' => 'Mô hình thuộc series The Witch from Mercury với thiết kế hầm hố, trang bị khẩu Gatling Gun cỡ lớn.',
        'price'       => 520000,
        'stock'       => 15,
        'image'       => 'lfrith_ur_hg.jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'HGUC 1/144 Nightingale',
        'slug'        => 'hguc-1-144-nightingale',
        'description' => 'Mẫu HG kích thước siêu khủng, chi tiết giáp vai và phần đuôi được tạo hình vô cùng sắc nét.',
        'price'       => 1850000,
        'stock'       => 8,
        'image'       => 'Nightingale (HG).jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'RG 1/144 Crossbone Gundam X1',
        'slug'        => 'rg-1-144-crossbone-gundam-x1',
        'description' => 'Công nghệ Advanced MS Joint cho khung xương siêu nhỏ gọn, chi tiết áo khoác ABC Cloak ấn tượng.',
        'price'       => 750000,
        'stock'       => 18,
        'image'       => 'crossbone_rg.jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'RG 1/144 Gundam Epyon',
        'slug'        => 'rg-1-144-gundam-epyon',
        'description' => 'Khả năng biến hình sang dạng MA mượt mà, roi Heat Rod khớp linh hoạt cực cao.',
        'price'       => 1150000,
        'stock'       => 12,
        'image'       => 'epyon_rg.jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'MG 1/100 MS-07B-3 Gouf Custom',
        'slug'        => 'mg-1-100-gouf-custom',
        'description' => 'Huyền thoại từ 08th MS Team với khẩu Gatling Shield hầm hố và dây Heat Rod cáp dẻo.',
        'price'       => 980000,
        'stock'       => 10,
        'image'       => 'Gouf Custom (MG).jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'MG 1/100 Sazabi Ver.Ka',
        'slug'        => 'mg-1-100-sazabi-ver-ka',
        'description' => 'Kiệt tác thiết kế từ Katoki Hajime với cơ chế mở giáp lộ khung xương cơ khí chi tiết đỉnh cao.',
        'price'       => 2450000,
        'stock'       => 6,
        'image'       => 'Sazabi Ver.Ka.jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'MG 1/100 Gundam Virtue',
        'slug'        => 'mg-1-100-gundam-virtue',
        'description' => 'Tích hợp lớp giáp dày đặc bên ngoài và có thể tháo rời hoàn toàn để biến thành GN-004 Nadleeh.',
        'price'       => 2100000,
        'stock'       => 9,
        'image'       => 'virtue_mg.jpg'
    ],
    [
        'category_id' => 4,
        'name'        => 'PG Unleashed 1/60 RX-78-2 Gundam',
        'slug'        => 'pg-unleashed-1-60-rx-78-2-gundam',
        'description' => 'Đỉnh cao công nghệ Gunpla với cấu trúc khung xương 5 lớp, hệ thống LED tích hợp và kim loại đúc.',
        'price'       => 7850000,
        'stock'       => 3,
        'image'       => 'PG Unleashed.jpg'
    ],
    [
        'category_id' => 4,
        'name'        => 'PG 1/60 Strike Freedom Gundam',
        'slug'        => 'pg-1-60-strike-freedom-gundam',
        'description' => 'Mô hình tỉ lệ 1/60 khổng lồ, bộ cánh Super DRAGOON xòe rộng với các chi tiết mạ vàng sang trọng.',
        'price'       => 6900000,
        'stock'       => 4,
        'image'       => 'strike_freedom_pg.jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'SD EX-Standard Unicorn Gundam',
        'slug'        => 'sd-ex-standard-unicorn-gundam',
        'description' => 'Dòng SD nhỏ gọn, tỉ lệ chibi đáng yêu, lắp ráp nhanh chóng, thích hợp trưng bày.',
        'price'       => 180000,
        'stock'       => 30,
        'image'       => 'SD Unicorn.jpg'
    ],
    [
        'category_id' => 1,
        'name'        => 'SD BB Senshi Knight Unicorn Gundam',
        'slug'        => 'sd-bb-senshi-knight-unicorn-gundam',
        'description' => 'Phiên bản hiệp sĩ huyền thoại với bộ giáp bạc mạ bóng và khả năng biến hình mặt nạ độc đáo.',
        'price'       => 320000,
        'stock'       => 15,
        'image'       => 'sd_knight_unicorn.jpg'
    ]
];

$stmtProduct = $conn->prepare("
    INSERT INTO products (category_id, name, slug, description, price, stock, image, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
    ON DUPLICATE KEY UPDATE 
        name = VALUES(name),
        description = VALUES(description),
        price = VALUES(price), 
        stock = VALUES(stock),
        image = VALUES(image),
        status = VALUES(status)
");

foreach ($products as $p) {
    $stmtProduct->bind_param('isssdis', $p['category_id'], $p['name'], $p['slug'], $p['description'], $p['price'], $p['stock'], $p['image']);
    $stmtProduct->execute();
}
$stmtProduct->close();


// 3. SEED USERS
$users = [
    ['username' => 'admin', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'email' => 'admin@gundamstore.vn', 'full_name' => 'Quản trị viên', 'phone' => '0901111111', 'address' => 'Hà Nội', 'role' => 'admin', 'position' => 'admin', 'is_active' => 1],
    ['username' => 'staff1', 'password' => password_hash('staff123', PASSWORD_DEFAULT), 'email' => 'staff1@gundamstore.vn', 'full_name' => 'Nhân viên bán hàng', 'phone' => '0902222222', 'address' => 'Đà Nẵng', 'role' => 'employee', 'position' => 'staff', 'is_active' => 1],
    ['username' => 'order_mgr', 'password' => password_hash('order123', PASSWORD_DEFAULT), 'email' => 'order@gundamstore.vn', 'full_name' => 'Quản lý đơn hàng', 'phone' => '0903333333', 'address' => 'Hồ Chí Minh', 'role' => 'employee', 'position' => 'order_manager', 'is_active' => 1],
    ['username' => 'product_mgr', 'password' => password_hash('product123', PASSWORD_DEFAULT), 'email' => 'product@gundamstore.vn', 'full_name' => 'Quản lý sản phẩm', 'phone' => '0904444444', 'address' => 'Cần Thơ', 'role' => 'employee', 'position' => 'product_manager', 'is_active' => 1],
    ['username' => 'customer1', 'password' => password_hash('customer123', PASSWORD_DEFAULT), 'email' => 'customer1@gmail.com', 'full_name' => 'Nguyễn Văn An', 'phone' => '0905555555', 'address' => 'Bắc Ninh', 'role' => 'user', 'position' => null, 'is_active' => 1],
    ['username' => 'customer2', 'password' => password_hash('customer123', PASSWORD_DEFAULT), 'email' => 'customer2@gmail.com', 'full_name' => 'Trần Thị Bình', 'phone' => '0906666666', 'address' => 'Hải Phòng', 'role' => 'user', 'position' => null, 'is_active' => 1],
];
$stmt = $conn->prepare('INSERT INTO users (username, password, email, full_name, phone, address, role, position, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE password=VALUES(password), full_name=VALUES(full_name), phone=VALUES(phone), address=VALUES(address), role=VALUES(role), position=VALUES(position), is_active=VALUES(is_active)');
foreach ($users as $u) {
    $position = $u['position'];
    $stmt->bind_param('ssssssssi', $u['username'], $u['password'], $u['email'], $u['full_name'], $u['phone'], $u['address'], $u['role'], $position, $u['is_active']);
    $stmt->execute();
}
$stmt->close();

function getUserIdByUsername($conn, $username)
{
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();
    return $id ?: null;
}


// 4. SEED COUPONS
$coupons = [
    ['code' => 'WELCOME10', 'description' => 'Giảm 10% cho đơn hàng đầu tiên', 'discount_type' => 'percent', 'discount_value' => 10.00, 'min_order' => 500000, 'max_uses' => 100, 'used_count' => 0, 'starts_at' => '2026-01-01 00:00:00', 'expires_at' => '2026-12-31 23:59:59', 'is_active' => 1],
    ['code' => 'SUMMER20', 'description' => 'Giảm 20% cho đơn hàng mùa hè', 'discount_type' => 'percent', 'discount_value' => 20.00, 'min_order' => 1000000, 'max_uses' => 50, 'used_count' => 0, 'starts_at' => '2026-06-01 00:00:00', 'expires_at' => '2026-08-31 23:59:59', 'is_active' => 1],
    ['code' => 'SHIPFREE', 'description' => 'Miễn phí vận chuyển', 'discount_type' => 'fixed', 'discount_value' => 30000, 'min_order' => 800000, 'max_uses' => 30, 'used_count' => 0, 'starts_at' => '2026-07-01 00:00:00', 'expires_at' => '2026-09-30 23:59:59', 'is_active' => 1],
];
$stmt = $conn->prepare('INSERT INTO coupons (code, description, discount_type, discount_value, min_order, max_uses, used_count, starts_at, expires_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE description=VALUES(description), discount_type=VALUES(discount_type), discount_value=VALUES(discount_value), min_order=VALUES(min_order), max_uses=VALUES(max_uses), used_count=VALUES(used_count), starts_at=VALUES(starts_at), expires_at=VALUES(expires_at), is_active=VALUES(is_active)');
foreach ($coupons as $c) {
    $stmt->bind_param('sssdiiisss', $c['code'], $c['description'], $c['discount_type'], $c['discount_value'], $c['min_order'], $c['max_uses'], $c['used_count'], $c['starts_at'], $c['expires_at'], $c['is_active']);
    $stmt->execute();
}
$stmt->close();


// 5. SEED NOTIFICATIONS
$stmt = $conn->prepare('INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)');
$notifications = [
    ['Khuyến mãi tháng 7', 'Giảm giá cực sốc cho các mẫu Gunpla mới và dụng cụ lắp ráp.', 'system'],
    ['Bộ sưu tập mới', 'Các mẫu Limited Edition đã về cửa hàng, đừng bỏ lỡ.', 'system'],
    ['Hỗ trợ giao hàng', 'Giao hàng nội thành trong 24 giờ khi đặt trước 18h.', 'order_update'],
];
foreach ($notifications as $n) {
    $stmt->bind_param('sss', $n[0], $n[1], $n[2]);
    $stmt->execute();
}
$stmt->close();


// 6. SEED ORDERS
$orders = [
    ['order_code' => 'GD260701A1', 'username' => 'customer1', 'full_name' => 'Nguyễn Văn An', 'phone' => '0905555555', 'email' => 'customer1@gmail.com', 'address' => 'Bắc Ninh', 'note' => 'Giao giờ hành chính', 'subtotal' => 2490000, 'shipping_fee' => 30000, 'total' => 2520000, 'payment_method' => 'cod', 'status' => 'completed', 'coupon_code' => 'WELCOME10', 'discount_amount' => 249000],
    ['order_code' => 'GD260702B2', 'username' => 'customer2', 'full_name' => 'Trần Thị Bình', 'phone' => '0906666666', 'email' => 'customer2@gmail.com', 'address' => 'Hải Phòng', 'note' => 'Gọi trước khi giao', 'subtotal' => 3990000, 'shipping_fee' => 30000, 'total' => 4020000, 'payment_method' => 'momo', 'status' => 'processing', 'coupon_code' => 'SUMMER20', 'discount_amount' => 798000],
    ['order_code' => 'GD260710C3', 'username' => 'customer1', 'full_name' => 'Nguyễn Văn An', 'phone' => '0905555555', 'email' => 'customer1@gmail.com', 'address' => 'Đà Nẵng', 'note' => 'Cho vào hộp đỏ', 'subtotal' => 5890000, 'shipping_fee' => 35000, 'total' => 5925000, 'payment_method' => 'vnpay', 'status' => 'pending', 'coupon_code' => 'SHIPFREE', 'discount_amount' => 30000],
];
$stmt = $conn->prepare('INSERT INTO orders (order_code, user_id, full_name, phone, email, address, note, subtotal, shipping_fee, total, payment_method, status, coupon_code, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), full_name = VALUES(full_name), phone = VALUES(phone), email = VALUES(email), address = VALUES(address), note = VALUES(note), subtotal = VALUES(subtotal), shipping_fee = VALUES(shipping_fee), total = VALUES(total), payment_method = VALUES(payment_method), status = VALUES(status), coupon_code = VALUES(coupon_code), discount_amount = VALUES(discount_amount), updated_at = NOW()');
foreach ($orders as $o) {
    $orderUserId = getUserIdByUsername($conn, $o['username']);
    if (!$orderUserId) {
        throw new Exception('Không tìm thấy user với username: ' . $o['username']);
    }

    $stmt->bind_param('sissssssddssss', $o['order_code'], $orderUserId, $o['full_name'], $o['phone'], $o['email'], $o['address'], $o['note'], $o['subtotal'], $o['shipping_fee'], $o['total'], $o['payment_method'], $o['status'], $o['coupon_code'], $o['discount_amount']);
    $stmt->execute();

    $orderId = $conn->insert_id;
    if ($orderId === 0) {
        $orderIdStmt = $conn->prepare('SELECT id FROM orders WHERE order_code = ? LIMIT 1');
        $orderIdStmt->bind_param('s', $o['order_code']);
        $orderIdStmt->execute();
        $orderIdStmt->bind_result($orderId);
        $orderIdStmt->fetch();
        $orderIdStmt->close();
    }

    if ($orderId) {
        $conn->query("DELETE FROM order_items WHERE order_id = $orderId");
    }

    $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)');

    // Chọn sản phẩm phù hợp theo slug để tránh phụ thuộc vào ID cứng
    $productSlug = 'hg-1-144-char-zaku-ii-red';
    $selectedProductName = 'HG 1/144 Char Zaku II Red';
    $selectedProductImage = 'char_zaku_red_hg.jpg';
    $selectedPrice = 450000;
    $selectedQty = 1;
    $selectedSubtotal = 450000;

    if ($o['username'] === 'customer2') {
        $productSlug = 'rg-1-144-gundam-epyon';
        $selectedProductName = 'RG 1/144 Gundam Epyon';
        $selectedProductImage = 'epyon_rg.jpg';
        $selectedPrice = 1150000;
        $selectedQty = 1;
        $selectedSubtotal = 1150000;
    } elseif ($o['username'] === 'customer1' && $o['subtotal'] >= 5800000) {
        $productSlug = 'pg-unleashed-1-60-rx-78-2-gundam';
        $selectedProductName = 'PG Unleashed 1/60 RX-78-2 Gundam';
        $selectedProductImage = 'PG Unleashed.jpg';
        $selectedPrice = 7850000;
        $selectedQty = 1;
        $selectedSubtotal = 7850000;
    }

    $productId = 0;
    $lookupStmt = $conn->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
    $lookupStmt->bind_param('s', $productSlug);
    $lookupStmt->execute();
    $lookupStmt->bind_result($productId);
    $lookupStmt->fetch();
    $lookupStmt->close();

    if (!$productId) {
        throw new Exception("Không tìm thấy sản phẩm với slug: $productSlug");
    }

    $itemStmt->bind_param('iisssii', $orderId, $productId, $selectedProductName, $selectedProductImage, $selectedPrice, $selectedQty, $selectedSubtotal);
    $itemStmt->execute();
    $itemStmt->close();
}
$stmt->close();

// ĐÓNG KẾT NỐI DB Ở CUỐI FILE
$conn->close();
echo "seed_more_data_completed\n";
?>