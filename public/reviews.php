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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/reviews.css">
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="reviews-page">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>

            <?php if(isset($_SESSION['user_id'])): ?>
            <div class="write-review">
                <button id="writeReviewBtn" class="btn btn-primary">Write a Review</button>
            </div>

            <!-- Review Form Modal -->
            <div id="reviewModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Write a Review</h2>
                    <form id="reviewForm" method="POST" action="<?php echo BASE_URL; ?>reviews/submit.php">
                        <div class="form-group">
                            <label>Rating</label>
                            <div class="rating">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" />
                                    <label for="star<?php echo $i; ?>">☆</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Your Review</label>
                            <textarea id="comment" name="comment" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Reviews Statistics -->
            <div class="reviews-stats">
                <div class="overall-rating">
                    <div class="rating-number"><?php echo number_format($stats['average_rating'], 1); ?></div>
                    <div class="rating-stars">
                        <?php
                        $fullStars = floor($stats['average_rating']);
                        $halfStar = $stats['average_rating'] - $fullStars >= 0.5;
                        
                        for($i = 1; $i <= 5; $i++) {
                            if($i <= $fullStars) {
                                echo '<span class="star full">★</span>';
                            } elseif($i == $fullStars + 1 && $halfStar) {
                                echo '<span class="star half">★</span>';
                            } else {
                                echo '<span class="star empty">☆</span>';
                            }
                        }
                        ?>
                    </div>
                    <div class="total-reviews"><?php echo $stats['total_reviews']; ?> reviews</div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="reviews-list">
                <?php if($reviews): while($review = $reviews->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $review['rating'] ? 'full' : 'empty'; ?>">
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
                                <button class="btn btn-secondary edit-review" data-review-id="<?php echo $review['id']; ?>">
                                    Edit
                                </button>
                                <button class="btn btn-danger delete-review" data-review-id="<?php echo $review['id']; ?>">
                                    Delete
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; else: ?>
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

        // Star rating functionality
        const ratingInputs = document.querySelectorAll('.rating input');
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                const rating = this.value;
                for (let i = 1; i <= 5; i++) {
                    const label = document.querySelector(`label[for="star${i}"]`);
                    if (i <= rating) {
                        label.textContent = '★';
                    } else {
                        label.textContent = '☆';
                    }
                }
            });
        });
    </script>
</body>
</html>
