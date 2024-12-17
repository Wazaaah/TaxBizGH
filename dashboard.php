<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's information
$stmt = $conn->prepare("SELECT first_name, company_name FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get totals from tax_calculations
$stmt = $conn->prepare("
    SELECT 
        SUM(revenue) as total_revenue,
        SUM(expenses) as total_expenses,
        SUM(corporate_income_tax + vat + nhil + getfl + covid_levy) as total_tax,
        SUM(revenue - expenses - (corporate_income_tax + vat + nhil + getfl + covid_levy)) as net_profit
    FROM tax_calculations 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: #2c3e50;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .kpi-card {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            transition: transform 0.3s;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
        }

        .kpi-value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .nav-link {
            color: #2c3e50;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background-color: #f8f9fa;
            color: #3498db;
        }

        .nav-link.active {
            background-color: #3498db;
            color: white;
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .tips-news {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .tips-news ul {
            list-style: none;
            padding: 0;
        }

        .tips-news li {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .tips-news li:last-child {
            border-bottom: none;
        }

        .welcome-section {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="#">TaxBizGh</a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="profile.php">
                    <img src="assets/images/profile.png" alt="Profile" class="rounded-circle" width="40">
                </a>
            </li>
        </ul>
    </div>
</nav>
<br><br>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar" style="width: 250px;">
        <div class="text-center mb-4">
            <h5><?php echo htmlspecialchars($user['company_name'] ?? $user['first_name']); ?></h5>
        </div>
        <div class="nav flex-column">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link" href="tax-calculator.php">
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
    <div class="main-content container-fluid px-4">
        <!-- Welcome Section -->
        <div class="welcome-section mb-4">
            <h4>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h4>
            <p>Here's your financial overview and tax calculation summary.</p>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card kpi-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Revenue</h5>
                        <p class="kpi-value">GHS <?php echo number_format($totals['total_revenue'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Expenditure</h5>
                        <p class="kpi-value">GHS <?php echo number_format($totals['total_expenses'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Net Profit</h5>
                        <p class="kpi-value">GHS <?php echo number_format(($totals['net_profit'] ?? 0), 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(calculation_date, '%Y-%m') as month,
        SUM(revenue) as total_revenue,
        SUM(expenses) as total_expenses,
        SUM(corporate_income_tax + vat + nhil + getfl + covid_levy) as total_tax,
        SUM(revenue - expenses - (corporate_income_tax + vat + nhil + getfl + covid_levy)) as profit_minus_tax
    FROM tax_calculations 
    WHERE user_id = ? 
    AND calculation_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(calculation_date, '%Y-%m')
    ORDER BY month ASC
");

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $months = [];
        $revenues = [];
        $expenses = [];
        $taxes = [];
        $profits = [];

        while ($row = $result->fetch_assoc()) {
            $months[] = date('M Y', strtotime($row['month'] . '-01'));
            $revenues[] = $row['total_revenue'] ?? 0;
            $expenses[] = $row['total_expenses'] ?? 0;
            $taxes[] = $row['total_tax'] ?? 0;
            $profits[] = $row['profit_minus_tax'] ?? 0;
        }

        // If no data, provide empty months
        if (empty($months)) {
            for ($i = 5; $i >= 0; $i--) {
                $months[] = date('M Y', strtotime("-$i month"));
                $revenues[] = 0;
                $expenses[] = 0;
                $taxes[] = 0;
                $profits[] = 0;
            }
        }

        $chartData = [
            'months' => $months,
            'revenues' => $revenues,
            'expenses' => $expenses,
            'taxes' => $taxes,
            'profits' => $profits
        ];
        ?>

        <!-- Financial Summary Chart (Full Width) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="chart-container" style="position: relative; height:450px;">
                    <h2>Financial Summary</h2>
                    <canvas id="financial-summary-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Secondary Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-container" style="position: relative; height:350px;">
                    <h2>Tax Trends</h2>
                    <canvas id="tax-trends-chart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container" style="position: relative; height:350px;">
                    <h2>Expenditure Trends</h2>
                    <canvas id="expenditure-trends-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tips and News Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="tips-news h-100">
                    <h2>Tips and Tricks</h2>
                    <ul id="tips-tricks" class="list-unstyled"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tips-news h-100">
                    <h2>Recent News and Updates</h2>
                    <ul id="news-updates" class="list-unstyled"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const chartData = <?php echo json_encode($chartData); ?>;
        const noDataMessage = "No Data Available";

        // Financial summary chart
        const financialCtx = document.getElementById('financial-summary-chart').getContext('2d');
        new Chart(financialCtx, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: 'Revenue',
                        data: chartData.revenues,
                        borderColor: '#3498db',
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4
                    },
                    {
                        label: 'Expenses',
                        data: chartData.expenses,
                        borderColor: '#e74c3c',
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4
                    },
                    {
                        label: 'Profit (After Tax)',
                        data: chartData.profits,
                        borderColor: '#2ecc71',
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 5,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        displayColors: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'GHS ' + value.toLocaleString();
                            }
                        },
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Tax trends chart
        const taxCtx = document.getElementById('tax-trends-chart').getContext('2d');
        new Chart(taxCtx, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [{
                    label: 'Total Tax',
                    data: chartData.taxes,
                    borderColor: '#9b59b6',
                    backgroundColor: 'rgba(155, 89, 182, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'GHS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Expenditure trends chart
        const expCtx = document.getElementById('expenditure-trends-chart').getContext('2d');
        new Chart(expCtx, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [{
                    label: 'Monthly Expenses',
                    data: chartData.expenses,
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'GHS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });

    // Add tips and news
    const tips = [
        "Review your tax deductions monthly",
        "Keep all receipts organized",
        "Plan for quarterly tax payments",
        "Stay updated with tax law changes"
    ];

    const news = [
        "New tax policies announced for 2024",
        "Upcoming tax filing deadline",
        "Changes in VAT regulations",
        "New digital tax submission system"
    ];

    const tipsElement = document.getElementById('tips-tricks');
    tips.forEach(tip => {
        const li = document.createElement('li');
        li.innerHTML = `<i class="fas fa-lightbulb text-warning mr-2"></i>${tip}`;
        tipsElement.appendChild(li);
    });

    const newsElement = document.getElementById('news-updates');
    news.forEach(item => {
        const li = document.createElement('li');
        li.innerHTML = `<i class="fas fa-newspaper text-info mr-2"></i>${item}`;
        newsElement.appendChild(li);
    });
</script>
</body>
</html>