<?php
session_start();
require_once "config/database.php";

// Check if user is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: login.php");
    exit;
}

$success = false;
$error = false;
$message = "";

// Check if the size column exists in order_items table
$checkColumnSql = "SHOW COLUMNS FROM order_items LIKE 'size'";
$result = mysqli_query($conn, $checkColumnSql);
$columnExists = mysqli_num_rows($result) > 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$columnExists) {
        // Add size column to order_items table
        $alterTableSql = "ALTER TABLE order_items ADD COLUMN size VARCHAR(20) DEFAULT NULL AFTER quantity";
        
        if (mysqli_query($conn, $alterTableSql)) {
            $success = true;
            $message = "Size column added to order_items table successfully!";
        } else {
            $error = true;
            $message = "Error adding size column: " . mysqli_error($conn);
        }
    } else {
        $success = true;
        $message = "Size column already exists in order_items table.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Items Table - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin/sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Update Order Items Table</h1>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo $message; ?>
                        <div class="mt-2">
                            <a href="admin/index.php" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Database Update</h5>
                        <p>This tool will update the order_items table to include a size column for products.</p>
                        
                        <?php if(!$columnExists): ?>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    The size column does not exist in the order_items table. Click the button below to add it.
                                </div>
                                <button type="submit" class="btn btn-primary">Update Database</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                The size column already exists in the order_items table. No action needed.
                            </div>
                            <a href="admin/index.php" class="btn btn-primary">Back to Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/logout-confirm.js"></script>
</body>
</html>
