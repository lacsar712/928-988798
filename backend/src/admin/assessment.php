<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>绩效评议管理 - GovCore 后台管理</title>
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
        .dept-tag, .ind-tag {
            display: inline-block;
            background: var(--gov-blue-light);
            color: var(--gov-blue-primary);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin: 2px;
        }
        .tag-remove {
            cursor: pointer;
            margin-left: 4px;
            opacity: 0.7;
        }
        .tag-remove:hover {
            opacity: 1;
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
                    <a href="purchase.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cart-check me-2"></i>政府采购管理
                    </a>
                    <a href="emergency_contact.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-telephone-inbound me-2"></i>应急通讯录
                    </a>
                    <a href="emergency_plan.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-medical me-2"></i>应急预案管理
                    </a>
                    <a href="assessment.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-person-check me-2"></i>绩效评议
                    </a>
                    <a href="openday.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-event me-2"></i>开放日预约
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">绩效评议</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <input type="text" class="form-control form-control-sm" style="width: 200px;" id="searchInput" placeholder="搜索活动..." onkeyup="if(event.key==='Enter')loadActivities()">
                                <button class="btn btn-outline-secondary btn-sm" onclick="loadActivities()">
                                    <i class="bi bi-search me-1"></i>搜索
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-gov-blue btn-sm" onclick="showActivityModal()">
                                    <i class="bi bi-plus-lg me-1"></i>新增活动
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
                                            <th class="px-4 py-3">活动标题</th>
                                            <th class="px-4 py-3" style="width: 200px;">活动时间</th>
                                            <th class="px-4 py-3" style="width: 80px;">状态</th>
                                            <th class="px-4 py-3" style="width: 160px;">更新时间</th>
                                            <th class="px-4 py-3" style="width: 200px;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="activitiesBody">
                                        <tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-pencil-square me-2"></i><span id="modalTitle">新增评议活动</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="activityForm">
                        <input type="hidden" id="activityId" value="">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">活动标题 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="activityTitle" required maxlength="255" placeholder="请输入活动标题">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">开始时间 <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="startTime" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">结束时间 <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="endTime" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">状态</label>
                            <select class="form-select" id="activityStatus">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">参评部门 <span class="text-danger">*</span></label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="deptInput" placeholder="输入部门名称后按回车添加" onkeydown="if(event.key==='Enter'){event.preventDefault();addDept();}">
                                <button class="btn btn-outline-secondary" type="button" onclick="addDept()">添加</button>
                            </div>
                            <div class="border rounded p-2 bg-light" style="min-height: 42px;" id="deptTags">
                                <span class="text-muted small">暂无部门，请添加</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">评议指标 <span class="text-danger">*</span></label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="indInput" placeholder="输入指标名称后按回车添加（如：服务态度）" onkeydown="if(event.key==='Enter'){event.preventDefault();addInd();}">
                                <button class="btn btn-outline-secondary" type="button" onclick="addInd()">添加</button>
                            </div>
                            <div class="border rounded p-2 bg-light" style="min-height: 42px;" id="indTags">
                                <span class="text-muted small">暂无指标，请添加</span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveActivity()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
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

        let allActivities = [];
        let deptList = [];
        let indList = [];

        function api(action, data = {}, method = 'POST') {
            const formData = new URLSearchParams();
            formData.append('action', action);
            for (let key in data) {
                formData.append(key, data[key]);
            }
            return fetch('../assessment_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = '../assessment_api.php?action=' + action;
            for (let key in data) {
                url += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            return fetch(url).then(r => r.json());
        }

        function formatDate(dateStr) {
            const d = new Date(dateStr);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const h = String(d.getHours()).padStart(2, '0');
            const min = String(d.getMinutes()).padStart(2, '0');
            return `${y}-${m}-${day} ${h}:${min}`;
        }

        function formatDateTimeLocal(dateStr) {
            const d = new Date(dateStr);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const h = String(d.getHours()).padStart(2, '0');
            const min = String(d.getMinutes()).padStart(2, '0');
            return `${y}-${m}-${day}T${h}:${min}`;
        }

        function loadActivities() {
            apiGet('get_activities').then(res => {
                if (res.code === 200) {
                    allActivities = res.data;
                    renderActivities();
                }
            });
        }

        function renderActivities() {
            const tbody = document.getElementById('activitiesBody');
            const search = document.getElementById('searchInput').value.trim().toLowerCase();
            let filtered = allActivities;
            if (search) {
                filtered = allActivities.filter(a => a.title.toLowerCase().includes(search));
            }

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</td></tr>';
                return;
            }

            tbody.innerHTML = filtered.map(act => `
                <tr>
                    <td class="px-4"><span class="font-monospace text-muted">#${act.id}</span></td>
                    <td class="px-4">
                        <div class="fw-bold">${act.title}</div>
                    </td>
                    <td class="px-4 small text-muted">
                        <div><i class="bi bi-play-circle me-1"></i>${formatDate(act.start_time)}</div>
                        <div><i class="bi bi-stop-circle me-1"></i>${formatDate(act.end_time)}</div>
                    </td>
                    <td class="px-4">
                        <span class="status-badge ${act.status ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'}">
                            ${act.status ? '启用' : '禁用'}
                        </span>
                    </td>
                    <td class="px-4 small text-muted">${formatDate(act.updated_at)}</td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewActivity(${act.id})" title="查看">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="viewStats(${act.id})" title="统计">
                                <i class="bi bi-bar-chart"></i>
                            </button>
                            <button class="btn btn-outline-info" onclick="editActivity(${act.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteActivity(${act.id}, '${act.title.replace(/'/g, "\\'")}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function showActivityModal() {
            document.getElementById('modalTitle').textContent = '新增评议活动';
            document.getElementById('activityId').value = '';
            document.getElementById('activityTitle').value = '';
            document.getElementById('startTime').value = '';
            document.getElementById('endTime').value = '';
            document.getElementById('activityStatus').value = '1';
            deptList = [];
            indList = [];
            renderDeptTags();
            renderIndTags();
            new bootstrap.Modal(document.getElementById('activityModal')).show();
        }

        function editActivity(id) {
            apiGet('get_activity_admin_detail', { id }).then(res => {
                if (res.code === 200) {
                    const d = res.data;
                    document.getElementById('modalTitle').textContent = '编辑评议活动';
                    document.getElementById('activityId').value = d.id;
                    document.getElementById('activityTitle').value = d.title;
                    document.getElementById('startTime').value = formatDateTimeLocal(d.start_time);
                    document.getElementById('endTime').value = formatDateTimeLocal(d.end_time);
                    document.getElementById('activityStatus').value = d.status;
                    deptList = d.departments.map(x => x.name);
                    indList = d.indicators.map(x => x.name);
                    renderDeptTags();
                    renderIndTags();
                    new bootstrap.Modal(document.getElementById('activityModal')).show();
                }
            });
        }

        function viewActivity(id) {
            window.open('../assessment.php?id=' + id, '_blank');
        }

        function viewStats(id) {
            window.open('../assessment_result.php?id=' + id, '_blank');
        }

        function addDept() {
            const input = document.getElementById('deptInput');
            const val = input.value.trim();
            if (val && !deptList.includes(val)) {
                deptList.push(val);
                renderDeptTags();
            }
            input.value = '';
            input.focus();
        }

        function removeDept(idx) {
            deptList.splice(idx, 1);
            renderDeptTags();
        }

        function renderDeptTags() {
            const container = document.getElementById('deptTags');
            if (deptList.length === 0) {
                container.innerHTML = '<span class="text-muted small">暂无部门，请添加</span>';
                return;
            }
            container.innerHTML = deptList.map((name, idx) => `
                <span class="dept-tag">
                    ${name}
                    <i class="bi bi-x tag-remove" onclick="removeDept(${idx})"></i>
                </span>
            `).join('');
        }

        function addInd() {
            const input = document.getElementById('indInput');
            const val = input.value.trim();
            if (val && !indList.includes(val)) {
                indList.push(val);
                renderIndTags();
            }
            input.value = '';
            input.focus();
        }

        function removeInd(idx) {
            indList.splice(idx, 1);
            renderIndTags();
        }

        function renderIndTags() {
            const container = document.getElementById('indTags');
            if (indList.length === 0) {
                container.innerHTML = '<span class="text-muted small">暂无指标，请添加</span>';
                return;
            }
            container.innerHTML = indList.map((name, idx) => `
                <span class="ind-tag">
                    ${name}
                    <i class="bi bi-x tag-remove" onclick="removeInd(${idx})"></i>
                </span>
            `).join('');
        }

        function saveActivity() {
            const id = document.getElementById('activityId').value;
            const title = document.getElementById('activityTitle').value.trim();
            const start_time = document.getElementById('startTime').value;
            const end_time = document.getElementById('endTime').value;
            const status = document.getElementById('activityStatus').value;

            if (!title) { Toast.fire({ icon: 'warning', title: '请输入活动标题' }); return; }
            if (!start_time) { Toast.fire({ icon: 'warning', title: '请选择开始时间' }); return; }
            if (!end_time) { Toast.fire({ icon: 'warning', title: '请选择结束时间' }); return; }
            if (deptList.length === 0) { Toast.fire({ icon: 'warning', title: '请至少添加一个部门' }); return; }
            if (indList.length === 0) { Toast.fire({ icon: 'warning', title: '请至少添加一个指标' }); return; }

            const data = { title, start_time: start_time.replace('T', ' ') + ':00', end_time: end_time.replace('T', ' ') + ':00', departments: JSON.stringify(deptList), indicators: JSON.stringify(indList), status };
            const action = id ? 'update_activity' : 'add_activity';
            if (id) data.id = id;

            api(action, data).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                    bootstrap.Modal.getInstance(document.getElementById('activityModal')).hide();
                    loadActivities();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deleteActivity(id, title) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除活动"${title}"吗？此操作将同时删除相关投票数据，不可恢复。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_activity', { id }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '已删除' });
                            loadActivities();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', loadActivities);
    </script>
</body>
</html>