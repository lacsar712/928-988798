<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>应急通讯录管理 - GovCore 后台管理</title>
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
        .badge-24h {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
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
        .department-badge {
            background: var(--gov-blue-light);
            color: var(--gov-blue-primary);
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .drag-handle {
            cursor: move;
            color: #adb5bd;
        }
        .drag-handle:hover {
            color: var(--gov-blue-primary);
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
                    <a href="emergency_contact.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-telephone-inbound me-2"></i>应急通讯录
                    </a>
                    <a href="emergency_plan.php" class="list-group-item list-group-item-action">
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">应急通讯录管理</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="contactTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts-tab-pane" type="button">
                                        <i class="bi bi-person-lines-fill me-2"></i>联系人管理
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="departments-tab" data-bs-toggle="tab" data-bs-target="#departments-tab-pane" type="button">
                                        <i class="bi bi-building me-2"></i>部门管理
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-content" id="contactTabContent">

                        <div class="tab-pane fade show active" id="contacts-tab-pane" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <select class="form-select form-select-sm" style="width: auto;" id="filterDepartment" onchange="loadContacts()">
                                            <option value="0">全部部门</option>
                                        </select>
                                        <input type="text" class="form-control form-control-sm" style="width: 200px;" id="searchInput" placeholder="搜索姓名/电话..." onkeyup="if(event.key==='Enter')loadContacts()">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="loadContacts()">
                                            <i class="bi bi-search me-1"></i>搜索
                                        </button>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="showSortModal()">
                                            <i class="bi bi-arrow-down-up me-1"></i>调整排序
                                        </button>
                                        <button class="btn btn-gov-blue btn-sm" onclick="showContactModal()">
                                            <i class="bi bi-plus-lg me-1"></i>新增联系人
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" id="contactsTable">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="px-4 py-3" style="width: 60px;">序号</th>
                                                    <th class="px-4 py-3">姓名</th>
                                                    <th class="px-4 py-3">部门</th>
                                                    <th class="px-4 py-3">职务</th>
                                                    <th class="px-4 py-3">应急电话</th>
                                                    <th class="px-4 py-3">办公电话</th>
                                                    <th class="px-4 py-3" style="width: 80px;">24h</th>
                                                    <th class="px-4 py-3" style="width: 90px;">状态</th>
                                                    <th class="px-4 py-3" style="width: 140px;">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody id="contactsBody">
                                                <tr><td colspan="9" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="departments-tab-pane" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 text-gov-blue fw-bold">
                                        <i class="bi bi-building me-2"></i>部门列表
                                        <span class="text-muted fw-normal small ms-2">拖动可调整排序</span>
                                    </h6>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="saveDeptSort()">
                                            <i class="bi bi-save2 me-1"></i>保存排序
                                        </button>
                                        <button class="btn btn-gov-blue btn-sm" onclick="showDeptModal()">
                                            <i class="bi bi-plus-lg me-1"></i>新增部门
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body p-4">
                                    <div id="departmentList">
                                        <div class="text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm me-2"></div>加载中...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue" id="contactModalTitle">
                        <i class="bi bi-person-plus me-2"></i><span id="contactModalTitleText">新增联系人</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="contactForm">
                        <input type="hidden" id="contactId" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">部门 <span class="text-danger">*</span></label>
                                <select class="form-select" id="contactDepartment" required>
                                    <option value="">请选择部门</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">姓名 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactName" required maxlength="50" placeholder="请输入姓名">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">职务</label>
                                <input type="text" class="form-control" id="contactPosition" maxlength="100" placeholder="请输入职务">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">24h应急电话</label>
                                <input type="text" class="form-control" id="contactEmergencyPhone" maxlength="50" placeholder="请输入应急电话">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">办公电话</label>
                                <input type="text" class="form-control" id="contactOfficePhone" maxlength="50" placeholder="请输入办公电话">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">邮箱</label>
                                <input type="email" class="form-control" id="contactEmail" maxlength="100" placeholder="请输入邮箱">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">值班时间</label>
                                <input type="text" class="form-control" id="contactDutyTime" maxlength="100" placeholder="如：24小时值班">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">排序值</label>
                                <input type="number" class="form-control" id="contactSortOrder" value="0" min="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">状态</label>
                                <select class="form-select" id="contactStatus">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">24h应急</label>
                                <div class="pt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="contactIs24h">
                                        <label class="form-check-label small text-muted" for="contactIs24h">24小时值班</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveContact()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue" id="deptModalTitle">
                        <i class="bi bi-building-add me-2"></i><span id="deptModalTitleText">新增部门</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="deptForm">
                        <input type="hidden" id="deptId" value="">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">部门名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="deptName" required maxlength="100" placeholder="请输入部门名称">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">图标 (Bootstrap Icons)</label>
                                <input type="text" class="form-control" id="deptIcon" maxlength="50" placeholder="如: bi-fire">
                                <div class="form-text small"><a href="https://icons.getbootstrap.com/" target="_blank" class="text-decoration-none">图标库参考</a></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">排序值</label>
                                <input type="number" class="form-control" id="deptSortOrder" value="0" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">状态</label>
                                <select class="form-select" id="deptStatus">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveDept()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sortModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-arrow-down-up me-2"></i>调整联系人排序
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">选择部门</label>
                        <select class="form-select" id="sortDeptFilter" onchange="loadSortItems()">
                            <option value="0">全部部门</option>
                        </select>
                    </div>
                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-2"></i>拖动列表项调整排序顺序，保存后生效
                    </div>
                    <ul id="sortItemsList" class="list-group" style="max-height: 500px; overflow-y: auto;">
                        <li class="list-group-item text-center text-muted py-4">请选择部门</li>
                    </ul>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveContactSort()">
                        <i class="bi bi-save2 me-1"></i>保存排序
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let allDepartments = [];
        let allContacts = [];
        let sortData = [];
        let deptSortData = [];

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
            return fetch('../emergency_contact_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = '../emergency_contact_api.php?action=' + action;
            for (let key in data) {
                url += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            return fetch(url).then(r => r.json());
        }

        function loadDepartments() {
            apiGet('get_all_departments').then(res => {
                if (res.code === 200) {
                    allDepartments = res.data;
                    renderDeptSelects();
                    renderDeptList();
                }
            });
        }

        function renderDeptSelects() {
            const filterSel = document.getElementById('filterDepartment');
            const contactSel = document.getElementById('contactDepartment');
            const sortSel = document.getElementById('sortDeptFilter');

            let optionsHTML = '';
            allDepartments.forEach(dept => {
                optionsHTML += `<option value="${dept.id}">${dept.name}</option>`;
            });

            if (filterSel) {
                const curVal = filterSel.value;
                filterSel.innerHTML = '<option value="0">全部部门</option>' + optionsHTML;
                filterSel.value = curVal;
            }
            if (sortSel) {
                const curVal = sortSel.value;
                sortSel.innerHTML = '<option value="0">全部部门</option>' + optionsHTML;
                sortSel.value = curVal;
            }
            if (contactSel) contactSel.innerHTML = '<option value="">请选择部门</option>' + optionsHTML;
        }

        function renderDeptList() {
            const list = document.getElementById('departmentList');
            if (allDepartments.length === 0) {
                list.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</div>';
                return;
            }
            list.innerHTML = allDepartments.map((dept, idx) => `
                <div class="card mb-3 dept-item" data-id="${dept.id}" draggable="true">
                    <div class="card-body py-3 d-flex align-items-center">
                        <i class="bi bi-grip-vertical drag-handle me-3"></i>
                        <i class="bi ${dept.icon || 'bi-building'} fs-4 me-3 text-gov-blue"></i>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">${dept.name}</h6>
                            <small class="text-muted">排序: ${dept.sort_order}</small>
                        </div>
                        <span class="badge ${dept.status ? 'bg-success' : 'bg-secondary'} rounded-pill me-3">${dept.status ? '启用' : '禁用'}</span>
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="showDeptModal(${dept.id})">
                            <i class="bi bi-pencil"></i> 编辑
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDept(${dept.id}, '${dept.name.replace(/'/g, "\\'")}')">
                            <i class="bi bi-trash"></i> 删除
                        </button>
                    </div>
                </div>
            `).join('');
            enableDeptDrag();
        }

        function enableDeptDrag() {
            const items = document.querySelectorAll('.dept-item');
            items.forEach(item => {
                item.addEventListener('dragstart', e => {
                    item.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });
                item.addEventListener('dragend', () => {
                    item.classList.remove('dragging');
                });
            });
            const list = document.getElementById('departmentList');
            list.addEventListener('dragover', e => {
                e.preventDefault();
                const after = getDragAfterDept(list, e.clientY);
                const dragging = document.querySelector('.dept-item.dragging');
                if (dragging) {
                    if (after == null) {
                        list.appendChild(dragging);
                    } else {
                        list.insertBefore(dragging, after);
                    }
                }
            });
        }

        function getDragAfterDept(container, y) {
            const els = [...container.querySelectorAll('.dept-item:not(.dragging)')];
            return els.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function saveDeptSort() {
            const items = [];
            document.querySelectorAll('#departmentList .dept-item').forEach((item, idx) => {
                items.push({
                    id: parseInt(item.dataset.id),
                    sort_order: idx + 1
                });
            });
            Swal.fire({
                title: '确认保存？',
                text: '将更新部门排序顺序',
                icon: 'question',
                showCancelButton: true
            }).then(r => {
                if (r.isConfirmed) {
                    api('update_sort', { type: 'department', items: JSON.stringify(items) }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '排序已保存' });
                            loadDepartments();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        function showDeptModal(id = null) {
            document.getElementById('deptModalTitleText').textContent = id ? '编辑部门' : '新增部门';
            document.getElementById('deptId').value = id || '';
            document.getElementById('deptName').value = '';
            document.getElementById('deptIcon').value = '';
            document.getElementById('deptSortOrder').value = '0';
            document.getElementById('deptStatus').value = '1';

            if (id) {
                const dept = allDepartments.find(d => d.id === id);
                if (dept) {
                    document.getElementById('deptName').value = dept.name;
                    document.getElementById('deptIcon').value = dept.icon || '';
                    document.getElementById('deptSortOrder').value = dept.sort_order;
                    document.getElementById('deptStatus').value = dept.status;
                }
            }
            new bootstrap.Modal(document.getElementById('deptModal')).show();
        }

        function saveDept() {
            const id = document.getElementById('deptId').value;
            const name = document.getElementById('deptName').value.trim();
            const icon = document.getElementById('deptIcon').value.trim();
            const sort_order = document.getElementById('deptSortOrder').value;
            const status = document.getElementById('deptStatus').value;

            if (!name) { Toast.fire({ icon: 'warning', title: '请输入部门名称' }); return; }

            const data = { name, icon, sort_order, status };
            const action = id ? 'update_department' : 'add_department';
            if (id) data.id = id;

            api(action, data).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                    bootstrap.Modal.getInstance(document.getElementById('deptModal')).hide();
                    loadDepartments();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deleteDept(id, name) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除部门"${name}"吗？`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_department', { id }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '已删除' });
                            loadDepartments();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        function loadContacts() {
            const deptId = document.getElementById('filterDepartment').value;
            const search = document.getElementById('searchInput').value.trim();
            apiGet('get_contacts_admin', { department_id: deptId, keyword: search }).then(res => {
                if (res.code === 200) {
                    allContacts = res.data;
                    renderContacts(allContacts);
                }
            });
        }

        function renderContacts(contacts) {
            const tbody = document.getElementById('contactsBody');
            if (contacts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</td></tr>';
                return;
            }
            tbody.innerHTML = contacts.map((item, idx) => `
                <tr>
                    <td class="px-4"><span class="text-muted">${idx + 1}</span></td>
                    <td class="px-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-gov-blue bg-opacity-10 text-gov-blue rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <span class="fw-bold">${item.name}</span>
                            ${item.is_24h ? '<span class="badge-24h ms-2">24h</span>' : ''}
                        </div>
                    </td>
                    <td class="px-4"><span class="department-badge">${item.department_name || '-'}</span></td>
                    <td class="px-4">${item.position || '-'}</td>
                    <td class="px-4">
                        ${item.emergency_phone ? `<a href="tel:${item.emergency_phone}" class="text-decoration-none text-danger fw-bold">${item.emergency_phone}</a>` : '-'}
                    </td>
                    <td class="px-4">${item.office_phone || '-'}</td>
                    <td class="px-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" ${item.is_24h ? 'checked' : ''}
                                   onchange="toggle24h(${item.id}, this.checked ? 1 : 0)">
                        </div>
                    </td>
                    <td class="px-4">
                        <span class="badge ${item.status ? 'bg-success' : 'bg-secondary'} rounded-pill">${item.status ? '启用' : '禁用'}</span>
                    </td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="showContactModal(${item.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteContact(${item.id}, '${item.name.replace(/'/g, "\\'")}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function showContactModal(id = null) {
            document.getElementById('contactModalTitleText').textContent = id ? '编辑联系人' : '新增联系人';
            document.getElementById('contactId').value = id || '';
            document.getElementById('contactDepartment').value = '';
            document.getElementById('contactName').value = '';
            document.getElementById('contactPosition').value = '';
            document.getElementById('contactEmergencyPhone').value = '';
            document.getElementById('contactOfficePhone').value = '';
            document.getElementById('contactEmail').value = '';
            document.getElementById('contactDutyTime').value = '';
            document.getElementById('contactSortOrder').value = '0';
            document.getElementById('contactStatus').value = '1';
            document.getElementById('contactIs24h').checked = false;

            if (id) {
                apiGet('get_contact_detail', { id }).then(res => {
                    if (res.code === 200) {
                        const d = res.data;
                        document.getElementById('contactDepartment').value = d.department_id;
                        document.getElementById('contactName').value = d.name;
                        document.getElementById('contactPosition').value = d.position || '';
                        document.getElementById('contactEmergencyPhone').value = d.emergency_phone || '';
                        document.getElementById('contactOfficePhone').value = d.office_phone || '';
                        document.getElementById('contactEmail').value = d.email || '';
                        document.getElementById('contactDutyTime').value = d.duty_time || '';
                        document.getElementById('contactSortOrder').value = d.sort_order;
                        document.getElementById('contactStatus').value = d.status;
                        document.getElementById('contactIs24h').checked = !!d.is_24h;
                    }
                });
            }
            new bootstrap.Modal(document.getElementById('contactModal')).show();
        }

        function saveContact() {
            const id = document.getElementById('contactId').value;
            const department_id = document.getElementById('contactDepartment').value;
            const name = document.getElementById('contactName').value.trim();
            const position = document.getElementById('contactPosition').value.trim();
            const emergency_phone = document.getElementById('contactEmergencyPhone').value.trim();
            const office_phone = document.getElementById('contactOfficePhone').value.trim();
            const email = document.getElementById('contactEmail').value.trim();
            const duty_time = document.getElementById('contactDutyTime').value.trim();
            const is_24h = document.getElementById('contactIs24h').checked ? 1 : 0;
            const sort_order = document.getElementById('contactSortOrder').value;
            const status = document.getElementById('contactStatus').value;

            if (!department_id) { Toast.fire({ icon: 'warning', title: '请选择部门' }); return; }
            if (!name) { Toast.fire({ icon: 'warning', title: '请输入姓名' }); return; }

            const data = { department_id, name, position, emergency_phone, office_phone, email, duty_time, is_24h, sort_order, status };
            const action = id ? 'update_contact' : 'add_contact';
            if (id) data.id = id;

            api(action, data).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                    bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
                    loadContacts();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deleteContact(id, name) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除联系人"${name}"吗？此操作不可恢复。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_contact', { id }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '已删除' });
                            loadContacts();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        function toggle24h(id, is_24h) {
            api('update_contact', { id, is_24h }).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: is_24h ? '已设为24h应急' : '已取消24h应急' });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                    loadContacts();
                }
            });
        }

        function showSortModal() {
            renderDeptSelects();
            document.getElementById('sortDeptFilter').value = document.getElementById('filterDepartment').value;
            loadSortItems();
            new bootstrap.Modal(document.getElementById('sortModal')).show();
        }

        function loadSortItems() {
            const deptId = document.getElementById('sortDeptFilter').value;
            apiGet('get_contacts_admin', { department_id: deptId }).then(res => {
                if (res.code === 200) {
                    sortData = res.data;
                    renderSortList();
                }
            });
        }

        function renderSortList() {
            const list = document.getElementById('sortItemsList');
            if (sortData.length === 0) {
                list.innerHTML = '<li class="list-group-item text-center text-muted py-4">暂无数据</li>';
                return;
            }
            list.innerHTML = sortData.map((item, idx) => `
                <li class="list-group-item d-flex align-items-center sort-list-item" draggable="true" data-id="${item.id}">
                    <i class="bi bi-grip-vertical drag-handle me-3"></i>
                    <span class="text-muted fw-bold me-3 sort-index">${idx + 1}</span>
                    <span class="fw-bold me-2">${item.name}</span>
                    <span class="department-badge">${item.department_name || '-'}</span>
                    <span class="ms-auto text-muted small">${item.position || ''}</span>
                </li>
            `).join('');
            enableSortDrag();
        }

        function enableSortDrag() {
            const list = document.getElementById('sortItemsList');
            list.querySelectorAll('.sort-list-item').forEach(li => {
                li.addEventListener('dragstart', () => {
                    li.classList.add('dragging');
                });
                li.addEventListener('dragend', () => {
                    li.classList.remove('dragging');
                    list.querySelectorAll('.sort-index').forEach((el, idx) => {
                        el.textContent = idx + 1;
                    });
                });
            });
            list.addEventListener('dragover', e => {
                e.preventDefault();
                const after = getDragAfterListItem(list, e.clientY);
                const dragging = document.querySelector('.sort-list-item.dragging');
                if (dragging) {
                    if (after == null) {
                        list.appendChild(dragging);
                    } else {
                        list.insertBefore(dragging, after);
                    }
                }
            });
        }

        function getDragAfterListItem(container, y) {
            const els = [...container.querySelectorAll('.sort-list-item:not(.dragging)')];
            return els.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function saveContactSort() {
            const items = [];
            document.querySelectorAll('#sortItemsList .sort-list-item').forEach((li, idx) => {
                items.push({
                    id: parseInt(li.dataset.id),
                    sort_order: idx + 1
                });
            });
            api('update_sort', { type: 'contact', items: JSON.stringify(items) }).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: '排序已保存' });
                    bootstrap.Modal.getInstance(document.getElementById('sortModal')).hide();
                    loadContacts();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadDepartments();
            loadContacts();
        });
    </script>
</body>
</html>
