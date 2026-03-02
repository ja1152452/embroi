<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

$message = '';

// Check if the 'sizes' column exists in the products table
$result = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'sizes'");
$exists = mysqli_num_rows($result) > 0;

if (!$exists) {
    // Add the 'sizes' column to the products table
    $sql = "ALTER TABLE products ADD COLUMN sizes VARCHAR(255) DEFAULT NULL AFTER image";
    if (mysqli_query($conn, $sql)) {
        $message = "Database updated successfully. The 'sizes' column has been added to the products table.";
    } else {
        $message = "Error updating database: " . mysqli_error($conn);
    }
} else {
    $message = "The 'sizes' column already exists in the products table.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-9 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Update Database</h1>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?>">
                            <?php echo $message; ?>
                        </div>
                        <div class="mt-3">
                            <a href="products.php" class="btn btn-primary">Back to Products</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout-confirm.js"></script>
</body>
</html>
