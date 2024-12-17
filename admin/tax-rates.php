<?php
// admin/tax-rates.php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get all tax rates
$query = "SELECT * FROM tax_rates ORDER BY name";
$result = $conn->query($query);

// Get tax rate change history
$history_query = "
    SELECT 
        tr.name,
        th.old_rate,
        th.new_rate,
        th.change_date,
        th.changed_by
    FROM tax_rate_history th
    JOIN tax_rates tr ON th.tax_rate_id = tr.id
    ORDER BY th.change_date DESC
    LIMIT 10";
$history_result = $conn->query($history_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Rates Management - TaxBizGh Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
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
        .tax-rates-container {
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
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users mr-2"></i>Manage Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="tax-rates.php">
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
            <h1 class="mb-4">Tax Rates Management</h1>

            <!-- Current Tax Rates -->
            <div class="tax-rates-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Current Tax Rates</h2>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#editRatesModal">
                        <i class="fas fa-edit mr-2"></i>Edit Rates
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Tax Type</th>
                            <th>Current Rate (%)</th>
                            <th>Description</th>
                            <th>Last Updated</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($rate = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rate['name']); ?></td>
                                <td><?php echo number_format($rate['rate'], 2); ?>%</td>
                                <td><?php echo htmlspecialchars($rate['description']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($rate['last_updated'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rate Change History -->
            <div class="tax-rates-container">
                <h2>Rate Change History</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Tax Type</th>
                            <th>Old Rate</th>
                            <th>New Rate</th>
                            <th>Changed By</th>
                            <th>Change Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($history = $history_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($history['name']); ?></td>
                                <td><?php echo number_format($history['old_rate'], 2); ?>%</td>
                                <td><?php echo number_format($history['new_rate'], 2); ?>%</td>
                                <td><?php echo htmlspecialchars($history['changed_by']); ?></td>
                                <td><?php echo date('M d, Y g:i A', strtotime($history['change_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Tax Rates Modal -->
<div class="modal fade" id="editRatesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Tax Rates</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editRatesForm">
                    <?php
                    // Reset result pointer
                    $result->data_seek(0);
                    while ($rate = $result->fetch_assoc()):
                        ?>
                        <div class="form-group">
                            <label for="rate_<?php echo $rate['id']; ?>">
                                <?php echo htmlspecialchars($rate['name']); ?> (%)
                            </label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control"
                                       id="rate_<?php echo $rate['id']; ?>"
                                       name="rates[<?php echo $rate['id']; ?>]"
                                       value="<?php echo $rate['rate']; ?>"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <?php echo htmlspecialchars($rate['description']); ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveRates">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#saveRates').click(function() {
            const formData = $('#editRatesForm').serialize();

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

        // Form validation
        $('#editRatesForm input').on('input', function() {
            const value = parseFloat($(this).val());
            if (value < 0 || value > 100) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Prevent form submission if values are invalid
        $('#editRatesForm').on('submit', function(e) {
            e.preventDefault();
            const invalidInputs = $(this).find('input.is-invalid');
            if (invalidInputs.length > 0) {
                alert('Please correct the highlighted fields');
                return false;
            }
        });
    });
</script>
</body>
</html>