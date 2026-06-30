<?php
/**
 * Admin AI Strategy Chat
 * Sử dụng Gemini AI để tư vấn chiến lược kinh doanh cho Admin
 */
require_once '../includes/auth.php';
requireAdmin();

$basePath = '../';
// ====== Thu thập dữ liệu kinh doanh để inject vào context ======
// Doanh thu 30 ngày qua
$revenue30 = $conn->query("SELECT COALESCE(SUM(total),0) as rev, COUNT(*) as cnt FROM orders WHERE status NOT IN ('cancelled') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc();

// Top 5 sản phẩm bán chạy
$top5 = $conn->query("SELECT p.name, p.type, p.price, SUM(oi.quantity) as sold FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id WHERE o.status NOT IN ('cancelled') GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Sản phẩm tồn kho thấp (< 10)
$lowStock = $conn->query("SELECT name, type, stock, price FROM products WHERE status='active' AND stock < 10 ORDER BY stock ASC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Đơn hàng theo trạng thái
$orderStats = $conn->query("SELECT status, COUNT(*) as cnt, COALESCE(SUM(total),0) as total FROM orders GROUP BY status")->fetch_all(MYSQLI_ASSOC);

// Doanh thu theo tháng (6 tháng gần nhất)
$revenueByMonth = $conn->query("SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as orders, COALESCE(SUM(total),0) as revenue FROM orders WHERE status NOT IN ('cancelled') AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month")->fetch_all(MYSQLI_ASSOC);

// Tổng tồn kho
$stockSummary = $conn->query("SELECT type, COUNT(*) as products, SUM(stock) as total_stock FROM products WHERE status='active' GROUP BY type")->fetch_all(MYSQLI_ASSOC);

// Format context data
$contextData  = "\n\n=== DỮ LIỆU KINH DOANH THỰC TẾ (" . date('d/m/Y H:i') . ") ===\n";
$contextData .= "Doanh thu 30 ngày: " . number_format($revenue30['rev'], 0, ',', '.') . "đ (" . $revenue30['cnt'] . " đơn)\n";

$contextData .= "\nTop 5 sản phẩm bán chạy:\n";
foreach ($top5 as $p) {
    $contextData .= "- {$p['name']} ({$p['type']}): bán " . ($p['sold'] ?? 0) . " cái, giá " . number_format($p['price'],0,'.','.') . "đ\n";
}

$contextData .= "\nSản phẩm sắp hết hàng (stock < 10):\n";
foreach ($lowStock as $p) {
    $contextData .= "- {$p['name']} ({$p['type']}): còn {$p['stock']} cái\n";
}

$contextData .= "\nTrạng thái đơn hàng:\n";
$statusLabel = ['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã hủy'];
foreach ($orderStats as $s) {
    $contextData .= "- " . ($statusLabel[$s['status']] ?? $s['status']) . ": " . $s['cnt'] . " đơn, " . number_format($s['total'],0,'.','.') . "đ\n";
}

$contextData .= "\nDoanh thu 6 tháng gần nhất:\n";
foreach ($revenueByMonth as $m) {
    $contextData .= "- {$m['month']}: " . $m['orders'] . " đơn, " . number_format($m['revenue'],0,'.','.') . "đ\n";
}

$contextData .= "\nTồn kho theo phân khúc:\n";
foreach ($stockSummary as $s) {
    $contextData .= "- {$s['type']}: {$s['products']} sản phẩm, tổng {$s['total_stock']} cái\n";
}
$contextData .= "===\n";

$pageTitle = 'AI Chiến lược - Admin';
include '../includes/header.php';
?>

<div class="container" style="max-width:100%;">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
        <div style="width:50px;height:50px;background:linear-gradient(135deg,#1f5fff,#7da7ff);border-radius:12px;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-brain" style="font-size:1.4rem;color:white;"></i>
        </div>
        <div>
            <h1 class="page-title" style="margin:0;text-align:left;font-size:1.8rem;">AI Chiến lược Kinh doanh</h1>
            <p style="color:var(--text-muted);margin:4px 0 0;font-size:0.9rem;">
                <i class="fas fa-robot"></i> Powered by Gemini AI &nbsp;·&nbsp; 
                <i class="fas fa-chart-line"></i> Dữ liệu thực từ hệ thống
            </p>
        </div>
        <div style="margin-left:auto;">
            <a href="index.php" class="btn btn-gray btn-sm"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>
    </div>

    <!-- Dashboard tóm tắt -->
    <div class="admin-stats" style="margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-dollar-sign"></i> Doanh thu 30 ngày</div>
            <div class="stat-value" style="font-size:1.3rem;"><?php echo number_format($revenue30['rev'],0,'.','.'); ?>đ</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label"><i class="fas fa-shopping-bag"></i> Đơn thành công</div>
            <div class="stat-value"><?php echo $revenue30['cnt']; ?></div>
        </div>
        <div class="stat-card red">
            <div class="stat-label"><i class="fas fa-exclamation-circle"></i> Sắp hết hàng</div>
            <div class="stat-value"><?php echo count($lowStock); ?></div>
        </div>
    </div>

    <!-- Gợi ý câu hỏi nhanh -->
    <div style="margin-bottom:16px;">
        <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:10px;"><i class="fas fa-lightbulb"></i> Gợi ý câu hỏi:</p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <?php
            $suggestions = [
                '📊 Phân tích doanh thu tháng này',
                '🚀 Chiến lược tăng doanh số HG/MG',
                '📦 Sản phẩm nào nên nhập thêm?',
                '🔥 Cách chạy khuyến mãi hiệu quả',
                '📉 Tại sao đơn hủy cao?',
                '🎯 Chiến lược marketing mùa hè',
                '💰 Nên đặt giá thế nào cho MGEX?',
                '📱 Chiến lược mạng xã hội cho Gunpla',
            ];
            foreach ($suggestions as $s): ?>
            <button class="suggestion-chip" onclick="insertSuggestion(this.textContent)"><?php echo $s; ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chat area -->
<div class="card" style="padding:0;overflow:visible;--radius-lg:0;border-radius:0;max-width:100%;">
         <div id="chatMessages" style="height:480px;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:16px;width:100%;box-sizing:border-box;">
            <!-- Welcome message -->
            <div class="ai-msg-wrapper">
                <div class="ai-avatar"><i class="fas fa-brain"></i></div>
                <div class="ai-bubble">
                    <p style="margin:0 0 10px;font-weight:600;color:var(--primary-blue);">Trợ lý Kinh doanh Gundam Store</p>
                    <p style="margin:0 0 8px;">Xin chào! Tôi là AI phân tích kinh doanh được tích hợp dữ liệu thực từ hệ thống của bạn.</p>
                    <p style="margin:0;color:var(--text-muted);font-size:0.88rem;">💡 Tôi có thể giúp bạn: phân tích doanh thu, đề xuất chiến lược marketing, tối ưu tồn kho, dự báo xu hướng thị trường Gunpla Việt Nam...</p>
                </div>
            </div>
        </div>

        <div style="padding:16px;border-top:1px solid var(--border-color);background:var(--bg-card);">
            <div style="display:flex;gap:10px;align-items:flex-end;">
                <textarea id="aiInput" placeholder="Hỏi về chiến lược kinh doanh, phân tích doanh số, kế hoạch marketing..." 
                    rows="2" style="flex:1;background:var(--bg-body);border:1px solid var(--border-color);border-radius:10px;color:var(--text-main);padding:12px 14px;font-size:0.9rem;resize:none;font-family:inherit;line-height:1.5;"
                    onkeydown="if(event.ctrlKey && event.key==='Enter') sendAdminAI()"></textarea>
                <button onclick="sendAdminAI()" id="sendBtn" style="padding:12px 20px;background:linear-gradient(135deg,#1f5fff,#7da7ff);color:white;border:none;border-radius:10px;cursor:pointer;font-weight:700;min-width:90px;display:flex;align-items:center;justify-content:center;gap:6px;transition:all 0.2s;">
                    <i class="fas fa-paper-plane"></i> Gửi
                </button>
            </div>
            <p style="color:var(--text-muted);font-size:0.75rem;margin:8px 0 0;">Ctrl+Enter để gửi nhanh</p>
        </div>
    </div>
</div>

<style>
/* SỬA LẠI ĐOẠN CSS NÀY TRONG FILE CỦA BẠN */
/* TÌM VÀ THAY THẾ ĐOẠN CSS CỦA SUGGESTION-CHIP THÀNH ĐOẠN NÀY */

.suggestion-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px; /* Khoảng cách giữa emoji và chữ bên trong nút */
    padding: 8px 16px; /* Tăng nhẹ padding cho nút cân đối, dễ bấm */
    border-radius: 20px; 
    border: 1px solid var(--border-color);
    background: rgba(255,255,255,0.04); 
    color: var(--text-muted);
    font-size: 0.85rem; 
    cursor: pointer; 
    transition: all 0.2s ease;
    white-space: nowrap; /* Giữ chữ trên 1 hàng bên trong nút, không bị vỡ chữ */
}

.suggestion-chip:hover {
    border-color: #1f5fff; 
    background: rgba(31,95,255,0.1); 
    color: #7da7ff;
    transform: translateY(-1px); /* Hiệu ứng nổi nhẹ khi di chuột */
}
#chatMessages { 
    width: 100%; 
    box-sizing: border-box;
}

.ai-msg-wrapper { 
    display: flex; 
    gap: 12px; 
    align-items: flex-start; 
    width: 100%; 
    box-sizing: border-box;
}

.ai-msg-wrapper.user-msg {
    justify-content: flex-end;
}

/* Gộp chung và sửa lại class .ai-bubble */
.ai-bubble {
    max-width: 85%; /* Giới hạn tối đa 85% chiều rộng khung chat để chừa khoảng trống đẹp mắt */
    width: auto;     /* Tự động co giãn theo độ dài văn bản */
    padding: 12px 16px;
    background: rgba(31,95,255,0.08);
    border: 1px solid rgba(31,95,255,0.2);
    border-radius: 0 14px 14px 14px;
    font-size: 0.9rem; 
    line-height: 1.6;
    color: var(--text-main);
    word-break: break-word;
    overflow-wrap: break-word;
    box-sizing: border-box;
}

/* Bong bóng chat của User (đẩy sát lề phải) */
.ai-msg-wrapper.user-msg .ai-bubble {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1);
    border-radius: 14px 0 14px 14px;
    color: var(--text-muted);
    max-width: 75%;
    margin-left: auto; /* Tự động đẩy về bên phải */
}
</style>

