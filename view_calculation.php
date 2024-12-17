<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$calculationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, company_name FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$calculationId) {
    header('Location: dashboard.php');
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
    <title>Tax Calculation Details - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .navbar {
            position: fixed;
            background-color: #2c3e50;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
        }

        .sidebar {
            background-color: white;
            height: calc(100vh - 56px);
            position: fixed;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            width: 250px;
            padding-top: 50px;
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
<br>
<div class="sidebar" style="width: 250px;">

    <div class="nav flex-column">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a class="nav-link" href="tax-calculator.php">
            <i class="fas fa-calculator"></i> Calculate Tax
        </a>
        <a class="nav-link active" href="tax-history.php">
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

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Tax Calculation Details</h4>
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Financial Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Revenue</th>
                            <td>GHS <?php echo number_format($calculation['revenue'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Expenses</th>
                            <td>GHS <?php echo number_format($calculation['expenses'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>VAT Exemptions</th>
                            <td>GHS <?php echo number_format($calculation['vat_exemptions'], 2); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Tax Breakdown</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Corporate Income Tax</th>
                            <td>GHS <?php echo number_format($calculation['corporate_income_tax'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>VAT</th>
                            <td>GHS <?php echo number_format($calculation['vat'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>NHIL</th>
                            <td>GHS <?php echo number_format($calculation['nhil'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>GETFL</th>
                            <td>GHS <?php echo number_format($calculation['getfl'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>COVID-19 Levy</th>
                            <td>GHS <?php echo number_format($calculation['covid_levy'], 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="download_calculation.php?id=<?php echo $calculationId; ?>"
                   class="btn btn-success">
                    <i class="fas fa-download mr-2"></i>Download Report
                </a>
            </div>
        </div>
        <div class="card-footer text-muted">
            Calculation Date: <?php echo date('F d, Y g:i A', strtotime($calculation['calculation_date'])); ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>