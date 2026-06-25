<?php
/**
 * Authentication & session helpers
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

define('REMEMBER_COOKIE', 'gs_remember');
define('REMEMBER_USER_COOKIE', 'gs_username');
define('REMEMBER_DAYS', 30);

function ensureRememberColumns($conn) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN remember_token VARCHAR(64) DEFAULT NULL");
    }
    if ($result) $result->free();

    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_expires'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN remember_expires DATETIME DEFAULT NULL");
    }
    if ($result) $result->free();
}


function getAppBaseUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $base = $base === '/' ? '' : rtrim($base, '/');
    return $scheme . '://' . $host . $base;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isEmployee() {
    return isLoggedIn() && isset($_SESSION['role']) && 
           in_array($_SESSION['role'], ['admin', 'employee'], true);
}

/**
 * Kiểm tra quyền theo chức vụ
 * @param string $action: 'products','orders','returns','users','notifications','ai'
 */
function hasPermission($action) {
    if (!isLoggedIn()) return false;
    $pos  = $_SESSION['position'] ?? null;
    $role = $_SESSION['role']     ?? 'user';

    // Admin có toàn quyền
    if ($role === 'admin' || $pos === 'admin') return true;

    /**
     * Mỗi chức vụ chỉ được truy cập đúng chức năng của mình:
     *  - order_manager  : quản lý đơn hàng
     *  - return_manager : quản lý đổi trả
     *  - staff          : xem đơn hàng + đổi trả (không sửa)
     * 'dashboard' luôn được phép cho mọi nhân viên.
     * 'notifications' chỉ hiển thị phần đọc (gửi thông báo chỉ admin).
     */
    $permissions = [
        'order_manager'  => ['dashboard', 'orders', 'notifications'],
        'return_manager' => ['dashboard', 'returns', 'notifications'],
        'staff'          => ['dashboard', 'orders', 'returns', 'notifications'],
    ];

    $allowed = $permissions[$pos] ?? [];
    return in_array($action, $allowed, true);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        redirect('login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;text-align:center;padding:60px;background:#0d0d0d;color:#f0f0f0;min-height:100vh;">
            <i style="font-size:4rem;color:#e10600;" class="fas fa-ban"></i>
            <h2 style="color:#e10600;margin:20px 0 10px;">Không có quyền truy cập</h2>
            <p style="color:#aaa;">Trang này chỉ dành cho Quản trị viên.</p>
            <a href="../index.php" style="color:#1f5fff;">← Về trang chủ</a>
        </div>');
    }
}

function requireEmployee() {
    requireLogin();
    if (!isEmployee()) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;text-align:center;padding:60px;background:#0d0d0d;color:#f0f0f0;min-height:100vh;">
            <i style="font-size:4rem;color:#e10600;" class="fas fa-lock"></i>
            <h2 style="color:#e10600;margin:20px 0 10px;">Không có quyền truy cập</h2>
            <p style="color:#aaa;">Bạn cần đăng nhập với tài khoản nhân viên.</p>
            <a href="../index.php" style="color:#1f5fff;">← Về trang chủ</a>
        </div>');
    }
}

function requirePermission($action) {
    requireLogin();
    if (!isEmployee()) redirect('../login.php');
    if (!hasPermission($action)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;text-align:center;padding:60px;background:#0d0d0d;color:#f0f0f0;min-height:100vh;">
            <i style="font-size:4rem;color:#ffc107;" class="fas fa-shield-alt"></i>
            <h2 style="color:#ffc107;margin:20px 0 10px;">Không có quyền</h2>
            <p style="color:#aaa;">Chức vụ của bạn không có quyền thực hiện thao tác này.</p>
            <a href="index.php" style="color:#1f5fff;">← Về Dashboard</a>
        </div>');
    }
}

function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;

    $userId = getUserId();
    $stmt = $conn->prepare("SELECT id, username, email, full_name, phone, address, role FROM users WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user;
}

function loginUser($user) {
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['position'] = $user['position'] ?? null;
}

function setRememberMe($conn, $userId, $username) {
    ensureRememberColumns($conn);
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $expires = date('Y-m-d H:i:s', time() + REMEMBER_DAYS * 86400);

    $stmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_expires = ? WHERE id = ?");
    $stmt->bind_param("ssi", $hash, $expires, $userId);
    $stmt->execute();
    $stmt->close();

    $cookieOpts = [
        'expires' => time() + REMEMBER_DAYS * 86400,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    setcookie(REMEMBER_COOKIE, $token, $cookieOpts);
    setcookie(REMEMBER_USER_COOKIE, $username, $cookieOpts);
}

function clearRememberMe($conn, $userId = null) {
    if ($userId) {
        ensureRememberColumns($conn);
        $stmt = $conn->prepare("UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }

    $expired = time() - 3600;
    setcookie(REMEMBER_COOKIE, '', ['expires' => $expired, 'path' => '/']);
    setcookie(REMEMBER_USER_COOKIE, '', ['expires' => $expired, 'path' => '/']);
}

function tryRememberLogin($conn) {
    if (isLoggedIn() || empty($_COOKIE[REMEMBER_COOKIE])) {
        return;
    }

    ensureRememberColumns($conn);
    $token = $_COOKIE[REMEMBER_COOKIE];
    $hash = hash('sha256', $token);

        $stmt = $conn->prepare(
    "SELECT id, username, password, role, is_active FROM users WHERE remember_token = ? AND remember_expires > NOW() AND is_active = 1"
);
    $stmt->bind_param("s", $hash);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        loginUser($user);
    } else {
        clearRememberMe($conn);
    }
}

function logoutUser() {
    global $conn;
    if (isLoggedIn()) {
        clearRememberMe($conn, getUserId());
    }
    session_unset();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getRememberedUsername() {
    return $_COOKIE[REMEMBER_USER_COOKIE] ?? '';
}

tryRememberLogin($conn);