<script>
var chatHistory = [];
var contextData = <?php echo json_encode($contextData, JSON_UNESCAPED_UNICODE); ?>;
var adminAiApiUrl = '../api/admin_ai_chat.php';

function insertSuggestion(text) {
    // Remove emoji from text
    var clean = text.replace(/^\p{Emoji}+\s*/u, '').trim();
    document.getElementById('aiInput').value = clean;
    document.getElementById('aiInput').focus();
}

function addMessage(role, content) {
    var container = document.getElementById('chatMessages');
    var wrapper = document.createElement('div');
    wrapper.className = 'ai-msg-wrapper' + (role === 'user' ? ' user-msg' : '');

    var avatar = document.createElement('div');
    avatar.className = 'ai-avatar';
    avatar.innerHTML = role === 'user' 
        ? '<i class="fas fa-user"></i>'
        : '<i class="fas fa-brain"></i>';

    var bubble = document.createElement('div');
    bubble.className = 'ai-bubble';

    if (role === 'assistant') {
        let html = content.replace(/\n/g, '<br>');
        html = html.replace(/!\[[^\]]*\]\([^)]*\)/g, '');
        html = html.replace(/<img[^>]*>/gi, '');
        bubble.innerHTML = html;
    } else {
        bubble.textContent = content;
    }

    wrapper.appendChild(avatar);
    wrapper.appendChild(bubble);
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
    return wrapper;
}

