<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../controllers/MenuController.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
require_once __DIR__ . '/../../controllers/ReservationController.php';

$menuController = new MenuController();
$orderController = new OrderController();
$reservationController = new ReservationController();

$pageTitle = 'Dashboard';
ob_start();
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Menu Items</h3>
        <div class="stat-value"><?php echo $menuController->getTotalItems(); ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Today's Orders</h3>
        <div class="stat-value"><?php echo $orderController->getTodayOrderCount(); ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Today's Revenue</h3>
        <div class="stat-value">$<?php echo number_format($orderController->getTodayRevenue(), 2); ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Pending Reservations</h3>
        <div class="stat-value"><?php echo $reservationController->getPendingCount(); ?></div>
    </div>
</div>

<div class="dashboard-sections">
    <div class="section">
        <h3>Recent Orders</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orderController->getRecentOrders(5) as $order): ?>
                <tr>
                    <td>#<?php echo $order['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td><?php echo date('M d, H:i', strtotime($order['order_date'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <h3>Today's Reservations</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Table</th>
                    <th>Guests</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reservationController->getTodayReservations() as $reservation): ?>
                <tr>
                    <td><?php echo date('H:i', strtotime($reservation['reservation_time'])); ?></td>
                    <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                    <td><?php echo $reservation['table_number']; ?></td>
                    <td><?php echo $reservation['guests']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
