<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $user;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->user = new User($db);
    }

    public function getUserById($userId) {
        return $this->user->getById($userId);
    }

    public function updateUser($data) {
        $this->user->user_id = $data['user_id'];
        $this->user->username = $data['username'];
        $this->user->email = $data['email'];
        $this->user->phone = $data['phone'];

        if ($this->user->update()) {
            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to update profile'
        ];
    }

    public function getRecentOrders($userId, $limit = 5) {
        return $this->user->getRecentOrders($userId, $limit);
    }

    public function validatePassword($userId, $password) {
        return $this->user->validatePassword($userId, $password);
    }

    public function updatePassword($userId, $newPassword) {
        return $this->user->updatePassword($userId, $newPassword);
    }
}
