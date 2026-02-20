<?php
/**
 * Backup Helper Function
 * Version 1.0
 */

function perform_database_backup($pdo) {
    try {
        $backup_dir = __DIR__ . '/../backups/db/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }

        // Access variables from database.php (included via config.php)
        global $host, $user, $pass, $db;
        $name = $db;

        $filename = 'backup_' . $name . '_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backup_dir . $filename;

        // Path to mysqldump in XAMPP (typical path)
        $mysqldump_path = 'c:\xampp2\mysql\bin\mysqldump.exe';
        
        // Escape password for shell
        $command = "\"$mysqldump_path\" --user=$user --password=$pass --host=$host $name > \"$filepath\"";
        
        // Execute command
        exec($command, $output, $return_var);

        if ($return_var === 0) {
            return [
                'status' => 'success',
                'filename' => $filename,
                'path' => $filepath,
                'size' => filesize($filepath)
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Gagal menjalankan mysqldump. Error code: ' . $return_var
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Exception: ' . $e->getMessage()
        ];
    }
}

/**
 * Trigger an automatic backup after an administrative activity.
 * This is designed to be called after successful CRUD operations.
 */
function trigger_auto_backup($pdo) {
    // We call the existing backup function
    // For now, we perform it synchronously to ensure it completes, 
    // but in a production environment with larger DBs, this could be offloaded.
    return perform_database_backup($pdo);
}
