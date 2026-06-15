<?php
require_once 'func.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo '<script>window.location.href="purchase.php";</script>';
    exit;
}

$sql = "SELECT * FROM purchases WHERE id = $id";
$result = mysqli_query($conn, $sql);
$purchase = mysqli_fetch_assoc($result);

if (!$purchase) {
    echo '<script>window.location.href="purchase.php";</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($purchase['project_name']); ?> - GovCore 政务公开与应急指挥平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/announcement.css" rel="stylesheet">
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--gov-blue-primary) 0%, var(--gov-blue-dark) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .status-招标中 { background: #fff3cd; color: #856404; }
        .status-已截止 { background: #e2e3e5; color: #383d41; }
        .status-已成交 { background: #d4edda; color: #155724; }
        .status-流标 { background: #f8d7da; color: #721c24; }
        .detail-section {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .info-grid dt {
            color: var(--gov-text-muted);
            font-weight: 500;
            font-size: 0.9rem;
        }
        .info-grid dd {
            font-weight: 600;
            color: var(--gov-text-main);
        }
        .winner-card {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 1px solid #b1dfbb;
            border-radius: var(--card-radius);
            padding: 1.5rem;
        }
        .content-body {
            line-height: 1.8;
            color: #374151;
        }
        .content-body ul, .content-body ol {
            padding-left: 24px;
            margin-bottom: 12px;
        }
        .content-body li {
            margin-bottom: 6px;
        }
        .content-body strong {
            color: var(--gov-blue-primary);
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
                    <li class="nav-item"><a class="nav-link" href="plans.php">应急预案文档库</a></li>
                    <li class="nav-item"><a class="nav-link active" href="purchase.php">政府采购</a></li>
                    <li class="nav-item"><a class="nav-link" href="assessment.php">绩效评议</a></li>
                    <li class="nav-item"><a class="nav-link" href="openday.php">开放日预约</a></li>
                    <li class="nav-item"><a class="nav-link" href="social_insurance.php">公积金社保</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-white-50 text-decoration-none">首页</a></li>
                    <li class="breadcrumb-item"><a href="purchase.php" class="text-white-50 text-decoration-none">政府采购</a></li>
                    <li class="breadcrumb-item active text-white">公告详情</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($purchase['project_name']); ?></h2>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="status-badge status-<?php echo $purchase['status']; ?>"><?php echo $purchase['status']; ?></span>
                <span class="opacity-75"><i class="bi bi-building me-1"></i><?php echo htmlspecialchars($purchase['procurement_unit']); ?></span>
                <span class="opacity-75"><i class="bi bi-calendar3 me-1"></i>发布于 <?php echo date('Y-m-d', strtotime($purchase['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="detail-section">
                    <h5 class="text-gov-blue fw-bold mb-4 border-start border-4 ps-3">
                        <i class="bi bi-file-text me-2"></i>公告内容
                    </h5>
                    <div class="content-body">
                        <?php echo $purchase['content']; ?>
                    </div>
                </div>

                <?php if ($purchase['status'] === '已成交' && !empty($purchase['winner'])): ?>
                <div class="winner-card mb-4">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="bi bi-trophy-fill me-2"></i>中标结果
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-check-fill text-success me-2 fs-5"></i>
                                <div>
                                    <div class="text-muted small">中标人</div>
                                    <div class="fw-bold fs-5"><?php echo htmlspecialchars($purchase['winner']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cash-stack text-success me-2 fs-5"></i>
                                <div>
                                    <div class="text-muted small">中标金额</div>
                                    <div class="fw-bold fs-5 text-success">¥<?php echo number_format(floatval($purchase['winning_amount']), 2); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($purchase['attachment'])): ?>
                <div class="detail-section">
                    <h5 class="text-gov-blue fw-bold mb-3 border-start border-4 ps-3">
                        <i class="bi bi-paperclip me-2"></i>相关附件
                    </h5>
                    <a href="purchase_api.php?action=download_attachment&id=<?php echo $purchase['id']; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-download me-2"></i><?php echo htmlspecialchars($purchase['attachment']); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="detail-section">
                    <h6 class="text-gov-blue fw-bold mb-3">
                        <i class="bi bi-info-circle me-2"></i>项目信息
                    </h6>
                    <dl class="info-grid row g-2">
                        <dt class="col-sm-5">采购单位</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($purchase['procurement_unit']); ?></dd>

                        <dt class="col-sm-5">预算金额</dt>
                        <dd class="col-sm-7 text-gov-blue">¥<?php echo number_format(floatval($purchase['budget_amount']), 2); ?></dd>

                        <dt class="col-sm-5">项目状态</dt>
                        <dd class="col-sm-7"><span class="status-badge status-<?php echo $purchase['status']; ?>"><?php echo $purchase['status']; ?></span></dd>

                        <dt class="col-sm-5">报名截止</dt>
                        <dd class="col-sm-7"><?php echo $purchase['deadline'] ? date('Y-m-d H:i', strtotime($purchase['deadline'])) : '-'; ?></dd>

                        <dt class="col-sm-5">发布时间</dt>
                        <dd class="col-sm-7"><?php echo date('Y-m-d H:i', strtotime($purchase['created_at'])); ?></dd>
                    </dl>
                </div>

                <div class="detail-section">
                    <a href="purchase.php" class="btn btn-gov-blue w-100">
                        <i class="bi bi-arrow-left me-2"></i>返回列表
                    </a>
                </div>
            </div>
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
    <script src="assets/js/announcement.js"></script>
</body>
</html>
