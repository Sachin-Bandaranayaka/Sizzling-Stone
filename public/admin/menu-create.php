<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';
require_once __DIR__ . '/../../app/controllers/MenuController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'public/admin/login.php');
    exit();
}

$menuController = new MenuController();
$categories = $menuController->getAllCategories();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../images/menu/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $itemData = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'category' => $_POST['category'] ?? '',
        'available' => isset($_POST['available']) ? 1 : 0,
        'image_path' => ''
    ];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tempName = $_FILES['image']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($tempName, $targetPath)) {
            $itemData['image_path'] = 'images/menu/' . $fileName;
        } else {
            $error = 'Failed to upload image';
        }
    }

    if (empty($error)) {
        $result = $menuController->createItem($itemData);
        if ($result['success']) {
            $success = 'Menu item created successfully';
        } else {
            $error = $result['message'] ?? 'Failed to create menu item';
        }
    }
}

$pageTitle = 'Create Menu Item';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="admin-dashboard">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="admin-content">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <div class="form-group">
                        <label for="name">Item Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="new">+ Add New Category</option>
                        </select>
                        <input type="text" id="new-category" name="new_category" class="form-control mt-2" 
                               style="display: none;" placeholder="Enter new category name">
                    </div>

                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Recommended size: 800x600 pixels</small>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" name="available" checked>
                            <span class="checkmark"></span>
                            Available for Order
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Create Menu Item</button>
                        <a href="menu.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const newCategoryInput = document.getElementById('new-category');

        categorySelect.addEventListener('change', function() {
            if (this.value === 'new') {
                newCategoryInput.style.display = 'block';
                newCategoryInput.required = true;
            } else {
                newCategoryInput.style.display = 'none';
                newCategoryInput.required = false;
            }
        });
    });
    </script>
</body>
</html>
