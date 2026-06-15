<?php
require_once 'func.php';

$search_keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$only_24h = isset($_GET['only_24h']) ? intval($_GET['only_24h']) : 0;

function get_departments() {
    global $conn;
    $sql = "SELECT * FROM emergency_departments WHERE status = 1 ORDER BY sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);
    $departments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = $row;
    }
    return $departments;
}

function get_contacts_by_dept($department_id, $only_24h = 0, $keyword = '') {
    global $conn;
    $dept_id = intval($department_id);
    $only = intval($only_24h);
    $where = "WHERE department_id = $dept_id AND status = 1";
    if ($only) {
        $where .= " AND is_24h = 1";
    }
    if (!empty($keyword)) {
        $keyword_esc = mysqli_real_escape_string($conn, $keyword);
        $where .= " AND (name LIKE '%$keyword_esc%' OR emergency_phone LIKE '%$keyword_esc%' OR office_phone LIKE '%$keyword_esc%' OR position LIKE '%$keyword_esc%')";
    }
    $sql = "SELECT * FROM emergency_contacts $where ORDER BY is_24h DESC, sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);
    $contacts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $contacts[] = $row;
    }
    return $contacts;
}

function get_all_contacts($only_24h = 0, $keyword = '') {
    global $conn;
    $only = intval($only_24h);
    $where = "WHERE ec.status = 1 AND ed.status = 1";
    if ($only) {
        $where .= " AND ec.is_24h = 1";
    }
    if (!empty($keyword)) {
        $keyword_esc = mysqli_real_escape_string($conn, $keyword);
        $where .= " AND (
            ec.name LIKE '%$keyword_esc%' 
            OR ec.emergency_phone LIKE '%$keyword_esc%' 
            OR ec.office_phone LIKE '%$keyword_esc%' 
            OR ec.position LIKE '%$keyword_esc%' 
            OR ed.name LIKE '%$keyword_esc%'
        )";
    }
    $sql = "SELECT ec.*, ed.name as department_name, ed.icon as department_icon 
            FROM emergency_contacts ec 
            LEFT JOIN emergency_departments ed ON ec.department_id = ed.id 
            $where 
            ORDER BY ec.is_24h DESC, ed.sort_order ASC, ec.sort_order ASC, ec.id ASC";
    $result = mysqli_query($conn, $sql);
    $contacts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $contacts[] = $row;
    }
    return $contacts;
}

function get_grouped_contacts($only_24h = 0, $keyword = '') {
    $departments = get_departments();
    $grouped = [];
    foreach ($departments as $dept) {
        $contacts = get_contacts_by_dept($dept['id'], $only_24h, $keyword);
        if (!empty($contacts) || empty($keyword)) {
            $dept['contacts'] = $contacts;
            $grouped[] = $dept;
        }
    }
    return $grouped;
}

function highlight_keyword($text, $keyword) {
    if (empty($keyword)) return $text;
    $keyword = htmlspecialchars($keyword);
    $text = htmlspecialchars($text);
    $text = preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<mark class="bg-warning text-dark px-1 rounded">$1</mark>', $text);
    return $text;
}

