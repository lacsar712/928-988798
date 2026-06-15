<?php
require_once '../func.php';
check_login();

$output = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_POST['ip'];
    $cmd = "ping -c 4 " . $ip;
    
    Logger::logAction('NetTool', "Executed command: $cmd");
    
    ob_start();
    system($cmd);
    $output = ob_get_clean();

    // Check for AJAX request
    if (isset($_POST['ajax'])) {
        echo $output;
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>网络连通性测试 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .fs-small-code { font-size: 0.85rem; line-height: 1.4; }
    </style>
</head>
<body>
    
    <!-- Top Navbar -->
    <nav class="navbar navbar-dark bg-gov-blue shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">GovCore 管理中心</a>
            <span class="navbar-text text-white">
                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | <a href="logout.php" class="text-white-50 text-decoration-none">退出</a>
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-4 border-end bg-white" style="min-height: calc(100vh - 56px);">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i>控制台
                    </a>
                    <a href="net_tool.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-broadcast me-2"></i>网络检测工具
                    </a>
                    <a href="upload.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-upload me-2"></i>政策文件上传
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 py-4 bg-light">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">控制台</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue">网络检测</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-hdd-network me-2"></i>服务器网络连通性测试 (Ping)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-info border-0 bg-light-blue text-gov-blue mb-4">
                                <i class="bi bi-info-circle-fill me-2"></i> 用于检测服务器是否能连接外部网络（如上级部门接口）。
                            </div>
                            
                            <form method="POST" class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="ip" class="form-label fw-bold text-secondary">目标 IP 或域名</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-globe"></i></span>
                                        <input type="text" class="form-control" id="ip" name="ip" placeholder="例如: 8.8.8.8" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-gov-blue w-100"><i class="bi bi-play-fill me-1"></i>开始检测</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            let ip = document.getElementById('ip').value;
            
            if(ip) {
                Swal.fire({
                    title: '正在检测...',
                    text: '网络连通性测试中，这可能需要几秒钟',
                    icon: 'info',
                    allowOutsideClick: false, 
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        
                        const formData = new FormData();
                        formData.append('ip', ip);
                        formData.append('ajax', '1');

                        fetch('net_tool.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            // 简单分析结果
                            let isSuccess = data.includes('0% packet loss') || data.includes('ttl=');
                            let titleText = isSuccess ? 'Ping 成功' : 'Ping 失败';
                            let iconType = isSuccess ? 'success' : 'error';

                            Swal.fire({
                                title: titleText,
                                html: '<div class="text-start bg-light border p-3 rounded fs-small-code text-secondary" style="max-height: 300px; overflow-y: auto; font-family: Consolas, monospace;">' + 
                                      data.replace(/\n/g, '<br>') + 
                                      '</div>',
                                icon: iconType,
                                width: 600,
                                confirmButtonText: '关闭'
                            });
                        })
                        .catch(error => {
                            Swal.fire('Error', '请求失败', 'error');
                        });
                    }
                });
            }
        });
    </script>

</body>
</html>
