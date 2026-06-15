<?php
// Core Functions & Vulnerable Classes

// Use __DIR__ to ensure correct path resolution when included from subdirectories (e.g., admin/)
require_once __DIR__ . '/config.php';

// [VULN] 4. PHP Deserialization Vulnerability
class CacheManager {
    public $cacheFile;
    public $cacheContent;

    public function __construct($file = 'data/cache.tmp', $content = '') {
        $this->cacheFile = $file;
        $this->cacheContent = $content;
    }

    public function updateCache($content) {
        $this->cacheContent = $content;
    }

    public function __destruct() {
        if (!empty($this->cacheFile)) {
            // [VULN] Arbitrary File Write via Deserialization
            // Note: file_put_contents relative path depends on CWD.
            // If exploited from admin/, 'data/cache.tmp' might end up in admin/data/cache.tmp or fail.
            // But for RCE via shell.php, attacker can specify absolute path or relative traversal.
            file_put_contents($this->cacheFile, $this->cacheContent);
            
            // Log keeping silent
            Logger::logAction('System', 'Cache auto-saved to ' . $this->cacheFile);
        }
    }
}

// Global Logger Class
class Logger {
    public static function logAction($type, $payload) {
        global $conn;
        
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        // Write to DB
        $clean_type = mysqli_real_escape_string($conn, $type);
        $clean_payload = mysqli_real_escape_string($conn, $payload);
        $clean_ip = mysqli_real_escape_string($conn, $ip);
        
        $sql = "INSERT INTO sys_logs (ip_address, action_type, payload) VALUES ('$clean_ip', '$clean_type', '$clean_payload')";
        @mysqli_query($conn, $sql);

        // Write to File
        $logEntry = "[" . date('Y-m-d H:i:s') . "] [$ip] [$type] $payload" . PHP_EOL;
        file_put_contents(LOG_PATH, $logEntry, FILE_APPEND);
    }
}

// Check User Config (Triggers Deserialization)
function check_user_config() {
    if (isset($_COOKIE['user_config'])) {
        $data = base64_decode($_COOKIE['user_config']);
        // [VULN] Insecure Deserialization
        @unserialize($data);
    }
}

// Execute check
check_user_config();

// Login Check Helper
function check_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");
        exit;
    }
}
