<?php
// admin/handlers/update_user.php
session_start();
require_once '../../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['id']) || empty($_POST['first_name']) ||
            empty($_POST['last_name']) || empty($_POST['email'])) {
            throw new Exception("Required fields are missing");
        }

        $id = (int)$_POST['id'];
        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $company_name = mysqli_real_escape_string($conn, $_POST['company_name'] ?? '');
        $company_address = mysqli_real_escape_string($conn, $_POST['company_address'] ?? '');
        $company_phone = mysqli_real_escape_string($conn, $_POST['company_phone'] ?? '');

        // Check if email exists for other users
        $check_email = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $id);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            throw new Exception("Email already exists for another user");
        }

        // Update user information
        $stmt = $conn->prepare("
            UPDATE user 
            SET first_name = ?, 
                last_name = ?, 
                email = ?, 
                company_name = ?, 
                company_address = ?, 
                company_phone = ?
            WHERE id = ?
        ");

        $stmt->bind_param("ssssssi",
            $first_name,
            $last_name,
            $email,
            $company_name,
            $company_address,
            $company_phone,
            $id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Error updating user");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}