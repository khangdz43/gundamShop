<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

function ensureChatTables($conn) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    $conn->query(
        "CREATE TABLE IF NOT EXISTS chat_sessions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_chat_sessions_user_updated (user_id, updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS chat_messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            session_id INT NOT NULL,
            user_id INT NOT NULL,
            role ENUM('user', 'assistant') NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_chat_messages_session_created (session_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function getOrCreateChatSession($conn, $userId) {
    $stmt = $conn->prepare("SELECT id FROM chat_sessions WHERE user_id = ? ORDER BY updated_at DESC, id DESC LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($session) {
        return (int)$session['id'];
    }

    $title = 'Tu van Gundam';
    $stmt = $conn->prepare("INSERT INTO chat_sessions (user_id, title) VALUES (?, ?)");
    $stmt->bind_param('is', $userId, $title);
    $stmt->execute();
    $sessionId = (int)$stmt->insert_id;
    $stmt->close();
    return $sessionId;
}

function getChatHistory($conn, $sessionId, $limit = 12) {
    $stmt = $conn->prepare(
        "SELECT role, message
         FROM (
            SELECT id, role, message
            FROM chat_messages
            WHERE session_id = ?
            ORDER BY id DESC
            LIMIT ?
         ) recent
         ORDER BY id ASC"
    );
    $stmt->bind_param('ii', $sessionId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
    return $messages;
}

function saveChatMessage($conn, $sessionId, $userId, $role, $message) {
    $stmt = $conn->prepare("INSERT INTO chat_messages (session_id, user_id, role, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiss', $sessionId, $userId, $role, $message);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE chat_sessions SET updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('i', $sessionId);
    $stmt->execute();
    $stmt->close();
}

function localGundamReply($message) {
    $lang = currentLang();
    $text = mb_strtolower($message, 'UTF-8');

    if ($lang === 'en') {
        if (strpos($text, 'ship') !== false || strpos($text, 'giao') !== false) {
            return 'We ship nationwide. Orders over 2,000,000₫ qualify for free shipping, otherwise the fee is 30,000₫. You can pay with COD or bank transfer.';
        }

        if (strpos($text, 'hg') !== false || strpos($text, 'rg') !== false || strpos($text, 'mg') !== false || strpos($text, 'pg') !== false) {
            return 'HG is easy to build and budget-friendly for beginners. RG is small with high detail. MG is 1/100 scale and great for display. PG is the largest, most detailed option for experienced builders.';
        }

        if (strpos($text, 'new') !== false || strpos($text, 'beginner') !== false || strpos($text, 'start') !== false) {
            return 'If you are new to Gunpla, I recommend starting with HG or SD models because they are easier to assemble and more affordable. Once you gain confidence, try RG or MG.';
        }

        if (strpos($text, 'sale') !== false || strpos($text, 'discount') !== false || strpos($text, 'promotion') !== false) {
            return 'You can browse sale items in the products on sale section. Pick a model from a series you like and a budget that works for you.';
        }

        return 'I am Gundam Store HUMG AI assistant. Ask me about choosing HG/RG/MG/PG models, beginner kits, shipping, payment, or sale items.';
    }

    if (strpos($text, 'ship') !== false || strpos($text, 'giao') !== false) {
        return 'Shop ho tro giao hang toan quoc. Don tu 2.000.000d duoc mien phi ship, don thap hon phi ship 30.000d. Ban co the thanh toan COD hoac chuyen khoan.';
    }

    if (strpos($text, 'hg') !== false || strpos($text, 'rg') !== false || strpos($text, 'mg') !== false || strpos($text, 'pg') !== false) {
        return 'HG de lap va gia mem, hop nguoi moi. RG nho nhung chi tiet cao. MG ti le 1/100, dep de trung bay. PG lon nhat, nhieu chi tiet va hop nguoi da co kinh nghiem.';
    }

    if (strpos($text, 'moi') !== false || strpos($text, 'new') !== false || strpos($text, 'bat dau') !== false) {
        return 'Neu ban moi choi Gunpla, minh goi y bat dau voi HG hoac SD vi de lap, it ap luc va chi phi hop ly. Khi quen tay hon hay thu RG hoac MG.';
    }

    if (strpos($text, 'sale') !== false || strpos($text, 'giam') !== false || strpos($text, 'khuyen') !== false) {
        return 'Ban co the xem cac mau dang giam gia o muc San pham sale. Neu can chon nhanh, hay uu tien mau dung series ban thich va phu hop ngan sach.';
    }

    return 'Minh la tro ly tu van cua Gundam Store HUMG. Ban co the hoi minh ve cach chon HG/RG/MG/PG, san pham cho nguoi moi, giao hang, thanh toan hoac cac mau dang sale.';
}

ensureChatTables($conn);
$userId = isLoggedIn() ? (int)getUserId() : null;
$sessionId = $userId ? getOrCreateChatSession($conn, $userId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$sessionId) {
        jsonResponse(['success' => true, 'messages' => []]);
    }

    jsonResponse(['success' => true, 'messages' => getChatHistory($conn, $sessionId, 20)]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if ($message === '') {
    jsonResponse(['success' => false, 'message' => 'Vui long nhap cau hoi.'], 400);
}

if (mb_strlen($message) > 500) {
    jsonResponse(['success' => false, 'message' => 'Tin nhan qua dai (toi da 500 ky tu).'], 400);
}

$history = $sessionId ? getChatHistory($conn, $sessionId, 12) : [];
if ($sessionId) {
    saveChatMessage($conn, $sessionId, $userId, 'user', $message);
}

$config = require __DIR__ . '/../config/gemini.php';
$apiKey = $config['api_key'];
$model = $config['model'];
$lang = currentLang();

function respondWithReply($conn, $sessionId, $userId, $message, $reply) {
    if ($sessionId) {
        saveChatMessage($conn, $sessionId, $userId, 'assistant', $reply);
    }
    jsonResponse(['success' => true, 'reply' => $reply]);
}

if (!function_exists('curl_init')) {
    respondWithReply($conn, $sessionId, $userId, $message, localGundamReply($message));
}

$contents = [];
foreach ($history as $item) {
    $contents[] = [
        'role' => $item['role'] === 'assistant' ? 'model' : 'user',
        'parts' => [['text' => $item['message']]]
    ];
}
$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $message]]
];

$languageInstruction = $lang === 'en' ? 'Please answer in English.' : 'Hãy trả lời bằng tiếng Việt.';
$payload = [
    'system_instruction' => [
        'parts' => [[
            'text' => $config['system_prompt']
                . ' ' . $languageInstruction
                . ' Neu lich su chat cho thay so thich, ngan sach, cap do lap rap cua khach, hay dua tren do de tu van lan tiep theo.'
        ]]
    ],
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 512,
    ]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    respondWithReply($conn, $sessionId, $userId, $message, localGundamReply($message));
}

$data = json_decode($response, true);

if ($httpCode !== 200 || empty($data['candidates'][0]['content']['parts'][0]['text'])) {
    respondWithReply($conn, $sessionId, $userId, $message, localGundamReply($message));
}

$reply = trim($data['candidates'][0]['content']['parts'][0]['text']);
respondWithReply($conn, $sessionId, $userId, $message, $reply);
