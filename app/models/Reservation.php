<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public $id;
    public $user_id;
    public $date;
    public $time;
    public $guests;
    public $special_requests;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, date, time, guests, special_requests, status)
                VALUES
                (:user_id, :date, :time, :guests, :special_requests, 'pending')";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->special_requests = htmlspecialchars(strip_tags($this->special_requests));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":time", $this->time);
        $stmt->bindParam(":guests", $this->guests);
        $stmt->bindParam(":special_requests", $this->special_requests);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getAll() {
        $query = "SELECT r.*, u.username 
                FROM " . $this->table_name . " r
                LEFT JOIN users u ON r.user_id = u.id
                ORDER BY r.date ASC, r.time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function getByUserId($userId) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE user_id = :user_id
                ORDER BY date ASC, time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        return $stmt;
    }

    public function update($id, $data) {
        $updateFields = [];
        $params = [":id" => $id];

        // Build dynamic update query based on provided data
        foreach($data as $key => $value) {
            if(in_array($key, ['date', 'time', 'guests', 'special_requests', 'status'])) {
                $updateFields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if(empty($updateFields)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . "
                SET " . implode(", ", $updateFields) . "
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        if($stmt->execute($params)) {
            return true;
        }
        return false;
    }

    public function delete($id, $userId) {
        $query = "DELETE FROM " . $this->table_name . "
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $userId);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function checkAvailability($date, $time) {
        $query = "SELECT COUNT(*) as count 
                FROM " . $this->table_name . "
                WHERE date = :date 
                AND time = :time
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
        $query = "SELECT time, COUNT(*) as count 
                FROM " . $this->table_name . "
                WHERE date = :date 
                AND status != 'cancelled'
                GROUP BY time
                HAVING count >= 10";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        return $stmt;
    }
}
