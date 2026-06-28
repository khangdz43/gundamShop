<?php
session_start();
include "config/db.php";
require_once "includes/auth.php";

$type_filter  = isset($_GET['type']) ? $_GET['type'] : '';
$search       = trim($_GET['search'] ?? '');
$price_min    = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? max(0, (int)$_GET['price_min']) : null;
$price_max    = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? max(0, (int)$_GET['price_max']) : null;
$sort_by      = $_GET['sort'] ?? 'newest';

// Phân trang
$items_per_page = 12;
$current_page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset         = ($current_page - 1) * $items_per_page;

$where  = ["status = 'active'"];
$params = [];
$types  = '';

if (!empty($search)) {
    $where[]     = "(name LIKE ? OR description LIKE ? OR series LIKE ? OR category LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params      = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    $types      .= 'ssss';
    $page_title  = sprintf(__('search_results'), htmlspecialchars($search));
}

if (!empty($type_filter)) {
    if ($type_filter == 'SALE') {
        $where[]    = "is_sale = 1 AND old_price IS NOT NULL";
        $page_title = $page_title ?? __('products_on_sale');
    } else {
        $where[]  = "type = ?";
        $params[] = $type_filter;
        $types   .= 's';
        $page_title = $page_title ?? sprintf(__('products_type'), $type_filter);
    }
}

// Lọc giá
if ($price_min !== null) {
    $where[]  = "price >= ?";
    $params[] = $price_min;
    $types   .= 'i';
}
if ($price_max !== null) {
    $where[]  = "price <= ?";
    $params[] = $price_max;
    $types   .= 'i';
}

$page_title  = $page_title ?? __('all_products');
$whereClause = implode(' AND ', $where);

// Sắp xếp
$orderSQL = match($sort_by) {
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'name_asc'   => 'name ASC',
    'popular'    => 'is_featured DESC, id DESC',
    default      => 'id DESC'
};

