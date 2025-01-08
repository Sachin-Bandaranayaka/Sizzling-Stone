<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/NotificationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to view your notifications';
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$notificationController = new NotificationController();
$pageTitle = "Notifications";

// Get user's notifications
$notifications = $notificationController->getUserNotifications($_SESSION['user_id']);

// Mark all as read
$notificationController->markAllAsRead($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sizzling Stone</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="notifications-page">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>

            <!-- Notifications List -->
            <div class="notifications-list">
                <?php if ($notifications && $notifications->rowCount() > 0): ?>
                    <?php while ($notification = $notifications->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="notification-card <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                            <div class="notification-header">
                                <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
                                <span class="notification-date">
                                    <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                </span>
                            </div>
                            <div class="notification-content">
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                            </div>
                            <?php if ($notification['type'] === 'order'): ?>
                                <div class="notification-actions">
                                    <a href="<?php echo BASE_URL; ?>orders/index.php" class="btn btn-primary">View Order</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-notifications">
                        <p>You don't have any notifications yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        // Auto refresh notifications every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
