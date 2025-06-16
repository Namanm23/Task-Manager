---

# 🗓️ Task Scheduler – PHP Task Management System

A pure PHP-based task planner that allows users to add and manage tasks, subscribe for email reminders, and receive hourly email notifications about pending tasks — **without using any database**.

---

## 📌 Features

### ✅ Task Management

* Add new tasks (duplicates prevented)
* Mark tasks as complete/incomplete
* Delete tasks
* All tasks stored in `tasks.txt` using **JSON** format

### 📧 Email Subscription

* Subscribe using email
* System sends a **6-digit verification code**
* Email verification link handled via `verify.php`
* Verified emails stored in `subscribers.txt`
* Unverified/pending emails in `pending_subscriptions.txt`
* One-click **unsubscribe** via link handled by `unsubscribe.php`

### ⏰ Hourly Reminder System

* Configurable CRON job runs `cron.php` every hour
* Sends reminders **only for pending tasks**
* Email contains a list of tasks and an unsubscribe link
* Emails are in **HTML format** using PHP’s native `mail()` function

---

## 🗂️ File Structure (Inside `src/`)

```bash
src/
├── cron.php                    # Sends reminder emails
├── functions.php               # Core logic and utilities
├── index.php                   # User interface
├── setup_cron.sh               # Sets up CRON job
├── subscribers.txt             # Verified subscribers
├── pending_subscriptions.txt   # Unverified emails and codes
├── tasks.txt                   # Task storage
├── unsubscribe.php             # Unsubscribe handler
└── verify.php                  # Email verification handler
```

---

## 💡 Data Format

### `tasks.txt`

```json
[
  {
    "id": "abc123",
    "name": "Buy groceries",
    "completed": false
  },
  {
    "id": "def456",
    "name": "Read book",
    "completed": true
  }
]
```

### `subscribers.txt`

```json
["user1@example.com", "user2@example.com"]
```

### `pending_subscriptions.txt`

```json
{
  "user@example.com": {
    "code": "123456",
    "timestamp": 1717694230
  }
}
```

---

## ✉️ Email Templates

### 🔐 Verification Email

* **Subject**: `Verify subscription to Task Planner`
* **HTML Body**:

```html
<p>Click the link below to verify your subscription to Task Planner:</p>
<p><a id="verification-link" href="{verification_link}">Verify Subscription</a></p>
```

### 🔔 Reminder Email

* **Subject**: `Task Planner - Pending Tasks Reminder`
* **HTML Body**:

```html
<h2>Pending Tasks Reminder</h2>
<p>Here are the current pending tasks:</p>
<ul>
  <li>Task 1</li>
  <li>Task 2</li>
</ul>
<p><a id="unsubscribe-link" href="{unsubscribe_link}">Unsubscribe from notifications</a></p>
```

---

## ✅ UI Element Guidelines

### Task Form

```html
<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
<button type="submit" id="add-task">Add Task</button>
```

### Task List

```html
<ul class="tasks-list">
  <li class="task-item completed">
    <input type="checkbox" class="task-status" checked>
    Task Name
    <button class="delete-task">Delete</button>
  </li>
</ul>
```

### Email Subscription Form

```html
<input type="email" name="email" required />
<button id="submit-email">Submit</button>
```



---

## 📸 Screenshots / Demo 


https://github.com/user-attachments/assets/88899f1c-c8f3-43d8-8a65-e84f6d44a179


---

## 📧 Contact

For issues, please open a GitHub Issue or reach out to:

**📬 Email:** [naman2392004@gmail.com](mailto:naman2392004@gmail.com)

---

Let me know if you want to add a [demo video](f), [badges](f), or [GitHub Pages documentation](f).
