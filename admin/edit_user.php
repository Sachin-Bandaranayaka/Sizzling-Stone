<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: ' . BASE_URL);
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = 'User ID is required.';
    header('Location: users.php');
    exit();
}

$userController = new UserController();
$user = $userController->getUserById($_GET['id']);

// If user not found, redirect back to users page
if (!$user) {
    $_SESSION['error_message'] = 'User not found.';
    header('Location: users.php');
    exit();
}

// Get available roles
$roles = ['user', 'admin'];

// Set default values for optional fields
$user['phone'] = $user['phone'] ?? '';
$user['is_active'] = $user['is_active'] ?? 1;
$user['name'] = $user['name'] ?? $user['username']; // Use username if name is not set
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .admin-container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .admin-header {
            margin-bottom: 2rem;
        }

        .admin-header h1 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .btn-container {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-submit {
            background: #10b981;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background: #059669;
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s;
            text-align: center;
        }

        .btn-cancel:hover {
            background: #4b5563;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .user-info {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .user-info p {
            margin: 0.5rem 0;
            color: #4b5563;
        }

        .user-info strong {
            color: #1f2937;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Edit User</h1>
            <p>Update user information using the form below</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="user-info">
                <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['user_id']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>

            <form action="process_user.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" class="form-control" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role); ?>"
                                    <?php echo $role === $user['role'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($role)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Account Status</label>
                    <div>
                        <label style="display: inline-block; margin-right: 1rem;">
                            <input type="radio" name="is_active" value="1" 
                                   <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                            Active
                        </label>
                        <label style="display: inline-block;">
                            <input type="radio" name="is_active" value="0" 
                                   <?php echo !$user['is_active'] ? 'checked' : ''; ?>>
                            Inactive
                        </label>
                    </div>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-submit">Update User</button>
                    <a href="users.php" class="btn-cancel">Cancel</a>
                    <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                        <button type="button" class="btn-delete" 
                                onclick="if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                                    document.getElementById('delete-form').submit();
                                }">
                            Delete User
                        </button>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Hidden form for delete action -->
            <form id="delete-form" action="process_user.php" method="POST" style="display: none;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>
</body>
</html>
