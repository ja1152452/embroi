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
    // Get form data
    $address_id = filter_input(INPUT_POST, 'address_id', FILTER_VALIDATE_INT);
    $address_name = trim($_POST['address_name']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address_line1 = trim($_POST['address_line1']);
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $user_id = $_SESSION["user_id"];
    
    // Validate required fields
    if (empty($address_id) || empty($address_name) || empty($full_name) || empty($phone) || empty($address_line1) || empty($city) || empty($postal_code)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }
    
    // Verify the address belongs to the user
    $sql = "SELECT id FROM addresses WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Address not found or does not belong to you']);
        exit;
    }
    
    // Update address in database
    $sql = "UPDATE addresses SET 
            address_name = ?, 
            full_name = ?, 
            phone = ?, 
            address_line1 = ?, 
            address_line2 = ?, 
            city = ?, 
            postal_code = ? 
            WHERE id = ? AND user_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssii", $address_name, $full_name, $phone, $address_line1, $address_line2, $city, $postal_code, $address_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Address updated successfully',
            'address' => [
                'id' => $address_id,
                'address_name' => $address_name,
                'full_name' => $full_name,
                'phone' => $phone,
                'address_line1' => $address_line1,
                'address_line2' => $address_line2,
                'city' => $city,
                'postal_code' => $postal_code
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating address: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
