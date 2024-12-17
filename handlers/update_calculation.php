<?php
// handlers/update_calculation.php
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
        $revenue = floatval($_POST['revenue']);
        $expenses = floatval($_POST['expenses']);
        $vat_exemptions = floatval($_POST['vat_exemptions']);
        $user_id = $_SESSION['user_id'];

        // Verify the calculation belongs to the user
        $stmt = $conn->prepare("SELECT id FROM tax_calculations WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Calculation not found or unauthorized");
        }

        // Get current tax rates
        $rates = [];
        $result = $conn->query("SELECT name, rate FROM tax_rates");
        while($row = $result->fetch_assoc()) {
            $rates[$row['name']] = $row['rate'];
        }

        // Calculate new tax amounts
        $netIncome = $revenue - $expenses;
        $corporateIncomeTax = $netIncome * ($rates['Corporate Income Tax'] / 100);
        $vat = ($revenue - $vat_exemptions) * ($rates['VAT'] / 100);
        $nhil = $revenue * ($rates['NHIL'] / 100);
        $getfl = $revenue * ($rates['GETFL'] / 100);
        $covidLevy = $revenue * ($rates['COVID-19 Levy'] / 100);

        // Update the calculation
        $stmt = $conn->prepare("
            UPDATE tax_calculations 
            SET revenue = ?, 
                expenses = ?, 
                vat_exemptions = ?,
                corporate_income_tax = ?,
                vat = ?,
                nhil = ?,
                getfl = ?,
                covid_levy = ?
            WHERE id = ? AND user_id = ?
        ");

        $stmt->bind_param("ddddddddii",
            $revenue, $expenses, $vat_exemptions,
            $corporateIncomeTax, $vat, $nhil,
            $getfl, $covidLevy, $id, $user_id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Error updating calculation");
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>