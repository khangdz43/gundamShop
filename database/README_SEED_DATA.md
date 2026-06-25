# Hướng Dẫn Nhập Dữ Liệu Mẫu Gundam Store

## 📋 Nội Dung Dữ Liệu

File `seed_full_data.sql` chứa dữ liệu mẫu hoàn chỉnh cho hệ thống:

### ✅ Người Dùng (25 tài khoản)
- **3 Admin** - Quản lý hệ thống
- **22 Khách hàng thường xuyên** - Có lịch sử mua hàng, đánh giá, chat

**Mật khẩu mặc định:** `password123` (cho tất cả tài khoản)

**Tài khoản admin:**
- `admin` - admin@gundamstore.vn
- `admin02` - admin2@gundamstore.vn
- `admin03` - admin3@gundamstore.vn

**Tài khoản khách hàng (một vài ví dụ):**
- `nguyenvana` - nguyenvana@gmail.com
- `tranthib` - tranthib@gmail.com
- `leminhc` - leminhc@yahoo.com
- Và 19 tài khoản khác...

### 🏪 Sản Phẩm (60+ mô hình Gundam)
Được phân loại theo dòng sản phẩm:

**HG (High Grade 1/144)** - 16 sản phẩm
- RX-78-2 Gundam
- Freedom Gundam, Justice Gundam, Destiny Gundam, Impulse Gundam
- Barbatos, Kimaris Trooper, Flauros
- Zaku II, Dom, Gelgoog
- Wing Gundam, 00 Gundam, v.v.

**SD (Super Deformed)** - 5 sản phẩm
- Freedom, Wing Zero, Zaku II, Barbatos, Unicorn

**RG (Real Grade 1/144)** - 6 sản phẩm
- Strike Freedom, Destiny, Barbatos Lupus Rex
- Zaku II, 00 Raiser, Banshee Norn

**MG (Master Grade 1/100)** - 12 sản phẩm
- Freedom 2.0, Destiny Spec II, Barbatos Lupus Rex
- Zaku II 2.0, Char Zaku II, Nu Gundam Ver.Ka
- Unicorn Ver.Ka, Deathscythe Hell, v.v.

**PG (Perfect Grade 1/60)** - 3 sản phẩm
- Strike Freedom, Unicorn, Wing Zero Custom

**MGEX (Master Grade EX)** - 2 sản phẩm
- Nu Gundam, Infinite Justice

### 📦 Đơn Hàng (20 đơn)
- Tổng giá trị: hơn 30 triệu VNĐ
- Trạng thái đa dạng: pending, confirmed, shipping, delivered, cancelled
- Phương thức thanh toán: COD, Bank Transfer
- Bao gồm ghi chú giao hàng thực tế

### ⭐ Đánh Giá Sản Phẩm (15 review)
- Rating từ 4-5 sao
- Nhận xét chi tiết từ khách hàng
- Liên kết với các sản phẩm khác nhau

### 🛒 Giỏ Hàng (19 mục)
- Dữ liệu thực tế của khách hàng đang shopping
- Bao gồm số lượng và thời gian thêm

### 💬 Chat Support
- 5 phiên chat lịch sử
- Thảo luận về tư vấn mua hàng và thông tin sản phẩm

### 📢 Thông Báo (10 notification)
- Xác nhận đơn hàng
- Cập nhật trạng thái giao hàng
- Sản phẩm mới
- Khuyến mãi

### 📁 Danh Mục (10 category)
- Gundam Universal Century
- Gundam SEED
- Iron-Blooded Orphans
- Zaku & Zeon
- SD Gundam
- Gundam Wing
- Gundam 00
- Zeta Gundam
- Gundam Unicorn
- Crossbone Gundam

---

## 🚀 Cách Nhập Dữ Liệu

### Cách 1: Dùng PHP Script (Dễ nhất) ⭐

1. Mở trình duyệt
2. Truy cập: `http://localhost/gundamShop/database/import_seed_data.php`
3. Script sẽ tự động nhập toàn bộ dữ liệu
4. Xem kết quả chi tiết trên màn hình

### Cách 2: Dùng phpMyAdmin

