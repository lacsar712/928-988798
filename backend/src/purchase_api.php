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

switch ($action) {
    case 'list_purchases':
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $budget_min = isset($_GET['budget_min']) ? $_GET['budget_min'] : '';
        $budget_max = isset($_GET['budget_max']) ? $_GET['budget_max'] : '';

        $where = "WHERE 1=1";
        if (!empty($status)) {
            $status_esc = mysqli_real_escape_string($conn, $status);
            $where .= " AND status = '$status_esc'";
        }
        if (!empty($keyword)) {
            $keyword_esc = mysqli_real_escape_string($conn, $keyword);
            $where .= " AND (project_name LIKE '%$keyword_esc%' OR procurement_unit LIKE '%$keyword_esc%')";
        }
        if ($budget_min !== '' && is_numeric($budget_min)) {
            $budget_min_val = floatval($budget_min);
            $where .= " AND budget_amount >= $budget_min_val";
        }
        if ($budget_max !== '' && is_numeric($budget_max)) {
            $budget_max_val = floatval($budget_max);
            $where .= " AND budget_amount <= $budget_max_val";
        }

        $sql = "SELECT * FROM purchases $where ORDER BY id DESC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'get_purchase_detail':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT * FROM purchases WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $item = mysqli_fetch_assoc($result);
        if ($item) {
            json_output(200, 'success', $item);
        } else {
            json_output(404, '记录不存在');
        }
        break;

    case 'add_purchase':
        check_login();
        $project_name = isset($_POST['project_name']) ? trim($_POST['project_name']) : '';
        $procurement_unit = isset($_POST['procurement_unit']) ? trim($_POST['procurement_unit']) : '';
        $budget_amount = isset($_POST['budget_amount']) ? floatval($_POST['budget_amount']) : 0;
        $status = isset($_POST['status']) ? trim($_POST['status']) : '招标中';
        $deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : null;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $winner = isset($_POST['winner']) ? trim($_POST['winner']) : '';
        $winning_amount = isset($_POST['winning_amount']) && $_POST['winning_amount'] !== '' ? floatval($_POST['winning_amount']) : 'NULL';

        if (empty($project_name) || empty($procurement_unit)) {
            json_output(400, '项目名称和采购单位不能为空');
        }
        if ($status === '已成交' && (empty($winner) || $winning_amount === 'NULL')) {
            json_output(400, '已成交状态必须填写中标人和中标金额');
        }

        $project_name_esc = mysqli_real_escape_string($conn, $project_name);
        $procurement_unit_esc = mysqli_real_escape_string($conn, $procurement_unit);
        $status_esc = mysqli_real_escape_string($conn, $status);
        $deadline_esc = $deadline ? "'" . mysqli_real_escape_string($conn, $deadline) . "'" : 'NULL';
        $content_esc = mysqli_real_escape_string($conn, $content);
        $winner_esc = mysqli_real_escape_string($conn, $winner);
        $winning_amount_esc = $winning_amount === 'NULL' ? 'NULL' : $winning_amount;

        $attachment = '';
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'purchase_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $target_dir = UPLOAD_PATH;
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
                $attachment = $filename;
            }
        }
        $attachment_esc = mysqli_real_escape_string($conn, $attachment);

        $sql = "INSERT INTO purchases (project_name, procurement_unit, budget_amount, status, deadline, content, winner, winning_amount, attachment) 
                VALUES ('$project_name_esc', '$procurement_unit_esc', $budget_amount, '$status_esc', $deadline_esc, '$content_esc', '$winner_esc', $winning_amount_esc, '$attachment_esc')";
        
        if (mysqli_query($conn, $sql)) {
            $id = mysqli_insert_id($conn);
            Logger::logAction('Purchase', "添加采购项目: $project_name");
            json_output(200, '添加成功', ['id' => $id]);
        } else {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_purchase':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $project_name = isset($_POST['project_name']) ? trim($_POST['project_name']) : '';
        $procurement_unit = isset($_POST['procurement_unit']) ? trim($_POST['procurement_unit']) : '';
        $budget_amount = isset($_POST['budget_amount']) ? floatval($_POST['budget_amount']) : 0;
        $status = isset($_POST['status']) ? trim($_POST['status']) : '招标中';
        $deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : null;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $winner = isset($_POST['winner']) ? trim($_POST['winner']) : '';
        $winning_amount = isset($_POST['winning_amount']) && $_POST['winning_amount'] !== '' ? floatval($_POST['winning_amount']) : 'NULL';

        if ($id <= 0 || empty($project_name) || empty($procurement_unit)) {
            json_output(400, '参数错误');
        }
        if ($status === '已成交' && (empty($winner) || $winning_amount === 'NULL')) {
            json_output(400, '已成交状态必须填写中标人和中标金额');
        }

        $project_name_esc = mysqli_real_escape_string($conn, $project_name);
        $procurement_unit_esc = mysqli_real_escape_string($conn, $procurement_unit);
        $status_esc = mysqli_real_escape_string($conn, $status);
        $deadline_esc = $deadline ? "'" . mysqli_real_escape_string($conn, $deadline) . "'" : 'NULL';
        $content_esc = mysqli_real_escape_string($conn, $content);
        $winner_esc = mysqli_real_escape_string($conn, $winner);
        $winning_amount_esc = $winning_amount === 'NULL' ? 'NULL' : $winning_amount;

        $attachment_sql = '';
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'purchase_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $target_dir = UPLOAD_PATH;
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
                $attachment_esc = mysqli_real_escape_string($conn, $filename);
                $attachment_sql = ", attachment = '$attachment_esc'";
            }
        }

        $sql = "UPDATE purchases SET 
                project_name = '$project_name_esc', 
                procurement_unit = '$procurement_unit_esc', 
                budget_amount = $budget_amount, 
                status = '$status_esc', 
                deadline = $deadline_esc, 
                content = '$content_esc', 
                winner = '$winner_esc', 
                winning_amount = $winning_amount_esc
                $attachment_sql
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('Purchase', "更新采购项目: $project_name");
            json_output(200, '更新成功');
        } else {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete_purchase':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "DELETE FROM purchases WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('Purchase', "删除采购项目 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'transition_status':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';
        $winner = isset($_POST['winner']) ? trim($_POST['winner']) : '';
        $winning_amount = isset($_POST['winning_amount']) && $_POST['winning_amount'] !== '' ? floatval($_POST['winning_amount']) : 'NULL';

        if ($id <= 0 || empty($new_status)) {
            json_output(400, '参数错误');
        }

        $valid_statuses = ['招标中', '已截止', '已成交', '流标'];
        if (!in_array($new_status, $valid_statuses)) {
            json_output(400, '无效的状态值');
        }

        if ($new_status === '已成交' && (empty($winner) || $winning_amount === 'NULL')) {
            json_output(400, '已成交状态必须填写中标人和中标金额');
        }

        $status_esc = mysqli_real_escape_string($conn, $new_status);
        $winner_esc = mysqli_real_escape_string($conn, $winner);
        $winning_amount_esc = $winning_amount === 'NULL' ? 'NULL' : $winning_amount;

        if ($new_status === '已成交') {
            $sql = "UPDATE purchases SET status = '$status_esc', winner = '$winner_esc', winning_amount = $winning_amount_esc WHERE id = $id";
        } else {
            $sql = "UPDATE purchases SET status = '$status_esc' WHERE id = $id";
        }

        if (mysqli_query($conn, $sql)) {
            Logger::logAction('Purchase', "状态流转 ID: $id -> $new_status");
            json_output(200, '状态更新成功');
        } else {
            json_output(500, '状态更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'download_attachment':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT attachment FROM purchases WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $item = mysqli_fetch_assoc($result);
        if (!$item || empty($item['attachment'])) {
            json_output(404, '附件不存在');
        }
        $filepath = UPLOAD_PATH . $item['attachment'];
        if (!file_exists($filepath)) {
            json_output(404, '附件文件不存在');
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($item['attachment']) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>
