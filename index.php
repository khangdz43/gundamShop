<?php
session_start();
include "config/db.php";
require_once "includes/auth.php";

$pageTitle = 'Gundam Store HUMG - Mô Hình Chính Hãng';
include "includes/header.php";
?>

<!-- HERO BANNER -->
<section class="hero-banner">
    <div class="hero-slider">
        <div class="hero-slide active">
            <img src="assets/images/Banner.jpg" alt="Gundam Banner">
            <div class="hero-content">
                <h1 class="hero-title">GUNDAM STORE HUMG</h1>
                <p class="hero-subtitle">Mô hình Gundam chính hãng 100% từ Nhật Bản. Sưu tập ngay những bộ kit độc đáo nhất!</p>
                <a href="#category" class="hero-btn">KHÁM PHÁ NGAY</a>
            </div>
        </div>
        <div class="hero-slide">
            <img src="assets/images/Banner2.jpg" alt="Gundam Collection">
            <div class="hero-content">
                <h1 class="hero-title">BỘ SƯU TẬP ĐỘC QUYỀN</h1>
                <p class="hero-subtitle">Hơn 100+ mẫu Gundam từ HG đến PG. Cập nhật mẫu mới hàng tuần!</p>
                <a href="products.php" class="hero-btn">XEM TẤT CẢ</a>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES SECTION -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3 class="feature-title">CHÍNH HÃNG 100%</h3>
                <p class="feature-desc">Sản phẩm Bandai Spirits chính hãng với tem bảo hành, đảm bảo chất lượng</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shipping-fast"></i></div>
                <h3 class="feature-title">MIỄN PHÍ VẬN CHUYỂN</h3>
                <p class="feature-desc">Miễn phí vận chuyển cho đơn hàng từ 2.000.000₫ trên toàn quốc</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h3 class="feature-title">HỖ TRỢ 24/7</h3>
                <p class="feature-desc">Đội ngũ tư vấn viên sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-exchange-alt"></i></div>
                <h3 class="feature-title">ĐỔI TRẢ DỄ DÀNG</h3>
                <p class="feature-desc">Đổi trả trong vòng 7 ngày nếu sản phẩm có lỗi từ nhà sản xuất</p>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORY SECTION -->
<section id="category" class="category-section">
    <div class="container">
        <div style="text-align: center; margin-bottom: 40px;">
            <h2 class="page-title">DANH MỤC SẢN PHẨM</h2>
            <p class="page-subtitle">Khám phá bộ sưu tập Gundam đa dạng với nhiều phân khúc từ cơ bản đến cao cấp</p>
        </div>

        <div class="category-grid">
            <?php
            // Danh mục sản phẩm mẫu
            $category_data = [
                'HG' => ['name' => 'High Grade', 'desc' => 'Mô hình cơ bản, dễ lắp ráp', 'image' => 'assets/images/cate_hg.jpg'],
                'MG' => ['name' => 'Master Grade', 'desc' => 'Độ chi tiết cao, tỷ lệ 1/100', 'image' => 'assets/images/cate_mg.jpg'],
                'RG' => ['name' => 'Real Grade', 'desc' => 'Chi tiết thực tế, tỷ lệ 1/144', 'image' => 'assets/images/cate_rg.jpg'],
                'PG' => ['name' => 'Perfect Grade', 'desc' => 'Cao cấp nhất, tỷ lệ 1/60', 'image' => 'assets/images/cate_pg.jpg'],
                'SD' => ['name' => 'Super Deformed', 'desc' => 'Dễ thương, đầu to body nhỏ', 'image' => 'assets/images/cate_sd.jpg']
            ];
            
            // Lấy số lượng sản phẩm từ DB
            $counts = [];
            $sql_categories = "SELECT type, COUNT(*) as count FROM products WHERE status = 'active' GROUP BY type";
            $result_categories = mysqli_query($conn, $sql_categories);
            if ($result_categories) {
                while($cat = mysqli_fetch_assoc($result_categories)) {
                    $counts[$cat['type']] = $cat['count'];
                }
            }
            
            foreach($category_data as $type => $category):
                $count = $counts[$type] ?? 0;
                $image = file_exists($category['image']) ? $category['image'] : "assets/images/LOGO.jpg";
            ?>
            <div class="category-card" onclick="window.location.href='products.php?type=<?= $type ?>'">
                <img src="<?= $image ?>" alt="<?= $type ?>" class="category-img" onerror="this.src='assets/images/LOGO.jpg'">
                <div class="category-info">
                    <h3 class="category-name"><?= $type ?> – <?= $category['name'] ?></h3>
                    <p class="category-desc"><?= $category['desc'] ?></p>
                    <span class="category-count"><?= $count ?> sản phẩm</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SALE PRODUCTS -->
