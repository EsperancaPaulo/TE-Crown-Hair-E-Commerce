-- ============================================
-- TE_CROWN HAIR E-COMMERCE DATABASE
-- ============================================

CREATE DATABASE IF NOT EXISTS te_crown_hair;

USE te_crown_hair;


-- ============================================
-- USERS
-- ============================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ============================================
-- CATEGORIES
-- ============================================

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL UNIQUE,

    description TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ============================================
-- PRODUCTS
-- ============================================

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,

    category_id INT NOT NULL,

    name VARCHAR(150) NOT NULL,

    description TEXT,

    price DECIMAL(10,2) NOT NULL,

    image VARCHAR(255),

    stock INT NOT NULL DEFAULT 0,

    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


-- ============================================
-- ORDERS
-- ============================================

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    total_amount DECIMAL(10,2) NOT NULL,

    payment_method ENUM('card', 'eft') NOT NULL,

    payment_status ENUM('pending', 'paid', 'failed')
        NOT NULL DEFAULT 'pending',

    order_status ENUM(
        'pending',
        'processing',
        'shipped',
        'completed',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',

    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


-- ============================================
-- ORDER ITEMS
-- ============================================

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL,

    product_id INT NOT NULL,

    quantity INT NOT NULL,

    price DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);


-- ============================================
-- INITIAL CATEGORIES
-- ============================================

INSERT INTO categories
(name, description)
VALUES

(
    'Wigs',
    'Human hair and synthetic wigs.'
),

(
    'Hair Extensions',
    'Premium hair extensions and bundles.'
),

(
    'Accessories',
    'Hair-care products and accessories.'
);


-- ============================================
-- INITIAL PRODUCTS
-- ============================================

INSERT INTO products
(category_id, name, description, price, image, stock, status)
VALUES

(
    1,
    'Straight Lace Wig',
    'Premium straight lace wig made from high-quality human hair.',
    2300.00,
    'straight-lace-wig.png',
    10,
    'active'
),

(
    1,
    'Body Wave Wig',
    'Premium body wave wig with a natural-looking finish.',
    2500.00,
    'body-wave-wig.png',
    8,
    'active'
),

(
    1,
    'Deep Wave Wig',
    'Beautiful deep wave wig made for a stylish and natural look.',
    2800.00,
    'deep-wave-wig.png',
    6,
    'active'
),

(
    2,
    'Premium Hair Extensions',
    'Premium-quality hair extensions for versatile styling.',
    1800.00,
    'premium-hair-extensions.png',
    15,
    'active'
),

(
    2,
    'Curly Hair Extensions',
    'Soft and natural-looking curly hair extensions.',
    1650.00,
    'curly-hair-extensions.png',
    12,
    'active'
),

(
    3,
    'Premium Hair Brush',
    'Premium brush designed for comfortable hair care.',
    250.00,
    'premium-hair-brush.png',
    20,
    'active'
),

(
    3,
    'Premium Wig Cap',
    'Comfortable wig cap for secure and easy wig installation.',
    150.00,
    'premium-wig-cap.png',
    25,
    'active'
),

(
    1,
    'HD Lace Frontal Wig',
    'Premium HD lace frontal wig with a natural hairline.',
    3200.00,
    'hd-lace-frontal-wig.png',
    5,
    'active'
);
