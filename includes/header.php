<?php
if (!isset($pageTitle)) $pageTitle = 'Gundam Store HUMG';
if (!isset($basePath)) $basePath = '';

$cartCount = 0;
if (isLoggedIn() && !isAdmin()) {
    $cartCount = getCartCount($conn, getUserId());
}
?>
<!DOCTYPE html>
<html lang="<?php echo currentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
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
    <div class="announcement-bar">
        🚀 <?php echo __('promo_message'); ?> <a href="<?php echo $basePath; ?>products.php?type=SALE"><?php echo __('promo_view'); ?></a>
    </div>
    <div class="header-container">
        <nav class="nav-main">
            <button type="button" class="nav-toggle" id="navToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?php echo $basePath; ?>index.php" class="logo-link">
                <img class="logo" src="<?php echo $basePath; ?>assets/images/LOGO.jpg" alt="Gundam Store">
                <div class="logo-text">
                    <span class="logo-name">Gundam HUMG</span>
                    <span class="logo-tagline">Gunpla Store</span>
                </div>
            </a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo $basePath; ?>index.php"><i class="fas fa-home"></i> <?php echo __('home'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>products.php"><i class="fas fa-box"></i> <?php echo __('products'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>products.php?type=SALE"><i class="fas fa-tag"></i> <?php echo __('sale'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>index.php#highlight"><i class="fas fa-star"></i> <?php echo __('highlight'); ?></a></li>
            </ul>
        </nav>

        <form class="search-form live-search-form" action="<?php echo $basePath; ?>products.php" method="GET" role="search">
            <input type="text" name="search" placeholder="<?php echo __('search_placeholder'); ?>" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
            <div class="search-suggestions" aria-live="polite"></div>
        </form>

        <div class="header-actions">
            <div class="lang-switcher">
                <?php
                $curLang = currentLang();
                ?>
                <a href="<?php echo htmlspecialchars(langUrl('vi')); ?>" class="btn-header btn-sm <?php echo $curLang === 'vi' ? 'active-lang' : ''; ?>" title="<?php echo __('lang_vi'); ?>">VI</a>
                <a href="<?php echo htmlspecialchars(langUrl('en')); ?>" class="btn-header btn-sm <?php echo $curLang === 'en' ? 'active-lang' : ''; ?>" title="<?php echo __('lang_en'); ?>">EN</a>
            </div>
            <button type="button" class="theme-toggle" title="<?php echo __('theme_toggle'); ?>" aria-label="<?php echo __('theme_toggle'); ?>"><i class="fas fa-sun"></i></button>
            <?php if (isLoggedIn()): ?>
                <!-- Notification Bell -->
                <div class="notification-dropdown-container">
                    <button type="button" class="btn-notification" id="notificationBell">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge">0</span>
                    </button>
                    <div class="dropdown-menu notification-menu" id="notificationDropdown">
                        <div class="notification-dropdown-header">
                            <span><?php echo __('notifications_new'); ?></span>
                        </div>
                        <div id="notificationList">
                            <div style="padding:15px; text-align:center; color:var(--text-muted);"><?php echo __('loading_notifications'); ?></div>
                        </div>
                    </div>
                </div>

                <a href="<?php echo $basePath; ?>cart.php" class="btn-cart">
                     <i class="fas fa-shopping-cart"></i> <span class="cart-text"><?php echo __('cart'); ?></span>
                     <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                </a>
                <div class="user-dropdown">
                    <button class="btn-user"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></button>
                    <div class="dropdown-menu">
                        <a href="<?php echo $basePath; ?>profile.php"><i class="fas fa-id-card"></i> <?php echo __('profile'); ?></a>
                        <a href="<?php echo $basePath; ?>orders.php"><i class="fas fa-receipt"></i> <?php echo __('orders'); ?></a>
                        <a href="<?php echo $basePath; ?>logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo __('logout'); ?></a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $basePath; ?>cart.php" class="btn-cart">
                    <i class="fas fa-shopping-cart"></i> <span class="cart-text"><?php echo __('cart'); ?></span>
                </a>
                <a href="<?php echo $basePath; ?>login.php" class="btn-header btn-primary"><i class="fas fa-sign-in-alt"></i> <?php echo __('login'); ?></a>
                <a href="<?php echo $basePath; ?>register.php" class="btn-header btn-outline"><i class="fas fa-user-plus"></i> <?php echo __('register'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php endif; ?>
<main class="site-main">
