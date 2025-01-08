<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sizzling Stone</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        body {
            background-color: #f9fafb;
            min-height: 100vh;
        }

        .auth-container {
            max-width: 450px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-header h2 {
            color: #1f2937;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .auth-header p {
            color: #6b7280;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #4b5563;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        .form-control:focus {
            outline: none;
            border-color: #e44d26;
            box-shadow: 0 0 0 3px rgba(228, 77, 38, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: #e44d26;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background: #d13a1c;
            transform: translateY(-2px);
        }

        .auth-links {
            text-align: center;
            margin-top: 2rem;
            font-size: 1rem;
        }

        .auth-links a {
            color: #e44d26;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.95rem;
        }

        @media (max-width: 640px) {
            .auth-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to your account</p>
                </div>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="error-message">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>public/auth/process.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" class="form-control" required
                               placeholder="Enter your username or email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required
                               placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn-submit">
                        Sign In
                    </button>
                </form>

                <div class="auth-links">
                    <p>Don't have an account? <a href="<?php echo BASE_URL; ?>public/auth/register.php">Sign up</a></p>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>
</body>
</html>
