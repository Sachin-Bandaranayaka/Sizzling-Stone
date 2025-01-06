<?php require_once __DIR__ . '/../../../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <h2>Login to <?php echo SITE_NAME; ?></h2>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form action="<?php echo BASE_URL; ?>login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <p class="auth-links">
                Don't have an account? <a href="<?php echo BASE_URL; ?>register.php">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
