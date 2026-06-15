<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>FAQ 管理 - GovCore 后台管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@wangeditor/editor@5.1.23/dist/css/style.css" rel="stylesheet">
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
        .drag-handle {
            cursor: move;
            color: #adb5bd;
        }
        .drag-handle:hover {
            color: var(--gov-blue-primary);
        }
        .sortable-placeholder {
            background-color: #e7f1ff;
            border: 2px dashed var(--gov-blue-primary);
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .dragging {
            opacity: 0.5;
            background-color: #f8f9fa;
        }
        .top-badge-admin {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        .modal-xl-custom {
            max-width: 1100px;
        }
        .category-badge {
            background: var(--gov-blue-light);
            color: var(--gov-blue-primary);
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .category-tree {
            list-style: none;
            padding-left: 0;
        }
        .category-tree ul {
            list-style: none;
            padding-left: 24px;
            margin-top: 8px;
        }
        .category-node {
            padding: 10px 14px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }
        .category-node:hover {
            border-color: var(--gov-blue-primary);
            box-shadow: 0 2px 8px rgba(0,77,153,0.1);
        }
        .category-node.dragging {
            opacity: 0.5;
        }
        #w-e-textarea-1 {
            min-height: 350px !important;
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
                    <a href="faq.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-question-circle me-2"></i>FAQ 管理
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">FAQ 管理</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="faqTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="items-tab" data-bs-toggle="tab" data-bs-target="#items-tab-pane" type="button">
                                        <i class="bi bi-journal-text me-2"></i>问答管理
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories-tab-pane" type="button">
                                        <i class="bi bi-folder2 me-2"></i>分类管理
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-content" id="faqTabContent">

                        <div class="tab-pane fade show active" id="items-tab-pane" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <select class="form-select form-select-sm" style="width: auto;" id="filterCategory" onchange="loadItems()">
                                            <option value="0">全部分类</option>
                                        </select>
                                        <input type="text" class="form-control form-control-sm" style="width: 200px;" id="searchInput" placeholder="搜索问答..." onkeyup="if(event.key==='Enter')loadItems()">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="loadItems()">
                                            <i class="bi bi-search me-1"></i>搜索
                                        </button>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="showSortModal()">
                                            <i class="bi bi-arrow-down-up me-1"></i>调整排序
                                        </button>
                                        <button class="btn btn-gov-blue btn-sm" onclick="showItemModal()">
                                            <i class="bi bi-plus-lg me-1"></i>新增问答
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
                                                    <th class="px-4 py-3" style="width: 60px;">排序</th>
                                                    <th class="px-4 py-3">ID</th>
                                                    <th class="px-4 py-3">分类</th>
                                                    <th class="px-4 py-3">问题</th>
                                                    <th class="px-4 py-3" style="width: 80px;">置顶</th>
                                                    <th class="px-4 py-3" style="width: 100px;">浏览量</th>
                                                    <th class="px-4 py-3" style="width: 90px;">状态</th>
                                                    <th class="px-4 py-3" style="width: 100px;">更新时间</th>
                                                    <th class="px-4 py-3" style="width: 140px;">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsBody">
                                                <tr><td colspan="9" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="categories-tab-pane" role="tabpanel">
                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 text-gov-blue fw-bold">
                                        <i class="bi bi-folder2-open me-2"></i>分类列表
                                        <span class="text-muted fw-normal small ms-2">拖动可调整排序</span>
                                    </h6>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="saveCategorySort()">
                                            <i class="bi bi-save2 me-1"></i>保存排序
                                        </button>
                                        <button class="btn btn-gov-blue btn-sm" onclick="showCategoryModal()">
                                            <i class="bi bi-plus-lg me-1"></i>新增分类
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body p-4">
                                    <ul class="category-tree" id="categoryTree">
                                        <li class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl-custom">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue" id="itemModalTitle">
                        <i class="bi bi-pencil-square me-2"></i><span id="itemModalTitleText">新增问答</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="itemForm">
                        <input type="hidden" id="itemId" value="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">分类 <span class="text-danger">*</span></label>
                                <select class="form-select" id="itemCategory" required>
                                    <option value="">请选择分类</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">排序值</label>
                                <input type="number" class="form-control" id="itemSortOrder" value="0" min="0">
                                <div class="form-text small">数值越小越靠前</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">状态</label>
                                <select class="form-select" id="itemStatus">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">置顶</label>
                                <div class="pt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="itemIsTop">
                                        <label class="form-check-label small text-muted" for="itemIsTop">设为置顶</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">问题 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="itemQuestion" required maxlength="500" placeholder="请输入问题...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">答案 <span class="text-danger">*</span></label>
                                <div style="border: 1px solid #ced4da; border-radius: 0.375rem; overflow: hidden;">
                                    <div id="editorToolbar" style="border-bottom: 1px solid #e9ecef;"></div>
                                    <div id="editorContent" style="min-height: 350px;"></div>
                                </div>
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

    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue" id="categoryModalTitle">
                        <i class="bi bi-folder-plus me-2"></i><span id="categoryModalTitleText">新增分类</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form id="categoryForm">
                        <input type="hidden" id="categoryId" value="">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">父级分类</label>
                            <select class="form-select" id="categoryParent">
                                <option value="0">顶级分类</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">分类名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryName" required maxlength="100" placeholder="请输入分类名称">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">图标 (Bootstrap Icons类名)</label>
                                <input type="text" class="form-control" id="categoryIcon" maxlength="50" placeholder="如: bi-people-fill">
                                <div class="form-text small"><a href="https://icons.getbootstrap.com/" target="_blank" class="text-decoration-none">图标库参考</a></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">排序值</label>
                                <input type="number" class="form-control" id="categorySortOrder" value="0" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">状态</label>
                                <select class="form-select" id="categoryStatus">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveCategory()">
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
                        <i class="bi bi-arrow-down-up me-2"></i>调整问答排序
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">选择分类</label>
                        <select class="form-select" id="sortCategoryFilter" onchange="loadSortItems()">
                            <option value="0">全部分类</option>
                        </select>
                    </div>
                    <div class="alert alert-info small py-2">
                        <i class="bi bi-info-circle me-2"></i>拖动列表项调整排序顺序，保存后生效
                    </div>
                    <ul id="sortItemsList" class="list-group" style="max-height: 500px; overflow-y: auto;">
                        <li class="list-group-item text-center text-muted py-4">请选择分类</li>
                    </ul>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveItemSort()">
                        <i class="bi bi-save2 me-1"></i>保存排序
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@wangeditor/editor@5.1.23/dist/index.min.js"></script>
    <script>
        const { createEditor, createToolbar } = window.wangEditor;
        let editor = null;
        let allCategories = [];
        let allItems = [];
        let sortData = [];
        let categorySortData = [];

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
            return fetch('../faq_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = '../faq_api.php?action=' + action;
            for (let key in data) {
                url += '&' + key + '=' + encodeURIComponent(data[key]);
            }
            return fetch(url).then(r => r.json());
        }

        function initEditor() {
            if (editor) {
                try { editor.destroy(); } catch(e) {}
                editor = null;
            }
            editor = createEditor({
                selector: '#editorContent',
                html: '',
                config: {
                    placeholder: '请输入答案内容，支持富文本格式...',
                    MENU_CONF: {
                        uploadImage: {
                            customUpload(file, insertFn) {
                                const reader = new FileReader();
                                reader.onload = e => {
                                    insertFn(e.target.result, file.name, '');
                                };
                                reader.readAsDataURL(file);
                            }
                        }
                    }
                },
                mode: 'default'
            });
            createToolbar({
                editor,
                selector: '#editorToolbar',
                config: {}
            });
        }

        function loadCategories() {
            apiGet('get_all_categories').then(res => {
                if (res.code === 200) {
                    allCategories = res.data;
                    renderCategorySelects();
                    renderCategoryTree();
                }
            });
        }

        function renderCategorySelects() {
            const filterSel = document.getElementById('filterCategory');
            const itemSel = document.getElementById('itemCategory');
            const parentSel = document.getElementById('categoryParent');
            const sortSel = document.getElementById('sortCategoryFilter');

            const buildOptions = (cats, level = 0) => {
                let html = '';
                cats.filter(c => c.parent_id === 0 || level > 0).forEach(() => {});
                const topCats = cats.filter(c => c.parent_id === 0);
                topCats.forEach(cat => {
                    const prefix = '　'.repeat(level);
                    html += `<option value="${cat.id}">${prefix}${cat.name}</option>`;
                    const children = cats.filter(c => c.parent_id === cat.id);
                    if (children.length) {
                        html += buildOptionsRecursive(cats, children, level + 1);
                    }
                });
                return html;
            };
            const buildOptionsRecursive = (all, children, level) => {
                let html = '';
                children.forEach(cat => {
                    const prefix = '　'.repeat(level) + '└ ';
                    html += `<option value="${cat.id}">${prefix}${cat.name}</option>`;
                    const sub = all.filter(c => c.parent_id === cat.id);
                    if (sub.length) html += buildOptionsRecursive(all, sub, level + 1);
                });
                return html;
            };

            const topCats = allCategories.filter(c => c.parent_id === 0);
            let optionsHTML = '';
            topCats.forEach(cat => {
                optionsHTML += `<option value="${cat.id}">${cat.name}</option>`;
                const children = allCategories.filter(c => c.parent_id === cat.id);
                if (children.length) optionsHTML += buildOptionsRecursive(allCategories, children, 1);
            });

            if (filterSel) {
                const curVal = filterSel.value;
                filterSel.innerHTML = '<option value="0">全部分类</option>' + optionsHTML;
                filterSel.value = curVal;
            }
            if (sortSel) {
                const curVal = sortSel.value;
                sortSel.innerHTML = '<option value="0">全部分类</option>' + optionsHTML;
                sortSel.value = curVal;
            }
            if (itemSel) itemSel.innerHTML = '<option value="">请选择分类</option>' + optionsHTML;
            if (parentSel) {
                const curVal = parentSel.value;
                parentSel.innerHTML = '<option value="0">顶级分类</option>' + optionsHTML;
                parentSel.value = curVal;
            }
        }

        function renderCategoryTree() {
            const tree = document.getElementById('categoryTree');
            const buildTree = (cats, parent_id, level) => {
                let html = '';
                const items = cats.filter(c => c.parent_id === parent_id);
                items.forEach(cat => {
                    const children = cats.filter(c => c.parent_id === cat.id);
                    const hasChildren = children.length > 0;
                    html += `<li data-id="${cat.id}" class="category-item">`;
                    html += `<div class="category-node">`;
                    html += `<i class="bi bi-grip-vertical drag-handle me-3"></i>`;
                    html += `<i class="bi ${cat.icon || (hasChildren ? 'bi-folder-fill' : 'bi-folder')} me-2 text-gov-blue"></i>`;
                    html += `<span class="fw-bold flex-grow-1">${cat.name}</span>`;
                    html += `<span class="badge ${cat.status ? 'bg-success' : 'bg-secondary'} rounded-pill me-2 small">${cat.status ? '启用' : '禁用'}</span>`;
                    html += `<span class="text-muted small me-3">排序: ${cat.sort_order}</span>`;
                    html += `<button class="btn btn-sm btn-outline-primary me-1" onclick="showCategoryModal(${cat.id})"><i class="bi bi-pencil"></i></button>`;
                    html += `<button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(${cat.id}, '${cat.name}')"><i class="bi bi-trash"></i></button>`;
                    html += `</div>`;
                    if (hasChildren) {
                        html += `<ul>${buildTree(cats, cat.id, level + 1)}</ul>`;
                    }
                    html += `</li>`;
                });
                return html;
            };
            tree.innerHTML = buildTree(allCategories, 0, 0);
            enableCategoryDrag();
        }

        function enableCategoryDrag() {
            const listItems = document.querySelectorAll('#categoryTree > li');
            listItems.forEach(li => {
                li.draggable = true;
                li.addEventListener('dragstart', e => {
                    li.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });
                li.addEventListener('dragend', () => {
                    li.classList.remove('dragging');
                });
            });
            const tree = document.getElementById('categoryTree');
            tree.addEventListener('dragover', e => {
                e.preventDefault();
                const after = getDragAfterElement(tree, e.clientY);
                const dragging = document.querySelector('.dragging');
                if (dragging) {
                    if (after == null) {
                        tree.appendChild(dragging);
                    } else {
                        tree.insertBefore(dragging, after);
                    }
                }
            });
        }

        function getDragAfterElement(container, y) {
            const els = [...container.querySelectorAll('.category-item:not(.dragging)')];
            return els.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function saveCategorySort() {
            const items = [];
            document.querySelectorAll('#categoryTree > li').forEach((li, idx) => {
                items.push({
                    id: parseInt(li.dataset.id),
                    sort_order: idx + 1
                });
            });
            Swal.fire({
                title: '确认保存？',
                text: '将更新分类排序顺序',
                icon: 'question',
                showCancelButton: true
            }).then(r => {
                if (r.isConfirmed) {
                    api('update_sort', { type: 'category', items: JSON.stringify(items) }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '排序已保存' });
                            loadCategories();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        function loadItems() {
            const catId = document.getElementById('filterCategory').value;
            const search = document.getElementById('searchInput').value.trim();
            apiGet('get_items_admin', { category_id: catId }).then(res => {
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
                    i.question.toLowerCase().includes(s) ||
                    (i.category_name && i.category_name.toLowerCase().includes(s))
                );
            }
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-3"></i>暂无数据</td></tr>';
                return;
            }
            tbody.innerHTML = filtered.map((item, idx) => `
                <tr>
                    <td class="px-4"><span class="text-muted">${idx + 1}</span></td>
                    <td class="px-4"><span class="font-monospace text-muted">#${item.id}</span></td>
                    <td class="px-4"><span class="category-badge">${item.category_name || '-'}</span></td>
                    <td class="px-4">
                        <div class="d-flex align-items-center">
                            ${item.is_top ? '<span class="top-badge-admin me-2">置顶</span>' : ''}
                            <span>${item.question.length > 60 ? item.question.slice(0, 60) + '...' : item.question}</span>
                        </div>
                    </td>
                    <td class="px-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" ${item.is_top ? 'checked' : ''}
                                   onchange="toggleTop(${item.id}, this.checked ? 1 : 0)">
                        </div>
                    </td>
                    <td class="px-4"><span class="badge bg-light text-dark"><i class="bi bi-eye me-1"></i>${item.view_count}</span></td>
                    <td class="px-4">
                        <span class="badge ${item.status ? 'bg-success' : 'bg-secondary'} rounded-pill">${item.status ? '启用' : '禁用'}</span>
                    </td>
                    <td class="px-4 small text-muted">${item.updated_at.slice(5, 16)}</td>
                    <td class="px-4">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="showItemModal(${item.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="previewItem(${item.id})" title="预览">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteItem(${item.id}, '${item.question.replace(/'/g, "\\'").slice(0, 30)}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function showItemModal(id = null) {
            document.getElementById('itemModalTitleText').textContent = id ? '编辑问答' : '新增问答';
            document.getElementById('itemId').value = id || '';
            document.getElementById('itemCategory').value = '';
            document.getElementById('itemQuestion').value = '';
            document.getElementById('itemSortOrder').value = '0';
            document.getElementById('itemStatus').value = '1';
            document.getElementById('itemIsTop').checked = false;

            const modal = new bootstrap.Modal(document.getElementById('itemModal'));
            modal.show();

            if (id) {
                apiGet('get_item_detail', { id }).then(res => {
                    if (res.code === 200) {
                        const d = res.data;
                        document.getElementById('itemCategory').value = d.category_id;
                        document.getElementById('itemQuestion').value = d.question;
                        document.getElementById('itemSortOrder').value = d.sort_order;
                        document.getElementById('itemStatus').value = d.status;
                        document.getElementById('itemIsTop').checked = !!d.is_top;
                        setTimeout(() => {
                            initEditor();
                            editor.setHtml(d.answer || '');
                        }, 200);
                    }
                });
            } else {
                setTimeout(() => {
                    initEditor();
                    editor.setHtml('');
                }, 200);
            }
        }

        function saveItem() {
            const id = document.getElementById('itemId').value;
            const category_id = document.getElementById('itemCategory').value;
            const question = document.getElementById('itemQuestion').value.trim();
            const sort_order = document.getElementById('itemSortOrder').value;
            const status = document.getElementById('itemStatus').value;
            const is_top = document.getElementById('itemIsTop').checked ? 1 : 0;
            const answer = editor ? editor.getHtml() : '';

            if (!category_id) { Toast.fire({ icon: 'warning', title: '请选择分类' }); return; }
            if (!question) { Toast.fire({ icon: 'warning', title: '请输入问题' }); return; }
            if (!answer || answer === '<p><br></p>') { Toast.fire({ icon: 'warning', title: '请输入答案' }); return; }

            const data = { category_id, question, answer, sort_order, is_top, status };
            const action = id ? 'update_item' : 'add_item';
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
                html: `确定要删除问题"${name}"吗？此操作不可恢复。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_item', { id }).then(res => {
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

        function toggleTop(id, is_top) {
            api('toggle_top', { id, is_top }).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: is_top ? '已置顶' : '已取消置顶' });
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function previewItem(id) {
            const item = allItems.find(i => i.id === id);
            if (item) {
                Swal.fire({
                    title: item.question,
                    html: `
                        <div class="text-start">
                            <div class="mb-3 small text-muted">
                                <span class="category-badge me-2">${item.category_name || '-'}</span>
                                ${item.is_top ? '<span class="top-badge-admin">置顶</span>' : ''}
                            </div>
                            <div style="line-height: 1.8;">${item.answer}</div>
                        </div>
                    `,
                    width: 700,
                    confirmButtonText: '关闭'
                });
            }
        }

        function showCategoryModal(id = null) {
            document.getElementById('categoryModalTitleText').textContent = id ? '编辑分类' : '新增分类';
            document.getElementById('categoryId').value = id || '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryParent').value = '0';
            document.getElementById('categoryIcon').value = '';
            document.getElementById('categorySortOrder').value = '0';
            document.getElementById('categoryStatus').value = '1';
            renderCategorySelects();

            if (id) {
                const cat = allCategories.find(c => c.id === id);
                if (cat) {
                    document.getElementById('categoryName').value = cat.name;
                    document.getElementById('categoryParent').value = cat.parent_id;
                    document.getElementById('categoryIcon').value = cat.icon || '';
                    document.getElementById('categorySortOrder').value = cat.sort_order;
                    document.getElementById('categoryStatus').value = cat.status;
                    const pSel = document.getElementById('categoryParent');
                    for (let opt of pSel.options) {
                        if (parseInt(opt.value) === id) {
                            opt.disabled = true;
                        }
                    }
                }
            }
            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }

        function saveCategory() {
            const id = document.getElementById('categoryId').value;
            const name = document.getElementById('categoryName').value.trim();
            const parent_id = document.getElementById('categoryParent').value;
            const icon = document.getElementById('categoryIcon').value.trim();
            const sort_order = document.getElementById('categorySortOrder').value;
            const status = document.getElementById('categoryStatus').value;

            if (!name) { Toast.fire({ icon: 'warning', title: '请输入分类名称' }); return; }

            const data = { name, parent_id, icon, sort_order, status };
            const action = id ? 'update_category' : 'add_category';
            if (id) data.id = id;

            api(action, data).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: id ? '更新成功' : '添加成功' });
                    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                    loadCategories();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        function deleteCategory(id, name) {
            Swal.fire({
                title: '确认删除？',
                html: `确定要删除分类"${name}"吗？`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认删除',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    api('delete_category', { id }).then(res => {
                        if (res.code === 200) {
                            Toast.fire({ icon: 'success', title: '已删除' });
                            loadCategories();
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    });
                }
            });
        }

        function showSortModal() {
            renderCategorySelects();
            document.getElementById('sortCategoryFilter').value = document.getElementById('filterCategory').value;
            loadSortItems();
            new bootstrap.Modal(document.getElementById('sortModal')).show();
        }

        function loadSortItems() {
            const catId = document.getElementById('sortCategoryFilter').value;
            apiGet('get_items_admin', { category_id: catId }).then(res => {
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
                    ${item.is_top ? '<span class="top-badge-admin me-2">置顶</span>' : ''}
                    <span class="flex-grow-1">${item.question.length > 80 ? item.question.slice(0, 80) + '...' : item.question}</span>
                    <span class="category-badge ms-2">${item.category_name || '-'}</span>
                </li>
            `).join('');
            enableSortDrag();
        }

        function enableSortDrag() {
            const list = document.getElementById('sortItemsList');
            list.querySelectorAll('.sort-list-item').forEach(li => {
                li.addEventListener('dragstart', () => {
                    li.classList.add('dragging');
                    list.querySelectorAll('.sort-list-item').forEach(el => {
                        if (!el.classList.contains('dragging')) el.classList.add('sortable-placeholder');
                    });
                });
                li.addEventListener('dragend', () => {
                    li.classList.remove('dragging');
                    list.querySelectorAll('.sort-list-item').forEach(el => el.classList.remove('sortable-placeholder'));
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

        function saveItemSort() {
            const items = [];
            document.querySelectorAll('#sortItemsList .sort-list-item').forEach((li, idx) => {
                items.push({
                    id: parseInt(li.dataset.id),
                    sort_order: idx + 1
                });
            });
            api('update_sort', { type: 'item', items: JSON.stringify(items) }).then(res => {
                if (res.code === 200) {
                    Toast.fire({ icon: 'success', title: '排序已保存' });
                    bootstrap.Modal.getInstance(document.getElementById('sortModal')).hide();
                    loadItems();
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const itemModalEl = document.getElementById('itemModal');
            itemModalEl.addEventListener('hidden.bs.modal', () => {
                if (editor) {
                    try { editor.destroy(); } catch(e) {}
                    editor = null;
                }
            });
            loadCategories();
            loadItems();
        });
    </script>
</body>
</html>
