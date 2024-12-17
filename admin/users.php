<?php
// admin/users.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get all users with their calculation counts and total revenue
$users_query = "
    SELECT 
        u.*,
        COUNT(DISTINCT tc.id) as calculations_count,
        COALESCE(SUM(tc.revenue), 0) as total_revenue,
        COALESCE(SUM(tc.expenses), 0) as total_expenses
    FROM user u
    LEFT JOIN tax_calculations tc ON u.id = tc.user_id
    GROUP BY u.id
    ORDER BY u.registration_date DESC";

$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - TaxBizGh Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
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
        .users-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 2px;
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
                    <a class="nav-link active" href="users.php">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Users</h1>
            </div>

            <div class="users-table">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Calculations</th>
                            <th>Total Revenue</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['company_name']); ?></td>
                                <td><?php echo number_format($user['calculations_count']); ?></td>
                                <td>GHS <?php echo number_format($user['total_revenue'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['registration_date'])); ?></td>
                                <td>
                                    <button class="btn btn-info btn-action view-user"
                                            data-id="<?php echo $user['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-action edit-user"
                                            data-id="<?php echo $user['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-action delete-user"
                                            data-id="<?php echo $user['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="userDetails"></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="form-group">
                        <label for="editFirstName">First Name</label>
                        <input type="text" class="form-control" id="editFirstName" required>
                    </div>
                    <div class="form-group">
                        <label for="editLastName">Last Name</label>
                        <input type="text" class="form-control" id="editLastName" required>
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" class="form-control" id="editEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="editCompanyName">Company Name</label>
                        <input type="text" class="form-control" id="editCompanyName">
                    </div>
                    <div class="form-group">
                        <label for="editCompanyAddress">Company Address</label>
                        <textarea class="form-control" id="editCompanyAddress" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editCompanyPhone">Company Phone</label>
                        <input type="tel" class="form-control" id="editCompanyPhone">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUserChanges">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#usersTable').DataTable({
            order: [[5, 'desc']],
            pageLength: 10,
            language: {
                search: "Search users:"
            }
        });

        // View User Details
        $('.view-user').click(function() {
            const userId = $(this).data('id');
            $.get('handlers/get_user.php', { id: userId }, function(response) {
                if (response.success) {
                    const user = response.user;
                    let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Personal Information</h5>
                            <p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>
                            <p><strong>Email:</strong> ${user.email}</p>
                            <p><strong>Registration Date:</strong> ${new Date(user.registration_date).toLocaleDateString()}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Company Information</h5>
                            <p><strong>Company Name:</strong> ${user.company_name || 'N/A'}</p>
                            <p><strong>Address:</strong> ${user.company_address || 'N/A'}</p>
                            <p><strong>Phone:</strong> ${user.company_phone || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Activity Summary</h5>
                            <p><strong>Total Calculations:</strong> ${user.calculations_count}</p>
                            <p><strong>Total Revenue:</strong> GHS ${parseFloat(user.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
                            <p><strong>Total Expenses:</strong> GHS ${parseFloat(user.total_expenses).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
                        </div>
                    </div>
                `;
                    $('#userDetails').html(html);
                    $('#viewUserModal').modal('show');
                } else {
                    alert('Error loading user details');
                }
            });
        });

        // Edit User
        $('.edit-user').click(function() {
            const userId = $(this).data('id');
            $.get('handlers/get_user.php', { id: userId }, function(response) {
                if (response.success) {
                    const user = response.user;
                    $('#editUserId').val(user.id);
                    $('#editFirstName').val(user.first_name);
                    $('#editLastName').val(user.last_name);
                    $('#editEmail').val(user.email);
                    $('#editCompanyName').val(user.company_name);
                    $('#editCompanyAddress').val(user.company_address);
                    $('#editCompanyPhone').val(user.company_phone);
                    $('#editUserModal').modal('show');
                }
            });
        });

        // Save User Changes
        $('#saveUserChanges').click(function() {
            const userData = {
                id: $('#editUserId').val(),
                first_name: $('#editFirstName').val(),
                last_name: $('#editLastName').val(),
                email: $('#editEmail').val(),
                company_name: $('#editCompanyName').val(),
                company_address: $('#editCompanyAddress').val(),
                company_phone: $('#editCompanyPhone').val()
            };

            $.ajax({
                url: 'handlers/update_user.php',
                type: 'POST',
                data: userData,
                success: function(response) {
                    if (response.success) {
                        alert('User updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating user');
                }
            });
        });

        // Delete User
        $('.delete-user').click(function() {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const userId = $(this).data('id');

                $.ajax({
                    url: 'handlers/delete_user.php',
                    type: 'POST',
                    data: { id: userId },
                    success: function(response) {
                        if (response.success) {
                            alert('User deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while deleting user');
                    }
                });
            }
        });
    });
</script>
</body>
</html>