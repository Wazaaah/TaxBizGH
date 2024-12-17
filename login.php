<?php
session_start();
require_once 'handlers/session_handler.php';


if (isset($_SESSION['user_id'])) {
    SessionManager::destroy();
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TaxBizGh</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
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
                <a class="nav-link" href="register.php">Sign Up</a>
            </li>
        </ul>
    </div>
</nav>
<div class="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-6 image">
                <img src="assets/images/login.jpg" alt="TaxBizGh" class="img-fluid">
            </div>
            <div class="col-md-6 login">
                <h1>Login</h1>
                <div id="error-message" class="alert alert-danger d-none"></div>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                    <p class="text-center mt-3">Don't have an account? <a href="register.php">Sign up here</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            const email = $('#email').val().trim();
            const password = $('#password').val();

            if (!email || !password) {
                $('#error-message').removeClass('d-none').text('Please fill in all fields');
                return;
            }

            $.ajax({
                url: 'handlers/login_handler.php',
                type: 'POST',
                data: {
                    email: email,
                    password: password
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        $('#error-message').removeClass('d-none').text(response.message);
                    }
                },
                error: function() {
                    $('#error-message').removeClass('d-none').text('An error occurred. Please try again.');
                }
            });
        });
    });
</script>
</body>
</html>