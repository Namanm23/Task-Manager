<?php
require_once 'functions.php';

$message = '';
$is_success = false;
$email = $_GET['email'] ?? '';

// Process unsubscription if email is provided
if (!empty($email)) {
    try {
        if (unsubscribeEmail($email)) {
            $message = "You have been successfully unsubscribed from task reminders.";
            $is_success = true;
        } else {
            $message = "Email not found in our subscription list or already unsubscribed.";
        }
    } catch (Exception $e) {
        $message = "Error processing your request: " . $e->getMessage();
    }
} else {
    $message = "No email address provided for unsubscription.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe from Task Updates</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(to right, #4b6cb7, #182848);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        #unsubscription-heading {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 28px;
        }
        .message {
            padding: 20px;
            margin: 25px 0;
            border-radius: 10px;
            font-size: 18px;
            line-height: 1.6;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .home-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #4b6cb7;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .home-link:hover {
            background: #3a5ca9;
        }
        .email-display {
            font-weight: bold;
            color: #e74c3c;
            word-break: break-all;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: <?= $is_success ? '#27ae60' : '#e74c3c' ?>;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Task Management System</h1>
            <p>Unsubscribe from notifications</p>
        </div>
        
        <div class="content">
            <h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
            
            <div class="icon">
                <?= $is_success ? '✓' : '✗' ?>
            </div>
            
            <div class="message <?= $is_success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
                <?php if (!empty($email)): ?>
                    <p>Email: <span class="email-display"><?= htmlspecialchars($email) ?></span></p>
                <?php endif; ?>
            </div>
            
            <a href="index.php" class="home-link">Return to Task Manager</a>
        </div>
        
        <div class="footer">
            <p>You will no longer receive hourly task reminders</p>
            <p>Task Management System &copy; <?= date('Y') ?></p>
        </div>
    </div>
</body>
</html>