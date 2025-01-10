<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // First, ensure we have our sample users
    $sampleUsers = [
        [
            'username' => 'john_doe',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'role' => 'user'
        ],
        [
            'username' => 'jane_smith',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'jane@example.com',
            'name' => 'Jane Smith',
            'role' => 'user'
        ]
    ];

    foreach ($sampleUsers as $user) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = :username");
        $stmt->execute(['username' => $user['username']]);
        
        if (!$stmt->fetch()) {
            $insertUser = $conn->prepare("
                INSERT INTO users (username, password, email, name, role, is_active)
                VALUES (:username, :password, :email, :name, :role, 1)
            ");
            $insertUser->execute($user);
            echo "Created user: {$user['username']}\n";
        }
    }

    // Get user IDs
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE username IN ('john_doe', 'jane_smith')");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $userIds = [];
    foreach ($users as $user) {
        $userIds[$user['username']] = $user['user_id'];
    }

    // Sample reviews
    $sampleReviews = [
        [
            'username' => 'john_doe',
            'rating' => 5,
            'review_text' => 'Amazing experience! The food was absolutely delicious, especially the signature dishes. The service was impeccable, and the atmosphere was perfect for a special evening out.',
            'is_approved' => 1,
            'days_ago' => 2
        ],
        [
            'username' => 'jane_smith',
            'rating' => 4,
            'review_text' => 'Great food and excellent service! The appetizers were fantastic, and the main course was cooked to perfection. Will definitely come back again.',
            'is_approved' => 1,
            'days_ago' => 1
        ],
        [
            'username' => 'john_doe',
            'rating' => 3,
            'review_text' => 'Decent experience overall. The food was good but the service was a bit slow during peak hours. The ambiance makes up for it though.',
            'is_approved' => 0,
            'days_ago' => 0
        ]
    ];

    // Clear existing reviews
    $conn->exec("DELETE FROM reviews");
    echo "Cleared existing reviews\n";

    // Insert sample reviews
    foreach ($sampleReviews as $review) {
        $userId = $userIds[$review['username']];
        $created_at = date('Y-m-d H:i:s', strtotime("-{$review['days_ago']} days"));
        
        $stmt = $conn->prepare("
            INSERT INTO reviews (user_id, rating, review_text, is_approved, created_at)
            VALUES (:user_id, :rating, :review_text, :is_approved, :created_at)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'rating' => $review['rating'],
            'review_text' => $review['review_text'],
            'is_approved' => $review['is_approved'],
            'created_at' => $created_at
        ]);
        
        echo "Created review by {$review['username']}\n";
    }

    echo "Sample data inserted successfully!\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