function addTyping() {
    var container = document.getElementById('chatMessages');
    var wrapper = document.createElement('div');
    wrapper.className = 'ai-msg-wrapper';
    wrapper.id = 'typingIndicator';
    wrapper.innerHTML = '<div class="ai-avatar"><i class="fas fa-brain"></i></div>' +
        '<div class="ai-bubble"><div class="typing-dots"><span></span><span></span><span></span></div></div>';
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}

function removeTyping() {
    var t = document.getElementById('typingIndicator');
    if (t) t.remove();
}

async function sendAdminAI() {
    var input   = document.getElementById('aiInput');
    var sendBtn = document.getElementById('sendBtn');
    var message = input.value.trim();
    if (!message) return;

    input.value = '';
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    addMessage('user', message);
    chatHistory.push({ role: 'user', parts: [{ text: message }] });
    addTyping();

    try {
        var payload = {
            history: chatHistory,
            context: contextData
        };

        var resp = await fetch(adminAiApiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        });

        if (!resp.ok) {
            throw new Error('HTTP ' + resp.status);
        }

        var data = await resp.json();
        removeTyping();

        if (data.success && data.reply) {
            chatHistory.push({ role: 'model', parts: [{ text: data.reply }] });
            addMessage('assistant', data.reply);
        } else {
            addMessage('assistant', '⚠️ ' + (data.error || 'Có lỗi xảy ra. Vui lòng thử lại.'));
        }
    } catch (e) {
        removeTyping();
        addMessage('assistant', '⚠️ Lỗi kết nối. Kiểm tra API key và thử lại.');
    }

    sendBtn.disabled = false;
    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi';
}

document.getElementById('aiInput').addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        sendAdminAI();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
