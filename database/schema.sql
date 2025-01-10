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

-- Create menu_categories table
CREATE TABLE IF NOT EXISTS menu_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO menu_categories (name, description) VALUES
('Appetizers', 'Start your meal with our delicious appetizers'),
('Main Course', 'Our signature main dishes'),
('Desserts', 'Sweet treats to end your meal'),
('Beverages', 'Refreshing drinks and beverages'),
('Specials', 'Chef\'s special dishes');

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

-- Create default admin user if not exists
INSERT INTO users (username, password, email, name, role, is_active)
SELECT 'admin', '$2y$10$YourHashedPasswordHere', 'admin@sizzlingstone.com', 'Administrator', 'admin', 1
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE username = 'admin'
);

-- Create some sample users if they don't exist
INSERT INTO users (username, password, email, name, role, is_active)
SELECT 'john_doe', '$2y$10$YourHashedPasswordHere', 'john@example.com', 'John Doe', 'user', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'john_doe');

INSERT INTO users (username, password, email, name, role, is_active)
SELECT 'jane_smith', '$2y$10$YourHashedPasswordHere', 'jane@example.com', 'Jane Smith', 'user', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'jane_smith');

-- Drop existing reviews table if exists
DROP TABLE IF EXISTS reviews;

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

-- Insert sample reviews
INSERT INTO reviews (user_id, rating, review_text, is_approved, created_at)
SELECT 
    (SELECT user_id FROM users WHERE username = 'john_doe'),
    5,
    'Amazing experience! The food was absolutely delicious, especially the signature dishes. The service was impeccable, and the atmosphere was perfect for a special evening out.',
    TRUE,
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY)
WHERE NOT EXISTS (SELECT 1 FROM reviews WHERE review_id = 1);

INSERT INTO reviews (user_id, rating, review_text, is_approved, created_at)
SELECT 
    (SELECT user_id FROM users WHERE username = 'jane_smith'),
    4,
    'Great food and excellent service! The appetizers were fantastic, and the main course was cooked to perfection. Will definitely come back again.',
    TRUE,
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)
WHERE NOT EXISTS (SELECT 1 FROM reviews WHERE review_id = 2);

INSERT INTO reviews (user_id, rating, review_text, is_approved, created_at)
SELECT 
    (SELECT user_id FROM users WHERE username = 'john_doe'),
    3,
    'Decent experience overall. The food was good but the service was a bit slow during peak hours. The ambiance makes up for it though.',
    FALSE,
    CURRENT_TIMESTAMP
WHERE NOT EXISTS (SELECT 1 FROM reviews WHERE review_id = 3);
