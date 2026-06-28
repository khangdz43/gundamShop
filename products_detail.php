<?php
session_start();
include "config/db.php";
require_once "includes/auth.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    die("<h3 style='color:red; text-align:center; margin-top:50px;'>" . __('product_not_found') . "</h3>");
}

// Lấy thông tin sản phẩm từ database
$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("<h3 style='color:red; text-align:center; margin-top:50px;'>" . __('product_not_found') . "</h3>");
}

// Xử lý gửi đánh giá mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        $review_error = 'Vui lòng đăng nhập để đánh giá.';
    } else {
        $user_id = $_SESSION['user_id'];
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
        $comment = trim($_POST['comment'] ?? '');
        
        if ($rating < 1 || $rating > 5) {
            $review_error = 'Số sao đánh giá phải từ 1 đến 5.';
        } else {
            $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $id, $user_id, $rating, $comment);
            if ($stmt->execute()) {
                header("Location: products_detail.php?id=$id&review_success=1");
                exit();
            } else {
                $review_error = 'Có lỗi xảy ra, vui lòng thử lại sau.';
            }
            $stmt->close();
        }
    }
}

// Lấy danh sách đánh giá
$sql_reviews = "SELECT r.*, u.username, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = $id ORDER BY r.created_at DESC";
$result_reviews = mysqli_query($conn, $sql_reviews);

// Tính trung bình đánh giá
$sql_avg = "SELECT AVG(rating) as avg_rating, COUNT(*) as count_reviews FROM reviews WHERE product_id = $id";
$result_avg = mysqli_query($conn, $sql_avg);
$avg_row = mysqli_fetch_assoc($result_avg);
$avg_rating = $avg_row['avg_rating'] ? round($avg_row['avg_rating'], 1) : 0;
$count_reviews = $avg_row['count_reviews'];

$formatted_price = number_format($product['price'], 0, ',', '.') . ' ₫';
$formatted_old_price = $product['old_price'] ? number_format($product['old_price'], 0, ',', '.') . ' ₫' : '';
$discount = $product['old_price'] ? round(100 - ($product['price'] / $product['old_price'] * 100)) : 0;

$pageTitle = htmlspecialchars($product['name']) . ' - Gundam Store';
include 'includes/header.php';
?>

