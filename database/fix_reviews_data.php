<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Update empty reviews
    $updateEmptyReviews = "UPDATE reviews 
                          SET review_text = 'Great experience at Sizzling Stone!' 
                          WHERE review_text = '' OR review_text IS NULL";
    $conn->exec($updateEmptyReviews);
    echo "Updated empty reviews\n";

    // Approve some reviews
    $approveReviews = "UPDATE reviews 
                      SET is_approved = 1 
                      WHERE review_text IS NOT NULL AND review_text != ''";
    $conn->exec($approveReviews);
    echo "Approved valid reviews\n";

    echo "Database updated successfully!\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
