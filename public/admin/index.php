<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$pageTitle = 'Admin Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="admin-dashboard">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>

            <div class="admin-grid">
                <!-- Menu Management -->
                <div class="admin-card">
                    <h2>Menu Management</h2>
                    <div class="card-actions">
                        <a href="menu.php" class="btn btn-primary">View All Items</a>
                        <a href="menu-create.php" class="btn btn-success">Add New Item</a>
                    </div>
                </div>

                <!-- Reservation Management -->
                <div class="admin-card">
                    <h2>Reservations</h2>
                    <div class="card-actions">
                        <a href="reservations.php" class="btn btn-primary">View All Reservations</a>
                        <a href="reservations.php?status=pending" class="btn btn-warning">Pending Reservations</a>
                    </div>
                </div>

                <!-- Review Management -->
                <div class="admin-card">
                    <h2>Reviews</h2>
                    <div class="card-actions">
                        <a href="reviews.php" class="btn btn-primary">View All Reviews</a>
                        <a href="reviews.php?status=reported" class="btn btn-danger">Reported Reviews</a>
                    </div>
                </div>

                <!-- User Management -->
                <div class="admin-card">
                    <h2>Users</h2>
                    <div class="card-actions">
                        <a href="users.php" class="btn btn-primary">View All Users</a>
                        <a href="user-create.php" class="btn btn-success">Add New User</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>
</body>
</html>
