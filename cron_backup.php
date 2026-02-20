<?php
/**
 * Cron Backup script
 * To be run by Windows Task Scheduler
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/backup_helper.php';

// Log start
$log_msg = "[" . date('Y-m-d H:i:s') . "] Starting automated backup...\n";

// Use a simple pdo for logging if needed, or just standard output
$result = perform_database_backup($pdo);

if ($result['status'] === 'success') {
    $log_msg .= "[" . date('Y-m-d H:i:s') . "] Success: " . $result['filename'] . " (" . round($result['size']/1024, 2) . " KB)\n";
} else {
    $log_msg .= "[" . date('Y-m-d H:i:s') . "] Error: " . $result['message'] . "\n";
}

// Write to a log file in backups dir
file_put_contents(__DIR__ . '/backups/backup_log.txt', $log_msg, FILE_APPEND);

echo $log_msg;
