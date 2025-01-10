<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/PaymentController.php';
require_once __DIR__ . '/../../app/controllers/OrderController.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to continue';
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$orderId = $_GET['order_id'] ?? null;
if (!$orderId) {
    $_SESSION['error'] = 'Invalid order';
    header('Location: ' . BASE_URL . 'orders/');
    exit();
}

$orderController = new OrderController();
$order = $orderController->getOrderById($orderId);

// Verify order belongs to user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'Order not found';
    header('Location: ' . BASE_URL . 'orders/');
    exit();
}

$pageTitle = "Pay for Order #" . $orderId;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/payment.css">
</head>
<body>
    <?php include_once __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="container">
        <h1><?php echo $pageTitle; ?></h1>

        <div class="payment-details">
            <h2>Order Summary</h2>
            <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
        </div>

        <div class="payment-form">
            <h2>Payment Information</h2>
            <form id="paymentForm" action="<?php echo BASE_URL; ?>public/orders/process_payment.php" method="POST">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" required 
                           maxlength="19" placeholder="1234 5678 9012 3456">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" id="expiry" name="expiry" required 
                               maxlength="5" placeholder="MM/YY">
                    </div>

                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" required 
                               maxlength="4" placeholder="123">
                    </div>
                </div>

                <div class="form-group">
                    <label for="card_name">Name on Card</label>
                    <input type="text" id="card_name" name="card_name" required>
                </div>

                <button type="submit" class="btn btn-primary">Pay Now $<?php echo number_format($order['total_amount'], 2); ?></button>
            </form>
        </div>
    </main>

    <?php include_once __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Processing...';

            // Clean up card number before submission
            const cardNumberInput = document.getElementById('card_number');
            const cleanCardNumber = cardNumberInput.value.replace(/\D/g, '');
            
            // Validate card number length
            if (cleanCardNumber.length !== 16) {
                alert('Please enter a valid 16-digit card number');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            // Validate expiry date
            const expiryInput = document.getElementById('expiry');
            const [month, year] = expiryInput.value.split('/');
            const now = new Date();
            const currentYear = now.getFullYear() % 100;
            const currentMonth = now.getMonth() + 1;

            if (!month || !year || 
                parseInt(month) < 1 || parseInt(month) > 12 || 
                parseInt(year) < currentYear || 
                (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
                alert('Please enter a valid expiry date');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            // Validate CVV
            const cvvInput = document.getElementById('cvv');
            const cvv = cvvInput.value.replace(/\D/g, '');
            if (cvv.length < 3 || cvv.length > 4) {
                alert('Please enter a valid CVV (3-4 digits)');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            // If all validations pass, submit the form
            this.submit();
        });

        // Format card number input with spaces
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 16) value = value.substr(0, 16);
            // Add space after every 4 digits
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            this.value = value;
        });

        // Format expiry date input
        document.getElementById('expiry').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 4) value = value.substr(0, 4);
            if (value.length >= 2) {
                const month = parseInt(value.substr(0, 2));
                if (month > 12) value = '12' + value.substr(2);
                value = value.substr(0, 2) + '/' + value.substr(2);
            }
            this.value = value;
        });

        // Format CVV input
        document.getElementById('cvv').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 4) value = value.substr(0, 4);
            this.value = value;
        });
    </script>
</body>
</html>
