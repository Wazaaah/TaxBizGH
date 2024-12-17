<?php
session_start();
//if (isset($_SESSION['user_id'])) {
//    header("Location: dashboard.php");
//    exit();
//}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background-color: #f8f9fa;
        }

        .wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            display: flex;
            align-items: stretch;
            padding: 20px;
            max-width: 1200px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .image-section {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 20px;
        }

        .image-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .register-section {
            flex: 1;
            padding: 40px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-weight: bold;
        }

        h4 {
            color: #2c3e50;
            margin: 25px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            font-weight: 600;
        }

        .form-group label {
            font-weight: 500;
            color: #34495e;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-primary {
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            background-color: #3498db;
            border-color: #3498db;
            margin-top: 20px;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
        }

        /* Required field indicator */
        label.required:after {
            content: "*";
            color: #e74c3c;
            margin-left: 4px;
        }

        @media (max-width: 992px) {
            .container {
                flex-direction: column;
            }

            .image-section, .register-section {
                width: 100%;
            }

            .image-section img {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">TaxBizGh</a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.html">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="about.html">About</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="login.php">Login</a>
            </li>
        </ul>
    </div>
</nav>

<div class="wrapper">
    <div class="container">
        <div class="image-section">
            <img src="assets/images/register.jpg" alt="TaxBizGh">
        </div>
        <div class="register-section">
            <h1>Create Account</h1>
            <div id="error-message" class="alert alert-danger d-none"></div>
            <div id="success-message" class="alert alert-success d-none"></div>

            <form id="registerForm" method="POST" action="handlers/register_handler.php" novalidate>
                <h4>Personal Information</h4>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required" for="firstName">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                        <div class="invalid-feedback">Please enter your first name</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="required" for="lastName">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" required>
                        <div class="invalid-feedback">Please enter your last name</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="required" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Please enter a valid email address</div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required" for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">Password must be at least 8 characters</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="required" for="confirmPassword">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        <div class="invalid-feedback">Passwords do not match</div>
                    </div>
                </div>

                <h4>Company Information</h4>
                <div class="form-group">
                    <label for="companyName">Company Name</label>
                    <input type="text" class="form-control" id="companyName" name="companyName">
                </div>

                <div class="form-group">
                    <label for="companyAddress">Company Address</label>
                    <textarea class="form-control" id="companyAddress" name="companyAddress" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label for="companyPhone">Company Phone</label>
                    <input type="tel" class="form-control" id="companyPhone" name="companyPhone">
                </div>

                <div class="form-group">
                    <label for="companyDescription">Company Description</label>
                    <textarea class="form-control" id="companyDescription" name="companyDescription" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();

            // Reset validation states
            $('.is-invalid').removeClass('is-invalid');
            $('#error-message').addClass('d-none');
            $('#success-message').addClass('d-none');

            // Get form data
            const formData = $(this).serialize();

            // Validate required fields
            const firstName = $('#firstName').val().trim();
            const lastName = $('#lastName').val().trim();
            const email = $('#email').val().trim();
            const password = $('#password').val();
            const confirmPassword = $('#confirmPassword').val();

            // Basic validation
            if (!firstName || !/^[a-zA-Z\s]{2,50}$/.test(firstName)) {
                $('#firstName').addClass('is-invalid');
                return false;
            }

            if (!lastName || !/^[a-zA-Z\s]{2,50}$/.test(lastName)) {
                $('#lastName').addClass('is-invalid');
                return false;
            }

            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $('#email').addClass('is-invalid');
                return false;
            }

            if (!password || password.length < 8 || !/[A-Z]/.test(password) ||
                !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
                $('#password').addClass('is-invalid');
                return false;
            }

            if (password !== confirmPassword) {
                $('#confirmPassword').addClass('is-invalid');
                return false;
            }

            // Phone number validation (optional)
            const phone = $('#companyPhone').val().trim();
            if (phone && !/^\+?[\d\s-]{10,}$/.test(phone)) {
                $('#companyPhone').addClass('is-invalid');
                return false;
            }

            // Submit form
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#success-message')
                            .removeClass('d-none')
                            .text('Registration successful! Redirecting to login...');

                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        $('#error-message')
                            .removeClass('d-none')
                            .text(response.message);
                    }
                },
                error: function() {
                    $('#error-message')
                        .removeClass('d-none')
                        .text('An error occurred. Please try again.');
                }
            });
        });
    });
</script>
</body>
</html>