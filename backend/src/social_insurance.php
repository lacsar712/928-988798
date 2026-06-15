<?php
require_once 'func.php';
$is_verified = isset($_SESSION['si_verified']) && $_SESSION['si_verified'] === true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>公积金社保查询 - GovCore 政务公开与应急指挥平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .page-header {
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .verify-card {
            max-width: 480px;
            margin: 0 auto;
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .verify-card .card-body {
            padding: 2.5rem;
        }
        .code-input-group {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .code-input-group input {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .code-input-group input:focus {
            border-color: #004d99;
            box-shadow: 0 0 0 3px rgba(0, 77, 153, 0.15);
            outline: none;
        }
        .code-input-group input.filled {
            border-color: #004d99;
            background-color: #e6f0ff;
        }
        .send-code-btn {
            white-space: nowrap;
            min-width: 120px;
        }
        .balance-card {
            background: linear-gradient(135deg, #004d99 0%, #0066cc 100%);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 77, 153, 0.3);
        }
        .balance-card.social {
            background: linear-gradient(135deg, #0d6efd 0%, #3d8bfd 100%);
            box-shadow: 0 8px 24px rgba(13, 110, 253, 0.3);
        }
        .balance-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }
        .balance-card::after {
            content: '';
            position: absolute;
            bottom: -40%;
            right: 10%;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }
        .balance-amount {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
        }
        .balance-label {
            font-size: 0.9rem;
            opacity: 0.85;
        }
        .detail-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        .detail-table thead th {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            font-weight: 600;
            color: #495057;
            border: none;
            padding: 0.85rem 1rem;
            font-size: 0.88rem;
        }
        .detail-table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-color: #f0f0f0;
            font-size: 0.9rem;
        }
        .detail-table tbody tr:hover {
            background-color: #f8f9ff;
        }
        .nav-tabs-custom {
            border-bottom: 2px solid #e9ecef;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 0.85rem 1.5rem;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .nav-tabs-custom .nav-link:hover {
            color: #004d99;
            border-bottom-color: rgba(0, 77, 153, 0.3);
        }
        .nav-tabs-custom .nav-link.active {
            color: #004d99;
            font-weight: 700;
            border-bottom-color: #004d99;
            background: none;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .info-item {
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .info-item .label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 4px;
        }
        .info-item .value {
            font-weight: 600;
            color: #2c3e50;
        }
        .chart-container {
            position: relative;
            height: 320px;
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        .section-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem;
        }
        .section-card .card-header {
            border-bottom: 1px solid #f0f0f0;
            font-weight: 600;
            padding: 1rem 1.25rem;
            background: white;
            border-radius: 12px 12px 0 0 !important;
        }
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 2rem;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            background: #e9ecef;
            color: #6c757d;
            transition: all 0.3s;
        }
        .step-circle.active {
            background: #004d99;
            color: white;
        }
        .step-circle.done {
            background: #28a745;
            color: white;
        }
        .step-text {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }
        .step-text.active {
            color: #004d99;
            font-weight: 600;
        }
        .step-line {
            width: 60px;
            height: 2px;
            background: #e9ecef;
            margin: 0 8px;
        }
        .step-line.done {
            background: #28a745;
        }
        @media (max-width: 768px) {
            .page-header { padding: 24px 0; }
            .page-header h1 { font-size: 1.5rem; }
            .verify-card .card-body { padding: 1.5rem; }
            .code-input-group input { width: 40px; height: 48px; font-size: 1.2rem; }
            .balance-amount { font-size: 2rem; }
            .info-grid { grid-template-columns: 1fr 1fr; }
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
                    <li class="nav-item"><a class="nav-link" href="plans.php">应急预案文档库</a></li>
                    <li class="nav-item"><a class="nav-link" href="assessment.php">绩效评议</a></li>
                    <li class="nav-item"><a class="nav-link" href="openday.php">开放日预约</a></li>
                    <li class="nav-item"><a class="nav-link active" href="social_insurance.php">公积金社保</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold mb-2">
                        <i class="bi bi-shield-check me-2"></i>个人公积金社保查询
                    </h1>
                    <p class="lead mb-0 opacity-90">安全查询您的住房公积金与社会保险账户信息</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <?php if (!$is_verified): ?>

        <div class="step-indicator">
            <div class="step-item">
                <div class="step-circle active" id="stepCircle1">1</div>
                <span class="step-text active" id="stepText1">填写信息</span>
            </div>
            <div class="step-line" id="stepLine1"></div>
            <div class="step-item">
                <div class="step-circle" id="stepCircle2">2</div>
                <span class="step-text" id="stepText2">输入验证码</span>
            </div>
            <div class="step-line" id="stepLine2"></div>
            <div class="step-item">
                <div class="step-circle" id="stepCircle3">3</div>
                <span class="step-text" id="stepText3">查询结果</span>
            </div>
        </div>

        <div class="card verify-card" id="verifyStep1">
            <div class="card-body">
                <h4 class="fw-bold text-center mb-1">身份验证</h4>
                <p class="text-muted text-center mb-4">请输入您的身份证号和手机号</p>

                <div class="mb-3">
                    <label class="form-label fw-medium">身份证号</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                        <input type="text" class="form-control" id="idCard" placeholder="请输入18位身份证号" maxlength="18">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">手机号码</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
                        <input type="tel" class="form-control" id="phone" placeholder="请输入11位手机号" maxlength="11">
                    </div>
                </div>

                <button class="btn btn-gov-blue w-100 py-2 fw-bold" id="toStep2Btn" onclick="goToStep2()">
                    <i class="bi bi-arrow-right me-2"></i>下一步，获取验证码
                </button>
            </div>
        </div>

        <div class="card verify-card" id="verifyStep2" style="display:none;">
            <div class="card-body">
                <h4 class="fw-bold text-center mb-1">输入验证码</h4>
                <p class="text-muted text-center mb-2">验证码已发送至 <strong id="maskedPhone"></strong></p>
                <p class="text-muted text-center small mb-4">
                    <i class="bi bi-info-circle me-1"></i>演示环境验证码已自动填入
                </p>

                <div class="code-input-group mb-3" id="codeInputGroup">
                    <input type="text" maxlength="1" class="code-digit" data-index="0" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="1" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="2" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="3" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="4" inputmode="numeric">
                    <input type="text" maxlength="1" class="code-digit" data-index="5" inputmode="numeric">
                </div>

                <div class="text-center mb-4">
                    <button class="btn btn-link text-muted small" id="resendBtn" onclick="sendCode()" disabled>
                        <span id="countdownText">重新发送 (60s)</span>
                    </button>
                </div>

                <button class="btn btn-gov-blue w-100 py-2 fw-bold" id="verifyBtn" onclick="verifyCode()">
                    <i class="bi bi-check-circle me-2"></i>验证并查询
                </button>

                <div class="text-center mt-3">
                    <a href="javascript:void(0)" class="text-muted small" onclick="goBackToStep1()">
                        <i class="bi bi-arrow-left me-1"></i>返回修改信息
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-success-subtle text-success me-2"><i class="bi bi-check-circle-fill me-1"></i>已验证</span>
                <span class="text-muted small">身份证尾号 <strong class="text-dark"><?php echo isset($_SESSION['si_id_card']) ? substr($_SESSION['si_id_card'], -4) : ''; ?></strong></span>
            </div>
            <button class="btn btn-outline-danger btn-sm" onclick="logoutSession()">
                <i class="bi bi-box-arrow-right me-1"></i>退出查询
            </button>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="resultTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="fund-tab" data-bs-toggle="tab" data-bs-target="#fundPanel" type="button" role="tab">
                    <i class="bi bi-building me-1"></i>住房公积金
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#socialPanel" type="button" role="tab">
                    <i class="bi bi-shield-check me-1"></i>社会保险
                </button>
            </li>
        </ul>

        <div class="tab-content" id="resultTabContent">
            <div class="tab-pane fade show active" id="fundPanel" role="tabpanel">
                <div id="fundContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">正在加载公积金数据...</p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="socialPanel" role="tabpanel">
                <div id="socialContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">正在加载社保数据...</p>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        var countdownTimer = null;
        var storedCode = '';

        function goToStep2() {
            var idCard = document.getElementById('idCard').value.trim();
            var phone = document.getElementById('phone').value.trim();

            if (!idCard || !phone) {
                Swal.fire('提示', '请填写身份证号和手机号', 'warning');
                return;
            }
            if (!/^\d{17}[\dXx]$/.test(idCard)) {
                Swal.fire('提示', '身份证号格式不正确', 'warning');
                return;
            }
            if (!/^1[3-9]\d{9}$/.test(phone)) {
                Swal.fire('提示', '手机号格式不正确', 'warning');
                return;
            }

            document.getElementById('maskedPhone').textContent = phone.substring(0, 3) + '****' + phone.substring(7);
            document.getElementById('verifyStep1').style.display = 'none';
            document.getElementById('verifyStep2').style.display = 'block';

            document.getElementById('stepCircle1').className = 'step-circle done';
            document.getElementById('stepCircle1').innerHTML = '<i class="bi bi-check"></i>';
            document.getElementById('stepText1').className = 'step-text';
            document.getElementById('stepLine1').className = 'step-line done';
            document.getElementById('stepCircle2').className = 'step-circle active';
            document.getElementById('stepText2').className = 'step-text active';

            sendCode();
        }

        function goBackToStep1() {
            document.getElementById('verifyStep2').style.display = 'none';
            document.getElementById('verifyStep1').style.display = 'block';

            document.getElementById('stepCircle1').className = 'step-circle active';
            document.getElementById('stepCircle1').innerHTML = '1';
            document.getElementById('stepText1').className = 'step-text active';
            document.getElementById('stepLine1').className = 'step-line';
            document.getElementById('stepCircle2').className = 'step-circle';
            document.getElementById('stepText2').className = 'step-text';

            if (countdownTimer) clearInterval(countdownTimer);
        }

        function sendCode() {
            var idCard = document.getElementById('idCard').value.trim();
            var phone = document.getElementById('phone').value.trim();

            fetch('social_insurance_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=send_code&id_card=' + encodeURIComponent(idCard) + '&phone=' + encodeURIComponent(phone)
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.code === 200) {
                    storedCode = res.data.code;
                    startCountdown();
                    autoFillCode(res.data.code);
                    Swal.fire({
                        title: '验证码已发送',
                        html: '<div class="alert alert-info py-2 small mb-0">演示模式：验证码为 <strong>' + res.data.code + '</strong></div>',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('发送失败', res.message, 'error');
                }
            })
            .catch(function() { Swal.fire('错误', '网络请求失败', 'error'); });
        }

        function autoFillCode(code) {
            var digits = document.querySelectorAll('.code-digit');
            for (var i = 0; i < 6 && i < code.length; i++) {
                digits[i].value = code[i];
                digits[i].classList.add('filled');
            }
        }

        function startCountdown() {
            var seconds = 60;
            var btn = document.getElementById('resendBtn');
            var text = document.getElementById('countdownText');
            btn.disabled = true;
            if (countdownTimer) clearInterval(countdownTimer);

            countdownTimer = setInterval(function() {
                seconds--;
                text.textContent = '重新发送 (' + seconds + 's)';
                if (seconds <= 0) {
                    clearInterval(countdownTimer);
                    btn.disabled = false;
                    text.textContent = '重新发送';
                }
            }, 1000);
        }

        function verifyCode() {
            var idCard = document.getElementById('idCard').value.trim();
            var phone = document.getElementById('phone').value.trim();
            var digits = document.querySelectorAll('.code-digit');
            var code = '';
            digits.forEach(function(d) { code += d.value; });

            if (code.length < 6) {
                Swal.fire('提示', '请输入6位验证码', 'warning');
                return;
            }

            document.getElementById('verifyBtn').disabled = true;
            document.getElementById('verifyBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>验证中...';

            fetch('social_insurance_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=verify_code&id_card=' + encodeURIComponent(idCard) + '&phone=' + encodeURIComponent(phone) + '&code=' + encodeURIComponent(code)
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.code === 200) {
                    document.getElementById('stepCircle2').className = 'step-circle done';
                    document.getElementById('stepCircle2').innerHTML = '<i class="bi bi-check"></i>';
                    document.getElementById('stepText2').className = 'step-text';
                    document.getElementById('stepLine2').className = 'step-line done';
                    document.getElementById('stepCircle3').className = 'step-circle done';
                    document.getElementById('stepText3').className = 'step-text active';

                    Swal.fire({
                        title: '验证成功',
                        text: '正在跳转到查询结果...',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = 'social_insurance.php';
                    });
                } else {
                    Swal.fire('验证失败', res.message, 'error');
                    document.getElementById('verifyBtn').disabled = false;
                    document.getElementById('verifyBtn').innerHTML = '<i class="bi bi-check-circle me-2"></i>验证并查询';
                }
            })
            .catch(function() {
                Swal.fire('错误', '网络请求失败', 'error');
                document.getElementById('verifyBtn').disabled = false;
                document.getElementById('verifyBtn').innerHTML = '<i class="bi bi-check-circle me-2"></i>验证并查询';
            });
        }

        function logoutSession() {
            Swal.fire({
                title: '确认退出？',
                text: '退出后需重新验证身份才能查询',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '确认退出',
                cancelButtonText: '取消',
                confirmButtonColor: '#dc3545'
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('social_insurance_api.php?action=logout')
                    .then(function(r) { return r.json(); })
                    .then(function() {
                        window.location.href = 'social_insurance.php';
                    });
                }
            });
        }

        function loadFundData() {
            fetch('social_insurance_api.php?action=query_fund')
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.code === 200) {
                    renderFundAccount(res.data);
                    loadDetails('fund');
                } else {
                    document.getElementById('fundContent').innerHTML = '<div class="alert alert-danger">' + res.message + '</div>';
                }
            });
        }

        function loadSocialData() {
            fetch('social_insurance_api.php?action=query_social')
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.code === 200) {
                    renderSocialAccount(res.data);
                    loadDetails('social');
                } else {
                    document.getElementById('socialContent').innerHTML = '<div class="alert alert-danger">' + res.message + '</div>';
                }
            });
        }

        function renderFundAccount(d) {
            var html = '';
            html += '<div class="balance-card mb-4">';
            html += '  <div class="row align-items-end">';
            html += '    <div class="col-sm-7">';
            html += '      <div class="balance-label mb-1"><i class="bi bi-building me-1"></i>公积金账户余额</div>';
            html += '      <div class="balance-amount">&yen;' + formatMoney(d.balance) + '</div>';
            html += '      <div class="mt-2 small opacity-75">账户状态：<span class="badge bg-success bg-opacity-75">' + d.status + '</span></div>';
            html += '    </div>';
            html += '    <div class="col-sm-5 mt-3 mt-sm-0">';
            html += '      <div class="row g-2">';
            html += '        <div class="col-6"><div class="small opacity-75">月缴存(单位)</div><div class="fw-bold">&yen;' + formatMoney(d.monthly_unit) + '</div></div>';
            html += '        <div class="col-6"><div class="small opacity-75">月缴存(个人)</div><div class="fw-bold">&yen;' + formatMoney(d.monthly_personal) + '</div></div>';
            html += '        <div class="col-6"><div class="small opacity-75">缴存基数</div><div class="fw-bold">&yen;' + formatMoney(d.base_amount) + '</div></div>';
            html += '        <div class="col-6"><div class="small opacity-75">连续缴存</div><div class="fw-bold">' + d.months + ' 个月</div></div>';
            html += '      </div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';

            html += '<div class="section-card card"><div class="card-header"><i class="bi bi-person-vcard me-2 text-primary"></i>账户信息</div>';
            html += '  <div class="card-body">';
            html += '    <div class="info-grid">';
            html += '      <div class="info-item"><div class="label">姓名</div><div class="value">' + d.name + '</div></div>';
            html += '      <div class="info-item"><div class="label">身份证号</div><div class="value">' + d.id_card + '</div></div>';
            html += '      <div class="info-item"><div class="label">账户号码</div><div class="value">' + d.account_no + '</div></div>';
            html += '      <div class="info-item"><div class="label">缴存单位</div><div class="value">' + d.company + '</div></div>';
            html += '      <div class="info-item"><div class="label">开户日期</div><div class="value">' + d.open_date + '</div></div>';
            html += '      <div class="info-item"><div class="label">账户类型</div><div class="value">' + d.fund_type + '</div></div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';

            html += '<div id="fundDetailsContainer"></div>';
            html += '<div id="fundChartContainer" class="section-card card mt-4"><div class="card-header"><i class="bi bi-graph-up me-2 text-primary"></i>缴费趋势</div><div class="card-body"><div class="chart-container"><canvas id="fundChart"></canvas></div></div></div>';

            document.getElementById('fundContent').innerHTML = html;
        }

        function renderSocialAccount(d) {
            var html = '';
            html += '<div class="balance-card social mb-4">';
            html += '  <div class="row align-items-end">';
            html += '    <div class="col-sm-7">';
            html += '      <div class="balance-label mb-1"><i class="bi bi-shield-check me-1"></i>社保账户余额</div>';
            html += '      <div class="balance-amount">&yen;' + formatMoney(d.balance) + '</div>';
            html += '      <div class="mt-2 small opacity-75">参保状态：<span class="badge bg-success bg-opacity-75">' + d.status + '</span></div>';
            html += '    </div>';
            html += '    <div class="col-sm-5 mt-3 mt-sm-0">';
            html += '      <div class="row g-2">';
            html += '        <div class="col-6"><div class="small opacity-75">月缴(单位)</div><div class="fw-bold">&yen;' + formatMoney(d.monthly_unit) + '</div></div>';
            html += '        <div class="col-6"><div class="small opacity-75">月缴(个人)</div><div class="fw-bold">&yen;' + formatMoney(d.monthly_personal) + '</div></div>';
            html += '        <div class="col-6"><div class="small opacity-75">缴费基数</div><div class="fw-bold">&yen;' + formatMoney(d.base_amount) + '</div></div>';
            html += '      </div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';

            html += '<div class="section-card card"><div class="card-header"><i class="bi bi-person-vcard me-2 text-primary"></i>账户信息</div>';
            html += '  <div class="card-body">';
            html += '    <div class="info-grid">';
            html += '      <div class="info-item"><div class="label">姓名</div><div class="value">' + d.name + '</div></div>';
            html += '      <div class="info-item"><div class="label">身份证号</div><div class="value">' + d.id_card + '</div></div>';
            html += '      <div class="info-item"><div class="label">社保编号</div><div class="value">' + d.account_no + '</div></div>';
            html += '      <div class="info-item"><div class="label">参保单位</div><div class="value">' + d.company + '</div></div>';
            html += '      <div class="info-item"><div class="label">账户类型</div><div class="value">' + d.fund_type + '</div></div>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';

            html += '<div class="section-card card mt-4"><div class="card-header"><i class="bi bi-list-check me-2 text-primary"></i>五险明细</div>';
            html += '  <div class="card-body">';
            html += '    <div class="table-responsive"><table class="table table-sm table-hover mb-0">';
            html += '      <thead><tr><th>险种</th><th class="text-end">单位缴纳</th><th class="text-end">个人缴纳</th><th class="text-end">合计</th></tr></thead>';
            html += '      <tbody>';
            html += '        <tr><td>养老保险</td><td class="text-end">&yen;' + formatMoney(d.pension_unit) + '</td><td class="text-end">&yen;' + formatMoney(d.pension_personal) + '</td><td class="text-end fw-bold">&yen;' + formatMoney(d.pension_unit + d.pension_personal) + '</td></tr>';
            html += '        <tr><td>医疗保险</td><td class="text-end">&yen;' + formatMoney(d.medical_unit) + '</td><td class="text-end">&yen;' + formatMoney(d.medical_personal) + '</td><td class="text-end fw-bold">&yen;' + formatMoney(d.medical_unit + d.medical_personal) + '</td></tr>';
            html += '        <tr><td>失业保险</td><td class="text-end">&yen;' + formatMoney(d.unemployment_unit) + '</td><td class="text-end">&yen;' + formatMoney(d.unemployment_personal) + '</td><td class="text-end fw-bold">&yen;' + formatMoney(d.unemployment_unit + d.unemployment_personal) + '</td></tr>';
            html += '        <tr><td>工伤保险</td><td class="text-end">&yen;' + formatMoney(d.injury_unit) + '</td><td class="text-end">-</td><td class="text-end fw-bold">&yen;' + formatMoney(d.injury_unit) + '</td></tr>';
            html += '        <tr><td>生育保险</td><td class="text-end">&yen;' + formatMoney(d.maternity_unit) + '</td><td class="text-end">-</td><td class="text-end fw-bold">&yen;' + formatMoney(d.maternity_unit) + '</td></tr>';
            html += '      </tbody>';
            html += '    </table></div>';
            html += '  </div>';
            html += '</div>';

            html += '<div id="socialDetailsContainer"></div>';
            html += '<div id="socialChartContainer" class="section-card card mt-4"><div class="card-header"><i class="bi bi-graph-up me-2 text-primary"></i>缴费趋势</div><div class="card-body"><div class="chart-container"><canvas id="socialChart"></canvas></div></div></div>';

            document.getElementById('socialContent').innerHTML = html;
        }

        function loadDetails(type) {
            fetch('social_insurance_api.php?action=query_details&type=' + type)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.code === 200) {
                    renderDetailTable(res.data, type);
                    renderTrendChart(res.data, type);
                }
            });
        }

        function renderDetailTable(details, type) {
            var containerId = type + 'DetailsContainer';
            var html = '<div class="section-card card mt-4"><div class="card-header"><i class="bi bi-table me-2 text-primary"></i>近12个月缴费明细</div>';
            html += '<div class="card-body p-0"><div class="table-responsive"><table class="table detail-table mb-0">';
            html += '<thead><tr><th>月份</th><th class="text-end">单位缴存</th><th class="text-end">个人缴存</th><th class="text-end">合计</th><th class="text-end">缴存基数</th></tr></thead>';
            html += '<tbody>';
            details.forEach(function(d) {
                html += '<tr>';
                html += '<td>' + d.month + '</td>';
                html += '<td class="text-end">&yen;' + formatMoney(d.unit_amount) + '</td>';
                html += '<td class="text-end">&yen;' + formatMoney(d.personal_amount) + '</td>';
                html += '<td class="text-end fw-bold text-primary">&yen;' + formatMoney(d.total) + '</td>';
                html += '<td class="text-end">&yen;' + formatMoney(d.base_amount) + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div></div></div>';

            document.getElementById(containerId).innerHTML = html;
        }

        function renderTrendChart(details, type) {
            var chartId = type + 'Chart';
            var canvas = document.getElementById(chartId);
            if (!canvas) return;

            var labels = details.map(function(d) { return d.month; });
            var unitData = details.map(function(d) { return d.unit_amount; });
            var personalData = details.map(function(d) { return d.personal_amount; });
            var totalData = details.map(function(d) { return d.total; });

            var isFund = type === 'fund';
            var mainColor = isFund ? '#004d99' : '#0d6efd';
            var subColor = isFund ? '#ff6b6b' : '#ffc107';
            var totalColor = isFund ? '#28a745' : '#6610f2';

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '合计',
                            data: totalData,
                            borderColor: totalColor,
                            backgroundColor: totalColor + '15',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        },
                        {
                            label: '单位缴存',
                            data: unitData,
                            borderColor: mainColor,
                            backgroundColor: mainColor + '10',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.3,
                            pointRadius: 2,
                            pointHoverRadius: 5
                        },
                        {
                            label: '个人缴存',
                            data: personalData,
                            borderColor: subColor,
                            backgroundColor: subColor + '10',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.3,
                            pointRadius: 2,
                            pointHoverRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return ctx.dataset.label + ': ¥' + ctx.parsed.y.toFixed(2); }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(v) { return '¥' + v; }
                            }
                        },
                        x: {
                            ticks: { maxRotation: 45, minRotation: 0 }
                        }
                    }
                }
            });
        }

        function formatMoney(num) {
            return parseFloat(num).toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var digits = document.querySelectorAll('.code-digit');
            digits.forEach(function(input, idx) {
                input.addEventListener('input', function(e) {
                    var val = e.target.value.replace(/\D/g, '');
                    e.target.value = val.substring(0, 1);
                    if (val && idx < 5) {
                        digits[idx + 1].focus();
                    }
                    e.target.classList.toggle('filled', !!val);
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                        digits[idx - 1].focus();
                        digits[idx - 1].value = '';
                        digits[idx - 1].classList.remove('filled');
                    }
                });
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                    for (var i = 0; i < 6 && i < text.length; i++) {
                        digits[i].value = text[i];
                        digits[i].classList.add('filled');
                    }
                    if (text.length >= 6) digits[5].focus();
                    else if (text.length > 0) digits[Math.min(text.length, 5)].focus();
                });
            });

            <?php if ($is_verified): ?>
            loadFundData();

            document.getElementById('social-tab').addEventListener('shown.bs.tab', function() {
                if (!document.getElementById('socialContent').querySelector('.balance-card')) {
                    loadSocialData();
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>
