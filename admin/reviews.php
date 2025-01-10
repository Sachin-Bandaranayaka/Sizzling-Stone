<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ReviewController.php';
require_once __DIR__ . '/../app/middleware/admin_auth.php';

$reviewController = new ReviewController();
$reviews = $reviewController->getAllReviews();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .admin-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-header {
            margin-bottom: 2rem;
        }

        .admin-header h1 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .reviews-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .reviews-table th,
        .reviews-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .reviews-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }

        .reviews-table tr:last-child td {
            border-bottom: none;
        }

        .rating {
            color: #fbbf24;
            font-size: 1.25rem;
        }

        .review-content {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background-color 0.2s;
        }

        .btn-view {
            background: #3b82f6;
            color: white;
        }

        .btn-view:hover {
            background: #2563eb;
        }

        .btn-approve {
            background: #10b981;
            color: white;
        }

        .btn-approve:hover {
            background: #059669;
        }

        .btn-unapprove {
            background: #f59e0b;
            color: white;
        }

        .btn-unapprove:hover {
            background: #d97706;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }

        .modal-close:hover {
            color: #374151;
        }

        .review-details {
            margin-bottom: 1.5rem;
        }

        .review-details p {
            margin: 0.5rem 0;
            line-height: 1.5;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .no-reviews {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Manage Reviews</h1>
            <p>View and manage customer reviews</p>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($reviews)): ?>
            <table class="reviews-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($review['username'] ?? 'Unknown User'); ?>
                            </td>
                            <td>
                                <div class="rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <span class="star"><?php echo $i <= ($review['rating'] ?? 0) ? '★' : '☆'; ?></span>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td class="review-content">
                                <?php echo htmlspecialchars($review['review_text'] ?? 'No review text'); ?>
                            </td>
                            <td><?php echo isset($review['created_at']) ? date('M d, Y', strtotime($review['created_at'])) : 'Unknown date'; ?></td>
                            <td>
                                <?php echo isset($review['is_approved']) && $review['is_approved'] ? 'Approved' : 'Pending'; ?>
                            </td>
                            <td class="action-buttons">
                                <button class="action-btn btn-view" onclick="viewReview(<?php echo htmlspecialchars(json_encode($review)); ?>)">
                                    View
                                </button>
                                <?php if (isset($review['is_approved']) && $review['is_approved']): ?>
                                    <form action="process_review.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="unapprove">
                                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['review_id']); ?>">
                                        <button type="submit" class="action-btn btn-unapprove">
                                            Unapprove
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="process_review.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['review_id']); ?>">
                                        <button type="submit" class="action-btn btn-approve">
                                            Approve
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form action="process_review.php" method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this review?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['review_id']); ?>">
                                    <button type="submit" class="action-btn btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-reviews">No reviews found.</p>
        <?php endif; ?>
    </main>

    <!-- Review Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Review Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="reviewDetails" class="review-details">
                <!-- Review details will be populated here -->
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>

    <script>
        function viewReview(review) {
            const modal = document.getElementById('reviewModal');
            const detailsDiv = document.getElementById('reviewDetails');
            
            // Format the review details
            const rating = '★'.repeat(review.rating || 0) + '☆'.repeat(5 - (review.rating || 0));
            const date = review.created_at ? new Date(review.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }) : 'Unknown date';

            detailsDiv.innerHTML = `
                <p><strong>User:</strong> ${review.username || 'Unknown User'}</p>
                <p><strong>Rating:</strong> <span style="color: #fbbf24">${rating}</span></p>
                <p><strong>Review:</strong> ${review.review_text || 'No review text'}</p>
                <p><strong>Date:</strong> ${date}</p>
                <p><strong>Status:</strong> ${review.is_approved ? 'Approved' : 'Pending'}</p>
            `;

            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reviewModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
