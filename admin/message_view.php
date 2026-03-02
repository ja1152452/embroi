<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Check if message ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    $_SESSION['error_message'] = "Message ID is required.";
    header("location: messages.php");
    exit;
}

$message_id = $_GET['id'];

// Get message details
$sql = "SELECT * FROM contact_messages WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $message_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    $_SESSION['error_message'] = "Message not found.";
    header("location: messages.php");
    exit;
}

$message = mysqli_fetch_assoc($result);

// Mark message as read if it's unread
if($message['is_read'] == 0){
    $update_sql = "UPDATE contact_messages SET is_read = 1 WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "i", $message_id);
    mysqli_stmt_execute($update_stmt);
    $message['is_read'] = 1;
}

// Handle reply form submission
$reply_sent = false;
$reply_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_email'])) {
    $to = $_POST['reply_email'];
    $subject = "Re: " . $_POST['reply_subject'];
    $reply_message = $_POST['reply_message'];
    $headers = "From: noreply@alingherasembroidery.com\r\n";
    $headers .= "Reply-To: noreply@alingherasembroidery.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // In a real application, you would use a proper email sending library
    // For now, we'll just show a success message
    $reply_sent = true;

    // Optionally, you could log the reply in the database
    // $log_reply_sql = "UPDATE contact_messages SET reply = ?, replied_at = NOW() WHERE id = ?";
    // $log_reply_stmt = mysqli_prepare($conn, $log_reply_sql);
    // mysqli_stmt_bind_param($log_reply_stmt, "si", $reply_message, $message_id);
    // mysqli_stmt_execute($log_reply_stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .message-container {
            background-color: #fff;
            border-radius: var(--admin-border-radius);
            box-shadow: var(--admin-box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .message-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .message-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .message-body {
            white-space: pre-line;
            margin-bottom: 2rem;
        }
        .reply-form {
            background-color: #f8f9fa;
            border-radius: var(--admin-border-radius);
            padding: 1.5rem;
            margin-top: 2rem;
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
                    <h1 class="h2">View Message</h1>
                    <div>
                        <?php if($message['is_read'] == 1): ?>
                            <a href="messages.php?mark_unread=<?php echo $message['id']; ?>" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-envelope"></i> Mark as Unread
                            </a>
                        <?php else: ?>
                            <a href="messages.php?mark_read=<?php echo $message['id']; ?>" class="btn btn-outline-primary me-2">
                                <i class="bi bi-envelope-open"></i> Mark as Read
                            </a>
                        <?php endif; ?>
                        <a href="messages.php?delete=<?php echo $message['id']; ?>" class="btn btn-outline-danger me-2" onclick="return confirm('Are you sure you want to delete this message?');">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                        <a href="messages.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Messages
                        </a>
                    </div>
                </div>

                <?php if($reply_sent): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i> Your reply has been sent successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(!empty($reply_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i> <?php echo $reply_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="message-container">
                    <div class="message-header">
                        <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                        <div class="message-meta">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?> (<?php echo htmlspecialchars($message['email']); ?>)</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p><strong>Received:</strong> <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="message-body">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>

                    <div class="reply-form">
                        <h4 class="mb-3"><i class="bi bi-reply me-2"></i>Reply to this message</h4>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $message_id); ?>">
                            <input type="hidden" name="reply_email" value="<?php echo htmlspecialchars($message['email']); ?>">
                            <input type="hidden" name="reply_subject" value="<?php echo htmlspecialchars($message['subject']); ?>">

                            <div class="mb-3">
                                <label for="reply_message" class="form-label">Your Reply</label>
                                <textarea class="form-control" id="reply_message" name="reply_message" rows="5" required></textarea>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout-confirm.js"></script>
</body>
</html>
