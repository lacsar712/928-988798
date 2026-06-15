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
    case 'get_announcements_admin':
        check_login();
        $position = isset($_GET['position']) ? intval($_GET['position']) : 0;
        $where = '';
        if ($position > 0) {
            $where = " WHERE position = $position";
        }
        $sql = "SELECT * FROM announcements $where ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'get_announcement_detail':
        check_login();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT * FROM announcements WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $item = mysqli_fetch_assoc($result);
        if ($item) {
            json_output(200, 'success', $item);
        } else {
            json_output(404, '记录不存在');
        }
        break;

    case 'add_announcement':
        check_login();
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $position = isset($_POST['position']) ? intval($_POST['position']) : 1;
        $bg_color = isset($_POST['bg_color']) ? trim($_POST['bg_color']) : '#dc3545';
        $text_color = isset($_POST['text_color']) ? trim($_POST['text_color']) : '#ffffff';
        $link_url = isset($_POST['link_url']) ? trim($_POST['link_url']) : '';
        $start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : null;
        $end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : null;
        $can_close = isset($_POST['can_close']) ? intval($_POST['can_close']) : 1;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if (empty($title)) {
            json_output(400, '公告标题不能为空');
        }

        $title_esc = mysqli_real_escape_string($conn, $title);
        $content_esc = mysqli_real_escape_string($conn, $content);
        $bg_color_esc = mysqli_real_escape_string($conn, $bg_color);
        $text_color_esc = mysqli_real_escape_string($conn, $text_color);
        $link_url_esc = mysqli_real_escape_string($conn, $link_url);
        $start_time_esc = $start_time ? "'" . mysqli_real_escape_string($conn, $start_time) . "'" : 'NULL';
        $end_time_esc = $end_time ? "'" . mysqli_real_escape_string($conn, $end_time) . "'" : 'NULL';

        $sql = "INSERT INTO announcements (title, content, position, bg_color, text_color, link_url, start_time, end_time, can_close, sort_order, status) 
                VALUES ('$title_esc', '$content_esc', $position, '$bg_color_esc', '$text_color_esc', '$link_url_esc', $start_time_esc, $end_time_esc, $can_close, $sort_order, $status)";
        
        if (mysqli_query($conn, $sql)) {
            $id = mysqli_insert_id($conn);
            Logger::logAction('Announcement', "添加公告: $title");
            json_output(200, '添加成功', ['id' => $id]);
        } else {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_announcement':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $position = isset($_POST['position']) ? intval($_POST['position']) : 1;
        $bg_color = isset($_POST['bg_color']) ? trim($_POST['bg_color']) : '#dc3545';
        $text_color = isset($_POST['text_color']) ? trim($_POST['text_color']) : '#ffffff';
        $link_url = isset($_POST['link_url']) ? trim($_POST['link_url']) : '';
        $start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : null;
        $end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : null;
        $can_close = isset($_POST['can_close']) ? intval($_POST['can_close']) : 1;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if ($id <= 0 || empty($title)) {
            json_output(400, '参数错误');
        }

        $title_esc = mysqli_real_escape_string($conn, $title);
        $content_esc = mysqli_real_escape_string($conn, $content);
        $bg_color_esc = mysqli_real_escape_string($conn, $bg_color);
        $text_color_esc = mysqli_real_escape_string($conn, $text_color);
        $link_url_esc = mysqli_real_escape_string($conn, $link_url);
        $start_time_esc = $start_time ? "'" . mysqli_real_escape_string($conn, $start_time) . "'" : 'NULL';
        $end_time_esc = $end_time ? "'" . mysqli_real_escape_string($conn, $end_time) . "'" : 'NULL';

        $sql = "UPDATE announcements SET 
                title = '$title_esc', 
                content = '$content_esc', 
                position = $position, 
                bg_color = '$bg_color_esc', 
                text_color = '$text_color_esc', 
                link_url = '$link_url_esc', 
                start_time = $start_time_esc, 
                end_time = $end_time_esc, 
                can_close = $can_close, 
                sort_order = $sort_order, 
                status = $status 
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('Announcement', "更新公告: $title");
            json_output(200, '更新成功');
        } else {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete_announcement':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "DELETE FROM announcements WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('Announcement', "删除公告 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'get_announcements':
        $position = isset($_GET['position']) ? intval($_GET['position']) : 0;
        if ($position <= 0) {
            json_output(400, '参数错误');
        }
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM announcements 
                WHERE position = $position 
                AND status = 1 
                AND (start_time IS NULL OR start_time <= '$now') 
                AND (end_time IS NULL OR end_time >= '$now') 
                ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'report_click':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "UPDATE announcements SET click_count = click_count + 1 WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            json_output(200, '上报成功');
        } else {
            json_output(500, '上报失败');
        }
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>
