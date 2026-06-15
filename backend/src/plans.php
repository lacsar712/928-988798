<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>应急预案文档库 - GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .category-tree .list-group-item {
            border: none;
            padding: 0.75rem 1rem;
            margin-bottom: 4px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .category-tree .list-group-item:hover {
            background-color: var(--gov-blue-light);
            color: var(--gov-blue-primary);
        }
        .category-tree .list-group-item.active {
            background-color: var(--gov-blue-primary);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,77,153,0.3);
        }
        .category-tree .list-group-item .badge {
            font-size: 0.7rem;
            font-weight: 600;
        }
        .plan-card {
            transition: all 0.2s;
            border-left: 4px solid var(--gov-blue-primary);
            cursor: pointer;
        }
        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .plan-card .plan-name {
            font-weight: 700;
            color: var(--gov-text-main);
            font-size: 1rem;
        }
        .plan-card .plan-code {
            font-family: monospace;
            color: var(--gov-text-muted);
            font-size: 0.8rem;
        }
        .plan-card .plan-meta {
            font-size: 0.85rem;
            color: var(--gov-text-muted);
        }
        .plan-card .plan-meta i {
            color: var(--gov-blue-primary);
            width: 16px;
        }
        .cls-badge-public {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .cls-badge-internal {
            background-color: rgba(255, 193, 7, 0.15);
            color: #b8860b;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .category-icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .cat-nature { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
        .cat-accident { background-color: rgba(255, 193, 7, 0.15); color: #b8860b; }
        .cat-health { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .cat-security { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .filter-chip {
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }
        .filter-chip:hover {
            border-color: var(--gov-blue-primary);
            color: var(--gov-blue-primary);
        }
        .filter-chip.active {
            background-color: var(--gov-blue-primary);
            color: white;
            border-color: var(--gov-blue-primary);
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
                    <li class="nav-item"><a class="nav-link" href="assessment.php">绩效评议</a></li>
                    <li class="nav-item"><a class="nav-link" href="openday.php">开放日预约</a></li>
                    <li class="nav-item"><a class="nav-link" href="social_insurance.php">公积金社保</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 border-start border-4 border-danger ps-3 text-gov-blue fw-bold">
                    <i class="bi bi-file-earmark-medical me-2"></i>应急预案文档库
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">
                    查阅和下载各类应急预案文档。公开级别预案可直接下载，内部级别预案需登录后台后下载。
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 text-gov-blue fw-bold">
                            <i class="bi bi-folder2-open me-2"></i>预案类别
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="category-tree list-group list-group-flush" id="categoryTree">
                            <div class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div>加载中...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small fw-bold">密级筛选：</span>
                            <span class="filter-chip active" data-cls="" onclick="filterClassification('')">全部</span>
                            <span class="filter-chip" data-cls="公开" onclick="filterClassification('公开')">
                                <i class="bi bi-unlock me-1"></i>公开
                            </span>
                            <span class="filter-chip" data-cls="内部" onclick="filterClassification('内部')">
                                <i class="bi bi-lock me-1"></i>内部
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" style="width: 200px;" id="searchInput" placeholder="搜索预案名称/编号..." onkeyup="if(event.key==='Enter')loadPlans()">
                            <button class="btn btn-outline-secondary btn-sm" onclick="loadPlans()">
                                <i class="bi bi-search me-1"></i>搜索
                            </button>
                        </div>
                    </div>
                </div>

                <div id="plansList">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2"></div>加载中...
                    </div>
                </div>

                <nav id="paginationNav" class="mt-3"></nav>
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
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });

        let currentCategory = '';
        let currentClassification = '';
        let currentPage = 1;

        const catClassMap = {
            '自然灾害': 'cat-nature',
            '事故灾难': 'cat-accident',
            '公共卫生': 'cat-health',
            '社会安全': 'cat-security'
        };

        function apiGet(action, data = {}) {
            let url = 'emergency_plan_api.php?action=' + action;
            for (let key in data) {
                url += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            return fetch(url).then(r => r.json());
        }

        function loadCategoryTree() {
            apiGet('get_category_tree').then(res => {
                if (res.code === 200) {
                    renderCategoryTree(res.data);
                }
            });
        }

        function renderCategoryTree(data) {
            const container = document.getElementById('categoryTree');
            let html = `
                <div class="list-group-item list-group-item-action active" onclick="selectCategory('')" data-cat="">
                    <div class="d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-grid me-2"></i>全部预案</span>
                        <span class="badge bg-gov-blue">${data.total}</span>
                    </div>
                </div>
            `;
            data.categories.forEach(cat => {
                const cls = catClassMap[cat.name] || '';
                html += `
                    <div class="list-group-item list-group-item-action" onclick="selectCategory('${cat.name}')" data-cat="${cat.name}">
                        <div class="d-flex align-items-center justify-content-between">
                            <span><i class="${cat.icon} me-2"></i>${cat.name}</span>
                            <span class="badge bg-secondary">${cat.count}</span>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function selectCategory(cat) {
            currentCategory = cat;
            currentPage = 1;
            document.querySelectorAll('.category-tree .list-group-item').forEach(el => {
                el.classList.toggle('active', el.getAttribute('data-cat') === cat);
            });
            loadPlans();
        }

        function filterClassification(cls) {
            currentClassification = cls;
            currentPage = 1;
            document.querySelectorAll('.filter-chip').forEach(el => {
                el.classList.toggle('active', el.getAttribute('data-cls') === cls);
            });
            loadPlans();
        }

        function loadPlans() {
            const keyword = document.getElementById('searchInput').value.trim();
            const params = { page: currentPage, page_size: 10 };
            if (currentCategory) params.category = currentCategory;
            if (currentClassification) params.classification = currentClassification;
            if (keyword) params.keyword = keyword;

            apiGet('get_plans', params).then(res => {
                if (res.code === 200) {
                    renderPlans(res.data);
                }
            });
        }

        function renderPlans(data) {
            const container = document.getElementById('plansList');
            const plans = data.list;

            if (plans.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-3"></i>
                        暂无符合条件的预案
                    </div>
                `;
                document.getElementById('paginationNav').innerHTML = '';
                return;
            }

            container.innerHTML = plans.map(plan => {
                const clsBadge = plan.classification === '公开'
                    ? '<span class="cls-badge-public"><i class="bi bi-unlock me-1"></i>公开</span>'
                    : '<span class="cls-badge-internal"><i class="bi bi-lock me-1"></i>内部</span>';
                const catCls = catClassMap[plan.category] || '';

                return `
                <div class="card plan-card border-0 shadow-sm rounded-3 mb-3" onclick="viewPlan(${plan.id})">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="category-icon-circle ${catCls}"><i class="bi bi-bookmark-fill"></i></span>
                                    <div>
                                        <div class="plan-name">${plan.name}</div>
                                        <div class="plan-code">${plan.plan_code}</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 plan-meta mt-2">
                                    <span><i class="bi bi-tag"></i>${plan.category}</span>
                                    <span><i class="bi bi-git"></i>v${plan.version}</span>
                                    <span><i class="bi bi-person"></i>${plan.reviser}</span>
                                    <span><i class="bi bi-calendar3"></i>${plan.publish_date || '-'}</span>
                                </div>
                            </div>
                            <div class="ms-3">
                                ${clsBadge}
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }).join('');

            renderPagination(data);
        }

        function renderPagination(data) {
            const total = data.total;
            const pageSize = data.page_size;
            const totalPages = Math.ceil(total / pageSize);
            const nav = document.getElementById('paginationNav');

            if (totalPages <= 1) {
                nav.innerHTML = `<div class="text-muted small text-center">共 ${total} 条记录</div>`;
                return;
            }

            let html = `<div class="d-flex align-items-center justify-content-between">
                <span class="text-muted small">共 ${total} 条记录，第 ${data.page}/${totalPages} 页</span>
                <ul class="pagination pagination-sm mb-0">`;

            html += `<li class="page-item ${data.page <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goPage(${data.page - 1}); return false;">&laquo;</a></li>`;

            const start = Math.max(1, data.page - 2);
            const end = Math.min(totalPages, data.page + 2);
            for (let i = start; i <= end; i++) {
                html += `<li class="page-item ${i === data.page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goPage(${i}); return false;">${i}</a></li>`;
            }

            html += `<li class="page-item ${data.page >= totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goPage(${data.page + 1}); return false;">&raquo;</a></li>`;

            html += '</ul></div>';
            nav.innerHTML = html;
        }

        function goPage(page) {
            currentPage = page;
            loadPlans();
            window.scrollTo({ top: 300, behavior: 'smooth' });
        }

        function viewPlan(id) {
            window.location.href = 'plan_detail.php?id=' + id;
        }

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

        document.addEventListener('DOMContentLoaded', function() {
            loadCategoryTree();
            loadPlans();
        });
    </script>
</body>
</html>
