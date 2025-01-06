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
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (username, password, email, phone, role, created_at)
                VALUES
                (:username, :password, :email, :phone, :role, :created_at)";

        $stmt = $this->conn->prepare($query);

        // Sanitize and hash password
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->created_at = date('Y-m-d H:i:s');

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":created_at", $this->created_at);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login($username, $password) {
        $query = "SELECT user_id, username, password, role FROM " . $this->table_name . " 
                 WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
