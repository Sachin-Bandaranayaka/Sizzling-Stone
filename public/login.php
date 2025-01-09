<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new AuthController();

// If user is already logged in, redirect appropriately
if($auth->isLoggedIn()) {
    if($auth->isAdmin()) {
        header('Location: ' . BASE_URL . 'public/admin/');
    } else {
        header('Location: ' . BASE_URL);
    }
    exit();
}

$error = '';

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $auth->login($username, $password);
    
    if($result['success']) {
        if($auth->isAdmin()) {
            header('Location: ' . BASE_URL . 'public/admin/');
        } else {
            header('Location: ' . BASE_URL);
        }
        exit();
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/auth.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 8rem auto 4rem;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .auth-links {
            text-align: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        .auth-links a {
            color: #6c757d;
            text-decoration: none;
        }
        .auth-links a:hover {
            color: #343a40;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>
    
    <main class="auth-page">
        <div class="auth-container">
            <div class="auth-form">
                <h1><?php echo $pageTitle; ?></h1>

                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn">Login</button>

                    <div class="auth-links">
                        <p>Don't have an account? <a href="<?php echo BASE_URL; ?>public/register.php">Register here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
