</main>
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-col">
            <h4>Gundam Store HUMG</h4>
            <p><?php echo __('footer_about'); ?></p>
        </div>
        <div class="footer-col">
            <h4><?php echo __('footer_links'); ?></h4>
            <a href="<?php echo $basePath ?? ''; ?>products.php"><?php echo __('products'); ?></a>
            <a href="<?php echo $basePath ?? ''; ?>products.php?type=SALE"><?php echo __('footer_promotions'); ?></a>
            <a href="<?php echo $basePath ?? ''; ?>orders.php"><?php echo __('footer_track_order'); ?></a>
        </div>
        <div class="footer-col">
            <h4><?php echo __('footer_contact'); ?></h4>
            <p><i class="fas fa-map-marker-alt"></i> Trường ĐH Mỏ - Địa chất</p>
            <p><i class="fas fa-phone"></i> 0969 946 335</p>
            <p><i class="fas fa-envelope"></i> gundamstore@humg.vn</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Gundam Store HUMG - <?php echo __('footer_rights'); ?></p>
    </div>
</footer>
<?php if (!isAdmin() && !isEmployee()): ?>
<?php include __DIR__ . '/chatbot.php'; ?>
<?php endif; ?>
<script src="<?php echo $basePath ?? ''; ?>assets/app.js"></script>
</body>
</html>
