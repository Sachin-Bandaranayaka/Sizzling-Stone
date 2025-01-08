<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Reservation.php';

class ReservationController {
    private $reservation;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->reservation = new Reservation($db);
    }

    public function getAllReservations() {
        return $this->reservation->getAll();
    }

    public function getReservationsByStatus($status) {
        return $this->reservation->getByStatus($status);
    }

    public function createReservation($data) {
        $this->reservation->user_id = $data['user_id'];
        $this->reservation->reservation_time = $data['reservation_time'];
        $this->reservation->guests = $data['guests'];

        if($this->reservation->create()) {
            return ['success' => true, 'message' => 'Reservation created successfully'];
        }
        return ['success' => false, 'message' => 'Unable to create reservation'];
    }

    public function updateReservationStatus($id, $status) {
        if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        if($this->reservation->updateStatus($id, $status)) {
            return ['success' => true, 'message' => 'Reservation status updated successfully'];
        }
        return ['success' => false, 'message' => 'Unable to update reservation status'];
    }

    public function deleteReservation($id) {
        if($this->reservation->delete($id)) {
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
