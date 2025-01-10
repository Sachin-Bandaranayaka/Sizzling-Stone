<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Create database connection
$database = new Database();
$conn = $database->getConnection();

// Create menu_categories table
$query = "CREATE TABLE IF NOT EXISTS menu_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->exec($query);

// Insert default categories if they don't exist
$categories = [
    ['name' => 'Appetizers', 'description' => 'Start your meal with our delicious appetizers'],
    ['name' => 'Main Course', 'description' => 'Our signature main dishes'],
    ['name' => 'Desserts', 'description' => 'Sweet treats to end your meal'],
    ['name' => 'Beverages', 'description' => 'Refreshing drinks and beverages'],
    ['name' => 'Specials', 'description' => 'Chef\'s special dishes']
];

$insertQuery = "INSERT IGNORE INTO menu_categories (name, description) VALUES (:name, :description)";
$stmt = $conn->prepare($insertQuery);

foreach ($categories as $category) {
    $stmt->bindParam(':name', $category['name']);
    $stmt->bindParam(':description', $category['description']);
    $stmt->execute();
}

// Create menu_items table if it doesn't exist
$query = "CREATE TABLE IF NOT EXISTS menu_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category INT NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    image_path VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category) REFERENCES menu_categories(category_id)
)";
$conn->exec($query);

// Create images directory if it doesn't exist
$imagesDir = __DIR__ . '/public/images/menu';
if (!file_exists($imagesDir)) {
    mkdir($imagesDir, 0777, true);
}

echo "Setup completed successfully!\n";
echo "- Database tables created\n";
echo "- Default categories added\n";
echo "- Images directory created\n";
