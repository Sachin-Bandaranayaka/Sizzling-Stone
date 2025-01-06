<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Reservation.php';

class ReservationController {
    private $db;
    private $reservation;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reservation = new Reservation($this->db);
    }

    public function createReservation($data) {
        $this->reservation->user_id = $data['user_id'];
        $this->reservation->date = $data['date'];
        $this->reservation->time = $data['time'];
        $this->reservation->guests = $data['guests'];
        $this->reservation->special_requests = $data['special_requests'] ?? '';

        if($this->reservation->create()) {
            return ['success' => true, 'message' => 'Reservation created successfully'];
        }
        return ['success' => false, 'message' => 'Unable to create reservation'];
    }

    public function getUserReservations($userId) {
        return $this->reservation->getByUserId($userId);
    }

    public function getAllReservations() {
        return $this->reservation->getAll();
    }

    public function updateReservation($id, $data) {
        return $this->reservation->update($id, $data);
    }

    public function deleteReservation($id, $userId) {
        return $this->reservation->delete($id, $userId);
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
        $bookedSlots = $this->reservation->getBookedTimeSlots($date);
        
        // Convert booked slots to easier to check format
        $bookedTimes = [];
        while($slot = $bookedSlots->fetch(PDO::FETCH_ASSOC)) {
            $bookedTimes[] = $slot['time'];
        }
        
        // Check each possible time slot
        for($time = $openTime; $time <= $closeTime; $time += $interval) {
            $timeStr = date('H:i', $time);
            if(!in_array($timeStr, $bookedTimes)) {
                $availableSlots[] = $timeStr;
            }
        }
        
        return $availableSlots;
    }
}
