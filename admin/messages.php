<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Handle message deletion
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM contact_messages WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $_SESSION['success_message'] = "Message deleted successfully.";
    }
    header("location: messages.php");
    exit;
}

// Handle mark as read/unread
if(isset($_GET['mark_read'])) {
    $id = $_GET['mark_read'];
    $sql = "UPDATE contact_messages SET is_read = 1 WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $_SESSION['success_message'] = "Message marked as read.";
    }
    header("location: messages.php");
    exit;
}

if(isset($_GET['mark_unread'])) {
    $id = $_GET['mark_unread'];
    $sql = "UPDATE contact_messages SET is_read = 0 WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $_SESSION['success_message'] = "Message marked as unread.";
    }
    header("location: messages.php");
    exit;
}

// Check if contact_messages table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'contact_messages'");
$table_exists = mysqli_num_rows($table_check) > 0;

// If table doesn't exist, create it
if (!$table_exists) {
    $create_table = "CREATE TABLE contact_messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $create_table);
}

// Get all messages
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Count unread messages
$unread_count = 0;
foreach($messages as $msg) {
    if($msg['is_read'] == 0) {
        $unread_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .message-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .message-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .unread {
            border-left: 4px solid var(--admin-secondary);
            background-color: rgba(231, 76, 60, 0.05);
        }
        .message-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .message-preview {
            color: #6c757d;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .badge-unread {
            background-color: var(--admin-secondary);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-9 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Messages <?php if($unread_count > 0): ?><span class="badge bg-danger"><?php echo $unread_count; ?> unread</span><?php endif; ?></h1>
                </div>

                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if(empty($messages)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i> No messages found. Messages sent through the contact form will appear here.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach($messages as $message): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card message-card <?php echo $message['is_read'] == 0 ? 'unread' : ''; ?>" onclick="window.location.href='message_view.php?id=<?php echo $message['id']; ?>'">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($message['name']); ?></h5>
                                            <?php if($message['is_read'] == 0): ?>
                                                <span class="badge badge-unread">Unread</span>
                                            <?php endif; ?>
                                        </div>
                                        <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($message['email']); ?></h6>
                                        <p class="card-text mb-2"><strong><?php echo htmlspecialchars($message['subject']); ?></strong></p>
                                        <p class="card-text message-preview"><?php echo htmlspecialchars($message['message']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <span class="message-date"><?php echo date('M d, Y g:i A', strtotime($message['created_at'])); ?></span>
                                            <div class="btn-group">
                                                <?php if($message['is_read'] == 0): ?>
                                                    <a href="messages.php?mark_read=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-primary" title="Mark as Read">
                                                        <i class="bi bi-envelope-open"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="messages.php?mark_unread=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Mark as Unread">
                                                        <i class="bi bi-envelope"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="messages.php?delete=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this message?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout-confirm.js"></script>
</body>
</html>
