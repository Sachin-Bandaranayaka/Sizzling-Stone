<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: ' . BASE_URL);
    exit();
}

$orderController = new OrderController();
$orders = $orderController->getAllOrders();

// Get filter parameters
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Filter orders if needed
if (!empty($status) || !empty($dateFrom) || !empty($dateTo)) {
    $filteredOrders = [];
    foreach ($orders as $order) {
        $includeOrder = true;
        
        // Filter by status
        if (!empty($status) && $order['status'] !== $status) {
            $includeOrder = false;
        }
        
        // Filter by date range
        if (!empty($dateFrom) && strtotime($order['order_date']) < strtotime($dateFrom)) {
            $includeOrder = false;
        }
        if (!empty($dateTo) && strtotime($order['order_date']) > strtotime($dateTo . ' 23:59:59')) {
            $includeOrder = false;
        }
        
        if ($includeOrder) {
            $filteredOrders[] = $order;
        }
    }
    $orders = $filteredOrders;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
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
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .filter-group label {
            font-weight: 500;
            color: #4b5563;
        }

        .filter-group select,
        .filter-group input[type="date"] {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: white;
        }

        .btn-filter {
            padding: 0.5rem 1rem;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-filter:hover {
            background-color: #1d4ed8;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .orders-table th,
        .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .orders-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }

        .orders-table tr:hover {
            background: #f9fafb;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-completed {
            background: #e0e7ff;
            color: #3730a3;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            color: white;
            margin-right: 0.5rem;
        }

        .btn-confirm {
            background: #059669;
        }

        .btn-cancel {
            background: #dc2626;
        }

        .btn-complete {
            background: #4f46e5;
        }

        .btn-confirm:hover {
            background: #047857;
        }

        .btn-cancel:hover {
            background: #b91c1c;
        }

        .btn-complete:hover {
            background: #4338ca;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }

            .orders-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Manage Orders</h1>
            <p>View and manage customer orders</p>
        </div>

        <div class="filters">
            <form action="" method="GET" class="filter-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">All</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>

                <label for="date_from">From:</label>
                <input type="date" name="date_from" id="date_from" value="<?php echo $dateFrom; ?>">

                <label for="date_to">To:</label>
                <input type="date" name="date_to" id="date_to" value="<?php echo $dateTo; ?>">

                <button type="submit" class="btn-filter">Filter</button>
            </form>
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

        <?php if (!empty($orders)): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['items'] ?? 'N/A'); ?></td>
                            <td>$<?php echo number_format($order['total'] ?? 0, 2); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($order['status'] === 'pending'): ?>
                                    <form action="process_order.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="action-btn btn-confirm">Confirm</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'confirmed'): ?>
                                    <form action="process_order.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit" class="action-btn btn-complete">Complete</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'completed'): ?>
                                    <form action="process_order.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="action-btn btn-cancel" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <p>No orders found.</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
