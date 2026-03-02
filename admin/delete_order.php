<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Check if order ID is provided
if(!isset($_POST['order_id']) || empty($_POST['order_id'])){
    $_SESSION['error_message'] = "Order ID is required.";
    header("location: orders.php");
    exit;
}

$order_id = intval($_POST['order_id']);

// Start transaction
mysqli_begin_transaction($conn);

try {
    // First delete related records from order_items table if it exists
    $check_table = "SHOW TABLES LIKE 'order_items'";
    $table_exists = mysqli_query($conn, $check_table);

    if (mysqli_num_rows($table_exists) > 0) {
        $sql = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
    }

    // Delete the order
    $sql = "DELETE FROM orders WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);

    if (mysqli_stmt_execute($stmt)) {
        // Commit transaction
        mysqli_commit($conn);
        $_SESSION['success_message'] = "Order deleted successfully.";
    } else {
        // Rollback transaction
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error deleting order: " . mysqli_error($conn);
    }
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);
    $_SESSION['error_message'] = "Error deleting order: " . $e->getMessage();
}

// Redirect back to appropriate page
if (isset($_POST['customer_id']) && !empty($_POST['customer_id'])) {
    // If coming from customer_orders.php, redirect back there
    header("location: customer_orders.php?id=" . intval($_POST['customer_id']));
} else {
    // Otherwise, redirect to orders.php
    header("location: orders.php");
}
exit;
?>
