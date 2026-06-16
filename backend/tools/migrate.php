#!/usr/bin/env php
<?php

declare(strict_types=1);

$db_host = getenv('DB_HOST') ?: 'db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: 'root';
$db_name = getenv('DB_NAME') ?: 'govcore';

$migrations_dir = __DIR__ . '/../migrations';
$version_table = 'schema_migrations';

function println(string $msg): void
{
    echo $msg . PHP_EOL;
}

function errln(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
}

function usage(): void
{
    println('GovCore Database Migration Tool');
    println('');
    println('Usage:');
    println('  php migrate.php status              查看当前数据库版本');
    println('  php migrate.php up [steps]          向前升级，默认升级到最新版，steps 指定步数');
    println('  php migrate.php down <steps>        向后回滚指定步数');
    println('  php migrate.php baseline            一次性导入所有基线迁移');
    println('  php migrate.php help                显示此帮助');
    println('');
}

function get_db_conn(string $host, string $user, string $pass, string $name): mysqli
{
    $conn = @mysqli_connect($host, $user, $pass);
    if (!$conn) {
        errln('[ERROR] 无法连接到数据库服务器: ' . mysqli_connect_error());
        exit(1);
    }
    mysqli_set_charset($conn, 'utf8mb4');
    if (!@mysqli_select_db($conn, $name)) {
        println('[INFO] 数据库 ' . $name . ' 不存在，正在创建...');
        if (!mysqli_query($conn, 'CREATE DATABASE `' . $name . '` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')) {
            errln('[ERROR] 创建数据库失败: ' . mysqli_error($conn));
            exit(1);
        }
        mysqli_select_db($conn, $name);
    }
    return $conn;
}

