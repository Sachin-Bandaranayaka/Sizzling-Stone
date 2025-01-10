<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/MenuController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$menuController = new MenuController();
$menuItemsStmt = $menuController->getAllItems();
$menuItems = $menuItemsStmt->fetchAll(PDO::FETCH_ASSOC);
$categories = $menuController->getAllCategories();

$pageTitle = 'Our Menu';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/menu.css">
    <style>
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .menu-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .menu-item:hover {
            transform: translateY(-4px);
        }

        .menu-item-image {
            position: relative;
            width: 100%;
            height: 200px;
            background: #f3f4f6;
            overflow: hidden;
        }

        .menu-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .menu-item-content {
            padding: 1.5rem;
        }

        .menu-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .menu-item-name {
            font-size: 1.25rem;
            color: #1f2937;
            margin: 0;
            flex: 1;
            padding-right: 1rem;
        }

        .menu-item-price {
            color: #ef4444;
            font-weight: 600;
            font-size: 1.25rem;
            white-space: nowrap;
        }

        .menu-item-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .menu-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-item-category {
            background: #f3f4f6;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            color: #4b5563;
        }

        .add-to-cart-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .add-to-cart-btn:hover {
            background: #059669;
        }

        .add-to-cart-btn.disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .category-filter {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .filter-btn {
            background: #f3f4f6;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #10b981;
            color: white;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-badge.available {
            background: #10b981;
            color: white;
        }

        .status-badge.unavailable {
            background: #ef4444;
            color: white;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>
    <main class="menu-page">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>
            
            <!-- Category Filter -->
            <div class="category-filter">
                <button class="filter-btn active" data-category="all">All</button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn" data-category="<?php echo htmlspecialchars($category['category_id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Menu Grid -->
            <div class="menu-grid">
                <?php if (!empty($menuItems)): ?>
                    <?php foreach ($menuItems as $item): ?>
                        <div class="menu-item" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                            <div class="menu-item-image">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?php echo BASE_URL . 'public/images/menu/' . htmlspecialchars($item['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <span>Image Coming Soon</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($item['available']): ?>
                                    <span class="status-badge available">Available</span>
                                <?php else: ?>
                                    <span class="status-badge unavailable">Sold Out</span>
                                <?php endif; ?>
                            </div>
                            <div class="menu-item-content">
                                <div class="menu-item-header">
                                    <h3 class="menu-item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <span class="menu-item-price">$<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                                <p class="menu-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                <div class="menu-item-footer">
                                    <span class="menu-item-category"><?php 
                                        foreach ($categories as $cat) {
                                            if ($cat['category_id'] == $item['category']) {
                                                echo htmlspecialchars($cat['name']);
                                                break;
                                            }
                                        }
                                    ?></span>
                                    <?php if ($item['available']): ?>
                                        <button class="add-to-cart-btn" 
                                                onclick="addToCart(<?php echo $item['item_id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>', <?php echo $item['price']; ?>)">
                                            Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <button class="add-to-cart-btn disabled" disabled>Sold Out</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-items">No menu items available.</p>
                <?php endif; ?>
            </div>

            <!-- Cart Section -->
            <div class="cart-section">
                <h2>Your Cart</h2>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="login-notice">
                        <p>Please <a href="<?php echo BASE_URL; ?>login.php">log in</a> to place an order.</p>
                    </div>
                <?php else: ?>
                    <div class="cart-items" id="cart-items">
                        <!-- Cart items will be populated here -->
                    </div>
                    <div class="cart-total">
                        Total: $<span id="cart-total">0.00</span>
                    </div>
                    <textarea id="special-instructions" 
                             class="special-instructions" 
                             placeholder="Any special instructions? (optional)"></textarea>
                    <button class="place-order-btn" onclick="placeOrder()">Place Order</button>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>

    <script>
        const BASE_URL = '<?php echo rtrim(BASE_URL, "/"); ?>/';
        let cart = {
            items: {},
            total: 0
        };

        // Filter menu items by category
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Add active class to clicked button
                button.classList.add('active');

                const category = button.dataset.category;
                const menuItems = document.querySelectorAll('.menu-item');

                menuItems.forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        function addToCart(itemId, itemName, price) {
            if (cart.items[itemId]) {
                cart.items[itemId].quantity++;
                cart.items[itemId].total = cart.items[itemId].quantity * cart.items[itemId].unit_price;
            } else {
                cart.items[itemId] = {
                    name: itemName,
                    unit_price: price,
                    price: price, // Keep this for display purposes
                    quantity: 1,
                    item_id: itemId,
                    total: price
                };
            }
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartItemsDiv = document.getElementById('cart-items');
            const cartTotalSpan = document.getElementById('cart-total');
            let total = 0;

            cartItemsDiv.innerHTML = '';
            
            Object.values(cart.items).forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'cart-item';
                itemDiv.innerHTML = `
                    <span class="cart-item-name">${item.name}</span>
                    <div class="cart-item-controls">
                        <button onclick="updateQuantity(${item.item_id}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button onclick="updateQuantity(${item.item_id}, 1)">+</button>
                    </div>
                    <span class="cart-item-price">$${(item.unit_price * item.quantity).toFixed(2)}</span>
                    <button class="remove-item" onclick="removeFromCart(${item.item_id})">×</button>
                `;
                cartItemsDiv.appendChild(itemDiv);
                total += item.unit_price * item.quantity;
            });

            cartTotalSpan.textContent = total.toFixed(2);
        }

        function updateQuantity(itemId, change) {
            const item = cart.items[itemId];
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeFromCart(itemId);
                } else {
                    item.total = item.quantity * item.unit_price;
                    updateCartDisplay();
                }
            }
        }

        function removeFromCart(itemId) {
            delete cart.items[itemId];
            updateCartDisplay();
        }

        function placeOrder() {
            if (!Object.keys(cart.items).length) {
                alert('Your cart is empty!');
                return;
            }

            const orderData = new FormData();
            orderData.append('action', 'create');
            
            // Format items for the server
            const formattedItems = Object.values(cart.items).map(item => ({
                item_id: parseInt(item.item_id),
                quantity: item.quantity,
                unit_price: item.unit_price
            }));
            
            orderData.append('cart', JSON.stringify({
                items: formattedItems,
                total: Object.values(cart.items).reduce((total, item) => total + (item.unit_price * item.quantity), 0)
            }));
            orderData.append('special_instructions', document.getElementById('special-instructions').value);
            orderData.append('order_type', 'take-away'); // Fixed the enum value to match database

            fetch(BASE_URL + 'public/orders/process.php', {
                method: 'POST',
                body: orderData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cart.items = {};
                    updateCartDisplay();
                    alert('Order placed successfully!');
                    window.location.href = BASE_URL + 'orders/';
                } else {
                    alert('Error placing order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error placing order: ' + error.message);
            });
        }
    </script>
</body>
</html>
