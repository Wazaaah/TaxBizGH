<?php
session_start();
header('Content-Type: application/json');

require_once '../db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$calculationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if (!$calculationId) {
    echo json_encode(['success' => false, 'message' => 'Invalid calculation ID']);
    exit;
}

// Fetch calculation details
$stmt = $conn->prepare("
    SELECT * 
    FROM tax_calculations 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $calculationId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Calculation not found']);
    exit;
}

$calculation = $result->fetch_assoc();

// Ensure numeric values are properly formatted
$numericFields = [
    'revenue', 'expenses', 'vat_exemptions',
    'corporate_income_tax', 'vat', 'nhil',
    'getfl', 'covid_levy'
];

foreach ($numericFields as $field) {
    $calculation[$field] = floatval($calculation[$field]);
}

echo json_encode([
    'success' => true,
    'calculation' => $calculation
]);
exit;
?>