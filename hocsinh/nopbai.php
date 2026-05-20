<?php
session_start();
include '../dp.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_baitap = intval($_POST['id_baitap']);
    $id_lop = intval($_POST['id_lop']);
    $id_hocsinh = $_SESSION['user_id'];
    
    $file_name = null;
    $link_nop = !empty($_POST['link_baitap']) ? trim($_POST['link_baitap']) : null;

    // Xử lý upload file từ máy tính nếu có
    if (isset($_FILES['file_baitap']) && $_FILES['file_baitap']['error'] == 0) {
        $target_dir = "../uploads/";
        
        // Đổi tên file ngẫu nhiên để tránh trùng lặp tệp tin hệ thống
        $ext = pathinfo($_FILES['file_baitap']['name'], PATHINFO_EXTENSION);
        $file_name = "BT_" . $id_hocsinh . "_" . time() . "." . $ext;
        $target_file = $target_dir . $file_name;
        
        move_uploaded_file($_FILES['file_baitap']['tmp_name'], $target_file);
    }

    if ($file_name !== null || $link_nop !== null) {
        // Lưu thông tin nộp bài vào bảng dữ liệu nop_bai_tap
        $sql = "INSERT INTO nop_bai_tap (id_baitap, id_hocsinh, file_nop, link_nop) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $id_baitap, $id_hocsinh, $file_name, $link_nop);
        $stmt->execute();
    }
    
    // Nộp xong quay trở lại giao diện phòng học ngay lập tức
    header("Location: phonghoc.php?id=" . $id_lop);
    exit();
}