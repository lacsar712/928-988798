<?php
require_once 'func.php';

function get_categories() {
    global $conn;
    $sql = "SELECT * FROM faq_categories WHERE status = 1 ORDER BY sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    return build_tree($categories, 0);
}

function build_tree($categories, $parent_id) {
    $tree = [];
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parent_id) {
            $cat['children'] = build_tree($categories, $cat['id']);
            $tree[] = $cat;
        }
    }
    return $tree;
}

function get_items_by_cat($category_id) {
    global $conn;
    $cid = intval($category_id);
    $sql = "SELECT * FROM faq_items WHERE category_id = $cid AND status = 1 ORDER BY is_top DESC, sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    return $items;
}

function get_all_items() {
    global $conn;
    $sql = "SELECT fi.*, fc.name as category_name FROM faq_items fi 
            LEFT JOIN faq_categories fc ON fi.category_id = fc.id 
            WHERE fi.status = 1 
            ORDER BY fi.is_top DESC, fi.sort_order ASC, fi.id ASC";
    $result = mysqli_query($conn, $sql);
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    return $items;
}

function highlight_keyword($text, $keyword) {
    if (empty($keyword)) return $text;
    $keyword = htmlspecialchars($keyword);
    $text = preg_replace('/(' . preg_quote($keyword, '/') . ')/iu', '<mark class="bg-warning text-dark px-1 rounded">$1</mark>', $text);
    return $text;
}

function strip_tags_deep($content) {
    return trim(preg_replace('/\s+/', ' ', strip_tags($content)));
}

$categories = get_categories();
$current_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
$search_keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$faq_items = [];
$page_title = '常见问题';
$search_mode = false;

if (!empty($search_keyword)) {
    $search_mode = true;
    $keyword_esc = mysqli_real_escape_string($conn, $search_keyword);
    $sql = "SELECT fi.*, fc.name as category_name FROM faq_items fi 
            LEFT JOIN faq_categories fc ON fi.category_id = fc.id 
            WHERE fi.status = 1 AND (
                MATCH(fi.question, fi.answer) AGAINST('$keyword_esc' IN BOOLEAN MODE)
                OR fi.question LIKE '%$keyword_esc%'
                OR fi.answer LIKE '%$keyword_esc%'
            ) ORDER BY fi.is_top DESC, fi.sort_order ASC, fi.id ASC";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $faq_items[] = $row;
    }
    $page_title = '搜索: ' . htmlspecialchars($search_keyword) . ' (找到 ' . count($faq_items) . ' 条)';
} elseif ($current_cat > 0) {
    $faq_items = get_items_by_cat($current_cat);
    $cat_name_sql = "SELECT name FROM faq_categories WHERE id = $current_cat";
    $cat_res = mysqli_query($conn, $cat_name_sql);
    $cat_row = mysqli_fetch_assoc($cat_res);
    if ($cat_row) {
        $page_title = $cat_row['name'];
    }
} else {
    $faq_items = get_all_items();
}

