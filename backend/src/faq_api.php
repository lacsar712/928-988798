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

function get_faq_category_tree() {
    global $conn;
    $sql = "SELECT * FROM faq_categories WHERE status = 1 ORDER BY sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    return build_category_tree($categories, 0);
}

function build_category_tree($categories, $parent_id) {
    $tree = [];
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parent_id) {
            $cat['children'] = build_category_tree($categories, $cat['id']);
            $tree[] = $cat;
        }
    }
    return $tree;
}

switch ($action) {
    case 'get_category_tree':
        $tree = get_faq_category_tree();
        json_output(200, 'success', $tree);
        break;

    case 'get_all_categories':
        $sql = "SELECT * FROM faq_categories ORDER BY sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        json_output(200, 'success', $categories);
        break;

    case 'get_items_by_category':
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        if ($category_id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT * FROM faq_items WHERE category_id = $category_id AND status = 1 ORDER BY is_top DESC, sort_order ASC, id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'search_items':
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        if (empty($keyword)) {
            json_output(400, '请输入搜索关键字');
        }
        $keyword_esc = mysqli_real_escape_string($conn, $keyword);
        $sql = "SELECT fi.*, fc.name as category_name FROM faq_items fi 
                LEFT JOIN faq_categories fc ON fi.category_id = fc.id 
                WHERE fi.status = 1 AND (
                    MATCH(fi.question, fi.answer) AGAINST('$keyword_esc' IN BOOLEAN MODE)
                    OR fi.question LIKE '%$keyword_esc%'
                    OR fi.answer LIKE '%$keyword_esc%'
                ) ORDER BY fi.is_top DESC, fi.sort_order ASC, fi.id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'add_category':
        check_login();
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        $icon = isset($_POST['icon']) ? trim($_POST['icon']) : '';
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        if (empty($name)) {
            json_output(400, '分类名称不能为空');
        }
        $name_esc = mysqli_real_escape_string($conn, $name);
        $icon_esc = mysqli_real_escape_string($conn, $icon);
        $sql = "INSERT INTO faq_categories (parent_id, name, icon, sort_order) VALUES ($parent_id, '$name_esc', '$icon_esc', $sort_order)";
        if (mysqli_query($conn, $sql)) {
            $id = mysqli_insert_id($conn);
            Logger::logAction('FAQ', "添加分类: $name");
            json_output(200, '添加成功', ['id' => $id]);
        } else {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_category':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        $icon = isset($_POST['icon']) ? trim($_POST['icon']) : '';
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        if ($id <= 0 || empty($name)) {
            json_output(400, '参数错误');
        }
        $name_esc = mysqli_real_escape_string($conn, $name);
        $icon_esc = mysqli_real_escape_string($conn, $icon);
        $sql = "UPDATE faq_categories SET parent_id = $parent_id, name = '$name_esc', icon = '$icon_esc', sort_order = $sort_order, status = $status WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('FAQ', "更新分类: $name");
            json_output(200, '更新成功');
        } else {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete_category':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql_check = "SELECT COUNT(*) as cnt FROM faq_items WHERE category_id = $id";
        $res = mysqli_query($conn, $sql_check);
        $row = mysqli_fetch_assoc($res);
        if ($row['cnt'] > 0) {
            json_output(400, '该分类下存在问答，无法删除');
        }
        $sql_check_child = "SELECT COUNT(*) as cnt FROM faq_categories WHERE parent_id = $id";
        $res2 = mysqli_query($conn, $sql_check_child);
        $row2 = mysqli_fetch_assoc($res2);
        if ($row2['cnt'] > 0) {
            json_output(400, '该分类下存在子分类，无法删除');
        }
        $sql = "DELETE FROM faq_categories WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('FAQ', "删除分类 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'get_items_admin':
        check_login();
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        $where = '';
        if ($category_id > 0) {
            $where = " WHERE fi.category_id = $category_id";
        }
        $sql = "SELECT fi.*, fc.name as category_name FROM faq_items fi LEFT JOIN faq_categories fc ON fi.category_id = fc.id $where ORDER BY fi.is_top DESC, fi.sort_order ASC, fi.id ASC";
        $result = mysqli_query($conn, $sql);
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        json_output(200, 'success', $items);
        break;

    case 'get_item_detail':
        check_login();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "SELECT * FROM faq_items WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $item = mysqli_fetch_assoc($result);
        if ($item) {
            json_output(200, 'success', $item);
        } else {
            json_output(404, '记录不存在');
        }
        break;

    case 'add_item':
        check_login();
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $question = isset($_POST['question']) ? trim($_POST['question']) : '';
        $answer = isset($_POST['answer']) ? $_POST['answer'] : '';
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $is_top = isset($_POST['is_top']) ? intval($_POST['is_top']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        if ($category_id <= 0 || empty($question) || empty($answer)) {
            json_output(400, '请填写完整信息');
        }
        $question_esc = mysqli_real_escape_string($conn, $question);
        $answer_esc = mysqli_real_escape_string($conn, $answer);
        $sql = "INSERT INTO faq_items (category_id, question, answer, sort_order, is_top, status) VALUES ($category_id, '$question_esc', '$answer_esc', $sort_order, $is_top, $status)";
        if (mysqli_query($conn, $sql)) {
            $id = mysqli_insert_id($conn);
            Logger::logAction('FAQ', "添加问答: $question");
            json_output(200, '添加成功', ['id' => $id]);
        } else {
            json_output(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'update_item':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $question = isset($_POST['question']) ? trim($_POST['question']) : '';
        $answer = isset($_POST['answer']) ? $_POST['answer'] : '';
        $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
        $is_top = isset($_POST['is_top']) ? intval($_POST['is_top']) : 0;
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        if ($id <= 0 || $category_id <= 0 || empty($question) || empty($answer)) {
            json_output(400, '请填写完整信息');
        }
        $question_esc = mysqli_real_escape_string($conn, $question);
        $answer_esc = mysqli_real_escape_string($conn, $answer);
        $sql = "UPDATE faq_items SET category_id = $category_id, question = '$question_esc', answer = '$answer_esc', sort_order = $sort_order, is_top = $is_top, status = $status WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('FAQ', "更新问答: $question");
            json_output(200, '更新成功');
        } else {
            json_output(500, '更新失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete_item':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "DELETE FROM faq_items WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('FAQ', "删除问答 ID: $id");
            json_output(200, '删除成功');
        } else {
            json_output(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'toggle_top':
        check_login();
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $is_top = isset($_POST['is_top']) ? intval($_POST['is_top']) : 0;
        if ($id <= 0) {
            json_output(400, '参数错误');
        }
        $sql = "UPDATE faq_items SET is_top = $is_top WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            json_output(200, '操作成功');
        } else {
            json_output(500, '操作失败: ' . mysqli_error($conn));
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
        $table = ($type === 'category') ? 'faq_categories' : 'faq_items';
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
            Logger::logAction('FAQ', "更新排序: $type");
            json_output(200, '排序更新成功');
        } else {
            json_output(500, '排序更新失败');
        }
        break;

    case 'increase_view':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            $sql = "UPDATE faq_items SET view_count = view_count + 1 WHERE id = $id";
            @mysqli_query($conn, $sql);
        }
        json_output(200, 'success');
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>