// Tính tổng sản phẩm
$count_sql = "SELECT COUNT(*) as total FROM products WHERE $whereClause";
$stmt      = $conn->prepare($count_sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total_products = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();
$total_pages = max(1, ceil($total_products / $items_per_page));

// Lấy sản phẩm hiện tại
$sql  = "SELECT * FROM products WHERE $whereClause ORDER BY $orderSQL LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$allParams = array_merge($params, [$items_per_page, $offset]);
$allTypes  = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$result = $stmt->get_result();

// Lấy giá min/max của toàn bộ catalog để hiển thị slider
$priceRange = $conn->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE status='active'")->fetch_assoc();
$globalMin  = (int)$priceRange['min_price'];
$globalMax  = (int)$priceRange['max_price'];

$pageTitle = $page_title . ' - Gundam Store';
include 'includes/header.php';
?>

<div class="container" style="margin-top: 20px;">
    <!-- Page Header -->
    <div style="margin-bottom: 30px;">
        <a href="index.php" class="btn btn-gray btn-sm" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> <?php echo __('back_home'); ?>
        </a>
        <h1 class="page-title" style="text-align: left; margin: 25px 0 10px;"><?php echo $page_title; ?></h1>
        <p class="page-subtitle" style="text-align: left; margin-bottom: 20px;">
            <?php if(!empty($type_filter)): ?>
                <?php echo sprintf(__('explore_type'), htmlspecialchars($type_filter)); ?>
            <?php else: ?>
                <?php echo __('explore_all'); ?>
            <?php endif; ?>
        </p>
    </div>
    
    <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start;">
    
    <!-- === BỘ LỌC NÂNG CAO === -->
    <div class="filter-sidebar" id="filterSidebar">
        <form method="GET" action="products.php" id="filterForm">
            <?php if(!empty($search)): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
            
            <!-- Lọc theo phân khúc -->
            <div class="filter-section">
                <div class="filter-section-title"><i class="fas fa-layer-group"></i> <?php echo __('filter_segment'); ?></div>
                <?php
                $count_sql    = "SELECT type, COUNT(*) as count FROM products WHERE status = 'active' GROUP BY type";
                $count_result = mysqli_query($conn, $count_sql);
                $counts       = [];
                if ($count_result) {
                    while($row = mysqli_fetch_assoc($count_result)) $counts[$row['type']] = $row['count'];
                }
                $sale_sql    = "SELECT COUNT(*) as sale_count FROM products WHERE status = 'active' AND is_sale = 1 AND old_price IS NOT NULL";
                $sale_result = mysqli_query($conn, $sale_sql);
                $sale_count  = $sale_result ? mysqli_fetch_assoc($sale_result)['sale_count'] : 0;
                $total_all   = array_sum($counts);
                
                $types_list = [
                    '' => ['label' => __('type_all'), 'icon' => 'fas fa-boxes', 'count' => $total_all],
                    'HG'   => ['label' => 'High Grade (HG)',   'icon' => 'fas fa-cube',      'count' => $counts['HG'] ?? 0],
                    'MG'   => ['label' => 'Master Grade (MG)', 'icon' => 'fas fa-cubes',     'count' => $counts['MG'] ?? 0],
                    'RG'   => ['label' => 'Real Grade (RG)',   'icon' => 'fas fa-gem',       'count' => $counts['RG'] ?? 0],
                    'PG'   => ['label' => 'Perfect Grade (PG)','icon' => 'fas fa-crown',     'count' => $counts['PG'] ?? 0],
                    'SD'   => ['label' => 'Super Deformed (SD)','icon' => 'fas fa-baby',     'count' => $counts['SD'] ?? 0],
                    'MGEX' => ['label' => 'MGEX',              'icon' => 'fas fa-star',      'count' => $counts['MGEX'] ?? 0],
                    'SALE' => ['label' => __('filter_on_sale') . ' 🔥',  'icon' => 'fas fa-fire',     'count' => $sale_count],
                ];
                foreach ($types_list as $val => $info):
                    $active = ($type_filter === $val) ? 'active' : '';
                ?>
                <label class="filter-type-item <?php echo $active; ?>" onclick="setType('<?php echo $val; ?>')">
                    <input type="radio" name="type" value="<?php echo $val; ?>" <?php echo $active ? 'checked' : ''; ?> style="display:none">
                    <i class="<?php echo $info['icon']; ?>"></i>
                    <span><?php echo $info['label']; ?></span>
                    <span class="filter-count"><?php echo $info['count']; ?></span>
                </label>
                <?php endforeach; ?>
            </div>

            <!-- Lọc theo giá -->
            <div class="filter-section">
                <div class="filter-section-title"><i class="fas fa-tag"></i> <?php echo __('filter_price'); ?></div>
                
                <!-- Preset nhanh -->
                <div class="price-presets">
                    <button type="button" class="price-preset <?php echo ($price_min===null && $price_max===null && empty($type_filter===false)) ? '' : ''; ?>" onclick="setPreset(0, 0)"><?php echo __('price_all'); ?></button>
                    <button type="button" class="price-preset" onclick="setPreset(0, 500000)"><?php echo __('price_under_500k'); ?></button>
                    <button type="button" class="price-preset" onclick="setPreset(500000, 1000000)"><?php echo __('price_500k_1m'); ?></button>
                    <button type="button" class="price-preset" onclick="setPreset(1000000, 2000000)"><?php echo __('price_1m_2m'); ?></button>
                    <button type="button" class="price-preset" onclick="setPreset(2000000, 0)"><?php echo __('price_over_2m'); ?></button>
                </div>

                <!-- Range slider -->
                <div class="price-range-container">
                    <div class="price-range-track" id="priceTrack">
                        <div class="price-range-fill" id="priceFill"></div>
                        <input type="range" class="price-slider" id="sliderMin"
                               min="<?php echo $globalMin; ?>" max="<?php echo $globalMax; ?>"
                               value="<?php echo $price_min ?? $globalMin; ?>" step="50000">
                        <input type="range" class="price-slider" id="sliderMax"
                               min="<?php echo $globalMin; ?>" max="<?php echo $globalMax; ?>"
                               value="<?php echo $price_max ?? $globalMax; ?>" step="50000">
                    </div>
                    <div class="price-range-labels">
                        <span id="labelMin"><?php echo number_format($price_min ?? $globalMin, 0, ',', '.'); ?>đ</span>
                        <span id="labelMax"><?php echo number_format($price_max ?? $globalMax, 0, ',', '.'); ?>đ</span>
                    </div>
                </div>

                <!-- Nhập tay -->
                <div class="price-inputs">
                    <div class="price-input-group">
                        <label><?php echo __('price_from'); ?></label>
                        <input type="number" name="price_min" id="inputMin" class="price-input-field"
                               min="0" step="10000" placeholder="0"
                               value="<?php echo $price_min ?? ''; ?>">
                    </div>
                    <span class="price-input-dash">—</span>
                    <div class="price-input-group">
                        <label><?php echo __('price_to'); ?></label>
                        <input type="number" name="price_max" id="inputMax" class="price-input-field"
                               min="0" step="10000" placeholder="<?php echo __('price_no_limit'); ?>"
                               value="<?php echo $price_max ?? ''; ?>">
                    </div>
                </div>
            </div>

            <!-- Sắp xếp -->
            <div class="filter-section">
                <div class="filter-section-title"><i class="fas fa-sort"></i> <?php echo __('filter_sort'); ?></div>
                <select name="sort" class="sort-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="newest"     <?php echo $sort_by==='newest'     ? 'selected' : ''; ?>><?php echo __('sort_newest'); ?></option>
                    <option value="price_asc"  <?php echo $sort_by==='price_asc'  ? 'selected' : ''; ?>><?php echo __('sort_price_asc'); ?></option>
                    <option value="price_desc" <?php echo $sort_by==='price_desc' ? 'selected' : ''; ?>><?php echo __('sort_price_desc'); ?></option>
                    <option value="name_asc"   <?php echo $sort_by==='name_asc'   ? 'selected' : ''; ?>><?php echo __('sort_name_asc'); ?></option>
                    <option value="popular"    <?php echo $sort_by==='popular'    ? 'selected' : ''; ?>><?php echo __('sort_popular'); ?></option>
                </select>
            </div>

            <button type="submit" class="filter-apply-btn">
                <i class="fas fa-search"></i> <?php echo __('filter_apply'); ?>
            </button>
            <a href="products.php" class="filter-reset-btn">
                <i class="fas fa-undo"></i> <?php echo __('filter_reset'); ?>
            </a>
        </form>
    </div>

    <!-- === DANH SÁCH SẢN PHẨM === -->
    <div>
        <!-- Thanh trạng thái lọc -->
        <div class="filter-status-bar">
            <span class="filter-count-text">
                <i class="fas fa-box"></i> 
                <strong><?php echo $total_products; ?></strong> sản phẩm
                <?php if ($price_min !== null || $price_max !== null): ?>
                <span class="filter-tag">
                    <?php if($price_min && $price_max): echo number_format($price_min,0,'.','.') . '₫ – ' . number_format($price_max,0,'.','.') . '₫';
                    elseif($price_min): echo 'Từ ' . number_format($price_min,0,'.','.') . '₫';
                    else: echo 'Đến ' . number_format($price_max,0,'.','.') . '₫'; ?>
                    <?php endif; ?>
                    <a href="products.php<?php echo $type_filter ? '?type='.$type_filter : ''; ?>" style="color:inherit;margin-left:4px;"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
            </span>
            <!-- Mobile filter toggle -->
            <button class="mobile-filter-toggle" onclick="toggleMobileFilter()">
                <i class="fas fa-sliders-h"></i> Bộ lọc
            </button>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="products-grid">
            <?php while($row = mysqli_fetch_assoc($result)):
                $formatted_price     = number_format($row['price'], 0, ',', '.') . ' ₫';
                $formatted_old_price = $row['old_price'] ? number_format($row['old_price'], 0, ',', '.') . ' ₫' : '';
                $is_sale             = $row['is_sale'] && $row['old_price'];
                $discount            = $is_sale ? round(100 - ($row['price'] / $row['old_price'] * 100)) : 0;
                $image_path          = "assets/images/" . $row['image'];
                $use_image           = (!empty($row['image']) && file_exists($image_path)) ? $row['image'] : 'LOGO.jpg';
            ?>
            <div class="product-card <?php echo $is_sale ? 'sale' : ''; ?>">
                <?php if($is_sale): ?>
                    <span class="product-badge">-<?php echo $discount; ?>%</span>
                <?php endif; ?>
                <div class="product-image-container">
                    <a href="products_detail.php?id=<?= $row['id'] ?>" style="display:contents">
                        <img src="assets/images/<?= htmlspecialchars($use_image) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="product-image" onerror="this.src='assets/images/LOGO.jpg'">
                    </a>
                </div>
                <div class="product-info">
                    <span class="product-type"><?= htmlspecialchars($row['type']) ?></span>
                    <h3 class="product-name"><a href="products_detail.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></h3>
                    <div class="product-rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        <span style="color: var(--text-muted); margin-left: 4px;">(4.8)</span>
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
            <?php endwhile; ?>
        </div>
        
        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $base_url = "products.php";
            $url_params = [];
            if (!empty($type_filter)) $url_params[] = "type=" . urlencode($type_filter);
            if (!empty($search))      $url_params[] = "search=" . urlencode($search);
            if ($price_min !== null)  $url_params[] = "price_min=$price_min";
            if ($price_max !== null)  $url_params[] = "price_max=$price_max";
            if ($sort_by !== 'newest') $url_params[] = "sort=$sort_by";
            $url = $base_url . (!empty($url_params) ? '?' . implode('&', $url_params) : '');
            $sep = strpos($url, '?') !== false ? '&' : '?';
            ?>
            <?php if ($current_page > 1): ?>
                <a href="<?php echo $url . $sep . 'page=' . ($current_page - 1); ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>
            <?php
            $start_page = max(1, $current_page - 2);
            $end_page   = min($total_pages, $current_page + 2);
            if ($start_page > 1): ?><a href="<?php echo $url . $sep . 'page=1'; ?>">1</a><?php if ($start_page > 2): ?><span>...</span><?php endif; endif;
            for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="<?php echo $url . $sep . 'page=' . $i; ?>" class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor;
            if ($end_page < $total_pages): if ($end_page < $total_pages - 1): ?><span>...</span><?php endif; ?><a href="<?php echo $url . $sep . 'page=' . $total_pages; ?>"><?php echo $total_pages; ?></a><?php endif; ?>
            <?php if ($current_page < $total_pages): ?>
                <a href="<?php echo $url . $sep . 'page=' . ($current_page + 1); ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-box-open" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
            <h3 style="font-size: 1.5rem; margin-bottom: 10px;">Không tìm thấy mô hình nào</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Thử thay đổi bộ lọc hoặc khoảng giá.</p>
            <a href="products.php" class="btn btn-blue" style="display: inline-flex;"><i class="fas fa-redo"></i> Xem tất cả mô hình</a>
        </div>
        <?php endif; ?>
    </div><!-- end products col -->
    </div><!-- end grid -->
</div>

<style>
/* ===== FILTER SIDEBAR ===== */
.filter-sidebar {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    position: sticky;
    top: 90px;
}

.filter-section {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}
.filter-section:last-of-type { border-bottom: none; margin-bottom: 8px; }

.filter-section-title {
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 12px;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 8px;
}
.filter-section-title i { color: var(--primary-red); }

/* Type items */
.filter-type-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.88rem;
    color: var(--text-muted);
    margin-bottom: 3px;
}
.filter-type-item:hover { background: rgba(232, 25, 28, 0.05); color: var(--primary-red); }
.filter-type-item.active { background: rgba(232, 25, 28, 0.1); color: var(--primary-red); font-weight: 600; }
.filter-type-item i { width: 16px; text-align: center; }
.filter-count { margin-left: auto; background: var(--border-color); border-radius: 12px; padding: 2px 8px; font-size: 0.78rem; color: var(--text-main); }
.filter-type-item.active .filter-count { background: var(--primary-red); color: white; }

