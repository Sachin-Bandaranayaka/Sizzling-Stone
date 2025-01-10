<?php
class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $password;
    public $email;
    public $phone;
    public $role;
    public $name;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (username, password, email, phone, role, name, is_active, created_at)
                VALUES
                (:username, :password, :email, :phone, :role, :name, :is_active, :created_at)";

        $stmt = $this->conn->prepare($query);

        // Sanitize and hash password
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->is_active = (int)$this->is_active;
        $this->created_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":created_at", $this->created_at);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login($username, $password) {
        // First try username or email
        $query = "SELECT user_id, username, password, role FROM " . $this->table_name . " 
                 WHERE username = :identifier OR email = :identifier LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $identifier = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":identifier", $identifier);
        
        try {
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if(password_verify($password, $row['password'])) {
                    error_log("User found - ID: " . $row['user_id'] . ", Role: " . $row['role']); // Debug log
                    return [
                        'user_id' => $row['user_id'],
                        'username' => $row['username'],
                        'role' => $row['role']
                    ];
                }
            }
            error_log("Login failed for username: " . $username); // Debug log
            return false;
        } catch (PDOException $e) {
            error_log("Database error during login: " . $e->getMessage()); // Debug log
            return false;
        }
    }

    public function getUserById($id) {
        $query = "SELECT user_id, username, email, phone, role, name, is_active, created_at 
                 FROM " . $this->table_name . " 
                 WHERE user_id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        try {
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return false;
        }
    }

    public function getById($userId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET name = :name,
                    phone = :phone,
                    role = :role,
                    is_active = :is_active
                WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->is_active = (int)$this->is_active;

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":user_id", $this->user_id);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    public function getRecentOrders($userId, $limit = 5) {
        $query = "SELECT * FROM orders 
                WHERE user_id = :user_id 
                ORDER BY order_date DESC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    public function validatePassword($userId, $password) {
        $query = "SELECT password FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            return password_verify($password, $user['password']);
        }
        return false;
    }

    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $query = "UPDATE " . $this->table_name . "
                SET password = :password
                WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":user_id", $userId);

        return $stmt->execute();
    }

    public function getAllUsers() {
        try {
            // First, get the list of available columns
            $columnsQuery = "SHOW COLUMNS FROM " . $this->table_name;
            $columnsStmt = $this->conn->query($columnsQuery);
            $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);

            // Build the SELECT query based on available columns
            $selectColumns = ['user_id', 'username', 'email', 'role', 'created_at'];
            
            // Add optional columns if they exist
            if (in_array('name', $columns)) {
                $selectColumns[] = 'name';
            }
            if (in_array('is_active', $columns)) {
                $selectColumns[] = 'is_active';
            }
            if (in_array('phone', $columns)) {
                $selectColumns[] = 'phone';
            }

            $query = "SELECT " . implode(', ', $selectColumns) . " FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllUsers: " . $e->getMessage());
            return [];
        }
    }

    public function delete($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }

    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getRecentUsers($limit = 5) {
        $query = "SELECT user_id, username, email, name, is_active, created_at 
                 FROM " . $this->table_name . " 
                 ORDER BY created_at DESC 
                 LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
