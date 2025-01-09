<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Notification.php';

class ReservationController {
    private $reservation;
    private $notification;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reservation = new Reservation($this->db);
        $this->notification = new Notification($this->db);
    }

    public function getAllReservations() {
        return $this->reservation->getAll();
    }

    public function getReservationsByStatus($status) {
        return $this->reservation->getByStatus($status);
    }

    public function createReservation($data) {
        $this->reservation->user_id = $data['user_id'];
        $this->reservation->reservation_date = $data['reservation_date'];
        $this->reservation->reservation_time = $data['reservation_time'];
        $this->reservation->party_size = $data['party_size'];
        $this->reservation->special_requests = $data['special_requests'] ?? '';

        if($this->reservation->create()) {
            // Send notification for new reservation
            $this->createNotification(
                $data['user_id'],
                'Reservation Received',
                'Your reservation request has been received and is pending confirmation.',
                'reservation',
                $this->reservation->reservation_id
            );
            return ['success' => true, 'message' => 'Reservation created successfully'];
        }
        return ['success' => false, 'message' => 'Unable to create reservation'];
    }

    public function updateReservationStatus($id, $status) {
        if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        // Get reservation details before update
        $reservation = $this->reservation->getById($id);
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }

        if($this->reservation->updateStatus($id, $status)) {
            // Send notification based on status
            $title = '';
            $message = '';
            
            switch ($status) {
                case 'confirmed':
                    $title = 'Reservation Confirmed';
                    $message = 'Your reservation for ' . date('M d, Y', strtotime($reservation['reservation_date'])) . 
                              ' at ' . date('h:i A', strtotime($reservation['reservation_time'])) . 
                              ' has been confirmed.';
                    break;
                case 'cancelled':
                    $title = 'Reservation Cancelled';
                    $message = 'Your reservation for ' . date('M d, Y', strtotime($reservation['reservation_date'])) . 
                              ' has been cancelled.';
                    break;
            }

            if ($title && $message) {
                $this->createNotification(
                    $reservation['user_id'],
                    $title,
                    $message,
                    'reservation',
                    $id
                );
            }

            return ['success' => true, 'message' => 'Reservation status updated successfully'];
        }
        return ['success' => false, 'message' => 'Unable to update reservation status'];
    }

    private function createNotification($userId, $title, $message, $type, $referenceId) {
        $this->notification->user_id = $userId;
        $this->notification->title = $title;
        $this->notification->message = $message;
        $this->notification->type = $type;
        $this->notification->reference_id = $referenceId;
        return $this->notification->create();
    }

    public function deleteReservation($id) {
        // Get reservation details before deletion
        $reservation = $this->reservation->getById($id);
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }

        if($this->reservation->delete($id)) {
            // Send notification for deletion
            $this->createNotification(
                $reservation['user_id'],
                'Reservation Deleted',
                'Your reservation has been deleted.',
                'reservation',
                $id
            );
            return ['success' => true, 'message' => 'Reservation deleted successfully'];
        }
        return ['success' => false, 'message' => 'Unable to delete reservation'];
    }

    public function getUserReservations($userId) {
        return $this->reservation->getByUserId($userId);
    }

    public function isTimeSlotAvailable($date, $time) {
        return $this->reservation->checkAvailability($date, $time);
    }

    public function getAvailableTimeSlots($date) {
        // Define restaurant hours
        $openTime = strtotime('11:00');
        $closeTime = strtotime('22:00');
        $interval = 30 * 60; // 30 minutes in seconds
        $availableSlots = [];

        // Get booked time slots
        $bookedSlots = $this->reservation->getBookedTimeSlots($date);
        $bookedSlotsCount = [];
        
        while ($row = $bookedSlots->fetch(PDO::FETCH_ASSOC)) {
            $bookedSlotsCount[$row['time']] = $row['count'];
        }

        // Generate all possible time slots
        for ($time = $openTime; $time <= $closeTime; $time += $interval) {
            $timeStr = date('H:i', $time);
            $count = $bookedSlotsCount[$timeStr] ?? 0;
            
            if ($count < 10) { // Less than 10 reservations for this time slot
                $availableSlots[] = [
                    'time' => $timeStr,
                    'available_tables' => 10 - $count
                ];
            }
        }

        return $availableSlots;
    }
}
