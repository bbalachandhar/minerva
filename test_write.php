<?php
// A script to diagnose file write issues.

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>PHP Write Test</h3>";

$directory = __DIR__ . '/backend/captcha_images/';
$file = $directory . 'test_from_php.txt';

echo "Attempting to write to file: " . htmlspecialchars($file) . "<br><br>";

// Check if directory exists
if (!is_dir($directory)) {
    echo "<b>Error:</b> Directory does not exist: " . htmlspecialchars($directory);
    exit;
}

// Check if directory is writable
if (!is_writable($directory)) {
    echo "<b>Error:</b> Directory is not writable according to is_writable(): " . htmlspecialchars($directory);
    echo "<br>PHP user is: " . exec('whoami');
    exit;
}

echo "Directory exists and is_writable() returned true.<br>";

// Try to write the file
$result = file_put_contents($file, 'This is a test from PHP script.');

if ($result === false) {
    echo "<b>Error:</b> file_put_contents() failed. The directory is not writable by the PHP process.";
    $error = error_get_last();
    if ($error) {
        echo "<br><b>Last Error:</b> " . htmlspecialchars($error['message']);
    }
} else {
    echo "<b>Success!</b> Wrote " . $result . " bytes to the file.";
    echo "<br>Check the 'backend/captcha_images' directory for 'test_from_php.txt'.";
    // Try to clean up
    unlink($file);
}