<section id="sale" class="category-section" style="background: rgba(220, 38, 38, 0.04); padding: 60px 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="text-align: center; margin-bottom: 40px;">
            <h2 class="page-title" style="background: linear-gradient(90deg, var(--text-main), var(--primary-red)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">KHUYẾN MÃI - GIẢM GIÁ</h2>
            <p class="page-subtitle">Ưu đãi đặc biệt dành cho khách hàng. Đừng bỏ lỡ cơ hội sở hữu Gundam với giá tốt nhất!</p>
        </div>

        <div class="products-grid">
            <?php
            $sql_sale = "SELECT * FROM products WHERE is_sale = 1 AND status = 'active' AND old_price IS NOT NULL ORDER BY (old_price - price) DESC LIMIT 4";
            $result_sale = mysqli_query($conn, $sql_sale);
            
            if ($result_sale && mysqli_num_rows($result_sale) > 0) {
                while($row = mysqli_fetch_assoc($result_sale)) {
                    $formatted_price = number_format($row['price'], 0, ',', '.') . ' ₫';
                    $formatted_old_price = number_format($row['old_price'], 0, ',', '.') . ' ₫';
                    $discount = round(100 - ($row['price'] / $row['old_price'] * 100));
            ?>
            <div class="product-card sale">
                <span class="product-badge">-<?= $discount ?>%</span>
                <div class="product-image-container">
                    <a href="products_detail.php?id=<?= $row['id'] ?>" style="display:contents">
                        <img src="assets/images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="product-image" onerror="this.src='assets/images/LOGO.jpg'">
                    </a>
                </div>
                <div class="product-info">
                    <span class="product-type"><?= htmlspecialchars($row['type']) ?></span>
                    <h3 class="product-name"><a href="products_detail.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></h3>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span style="color: var(--text-muted); margin-left: 4px;">(4.8)</span>
                    </div>
                    <div class="product-price">
                        <span class="current-price"><?= $formatted_price ?></span>
                        <span class="old-price"><?= $formatted_old_price ?></span>
                    </div>
                    <div class="product-actions">
                        <a href="products_detail.php?id=<?= $row['id'] ?>" class="btn-detail">Chi tiết</a>
                        <button onclick="addToCart(<?= $row['id'] ?>)" class="btn-cart-add" title="Thêm vào giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p style='text-align:center; color:var(--text-muted); width:100%; padding: 40px; grid-column: 1/-1;'>Hiện chưa có sản phẩm giảm giá</p>";
            }
            ?>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section id="highlight" class="category-section" style="padding: 60px 0;">
    <div class="container">
        <div style="text-align: center; margin-bottom: 40px;">
            <h2 class="page-title">GUNDAM NỔI BẬT</h2>
            <p class="page-subtitle">Những mẫu Gundam được yêu thích nhất. Chất lượng vượt trội, thiết kế ấn tượng</p>
        </div>

        <div class="products-grid">
            <?php
            $sql = "SELECT * FROM products WHERE is_featured = 1 AND status = 'active' ORDER BY RAND() LIMIT 8";
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $formatted_price = number_format($row['price'], 0, ',', '.') . ' ₫';
                    $is_sale = $row['is_sale'] && $row['old_price'];
                    $formatted_old_price = $is_sale ? number_format($row['old_price'], 0, ',', '.') . ' ₫' : '';
                    $discount = $is_sale ? round(100 - ($row['price'] / $row['old_price'] * 100)) : 0;
            ?>
            <div class="product-card <?= $is_sale ? 'sale' : '' ?>">
                <?php if($is_sale): ?>
                <span class="product-badge">-<?= $discount ?>%</span>
                <?php endif; ?>
                <div class="product-image-container">
                    <a href="products_detail.php?id=<?= $row['id'] ?>" style="display:contents">
                        <img src="assets/images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="product-image" onerror="this.src='assets/images/LOGO.jpg'">
                    </a>
                </div>
                <div class="product-info">
                    <span class="product-type"><?= htmlspecialchars($row['type']) ?></span>
                    <h3 class="product-name"><a href="products_detail.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></h3>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <span style="color: var(--text-muted); margin-left: 4px;">(5.0)</span>
                    </div>
                    <div class="product-price">
                        <span class="current-price"><?= $formatted_price ?></span>
                        <?php if($is_sale): ?>
                        <span class="old-price"><?= $formatted_old_price ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-actions">
                        <a href="products_detail.php?id=<?= $row['id'] ?>" class="btn-detail">Chi tiết</a>
                        <button onclick="addToCart(<?= $row['id'] ?>)" class="btn-cart-add" title="Thêm vào giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p style='text-align:center; color:var(--text-muted); width:100%; padding: 40px; grid-column: 1/-1;'>Không có sản phẩm nổi bật</p>";
            }
            ?>
        </div>
        
        <div style="text-align: center; margin-top: 50px;">
            <a href="products.php" class="btn btn-blue" style="border-radius:30px; padding: 14px 36px;">
                <i class="fas fa-eye"></i> XEM TẤT CẢ SẢN PHẨM
            </a>
        </div>
    </div>
