<?php
session_start();
include "config/db.php";
require_once "includes/auth.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    die("<h3 style='color:red; text-align:center; margin-top:50px;'>Sản phẩm không tồn tại!</h3>");
}

// Lấy thông tin sản phẩm từ database
$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("<h3 style='color:red; text-align:center; margin-top:50px;'>Sản phẩm không tồn tại!</h3>");
}

$formatted_price = number_format($product['price'], 0, ',', '.') . ' ₫';
$formatted_old_price = $product['old_price'] ? number_format($product['old_price'], 0, ',', '.') . ' ₫' : '';
$discount = $product['old_price'] ? round(100 - ($product['price'] / $product['old_price'] * 100)) : 0;

$pageTitle = htmlspecialchars($product['name']) . ' - Gundam Store';
include 'includes/header.php';
?>

<div class="product-detail-container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
        <i class="fas fa-chevron-right"></i>
        <a href="products.php">Sản phẩm</a>
        <i class="fas fa-chevron-right"></i>
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>
    
    <!-- Product Content Layout -->
    <div class="product-detail-layout">
        <!-- Gallery -->
        <div class="detail-gallery">
            <div class="detail-main-img-box">
                <img src="assets/images/<?php echo htmlspecialchars($product['image'] ?: 'LOGO.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="detail-main-img" id="mainImage" onerror="this.src='assets/images/LOGO.jpg'">
            </div>
            <div class="detail-thumbnails">
                <img src="assets/images/<?php echo htmlspecialchars($product['image'] ?: 'LOGO.jpg'); ?>" alt="Gundam Thumbnail" class="detail-thumb active" onclick="changeImage(this.src)" onerror="this.src='assets/images/LOGO.jpg'">
            </div>
        </div>
        
        <!-- Info Panel -->
        <div class="detail-info-panel">
            <h1 class="detail-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="detail-meta-row">
                <span class="product-type"><?= htmlspecialchars($product['type']) ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;">
                    <i class="fas fa-barcode" style="color: var(--primary-blue); margin-right: 4px;"></i>
                    Mã: GUNDAM-<?php echo str_pad($product['id'], 3, '0', STR_PAD_LEFT); ?>
                </span>
                <span class="stock-badge <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                    <?php echo $product['stock'] > 0 ? 'Còn hàng (' . $product['stock'] . ' SP)' : 'Hết hàng'; ?>
                </span>
            </div>
            
            <!-- Price Box -->
            <div class="detail-price-box">
                <div class="detail-current-price"><?php echo $formatted_price; ?></div>
                <?php if($formatted_old_price): ?>
                    <span class="detail-old-price"><?php echo $formatted_old_price; ?></span>
                    <span class="detail-discount">-<?php echo $discount; ?>%</span>
                <?php endif; ?>
            </div>
            
            <!-- Description -->
            <div class="detail-desc">
                <strong>Mô tả sản phẩm:</strong>
                <p style="margin-top: 8px; color: var(--text-muted);">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?: 'Mô hình Gundam chính hãng Bandai. Chất lượng Nhật Bản, độ chi tiết cao, màu sắc sắc nét.')); ?>
                </p>
            </div>
            
            <!-- Quantity Control -->
            <?php if($product['stock'] > 0): ?>
            <div class="detail-qty-row">
                <strong style="color: var(--text-white);">Số lượng:</strong>
                <div class="qty-control">
                    <button class="qty-btn" type="button" onclick="decreaseQuantity()">-</button>
                    <input type="number" id="quantity" class="qty-input" value="1" min="1" max="<?php echo min(99, $product['stock']); ?>" readonly>
                    <button class="qty-btn" type="button" onclick="increaseQuantity()">+</button>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="detail-actions">
                <button type="button" class="btn btn-blue" onclick="addToCart(<?php echo $product['id']; ?>, getQuantity())">
                    <i class="fas fa-cart-plus"></i> THÊM VÀO GIỎ HÀNG
                </button>
                <button type="button" class="btn btn-red" onclick="buyNow(<?php echo $product['id']; ?>)">
                    <i class="fas fa-bolt"></i> MUA NGAY
                </button>
            </div>
            <?php else: ?>
            <div style="margin: 20px 0;">
                <button class="btn btn-gray" style="width: 100%; cursor: not-allowed;" disabled>
                    <i class="fas fa-ban"></i> SẢN PHẨM HẾT HÀNG
                </button>
            </div>
            <?php endif; ?>
            
            <!-- Specifications -->
            <div class="product-meta" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin-top: auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding: 6px 0; border-bottom: 1px solid var(--border-color)">
                        <span style="color: var(--text-muted)">Phân khúc:</span>
                        <strong><?php echo htmlspecialchars($product['type']); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding: 6px 0; border-bottom: 1px solid var(--border-color)">
                        <span style="color: var(--text-muted)">Danh mục:</span>
                        <strong><?php echo htmlspecialchars($product['category']); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding: 6px 0;">
                        <span style="color: var(--text-muted)">Tỉ lệ:</span>
                        <strong>
                            <?php 
                                if($product['type'] == 'HG' || $product['type'] == 'RG') echo '1/144';
                                elseif($product['type'] == 'MG') echo '1/100';
                                elseif($product['type'] == 'PG') echo '1/60';
                                else echo 'Chibi';
                            ?>
                        </strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding: 6px 0;">
                        <span style="color: var(--text-muted)">Hãng:</span>
                        <strong>Bandai Spirits</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <div class="related-section">
        <h2 class="section-title">SẢN PHẨM LIÊN QUAN</h2>
        <div class="products-grid" style="margin-top: 20px;">
            <?php
            // Lấy sản phẩm cùng loại
            $related_sql = "SELECT * FROM products WHERE type = ? AND id != ? AND status = 'active' LIMIT 4";
            $related_stmt = $conn->prepare($related_sql);
            $related_stmt->bind_param("si", $product['type'], $product['id']);
            $related_stmt->execute();
            $related_result = $related_stmt->get_result();
            
            if ($related_result->num_rows === 0) {
                $related_stmt->close();
                // Dự phòng: Lấy sản phẩm ngẫu nhiên
                $related_sql = "SELECT * FROM products WHERE id != ? AND status = 'active' ORDER BY RAND() LIMIT 4";
                $related_stmt = $conn->prepare($related_sql);
                $related_stmt->bind_param("i", $product['id']);
                $related_stmt->execute();
                $related_result = $related_stmt->get_result();
            }
            
            while($related = $related_result->fetch_assoc()) {
                $related_price = number_format($related['price'], 0, ',', '.') . ' ₫';
                $related_old_price = $related['old_price'] ? number_format($related['old_price'], 0, ',', '.') . ' ₫' : '';
                $is_sale = $related['is_sale'] && $related['old_price'];
                $discount = $is_sale ? round(100 - ($related['price'] / $related['old_price'] * 100)) : 0;
            ?>
            <div class="product-card <?php echo $is_sale ? 'sale' : ''; ?>">
                <?php if($is_sale): ?>
                    <span class="product-badge">-<?php echo $discount; ?>%</span>
                <?php endif; ?>
                <div class="product-image-container">
                    <a href="products_detail.php?id=<?= $related['id'] ?>" style="display:contents">
                        <img src="assets/images/<?= htmlspecialchars($related['image'] ?: 'LOGO.jpg') ?>" alt="<?= htmlspecialchars($related['name']) ?>" class="product-image" onerror="this.src='assets/images/LOGO.jpg'">
                    </a>
                </div>
                <div class="product-info">
                    <span class="product-type"><?= htmlspecialchars($related['type']) ?></span>
                    <h3 class="product-name"><a href="products_detail.php?id=<?= $related['id'] ?>"><?= htmlspecialchars($related['name']) ?></a></h3>
                    <div class="product-price">
                        <span class="current-price"><?= $related_price ?></span>
                        <?php if($is_sale): ?>
                            <span class="old-price"><?= $related_old_price ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-actions">
                        <a href="products_detail.php?id=<?= $related['id'] ?>" class="btn-detail">Chi tiết</a>
                        <button onclick="addToCart(<?= $related['id'] ?>)" class="btn-cart-add" title="Thêm vào giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php
            }
            $related_stmt->close();
            ?>
        </div>
    </div>
</div>

<script>
    function getQuantity() {
        return parseInt(document.getElementById('quantity').value) || 1;
    }
    
    function increaseQuantity() {
        let maxStock = <?php echo (int)$product['stock']; ?>;
        let q = getQuantity();
        if (q < maxStock && q < 99) {
            document.getElementById('quantity').value = q + 1;
        }
    }
    
    function decreaseQuantity() {
        let q = getQuantity();
        if (q > 1) {
            document.getElementById('quantity').value = q - 1;
        }
    }
    
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.detail-thumb').forEach(thumb => {
            thumb.classList.remove('active');
        });
        if (event && event.target) {
            event.target.classList.add('active');
        }
    }
    
    function buyNow(productId) {
        const qty = getQuantity();
        const base = document.body.dataset.basePath || '';
        fetch(base + 'api/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&id=${productId}&quantity=${qty}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            if (data.success) {
                window.location.href = base + 'cart.php';
            } else {
                alert(data.message);
            }
        })
        .catch(() => alert('Có lỗi xảy ra. Vui lòng thử lại!'));
    }
</script>

<?php
include 'includes/footer.php';
?>