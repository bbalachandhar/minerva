<?php
// Define APPPATH if not already defined (for CLI execution)
if (!defined('APPPATH')) {
    define('APPPATH', '/Applications/XAMPP/xamppfiles/htdocs/minerva/application/');
}

// Load CodeIgniter's config file to get session settings
$config = [];
require APPPATH . 'config/config.php';

$sessionPath = $config['sess_save_path'];
$sessionExpiration = $config['sess_expiration'];

echo "Starting session cleanup...\n";
echo "Session path: " . $sessionPath . "\n";
echo "Session expiration: " . $sessionExpiration . " seconds\n";

if (!is_dir($sessionPath)) {
    echo "Error: Session save path does not exist or is not a directory.\n";
    exit(1);
}

$filesDeleted = 0;
$errors = [];

if ($handle = opendir($sessionPath)) {
    while (false !== ($file = readdir($handle))) {
        // Skip current and parent directory entries
        if ($file === "." || $file === "..") {
            continue;
        }

        $filePath = $sessionPath . '/' . $file;

        // Ensure it's a file and not a directory
        if (is_file($filePath)) {
            $filemtime = filemtime($filePath);
            if ($filemtime === false) {
                $errors[] = "Could not get modification time for " . $filePath;
                continue;
            }

            // Check if file is older than sessionExpiration
            if ((time() - $filemtime) > $sessionExpiration) {
                if (unlink($filePath)) {
                    $filesDeleted++;
                    echo "Deleted: " . $file . " (age: " . (time() - $filemtime) . "s)\n";
                } else {
                    $errors[] = "Failed to delete: " . $filePath;
                }
            }
        }
    }
    closedir($handle);
} else {
    echo "Error: Could not open session directory: " . $sessionPath . "\n";
    exit(1);
}

echo "\nCleanup complete.\n";
echo "Files deleted: " . $filesDeleted . "\n";

if (!empty($errors)) {
    echo "Errors encountered:\n";
    foreach ($errors as $error) {
        echo "- " . $error . "\n";
    }
    exit(1);
}

exit(0);
