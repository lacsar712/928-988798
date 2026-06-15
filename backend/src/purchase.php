<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>政府采购公告 - GovCore 政务公开与应急指挥平台</title>
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
        .nav-tabs .nav-link {
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: var(--gov-text-muted);
            border-bottom: 2px solid transparent;
        }
        .nav-tabs .nav-link.active {
            color: var(--gov-blue-primary);
            border-bottom-color: var(--gov-blue-primary);
            background: transparent;
        }
        .nav-tabs {
            border-bottom: 1px solid #e9ecef;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .status-招标中 { background: #fff3cd; color: #856404; }
        .status-已截止 { background: #e2e3e5; color: #383d41; }
        .status-已成交 { background: #d4edda; color: #155724; }
        .status-流标 { background: #f8d7da; color: #721c24; }
        .row-已成交 {
            background-color: #d4edda !important;
        }
        .row-已成交:hover {
            background-color: #c3e6cb !important;
        }
        .budget-amount {
            font-weight: 600;
            color: var(--gov-blue-primary);
        }
        .filter-card {
            border-radius: var(--card-radius);
        }
        .purchase-table th {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold mb-2"><i class="bi bi-file-earmark-text me-2"></i>政府采购公告</h1>
                    <p class="lead mb-0 opacity-90">公开透明 · 阳光采购 · 规范高效</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="card border-0 shadow-sm filter-card rounded-3 mb-4">
            <div class="card-body py-3 px-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">关键字</label>
                        <input type="text" class="form-control form-control-sm" id="filterKeyword" placeholder="项目名 / 采购单位">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">预算金额(元) 起</label>
                        <input type="number" class="form-control form-control-sm" id="filterBudgetMin" placeholder="最低金额">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">预算金额(元) 止</label>
                        <input type="number" class="form-control form-control-sm" id="filterBudgetMax" placeholder="最高金额">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-gov-blue btn-sm w-100" onclick="loadPurchases()">
                            <i class="bi bi-search me-1"></i>筛选
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4" id="statusTabs">
            <li class="nav-item">
                <button class="nav-link active" data-status="" onclick="switchTab(this, '')">全部</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-status="招标中" onclick="switchTab(this, '招标中')">招标中</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-status="已截止" onclick="switchTab(this, '已截止')">已截止</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-status="已成交" onclick="switchTab(this, '已成交')">已成交</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-status="流标" onclick="switchTab(this, '流标')">流标</button>
            </li>
        </ul>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 purchase-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">项目名称</th>
                                <th class="px-4 py-3">采购单位</th>
                                <th class="px-4 py-3" style="width: 140px;">预算金额</th>
                                <th class="px-4 py-3" style="width: 100px;">状态</th>
                                <th class="px-4 py-3" style="width: 160px;">报名截止</th>
                                <th class="px-4 py-3" style="width: 100px;">操作</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseBody">
                            <tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
                        </tbody>
                    </table>
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
        let currentStatus = '';

        function formatAmount(val) {
            if (!val) return '-';
            return '¥' + parseFloat(val).toLocaleString('zh-CN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function formatDate(val) {
            if (!val) return '-';
            return val.slice(0, 16).replace('T', ' ');
        }

        function loadPurchases() {
            const keyword = document.getElementById('filterKeyword').value.trim();
            const budgetMin = document.getElementById('filterBudgetMin').value;
            const budgetMax = document.getElementById('filterBudgetMax').value;

            let url = 'purchase_api.php?action=list_purchases';
            if (currentStatus) url += '&status=' + encodeURIComponent(currentStatus);
            if (keyword) url += '&keyword=' + encodeURIComponent(keyword);
            if (budgetMin !== '') url += '&budget_min=' + encodeURIComponent(budgetMin);
            if (budgetMax !== '') url += '&budget_max=' + encodeURIComponent(budgetMax);

            fetch(url).then(r => r.json()).then(res => {
                const tbody = document.getElementById('purchaseBody');
                if (res.code === 200 && res.data.length > 0) {
                    tbody.innerHTML = res.data.map(item => {
                        const rowClass = item.status === '已成交' ? 'row-已成交' : '';
                        return `
                        <tr class="${rowClass}">
                            <td class="px-4">
                                <span class="fw-bold">${item.project_name}</span>
                            </td>
                            <td class="px-4 text-muted">${item.procurement_unit}</td>
                            <td class="px-4 budget-amount">${formatAmount(item.budget_amount)}</td>
                            <td class="px-4"><span class="status-badge status-${item.status}">${item.status}</span></td>
                            <td class="px-4 small text-muted">${formatDate(item.deadline)}</td>
                            <td class="px-4">
                                <a href="purchase_detail.php?id=${item.id}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>详情
                                </a>
                            </td>
                        </tr>`;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无采购公告</td></tr>';
                }
            });
        }

        function switchTab(el, status) {
            document.querySelectorAll('#statusTabs .nav-link').forEach(tab => tab.classList.remove('active'));
            el.classList.add('active');
            currentStatus = status;
            loadPurchases();
        }

        document.getElementById('filterKeyword').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') loadPurchases();
        });

        document.addEventListener('DOMContentLoaded', () => {
            loadPurchases();
        });
    </script>
</body>
</html>