/* Price presets */
.price-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}
.price-preset {
    padding: 5px 10px;
    border: 1px solid var(--border-color);
    border-radius: 20px;
    background: var(--bg-card);
    color: var(--text-muted);
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.2s;
}
.price-preset:hover, .price-preset.active {
    background: rgba(232, 25, 28, 0.05);
    border-color: var(--primary-red);
    color: var(--primary-red);
}

/* Range slider */
.price-range-container { margin-bottom: 14px; }
.price-range-track {
    position: relative;
    height: 6px;
    background: var(--border-color);
    border-radius: 3px;
    margin: 20px 0 8px;
}
.price-range-fill {
    position: absolute;
    height: 100%;
    background: var(--primary-red);
    border-radius: 3px;
}
.price-slider {
    position: absolute;
    top: -7px;
    width: 100%;
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
    pointer-events: none;
}
.price-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: white;
    border: 3px solid var(--primary-red);
    cursor: pointer;
    pointer-events: all;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.15s;
}
.price-slider::-webkit-slider-thumb:hover { transform: scale(1.2); }
.price-range-labels { display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted); }

/* Price inputs */
.price-inputs { display: flex; align-items: center; gap: 8px; }
.price-input-group { flex: 1; }
.price-input-group label { display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px; }
.price-input-field {
    width: 100%;
    background: var(--bg-body);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-main);
    padding: 7px 8px;
    font-size: 0.82rem;
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.price-input-field:focus { outline: none; border-color: var(--primary-red); }
.price-input-dash { color: var(--text-muted); flex-shrink: 0; margin-top: 18px; }

