<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ReviewController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to write a review';
    header('Location: ' . BASE_URL . 'public/auth/login.php');
    exit();
}

$pageTitle = 'Write a Review';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .review-form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #6b7280;
        }

        .rating-container {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 0.5rem;
            margin: 2rem 0;
        }

        .rating-container input {
            display: none;
        }

        .rating-container label {
            cursor: pointer;
            width: 40px;
            height: 40px;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            color: #9ca3af;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .rating-container label:hover,
        .rating-container label:hover ~ label,
        .rating-container input:checked ~ label {
            background: #e44d26;
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #4b5563;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #e44d26;
            box-shadow: 0 0 0 3px rgba(228, 77, 38, 0.1);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: #e44d26;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #d13a1c;
            transform: translateY(-2px);
        }

        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        @media (max-width: 640px) {
            .review-form-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .rating-container label {
                width: 35px;
                height: 35px;
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="review-form-container">
                <div class="form-header">
                    <h2><?php echo $pageTitle; ?></h2>
                    <p>Share your dining experience at Sizzling Stone</p>
                </div>

                <form id="reviewForm" action="<?php echo BASE_URL; ?>public/reviews/process.php" method="POST">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="rating-container">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                            <label for="star<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>

                    <div class="form-group">
                        <label for="review_text">Your Review</label>
                        <textarea id="review_text" name="review_text" class="form-control" required
                                  placeholder="Tell us about your dining experience..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        Submit Review
                    </button>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        });
    </script>
</body>
</html>
