<?php
require_once __DIR__ . '/auth.php';
$adminBasePath    = $basePath ?? '';
$currentAdminPage = basename($_SERVER['SCRIPT_NAME'] ?? '');

if (!function_exists('adminNavActive')) {
    function adminNavActive($page, $currentAdminPage) {
        return $page === $currentAdminPage ? ' active' : '';
    }
}

$posLabels = [
    'admin'          => __('role_admin'),
    'order_manager'  => __('role_order_manager'),
    'return_manager' => __('role_return_manager'),
    'staff'          => __('role_staff'),
];
$pos = $_SESSION['position'] ?? null;
$displayRole = $posLabels[$pos] ?? ((($_SESSION['role'] ?? '') === 'admin') ? __('role_admin') : __('role_staff'));
?>
<header class="site-header admin-navbar">
    <div class="header-container admin-header-container">
        <nav class="nav-main admin-nav-main">
            <button type="button" class="nav-toggle" id="navToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?php echo $adminBasePath; ?>index.php" class="logo-link admin-logo-link">
                <img class="logo" src="<?php echo $adminBasePath; ?>assets/images/LOGO.jpg" alt="Gundam Store" width="90">
            </a>
            <ul class="nav-menu admin-site-menu" id="navMenu">
                <li><a href="<?php echo $adminBasePath; ?>index.php"><i class="fas fa-home"></i> <?php echo __('home'); ?></a></li>
                <li><a href="<?php echo $adminBasePath; ?>products.php"><i class="fas fa-box"></i> <?php echo __('products'); ?></a></li>
            </ul>
        </nav>

        <div class="header-actions admin-header-actions">
            <button type="button" class="theme-toggle" title="<?php echo __('theme_toggle'); ?>" aria-label="<?php echo __('theme_toggle'); ?>">
                <i class="fas fa-sun"></i>
            </button>

            <span class="welcome-text admin-welcome">
                <span style="font-size:0.75rem;color:var(--text-muted);"><?php echo $displayRole; ?></span>
                <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?></strong>
            </span>

            <a href="<?php echo $adminBasePath; ?>admin/index.php" class="btn-header btn-admin<?php echo adminNavActive('index.php', $currentAdminPage); ?>">
                <i class="fas fa-tachometer-alt"></i> <?php echo __('dashboard'); ?>
            </a>

            <?php if (hasPermission('products')): ?>
            <a href="<?php echo $adminBasePath; ?>admin/models.php" class="btn-header<?php echo in_array($currentAdminPage, ['models.php','add_model.php','edit_model.php']) ? ' active' : ''; ?>">
                <i class="fas fa-robot"></i> <?php echo __('products'); ?>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('orders')): ?>
            <a href="<?php echo $adminBasePath; ?>admin/orders.php" class="btn-header<?php echo in_array($currentAdminPage, ['orders.php','order_detail.php']) ? ' active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> <?php echo __('orders'); ?>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('returns')): ?>
            <a href="<?php echo $adminBasePath; ?>admin/returns.php" class="btn-header<?php echo in_array($currentAdminPage, ['returns.php']) ? ' active' : ''; ?>">
                <i class="fas fa-undo"></i> <?php echo __('returns'); ?>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('users')): ?>
            <a href="<?php echo $adminBasePath; ?>admin/users.php" class="btn-header<?php echo in_array($currentAdminPage, ['users.php','add_user.php','edit_user.php']) ? ' active' : ''; ?>">
                <i class="fas fa-users"></i> <?php echo __('users'); ?>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('notifications')): ?>
            <a href="<?php echo $adminBasePath; ?>admin/send_notification.php" class="btn-header<?php echo in_array($currentAdminPage, ['send_notification.php']) ? ' active' : ''; ?>">
                <i class="fas fa-paper-plane"></i> <?php echo __('send_notif'); ?>
            </a>
            <?php endif; ?>

            <?php if (isAdmin()): ?>
            <a href="<?php echo $adminBasePath; ?>admin/coupons.php" class="btn-header<?php echo in_array($currentAdminPage, ['coupons.php']) ? ' active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> <?php echo __('coupons'); ?>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('ai')): ?>
            <a href="<?php echo $adminBasePath; ?>admin/ai_strategy.php" class="btn-header<?php echo in_array($currentAdminPage, ['ai_strategy.php']) ? ' active' : ''; ?>"
               style="background:linear-gradient(135deg,rgba(31,95,255,0.3),rgba(125,167,255,0.2));border-color:rgba(31,95,255,0.5);">
                <i class="fas fa-brain"></i> AI
            </a>
            <?php endif; ?>

            <?php if (hasPermission('orders') || isAdmin()): ?>
            <div style="position:relative;display:inline-block;" id="exportDropdownContainer">
                <button type="button" class="btn-header" onclick="toggleExportMenu()" title="<?php echo __('export_data'); ?>"
                        style="gap:4px;">
                    <i class="fas fa-file-excel"></i> <i class="fas fa-chevron-down" style="font-size:0.65rem;"></i>
                </button>
                <div id="exportDropdownMenu" style="display:none;position:absolute;right:0;top:120%;background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;box-shadow:var(--shadow-main);z-index:1000;min-width:180px;overflow:hidden;">
                    <?php if (hasPermission('orders')): ?>
                    <a href="<?php echo $adminBasePath; ?>admin/export_excel.php?type=orders" class="export-menu-item">
                        <i class="fas fa-shopping-bag"></i> <?php echo __('export_orders'); ?>
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('orders')): ?>
                    <a href="<?php echo $adminBasePath; ?>admin/export_excel.php?type=revenue" class="export-menu-item">
                        <i class="fas fa-chart-line"></i> <?php echo __('export_revenue'); ?>
                    </a>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                    <a href="<?php echo $adminBasePath; ?>admin/export_excel.php?type=users" class="export-menu-item">
                        <i class="fas fa-users"></i> <?php echo __('export_users'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="notification-dropdown-container" style="position:relative; display:inline-block;">
                <button type="button" class="btn-header" id="notificationBell"
                        style="position:relative; width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; padding:0; cursor:pointer;">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationBadge"
                          style="display:none; position:absolute; top:-5px; right:-5px; background:#e10600; color:white; border-radius:50%; width:18px; height:18px; font-size:10px; font-weight:bold; align-items:center; justify-content:center; box-shadow:0 0 5px rgba(255,0,0,0.5);">0</span>
                </button>
                <div id="notificationDropdown"
                     style="display:none; position:absolute; right:0; top:120%; width:320px; max-height:400px; overflow-y:auto; background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); box-shadow:var(--shadow-main); z-index:2000; padding:10px 0;">
                    <div class="notification-dropdown-header" style="padding:10px 15px; border-bottom:1px solid var(--border-color); font-weight:bold; color:var(--text-main); font-family:'Outfit';"><?php echo __('notifications'); ?></div>
                    <div id="notificationList" style="max-height:300px; overflow-y:auto;">
                        <div style="padding:15px; text-align:center; color:var(--text-muted);"><?php echo __('loading_notifications'); ?></div>
                    </div>
                </div>
            </div>

            <a href="<?php echo $adminBasePath; ?>logout.php" class="btn-header btn-outline admin-logout" title="<?php echo __('logout'); ?>">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</header>

<style>
.export-menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    color: var(--text-main);
    text-decoration: none;
    font-size: 0.88rem;
    transition: background 0.15s;
}
.export-menu-item:hover {
    background: rgba(31,95,255,0.1);
    color: #7da7ff;
}
.export-menu-item i { width: 16px; text-align: center; color: #28a745; }
</style>

<script>
function toggleExportMenu() {
    var menu = document.getElementById('exportDropdownMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    var container = document.getElementById('exportDropdownContainer');
    if (container && !container.contains(e.target)) {
        var menu = document.getElementById('exportDropdownMenu');
        if (menu) menu.style.display = 'none';
    }
});
</script>
