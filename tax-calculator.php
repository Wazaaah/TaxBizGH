<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, company_name FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get tax rates from database
$rates = [];
$query = "SELECT name, rate FROM tax_rates";
$result = $conn->query($query);
while($row = $result->fetch_assoc()) {
    $rates[$row['name']] = $row['rate'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Calculator - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: #2c3e50;
        }

        .sidebar {
            background-color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        .sidebar {
            padding-top: 76px;
            padding-left: 20px;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
        }

        .nav-link {
            color: #2c3e50;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background-color: #3498db;
            color: white;
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        #tax-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
        }

        .btn-primary {
            padding: 12px;
            font-weight: 600;
        }

        #tax-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .saved-summary {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .tax-calculator-container {
            display: flex;
            gap: 20px;
        }

        .tax-calculator-left {
            flex: 1;
        }

        .tax-calculator-right {
            flex: 1;
            max-width: 50%;
        }

        @media (max-width: 768px) {
            .tax-calculator-container {
                flex-direction: column;
            }

            .tax-calculator-right {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">TaxBizGh</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="profile.php">
                    <img src="assets/images/profile.png" alt="Profile Picture" class="rounded-circle" width="40">
                </a>
            </li>
        </ul>
    </div>
</nav>
<br><br>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <h5><?php echo htmlspecialchars($user['company_name'] ?? $user['first_name']); ?></h5>
        </div>
        <div class="nav flex-column">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link active" href="tax-calculator.php">
                <i class="fas fa-calculator"></i> Calculate Tax
            </a>
            <a class="nav-link" href="tax-history.php">
                <i class="fas fa-history"></i> Tax History
            </a>
            <a class="nav-link" href="profile.php">
                <i class="fas fa-user"></i> Profile
            </a>
            <a class="nav-link" href="support.php">
                <i class="fas fa-question-circle"></i> Support
            </a>
            <a class="nav-link" href="handlers/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="text-center mb-3">Tax Calculator</h1>
        <p class="text-center mb-4">Provide your financial information below to calculate taxes:</p>

        <div class="tax-calculator-container">
            <!-- Left Side: Tax Calculation Form -->
            <div class="tax-calculator-left">
                <form id="tax-form">
                    <div class="form-group">
                        <label for="revenue">Total Revenue (GHS)</label>
                        <input type="number" class="form-control" id="revenue" name="revenue" placeholder="Enter total revenue" required>
                    </div>
                    <div class="form-group">
                        <label for="expenses">Total Expenses (GHS)</label>
                        <input type="number" class="form-control" id="expenses" name="expenses" placeholder="Enter total expenses" required>
                    </div>
                    <div class="form-group">
                        <label for="vat">VAT Exemptions (GHS)</label>
                        <input type="number" class="form-control" id="vat" name="vat" placeholder="Enter VAT exemptions">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Calculate Taxes</button>
                </form>

                <div class="mt-4">
                    <h2 class="text-center">Tax Summary</h2>
                    <div id="tax-summary" class="d-none">
                        <!-- Tax summary will be inserted here -->
                    </div>
                    <button type="button" id="save-button" class="btn btn-success btn-block mt-3 d-none">Save Summary</button>
                </div>
            </div>

            <!-- Right Side: Recent Calculations -->
            <div class="tax-calculator-right">
                <h2 class="text-center">Recent Calculations</h2>
                <div id="saved-summaries" class="mt-3">
                    <!-- Recent calculations will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const taxRates = <?php echo json_encode($rates); ?>;

    $(document).ready(function() {
        // Load recent calculations
        loadRecentCalculations();

        $('#tax-form').on('submit', function(e) {
            e.preventDefault();
            calculateTaxes();
        });

        $('#save-button').on('click', function() {
            saveSummary();
        });
    });

    function calculateTaxes() {
        const revenue = parseFloat($('#revenue').val());
        const expenses = parseFloat($('#expenses').val());
        const vatExemptions = parseFloat($('#vat').val()) || 0;

        const netIncome = revenue - expenses;
        const corporateIncomeTax = netIncome * (taxRates['Corporate Income Tax'] / 100);
        const vat = (revenue - vatExemptions) * (taxRates['VAT'] / 100);
        const nhil = revenue * (taxRates['NHIL'] / 100);
        const getfl = revenue * (taxRates['GETFL'] / 100);
        const covidTax = revenue * (taxRates['COVID-19 Levy'] / 100);

        const totalTax = corporateIncomeTax + vat + nhil + getfl + covidTax;

        const summary = `
        <div class="tax-breakdown">
            <h4 class="mb-3">Tax Breakdown:</h4>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Corporate Income Tax (${taxRates['Corporate Income Tax']}%)
                    <span>GHS ${corporateIncomeTax.toFixed(2)}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    VAT (${taxRates['VAT']}%)
                    <span>GHS ${vat.toFixed(2)}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    NHIL (${taxRates['NHIL']}%)
                    <span>GHS ${nhil.toFixed(2)}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    GETFL (${taxRates['GETFL']}%)
                    <span>GHS ${getfl.toFixed(2)}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    COVID-19 Levy (${taxRates['COVID-19 Levy']}%)
                    <span>GHS ${covidTax.toFixed(2)}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center font-weight-bold">
                    Total Tax
                    <span>GHS ${totalTax.toFixed(2)}</span>
                </li>
            </ul>
        </div>
    `;

        $('#tax-summary').html(summary).removeClass('d-none');
        $('#save-button').removeClass('d-none');
    }

    function saveSummary() {
        const formData = {
            revenue: $('#revenue').val(),
            expenses: $('#expenses').val(),
            vat_exemptions: $('#vat').val() || 0
        };

        $.ajax({
            url: 'handlers/save_calculation.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Calculation saved successfully!');
                    loadRecentCalculations();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while saving the calculation.');
            }
        });
    }

    function loadRecentCalculations() {
        $.ajax({
            url: 'handlers/get_calculations.php',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayRecentCalculations(response.calculations);
                }
            }
        });
    }

    function displayRecentCalculations(calculations) {
        const container = $('#saved-summaries');
        container.empty();

        if (calculations.length === 0) {
            container.html('<p class="text-center">No calculations found</p>');
            return;
        }

        calculations.forEach(calc => {
            const totalTax = parseFloat(calc.corporate_income_tax) +
                parseFloat(calc.vat) +
                parseFloat(calc.nhil) +
                parseFloat(calc.getfl) +
                parseFloat(calc.covid_levy);

            container.append(`
            <div class="saved-summary">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Calculation from ${new Date(calc.calculation_date).toLocaleDateString()}</h5>
                    <div>
                        <a href="view_calculation.php?id=${calc.id}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="download_calculation.php?id=${calc.id}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Revenue:</strong> GHS ${parseFloat(calc.revenue).toFixed(2)}
                    </div>
                    <div class="col-md-4">
                        <strong>Expenses:</strong> GHS ${parseFloat(calc.expenses).toFixed(2)}
                    </div>
                    <div class="col-md-4">
                        <strong>Total Tax:</strong> GHS ${totalTax.toFixed(2)}
                    </div>
                </div>
            </div>
        `);
        });
    }
</script>
</body>
</html>