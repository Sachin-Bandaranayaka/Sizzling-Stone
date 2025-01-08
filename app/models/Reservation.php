<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public $reservation_id;
    public $user_id;
    public $reservation_time;
    public $guests;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, reservation_time, guests, status)
                VALUES
                (:user_id, :reservation_time, :guests, 'pending')";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":reservation_time", $this->reservation_time);
        $stmt->bindParam(":guests", $this->guests);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getAll() {
        $query = "SELECT r.*, u.username 
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.user_id
                ORDER BY r.reservation_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function getByStatus($status) {
        $query = "SELECT r.*, u.username 
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.user_id
                WHERE r.status = :status
                ORDER BY r.reservation_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        return $stmt;
    }

    public function getByUserId($userId) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE user_id = :user_id
                ORDER BY reservation_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        return $stmt;
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE reservation_id = :id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . "
                WHERE reservation_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function checkAvailability($date, $time) {
        $query = "SELECT COUNT(*) as count 
                FROM " . $this->table_name . "
                WHERE DATE(reservation_time) = :date 
                AND TIME(reservation_time) = :time
                AND status != 'cancelled'";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":time", $time);
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Assuming we have a maximum of 10 tables available per time slot
        return $row['count'] < 10;
    }

    public function getBookedTimeSlots($date) {
        $query = "SELECT TIME(reservation_time) as time, COUNT(*) as count 
                FROM " . $this->table_name . "
                WHERE DATE(reservation_time) = :date
                AND status != 'cancelled'
                GROUP BY TIME(reservation_time)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        return $stmt;
    }
}
