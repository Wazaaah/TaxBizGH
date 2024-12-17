<?php
// admin/handlers/update_tax_rates.php
session_start();
require_once '../../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rates'])) {
    try {
        // Start transaction
        $conn->begin_transaction();

        foreach ($_POST['rates'] as $id => $rate) {
            $id = (int)$id;
            $rate = (float)$rate;

            // Validate rate
            if ($rate < 0 || $rate > 100) {
                throw new Exception("Invalid rate value. Rate must be between 0 and 100");
            }

            // Get current rate
            $current_rate_stmt = $conn->prepare("SELECT rate, name FROM tax_rates WHERE id = ?");
            $current_rate_stmt->bind_param("i", $id);
            $current_rate_stmt->execute();
            $current_result = $current_rate_stmt->get_result();

            if ($current_result->num_rows === 0) {
                throw new Exception("Tax rate ID not found");
            }

            $current_data = $current_result->fetch_assoc();
            $old_rate = $current_data['rate'];
            $tax_name = $current_data['name'];

            // Only update if the rate has changed
            if ($old_rate != $rate) {
                // Update tax rate
                $update_stmt = $conn->prepare("
                    UPDATE tax_rates 
                    SET rate = ?, 
                        last_updated = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $update_stmt->bind_param("di", $rate, $id);

                if (!$update_stmt->execute()) {
                    throw new Exception("Error updating {$tax_name} rate");
                }

                // Record in history
                $history_stmt = $conn->prepare("
                    INSERT INTO tax_rate_history 
                    (tax_rate_id, old_rate, new_rate, changed_by) 
                    VALUES (?, ?, ?, ?)
                ");
                $admin_username = $_SESSION['username'];
                $history_stmt->bind_param("idds", $id, $old_rate, $rate, $admin_username);

                if (!$history_stmt->execute()) {
                    throw new Exception("Error recording rate change history");
                }

                // Log the change
                $log_message = "Tax rate changed for {$tax_name}: {$old_rate}% to {$rate}%";
                error_log($log_message);
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Tax rates updated successfully'
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();

        error_log("Error updating tax rates: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Rate data not provided.'
    ]);
}

// Close database connection
$conn->close();
?>