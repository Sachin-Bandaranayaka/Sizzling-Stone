<?php 
require_once __DIR__ . '/../../../config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/admin.css">
</head>
<body class="admin-panel">
    <nav class="admin-nav">
        <div class="logo">
            <h1><?php echo SITE_NAME; ?> Admin</h1>
        </div>
        <ul class="nav-links">
            <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/menu.php">Menu</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/orders.php">Orders</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/reservations.php">Reservations</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/reviews.php">Reviews</a></li>
            <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="admin-container">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="content">
            <?php if(isset($pageTitle)): ?>
                <h2 class="page-title"><?php echo $pageTitle; ?></h2>
            <?php endif; ?>
            
            <?php echo $content ?? ''; ?>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>public/js/admin.js"></script>
</body>
</html>
