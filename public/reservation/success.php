<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if no success message (means they didn't come from process.php)
if (!isset($_SESSION['success'])) {
    header('Location: ' . BASE_URL . 'public/reservation/create.php');
    exit();
}

$success_message = $_SESSION['success'];
unset($_SESSION['success']); // Clear the message

$pageTitle = 'Reservation Confirmed';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        .success-title {
            color: #2d3748;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .success-message {
            color: #4a5568;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #e44d26;
            color: white;
        }

        .btn-secondary {
            background: #718096;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="success-container">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                    </svg>
                </div>
                <h1 class="success-title">Reservation Confirmed!</h1>
                <p class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                    <br>
                    We look forward to serving you!
                </p>
                <div class="action-buttons">
                    <a href="<?php echo BASE_URL; ?>public/menu.php" class="btn btn-primary">View Menu</a>
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary">Return Home</a>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>
</body>
</html>
