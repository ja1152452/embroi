<?php
require_once "config/database.php";

header('Content-Type: application/json');

if (!isset($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$query = trim($_GET['q']);
$query = "%{$query}%";

$sql = "SELECT id, name, price, image FROM products 
        WHERE name LIKE ? OR description LIKE ? 
        AND stock > 0 
        ORDER BY name 
        LIMIT 5";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $query, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products); 