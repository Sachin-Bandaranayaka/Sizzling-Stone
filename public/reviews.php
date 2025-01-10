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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
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

            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="write-review">
                    <h2>Write a Review</h2>
                    <form id="reviewForm" action="<?php echo BASE_URL; ?>public/reviews/process.php" method="POST">
                        <input type="hidden" name="action" value="create">
                        <div class="rating-input">
                            <label>Rating:</label>
                            <div class="star-rating">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                    <label for="star<?php echo $i; ?>">☆</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="review-input">
                            <label for="comment">Your Review:</label>
                            <textarea id="comment" name="comment" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Submit Review</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <div class="reviews-list">
                <?php 
                if (!$reviews || !($reviews instanceof PDOStatement)): ?>
                    <p class="no-reviews">Error loading reviews. Please try again later.</p>
                <?php else:
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
                            <div class="review-author">
                                <?php echo htmlspecialchars($review['customer_name'] ?? 'Anonymous'); ?>
                            </div>
                            <div class="review-text">
                                <?php echo htmlspecialchars($review['comment']); ?>
                            </div>
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']): ?>
                                <div class="review-actions">
                                    <button class="btn-edit edit-review" 
                                            data-review-id="<?php echo $review['review_id']; ?>"
                                            data-rating="<?php echo $review['rating']; ?>"
                                            data-comment="<?php echo htmlspecialchars($review['comment']); ?>">
                                        Edit
                                    </button>
                                    <button class="btn-delete delete-review" 
                                            data-review-id="<?php echo $review['review_id']; ?>">
                                        Delete
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endwhile;
                    if (!$hasReviews): ?>
                        <p class="no-reviews">No reviews yet. Be the first to write a review!</p>
                    <?php endif;
                endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        // Add BASE_URL constant
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        // Star rating functionality
        const starRating = document.querySelector('.star-rating');
        if (starRating) {
            const stars = starRating.querySelectorAll('input');
            const labels = starRating.querySelectorAll('label');

            stars.forEach((star, index) => {
                star.addEventListener('change', () => {
                    labels.forEach((label, i) => {
                        if (i <= index) {
                            label.textContent = '★';
                        } else {
                            label.textContent = '☆';
                        }
                    });
                });
            });
        }

        // Form submission
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(reviewForm);
                
                try {
                    const response = await fetch(reviewForm.action, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Your review has been submitted.',
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Failed to submit review.',
                            icon: 'error'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred. Please try again.',
                        icon: 'error'
                    });
                }
            });
        }

        function editReview(reviewId) {
            // Implement edit functionality
            console.log('Edit review:', reviewId);
        }

        function deleteReview(reviewId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
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
                            Swal.fire(
                                'Deleted!',
                                'Your review has been deleted.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message || 'Failed to delete review.',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the review.',
                            'error'
                        );
                    });
                }
            });
        }
    </script>
    <script src="<?php echo BASE_URL; ?>js/reviews.js"></script>
</body>
</html>
