<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../controllers/MenuController.php';

$menuController = new MenuController();
$items = $menuController->getAllItems();

$pageTitle = 'Menu Management';
ob_start();
?>

<div class="admin-actions">
    <a href="<?php echo BASE_URL; ?>admin/menu-add.php" class="btn-admin btn-primary">Add New Item</a>
</div>

<div class="data-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($item = $items->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo htmlspecialchars($item['category']); ?></td>
                <td>$<?php echo number_format($item['price'], 2); ?></td>
                <td>
                    <?php echo $item['available'] ? 
                        '<span class="badge badge-success">Yes</span>' : 
                        '<span class="badge badge-danger">No</span>'; ?>
                </td>
                <td>
                    <a href="<?php echo BASE_URL; ?>admin/menu-edit.php?id=<?php echo $item['item_id']; ?>" 
                       class="btn-admin btn-primary btn-sm">Edit</a>
                    <form action="<?php echo BASE_URL; ?>admin/menu-delete.php" method="POST" style="display: inline;">
                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                        <button type="submit" class="btn-admin btn-danger btn-sm" 
                                onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
