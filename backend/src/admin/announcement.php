<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>公告管理 - GovCore 后台管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .position-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .position-1 { background: #fff3cd; color: #856404; }
        .position-2 { background: #d1ecf1; color: #0c5460; }
        .position-3 { background: #d4edda; color: #155724; }
        
        .preview-container {
            background: #f8f9fa;
            border-radius: 8px;
            min-height: 400px;
            position: relative;
            overflow: hidden;
        }
        
        .preview-mockup {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            min-height: 300px;
            position: relative;
        }
        
        .mockup-header {
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            padding: 10px 15px;
            color: white;
            font-size: 14px;
        }
        
        .mockup-content {
            padding: 20px;
            min-height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            opacity: 0.3;
        }

        .marquee-preview {
            overflow: hidden;
            white-space: nowrap;
            width: 100%;
        }
        
        .marquee-content {
            display: inline-block;
            padding-left: 100%;
            animation: marquee-preview 15s linear infinite;
        }
        
        @keyframes marquee-preview {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }

        .float-preview {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 10;
        }

        .bottombar-preview {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 15px;
        }

        .preview-close-btn {
            position: absolute;
            top: 5px;
            right: 8px;
            cursor: pointer;
            opacity: 0.8;
        }
        .preview-close-btn:hover { opacity: 1; }

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
                    <a href="purchase.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cart-check me-2"></i>政府采购管理
                    </a>
                    <a href="emergency_contact.php" class="list-group-item list-group-item-action">
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">公告管理</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <select class="form-select form-select-sm" style="width: auto;" id="filterPosition" onchange="loadItems()">
                                    <option value="0">全部位置</option>
                                    <option value="1">顶部跑马灯</option>
                                    <option value="2">中部飘窗</option>
                                    <option value="3">底部条</option>
                                </select>
                                <input type="text" class="form-control form-control-sm" style="width: 200px;" id="searchInput" placeholder="搜索公告..." onkeyup="if(event.key==='Enter')loadItems()">
                                <button class="btn btn-outline-secondary btn-sm" onclick="loadItems()">
                                    <i class="bi bi-search me-1"></i>搜索
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-gov-blue btn-sm" onclick="showItemModal()">
                                    <i class="bi bi-plus-lg me-1"></i>新增公告
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="itemsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3" style="width: 60px;">ID</th>
                                            <th class="px-4 py-3">标题</th>
                                            <th class="px-4 py-3" style="width: 120px;">展示位置</th>
                                            <th class="px-4 py-3" style="width: 100px;">点击量</th>
                                            <th class="px-4 py-3" style="width: 100px;">可关闭</th>
                                            <th class="px-4 py-3" style="width: 90px;">状态</th>
                                            <th class="px-4 py-3" style="width: 180px;">生效时间</th>
                                            <th class="px-4 py-3" style="width: 140px;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        <tr><td colspan="8" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue" id="itemModalTitle">
                        <i class="bi bi-megaphone me-2"></i><span id="itemModalTitleText">新增公告</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <ul class="nav nav-tabs mb-4" id="editTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit-tab-pane" type="button">
                                <i class="bi bi-pencil-square me-1"></i>编辑
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-tab-pane" type="button">
                                <i class="bi bi-eye me-1"></i>实时预览
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="edit-tab-pane">
                            <form id="itemForm">
                                <input type="hidden" id="itemId" value="">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">展示位置 <span class="text-danger">*</span></label>
                                        <select class="form-select" id="itemPosition" required onchange="updatePreview()">
                                            <option value="1">顶部跑马灯</option>
                                            <option value="2">中部飘窗</option>
                                            <option value="3">全站固定底部条</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">排序值</label>
                                        <input type="number" class="form-control" id="itemSortOrder" value="0" min="0" oninput="updatePreview()">
                                        <div class="form-text small">数值越小越靠前</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">状态</label>
                                        <select class="form-select" id="itemStatus">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">公告标题 <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="itemTitle" required maxlength="500" placeholder="请输入公告标题..." oninput="updatePreview()">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">公告内容</label>
                                        <textarea class="form-control" id="itemContent" rows="3" placeholder="请输入公告内容..." oninput="updatePreview()"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">背景颜色</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="itemBgColor" value="#dc3545" style="width: 60px; height: 38px;" onchange="updatePreview()">
                                            <input type="text" class="form-control" id="itemBgColorText" value="#dc3545" oninput="document.getElementById('itemBgColor').value = this.value; updatePreview();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">文字颜色</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="itemTextColor" value="#ffffff" style="width: 60px; height: 38px;" onchange="updatePreview()">
                                            <input type="text" class="form-control" id="itemTextColorText" value="#ffffff" oninput="document.getElementById('itemTextColor').value = this.value; updatePreview();">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">跳转链接</label>
                                        <input type="text" class="form-control" id="itemLinkUrl" placeholder="https://example.com 或 faq.php" oninput="updatePreview()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">允许关闭</label>
                                        <div class="pt-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="itemCanClose" checked onchange="updatePreview()">
                                                <label class="form-check-label small text-muted" for="itemCanClose">允许用户关闭</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">生效开始时间</label>
                                        <input type="datetime-local" class="form-control" id="itemStartTime">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">生效结束时间</label>
                                        <input type="datetime-local" class="form-control" id="itemEndTime">
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="preview-tab-pane">
                            <div class="preview-container">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <h6 class="text-center mb-3 text-muted">顶部跑马灯</h6>
                                        <div class="preview-mockup" style="min-height: 150px;">
                                            <div id="preview-marquee" class="marquee-preview" style="background: #dc3545; color: #ffffff; padding: 10px 0;">
                                                <div class="marquee-content">
                                                    <i class="bi bi-megaphone-fill me-2"></i>
                                                    <span id="preview-marquee-text">公告标题预览</span>
                                                </div>
                                            </div>
                                            <div class="mockup-content" style="min-height: 80px; font-size: 14px;">页面内容区域</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-center mb-3 text-muted">中部飘窗</h6>
                                        <div class="preview-mockup" style="min-height: 150px;">
                                            <div class="mockup-header">模拟网站头部</div>
                                            <div id="preview-float" class="float-preview" style="background: #004d99; color: #ffffff;">
                                                <div id="preview-float-close" class="preview-close-btn" style="color: #ffffff;">&times;</div>
                                                <div class="p-3">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                        <strong id="preview-float-title">公告标题预览</strong>
                                                    </div>
                                                    <div id="preview-float-content" class="small mb-3" style="opacity: 0.9;">公告内容预览...</div>
                                                    <a href="javascript:void(0)" id="preview-float-link" class="btn btn-sm btn-light" style="color: #004d99;">查看详情</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="text-center mb-3 text-muted">底部条</h6>
                                        <div class="preview-mockup" style="min-height: 150px;">
                                            <div class="mockup-content" style="min-height: 100px; font-size: 14px;">页面内容区域</div>
                                            <div id="preview-bottombar" class="bottombar-preview" style="background: #28a745; color: #ffffff;">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        <span id="preview-bottombar-text">公告标题预览</span>
                                                    </div>
                                                    <div id="preview-bottombar-close" class="preview-close-btn" style="position: static; color: #ffffff;">&times;</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                formData.append(key, data[key]);
            }
            return fetch('../announcement_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = '../announcement_api.php?action=' + action;
            for (let key in data) {
                url += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            return fetch(url).then(r => r.json());
        }

        function getPositionText(pos) {
            const map = { 1: '顶部跑马灯', 2: '中部飘窗', 3: '底部条' };
            return map[pos] || '未知';
        }

        function loadItems() {
            const position = document.getElementById('filterPosition').value;
            const search = document.getElementById('searchInput').value.trim();
            apiGet('get_announcements_admin', { position }).then(res => {
                if (res.code === 200) {
                    allItems = res.data;
                    renderItems(allItems, search);
                }
            });
        }

        function renderItems(items, search = '') {
            const tbody = document.getElementById('itemsBody');
            let filtered = items;
            if (search) {
                const s = search.toLowerCase();
                filtered = items.filter(i =>
                    i.title.toLowerCase().includes(s) ||
                    (i.content && i.content.toLowerCase().includes(s))
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
                            <i class="bi bi-circle-fill me-2" style="color: ${item.bg_color}; font-size: 10px;"></i>
                            <span>${item.title.length > 60 ? item.title.slice(0, 60) + '...' : item.title}</span>
                        </div>
                    </td>
                    <td class="px-4"><span class="position-badge position-${item.position}">${getPositionText(item.position)}</span></td>
                    <td class="px-4"><span class="badge bg-light text-dark"><i class="bi bi-hand-index-thumb me-1"></i>${item.click_count}</span></td>
                    <td class="px-4">
                        <span class="badge ${item.can_close ? 'bg-success' : 'bg-warning'} rounded-pill">${item.can_close ? '允许' : '不允许'}</span>
                    </td>
                    <td class="px-4">
                        <span class="badge ${item.status ? 'bg-success' : 'bg-secondary'} rounded-pill">${item.status ? '启用' : '禁用'}</span>
                    </td>
                    <td class="px-4 small text-muted">
                        ${item.start_time ? item.start_time.slice(5, 16) : '不限'}
                        <br>
                        ~ ${item.end_time ? item.end_time.slice(5, 16) : '不限'}
                    </td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="showItemModal(${item.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteItem(${item.id}, '${item.title.replace(/'/g, "\\'").slice(0, 30)}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function updatePreview() {
            const title = document.getElementById('itemTitle').value || '公告标题预览';
            const content = document.getElementById('itemContent').value || '公告内容预览...';
            const bgColor = document.getElementById('itemBgColor').value;
            const textColor = document.getElementById('itemTextColor').value;
            const canClose = document.getElementById('itemCanClose').checked;
            const linkUrl = document.getElementById('itemLinkUrl').value;

            const marquee = document.getElementById('preview-marquee');
            marquee.style.background = bgColor;
            marquee.style.color = textColor;
            document.getElementById('preview-marquee-text').textContent = title;

            const floatBox = document.getElementById('preview-float');
            floatBox.style.background = bgColor;
            floatBox.style.color = textColor;
            document.getElementById('preview-float-title').textContent = title;
            document.getElementById('preview-float-content').textContent = content;
            const floatLink = document.getElementById('preview-float-link');
            floatLink.style.color = bgColor;
            floatLink.style.display = linkUrl ? 'inline-block' : 'none';
            document.getElementById('preview-float-close').style.display = canClose ? 'block' : 'none';
            document.getElementById('preview-float-close').style.color = textColor;

            const bottombar = document.getElementById('preview-bottombar');
            bottombar.style.background = bgColor;
            bottombar.style.color = textColor;
            document.getElementById('preview-bottombar-text').textContent = title;
            document.getElementById('preview-bottombar-close').style.display = canClose ? 'block' : 'none';
            document.getElementById('preview-bottombar-close').style.color = textColor;
        }

        function showItemModal(id = null) {
            document.getElementById('itemModalTitleText').textContent = id ? '编辑公告' : '新增公告';
            document.getElementById('itemId').value = id || '';
            document.getElementById('itemTitle').value = '';
            document.getElementById('itemContent').value = '';
            document.getElementById('itemPosition').value = '1';
            document.getElementById('itemBgColor').value = '#dc3545';
            document.getElementById('itemBgColorText').value = '#dc3545';
            document.getElementById('itemTextColor').value = '#ffffff';
            document.getElementById('itemTextColorText').value = '#ffffff';
            document.getElementById('itemLinkUrl').value = '';
            document.getElementById('itemCanClose').checked = true;
            document.getElementById('itemSortOrder').value = '0';
            document.getElementById('itemStatus').value = '1';
            document.getElementById('itemStartTime').value = '';
            document.getElementById('itemEndTime').value = '';

            updatePreview();

            const modal = new bootstrap.Modal(document.getElementById('itemModal'));
            modal.show();

            if (id) {
                apiGet('get_announcement_detail', { id }).then(res => {
                    if (res.code === 200) {
                        const d = res.data;
                        document.getElementById('itemTitle').value = d.title || '';
                        document.getElementById('itemContent').value = d.content || '';
                        document.getElementById('itemPosition').value = d.position;
                        document.getElementById('itemBgColor').value = d.bg_color || '#dc3545';
                        document.getElementById('itemBgColorText').value = d.bg_color || '#dc3545';
                        document.getElementById('itemTextColor').value = d.text_color || '#ffffff';
                        document.getElementById('itemTextColorText').value = d.text_color || '#ffffff';
                        document.getElementById('itemLinkUrl').value = d.link_url || '';
                        document.getElementById('itemCanClose').checked = !!d.can_close;
                        document.getElementById('itemSortOrder').value = d.sort_order;
                        document.getElementById('itemStatus').value = d.status;
                        if (d.start_time) {
                            document.getElementById('itemStartTime').value = d.start_time.replace(' ', 'T').slice(0, 16);
                        }
                        if (d.end_time) {
                            document.getElementById('itemEndTime').value = d.end_time.replace(' ', 'T').slice(0, 16);
                        }
                        updatePreview();
                    }
                });
            }
        }

        function saveItem() {
            const id = document.getElementById('itemId').value;
            const title = document.getElementById('itemTitle').value.trim();
            const content = document.getElementById('itemContent').value.trim();
            const position = document.getElementById('itemPosition').value;
            const bg_color = document.getElementById('itemBgColor').value;
            const text_color = document.getElementById('itemTextColor').value;
            const link_url = document.getElementById('itemLinkUrl').value.trim();
            const can_close = document.getElementById('itemCanClose').checked ? 1 : 0;
            const sort_order = document.getElementById('itemSortOrder').value;
            const status = document.getElementById('itemStatus').value;
            const start_time = document.getElementById('itemStartTime').value || '';
            const end_time = document.getElementById('itemEndTime').value || '';

            if (!title) { Toast.fire({ icon: 'warning', title: '请输入公告标题' }); return; }

            const data = { title, content, position, bg_color, text_color, link_url, can_close, sort_order, status, start_time, end_time };
            const action = id ? 'update_announcement' : 'add_announcement';
            if (id) data.id = id;

            api(action, data).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                    bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
                    loadItems();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deleteItem(id, name) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除公告"${name}"吗？此操作不可恢复。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_announcement', { id }).then(res => {
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
