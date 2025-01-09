<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/middleware/admin_auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug logging
error_log("Admin Dashboard Access - User ID: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Admin Dashboard Access - Role: " . ($_SESSION['role'] ?? 'not set'));

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    error_log("Access denied: No user_id in session");
    $_SESSION['error_message'] = 'Please log in to access the admin panel.';
    header('Location: ' . BASE_URL);
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    error_log("Access denied: Not an admin. Role: " . ($_SESSION['role'] ?? 'not set'));
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: ' . BASE_URL);
    exit();
}

// If we get here, the user is an admin
error_log("Admin access granted for user: " . $_SESSION['username']);

$dashboardController = new DashboardController();
$stats = $dashboardController->getDashboardStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sizzling Stone</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #4b5563;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .number {
            color: #1f2937;
            font-size: 2rem;
            font-weight: 600;
        }

        .admin-tables {
            margin-bottom: 2rem;
        }

        .table-section {
            margin-bottom: 2rem;
        }

        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            border: 1px solid #e5e7eb;
        }

        th {
            background: #f7f7f7;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .status-pending {
            background: #f7d2c4;
            color: #e76f51;
        }

        .status-processing {
            background: #87ceeb;
            color: #4682b4;
        }

        .status-completed {
            background: #c6efce;
            color: #3e8e41;
        }

        .status-cancelled {
            background: #ffc5c5;
            color: #ff3737;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>Total Orders</h3>
                <div class="number"><?php echo number_format($stats['total_orders']); ?></div>
            </div>

            <div class="stat-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>Total Revenue</h3>
                <div class="number">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
            </div>

            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h3>Today's Orders</h3>
                <div class="number"><?php echo number_format($stats['todays_orders']); ?></div>
            </div>

            <div class="stat-card">
                <i class="fas fa-coins"></i>
                <h3>Today's Revenue</h3>
                <div class="number">₹<?php echo number_format($stats['todays_revenue'], 2); ?></div>
            </div>

            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3>Total Users</h3>
                <div class="number"><?php echo number_format($stats['total_users']); ?></div>
            </div>

            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Pending Reservations</h3>
                <div class="number"><?php echo number_format($stats['pending_reservations']); ?></div>
            </div>
        </div>

        <div class="admin-tables">
            <div class="table-section">
                <h2>Recent Orders</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_orders'] as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-section">
                <h2>Recent Users</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Joined Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_users'] as $user): ?>
                            <tr>
                                <td>#<?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-section">
                <h2>Recent Reviews</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_reviews'] as $review): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($review['username']); ?></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['rating'] ? ' text-warning' : ' text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><?php echo htmlspecialchars(substr($review['comment'], 0, 100)) . (strlen($review['comment']) > 100 ? '...' : ''); ?></td>
                                <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
