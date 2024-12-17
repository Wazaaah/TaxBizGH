<?php
// handlers/login_handler.php
session_start();
require_once '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $response = array();

    // First check if it's an admin
    $sql = "SELECT * FROM admin WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['username'] = $row['username'];

            $response['success'] = true;
            $response['redirect'] = '../finalProject/admin/dashboard.php';
        } else {
            $response['success'] = false;
            $response['message'] = "Invalid password!";
        }
    } else {
        // Check regular user
        $sql = "SELECT * FROM user WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_type'] = 'user';
                $_SESSION['name'] = $row['first_name'];

                $response['success'] = true;
                $response['redirect'] = '../finalProject/dashboard.php';
            } else {
                $response['success'] = false;
                $response['message'] = "Invalid password!";
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No account found with this email!";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>