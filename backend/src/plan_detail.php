<?php
require_once 'func.php';

$plan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($plan_id <= 0) {
    die('无效的预案ID');
}

$sql = "SELECT * FROM emergency_plans WHERE id = $plan_id AND status = 1";
$result = mysqli_query($conn, $sql);
$plan = mysqli_fetch_assoc($result);
if (!$plan) {
    die('预案不存在');
}

$sql_rev = "SELECT * FROM emergency_plan_revisions WHERE plan_id = $plan_id ORDER BY created_at DESC";
$rev_result = mysqli_query($conn, $sql_rev);
$revisions = [];
while ($row = mysqli_fetch_assoc($rev_result)) {
    $revisions[] = $row;
}

$catClassMap = [
    '自然灾害' => 'cat-nature',
    '事故灾难' => 'cat-accident',
    '公共卫生' => 'cat-health',
    '社会安全' => 'cat-security'
];
$catCls = isset($catClassMap[$plan['category']]) ? $catClassMap[$plan['category']] : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($plan['name']); ?> - 应急预案详情 - GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .meta-label {
            font-size: 0.8rem;
            color: var(--gov-text-muted);
            margin-bottom: 2px;
        }
        .meta-value {
            font-weight: 600;
            color: var(--gov-text-main);
            font-size: 0.95rem;
        }
        .cls-badge-public {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .cls-badge-internal {
            background-color: rgba(255, 193, 7, 0.15);
            color: #b8860b;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .category-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        .cat-nature { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .cat-accident { background-color: rgba(255, 193, 7, 0.15); color: #b8860b; }
        .cat-health { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .cat-security { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .revision-item {
            position: relative;
            padding-left: 30px;
            padding-bottom: 20px;
            border-left: 2px solid #e9ecef;
        }
        .revision-item:last-child {
            border-left-color: transparent;
            padding-bottom: 0;
        }
        .revision-dot {
            position: absolute;
            left: -7px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--gov-blue-primary);
            border: 2px solid white;
            box-shadow: 0 0 0 2px var(--gov-blue-primary);
        }
        .revision-item:not(:first-child) .revision-dot {
            background-color: #adb5bd;
            box-shadow: 0 0 0 2px #adb5bd;
        }
    </style>
</head>
<body class="bg-light">

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
                    <li class="nav-item"><a class="nav-link" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="faq.php">常见问题</a></li>
                    <li class="nav-item"><a class="nav-link" href="emergency_contact.php">应急通讯录</a></li>
                    <li class="nav-item"><a class="nav-link active" href="plans.php">应急预案文档库</a></li>
                    <li class="nav-item"><a class="nav-link" href="purchase.php">政府采购</a></li>
                    <li class="nav-item"><a class="nav-link" href="assessment.php">绩效评议</a></li>
                    <li class="nav-item"><a class="nav-link" href="openday.php">开放日预约</a></li>
                    <li class="nav-item"><a class="nav-link" href="social_insurance.php">公积金社保</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded shadow-sm mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                <li class="breadcrumb-item"><a href="plans.php" class="text-decoration-none text-muted">应急预案文档库</a></li>
                <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page"><?php echo htmlspecialchars($plan['name']); ?></li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="category-icon-circle <?php echo $catCls; ?>">
                            <i class="bi bi-bookmark-fill"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-gov-blue"><?php echo htmlspecialchars($plan['name']); ?></h4>
                            <span class="font-monospace text-muted small"><?php echo htmlspecialchars($plan['plan_code']); ?></span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <?php if ($plan['classification'] === '公开'): ?>
                            <span class="cls-badge-public"><i class="bi bi-unlock me-1"></i>公开</span>
                        <?php else: ?>
                            <span class="cls-badge-internal"><i class="bi bi-lock me-1"></i>内部</span>
                        <?php endif; ?>
                        <?php if ($plan['pdf_file']): ?>
                            <button class="btn btn-gov-blue btn-sm" onclick="downloadPlan(<?php echo $plan['id']; ?>, '<?php echo $plan['classification']; ?>')">
                                <i class="bi bi-download me-1"></i>下载PDF
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="meta-label">预案编号</div>
                            <div class="meta-value font-monospace"><?php echo htmlspecialchars($plan['plan_code']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="meta-label">预案类别</div>
                            <div class="meta-value"><?php echo htmlspecialchars($plan['category']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="meta-label">密级</div>
                            <div class="meta-value"><?php echo htmlspecialchars($plan['classification']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="meta-label">当前版本</div>
                            <div class="meta-value">v<?php echo htmlspecialchars($plan['version']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="meta-label">修订人</div>
                            <div class="meta-value"><?php echo htmlspecialchars($plan['reviser']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="meta-label">发布日期</div>
                            <div class="meta-value"><?php echo $plan['publish_date'] ? htmlspecialchars($plan['publish_date']) : '-'; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="meta-label">创建时间</div>
                            <div class="meta-value small"><?php echo $plan['created_at']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <div class="meta-label">最近更新</div>
                            <div class="meta-value small"><?php echo $plan['updated_at']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 text-gov-blue fw-bold">
                    <i class="bi bi-clock-history me-2"></i>修订历史
                </h6>
            </div>
            <div class="card-body p-4">
                <?php if (empty($revisions)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-3"></i>
                        暂无修订记录
                    </div>
                <?php else: ?>
                    <div class="revisions-timeline">
                        <?php foreach ($revisions as $i => $rev): ?>
                            <div class="revision-item">
                                <div class="revision-dot"></div>
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <div class="fw-bold text-gov-blue">
                                            版本 <?php echo htmlspecialchars($rev['version']); ?>
                                            <?php if ($i === 0): ?>
                                                <span class="badge bg-gov-blue ms-2" style="font-size:0.7rem;">当前</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($rev['reviser']); ?>
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-clock me-1"></i><?php echo $rev['created_at']; ?>
                                        </div>
                                        <?php if (!empty($rev['change_summary'])): ?>
                                            <div class="mt-2 p-2 bg-light rounded small">
                                                <?php echo htmlspecialchars($rev['change_summary']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <a href="plans.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>返回预案列表
            </a>
        </div>
    </div>

    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved. | 建议使用 Chrome 或 Edge 浏览器访问</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function downloadPlan(id, classification) {
            if (classification === '内部') {
                <?php
                $is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
                ?>
                const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
                if (!isLoggedIn) {
                    Swal.fire({
                        title: '需要登录',
                        html: '该预案为<strong>内部文件</strong>，需要登录管理后台后才能下载。<br><small class="text-muted">请登录后刷新此页面</small>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '前往登录',
                        cancelButtonText: '取消'
                    }).then(r => {
                        if (r.isConfirmed) {
                            window.open('admin/login.php', '_blank');
                        }
                    });
                    return;
                }
            }
            window.open('emergency_plan_api.php?action=download_plan&id=' + id, '_blank');
        }
    </script>
</body>
</html>
