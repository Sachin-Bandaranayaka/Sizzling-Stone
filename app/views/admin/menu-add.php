<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../controllers/MenuController.php';

$pageTitle = 'Add Menu Item';
ob_start();
?>

<div class="admin-form">
    <form action="<?php echo BASE_URL; ?>admin/menu-process.php" method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <label for="name">Item Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-row">
            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <div class="form-row">
            <label for="price">Price ($)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
        </div>

        <div class="form-row">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="Appetizers">Appetizers</option>
                <option value="Main Course">Main Course</option>
                <option value="Desserts">Desserts</option>
                <option value="Beverages">Beverages</option>
            </select>
        </div>

        <div class="form-row">
            <label for="image">Item Image</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>

        <div class="form-row">
            <label>
                <input type="checkbox" name="available" value="1" checked>
                Available
            </label>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn-admin btn-primary">Add Item</button>
            <a href="<?php echo BASE_URL; ?>admin/menu.php" class="btn-admin btn-danger">Cancel</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
