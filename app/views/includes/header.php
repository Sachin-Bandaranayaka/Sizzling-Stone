<header class="header">
    <style>
        .header {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .main-content {
            padding-top: 80px; /* Height of the fixed header */
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        .nav-links a {
            color: #666;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .nav-links a:hover {
            color: #007bff;
        }
        .nav-links a.active {
            color: #007bff;
        }
        .logout-link {
            color: #dc3545 !important;
        }
        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
            text-align: center;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>

    <div class="container">
        <nav class="navbar">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="logo">Admin Dashboard</a>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/dashboard.php') !== false ? 'active' : ''; ?>">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>admin/orders.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/orders.php') !== false ? 'active' : ''; ?>">Orders</a>
                    <a href="<?php echo BASE_URL; ?>admin/reservations.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/reservations.php') !== false ? 'active' : ''; ?>">Reservations</a>
                    <a href="<?php echo BASE_URL; ?>admin/menu.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/menu.php') !== false ? 'active' : ''; ?>">Menu</a>
                    <a href="<?php echo BASE_URL; ?>admin/users.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/users.php') !== false ? 'active' : ''; ?>">Users</a>
                    <a href="<?php echo BASE_URL; ?>admin/reviews.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/reviews.php') !== false ? 'active' : ''; ?>">Reviews</a>
                    <a href="<?php echo BASE_URL; ?>public/auth/logout.php" class="logout-link">Logout</a>
                </div>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>" class="logo">Sizzling Stone</a>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/public/index.php') !== false || $_SERVER['REQUEST_URI'] === BASE_URL ? 'active' : ''; ?>">Home</a>
                    <a href="<?php echo BASE_URL; ?>public/menu.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/menu.php') !== false ? 'active' : ''; ?>">Menu</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>public/reservation/create.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/reservation/') !== false ? 'active' : ''; ?>">Reservations</a>
                        <a href="<?php echo BASE_URL; ?>public/orders/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/orders/') !== false ? 'active' : ''; ?>">My Orders</a>
                        <a href="<?php echo BASE_URL; ?>public/reviews/create.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/reviews/') !== false ? 'active' : ''; ?>">Write Review</a>
                        <a href="<?php echo BASE_URL; ?>public/profile.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/profile.php') !== false ? 'active' : ''; ?>">Profile</a>
                        <a href="<?php echo BASE_URL; ?>public/auth/logout.php" class="logout-link">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>public/auth/login.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/auth/login.php') !== false ? 'active' : ''; ?>">Login</a>
                        <a href="<?php echo BASE_URL; ?>public/auth/register.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/auth/register.php') !== false ? 'active' : ''; ?>">Register</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="main-content">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="container">
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="container">
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        </div>
    <?php endif; ?>
    
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
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        });
    });
    </script>
