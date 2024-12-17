<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's information
$stmt = $conn->prepare("SELECT first_name, last_name, company_name, email FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .support-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }

        .status-pending {
            color: #ffc107;
        }

        .status-in_progress {
            color: #17a2b8;
        }

        .status-resolved {
            color: #28a745;
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
                    <img src="../assets/images/profile.png" alt="Profile Picture" class="rounded-circle" width="40">
                </a>
            </li>
        </ul>
    </div>
</nav>
<br><br>
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
        <a class="nav-link" href="tax-history.php">
            <i class="fas fa-history"></i> Tax History
        </a>
        <a class="nav-link" href="profile.php">
            <i class="fas fa-user"></i> Profile
        </a>
        <a class="nav-link active" href="support.php">
            <i class="fas fa-question-circle"></i> Support
        </a>
        <a class="nav-link" href="../handlers/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="container">
    <div class="support-card">
        <h2 class="text-center mb-4">Contact Support</h2>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Contact Information</h5>
                        <p><i class="fas fa-envelope mr-2"></i> support@taxbizgh.com</p>
                        <p><i class="fas fa-phone mr-2"></i> +233 123 456 789</p>
                        <p><i class="fas fa-map-marker-alt mr-2"></i> 123 Business St, Accra, Ghana</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">FAQs</h5>
                        <div class="accordion" id="faqAccordion">
                            <div class="card border-0">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0">
                                        <a href="#" data-toggle="collapse" data-target="#faq1">
                                            How do I calculate my taxes?
                                        </a>
                                    </h6>
                                </div>
                                <div id="faq1" class="collapse" data-parent="#faqAccordion">
                                    <div class="card-body">
                                        Use our tax calculator tool available in the dashboard to calculate your taxes accurately.
                                    </div>
                                </div>
                            </div>
                            <!-- Add more FAQs as needed -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <h4>Submit a Support Request</h4>
                <form id="supportForm">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" class="form-control" id="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-12">
                <h4>Your Support History</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody id="supportHistory">
                        <?php
                        $stmt = $conn->prepare("
                                    SELECT * FROM support_messages 
                                    WHERE user_id = ? 
                                    ORDER BY created_at DESC
                                ");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                            echo "<td>" . htmlspecialchars(substr($row['message'], 0, 50)) . "...</td>";
                            echo "<td><span class='status-" . $row['status'] . "'>" .
                                ucfirst(str_replace('_', ' ', $row['status'])) . "</span></td>";
                            echo "</tr>";
                        }

                        if ($result->num_rows === 0) {
                            echo "<tr><td colspan='4' class='text-center'>No support requests yet</td></tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#supportForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'handlers/support_handler.php',
                type: 'POST',
                data: {
                    subject: $('#subject').val(),
                    message: $('#message').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Support request submitted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
</script>
</body>
</html>