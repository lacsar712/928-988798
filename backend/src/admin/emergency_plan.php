<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>应急预案管理 - GovCore 后台管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .sidebar .list-group-item {
            border: none;
            padding: 0.8rem 1.2rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            color: var(--gov-text-main);
            transition: all 0.2s;
        }
        .sidebar .list-group-item:hover {
            background-color: #e9ecef;
            color: var(--gov-blue-primary);
        }
        .sidebar .list-group-item.active {
            background-color: var(--gov-blue-primary);
            color: white;
            font-weight: 600;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .cls-badge-public {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .cls-badge-internal {
            background-color: rgba(255, 193, 7, 0.15);
            color: #b8860b;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-gov-blue shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">GovCore 管理中心</a>
            <span class="navbar-text text-white">
                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | <a href="logout.php" class="text-white-50 text-decoration-none">退出</a>
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar py-4 border-end bg-white" style="min-height: calc(100vh - 56px);">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i>控制台
                    </a>
                    <a href="faq.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-question-circle me-2"></i>FAQ 管理
                    </a>
                    <a href="announcement.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-megaphone me-2"></i>公告管理
                    </a>
                    <a href="emergency_contact.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-telephone-inbound me-2"></i>应急通讯录
                    </a>
                    <a href="assessment.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person-check me-2"></i>绩效评议
                    </a>
                    <a href="openday.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-event me-2"></i>开放日预约
                    </a>
                    <a href="emergency_plan.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-file-earmark-medical me-2"></i>应急预案管理
                    </a>
                    <a href="net_tool.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-broadcast me-2"></i>网络检测工具
                    </a>
                    <a href="upload.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-upload me-2"></i>政策文件上传
                    </a>
                </div>
            </div>

            <div class="col-md-10 py-4 bg-light">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded shadow-sm mb-0">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">应急预案管理</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <select class="form-select form-select-sm" style="width: 150px;" id="filterCategory" onchange="loadPlans()">
                                    <option value="">全部类别</option>
                                    <option value="自然灾害">自然灾害</option>
                                    <option value="事故灾难">事故灾难</option>
                                    <option value="公共卫生">公共卫生</option>
                                    <option value="社会安全">社会安全</option>
                                </select>
                                <select class="form-select form-select-sm" style="width: 120px;" id="filterClassification" onchange="loadPlans()">
                                    <option value="">全部密级</option>
                                    <option value="公开">公开</option>
                                    <option value="内部">内部</option>
                                </select>
                                <input type="text" class="form-control form-control-sm" style="width: 200px;" id="searchInput" placeholder="搜索预案..." onkeyup="if(event.key==='Enter')loadPlans()">
                                <button class="btn btn-outline-secondary btn-sm" onclick="loadPlans()">
                                    <i class="bi bi-search me-1"></i>搜索
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-gov-blue btn-sm" onclick="showPlanModal()">
                                    <i class="bi bi-plus-lg me-1"></i>新增预案
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3" style="width: 60px;">ID</th>
                                            <th class="px-4 py-3">编号</th>
                                            <th class="px-4 py-3">名称</th>
                                            <th class="px-4 py-3" style="width: 100px;">类别</th>
                                            <th class="px-4 py-3" style="width: 80px;">密级</th>
                                            <th class="px-4 py-3" style="width: 70px;">版本</th>
                                            <th class="px-4 py-3" style="width: 110px;">修订人</th>
                                            <th class="px-4 py-3" style="width: 100px;">发布日期</th>
                                            <th class="px-4 py-3" style="width: 70px;">PDF</th>
                                            <th class="px-4 py-3" style="width: 70px;">状态</th>
                                            <th class="px-4 py-3" style="width: 150px;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="plansBody">
                                        <tr><td colspan="11" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-file-earmark-medical me-2"></i><span id="modalTitle">新增应急预案</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="planForm" enctype="multipart/form-data">
                        <input type="hidden" id="planId" value="">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">预案编号 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="planCode" required maxlength="50" placeholder="如 YA-2024-001">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold small">预案名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="planName" required maxlength="255" placeholder="请输入预案名称">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">类别 <span class="text-danger">*</span></label>
                                <select class="form-select" id="planCategory" required>
                                    <option value="">请选择</option>
                                    <option value="自然灾害">自然灾害</option>
                                    <option value="事故灾难">事故灾难</option>
                                    <option value="公共卫生">公共卫生</option>
                                    <option value="社会安全">社会安全</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">密级 <span class="text-danger">*</span></label>
                                <select class="form-select" id="planClassification">
                                    <option value="公开">公开</option>
                                    <option value="内部">内部</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">版本号 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="planVersion" required maxlength="20" placeholder="如 1.0">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">修订人 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="planReviser" required maxlength="100" placeholder="请输入修订人">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">发布日期</label>
                                <input type="date" class="form-control" id="planPublishDate">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">状态</label>
                                <select class="form-select" id="planStatus">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">PDF 附件</label>
                            <input type="file" class="form-control" id="planPdfFile" name="pdf_file" accept=".pdf">
                            <div class="form-text text-muted"><i class="bi bi-filetype-pdf me-1"></i>仅支持 PDF 格式</div>
                        </div>
                        <div class="mb-3" id="changeSummaryGroup" style="display:none;">
                            <label class="form-label fw-bold small">修订说明</label>
                            <textarea class="form-control" id="planChangeSummary" rows="2" placeholder="请描述本次修订的主要变更内容"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="savePlan()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="revisionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-clock-history me-2"></i><span id="revModalTitle">修订历史</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>版本号</th>
                                    <th>修订人</th>
                                    <th>变更说明</th>
                                    <th>修订时间</th>
                                </tr>
                            </thead>
                            <tbody id="revisionBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        let allPlans = [];

        function api(action, data = {}, method = 'POST') {
            if (data instanceof FormData) {
                data.append('action', action);
                return fetch('../emergency_plan_api.php', {
                    method: method,
                    body: data
                }).then(r => r.json());
            }
            const formData = new URLSearchParams();
            formData.append('action', action);
            for (let key in data) {
                formData.append(key, data[key]);
            }
            return fetch('../emergency_plan_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = '../emergency_plan_api.php?action=' + action;
            for (let key in data) {
                url += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            return fetch(url).then(r => r.json());
        }

        function loadPlans() {
            apiGet('get_all_plans').then(res => {
                if (res.code === 200) {
                    allPlans = res.data;
                    renderPlans();
                }
            });
        }

        function renderPlans() {
            const tbody = document.getElementById('plansBody');
            const search = document.getElementById('searchInput').value.trim().toLowerCase();
            const filterCat = document.getElementById('filterCategory').value;
            const filterCls = document.getElementById('filterClassification').value;

            let filtered = allPlans;
            if (search) {
                filtered = filtered.filter(p => p.name.toLowerCase().includes(search) || p.plan_code.toLowerCase().includes(search));
            }
            if (filterCat) {
                filtered = filtered.filter(p => p.category === filterCat);
            }
            if (filterCls) {
                filtered = filtered.filter(p => p.classification === filterCls);
            }

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</td></tr>';
                return;
            }

            tbody.innerHTML = filtered.map(plan => {
                const clsBadge = plan.classification === '公开'
                    ? '<span class="cls-badge-public">公开</span>'
                    : '<span class="cls-badge-internal">内部</span>';
                const hasPdf = plan.pdf_file
                    ? '<i class="bi bi-file-earmark-pdf text-danger fs-5"></i>'
                    : '<span class="text-muted">-</span>';

                return `
                <tr>
                    <td class="px-4"><span class="font-monospace text-muted">#${plan.id}</span></td>
                    <td class="px-4"><span class="font-monospace small">${plan.plan_code}</span></td>
                    <td class="px-4">
                        <div class="fw-bold">${plan.name}</div>
                    </td>
                    <td class="px-4 small">${plan.category}</td>
                    <td class="px-4">${clsBadge}</td>
                    <td class="px-4 small font-monospace">v${plan.version}</td>
                    <td class="px-4 small">${plan.reviser}</td>
                    <td class="px-4 small text-muted">${plan.publish_date || '-'}</td>
                    <td class="px-4 text-center">${hasPdf}</td>
                    <td class="px-4">
                        <span class="status-badge ${plan.status ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'}">
                            ${plan.status ? '启用' : '禁用'}
                        </span>
                    </td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info" onclick="viewRevisions(${plan.id}, '${plan.name.replace(/'/g, "\\'")}')" title="修订历史">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            <button class="btn btn-outline-primary" onclick="editPlan(${plan.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deletePlan(${plan.id}, '${plan.name.replace(/'/g, "\\'")}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `}).join('');
        }

        function showPlanModal() {
            document.getElementById('modalTitle').textContent = '新增应急预案';
            document.getElementById('planId').value = '';
            document.getElementById('planCode').value = '';
            document.getElementById('planName').value = '';
            document.getElementById('planCategory').value = '';
            document.getElementById('planClassification').value = '公开';
            document.getElementById('planVersion').value = '1.0';
            document.getElementById('planReviser').value = '';
            document.getElementById('planPublishDate').value = '';
            document.getElementById('planStatus').value = '1';
            document.getElementById('planPdfFile').value = '';
            document.getElementById('planChangeSummary').value = '';
            document.getElementById('changeSummaryGroup').style.display = 'none';
            document.getElementById('planCode').removeAttribute('readonly');
            new bootstrap.Modal(document.getElementById('planModal')).show();
        }

        function editPlan(id) {
            apiGet('get_plan_detail', { id }).then(res => {
                if (res.code === 200) {
                    const d = res.data;
                    document.getElementById('modalTitle').textContent = '编辑应急预案';
                    document.getElementById('planId').value = d.id;
                    document.getElementById('planCode').value = d.plan_code;
                    document.getElementById('planName').value = d.name;
                    document.getElementById('planCategory').value = d.category;
                    document.getElementById('planClassification').value = d.classification;
                    document.getElementById('planVersion').value = d.version;
                    document.getElementById('planReviser').value = d.reviser;
                    document.getElementById('planPublishDate').value = d.publish_date || '';
                    document.getElementById('planStatus').value = d.status;
                    document.getElementById('planPdfFile').value = '';
                    document.getElementById('planChangeSummary').value = '';
                    document.getElementById('changeSummaryGroup').style.display = 'block';
                    new bootstrap.Modal(document.getElementById('planModal')).show();
                }
            });
        }

        function savePlan() {
            const id = document.getElementById('planId').value;
            const plan_code = document.getElementById('planCode').value.trim();
            const name = document.getElementById('planName').value.trim();
            const category = document.getElementById('planCategory').value;
            const classification = document.getElementById('planClassification').value;
            const version = document.getElementById('planVersion').value.trim();
            const reviser = document.getElementById('planReviser').value.trim();
            const publish_date = document.getElementById('planPublishDate').value;
            const status = document.getElementById('planStatus').value;
            const change_summary = document.getElementById('planChangeSummary').value.trim();
            const pdfFile = document.getElementById('planPdfFile').files[0];

            if (!plan_code) { Toast.fire({ icon: 'warning', title: '请输入预案编号' }); return; }
            if (!name) { Toast.fire({ icon: 'warning', title: '请输入预案名称' }); return; }
            if (!category) { Toast.fire({ icon: 'warning', title: '请选择类别' }); return; }
            if (!version) { Toast.fire({ icon: 'warning', title: '请输入版本号' }); return; }
            if (!reviser) { Toast.fire({ icon: 'warning', title: '请输入修订人' }); return; }

            const formData = new FormData();
            formData.append('plan_code', plan_code);
            formData.append('name', name);
            formData.append('category', category);
            formData.append('classification', classification);
            formData.append('version', version);
            formData.append('reviser', reviser);
            formData.append('publish_date', publish_date);
            formData.append('status', status);
            if (change_summary) formData.append('change_summary', change_summary);
            if (pdfFile) formData.append('pdf_file', pdfFile);

            const action = id ? 'update_plan' : 'add_plan';
            if (id) formData.append('id', id);

            api(action, formData).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                    bootstrap.Modal.getInstance(document.getElementById('planModal')).hide();
                    loadPlans();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deletePlan(id, name) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除预案"${name}"吗？此操作将同时删除相关修订记录和附件，不可恢复。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_plan', { id }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '已删除' });
                            loadPlans();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        function viewRevisions(id, name) {
            apiGet('get_plan_detail', { id }).then(res => {
                if (res.code === 200) {
                    const d = res.data;
                    document.getElementById('revModalTitle').textContent = name + ' - 修订历史';
                    const tbody = document.getElementById('revisionBody');
                    if (!d.revisions || d.revisions.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">暂无修订记录</td></tr>';
                    } else {
                        tbody.innerHTML = d.revisions.map(r => `
                            <tr>
                                <td class="font-monospace fw-bold">v${r.version}</td>
                                <td>${r.reviser}</td>
                                <td class="small text-muted">${r.change_summary || '-'}</td>
                                <td class="small text-muted">${r.created_at}</td>
                            </tr>
                        `).join('');
                    }
                    new bootstrap.Modal(document.getElementById('revisionModal')).show();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', loadPlans);
    </script>
</body>
</html>
