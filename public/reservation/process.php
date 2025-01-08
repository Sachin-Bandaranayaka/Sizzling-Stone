<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/ReservationController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
error_log("Reservation request received. Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to make a reservation';
    header('Location: ' . BASE_URL . 'public/auth/login.php');
    exit();
}

$reservationController = new ReservationController();

// Handle status update (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    if (!isset($_POST['reservation_id']) || !isset($_POST['status'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }

    $result = $reservationController->updateReservationStatus($_POST['reservation_id'], $_POST['status']);
    echo json_encode($result);
    exit();
}

// Handle reservation creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['date', 'time', 'guests'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception('Please fill in all required fields: ' . implode(', ', $missing_fields));
        }

        // Validate date and time
        $date = $_POST['date'];
        $time = $_POST['time'];
        $current_date = date('Y-m-d');
        
        if ($date < $current_date) {
            throw new Exception('Please select a future date');
        }

        // Create reservation data array
        $reservationData = [
            'user_id' => $_SESSION['user_id'],
            'reservation_time' => $date . ' ' . $time,
            'guests' => (int)$_POST['guests']
        ];

        error_log("Processing reservation with data: " . print_r($reservationData, true));

        // Check if the time slot is available
        if (!$reservationController->isTimeSlotAvailable($date, $time)) {
            throw new Exception('Sorry, this time slot is no longer available. Please choose another time.');
        }

        // Create the reservation
        $result = $reservationController->createReservation($reservationData);
        error_log("Reservation creation result: " . print_r($result, true));

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: ' . BASE_URL . 'public/reservation/success.php');
        } else {
            throw new Exception($result['message']);
        }
    } catch (Exception $e) {
        error_log("Error creating reservation: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header('Location: ' . BASE_URL . 'public/reservation/create.php');
    }
    exit();
}

// If someone tries to access this file directly without POST data
header('Location: ' . BASE_URL . 'public/reservation/create.php');
exit();
