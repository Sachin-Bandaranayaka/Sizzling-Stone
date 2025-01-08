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
                                <span class="order-status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
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
                                    </div>
                                    <div class="order-items">
                                        <h4>Order Items</h4>
                                        <div class="item-list">
                                            <?php
                                            $items = $orderController->getOrderItems($order['order_id']);
                                            while ($item = $items->fetch(PDO::FETCH_ASSOC)): ?>
                                                <div class="item">
                                                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                                    <span class="item-quantity">×<?php echo $item['quantity']; ?></span>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($order['special_instructions'])): ?>
                                        <div class="special-instructions">
                                            <h4>Special Instructions</h4>
                                            <p><?php echo htmlspecialchars($order['special_instructions']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($order['status'] === 'pending'): ?>
                                    <div class="order-actions">
                                        <button onclick="cancelOrder(<?php echo $order['order_id']; ?>)" class="btn btn-danger">
                                            <svg viewBox="0 0 20 20">
                                                <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                            </svg>
                                            Cancel Order
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-orders">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 3h18a1 1 0 011 1v16a1 1 0 01-1 1H3a1 1 0 01-1-1V4a1 1 0 011-1zm17 4H4v12h16V7zm-5-4v2H9V3h6zM7 11h10v2H7v-2zm0 4h7v2H7v-2z"/>
                        </svg>
                        <p>You haven't placed any orders yet.</p>
                        <a href="<?php echo BASE_URL; ?>public/menu.php" class="btn btn-primary">
                            <svg viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            View Menu
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        const BASE_URL = '<?php echo rtrim(BASE_URL, "/"); ?>';

        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                const button = event.target.closest('.btn');
                button.classList.add('btn-loading');
                button.disabled = true;

                fetch(`${BASE_URL}/public/orders/process.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_status&order_id=${orderId}&status=cancelled`
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
                        button.classList.remove('btn-loading');
                        button.disabled = false;
                        alert('Error cancelling order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.classList.remove('btn-loading');
                    button.disabled = false;
                    alert('An error occurred while cancelling the order');
                });
            }
        }

        // Auto refresh orders every minute
        setInterval(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
