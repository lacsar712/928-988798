<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>开放日预约管理 - GovCore 后台管理</title>
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
        .remaining-tag {
            font-weight: 700;
            font-size: 0.85rem;
        }
        .remaining-tag.full {
            color: #dc3545;
        }
        .remaining-tag.available {
            color: #198754;
        }
        .remaining-tag.low {
            color: #fd7e14;
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
                    <a href="openday.php" class="list-group-item list-group-item-action active">
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">开放日预约</li>
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
                                            <th class="px-4 py-3">主题</th>
                                            <th class="px-4 py-3" style="width: 150px;">活动时间</th>
                                            <th class="px-4 py-3" style="width: 120px;">地点</th>
                                            <th class="px-4 py-3" style="width: 80px;">限额</th>
                                            <th class="px-4 py-3" style="width: 80px;">剩余</th>
                                            <th class="px-4 py-3" style="width: 80px;">负责人</th>
                                            <th class="px-4 py-3" style="width: 80px;">状态</th>
                                            <th class="px-4 py-3" style="width: 200px;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="activitiesBody">
                                        <tr><td colspan="9" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
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
                        <i class="bi bi-calendar-event me-2"></i><span id="modalTitle">新增开放日活动</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="activityForm">
                        <input type="hidden" id="activityId" value="">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">活动主题 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="activityTheme" required maxlength="255" placeholder="请输入活动主题">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">活动时间 <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="eventTime" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">活动地点 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="activityLocation" required maxlength="255" placeholder="请输入活动地点">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">名额限额 <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="activityQuota" required min="1" placeholder="请输入名额限额">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">负责人 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="activityManager" required maxlength="100" placeholder="请输入负责人姓名">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">状态</label>
                            <select class="form-select" id="activityStatus">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
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

    <div class="modal fade" id="reservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-people me-2"></i><span id="resvModalTitle">预约记录</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>编号</th>
                                    <th>姓名</th>
                                    <th>手机号</th>
                                    <th>身份证号</th>
                                    <th>同行人数</th>
                                    <th>状态</th>
                                    <th>预约时间</th>
                                </tr>
                            </thead>
                            <tbody id="resvBody">
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

        let allActivities = [];

        function api(action, data = {}, method = 'POST') {
            const formData = new URLSearchParams();
            formData.append('action', action);
            for (let key in data) {
                formData.append(key, data[key]);
            }
            return fetch('../openday_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = '../openday_api.php?action=' + action;
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
                filtered = allActivities.filter(a => a.theme.toLowerCase().includes(search));
            }

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</td></tr>';
                return;
            }

            tbody.innerHTML = filtered.map(act => {
                const remaining = act.remaining;
                let remainClass = 'available';
                if (remaining === 0) remainClass = 'full';
                else if (remaining <= Math.ceil(act.quota * 0.2)) remainClass = 'low';

                return `
                <tr>
                    <td class="px-4"><span class="font-monospace text-muted">#${act.id}</span></td>
                    <td class="px-4">
                        <div class="fw-bold">${act.theme}</div>
                    </td>
                    <td class="px-4 small text-muted">${formatDate(act.event_time)}</td>
                    <td class="px-4 small">${act.location}</td>
                    <td class="px-4 text-center fw-bold">${act.quota}</td>
                    <td class="px-4 text-center"><span class="remaining-tag ${remainClass}">${remaining}</span></td>
                    <td class="px-4 small">${act.manager}</td>
                    <td class="px-4">
                        <span class="status-badge ${act.status ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'}">
                            ${act.status ? '启用' : '禁用'}
                        </span>
                    </td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewReservations(${act.id})" title="预约记录">
                                <i class="bi bi-people"></i>
                            </button>
                            <button class="btn btn-outline-info" onclick="editActivity(${act.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteActivity(${act.id}, '${act.theme.replace(/'/g, "\\'")}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `}).join('');
        }

        function showActivityModal() {
            document.getElementById('modalTitle').textContent = '新增开放日活动';
            document.getElementById('activityId').value = '';
            document.getElementById('activityTheme').value = '';
            document.getElementById('eventTime').value = '';
            document.getElementById('activityLocation').value = '';
            document.getElementById('activityQuota').value = '';
            document.getElementById('activityManager').value = '';
            document.getElementById('activityStatus').value = '1';
            new bootstrap.Modal(document.getElementById('activityModal')).show();
        }

        function editActivity(id) {
            apiGet('get_activity_detail', { id }).then(res => {
                if (res.code === 200) {
                    const d = res.data;
                    document.getElementById('modalTitle').textContent = '编辑开放日活动';
                    document.getElementById('activityId').value = d.id;
                    document.getElementById('activityTheme').value = d.theme;
                    document.getElementById('eventTime').value = formatDateTimeLocal(d.event_time);
                    document.getElementById('activityLocation').value = d.location;
                    document.getElementById('activityQuota').value = d.quota;
                    document.getElementById('activityManager').value = d.manager;
                    document.getElementById('activityStatus').value = d.status;
                    new bootstrap.Modal(document.getElementById('activityModal')).show();
                }
            });
        }

        function viewReservations(id) {
            apiGet('get_activity_detail', { id }).then(res => {
                if (res.code === 200) {
                    const d = res.data;
                    document.getElementById('resvModalTitle').textContent = d.theme + ' - 预约记录';
                    const tbody = document.getElementById('resvBody');
                    if (!d.reservations || d.reservations.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">暂无预约记录</td></tr>';
                    } else {
                        tbody.innerHTML = d.reservations.map(r => `
                            <tr>
                                <td class="font-monospace">${r.booking_code}</td>
                                <td>${r.name}</td>
                                <td>${r.phone}</td>
                                <td>${r.id_card}</td>
                                <td class="text-center">${r.companions}</td>
                                <td>
                                    <span class="status-badge ${r.status == 1 ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'}">
                                        ${r.status == 1 ? '有效' : '已取消'}
                                    </span>
                                </td>
                                <td class="small text-muted">${formatDate(r.created_at)}</td>
                            </tr>
                        `).join('');
                    }
                    new bootstrap.Modal(document.getElementById('reservationModal')).show();
                }
            });
        }

        function saveActivity() {
            const id = document.getElementById('activityId').value;
            const theme = document.getElementById('activityTheme').value.trim();
            const event_time = document.getElementById('eventTime').value;
            const location = document.getElementById('activityLocation').value.trim();
            const quota = document.getElementById('activityQuota').value;
            const manager = document.getElementById('activityManager').value.trim();
            const status = document.getElementById('activityStatus').value;

            if (!theme) { Toast.fire({ icon: 'warning', title: '请输入活动主题' }); return; }
            if (!event_time) { Toast.fire({ icon: 'warning', title: '请选择活动时间' }); return; }
            if (!location) { Toast.fire({ icon: 'warning', title: '请输入活动地点' }); return; }
            if (!quota || quota <= 0) { Toast.fire({ icon: 'warning', title: '请输入有效的名额限额' }); return; }
            if (!manager) { Toast.fire({ icon: 'warning', title: '请输入负责人' }); return; }

            const data = { theme, event_time: event_time.replace('T', ' ') + ':00', location, quota, manager, status };
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
                html: `确定要删除活动"${title}"吗？此操作将同时删除相关预约数据，不可恢复。`,
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
