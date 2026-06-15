<?php
require_once '../func.php';
check_login();

$msg = '';
$msg_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        
        // [VULN] 3. Unrestricted File Upload Vulnerability
        // 仅检查 MIME Type，伪造 Content-Type: application/pdf 即可绕过
        // 且未重命名文件，直接使用原文件名
        if ($file['type'] == 'application/pdf') {
            $target_dir = "../uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($file['name']);
            
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $msg = "文件上传成功: " . htmlspecialchars($file['name']);
                $msg_type = 'success';
                Logger::logAction('Upload', "Success: " . $file['name']);
            } else {
                $msg = "文件上传失败，权限不足";
                $msg_type = 'danger';
                Logger::logAction('Upload', "Failed (Move Error): " . $file['name']);
            }
        } else {
            $msg = "上传失败：仅允许 PDF 格式的红头文件！检测到类型：" . htmlspecialchars($file['type']);
            $msg_type = 'danger';
            Logger::logAction('Upload', "Blocked (Type Mismatch): " . $file['name'] . " Type: " . $file['type']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>政策文件上传 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
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
                    <a href="net_tool.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-broadcast me-2"></i>网络检测工具
                    </a>
                    <a href="upload.php" class="list-group-item list-group-item-action active">
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue">文件上传</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i>政策资料直传</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning-emphasis mb-4">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> 注意：仅支持 PDF 格式的红头文件归档，严禁上传涉密文件。
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data" class="needs-validation">
                                <div class="mb-4">
                                    <label for="file" class="form-label fw-bold">选择文件</label>
                                    <input class="form-control form-control-lg" type="file" id="file" name="file" required>
                                    <div class="form-text text-muted"><i class="bi bi-filetype-pdf me-1"></i>支持格式：.pdf (文件将自动保存至 server)</div>
                                </div>
                                <button type="submit" class="btn btn-gov-blue px-4 py-2"><i class="bi bi-cloud-upload-fill me-2"></i>立即上传</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($msg): ?>
    <script>
        Swal.fire({
            title: '<?php echo $msg_type == "success" ? "成功" : "失败"; ?>',
            text: '<?php echo htmlspecialchars($msg); ?>',
            icon: '<?php echo $msg_type == "success" ? "success" : "error"; ?>',
            confirmButtonText: '确定'
        });
    </script>
    <?php endif; ?>

</body>
</html>
