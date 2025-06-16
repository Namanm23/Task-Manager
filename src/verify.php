<?php
require_once 'functions.php';

$message = '';
$is_success = false;
$email = $_GET['email'] ?? '';
$code = $_GET['code'] ?? '';

// Process verification if email and code are provided
if (!empty($email) && !empty($code)) {
    try {
        if (verifySubscription($email, $code)) {
            $message = "Your email has been successfully verified! You will now receive hourly task reminders.";
            $is_success = true;
        } else {
            $message = "Invalid verification link. The code may have expired or doesn't match our records.";
        }
    } catch (Exception $e) {
        $message = "Error processing your request: " . $e->getMessage();
    }
} else {
    $message = "Missing required parameters for verification.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Verification</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .container {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(to right, #3498db, #2c3e50);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        #verification-heading {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 28px;
            position: relative;
        }
        #verification-heading:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #3498db;
            margin: 15px auto;
            border-radius: 2px;
        }
        .message {
            padding: 25px;
            margin: 25px 0;
            border-radius: 10px;
            font-size: 18px;
            line-height: 1.6;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.3);
        }
        .home-link:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(52, 152, 219, 0.4);
        }
        .email-display {
            font-weight: bold;
            color: #2980b9;
            word-break: break-all;
            margin-top: 15px;
            display: inline-block;
            padding: 8px 15px;
            background: #eaf2f8;
            border-radius: 6px;
        }
        .icon {
            font-size: 72px;
            margin-bottom: 25px;
            color: <?= $is_success ? '#27ae60' : '#e74c3c' ?>;
            text-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .animation {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
        .steps {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
        }
        .step {
            flex: 1;
            padding: 15px;
            text-align: center;
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .step-text {
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Task Management System</h1>
            <p>Email Verification Process</p>
        </div>
        
        <div class="content">
            <h2 id="verification-heading">Subscription Verification</h2>
            
            <div class="icon <?= $is_success ? 'animation' : '' ?>">
                <?= $is_success ? '✓' : '✗' ?>
            </div>
            
            <div class="message <?= $is_success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
                <?php if (!empty($email)): ?>
                    <p>Email: <span class="email-display"><?= htmlspecialchars($email) ?></span></p>
                <?php endif; ?>
            </div>
            
            <a href="index.php" class="home-link">Go to Task Manager</a>
            
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-text">Subscribe</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">Verify Email</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">Get Reminders</div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>You will receive hourly reminders for pending tasks</p>
            <p>Task Management System &copy; <?= date('Y') ?></p>
        </div>
    </div>
</body>
</html>