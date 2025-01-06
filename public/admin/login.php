<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new AuthController();

// If user is already logged in and is admin, redirect to admin panel
if($auth->isLoggedIn() && $auth->isAdmin()) {
    header('Location: ' . BASE_URL . 'public/admin/');
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
            exit();
        } else {
            $error = 'Access denied. Admin privileges required.';
            $auth->logout(); // Logout non-admin users
        }
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Admin Login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
    <style>
        .admin-login-form {
            max-width: 400px;
            margin: 8rem auto 4rem;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .admin-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        .admin-header p {
            color: #666;
        }
        .back-to-site {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <div class="admin-login-form">
                <div class="admin-header">
                    <h1><?php echo $pageTitle; ?></h1>
                    <p>Please enter your credentials to access the admin panel</p>
                </div>

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

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Login to Admin Panel</button>
                    </div>

                    <div class="back-to-site">
                        <a href="<?php echo BASE_URL; ?>" class="text-muted">← Back to Website</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
