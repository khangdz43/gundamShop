<?php
/**
 * API: Admin AI Chat (Gemini)
 * Prompt khác hoàn toàn với chatbot khách hàng — dành cho admin phân tích kinh doanh
 */
require_once '../includes/auth.php';
requireAdmin();

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
    exit;
}

$history     = $input['history']     ?? [];
$contextData = $input['context']     ?? '';

$config  = require_once '../config/gemini.php';
$apiKey  = $config['api_key'];
$model   = $config['model'] ?? 'gemini-2.5-flash';

// ======================================================
// SYSTEM PROMPT dành riêng cho ADMIN - khác chatbot khách hàng
// ======================================================
$adminSystemPrompt = 
    "Bạn là Chuyên gia Chiến lược Kinh doanh AI của Gundam Store HUMG — cửa hàng mô hình Gunpla chính hãng Bandai tại Việt Nam.\n\n" .
    "NHIỆM VỤ CỦA BẠN:\n" .
    "1. Phân tích dữ liệu kinh doanh thực tế được cung cấp\n" .
    "2. Đề xuất chiến lược tăng trưởng doanh thu cụ thể và khả thi\n" .
    "3. Tư vấn quản lý tồn kho và nhập hàng thông minh\n" .
    "4. Phân tích hành vi mua sắm và phân khúc khách hàng\n" .
    "5. Gợi ý chiến lược marketing, khuyến mãi, pricing phù hợp thị trường Gunpla Việt Nam\n" .
    "6. Dự báo xu hướng và rủi ro kinh doanh\n\n" .
    "NGUYÊN TẮC:\n" .
    "- Luôn trả lời bằng tiếng Việt\n" .
    "- Phân tích SÂU, cụ thể, có số liệu dựa trên dữ liệu được cung cấp\n" .
    "- Đề xuất HÀNH ĐỘNG CỤ THỂ, không chung chung\n" .
    "- Tham chiếu dữ liệu thực tế khi phân tích\n" .
    "- Sử dụng kiến thức về thị trường Gunpla Việt Nam, fanbase Gundam, xu hướng anime\n" .
    "- Format câu trả lời rõ ràng với tiêu đề, bullet points khi cần\n" .
    "- Đây là môi trường ADMIN — có thể thảo luận về giá cost, margin, chiến lược cạnh tranh\n\n" .
    "KIẾN THỨC NỀN:\n" .
    "- Gunpla (Gundam Plastic Models) là dòng sản phẩm của Bandai Spirits Nhật Bản\n" .
    "- Phân khúc: SD (đơn giản, rẻ) → HG (cơ bản, phổ biến nhất) → RG (chi tiết cao) → MG (master) → PG/MGEX (cao cấp, đắt)\n" .
    "- Khách hàng Việt Nam chủ yếu yêu thích HG và MG; SD cho người mới/trẻ em\n" .
    "- Mùa cao điểm: Tết, khai giảng, sinh nhật, dịp anime mới ra mắt\n" .
    "- Cạnh tranh: shop online, Shopee, Lazada, các cửa hàng mô hình khác\n\n";

// Inject context data vào system prompt
if (!empty($contextData)) {
    $adminSystemPrompt .= $contextData;
}

// Build request body cho Gemini
$contents = [];
foreach ($history as $msg) {
    if (isset($msg['role']) && isset($msg['parts'])) {
        $contents[] = [
            'role'  => $msg['role'],
            'parts' => $msg['parts']
        ];
    }
}

$body = [
    'system_instruction' => [
        'parts' => [['text' => $adminSystemPrompt]]
    ],
    'contents'           => $contents,
    'generationConfig'   => [
        'temperature'     => 0.8,
        'maxOutputTokens' => 2048,
        'topP'            => 0.95,
    ]
];

$url  = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
$ch   = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($body),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['success' => false, 'error' => 'Lỗi kết nối: ' . $curlErr]);
    exit;
}

$result = json_decode($response, true);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode([
        'success' => true,
        'reply'   => $result['candidates'][0]['content']['parts'][0]['text']
    ], JSON_UNESCAPED_UNICODE);
} elseif (isset($result['error'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'API Error: ' . ($result['error']['message'] ?? 'Unknown error')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error'   => 'Không nhận được phản hồi từ AI'
    ]);
}
