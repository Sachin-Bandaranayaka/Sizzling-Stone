<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ReservationController.php';

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

$reservationController = new ReservationController();
$reservations = $reservationController->getAllReservations();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations - Admin Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .admin-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-header {
            margin-bottom: 2rem;
        }

        .admin-header h1 {
            color: #1f2937;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .reservations-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .reservations-table th,
        .reservations-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .reservations-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }

        .reservations-table tr:hover {
            background: #f9fafb;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #dcfce7;
            color: #166534;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
        }

        .btn-confirm {
            background: #059669;
            color: white;
        }

        .btn-cancel {
            background: #dc2626;
            color: white;
        }

        .btn-confirm:hover {
            background: #047857;
        }

        .btn-cancel:hover {
            background: #b91c1c;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        .date-filter {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .date-filter input[type="date"] {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .filter-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .filter-btn:hover {
            background: #1d4ed8;
        }

        @media (max-width: 768px) {
            .reservations-table {
                display: block;
                overflow-x: auto;
            }

            .date-filter {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../app/views/includes/header.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Manage Reservations</h1>
            <p>View and manage table reservations</p>
        </div>

        <div class="date-filter">
            <input type="date" id="start-date" name="start-date">
            <input type="date" id="end-date" name="end-date">
            <button class="filter-btn" onclick="filterReservations()">Filter</button>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($reservations)): ?>
            <table class="reservations-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Party Size</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($reservation['reservation_id']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></td>
                            <td><?php echo htmlspecialchars($reservation['party_size']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($reservation['status']); ?>">
                                    <?php echo htmlspecialchars($reservation['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($reservation['status'] === 'Pending'): ?>
                                    <form action="process_reservation.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="action-btn btn-confirm">Confirm</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($reservation['status'] !== 'Cancelled'): ?>
                                    <form action="process_reservation.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="action-btn btn-cancel">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <p>No reservations found.</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../app/views/includes/footer.php'; ?>

    <script>
        function filterReservations() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (startDate && endDate) {
                window.location.href = `?start=${startDate}&end=${endDate}`;
            }
        }
    </script>
</body>
</html>