$departments = get_departments();
$search_mode = !empty($search_keyword);
$total_contacts = $search_mode ? get_all_contacts($only_24h, $search_keyword) : [];
$grouped = get_grouped_contacts($only_24h, $search_keyword);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>应急通讯录 - GovCore 政务公开与应急指挥平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/announcement.css" rel="stylesheet">
    <style>
        .page-header {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .dept-accordion .accordion-item {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px !important;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }
        .dept-accordion .accordion-item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .dept-accordion .accordion-button:not(.collapsed) {
            background-color: #fff5f5;
            color: #dc3545;
            box-shadow: none;
        }
        .dept-accordion .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0,0,0,0.125);
        }
        .contact-card {
            background: white;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid #f0f0f0;
            transition: all 0.2s;
        }
        .contact-card:hover {
            border-color: #dc3545;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.1);
        }
        .contact-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .badge-24h {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        .call-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .call-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }
        .office-call-btn {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
        .office-call-btn:hover {
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        .contact-info-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .contact-info-item i {
            width: 16px;
            text-align: center;
        }
        .filter-bar {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }
        .form-check-input:checked {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #9ca3af;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
            opacity: 0.5;
        }
        .dept-icon {
            font-size: 1.5rem;
            margin-right: 12px;
        }
        .contact-count {
            background: #f8f9fa;
            color: #6c757d;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 12px;
        }
        @media (max-width: 768px) {
            .page-header {
                padding: 24px 0;
            }
            .page-header h1 {
                font-size: 1.5rem;
            }
            .contact-card {
                padding: 12px;
            }
            .contact-avatar {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            .call-btn {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
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
                    <li class="nav-item"><a class="nav-link active" href="emergency_contact.php">应急通讯录</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold mb-2">
                        <i class="bi bi-telephone-inbound me-2"></i>应急通讯录
                    </h1>
                    <p class="lead mb-0 opacity-90">快速查找各部门应急联系人，24小时守护您的安全</p>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <form action="emergency_contact.php" method="GET" id="searchForm">
                        <div class="input-group input-group-lg">
                            <input type="text" name="keyword" class="form-control border-0 shadow-sm" placeholder="搜索姓名/电话/部门..." value="<?php echo htmlspecialchars($search_keyword); ?>" aria-label="搜索">
                            <?php if ($only_24h): ?>
                                <input type="hidden" name="only_24h" value="1">
                            <?php endif; ?>
                            <button class="btn btn-light text-danger" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="filter-bar">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-muted"></i>
                    <span class="text-muted small me-2">筛选：</span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="only24h" <?php echo $only_24h ? 'checked' : ''; ?> onchange="toggle24hFilter()">
                        <label class="form-check-label small" for="only24h">仅看 24h 应急</label>
                    </div>
                </div>
                <div class="text-muted small">
                    共 <strong class="text-danger"><?php echo count($departments); ?></strong> 个部门
                    <?php if ($search_mode): ?>
                    ，找到 <strong class="text-danger"><?php echo count($total_contacts); ?></strong> 位联系人
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (empty($grouped)): ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body empty-state">
                    <i class="bi bi-telephone-x"></i>
                    <h5 class="mb-2">暂无相关联系人</h5>
                    <p class="mb-4"><?php echo $search_mode ? '没有找到与 "' . htmlspecialchars($search_keyword) . '" 相关的联系人' : '暂无应急联系人'; ?></p>
                    <?php if ($search_mode || $only_24h): ?>
                    <a href="emergency_contact.php" class="btn btn-outline-danger">
                        <i class="bi bi-arrow-left me-2"></i>查看全部
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <?php if ($search_mode): ?>
                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info-emphasis mb-4">
                    <i class="bi bi-search me-2"></i>
                    搜索 "<strong><?php echo htmlspecialchars($search_keyword); ?></strong> 
                    共找到 <strong><?php echo count($total_contacts); ?></strong> 位联系人
                </div>
            <?php endif; ?>

            <div class="accordion dept-accordion" id="deptAccordion">
                <?php 
                $dept_index = 0;
                foreach ($grouped as $dept): 
                    $dept_index++;
                    $contacts = $dept['contacts'];
                    $contact_count = count($contacts);
                ?>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="deptHeading<?php echo $dept_index; ?>">
                            <button class="accordion-button collapsed py-4 px-4" type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#deptCollapse<?php echo $dept_index; ?>" 
                                    aria-expanded="false" 
                                    aria-controls="deptCollapse<?php echo $dept_index; ?>">
                                <div class="d-flex align-items-center w-100">
                                    <i class="bi <?php echo !empty($dept['icon']) ? htmlspecialchars($dept['icon']) : 'bi-building'; ?> dept-icon text-danger"></i>
                                    <span class="fw-bold fs-5"><?php echo htmlspecialchars($dept['name']); ?></span>
                                    <span class="contact-count"><?php echo $contact_count; ?> 人</span>
                                </div>
                            </button>
                        </h2>
                        <div id="deptCollapse<?php echo $dept_index; ?>" class="accordion-collapse collapse" 
                             aria-labelledby="deptHeading<?php echo $dept_index; ?>" 
                             data-bs-parent="#deptAccordion">
                            <div class="accordion-body px-4 pb-4 pt-0">
                                <?php if (empty($contacts)): ?>
                                    <div class="text-center py-4 text-muted">
                                        <small>该部门暂无联系人</small>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($contacts as $contact): ?>
                                        <div class="contact-card">
                                            <div class="d-flex gap-3">
                                            <div class="contact-avatar">
                                                    <?php echo mb_substr($contact['name'], 0, 1); ?>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                                        <h6 class="mb-0 fw-bold"><?php echo $search_mode ? highlight_keyword($contact['name'], $search_keyword) : htmlspecialchars($contact['name']); ?></h6>
                                                        <?php if (!empty($contact['is_24h'])): ?>
                                                            <span class="badge-24h">
                                                                <i class="bi bi-clock-history me-1"></i>24h应急
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if ($search_mode && !empty($contact['department_name'])): ?>
                                                            <span class="badge bg-light text-dark small">
                                                                <i class="bi bi-building me-1"></i><?php echo highlight_keyword($contact['department_name'], $search_keyword); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($contact['position'])): ?>
                                                    <div class="text-muted small mb-2">
                                                            <?php echo $search_mode ? highlight_keyword($contact['position'], $search_keyword) : htmlspecialchars($contact['position']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="row g-2 mt-2">
                                                        <?php if (!empty($contact['emergency_phone'])): ?>
                                                        <div class="col-md-6">
                                                            <div class="contact-info-item">
                                                                <i class="bi bi-telephone-forward text-danger"></i>
                                                                <span class="text-muted">应急电话：</span>
                                                                <span class="fw-medium">
                                                                    <?php echo $search_mode ? highlight_keyword($contact['emergency_phone'], $search_keyword) : htmlspecialchars($contact['emergency_phone']); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($contact['office_phone'])): ?>
                                                        <div class="col-md-6">
                                                            <div class="contact-info-item">
                                                                <i class="bi bi-telephone text-primary"></i>
                                                                <span class="text-muted">办公电话：</span>
                                                                <span>
                                                                    <?php echo $search_mode ? highlight_keyword($contact['office_phone'], $search_keyword) : htmlspecialchars($contact['office_phone']); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($contact['email'])): ?>
                                                        <div class="col-md-6">
                                                            <div class="contact-info-item">
                                                                <i class="bi bi-envelope text-success"></i>
                                                                <span class="text-muted">邮箱：</span>
                                                                <span><?php echo htmlspecialchars($contact['email']); ?></span>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($contact['duty_time'])): ?>
                                                        <div class="col-md-6">
                                                            <div class="contact-info-item">
                                                                <i class="bi bi-clock text-warning"></i>
                                                                <span class="text-muted">值班时间：</span>
                                                                <span><?php echo htmlspecialchars($contact['duty_time']); ?></span>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-2 flex-shrink-0">
                                                    <?php if (!empty($contact['emergency_phone'])): ?>
                                                        <a href="tel:<?php echo htmlspecialchars($contact['emergency_phone']); ?>" class="call-btn" title="一键拨号">
                                                            <i class="bi bi-telephone-forward"></i>
                                                            <span class="d-none d-sm-inline">应急拨号</span>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($contact['office_phone'])): ?>
                                                        <a href="tel:<?php echo htmlspecialchars($contact['office_phone']); ?>" class="call-btn office-call-btn" title="办公拨号">
                                                            <i class="bi bi-telephone"></i>
                                                            <span class="d-none d-sm-inline">办公拨号</span>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3 mt-4">
            <div class="card-body text-center py-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-danger fs-1 mb-2">
                            <i class="bi bi-telephone-forward"></i>
                        </div>
                        <h5 class="fw-bold mb-1">火警电话</h5>
                        <p class="display-6 fw-bold text-danger mb-0">119</p>
                    </div>
                    <div class="col-md-4">
                        <div class="text-primary fs-1 mb-2">
                            <i class="bi bi-ambulance"></i>
                        </div>
                        <h5 class="fw-bold mb-1">急救电话</h5>
                        <p class="display-6 fw-bold text-primary mb-0">120</p>
                    </div>
                    <div class="col-md-4">
                        <div class="text-success fs-1 mb-2">
                            <i class="bi bi-shield-exclamation"></i>
                        </div>
                        <h5 class="fw-bold mb-1">报警电话</h5>
                        <p class="display-6 fw-bold text-success mb-0">110</p>
                    </div>
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
    <script>
        function toggle24hFilter() {
            const checkbox = document.getElementById('only24h');
            const checked = checkbox.checked;
            const url = new URL(window.location.href);
            if (checked) {
                url.searchParams.set('only_24h', '1');
            } else {
                url.searchParams.delete('only_24h');
            }
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const expand = urlParams.get('expand');
            if (expand === 'all') {
                document.querySelectorAll('.accordion-collapse').forEach(el => {
                    el.classList.add('show');
                });
                document.querySelectorAll('.accordion-button').forEach(btn => {
                    btn.classList.remove('collapsed');
                });
            }
        });
    </script>
</body>
</html>