function ensure_version_table(mysqli $conn, string $table): void
{
    $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
        `version` varchar(20) NOT NULL PRIMARY KEY,
        `name` varchar(255) NOT NULL,
        `checksum` varchar(64) NOT NULL,
        `applied_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_applied_at` (`applied_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!mysqli_query($conn, $sql)) {
        errln('[ERROR] 创建版本记录表失败: ' . mysqli_error($conn));
        exit(1);
    }
}

function scan_migrations(string $dir): array
{
    if (!is_dir($dir)) {
        errln('[ERROR] 迁移目录不存在: ' . $dir);
        exit(1);
    }
    $files = glob($dir . '/*.sql');
    if (!$files) {
        return [];
    }
    $migrations = [];
    foreach ($files as $file) {
        $basename = basename($file, '.sql');
        if (!preg_match('/^(\d+)_(.+)$/', $basename, $m)) {
            errln('[WARN] 跳过不规范命名的迁移文件: ' . $basename);
            continue;
        }
        $version = (int)$m[1];
        $migrations[$version] = [
            'version' => $version,
            'name' => $m[2],
            'file' => $file,
            'basename' => $basename,
        ];
    }
    ksort($migrations);
    return $migrations;
}

function compute_checksum(string $file): string
{
    return hash_file('sha256', $file);
}

function parse_rollback_sql(string $file): string
{
    $content = file_get_contents($file);
    $pattern = '/--\s*ROLLBACK BEGIN\s*[\r\n]+(.*?)--\s*ROLLBACK END/s';
    if (!preg_match($pattern, $content, $matches)) {
        return '';
    }
    $rollback = trim($matches[1]);
    $lines = explode("\n", $rollback);
    $sql = '';
    foreach ($lines as $line) {
        $line = preg_replace('/^\s*--\s?/', '', $line);
        $line = trim($line);
        if ($line === '' || strpos($line, '===') === 0) {
            continue;
        }
        $sql .= $line . "\n";
    }
    return trim($sql);
}

function parse_up_sql(string $file): string
{
    $content = file_get_contents($file);
    $pattern = '/--\s*ROLLBACK BEGIN\s*[\r\n]+.*--\s*ROLLBACK END/s';
    $content = preg_replace($pattern, '', $content);
    return trim($content);
}

function get_applied_versions(mysqli $conn, string $table): array
{
    $result = mysqli_query($conn, "SELECT `version`, `name`, `checksum` FROM `{$table}` ORDER BY CAST(`version` AS UNSIGNED) ASC");
    if (!$result) {
        return [];
    }
    $applied = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $applied[(int)$row['version']] = $row;
    }
    return $applied;
}

function get_current_version(array $applied): int
{
    if (empty($applied)) {
        return 0;
    }
    $keys = array_keys($applied);
    sort($keys, SORT_NUMERIC);
    return (int)end($keys);
}

function validate_checksums(array $migrations, array $applied): bool
{
    $ok = true;
    foreach ($applied as $version => $row) {
        if (!isset($migrations[$version])) {
            errln("[WARN] 已应用的迁移版本 {$version} 在文件系统中不存在对应的脚本文件");
            continue;
        }
        $actual = compute_checksum($migrations[$version]['file']);
        if ($actual !== $row['checksum']) {
            errln("[ERROR] 迁移脚本校验码不一致！版本 {$version} (" . $migrations[$version]['basename'] . ")");
            errln("  数据库记录: " . $row['checksum']);
            errln("  当前文件:   " . $actual);
            errln("  该脚本已被人修改，拒绝继续向前迁移。如需强制，请先手动处理该版本记录。");
            $ok = false;
        }
    }
    return $ok;
}

function execute_sql_file(mysqli $conn, string $file, bool $is_rollback = false): bool
{
    if ($is_rollback) {
        $sql = parse_rollback_sql($file);
        if ($sql === '') {
            errln('[WARN] 迁移文件未包含 ROLLBACK 段落: ' . basename($file));
            return true;
        }
    } else {
        $sql = parse_up_sql($file);
    }

    $statements = explode_sql($sql);
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') {
            continue;
        }
        if (!mysqli_query($conn, $stmt)) {
            errln('[ERROR] SQL 执行失败: ' . mysqli_error($conn));
            errln('  语句: ' . substr($stmt, 0, 200) . (strlen($stmt) > 200 ? '...' : ''));
            return false;
        }
    }
    return true;
}

function explode_sql(string $sql): array
{
    $statements = [];
    $current = '';
    $in_string = false;
    $string_char = '';
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $prev = $i > 0 ? $sql[$i - 1] : '';

        if ($in_string) {
            $current .= $char;
            if ($char === $string_char && $prev !== '\\') {
                $in_string = false;
            }
            continue;
        }

        if ($char === '"' || $char === "'") {
            $in_string = true;
            $string_char = $char;
            $current .= $char;
            continue;
        }

        if ($char === ';') {
            $statements[] = $current;
            $current = '';
            continue;
        }

        $current .= $char;
    }

    if (trim($current) !== '') {
        $statements[] = $current;
    }

    return $statements;
}

function record_migration(mysqli $conn, string $table, int $version, string $name, string $checksum): bool
{
    $v = mysqli_real_escape_string($conn, (string)$version);
    $n = mysqli_real_escape_string($conn, $name);
    $c = mysqli_real_escape_string($conn, $checksum);
    $sql = "INSERT INTO `{$table}` (`version`, `name`, `checksum`) VALUES ('{$v}', '{$n}', '{$c}')";
    return (bool)mysqli_query($conn, $sql);
}

function unrecord_migration(mysqli $conn, string $table, int $version): bool
{
    $v = mysqli_real_escape_string($conn, (string)$version);
    $sql = "DELETE FROM `{$table}` WHERE `version` = '{$v}'";
    return (bool)mysqli_query($conn, $sql);
}

function cmd_status(mysqli $conn, string $table, array $migrations): void
{
    $applied = get_applied_versions($conn, $table);
    $current = get_current_version($applied);
    $all_versions = array_keys($migrations);
    $latest = !empty($all_versions) ? (int)max($all_versions) : 0;

    println('+--------+------------------------------+----------+------------+');
    println('| 版本号 | 迁移名称                     | 状态     | 应用时间   |');
    println('+--------+------------------------------+----------+------------+');

    foreach ($migrations as $v => $m) {
        $status = '未应用';
        $applied_at = '-';
        $checksum_ok = true;
        if (isset($applied[$v])) {
            $status = '已应用';
            $row = $applied[$v];
            $applied_at = $row['applied_at'];
            $actual = compute_checksum($m['file']);
            if ($actual !== $row['checksum']) {
                $status = '已篡改!';
                $checksum_ok = false;
            }
        }
        $name = strlen($m['basename']) > 28 ? substr($m['basename'], 0, 25) . '...' : $m['basename'];
        $name = str_pad($name, 28, ' ', STR_PAD_RIGHT);
        $marker = $checksum_ok ? ' ' : '!';
        println(sprintf('| %-6s | %s | %-8s | %-10s |%s',
            $v, $name, $status, substr($applied_at, 0, 10), $marker));
    }

    println('+--------+------------------------------+----------+------------+');
    println('');
    println("当前数据库版本: {$current}");
    println("最新可用版本:   {$latest}");
    if ($current < $latest) {
        println("需要升级:       还剩 " . ($latest - $current) . " 个迁移未应用");
    } elseif ($current === $latest) {
        println("状态:           已是最新版本");
    } else {
        println("状态:           数据库版本超前于迁移文件（可能需要回滚）");
    }
}

function cmd_up(mysqli $conn, string $table, array $migrations, ?int $steps): void
{
    $applied = get_applied_versions($conn, $table);

    if (!validate_checksums($migrations, $applied)) {
        errln('[ERROR] 由于已应用迁移的校验码不一致，已中止迁移。');
        exit(1);
    }

    $pending = [];
    foreach ($migrations as $v => $m) {
        if (!isset($applied[$v])) {
            $pending[$v] = $m;
        }
    }
    ksort($pending);

    if (empty($pending)) {
        println('[INFO] 数据库已是最新版本，无需升级。');
        return;
    }

    $total = count($pending);
    if ($steps !== null) {
        $pending = array_slice($pending, 0, $steps, true);
    }

    println('[INFO] 将应用 ' . count($pending) . " 个迁移（共 {$total} 个待应用）");

    foreach ($pending as $v => $m) {
        println("[UP]   应用迁移 {$v}: {$m['basename']} ...");
        $checksum = compute_checksum($m['file']);

        mysqli_begin_transaction($conn);
        try {
            if (!execute_sql_file($conn, $m['file'], false)) {
                mysqli_rollback($conn);
                errln("[ERROR] 迁移 {$v} 执行失败，已回滚。");
                exit(1);
            }
            if (!record_migration($conn, $table, $v, $m['basename'], $checksum)) {
                mysqli_rollback($conn);
                errln("[ERROR] 记录迁移版本 {$v} 失败: " . mysqli_error($conn));
                exit(1);
            }
            mysqli_commit($conn);
            println("[OK]   迁移 {$v} 应用成功 (checksum: " . substr($checksum, 0, 12) . ")");
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            errln("[ERROR] 迁移 {$v} 异常: " . $e->getMessage());
            exit(1);
        }
    }

    println('[INFO] 升级完成。');
}

function cmd_down(mysqli $conn, string $table, array $migrations, int $steps): void
{
    $applied = get_applied_versions($conn, $table);
    if (empty($applied)) {
        println('[INFO] 当前无已应用的迁移，无需回滚。');
        return;
    }

    $applied_versions = array_keys($applied);
    rsort($applied_versions, SORT_NUMERIC);

    $to_rollback = [];
    $count = min($steps, count($applied_versions));
    for ($i = 0; $i < $count; $i++) {
        $v = (int)$applied_versions[$i];
        if (!isset($migrations[$v])) {
            errln("[WARN] 版本 {$v} 对应的迁移文件不存在，跳过回滚");
            continue;
        }
        $to_rollback[$v] = $migrations[$v];
    }

    if (empty($to_rollback)) {
        println('[INFO] 无可回滚的迁移。');
        return;
    }

    krsort($to_rollback);
    println('[INFO] 将回滚 ' . count($to_rollback) . ' 个迁移');

    foreach ($to_rollback as $v => $m) {
        println("[DOWN] 回滚迁移 {$v}: {$m['basename']} ...");

        mysqli_begin_transaction($conn);
        try {
            if (!execute_sql_file($conn, $m['file'], true)) {
                mysqli_rollback($conn);
                errln("[ERROR] 回滚 {$v} 执行失败，已中止。");
                exit(1);
            }
            if (!unrecord_migration($conn, $table, $v)) {
                mysqli_rollback($conn);
                errln("[ERROR] 删除迁移版本记录 {$v} 失败: " . mysqli_error($conn));
                exit(1);
            }
            mysqli_commit($conn);
            println("[OK]   迁移 {$v} 回滚成功");
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            errln("[ERROR] 回滚 {$v} 异常: " . $e->getMessage());
            exit(1);
        }
    }

    println('[INFO] 回滚完成。');
}

function cmd_baseline(mysqli $conn, string $table, array $migrations): void
{
    $applied = get_applied_versions($conn, $table);
    if (!empty($applied)) {
        errln('[ERROR] 数据库已有迁移记录，baseline 只能在空库上执行。当前版本: ' . get_current_version($applied));
        exit(1);
    }

    println('[INFO] 将一次性导入全部 ' . count($migrations) . ' 个基线迁移');

    foreach ($migrations as $v => $m) {
        println("[BASE] 导入迁移 {$v}: {$m['basename']} ...");
        $checksum = compute_checksum($m['file']);

        mysqli_begin_transaction($conn);
        try {
            if (!execute_sql_file($conn, $m['file'], false)) {
                mysqli_rollback($conn);
                errln("[ERROR] 迁移 {$v} 执行失败，已回滚。");
                exit(1);
            }
            if (!record_migration($conn, $table, $v, $m['basename'], $checksum)) {
                mysqli_rollback($conn);
                errln("[ERROR] 记录迁移版本 {$v} 失败: " . mysqli_error($conn));
                exit(1);
            }
            mysqli_commit($conn);
            println("[OK]   迁移 {$v} 导入成功 (checksum: " . substr($checksum, 0, 12) . ")");
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            errln("[ERROR] 迁移 {$v} 异常: " . $e->getMessage());
            exit(1);
        }
    }

    println('[INFO] 基线导入完成。');
}

if (PHP_SAPI !== 'cli') {
    errln('[ERROR] 此脚本只能在命令行模式下运行。');
    exit(1);
}

$action = $argv[1] ?? 'help';
$param = $argv[2] ?? null;

$conn = get_db_conn($db_host, $db_user, $db_pass, $db_name);
ensure_version_table($conn, $version_table);
$migrations = scan_migrations($migrations_dir);

if (empty($migrations)) {
    errln('[WARN] 未找到任何迁移脚本，请检查 ' . $migrations_dir . ' 目录。');
}

switch ($action) {
    case 'status':
        cmd_status($conn, $version_table, $migrations);
        break;
    case 'up':
        $steps = $param !== null ? (int)$param : null;
        if ($steps !== null && $steps <= 0) {
            errln('[ERROR] 步数必须是正整数');
            exit(1);
        }
        cmd_up($conn, $version_table, $migrations, $steps);
        break;
    case 'down':
        if ($param === null) {
            errln('[ERROR] down 命令必须指定回滚步数');
            exit(1);
        }
        $steps = (int)$param;
        if ($steps <= 0) {
            errln('[ERROR] 步数必须是正整数');
            exit(1);
        }
        cmd_down($conn, $version_table, $migrations, $steps);
        break;
    case 'baseline':
        cmd_baseline($conn, $version_table, $migrations);
        break;
    case 'help':
    case '--help':
    case '-h':
        usage();
        break;
    default:
        errln('[ERROR] 未知命令: ' . $action);
        usage();
        exit(1);
}

mysqli_close($conn);
