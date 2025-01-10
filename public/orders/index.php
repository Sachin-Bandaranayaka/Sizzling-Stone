<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/OrderController.php';
require_once __DIR__ . '/../../app/controllers/NotificationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to view your orders';
    header('Location: ' . BASE_URL . 'public/auth/login.php');
    exit();
}

$orderController = new OrderController();
$notificationController = new NotificationController();

$pageTitle = "My Orders";
$orders = $orderController->getUserOrders($_SESSION['user_id']);

// Get unread notifications count
$unreadCount = $notificationController->getUnreadCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sizzling Stone</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/orders.css">
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>
    <main class="orders-page">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>

            <!-- Notification Badge -->
            <?php if ($unreadCount > 0): ?>
            <div class="notification-badge">
                <div class="notification-count">
                    <svg viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                    </svg>
                    <span><?php echo $unreadCount; ?> New Updates</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Orders List -->
            <div class="orders-list">
                <?php if ($orders && $orders->rowCount() > 0): ?>
                    <?php while ($order = $orders->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Order #<?php echo $order['order_id']; ?></h3>
                                <div class="order-status-group">
                                    <span class="order-status status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span>
                                    <?php if ($order['payment_status']): ?>
                                        <span class="payment-status status-<?php echo strtolower($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="order-content">
                                <div class="order-details">
                                    <div class="order-info">
                                        <div class="info-group">
                                            <span class="info-label">Date</span>
                                            <span class="info-value"><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></span>
                                        </div>
                                        <div class="info-group">
                                            <span class="info-label">Order Type</span>
                                            <span class="info-value"><?php echo ucfirst($order['order_type']); ?></span>
                                        </div>
                                        <div class="info-group">
                                            <span class="info-label">Total Amount</span>
                                            <span class="info-value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                        <?php if ($order['status'] === 'confirmed' && (!$order['payment_status'] || $order['payment_status'] === 'unpaid')): ?>
                                            <div class="payment-action">
                                                <a href="<?php echo BASE_URL; ?>public/orders/pay.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary">Pay Now</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-items">
                                        <h4>Order Items</h4>
                                        <div class="item-list">
                                            <?php if (!empty($order['items'])): ?>
                                                <?php 
                                                $itemsList = explode(', ', $order['items']);
                                                foreach ($itemsList as $item): 
                                                    list($quantity, $name) = explode('x ', $item);
                                                ?>
                                                    <div class="item">
                                                        <span class="item-name"><?php echo htmlspecialchars($name); ?></span>
                                                        <span class="item-quantity">×<?php echo $quantity; ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p>No items found</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($order['special_instructions'])): ?>
                                        <div class="special-instructions">
                                            <h4>Special Instructions</h4>
                                            <p><?php echo htmlspecialchars($order['special_instructions']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="<?php echo BASE_URL; ?>public/menu" class="btn btn-primary">View Menu</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>
</body>
</html>
