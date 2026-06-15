<?php
// GovCore CMS Configuration
// [架构师备注] 数据库配置，建议从环境变量读取以适应容器化部署

$db_host = getenv('DB_HOST') ?: 'db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: 'root';
$db_name = getenv('DB_NAME') ?: 'govcore';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    // [VULN] 真实环境中不应直接输出错误信息，这里保留以便调试
    die("Connection failed: " . mysqli_connect_error());
}

// 强制设置字符集，避免宽字节注入等乱码问题（虽然我们要制造漏洞，但乱码不是目标）
mysqli_set_charset($conn, "utf8mb4");

// 系统常量
define('APP_NAME', 'GovCore 政务公开与应急指挥平台');
define('APP_VERSION', '2.4.0');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('LOG_PATH', __DIR__ . '/data/app.log');

// 开启 Session
session_start();
?>
