<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public $notification_id;
    public $user_id;
    public $title;
    public $message;
    public $type;
    public $reference_id;
    public $is_read;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, title, message, type, reference_id)
                VALUES
                (:user_id, :title, :message, :type, :reference_id)";

        try {
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":title", $this->title);
            $stmt->bindParam(":message", $this->message);
            $stmt->bindParam(":type", $this->type);
            $stmt->bindParam(":reference_id", $this->reference_id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }

    public function getUserNotifications($userId) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE user_id = :user_id
                ORDER BY created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error getting user notifications: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as unread_count 
                FROM " . $this->table_name . "
                WHERE user_id = :user_id AND is_read = FALSE";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['unread_count'];
        } catch (PDOException $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }

    public function markAsRead($notificationId) {
        $query = "UPDATE " . $this->table_name . "
                SET is_read = TRUE
                WHERE notification_id = :notification_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":notification_id", $notificationId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }

    public function markAllAsRead($userId) {
        $query = "UPDATE " . $this->table_name . "
                SET is_read = TRUE
                WHERE user_id = :user_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }

    public function deleteOldNotifications($days = 30) {
        $query = "DELETE FROM " . $this->table_name . "
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                AND is_read = TRUE";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":days", $days, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting old notifications: " . $e->getMessage());
            return false;
        }
    }
}
