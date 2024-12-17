<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$calculationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if (!$calculationId) {
    header('Location: dashboard.php');
    exit;
}

// Fetch calculation details with company info
$stmt = $conn->prepare("
    SELECT tc.*, u.company_name, u.company_address 
    FROM tax_calculations tc
    JOIN user u ON tc.user_id = u.id 
    WHERE tc.id = ? AND tc.user_id = ?
");
$stmt->bind_param("ii", $calculationId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit;
}

$calculation = $result->fetch_assoc();
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tax Calculation Report - <?php echo htmlspecialchars($calculation['company_name']); ?></title>
        <style>
            @media print {
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }

                .no-print {
                    display: none;
                }

                .container {
                    max-width: 800px;
                    margin: 0 auto;
                }

                .header {
                    text-align: center;
                    margin-bottom: 30px;
                }

                .logo {
                    max-width: 200px;
                    margin-bottom: 20px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }

                th, td {
                    border: 1px solid #ddd;
                    padding: 12px;
                    text-align: left;
                }

                th {
                    background-color: #f5f5f5;
                }

                .total-section {
                    margin-top: 30px;
                    border-top: 2px solid #333;
                    padding-top: 20px;
                }

                .footer {
                    margin-top: 50px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
            }

            /* Screen styles */
            @media screen {
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                    background-color: #f0f2f5;
                }

                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                }

                .print-button {
                    display: block;
                    width: 200px;
                    margin: 20px auto;
                    padding: 12px;
                    background-color: #1A5F7A;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                }

                .print-button:hover {
                    background-color: #134B61;
                }

                /* Rest of the styles same as print media */
                .header, table, th, td, .total-section, .footer {
                    /* Same as above print styles */
                }
            }
        </style>
    </head>
    <body>
    <button onclick="window.print()" class="print-button no-print">Download as PDF</button>

    <div class="container">
        <div class="header">
            <img src="../assets/images/logo.png" alt="TaxBizGh Logo" class="logo">
            <h1>Tax Calculation Report</h1>
        </div>

        <div class="company-details">
            <h2><?php echo htmlspecialchars($calculation['company_name']); ?></h2>
            <p><?php echo htmlspecialchars($calculation['company_address']); ?></p>
            <p>Date: <?php echo date('F d, Y', strtotime($calculation['calculation_date'])); ?></p>
        </div>

        <h3>Financial Information</h3>
        <table>
            <tr>
                <th>Description</th>
                <th>Amount (GHS)</th>
            </tr>
            <tr>
                <td>Revenue</td>
                <td><?php echo number_format($calculation['revenue'], 2); ?></td>
            </tr>
            <tr>
                <td>Expenses</td>
                <td><?php echo number_format($calculation['expenses'], 2); ?></td>
            </tr>
            <tr>
                <td>VAT Exemptions</td>
                <td><?php echo number_format($calculation['vat_exemptions'], 2); ?></td>
            </tr>
        </table>

        <h3>Tax Breakdown</h3>
        <table>
            <tr>
                <th>Tax Type</th>
                <th>Rate (%)</th>
                <th>Amount (GHS)</th>
            </tr>
            <tr>
                <td>Corporate Income Tax</td>
                <td>25.0</td>
                <td><?php echo number_format($calculation['corporate_income_tax'], 2); ?></td>
            </tr>
            <tr>
                <td>Value Added Tax (VAT)</td>
                <td>12.5</td>
                <td><?php echo number_format($calculation['vat'], 2); ?></td>
            </tr>
            <tr>
                <td>National Health Insurance Levy (NHIL)</td>
                <td>2.5</td>
                <td><?php echo number_format($calculation['nhil'], 2); ?></td>
            </tr>
            <tr>
                <td>Ghana Education Trust Fund (GETFL)</td>
                <td>2.5</td>
                <td><?php echo number_format($calculation['getfl'], 2); ?></td>
            </tr>
            <tr>
                <td>COVID-19 Health Recovery Levy</td>
                <td>1.0</td>
                <td><?php echo number_format($calculation['covid_levy'], 2); ?></td>
            </tr>
        </table>

        <div class="total-section">
            <h3>Total Tax Payable</h3>
            <table>
                <tr>
                    <th>Total</th>
                    <th>GHS <?php echo number_format(
                            $calculation['corporate_income_tax'] +
                            $calculation['vat'] +
                            $calculation['nhil'] +
                            $calculation['getfl'] +
                            $calculation['covid_levy'],
                            2
                        ); ?></th>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>This document was generated by TaxBizGh on <?php echo date('F d, Y g:i A'); ?></p>
            <p>For any queries, please contact support@taxbizgh.com</p>
        </div>
    </div>

    <script>
        // Auto-print when using download button
        document.querySelector('.print-button').addEventListener('click', function() {
            // Small delay to ensure styles are loaded
            setTimeout(function() {
                window.print();
            }, 200);
        });
    </script>
    </body>
    </html>