/* Sort select */
.sort-select {
    width: 100%;
    background: var(--bg-body);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-main);
    padding: 10px 12px;
    font-size: 0.88rem;
    cursor: pointer;
}
.sort-select:focus { outline: none; border-color: var(--primary-red); }

/* Apply & reset buttons */
.filter-apply-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 11px;
    background: var(--primary-red);
    color: white; border: none; border-radius: 8px;
    font-size: 0.92rem; font-weight: 700; cursor: pointer;
    transition: all 0.25s; margin-bottom: 8px;
}
.filter-apply-btn:hover { background: var(--primary-red-hover); transform: translateY(-1px); }
.filter-reset-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 10px;
    background: transparent; color: var(--text-muted);
    border: 1px solid var(--border-color); border-radius: 8px;
    font-size: 0.88rem; cursor: pointer; text-decoration: none;
    transition: all 0.2s;
}
.filter-reset-btn:hover { border-color: var(--primary-red); color: var(--primary-red); }

/* Status bar */
.filter-status-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px; background: var(--bg-card);
    border: 1px solid var(--border-color); border-radius: 8px;
    margin-bottom: 16px; font-size: 0.9rem; color: var(--text-muted);
}
.filter-count-text strong { color: var(--text-main); }
.filter-tag {
    display: inline-flex; align-items: center; gap: 4px;
    background: rgba(232, 25, 28, 0.1); border: 1px solid rgba(232, 25, 28, 0.2);
    color: var(--primary-red); border-radius: 20px; padding: 3px 10px; font-size: 0.8rem;
    margin-left: 8px;
}
.mobile-filter-toggle { display: none; }

