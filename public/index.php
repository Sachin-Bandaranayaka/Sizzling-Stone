<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/MenuController.php';
require_once __DIR__ . '/../app/controllers/ReviewController.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$menuController = new MenuController();
$reviewController = new ReviewController();

// Get featured menu items
$featuredItems = $menuController->getFeaturedItems();

// Get review statistics
$reviewStats = $reviewController->getReviewStatistics();

$pageTitle = 'Welcome to Sizzling Stone';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/home.css">
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Experience Fine Dining</h1>
            <p>Discover our exquisite menu and unforgettable ambiance</p>
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>public/menu.php" class="btn btn-primary">View Menu</a>
                <a href="<?php echo BASE_URL; ?>public/reservation/create.php" class="btn btn-secondary">Book a Table</a>
            </div>
        </div>
    </section>

    <!-- Featured Menu Section -->
    <section class="featured-menu">
        <div class="container">
            <h2 class="section-title">Featured Menu Items</h2>
            <div class="menu-grid">
                <?php if ($featuredItems && !empty($featuredItems)): ?>
                    <?php foreach ($featuredItems as $item): ?>
                        <div class="menu-item">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?php echo BASE_URL . 'images/menu/' . htmlspecialchars($item['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     class="menu-item-image">
                            <?php endif; ?>
                            <div class="menu-item-content">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="menu-item-description">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>
                                <p class="menu-item-price">$<?php echo number_format($item['price'], 2); ?></p>
                                <span class="menu-item-category"><?php echo htmlspecialchars($item['category']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-items">No featured items available at the moment.</p>
                <?php endif; ?>
            </div>
            <div class="view-all-menu">
                <a href="<?php echo BASE_URL; ?>public/menu.php" class="btn btn-primary">View Full Menu</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Welcome to Sizzling Stone</h2>
                    <p>Experience the unique dining concept where your meal is served on volcanic hot stones, 
                       allowing you to cook your food exactly to your liking at your table.</p>
                    <p>Our restaurant combines traditional cooking methods with modern cuisine to create 
                       an unforgettable dining experience.</p>
                </div>
                <div class="about-image">
                    <img src="<?php echo BASE_URL; ?>images/restaurant.jpg" alt="Restaurant Interior">
                </div>
            </div>
        </div>
    </section>

    <!-- Review Statistics Section -->
    <?php if ($reviewStats && $reviewStats['total_reviews'] > 0): ?>
    <section class="review-stats">
        <div class="container">
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $reviewStats['average_rating']; ?></span>
                    <span class="stat-label">Average Rating</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $reviewStats['total_reviews']; ?></span>
                    <span class="stat-label">Total Reviews</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $reviewStats['five_star_percentage']; ?>%</span>
                    <span class="stat-label">5-Star Reviews</span>
                </div>
            </div>
            <div class="view-all-reviews">
                <a href="<?php echo BASE_URL; ?>public/reviews.php" class="btn btn-secondary">Read All Reviews</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
