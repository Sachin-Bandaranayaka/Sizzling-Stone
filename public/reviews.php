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
                    <button id="writeReviewBtn" class="btn btn-primary">
                        <i class="fas fa-pencil-alt"></i> Write a Review
                    </button>
                </div>

                <!-- Review Form Modal -->
                <div id="reviewModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2 id="modalTitle">Write a Review</h2>
                        <form id="reviewForm">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="review_id" id="editReviewId">
                            <div class="form-group">
                                <label>Rating</label>
                                <div class="rating">
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required />
                                        <label for="star<?php echo $i; ?>" class="star-label">☆</label>
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
                                <button class="btn btn-edit" onclick="editReview(<?php echo $review['review_id']; ?>, <?php echo $review['rating']; ?>, '<?php echo addslashes(htmlspecialchars($review['comment'])); ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-delete" onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
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
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        // Add BASE_URL constant
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        // Modal functionality
        const modal = document.getElementById('reviewModal');
        const modalTitle = document.getElementById('modalTitle');
        const reviewForm = document.getElementById('reviewForm');
        const editReviewId = document.getElementById('editReviewId');
        const btn = document.getElementById('writeReviewBtn');
        const span = document.getElementsByClassName('close')[0];

        if (btn) {
            btn.onclick = function() {
                resetForm();
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

        function resetForm() {
            modalTitle.textContent = 'Write a Review';
            reviewForm.action.value = 'create';
            editReviewId.value = '';
            reviewForm.reset();
        }

        // Form submission handler
        if (reviewForm) {
            reviewForm.onsubmit = function(e) {
                e.preventDefault();
                const rating = document.querySelector('input[name="rating"]:checked');
                const comment = document.getElementById('comment').value.trim();

                if (!rating) {
                    Swal.fire('Error!', 'Please select a rating', 'error');
                    return false;
                }

                if (!comment) {
                    Swal.fire('Error!', 'Please write a review comment', 'error');
                    return false;
                }

                const formData = new FormData(reviewForm);
                const data = {};
                formData.forEach((value, key) => data[key] = value);

                fetch(`${BASE_URL}public/reviews/process.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data).toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            document.getElementById('reviewModal').style.display = 'none';
                            window.location.href = `${BASE_URL}public/reviews.php`;
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An error occurred while submitting the review', 'error');
                });

                return false;
            }
        }

        // Edit review function
        function editReview(reviewId, rating, comment) {
            modalTitle.textContent = 'Edit Review';
            reviewForm.action.value = 'edit';
            editReviewId.value = reviewId;
            
            // Set rating
            document.querySelector(`input[name="rating"][value="${rating}"]`).checked = true;
            
            // Set comment
            document.getElementById('comment').value = comment;
            
            modal.style.display = 'block';
        }

        // Delete review function
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = `${BASE_URL}public/reviews.php`;
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An error occurred while deleting the review', 'error');
                });
            }
        }
    </script>
</body>
</html>
