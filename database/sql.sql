-- Gundam Store - Database Schema v2
CREATE DATABASE IF NOT EXISTS gundam_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gundam_store;

-- Bảng người dùng
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    remember_token VARCHAR(64) DEFAULT NULL,
    remember_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng danh mục
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng sản phẩm
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    price DECIMAL(12,2) NOT NULL,
    old_price DECIMAL(12,2) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    category VARCHAR(50) DEFAULT 'Gundam',
    image VARCHAR(255) DEFAULT 'models_default_img.jpeg',
    description TEXT,
    type ENUM('HG', 'RG', 'MG', 'PG', 'SD', 'MGEX', 'Other') DEFAULT 'HG',
    series VARCHAR(100) DEFAULT NULL,
    scale VARCHAR(20) DEFAULT NULL,
    stock INT DEFAULT 50,
    is_featured TINYINT(1) DEFAULT 0,
    is_sale TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng giỏ hàng
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng đơn hàng
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    note TEXT DEFAULT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cod', 'bank_transfer') DEFAULT 'cod',
    status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(255) DEFAULT NULL,
    price DECIMAL(12,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- Đánh giá sản phẩm
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS chat_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_chat_sessions_user_updated (user_id, updated_at)
);

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('user', 'assistant') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_chat_messages_session_created (session_id, created_at)
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_user (user_id),
    INDEX idx_password_resets_expires (expires_at)
);

-- Dữ liệu mẫu
INSERT INTO categories (name, slug, description) VALUES
('Gundam Universal Century', 'gundam-uc', 'Các mô hình thuộc vũ trụ Universal Century'),
('Gundam SEED', 'gundam-seed', 'Các mô hình thuộc vũ trụ Cosmic Era'),
('Iron-Blooded Orphans', 'ibo', 'Các mô hình từ Mobile Suit Gundam: Iron-Blooded Orphans'),
('Zaku & Zeon', 'zaku', 'Các mô hình phe Zeon'),
('SD Gundam', 'sd', 'Dòng Super Deformed');

-- Admin: username=admin, password=admin123
INSERT INTO users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$Ou2Mle54EQzBm2TQQ/Jaoe9a5jAaXvqYOdpye8VmKmdubGVhDmdlW', 'admin@gundamstore.vn', 'Quản trị viên', 'admin');

INSERT INTO products (name, price, category, image, description, old_price, type, series, scale, stock, is_featured, is_sale) VALUES
('RX-78-2 Gundam (HG 1/144)', 350000, 'Gundam', 'RX-78-2 Gundam.avif', 'Mô hình Gundam cổ điển - kit HG phù hợp người mới.', NULL, 'HG', 'Mobile Suit Gundam', '1/144', 100, 1, 0),
('Strike Freedom Gundam (MGEX)', 1950000, 'Gundam', 'Strike Freedom Gundam.png', 'Phiên bản MGEX cao cấp với khung xương chi tiết.', NULL, 'MGEX', 'Gundam SEED Destiny', '1/100', 25, 1, 0),
('Gundam Barbatos Lupus (HG)', 480000, 'Gundam', 'Gundam Barbatos Lupus.webp', 'Từ Iron-Blooded Orphans - thiết kế hung hãn.', 520000, 'HG', 'Iron-Blooded Orphans', '1/144', 80, 1, 1),
('Unicorn Gundam (RG)', 1050000, 'Gundam', 'Unicorn Gundam.jpg', 'Chuyển đổi Psycho-Frame với chi tiết siêu cao.', 1200000, 'RG', 'Gundam Unicorn', '1/144', 40, 1, 1),
('Wing Gundam Zero EW (MG)', 1100000, 'Gundam', 'Wing Gundam Zero EW.jpg', 'Endless Waltz - cánh angel wings biểu tượng.', 1300000, 'MG', 'Gundam Wing EW', '1/100', 35, 1, 1),
('Zaku II Green (HG)', 320000, 'Zaku', 'Zaku II Green.jpg', 'Mô hình Zaku cổ điển phe Zeon.', NULL, 'HG', 'Mobile Suit Gundam', '1/144', 120, 1, 0),
('Gundam Exia (MG)', 850000, 'Gundam', 'Gundam Exia.jpg', 'Mô hình MG chi tiết cao từ Gundam 00.', NULL, 'MG', 'Gundam 00', '1/100', 45, 0, 0),
('Sazabi Ver.Ka (MG)', 2200000, 'Gundam', 'Sazabi Ver.Ka.jpg', 'Phiên bản Ver.Ka cao cấp của Char Aznable.', NULL, 'MG', 'Char''s Counterattack', '1/100', 15, 0, 0),
('SD Gundam Unicorn', 250000, 'Gundam', 'SD Unicorn.jpg', 'Mô hình SD dễ thương, lắp ráp nhanh.', NULL, 'SD', 'Gundam Unicorn', 'SD', 200, 0, 0),
('PG Unleashed RX-78-2', 6500000, 'Gundam', 'PG Unleashed.jpg', 'Phiên bản Perfect Grade cao cấp nhất.', NULL, 'PG', 'Mobile Suit Gundam', '1/60', 8, 0, 0);
