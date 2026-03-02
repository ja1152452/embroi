<?php
session_start();
require_once "config/database.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get address ID
    $address_id = filter_input(INPUT_POST, 'address_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION["user_id"];
    
    if (empty($address_id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid address ID']);
        exit;
    }
    
    // Check if address exists and belongs to the user
    $sql = "SELECT is_default FROM addresses WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Address not found or does not belong to you']);
        exit;
    }
    
    $address = mysqli_fetch_assoc($result);
    $is_default = $address['is_default'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete the address
        $sql = "DELETE FROM addresses WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting address: " . mysqli_stmt_error($stmt));
        }
        
        // If the deleted address was the default, set a new default
        if ($is_default) {
            // Find the oldest address to set as default
            $sql = "SELECT id FROM addresses WHERE user_id = ? ORDER BY created_at ASC LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $new_default = mysqli_fetch_assoc($result);
                $new_default_id = $new_default['id'];
                
                // Set the new default
                $sql = "UPDATE addresses SET is_default = 1 WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $new_default_id);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error setting new default address: " . mysqli_stmt_error($stmt));
                }
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode(['success' => true, 'message' => 'Address deleted successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
