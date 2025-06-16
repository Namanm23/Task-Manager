<?php
require_once 'functions.php';

// Log execution
file_put_contents('cron.log', date('Y-m-d H:i:s') . " - Cron job started\n", FILE_APPEND);

try {
    $sent_count = 0;
    $subscribers_file = __DIR__ . '/subscribers.txt';
    
    // Get subscribers
    if (!file_exists($subscribers_file)) {
        file_put_contents('cron.log', date('Y-m-d H:i:s') . " - No subscribers found\n", FILE_APPEND);
        exit(0);
    }
    
    $subscribers = json_decode(file_get_contents($subscribers_file), true) ?: [];
    if (empty($subscribers)) {
        file_put_contents('cron.log', date('Y-m-d H:i:s') . " - No subscribers to notify\n", FILE_APPEND);
        exit(0);
    }

    // Get tasks
    $tasks = getAllTasks();
    $pending_tasks = array_filter($tasks, function($task) {
        return !$task['completed'];
    });

    if (empty($pending_tasks)) {
        file_put_contents('cron.log', date('Y-m-d H:i:s') . " - No pending tasks\n", FILE_APPEND);
        exit(0);
    }

    // Send emails
    foreach ($subscribers as $email) {
        if (sendTaskEmail($email, $pending_tasks)) {
            $sent_count++;
        }
    }
    
    file_put_contents('cron.log', date('Y-m-d H:i:s') . " - Sent $sent_count reminders\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents('cron.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    exit(1); // Return non-zero status for error
}

exit(0); // Success