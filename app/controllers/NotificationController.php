<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $notification;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->notification = new Notification($db);
    }

    public function getUserNotifications($userId) {
        return $this->notification->getUserNotifications($userId);
    }

    public function getUnreadCount($userId) {
        return $this->notification->getUnreadCount($userId);
    }

    public function markAsRead($notificationId) {
        if($this->notification->markAsRead($notificationId)) {
            return ['success' => true, 'message' => 'Notification marked as read'];
        }
        return ['success' => false, 'message' => 'Unable to mark notification as read'];
    }

    public function markAllAsRead($userId) {
        if($this->notification->markAllAsRead($userId)) {
            return ['success' => true, 'message' => 'All notifications marked as read'];
        }
        return ['success' => false, 'message' => 'Unable to mark notifications as read'];
    }

    public function cleanupOldNotifications() {
        return $this->notification->deleteOldNotifications();
    }
}
