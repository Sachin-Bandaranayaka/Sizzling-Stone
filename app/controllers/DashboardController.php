<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../../config/Database.php';

class DashboardController {
    private $orderModel;
    private $userModel;
    private $reviewModel;
    private $reservationModel;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        $this->orderModel = new Order($this->db);
        $this->userModel = new User($this->db);
        $this->reviewModel = new Review($this->db);
        $this->reservationModel = new Reservation($this->db);
    }

    public function getDashboardStats() {
        $stats = [
            'total_orders' => $this->orderModel->getTotalOrders(),
            'total_revenue' => $this->orderModel->getTotalRevenue(),
            'todays_orders' => $this->orderModel->getTodaysOrders(),
            'todays_revenue' => $this->orderModel->getTodaysRevenue(),
            'total_users' => $this->userModel->getTotalUsers(),
            'total_reviews' => $this->reviewModel->getTotalReviews(),
            'pending_reservations' => $this->reservationModel->getPendingReservationsCount(),
            'recent_orders' => $this->orderModel->getRecentOrders(5),
            'recent_users' => $this->userModel->getRecentUsers(5),
            'recent_reviews' => $this->reviewModel->getRecentReviews(5)
        ];

        return $stats;
    }
}
