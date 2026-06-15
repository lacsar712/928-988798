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

$CATEGORIES = ['自然灾害', '事故灾难', '公共卫生', '社会安全'];
$CATEGORY_ICONS = [
    '自然灾害' => 'bi-tornado',
    '事故灾难' => 'bi-exclamation-triangle',
    '公共卫生' => 'bi-heart-pulse',
    '社会安全' => 'bi-shield-lock'
];
$CLASSIFICATIONS = ['公开', '内部'];

switch ($action) {

    case 'add_plan':
        check_login();
        $plan_code = isset($_POST['plan_code']) ? trim($_POST['plan_code']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : '';
        $classification = isset($_POST['classification']) ? trim($_POST['classification']) : '公开';
        $version = isset($_POST['version']) ? trim($_POST['version']) : '1.0';
        $reviser = isset($_POST['reviser']) ? trim($_POST['reviser']) : '';
        $publish_date = isset($_POST['publish_date']) ? trim($_POST['publish_date']) : '';
        $change_summary = isset($_POST['change_summary']) ? trim($_POST['change_summary']) : '初始版本发布';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if (empty($plan_code) || empty($name) || empty($category) || empty($reviser)) {
            json_output(400, '请填写完整信息（编号、名称、类别、修订人为必填）');
        }
        if (!in_array($category, $CATEGORIES)) {
            json_output(400, '类别无效，可选：自然灾害/事故灾难/公共卫生/社会安全');
        }
        if (!in_array($classification, $CLASSIFICATIONS)) {
            json_output(400, '密级无效，可选：公开/内部');
        }

        $code_esc = mysqli_real_escape_string($conn, $plan_code);
        $check = mysqli_query($conn, "SELECT id FROM emergency_plans WHERE plan_code = '$code_esc' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            json_output(400, '预案编号已存在');
        }

        $pdf_file = null;
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['pdf_file'];
            if ($file['type'] !== 'application/pdf') {
                json_output(400, '仅支持PDF格式附件');
            }
            $upload_dir = UPLOAD_PATH . 'plans/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = $plan_code . '_v' . $version . '_' . time() . '.pdf';
            $target = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $pdf_file = 'plans/' . $filename;
            }
        }

        $name_esc = mysqli_real_escape_string($conn, $name);
        $cat_esc = mysqli_real_escape_string($conn, $category);
        $cls_esc = mysqli_real_escape_string($conn, $classification);
        $ver_esc = mysqli_real_escape_string($conn, $version);
        $rev_esc = mysqli_real_escape_string($conn, $reviser);
        $date_esc = mysqli_real_escape_string($conn, $publish_date);
        $pdf_esc = $pdf_file ? "'" . mysqli_real_escape_string($conn, $pdf_file) . "'" : 'NULL';

        $sql = "INSERT INTO emergency_plans (plan_code, name, category, classification, version, reviser, publish_date, pdf_file, status)
                VALUES ('$code_esc', '$name_esc', '$cat_esc', '$cls_esc', '$ver_esc', '$rev_esc', '$date_esc', $pdf_esc, $status)";
        if (!mysqli_query($conn, $sql)) {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        $plan_id = mysqli_insert_id($conn);

        $cs_esc = mysqli_real_escape_string($conn, $change_summary);
        $rev_pdf_esc = $pdf_file ? "'" . mysqli_real_escape_string($conn, $pdf_file) . "'" : 'NULL';
        $sql_rev = "INSERT INTO emergency_plan_revisions (plan_id, version, reviser, change_summary, pdf_file)
                    VALUES ($plan_id, '$ver_esc', '$rev_esc', '$cs_esc', $rev_pdf_esc)";
        mysqli_query($conn, $sql_rev);

        Logger::logAction('EmergencyPlan', "添加预案: $plan_code - $name");
        json_output(200, '添加成功', ['id' => $plan_id]);
        break;

    case 'update_plan':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $plan_code = isset($_POST['plan_code']) ? trim($_POST['plan_code']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : '';
        $classification = isset($_POST['classification']) ? trim($_POST['classification']) : '公开';
        $version = isset($_POST['version']) ? trim($_POST['version']) : '';
        $reviser = isset($_POST['reviser']) ? trim($_POST['reviser']) : '';
        $publish_date = isset($_POST['publish_date']) ? trim($_POST['publish_date']) : '';
        $change_summary = isset($_POST['change_summary']) ? trim($_POST['change_summary']) : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if ($id <= 0 || empty($plan_code) || empty($name) || empty($category) || empty($reviser)) {
            json_output(400, '请填写完整信息');
        }
        if (!in_array($category, $CATEGORIES)) {
            json_output(400, '类别无效');
        }
        if (!in_array($classification, $CLASSIFICATIONS)) {
            json_output(400, '密级无效');
        }

        $code_esc = mysqli_real_escape_string($conn, $plan_code);
        $check_dup = mysqli_query($conn, "SELECT id FROM emergency_plans WHERE plan_code = '$code_esc' AND id != $id LIMIT 1");
        if (mysqli_num_rows($check_dup) > 0) {
            json_output(400, '预案编号已被其他预案使用');
        }

        $sql_existing = "SELECT version FROM emergency_plans WHERE id = $id";
        $res_existing = mysqli_query($conn, $sql_existing);
        $existing = mysqli_fetch_assoc($res_existing);
        if (!$existing) {
            json_output(404, '预案不存在');
        }

        $pdf_file = null;
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['pdf_file'];
            if ($file['type'] !== 'application/pdf') {
                json_output(400, '仅支持PDF格式附件');
            }
            $upload_dir = UPLOAD_PATH . 'plans/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = $plan_code . '_v' . $version . '_' . time() . '.pdf';
            $target = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $pdf_file = 'plans/' . $filename;
            }
        }

        $name_esc = mysqli_real_escape_string($conn, $name);
        $cat_esc = mysqli_real_escape_string($conn, $category);
        $cls_esc = mysqli_real_escape_string($conn, $classification);
        $ver_esc = mysqli_real_escape_string($conn, $version);
        $rev_esc = mysqli_real_escape_string($conn, $reviser);
        $date_esc = mysqli_real_escape_string($conn, $publish_date);

        $pdf_set = '';
        if ($pdf_file) {
            $pdf_esc = mysqli_real_escape_string($conn, $pdf_file);
            $pdf_set = ", pdf_file = '$pdf_esc'";
        }

        $sql = "UPDATE emergency_plans SET plan_code = '$code_esc', name = '$name_esc', category = '$cat_esc',
                classification = '$cls_esc', version = '$ver_esc', reviser = '$rev_esc',
                publish_date = '$date_esc', status = $status $pdf_set WHERE id = $id";
        if (!mysqli_query($conn, $sql)) {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }

        if ($version !== $existing['version']) {
            $cs_esc = mysqli_real_escape_string($conn, $change_summary);
            $rev_pdf_esc = $pdf_file ? "'" . mysqli_real_escape_string($conn, $pdf_file) . "'" : 'NULL';
            $sql_rev = "INSERT INTO emergency_plan_revisions (plan_id, version, reviser, change_summary, pdf_file)
                        VALUES ($id, '$ver_esc', '$rev_esc', '$cs_esc', $rev_pdf_esc)";
            mysqli_query($conn, $sql_rev);
        }

        Logger::logAction('EmergencyPlan', "更新预案: $plan_code - $name (v$version)");
        json_output(200, '更新成功');
        break;

    case 'delete_plan':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }

        $sql_plan = "SELECT * FROM emergency_plans WHERE id = $id";
        $res_plan = mysqli_query($conn, $sql_plan);
        $plan = mysqli_fetch_assoc($res_plan);
        if (!$plan) {
            json_output(404, '预案不存在');
        }

        if ($plan['pdf_file']) {
            $file_path = UPLOAD_PATH . $plan['pdf_file'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        mysqli_query($conn, "DELETE FROM emergency_plan_revisions WHERE plan_id = $id");
        mysqli_query($conn, "DELETE FROM emergency_plans WHERE id = $id");

        Logger::logAction('EmergencyPlan', "删除预案: {$plan['plan_code']} - {$plan['name']}");
        json_output(200, '删除成功');
        break;

    case 'get_category_tree':
        $tree = [];
        foreach ($CATEGORIES as $cat) {
            $cat_esc = mysqli_real_escape_string($conn, $cat);
            $sql = "SELECT COUNT(*) as cnt FROM emergency_plans WHERE category = '$cat_esc' AND status = 1";
            $res = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($res);
            $tree[] = [
                'name' => $cat,
                'icon' => $CATEGORY_ICONS[$cat],
                'count' => intval($row['cnt'])
            ];
        }
        $total_sql = "SELECT COUNT(*) as cnt FROM emergency_plans WHERE status = 1";
        $total_res = mysqli_query($conn, $total_sql);
        $total_row = mysqli_fetch_assoc($total_res);

        json_output(200, 'success', [
            'total' => intval($total_row['cnt']),
            'categories' => $tree
        ]);
        break;

    case 'get_plans':
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $classification = isset($_GET['classification']) ? trim($_GET['classification']) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = isset($_GET['page_size']) ? intval($_GET['page_size']) : 20;
        if ($page < 1) $page = 1;
        if ($page_size < 1 || $page_size > 100) $page_size = 20;
        $offset = ($page - 1) * $page_size;

        $where = ["status = 1"];
        if ($category !== '' && in_array($category, $CATEGORIES)) {
            $cat_esc = mysqli_real_escape_string($conn, $category);
            $where[] = "category = '$cat_esc'";
        }
        if ($classification !== '' && in_array($classification, $CLASSIFICATIONS)) {
            $cls_esc = mysqli_real_escape_string($conn, $classification);
            $where[] = "classification = '$cls_esc'";
        }
        if ($keyword !== '') {
            $kw_esc = mysqli_real_escape_string($conn, $keyword);
            $where[] = "(name LIKE '%$kw_esc%' OR plan_code LIKE '%$kw_esc%')";
        }

        $where_sql = implode(' AND ', $where);

        $count_sql = "SELECT COUNT(*) as total FROM emergency_plans WHERE $where_sql";
        $count_res = mysqli_query($conn, $count_sql);
        $count_row = mysqli_fetch_assoc($count_res);
        $total = intval($count_row['total']);

        $sql = "SELECT id, plan_code, name, category, classification, version, reviser, publish_date, created_at
                FROM emergency_plans WHERE $where_sql
                ORDER BY publish_date DESC, id DESC
                LIMIT $page_size OFFSET $offset";
        $result = mysqli_query($conn, $sql);
        $plans = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $plans[] = $row;
        }

        json_output(200, 'success', [
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size,
            'list' => $plans
        ]);
        break;

    case 'get_plan_detail':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }

        $sql = "SELECT * FROM emergency_plans WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $plan = mysqli_fetch_assoc($result);
        if (!$plan) {
            json_output(404, '预案不存在');
        }

        $sql_rev = "SELECT id, version, reviser, change_summary, created_at
                    FROM emergency_plan_revisions WHERE plan_id = $id
                    ORDER BY created_at DESC";
        $rev_result = mysqli_query($conn, $sql_rev);
        $revisions = [];
        while ($row = mysqli_fetch_assoc($rev_result)) {
            $revisions[] = $row;
        }
        $plan['revisions'] = $revisions;

        json_output(200, 'success', $plan);
        break;

    case 'download_plan':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }

        $sql = "SELECT * FROM emergency_plans WHERE id = $id AND status = 1";
        $result = mysqli_query($conn, $sql);
        $plan = mysqli_fetch_assoc($result);
        if (!$plan) {
            json_output(404, '预案不存在');
        }

        if ($plan['classification'] === '内部') {
            if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
                json_output(403, '该预案为内部文件，请先登录后台');
            }
        }

        if (empty($plan['pdf_file'])) {
            json_output(404, '该预案暂无PDF附件');
        }

        $file_path = UPLOAD_PATH . $plan['pdf_file'];
        if (!file_exists($file_path)) {
            json_output(404, 'PDF文件不存在');
        }

        Logger::logAction('EmergencyPlan', "下载预案: {$plan['plan_code']} - {$plan['name']}");

        $download_name = $plan['plan_code'] . '_' . $plan['name'] . '_v' . $plan['version'] . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
        break;

    case 'get_all_plans':
        check_login();
        $sql = "SELECT * FROM emergency_plans ORDER BY id DESC";
        $result = mysqli_query($conn, $sql);
        $plans = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $plans[] = $row;
        }
        json_output(200, 'success', $plans);
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>