</section>

<!-- STATS SECTION -->
<section class="stats-section">
    <div class="container">
        <div class="admin-stats" style="margin-bottom: 0;">
            <?php
            // Lấy số lượng sản phẩm thật
            $sql_prod_count = "SELECT COUNT(*) as count FROM products WHERE status = 'active'";
            $result_prod_count = mysqli_query($conn, $sql_prod_count);
            $actual_prod_count = $result_prod_count ? mysqli_fetch_assoc($result_prod_count)['count'] : 0;

            // Lấy số lượng khách hàng thật
            $sql_cust_count = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
            $result_cust_count = mysqli_query($conn, $sql_cust_count);
            $actual_cust_count = $result_cust_count ? mysqli_fetch_assoc($result_cust_count)['count'] : 0;
            ?>
            <div class="stat-card" style="border-left: none; background: rgba(255,255,255,0.06); border-color: var(--border-color); text-align: center;">
                <div class="stat-value" style="font-size: 3rem;"><?= number_format($actual_prod_count) ?></div>
                <div class="stat-label" style="justify-content: center;">Sản Phẩm</div>
            </div>
            <div class="stat-card" style="border-left: none; background: rgba(255,255,255,0.06); border-color: var(--border-color); text-align: center;">
                <div class="stat-value" style="font-size: 3rem;"><?= number_format($actual_cust_count) ?></div>
                <div class="stat-label" style="justify-content: center;">Khách Hàng</div>
            </div>
            <div class="stat-card" style="border-left: none; background: rgba(255,255,255,0.06); border-color: var(--border-color); text-align: center;">
                <div class="stat-value" style="font-size: 3rem;">98%</div>
                <div class="stat-label" style="justify-content: center;">Hài Lòng</div>
            </div>
            <div class="stat-card" style="border-left: none; background: rgba(255,255,255,0.06); border-color: var(--border-color); text-align: center;">
                <div class="stat-value" style="font-size: 3rem;">24/7</div>
                <div class="stat-label" style="justify-content: center;">Hỗ Trợ</div>
            </div>
        </div>
    </div>
</section>

<script>
    // Banner Slider
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        if(slides[index]) slides[index].classList.add('active');
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    if (slides.length > 0) {
        setInterval(nextSlide, 5000);
        showSlide(0);
    }
</script>

<?php
include "includes/footer.php";
?>