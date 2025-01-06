<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ReservationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to make a reservation';
    header('Location: ' . BASE_URL . 'login.php');
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
            
            <div class="reservation-form-container">
                <form id="reservationForm" class="reservation-form" method="POST" action="<?php echo BASE_URL; ?>reservation/process.php">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="time">Time</label>
                        <select id="time" name="time" required>
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
                        <select id="guests" name="guests" required>
                            <?php for($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i === 1 ? 'Guest' : 'Guests'; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests (Optional)</label>
                        <textarea id="special_requests" name="special_requests" rows="4"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Make Reservation</button>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        // Add any client-side validation or dynamic functionality here
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            const guests = document.getElementById('guests').value;

            if (!date || !time || !guests) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    </script>
</body>
</html>
