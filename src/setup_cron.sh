#!/bin/bash

# CRON Setup Script for Task Management System
# This script configures the CRON job to run hourly reminders

# Get absolute path to script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Path to the cron.php file
CRON_PATH="${SCRIPT_DIR}/cron.php"

# Path to log file
LOG_PATH="${SCRIPT_DIR}/cron.log"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed or not found in PATH"
    echo "Please install PHP and try again"
    exit 1
fi

# Check if cron.php exists
if [ ! -f "$CRON_PATH" ]; then
    echo "ERROR: cron.php not found at ${CRON_PATH}"
    echo "Make sure this script is in the same directory as cron.php"
    exit 1
fi

# Create log file if it doesn't exist
touch "$LOG_PATH"
chmod 666 "$LOG_PATH"  # Make writable by web server

# Create the cron job command
CRON_JOB="0 * * * * /usr/bin/php ${CRON_PATH} >> ${LOG_PATH} 2>&1"

# Check if cron job already exists
if crontab -l | grep -qF "$CRON_PATH"; then
    echo "CRON job is already set up:"
    crontab -l | grep "$CRON_PATH"
    echo "No changes made."
    exit 0
fi

# Add to crontab
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

# Verify installation
if crontab -l | grep -qF "$CRON_PATH"; then
    echo "CRON job successfully installed:"
    echo "--------------------------------"
    crontab -l | grep "$CRON_PATH"
    echo "--------------------------------"
    echo "This job will run every hour at minute 0"
    echo "Output will be logged to: ${LOG_PATH}"
    exit 0
else
    echo "ERROR: Failed to install CRON job"
    exit 1
fi