/* Responsive */
@media (max-width: 900px) {
    div[style*="grid-template-columns:280px"] {
        grid-template-columns: 1fr !important;
    }
    .filter-sidebar {
        position: static;
        display: none;
    }
    .filter-sidebar.mobile-open { display: block; }
    .mobile-filter-toggle { display: flex; align-items: center; gap: 6px; padding: 7px 14px; background: rgba(232, 25, 28, 0.1); border: 1px solid rgba(232, 25, 28, 0.2); color: var(--primary-red); border-radius: 8px; cursor: pointer; font-size: 0.85rem; }
}
</style>

<script>
var globalMin = <?php echo $globalMin; ?>;
var globalMax = <?php echo $globalMax; ?>;

function setType(val) {
    document.querySelector('input[name="type"][value="' + val + '"]').checked = true;
    document.getElementById('filterForm').submit();
}

function setPreset(min, max) {
    var inputMin = document.getElementById('inputMin');
    var inputMax = document.getElementById('inputMax');
    var sliderMin = document.getElementById('sliderMin');
    var sliderMax = document.getElementById('sliderMax');
    
    if (min === 0 && max === 0) {
        inputMin.value = '';
        inputMax.value = '';
        sliderMin.value = globalMin;
        sliderMax.value = globalMax;
    } else {
        inputMin.value = min || '';
        inputMax.value = max || '';
        if (min) sliderMin.value = min;
        if (max) sliderMax.value = max;
    }
    updateSliderUI();
    document.getElementById('filterForm').submit();
}

