<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/MenuController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$menuController = new MenuController();
$menuItems = $menuController->getAllItems();
$categoriesStmt = $menuController->getAllCategories();
$categories = [];
while ($row = $categoriesStmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row['category'];
}

$pageTitle = 'Our Menu';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/menu.css">
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="menu-page">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>
            
            <!-- Category Filter -->
            <div class="category-filter">
                <button class="filter-btn active" data-category="all">All</button>
                <?php foreach($categories as $category): ?>
                    <button class="filter-btn" data-category="<?php echo htmlspecialchars($category); ?>">
                        <?php echo htmlspecialchars($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Menu Items Grid -->
            <div class="menu-grid">
                <?php 
                $hasItems = false;
                while($item = $menuItems->fetch(PDO::FETCH_ASSOC)): 
                    $hasItems = true;
                ?>
                    <div class="menu-item" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                        <?php if($item['image_path']): ?>
                            <div class="item-image">
                                <img src="<?php echo BASE_URL . 'images/menu/' . $item['image_path']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="item-footer">
                                <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <button class="btn btn-primary order-btn" data-item-id="<?php echo $item['item_id']; ?>">
                                        Order Now
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php if (!$hasItems): ?>
                    <div class="no-items">
                        <p>No menu items available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>

    <script>
        // Category filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const menuItems = document.querySelectorAll('.menu-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Remove active class from all buttons
                    filterBtns.forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    btn.classList.add('active');

                    const category = btn.dataset.category;

                    menuItems.forEach(item => {
                        if (category === 'all' || item.dataset.category === category) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });

            // Order button functionality
            const orderBtns = document.querySelectorAll('.order-btn');
            orderBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const itemId = btn.dataset.itemId;
                    // Add your order functionality here
                    alert('Order functionality coming soon!');
                });
            });
        });
    </script>
</body>
</html>
