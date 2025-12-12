<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logger {
    private $log_file = APPPATH . 'logs/attendance_debug.log';

    public function __construct() {
        // Ensure the log directory exists
        $log_path = dirname($this->log_file);
        if (!is_dir($log_path)) {
            mkdir($log_path, 0777, true);
        }
    }

    public function log($message, $level = 'DEBUG') {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "$timestamp - $level - $message\n";
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
}

