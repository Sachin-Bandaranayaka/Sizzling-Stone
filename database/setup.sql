-- Create database
CREATE DATABASE IF NOT EXISTS sizzling_stone;
USE sizzling_stone;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu_Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    category VARCHAR(50),
    available BOOLEAN DEFAULT TRUE,
    image_path VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    order_type ENUM('dine_in', 'takeaway') DEFAULT 'dine_in',
    special_instructions TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status VARCHAR(20) DEFAULT 'unpaid',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Order_Items Table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    item_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE SET NULL
);

-- Reservations Table
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

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_reported BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Notifications Table
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

-- Insert default admin user
INSERT INTO users (username, password, email, role) 
VALUES ('admin', '$2y$10$8tPkDUY6yDtpGJ9k9KzUJOG4AYOZwAoOdtqjRzFVYOtfqfPBgPkIm', 'admin@sizzlingstone.com', 'admin');
-- Default password is 'admin123' - change this in production!

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category, available, image_path, is_featured) VALUES
('Crispy Calamari', 'Tender calamari rings, lightly breaded and fried to perfection', 12.99, 'Appetizers', TRUE, 'placeholder.jpg', TRUE),
('Spinach Artichoke Dip', 'Creamy blend of spinach, artichokes, and melted cheeses', 10.99, 'Appetizers', TRUE, 'placeholder.jpg', TRUE),
('Classic Ribeye', 'Premium 12oz ribeye steak grilled on volcanic stone', 32.99, 'Steaks', TRUE, 'placeholder.jpg', TRUE),
('Filet Mignon', '8oz center-cut filet, tender and juicy', 34.99, 'Steaks', TRUE, 'placeholder.jpg', TRUE),
('Grilled Salmon', 'Fresh Atlantic salmon with lemon herb butter', 26.99, 'Seafood', TRUE, 'placeholder.jpg', TRUE),
('Sea Bass', 'Chilean sea bass with ginger soy glaze', 29.99, 'Seafood', TRUE, 'placeholder.jpg', TRUE),
('Chocolate Lava Cake', 'Warm chocolate cake with molten center', 8.99, 'Desserts', TRUE, 'placeholder.jpg', FALSE),
('New York Cheesecake', 'Classic New York style cheesecake', 7.99, 'Desserts', TRUE, 'placeholder.jpg', FALSE);

-- Update menu items to use placeholder images
UPDATE menu_items 
SET image_path = 'placeholder.jpg';
