<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check if reviews table exists
    $query = "SHOW TABLES LIKE 'reviews'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        // Create reviews table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS reviews (
            review_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            rating INT NOT NULL,
            review_text TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )";
        
        $conn->exec($createTable);
        echo "Reviews table created successfully.\n";
        
        // Insert sample data
        $sampleData = file_get_contents(__DIR__ . '/sample_reviews.sql');
        $conn->exec($sampleData);
        echo "Sample reviews added successfully.\n";
    } else {
        // Check if table is empty
        $query = "SELECT COUNT(*) as count FROM reviews";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            // Insert sample data if table is empty
            $sampleData = file_get_contents(__DIR__ . '/sample_reviews.sql');
            $conn->exec($sampleData);
            echo "Sample reviews added successfully.\n";
        } else {
            echo "Reviews table exists and contains " . $result['count'] . " reviews.\n";
        }
    }
    
    // Show table structure
    $query = "DESCRIBE reviews";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    echo "\nTable structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
