<?php
$chatBasePath = $basePath ?? '';
?>
<div id="chatbot-widget" class="chatbot-widget">
    <button type="button" id="chatbot-toggle" class="chatbot-toggle" aria-label="Mở trợ lý AI">
        <i class="fas fa-robot"></i>
        <span class="chatbot-badge">AI</span>
    </button>

    <div id="chatbot-panel" class="chatbot-panel" aria-hidden="true">
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <i class="fas fa-robot"></i>
                <div>
                    <strong>Gundam AI Advisor</strong>
                    <small>Trợ lý tư vấn 24/7</small>
                </div>
            </div>
            <button type="button" id="chatbot-close" class="chatbot-close" aria-label="Đóng">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="chatbot-messages" class="chatbot-messages">
            <div class="chat-msg bot">
                <div class="chat-bubble">
                    Xin chào! Mình là trợ lý AI của Gundam Store. Bạn cần tư vấn mô hình HG/MG/RG/PG hay hỗ trợ đặt hàng?
                </div>
            </div>
        </div>

        <div class="chatbot-quick">
            <button type="button" data-msg="Gợi ý mô hình cho người mới">Người mới</button>
            <button type="button" data-msg="Khác nhau HG RG MG PG là gì?">Phân loại</button>
            <button type="button" data-msg="Chính sách giao hàng và thanh toán">Giao hàng</button>
        </div>

        <form id="chatbot-form" class="chatbot-form">
            <input type="text" id="chatbot-input" placeholder="Nhập câu hỏi..." maxlength="500" autocomplete="off">
            <button type="submit" id="chatbot-send" aria-label="Gửi">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>
