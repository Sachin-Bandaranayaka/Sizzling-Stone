<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'public/auth/login.php');
    exit;
}

$userController = new UserController();
$user = $userController->getUserById($_SESSION['user_id']);

$pageTitle = 'My Profile';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <style>
        .profile-section {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-header h1 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .profile-info {
            display: grid;
            gap: 1.5rem;
        }

        .info-group {
            display: grid;
            gap: 0.5rem;
        }

        .info-label {
            font-weight: 500;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: #1f2937;
            padding: 0.75rem;
            background: #f3f4f6;
            border-radius: 8px;
        }

        .edit-profile-btn {
            display: inline-block;
            background: #e44d26;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 2rem;
            transition: all 0.3s;
        }

        .edit-profile-btn:hover {
            background: #d13a1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(228, 77, 38, 0.2);
        }

        .activity-section {
            margin-top: 3rem;
        }

        .activity-section h2 {
            color: #1f2937;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .activity-list {
            display: grid;
            gap: 1rem;
        }

        .activity-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #e44d26;
        }

        .activity-date {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .activity-description {
            margin-top: 0.5rem;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="profile-section">
                <div class="profile-header">
                    <h1>My Profile</h1>
                </div>

                <div class="profile-info">
                    <div class="info-group">
                        <span class="info-label">Username</span>
                        <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>

                    <div class="info-group">
                        <span class="info-label">Email</span>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>

                    <div class="info-group">
                        <span class="info-label">Phone</span>
                        <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></div>
                    </div>

                    <div class="info-group">
                        <span class="info-label">Member Since</span>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>

                <div style="text-align: center;">
                    <a href="<?php echo BASE_URL; ?>public/profile/edit.php" class="edit-profile-btn">Edit Profile</a>
                </div>

                <div class="activity-section">
                    <h2>Recent Activity</h2>
                    <div class="activity-list">
                        <?php
                        // Get recent orders
                        $recentOrders = $userController->getRecentOrders($_SESSION['user_id'], 5);
                        if ($recentOrders && $recentOrders->rowCount() > 0):
                            while ($order = $recentOrders->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <div class="activity-item">
                                <div class="activity-date">
                                    <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?>
                                </div>
                                <div class="activity-description">
                                    Order #<?php echo $order['order_id']; ?> - 
                                    <?php echo ucfirst($order['status']); ?> - 
                                    $<?php echo number_format($order['total_amount'], 2); ?>
                                </div>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <div class="activity-item">
                                <div class="activity-description">No recent activity</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
