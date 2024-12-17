<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];

        // Get user info
        $stmt = $conn->prepare("SELECT first_name, last_name, email FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        $stmt = $conn->prepare("
            INSERT INTO support_messages (user_id, name, email, subject, message)
            VALUES (?, ?, ?, ?, ?)
        ");

        $name = $user['first_name'] . ' ' . $user['last_name'];
        $stmt->bind_param("issss",
            $user_id,
            $name,
            $user['email'],
            $subject,
            $message
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Support request submitted successfully'
            ]);
        } else {
            throw new Exception("Error submitting support request");
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>