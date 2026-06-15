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

function generate_booking_code() {
    global $conn;
    $prefix = 'OD';
    $date_part = date('Ymd');
    for ($i = 0; $i < 10; $i++) {
        $rand_part = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $code = $prefix . $date_part . $rand_part;
        $code_esc = mysqli_real_escape_string($conn, $code);
        $check = mysqli_query($conn, "SELECT id FROM openday_reservations WHERE booking_code = '$code_esc' LIMIT 1");
        if (mysqli_num_rows($check) === 0) {
            return $code;
        }
    }
    return $prefix . $date_part . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
}

function get_remaining_slots($activity_id) {
    global $conn;
    $aid = intval($activity_id);
    $sql = "SELECT quota FROM openday_activities WHERE id = $aid";
    $result = mysqli_query($conn, $sql);
    $activity = mysqli_fetch_assoc($result);
    if (!$activity) return -1;

    $quota = intval($activity['quota']);
    $sql_reserved = "SELECT COALESCE(SUM(companions + 1), 0) as used_slots FROM openday_reservations WHERE activity_id = $aid AND status = 1";
    $res_reserved = mysqli_query($conn, $sql_reserved);
    $row = mysqli_fetch_assoc($res_reserved);
    $used = intval($row['used_slots']);

    return max(0, $quota - $used);
}

