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

function get_tags_by_point($conn, $point_id) {
    $sql = "SELECT t.id, t.name, t.color, t.icon 
            FROM service_tags t 
            INNER JOIN service_point_tags spt ON t.id = spt.tag_id 
            WHERE spt.service_point_id = $point_id 
            AND t.status = 1 
            ORDER BY t.sort_order ASC";
    $result = mysqli_query($conn, $sql);
    $tags = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tags[] = $row;
    }
    return $tags;
}

switch ($action) {
    case 'get_townships':
        $sql = "SELECT * FROM townships WHERE status = 1 ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'get_townships_admin':
        check_login();
        $sql = "SELECT * FROM townships ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sql_count = "SELECT COUNT(*) as cnt FROM service_points WHERE township_id = " . $row['id'];
            $res_count = mysqli_query($conn, $sql_count);
            $row_count = mysqli_fetch_assoc($res_count);
            $row['point_count'] = $row_count['cnt'];
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'get_township_detail':
        check_login();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT * FROM townships WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $item = mysqli_fetch_assoc($result);
        if ($item) {
            json_output(200, 'success', $item);
        } else {
            json_output(404, '记录不存在');
        }
        break;

    case 'add_township':
        check_login();
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $type = isset($_POST['type']) ? intval($_POST['type']) : 1;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if (empty($name)) {
            json_output(400, '乡镇名称不能为空');
        }

        $name_esc = mysqli_real_escape_string($conn, $name);
        $sql = "INSERT INTO townships (name, type, sort_order, status) 
                VALUES ('$name_esc', $type, $sort_order, $status)";
        
        if (mysqli_query($conn, $sql)) {
            $id = mysqli_insert_id($conn);
            Logger::logAction('ServiceMap', "添加乡镇: $name");
            json_output(200, '添加成功', ['id' => $id]);
        } else {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_township':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $type = isset($_POST['type']) ? intval($_POST['type']) : 1;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;

        if ($id <= 0 || empty($name)) {
            json_output(400, '参数错误');
        }

        $name_esc = mysqli_real_escape_string($conn, $name);
        $sql = "UPDATE townships SET 
                name = '$name_esc', 
                type = $type, 
                sort_order = $sort_order, 
                status = $status 
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('ServiceMap', "更新乡镇: $name");
            json_output(200, '更新成功');
        } else {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete_township':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        
        $sql_check = "SELECT COUNT(*) as cnt FROM service_points WHERE township_id = $id";
        $res_check = mysqli_query($conn, $sql_check);
        $row_check = mysqli_fetch_assoc($res_check);
        if ($row_check['cnt'] > 0) {
            json_output(400, '该乡镇下还有服务点，无法删除');
        }

        $sql = "DELETE FROM townships WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('ServiceMap', "删除乡镇 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'get_tags':
        $sql = "SELECT * FROM service_tags WHERE status = 1 ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'get_service_points':
        $township_id = isset($_GET['township_id']) ? intval($_GET['township_id']) : 0;
        $tag_ids = isset($_GET['tag_ids']) ? trim($_GET['tag_ids']) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

        $where = ["sp.status = 1"];
        if ($township_id > 0) {
            $where[] = "sp.township_id = $township_id";
        }
        if ($keyword) {
            $keyword_esc = mysqli_real_escape_string($conn, $keyword);
            $where[] = "(sp.name LIKE '%$keyword_esc%' OR sp.address LIKE '%$keyword_esc%')";
        }

        $join = '';
        if ($tag_ids) {
            $tag_arr = array_filter(array_map('intval', explode(',', $tag_ids)));
            if (!empty($tag_arr)) {
                $tag_str = implode(',', $tag_arr);
                $join = "INNER JOIN service_point_tags spt ON sp.id = spt.service_point_id";
                $where[] = "spt.tag_id IN ($tag_str)";
            }
        }

        $where_sql = implode(' AND ', $where);
        $group_sql = $tag_ids ? "GROUP BY sp.id HAVING COUNT(DISTINCT spt.tag_id) = " . count($tag_arr) : "";

        $sql = "SELECT sp.*, t.name as township_name, t.type as township_type 
                FROM service_points sp 
                INNER JOIN townships t ON sp.township_id = t.id 
                $join 
                WHERE $where_sql 
                $group_sql 
                ORDER BY sp.sort_order ASC, sp.id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['tags'] = get_tags_by_point($conn, $row['id']);
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'get_service_points_admin':
        check_login();
        $township_id = isset($_GET['township_id']) ? intval($_GET['township_id']) : 0;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

        $where = [];
        if ($township_id > 0) {
            $where[] = "sp.township_id = $township_id";
        }
        if ($keyword) {
            $keyword_esc = mysqli_real_escape_string($conn, $keyword);
            $where[] = "(sp.name LIKE '%$keyword_esc%' OR sp.address LIKE '%$keyword_esc%')";
        }

        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT sp.*, t.name as township_name 
                FROM service_points sp 
                LEFT JOIN townships t ON sp.township_id = t.id 
                $where_sql 
                ORDER BY sp.sort_order ASC, sp.id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['tags'] = get_tags_by_point($conn, $row['id']);
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'get_service_point_detail':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT sp.*, t.name as township_name, t.type as township_type 
                FROM service_points sp 
                INNER JOIN townships t ON sp.township_id = t.id 
                WHERE sp.id = $id";
        $result = mysqli_query($conn, $sql);
        $item = mysqli_fetch_assoc($result);
        if ($item) {
            $item['tags'] = get_tags_by_point($conn, $item['id']);
            json_output(200, 'success', $item);
        } else {
            json_output(404, '记录不存在');
        }
        break;

    case 'add_service_point':
        check_login();
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $township_id = isset($_POST['township_id']) ? intval($_POST['township_id']) : 0;
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $open_time = isset($_POST['open_time']) ? trim($_POST['open_time']) : '';
        $coord_x = isset($_POST['coord_x']) ? floatval($_POST['coord_x']) : 0;
        $coord_y = isset($_POST['coord_y']) ? floatval($_POST['coord_y']) : 0;
        $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        $tag_ids = isset($_POST['tag_ids']) ? trim($_POST['tag_ids']) : '';

        if (empty($name) || $township_id <= 0) {
            json_output(400, '服务点名称和所属乡镇不能为空');
        }

        $name_esc = mysqli_real_escape_string($conn, $name);
        $address_esc = mysqli_real_escape_string($conn, $address);
        $phone_esc = mysqli_real_escape_string($conn, $phone);
        $open_time_esc = mysqli_real_escape_string($conn, $open_time);

        $sql = "INSERT INTO service_points (name, township_id, address, phone, open_time, coord_x, coord_y, distance, sort_order, status) 
                VALUES ('$name_esc', $township_id, '$address_esc', '$phone_esc', '$open_time_esc', $coord_x, $coord_y, $distance, $sort_order, $status)";
        
        if (mysqli_query($conn, $sql)) {
            $id = mysqli_insert_id($conn);
            
            if ($tag_ids) {
                $tag_arr = array_filter(array_map('intval', explode(',', $tag_ids)));
                foreach ($tag_arr as $tag_id) {
                    $tag_id = intval($tag_id);
                    $sql_tag = "INSERT IGNORE INTO service_point_tags (service_point_id, tag_id) VALUES ($id, $tag_id)";
                    mysqli_query($conn, $sql_tag);
                }
            }
            
            Logger::logAction('ServiceMap', "添加服务点: $name");
            json_output(200, '添加成功', ['id' => $id]);
        } else {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_service_point':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $township_id = isset($_POST['township_id']) ? intval($_POST['township_id']) : 0;
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $open_time = isset($_POST['open_time']) ? trim($_POST['open_time']) : '';
        $coord_x = isset($_POST['coord_x']) ? floatval($_POST['coord_x']) : 0;
        $coord_y = isset($_POST['coord_y']) ? floatval($_POST['coord_y']) : 0;
        $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        $tag_ids = isset($_POST['tag_ids']) ? trim($_POST['tag_ids']) : '';

        if ($id <= 0 || empty($name) || $township_id <= 0) {
            json_output(400, '参数错误');
        }

        $name_esc = mysqli_real_escape_string($conn, $name);
        $address_esc = mysqli_real_escape_string($conn, $address);
        $phone_esc = mysqli_real_escape_string($conn, $phone);
        $open_time_esc = mysqli_real_escape_string($conn, $open_time);

        $sql = "UPDATE service_points SET 
                name = '$name_esc', 
                township_id = $township_id, 
                address = '$address_esc', 
                phone = '$phone_esc', 
                open_time = '$open_time_esc', 
                coord_x = $coord_x, 
                coord_y = $coord_y, 
                distance = $distance, 
                sort_order = $sort_order, 
                status = $status 
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            mysqli_query($conn, "DELETE FROM service_point_tags WHERE service_point_id = $id");
            
            if ($tag_ids) {
                $tag_arr = array_filter(array_map('intval', explode(',', $tag_ids)));
                foreach ($tag_arr as $tag_id) {
                    $tag_id = intval($tag_id);
                    $sql_tag = "INSERT IGNORE INTO service_point_tags (service_point_id, tag_id) VALUES ($id, $tag_id)";
                    mysqli_query($conn, $sql_tag);
                }
            }
            
            Logger::logAction('ServiceMap', "更新服务点: $name");
            json_output(200, '更新成功');
        } else {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete_service_point':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        
        mysqli_query($conn, "DELETE FROM service_point_tags WHERE service_point_id = $id");
        
        $sql = "DELETE FROM service_points WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('ServiceMap', "删除服务点 ID: $id");
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
