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

    public function getAllUsers() {
        return $this->user->getAllUsers();
    }

    public function getUserById($userId) {
        return $this->user->getUserById($userId);
    }

    public function updateUser($data) {
        $this->user->user_id = $data['user_id'];
        $this->user->name = $data['name'];
        $this->user->phone = $data['phone'];
        $this->user->role = $data['role'];
        $this->user->is_active = $data['is_active'];

        if ($this->user->update()) {
            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to update user'
        ];
    }

    public function deleteUser($userId) {
        if ($this->user->delete($userId)) {
            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to delete user'
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
