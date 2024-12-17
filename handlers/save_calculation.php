<?php
// handlers/save_calculation.php
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
        $revenue = floatval($_POST['revenue']);
        $expenses = floatval($_POST['expenses']);
        $vat_exemptions = floatval($_POST['vat_exemptions']);

        // Get tax rates
        $rates = [];
        $result = $conn->query("SELECT name, rate FROM tax_rates");
        while($row = $result->fetch_assoc()) {
            $rates[$row['name']] = $row['rate'];
        }

        // Calculate taxes
        $netIncome = $revenue - $expenses;
        $corporateIncomeTax = $netIncome * ($rates['Corporate Income Tax'] / 100);
        $vat = ($revenue - $vat_exemptions) * ($rates['VAT'] / 100);
        $nhil = $revenue * ($rates['NHIL'] / 100);
        $getfl = $revenue * ($rates['GETFL'] / 100);
        $covidLevy = $revenue * ($rates['COVID-19 Levy'] / 100);

        // Insert into database
        $sql = "INSERT INTO tax_calculations (user_id, revenue, expenses, vat_exemptions, 
                corporate_income_tax, vat, nhil, getfl, covid_levy) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idddddddd",
            $user_id, $revenue, $expenses, $vat_exemptions,
            $corporateIncomeTax, $vat, $nhil, $getfl, $covidLevy
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Calculation saved successfully'
            ]);
        } else {
            throw new Exception("Error saving calculation");
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>