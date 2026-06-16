<?php

$GLOBALS['TEST_PASSED'] = 0;
$GLOBALS['TEST_FAILED'] = 0;

function run_test($name, $fn) {
    try {
        $fn();
        $GLOBALS['TEST_PASSED']++;
        echo "[通过] {$name}\n";
    } catch (Throwable $e) {
        $GLOBALS['TEST_FAILED']++;
        echo "[失败] {$name} - {$e->getMessage()}\n";
    }
}

function assert_true($cond, $msg = '断言失败') {
    if (!$cond) {
        throw new Exception($msg);
    }
}

function assert_eq($actual, $expected, $msg = '') {
    if ($actual !== $expected) {
        $detail = $msg ? " ({$msg})" : '';
        throw new Exception("期望 " . var_export($expected, true) . "，实际 " . var_export($actual, true) . $detail);
    }
}

$TEST_TEMP_DIR = sys_get_temp_dir() . '/logger_test_' . uniqid();
mkdir($TEST_TEMP_DIR);
define('LOG_PATH', $TEST_TEMP_DIR . '/app.log');

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: 'root';
$db_name = getenv('DB_NAME') ?: 'govcore';
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if ($conn) {
    mysqli_set_charset($conn, "utf8mb4");
}
$GLOBALS['conn'] = $conn;

$func_content = @file_get_contents(__DIR__ . '/src/func.php');
if ($func_content === false) {
    $func_content = @file_get_contents(__DIR__ . '/../src/func.php');
}
if ($func_content === false) {
    echo "致命错误：无法读取 src/func.php，请确认 test_logger.php 放在 backend/ 目录下运行\n";
    exit(1);
}

if (!preg_match('/(class Logger\s*\{.*?^\})/sm', $func_content, $m)) {
    echo "致命错误：无法从 func.php 中提取 Logger 类定义\n";
    exit(1);
}
$logger_src = $m[1];
$logger_src = preg_replace('/^\\s*require_once.*$/m', '', $logger_src);
eval($logger_src);

echo "==== Logger 单元测试 ====\n";
echo "临时日志目录: {$TEST_TEMP_DIR}\n";
echo "数据库状态: " . ($conn ? "可用（将执行数据库断言）" : "不可用（仅执行文件与逻辑断言）") . "\n\n";

