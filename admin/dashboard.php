<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sizzling Stone</title>
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

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-btn {
            background: #e44d26;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #d13a1c;
            transform: translateY(-2px);
        }

        .recent-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .recent-section h2 {
            color: #1f2937;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .recent-list {
            list-style: none;
            padding: 0;
        }

        .recent-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-item .date {
            color: #6b7280;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .admin-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-card">
                <h3>Today's Orders</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="number">$0</div>
            </div>
        </div>

        <div class="admin-actions">
            <a href="<?php echo BASE_URL; ?>admin/orders.php" class="action-btn">Manage Orders</a>
            <a href="<?php echo BASE_URL; ?>admin/users.php" class="action-btn">Manage Users</a>
            <a href="<?php echo BASE_URL; ?>admin/menu.php" class="action-btn">Manage Menu</a>
            <a href="<?php echo BASE_URL; ?>admin/reservations.php" class="action-btn">Manage Reservations</a>
        </div>

        <div class="recent-section">
            <h2>Recent Orders</h2>
            <div class="recent-list">
                <div class="recent-item">
                    <p>No recent orders found.</p>
                </div>
            </div>
        </div>

        <div class="recent-section">
            <h2>Recent Users</h2>
            <div class="recent-list">
                <div class="recent-item">
                    <p>No recent users found.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
