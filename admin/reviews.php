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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/admin.css">
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
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .reviews-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .reviews-table th,
        .reviews-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .reviews-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }

        .reviews-table tr:hover {
            background: #f9fafb;
        }

        .rating {
            display: flex;
            gap: 0.25rem;
            color: #fbbf24;
        }

        .review-content {
            max-width: 300px;
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
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .btn-delete:hover {
            background: #b91c1c;
        }

        .btn-view {
            background: #2563eb;
            color: white;
        }

        .btn-view:hover {
            background: #1d4ed8;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        .review-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }

        .close-modal:hover {
            color: #1f2937;
        }

        @media (max-width: 768px) {
            .reviews-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                text-align: center;
            }
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
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($review['review_id']); ?></td>
                            <td><?php echo htmlspecialchars($review['customer_name']); ?></td>
                            <td>
                                <div class="rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <span class="star"><?php echo $i <= $review['rating'] ? '★' : '☆'; ?></span>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td class="review-content">
                                <?php echo htmlspecialchars($review['review_text']); ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                            <td class="action-buttons">
                                <button class="action-btn btn-view" onclick="viewReview(<?php echo htmlspecialchars(json_encode($review)); ?>)">
                                    View
                                </button>
                                <form action="process_review.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this review?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <p>No reviews found.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Review Modal -->
    <div id="reviewModal" class="review-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Review Details</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div id="reviewDetails"></div>
        </div>
    </div>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>

    <script>
        function viewReview(review) {
            const modal = document.getElementById('reviewModal');
            const detailsDiv = document.getElementById('reviewDetails');
            
            // Format the review details
            const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
            const date = new Date(review.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            detailsDiv.innerHTML = `
                <div style="margin-bottom: 1rem;">
                    <strong>Customer:</strong> ${review.customer_name}
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Rating:</strong> <span style="color: #fbbf24;">${stars}</span>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Date:</strong> ${date}
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>Review:</strong>
                    <p style="white-space: pre-wrap;">${review.review_text}</p>
                </div>
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
