<?php
/**
 * Simple logging utility for the API.
 *
 * Handles writing messages to a log file with optional log rotation.
 * Automatically backs up the log file if it exceeds 5 MB.
 *
 * Usage:
 * ```php
 * Logger::write('Something happened');
 * Logger::write('An error occurred', 'ERROR');
 * ```
 *
 * @package GiApiRoute\Support
 * @author Artem <gammaironak@gmail.com>
 * @date 26.11.2025
 */

namespace GiApiRoute\Support;

class Logger
{

    /**
     * Writes a message to the log file.
     *
     * Automatically rotates the log file if it exceeds 5 MB.
     *
     * @param string $message The log message.
     * @param string $level Optional log level (e.g., INFO, ERROR). Default is 'INFO'.
     * @return void
     */
    public static function write(string $message, string $level = 'INFO'): void
    {
        $logFile = __DIR__ . '/log.txt';
        $maxSize = 5 * 1024 * 1024; // 5 MB

        // Rotate log if too large
        if (file_exists($logFile) && filesize($logFile) > $maxSize) {
            $backupFile = __DIR__ . '/log_' . date('Y-m-d_His') . '.txt';
            @rename($logFile, $backupFile);
        }

        // Get current time in WordPress format
        $time = current_time('mysql');
        // Format log line
        $line = sprintf('[%s] [%s] %s%s', $time, $level, $message, PHP_EOL);

        // Append to log file
        $result = @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

        /*if ($result === false) {
            // Fallback if file write fails
            error_log("Failed to write to log: $message");
        }*/
    }
}
