<?php
if (!isset($pageTitle)) $pageTitle = 'Gundam Store HUMG';
if (!isset($basePath)) $basePath = '';

$cartCount = 0;
if (isLoggedIn() && !isAdmin()) {
    $cartCount = getCartCount($conn, getUserId());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/style.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="<?php echo $basePath; ?>assets/images/favicon.ico">
</head>
<body data-base-path="<?php echo htmlspecialchars($basePath); ?>">
<?php if (isAdmin()): ?>
<?php include __DIR__ . '/admin_nav.php'; ?>
<?php elseif (isEmployee()): ?>
<?php include __DIR__ . '/admin_nav.php'; ?>
<?php else: ?>
<header class="site-header">
    <div class="header-container">
        <nav class="nav-main">
            <button type="button" class="nav-toggle" id="navToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?php echo $basePath; ?>index.php" class="logo-link">
                <img class="logo" src="<?php echo $basePath; ?>assets/images/LOGO.jpg" alt="Gundam Store" width="90">
            </a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo $basePath; ?>index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li><a href="<?php echo $basePath; ?>products.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
                <li><a href="<?php echo $basePath; ?>products.php?type=SALE"><i class="fas fa-tag"></i> Giảm giá</a></li>
                <li><a href="<?php echo $basePath; ?>index.php#highlight"><i class="fas fa-star"></i> Nổi bật</a></li>
            </ul>
        </nav>

        <form class="search-form live-search-form" action="<?php echo $basePath; ?>products.php" method="GET" role="search">
            <input type="text" name="search" placeholder="Tìm mô hình Gundam..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
            <div class="search-suggestions" aria-live="polite"></div>
        </form>

        <div class="header-actions">
            <button type="button" class="theme-toggle" title="Chuyển đổi chủ đề" aria-label="Chuyển đổi chủ đề"><i class="fas fa-sun"></i></button>
            <?php if (isLoggedIn()): ?>
                <!-- Notification Bell -->
                <div class="notification-dropdown-container" style="position:relative; display:inline-block;">
                    <button type="button" class="btn-header btn-notification" id="notificationBell" style="position:relative; background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: var(--text-main); width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius:var(--radius-md); padding:0; cursor:pointer;">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge" style="display:none; position:absolute; top:-5px; right:-5px; background:var(--primary-red); color:white; border-radius:50%; width:18px; height:18px; font-size:10px; font-weight:bold; display:flex; align-items:center; justify-content:center; box-shadow:0 0 5px rgba(255,0,0,0.5);">0</span>
                    </button>
                    <div class="dropdown-menu notification-menu" id="notificationDropdown" style="display:none; position:absolute; right:0; top:120%; width:320px; max-height:400px; overflow-y:auto; background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); box-shadow:var(--shadow-main); z-index:1000; padding:10px 0; opacity:1; visibility:visible; transform:none;">
                        <div style="padding:10px 15px; border-bottom:1px solid var(--border-color); font-weight:bold; color:white; display:flex; justify-content:space-between; align-items:center; font-family:'Outfit';">
                            <span>Thông báo mới</span>
                        </div>
                        <div id="notificationList" style="max-height:300px; overflow-y:auto;">
                            <div style="padding:15px; text-align:center; color:var(--text-muted);">Đang tải thông báo...</div>
                        </div>
                    </div>
                </div>

                <a href="<?php echo $basePath; ?>cart.php" class="btn-header btn-cart">
                     <i class="fas fa-shopping-cart"></i> Giỏ hàng
                     <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                </a>
                <div class="user-dropdown">
                    <button class="btn-header btn-user"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></button>
                    <div class="dropdown-menu">
                        <a href="<?php echo $basePath; ?>profile.php"><i class="fas fa-id-card"></i> Hồ sơ</a>
                        <a href="<?php echo $basePath; ?>orders.php"><i class="fas fa-receipt"></i> Đơn hàng</a>
                        <a href="<?php echo $basePath; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $basePath; ?>cart.php" class="btn-header btn-cart">
                    <i class="fas fa-shopping-cart"></i> Giỏ hàng
                </a>
                <a href="<?php echo $basePath; ?>login.php" class="btn-header btn-primary"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                <a href="<?php echo $basePath; ?>register.php" class="btn-header btn-outline">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php endif; ?>
<main class="site-main">
