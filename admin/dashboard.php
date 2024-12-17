<?php
// admin/dashboard.php
session_start();
require_once '../db_connect.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id'])  || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get total number of users
$users_query = "SELECT COUNT(*) as total_users FROM user";
$users_result = $conn->query($users_query);
$total_users = $users_result->fetch_assoc()['total_users'];

// Get total number of calculations
$calc_query = "SELECT COUNT(*) as total_calculations FROM tax_calculations";
$calc_result = $conn->query($calc_query);
$total_calculations = $calc_result->fetch_assoc()['total_calculations'];

// Get total revenue and expenses
$totals_query = "SELECT 
    SUM(revenue) as total_revenue,
    SUM(expenses) as total_expenses,
    SUM(revenue - expenses) as total_profit
    FROM tax_calculations";
$totals_result = $conn->query($totals_query);
$totals = $totals_result->fetch_assoc();

// Get tax rates
$rates_query = "SELECT * FROM tax_rates";
$rates_result = $conn->query($rates_query);
$tax_rates = [];
while ($rate = $rates_result->fetch_assoc()) {
    $tax_rates[] = $rate;
}

// Get recent users
$recent_users_query = "SELECT * FROM user ORDER BY registration_date DESC LIMIT 5";
$recent_users_result = $conn->query($recent_users_query);

// Get recent calculations
$recent_calcs_query = "
    SELECT tc.*, u.company_name 
    FROM tax_calculations tc
    JOIN user u ON tc.user_id = u.id
    ORDER BY calculation_date DESC LIMIT 5";
$recent_calcs_result = $conn->query($recent_calcs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
        }
        .main-content {
            padding: 20px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar {
            background-color: #343a40;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">TaxBizGh</a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="#">
                    <img src="../assets/images/profile.png" alt="Profile" class="rounded-circle" width="40">
                </a>
            </li>
        </ul>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/users.php">
                        <i class="fas fa-users mr-2"></i>Manage Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tax-rates.php">
                        <i class="fas fa-percentage mr-2"></i>Tax Rates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar mr-2"></i>Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../handlers/logout.php">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <h1 class="mb-4">Admin Dashboard</h1>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo number_format($total_users); ?></h3>
                        <p class="text-muted">Total Users</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3><?php echo number_format($total_calculations); ?></h3>
                        <p class="text-muted">Total Calculations</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3>GHS <?php echo number_format($totals['total_revenue'], 2); ?></h3>
                        <p class="text-muted">Total Revenue</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <h3>GHS <?php echo number_format($totals['total_profit'], 2); ?></h3>
                        <p class="text-muted">Total Profit</p>
                    </div>
                </div>
            </div>

            <!-- Tax Rates -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Current Tax Rates</h2>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#editTaxRatesModal">
                        <i class="fas fa-edit"></i> Edit Rates
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Tax Type</th>
                            <th>Rate (%)</th>
                            <th>Last Updated</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tax_rates as $rate): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rate['name']); ?></td>
                                <td><?php echo number_format($rate['rate'], 2); ?>%</td>
                                <td><?php echo date('M d, Y', strtotime($rate['last_updated'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <!-- Recent Users -->
                <div class="col-md-6">
                    <div class="table-container">
                        <h2>Recent Users</h2>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Registration Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php while ($user = $recent_users_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['company_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['registration_date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Calculations -->
                <div class="col-md-6">
                    <div class="table-container">
                        <h2>Recent Calculations</h2>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Revenue</th>
                                    <th>Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php while ($calc = $recent_calcs_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($calc['company_name']); ?></td>
                                        <td>GHS <?php echo number_format($calc['revenue'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($calc['calculation_date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Tax Rates Modal -->
<div class="modal fade" id="editTaxRatesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Tax Rates</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="taxRatesForm">
                    <?php foreach ($tax_rates as $rate): ?>
                        <div class="form-group">
                            <label for="rate_<?php echo $rate['id']; ?>">
                                <?php echo htmlspecialchars($rate['name']); ?> (%)
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="rate_<?php echo $rate['id']; ?>"
                                   name="rates[<?php echo $rate['id']; ?>]"
                                   value="<?php echo $rate['rate']; ?>"
                                   step="0.01"
                                   min="0"
                                   max="100">
                        </div>
                    <?php endforeach; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTaxRates">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#saveTaxRates').click(function() {
            const formData = $('#taxRatesForm').serialize();

            $.ajax({
                url: 'handlers/update_tax_rates.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert('Tax rates updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating tax rates');
                }
            });
        });
    });
</script>
</body>
</html>