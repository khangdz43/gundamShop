-- ============================================================
-- SEED DATA ĐẦY ĐỦ CHO GUNDAM STORE HUMG
-- File: seed_full_data.sql
-- Chạy file này SAU KHI đã import sql.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- ============================================================
-- 1. THÊM NHIỀU USERS (mật khẩu đều là "password123")
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================================

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `phone`, `address`, `role`, `is_active`, `created_at`) VALUES
-- Admin users
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gundamstore.vn', 'Admin Gundam Store', '0900000000', '1 Nguyen Du, Q.1, TP.HCM', 'admin', 1, '2026-05-01 08:00:00'),
(2, 'admin02', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin2@gundamstore.vn', 'Tran Dinh Khoa', '0900111222', '1 Nguyen Du, Q.1, TP.HCM', 'admin', 1, '2026-05-01 08:00:00'),
(3, 'admin03', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin3@gundamstore.vn', 'Le Thi Mai Anh', '0901111222', '2 Tran Hung Dao, Q.1, TP.HCM', 'admin', 1, '2026-05-01 09:00:00'),

-- Khách hàng thường xuyên
(4, 'nguyenvana', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nguyenvana@gmail.com', 'Nguyen Van An', '0901234567', '45 Nguyen Hue, Q.1, TP.HCM', 'user', 1, '2026-05-10 08:00:00'),
(5, 'tranthib', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tranthib@gmail.com', 'Tran Thi Bich', '0912345678', '12 Le Loi, Q.Hoan Kiem, Ha Noi', 'user', 1, '2026-05-15 09:30:00'),
(6, 'leminhc', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'leminhc@yahoo.com', 'Le Minh Chau', '0933456789', '88 Tran Phu, Q.Hai Chau, Da Nang', 'user', 1, '2026-05-20 10:15:00'),
(7, 'phamthuyd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'phamthuyd@gmail.com', 'Pham Thuy Dung', '0944567890', '56 Hoang Van Thu, TP. Thai Nguyen', 'user', 1, '2026-05-25 11:00:00'),
(8, 'hoangvane', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hoangvane@hotmail.com', 'Hoang Van Em', '0955678901', '99 Bach Dang, Q.Hong Bang, Hai Phong', 'user', 1, '2026-06-01 08:45:00'),
(9, 'vuthif', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vuthif@gmail.com', 'Vu Thi Phuong', '0966789012', '23 Dien Bien Phu, Q.3, TP.HCM', 'user', 1, '2026-06-05 09:00:00'),
(10, 'dinhvang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dinhvang@gmail.com', 'Dinh Van Giang', '0977890123', '34 Nguyen Trai, Q.Thanh Xuan, Ha Noi', 'user', 1, '2026-06-08 10:30:00'),
(11, 'buithih', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buithih@gmail.com', 'Bui Thi Hoa', '0988901234', '67 Ly Thuong Kiet, Q.10, TP.HCM', 'user', 1, '2026-06-10 11:15:00'),
(12, 'ngohungi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ngohungi@gmail.com', 'Ngo Hung Ich', '0999012345', '11 Cau Giay, Q.Cau Giay, Ha Noi', 'user', 1, '2026-06-12 08:00:00'),
(13, 'doanthij', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doanthij@gmail.com', 'Doan Thi Kieu', '0900123456', '78 Pasteur, Q.3, TP.HCM', 'user', 1, '2026-06-14 09:30:00'),
(14, 'tranbinhk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'binhtran@gmail.com', 'Tran Binh Khai', '0901234560', '100 Hai Ba Trung, Q.1, TP.HCM', 'user', 1, '2026-06-15 08:00:00'),
(15, 'phungvang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'phung.van.g@gmail.com', 'Phung Van Giang', '0912345679', '234 Nguyen Hue, Q.1, TP.HCM', 'user', 1, '2026-06-16 09:00:00'),
(16, 'cholo95', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cholo.95@hotmail.com', 'Cho Lo Linh', '0933456780', '567 Le Loi, Ha Noi', 'user', 1, '2026-06-17 10:00:00'),
(17, 'minguyen23', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'minguyen23@yahoo.com', 'Minh Nguyen', '0944567891', '789 Tran Phu, Da Nang', 'user', 1, '2026-06-18 11:00:00'),
(18, 'huongpham', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'huong.pham@gmail.com', 'Pham Huong', '0955678902', '112 Bach Dang, Hai Phong', 'user', 1, '2026-06-19 08:30:00'),
(19, 'anhvu2024', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'anhvu2024@hotmail.com', 'Vu Anh', '0966789013', '345 Dien Bien Phu, TP.HCM', 'user', 1, '2026-06-20 09:00:00'),
(20, 'anhduongn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'anhduong.n@gmail.com', 'Duong Anh', '0977890124', '456 Pasteur, TP.HCM', 'user', 1, '2026-06-21 10:15:00'),
(21, 'anhtrinh9x', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'anhtrinh9x@gmail.com', 'Trinh Anh', '0988901235', '678 Nguyen Trai, Ha Noi', 'user', 1, '2026-06-22 11:30:00'),
(22, 'thaohoang25', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'thaoh25@yahoo.com', 'Hoang Thao', '0999012346', '789 Ly Thuong Kiet, TP.HCM', 'user', 1, '2026-06-23 08:00:00'),
(23, 'longuit00', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'longuit00@hotmail.com', 'Tran Long', '0900234567', '890 Cau Giay, Ha Noi', 'user', 1, '2026-06-24 09:30:00'),
(24, 'sophiatk88', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sophia88@gmail.com', 'Tran Sophia', '0901345678', '901 Hoang Van Thu, Thai Nguyen', 'user', 1, '2026-06-25 10:00:00'),
(25, 'vietkhang123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vietkhang123@gmail.com', 'Khang Viet', '0912456789', '102 Bach Dang, Hai Phong', 'user', 1, '2026-06-26 08:00:00');

-- ============================================================
-- 2. THÊM CATEGORIES (Nếu chưa có)
-- ============================================================
INSERT INTO `categories` (`name`, `slug`, `description`) VALUES
('Gundam Universal Century', 'gundam-uc', 'Các mô hình thuộc vũ trụ Universal Century - dòng chính của Gundam'),
('Gundam SEED', 'gundam-seed', 'Các mô hình thuộc vũ trụ Cosmic Era từ series Gundam SEED'),
('Iron-Blooded Orphans', 'ibo', 'Các mô hình từ Mobile Suit Gundam: Iron-Blooded Orphans'),
('Zaku & Zeon', 'zaku', 'Các mô hình phe Zeon - đối thủ chính của Gundam'),
('SD Gundam', 'sd', 'Dòng Super Deformed - cute và dễ lắp'),
('Gundam Wing', 'gundam-wing', 'Các mô hình từ series Gundam Wing Endless Waltz'),
('Gundam 00', 'gundam-00', 'Các mô hình từ series Mobile Suit Gundam 00'),
('Zeta Gundam', 'zeta-gundam', 'Các mô hình từ series Zeta Gundam'),
('Gundam Unicorn', 'gundam-unicorn', 'Các mô hình từ series Gundam Unicorn'),
('Crossbone Gundam', 'crossbone', 'Các mô hình Crossbone - pirate Gundam');

-- ============================================================
-- 3. THÊM NHIỀU PRODUCTS (60+ sản phẩm)
-- ============================================================

INSERT INTO `products` (`name`, `slug`, `price`, `old_price`, `category_id`, `category`, `image`, `description`, `type`, `series`, `scale`, `stock`, `is_featured`, `is_sale`, `status`) VALUES
-- HG Products
('RX-78-2 Gundam (HG 1/144)', 'rx-78-2-gundam-hg', 340000, NULL, 1, 'Gundam', 'models_default_img.jpeg', 'HG RX-78-2 Gundam - mô hình cổ điển đầu tiên, tuyệt vời cho người mới bắt đầu', 'HG', 'Mobile Suit Gundam', '1/144', 120, 1, 0, 'active'),
('Freedom Gundam (HG SEED)', 'freedom-gundam-hg-seed', 580000, 680000, 2, 'Gundam', 'models_default_img.jpeg', 'HG Freedom Gundam từ Gundam SEED - cánh sải ấn tượng, khớp cử động linh hoạt', 'HG', 'Gundam SEED', '1/144', 85, 1, 1, 'active'),
('Justice Gundam (HG SEED)', 'justice-gundam-hg-seed', 560000, NULL, 2, 'Gundam', 'models_default_img.jpeg', 'HG Justice Gundam - cặp đôi cùng Freedom, thiết kế linh hoạt với Fatum-01', 'HG', 'Gundam SEED', '1/144', 60, 0, 0, 'active'),
('Destiny Gundam (HG SEED)', 'destiny-gundam-hg-seed', 620000, NULL, 2, 'Gundam', 'models_default_img.jpeg', 'HG Destiny Gundam - hình dáng oai phong, cánh sáng đặc trưng từ Gundam SEED Destiny', 'HG', 'Gundam SEED Destiny', '1/144', 75, 1, 0, 'active'),
('Impulse Gundam (HG)', 'impulse-gundam-hg', 540000, NULL, 2, 'Gundam', 'models_default_img.jpeg', 'HG Impulse Gundam - hệ thống lắp ghép Silhouette độc đáo', 'HG', 'Gundam SEED Destiny', '1/144', 55, 0, 0, 'active'),
('Gundam Barbatos (HG IBO)', 'gundam-barbatos-hg', 520000, NULL, 3, 'Gundam', 'models_default_img.jpeg', 'HG Barbatos - MS chiến đấu mạnh mẽ từ Iron-Blooded Orphans', 'HG', 'Iron-Blooded Orphans', '1/144', 95, 1, 0, 'active'),
('Gundam Kimaris Trooper (HG)', 'gundam-kimaris-trooper-hg', 590000, NULL, 3, 'Gundam', 'models_default_img.jpeg', 'HG Kimaris Trooper - MS chiến đấu mặt đất với giao pháp lực từ Iron-Blooded Orphans', 'HG', 'Iron-Blooded Orphans', '1/144', 50, 0, 0, 'active'),
('Gundam Flauros (HG)', 'gundam-flauros-hg', 570000, NULL, 3, 'Gundam', 'models_default_img.jpeg', 'HG Flauros - pháo thủ hạng nặng, hai pháo ray gun ấn tượng', 'HG', 'Iron-Blooded Orphans', '1/144', 45, 0, 0, 'active'),
('Zaku II Commander Type (HG)', 'zaku-ii-commander-type-hg', 480000, 530000, 4, 'Zaku', 'Zaku II Green.jpg', 'HG Zaku II Commander Type - phiên bản chỉ huy với anten đặc trưng, màu xanh rêu Zeon', 'HG', 'Mobile Suit Gundam', '1/144', 110, 1, 1, 'active'),
('Dom (HG UC)', 'dom-hg-uc', 510000, NULL, 4, 'Zaku', 'models_default_img.jpeg', 'HG Dom - MS hạng nặng lượt mặt đất của Zeon, thiết kế tròn trịa đặc trưng', 'HG', 'Mobile Suit Gundam', '1/144', 70, 0, 0, 'active'),
('Gelgoog (HG UC)', 'gelgoog-hg-uc', 490000, NULL, 4, 'Zaku', 'models_default_img.jpeg', 'HG Gelgoog - MS cao cấp nhất của Zeon, sử dụng thanh kiếm beam', 'HG', 'Mobile Suit Gundam', '1/144', 65, 0, 0, 'active'),
('Wing Gundam (HG)', 'wing-gundam-hg', 380000, NULL, 6, 'Gundam', 'models_default_img.jpeg', 'HG Wing Gundam - Heero Yuy, thiết kế cánh lớn độc đáo', 'HG', 'Gundam Wing', '1/144', 80, 0, 0, 'active'),
('00 Gundam (HG)', '00-gundam-hg', 360000, NULL, 7, 'Gundam', 'models_default_img.jpeg', 'HG 00 Gundam - Setsuna F. Seiei, Twin Drive System', 'HG', 'Gundam 00', '1/144', 90, 0, 0, 'active'),
('Guncannon (HG)', 'guncannon-hg', 310000, NULL, 1, 'Gundam', 'models_default_img.jpeg', 'HG Guncannon - unit hỗ trợ Universal Century', 'HG', 'Mobile Suit Gundam', '1/144', 120, 0, 0, 'active'),
('Guntank (HG)', 'guntank-hg', 300000, NULL, 1, 'Gundam', 'models_default_img.jpeg', 'HG Guntank - unit hỗ trợ cổ điển', 'HG', 'Mobile Suit Gundam', '1/144', 125, 0, 0, 'active'),
('GM (HG)', 'gm-hg', 280000, NULL, 1, 'Gundam', 'models_default_img.jpeg', 'HG GM - unit sản xuất hàng loạt', 'HG', 'Mobile Suit Gundam', '1/144', 150, 0, 0, 'active'),

-- SD Products
('SD Gundam Freedom', 'sd-gundam-freedom', 220000, NULL, 5, 'Gundam', 'models_default_img.jpeg', 'SD Freedom Gundam - dễ thương, lắp ráp nhanh trong 30 phút', 'SD', 'Gundam SEED', 'SD', 200, 0, 0, 'active'),
('SD Gundam Wing Zero', 'sd-gundam-wing-zero', 230000, NULL, 5, 'Gundam', 'models_default_img.jpeg', 'SD Wing Gundam Zero - thiết kế cực kỳ dễ thương với cánh angel thu nhỏ', 'SD', 'Gundam Wing', 'SD', 180, 0, 0, 'active'),
('SD Zaku II', 'sd-zaku-ii', 200000, NULL, 5, 'Gundam', 'models_default_img.jpeg', 'SD Zaku II - phiên bản SD của huyền thoại phe Zeon, cực kỳ đáng yêu', 'SD', 'Mobile Suit Gundam', 'SD', 250, 1, 0, 'active'),
('SD Gundam Barbatos', 'sd-gundam-barbatos', 210000, 250000, 5, 'Gundam', 'models_default_img.jpeg', 'SD Barbatos - phiên bản mini dễ thương từ Iron-Blooded Orphans', 'SD', 'Iron-Blooded Orphans', 'SD', 130, 0, 1, 'active'),
('SD Unicorn Gundam', 'sd-unicorn-gundam', 215000, NULL, 5, 'Gundam', 'models_default_img.jpeg', 'SD Unicorn - phiên bản SD của Unicorn Gundam', 'SD', 'Gundam Unicorn', 'SD', 140, 0, 0, 'active'),

-- RG Products
('Strike Freedom Gundam (RG)', 'strike-freedom-gundam-rg', 1150000, 1350000, 2, 'Gundam', 'models_default_img.jpeg', 'RG Strike Freedom - siêu phẩm RG 1/144 với khung nội khung Inner Frame chi tiết', 'RG', 'Gundam SEED Destiny', '1/144', 50, 1, 1, 'active'),
('Destiny Gundam (RG)', 'destiny-gundam-rg', 1100000, NULL, 2, 'Gundam', 'models_default_img.jpeg', 'RG Destiny Gundam - chi tiết siêu cao với các đường gắn nối khung rõ nét', 'RG', 'Gundam SEED Destiny', '1/144', 45, 1, 0, 'active'),
('Gundam Barbatos Lupus Rex (RG)', 'barbatos-lupus-rex-rg', 1050000, 1200000, 3, 'Gundam', 'models_default_img.jpeg', 'RG Barbatos Lupus Rex - form cuối cùng của Barbatos, vũ khí mace cực đa', 'RG', 'Iron-Blooded Orphans', '1/144', 40, 1, 1, 'active'),
('Zaku II (RG)', 'zaku-ii-rg', 920000, NULL, 4, 'Zaku', 'models_default_img.jpeg', 'RG Zaku II - phiên bản RG với chi tiết nối khung chính xác, màu xanh Zeon', 'RG', 'Mobile Suit Gundam', '1/144', 70, 0, 0, 'active'),
('00 Raiser (RG)', '00-raiser-rg', 890000, 990000, 7, 'Gundam', 'models_default_img.jpeg', 'RG 00 Raiser - Twin Drive System chi tiết cao', 'RG', 'Gundam 00', '1/144', 55, 1, 1, 'active'),
('Banshee Norn (RG)', 'banshee-norn-rg', 1150000, 1280000, 9, 'Gundam', 'models_default_img.jpeg', 'RG Unicorn Banshee Norn - phiên bản Newtype', 'RG', 'Gundam Unicorn', '1/144', 35, 1, 1, 'active'),

-- MG Products
('Freedom Gundam Ver.2.0 (MG)', 'freedom-gundam-ver2-mg', 1500000, 1700000, 2, 'Gundam', 'models_default_img.jpeg', 'MG Freedom 2.0 - bản nâng cấp hoàn toàn với khung xương mới', 'MG', 'Gundam SEED', '1/100', 40, 1, 1, 'active'),
('Destiny Gundam Spec II (MG)', 'destiny-gundam-spec2-mg', 1650000, NULL, 2, 'Gundam', 'models_default_img.jpeg', 'MG Destiny Spec II - phiên bản cải tiến 2024 với chi tiết nâng cao', 'MG', 'Gundam SEED Destiny', '1/100', 30, 1, 0, 'active'),
('Barbatos Lupus Rex (MG)', 'barbatos-lupus-rex-mg', 1400000, 1600000, 3, 'Gundam', 'models_default_img.jpeg', 'MG Barbatos Lupus Rex - chi tiết cực kỳ cao, mace weapon không lồ', 'MG', 'Iron-Blooded Orphans', '1/100', 35, 1, 1, 'active'),
('Zaku II Ver.2.0 (MG)', 'zaku-ii-ver2-mg', 1100000, 1250000, 4, 'Zaku', 'models_default_img.jpeg', 'MG Zaku II 2.0 - khung xương nối chi tiết, cylinder arm đặc trưng', 'MG', 'Mobile Suit Gundam', '1/100', 50, 0, 1, 'active'),
('Chars Zaku II Ver.2.0 (MG)', 'chars-zaku-ii-ver2-mg', 1150000, NULL, 4, 'Zaku', 'models_default_img.jpeg', 'MG Char Zaku II - phiên bản màu đỏ huyền thoại của Char Aznable', 'MG', 'Mobile Suit Gundam', '1/100', 45, 1, 0, 'active'),
('Nu Gundam Ver.Ka (MG)', 'nu-gundam-verka-mg', 2100000, 2400000, 1, 'Gundam', 'models_default_img.jpeg', 'MG Nu Gundam Ver.Ka - kiệt tác của Hajime Katoki, fin funnel ấn tượng', 'MG', 'Char Counterattack', '1/100', 20, 1, 1, 'active'),
('Unicorn Gundam Ver.Ka (MG)', 'unicorn-gundam-verka-mg', 1900000, NULL, 9, 'Gundam', 'models_default_img.jpeg', 'MG Unicorn Ver.Ka - chuyển đổi Unicorn/Destroy mode, Psycho-frame phát sáng UV', 'MG', 'Gundam Unicorn', '1/100', 28, 1, 0, 'active'),
('Deathscythe Hell (MG)', 'deathscythe-hell-mg', 980000, NULL, 6, 'Gundam', 'models_default_img.jpeg', 'MG Deathscythe Hell - Duo Maxwell, lưỡi liêm xoay', 'MG', 'Gundam Wing', '1/100', 38, 0, 0, 'active'),
('Qan[T] Full Saber (MG)', 'qan-t-full-saber-mg', 1250000, NULL, 7, 'Gundam', 'models_default_img.jpeg', 'MG Qan[T] Full Saber - quantum system', 'MG', 'Gundam 00', '1/100', 32, 0, 0, 'active'),
('Zeta Gundam Ver.Ka (MG)', 'zeta-gundam-verka-mg', 1680000, NULL, 8, 'Gundam', 'models_default_img.jpeg', 'MG Zeta Gundam Ver.Ka - biến hình Waverider', 'MG', 'Zeta Gundam', '1/100', 22, 1, 0, 'active'),
('Hyaku Shiki (MG)', 'hyaku-shiki-mg', 1050000, 1180000, 8, 'Gundam', 'models_default_img.jpeg', 'MG Hyaku Shiki - màu vàng gold', 'MG', 'Zeta Gundam', '1/100', 28, 0, 1, 'active'),

-- PG Products
('Strike Freedom Gundam (PG)', 'strike-freedom-gundam-pg', 6800000, 7500000, 2, 'Gundam', 'models_default_img.jpeg', 'PG Strike Freedom - siêu phẩm Perfect Grade với LED unit tích hợp', 'PG', 'Gundam SEED Destiny', '1/60', 8, 1, 1, 'active'),
('Unicorn Gundam (PG)', 'unicorn-gundam-pg', 7200000, NULL, 9, 'Gundam', 'models_default_img.jpeg', 'PG Unicorn Gundam - bộ kit lớn nhất với LED Psycho-frame', 'PG', 'Gundam Unicorn', '1/60', 6, 1, 0, 'active'),
('Wing Gundam Zero Custom (PG)', 'wing-gundam-zero-custom-pg', 6800000, NULL, 6, 'Gundam', 'models_default_img.jpeg', 'PG Wing Zero Custom EW - cánh angel LED', 'PG', 'Gundam Wing', '1/60', 5, 0, 0, 'active'),

-- MGEX Products
('Nu Gundam (MGEX)', 'nu-gundam-mgex', 3500000, NULL, 1, 'Gundam', 'models_default_img.jpeg', 'MGEX Nu Gundam - dòng cao cấp nhất của MG với chi tiết vượt trội', 'MGEX', 'Char Counterattack', '1/100', 12, 1, 0, 'active'),
('Infinite Justice Gundam (MGEX)', 'infinite-justice-gundam-mgex', 2250000, NULL, 2, 'Gundam', 'models_default_img.jpeg', 'MGEX Infinite Justice - cao cấp với công nghệ mới nhất', 'MGEX', 'Gundam SEED Destiny', '1/100', 15, 1, 0, 'active'),

-- Thêm nhiều sản phẩm khác
('Red Frame Astray (MG)', 'red-frame-astray-mg', 920000, NULL, 2, 'Gundam', 'models_default_img.jpeg', 'MG Astray Red Frame với katana', 'MG', 'Gundam SEED Astray', '1/100', 42, 1, 0, 'active'),
('Blue Frame D (HG)', 'blue-frame-d-hg', 340000, NULL, 2, 'Gundam', 'models_default_img.jpeg', 'HG Astray Blue Frame D', 'HG', 'Gundam SEED Astray', '1/144', 75, 0, 0, 'active'),
('Gouf (HG)', 'gouf-hg', 330000, NULL, 4, 'Zaku', 'models_default_img.jpeg', 'HG Gouf - phe Zeon, thiết kế tay lớn độc đáo', 'HG', 'Mobile Suit Gundam', '1/144', 70, 0, 0, 'active'),
('Hi-Nu Gundam (RG)', 'hi-nu-gundam-rg', 1380000, 1500000, 1, 'Gundam', 'models_default_img.jpeg', 'RG Hi-Nu Ver.Ka - Amuro Ray', 'RG', 'Char Counterattack', '1/144', 30, 1, 1, 'active'),
('Crossbone Gundam X1 (MG)', 'crossbone-gundam-x1-mg', 780000, NULL, 10, 'Gundam', 'models_default_img.jpeg', 'MG Crossbone X1 - pirate Gundam', 'MG', 'Crossbone Gundam', '1/100', 32, 0, 0, 'active'),
('Sinanju (MG)', 'sinanju-mg', 1450000, 1600000, 1, 'Gundam', 'models_default_img.jpeg', 'MG Sinanju Ver.Ka - màu đỏ huyền thoại', 'MG', 'Gundam Unicorn', '1/100', 25, 1, 1, 'active');

-- ============================================================
-- 4. THÊM NHIỀU ORDERS (Đơn hàng)
-- ============================================================

INSERT INTO `orders` (`order_code`, `user_id`, `full_name`, `phone`, `email`, `address`, `note`, `subtotal`, `shipping_fee`, `total`, `payment_method`, `status`, `created_at`) VALUES
('GD260501001', 4, 'Nguyen Van An', '0901234567', 'nguyenvana@gmail.com', '45 Nguyen Hue, Q.1, TP.HCM', 'Giao giờ hành chính', 580000.00, 30000.00, 610000.00, 'cod', 'delivered', '2026-05-12 10:00:00'),
('GD260502002', 4, 'Nguyen Van An', '0901234567', 'nguyenvana@gmail.com', '45 Nguyen Hue, Q.1, TP.HCM', NULL, 1500000.00, 0.00, 1500000.00, 'bank_transfer', 'delivered', '2026-05-21 09:00:00'),
('GD260503003', 4, 'Nguyen Van An', '0901234567', 'nguyenvana@gmail.com', '45 Nguyen Hue, Q.1, TP.HCM', NULL, 3500000.00, 0.00, 3500000.00, 'bank_transfer', 'shipping', '2026-06-02 08:00:00'),
('GD260504004', 5, 'Tran Thi Bich', '0912345678', 'tranthib@gmail.com', '12 Le Loi, Q.Hoan Kiem, Ha Noi', 'Giao nhanh', 1050000.00, 30000.00, 1080000.00, 'cod', 'delivered', '2026-05-16 14:00:00'),
('GD260505005', 5, 'Tran Thi Bich', '0912345678', 'tranthib@gmail.com', '12 Le Loi, Q.Hoan Kiem, Ha Noi', NULL, 2200000.00, 0.00, 2200000.00, 'bank_transfer', 'confirmed', '2026-06-03 10:00:00'),
('GD260506006', 6, 'Le Minh Chau', '0933456789', 'leminhc@yahoo.com', '88 Tran Phu, Q.Hai Chau, Da Nang', 'Để tại bưu cục', 850000.00, 30000.00, 880000.00, 'cod', 'delivered', '2026-05-23 08:00:00'),
('GD260507007', 6, 'Le Minh Chau', '0933456789', 'leminhc@yahoo.com', '88 Tran Phu, Q.Hai Chau, Da Nang', NULL, 1150000.00, 0.00, 1150000.00, 'bank_transfer', 'pending', '2026-06-05 09:00:00'),
('GD260508008', 7, 'Pham Thuy Dung', '0944567890', 'phamthuyd@gmail.com', 'TP. Thai Nguyen', NULL, 250000.00, 30000.00, 280000.00, 'cod', 'delivered', '2026-05-27 10:00:00'),
('GD260509009', 7, 'Pham Thuy Dung', '0944567890', 'phamthuyd@gmail.com', 'TP. Thai Nguyen', 'Hàng mong manh', 920000.00, 30000.00, 950000.00, 'cod', 'shipping', '2026-06-06 11:00:00'),
('GD260510010', 8, 'Hoang Van Em', '0955678901', 'hoangvane@hotmail.com', '99 Bach Dang, Q.Hong Bang, Hai Phong', NULL, 2100000.00, 0.00, 2100000.00, 'bank_transfer', 'delivered', '2026-06-04 08:00:00'),
('GD260511011', 8, 'Hoang Van Em', '0955678901', 'hoangvane@hotmail.com', '99 Bach Dang, Q.Hong Bang, Hai Phong', NULL, 480000.00, 30000.00, 510000.00, 'cod', 'cancelled', '2026-06-16 09:00:00'),
('GD260512012', 9, 'Vu Thi Phuong', '0966789012', 'vuthif@gmail.com', '23 Dien Bien Phu, Q.3, TP.HCM', 'Tặng sinh nhật bạn', 1900000.00, 0.00, 1900000.00, 'bank_transfer', 'delivered', '2026-06-08 10:00:00'),
('GD260513013', 9, 'Vu Thi Phuong', '0966789012', 'vuthif@gmail.com', '23 Dien Bien Phu, Q.3, TP.HCM', NULL, 6800000.00, 0.00, 6800000.00, 'bank_transfer', 'confirmed', '2026-06-19 08:00:00'),
('GD260514014', 10, 'Dinh Van Giang', '0977890123', 'dinhvang@gmail.com', '34 Nguyen Trai, Q.Thanh Xuan, Ha Noi', NULL, 1150000.00, 0.00, 1150000.00, 'bank_transfer', 'delivered', '2026-06-11 08:00:00'),
('GD260515015', 10, 'Dinh Van Giang', '0977890123', 'dinhvang@gmail.com', '34 Nguyen Trai, Q.Thanh Xuan, Ha Noi', 'Giao buổi chiều', 580000.00, 30000.00, 610000.00, 'cod', 'pending', '2026-06-21 10:00:00'),
('GD260516016', 11, 'Bui Thi Hoa', '0988901234', 'buithih@gmail.com', '67 Ly Thuong Kiet, Q.10, TP.HCM', NULL, 200000.00, 30000.00, 230000.00, 'cod', 'delivered', '2026-06-13 09:00:00'),
('GD260517017', 11, 'Bui Thi Hoa', '0988901234', 'buithih@gmail.com', '67 Ly Thuong Kiet, Q.10, TP.HCM', NULL, 1400000.00, 0.00, 1400000.00, 'bank_transfer', 'shipping', '2026-06-23 08:00:00'),
('GD260518018', 12, 'Ngo Hung Ich', '0999012345', 'ngohungi@gmail.com', '11 Cau Giay, Q.Cau Giay, Ha Noi', 'Giao sáng sớm', 920000.00, 30000.00, 950000.00, 'cod', 'delivered', '2026-06-06 08:00:00'),
('GD260519019', 12, 'Ngo Hung Ich', '0999012345', 'ngohungi@gmail.com', '11 Cau Giay, Q.Cau Giay, Ha Noi', NULL, 3500000.00, 0.00, 3500000.00, 'bank_transfer', 'confirmed', '2026-06-20 10:00:00'),
('GD260520020', 13, 'Doan Thi Kieu', '0900123456', 'doanthij@gmail.com', '78 Pasteur, Q.3, TP.HCM', NULL, 540000.00, 30000.00, 570000.00, 'cod', 'delivered', '2026-05-28 09:00:00');

-- ============================================================
-- 5. THÊM ORDER_ITEMS (Chi tiết đơn hàng)
-- ============================================================

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `product_image`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 'RX-78-2 Gundam (HG 1/144)', 'models_default_img.jpeg', 340000, 1, 340000),
(1, 17, 'SD Gundam Freedom', 'models_default_img.jpeg', 220000, 1, 220000),
(2, 23, 'Strike Freedom Gundam (RG)', 'models_default_img.jpeg', 1150000, 1, 1150000),
(2, 24, 'Destiny Gundam (RG)', 'models_default_img.jpeg', 1100000, 1, 1100000),
(3, 30, 'Freedom Gundam Ver.2.0 (MG)', 'models_default_img.jpeg', 1500000, 1, 1500000),
(3, 34, 'Nu Gundam Ver.Ka (MG)', 'models_default_img.jpeg', 2100000, 1, 2100000),
(4, 25, 'Gundam Barbatos Lupus Rex (RG)', 'models_default_img.jpeg', 1050000, 1, 1050000),
(5, 30, 'Freedom Gundam Ver.2.0 (MG)', 'models_default_img.jpeg', 1500000, 1, 1500000),
(5, 31, 'Destiny Gundam Spec II (MG)', 'models_default_img.jpeg', 1650000, 1, 1650000),
(6, 2, 'Freedom Gundam (HG SEED)', 'models_default_img.jpeg', 580000, 1, 580000),
(6, 6, 'Gundam Barbatos (HG IBO)', 'models_default_img.jpeg', 520000, 1, 520000),
(7, 23, 'Strike Freedom Gundam (RG)', 'models_default_img.jpeg', 1150000, 1, 1150000),
(8, 18, 'SD Gundam Wing Zero', 'models_default_img.jpeg', 230000, 1, 230000),
(9, 26, 'Zaku II (RG)', 'models_default_img.jpeg', 920000, 1, 920000),
(10, 35, 'Unicorn Gundam Ver.Ka (MG)', 'models_default_img.jpeg', 1900000, 1, 1900000),
(10, 36, 'Deathscythe Hell (MG)', 'models_default_img.jpeg', 980000, 1, 980000),
(11, 9, 'Zaku II Commander Type (HG)', 'models_default_img.jpeg', 480000, 1, 480000),
(12, 40, 'Sinanju (MG)', 'models_default_img.jpeg', 1450000, 1, 1450000),
(13, 42, 'Strike Freedom Gundam (PG)', 'models_default_img.jpeg', 6800000, 1, 6800000),
(14, 24, 'Destiny Gundam (RG)', 'models_default_img.jpeg', 1100000, 1, 1100000),
(15, 2, 'Freedom Gundam (HG SEED)', 'models_default_img.jpeg', 580000, 1, 580000),
(16, 20, 'SD Zaku II', 'models_default_img.jpeg', 200000, 1, 200000),
(17, 32, 'Barbatos Lupus Rex (MG)', 'models_default_img.jpeg', 1400000, 1, 1400000),
(18, 3, 'Justice Gundam (HG SEED)', 'models_default_img.jpeg', 560000, 1, 560000),
(18, 19, 'SD Gundam Barbatos', 'models_default_img.jpeg', 210000, 1, 210000),
(19, 41, 'Crossbone Gundam X1 (MG)', 'models_default_img.jpeg', 780000, 1, 780000),
(19, 43, 'Unicorn Gundam (PG)', 'models_default_img.jpeg', 7200000, 1, 7200000),
(20, 4, 'Destiny Gundam (HG SEED)', 'models_default_img.jpeg', 620000, 1, 620000);

-- ============================================================
-- 6. THÊM REVIEWS (Đánh giá sản phẩm)
-- ============================================================

INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 4, 5, 'Sản phẩm quá tuyệt vời! Chất lượng rất tốt, lắp ráp dễ, khớp cử động mượt mà', '2026-05-14 10:00:00'),
(1, 5, 4, 'Rất hài lòng với chất lượng, giá cũng hợp lý cho một HG cổ điển', '2026-05-18 14:30:00'),
(2, 6, 5, 'Freedom Gundam quá đẹp! Chi tiết cánh sải rất ấn tượng, recommend cho mọi người', '2026-05-25 09:00:00'),
(2, 7, 5, 'Mô hình chất lượng cao, đúng mô tả trong shop', '2026-06-08 11:00:00'),
(3, 8, 4, 'Justice Gundam cũng rất đẹp, chi tiết tốt nhưng giá hơi cao', '2026-06-05 15:00:00'),
(4, 9, 5, 'Destiny Gundam tuyệt vời! Hình dáng oai phong, cánh sáng rất ấn tượng', '2026-06-10 10:30:00'),
(6, 10, 5, 'Barbatos là một trong những HG đẹp nhất của tôi, lắp ráp tuyệt vời', '2026-06-12 16:00:00'),
(17, 11, 5, 'SD Freedom cưng lắm! Dễ lắp, mau xong, chất lượng tốt cho giá', '2026-06-15 11:00:00'),
(18, 12, 4, 'SD Wing Zero rất dễ thương, lắp dễ trong 30 phút', '2026-06-08 12:00:00'),
(23, 4, 5, 'RG Strike Freedom là siêu phẩm! Chi tiết cực cao, đúng giá tiền', '2026-06-04 08:00:00'),
(23, 9, 5, 'Mô hình chất lượng tuyệt vời, Inner Frame chi tiết quá đẹp', '2026-06-20 14:00:00'),
(30, 5, 5, 'MG Freedom 2.0 thực sự tuyệt vời! Nâng cấp rất tốt so với bản cũ', '2026-06-06 10:00:00'),
(31, 11, 4, 'Destiny Spec II chất lượng tốt nhưng giá tương đối cao', '2026-06-25 09:00:00'),
(34, 12, 5, 'Nu Gundam Ver.Ka là kiệt tác! Tiền giá vào đó hàng triệu nhưng quá đáng giá', '2026-06-14 13:00:00'),
(42, 9, 5, 'PG Strike Freedom là điểm nhấn của bộ sưu tập của tôi, tuyệt vời!', '2026-06-21 16:00:00');

-- ============================================================
-- 7. THÊM CART_ITEMS (Giỏ hàng)
-- ============================================================

INSERT INTO `cart` (`user_id`, `product_id`, `quantity`, `added_at`) VALUES
(4, 1, 1, '2026-06-25 10:00:00'),
(4, 23, 2, '2026-06-25 10:05:00'),
(5, 2, 1, '2026-06-25 11:00:00'),
(5, 30, 1, '2026-06-25 11:10:00'),
(6, 17, 3, '2026-06-25 12:00:00'),
(7, 4, 1, '2026-06-25 13:30:00'),
(8, 34, 1, '2026-06-25 14:00:00'),
(9, 42, 1, '2026-06-25 15:00:00'),
(10, 23, 1, '2026-06-25 15:30:00'),
(11, 18, 2, '2026-06-25 16:00:00'),
(12, 6, 1, '2026-06-25 16:30:00'),
(13, 3, 1, '2026-06-25 17:00:00'),
(14, 20, 2, '2026-06-26 08:00:00'),
(15, 24, 1, '2026-06-26 08:30:00'),
(16, 25, 1, '2026-06-26 09:00:00'),
(17, 31, 1, '2026-06-26 09:30:00'),
(18, 9, 1, '2026-06-26 10:00:00'),
(19, 43, 1, '2026-06-26 10:30:00'),
(20, 5, 2, '2026-06-26 11:00:00');

-- ============================================================
-- 8. THÊM CHAT SESSIONS & MESSAGES
-- ============================================================

INSERT INTO `chat_sessions` (`user_id`, `title`, `created_at`, `updated_at`) VALUES
(4, 'Tư vấn mua hàng lần đầu', '2026-06-01 10:00:00', '2026-06-01 14:00:00'),
(5, 'Hỏi về độ chi tiết các dòng mô hình', '2026-06-05 09:00:00', '2026-06-05 11:30:00'),
(6, 'Tư vấn mua HG hay RG', '2026-06-10 08:00:00', '2026-06-10 09:00:00'),
(7, 'Hỏi về dịch vụ bảo hành', '2026-06-12 10:00:00', '2026-06-12 11:00:00'),
(8, 'Tư vấn mô hình SD cho con', '2026-06-15 14:00:00', '2026-06-15 15:00:00');

INSERT INTO `chat_messages` (`session_id`, `user_id`, `role`, `message`, `created_at`) VALUES
(1, 4, 'user', 'Mình mới bắt đầu sưu tầm Gundam, chưa biết nên mua loại nào, bạn giới thiệu cho mình được không?', '2026-06-01 10:00:00'),
(1, 4, 'assistant', 'Chào bạn! Rất vui được hỗ trợ. Với người mới bắt đầu, mình gợi ý nên chọn dòng HG (High Grade) hoặc SD (Super Deformed). HG có độ chi tiết tốt, khớp cử động linh hoạt, rất dễ lắp ráp. SD thì nhỏ gọn, dễ thương, lắp nhanh chóng.', '2026-06-01 10:05:00'),
(2, 5, 'user', 'Khác nhau giữa HG, RG, MG, PG là gì vậy bạn?', '2026-06-05 09:00:00'),
(2, 5, 'assistant', 'Đó là những dòng mô hình khác nhau:\n- HG (High Grade 1/144): Dễ lắp, đẹp, giá tốt, dành cho người mới\n- RG (Real Grade 1/144): Chi tiết cao hơn, khung nội khung, khó lắp hơn\n- MG (Master Grade 1/100): Kích thước lớn hơn, chi tiết cực cao\n- PG (Perfect Grade 1/60): Siêu phẩm, kích thước rất lớn, chi tiết tuyệt vời nhất', '2026-06-05 09:10:00'),
(3, 6, 'user', 'Tôi nên mua HG hay RG bây giờ?', '2026-06-10 08:00:00'),
(3, 6, 'assistant', 'Nếu bạn là người mới, tôi khuyên nên chọn HG trước. Bạn sẽ làm quen với quá trình lắp ráp, không cần keo hay sơn. Khi đã có kinh nghiệm, có thể nâng cấp lên RG hoặc MG.', '2026-06-10 08:05:00');

-- ============================================================
-- 9. NOTIFICATIONS (Thông báo)
-- ============================================================

INSERT INTO `notifications` (`user_id`, `type`, `subject`, `message`, `is_read`, `created_at`) VALUES
(4, 'order_status', 'Đơn hàng của bạn đã giao thành công', 'Đơn hàng GD260501001 đã được giao thành công vào lúc 14:00 ngày hôm qua', 1, '2026-05-13 14:05:00'),
(4, 'new_product', 'Sản phẩm mới: Nu Gundam MGEX', 'MGEX Nu Gundam - dòng cao cấp nhất vừa được thêm vào kho', 1, '2026-06-01 08:00:00'),
(5, 'order_status', 'Đơn hàng của bạn đang vận chuyển', 'Đơn hàng GD260505005 đang trên đường giao đến bạn', 0, '2026-06-04 10:00:00'),
(6, 'order_confirm', 'Xác nhận đơn hàng GD260506006', 'Cảm ơn bạn đã mua hàng! Đơn hàng của bạn đang được chuẩn bị', 1, '2026-05-23 09:00:00'),
(7, 'new_product', 'Sản phẩm giảm giá: RG Strike Freedom', 'RG Strike Freedom giảm từ 1.350.000 xuống 1.150.000', 1, '2026-06-01 10:00:00'),
(8, 'order_status', 'Đơn hàng GD260510010 đã giao', 'Cảm ơn bạn đã mua hàng tại Gundam Store HUMG', 1, '2026-06-08 17:05:00'),
(9, 'promotional', 'Khuyến mãi tháng 6: Giảm tối đa 30%', 'Hãy chọn các sản phẩm yêu thích của bạn và nhận giảm giá hôm nay', 0, '2026-06-01 07:00:00'),
(10, 'order_confirm', 'Xác nhận đơn hàng GD260514014', 'Đơn hàng của bạn đã được xác nhận, chuẩn bị giao sớm nhất', 1, '2026-06-11 08:30:00'),
(11, 'new_product', 'Hàng mới về: MGEX Infinite Justice', 'Sản phẩm mới MGEX Infinite Justice Gundam vừa có sẵn', 0, '2026-06-15 08:00:00'),
(12, 'order_status', 'Đơn hàng GD260519019 đang vận chuyển', 'Theo dõi đơn hàng của bạn với mã vận chuyển XYZ123ABC', 0, '2026-06-20 11:00:00');

SET FOREIGN_KEY_CHECKS=1;
