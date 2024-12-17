<?php
// handlers/register_handler.php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array();

    try {
        // Validate required fields
        if (empty($_POST['firstName']) || empty($_POST['lastName']) ||
            empty($_POST['email']) || empty($_POST['password'])) {
            throw new Exception("Please fill all required fields");
        }

        // Validate password
        $password = $_POST['password'];
        if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) ||
            !preg_match("/[a-z]/", $password) || !preg_match("/[0-9]/", $password)) {
            throw new Exception("Password must be at least 8 characters with uppercase, lowercase, and numbers");
        }

        // Sanitize inputs
        $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
        $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $companyName = mysqli_real_escape_string($conn, $_POST['companyName']);
        $companyAddress = mysqli_real_escape_string($conn, $_POST['companyAddress']);
        $companyPhone = mysqli_real_escape_string($conn, $_POST['companyPhone']);
        $companyDescription = mysqli_real_escape_string($conn, $_POST['companyDescription']);

        // Check if email already exists
        $check_email = $conn->query("SELECT id FROM user WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        // Insert new user
        $sql = "INSERT INTO user (first_name, last_name, email, password, company_name, 
                company_address, company_phone, company_description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss",
            $firstName, $lastName, $email, $hashedPassword,
            $companyName, $companyAddress, $companyPhone, $companyDescription
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Registration successful!";
        } else {
            throw new Exception("Registration failed: " . $conn->error);
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}
?>