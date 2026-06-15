<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'func.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

function json_output($code, $message, $data = null) {
    echo json_encode([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function check_session() {
    if (!isset($_SESSION['si_verified']) || $_SESSION['si_verified'] !== true) {
        json_output(401, '请先完成身份验证');
    }
    if (!isset($_SESSION['si_id_card'])) {
        json_output(401, '会话已失效，请重新验证');
    }
}

function generate_stable_data($id_card, $seed_offset = 0) {
    $hash = crc32($id_card);
    $seed = abs($hash) + $seed_offset;
    $base = ($seed % 5000) + 3000;
    return $base;
}

function generate_fund_account($id_card) {
    $seed = abs(crc32($id_card));
    $balance = round(($seed % 80000) + 20000 + ($seed % 1000) / 100, 2);
    $monthly_unit = round((($seed % 800) + 400) + ($seed % 100) / 100, 2);
    $monthly_personal = round((($seed % 500) + 200) + ($seed % 50) / 100, 2);
    $base_amount = round($monthly_unit / 0.12, 2);
    $months = (($seed % 60) + 6);

    $name_hash = $seed % 100;
    $surnames = ['张', '李', '王', '刘', '陈', '杨', '赵', '黄', '周', '吴'];
    $given1 = ['伟', '芳', '秀英', '敏', '静', '丽', '强', '磊', '洋', '勇'];
    $given2 = ['明', '华', '军', '平', '刚', '桂英', '玉兰', '建华', '建国', '志强'];
    $name = $surnames[$name_hash % 10] . $given1[($name_hash + 3) % 10];

    $company_hash = ($seed + 7) % 20;
    $companies = [
        'XX市信息技术有限公司', 'XX市城市建设集团', 'XX市教育科技有限公司',
        'XX市医疗器械有限公司', 'XX市金融服务有限公司', 'XX市交通运输集团',
        'XX市文化传媒有限公司', 'XX市新能源科技有限公司', 'XX市农业发展有限公司',
        'XX市商贸集团有限公司'
    ];

    $account_no = 'HF' . substr($id_card, 0, 6) . str_pad($seed % 10000, 8, '0', STR_PAD_LEFT);

    return [
        'name' => $name,
        'id_card' => substr($id_card, 0, 4) . '**********' . substr($id_card, -4),
        'account_no' => $account_no,
        'company' => $companies[$company_hash % 10],
        'balance' => $balance,
        'monthly_unit' => $monthly_unit,
        'monthly_personal' => $monthly_personal,
        'base_amount' => $base_amount,
        'months' => $months,
        'status' => '正常缴存',
        'open_date' => date('Y-m-d', strtotime("-{$months} months")),
        'fund_type' => '住房公积金'
    ];
}

function generate_social_account($id_card) {
    $seed = abs(crc32($id_card . 'social'));
    $balance = round(($seed % 40000) + 10000 + ($seed % 1000) / 100, 2);
    $monthly_unit = round((($seed % 1200) + 600) + ($seed % 100) / 100, 2);
    $monthly_personal = round((($seed % 400) + 200) + ($seed % 50) / 100, 2);
    $base_amount = round($monthly_unit / 0.16, 2);

    $name_hash = abs(crc32($id_card)) % 100;
    $surnames = ['张', '李', '王', '刘', '陈', '杨', '赵', '黄', '周', '吴'];
    $given1 = ['伟', '芳', '秀英', '敏', '静', '丽', '强', '磊', '洋', '勇'];
    $name = $surnames[$name_hash % 10] . $given1[($name_hash + 3) % 10];

    $company_hash = ($seed + 11) % 20;
    $companies = [
        'XX市信息技术有限公司', 'XX市城市建设集团', 'XX市教育科技有限公司',
        'XX市医疗器械有限公司', 'XX市金融服务有限公司', 'XX市交通运输集团',
        'XX市文化传媒有限公司', 'XX市新能源科技有限公司', 'XX市农业发展有限公司',
        'XX市商贸集团有限公司'
    ];

    $account_no = 'SB' . substr($id_card, 0, 6) . str_pad($seed % 10000, 8, '0', STR_PAD_LEFT);

    return [
        'name' => $name,
        'id_card' => substr($id_card, 0, 4) . '**********' . substr($id_card, -4),
        'account_no' => $account_no,
        'company' => $companies[$company_hash % 10],
        'balance' => $balance,
        'monthly_unit' => $monthly_unit,
        'monthly_personal' => $monthly_personal,
        'base_amount' => $base_amount,
        'pension_unit' => round($base_amount * 0.16, 2),
        'pension_personal' => round($base_amount * 0.08, 2),
        'medical_unit' => round($base_amount * 0.08, 2),
        'medical_personal' => round($base_amount * 0.02, 2),
        'unemployment_unit' => round($base_amount * 0.005, 2),
        'unemployment_personal' => round($base_amount * 0.005, 2),
        'injury_unit' => round($base_amount * 0.004, 2),
        'maternity_unit' => round($base_amount * 0.008, 2),
        'status' => '正常参保',
        'fund_type' => '社会保险'
    ];
}

function generate_payment_details($id_card, $type = 'fund') {
    $suffix = ($type === 'social') ? 'social' : 'fund';
    $seed = abs(crc32($id_card . $suffix));
    $base = ($seed % 5000) + 5000;

    $details = [];
    $current_time = time();

    for ($i = 11; $i >= 0; $i--) {
        $month_time = strtotime("-{$i} months", $current_time);
        $month_label = date('Y-m', $month_time);

        $variation = (($seed + $i * 7) % 300) - 100;
        $current_base = $base + $variation;
        if ($i < 6) {
            $current_base = $base;
        }

        if ($type === 'fund') {
            $unit_rate = 0.12;
            $personal_rate = 0.12;
        } else {
            $unit_rate = 0.16;
            $personal_rate = 0.08;
        }

        $unit_amount = round($current_base * $unit_rate, 2);
        $personal_amount = round($current_base * $personal_rate, 2);
        $total = round($unit_amount + $personal_amount, 2);

        $details[] = [
            'month' => $month_label,
            'unit_amount' => $unit_amount,
            'personal_amount' => $personal_amount,
            'total' => $total,
            'base_amount' => $current_base
        ];
    }

    return $details;
}

switch ($action) {
    case 'send_code':
        $id_card = isset($_POST['id_card']) ? trim($_POST['id_card']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

        if (empty($id_card) || empty($phone)) {
            json_output(400, '请填写身份证号和手机号');
        }

        if (!preg_match('/^\d{17}[\dXx]$/', $id_card)) {
            json_output(400, '身份证号格式不正确');
        }

        if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
            json_output(400, '手机号格式不正确');
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $_SESSION['si_verify_code'] = $code;
        $_SESSION['si_verify_id_card'] = $id_card;
        $_SESSION['si_verify_phone'] = $phone;
        $_SESSION['si_verify_time'] = time();
        $_SESSION['si_code_attempts'] = 0;

        Logger::logAction('SocialInsurance', "验证码已发送: 手机尾号" . substr($phone, -4));

        json_output(200, '验证码发送成功', ['code' => $code, 'expire' => 300]);
        break;

    case 'verify_code':
        $id_card = isset($_POST['id_card']) ? trim($_POST['id_card']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $code = isset($_POST['code']) ? trim($_POST['code']) : '';

        if (empty($id_card) || empty($phone) || empty($code)) {
            json_output(400, '请填写完整验证信息');
        }

        if (!isset($_SESSION['si_verify_code'])) {
            json_output(400, '请先获取验证码');
        }

        if (!isset($_SESSION['si_verify_time']) || (time() - $_SESSION['si_verify_time']) > 300) {
            unset($_SESSION['si_verify_code']);
            unset($_SESSION['si_verify_id_card']);
            unset($_SESSION['si_verify_phone']);
            unset($_SESSION['si_verify_time']);
            json_output(400, '验证码已过期，请重新获取');
        }

        if (!isset($_SESSION['si_code_attempts'])) {
            $_SESSION['si_code_attempts'] = 0;
        }
        $_SESSION['si_code_attempts']++;

        if ($_SESSION['si_code_attempts'] > 5) {
            unset($_SESSION['si_verify_code']);
            unset($_SESSION['si_verify_id_card']);
            unset($_SESSION['si_verify_phone']);
            unset($_SESSION['si_verify_time']);
            unset($_SESSION['si_code_attempts']);
            json_output(429, '验证次数过多，请重新获取验证码');
        }

        if ($_SESSION['si_verify_id_card'] !== $id_card || $_SESSION['si_verify_phone'] !== $phone) {
            json_output(400, '身份信息与获取验证码时不一致');
        }

        if ($_SESSION['si_verify_code'] !== $code) {
            json_output(400, '验证码错误');
        }

        $_SESSION['si_verified'] = true;
        $_SESSION['si_id_card'] = $id_card;
        $_SESSION['si_phone'] = $phone;
        $_SESSION['si_verify_time'] = time();

        unset($_SESSION['si_verify_code']);
        unset($_SESSION['si_verify_id_card']);
        unset($_SESSION['si_verify_phone']);
        unset($_SESSION['si_code_attempts']);

        Logger::logAction('SocialInsurance', "身份验证通过: 身份证尾号" . substr($id_card, -4));

        json_output(200, '验证成功');
        break;

    case 'query_fund':
        check_session();
        $id_card = $_SESSION['si_id_card'];
        $account = generate_fund_account($id_card);
        Logger::logAction('SocialInsurance', "查询公积金账户: 身份证尾号" . substr($id_card, -4));
        json_output(200, 'success', $account);
        break;

    case 'query_social':
        check_session();
        $id_card = $_SESSION['si_id_card'];
        $account = generate_social_account($id_card);
        Logger::logAction('SocialInsurance', "查询社保账户: 身份证尾号" . substr($id_card, -4));
        json_output(200, 'success', $account);
        break;

    case 'query_details':
        check_session();
        $id_card = $_SESSION['si_id_card'];
        $type = isset($_GET['type']) ? $_GET['type'] : 'fund';
        if (!in_array($type, ['fund', 'social'])) {
            $type = 'fund';
        }
        $details = generate_payment_details($id_card, $type);
        Logger::logAction('SocialInsurance', "查询缴费明细: type={$type}");
        json_output(200, 'success', $details);
        break;

    case 'logout':
        unset($_SESSION['si_verified']);
        unset($_SESSION['si_id_card']);
        unset($_SESSION['si_phone']);
        unset($_SESSION['si_verify_time']);
        Logger::logAction('SocialInsurance', '退出查询会话');
        json_output(200, '已退出查询');
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>
