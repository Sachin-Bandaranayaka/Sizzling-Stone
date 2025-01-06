<header class="header">
    <div class="container">
        <nav class="navbar">
            <a href="<?php echo BASE_URL; ?>" class="logo">Sizzling Stone</a>
            <div class="nav-links">
                <a href="<?php echo BASE_URL; ?>public/menu.php">Menu</a>
                <a href="<?php echo BASE_URL; ?>public/reservation/create.php">Reservations</a>
                <a href="<?php echo BASE_URL; ?>public/reviews.php">Reviews</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>public/admin/">Admin Panel</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>public/orders.php">My Orders</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>public/logout.php" class="logout-link">Logout</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>public/login.php">Login</a>
                    <a href="<?php echo BASE_URL; ?>public/register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listener to logout link
    const logoutLink = document.querySelector('.logout-link');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = this.href;
            }
        });
    }
});
</script>
