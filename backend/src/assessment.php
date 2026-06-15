<?php
require_once 'func.php';

$activity_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>政府绩效评议 - GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .star-rating {
            display: flex;
            gap: 4px;
            cursor: pointer;
        }
        .star-rating .bi {
            font-size: 1.8rem;
            color: #dee2e6;
            transition: color 0.15s;
        }
        .star-rating .bi.active {
            color: #ffc107;
        }
        .star-rating .bi:hover {
            transform: scale(1.1);
        }
        .dept-card {
            transition: all 0.2s;
        }
        .dept-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .activity-card {
            transition: all 0.2s;
            border-left: 4px solid var(--gov-blue-primary);
        }
        .activity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .voted-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .indicator-label {
            min-width: 100px;
            font-weight: 500;
            color: #495057;
        }
        .score-display {
            min-width: 50px;
            text-align: center;
            font-weight: 600;
            color: var(--gov-blue-primary);
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
                    <li class="nav-item"><a class="nav-link active" href="assessment.php">绩效评议</a></li>
                    <li class="nav-item"><a class="nav-link" href="openday.php">开放日预约</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <?php if ($activity_id > 0): ?>
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb bg-white p-3 rounded shadow-sm mb-0">
                    <li class="breadcrumb-item"><a href="assessment.php" class="text-decoration-none text-muted">绩效评议</a></li>
                    <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page" id="breadcrumbTitle">活动详情</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1 text-gov-blue fw-bold" id="activityTitle">加载中...</h3>
                            <p class="text-muted mb-0 small" id="activityTime"></p>
                        </div>
                        <div id="votedBadgeContainer"></div>
                    </div>
                </div>
            </div>

            <div id="voteContainer" class="d-none">
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    请对以下部门的各项指标进行评分（1-5星），评分完成后点击底部"提交评议"按钮。
                </div>

                <div id="departmentsContainer">
                </div>

                <div class="text-center mt-5 mb-4">
                    <button class="btn btn-gov-blue btn-lg px-5" onclick="submitVote()">
                        <i class="bi bi-check-circle me-2"></i>提交评议
                    </button>
                </div>
            </div>

            <div id="hasVotedContainer" class="d-none">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="mb-3">您已参与本次评议</h4>
                        <p class="text-muted mb-4">感谢您对政府绩效评议工作的支持！同一活动每人限投一票。</p>
                        <a href="assessment_result.php?id=<?php echo $activity_id; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-bar-chart me-2"></i>查看统计结果
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 border-start border-4 border-danger ps-3 text-gov-blue fw-bold">
                        <i class="bi bi-person-check me-2"></i>政府绩效评议
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        欢迎参与政府部门绩效评议活动。您的评价是我们改进工作的重要依据。请根据您的真实体验，对各部门在服务态度、办事效率、廉洁奉公等方面进行客观评价。
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h6 class="mb-0 text-gov-blue fw-bold">
                        <i class="bi bi-calendar-check me-2"></i>正在进行的评议活动
                    </h6>
                </div>
                <div class="card-body pt-0">
                    <div id="activitiesList">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm me-2"></div>加载中...
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved. | 建议使用 Chrome 或 Edge 浏览器访问</p>
        </div>
    </footer>

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

        function api(action, data = {}, method = 'POST') {
            const formData = new URLSearchParams();
            formData.append('action', action);
            for (let key in data) {
                formData.append(key, data[key]);
            }
            return fetch('assessment_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = 'assessment_api.php?action=' + action;
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

        <?php if ($activity_id > 0): ?>

        let activityData = null;
        let scores = {};

        function loadActivity() {
            apiGet('get_activity_detail', { id: <?php echo $activity_id; ?> }).then(res => {
                if (res.code === 200) {
                    activityData = res.data;
                    renderActivity();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '加载失败',
                        text: res.message
                    }).then(() => {
                        window.location.href = 'assessment.php';
                    });
                }
            });
        }

        function renderActivity() {
            document.getElementById('activityTitle').textContent = activityData.title;
            document.getElementById('breadcrumbTitle').textContent = activityData.title;
            document.getElementById('activityTime').textContent = 
                '活动时间：' + formatDate(activityData.start_time) + ' 至 ' + formatDate(activityData.end_time);

            if (activityData.has_voted) {
                document.getElementById('hasVotedContainer').classList.remove('d-none');
                document.getElementById('votedBadgeContainer').innerHTML = 
                    '<span class="voted-badge"><i class="bi bi-check-circle me-1"></i>已参与</span>';
            } else {
                document.getElementById('voteContainer').classList.remove('d-none');
                initScores();
                renderDepartments();
            }
        }

        function initScores() {
            activityData.departments.forEach(dept => {
                scores[dept.id] = {};
                activityData.indicators.forEach(ind => {
                    scores[dept.id][ind.id] = 0;
                });
            });
        }

        function renderDepartments() {
            const container = document.getElementById('departmentsContainer');
            let html = '';

            activityData.departments.forEach((dept, deptIdx) => {
                html += `
                    <div class="card border-0 shadow-sm rounded-3 mb-3 dept-card">
                        <div class="card-header bg-white py-3 d-flex align-items-center">
                            <span class="badge bg-gov-blue rounded-pill me-3">${deptIdx + 1}</span>
                            <h6 class="mb-0 fw-bold text-gov-blue">${dept.name}</h6>
                        </div>
                        <div class="card-body py-4">
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <tbody>
                `;

                activityData.indicators.forEach(ind => {
                    html += `
                        <tr>
                            <td class="indicator-label">${ind.name}</td>
                            <td>
                                <div class="star-rating" data-dept="${dept.id}" data-ind="${ind.id}">
                                    ${[1,2,3,4,5].map(i => `
                                        <i class="bi bi-star-fill" data-score="${i}" onclick="setStarScore(${dept.id}, ${ind.id}, ${i})"></i>
                                    `).join('')}
                                </div>
                            </td>
                            <td class="score-display" id="score-display-${dept.id}-${ind.id}">-</td>
                        </tr>
                    `;
                });

                html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function setStarScore(deptId, indId, score) {
            scores[deptId][indId] = score;

            const stars = document.querySelectorAll(`.star-rating[data-dept="${deptId}"][data-ind="${indId}"] .bi`);
            stars.forEach((star, idx) => {
                if (idx < score) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });

            const display = document.getElementById(`score-display-${deptId}-${indId}`);
            if (display) {
                display.textContent = score + ' 星';
            }
        }

        function submitVote() {
            let valid = true;
            const votes = [];

            activityData.departments.forEach(dept => {
                activityData.indicators.forEach(ind => {
                    const score = scores[dept.id][ind.id];
                    if (score <= 0) {
                        valid = false;
                    }
                    votes.push({
                        department_id: dept.id,
                        indicator_id: ind.id,
                        score: score
                    });
                });
            });

            if (!valid) {
                Toast.fire({
                    icon: 'warning',
                    title: '请完成所有评分项'
                });
                return;
            }

            Swal.fire({
                title: '确认提交？',
                text: '提交后将无法修改，请确认您的评分。',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '确认提交',
                cancelButtonText: '再改改'
            }).then(r => {
                if (r.isConfirmed) {
                    api('submit_vote', {
                        activity_id: <?php echo $activity_id; ?>,
                        votes: JSON.stringify(votes)
                    }).then(res => {
                        if (res.code === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: '提交成功',
                                text: '感谢您的参与！'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '提交失败',
                                text: res.message
                            });
                        }
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', loadActivity);

        <?php else: ?>

        function loadActivities() {
            apiGet('get_active_activities').then(res => {
                if (res.code === 200) {
                    renderActivities(res.data);
                }
            });
        }

        function renderActivities(activities) {
            const container = document.getElementById('activitiesList');
            if (activities.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-3"></i>
                        暂无进行中的评议活动
                    </div>
                `;
                return;
            }

            container.innerHTML = activities.map(act => `
                <div class="card activity-card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="flex-grow-1">
                                <h5 class="mb-2 fw-bold text-gov-blue">${act.title}</h5>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    ${formatDate(act.start_time)} - ${formatDate(act.end_time)}
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="assessment_result.php?id=${act.id}" class="btn btn-outline-secondary">
                                    <i class="bi bi-bar-chart me-1"></i>统计结果
                                </a>
                                <a href="assessment.php?id=${act.id}" class="btn btn-gov-blue">
                                    <i class="bi bi-pencil-square me-1"></i>立即参与
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        document.addEventListener('DOMContentLoaded', loadActivities);

        <?php endif; ?>
    </script>
</body>
</html>