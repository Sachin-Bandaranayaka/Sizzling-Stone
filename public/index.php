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
$stmt = $menuController->getFeaturedItems();
$featuredItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/home.css">
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Experience Fine Dining</h1>
            <p>Discover our exquisite menu and unforgettable ambiance</p>
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>public/menu.php" class="btn btn-primary">
                    <svg viewBox="0 0 20 20" class="w-5 h-5">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    View Menu
                </a>
                <a href="<?php echo BASE_URL; ?>public/reservation/create.php" class="btn btn-secondary">
                    <svg viewBox="0 0 20 20" class="w-5 h-5">
                        <path d="M3 3a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V3zM3 8a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zM3 13a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z"/>
                    </svg>
                    Book a Table
                </a>
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
                            <div class="menu-item-image">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo BASE_URL . 'public/images/menu/' . $item['image_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 16H6c-.55 0-1-.45-1-1V6c0-.55.45-1 1-1h12c.55 0 1 .45 1 1v12c0 .55-.45 1-1 1zm-4.44-6.19l-2.35 3.02-1.56-1.88c-.2-.25-.58-.24-.78.01l-1.74 2.23c-.26.33-.02.81.39.81h8.98c.41 0 .65-.47.4-.8l-2.55-3.39c-.19-.26-.59-.26-.79 0z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
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
                <a href="<?php echo BASE_URL; ?>public/menu.php" class="btn btn-primary">
                    <svg viewBox="0 0 20 20" class="w-5 h-5">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    View Full Menu
                </a>
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
                    <img src="<?php echo BASE_URL; ?>public/images/restaurant.jpg" alt="Restaurant Interior">
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
                    <span class="stat-number"><?php echo number_format($reviewStats['average_rating'], 1); ?></span>
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
                <a href="<?php echo BASE_URL; ?>public/reviews.php" class="btn btn-secondary">
                    <svg viewBox="0 0 20 20" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    Read All Reviews
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
