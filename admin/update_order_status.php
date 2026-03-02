<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $status = trim($_POST['status']);
    
    // Validate required fields
    if (empty($order_id) || empty($status)) {
        $_SESSION['error_message'] = "Order ID and status are required.";
        header("location: orders.php");
        exit;
    }
    
    // Validate status
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error_message'] = "Invalid status.";
        header("location: order_details.php?id=" . $order_id);
        exit;
    }
    
    // Update order status
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Order status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating order status: " . mysqli_error($conn);
    }
    
    // Redirect back to order details page
    header("location: order_details.php?id=" . $order_id);
    exit;
} else {
    // Not a POST request, redirect to orders page
    header("location: orders.php");
    exit;
}
?>
