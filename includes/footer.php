</main>
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-col">
            <h4>Gundam Store HUMG</h4>
            <p>Chuyên cung cấp mô hình Gunpla chính hãng Bandai - HG, RG, MG, PG, SD.</p>
        </div>
        <div class="footer-col">
            <h4>Liên kết</h4>
            <a href="<?php echo $basePath ?? ''; ?>products.php">Sản phẩm</a>
            <a href="<?php echo $basePath ?? ''; ?>products.php?type=SALE">Khuyến mãi</a>
            <a href="<?php echo $basePath ?? ''; ?>orders.php">Theo dõi đơn hàng</a>
        </div>
        <div class="footer-col">
            <h4>Liên hệ</h4>
            <p><i class="fas fa-map-marker-alt"></i> Trường ĐH Mỏ - Địa chất</p>
            <p><i class="fas fa-phone"></i> 0969 946 335</p>
            <p><i class="fas fa-envelope"></i> gundamstore@humg.vn</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Gundam Store HUMG - All Rights Reserved</p>
    </div>
</footer>
<?php if (!isAdmin() && !isEmployee()): ?>
<?php include __DIR__ . '/chatbot.php'; ?>
<?php endif; ?>
<script src="<?php echo $basePath ?? ''; ?>assets/app.js"></script>
</body>
</html>
