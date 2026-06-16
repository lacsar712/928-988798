<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>服务点管理 - GovCore 后台管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
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
        .coord-preview {
            width: 100%;
            height: 250px;
            background: #fafbfc;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            position: relative;
            cursor: crosshair;
        }
        .coord-preview svg {
            width: 100%;
            height: 100%;
        }
        .coord-preview .coord-marker {
            cursor: pointer;
        }
        .tag-checkbox {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            margin: 4px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
        }
        .tag-checkbox input {
            margin-right: 6px;
        }
        .tag-checkbox:hover {
            border-color: var(--gov-blue-primary);
        }
        .tag-checkbox.checked {
            background: var(--gov-blue-light);
            border-color: var(--gov-blue-primary);
            color: var(--gov-blue-primary);
        }
        .mini-map {
            width: 60px;
            height: 50px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            position: relative;
        }
        .mini-marker {
            width: 8px;
            height: 8px;
            background: #004d99;
            border-radius: 50%;
            position: absolute;
            transform: translate(-50%, -50%);
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
                    <a href="assessment.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person-check me-2"></i>绩效评议
                    </a>
                    <a href="openday.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar-event me-2"></i>开放日预约
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
                    <a href="service_point.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-geo-alt-fill me-2"></i>服务点管理
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">服务点管理</li>
                        </ol>
                    </nav>

                    <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="points-tab" data-bs-toggle="tab" data-bs-target="#points-tab-pane" type="button">
                                <i class="bi bi-geo-alt me-1"></i>服务点管理
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="townships-tab" data-bs-toggle="tab" data-bs-target="#townships-tab-pane" type="button">
                                <i class="bi bi-building me-1"></i>乡镇/街道管理
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="points-tab-pane">
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <select class="form-select form-select-sm" style="width: auto;" id="filterTownship" onchange="loadPoints()">
                                            <option value="0">全部乡镇</option>
                                        </select>
                                        <input type="text" class="form-control form-control-sm" style="width: 200px;" id="searchInput" placeholder="搜索服务点..." onkeyup="if(event.key==='Enter')loadPoints()">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="loadPoints()">
                                            <i class="bi bi-search me-1"></i>搜索
                                        </button>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="../service_map.php" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>前台预览
                                        </a>
                                        <button class="btn btn-gov-blue btn-sm" onclick="showPointModal()">
                                            <i class="bi bi-plus-lg me-1"></i>新增服务点
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" id="pointsTable">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="px-4 py-3" style="width: 60px;">ID</th>
                                                    <th class="px-4 py-3">服务点名称</th>
                                                    <th class="px-4 py-3" style="width: 120px;">所属乡镇</th>
                                                    <th class="px-4 py-3" style="width: 80px;">坐标</th>
                                                    <th class="px-4 py-3">服务标签</th>
                                                    <th class="px-4 py-3" style="width: 100px;">距离</th>
                                                    <th class="px-4 py-3" style="width: 90px;">状态</th>
                                                    <th class="px-4 py-3" style="width: 140px;">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody id="pointsBody">
                                                <tr><td colspan="8" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="townships-tab-pane">
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                                    <span class="text-muted small">共 <span id="townshipCount" class="fw-bold text-gov-blue">0</span> 个乡镇/街道</span>
                                    <button class="btn btn-gov-blue btn-sm" onclick="showTownshipModal()">
                                        <i class="bi bi-plus-lg me-1"></i>新增乡镇
                                    </button>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="px-4 py-3" style="width: 60px;">ID</th>
                                                    <th class="px-4 py-3">名称</th>
                                                    <th class="px-4 py-3" style="width: 100px;">类型</th>
                                                    <th class="px-4 py-3" style="width: 100px;">服务点数</th>
                                                    <th class="px-4 py-3" style="width: 90px;">排序</th>
                                                    <th class="px-4 py-3" style="width: 90px;">状态</th>
                                                    <th class="px-4 py-3" style="width: 140px;">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody id="townshipsBody">
                                                <tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pointModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-geo-alt-fill me-2"></i><span id="pointModalTitleText">新增服务点</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="pointForm">
                        <input type="hidden" id="pointId" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-12">
                                    <label class="form-label fw-bold small">服务点名称 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="pointName" required maxlength="200" placeholder="请输入服务点名称">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">所属乡镇/街道 <span class="text-danger">*</span></label>
                                    <select class="form-select" id="pointTownshipId" required>
                                        <option value="">请选择</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">联系电话</label>
                                    <input type="text" class="form-control" id="pointPhone" maxlength="50" placeholder="010-88880000">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">详细地址</label>
                                    <input type="text" class="form-control" id="pointAddress" maxlength="500" placeholder="请输入详细地址">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">开放时间</label>
                                    <input type="text" class="form-control" id="pointOpenTime" maxlength="200" placeholder="工作日 9:00-17:00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">X 坐标 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="pointCoordX" min="0" max="100" step="0.01" required oninput="updateCoordMarker()">
                                        <span class="input-group-text">0-100</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Y 坐标 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="pointCoordY" min="0" max="100" step="0.01" required oninput="updateCoordMarker()">
                                        <span class="input-group-text">0-100</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">距离（公里）</label>
                                    <input type="number" class="form-control" id="pointDistance" min="0" step="0.01" placeholder="0.00">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">排序值</label>
                                    <input type="number" class="form-control" id="pointSortOrder" value="0" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">状态</label>
                                    <select class="form-select" id="pointStatus">
                                        <option value="1">启用</option>
                                        <option value="0">禁用</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">地图位置预览（点击可设置坐标）</label>
                            <div class="coord-preview" id="coordPreview" onclick="setCoordFromClick(event)">
                                <svg viewBox="0 0 600 500">
                                    <defs>
                                        <pattern id="adminGrid" width="30" height="30" patternUnits="userSpaceOnUse">
                                            <path d="M 30 0 L 0 0 0 30" fill="none" stroke="#e9ecef" stroke-width="1"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100%" height="100%" fill="url(#adminGrid)"/>
                                    <circle id="coordMarker" class="coord-marker" cx="150" cy="125" r="10" fill="#dc3545" stroke="white" stroke-width="3"/>
                                </svg>
                            </div>
                            <div class="mt-2 text-muted small text-center">
                                点击地图任意位置可快速设置坐标
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold small">服务事项标签 <span class="text-danger">*</span></label>
                            <div id="tagCheckboxes" class="d-flex flex-wrap">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="savePoint()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="townshipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-building me-2"></i><span id="townshipModalTitleText">新增乡镇</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="townshipForm">
                        <input type="hidden" id="townshipId" value="">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">乡镇/街道名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="townshipName" required maxlength="100" placeholder="请输入名称">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">类型</label>
                                <select class="form-select" id="townshipType">
                                    <option value="1">乡镇</option>
                                    <option value="2">街道</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">排序</label>
                                <input type="number" class="form-control" id="townshipSortOrder" value="0" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">状态</label>
                                <select class="form-select" id="townshipStatus">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveTownship()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let allPoints = [];
        let allTownships = [];
        let allTags = [];

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });

        function api(action, data = {}, method = 'POST') {
            const formData = new URLSearchParams();
            formData.append('action', action);
            for (let key in data) {
                formData.append(key, data[key]);
            }
            return fetch('../service_map_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = '../service_map_api.php?action=' + action;
            for (let key in data) {
                url += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            return fetch(url).then(r => r.json());
        }

        function loadTownshipOptions() {
            apiGet('get_townships').then(res => {
                if (res.code === 200) {
                    allTownships = res.data;
                    const select = document.getElementById('filterTownship');
                    select.innerHTML = '<option value="0">全部乡镇</option>';
                    const modalSelect = document.getElementById('pointTownshipId');
                    modalSelect.innerHTML = '<option value="">请选择</option>';
                    allTownships.forEach(t => {
                        const opt1 = document.createElement('option');
                        opt1.value = t.id;
                        opt1.textContent = t.name + (t.type === 2 ? '（街道）' : '（乡镇）');
                        select.appendChild(opt1);

                        const opt2 = document.createElement('option');
                        opt2.value = t.id;
                        opt2.textContent = t.name + (t.type === 2 ? '（街道）' : '（乡镇）');
                        modalSelect.appendChild(opt2);
                    });
                }
            });
        }

        function loadTagCheckboxes() {
            apiGet('get_tags').then(res => {
                if (res.code === 200) {
                    allTags = res.data;
                    const container = document.getElementById('tagCheckboxes');
                    container.innerHTML = allTags.map(tag => `
                        <label class="tag-checkbox" data-tag-id="${tag.id}">
                            <input type="checkbox" value="${tag.id}" onchange="toggleTagCheckbox(this)">
                            <i class="bi ${tag.icon || 'bi-tag'} me-1"></i>${tag.name}
                        </label>
                    `).join('');
                }
            });
        }

        function toggleTagCheckbox(checkbox) {
            const label = checkbox.closest('.tag-checkbox');
            if (checkbox.checked) {
                label.classList.add('checked');
            } else {
                label.classList.remove('checked');
            }
        }

        function getSelectedTags() {
            const checks = document.querySelectorAll('#tagCheckboxes input:checked');
            return Array.from(checks).map(c => c.value);
        }

        function setSelectedTags(tagIds) {
            document.querySelectorAll('#tagCheckboxes input').forEach(cb => {
                cb.checked = tagIds.includes(parseInt(cb.value));
                toggleTagCheckbox(cb);
            });
        }

        function loadPoints() {
            const townshipId = document.getElementById('filterTownship').value;
            const keyword = document.getElementById('searchInput').value.trim();
            apiGet('get_service_points_admin', { township_id: townshipId, keyword }).then(res => {
                if (res.code === 200) {
                    allPoints = res.data;
                    renderPoints();
                }
            });
        }

        function renderPoints() {
            const tbody = document.getElementById('pointsBody');
            const search = document.getElementById('searchInput').value.trim().toLowerCase();
            let filtered = allPoints;
            if (search) {
                filtered = allPoints.filter(p =>
                    p.name.toLowerCase().includes(search) ||
                    (p.address && p.address.toLowerCase().includes(search))
                );
            }
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</td></tr>';
                return;
            }
            tbody.innerHTML = filtered.map(item => `
                <tr>
                    <td class="px-4"><span class="font-monospace text-muted">#${item.id}</span></td>
                    <td class="px-4">
                        <div class="d-flex align-items-center">
                            <div class="mini-map me-3">
                                <div class="mini-marker" style="left: ${item.coord_x}%; top: ${item.coord_y}%;"></div>
                            </div>
                            <div>
                                <div class="fw-bold">${item.name}</div>
                                <div class="small text-muted">${item.address || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4"><span class="badge bg-light text-dark">${item.township_name || '-'}</span></td>
                    <td class="px-4">
                        <span class="font-monospace small text-muted">${item.coord_x}, ${item.coord_y}</span>
                    </td>
                    <td class="px-4">
                        <div class="d-flex flex-wrap gap-1">
                            ${item.tags.slice(0, 3).map(tag => `<span class="badge rounded-pill" style="background: ${tag.color}; padding: 2px 8px;">${tag.name}</span>`).join('')}
                            ${item.tags.length > 3 ? `<span class="badge bg-secondary rounded-pill">+${item.tags.length - 3}</span>` : ''}
                        </div>
                    </td>
                    <td class="px-4"><span class="badge bg-light text-dark">${item.distance} km</span></td>
                    <td class="px-4">
                        <span class="badge ${item.status ? 'bg-success' : 'bg-secondary'} rounded-pill">${item.status ? '启用' : '禁用'}</span>
                    </td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="showPointModal(${item.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deletePoint(${item.id}, '${item.name.replace(/'/g, "\\'").slice(0, 30)}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function loadTownships() {
            apiGet('get_townships_admin').then(res => {
                if (res.code === 200) {
                    allTownships = res.data;
                    document.getElementById('townshipCount').textContent = allTownships.length;
                    renderTownships();
                }
            });
        }

        function renderTownships() {
            const tbody = document.getElementById('townshipsBody');
            if (allTownships.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</td></tr>';
                return;
            }
            tbody.innerHTML = allTownships.map(item => `
                <tr>
                    <td class="px-4"><span class="font-monospace text-muted">#${item.id}</span></td>
                    <td class="px-4 fw-bold">${item.name}</td>
                    <td class="px-4"><span class="badge ${item.type === 2 ? 'bg-info' : 'bg-primary'} rounded-pill">${item.type === 2 ? '街道' : '乡镇'}</span></td>
                    <td class="px-4"><span class="badge bg-light text-dark">${item.point_count} 个</span></td>
                    <td class="px-4"><span class="font-monospace small text-muted">${item.sort_order}</span></td>
                    <td class="px-4">
                        <span class="badge ${item.status ? 'bg-success' : 'bg-secondary'} rounded-pill">${item.status ? '启用' : '禁用'}</span>
                    </td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="showTownshipModal(${item.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteTownship(${item.id}, '${item.name.replace(/'/g, "\\'").slice(0, 30)}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function updateCoordMarker() {
            const x = parseFloat(document.getElementById('pointCoordX').value) || 50;
            const y = parseFloat(document.getElementById('pointCoordY').value) || 50;
            const marker = document.getElementById('coordMarker');
            marker.setAttribute('cx', x * 5 + 25);
            marker.setAttribute('cy', y * 4.5 + 25);
        }

        function setCoordFromClick(event) {
            const preview = document.getElementById('coordPreview');
            const rect = preview.getBoundingClientRect();
            const x = (event.clientX - rect.left) / rect.width;
            const y = (event.clientY - rect.top) / rect.height;
            document.getElementById('pointCoordX').value = (x * 100).toFixed(2);
            document.getElementById('pointCoordY').value = (y * 100).toFixed(2);
            updateCoordMarker();
        }

        function showPointModal(id = null) {
            document.getElementById('pointModalTitleText').textContent = id ? '编辑服务点' : '新增服务点';
            document.getElementById('pointId').value = id || '';
            document.getElementById('pointName').value = '';
            document.getElementById('pointTownshipId').value = '';
            document.getElementById('pointAddress').value = '';
            document.getElementById('pointPhone').value = '';
            document.getElementById('pointOpenTime').value = '';
            document.getElementById('pointCoordX').value = '50';
            document.getElementById('pointCoordY').value = '50';
            document.getElementById('pointDistance').value = '0.00';
            document.getElementById('pointSortOrder').value = '0';
            document.getElementById('pointStatus').value = '1';
            setSelectedTags([]);
            updateCoordMarker();

            const modal = new bootstrap.Modal(document.getElementById('pointModal'));
            modal.show();

            if (id) {
                apiGet('get_service_point_detail', { id }).then(res => {
                    if (res.code === 200) {
                        const d = res.data;
                        document.getElementById('pointName').value = d.name || '';
                        document.getElementById('pointTownshipId').value = d.township_id;
                        document.getElementById('pointAddress').value = d.address || '';
                        document.getElementById('pointPhone').value = d.phone || '';
                        document.getElementById('pointOpenTime').value = d.open_time || '';
                        document.getElementById('pointCoordX').value = d.coord_x;
                        document.getElementById('pointCoordY').value = d.coord_y;
                        document.getElementById('pointDistance').value = d.distance;
                        document.getElementById('pointSortOrder').value = d.sort_order;
                        document.getElementById('pointStatus').value = d.status;
                        setSelectedTags(d.tags.map(t => t.id));
                        updateCoordMarker();
                    }
                });
            }
        }

        function savePoint() {
            const id = document.getElementById('pointId').value;
            const name = document.getElementById('pointName').value.trim();
            const township_id = document.getElementById('pointTownshipId').value;
            const address = document.getElementById('pointAddress').value.trim();
            const phone = document.getElementById('pointPhone').value.trim();
            const open_time = document.getElementById('pointOpenTime').value.trim();
            const coord_x = document.getElementById('pointCoordX').value;
            const coord_y = document.getElementById('pointCoordY').value;
            const distance = document.getElementById('pointDistance').value;
            const sort_order = document.getElementById('pointSortOrder').value;
            const status = document.getElementById('pointStatus').value;
            const tag_ids = getSelectedTags().join(',');

            if (!name) { Toast.fire({ icon: 'warning', title: '请输入服务点名称' }); return; }
            if (!township_id) { Toast.fire({ icon: 'warning', title: '请选择所属乡镇' }); return; }

            const data = { name, township_id, address, phone, open_time, coord_x, coord_y, distance, sort_order, status, tag_ids };
            const action = id ? 'update_service_point' : 'add_service_point';
            if (id) data.id = id;

            api(action, data).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                    bootstrap.Modal.getInstance(document.getElementById('pointModal')).hide();
                    loadPoints();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deletePoint(id, name) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除服务点"${name}"吗？此操作不可恢复。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_service_point', { id }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '已删除' });
                            loadPoints();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        function showTownshipModal(id = null) {
            document.getElementById('townshipModalTitleText').textContent = id ? '编辑乡镇' : '新增乡镇';
            document.getElementById('townshipId').value = id || '';
            document.getElementById('townshipName').value = '';
            document.getElementById('townshipType').value = '1';
            document.getElementById('townshipSortOrder').value = '0';
            document.getElementById('townshipStatus').value = '1';

            const modal = new bootstrap.Modal(document.getElementById('townshipModal'));
            modal.show();

            if (id) {
                apiGet('get_township_detail', { id }).then(res => {
                    if (res.code === 200) {
                        const d = res.data;
                        document.getElementById('townshipName').value = d.name || '';
                        document.getElementById('townshipType').value = d.type;
                        document.getElementById('townshipSortOrder').value = d.sort_order;
                        document.getElementById('townshipStatus').value = d.status;
                    }
                });
            }
        }

        function saveTownship() {
            const id = document.getElementById('townshipId').value;
            const name = document.getElementById('townshipName').value.trim();
            const type = document.getElementById('townshipType').value;
            const sort_order = document.getElementById('townshipSortOrder').value;
            const status = document.getElementById('townshipStatus').value;

            if (!name) { Toast.fire({ icon: 'warning', title: '请输入乡镇名称' }); return; }

            const data = { name, type, sort_order, status };
            const action = id ? 'update_township' : 'add_township';
            if (id) data.id = id;

            api(action, data).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                    bootstrap.Modal.getInstance(document.getElementById('townshipModal')).hide();
                    loadTownships();
                    loadTownshipOptions();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deleteTownship(id, name) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除乡镇"${name}"吗？此操作不可恢复。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_township', { id }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '已删除' });
                            loadTownships();
                            loadTownshipOptions();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadTownshipOptions();
            loadTagCheckboxes();
            loadPoints();
            loadTownships();

            const tabEl = document.querySelector('button[data-bs-target="#townships-tab"]');
            tabEl.addEventListener('shown.bs.tab', () => {
                loadTownships();
            });
        });
    </script>
</body>
</html>
