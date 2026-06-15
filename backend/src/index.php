<?php
require_once 'func.php';

// [VULN] 1. SQL Injection (Search)
$search_sql = "";
$mode = "latest";
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    // [VULN] 直接拼接 SQL，无过滤
    // Payload: ' UNION SELECT 1, user(), database(), 4 -- 
    $sql = "SELECT * FROM news WHERE title LIKE '%$keyword%' ORDER BY publish_date DESC";
    $mode = "search";
    Logger::logAction('Search', "User searched for: $keyword");
} else {
    $sql = "SELECT * FROM news ORDER BY publish_date DESC LIMIT 5";
}

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovCore 政务公开与应急指挥平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gov-blue shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="https://via.placeholder.com/30/ffffff/000000?text=G" alt="" class="d-inline-block align-text-top me-2">
                GovCore 政务平台
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Carousel -->
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/img/banner1.png" class="d-block w-100" alt="数字政府 智慧应急" style="height: 480px; object-fit: cover; filter: brightness(0.8);">
                <div class="carousel-caption d-none d-md-block">
                    <h1 class="display-4 fw-bold mb-3 text-shadow">数字政府 智慧应急</h1>
                    <p class="lead mb-4 text-shadow">全面提升政府治理现代化水平</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/img/banner2.png" class="d-block w-100" alt="权威发布 透明高效" style="height: 480px; object-fit: cover; filter: brightness(0.8);">
                <div class="carousel-caption d-none d-md-block">
                    <h1 class="display-4 fw-bold mb-3 text-shadow">权威发布 透明高效</h1>
                    <p class="lead mb-4 text-shadow">打造人民满意的服务型政府</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- News Section -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0 border-start border-4 border-danger ps-3 text-gov-blue fw-bold">
                            <?php echo ($mode == 'search') ? '搜索结果' : '通知公告'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Bar -->
                        <form action="index.php" method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="keyword" class="form-control" placeholder="请输入关键字查询..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                                <button class="btn btn-gov-blue" type="submit">搜索</button>
                            </div>
                        </form>

                        <!-- News List -->
                        <div class="list-group list-group-flush">
                            <?php
                            if ($result) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<a href="javascript:void(0);" class="list-group-item list-group-item-action py-3 news-item" data-title="' . htmlspecialchars($row['title']) . '" data-date="' . date('Y-m-d', strtotime($row['publish_date'])) . '">';
                                    echo '<div class="d-flex w-100 justify-content-between">';
                                    echo '<h6 class="mb-1 text-dark fw-bold">' . htmlspecialchars($row['title']) . '</h6>';
                                    echo '<small class="text-muted">' . date('Y-m-d', strtotime($row['publish_date'])) . '</small>';
                                    echo '</div>';
                                    echo '</a>';
                                }
                            } else {
                                echo '<div class="alert alert-danger">系统错误: ' . mysqli_error($conn) . '</div>'; // Detailed error helps SQLi
                            }
                            ?>
                        </div>
                    </div>
                </div>
            
            <!-- Policy Documents Section -->
            <div class="card border-0 shadow-sm rounded-3 mt-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0 border-start border-4 border-primary ps-3 text-gov-blue fw-bold">
                            政策文件
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php
                            $upload_dir = 'uploads/';
                            if (is_dir($upload_dir)) {
                                $files = scandir($upload_dir);
                                $found_files = false;
                                foreach ($files as $file) {
                                    if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
                                        $found_files = true;
                                        echo '<a href="uploads/' . htmlspecialchars($file) . '" class="list-group-item list-group-item-action py-2" target="_blank">';
                                        echo '<div class="d-flex w-100 justify-content-between align-items-center">';
                                        echo '<span class="text-dark"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>' . htmlspecialchars($file) . '</span>';
                                        echo '<small class="text-muted">下载</small>';
                                        echo '</div>';
                                        echo '</a>';
                                    }
                                }
                                if (!$found_files) {
                                    echo '<div class="text-center text-muted py-3">暂无政策文件</div>';
                                }
                            } else {
                                echo '<div class="text-center text-muted py-3">暂无政策文件</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
 <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-gov-blue text-white py-3">
                        <h6 class="mb-0">便民服务</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary text-start feature-btn" type="button">📝 在线办事申请</button>
                            <button class="btn btn-outline-secondary text-start feature-btn" type="button">🔍 办件进度查询</button>
                            <button class="btn btn-outline-secondary text-start feature-btn" type="button">📞 12345 热线</button>
                        </div>
                        </div>
                    </div>
                </div>
                
            </div>
           
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved. | 建议使用 Chrome 或 Edge 浏览器访问</p>
        </div>
    </footer>

    <!-- Feature Not Available Modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.feature-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                Swal.fire({
                    title: '功能未开放',
                    text: '该功能正在建设中，敬请期待。',
                    icon: 'warning',
                    confirmButtonText: '知道了'
                });
            });
        });

        document.querySelectorAll('.news-item').forEach(item => {
            item.addEventListener('click', function() {
                const title = this.getAttribute('data-title');
                const date = this.getAttribute('data-date');
                Swal.fire({
                    title: title,
                    html: `
                        <p class="text-muted small mb-3">发布日期: ${date}</p>
                        <div class="text-start px-3">
                            <p>这是一个演示新闻条目。在实际系统中，这里将显示完整的新闻内容详情。</p>
                            <p>GovCore 致力于打造高效、透明的政务服务平台。</p>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: '关闭'
                });
            });
        });
    </script>
</body>
</html>