switch ($action) {

    case 'add_activity':
        check_login();
        $theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
        $event_time = isset($_POST['event_time']) ? trim($_POST['event_time']) : '';
        $location = isset($_POST['location']) ? trim($_POST['location']) : '';
        $quota = isset($_POST['quota']) ? intval($_POST['quota']) : 0;
        $manager = isset($_POST['manager']) ? trim($_POST['manager']) : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if (empty($theme) || empty($event_time) || empty($location) || $quota <= 0 || empty($manager)) {
            json_output(400, '请填写完整信息');
        }

        $theme_esc = mysqli_real_escape_string($conn, $theme);
        $time_esc = mysqli_real_escape_string($conn, $event_time);
        $loc_esc = mysqli_real_escape_string($conn, $location);
        $mgr_esc = mysqli_real_escape_string($conn, $manager);

        $sql = "INSERT INTO openday_activities (theme, event_time, location, quota, manager, status)
                VALUES ('$theme_esc', '$time_esc', '$loc_esc', $quota, '$mgr_esc', $status)";
        if (!mysqli_query($conn, $sql)) {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        $activity_id = mysqli_insert_id($conn);

        Logger::logAction('OpenDay', "添加活动: $theme");
        json_output(200, '添加成功', ['id' => $activity_id]);
        break;

    case 'update_activity':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
        $event_time = isset($_POST['event_time']) ? trim($_POST['event_time']) : '';
        $location = isset($_POST['location']) ? trim($_POST['location']) : '';
        $quota = isset($_POST['quota']) ? intval($_POST['quota']) : 0;
        $manager = isset($_POST['manager']) ? trim($_POST['manager']) : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if ($id <= 0 || empty($theme) || empty($event_time) || empty($location) || $quota <= 0 || empty($manager)) {
            json_output(400, '请填写完整信息');
        }

        $theme_esc = mysqli_real_escape_string($conn, $theme);
        $time_esc = mysqli_real_escape_string($conn, $event_time);
        $loc_esc = mysqli_real_escape_string($conn, $location);
        $mgr_esc = mysqli_real_escape_string($conn, $manager);

        $sql = "UPDATE openday_activities SET theme = '$theme_esc', event_time = '$time_esc',
                location = '$loc_esc', quota = $quota, manager = '$mgr_esc', status = $status WHERE id = $id";
        if (!mysqli_query($conn, $sql)) {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }

        Logger::logAction('OpenDay', "更新活动: $theme");
        json_output(200, '更新成功');
        break;

    case 'delete_activity':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }

        mysqli_query($conn, "DELETE FROM openday_reservations WHERE activity_id = $id");
        $sql = "DELETE FROM openday_activities WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('OpenDay', "删除活动 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'get_activities':
        check_login();
        $sql = "SELECT * FROM openday_activities ORDER BY id DESC";
        $result = mysqli_query($conn, $sql);
        $activities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['remaining'] = get_remaining_slots($row['id']);
            $activities[] = $row;
        }
        json_output(200, 'success', $activities);
        break;

    case 'get_future_activities':
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM openday_activities
                WHERE status = 1 AND event_time > '$now'
                ORDER BY event_time ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $activities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['remaining'] = get_remaining_slots($row['id']);
            $activities[] = $row;
        }
        json_output(200, 'success', $activities);
        break;

    case 'get_remaining_slots':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $remaining = get_remaining_slots($id);
        if ($remaining < 0) {
            json_output(404, '活动不存在');
        }
        json_output(200, 'success', ['remaining' => $remaining]);
        break;

    case 'submit_reservation':
        $activity_id = isset($_POST['activity_id']) ? intval($_POST['activity_id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $id_card = isset($_POST['id_card']) ? trim($_POST['id_card']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $companions = isset($_POST['companions']) ? intval($_POST['companions']) : 0;

        if ($activity_id <= 0 || empty($name) || empty($id_card) || empty($phone)) {
            json_output(400, '请填写完整预约信息');
        }
        if (!preg_match('/^[1-9]\d{5}(18|19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{3}[\dXx]$/', $id_card)) {
            json_output(400, '身份证号格式不正确');
        }
        if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
            json_output(400, '手机号格式不正确');
        }
        if ($companions < 0) {
            json_output(400, '同行人数不能为负数');
        }

        $sql = "SELECT * FROM openday_activities WHERE id = $activity_id AND status = 1";
        $result = mysqli_query($conn, $sql);
        $activity = mysqli_fetch_assoc($result);
        if (!$activity) {
            json_output(404, '活动不存在或已关闭');
        }

        $now = date('Y-m-d H:i:s');
        if ($activity['event_time'] <= $now) {
            json_output(400, '活动已开始，无法预约');
        }

        $total_persons = $companions + 1;

        mysqli_query($conn, "SET autocommit = 0");
        mysqli_query($conn, "START TRANSACTION");

        try {
            $sql_lock = "SELECT quota FROM openday_activities WHERE id = $activity_id FOR UPDATE";
            $res_lock = mysqli_query($conn, $sql_lock);
            if (!$res_lock) throw new Exception('锁定活动失败');

            $act_row = mysqli_fetch_assoc($res_lock);
            $quota = intval($act_row['quota']);

            $sql_used = "SELECT COALESCE(SUM(companions + 1), 0) as used_slots FROM openday_reservations WHERE activity_id = $activity_id AND status = 1 FOR UPDATE";
            $res_used = mysqli_query($conn, $sql_used);
            if (!$res_used) throw new Exception('查询已用名额失败');

            $used_row = mysqli_fetch_assoc($res_used);
            $used = intval($used_row['used_slots']);
            $remaining = $quota - $used;

            if ($total_persons > $remaining) {
                mysqli_query($conn, "ROLLBACK");
                mysqli_query($conn, "SET autocommit = 1");
                json_output(400, "剩余名额不足，当前剩余 {$remaining} 个名额，您需要 {$total_persons} 个名额");
            }

            $booking_code = generate_booking_code();
            $code_esc = mysqli_real_escape_string($conn, $booking_code);
            $name_esc = mysqli_real_escape_string($conn, $name);
            $idcard_esc = mysqli_real_escape_string($conn, $id_card);
            $phone_esc = mysqli_real_escape_string($conn, $phone);

            $sql_insert = "INSERT INTO openday_reservations (activity_id, booking_code, name, id_card, phone, companions, status)
                           VALUES ($activity_id, '$code_esc', '$name_esc', '$idcard_esc', '$phone_esc', $companions, 1)";
            if (!mysqli_query($conn, $sql_insert)) {
                throw new Exception('插入预约失败: ' . mysqli_error($conn));
            }

            mysqli_query($conn, "COMMIT");
            mysqli_query($conn, "SET autocommit = 1");

            Logger::logAction('OpenDay', "预约成功: 活动ID=$activity_id, 编号=$booking_code, 手机=$phone");

            json_output(200, '预约成功', [
                'booking_code' => $booking_code,
                'activity_theme' => $activity['theme'],
                'event_time' => $activity['event_time'],
                'location' => $activity['location'],
                'name' => $name,
                'companions' => $companions,
                'total_persons' => $total_persons
            ]);
        } catch (Exception $e) {
            mysqli_query($conn, "ROLLBACK");
            mysqli_query($conn, "SET autocommit = 1");
            json_output(500, '预约失败: ' . $e->getMessage());
        }
        break;

    case 'get_reservations_by_phone':
        $phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
        if (empty($phone)) {
            json_output(400, '请输入手机号');
        }
        $phone_esc = mysqli_real_escape_string($conn, $phone);
        $sql = "SELECT r.*, a.theme, a.event_time, a.location
                FROM openday_reservations r
                LEFT JOIN openday_activities a ON r.activity_id = a.id
                WHERE r.phone = '$phone_esc'
                ORDER BY r.created_at DESC";
        $result = mysqli_query($conn, $sql);
        $reservations = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reservations[] = $row;
        }
        json_output(200, 'success', $reservations);
        break;

    case 'cancel_reservation':
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $booking_code = isset($_POST['booking_code']) ? trim($_POST['booking_code']) : '';

        if (empty($phone) || empty($booking_code)) {
            json_output(400, '请输入手机号和预约编号');
        }

        $phone_esc = mysqli_real_escape_string($conn, $phone);
        $code_esc = mysqli_real_escape_string($conn, $booking_code);

        $sql = "SELECT r.*, a.event_time, a.theme
                FROM openday_reservations r
                LEFT JOIN openday_activities a ON r.activity_id = a.id
                WHERE r.phone = '$phone_esc' AND r.booking_code = '$code_esc'";
        $result = mysqli_query($conn, $sql);
        $reservation = mysqli_fetch_assoc($result);

        if (!$reservation) {
            json_output(404, '未找到该预约记录，请确认手机号和编号是否正确');
        }

        if (intval($reservation['status']) === 0) {
            json_output(400, '该预约已取消');
        }

        $event_time = $reservation['event_time'];
        $now = date('Y-m-d H:i:s');
        $deadline = date('Y-m-d H:i:s', strtotime($event_time) - 86400);
        if ($now >= $deadline) {
            json_output(400, '活动开始前24小时内无法取消预约');
        }

        $rid = intval($reservation['id']);
        $sql_cancel = "UPDATE openday_reservations SET status = 0 WHERE id = $rid";
        if (mysqli_query($conn, $sql_cancel)) {
            Logger::logAction('OpenDay', "取消预约: 编号=$booking_code, 手机=$phone");
            json_output(200, '取消预约成功');
        } else {
            json_output(500, '取消失败: ' . mysqli_error($conn));
        }
        break;

    case 'get_activity_detail':
        check_login();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT * FROM openday_activities WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $activity = mysqli_fetch_assoc($result);
        if (!$activity) {
            json_output(404, '活动不存在');
        }
        $activity['remaining'] = get_remaining_slots($id);

        $sql_res = "SELECT * FROM openday_reservations WHERE activity_id = $id ORDER BY created_at DESC";
        $res_result = mysqli_query($conn, $sql_res);
        $reservations = [];
        while ($row = mysqli_fetch_assoc($res_result)) {
            $reservations[] = $row;
        }
        $activity['reservations'] = $reservations;

        json_output(200, 'success', $activity);
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>
