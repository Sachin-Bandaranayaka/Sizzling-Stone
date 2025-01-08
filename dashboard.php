<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/controllers/MenuController.php';
require_once __DIR__ . '/app/controllers/ReservationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$menuController = new MenuController();
$reservationController = new ReservationController();

// Get featured menu items
$featuredItems = $menuController->getFeaturedItems();

// Get upcoming reservations if user is logged in
$upcomingReservations = null;
if (isset($_SESSION['user_id'])) {
    $upcomingReservations = $reservationController->getUpcomingReservations($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Sizzling Stone - The Ultimate Dining Experience</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        /* Hero section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('assets/images/hero-bg.jpg') center/cover no-repeat;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 2rem;
        }

        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-button {
            display: inline-block;
            padding: 1rem 2rem;
            background-color: #e31837;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .cta-button:hover {
            background-color: #c41230;
        }

        /* Features section */
        .features {
            padding: 4rem 0;
            background: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #e31837;
            margin-bottom: 1rem;
        }

        /* Featured Menu section */
        .featured-menu {
            padding: 4rem 0;
            background: #f8f9fa;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .menu-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .menu-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .menu-content {
            padding: 1.5rem;
        }

        .menu-price {
            color: #e31837;
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Reservations section */
        .reservations {
            padding: 4rem 0;
            background: white;
        }

        .reservation-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        /* Section headers */
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .section-header p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .features-grid,
            .menu-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/app/views/includes/header.php'; ?>

    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Sizzling Stone</h1>
            <p>Experience the art of stone-grilled cuisine in a warm, welcoming atmosphere</p>
            <a href="<?php echo BASE_URL; ?>menu.php" class="cta-button">View Our Menu</a>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Us</h2>
                <p>Discover what makes Sizzling Stone the perfect choice for your dining experience</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔥</div>
                    <h3>Stone-Grilled Excellence</h3>
                    <p>Experience the unique flavor of dishes cooked on volcanic hot stones</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌟</div>
                    <h3>Premium Quality</h3>
                    <p>We use only the finest ingredients sourced from local suppliers</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👨‍🍳</div>
                    <h3>Expert Chefs</h3>
                    <p>Our skilled chefs bring years of culinary expertise to your table</p>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-menu">
        <div class="container">
            <div class="section-header">
                <h2>Featured Dishes</h2>
                <p>Explore our chef's specially curated selection of signature dishes</p>
            </div>
            <div class="menu-grid">
                <?php if ($featuredItems && $featuredItems->rowCount() > 0): ?>
                    <?php while ($item = $featuredItems->fetch(PDO::FETCH_ASSOC)): ?>
                        <div class="menu-card">
                            <img src="<?php echo BASE_URL . 'assets/images/menu/' . $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-image">
                            <div class="menu-content">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <p class="menu-price">$<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No featured items available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (isset($_SESSION['user_id']) && $upcomingReservations): ?>
    <section class="reservations">
        <div class="container">
            <div class="section-header">
                <h2>Your Upcoming Reservations</h2>
                <p>Keep track of your dining plans with us</p>
            </div>
            <?php while ($reservation = $upcomingReservations->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="reservation-card">
                    <h3>Reservation for <?php echo htmlspecialchars($reservation['guest_name']); ?></h3>
                    <p>Date: <?php echo date('F j, Y', strtotime($reservation['reservation_date'])); ?></p>
                    <p>Time: <?php echo date('g:i A', strtotime($reservation['reservation_time'])); ?></p>
                    <p>Party Size: <?php echo $reservation['party_size']; ?> people</p>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php include __DIR__ . '/app/views/includes/footer.php'; ?>

    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Smooth scroll for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>