<div class="product-detail-container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> <?php echo __('breadcrumb_home'); ?></a>
        <i class="fas fa-chevron-right"></i>
        <a href="products.php"><?php echo __('products'); ?></a>
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
            
            <div class="detail-rating-summary" style="margin: 10px 0; display: flex; align-items: center; gap: 8px;">
                <div class="stars" style="color: #ffb800;">
                    <?php
                    if ($avg_rating > 0) {
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $avg_rating) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i - 0.5 <= $avg_rating) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        echo ' <strong style="color: var(--text-main); margin-left: 4px;">' . $avg_rating . '/5</strong>';
                    } else {
                        for ($i = 1; $i <= 5; $i++) {
                            echo '<i class="far fa-star"></i>';
                        }
                        echo ' <span style="color: var(--text-muted); margin-left: 4px;">Chưa có đánh giá</span>';
                    }
                    ?>
                </div>
                <span style="color: var(--text-muted);">| <?php echo $count_reviews; ?> đánh giá</span>
            </div>
            
            <div class="detail-meta-row">
                <span class="product-type"><?= htmlspecialchars($product['type']) ?></span>
                <span style="color: var(--text-muted); font-size: 0.9rem;">
                    <i class="fas fa-barcode" style="color: var(--text-muted); margin-right: 4px;"></i>
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
                <strong style="color: var(--text-main);">Số lượng:</strong>
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
            <div class="product-meta" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 20px; margin-top: auto;">
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

    <!-- REVIEWS SECTION -->
    <div class="reviews-section" style="margin: 50px 0; padding: 30px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius);">
        <h2 class="section-title" style="margin-bottom: 25px; font-size: 1.5rem; color: var(--text-main); border-bottom: 2px solid var(--primary-red); padding-bottom: 10px; display: inline-block;">
            ĐÁNH GIÁ SẢN PHẨM (<?php echo $count_reviews; ?>)
        </h2>
        
        <style>
            .reviews-layout {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 40px;
                margin-top: 20px;
            }
            @media (max-width: 768px) {
                .reviews-layout {
                    grid-template-columns: 1fr !important;
                    gap: 30px !important;
                }
            }
        </style>
        
        <div class="reviews-layout">
            <!-- Left: Reviews List -->
            <div class="reviews-list">
                <?php if ($count_reviews > 0): ?>
                    <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                        <?php 
                        mysqli_data_seek($result_reviews, 0); // Đưa con trỏ kết quả về đầu
                        while($rev = mysqli_fetch_assoc($result_reviews)): 
                        ?>
                            <div class="review-item" style="padding: 15px; border-bottom: 1px solid var(--border-color); margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <strong style="color: var(--text-main);"><?php echo htmlspecialchars($rev['full_name'] ?: $rev['username']); ?></strong>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;"><?php echo date('d/m/Y H:i', strtotime($rev['created_at'])); ?></span>
                                </div>
                                <div style="color: #ffb800; margin-bottom: 8px; font-size: 0.9rem;">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rev['rating']) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <p style="color: var(--text-muted); line-height: 1.5; font-size: 0.95rem; margin: 0;">
                                    <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="far fa-comments" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                        <p style="font-size: 0.9rem; margin-top: 5px;">Hãy là người đầu tiên đánh giá sản phẩm!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right: Add Review Form -->
            <div class="add-review-box" style="background: var(--bg-body); padding: 25px; border-radius: 8px; border: 1px solid var(--border-color);">
                <h3 style="color: var(--text-main); margin-bottom: 20px; font-size: 1.2rem;">Viết đánh giá của bạn</h3>
                
                <?php if (isset($_GET['review_success'])): ?>
                    <div class="alert alert-success" style="background: rgba(40, 167, 69, 0.1); border: 1px solid #28a745; color: #28a745; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;">
                        Cảm ơn bạn đã gửi đánh giá thành công!
                    </div>
                <?php endif; ?>
                
                <?php if (isset($review_error)): ?>
                    <div class="alert alert-danger" style="background: rgba(220, 53, 69, 0.1); border: 1px solid #dc3545; color: #dc3545; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                        <?php echo $review_error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="submit_review" value="1">
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">Chọn mức đánh giá:</label>
                            <div class="star-rating-input" style="display: flex; gap: 8px; font-size: 1.5rem; color: #ffb800; cursor: pointer;">
                                <i class="far fa-star rating-star" data-value="1"></i>
                                <i class="far fa-star rating-star" data-value="2"></i>
                                <i class="far fa-star rating-star" data-value="3"></i>
                                <i class="far fa-star rating-star" data-value="4"></i>
                                <i class="far fa-star rating-star" data-value="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="5">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="comment" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">Nội dung đánh giá:</label>
                            <textarea name="comment" id="comment" required class="form-control" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..." style="width: 100%; min-height: 120px; padding: 10px; border-radius: 6px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main); resize: vertical; box-sizing: border-box;"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-blue" style="width: 100%; padding: 12px; font-weight: bold; border-radius: 6px;">GỬI ĐÁNH GIÁ</button>
                    </form>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const stars = document.querySelectorAll('.rating-star');
                            const ratingValueInput = document.getElementById('ratingValue');
                            
                            // Mặc định là 5 sao
                            highlightStars(5);
                            
                            stars.forEach(star => {
                                star.addEventListener('mouseover', function() {
                                    const val = parseInt(this.getAttribute('data-value'));
                                    highlightStars(val);
                                });
                                
                                star.addEventListener('mouseout', function() {
                                    const currentVal = parseInt(ratingValueInput.value);
                                    highlightStars(currentVal);
                                });
                                
                                star.addEventListener('click', function() {
                                    const val = parseInt(this.getAttribute('data-value'));
                                    ratingValueInput.value = val;
                                    highlightStars(val);
                                });
                            });
                            
                            function highlightStars(val) {
                                stars.forEach(star => {
                                    const starVal = parseInt(star.getAttribute('data-value'));
                                    if (starVal <= val) {
                                        star.className = 'fas fa-star rating-star';
                                    } else {
                                        star.className = 'far fa-star rating-star';
                                    }
                                });
                            }
                        });
                    </script>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                        <p style="margin-bottom: 15px;">Bạn cần đăng nhập để gửi đánh giá cho sản phẩm này.</p>
                        <a href="login.php" class="btn btn-blue" style="display: inline-block; padding: 10px 24px; font-weight: bold; border-radius: 6px; text-decoration: none;">Đăng nhập ngay</a>
                    </div>
                <?php endif; ?>
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