function updateSliderUI() {
    var sliderMin  = document.getElementById('sliderMin');
    var sliderMax  = document.getElementById('sliderMax');
    var fill       = document.getElementById('priceFill');
    var labelMin   = document.getElementById('labelMin');
    var labelMax   = document.getElementById('labelMax');
    var inputMin   = document.getElementById('inputMin');
    var inputMax   = document.getElementById('inputMax');
    
    var minVal = parseInt(sliderMin.value);
    var maxVal = parseInt(sliderMax.value);
    
    // Prevent crossing
    if (minVal > maxVal) {
        if (this === sliderMin) sliderMin.value = maxVal;
        else sliderMax.value = minVal;
        minVal = parseInt(sliderMin.value);
        maxVal = parseInt(sliderMax.value);
    }
    
    var range = globalMax - globalMin;
    var leftPct  = ((minVal - globalMin) / range) * 100;
    var rightPct = ((maxVal - globalMin) / range) * 100;
    
    fill.style.left  = leftPct + '%';
    fill.style.width = (rightPct - leftPct) + '%';
    
    labelMin.textContent = minVal.toLocaleString('vi-VN') + 'đ';
    labelMax.textContent = maxVal.toLocaleString('vi-VN') + 'đ';
    
    inputMin.value = minVal === globalMin ? '' : minVal;
    inputMax.value = maxVal === globalMax ? '' : maxVal;
}

function syncFromInputs() {
    var inputMin  = document.getElementById('inputMin');
    var inputMax  = document.getElementById('inputMax');
    var sliderMin = document.getElementById('sliderMin');
    var sliderMax = document.getElementById('sliderMax');
    if (inputMin.value) sliderMin.value = Math.min(Math.max(parseInt(inputMin.value), globalMin), globalMax);
    if (inputMax.value) sliderMax.value = Math.min(Math.max(parseInt(inputMax.value), globalMin), globalMax);
    updateSliderUI();
}

function toggleMobileFilter() {
    document.getElementById('filterSidebar').classList.toggle('mobile-open');
}

document.addEventListener('DOMContentLoaded', function() {
    var sliderMin = document.getElementById('sliderMin');
    var sliderMax = document.getElementById('sliderMax');
    sliderMin.addEventListener('input', updateSliderUI);
    sliderMax.addEventListener('input', updateSliderUI);
    document.getElementById('inputMin').addEventListener('input', syncFromInputs);
    document.getElementById('inputMax').addEventListener('input', syncFromInputs);
    updateSliderUI();
});
</script>

<?php
$stmt->close();
include 'includes/footer.php';
?>
