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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reservationId = $_POST['reservation_id'] ?? null;
    
    if (!$reservationId) {
        $_SESSION['error_message'] = 'Reservation ID is required';
        header('Location: ' . BASE_URL . 'admin/reservations.php');
        exit();
    }
    
    switch ($action) {
        case 'confirm':
            $result = $reservationController->updateReservationStatus($reservationId, 'confirmed');
            if ($result['success']) {
                $_SESSION['success_message'] = 'Reservation confirmed successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        case 'cancel':
            $result = $reservationController->updateReservationStatus($reservationId, 'cancelled');
            if ($result['success']) {
                $_SESSION['success_message'] = 'Reservation cancelled successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        case 'delete':
            $result = $reservationController->deleteReservation($reservationId);
            if ($result['success']) {
                $_SESSION['success_message'] = 'Reservation deleted successfully';
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            break;
            
        default:
            $_SESSION['error_message'] = 'Invalid action';
            break;
    }
}

header('Location: ' . BASE_URL . 'admin/reservations.php');
exit();
