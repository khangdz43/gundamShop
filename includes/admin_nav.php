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
    'admin'           => __('role_admin'),
    'order_manager'   => __('role_order_manager'),
    'product_manager' => __('role_product_manager'),
    'staff'           => __('role_staff'),
];
$pos = $_SESSION['position'] ?? null;
$displayRole = $posLabels[$pos] ?? ((($_SESSION['role'] ?? '') === 'admin') ? __('role_admin') : __('role_staff'));
?>
<header class="site-header admin-navbar" style="overflow: visible !important;">
    <div class="header-container admin-header-container" style="overflow: visible !important;">
        <nav class="nav-main admin-nav-main">
            <button type="button" class="nav-toggle" id="navToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?php echo $adminBasePath; ?>index.php" class="logo-link admin-logo-link">
                <img class="logo" src="<?php echo $adminBasePath; ?>assets/images/LOGO.jpg" alt="Gundam Store" width="90">
            </a>
            <ul class="nav-menu admin-site-menu" id="navMenu">
            </ul>
        </nav>

        <div class="header-actions admin-header-actions" style="overflow: visible !important;">
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
            <a href="<?php echo $adminBasePath; ?>admin/categories.php" class="btn-header<?php echo in_array($currentAdminPage, ['categories.php']) ? ' active' : ''; ?>">
                <i class="fas fa-tags"></i> Danh mục
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

            <?php if (isAdmin()): ?>
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

            <!-- EXPORT EXCEL DROPDOWN -->
            <?php if (hasPermission('orders') || isAdmin()): ?>
            <div class="export-dropdown-wrapper">
                <button type="button" class="btn-header" id="exportBtnToggle" style="gap:5px;">
                    <i class="fas fa-file-excel"></i> <i class="fas fa-chevron-down" style="font-size:0.65rem;"></i>
                </button>
                <div class="export-dropdown-content" id="exportMenuBox">
                    <?php if (hasPermission('orders')): ?>
                    <a href="<?php echo $adminBasePath; ?>admin/export_excel.php?type=orders" class="export-menu-item">
                        <i class="fas fa-shopping-bag"></i> <?php echo __('export_orders'); ?>
                    </a>
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

            <a href="<?php echo $adminBasePath; ?>logout.php" class="btn-header btn-outline admin-logout" title="<?php echo __('logout'); ?>">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</header>

<style>
/* Style riêng cho Dropdown Export để chống đè Z-Index */
.export-dropdown-wrapper {
    position: relative !important;
    display: inline-block !important;
}

.export-dropdown-content {
    display: none;
    position: absolute !important;
    right: 0 !important;
    top: calc(100% + 5px) !important;
    background: #1e1e2d; /* Fallback màu tối nếu CSS var chưa load */
    background: var(--bg-card, #1e1e2d);
    border: 1px solid var(--border-color, #2b2b40);
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    z-index: 999999 !important;
    min-width: 190px;
    padding: 6px 0;
}

.export-dropdown-content.show {
    display: block !important;
}

.export-menu-item {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 10px 15px !important;
    color: var(--text-main, #ffffff) !important;
    text-decoration: none !important;
    font-size: 0.85rem !important;
    white-space: nowrap !important;
    transition: background 0.2s;
}

.export-menu-item:hover {
    background: rgba(31, 95, 255, 0.15) !important;
    color: #7da7ff !important;
}

.export-menu-item i {
    width: 16px;
    text-align: center;
    color: #28a745;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('exportBtnToggle');
    var menu = document.getElementById('exportMenuBox');

    if (btn && menu) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    }
});
</script>