<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'public/auth/login.php');
    exit;
}

$userController = new UserController();
$user = $userController->getUserById($_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone']
    ];

    $result = $userController->updateUser($data);
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: ' . BASE_URL . 'public/profile.php');
        exit;
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

$pageTitle = 'Edit Profile';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <style>
        .edit-profile-section {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .edit-profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .edit-profile-header h1 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4b5563;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #e44d26;
            box-shadow: 0 0 0 3px rgba(228, 77, 38, 0.1);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .save-btn {
            background: #e44d26;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .save-btn:hover {
            background: #d13a1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(228, 77, 38, 0.2);
        }

        .cancel-btn {
            background: #6b7280;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .cancel-btn:hover {
            background: #4b5563;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(107, 114, 128, 0.2);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="edit-profile-section">
                <div class="edit-profile-header">
                    <h1>Edit Profile</h1>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               pattern="[0-9]{10}">
                    </div>

                    <div class="button-group">
                        <button type="submit" class="save-btn">Save Changes</button>
                        <a href="<?php echo BASE_URL; ?>public/profile.php" class="cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>
</body>
</html>
