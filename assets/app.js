// Gundam Store - Common JavaScript

function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;top:90px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = `
        margin: 0;
        pointer-events: auto;
        box-shadow: var(--shadow-main);
        transform: translateX(400px);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease;
        opacity: 0;
        width: 320px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    `;

    const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
    toast.innerHTML = `
        <i class="fas ${icon}" style="font-size: 1.25rem;"></i>
        <div style="flex:1; font-size: 0.9rem;">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    }, 10);

    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

function addToCart(productId, quantity = 1) {
    const base = document.body.dataset.basePath || '';
    fetch(base + 'api/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&id=${productId}&quantity=${quantity}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }
        if (data.success) {
            showToast(data.message, 'success');
            updateCartCount();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(() => showToast('Có lỗi xảy ra', 'error'));
}

function updateCartCount() {
    const el = document.getElementById('cartCount');
    if (!el) return;
    const base = document.body.dataset.basePath || '';
    fetch(base + 'api/get_cart_count.php')
        .then(r => r.json())
        .then(data => { el.textContent = data.count || 0; })
        .catch(() => {});
}

function initMobileNav() {
    const toggle = document.getElementById('navToggle');
    const menu = document.getElementById('navMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
        menu.classList.toggle('open');
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.className = menu.classList.contains('open') ? 'fas fa-times' : 'fas fa-bars';
        }
    });

    document.addEventListener('click', (e) => {
        if (!menu.classList.contains('open')) return;
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('open');
            const icon = toggle.querySelector('i');
            if (icon) icon.className = 'fas fa-bars';
        }
    });
}

function initChatbot() {
    const widget = document.getElementById('chatbot-widget');
    if (!widget) return;

    const toggle = document.getElementById('chatbot-toggle');
    const panel = document.getElementById('chatbot-panel');
    const closeBtn = document.getElementById('chatbot-close');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const messages = document.getElementById('chatbot-messages');
    const sendBtn = document.getElementById('chatbot-send');
    const base = document.body.dataset.basePath || '';

    let isOpen = false;
    let isSending = false;
    let historyLoaded = false;

    function openChat() {
        isOpen = true;
        panel.classList.add('open');
        panel.setAttribute('aria-hidden', 'false');
        loadHistory();
        input.focus();
    }

    function closeChat() {
        isOpen = false;
        panel.classList.remove('open');
        panel.setAttribute('aria-hidden', 'true');
    }

    toggle.addEventListener('click', () => isOpen ? closeChat() : openChat());
    closeBtn.addEventListener('click', closeChat);

    function appendMessage(text, role) {
        const wrap = document.createElement('div');
        wrap.className = `chat-msg ${role}`;
        wrap.innerHTML = `<div class="chat-bubble">${escapeHtml(text)}</div>`;
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
        return wrap;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML.replace(/\n/g, '<br>');
    }

    async function loadHistory() {
        if (historyLoaded) return;
        historyLoaded = true;

        try {
            const res = await fetch(base + 'api/chat.php');
            const data = await res.json();
            if (!data.success || !Array.isArray(data.messages) || data.messages.length === 0) return;

            messages.innerHTML = '';
            data.messages.forEach(item => appendMessage(item.message, item.role === 'user' ? 'user' : 'bot'));
        } catch {
            // Chat history is optional; sending new messages should still work.
        }
    }

    async function sendMessage(text) {
        const msg = text.trim();
        if (!msg || isSending) return;

        isSending = true;
        sendBtn.disabled = true;
        input.value = '';

        appendMessage(msg, 'user');
        const typing = appendMessage('Đang suy nghĩ...', 'typing bot');

        try {
            const res = await fetch(base + 'api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg })
            });
            const data = await res.json();
            typing.remove();

            if (data.success) {
                appendMessage(data.reply, 'bot');
            } else {
                appendMessage(data.message || 'Không thể kết nối AI.', 'bot');
            }
        } catch {
            typing.remove();
            appendMessage('Lỗi kết nối. Vui lòng thử lại sau.', 'bot');
        }

        isSending = false;
        sendBtn.disabled = false;
        input.focus();
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage(input.value);
    });

    document.querySelectorAll('.chatbot-quick button').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!isOpen) openChat();
            sendMessage(btn.dataset.msg);
        });
    });
}

function initLiveProductSearch() {
    const form = document.querySelector('.live-search-form');
    if (!form) return;

    const input = form.querySelector('input[name="search"]');
    const box = form.querySelector('.search-suggestions');
    const base = document.body.dataset.basePath || '';
    let controller = null;
    let timer = null;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function hideSuggestions() {
        box.classList.remove('open');
        box.innerHTML = '';
    }

    function render(products, query) {
        if (!products.length) {
            box.innerHTML = '<div class="search-empty">Khong tim thay san pham phu hop</div>';
            box.classList.add('open');
            return;
        }

        box.innerHTML = products.map(product => `
            <a class="search-suggestion-item" href="${base + product.url}">
                <img src="${base + product.image}" alt="${escapeHtml(product.name)}" onerror="this.src='${base}assets/images/LOGO.jpg'">
                <span class="search-suggestion-info">
                    <strong>${escapeHtml(product.name)}</strong>
                    <small>${escapeHtml(product.type)} - ${escapeHtml(product.price)}${product.stock <= 0 ? ' - Het hang' : ''}</small>
                </span>
            </a>
        `).join('') + `
            <a class="search-suggestion-all" href="${base}products.php?search=${encodeURIComponent(query)}">
                Xem tat ca ket qua
            </a>
        `;
        box.classList.add('open');
    }

    async function searchProducts() {
        const query = input.value.trim();
        if (query.length < 2) {
            hideSuggestions();
            return;
        }

        if (controller) controller.abort();
        controller = new AbortController();

        try {
            const res = await fetch(`${base}api/search_products.php?q=${encodeURIComponent(query)}`, {
                signal: controller.signal
            });
            const data = await res.json();
            if (data.success) render(data.products || [], query);
        } catch (error) {
            if (error.name !== 'AbortError') hideSuggestions();
        }
    }

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(searchProducts, 180);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && box.innerHTML.trim()) {
            box.classList.add('open');
        }
    });

    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) hideSuggestions();
    });
}

function initThemeToggle() {
    const toggle = document.querySelector('.theme-toggle');
    if (!toggle) return;

    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.classList.toggle('light-theme', savedTheme === 'light');
    updateThemeIcon();

    toggle.addEventListener('click', () => {
        const isLight = document.documentElement.classList.toggle('light-theme');
        const theme = isLight ? 'light' : 'dark';
        localStorage.setItem('theme', theme);
        updateThemeIcon();
    });

    function updateThemeIcon() {
        const isLight = document.documentElement.classList.contains('light-theme');
        toggle.innerHTML = isLight ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
    }
}

function initNotifications() {
    const bell = document.getElementById('notificationBell');
    const badge = document.getElementById('notificationBadge');
    const dropdown = document.getElementById('notificationDropdown');
    const list = document.getElementById('notificationList');
    const base = document.body.dataset.basePath || '';
    
    if (!bell || !dropdown || !list || !badge) return;

    let unreadCount = 0;

    async function loadNotifications() {
        try {
            const res = await fetch(base + 'api/notifications.php?action=list');
            const data = await res.json();
            
            unreadCount = data.unread_count;
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }

            if (!data.notifications || data.notifications.length === 0) {
                list.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-muted); font-size:0.9rem;">Không có thông báo mới</div>';
                return;
            }

            list.innerHTML = data.notifications.map(notif => {
                const isUnread = notif.is_read === 0;
                const dotHtml = isUnread ? `<span class="unread-dot" style="width: 8px; height: 8px; background: var(--primary-blue); border-radius: 50%; display: inline-block;"></span>` : '';
                const titleColor = isUnread ? 'white' : 'var(--text-muted)';
                const titleWeight = isUnread ? 'bold' : 'normal';

                return `
                    <div class="notification-item ${isUnread ? 'unread' : ''}" 
                         data-id="${notif.id}"
                         style="padding: 12px 15px; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.3s; text-align: left;"
                         onmouseover="this.style.background='rgba(255, 255, 255, 0.04)'" 
                         onmouseout="this.style.background='transparent'">
                        <div style="font-weight: ${titleWeight}; font-size: 0.92rem; color: ${titleColor}; display: flex; align-items: center; justify-content: space-between;">
                            <span>${notif.title}</span>
                            ${dotHtml}
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; line-height: 1.4; white-space: normal; word-break: break-word;">${notif.message}</div>
                        <div style="font-size: 0.75rem; color: #666; margin-top: 6px; text-align: right;">${notif.created_at}</div>
                    </div>
                `;
            }).join('');

            // Bind click events
            list.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', async () => {
                    const id = item.dataset.id;
                    if (item.classList.contains('unread')) {
                        // Mark as read in DB
                        try {
                            const postRes = await fetch(base + 'api/notifications.php?action=mark_read', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `notification_id=${id}`
                            });
                            const postData = await postRes.json();
                            if (postData.success) {
                                item.classList.remove('unread');
                                const dot = item.querySelector('.unread-dot');
                                if (dot) dot.remove();
                                const titleSpan = item.querySelector('div:first-child');
                                if (titleSpan) {
                                    titleSpan.style.color = 'var(--text-muted)';
                                    titleSpan.style.fontWeight = 'normal';
                                }
                                
                                // Update badge
                                unreadCount = Math.max(0, unreadCount - 1);
                                if (unreadCount > 0) {
                                    badge.textContent = unreadCount;
                                    badge.style.display = 'flex';
                                } else {
                                    badge.style.display = 'none';
                                }
                            }
                        } catch (e) {
                            console.error("Lỗi khi đánh dấu đã đọc:", e);
                        }
                    }
                });
            });

        } catch (error) {
            list.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-muted); font-size:0.9rem;">Không thể tải thông báo</div>';
        }
    }

    // Toggle dropdown
    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (!isOpen) {
            loadNotifications();
        }
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Initial load
    loadNotifications();
    // Poll every 30 seconds for new notifications
    setInterval(loadNotifications, 30000);
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    initMobileNav();
    initChatbot();
    initLiveProductSearch();
    initThemeToggle();
    initNotifications();
});
