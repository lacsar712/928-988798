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
    case 'get_departments':
        $sql = "SELECT * FROM emergency_departments WHERE status = 1 ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $departments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $departments[] = $row;
        }
        json_output(200, 'success', $departments);
        break;

    case 'get_all_departments':
        check_login();
        $sql = "SELECT * FROM emergency_departments ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $departments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $departments[] = $row;
        }
        json_output(200, 'success', $departments);
        break;

    case 'add_department':
        check_login();
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $icon = isset($_POST['icon']) ? trim($_POST['icon']) : '';
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        if (empty($name)) {
            json_output(400, '部门名称不能为空');
        }
        $name_esc = mysqli_real_escape_string($conn, $name);
        $icon_esc = mysqli_real_escape_string($conn, $icon);
        $sql = "INSERT INTO emergency_departments (name, icon, sort_order, status) VALUES ('$name_esc', '$icon_esc', $sort_order, $status)";
        if (mysqli_query($conn, $sql)) {
            $id = mysqli_insert_id($conn);
            Logger::logAction('EmergencyContact', "添加部门: $name");
            json_output(200, '添加成功', ['id' => $id]);
        } else {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_department':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $icon = isset($_POST['icon']) ? trim($_POST['icon']) : '';
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        if ($id <= 0 || empty($name)) {
            json_output(400, '参数错误');
        }
        $name_esc = mysqli_real_escape_string($conn, $name);
        $icon_esc = mysqli_real_escape_string($conn, $icon);
        $sql = "UPDATE emergency_departments SET name = '$name_esc', icon = '$icon_esc', sort_order = $sort_order, status = $status WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('EmergencyContact', "更新部门: $name");
            json_output(200, '更新成功');
        } else {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete_department':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql_check = "SELECT COUNT(*) as cnt FROM emergency_contacts WHERE department_id = $id";
        $res = mysqli_query($conn, $sql_check);
        $row = mysqli_fetch_assoc($res);
        if ($row['cnt'] > 0) {
            json_output(400, '该部门下存在联系人，无法删除');
        }
        $sql = "DELETE FROM emergency_departments WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('EmergencyContact', "删除部门 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'get_contacts_by_department':
        $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
        $only_24h = isset($_GET['only_24h']) ? intval($_GET['only_24h']) : 0;
        $where = "WHERE ec.status = 1 AND ed.status = 1";
        if ($department_id > 0) {
            $where .= " AND ec.department_id = $department_id";
        }
        if ($only_24h) {
            $where .= " AND ec.is_24h = 1";
        }
        $sql = "SELECT ec.*, ed.name as department_name, ed.icon as department_icon 
                FROM emergency_contacts ec 
                LEFT JOIN emergency_departments ed ON ec.department_id = ed.id 
                $where 
                ORDER BY ed.sort_order ASC, ec.sort_order ASC, ec.id ASC";
        $result = mysqli_query($conn, $sql);
        $contacts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $contacts[] = $row;
        }
        json_output(200, 'success', $contacts);
        break;

    case 'get_contacts_grouped':
        $only_24h = isset($_GET['only_24h']) ? intval($_GET['only_24h']) : 0;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        
        $dept_where = "WHERE status = 1";
        $contact_where = "WHERE ec.status = 1";
        
        if ($only_24h) {
            $contact_where .= " AND ec.is_24h = 1";
        }
        
        if (!empty($keyword)) {
            $keyword_esc = mysqli_real_escape_string($conn, $keyword);
            $contact_where .= " AND (
                ec.name LIKE '%$keyword_esc%' 
                OR ec.emergency_phone LIKE '%$keyword_esc%' 
                OR ec.office_phone LIKE '%$keyword_esc%' 
                OR ec.position LIKE '%$keyword_esc%' 
                OR ed.name LIKE '%$keyword_esc%'
            )";
        }
        
        $sql = "SELECT ed.* FROM emergency_departments ed $dept_where ORDER BY ed.sort_order ASC, ed.id ASC";
        $result = mysqli_query($conn, $sql);
        $departments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $departments[] = $row;
        }
        
        $grouped = [];
        foreach ($departments as $dept) {
            $dept_id = intval($dept['id']);
            $contact_sql = "SELECT ec.*, ed.name as department_name 
                           FROM emergency_contacts ec 
                           LEFT JOIN emergency_departments ed ON ec.department_id = ed.id 
                           $contact_where AND ec.department_id = $dept_id
                           ORDER BY ec.sort_order ASC, ec.id ASC";
            $contact_result = mysqli_query($conn, $contact_sql);
            $contacts = [];
            while ($contact_row = mysqli_fetch_assoc($contact_result)) {
                $contacts[] = $contact_row;
            }
            if (!empty($contacts) || empty($keyword)) {
                $dept['contacts'] = $contacts;
                $grouped[] = $dept;
            }
        }
        
        json_output(200, 'success', $grouped);
        break;

    case 'search_contacts':
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $only_24h = isset($_GET['only_24h']) ? intval($_GET['only_24h']) : 0;
        if (empty($keyword)) {
            json_output(400, '请输入搜索关键字');
        }
        $keyword_esc = mysqli_real_escape_string($conn, $keyword);
        $where = "WHERE ec.status = 1 AND ed.status = 1 AND (
            ec.name LIKE '%$keyword_esc%' 
            OR ec.emergency_phone LIKE '%$keyword_esc%' 
            OR ec.office_phone LIKE '%$keyword_esc%' 
            OR ec.position LIKE '%$keyword_esc%' 
            OR ed.name LIKE '%$keyword_esc%'
        )";
        if ($only_24h) {
            $where .= " AND ec.is_24h = 1";
        }
        $sql = "SELECT ec.*, ed.name as department_name, ed.icon as department_icon 
                FROM emergency_contacts ec 
                LEFT JOIN emergency_departments ed ON ec.department_id = ed.id 
                $where 
                ORDER BY ec.is_24h DESC, ed.sort_order ASC, ec.sort_order ASC, ec.id ASC";
        $result = mysqli_query($conn, $sql);
        $contacts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $contacts[] = $row;
        }
        json_output(200, 'success', $contacts);
        break;

    case 'get_contacts_admin':
        check_login();
        $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $where = "";
        $conditions = [];
        if ($department_id > 0) {
            $conditions[] = "ec.department_id = $department_id";
        }
        if (!empty($keyword)) {
            $keyword_esc = mysqli_real_escape_string($conn, $keyword);
            $conditions[] = "(
                ec.name LIKE '%$keyword_esc%' 
                OR ec.emergency_phone LIKE '%$keyword_esc%' 
                OR ec.office_phone LIKE '%$keyword_esc%'
            )";
        }
        if (!empty($conditions)) {
            $where = " WHERE " . implode(" AND ", $conditions);
        }
        $sql = "SELECT ec.*, ed.name as department_name 
                FROM emergency_contacts ec 
                LEFT JOIN emergency_departments ed ON ec.department_id = ed.id 
                $where 
                ORDER BY ed.sort_order ASC, ec.sort_order ASC, ec.id ASC";
        $result = mysqli_query($conn, $sql);
        $contacts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $contacts[] = $row;
        }
        json_output(200, 'success', $contacts);
        break;

    case 'get_contact_detail':
        check_login();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT * FROM emergency_contacts WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $contact = mysqli_fetch_assoc($result);
        if ($contact) {
            json_output(200, 'success', $contact);
        } else {
            json_output(404, '记录不存在');
        }
        break;

    case 'add_contact':
        check_login();
        $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $position = isset($_POST['position']) ? trim($_POST['position']) : '';
        $emergency_phone = isset($_POST['emergency_phone']) ? trim($_POST['emergency_phone']) : '';
        $office_phone = isset($_POST['office_phone']) ? trim($_POST['office_phone']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $duty_time = isset($_POST['duty_time']) ? trim($_POST['duty_time']) : '';
        $is_24h = isset($_POST['is_24h']) ? intval($_POST['is_24h']) : 0;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if ($department_id <= 0 || empty($name)) {
            json_output(400, '请填写完整信息');
        }
        $name_esc = mysqli_real_escape_string($conn, $name);
        $position_esc = mysqli_real_escape_string($conn, $position);
        $emergency_phone_esc = mysqli_real_escape_string($conn, $emergency_phone);
        $office_phone_esc = mysqli_real_escape_string($conn, $office_phone);
        $email_esc = mysqli_real_escape_string($conn, $email);
        $duty_time_esc = mysqli_real_escape_string($conn, $duty_time);

        $sql = "INSERT INTO emergency_contacts (department_id, name, position, emergency_phone, office_phone, email, duty_time, is_24h, sort_order, status) 
                VALUES ($department_id, '$name_esc', '$position_esc', '$emergency_phone_esc', '$office_phone_esc', '$email_esc', '$duty_time_esc', $is_24h, $sort_order, $status)";
        if (mysqli_query($conn, $sql)) {
            $id = mysqli_insert_id($conn);
            Logger::logAction('EmergencyContact', "添加联系人: $name");
            json_output(200, '添加成功', ['id' => $id]);
        } else {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_contact':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $position = isset($_POST['position']) ? trim($_POST['position']) : '';
        $emergency_phone = isset($_POST['emergency_phone']) ? trim($_POST['emergency_phone']) : '';
        $office_phone = isset($_POST['office_phone']) ? trim($_POST['office_phone']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $duty_time = isset($_POST['duty_time']) ? trim($_POST['duty_time']) : '';
        $is_24h = isset($_POST['is_24h']) ? intval($_POST['is_24h']) : 0;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if ($id <= 0 || $department_id <= 0 || empty($name)) {
            json_output(400, '请填写完整信息');
        }
        $name_esc = mysqli_real_escape_string($conn, $name);
        $position_esc = mysqli_real_escape_string($conn, $position);
        $emergency_phone_esc = mysqli_real_escape_string($conn, $emergency_phone);
        $office_phone_esc = mysqli_real_escape_string($conn, $office_phone);
        $email_esc = mysqli_real_escape_string($conn, $email);
        $duty_time_esc = mysqli_real_escape_string($conn, $duty_time);

        $sql = "UPDATE emergency_contacts SET 
                department_id = $department_id, 
                name = '$name_esc', 
                position = '$position_esc', 
                emergency_phone = '$emergency_phone_esc', 
                office_phone = '$office_phone_esc', 
                email = '$email_esc', 
                duty_time = '$duty_time_esc', 
                is_24h = $is_24h, 
                sort_order = $sort_order, 
                status = $status 
                WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('EmergencyContact', "更新联系人: $name");
            json_output(200, '更新成功');
        } else {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete_contact':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "DELETE FROM emergency_contacts WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('EmergencyContact', "删除联系人 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_sort':
        check_login();
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $items_raw = isset($_POST['items']) ? $_POST['items'] : '';
        $items = json_decode($items_raw, true);
        if (empty($type) || !is_array($items) || empty($items)) {
            json_output(400, '参数错误');
        }
        $table = ($type === 'department') ? 'emergency_departments' : 'emergency_contacts';
        $success = true;
        foreach ($items as $item) {
            $id = intval($item['id']);
            $sort_order = intval($item['sort_order']);
            $sql = "UPDATE $table SET sort_order = $sort_order WHERE id = $id";
            if (!mysqli_query($conn, $sql)) {
                $success = false;
                break;
            }
        }
        if ($success) {
            Logger::logAction('EmergencyContact', "更新排序: $type");
            json_output(200, '排序更新成功');
        } else {
            json_output(500, '排序更新失败');
        }
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>
