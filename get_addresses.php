<?php
session_start();
require_once "config/database.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get user ID
$user_id = $_SESSION["user_id"];

// Check if addresses table exists
$check_table = "SHOW TABLES LIKE 'addresses'";
$table_exists = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    // Table doesn't exist, return empty array
    echo json_encode(['success' => true, 'addresses' => []]);
    exit;
}

// Get all addresses for the user
$sql = "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$addresses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $addresses[] = $row;
}

echo json_encode(['success' => true, 'addresses' => $addresses]);
?>
