<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ReservationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to make a reservation';
    header('Location: ' . BASE_URL . 'public/auth/login.php');
    exit();
}

$pageTitle = 'Make a Reservation';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/reservation.css">
</head>
<body>
    <?php include __DIR__ . '/../../app/views/includes/header.php'; ?>

    <main class="reservation-page">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="reservation-form-container">
                <div class="form-header">
                    <h2>Book Your Table</h2>
                    <p>Reserve your table at Sizzling Stone for a memorable dining experience</p>
                </div>

                <form id="reservationForm" class="reservation-form" method="POST" action="<?php echo BASE_URL; ?>public/reservation/process.php">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="date">Select Date</label>
                            <input type="date" 
                                   id="date" 
                                   name="date" 
                                   required 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="time">Select Time</label>
                            <select id="time" name="time" required class="form-control">
                                <option value="">Choose a time</option>
                                <?php
                                $start = strtotime('11:00');
                                $end = strtotime('22:00');
                                for ($i = $start; $i <= $end; $i += 1800) { // 30-minute intervals
                                    echo '<option value="' . date('H:i', $i) . '">' . date('h:i A', $i) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="guests">Number of Guests</label>
                            <select id="guests" name="guests" required class="form-control">
                                <option value="">Select number of guests</option>
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i === 1 ? 'Guest' : 'Guests'; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <span class="button-text">Make Reservation</span>
                    </button>
                </form>
            </div>

            <div class="reservation-policy">
                <h3 class="policy-title">Reservation Policy</h3>
                <ul class="policy-list">
                    <li class="policy-item">
                        <svg viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        Reservations can be made up to 30 days in advance
                    </li>
                    <li class="policy-item">
                        <svg viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        Please arrive 10 minutes before your reservation time
                    </li>
                    <li class="policy-item">
                        <svg viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        Tables will be held for 15 minutes after reservation time
                    </li>
                    <li class="policy-item">
                        <svg viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        Cancellations should be made at least 2 hours in advance
                    </li>
                </ul>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            const guests = document.getElementById('guests').value;

            if (!date || !time || !guests) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }

            // Add loading state to button
            const submitBtn = this.querySelector('button[type="submit"]');
            const buttonText = submitBtn.querySelector('.button-text');
            submitBtn.disabled = true;
            buttonText.textContent = 'Processing...';
        });

        // Set minimum date to today
        document.getElementById('date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
