<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>政府开放日预约 - GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .activity-card {
            transition: all 0.2s;
            border-left: 4px solid var(--gov-blue-primary);
        }
        .activity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .activity-card.full {
            border-left-color: #6c757d;
            opacity: 0.7;
        }
        .activity-card.full:hover {
            transform: none;
            box-shadow: none;
        }
        .remaining-number {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }
        .remaining-number.available {
            color: #198754;
        }
        .remaining-number.low {
            color: #fd7e14;
        }
        .remaining-number.full {
            color: #6c757d;
        }
        .remaining-label {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .quota-bar {
            height: 6px;
            border-radius: 3px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        .quota-bar-inner {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            color: #495057;
        }
        .info-item i {
            color: var(--gov-blue-primary);
            width: 18px;
            text-align: center;
        }
        #qrcodeCanvas {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
        }
        .cancel-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 2rem;
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
                    <li class="nav-item"><a class="nav-link" href="assessment.php">绩效评议</a></li>
                    <li class="nav-item"><a class="nav-link active" href="openday.php">开放日预约</a></li>
                    <li class="nav-item"><a class="nav-link" href="social_insurance.php">公积金社保</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 border-start border-4 border-danger ps-3 text-gov-blue fw-bold">
                    <i class="bi bi-calendar-event me-2"></i>政府开放日预约
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">
                    欢迎预约参加政府开放日活动，零距离了解政府工作。请选择感兴趣的活动进行预约，名额有限，先到先得。
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 text-gov-blue fw-bold">
                    <i class="bi bi-calendar-check me-2"></i> upcoming 开放日活动
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

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h6 class="mb-0 text-gov-blue fw-bold">
                    <i class="bi bi-x-circle me-2"></i>取消预约
                </h6>
            </div>
            <div class="card-body">
                <div class="cancel-section">
                    <p class="text-muted small mb-3">如果您已预约但需要取消，请输入手机号和预约编号进行取消。活动开始前24小时内无法取消。</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="cancelPhone" placeholder="手机号" maxlength="11">
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="cancelCode" placeholder="预约编号（如 OD202606160012）">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-danger w-100" onclick="cancelReservation()">
                                <i class="bi bi-x-circle me-1"></i>取消预约
                            </button>
                        </div>
                    </div>
                    <div id="cancelResult" class="mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h6 class="mb-0 text-gov-blue fw-bold">
                    <i class="bi bi-search me-2"></i>查询我的预约
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="queryPhone" placeholder="请输入手机号查询预约记录" maxlength="11">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-gov-blue w-100" onclick="queryReservations()">
                            <i class="bi bi-search me-1"></i>查询
                        </button>
                    </div>
                </div>
                <div id="queryResult"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reserveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-gov-blue">
                        <i class="bi bi-pencil-square me-2"></i>我要预约
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="alert alert-info py-2 small" id="reserveActivityInfo"></div>
                    <form id="reserveForm">
                        <input type="hidden" id="reserveActivityId" value="">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">姓名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reserveName" required maxlength="50" placeholder="请输入真实姓名">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">身份证号 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reserveIdCard" required maxlength="18" placeholder="请输入18位身份证号">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">手机号 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reservePhone" required maxlength="11" placeholder="请输入手机号">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">同行人数</label>
                            <input type="number" class="form-control" id="reserveCompanions" min="0" max="9" value="0" placeholder="不含本人">
                            <div class="form-text">不包含本人，如只有您一人参加请填0</div>
                        </div>
                        <div class="mb-3" id="reserveSlotsHint"></div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="submitReservation()">
                        <i class="bi bi-check-lg me-1"></i>确认预约
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 rounded-3 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="bi bi-check-circle me-2"></i>预约成功
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4 text-center">
                    <div class="mb-3">
                        <canvas id="qrcodeCanvas" width="200" height="200"></canvas>
                    </div>
                    <div class="alert alert-success text-start">
                        <p class="mb-1"><strong>预约编号：</strong><span id="successCode" class="font-monospace fs-5"></span></p>
                        <p class="mb-1"><strong>活动主题：</strong><span id="successTheme"></span></p>
                        <p class="mb-1"><strong>活动时间：</strong><span id="successTime"></span></p>
                        <p class="mb-1"><strong>活动地点：</strong><span id="successLocation"></span></p>
                        <p class="mb-1"><strong>预约人：</strong><span id="successName"></span></p>
                        <p class="mb-0"><strong>同行人数：</strong><span id="successCompanions"></span></p>
                    </div>
                    <div class="alert alert-warning text-start small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        请妥善保存预约编号，取消预约时需提供手机号和预约编号。活动开始前24小时内不可取消。
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 justify-content-center">
                    <button type="button" class="btn btn-gov-blue" data-bs-dismiss="modal">我知道了</button>
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
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
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
            return fetch('openday_api.php', {
                method: method,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(r => r.json());
        }

        function apiGet(action, data = {}) {
            let url = 'openday_api.php?action=' + action;
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

        function loadActivities() {
            apiGet('get_future_activities').then(res => {
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
                        暂无 upcoming 开放日活动
                    </div>
                `;
                return;
            }

            container.innerHTML = activities.map(act => {
                const remaining = act.remaining;
                const quota = act.quota;
                const used = quota - remaining;
                const isFull = remaining === 0;
                const percentage = quota > 0 ? Math.round((used / quota) * 100) : 0;
                let remainClass = 'available';
                let barColor = '#198754';
                if (remaining === 0) { remainClass = 'full'; barColor = '#6c757d'; }
                else if (remaining <= Math.ceil(quota * 0.2)) { remainClass = 'low'; barColor = '#fd7e14'; }

                return `
                <div class="card activity-card ${isFull ? 'full' : ''} border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h5 class="mb-3 fw-bold ${isFull ? 'text-muted' : 'text-gov-blue'}">${act.theme}</h5>
                                <div class="d-flex flex-column gap-2">
                                    <div class="info-item">
                                        <i class="bi bi-clock"></i>
                                        <span>${formatDate(act.event_time)}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>${act.location}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="bi bi-person-badge"></i>
                                        <span>负责人：${act.manager}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center mt-3 mt-md-0">
                                <div class="remaining-number ${remainClass}">${remaining}</div>
                                <div class="remaining-label">剩余名额</div>
                                <div class="quota-bar mt-2" style="width: 120px; margin: 0 auto;">
                                    <div class="quota-bar-inner" style="width: ${percentage}%; background-color: ${barColor};"></div>
                                </div>
                                <div class="remaining-label mt-1">总名额 ${quota} / 已预约 ${used}</div>
                            </div>
                            <div class="col-md-2 text-center mt-3 mt-md-0">
                                ${isFull
                                    ? '<button class="btn btn-secondary btn-sm" disabled><i class="bi bi-ban me-1"></i>已满员</button>'
                                    : `<button class="btn btn-gov-blue btn-sm" onclick="showReserveModal(${act.id}, '${act.theme.replace(/'/g, "\\'")}', '${formatDate(act.event_time)}', '${act.location.replace(/'/g, "\\'")}', ${remaining})"><i class="bi bi-pencil-square me-1"></i>我要预约</button>`
                                }
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
        }

        function showReserveModal(id, theme, time, location, remaining) {
            document.getElementById('reserveActivityId').value = id;
            document.getElementById('reserveActivityInfo').innerHTML =
                `<strong>${theme}</strong><br><i class="bi bi-clock me-1"></i>${time} | <i class="bi bi-geo-alt me-1"></i>${location} | <i class="bi bi-people me-1"></i>剩余名额：${remaining}`;
            document.getElementById('reserveName').value = '';
            document.getElementById('reserveIdCard').value = '';
            document.getElementById('reservePhone').value = '';
            document.getElementById('reserveCompanions').value = '0';
            document.getElementById('reserveSlotsHint').innerHTML =
                `<small class="text-muted">当前剩余名额：<strong class="text-success">${remaining}</strong>（含本人及同行人员）</small>`;
            new bootstrap.Modal(document.getElementById('reserveModal')).show();
        }

        function submitReservation() {
            const activity_id = document.getElementById('reserveActivityId').value;
            const name = document.getElementById('reserveName').value.trim();
            const id_card = document.getElementById('reserveIdCard').value.trim();
            const phone = document.getElementById('reservePhone').value.trim();
            const companions = document.getElementById('reserveCompanions').value;

            if (!name) { Toast.fire({ icon: 'warning', title: '请输入姓名' }); return; }
            if (!id_card) { Toast.fire({ icon: 'warning', title: '请输入身份证号' }); return; }
            if (!phone) { Toast.fire({ icon: 'warning', title: '请输入手机号' }); return; }

            Swal.fire({
                title: '确认预约？',
                html: `您即将预约参加活动，请确认信息无误。<br>姓名：${name}<br>同行人数：${companions}人`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '确认预约',
                cancelButtonText: '再看看'
            }).then(r => {
                if (r.isConfirmed) {
                    api('submit_reservation', { activity_id, name, id_card, phone, companions }).then(res => {
                        if (res.code === 200) {
                            bootstrap.Modal.getInstance(document.getElementById('reserveModal')).hide();
                            showSuccessModal(res.data);
                            loadActivities();
                        } else {
                            Swal.fire({ icon: 'error', title: '预约失败', text: res.message });
                        }
                    });
                }
            });
        }

        function showSuccessModal(data) {
            document.getElementById('successCode').textContent = data.booking_code;
            document.getElementById('successTheme').textContent = data.activity_theme;
            document.getElementById('successTime').textContent = formatDate(data.event_time);
            document.getElementById('successLocation').textContent = data.location;
            document.getElementById('successName').textContent = data.name;
            document.getElementById('successCompanions').textContent = data.companions + '人（共' + data.total_persons + '人）';

            const canvas = document.getElementById('qrcodeCanvas');
            const qrText = 'OpenDayReservation:' + data.booking_code + '|' + data.activity_theme + '|' + data.name + '|' + data.event_time;
            QRCode.toCanvas(canvas, qrText, {
                width: 180,
                margin: 1,
                color: { dark: '#004d99', light: '#ffffff' }
            }, function(error) {
                if (error) console.error(error);
            });

            new bootstrap.Modal(document.getElementById('successModal')).show();
        }

        function cancelReservation() {
            const phone = document.getElementById('cancelPhone').value.trim();
            const booking_code = document.getElementById('cancelCode').value.trim();

            if (!phone || !booking_code) {
                Toast.fire({ icon: 'warning', title: '请输入手机号和预约编号' });
                return;
            }

            Swal.fire({
                title: '确认取消预约？',
                text: '取消后将释放名额，是否确认？',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '确认取消',
                cancelButtonText: '再想想'
            }).then(r => {
                if (r.isConfirmed) {
                    api('cancel_reservation', { phone, booking_code }).then(res => {
                        if (res.code === 200) {
                            Swal.fire({ icon: 'success', title: '取消成功', text: res.message });
                            document.getElementById('cancelPhone').value = '';
                            document.getElementById('cancelCode').value = '';
                            loadActivities();
                        } else {
                            Swal.fire({ icon: 'error', title: '取消失败', text: res.message });
                        }
                    });
                }
            });
        }

        function queryReservations() {
            const phone = document.getElementById('queryPhone').value.trim();
            if (!phone) {
                Toast.fire({ icon: 'warning', title: '请输入手机号' });
                return;
            }

            apiGet('get_reservations_by_phone', { phone }).then(res => {
                const container = document.getElementById('queryResult');
                if (res.code !== 200 || !res.data || res.data.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-info-circle me-2"></i>未找到相关预约记录
                        </div>
                    `;
                    return;
                }

                container.innerHTML = `
                    <div class="table-responsive">
                        <table class="table table-hover align-middle bg-white rounded shadow-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th>预约编号</th>
                                    <th>活动主题</th>
                                    <th>活动时间</th>
                                    <th>地点</th>
                                    <th>同行人数</th>
                                    <th>状态</th>
                                    <th>预约时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${res.data.map(r => `
                                    <tr>
                                        <td class="font-monospace">${r.booking_code}</td>
                                        <td>${r.theme || '-'}</td>
                                        <td class="small">${r.event_time ? formatDate(r.event_time) : '-'}</td>
                                        <td class="small">${r.location || '-'}</td>
                                        <td class="text-center">${r.companions}</td>
                                        <td>
                                            <span class="status-badge ${r.status == 1 ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'}">
                                                ${r.status == 1 ? '有效' : '已取消'}
                                            </span>
                                        </td>
                                        <td class="small text-muted">${formatDate(r.created_at)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            });
        }

        document.addEventListener('DOMContentLoaded', loadActivities);
    </script>
</body>
</html>
