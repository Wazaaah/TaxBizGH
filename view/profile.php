<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_personal'])) {
        // Update personal information
        $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
        $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $updateStmt = $conn->prepare("
            UPDATE user 
            SET first_name = ?, last_name = ?, email = ?
            WHERE id = ?
        ");
        $updateStmt->bind_param("sssi", $firstName, $lastName, $email, $user_id);

        if ($updateStmt->execute()) {
            $message = "Personal information updated successfully!";
            $messageType = "success";
            // Refresh user data
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $message = "Error updating personal information.";
            $messageType = "danger";
        }
    }
    elseif (isset($_POST['update_company'])) {
        // Update company information
        $companyName = mysqli_real_escape_string($conn, $_POST['companyName']);
        $companyAddress = mysqli_real_escape_string($conn, $_POST['companyAddress']);
        $companyPhone = mysqli_real_escape_string($conn, $_POST['companyPhone']);
        $companyDescription = mysqli_real_escape_string($conn, $_POST['companyDescription']);

        $updateStmt = $conn->prepare("
            UPDATE user 
            SET company_name = ?, company_address = ?, company_phone = ?, company_description = ?
            WHERE id = ?
        ");
        $updateStmt->bind_param("ssssi", $companyName, $companyAddress, $companyPhone, $companyDescription, $user_id);

        if ($updateStmt->execute()) {
            $message = "Company information updated successfully!";
            $messageType = "success";
            // Refresh user data
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $message = "Error updating company information.";
            $messageType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #1A5F7A;
            border-color: #1A5F7A;
        }
        .btn-primary:hover {
            background-color: #134B61;
            border-color: #134B61;
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
        <a class="nav-link active" href="profile.php">
            <i class="fas fa-user"></i> Profile
        </a>
        <a class="nav-link" href="support.php">
            <i class="fas fa-question-circle"></i> Support
        </a>
        <a class="nav-link" href="../handlers/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="container mt-4">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Personal Information Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user mr-2"></i>Personal Information
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName"
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName"
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <button type="submit" name="update_personal" class="btn btn-primary btn-block">
                            Update Personal Information
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Company Information Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-building mr-2"></i>Company Information
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="companyName">Company Name</label>
                            <input type="text" class="form-control" id="companyName" name="companyName"
                                   value="<?php echo htmlspecialchars($user['company_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="companyAddress">Company Address</label>
                            <input type="text" class="form-control" id="companyAddress" name="companyAddress"
                                   value="<?php echo htmlspecialchars($user['company_address'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="companyPhone">Company Phone Number</label>
                            <input type="tel" class="form-control" id="companyPhone" name="companyPhone"
                                   value="<?php echo htmlspecialchars($user['company_phone'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="companyDescription">Company Description</label>
                            <textarea class="form-control" id="companyDescription" name="companyDescription"
                                      rows="4" required><?php echo htmlspecialchars($user['company_description'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_company" class="btn btn-primary btn-block">
                            Update Company Information
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>