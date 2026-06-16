<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>政府采购管理 - GovCore 后台管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .status-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-招标中 { background: #fff3cd; color: #856404; }
        .status-已截止 { background: #e2e3e5; color: #383d41; }
        .status-已成交 { background: #d4edda; color: #155724; }
        .status-流标 { background: #f8d7da; color: #721c24; }
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
                    <a href="purchase.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-cart-check me-2"></i>政府采购管理
                    </a>
                    <a href="emergency_contact.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-telephone-inbound me-2"></i>应急通讯录
                    </a>
                    <a href="emergency_plan.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-medical me-2"></i>应急预案管理
                    </a>
                    <a href="service_point.php" class="list-group-item list-group-item-action">
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">政府采购管理</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <input type="text" class="form-control form-control-sm" style="width: 200px;" id="searchInput" placeholder="搜索项目名/采购单位..." onkeyup="if(event.key==='Enter')loadItems()">
                                <select class="form-select form-select-sm" style="width: auto;" id="filterStatus" onchange="loadItems()">
                                    <option value="">全部状态</option>
                                    <option value="招标中">招标中</option>
                                    <option value="已截止">已截止</option>
                                    <option value="已成交">已成交</option>
                                    <option value="流标">流标</option>
                                </select>
                                <button class="btn btn-outline-secondary btn-sm" onclick="loadItems()">
                                    <i class="bi bi-search me-1"></i>搜索
                                </button>
                            </div>
                            <button class="btn btn-gov-blue btn-sm" onclick="showItemModal()">
                                <i class="bi bi-plus-lg me-1"></i>新增采购公告
                            </button>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="itemsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3" style="width: 50px;">ID</th>
                                            <th class="px-4 py-3">项目名称</th>
                                            <th class="px-4 py-3" style="width: 130px;">采购单位</th>
                                            <th class="px-4 py-3" style="width: 120px;">预算金额</th>
                                            <th class="px-4 py-3" style="width: 90px;">状态</th>
                                            <th class="px-4 py-3" style="width: 150px;">报名截止</th>
                                            <th class="px-4 py-3" style="width: 200px;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
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

    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue" id="itemModalTitle">
                        <i class="bi bi-cart-check me-2"></i><span id="itemModalTitleText">新增采购公告</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="itemForm" enctype="multipart/form-data">
                        <input type="hidden" id="itemId" value="">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small">项目名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="itemProjectName" required maxlength="500" placeholder="请输入项目名称">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">采购单位 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="itemProcurementUnit" required placeholder="请输入采购单位">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">预算金额(元) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="itemBudgetAmount" required min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">报名截止时间</label>
                                <input type="datetime-local" class="form-control" id="itemDeadline">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">状态</label>
                                <select class="form-select" id="itemStatus" onchange="onStatusChange()">
                                    <option value="招标中">招标中</option>
                                    <option value="已截止">已截止</option>
                                    <option value="已成交">已成交</option>
                                    <option value="流标">流标</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">公告内容</label>
                                <textarea class="form-control" id="itemContent" rows="5" placeholder="请输入公告内容（支持HTML）..."></textarea>
                            </div>
                            <div class="col-md-6" id="winnerGroup" style="display:none;">
                                <label class="form-label fw-bold small">中标人 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="itemWinner" placeholder="请输入中标人名称">
                            </div>
                            <div class="col-md-6" id="winningAmountGroup" style="display:none;">
                                <label class="form-label fw-bold small">中标金额(元) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="itemWinningAmount" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">附件</label>
                                <input type="file" class="form-control" id="itemAttachment">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveItem()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-arrow-repeat me-2"></i>状态流转
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <input type="hidden" id="statusItemId" value="">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">新状态</label>
                        <select class="form-select" id="newStatus" onchange="onNewStatusChange()">
                            <option value="招标中">招标中</option>
                            <option value="已截止">已截止</option>
                            <option value="已成交">已成交</option>
                            <option value="流标">流标</option>
                        </select>
                    </div>
                    <div id="statusWinnerGroup" style="display:none;" class="mb-3">
                        <label class="form-label fw-bold small">中标人 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="statusWinner" placeholder="请输入中标人名称">
                    </div>
                    <div id="statusWinningAmountGroup" style="display:none;" class="mb-3">
                        <label class="form-label fw-bold small">中标金额(元) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="statusWinningAmount" min="0" step="0.01" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="transitionStatus()">
                        <i class="bi bi-check-lg me-1"></i>确认流转
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let allItems = [];

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
                if (data[key] !== undefined && data[key] !== null) {
                    formData.append(key, data[key]);
                }
            }
            return fetch('../purchase_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = '../purchase_api.php?action=' + action;
            for (let key in data) {
                url += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            return fetch(url).then(r => r.json());
        }

        function apiUpload(action, formData) {
            formData.append('action', action);
            return fetch('../purchase_api.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json());
        }

        function formatAmount(val) {
            if (!val) return '-';
            return '¥' + parseFloat(val).toLocaleString('zh-CN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function formatDate(val) {
            if (!val) return '-';
            return val.slice(0, 16).replace('T', ' ');
        }

        function loadItems() {
            const status = document.getElementById('filterStatus').value;
            const search = document.getElementById('searchInput').value.trim();
            apiGet('list_purchases', { status, keyword: search }).then(res => {
                if (res.code === 200) {
                    allItems = res.data;
                    renderItems(allItems);
                }
            });
        }

        function renderItems(items) {
            const tbody = document.getElementById('itemsBody');
            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</td></tr>';
                return;
            }
            tbody.innerHTML = items.map(item => `
                <tr>
                    <td class="px-4"><span class="font-monospace text-muted">#${item.id}</span></td>
                    <td class="px-4">
                        <span class="fw-bold">${item.project_name.length > 40 ? item.project_name.slice(0, 40) + '...' : item.project_name}</span>
                    </td>
                    <td class="px-4 small text-muted">${item.procurement_unit}</td>
                    <td class="px-4 fw-bold text-gov-blue">${formatAmount(item.budget_amount)}</td>
                    <td class="px-4"><span class="status-badge status-${item.status}">${item.status}</span></td>
                    <td class="px-4 small text-muted">${formatDate(item.deadline)}</td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="showItemModal(${item.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-success" onclick="showStatusModal(${item.id}, '${item.status}')" title="状态流转">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteItem(${item.id}, '${item.project_name.replace(/'/g, "\\'").slice(0, 30)}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function onStatusChange() {
            const status = document.getElementById('itemStatus').value;
            const show = status === '已成交';
            document.getElementById('winnerGroup').style.display = show ? 'block' : 'none';
            document.getElementById('winningAmountGroup').style.display = show ? 'block' : 'none';
        }

        function onNewStatusChange() {
            const status = document.getElementById('newStatus').value;
            const show = status === '已成交';
            document.getElementById('statusWinnerGroup').style.display = show ? 'block' : 'none';
            document.getElementById('statusWinningAmountGroup').style.display = show ? 'block' : 'none';
        }

        function showItemModal(id = null) {
            document.getElementById('itemModalTitleText').textContent = id ? '编辑采购公告' : '新增采购公告';
            document.getElementById('itemId').value = id || '';
            document.getElementById('itemProjectName').value = '';
            document.getElementById('itemProcurementUnit').value = '';
            document.getElementById('itemBudgetAmount').value = '';
            document.getElementById('itemDeadline').value = '';
            document.getElementById('itemStatus').value = '招标中';
            document.getElementById('itemContent').value = '';
            document.getElementById('itemWinner').value = '';
            document.getElementById('itemWinningAmount').value = '';
            document.getElementById('itemAttachment').value = '';
            onStatusChange();

            const modal = new bootstrap.Modal(document.getElementById('itemModal'));
            modal.show();

            if (id) {
                apiGet('get_purchase_detail', { id }).then(res => {
                    if (res.code === 200) {
                        const d = res.data;
                        document.getElementById('itemProjectName').value = d.project_name || '';
                        document.getElementById('itemProcurementUnit').value = d.procurement_unit || '';
                        document.getElementById('itemBudgetAmount').value = d.budget_amount || '';
                        document.getElementById('itemDeadline').value = d.deadline ? d.deadline.replace(' ', 'T').slice(0, 16) : '';
                        document.getElementById('itemStatus').value = d.status || '招标中';
                        document.getElementById('itemContent').value = d.content || '';
                        document.getElementById('itemWinner').value = d.winner || '';
                        document.getElementById('itemWinningAmount').value = d.winning_amount || '';
                        onStatusChange();
                    }
                });
            }
        }

        function saveItem() {
            const id = document.getElementById('itemId').value;
            const project_name = document.getElementById('itemProjectName').value.trim();
            const procurement_unit = document.getElementById('itemProcurementUnit').value.trim();
            const budget_amount = document.getElementById('itemBudgetAmount').value;
            const deadline = document.getElementById('itemDeadline').value || '';
            const status = document.getElementById('itemStatus').value;
            const content = document.getElementById('itemContent').value.trim();
            const winner = document.getElementById('itemWinner').value.trim();
            const winning_amount = document.getElementById('itemWinningAmount').value;

            if (!project_name) { Toast.fire({ icon: 'warning', title: '请输入项目名称' }); return; }
            if (!procurement_unit) { Toast.fire({ icon: 'warning', title: '请输入采购单位' }); return; }
            if (!budget_amount) { Toast.fire({ icon: 'warning', title: '请输入预算金额' }); return; }
            if (status === '已成交' && !winner) { Toast.fire({ icon: 'warning', title: '已成交状态必须填写中标人' }); return; }
            if (status === '已成交' && !winning_amount) { Toast.fire({ icon: 'warning', title: '已成交状态必须填写中标金额' }); return; }

            const fileInput = document.getElementById('itemAttachment');
            const hasFile = fileInput.files && fileInput.files.length > 0;

            if (hasFile || id) {
                const fd = new FormData();
                fd.append('project_name', project_name);
                fd.append('procurement_unit', procurement_unit);
                fd.append('budget_amount', budget_amount);
                fd.append('deadline', deadline);
                fd.append('status', status);
                fd.append('content', content);
                fd.append('winner', winner);
                fd.append('winning_amount', winning_amount);
                if (hasFile) fd.append('attachment', fileInput.files[0]);
                if (id) fd.append('id', id);

                const action = id ? 'update_purchase' : 'add_purchase';
                apiUpload(action, fd).then(res => {
                    if (res.code === 200) {
                        Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                        bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
                        loadItems();
                    } else {
                        Toast.fire({ icon: 'error', title: res.message });
                    }
                });
            } else {
                const data = { project_name, procurement_unit, budget_amount, deadline, status, content, winner, winning_amount };
                api('add_purchase', data).then(res => {
                    if (res.code === 200) {
                        Toast.fire({ icon: 'success', title: '添加成功' });
                        bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
                        loadItems();
                    } else {
                        Toast.fire({ icon: 'error', title: res.message });
                    }
                });
            }
        }

        function showStatusModal(id, currentStatus) {
            document.getElementById('statusItemId').value = id;
            document.getElementById('newStatus').value = currentStatus;
            document.getElementById('statusWinner').value = '';
            document.getElementById('statusWinningAmount').value = '';
            onNewStatusChange();
            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        }

        function transitionStatus() {
            const id = document.getElementById('statusItemId').value;
            const status = document.getElementById('newStatus').value;
            const winner = document.getElementById('statusWinner').value.trim();
            const winning_amount = document.getElementById('statusWinningAmount').value;

            if (status === '已成交' && !winner) { Toast.fire({ icon: 'warning', title: '已成交状态必须填写中标人' }); return; }
            if (status === '已成交' && !winning_amount) { Toast.fire({ icon: 'warning', title: '已成交状态必须填写中标金额' }); return; }

            api('transition_status', { id, status, winner, winning_amount }).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: '状态流转成功' });
                    bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
                    loadItems();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deleteItem(id, name) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除采购公告"${name}"吗？此操作不可恢复。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_purchase', { id }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '已删除' });
                            loadItems();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadItems();
        });
    </script>
</body>
</html>
