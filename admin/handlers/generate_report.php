<?php
// admin/handlers/generate_report.php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="TaxBizGh_Report.xls"');
header('Cache-Control: max-age=0');

// Determine date range based on report type
$where_clause = "";
switch ($_GET['type']) {
    case 'monthly':
        $where_clause = "WHERE tc.calculation_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
        break;
    case 'annual':
        $where_clause = "WHERE tc.calculation_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)";
        break;
    case 'custom':
        $start_date = $_GET['start'];
        $end_date = $_GET['end'];
        $where_clause = "WHERE tc.calculation_date BETWEEN '$start_date' AND '$end_date'";
        break;
}

// Get report data
$query = "
    SELECT 
        u.company_name,
        u.email,
        tc.calculation_date,
        tc.revenue,
        tc.expenses,
        tc.corporate_income_tax,
        tc.vat,
        tc.nhil,
        tc.getfl,
        tc.covid_levy,
        (tc.corporate_income_tax + tc.vat + tc.nhil + tc.getfl + tc.covid_levy) as total_tax
    FROM tax_calculations tc
    JOIN user u ON tc.user_id = u.id
    $where_clause
    ORDER BY tc.calculation_date DESC";

$result = $conn->query($query);

// Create Excel content
echo "
<table border='1'>
    <tr>
        <th>Company Name</th>
        <th>Email</th>
        <th>Date</th>
        <th>Revenue (GHS)</th>
        <th>Expenses (GHS)</th>
        <th>Corporate Tax (GHS)</th>
        <th>VAT (GHS)</th>
        <th>NHIL (GHS)</th>
        <th>GETFL (GHS)</th>
        <th>COVID-19 Levy (GHS)</th>
        <th>Total Tax (GHS)</th>
    </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . date('Y-m-d', strtotime($row['calculation_date'])) . "</td>";
    echo "<td>" . number_format($row['revenue'], 2) . "</td>";
    echo "<td>" . number_format($row['expenses'], 2) . "</td>";
    echo "<td>" . number_format($row['corporate_income_tax'], 2) . "</td>";
    echo "<td>" . number_format($row['vat'], 2) . "</td>";
    echo "<td>" . number_format($row['nhil'], 2) . "</td>";
    echo "<td>" . number_format($row['getfl'], 2) . "</td>";
    echo "<td>" . number_format($row['covid_levy'], 2) . "</td>";
    echo "<td>" . number_format($row['total_tax'], 2) . "</td>";
    echo "</tr>";
}

echo "</table>";