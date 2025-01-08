<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/OrderController.php';
require_once __DIR__ . '/../../app/controllers/NotificationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Unauthorized access';
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$orderController = new OrderController();
$notificationController = new NotificationController();

$pageTitle = "Order Management";
$pendingOrders = $orderController->getPendingOrders();
$allOrders = $orderController->getAllOrders();

// Get unread notifications count
$unreadCount = $notificationController->getUnreadCount(1); // Admin ID is 1
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sizzling Stone</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="admin-orders">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>

            <!-- Notification Badge -->
            <div class="notification-badge">
                <span class="count"><?php echo $unreadCount; ?></span> New Orders
            </div>

            <!-- Pending Orders Section -->
            <section class="pending-orders">
                <h2>Pending Orders</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $pendingOrders->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['username']); ?><br>
                                        <small><?php echo htmlspecialchars($order['phone']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $items = $orderController->getOrderItems($order['order_id']);
                                        while ($item = $items->fetch(PDO::FETCH_ASSOC)) {
                                            echo htmlspecialchars($item['quantity'] . 'x ' . $item['name']) . '<br>';
                                        }
                                        ?>
                                    </td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo ucfirst($order['order_type']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <button onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'confirmed')" class="btn btn-success">Confirm</button>
                                        <button onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, 'cancelled')" class="btn btn-danger">Cancel</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- All Orders Section -->
            <section class="all-orders">
                <h2>All Orders</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $allOrders->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="status-<?php echo $order['status']; ?>">
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['username']); ?><br>
                                        <small><?php echo htmlspecialchars($order['phone']); ?></small>
                                    </td>
                                    <td><?php echo ucfirst($order['status']); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo ucfirst($order['order_type']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                            <select onchange="updateOrderStatus(<?php echo $order['order_id']; ?>, this.value)">
                                                <option value="">Update Status</option>
                                                <option value="preparing">Preparing</option>
                                                <option value="ready">Ready</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        const BASE_URL = '<?php echo rtrim(BASE_URL, "/"); ?>';

        function updateOrderStatus(orderId, status) {
            if (confirm(`Are you sure you want to update this order to ${status}?`)) {
                fetch(`${BASE_URL}/public/orders/process.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_status&order_id=${orderId}&status=${status}`
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
                        alert('Error updating order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the order');
                });
            }
        }

        // Auto refresh pending orders every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
