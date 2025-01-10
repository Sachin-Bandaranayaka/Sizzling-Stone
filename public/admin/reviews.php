<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ReviewController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$reviewController = new ReviewController();

// Get status filter from URL
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$reviews = $reviewController->getAllReviews();
$stats = $reviewController->getReviewStatistics();

$pageTitle = $status === 'reported' ? 'Reported Reviews' : 'All Reviews';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="admin-reviews">
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

            <!-- Status Filter -->
            <div class="status-filter">
                <a href="?status=all" class="btn <?php echo $status === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                    All Reviews
                </a>
                <a href="?status=reported" class="btn <?php echo $status === 'reported' ? 'btn-primary' : 'btn-secondary'; ?>">
                    Reported Reviews
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Reviews Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hasReviews = false;
                        while ($review = $reviews->fetch(PDO::FETCH_ASSOC)):
                            // Skip if filtering by reported status
                            if ($status === 'reported' && !$review['is_reported']) {
                                continue;
                            }
                            $hasReviews = true;
                        ?>
                            <tr>
                                <td><?php echo $review['review_id']; ?></td>
                                <td><?php echo htmlspecialchars($review['username'] ?? 'Anonymous'); ?></td>
                                <td>
                                    <div class="rating-display">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">
                                                <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                                            </span>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($review['review_text'] ?? ''); ?></td>
                                <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                <td>
                                    <?php if($review['is_reported']): ?>
                                        <span class="badge badge-danger">Reported</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <?php if($review['is_reported']): ?>
                                        <button class="btn btn-success btn-sm" 
                                                onclick="updateReviewStatus(<?php echo $review['review_id']; ?>, 'approve')">
                                            Approve
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (!$hasReviews): ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    No <?php echo $status === 'reported' ? 'reported ' : ''; ?>reviews found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        // Define BASE_URL for JavaScript
        const BASE_URL = '<?php echo rtrim(BASE_URL, "/"); ?>';

        function updateReviewStatus(reviewId, action) {
            if (confirm(`Are you sure you want to ${action} this review?`)) {
                fetch(`${BASE_URL}/public/reviews/process.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_status&review_id=${reviewId}&status=${action}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating review: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the review');
                });
            }
        }

        function deleteReview(reviewId) {
            if (confirm('Are you sure you want to delete this review?')) {
                fetch(`${BASE_URL}/public/reviews/process.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&review_id=${reviewId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
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
    </script>
</body>
</html>
