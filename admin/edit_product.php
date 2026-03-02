<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

$error = '';
$success = '';

// Check if product ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: products.php");
    exit;
}

$product_id = $_GET['id'];

// Fetch product data
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    header("location: products.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

// Fetch categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $sizes = isset($_POST['sizes']) ? implode(',', $_POST['sizes']) : '';
    $current_image = $product['image'];

    // Handle image upload
    $image = $current_image; // Default to current image
    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../uploads/products/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . time() . '_' . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Check file size (5MB max)
            if ($_FILES["image"]["size"] <= 5000000) {
                // Allow certain file formats
                if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg") {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image = str_replace('../', '', $target_file);

                        // Delete old image if it exists and is not the default image
                        if(!empty($current_image) && file_exists("../" . $current_image)) {
                            unlink("../" . $current_image);
                        }
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $error = "Sorry, only JPG, JPEG & PNG files are allowed.";
                }
            } else {
                $error = "Sorry, your file is too large.";
            }
        } else {
            $error = "File is not an image.";
        }
    }

    if(empty($error)) {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?, sizes = ? WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssdiissi", $name, $description, $price, $stock, $category_id, $image, $sizes, $product_id);

            if(mysqli_stmt_execute($stmt)) {
                $success = "Product updated successfully!";

                // Refresh product data
                $sql = "SELECT * FROM products WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $product_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $product = mysqli_fetch_assoc($result);
            } else {
                $error = "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Aling Hera's Embroidery</title>
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
                    <h1 class="h2">Edit Product</h1>
                </div>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if(!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo $success; ?>
                        <div class="mt-2">
                            <a href="products.php" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-arrow-left me-1"></i> Back to Products
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form id="editProductForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $product_id; ?>" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php
                                            mysqli_data_seek($categories, 0);
                                            while($category = mysqli_fetch_assoc($categories)):
                                            ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                                    <?php echo $category['name']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stock</label>
                                        <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Available Sizes</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php
                                            $available_sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Free Size'];
                                            $product_sizes = isset($product['sizes']) ? explode(',', $product['sizes']) : [];

                                            foreach($available_sizes as $size):
                                            ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="sizes[]" value="<?php echo $size; ?>" id="size-<?php echo $size; ?>"
                                                        <?php echo in_array($size, $product_sizes) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="size-<?php echo $size; ?>">
                                                        <?php echo $size; ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <small class="form-text text-muted">Select all applicable sizes</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Product Image</label>
                                        <?php if(!empty($product['image'])): ?>
                                            <div class="mb-2">
                                                <div class="card">
                                                    <div class="card-body p-2 text-center">
                                                        <img src="../<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-thumbnail" style="max-height: 150px;">
                                                        <p class="mt-2 mb-0 small text-muted">Current Image</p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                        <small class="form-text text-muted">Leave empty to keep current image</small>
                                        <div id="imagePreview" class="mt-2" style="display: none;">
                                            <div class="card">
                                                <div class="card-body p-2 text-center">
                                                    <img id="preview" src="#" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                                                    <p class="mt-2 mb-0 small text-muted">New Image Preview</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Update Product</button>
                                <a href="products.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout-confirm.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const imagePreview = document.getElementById('imagePreview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '#';
                imagePreview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
