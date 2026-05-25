<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'GV_SESSION');
session_start();
}
require '../config.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

$target_dir = "../uploads/"; 
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ma_lop  = $_POST['ma_lop'] ?? $_GET['malop'] ?? '';

// ====================== HÀM UPLOAD FILE ======================
function uploadFiles($input_name, $target_dir) {
    $uploaded_files = [];
    if (isset($_FILES[$input_name]) && !empty($_FILES[$input_name]['name'][0])) {
        foreach ($_FILES[$input_name]['tmp_name'] as $key => $tmp_name) {
            if ($_FILES[$input_name]['error'][$key] === 0) {
                $safe_filename = preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES[$input_name]['name'][$key]));
                $fname = time() . "_" . $safe_filename;
                if (move_uploaded_file($tmp_name, $target_dir . $fname)) {
                    $uploaded_files[] = $fname;
                }
            }
        }
    }
    return $uploaded_files;
}

// Lấy class_id từ ma_lop
$class_id = 0;
if (!empty($ma_lop)) {
    $stmt = $conn->prepare("SELECT id FROM classes WHERE ma_lop = ?");
    $stmt->bind_param("s", $ma_lop);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $class_id = $row['id'];
    }
}

// ====================== XỬ LÝ ======================
if ($action == 'add_bt') {
    $tieu_de = $_POST['tieu_de'] ?? '';
    $noi_dung = $_POST['noi_dung'] ?? '';
    $han_nop = $_POST['deadline'] ?? null;

    if ($class_id <= 0) {
        die("Lỗi: Không tìm thấy lớp học!");
    }

    $uploaded_files = uploadFiles('bt_files', $target_dir);
    $file_str = implode(',', $uploaded_files);

    $stmt = $conn->prepare("INSERT INTO bai_tap (class_id, tieu_de, noi_dung, file_dinh_kem, han_nop, chinh_sua) 
                           VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("issss", $class_id, $tieu_de, $noi_dung, $file_str, $han_nop);
    $stmt->execute();

    header("Location: phonghoc.php?malop=$ma_lop&tab=bai-tap");
    exit;

} elseif ($action == 'update_bt') {
    $id_update = $_POST['id_update'] ?? 0;
    $tieu_de = $_POST['tieu_de'] ?? '';
    $noi_dung = $_POST['noi_dung'] ?? '';
    $han_nop = $_POST['deadline'] ?? null;
    $kept_files = $_POST['old_files'] ?? [];

    // Xử lý file cũ và mới
    $stmt = $conn->prepare("SELECT file_dinh_kem FROM bai_tap WHERE id = ?");
    $stmt->bind_param("i", $id_update);
    $stmt->execute();
    $old_file_str = $stmt->get_result()->fetch_assoc()['file_dinh_kem'] ?? '';
    $old_files = !empty($old_file_str) ? explode(',', $old_file_str) : [];
    
    $deleted_files = array_diff($old_files, $kept_files);
    foreach ($deleted_files as $df) {
        if (file_exists($target_dir . $df)) @unlink($target_dir . $df);
    }

    $uploaded_files = uploadFiles('bt_files', $target_dir);
    $final_files = array_merge($kept_files, $uploaded_files);
    $file_str = implode(',', $final_files);

    // CẬP NHẬT + ĐÁNH DẤU ĐÃ CHỈNH SỬA
    $stmt = $conn->prepare("UPDATE bai_tap 
                           SET tieu_de = ?, noi_dung = ?, file_dinh_kem = ?, han_nop = ?, chinh_sua = 1 
                           WHERE id = ?");
    $stmt->bind_param("ssssi", $tieu_de, $noi_dung, $file_str, $han_nop, $id_update);
    $stmt->execute();
    
    header("Location: phonghoc.php?malop=$ma_lop&tab=bai-tap");
    exit;

} elseif ($action == 'delete_bt') {
    $id_delete = $_GET['id'] ?? 0;
    // Xóa file...
    $stmt = $conn->prepare("SELECT file_dinh_kem FROM bai_tap WHERE id = ?");
    $stmt->bind_param("i", $id_delete);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc()['file_dinh_kem'] ?? '';
    if ($old) {
        foreach(explode(',', $old) as $f) {
            if (file_exists($target_dir . $f)) @unlink($target_dir . $f);
        }
    }

    $stmt = $conn->prepare("DELETE FROM bai_tap WHERE id = ?");
    $stmt->bind_param("i", $id_delete);
    $stmt->execute();
}

header("Location: phonghoc.php?malop=$ma_lop&tab=bai-tap");
exit;
?>