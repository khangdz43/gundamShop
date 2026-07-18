# Gundam Store

Đây là một hệ thống bán hàng mô hình Gundam (Gunpla) được xây dựng bằng PHP thuần, kết hợp MySQL, HTML/CSS/JS và tích hợp chatbot AI bằng Gemini.

## Tính năng chính

- Giao diện bán hàng cho khách hàng:
  - xem sản phẩm theo danh mục, loại và trạng thái sale
  - xem chi tiết sản phẩm
  - thêm vào giỏ hàng
  - thanh toán và theo dõi đơn hàng
  - đăng nhập / đăng ký / quản lý hồ sơ
  - áp dụng mã giảm giá

- Hệ thống quản trị admin:
  - quản lý sản phẩm / model
  - quản lý đơn hàng và đổi trả
  - quản lý người dùng và nhân viên
  - quản lý mã giảm giá
  - gửi thông báo
  - AI chiến lược kinh doanh bằng Gemini

- Tính năng AI:
  - chatbot tư vấn khách hàng
  - AI phân tích kinh doanh cho admin

## Cấu trúc thư mục

- admin/: các trang quản trị và chức năng quản lý
- api/: các endpoint xử lý AJAX như giỏ hàng, chatbot, thông báo, coupon
- assets/: file CSS, JS, hình ảnh và icon
- config/: cấu hình database và Gemini
- includes/: các file dùng chung như auth, header, footer, ngôn ngữ, chatbot widget
- public/: các trang giao diện người dùng (trang chủ, sản phẩm, giỏ hàng, checkout, đơn hàng, đăng nhập...)

## Yêu cầu hệ thống

- XAMPP / WAMP / Laragon với Apache + MySQL + PHP 8+
- PHP extension: mysqli
- Database MySQL

## Cài đặt

1. Đưa thư mục dự án vào htdocs của XAMPP.
2. Tạo database MySQL có tên `gundam_store`.
3. Import file SQL có sẵn vào database.
4. Cấu hình kết nối database trong [config/db.php](config/db.php).
5. Cấu hình Gemini API trong [config/gemini.php](config/gemini.php) hoặc dùng biến môi trường theo mẫu [\.env.example](.env.example).
6. Khởi động Apache và MySQL trong XAMPP.
7. Truy cập ứng dụng tại:
   - Trang khách hàng: http://localhost/GUNDAM/
   - Trang admin: http://localhost/GUNDAM/admin/

## Cấu hình môi trường

Bạn có thể cấu hình bằng biến môi trường như sau:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=gundam_store
DB_USER=root
DB_PASS=

GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-2.5-flash
```

## Ghi chú

- Dự án đang dùng route thân thiện và một số file wrapper để hỗ trợ chuyển đổi từ cấu trúc cũ sang cấu trúc mới.
- Nếu gặp lỗi 404 hoặc thiếu asset sau khi đổi cấu trúc thư mục, hãy kiểm tra lại Apache rewrite và file .htaccess.

## Tác giả

Dự án được phát triển cho mục đích học tập và demo bán hàng mô hình Gundam.
