<?php
/**
 * Core helper functions for Gundam Store
 */

function formatPrice($price) {
    return number_format((float)$price, 0, ',', '.') . ' ₫';
}

function generateOrderCode() {
    return 'GD' . date('ymd') . strtoupper(substr(uniqid(), -6));
}

function sanitize($conn, $value) {
    return mysqli_real_escape_string($conn, trim($value));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function setFlash($key, $message, $type = 'success') {
    $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
}

function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

function getCartCount($conn, $userId) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($result['total'] ?? 0);
}

function getProductById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $product;
}

function getOrderStatusLabel($status) {
    $keys = [
        'pending' => 'status_pending',
        'confirmed' => 'status_confirmed',
        'shipping' => 'status_shipping',
        'delivered' => 'status_delivered',
        'cancelled' => 'status_cancelled',
    ];
    if (function_exists('__') && isset($keys[$status])) {
        return __($keys[$status]);
    }
    $labels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy'
    ];
    return $labels[$status] ?? $status;
}

function ensureCouponsTable($conn) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    $conn->query("CREATE TABLE IF NOT EXISTS `coupons` (
        `id` int NOT NULL AUTO_INCREMENT,
        `code` varchar(50) NOT NULL,
        `description` varchar(255) DEFAULT NULL,
        `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
        `discount_value` decimal(10,2) NOT NULL DEFAULT '0.00',
        `min_order` decimal(12,2) NOT NULL DEFAULT '0.00',
        `max_uses` int DEFAULT NULL,
        `used_count` int NOT NULL DEFAULT '0',
        `starts_at` datetime DEFAULT NULL,
        `expires_at` datetime DEFAULT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $cols = ['coupon_code' => "VARCHAR(50) DEFAULT NULL", 'discount_amount' => "DECIMAL(12,2) DEFAULT 0"];
    foreach ($cols as $col => $def) {
        $r = $conn->query("SHOW COLUMNS FROM orders LIKE '$col'");
        if ($r && $r->num_rows === 0) {
            $conn->query("ALTER TABLE orders ADD COLUMN `$col` $def");
        }
        if ($r) $r->free();
    }
}

function sendUserNotification($conn, $userId, $title, $message, $type = 'info') {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param('iss', $userId, $title, $message);
    } else {
        $stmt->bind_param('isss', $userId, $title, $message, $type);
    }
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function notifyOrderStatusChange($conn, $order, $oldStatus, $newStatus) {
    if ($oldStatus === $newStatus) return;

    $userId = (int)$order['user_id'];
    $code = $order['order_code'];

    if ($newStatus === 'cancelled') {
        sendUserNotification(
            $conn,
            $userId,
            __('notif_order_cancelled'),
            sprintf(__('notif_order_cancelled_msg'), $code),
            'warning'
        );
        return;
    }

    sendUserNotification(
        $conn,
        $userId,
        __('notif_order_status'),
        sprintf(__('notif_order_status_msg'), $code, getOrderStatusLabel($newStatus)),
        'info'
    );
}

function restockOrderItems($conn, $orderId) {
    $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($items as $item) {
        $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param('ii', $item['quantity'], $item['product_id']);
        $stmt->execute();
        $stmt->close();
    }
}

function validateCoupon($conn, $code, $subtotal) {
    ensureCouponsTable($conn);
    $code = strtoupper(trim($code));
    if ($code === '') {
        return ['valid' => false, 'message' => __('coupon_invalid')];
    }

    $stmt = $conn->prepare("SELECT * FROM coupons WHERE UPPER(code) = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $coupon = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$coupon) {
        return ['valid' => false, 'message' => __('coupon_invalid')];
    }

    $now = time();
    if (!empty($coupon['starts_at']) && strtotime($coupon['starts_at']) > $now) {
        return ['valid' => false, 'message' => __('coupon_invalid')];
    }
    if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < $now) {
        return ['valid' => false, 'message' => __('coupon_invalid')];
    }
    if ($coupon['max_uses'] !== null && (int)$coupon['used_count'] >= (int)$coupon['max_uses']) {
        return ['valid' => false, 'message' => __('coupon_invalid')];
    }
    if ((float)$subtotal < (float)$coupon['min_order']) {
        return ['valid' => false, 'message' => __('coupon_invalid')];
    }

    if ($coupon['discount_type'] === 'percent') {
        $discount = round($subtotal * (float)$coupon['discount_value'] / 100);
    } else {
        $discount = min((float)$coupon['discount_value'], $subtotal);
    }

    return [
        'valid' => true,
        'code' => $coupon['code'],
        'discount' => $discount,
        'coupon_id' => (int)$coupon['id'],
    ];
}

function incrementCouponUsage($conn, $couponId) {
    $stmt = $conn->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
    $stmt->bind_param('i', $couponId);
    $stmt->execute();
    $stmt->close();
}

function getAverageReviewRating($conn) {
    $r = $conn->query("SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total FROM reviews");
    if (!$r) return ['avg' => 0, 'count' => 0];
    $row = $r->fetch_assoc();
    return [
        'avg' => round((float)$row['avg_rating'], 1),
        'count' => (int)$row['total'],
    ];
}

function getOrderStatusClass($status) {
    $classes = [
        'pending' => 'status-pending',
        'confirmed' => 'status-confirmed',
        'shipping' => 'status-shipping',
        'delivered' => 'status-delivered',
        'cancelled' => 'status-cancelled'
    ];
    return $classes[$status] ?? '';
}

function calculateShippingFee($subtotal) {
    return $subtotal >= 2000000 ? 0 : 30000;
}

function ensureCartSelectedColumn($conn) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    $r = $conn->query("SHOW COLUMNS FROM cart LIKE 'selected'");
    if ($r && $r->num_rows === 0) {
        $conn->query("ALTER TABLE cart ADD COLUMN `selected` TINYINT(1) NOT NULL DEFAULT 1");
    }
    if ($r) $r->free();
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}