1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Chọn database `gundam_store`
3. Click tab **SQL**
4. Copy toàn bộ nội dung file `seed_full_data.sql`
5. Paste vào ô nhập SQL
6. Click **Execute** (Thực thi)

### Cách 3: Dùng MySQL Command Line

```bash
# Mở Command Prompt/Terminal
mysql -u root -p gundam_store < "C:\xampp\htdocs\gundamShop\database\seed_full_data.sql"
# Hoặc nếu không có password:
mysql -u root gundam_store < "C:\xampp\htdocs\gundamShop\database\seed_full_data.sql"
```

### Cách 4: Chạy từ PHP CLI

```bash
php -r "require 'database/import_seed_data.php';"
```

---

## 🔍 Kiểm Tra Dữ Liệu Đã Nhập

Sau khi nhập, bạn có thể kiểm tra bằng các query sau trong phpMyAdmin:

### Đếm số lượng dữ liệu:
```sql
SELECT 
    (SELECT COUNT(*) FROM users) AS 'Người dùng',
    (SELECT COUNT(*) FROM products) AS 'Sản phẩm',
    (SELECT COUNT(*) FROM orders) AS 'Đơn hàng',
    (SELECT COUNT(*) FROM reviews) AS 'Đánh giá',
    (SELECT COUNT(*) FROM cart) AS 'Giỏ hàng',
    (SELECT COUNT(*) FROM categories) AS 'Danh mục';
```

### Xem danh sách người dùng:
```sql
SELECT id, username, email, full_name, role FROM users LIMIT 10;
```

### Xem danh sách sản phẩm:
```sql
SELECT id, name, price, old_price, type, stock FROM products LIMIT 10;
```

### Xem danh sách đơn hàng:
```sql
SELECT * FROM orders LIMIT 10;
```

---

## 🧪 Test Tài Khoản

### Đăng nhập Admin
- **Username:** admin
- **Password:** password123
- **URL:** http://localhost/gundamShop/admin/

### Đăng nhập Khách Hàng
- **Username:** nguyenvana
- **Password:** password123
- **URL:** http://localhost/gundamShop/

---

## ⚠️ Lưu Ý Quan Trọng

1. **Chạy sau khi tạo bảng** - Hãy chắc chắn đã chạy `sql.sql` trước đó
2. **Không trùng lặp** - Script tự động xử lý các lỗi duplicate key
3. **Mật khẩu** - Tất cả tài khoản đều dùng mật khẩu `password123`
4. **Dữ liệu thực tế** - Các đơn hàng, giá cả và thông tin đều là dữ liệu mẫu thực tế
5. **Reset dữ liệu** - Nếu muốn chạy lại, xóa các bảng hoặc database trước

---

## 🆘 Xử Lý Lỗi

### Lỗi: "Table already exists"
→ Điều này là bình thường, script sẽ bỏ qua

### Lỗi: "Foreign key constraint fails"
→ Chắc chắn là database chưa được tạo đúng cách
→ Chạy `sql.sql` trước rồi chạy `seed_full_data.sql`

### Lỗi: "Access denied"
→ Kiểm tra thông tin đăng nhập MySQL trong `config/db.php`

### Lỗi: File not found
→ Đảm bảo file `seed_full_data.sql` nằm trong thư mục `database/`

---

## 📊 Thống Kê Dữ Liệu

| Thành phần | Số lượng |
|-----------|---------|
| Người dùng | 25 |
| Admin | 3 |
| Khách hàng | 22 |
| Danh mục | 10 |
| Sản phẩm | 60+ |
| Đơn hàng | 20 |
| Chi tiết đơn hàng | 28 |
| Đánh giá | 15 |
| Giỏ hàng | 19 |
| Phiên chat | 5 |
| Tin nhắn chat | 10+ |
| Thông báo | 10 |

---

## 💡 Tips

- Sử dụng dữ liệu này để test các tính năng
- Có thể tạo thêm dữ liệu khác dựa trên mẫu này
- Để xóa dữ liệu và reset, xóa database và tạo mới
- Dữ liệu được thiết kế gần với thực tế để dễ test

---

**Tạo bởi:** Gundam Store HUMG  
**Ngày:** 2026-06-26  
**Phiên bản:** 1.0