function render_category_tree($categories, $level = 0, $current_cat, $search_keyword) {
    $html = '';
    foreach ($categories as $cat) {
        $active = ($current_cat == $cat['id']) ? 'active' : '';
        $has_children = !empty($cat['children']);
        $url = 'faq.php?cat=' . $cat['id'];
        if (!empty($search_keyword)) {
            $url .= '&keyword=' . urlencode($search_keyword);
        }
        $padding = $level * 16 + 12;
        $html .= '<div class="tree-item">';
        $html .= '<a href="' . $url . '" class="list-group-item list-group-item-action border-0 d-flex align-items-center ' . $active . '" style="padding-left: ' . $padding . 'px; border-radius: 8px; margin-bottom: 4px;">';
        if (!empty($cat['icon'])) {
            $html .= '<i class="bi ' . htmlspecialchars($cat['icon']) . ' me-2"></i>';
        }
        $html .= '<span>' . htmlspecialchars($cat['name']) . '</span>';
        if ($has_children) {
            $html .= '<i class="bi bi-chevron-down ms-auto text-muted toggle-icon"></i>';
        }
        $html .= '</a>';
        if ($has_children) {
            $html .= '<div class="tree-children">';
            $html .= render_category_tree($cat['children'], $level + 1, $current_cat, $search_keyword);
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>常见问题 - GovCore 政务公开与应急指挥平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/announcement.css" rel="stylesheet">
    <style>
        .faq-sidebar {
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        .tree-item .list-group-item.active {
            background-color: var(--gov-blue-primary);
            color: white;
        }
        .tree-children {
            display: block;
        }
        .accordion-item {
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px !important;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }
        .accordion-item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .accordion-button:not(.collapsed) {
            background-color: var(--gov-blue-light);
            color: var(--gov-blue-primary);
            box-shadow: none;
        }
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0,0,0,0.125);
        }
        .top-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 4px;
            margin-right: 8px;
            font-weight: 600;
        }
        .category-tag {
            background: var(--gov-blue-light);
            color: var(--gov-blue-primary);
            font-size: 0.75rem;
            padding: 2px 10px;
            border-radius: 12px;
            margin-left: 8px;
        }
        .view-count {
            color: #6c757d;
            font-size: 0.8rem;
            margin-left: auto;
        }
        .accordion-body {
            padding: 24px;
            line-height: 1.8;
            color: #374151;
        }
        .accordion-body p {
            margin-bottom: 12px;
        }
        .accordion-body ul, .accordion-body ol {
            padding-left: 24px;
            margin-bottom: 12px;
        }
        .accordion-body li {
            margin-bottom: 6px;
        }
        .accordion-body strong {
            color: var(--gov-blue-primary);
        }
        .search-highlight mark {
            background-color: #fff3cd;
            padding: 1px 4px;
            border-radius: 3px;
        }
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #9ca3af;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
            opacity: 0.5;
        }
        .page-header {
            background: linear-gradient(135deg, var(--gov-blue-primary) 0%, var(--gov-blue-dark) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
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
                    <li class="nav-item"><a class="nav-link active" href="faq.php">常见问题</a></li>
                    <li class="nav-item"><a class="nav-link" href="emergency_contact.php">应急通讯录</a></li>
                    <li class="nav-item"><a class="nav-link" href="plans.php">应急预案文档库</a></li>
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
                    <h1 class="display-6 fw-bold mb-2">常见问题知识库</h1>
                    <p class="lead mb-0 opacity-90">为您提供户口、医保、社保、教育、出行等政务服务常见问题解答</p>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <form action="faq.php" method="GET" id="searchForm">
                        <div class="input-group input-group-lg">
                            <input type="text" name="keyword" class="form-control border-0 shadow-sm" placeholder="搜索问题或答案..." value="<?php echo htmlspecialchars($search_keyword); ?>" aria-label="搜索">
                            <?php if ($current_cat > 0): ?>
                                <input type="hidden" name="cat" value="<?php echo $current_cat; ?>">
                            <?php endif; ?>
                            <button class="btn btn-light text-gov-blue" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="faq-sidebar">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h6 class="mb-0 text-gov-blue fw-bold d-flex align-items-center">
                                <i class="bi bi-list-ul me-2"></i>问题分类
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="list-group list-group-flush">
                                <a href="faq.php<?php echo !empty($search_keyword) ? '?keyword=' . urlencode($search_keyword) : ''; ?>" 
                                   class="list-group-item list-group-item-action border-0 d-flex align-items-center <?php echo ($current_cat == 0 && !$search_mode) ? 'active' : ''; ?>" 
                                   style="border-radius: 8px; margin-bottom: 4px;">
                                    <i class="bi bi-grid-fill me-2"></i>
                                    <span>全部问题</span>
                                    <span class="ms-auto badge bg-secondary rounded-pill"><?php echo count(get_all_items()); ?></span>
                                </a>
                                <?php echo render_category_tree($categories, 0, $current_cat, $search_keyword); ?>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3 mt-4">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-telephone-fill text-gov-blue fs-2 mb-2"></i>
                            <h6 class="text-gov-blue fw-bold mb-1">服务热线</h6>
                            <p class="display-6 fw-bold text-gov-blue mb-0">12345</p>
                            <small class="text-muted">工作日 9:00-17:00</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <nav aria-label="breadcrumb" class="mb-0">
                            <ol class="breadcrumb mb-0 bg-transparent p-0">
                                <li class="breadcrumb-item">
                                    <a href="faq.php" class="text-decoration-none text-muted">
                                        <i class="bi bi-house-door"></i> 常见问题
                                    </a>
                                </li>
                                <?php if ($search_mode): ?>
                                    <li class="breadcrumb-item active fw-bold text-gov-blue">
                                        <i class="bi bi-search"></i> 搜索结果
                                    </li>
                                <?php elseif ($current_cat > 0): ?>
                                    <li class="breadcrumb-item active fw-bold text-gov-blue">
                                        <?php echo htmlspecialchars($page_title); ?>
                                    </li>
                                <?php endif; ?>
                            </ol>
                        </nav>
                        <div class="text-muted small">
                            共 <strong class="text-gov-blue"><?php echo count($faq_items); ?></strong> 条问题
                        </div>
                    </div>
                </div>

                <?php if (empty($faq_items)): ?>
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5 class="mb-2">暂无相关问题</h5>
                            <p class="mb-4"><?php echo $search_mode ? '没有找到与 "' . htmlspecialchars($search_keyword) . '" 相关的内容' : '该分类下暂无问题'; ?></p>
                            <a href="faq.php" class="btn btn-gov-blue">
                                <i class="bi bi-arrow-left me-2"></i>返回全部问题
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="accordion" id="faqAccordion">
                        <?php 
                        $index = 0;
                        foreach ($faq_items as $item): 
                            $index++;
                            $question = $search_mode ? highlight_keyword(htmlspecialchars($item['question']), $search_keyword) : htmlspecialchars($item['question']);
                            $answer = $search_mode ? highlight_keyword($item['answer'], $search_keyword) : $item['answer'];
                        ?>
                            <div class="accordion-item border-0" data-id="<?php echo $item['id']; ?>">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button collapsed py-4 px-4" type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?php echo $index; ?>" 
                                            aria-expanded="false" 
                                            aria-controls="collapse<?php echo $index; ?>"
                                            onclick="increaseView(<?php echo $item['id']; ?>)">
                                        <div class="d-flex align-items-center flex-wrap w-100">
                                            <span class="me-2 text-gov-blue fw-bold">Q<?php echo $index; ?>.</span>
                                            <?php if (!empty($item['is_top'])): ?>
                                                <span class="top-badge">置顶</span>
                                            <?php endif; ?>
                                            <span class="search-highlight flex-grow-1"><?php echo $question; ?></span>
                                            <?php if ($search_mode && !empty($item['category_name'])): ?>
                                                <span class="category-tag"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                            <?php endif; ?>
                                            <span class="view-count">
                                                <i class="bi bi-eye me-1"></i>
                                                <span id="viewCount<?php echo $item['id']; ?>"><?php echo number_format($item['view_count']); ?></span>
                                            </span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" 
                                     aria-labelledby="heading<?php echo $index; ?>" 
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body search-highlight">
                                        <div class="d-flex mb-3">
                                            <span class="badge bg-gov-blue text-white me-2">回答</span>
                                            <small class="text-muted">
                                                更新于 <?php echo date('Y-m-d', strtotime($item['updated_at'])); ?>
                                            </small>
                                        </div>
                                        <?php echo $answer; ?>
                                        <div class="mt-4 pt-3 border-top">
                                            <div class="d-flex gap-3 text-muted small">
                                                <span class="helpful-btn cursor-pointer" data-id="<?php echo $item['id']; ?>" data-type="yes">
                                                    <i class="bi bi-hand-thumbs-up me-1"></i>
                                                    有帮助 (<span class="yes-count">0</span>)
                                                </span>
                                                <span class="helpful-btn cursor-pointer" data-id="<?php echo $item['id']; ?>" data-type="no">
                                                    <i class="bi bi-hand-thumbs-down me-1"></i>
                                                    没帮助 (<span class="no-count">0</span>)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
        let viewedItems = new Set();

        function increaseView(id) {
            if (viewedItems.has(id)) return;
            viewedItems.add(id);
            
            const viewSpan = document.getElementById('viewCount' + id);
            if (viewSpan) {
                const current = parseInt(viewSpan.textContent.replace(/,/g, '')) || 0;
                viewSpan.textContent = (current + 1).toLocaleString();
            }
            
            fetch('faq_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=increase_view&id=' + id
            }).catch(() => {});
        }

        const searchParams = new URLSearchParams(window.location.search);
        const highlight = searchParams.get('keyword');
        if (highlight) {
            document.querySelectorAll('.search-highlight').forEach(el => {
                const regex = new RegExp('(' + highlight.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                if (el.children.length === 0) {
                    el.innerHTML = el.textContent.replace(regex, '<mark class="bg-warning text-dark px-1 rounded">$1</mark>');
                }
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        const expandId = urlParams.get('expand');
        if (expandId) {
            const target = document.querySelector('[data-id="' + expandId + '"] .accordion-button');
            if (target) {
                setTimeout(() => {
                    target.click();
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        }

        document.querySelectorAll('.helpful-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const countSpan = this.querySelector('.' + type + '-count');
                const current = parseInt(countSpan.textContent) || 0;
                countSpan.textContent = current + 1;
                this.classList.add('text-gov-blue');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: type === 'yes' ? '感谢您的反馈！' : '感谢您的反馈，我们将继续改进！',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        });

        document.querySelectorAll('.tree-item .list-group-item').forEach(item => {
            const toggle = item.querySelector('.toggle-icon');
            const children = item.parentElement.querySelector('.tree-children');
            if (toggle && children) {
                toggle.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (e.target === toggle || toggle.contains(e.target)) {
                        const isHidden = children.style.display === 'none';
                        children.style.display = isHidden ? 'block' : 'none';
                        toggle.classList.toggle('bi-chevron-down', isHidden);
                        toggle.classList.toggle('bi-chevron-right', !isHidden);
                    } else {
                        window.location.href = this.getAttribute('href');
                    }
                });
            }
        });
    </script>
</body>
</html>
