<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/MenuController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$menuController = new MenuController();
$menuItems = $menuController->getAllItems();
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
                    <button class="filter-btn" data-category="<?php echo htmlspecialchars($category); ?>">
                        <?php echo htmlspecialchars($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Menu Grid -->
            <div class="menu-grid">
                <?php if ($menuItems): ?>
                    <?php foreach ($menuItems as $item): ?>
                        <div class="menu-item" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                            <div class="menu-item-image">
                                <div class="image-placeholder">
                                    <span>Image Coming Soon</span>
                                </div>
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
                                    <span class="menu-item-category"><?php echo htmlspecialchars($item['category']); ?></span>
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
        const BASE_URL = '<?php echo rtrim(BASE_URL, "/"); ?>';
        let cart = {
            items: {},
            total: 0
        };

        // Filter menu items by category
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                const category = button.dataset.category;
                
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
                
                // Show/hide menu items
                document.querySelectorAll('.menu-item').forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        function addToCart(itemId, itemName, itemPrice) {
            if (!cart.items[itemId]) {
                cart.items[itemId] = {
                    id: itemId,
                    name: itemName,
                    price: itemPrice,
                    quantity: 0
                };
            }
            cart.items[itemId].quantity++;
            updateCart();
        }

        function removeFromCart(itemId) {
            if (cart.items[itemId] && cart.items[itemId].quantity > 0) {
                cart.items[itemId].quantity--;
                if (cart.items[itemId].quantity === 0) {
                    delete cart.items[itemId];
                }
            }
            updateCart();
        }

        function updateCart() {
            const cartItems = document.getElementById('cart-items');
            const cartTotal = document.getElementById('cart-total');
            cartItems.innerHTML = '';
            
            let total = 0;
            
            Object.values(cart.items).forEach(item => {
                if (item.quantity > 0) {
                    total += item.price * item.quantity;
                    
                    const itemElement = document.createElement('div');
                    itemElement.className = 'cart-item';
                    itemElement.innerHTML = `
                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">$${item.price.toFixed(2)}</div>
                        </div>
                        <div class="cart-item-controls">
                            <button class="quantity-btn" onclick="removeFromCart(${item.id})">-</button>
                            <span class="quantity">${item.quantity}</span>
                            <button class="quantity-btn" onclick="addToCart(${item.id}, '${item.name}', ${item.price})">+</button>
                        </div>
                    `;
                    cartItems.appendChild(itemElement);
                }
            });
            
            cart.total = total;
            cartTotal.textContent = total.toFixed(2);
        }

        function placeOrder() {
            if (Object.keys(cart.items).length === 0) {
                alert('Your cart is empty!');
                return;
            }

            const orderData = {
                items: Object.values(cart.items).map(item => ({
                    id: item.id,
                    quantity: item.quantity,
                    price: item.price
                })),
                total_amount: cart.total,
                special_instructions: document.getElementById('special-instructions')?.value || '',
                order_type: 'dine_in'
            };

            fetch(`${BASE_URL}process_order.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cart.items = {};
                    cart.total = 0;
                    updateCart();
                    alert('Order placed successfully!');
                    window.location.href = `${BASE_URL}orders.php`;
                } else {
                    alert(data.message || 'An error occurred while placing the order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while placing the order. Please try again.');
            });
        }
    </script>
</body>
</html>
