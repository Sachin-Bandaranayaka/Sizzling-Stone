<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/MenuController.php';

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

$menuController = new MenuController();
$menuItems = $menuController->getAllItems();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - Admin Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .admin-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            color: #1f2937;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .menu-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .menu-item:hover {
            transform: translateY(-4px);
        }

        .menu-item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .menu-item-content {
            padding: 1.5rem;
        }

        .menu-item-title {
            font-size: 1.25rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .menu-item-price {
            font-size: 1.125rem;
            color: #e44d26;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .menu-item-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .menu-item-actions {
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            color: white;
        }

        .btn-add {
            background: #059669;
        }

        .btn-edit {
            background: #2563eb;
            flex: 1;
        }

        .btn-delete {
            background: #dc2626;
            flex: 1;
        }

        .btn-add:hover {
            background: #047857;
        }

        .btn-edit:hover {
            background: #1d4ed8;
        }

        .btn-delete:hover {
            background: #b91c1c;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .menu-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <div>
                <h1>Manage Menu</h1>
                <p>Add, edit, or remove menu items</p>
            </div>
            <a href="add_menu_item.php" class="action-btn btn-add">Add New Item</a>
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

        <?php if (!empty($menuItems)): ?>
            <div class="menu-grid">
                <?php foreach ($menuItems as $item): ?>
                    <div class="menu-item">
                        <?php if (!empty($item['image_path'])): ?>
                            <img src="<?php echo BASE_URL . 'public/images/menu/' . htmlspecialchars($item['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="menu-item-image">
                        <?php endif; ?>
                        <div class="menu-item-content">
                            <h3 class="menu-item-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="menu-item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <p class="menu-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="menu-item-actions">
                                <a href="edit_menu_item.php?id=<?php echo $item['item_id']; ?>" class="action-btn btn-edit">Edit</a>
                                <form action="process_menu.php" method="POST" style="flex: 1;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No menu items found. Click the "Add New Item" button to add your first menu item.</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
