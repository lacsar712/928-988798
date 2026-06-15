<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>GovCore 后台管理</title>
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
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-4 border-end bg-white" style="min-height: calc(100vh - 56px);">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-speedometer2 me-2"></i>控制台
                    </a>
                    <a href="net_tool.php" class="list-group-item list-group-item-action">
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
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">首页</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">控制台</li>
                        </ol>
                    </nav>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-3 hover-effect h-100">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <p class="text-muted small mb-1 text-uppercase fw-bold ls-1">今日访问量</p>
                                    <h3 class="mb-0 fw-bold text-gov-blue">1,245</h3>
                                </div>
                                <div class="icon-shape bg-light-blue text-gov-blue rounded-circle p-3">
                                    <i class="bi bi-people-fill fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-3 hover-effect h-100">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <p class="text-muted small mb-1 text-uppercase fw-bold ls-1">新闻发布数</p>
                                    <h3 class="mb-0 fw-bold text-success">58</h3>
                                </div>
                                <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle p-3">
                                    <i class="bi bi-newspaper fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-3 hover-effect h-100">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <p class="text-muted small mb-1 text-uppercase fw-bold ls-1">待办事项</p>
                                    <h3 class="mb-0 fw-bold text-warning">12</h3>
                                </div>
                                <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                                    <i class="bi bi-list-check fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-3 hover-effect h-100">
                            <div class="card-body d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <p class="text-muted small mb-1 text-uppercase fw-bold ls-1">安全告警</p>
                                    <h3 class="mb-0 fw-bold text-danger">0</h3>
                                </div>
                                <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                                    <i class="bi bi-shield-exclamation fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-cpu me-2"></i>系统概况</h5>
                            </div>
                            <div class="card-body p-4">
                                <p class="mb-2">欢迎使用 <strong>GovCore 政务公开与应急指挥平台</strong> 管理系统。</p>
                                <div class="row g-3 mt-2">
                                    <div class="col-sm-6">
                                        <div class="p-3 bg-light rounded border">
                                            <small class="text-muted d-block">系统版本</small>
                                            <span class="fw-bold fs-5"><?php echo APP_VERSION; ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="p-3 bg-light rounded border">
                                            <small class="text-muted d-block">服务器时间</small>
                                            <span class="fw-bold font-monospace"><?php echo date('Y-m-d H:i:s'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-tools me-2"></i>系统维护</h5>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted small mb-3">定期清理系统缓存可提高系统响应速度。</p>
                                <button type="button" class="btn btn-outline-danger w-100" id="clearCacheBtn">
                                    <i class="bi bi-trash3 me-2"></i>立即清理缓存
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('clearCacheBtn').addEventListener('click', function() {
            Swal.fire({
                title: '确认清理?',
                text: "此操作将清除系统所有临时文件缓存，是否继续？",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '立即清理',
                cancelButtonText: '取消'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        '已清理!',
                        '系统缓存清理任务已提交后台队列。',
                        'success'
                    )
                }
            })
        });
    </script>

</body>
</html>
