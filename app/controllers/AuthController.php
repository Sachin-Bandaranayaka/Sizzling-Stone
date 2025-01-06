<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register($data) {
        if(empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        $this->user->username = $data['username'];
        $this->user->password = $data['password'];
        $this->user->email = $data['email'];
        $this->user->phone = $data['phone'] ?? '';
        $this->user->role = 'customer';

        if($this->user->create()) {
            return ['success' => true, 'message' => 'Registration successful'];
        }
        return ['success' => false, 'message' => 'Registration failed'];
    }

    public function login($username, $password) {
        $result = $this->user->login($username, $password);
        
        if($result) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['role'] = $result['role'];
            return ['success' => true, 'message' => 'Login successful'];
        }
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout successful'];
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
?>
