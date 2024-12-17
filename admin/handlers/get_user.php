<?php
// admin/handlers/get_user.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (isset($_GET['id'])) {
    try {
        $user_id = (int)$_GET['id'];

        // Get user details with calculation statistics
        $query = "
            SELECT 
                u.*,
                COUNT(DISTINCT tc.id) as calculations_count,
                COALESCE(SUM(tc.revenue), 0) as total_revenue,
                COALESCE(SUM(tc.expenses), 0) as total_expenses
            FROM user u
            LEFT JOIN tax_calculations tc ON u.id = tc.user_id
            WHERE u.id = ?
            GROUP BY u.id";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }

        $user = $result->fetch_assoc();
        echo json_encode(['success' => true, 'user' => $user]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
}