<?php
// admin/handlers/delete_user.php
session_start();
require_once '../../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $user_id = (int)$_POST['id'];

        // Start transaction
        $conn->begin_transaction();

        // Delete user's tax calculations first (due to foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM tax_calculations WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting user's tax calculations");
        }

        // Delete user's support messages
        $stmt = $conn->prepare("DELETE FROM support_messages WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting user's support messages");
        }

        // Finally, delete the user
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting user");
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}