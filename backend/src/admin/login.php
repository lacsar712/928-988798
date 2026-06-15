<?php
require_once '../func.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // [VULN] 这里的查询可以是注入点，也可以是普通逻辑
    // 为了符合"Red Team" 靶场，这里也留个万能密码漏洞吧
    // $sql = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    // 但Prompt主要让首页News做SQLi。这里写稍微规范一点（或者继续烂）
    // 为了让User更容易拿到后台权限配合RCE，这里留个弱口令 admin/admin888 (在db.sql里)
    
    // 使用非预处理，方便演示万能密码 ' or 1=1 #
    $sql = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user['username'];
        Logger::logAction('Login', "Success: $username");
        header("Location: index.php");
        exit;
    } else {
        $error = "用户名或密码错误";
        Logger::logAction('Login', "Failed: $username");
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>GovCore 管理中心登录</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gov-blue d-flex align-items-center justify-content-center vh-100">

    <div class="card p-5 border-0 shadow-lg" style="width: 420px; border-radius: 16px;">
        <div class="text-center mb-4">
            <h3 class="text-gov-blue fw-bold mb-2">GovCore 管理中心</h3>
            <p class="text-muted small">系统管理员安全登录入口</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">用户名</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">密码</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-gov-blue">立即登录</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <small class="text-muted">技术支持：XX大数据中心</small>
        </div>
    </div>

</body>
</html>
