-- Complete Database Setup Script
-- This single file contains all necessary SQL commands to set up the database

-- Create database
CREATE DATABASE IF NOT EXISTS sizzling_stone;
USE sizzling_stone;

-- Create menu_categories table
CREATE TABLE IF NOT EXISTS menu_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create menu_items table
CREATE TABLE IF NOT EXISTS menu_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    image_path VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    order_type ENUM('dine-in', 'take-away') DEFAULT 'dine-in',
    special_instructions TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status VARCHAR(20) DEFAULT 'unpaid',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    item_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE SET NULL
);

-- Create reservations table
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    table_number INT,
    reservation_time DATETIME,
    guests INT,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create reviews table
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(100),
    message TEXT,
    type ENUM('order', 'reservation', 'system') DEFAULT 'system',
    reference_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO menu_categories (name, description) VALUES
('Appetizers', 'Start your meal with our delicious appetizers'),
('Main Course', 'Our signature main dishes'),
('Desserts', 'Sweet treats to end your meal'),
('Beverages', 'Refreshing drinks and beverages'),
('Specials', 'Chef\'s special dishes');

-- Insert default admin user
INSERT INTO users (username, password, email, name, role, is_active)
VALUES ('admin', '$2y$10$8tPkDUY6yDtpGJ9k9KzUJOG4AYOZwAoOdtqjRzFVYOtfqfPBgPkIm', 'admin@sizzlingstone.com', 'Administrator', 'admin', 1);
-- Default password is 'admin123' - change this in production!

-- Insert sample users
INSERT INTO users (username, password, email, name, role, is_active) VALUES
('john_doe', '$2y$10$YourHashedPasswordHere', 'john@example.com', 'John Doe', 'user', 1),
('jane_smith', '$2y$10$YourHashedPasswordHere', 'jane@example.com', 'Jane Smith', 'user', 1);

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category, available, image_path, is_featured) VALUES
('Crispy Calamari', 'Tender calamari rings, lightly breaded and fried to perfection', 12.99, 'Appetizers', TRUE, 'placeholder.jpg', TRUE),
('Spinach Artichoke Dip', 'Creamy blend of spinach, artichokes, and melted cheeses', 10.99, 'Appetizers', TRUE, 'placeholder.jpg', TRUE),
('Classic Ribeye', 'Premium 12oz ribeye steak grilled on volcanic stone', 32.99, 'Main Course', TRUE, 'placeholder.jpg', TRUE),
('Filet Mignon', '8oz center-cut filet, tender and juicy', 34.99, 'Main Course', TRUE, 'placeholder.jpg', TRUE),
('Grilled Salmon', 'Fresh Atlantic salmon with lemon herb butter', 26.99, 'Main Course', TRUE, 'placeholder.jpg', TRUE),
('Sea Bass', 'Chilean sea bass with ginger soy glaze', 29.99, 'Main Course', TRUE, 'placeholder.jpg', TRUE),
('Chocolate Lava Cake', 'Warm chocolate cake with molten center', 8.99, 'Desserts', TRUE, 'placeholder.jpg', FALSE),
('New York Cheesecake', 'Classic New York style cheesecake', 7.99, 'Desserts', TRUE, 'placeholder.jpg', FALSE);

-- Insert sample reviews
INSERT INTO reviews (user_id, rating, review_text, is_approved, created_at) VALUES
((SELECT user_id FROM users WHERE username = 'john_doe'), 5, 
'Amazing experience! The food was absolutely delicious, especially the signature dishes. The service was impeccable, and the atmosphere was perfect for a special evening out.',
TRUE, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY)),

((SELECT user_id FROM users WHERE username = 'jane_smith'), 4,
'Great food and excellent service! The appetizers were fantastic, and the main course was cooked to perfection. Will definitely come back again.',
TRUE, DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),

((SELECT user_id FROM users WHERE username = 'john_doe'), 3,
'Decent experience overall. The food was good but the service was a bit slow during peak hours. The ambiance makes up for it though.',
FALSE, CURRENT_TIMESTAMP);
