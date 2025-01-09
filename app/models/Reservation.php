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
        $query = "SELECT 
                    r.*,
                    u.username as customer_name,
                    DATE(r.reservation_time) as reservation_date,
                    TIME(r.reservation_time) as reservation_time,
                    r.guests as party_size,
                    r.status
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.user_id
                ORDER BY r.reservation_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStatus($status) {
        $query = "SELECT 
                    r.*,
                    u.username as customer_name,
                    DATE(r.reservation_time) as reservation_date,
                    TIME(r.reservation_time) as reservation_time,
                    r.guests as party_size
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.user_id
                WHERE r.status = :status
                ORDER BY r.reservation_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT 
                    r.*,
                    u.username as customer_name,
                    DATE(r.reservation_time) as reservation_date,
                    TIME(r.reservation_time) as reservation_time,
                    r.guests as party_size
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.user_id
                WHERE r.reservation_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByIdJoin($id) {
        $query = "SELECT r.*, u.username as customer_name 
                 FROM " . $this->table_name . " r
                 JOIN users u ON r.user_id = u.user_id
                 WHERE r.reservation_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE reservation_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . "
                WHERE reservation_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function getByUserId($userId) {
        $query = "SELECT 
                    r.*,
                    DATE(r.reservation_time) as reservation_date,
                    TIME(r.reservation_time) as reservation_time,
                    r.guests as party_size
                FROM " . $this->table_name . " r
                WHERE r.user_id = :user_id
                ORDER BY r.reservation_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function getPendingReservationsCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTotalReservationsCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getCancelledReservationsCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getCompletedReservationsCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
