<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ReservationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$reservationController = new ReservationController();

// Get status filter from URL
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$reservations = $reservationController->getAllReservations();

$pageTitle = $status === 'pending' ? 'Pending Reservations' : 'All Reservations';
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

    <main class="admin-reservations">
        <div class="container">
            <h1 class="page-title"><?php echo $pageTitle; ?></h1>

            <!-- Status Filter -->
            <div class="status-filter">
                <a href="?status=all" class="btn <?php echo $status === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                    All Reservations
                </a>
                <a href="?status=pending" class="btn <?php echo $status === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">
                    Pending Reservations
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Reservations Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Date & Time</th>
                            <th>Guests</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hasReservations = false;
                        while ($reservation = $reservations->fetch(PDO::FETCH_ASSOC)):
                            // Skip if filtering by status
                            if ($status !== 'all' && $reservation['status'] !== $status) {
                                continue;
                            }
                            $hasReservations = true;
                        ?>
                            <tr>
                                <td><?php echo $reservation['reservation_id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($reservation['username']); ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y h:i A', strtotime($reservation['reservation_time'])); ?>
                                </td>
                                <td><?php echo $reservation['guests']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <button class="btn btn-success btn-sm" 
                                                onclick="updateStatus(<?php echo $reservation['reservation_id']; ?>, 'confirmed')">
                                            Confirm
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="updateStatus(<?php echo $reservation['reservation_id']; ?>, 'cancelled')">
                                            Cancel
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (!$hasReservations): ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    No <?php echo $status === 'pending' ? 'pending ' : ''; ?>reservations found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../app/views/includes/footer.php'; ?>

    <script>
        function updateStatus(reservationId, status) {
            if (confirm(`Are you sure you want to ${status} this reservation?`)) {
                fetch(`${BASE_URL}public/reservation/process.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_status&reservation_id=${reservationId}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating reservation: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the reservation');
                });
            }
        }
    </script>
</body>
</html>
