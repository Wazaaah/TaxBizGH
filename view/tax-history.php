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

// Get tax rates for calculations
$rates = [];
$result = $conn->query("SELECT name, rate FROM tax_rates");
while($row = $result->fetch_assoc()) {
    $rates[$row['name']] = $row['rate'];
}

// Get all tax calculations for the user
$stmt = $conn->prepare("
    SELECT * FROM tax_calculations 
    WHERE user_id = ? 
    ORDER BY calculation_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$calculations = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax History - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .history-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .action-buttons .btn { margin: 0 2px; }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .modal-footer {
            border-top: 1px solid #dee2e6;
        }

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
    <div class="text-center mb-4">
        <h5><?php echo htmlspecialchars($user['company_name'] ?? $user['first_name']); ?></h5>
    </div>
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

<div class="container">
    <div class="history-card">
        <h2 class="mb-4">Tax Calculation History</h2>

        <div class="table-responsive">
            <table id="taxHistoryTable" class="table table-striped">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Revenue</th>
                    <th>Expenses</th>
                    <th>Total Tax</th>
                    <th>Net Profit</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($calc = $calculations->fetch_assoc()): ?>
                    <?php
                    $totalTax = $calc['corporate_income_tax'] +
                        $calc['vat'] +
                        $calc['nhil'] +
                        $calc['getfl'] +
                        $calc['covid_levy'];

                    $netProfit = $calc['revenue'] - $calc['expenses'] - $totalTax;
                    ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($calc['calculation_date'])); ?></td>
                        <td>GHS <?php echo number_format($calc['revenue'], 2); ?></td>
                        <td>GHS <?php echo number_format($calc['expenses'], 2); ?></td>
                        <td>GHS <?php echo number_format($totalTax, 2); ?></td>
                        <td>GHS <?php echo number_format($netProfit, 2); ?></td>
                        <td class="action-buttons">
                            <a href="view_calculation.php?id=<?php echo $calc['id']; ?>"
                               class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-danger delete-btn"
                                    onclick="deleteCalculation(<?php echo $calc['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <a href="download_calculation.php?id=<?php echo $calc['id']; ?>"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        const table = $('#taxHistoryTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                search: "Search calculations:"
            }
        });

        // Save changes
        $('#saveChanges').click(function() {
            const data = {
                id: $('#editId').val(),
                revenue: $('#editRevenue').val(),
                expenses: $('#editExpenses').val(),
                vat_exemptions: $('#editVatExemptions').val()
            };

            $.ajax({
                url: 'handlers/update_calculation.php',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#editModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating');
                }
            });
        });
    });

    // Delete calculation
    function deleteCalculation(id) {
        if (confirm('Are you sure you want to delete this calculation?')) {
            $.ajax({
                url: 'handlers/delete_calculation.php',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting');
                }
            });
        }
    }
</script>
</body>
</html>