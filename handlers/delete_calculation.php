<?php
// handlers/delete_calculation.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)$_POST['id'];
        $user_id = $_SESSION['user_id'];

        // First verify the calculation belongs to the user
        $stmt = $conn->prepare("SELECT id FROM tax_calculations WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Calculation not found or unauthorized");
        }

        // Delete the calculation
        $stmt = $conn->prepare("DELETE FROM tax_calculations WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Error deleting calculation");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>