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
