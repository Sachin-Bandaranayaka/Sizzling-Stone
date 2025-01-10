<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/ReviewController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$reviewController = new ReviewController();
$reviews = $reviewController->getAllReviews();
$stats = $reviewController->getReviewStatistics();

$pageTitle = 'Customer Reviews';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/reviews.css">
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>
    <main class="reviews-page">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>

            <!-- Review Statistics -->
            <div class="review-stats">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?php echo $stats['total_reviews']; ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-value"><?php echo number_format($stats['average_rating'], 1); ?></div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-value"><?php echo $stats['five_star_percentage']; ?>%</div>
                    <div class="stat-label">5-Star Reviews</div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="reviews-list">
                <?php if (empty($reviews)): ?>
                    <p class="no-reviews">No reviews yet.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">
                                            <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                                        </span>
                                    <?php endfor; ?>
                                </div>
                                <div class="review-date">
                                    <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <div class="review-author">
                                    <?php echo htmlspecialchars($review['username'] ?? 'Anonymous'); ?>
                                </div>
                                <div class="review-text">
                                    <?php echo htmlspecialchars($review['review_text']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
