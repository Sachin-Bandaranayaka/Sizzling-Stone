<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/MenuController.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$menuController = new MenuController();
$menuItems = $menuController->getAllItems();

$pageTitle = 'Menu Management';

// Handle item deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $itemId = $_POST['item_id'];
    $result = $menuController->deleteItem($itemId);
    if ($result['success']) {
        $_SESSION['success_message'] = 'Menu item deleted successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to delete menu item';
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
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

            <div class="admin-actions">
                <a href="menu-create.php" class="btn btn-success">Add New Item</a>
            </div>

            <div class="admin-content">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $menuItems->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $item['available'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $item['available'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="menu-edit.php?id=<?php echo $item['item_id']; ?>" 
                                           class="btn btn-sm btn-primary">Edit</a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this item?');">
                                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                            <button type="submit" name="delete_item" 
                                                    class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>
</body>
</html>
