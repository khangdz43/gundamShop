</main>
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-col">
            <div class="footer-logo">
                <img src="<?php echo $basePath ?? ''; ?>assets/images/LOGO.jpg" alt="Logo">
                <span class="footer-logo-text">Gundam HUMG</span>
            </div>
            <p><?php echo __('footer_about'); ?></p>
            <div style="display:flex;gap:12px;margin-top:16px;">
                <a href="#" style="color:#999;font-size:18px;"><i class="fab fa-facebook"></i></a>
                <a href="#" style="color:#999;font-size:18px;"><i class="fab fa-instagram"></i></a>
                <a href="#" style="color:#999;font-size:18px;"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4><?php echo __('footer_links'); ?></h4>
            <a href="<?php echo $basePath ?? ''; ?>products.php"><?php echo __('products'); ?></a>
            <a href="<?php echo $basePath ?? ''; ?>products.php?type=SALE"><?php echo __('footer_promotions'); ?></a>
            <a href="<?php echo $basePath ?? ''; ?>orders.php"><?php echo __('footer_track_order'); ?></a>
            <a href="#">Điều khoản bảo mật</a>
        </div>
        <div class="footer-col">
            <h4><?php echo __('footer_contact'); ?></h4>
            <p><i class="fas fa-map-marker-alt"></i> Trường ĐH Mỏ - Địa chất</p>
            <p><i class="fas fa-phone"></i> 0969 946 335</p>
            <p><i class="fas fa-envelope"></i> gundamstore@humg.vn</p>
            
            <h4 style="margin-top: 30px;">Đăng ký nhận tin</h4>
            <form style="display:flex; gap:8px;" onsubmit="event.preventDefault();">
                <input type="email" placeholder="Email của bạn..." class="form-control" style="background:#222; border-color:#333; color:#fff;">
                <button class="btn btn-red btn-sm" style="padding: 0 16px;" type="button"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Gundam Store HUMG - <?php echo __('footer_rights'); ?></p>
        <div class="footer-payment-icons">
            <span class="payment-icon">VISA</span>
            <span class="payment-icon">MASTERCARD</span>
            <span class="payment-icon">JCB</span>
            <span class="payment-icon">COD</span>
        </div>
    </div>
</footer>
<?php if (!isAdmin() && !isEmployee()): ?>
<?php include __DIR__ . '/chatbot.php'; ?>
<?php endif; ?>
<script src="<?php echo $basePath ?? ''; ?>assets/app.js"></script>
</body>
</html>
