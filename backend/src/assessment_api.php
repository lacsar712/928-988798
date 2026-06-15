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

function get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $ip;
}

function get_vote_cookie_token($activity_id) {
    $cookie_name = 'assessment_vote_' . $activity_id;
    return isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : null;
}

function set_vote_cookie_token($activity_id, $token) {
    $cookie_name = 'assessment_vote_' . $activity_id;
    setcookie($cookie_name, $token, time() + 86400 * 365, '/');
}

switch ($action) {

    case 'get_active_activities':
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM assessment_activities 
                WHERE status = 1 AND start_time <= '$now' AND end_time >= '$now' 
                ORDER BY start_time DESC, id DESC";
        $result = mysqli_query($conn, $sql);
        $activities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $activities[] = $row;
        }
        json_output(200, 'success', $activities);
        break;

    case 'get_activity_detail':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }

        $sql = "SELECT * FROM assessment_activities WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $activity = mysqli_fetch_assoc($result);
        if (!$activity) {
            json_output(404, '活动不存在');
        }

        $sql_dept = "SELECT * FROM assessment_departments WHERE activity_id = $id ORDER BY sort_order ASC, id ASC";
        $res_dept = mysqli_query($conn, $sql_dept);
        $departments = [];
        while ($row = mysqli_fetch_assoc($res_dept)) {
            $departments[] = $row;
        }

        $sql_ind = "SELECT * FROM assessment_indicators WHERE activity_id = $id ORDER BY sort_order ASC, id ASC";
        $res_ind = mysqli_query($conn, $sql_ind);
        $indicators = [];
        while ($row = mysqli_fetch_assoc($res_ind)) {
            $indicators[] = $row;
        }

        $activity['departments'] = $departments;
        $activity['indicators'] = $indicators;

        $ip = get_client_ip();
        $cookie_token = get_vote_cookie_token($id);
        $has_voted = false;
        $ip_esc = mysqli_real_escape_string($conn, $ip);
        $sql_check = "SELECT id FROM assessment_votes WHERE activity_id = $id AND (ip_address = '$ip_esc'";
        if ($cookie_token) {
            $token_esc = mysqli_real_escape_string($conn, $cookie_token);
            $sql_check .= " OR cookie_token = '$token_esc'";
        }
        $sql_check .= ") LIMIT 1";
        $res_check = mysqli_query($conn, $sql_check);
        if (mysqli_num_rows($res_check) > 0) {
            $has_voted = true;
        }

        $activity['has_voted'] = $has_voted;

        json_output(200, 'success', $activity);
        break;

    case 'submit_vote':
        $activity_id = isset($_POST['activity_id']) ? intval($_POST['activity_id']) : 0;
        $votes_raw = isset($_POST['votes']) ? $_POST['votes'] : '';

        if ($activity_id <= 0 || empty($votes_raw)) {
            json_output(400, '参数错误');
        }

        $votes = json_decode($votes_raw, true);
        if (!is_array($votes) || empty($votes)) {
            json_output(400, '投票数据无效');
        }

        $sql = "SELECT * FROM assessment_activities WHERE id = $activity_id AND status = 1";
        $result = mysqli_query($conn, $sql);
        $activity = mysqli_fetch_assoc($result);
        if (!$activity) {
            json_output(404, '活动不存在或已关闭');
        }

        $now = date('Y-m-d H:i:s');
        if ($now < $activity['start_time'] || $now > $activity['end_time']) {
            json_output(400, '活动未开始或已结束');
        }

        $ip = get_client_ip();
        $cookie_token = get_vote_cookie_token($activity_id);
        $ip_esc = mysqli_real_escape_string($conn, $ip);
        $sql_check = "SELECT id FROM assessment_votes WHERE activity_id = $activity_id AND (ip_address = '$ip_esc'";
        if ($cookie_token) {
            $token_esc = mysqli_real_escape_string($conn, $cookie_token);
            $sql_check .= " OR cookie_token = '$token_esc'";
        }
        $sql_check .= ") LIMIT 1";
        $res_check = mysqli_query($conn, $sql_check);
        if (mysqli_num_rows($res_check) > 0) {
            json_output(400, '您已经参与过本次评议了');
        }

        $sql_dept = "SELECT id FROM assessment_departments WHERE activity_id = $activity_id";
        $res_dept = mysqli_query($conn, $sql_dept);
        $dept_ids = [];
        while ($row = mysqli_fetch_assoc($res_dept)) {
            $dept_ids[] = $row['id'];
        }

        $sql_ind = "SELECT id FROM assessment_indicators WHERE activity_id = $activity_id";
        $res_ind = mysqli_query($conn, $sql_ind);
        $ind_ids = [];
        while ($row = mysqli_fetch_assoc($res_ind)) {
            $ind_ids[] = $row['id'];
        }

        $new_token = md5(uniqid('vote_', true) . $ip . $activity_id);
        set_vote_cookie_token($activity_id, $new_token);

        $ip_esc = mysqli_real_escape_string($conn, $ip);
        $token_esc = mysqli_real_escape_string($conn, $new_token);
        $sql_insert = "INSERT INTO assessment_votes (activity_id, ip_address, cookie_token) 
                       VALUES ($activity_id, '$ip_esc', '$token_esc')";
        if (!mysqli_query($conn, $sql_insert)) {
            json_output(500, '提交失败: ' . mysqli_error($conn));
        }
        $vote_id = mysqli_insert_id($conn);

        foreach ($votes as $vote) {
            $dept_id = intval($vote['department_id']);
            $ind_id = intval($vote['indicator_id']);
            $score = intval($vote['score']);
            if (!in_array($dept_id, $dept_ids) || !in_array($ind_id, $ind_ids)) {
                continue;
            }
            if ($score < 1 || $score > 5) {
                continue;
            }
            $sql_detail = "INSERT INTO assessment_vote_details (vote_id, department_id, indicator_id, score) 
                           VALUES ($vote_id, $dept_id, $ind_id, $score)";
            mysqli_query($conn, $sql_detail);
        }

        Logger::logAction('Assessment', "提交投票: 活动ID=$activity_id, IP=$ip");
        json_output(200, '投票成功，感谢您的参与');
        break;

    case 'get_statistics':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }

        $sql = "SELECT * FROM assessment_activities WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $activity = mysqli_fetch_assoc($result);
        if (!$activity) {
            json_output(404, '活动不存在');
        }

        $sql_dept = "SELECT * FROM assessment_departments WHERE activity_id = $id ORDER BY sort_order ASC, id ASC";
        $res_dept = mysqli_query($conn, $sql_dept);
        $departments = [];
        while ($row = mysqli_fetch_assoc($res_dept)) {
            $departments[] = $row;
        }

        $sql_ind = "SELECT * FROM assessment_indicators WHERE activity_id = $id ORDER BY sort_order ASC, id ASC";
        $res_ind = mysqli_query($conn, $sql_ind);
        $indicators = [];
        while ($row = mysqli_fetch_assoc($res_ind)) {
            $indicators[] = $row;
        }

        $sql_vote_count = "SELECT COUNT(*) as total FROM assessment_votes WHERE activity_id = $id";
        $res_vote_count = mysqli_query($conn, $sql_vote_count);
        $vote_count_row = mysqli_fetch_assoc($res_vote_count);
        $total_votes = intval($vote_count_row['total']);

        $stats = [];
        foreach ($departments as $dept) {
            $dept_id = $dept['id'];
            $dept_stats = [
                'id' => $dept_id,
                'name' => $dept['name'],
                'indicators' => [],
                'total_avg' => 0
            ];
            $total_score = 0;
            $ind_count = 0;

            foreach ($indicators as $ind) {
                $ind_id = $ind['id'];
                $sql_score = "SELECT AVG(score) as avg_score, COUNT(*) as vote_count 
                              FROM assessment_vote_details 
                              WHERE department_id = $dept_id AND indicator_id = $ind_id";
                $res_score = mysqli_query($conn, $sql_score);
                $row = mysqli_fetch_assoc($res_score);
                $avg = floatval($row['avg_score']);
                $count = intval($row['vote_count']);
                $dept_stats['indicators'][] = [
                    'id' => $ind_id,
                    'name' => $ind['name'],
                    'avg' => round($avg, 2),
                    'vote_count' => $count
                ];
                $total_score += $avg;
                $ind_count++;
            }

            $dept_stats['total_avg'] = $ind_count > 0 ? round($total_score / $ind_count, 2) : 0;
            $stats[] = $dept_stats;
        }

        $result_data = [
            'activity' => $activity,
            'departments' => $departments,
            'indicators' => $indicators,
            'total_votes' => $total_votes,
            'stats' => $stats
        ];

        json_output(200, 'success', $result_data);
        break;

    case 'get_activities':
        check_login();
        $sql = "SELECT * FROM assessment_activities ORDER BY id DESC";
        $result = mysqli_query($conn, $sql);
        $activities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $activities[] = $row;
        }
        json_output(200, 'success', $activities);
        break;

    case 'get_activity_admin_detail':
        check_login();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }

        $sql = "SELECT * FROM assessment_activities WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $activity = mysqli_fetch_assoc($result);
        if (!$activity) {
            json_output(404, '活动不存在');
        }

        $sql_dept = "SELECT * FROM assessment_departments WHERE activity_id = $id ORDER BY sort_order ASC, id ASC";
        $res_dept = mysqli_query($conn, $sql_dept);
        $departments = [];
        while ($row = mysqli_fetch_assoc($res_dept)) {
            $departments[] = $row;
        }

        $sql_ind = "SELECT * FROM assessment_indicators WHERE activity_id = $id ORDER BY sort_order ASC, id ASC";
        $res_ind = mysqli_query($conn, $sql_ind);
        $indicators = [];
        while ($row = mysqli_fetch_assoc($res_ind)) {
            $indicators[] = $row;
        }

        $activity['departments'] = $departments;
        $activity['indicators'] = $indicators;

        json_output(200, 'success', $activity);
        break;

    case 'add_activity':
        check_login();
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
        $end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
        $departments_raw = isset($_POST['departments']) ? $_POST['departments'] : '';
        $indicators_raw = isset($_POST['indicators']) ? $_POST['indicators'] : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if (empty($title) || empty($start_time) || empty($end_time)) {
            json_output(400, '请填写完整信息');
        }

        $departments = json_decode($departments_raw, true);
        $indicators = json_decode($indicators_raw, true);

        if (!is_array($departments) || empty($departments)) {
            json_output(400, '请至少添加一个参评部门');
        }
        if (!is_array($indicators) || empty($indicators)) {
            json_output(400, '请至少添加一个评议指标');
        }

        $title_esc = mysqli_real_escape_string($conn, $title);
        $start_esc = mysqli_real_escape_string($conn, $start_time);
        $end_esc = mysqli_real_escape_string($conn, $end_time);

        $sql = "INSERT INTO assessment_activities (title, start_time, end_time, status) 
                VALUES ('$title_esc', '$start_esc', '$end_esc', $status)";
        if (!mysqli_query($conn, $sql)) {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        $activity_id = mysqli_insert_id($conn);

        foreach ($departments as $idx => $dept) {
            $dept_name = mysqli_real_escape_string($conn, trim($dept));
            $sort_order = $idx + 1;
            $sql_dept = "INSERT INTO assessment_departments (activity_id, name, sort_order) 
                         VALUES ($activity_id, '$dept_name', $sort_order)";
            mysqli_query($conn, $sql_dept);
        }

        foreach ($indicators as $idx => $ind) {
            $ind_name = mysqli_real_escape_string($conn, trim($ind));
            $sort_order = $idx + 1;
            $sql_ind = "INSERT INTO assessment_indicators (activity_id, name, sort_order) 
                        VALUES ($activity_id, '$ind_name', $sort_order)";
            mysqli_query($conn, $sql_ind);
        }

        Logger::logAction('Assessment', "添加活动: $title");
        json_output(200, '添加成功', ['id' => $activity_id]);
        break;

    case 'update_activity':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
        $end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
        $departments_raw = isset($_POST['departments']) ? $_POST['departments'] : '';
        $indicators_raw = isset($_POST['indicators']) ? $_POST['indicators'] : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if ($id <= 0 || empty($title) || empty($start_time) || empty($end_time)) {
            json_output(400, '请填写完整信息');
        }

        $departments = json_decode($departments_raw, true);
        $indicators = json_decode($indicators_raw, true);

        if (!is_array($departments) || empty($departments)) {
            json_output(400, '请至少添加一个参评部门');
        }
        if (!is_array($indicators) || empty($indicators)) {
            json_output(400, '请至少添加一个评议指标');
        }

        $title_esc = mysqli_real_escape_string($conn, $title);
        $start_esc = mysqli_real_escape_string($conn, $start_time);
        $end_esc = mysqli_real_escape_string($conn, $end_time);

        $sql = "UPDATE assessment_activities SET title = '$title_esc', start_time = '$start_esc', 
                end_time = '$end_esc', status = $status WHERE id = $id";
        if (!mysqli_query($conn, $sql)) {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }

        mysqli_query($conn, "DELETE FROM assessment_departments WHERE activity_id = $id");
        foreach ($departments as $idx => $dept) {
            $dept_name = mysqli_real_escape_string($conn, trim($dept));
            $sort_order = $idx + 1;
            $sql_dept = "INSERT INTO assessment_departments (activity_id, name, sort_order) 
                         VALUES ($id, '$dept_name', $sort_order)";
            mysqli_query($conn, $sql_dept);
        }

        mysqli_query($conn, "DELETE FROM assessment_indicators WHERE activity_id = $id");
        foreach ($indicators as $idx => $ind) {
            $ind_name = mysqli_real_escape_string($conn, trim($ind));
            $sort_order = $idx + 1;
            $sql_ind = "INSERT INTO assessment_indicators (activity_id, name, sort_order) 
                        VALUES ($id, '$ind_name', $sort_order)";
            mysqli_query($conn, $sql_ind);
        }

        Logger::logAction('Assessment', "更新活动: $title");
        json_output(200, '更新成功');
        break;

    case 'delete_activity':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }

        mysqli_query($conn, "DELETE FROM assessment_vote_details WHERE vote_id IN (SELECT id FROM assessment_votes WHERE activity_id = $id)");
        mysqli_query($conn, "DELETE FROM assessment_votes WHERE activity_id = $id");
        mysqli_query($conn, "DELETE FROM assessment_departments WHERE activity_id = $id");
        mysqli_query($conn, "DELETE FROM assessment_indicators WHERE activity_id = $id");
        $sql = "DELETE FROM assessment_activities WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('Assessment', "删除活动 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>