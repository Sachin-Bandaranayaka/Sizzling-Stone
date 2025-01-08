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
                <div class="stat-item">
                    <span class="stat-label">Total Reviews</span>
                    <span class="stat-value"><?php echo $stats['total_reviews']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Average Rating</span>
                    <div class="stat-value">
                        <span class="rating-value"><?php echo $stats['average_rating']; ?></span>
                        <div class="stars">
                            <?php
                            $avgRating = floatval($stats['average_rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $avgRating) {
                                    echo '<span class="star filled">★</span>';
                                } elseif ($i - 0.5 <= $avgRating) {
                                    echo '<span class="star half">★</span>';
                                } else {
                                    echo '<span class="star">☆</span>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-label">5-Star Reviews</span>
                    <span class="stat-value"><?php echo $stats['five_star_percentage']; ?>%</span>
                </div>
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="write-review">
                    <button id="writeReviewBtn" class="btn btn-primary">Write a Review</button>
                </div>

                <!-- Review Form Modal -->
                <div id="reviewModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Write a Review</h2>
                        <form id="reviewForm" method="POST" action="<?php echo BASE_URL; ?>public/reviews/process.php">
                            <div class="form-group">
                                <label>Rating</label>
                                <div class="rating">
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required />
                                        <label for="star<?php echo $i; ?>">☆</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comment">Your Review</label>
                                <textarea id="comment" name="comment" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <div class="reviews-list">
                <?php 
                $hasReviews = false;
                while ($review = $reviews->fetch(PDO::FETCH_ASSOC)):
                    $hasReviews = true;
                ?>
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
                            <p><?php echo htmlspecialchars($review['comment']); ?></p>
                        </div>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']): ?>
                            <div class="review-actions">
                                <button class="btn btn-edit" onclick="editReview(<?php echo $review['review_id']; ?>)">Edit</button>
                                <button class="btn btn-delete" onclick="deleteReview(<?php echo $review['review_id']; ?>)">Delete</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>

                <?php if(!$hasReviews): ?>
                    <p class="no-reviews">No reviews yet. Be the first to write a review!</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>

    <script>
        // Modal functionality
        const modal = document.getElementById('reviewModal');
        const btn = document.getElementById('writeReviewBtn');
        const span = document.getElementsByClassName('close')[0];

        if (btn) {
            btn.onclick = function() {
                modal.style.display = 'block';
            }
        }

        if (span) {
            span.onclick = function() {
                modal.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Review form validation
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.onsubmit = function(e) {
                const rating = document.querySelector('input[name="rating"]:checked');
                const comment = document.getElementById('comment').value.trim();

                if (!rating) {
                    e.preventDefault();
                    alert('Please select a rating');
                    return false;
                }

                if (!comment) {
                    e.preventDefault();
                    alert('Please write a review comment');
                    return false;
                }

                return true;
            }
        }

        // Review actions
        function deleteReview(reviewId) {
            if (confirm('Are you sure you want to delete this review?')) {
                fetch(`${BASE_URL}public/reviews/process.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&review_id=${reviewId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting review: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the review');
                });
            }
        }

        function editReview(reviewId) {
            // Implement edit functionality
            alert('Edit functionality coming soon!');
        }
    </script>
</body>
</html>