run_test('常规调用-数据库与文件双写留痕', function() use ($conn, $TEST_TEMP_DIR) {
    @unlink(LOG_PATH);
    $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
    unset($_SERVER['HTTP_X_FORWARDED_FOR']);

    $type = 'TEST_login';
    $payload = '用户 admin 登录成功';
    Logger::logAction($type, $payload);

    assert_true(file_exists(LOG_PATH), '日志文件应被创建');
    $content = file_get_contents(LOG_PATH);
    assert_true(strpos($content, '192.168.1.100') !== false, '日志文件应含 IP');
    assert_true(strpos($content, $type) !== false, '日志文件应含 action_type');
    assert_true(strpos($content, $payload) !== false, '日志文件应含 payload');

    if ($conn) {
        $res = mysqli_query($conn, "SELECT ip_address, action_type, payload FROM sys_logs 
            WHERE action_type='TEST_login' ORDER BY id DESC LIMIT 1");
        assert_true($res && $res->num_rows > 0, '数据库中应有 TEST_login 记录');
        $row = mysqli_fetch_assoc($res);
        assert_eq($row['ip_address'], '192.168.1.100');
        assert_eq($row['action_type'], 'TEST_login');
        assert_eq($row['payload'], $payload);
        mysqli_query($conn, "DELETE FROM sys_logs WHERE action_type='TEST_login'");
    }
});

run_test('数据库不可用-方法不崩溃且文件仍写入', function() use ($conn) {
    @unlink(LOG_PATH);
    $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
    unset($_SERVER['HTTP_X_FORWARDED_FOR']);

    $saved_conn = $GLOBALS['conn'];
    $GLOBALS['conn'] = null;

    $caught = null;
    set_error_handler(function() { return true; });
    try {
        Logger::logAction('TEST_nodb', '数据库挂了但请求不能挂');
    } catch (Throwable $e) {
        $caught = $e;
    }
    restore_error_handler();
    $GLOBALS['conn'] = $saved_conn;

    assert_true($caught === null, '数据库不可用时方法不应抛异常或致命错误');
    assert_true(file_exists(LOG_PATH), '数据库不可用时日志文件仍应被创建');
    $c = file_get_contents(LOG_PATH);
    assert_true(strpos($c, 'TEST_nodb') !== false, '日志内容需写入文件');
});

run_test('反向代理头-X-Forwarded-For 优先于 REMOTE_ADDR', function() use ($conn) {
    @unlink(LOG_PATH);
    $_SERVER['REMOTE_ADDR'] = '10.0.0.99';
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.42';

    Logger::logAction('TEST_proxy', '经反向代理访问');

    $content = file_get_contents(LOG_PATH);
    assert_true(strpos($content, '203.0.113.42') !== false, '日志中应出现 X-Forwarded-For 的 IP');
    assert_true(strpos($content, '] 10.0.0.99 ') === false, 'REMOTE_ADDR 不应出现在日志 IP 字段');

    if ($conn) {
        $res = mysqli_query($conn, "SELECT ip_address FROM sys_logs 
            WHERE action_type='TEST_proxy' ORDER BY id DESC LIMIT 1");
        $row = mysqli_fetch_assoc($res);
        assert_eq($row['ip_address'], '203.0.113.42');
        mysqli_query($conn, "DELETE FROM sys_logs WHERE action_type='TEST_proxy'");
    }
});

run_test('特殊字符-引号/反斜杠/中文/emoji 原样入库', function() use ($conn) {
    @unlink(LOG_PATH);
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    unset($_SERVER['HTTP_X_FORWARDED_FOR']);

    $type = 'TEST_special';
    $payload = '数据"带双引号"和\\反斜杠/，中文：政务公开🚀🔥，换行' . "\n" . '续行与制表' . "\t" . '符完';

    Logger::logAction($type, $payload);

    $file_content = file_get_contents(LOG_PATH);
    assert_true(strpos($file_content, '政务公开') !== false, '文件中应保留中文');
    assert_true(strpos($file_content, '🚀🔥') !== false, '文件中应保留 emoji');

    if ($conn) {
        $res = mysqli_query($conn, "SELECT payload FROM sys_logs 
            WHERE action_type='TEST_special' ORDER BY id DESC LIMIT 1");
        $row = mysqli_fetch_assoc($res);
        assert_true($row !== null, '数据库中应有记录');
        assert_eq($row['payload'], $payload, '入库内容必须与原始 payload 字节级一致');
        mysqli_query($conn, "DELETE FROM sys_logs WHERE action_type='TEST_special'");
    }
});

run_test('日志目录不可写-方法平稳返回不抛异常', function() use ($conn, $TEST_TEMP_DIR, $logger_src) {
    $unwritable_dir = $TEST_TEMP_DIR . '/unwritable_sub_' . uniqid();
    $bad_path = $unwritable_dir . '/nested/deep/app.log';

    $tmp_conn = $GLOBALS['conn'];
    $GLOBALS['conn'] = null;
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    unset($_SERVER['HTTP_X_FORWARDED_FOR']);

    $caught = null;
    set_error_handler(function() { return true; });

    try {
        $ip = '127.0.0.1';
        $entry = '[' . date('Y-m-d H:i:s') . "] [$ip] [TEST_dir] payload" . PHP_EOL;
        $ret = @file_put_contents($bad_path, $entry, FILE_APPEND);
    } catch (Throwable $e) {
        $caught = $e;
    }

    restore_error_handler();
    $GLOBALS['conn'] = $tmp_conn;

    assert_true($caught === null, 'Logger 内部的 @file_put_contents 对不可写目录不应抛出可捕获异常');
    assert_true($ret === false, '向不存在目录写入应返回 false 而非抛出异常');

    $alt_class = 'LoggerAlt_' . substr(md5(uniqid()), 0, 6);
    $alt_src = preg_replace('/\bclass Logger\b/', 'class ' . $alt_class, $logger_src);
    $alt_src = preg_replace('/\bLOG_PATH\b/', 'LOG_PATH_ALT_TEST', $alt_src);
    define('LOG_PATH_ALT_TEST', $bad_path);
    eval($alt_src);

    $caught2 = null;
    set_error_handler(function() { return true; });
    $tmp_conn2 = $GLOBALS['conn'];
    $GLOBALS['conn'] = null;
    try {
        $alt_class::logAction('T_RO', '目录不可写 payload');
    } catch (Throwable $e) {
        $caught2 = $e;
    }
    $GLOBALS['conn'] = $tmp_conn2;
    restore_error_handler();

    assert_true($caught2 === null, 'Logger 类方法本身在日志目录不可写时必须平稳返回，不得让异常上溢');
});

run_test('性能-100次连续调用总耗时<1秒', function() use ($conn) {
    @unlink(LOG_PATH);
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    unset($_SERVER['HTTP_X_FORWARDED_FOR']);

    $start = microtime(true);
    for ($i = 0; $i < 100; $i++) {
        Logger::logAction('TEST_perf', '调用序号 ' . $i);
    }
    $elapsed = microtime(true) - $start;

    echo "       耗时 " . number_format($elapsed * 1000, 2) . " ms\n";
    assert_true($elapsed < 1.0, "100次调用应在 1s 内完成，实际 {$elapsed}s");

    if ($conn) {
        mysqli_query($conn, "DELETE FROM sys_logs WHERE action_type='TEST_perf'");
    }
});

if ($conn) {
    mysqli_query($conn, "DELETE FROM sys_logs WHERE action_type LIKE 'TEST_%'");
    mysqli_close($conn);
}

$cleanup = function($dir) use (&$cleanup) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $it) {
        if ($it === '.' || $it === '..') continue;
        $p = $dir . '/' . $it;
        is_dir($p) ? $cleanup($p) : @unlink($p);
    }
    @rmdir($dir);
};
$cleanup($TEST_TEMP_DIR);

$passed = $GLOBALS['TEST_PASSED'];
$failed = $GLOBALS['TEST_FAILED'];
echo "\n通过 {$passed}、失败 {$failed}\n";
exit($failed > 0 ? 1 : 0);
