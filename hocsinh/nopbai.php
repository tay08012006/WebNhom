<?php
session_start();
include '../config.php';

// Kiểm tra quyền học sinh
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    die("Lỗi quyền truy cập.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Phương thức truy cập không hợp lệ.");
}

$id_baitap  = intval($_POST['id_baitap']);
$id_lop     = intval($_POST['id_lop']);
$id_hocsinh = $_SESSION['user_id'];
$link_nop   = isset($_POST['link_baitap']) ? trim($_POST['link_baitap']) : '';

if ($id_baitap <= 0) die("Mã bài tập không hợp lệ.");

// 1. Chống nộp trùng
$stmt_check = $conn->prepare("SELECT id FROM nop_bai WHERE bai_tap_id = ? AND student_id = ?");
$stmt_check->bind_param("ii", $id_baitap, $id_hocsinh);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo "<script>alert('Bạn đã nộp bài này rồi!'); window.location.href='phonghoc.php?id=$id_lop&tab=bai-tap';</script>";
    exit();
}

// 2. Xử lý file upload
$ten_file = '';
if (isset($_FILES['file_baitap']) && $_FILES['file_baitap']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/baitap/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $ext        = pathinfo($_FILES['file_baitap']['name'], PATHINFO_EXTENSION);
    $ten_file   = 'BAITAP_' . $id_hocsinh . '_' . time() . '_' . uniqid() . '.' . $ext;
    $duong_dan  = $upload_dir . $ten_file;

    // Kiểm tra loại file cho phép
    $allowed_ext = [
        // Tài liệu
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'odt',
        // Hình ảnh
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'heic',
        // Nén
        'zip', 'rar', '7z',
        // Code / Database
        'sql', 'php', 'html', 'css', 'js', 'py', 'java', 'cpp', 'c',
        // Video/Audio (bài thuyết trình, thực hành)
        'mp4', 'mov', 'avi', 'mkv', 'mp3',
    ];
    if (!in_array(strtolower($ext), $allowed_ext)) {
        echo '<script>alert("Loại file .' . $ext . ' không được hỗ trợ! Chỉ chấp nhận: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP, RAR, MP4..."); history.back();</script>';
        exit();
    }

    // Giới hạn 50MB
    if ($_FILES['file_baitap']['size'] > 50 * 1024 * 1024) {
        echo "<script>alert('File quá lớn! Tối đa 50MB.'); history.back();</script>";
        exit();
    }

    if (!move_uploaded_file($_FILES['file_baitap']['tmp_name'], $duong_dan)) {
        echo "<script>alert('Lỗi khi tải file lên. Vui lòng thử lại.'); history.back();</script>";
        exit();
    }
} elseif (empty($link_nop)) {
    echo "<script>alert('Vui lòng chọn file hoặc nhập link tài liệu để nộp bài!'); history.back();</script>";
    exit();
}

// 3. Lưu vào CSDL
$stmt_ins = $conn->prepare("INSERT INTO nop_bai (bai_tap_id, student_id, file_nop, link_nop, ngay_nop) VALUES (?, ?, ?, ?, NOW())");
$stmt_ins->bind_param("iiss", $id_baitap, $id_hocsinh, $ten_file, $link_nop);

if ($stmt_ins->execute()) {
    echo "<script>alert('Nộp bài thành công! Vui lòng chờ giáo viên chấm điểm.'); window.location.href='phonghoc.php?id=$id_lop&tab=bai-tap';</script>";
} else {
    echo "<script>alert('Lỗi hệ thống: " . $conn->error . "'); history.back();</script>";
}
?>