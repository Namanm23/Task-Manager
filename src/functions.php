<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask(string $task_name): bool {
    $file = __DIR__ . '/tasks.txt';
    
    // Read existing tasks
    $tasks = [];
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $tasks = json_decode($content, true) ?: [];
    }
    
    // Check for duplicates
    $normalized_name = strtolower(trim($task_name));
    foreach ($tasks as $task) {
        if (strtolower(trim($task['name'])) === $normalized_name) {
            return false;
        }
    }
    
    // Add new task
    $tasks[] = [
        'id' => uniqid(),
        'name' => trim($task_name),
        'completed' => false
    ];
    
    // Save tasks
    return file_put_contents($file, json_encode($tasks)) !== false;
}

/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks(): array {
    $file = __DIR__ . '/tasks.txt';
    
    if (!file_exists($file)) {
        return [];
    }
    
    $content = file_get_contents($file);
    $tasks = json_decode($content, true) ?: [];
    
    // Ensure consistent format
    return array_map(function($task) {
        return [
            'id' => $task['id'] ?? '',
            'name' => $task['name'] ?? '',
            'completed' => $task['completed'] ?? false
        ];
    }, $tasks);
}

/**
 * Marks a task as completed or uncompleted
 * 
 * @param string  $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted(string $task_id, bool $is_completed): bool {
    $file = __DIR__ . '/tasks.txt';
    
    // Read tasks
    $tasks = getAllTasks();
    $found = false;
    
    // Update task status
    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
            $found = true;
            break;
        }
    }
    
    // Save if found
    return $found && file_put_contents($file, json_encode($tasks)) !== false;
}

/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask(string $task_id): bool {
    $file = __DIR__ . '/tasks.txt';
    
    // Read tasks
    $tasks = getAllTasks();
    $initial_count = count($tasks);
    
    // Filter out the task to delete
    $tasks = array_filter($tasks, function($task) use ($task_id) {
        return $task['id'] !== $task_id;
    });
    
    // Save if changed
    if (count($tasks) < $initial_count) {
        return file_put_contents($file, json_encode(array_values($tasks))) !== false;
    }
    
    return false;
}

/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode(): string {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail(string $email): bool {
    $file = __DIR__ . '/pending_subscriptions.txt';
    
    // Normalize email
    $email = strtolower(trim($email));
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: $email");
        return false;
    }
    
    // Check existing subscribers
    $subscribers_file = __DIR__ . '/subscribers.txt';
    $subscribers = [];
    if (file_exists($subscribers_file)) {
        $subscribers = json_decode(file_get_contents($subscribers_file), true) ?: [];
        if (in_array($email, $subscribers)) {
            error_log("Email already subscribed: $email");
            return true; // Already subscribed
        }
    }
    
    // Read pending subscriptions
    $pending = [];
    if (file_exists($file)) {
        $pending = json_decode(file_get_contents($file), true) ?: [];
    }
    
    // Generate verification code
    $code = generateVerificationCode();
    
    // Add to pending
    $pending[$email] = [
        'code' => $code,
        'timestamp' => time()
    ];
    
    // Save pending - check for write errors
    if (file_put_contents($file, json_encode($pending)) === false) {
        error_log("Failed to write to pending_subscriptions.txt");
        return false;
    }
    
    // Build verification link
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $base_url = $protocol . '://' . $host . $path;
    $verification_link = $base_url . "/verify.php?email=" . urlencode($email) . "&code=" . $code;
    
    $subject = "Verify subscription to Task Planner";
    $message = '<p>Click the link below to verify your subscription to Task Planner:</p>';
    $message .= '<p><a id="verification-link" href="' . $verification_link . '">Verify Subscription</a></p>';
    
    $headers = "From: no-reply@taskplanner.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Test email sending capability
    if (!function_exists('mail')) {
        error_log("mail() function not available");
        return false;
    }
    
    // Try to send email
    $result = mail($email, $subject, $message, $headers);
    
    if (!$result) {
        // Log detailed error information
        $last_error = error_get_last();
        error_log("Failed to send verification email to $email");
        error_log("Mail error: " . print_r($last_error, true));
        error_log("Mail headers: $headers");
        error_log("Mail subject: $subject");
        error_log("Verification link: $verification_link");
        
        // For development: Display the link
        if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
            session_start();
            $_SESSION['verification_link'] = $verification_link;
        }
    }
    
    return $result;
}

/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription(string $email, string $code): bool {
    $pending_file = __DIR__ . '/pending_subscriptions.txt';
    $subscribers_file = __DIR__ . '/subscribers.txt';
    
    // Normalize email
    $email = strtolower(trim($email));
    
    // Read pending subscriptions
    $pending = [];
    if (file_exists($pending_file)) {
        $pending = json_decode(file_get_contents($pending_file), true) ?: [];
    }
    
    // Check if exists and code matches
    if (!isset($pending[$email]) || $pending[$email]['code'] !== $code) {
        error_log("Verification failed for $email with code $code");
        return false;
    }
    
    // Check if verification code expired (24 hours)
    if (time() - $pending[$email]['timestamp'] > 86400) {
        unset($pending[$email]);
        file_put_contents($pending_file, json_encode($pending));
        error_log("Verification code expired for $email");
        return false;
    }
    
    // Remove from pending
    unset($pending[$email]);
    file_put_contents($pending_file, json_encode($pending));
    
    // Add to subscribers
    $subscribers = [];
    if (file_exists($subscribers_file)) {
        $subscribers = json_decode(file_get_contents($subscribers_file), true) ?: [];
    }
    
    // Add if not already exists
    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
        return file_put_contents($subscribers_file, json_encode($subscribers)) !== false;
    }
    
    return true;
}

/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail(string $email): bool {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    
    // Normalize email
    $email = strtolower(trim($email));
    
    // Read subscribers
    if (!file_exists($subscribers_file)) {
        return false;
    }
    
    $subscribers = json_decode(file_get_contents($subscribers_file), true) ?: [];
    
    // Find and remove email
    $index = array_search($email, $subscribers);
    if ($index !== false) {
        unset($subscribers[$index]);
        $subscribers = array_values($subscribers); // Reindex array
        return file_put_contents($subscribers_file, json_encode($subscribers)) !== false;
    }
    
    return false;
}

/**
 * Sends task reminders to all subscribers
 * Internally calls sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    
    // Get subscribers
    if (!file_exists($subscribers_file)) {
        return;
    }
    
    $subscribers = json_decode(file_get_contents($subscribers_file), true) ?: [];
    if (empty($subscribers)) {
        return;
    }
    
    // Get pending tasks
    $tasks = getAllTasks();
    $pending_tasks = array_filter($tasks, function($task) {
        return !$task['completed'];
    });
    
    if (empty($pending_tasks)) {
        error_log("No pending tasks to send in reminders");
        return;
    }
    
    // Send emails
    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pending_tasks);
    }
}

/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail(string $email, array $pending_tasks): bool {
    $subject = 'Task Planner - Pending Tasks Reminder';
    
    // Build email body
    $message = '<html><body>';
    $message .= '<h2>Pending Tasks Reminder</h2>';
    $message .= '<p>Here are the current pending tasks:</p>';
    $message .= '<ul>';
    
    foreach ($pending_tasks as $task) {
        $message .= '<li>' . htmlspecialchars($task['name']) . '</li>';
    }
    
    $message .= '</ul>';
    
    // Add unsubscribe link
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $base_url = $protocol . '://' . $host . $path;
    $unsubscribe_link = $base_url . "/unsubscribe.php?email=" . urlencode($email);
    
    $message .= '<p><a id="unsubscribe-link" href="' . $unsubscribe_link . '">Unsubscribe from notifications</a></p>';
    $message .= '</body></html>';
    
    // Set headers
    $headers = "From: no-reply@taskplanner.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Try to send email
    $result = mail($email, $subject, $message, $headers);
    
    if (!$result) {
        // Log detailed error information
        $last_error = error_get_last();
        error_log("Failed to send task reminder to $email");
        error_log("Mail error: " . print_r($last_error, true));
    }
    
    return $result;
}