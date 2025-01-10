<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Check reviews table
    $reviewsQuery = "SELECT r.*, u.username 
                    FROM reviews r
                    LEFT JOIN users u ON r.user_id = u.user_id
                    ORDER BY r.created_at DESC";
    $stmt = $conn->query($reviewsQuery);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($reviews) . " reviews:\n\n";
    foreach ($reviews as $review) {
        echo "Review ID: {$review['review_id']}\n";
        echo "User: {$review['username']}\n";
        echo "Rating: {$review['rating']}\n";
        echo "Text: {$review['review_text']}\n";
        echo "Approved: " . ($review['is_approved'] ? 'Yes' : 'No') . "\n";
        echo "Created: {$review['created_at']}\n";
        echo "-------------------\n";
    }

    // Check users table
    $usersQuery = "SELECT * FROM users";
    $stmt = $conn->query($usersQuery);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\nFound " . count($users) . " users:\n\n";
    foreach ($users as $user) {
        echo "User ID: {$user['user_id']}\n";
        echo "Username: {$user['username']}\n";
        echo "Role: {$user['role']}\n";
        echo "-------------------\n";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
