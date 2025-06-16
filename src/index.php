<?php
require_once 'functions.php';

// Start session for debug info
session_start();

// Initialize variables
$task_message = '';
$email_message = '';
$tasks = getAllTasks();

// Handle task form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task-name'])) {
    $task_name = trim($_POST['task-name']);
    if (!empty($task_name)) {
        if (addTask($task_name)) {
            $task_message = 'Task added successfully!';
            $tasks = getAllTasks(); // Refresh tasks
        } else {
            $task_message = 'Task already exists!';
        }
    }
}

// Handle email form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (!empty($email)) {
        if (subscribeEmail($email)) {
            $email_message = 'Verification email sent!';
        } else {
            $email_message = 'Error subscribing email. Please try again.';
        }
    }
}

// Handle task actions (complete/incomplete/delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $action = $_GET['action'];
    
    switch ($action) {
        case 'complete':
            markTaskAsCompleted($task_id, true);
            break;
        case 'incomplete':
            markTaskAsCompleted($task_id, false);
            break;
        case 'delete':
            deleteTask($task_id);
            break;
    }
    
    // Refresh tasks after action
    $tasks = getAllTasks();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        header {
            background: linear-gradient(to right, #4b6cb7, #182848);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-group {
            display: flex;
            margin-bottom: 15px;
        }
        input[type="text"], input[type="email"] {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px 0 0 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus {
            border-color: #4b6cb7;
            outline: none;
        }
        button {
            padding: 12px 20px;
            background: #4b6cb7;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }
        button:hover {
            background: #3a5ca9;
        }
        #submit-email {
            border-radius: 8px;
            margin-top: 10px;
            width: 100%;
            background: #27ae60;
        }
        #submit-email:hover {
            background: #219653;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        .tasks-list {
            list-style: none;
        }
        .task-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .task-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .task-item.completed {
            background: #e8f5e9;
        }
        .task-item.completed .task-name {
            text-decoration: line-through;
            color: #7b8a8b;
        }
        .task-status {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            cursor: pointer;
        }
        .task-name {
            flex: 1;
            font-size: 17px;
        }
        .delete-task {
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        .delete-task:hover {
            background: #c0392b;
        }
        .email-form {
            background: #f1f8ff;
            border-radius: 10px;
            padding: 20px;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            text-align: center;
        }
        .stat-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            flex: 1;
            margin: 0 10px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
        }
        footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
        @media (max-width: 600px) {
            .form-group {
                flex-direction: column;
            }
            input[type="text"], input[type="email"] {
                border-radius: 8px;
                margin-bottom: 10px;
            }
            button {
                border-radius: 8px;
            }
            .stats {
                flex-direction: column;
            }
            .stat-box {
                margin: 10px 0;
            }
        }
        .debug-info {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .debug-info h3 {
            margin-top: 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Task Management System</h1>
            <p class="subtitle">Organize your tasks and get hourly reminders</p>
        </header>
        
        <div class="content">
            <div class="section">
                <h2>Add New Task</h2>
                <?php if ($task_message): ?>
                    <div class="message success"><?= htmlspecialchars($task_message) ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
                        <button type="submit" id="add-task">Add Task</button>
                    </div>
                </form>
            </div>
            
            <div class="section">
                <h2>Your Tasks</h2>
                <?php if (empty($tasks)): ?>
                    <p>No tasks found. Add your first task above!</p>
                <?php else: ?>
                    <ul id="tasks-list" class="tasks-list">
                        <?php foreach ($tasks as $task): ?>
                            <li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
                                <input type="checkbox" class="task-status" 
                                    <?= $task['completed'] ? 'checked' : '' ?>
                                    onclick="window.location='index.php?action=<?= $task['completed'] ? 'incomplete' : 'complete' ?>&id=<?= $task['id'] ?>'">
                                <span class="task-name"><?= htmlspecialchars($task['name']) ?></span>
                                <button class="delete-task" onclick="window.location='index.php?action=delete&id=<?= $task['id'] ?>'">Delete</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <div class="section email-form">
                <h2>Email Reminders</h2>
                <p>Subscribe to receive hourly email reminders for pending tasks</p>
                
                <?php if ($email_message): ?>
                    <div class="message success"><?= htmlspecialchars($email_message) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="email" name="email" required placeholder="Enter your email" style="width: 100%; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; border: 2px solid #ddd;">
                    <button type="submit" id="submit-email">Subscribe to Hourly Reminders</button>
                </form>
            </div>
            
            <?php if (isset($_SESSION['verification_link'])): ?>
                <div class="section debug-info">
                    <h3>Email Debug Information (Development Only)</h3>
                    <p>Email sending failed. Verification link:</p>
                    <p><a href="<?= $_SESSION['verification_link'] ?>"><?= $_SESSION['verification_link'] ?></a></p>
                    <?php unset($_SESSION['verification_link']); ?>
                </div>
            <?php endif; ?>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-value"><?= count($tasks) ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= count(array_filter($tasks, function($t) { return !$t['completed']; })) ?></div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= count(array_filter($tasks, function($t) { return $t['completed']; })) ?></div>
                    <div class="stat-label">Completed Tasks</div>
                </div>
            </div>
        </div>
        
        <footer>
            <p>Task Management System &copy; <?= date('Y') ?> | All tasks are stored locally</p>
        </footer>
    </div>
</body>
</html>