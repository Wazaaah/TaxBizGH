<?php
// admin/reports.php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get monthly statistics for the past 12 months
$monthly_stats_query = "
    SELECT 
        DATE_FORMAT(calculation_date, '%Y-%m') as month,
        COUNT(*) as calculations_count,
        SUM(revenue) as total_revenue,
        SUM(expenses) as total_expenses,
        SUM(corporate_income_tax + vat + nhil + getfl + covid_levy) as total_tax
    FROM tax_calculations
    WHERE calculation_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(calculation_date, '%Y-%m')
    ORDER BY month ASC";

$monthly_stats_result = $conn->query($monthly_stats_query);

// Get top 10 companies by revenue
$top_companies_query = "
    SELECT 
        u.company_name,
        COUNT(tc.id) as calculations_count,
        SUM(tc.revenue) as total_revenue,
        SUM(tc.expenses) as total_expenses,
        SUM(tc.corporate_income_tax + tc.vat + tc.nhil + tc.getfl + tc.covid_levy) as total_tax
    FROM user u
    JOIN tax_calculations tc ON u.id = tc.user_id
    GROUP BY u.id, u.company_name
    ORDER BY total_revenue DESC
    LIMIT 10";

$top_companies_result = $conn->query($top_companies_query);

// Get tax type distribution
$tax_distribution_query = "
    SELECT 
        SUM(corporate_income_tax) as corporate_tax,
        SUM(vat) as vat,
        SUM(nhil) as nhil,
        SUM(getfl) as getfl,
        SUM(covid_levy) as covid_levy
    FROM tax_calculations";

$tax_distribution_result = $conn->query($tax_distribution_query);
$tax_distribution = $tax_distribution_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - TaxBizGh Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .chart-container {
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
                    <a class="nav-link" href="tax-rates.php">
                        <i class="fas fa-percentage mr-2"></i>Tax Rates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="reports.php">
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
            <h1 class="mb-4">Reports & Analytics</h1>

            <!-- Monthly Trends Chart -->
            <div class="chart-container">
                <h2>Monthly Trends</h2>
                <canvas id="monthlyTrendsChart"></canvas>
            </div>

            <!-- Tax Distribution Chart -->
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h2>Tax Distribution</h2>
                        <canvas id="taxDistributionChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h2>Top Companies</h2>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Revenue</th>
                                    <th>Tax Paid</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php while ($company = $top_companies_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($company['company_name']); ?></td>
                                        <td>GHS <?php echo number_format($company['total_revenue'], 2); ?></td>
                                        <td>GHS <?php echo number_format($company['total_tax'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Download Reports Section -->
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Generate Reports</h2>
                        <div>
                            <button class="btn btn-primary" id="downloadMonthly">
                                <i class="fas fa-download mr-2"></i>Monthly Report
                            </button>
                            <button class="btn btn-success" id="downloadAnnual">
                                <i class="fas fa-download mr-2"></i>Annual Report
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reportStartDate">Start Date</label>
                                <input type="date" class="form-control" id="reportStartDate">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reportEndDate">End Date</label>
                                <input type="date" class="form-control" id="reportEndDate">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-info" id="generateCustomReport">
                        <i class="fas fa-file-export mr-2"></i>Generate Custom Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Trends Chart
            const monthlyData = <?php
                $months = [];
                $revenues = [];
                $expenses = [];
                $taxes = [];

                while ($row = $monthly_stats_result->fetch_assoc()) {
                    $months[] = date('M Y', strtotime($row['month'] . '-01'));
                    $revenues[] = $row['total_revenue'];
                    $expenses[] = $row['total_expenses'];
                    $taxes[] = $row['total_tax'];
                }

                echo json_encode([
                    'months' => $months,
                    'revenues' => $revenues,
                    'expenses' => $expenses,
                    'taxes' => $taxes
                ]);
                ?>;

            new Chart(document.getElementById('monthlyTrendsChart').getContext('2d'), {
                type: 'bar', // Change this to 'bar'
                data: {
                    labels: monthlyData.months,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: monthlyData.revenues,
                            backgroundColor: '#28a745'
                        },
                        {
                            label: 'Expenses',
                            data: monthlyData.expenses,
                            backgroundColor: '#dc3545'
                        },
                        {
                            label: 'Total Tax',
                            data: monthlyData.taxes,
                            backgroundColor: '#007bff'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'GHS ' + new Intl.NumberFormat().format(value);
                                }
                            }
                        }
                    }
                }
            });


            // Tax Distribution Chart
            const taxData = <?php echo json_encode($tax_distribution); ?>;

            new Chart(document.getElementById('taxDistributionChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: [
                        'Corporate Income Tax',
                        'VAT',
                        'NHIL',
                        'GETFL',
                        'COVID-19 Levy'
                    ],
                    datasets: [{
                        data: [
                            taxData.corporate_tax,
                            taxData.vat,
                            taxData.nhil,
                            taxData.getfl,
                            taxData.covid_levy
                        ],
                        backgroundColor: [
                            '#007bff',
                            '#28a745',
                            '#ffc107',
                            '#dc3545',
                            '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Handle report downloads
            $('#downloadMonthly').click(function() {
                window.location.href = 'handlers/generate_report.php?type=monthly';
            });

            $('#downloadAnnual').click(function() {
                window.location.href = 'handlers/generate_report.php?type=annual';
            });

            $('#generateCustomReport').click(function() {
                const startDate = $('#reportStartDate').val();
                const endDate = $('#reportEndDate').val();

                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }

                window.location.href = `handlers/generate_report.php?type=custom&start=${startDate}&end=${endDate}`;
            });
        });
    </script>
</body>
</html>