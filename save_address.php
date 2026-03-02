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
    $address_name = trim($_POST['address_name']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address_line1 = trim($_POST['address_line1']);
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $user_id = $_SESSION["user_id"];
    
    // Validate required fields
    if (empty($address_name) || empty($full_name) || empty($phone) || empty($address_line1) || empty($city) || empty($postal_code)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }
    
    // Check if addresses table exists, if not create it
    $check_table = "SHOW TABLES LIKE 'addresses'";
    $table_exists = mysqli_query($conn, $check_table);
    
    if (mysqli_num_rows($table_exists) == 0) {
        // Create addresses table
        $create_table = "CREATE TABLE addresses (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            address_name VARCHAR(100) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            address_line1 VARCHAR(255) NOT NULL,
            address_line2 VARCHAR(255),
            city VARCHAR(100) NOT NULL,
            postal_code VARCHAR(20) NOT NULL,
            is_default TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        if (!mysqli_query($conn, $create_table)) {
            echo json_encode(['success' => false, 'message' => 'Error creating addresses table: ' . mysqli_error($conn)]);
            exit;
        }
    }
    
    // Check if this is the first address for the user (to set as default)
    $check_addresses = "SELECT COUNT(*) as count FROM addresses WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $check_addresses);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $is_default = ($row['count'] == 0) ? 1 : 0;
    
    // Insert address into database
    $sql = "INSERT INTO addresses (user_id, address_name, full_name, phone, address_line1, address_line2, city, postal_code, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssssssi", $user_id, $address_name, $full_name, $phone, $address_line1, $address_line2, $city, $postal_code, $is_default);
    
    if (mysqli_stmt_execute($stmt)) {
        $address_id = mysqli_insert_id($conn);
        echo json_encode([
            'success' => true, 
            'message' => 'Address saved successfully',
            'address' => [
                'id' => $address_id,
                'address_name' => $address_name,
                'full_name' => $full_name,
                'phone' => $phone,
                'address_line1' => $address_line1,
                'address_line2' => $address_line2,
                'city' => $city,
                'postal_code' => $postal_code,
                'is_default' => $is_default
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving address: ' . mysqli_error($conn)]);
    }
} else {
    // Not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
