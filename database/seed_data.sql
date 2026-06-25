-- ============================================================
-- SEED DATA CHO GUNDAM STORE HUMG
-- Chạy file này SAU KHI đã import sql.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- ============================================================
-- 1. THÊM USERS (mật khẩu đều là "password123")
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================================

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `phone`, `address`, `role`, `is_active`, `created_at`) VALUES
(4,  'nguyenvana',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nguyenvana@gmail.com',   'Nguyen Van An',    '0901234567', '45 Nguyen Hue, Q.1, TP.HCM',              'user',     1, '2026-05-10 08:00:00'),
(5,  'tranthib',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tranthib@gmail.com',     'Tran Thi Bich',    '0912345678', '12 Le Loi, Q.Hoan Kiem, Ha Noi',           'user',     1, '2026-05-15 09:30:00'),
(6,  'leminhc',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'leminhc@yahoo.com',      'Le Minh Chau',     '0933456789', '88 Tran Phu, Q.Hai Chau, Da Nang',         'user',     1, '2026-05-20 10:15:00'),
(7,  'phamthuyd',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'phamthuyd@gmail.com',    'Pham Thuy Dung',   '0944567890', '56 Hoang Van Thu, TP. Thai Nguyen',        'user',     1, '2026-05-25 11:00:00'),
(8,  'hoangvane',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hoangvane@hotmail.com',  'Hoang Van Em',     '0955678901', '99 Bach Dang, Q.Hong Bang, Hai Phong',     'user',     1, '2026-06-01 08:45:00'),
(9,  'vuthif',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vuthif@gmail.com',       'Vu Thi Phuong',    '0966789012', '23 Dien Bien Phu, Q.3, TP.HCM',            'user',     1, '2026-06-05 09:00:00'),
(10, 'dinhvang',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dinhvang@gmail.com',     'Dinh Van Giang',   '0977890123', '34 Nguyen Trai, Q.Thanh Xuan, Ha Noi',     'user',     1, '2026-06-08 10:30:00'),
(11, 'buithih',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buithih@gmail.com',      'Bui Thi Hoa',      '0988901234', '67 Ly Thuong Kiet, Q.10, TP.HCM',          'user',     1, '2026-06-10 11:15:00'),
(12, 'ngohungi',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ngohungi@gmail.com',     'Ngo Hung Ich',     '0999012345', '11 Cau Giay, Q.Cau Giay, Ha Noi',          'user',     1, '2026-06-12 08:00:00'),
(13, 'doanthij',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doanthij@gmail.com',     'Doan Thi Kieu',    '0900123456', '78 Pasteur, Q.3, TP.HCM',                  'user',     1, '2026-06-14 09:30:00'),
(14, 'staff01',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff01@gundamstore.vn', 'Tran Dinh Khoa',   '0901111222', '1 Nguyen Du, Q.1, TP.HCM',                 'employee', 1, '2026-05-01 08:00:00'),
(15, 'staff02',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff02@gundamstore.vn', 'Le Thi Mai Anh',   '0902222333', '2 Tran Hung Dao, Q.1, TP.HCM',             'employee', 1, '2026-05-01 08:00:00');

-- ============================================================
-- 2. THÊM PRODUCTS
-- ============================================================

-- Cập nhật category_id và series cho sản phẩm hiện có
UPDATE `products` SET `category_id` = 1, `series` = 'Mobile Suit Gundam', `scale` = '1/144' WHERE `id` = 1;
UPDATE `products` SET `category_id` = 2, `series` = 'Gundam SEED Destiny', `scale` = '1/100' WHERE `id` = 2;
UPDATE `products` SET `category_id` = 3, `series` = 'Iron-Blooded Orphans', `scale` = '1/144' WHERE `id` = 3;
UPDATE `products` SET `category_id` = 1, `series` = 'Gundam Unicorn', `scale` = '1/144' WHERE `id` = 4;
UPDATE `products` SET `category_id` = 1, `series` = 'Gundam Wing Endless Waltz', `scale` = '1/100' WHERE `id` = 5;
UPDATE `products` SET `category_id` = 4, `series` = 'Mobile Suit Gundam', `scale` = '1/144' WHERE `id` = 6;
UPDATE `products` SET `category_id` = 1, `series` = 'Gundam 00', `scale` = '1/100' WHERE `id` = 7;
UPDATE `products` SET `category_id` = 1, `series` = 'Char Counterattack', `scale` = '1/100' WHERE `id` = 8;
UPDATE `products` SET `category_id` = 5, `series` = 'Gundam Unicorn', `scale` = 'SD' WHERE `id` = 9;
UPDATE `products` SET `category_id` = 1, `series` = 'Mobile Suit Gundam', `scale` = '1/60' WHERE `id` = 10;

-- Thêm sản phẩm mới
INSERT INTO `products` (`id`, `name`, `slug`, `price`, `old_price`, `category_id`, `category`, `image`, `description`, `type`, `series`, `scale`, `stock`, `is_featured`, `is_sale`, `status`, `created_at`) VALUES
(36, 'Freedom Gundam (HG SEED)',           'freedom-gundam-hg-seed',          580000.00,  680000.00, 2, 'Gundam', 'models_default_img.jpeg', 'HG Freedom Gundam tu Gundam SEED - canh sai an tuong, khop cu dong linh hoat. Phu hop nguoi moi den trung cap.', 'HG', 'Mobile Suit Gundam SEED', '1/144', 60, 1, 1, 'active', '2026-06-01 08:00:00'),
(37, 'Justice Gundam (HG SEED)',           'justice-gundam-hg-seed',          560000.00,  NULL,      2, 'Gundam', 'models_default_img.jpeg', 'HG Justice Gundam - cap doi cung Freedom, thiet ke linh hoat voi Fatum-01.', 'HG', 'Mobile Suit Gundam SEED', '1/144', 45, 0, 0, 'active', '2026-06-01 08:05:00'),
(38, 'Destiny Gundam (HG SEED Destiny)',   'destiny-gundam-hg-seed-destiny',  620000.00,  NULL,      2, 'Gundam', 'models_default_img.jpeg', 'HG Destiny Gundam - hinh dang oai phong, canh sang dac trung tu Gundam SEED Destiny.', 'HG', 'Mobile Suit Gundam SEED Destiny', '1/144', 55, 1, 0, 'active', '2026-06-02 08:00:00'),
(39, 'Impulse Gundam (HG SEED Destiny)',   'impulse-gundam-hg',               540000.00,  NULL,      2, 'Gundam', 'models_default_img.jpeg', 'HG Impulse Gundam - he thong lap ghep Silhouette doc dao.', 'HG', 'Mobile Suit Gundam SEED Destiny', '1/144', 40, 0, 0, 'active', '2026-06-02 08:10:00'),
(40, 'Gundam Kimaris Trooper (HG IBO)',    'gundam-kimaris-trooper-hg',       590000.00,  NULL,      3, 'Gundam', 'models_default_img.jpeg', 'HG Kimaris Trooper - MS chien dau mat dat voi giao phan luc tu Iron-Blooded Orphans.', 'HG', 'Mobile Suit Gundam: Iron-Blooded Orphans', '1/144', 35, 0, 0, 'active', '2026-06-03 08:00:00'),
(41, 'Gundam Flauros (HG IBO)',            'gundam-flauros-hg',               570000.00,  NULL,      3, 'Gundam', 'models_default_img.jpeg', 'HG Flauros - phao thu hang nang trong Iron-Blooded Orphans, hai phao ray gun an tuong.', 'HG', 'Mobile Suit Gundam: Iron-Blooded Orphans', '1/144', 30, 0, 0, 'active', '2026-06-03 08:10:00'),
(42, 'Zaku II Commander Type (HG UC)',     'zaku-ii-commander-type-hg',       480000.00,  530000.00, 4, 'Zaku',  'Zaku II Green.jpg',        'HG Zaku II Commander Type - phien ban chi huy voi anten dac trung. Mau xanh reu co dien phe Zeon.', 'HG', 'Mobile Suit Gundam', '1/144', 80, 1, 1, 'active', '2026-06-04 08:00:00'),
(43, 'Dom (HG UC)',                        'dom-hg-uc',                       510000.00,  NULL,      4, 'Zaku',  'models_default_img.jpeg',  'HG Dom - MS hang nang luot mat dat cua phe Zeon. Thiet ke tron tria dac trung.', 'HG', 'Mobile Suit Gundam', '1/144', 45, 0, 0, 'active', '2026-06-04 08:10:00'),
(44, 'Gelgoog (HG UC)',                    'gelgoog-hg-uc',                   490000.00,  NULL,      4, 'Zaku',  'models_default_img.jpeg',  'HG Gelgoog - MS cao cap nhat cua Zeon, su dung thanh kiem beam. Char Aznable tung dung.', 'HG', 'Mobile Suit Gundam', '1/144', 50, 0, 0, 'active', '2026-06-04 08:20:00'),
(45, 'SD Gundam Freedom',                  'sd-gundam-freedom',               220000.00,  NULL,      5, 'Gundam', 'models_default_img.jpeg', 'SD Freedom Gundam de thuong - lap rap nhanh trong 30 phut, mau sac sac so.', 'SD', 'Mobile Suit Gundam SEED', 'SD', 150, 0, 0, 'active', '2026-06-05 08:00:00'),
(46, 'SD Gundam Wing Zero',                'sd-gundam-wing-zero',             230000.00,  NULL,      5, 'Gundam', 'models_default_img.jpeg', 'SD Wing Gundam Zero - thiet ke cuc ky de thuong voi canh angel thu nho.', 'SD', 'Mobile Suit Gundam Wing', 'SD', 120, 0, 0, 'active', '2026-06-05 08:10:00'),
(47, 'SD Zaku II',                         'sd-zaku-ii',                      200000.00,  NULL,      5, 'Gundam', 'models_default_img.jpeg', 'SD Zaku II - phien ban SD cua huyen thoai phe Zeon, cuc ky dang yeu.', 'SD', 'Mobile Suit Gundam', 'SD', 200, 1, 0, 'active', '2026-06-05 08:20:00'),
(48, 'SD Gundam Barbatos',                 'sd-gundam-barbatos',              210000.00,  250000.00, 5, 'Gundam', 'models_default_img.jpeg', 'SD Barbatos - phien ban mini de thuong tu Iron-Blooded Orphans.', 'SD', 'Mobile Suit Gundam: Iron-Blooded Orphans', 'SD', 90, 0, 1, 'active', '2026-06-05 08:30:00'),
(49, 'Strike Freedom Gundam (RG)',         'strike-freedom-gundam-rg',        1150000.00, 1350000.00,2, 'Gundam', 'models_default_img.jpeg', 'RG Strike Freedom - sieu pham RG 1/144 voi khung noi khung Inner Frame chi tiet, canh Dragoon an tuong.', 'RG', 'Mobile Suit Gundam SEED Destiny', '1/144', 40, 1, 1, 'active', '2026-06-06 08:00:00'),
(50, 'Destiny Gundam (RG)',                'destiny-gundam-rg',               1100000.00, NULL,      2, 'Gundam', 'models_default_img.jpeg', 'RG Destiny Gundam - chi tiet sieu cao voi cac duong gan noi khung ro net.', 'RG', 'Mobile Suit Gundam SEED Destiny', '1/144', 35, 1, 0, 'active', '2026-06-06 08:10:00'),
(51, 'Gundam Barbatos Lupus Rex (RG)',     'barbatos-lupus-rex-rg',           1050000.00, 1200000.00,3, 'Gundam', 'models_default_img.jpeg', 'RG Barbatos Lupus Rex - form cuoi cung cua Barbatos, vu khi mace cuc da.', 'RG', 'Mobile Suit Gundam: Iron-Blooded Orphans', '1/144', 28, 1, 1, 'active', '2026-06-06 08:20:00'),
(52, 'Zaku II (RG)',                       'zaku-ii-rg',                      920000.00,  NULL,      4, 'Zaku',  'models_default_img.jpeg',  'RG Zaku II - phien ban RG voi chi tiet noi khung chinh xac, mau xanh phe Zeon dac trung.', 'RG', 'Mobile Suit Gundam', '1/144', 55, 0, 0, 'active', '2026-06-07 08:00:00'),
(53, 'Freedom Gundam Ver.2.0 (MG)',        'freedom-gundam-ver2-mg',          1500000.00, 1700000.00,2, 'Gundam', 'models_default_img.jpeg', 'MG Freedom 2.0 - ban nang cap hoan toan voi khung xuong moi, canh sai chi tiet hon bao gio het.', 'MG', 'Mobile Suit Gundam SEED', '1/100', 30, 1, 1, 'active', '2026-06-08 08:00:00'),
(54, 'Destiny Gundam Spec II (MG)',        'destiny-gundam-spec2-mg',         1650000.00, NULL,      2, 'Gundam', 'models_default_img.jpeg', 'MG Destiny Spec II - phien ban cai tien 2024 voi chi tiet nang cao va decal moi.', 'MG', 'Mobile Suit Gundam SEED Destiny', '1/100', 20, 1, 0, 'active', '2026-06-08 08:10:00'),
(55, 'Barbatos Lupus Rex (MG)',            'barbatos-lupus-rex-mg',           1400000.00, 1600000.00,3, 'Gundam', 'models_default_img.jpeg', 'MG Barbatos Lupus Rex - chi tiet cuc ky cao, mace weapon khong lo, day xich kim loai.', 'MG', 'Mobile Suit Gundam: Iron-Blooded Orphans', '1/100', 25, 1, 1, 'active', '2026-06-09 08:00:00'),
(56, 'Zaku II Ver.2.0 (MG)',              'zaku-ii-ver2-mg',                  1100000.00, 1250000.00,4, 'Zaku',  'models_default_img.jpeg',  'MG Zaku II 2.0 - khung xuong noi chi tiet, cylinder arm dac trung, nhieu vu khi di kem.', 'MG', 'Mobile Suit Gundam', '1/100', 40, 0, 1, 'active', '2026-06-09 08:10:00'),
(57, 'Chars Zaku II Ver.2.0 (MG)',        'chars-zaku-ii-ver2-mg',            1150000.00, NULL,      4, 'Zaku',  'models_default_img.jpeg',  'MG Char Zaku II - phien ban mau do huyen thoai cua Char Aznable, nhanh gap 3 lan.', 'MG', 'Mobile Suit Gundam', '1/100', 35, 1, 0, 'active', '2026-06-09 08:20:00'),
(58, 'Nu Gundam Ver.Ka (MG)',             'nu-gundam-verka-mg',               2100000.00, 2400000.00,1, 'Gundam', 'models_default_img.jpeg', 'MG Nu Gundam Ver.Ka - kiet tac cua Hajime Katoki, fin funnel an tuong, decal nuoc chi tiet.', 'MG', 'Char Counterattack', '1/100', 15, 1, 1, 'active', '2026-06-10 08:00:00'),
(59, 'Unicorn Gundam Ver.Ka (MG)',        'unicorn-gundam-verka-mg',           1900000.00, NULL,      1, 'Gundam', 'models_default_img.jpeg', 'MG Unicorn Ver.Ka - chuyen doi Unicorn/Destroy mode, Psycho-frame phat sang UV.', 'MG', 'Gundam Unicorn', '1/100', 20, 1, 0, 'active', '2026-06-10 08:10:00'),
(60, 'Strike Freedom Gundam (PG)',        'strike-freedom-gundam-pg',          6800000.00, 7500000.00,2, 'Gundam', 'models_default_img.jpeg', 'PG Strike Freedom - sieu pham Perfect Grade voi LED unit tich hop, canh Dragoon day du, khung noi chi tiet nhat.', 'PG', 'Mobile Suit Gundam SEED Destiny', '1/60', 5, 1, 1, 'active', '2026-06-11 08:00:00'),
(61, 'Unicorn Gundam (PG)',               'unicorn-gundam-pg',                 7200000.00, NULL,      1, 'Gundam', 'models_default_img.jpeg', 'PG Unicorn Gundam - bo kit lon nhat voi LED Psycho-frame, chuyen doi mode, cuc ky chi tiet.', 'PG', 'Gundam Unicorn', '1/60', 4, 1, 0, 'active', '2026-06-11 08:10:00'),
(62, 'Nu Gundam (MGEX)',                  'nu-gundam-mgex',                    3500000.00, NULL,      1, 'Gundam', 'models_default_img.jpeg', 'MGEX Nu Gundam - dong cao cap nhat cua MG voi chi tiet vuot troi, LED unit va fin funnel.', 'MGEX', 'Char Counterattack', '1/100', 8, 1, 0, 'active', '2026-06-12 08:00:00');

-- ============================================================
-- 3. THÊM ORDERS
-- ============================================================

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `full_name`, `phone`, `email`, `address`, `note`, `subtotal`, `shipping_fee`, `total`, `payment_method`, `status`, `created_at`, `updated_at`) VALUES
(8,  'GD260510A1B2C3', 4,  'Nguyen Van An',  '0901234567', 'nguyenvana@gmail.com',   '45 Nguyen Hue, Q.1, TP.HCM',         'Giao gio hanh chinh', 580000.00,  30000.00,  610000.00,  'cod',           'delivered', '2026-05-12 10:00:00', '2026-05-15 14:00:00'),
(9,  'GD260520B2C3D4', 4,  'Nguyen Van An',  '0901234567', 'nguyenvana@gmail.com',   '45 Nguyen Hue, Q.1, TP.HCM',         NULL,                  1500000.00, 0.00,      1500000.00, 'bank_transfer', 'delivered', '2026-05-21 09:00:00', '2026-05-24 11:00:00'),
(10, 'GD260601C3D4E5', 4,  'Nguyen Van An',  '0901234567', 'nguyenvana@gmail.com',   '45 Nguyen Hue, Q.1, TP.HCM',         NULL,                  3500000.00, 0.00,      3500000.00, 'bank_transfer', 'shipping',  '2026-06-02 08:00:00', '2026-06-03 10:00:00'),
(11, 'GD260515D4E5F6', 5,  'Tran Thi Bich',  '0912345678', 'tranthib@gmail.com',     '12 Le Loi, Q.Hoan Kiem, Ha Noi',     'Giao nhanh',          1050000.00, 30000.00,  1080000.00, 'cod',           'delivered', '2026-05-16 14:00:00', '2026-05-19 09:00:00'),
(12, 'GD260602E5F6G7', 5,  'Tran Thi Bich',  '0912345678', 'tranthib@gmail.com',     '12 Le Loi, Q.Hoan Kiem, Ha Noi',     NULL,                  2200000.00, 0.00,      2200000.00, 'bank_transfer', 'confirmed', '2026-06-03 10:00:00', '2026-06-04 08:00:00'),
(13, 'GD260522F6G7H8', 6,  'Le Minh Chau',   '0933456789', 'leminhc@yahoo.com',      '88 Tran Phu, Q.Hai Chau, Da Nang',   'De tai buu cuc',      850000.00,  30000.00,  880000.00,  'cod',           'delivered', '2026-05-23 08:00:00', '2026-05-27 16:00:00'),
(14, 'GD260604G7H8I9', 6,  'Le Minh Chau',   '0933456789', 'leminhc@yahoo.com',      '88 Tran Phu, Q.Hai Chau, Da Nang',   NULL,                  1150000.00, 0.00,      1150000.00, 'bank_transfer', 'pending',   '2026-06-05 09:00:00', '2026-06-05 09:00:00'),
(15, 'GD260526H8I9J0', 7,  'Pham Thuy Dung', '0944567890', 'phamthuyd@gmail.com',    'TP. Thai Nguyen',                    NULL,                  250000.00,  30000.00,  280000.00,  'cod',           'delivered', '2026-05-27 10:00:00', '2026-05-30 15:00:00'),
(16, 'GD260605I9J0K1', 7,  'Pham Thuy Dung', '0944567890', 'phamthuyd@gmail.com',    'TP. Thai Nguyen',                    'Hang mong manh',      920000.00,  30000.00,  950000.00,  'cod',           'shipping',  '2026-06-06 11:00:00', '2026-06-07 08:00:00'),
(17, 'GD260603J0K1L2', 8,  'Hoang Van Em',   '0955678901', 'hoangvane@hotmail.com',  '99 Bach Dang, Q.Hong Bang, Hai Phong',NULL,                 2100000.00, 0.00,      2100000.00, 'bank_transfer', 'delivered', '2026-06-04 08:00:00', '2026-06-08 17:00:00'),
(18, 'GD260615K1L2M3', 8,  'Hoang Van Em',   '0955678901', 'hoangvane@hotmail.com',  '99 Bach Dang, Q.Hong Bang, Hai Phong',NULL,                 480000.00,  30000.00,  510000.00,  'cod',           'cancelled', '2026-06-16 09:00:00', '2026-06-16 11:00:00'),
(19, 'GD260607L2M3N4', 9,  'Vu Thi Phuong',  '0966789012', 'vuthif@gmail.com',       '23 Dien Bien Phu, Q.3, TP.HCM',     'Tang sinh nhat ban',  1900000.00, 0.00,      1900000.00, 'bank_transfer', 'delivered', '2026-06-08 10:00:00', '2026-06-12 14:00:00'),
(20, 'GD260618M3N4O5', 9,  'Vu Thi Phuong',  '0966789012', 'vuthif@gmail.com',       '23 Dien Bien Phu, Q.3, TP.HCM',     NULL,                  6800000.00, 0.00,      6800000.00, 'bank_transfer', 'confirmed', '2026-06-19 08:00:00', '2026-06-20 09:00:00'),
(21, 'GD260610N4O5P6', 10, 'Dinh Van Giang', '0977890123', 'dinhvang@gmail.com',     '34 Nguyen Trai, Q.Thanh Xuan, Ha Noi',NULL,                1150000.00, 0.00,      1150000.00, 'bank_transfer', 'delivered', '2026-06-11 08:00:00', '2026-06-14 16:00:00'),
(22, 'GD260620O5P6Q7', 10, 'Dinh Van Giang', '0977890123', 'dinhvang@gmail.com',     '34 Nguyen Trai, Q.Thanh Xuan, Ha Noi','Giao buoi chieu',   580000.00,  30000.00,  610000.00,  'cod',           'pending',   '2026-06-21 10:00:00', '2026-06-21 10:00:00'),
(23, 'GD260612P6Q7R8', 11, 'Bui Thi Hoa',    '0988901234', 'buithih@gmail.com',      '67 Ly Thuong Kiet, Q.10, TP.HCM',   NULL,                  200000.00,  30000.00,  230000.00,  'cod',           'delivered', '2026-06-13 09:00:00', '2026-06-16 15:00:00'),
(24, 'GD260622Q7R8S9', 11, 'Bui Thi Hoa',    '0988901234', 'buithih@gmail.com',      '67 Ly Thuong Kiet, Q.10, TP.HCM',   NULL,                  1400000.00, 0.00,      1400000.00, 'bank_transfer', 'shipping',  '2026-06-23 08:00:00', '2026-06-24 10:00:00');

-- ============================================================
-- 4. THÊM ORDER_ITEMS
-- ============================================================

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_image`, `price`, `quantity`, `subtotal`) VALUES
(8,  8,  36, 'Freedom Gundam (HG SEED)',       'models_default_img.jpeg', 580000.00,  1, 580000.00),
(9,  9,  53, 'Freedom Gundam Ver.2.0 (MG)',    'models_default_img.jpeg', 1500000.00, 1, 1500000.00),
(10, 10, 61, 'Unicorn Gundam (PG)',            'models_default_img.jpeg', 3500000.00, 1, 3500000.00),
(11, 11, 4,  'Unicorn Gundam (RG)',            'Unicorn Gundam.jpg',       1050000.00, 1, 1050000.00),
(12, 12, 8,  'Sazabi Ver.Ka (MG)',            'Sazabi Ver.Ka.jpg',        2200000.00, 1, 2200000.00),
(13, 13, 7,  'Gundam Exia (MG)',              'Gundam Exia.jpg',           850000.00, 1,  850000.00),
(14, 14, 28, 'Dynames (MG)',                  'models_default_img.jpeg', 1150000.00, 1, 1150000.00),
(15, 15, 9,  'SD Gundam Unicorn',             'SD Unicorn.jpg',            250000.00, 1,  250000.00),
(16, 16, 52, 'Zaku II (RG)',                  'models_default_img.jpeg',   920000.00, 1,  920000.00),
(17, 17, 58, 'Nu Gundam Ver.Ka (MG)',         'models_default_img.jpeg', 2100000.00, 1, 2100000.00),
(18, 18, 3,  'Gundam Barbatos Lupus (HG)',    'Gundam Barbatos Lupus.webp',480000.00, 1,  480000.00),
(19, 19, 59, 'Unicorn Gundam Ver.Ka (MG)',    'models_default_img.jpeg', 1900000.00, 1, 1900000.00),
(20, 20, 60, 'Strike Freedom Gundam (PG)',    'models_default_img.jpeg', 6800000.00, 1, 6800000.00),
(21, 21, 28, 'Dynames (MG)',                  'models_default_img.jpeg', 1150000.00, 1, 1150000.00),
(22, 22, 36, 'Freedom Gundam (HG SEED)',      'models_default_img.jpeg',   580000.00, 1,  580000.00),
(23, 23, 47, 'SD Zaku II',                    'models_default_img.jpeg',   200000.00, 1,  200000.00),
(24, 24, 55, 'Barbatos Lupus Rex (MG)',       'models_default_img.jpeg', 1400000.00, 1, 1400000.00);

-- ============================================================
-- 5. THÊM REVIEWS
-- ============================================================

INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 4, 5, 'Kit rat de lap, phu hop nguoi moi bat dau nhu minh. Khop cu dong tot, mau dep. Se mua them!', '2026-05-16 10:00:00'),
(1, 5, 4, 'San pham on, giao hang nhanh. Chi tiec la khong co decal nuoc di kem.', '2026-05-20 11:00:00'),
(1, 6, 5, 'Day la kit HG dau tien cua minh. Huong dan ro rang, lap xong trong 2 tieng. Rat hai long!', '2026-05-25 14:00:00'),
(2, 9, 5, 'MGEX xung dang 5 sao! Chi tiet khong cho nao che duoc. Canh DRAGOON bung ra cuc dep. Dang tung dong!', '2026-06-10 09:00:00'),
(2, 4, 5, 'Sieu pham! Minh da cho bo nay 2 thang. Lap mat 3 ngay nhung thanh qua xung dang. Trung bay cuc ky an tuong.', '2026-06-15 16:00:00'),
(3, 7, 4, 'Thiet ke hung han dung chat IBO. Lap kha nhanh. Gia sale hop ly. Se mua them Kimaris.', '2026-05-30 10:00:00'),
(3, 10, 5, 'Barbatos Lupus dep hon minh nghi nhieu. Mau sac chuan anime, khop hong cu dong linh hoat. Highly recommend!', '2026-06-12 11:00:00'),
(3, 11, 4, 'Giao hang nhanh, dong goi can than. Kit lap duoc nhung can chu y mot so goc nho. Nhin chung rat dep.', '2026-06-16 15:00:00'),
(4, 5, 5, 'RG Unicorn la mot trong nhung kit dep nhat minh tung lap. Noi khung chi tiet tuyet voi. Bo phan nho can can than.', '2026-05-19 14:00:00'),
(4, 8, 4, 'Kit rat chi tiet, nhung mot so bo phan nho can kep giu tot. Ket qua cuoi cung rat an tuong.', '2026-06-10 10:00:00'),
(5, 6, 5, 'Wing Zero EW la uoc mo cua minh tu nho. MG 1/100 nay cuc ky chi tiet va chac chan. Canh angel wings bung ra dep khong the ta!', '2026-05-27 16:00:00'),
(7, 6, 4, 'MG Exia lap kha tot, khop GN Drive noi bat. Mau xanh duong rat dep. Recommend cho fan Gundam 00.', '2026-05-29 11:00:00'),
(8, 5, 5, 'Sazabi Ver.Ka la dinh cao cua dong MG! Decal nuoc rat nhieu, doi hoi kien nhan nhung ket qua cuc dep. Xung dang gia tien.', '2026-06-06 09:00:00'),
(9, 7, 5, 'SD Unicorn cuc ky de thuong! Lap xong trong 1 tieng, dat ban lam viec nhin rat cute. Mua them cho ban gai minh.', '2026-05-31 10:00:00'),
(9, 11, 5, 'Rat dang tien cho nguoi moi bat dau. Bao bi dep, huong dan ro rang. Con gai minh 8 tuoi lap duoc luon!', '2026-06-16 16:00:00'),
(36, 4, 4, 'Freedom HG dep hon minh ky vong. Canh sai day du, lap khoang 3 tieng. Gia sale hop ly.', '2026-05-14 11:00:00'),
(36, 10, 5, 'Sieu kit cho fan SEED! Canh tia sang an tuong, mau sac dep. Recommend tuyet doi.', '2026-06-23 10:00:00'),
(47, 11, 5, 'SD Zaku II cuc ky cute! Lap 45 phut xong. Dat canh SD Unicorn trong rat de thuong.', '2026-06-17 09:00:00'),
(53, 4, 5, 'Freedom 2.0 la ban nang cap hoan hao! Khung xuong moi cung hon, canh sai chi tiet hon nhieu. Rat dang dong tien.', '2026-05-25 14:00:00'),
(58, 8, 5, 'Nu Gundam Ver.Ka la dinh cua dinh! Fin funnel bung ra hoan hao. Decal nuoc nhieu vo ke nhung ket qua rat xung dang. Mat 5 ngay lap.', '2026-06-09 15:00:00');

-- ============================================================
-- 6. THÊM NOTIFICATIONS
-- ============================================================

INSERT INTO `notifications` (`user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(4,    'Don hang da duoc xac nhan',  'Don hang #GD260510A1B2C3 cua ban da duoc xac nhan. Chung toi se giao hang trong 3-5 ngay lam viec.', 1, '2026-05-12 11:00:00'),
(4,    'Don hang da duoc giao',      'Don hang #GD260510A1B2C3 da duoc giao thanh cong. Cam on ban da mua hang!', 1, '2026-05-15 15:00:00'),
(4,    'Don hang da duoc xac nhan',  'Don hang #GD260520B2C3D4 cua ban da duoc xac nhan. Vui long chuyen khoan de chung toi xu ly.', 1, '2026-05-21 10:00:00'),
(5,    'Don hang da duoc giao',      'Don hang #GD260515D4E5F6 da duoc giao thanh cong. Cam on ban da tin tuong Gundam Store HUMG!', 1, '2026-05-19 10:00:00'),
(5,    'Don hang dang duoc xu ly',   'Don hang #GD260602E5F6G7 da duoc xac nhan. Chung toi dang chuan bi hang cho ban.', 0, '2026-06-04 09:00:00'),
(6,    'Don hang da giao thanh cong','Don hang #GD260522F6G7H8 da duoc giao. Ban co the de lai danh gia san pham tai trang chi tiet.', 1, '2026-05-27 17:00:00'),
(7,    'Don hang dang van chuyen',   'Don hang #GD260605I9J0K1 dang tren duong giao den ban. Du kien nhan hang ngay 09/06/2026.', 0, '2026-06-07 09:00:00'),
(8,    'Thanh toan thanh cong',      'Chung toi da nhan duoc thanh toan cho don #GD260603J0K1L2. Don hang dang duoc chuan bi.', 1, '2026-06-04 10:00:00'),
(8,    'Don hang da giao thanh cong','Don hang #GD260603J0K1L2 da duoc giao thanh cong. Chuc ban vui voi Nu Gundam Ver.Ka!', 1, '2026-06-08 18:00:00'),
(9,    'Don hang da giao thanh cong','Don hang #GD260607L2M3N4 da duoc giao. Cam on ban da mua hang tai Gundam Store HUMG!', 1, '2026-06-12 15:00:00'),
(9,    'Don hang cho thanh toan',    'Don hang #GD260618M3N4O5 (PG Strike Freedom) tri gia 6.800.000d dang cho xac nhan chuyen khoan.', 0, '2026-06-19 09:00:00'),
(10,   'Don hang da duoc giao',      'Don hang #GD260610N4O5P6 da giao thanh cong. Cam on ban!', 1, '2026-06-14 17:00:00'),
(11,   'Don hang da giao thanh cong','Don hang #GD260612P6Q7R8 da duoc giao. Cam on ban da mua hang!', 1, '2026-06-16 16:00:00'),
(11,   'Don hang dang van chuyen',   'Don hang #GD260622Q7R8S9 (Barbatos Lupus Rex MG) dang duoc van chuyen. Du kien nhan: 26/06/2026.', 0, '2026-06-24 11:00:00'),
(NULL, 'Khuyen mai mung Tet Thieu Nhi','Giam 15% toan bo san pham SD va HG tu ngay 01/06 den 05/06/2026. Ap dung cho don tu 300.000d!', 0, '2026-06-01 00:00:00'),
(NULL, 'Hang moi ve - PG Strike Freedom','PG Strike Freedom Gundam da co hang! So luong gioi han chi 5 bo. Dat hang ngay de khong lo!', 0, '2026-06-11 09:00:00'),
(NULL, 'Flash Sale cuoi tuan',       'Flash Sale thu 7 va Chu nhat: Giam 20% cho dong MG va RG. Chi ap dung tu 00:00 den 23:59!', 0, '2026-06-20 00:00:00'),
(NULL, 'Mien phi ship toan quoc',    'Mien phi giao hang cho TAT CA don hang tu 01/06 den 15/06/2026. Khong gioi han gia tri don!', 0, '2026-06-01 07:00:00');

-- ============================================================
-- 7. THÊM CHAT SESSIONS & MESSAGES
-- ============================================================

INSERT INTO `chat_sessions` (`id`, `user_id`, `title`, `created_at`, `updated_at`) VALUES
(4, 4,  'Tu van mo hinh cho nguoi moi', '2026-05-10 09:00:00', '2026-05-10 09:15:00'),
(5, 5,  'Tu van mo hinh trung bay',     '2026-05-16 10:00:00', '2026-05-16 10:20:00'),
(6, 6,  'Hoi ve dong MG',               '2026-05-22 08:30:00', '2026-05-22 08:45:00'),
(7, 8,  'Tu van PG va MG',              '2026-06-03 09:00:00', '2026-06-03 09:30:00'),
(8, 9,  'Tu van mo hinh lam qua',       '2026-06-07 11:00:00', '2026-06-07 11:20:00');

INSERT INTO `chat_messages` (`session_id`, `user_id`, `role`, `message`, `created_at`) VALUES
(4, 4, 'user',      'Minh moi bat dau choi Gunpla, nen bat dau tu dau?', '2026-05-10 09:00:00'),
(4, 4, 'assistant', 'Chao ban! Voi nguoi moi, minh khuyen bat dau voi dong HG (High Grade) 1/144. De lap, gia hop ly (300.000-600.000d), khong can keo hay son. Cac kit goi y: RX-78-2 HG, Freedom HG, hoac bat ky SD nao neu muon thu nhanh hon!', '2026-05-10 09:02:00'),
(4, 4, 'user',      'Can dung cu gi khong?', '2026-05-10 09:05:00'),
(4, 4, 'assistant', 'Dung cu co ban: 1) Keo cat runner (nippet). 2) Giua mong tay de lam nhan gate mark. 3) Tuy chon: but panel line mau xam de to duong vien chi tiet hon. Tat ca co ban tai shop!', '2026-05-10 09:07:00'),
(5, 5, 'user',      'Minh muon mua mo hinh de trung bay, nen chon loai nao?', '2026-05-16 10:00:00'),
(5, 5, 'assistant', 'De trung bay dep, ban nen chon MG (Master Grade) 1/100 hoac PG (Perfect Grade) 1/60. MG co gia tu 850.000-2.200.000d, chi tiet cao va kich thuoc vua phai. PG lon hon va chi tiet nhat nhung gia cao hon.', '2026-05-16 10:02:00'),
(5, 5, 'user',      'Recommend cho minh bo MG dep nhat di?', '2026-05-16 10:05:00'),
(5, 5, 'assistant', 'Top 3 MG dep nhat de trung bay: 1) Sazabi Ver.Ka (2.200.000d) - to lon, do noi bat. 2) Wing Gundam Zero EW (1.100.000d) - canh angel wings iconic. 3) Nu Gundam Ver.Ka (2.100.000d) - fin funnel an tuong. Tat ca deu dang co hang!', '2026-05-16 10:08:00'),
(7, 8, 'user',      'Khac nhau PG va MG la gi? Nen chon cai nao?', '2026-06-03 09:00:00'),
(7, 8, 'assistant', 'PG (Perfect Grade) 1/60: Lon nhat, chi tiet nhat, nhieu chi tiet kim loai, thuong co LED, gia 3.500.000-7.500.000d. MG (Master Grade) 1/100: Can bang giua chi tiet va gia tien, 850.000-2.500.000d. Neu muon kit trung bay dinh va ngan sach cao, chon PG. MG phu hop hon cho nguoi choi hang ngay.', '2026-06-03 09:05:00'),
(7, 8, 'user',      'Minh muon mua Nu Gundam MG, con hang khong?', '2026-06-03 09:10:00'),
(7, 8, 'assistant', 'Nu Gundam Ver.Ka MG con hang! Gia 2.100.000d (dang giam tu 2.400.000d). Day la kit cuc dep voi fin funnel bung ra hoan hao. Decal nuoc nhieu nhung ket qua rat xung dang. Ban dat hang ngay nhe!', '2026-06-03 09:15:00');

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================
-- HOAN TAT! Tom tat data da them:
-- - 12 users moi (10 user thuong + 2 nhan vien)
-- - 27 san pham moi (du cac dong HG, SD, RG, MG, PG, MGEX)
-- - 17 don hang moi tu nhieu khach hang
-- - 17 order_items tuong ung
-- - 20 reviews that (co nhan xet chi tiet)
-- - 18 notifications (ca nhan + toan he thong)
-- - 5 chat sessions + 14 chat messages
-- ============================================================
