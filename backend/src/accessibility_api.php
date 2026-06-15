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

function get_identifier() {
    $session_id = session_id();
    $admin_user = isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : null;
    return [
        'session_id' => $session_id,
        'admin_user' => $admin_user
    ];
}

function get_current_preferences() {
    global $conn;
    $ident = get_identifier();
    $session_id = mysqli_real_escape_string($conn, $ident['session_id']);
    
    $sql = "SELECT * FROM accessibility_preferences WHERE ";
    if (!empty($ident['admin_user'])) {
        $admin_user = mysqli_real_escape_string($conn, $ident['admin_user']);
        $sql .= "admin_user = '$admin_user' OR session_id = '$session_id' ";
        $sql .= "ORDER BY CASE WHEN admin_user = '$admin_user' THEN 0 ELSE 1 END LIMIT 1";
    } else {
        $sql .= "session_id = '$session_id' LIMIT 1";
    }
    
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return [
            'font_size' => intval($row['font_size']),
            'high_contrast' => intval($row['high_contrast']),
            'eye_care' => intval($row['eye_care']),
            'tts_mode' => intval($row['tts_mode']),
            'focus_highlight' => intval($row['focus_highlight'])
        ];
    }
    return null;
}

$default_prefs = [
    'font_size' => 100,
    'high_contrast' => 0,
    'eye_care' => 0,
    'tts_mode' => 0,
    'focus_highlight' => 0
];

switch ($action) {
    case 'get_preferences':
        $prefs = get_current_preferences();
        if ($prefs) {
            json_output(200, 'success', $prefs);
        } else {
            json_output(200, 'success', $default_prefs);
        }
        break;

    case 'save_preferences':
        $ident = get_identifier();
        $session_id = mysqli_real_escape_string($conn, $ident['session_id']);
        $admin_user = !empty($ident['admin_user']) ? mysqli_real_escape_string($conn, $ident['admin_user']) : null;
        
        $font_size = isset($_POST['font_size']) ? intval($_POST['font_size']) : 100;
        $high_contrast = isset($_POST['high_contrast']) ? intval($_POST['high_contrast']) : 0;
        $eye_care = isset($_POST['eye_care']) ? intval($_POST['eye_care']) : 0;
        $tts_mode = isset($_POST['tts_mode']) ? intval($_POST['tts_mode']) : 0;
        $focus_highlight = isset($_POST['focus_highlight']) ? intval($_POST['focus_highlight']) : 0;
        
        $font_size = max(80, min(150, $font_size));
        $high_contrast = $high_contrast ? 1 : 0;
        $eye_care = $eye_care ? 1 : 0;
        $tts_mode = $tts_mode ? 1 : 0;
        $focus_highlight = $focus_highlight ? 1 : 0;
        
        $existing = get_current_preferences();
        
        if ($existing) {
            if (!empty($admin_user)) {
                $sql = "UPDATE accessibility_preferences SET 
                        font_size = $font_size, high_contrast = $high_contrast, 
                        eye_care = $eye_care, tts_mode = $tts_mode, focus_highlight = $focus_highlight
                        WHERE admin_user = '$admin_user'";
            } else {
                $sql = "UPDATE accessibility_preferences SET 
                        font_size = $font_size, high_contrast = $high_contrast, 
                        eye_care = $eye_care, tts_mode = $tts_mode, focus_highlight = $focus_highlight
                        WHERE session_id = '$session_id' AND admin_user IS NULL";
            }
            mysqli_query($conn, $sql);
        } else {
            $admin_val = !empty($admin_user) ? "'$admin_user'" : 'NULL';
            $sql = "INSERT INTO accessibility_preferences 
                    (session_id, admin_user, font_size, high_contrast, eye_care, tts_mode, focus_highlight)
                    VALUES ('$session_id', $admin_val, $font_size, $high_contrast, $eye_care, $tts_mode, $focus_highlight)";
            mysqli_query($conn, $sql);
        }
        
        if (!empty($admin_user)) {
            $sql_check = "SELECT id FROM accessibility_preferences WHERE admin_user = '$admin_user'";
            $res_check = mysqli_query($conn, $sql_check);
            if (!mysqli_fetch_assoc($res_check)) {
                $sql_update = "UPDATE accessibility_preferences SET admin_user = '$admin_user' 
                              WHERE session_id = '$session_id' AND admin_user IS NULL LIMIT 1";
                mysqli_query($conn, $sql_update);
            }
        }
        
        Logger::logAction('Accessibility', "Saved preferences");
        json_output(200, '保存成功', [
            'font_size' => $font_size,
            'high_contrast' => $high_contrast,
            'eye_care' => $eye_care,
            'tts_mode' => $tts_mode,
            'focus_highlight' => $focus_highlight
        ]);
        break;

    case 'reset_preferences':
        $ident = get_identifier();
        $session_id = mysqli_real_escape_string($conn, $ident['session_id']);
        $admin_user = !empty($ident['admin_user']) ? mysqli_real_escape_string($conn, $ident['admin_user']) : null;
        
        if (!empty($admin_user)) {
            $sql = "DELETE FROM accessibility_preferences WHERE admin_user = '$admin_user'";
        } else {
            $sql = "DELETE FROM accessibility_preferences WHERE session_id = '$session_id' AND admin_user IS NULL";
        }
        mysqli_query($conn, $sql);
        
        Logger::logAction('Accessibility', "Reset preferences to default");
        json_output(200, '已重置为默认设置', $default_prefs);
        break;

    default:
        json_output(404, '接口不存在');
        break;
}
?>
