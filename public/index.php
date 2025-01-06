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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/home.css">
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), #2c3e50;">
        <div class="hero-content">
            <h1>Experience Fine Dining</h1>
            <p>Discover our exquisite menu and unforgettable ambiance</p>
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>menu.php" class="btn btn-primary">View Menu</a>
                <a href="<?php echo BASE_URL; ?>reservation/create.php" class="btn btn-secondary">Book a Table</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About Sizzling Stone</h2>
                    <p>Welcome to Sizzling Stone, where culinary excellence meets warm hospitality. Our restaurant offers a unique dining experience with our signature stone-grilled dishes and carefully curated menu.</p>
                    <p>Each dish is prepared with the finest ingredients and served with passion by our expert chefs. Whether you're joining us for a romantic dinner, family celebration, or business lunch, we promise an unforgettable experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact">
        <div class="container">
            <h2>Visit Us</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="info-item">
                        <h3>Address</h3>
                        <p>123 Restaurant Street<br>City, State 12345</p>
                    </div>
                    <div class="info-item">
                        <h3>Hours</h3>
                        <p>Monday - Friday: 11:00 AM - 10:00 PM<br>
                           Saturday - Sunday: 10:00 AM - 11:00 PM</p>
                    </div>
                    <div class="info-item">
                        <h3>Contact</h3>
                        <p>Phone: (123) 456-7890<br>
                           Email: info@sizzlingstone.com</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
