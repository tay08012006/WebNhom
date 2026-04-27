<?php
// Kiểm tra và khởi động session an toàn, tránh lỗi Notice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['bai_tap_list'])) {
    $_SESSION['bai_tap_list'] = [];
}

$target_dir = __DIR__ . "/../uploads/"; 
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// 1. XÓA BÀI TẬP
if (isset($_GET['action']) && $_GET['action'] == 'delete_bt') {
    $id_delete = $_GET['id'] ?? '';
    $ma_lop_del = $_GET['malop'] ?? '';
    
    foreach ($_SESSION['bai_tap_list'] as $key => $bt) {
        if ($bt['id'] == $id_delete) {
            if (!empty($bt['files'])) {
                foreach ($bt['files'] as $f) {
                    if (file_exists($target_dir . $f)) @unlink($target_dir . $f);
                }
            }
            unset($_SESSION['bai_tap_list'][$key]);
            break;
        }
    }
    header("Location: phonghoc.php?malop=$ma_lop_del&tab=bai-tap");
    exit;
}

// 2. THÊM VÀ SỬA BÀI TẬP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $ma_lop = $_POST['ma_lop'] ?? '';

    $uploaded_files = [];
    if (isset($_FILES['bt_files']) && !empty($_FILES['bt_files']['name'][0])) {
        foreach ($_FILES['bt_files']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['bt_files']['error'][$key] === 0) {
                $safe_filename = preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES['bt_files']['name'][$key]));
                $fname = time() . "_" . $safe_filename;
                if (move_uploaded_file($tmp_name, $target_dir . $fname)) {
                    $uploaded_files[] = $fname;
                }
            }
        }
    }

    if ($action == 'add_bt') {
        $_SESSION['bai_tap_list'][] = [
            'id' => uniqid(),
            'ma_lop' => $ma_lop,
            'tieu_de' => $_POST['tieu_de'] ?? '',
            'noi_dung' => $_POST['noi_dung'] ?? '',
            'deadline' => $_POST['deadline'] ?? '',
            'files' => $uploaded_files,
            'ngay_dang' => date('H:i d/m/Y'),
            'is_edited' => false
        ];
    } elseif ($action == 'update_bt') {
        $id_update = $_POST['id_update'] ?? '';
        $kept_files = $_POST['old_files'] ?? [];

        foreach ($_SESSION['bai_tap_list'] as &$bt) {
            if ($bt['id'] == $id_update) {
                $deleted_files = array_diff($bt['files'] ?? [], $kept_files);
                foreach ($deleted_files as $df) {
                    if (file_exists($target_dir . $df)) @unlink($target_dir . $df);
                }

                $bt['tieu_de'] = $_POST['tieu_de'] ?? '';
                $bt['noi_dung'] = $_POST['noi_dung'] ?? '';
                $bt['deadline'] = $_POST['deadline'] ?? '';
                $bt['files'] = array_merge($kept_files, $uploaded_files);
                $bt['is_edited'] = true; 
                break;
            }
        }
    }
    header("Location: phonghoc.php?malop=$ma_lop&tab=bai-tap");
    exit;
}
?>