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
    if (empty($url)) {
        $url = 'index.php';
    }

    if (preg_match('#^(https?:)?//#i', $url) || preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
        header("Location: $url");
        exit();
    }

    $baseUrl = function_exists('getAppBaseUrl') ? getAppBaseUrl() : '';
    $normalizedPath = '/' . ltrim($url, '/');
    $target = $baseUrl !== '' ? rtrim($baseUrl, '/') . $normalizedPath : $normalizedPath;

    header("Location: $target");
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
    // Mapping status DB mới: pending, processing, shipped, completed, cancelled
    $keys = [
        'pending'    => 'status_pending',
        'processing' => 'status_processing',
        'shipped'    => 'status_shipped',
        'completed'  => 'status_completed',
        'cancelled'  => 'status_cancelled',
    ];
    if (function_exists('__') && isset($keys[$status])) {
        return __($keys[$status]);
    }
    $labels = [
        'pending'    => 'Chờ xác nhận',
        'processing' => 'Đang xử lý',
        'shipped'    => 'Đang giao hàng',
        'completed'  => 'Hoàn thành',
        'cancelled'  => 'Đã hủy',
    ];
    return $labels[$status] ?? $status;
}

/**
 * Gửi thông báo cho một user cụ thể.
 * Schema mới: notifications (nội dung) + notification_users (trạng thái đọc).
 * @param int|null $userId  null = broadcast tất cả user.
 */
function sendUserNotification($conn, $userId, $title, $message, $type = 'system') {
    try {
        // 1. Chèn nội dung thông báo vào bảng notifications
        $stmt = $conn->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param('sss', $title, $message, $type);
        $stmt->execute();
        $notifId = (int)$conn->insert_id;
        $stmt->close();

        if ($notifId <= 0) return false;

        // 2a. Nếu có userId cụ thể -> chèn 1 dòng vào notification_users
        if ($userId !== null) {
            $uid = (int)$userId;
            $stmt = $conn->prepare("INSERT IGNORE INTO notification_users (user_id, notification_id, is_read) VALUES (?, ?, 0)");
            if ($stmt) {
                $stmt->bind_param('ii', $uid, $notifId);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            // 2b. Broadcast: chèn cho tất cả user đang active
            $users = $conn->query("SELECT id FROM users WHERE is_active = 1");
            if ($users) {
                $ins = $conn->prepare("INSERT IGNORE INTO notification_users (user_id, notification_id, is_read) VALUES (?, ?, 0)");
                while ($u = $users->fetch_assoc()) {
                    $uid = (int)$u['id'];
                    $ins->bind_param('ii', $uid, $notifId);
                    $ins->execute();
                }
                $ins->close();
                $users->free();
            }
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function notifyOrderStatusChange($conn, $order, $oldStatus, $newStatus) {
    if ($oldStatus === $newStatus) return;

    $userId = (int)$order['user_id'];
    $code = $order['order_code'];
    $type = ($newStatus === 'cancelled') ? 'personal' : 'order_update';

    if (function_exists('__')) {
        $titleKey = ($newStatus === 'cancelled') ? 'notif_order_cancelled' : 'notif_order_status';
        $msgKey   = ($newStatus === 'cancelled') ? 'notif_order_cancelled_msg' : 'notif_order_status_msg';
        $title   = __($titleKey);
        $message = sprintf(__($msgKey), $code, getOrderStatusLabel($newStatus));
    } else {
        $label = getOrderStatusLabel($newStatus);
        if ($newStatus === 'cancelled') {
            $title   = 'Đơn hàng đã bị hủy';
            $message = "Đơn hàng #{$code} đã bị hủy.";
        } else {
            $title   = 'Cập nhật trạng thái đơn hàng';
            $message = "Đơn hàng #{$code} đã chuyển sang trạng thái: {$label}.";
        }
    }

    sendUserNotification($conn, $userId, $title, $message, $type);
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
    // Mapping status DB mới: pending, processing, shipped, completed, cancelled
    $classes = [
        'pending'    => 'status-pending',
        'processing' => 'status-processing',
        'shipped'    => 'status-shipped',
        'completed'  => 'status-completed',
        'cancelled'  => 'status-cancelled',
    ];
    return $classes[$status] ?? '';
}

/**
 * Stub: bảng coupons đã tồn tại trong DB mới, không cần tạo động nữa.
 * Giữ hàm này để tránh lỗi undefined function ở các file cũ còn gọi nó.
 */
function ensureCouponsTable($conn) {
    // No-op: coupons table already exists in current schema
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
