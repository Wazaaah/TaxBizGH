<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];

    // Get recent calculations
    $sql = "SELECT * FROM tax_calculations 
            WHERE user_id = ? 
            ORDER BY calculation_date DESC 
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $calculations = array();
    while ($row = $result->fetch_assoc()) {
        $calculations[] = [
            'id' => $row['id'],
            'revenue' => $row['revenue'],
            'expenses' => $row['expenses'],
            'vat_exemptions' => $row['vat_exemptions'],
            'corporate_income_tax' => $row['corporate_income_tax'],
            'vat' => $row['vat'],
            'nhil' => $row['nhil'],
            'getfl' => $row['getfl'],
            'covid_levy' => $row['covid_levy'],
            'calculation_date' => $row['calculation_date']
        ];
    }

    echo json_encode([
        'success' => true,
        'calculations' => $calculations
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>