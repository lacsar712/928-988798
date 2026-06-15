<?php
require_once 'func.php';

$activity_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>评议统计结果 - GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .score-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .score-high { background: #d4edda; color: #155724; }
        .score-mid { background: #fff3cd; color: #856404; }
        .score-low { background: #f8d7da; color: #721c24; }
        .rank-1 { background: linear-gradient(135deg, #ffc107, #ff9800); color: white; }
        .rank-2 { background: linear-gradient(135deg, #6c757d, #adb5bd); color: white; }
        .rank-3 { background: linear-gradient(135deg, #cd7f32, #daa06d); color: white; }
        .dept-row:hover {
            background-color: #f8f9fa;
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
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

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded shadow-sm mb-0">
                <li class="breadcrumb-item"><a href="assessment.php" class="text-decoration-none text-muted">绩效评议</a></li>
                <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">统计结果</li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body py-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h3 class="mb-1 text-gov-blue fw-bold" id="activityTitle">加载中...</h3>
                        <p class="text-muted mb-0 small" id="activityTime"></p>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small mb-1">参与人次</div>
                        <div class="fs-3 fw-bold text-gov-blue" id="totalVotes">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-gov-blue">
                    <i class="bi bi-bar-chart me-2"></i>各部门总分排名
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="totalScoreChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-gov-blue">
                    <i class="bi bi-table me-2"></i>详细评分表
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="statsTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3" style="width: 60px;">排名</th>
                                <th class="px-4 py-3">部门名称</th>
                                <th class="px-4 py-3 text-center" id="indicatorHeaders"></th>
                                <th class="px-4 py-3 text-center" style="width: 100px;">总分</th>
                            </tr>
                        </thead>
                        <tbody id="statsBody">
                            <tr><td colspan="10" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>加载中...</td></tr>
                        </tbody>
                    </table>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let statsData = null;
        let chart = null;

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

        function getScoreClass(score) {
            if (score >= 4) return 'score-high';
            if (score >= 3) return 'score-mid';
            return 'score-low';
        }

        function loadStats() {
            apiGet('get_statistics', { id: <?php echo $activity_id; ?> }).then(res => {
                if (res.code === 200) {
                    statsData = res.data;
                    renderStats();
                    renderChart();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '加载失败',
                        text: res.message
                    });
                }
            });
        }

        function renderStats() {
            const { activity, indicators, total_votes, stats } = statsData;

            document.getElementById('activityTitle').textContent = activity.title;
            document.getElementById('activityTime').textContent = 
                '活动时间：' + formatDate(activity.start_time) + ' 至 ' + formatDate(activity.end_time);
            document.getElementById('totalVotes').textContent = total_votes;

            const headerRow = document.getElementById('indicatorHeaders');
            headerRow.colSpan = indicators.length;
            headerRow.outerHTML = indicators.map(ind => 
                `<th class="px-4 py-3 text-center">${ind.name}</th>`
            ).join('');

            const sortedStats = [...stats].sort((a, b) => b.total_avg - a.total_avg);

            const tbody = document.getElementById('statsBody');
            tbody.innerHTML = sortedStats.map((s, idx) => {
                const rank = idx + 1;
                let rankBadge = '';
                if (rank === 1) rankBadge = '<span class="score-badge rank-1">第 1 名</span>';
                else if (rank === 2) rankBadge = '<span class="score-badge rank-2">第 2 名</span>';
                else if (rank === 3) rankBadge = '<span class="score-badge rank-3">第 3 名</span>';
                else rankBadge = `<span class="text-muted fw-bold">${rank}</span>`;

                return `
                    <tr class="dept-row">
                        <td class="px-4">${rankBadge}</td>
                        <td class="px-4 fw-bold">${s.name}</td>
                        ${s.indicators.map(ind => `
                            <td class="px-4 text-center">
                                <span class="score-badge ${getScoreClass(ind.avg)}">${ind.avg.toFixed(2)}</span>
                            </td>
                        `).join('')}
                        <td class="px-4 text-center">
                            <span class="fw-bold fs-5 text-gov-blue">${s.total_avg.toFixed(2)}</span>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderChart() {
            const { indicators, stats } = statsData;
            const ctx = document.getElementById('totalScoreChart').getContext('2d');

            const sortedStats = [...stats].sort((a, b) => b.total_avg - a.total_avg);
            const labels = sortedStats.map(s => s.name);
            const data = sortedStats.map(s => s.total_avg);

            const colors = labels.map((_, i) => {
                if (i === 0) return 'rgba(255, 193, 7, 0.8)';
                if (i === 1) return 'rgba(108, 117, 125, 0.8)';
                if (i === 2) return 'rgba(205, 127, 50, 0.8)';
                return 'rgba(0, 77, 153, 0.6)';
            });

            if (chart) {
                chart.destroy();
            }

            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '总分均分',
                        data: data,
                        backgroundColor: colors,
                        borderRadius: 6,
                        borderWidth: 0,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '均分: ' + context.raw.toFixed(2) + ' 分';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
</body>
</html>