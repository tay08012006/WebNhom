<?php
session_start();
include '../config.php'; // ← Sửa từ dp.php → config.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_baitap  = intval($_POST['id_baitap']);
    $id_lop     = intval($_POST['id_lop']);
    $id_hocsinh = $_SESSION['user_id'];

    $file_name = null;
    // Cột link_nop không có trong bảng nop_bai → lưu vào file_nop nếu là link
    $link_nop = !empty($_POST['link_baitap']) ? trim($_POST['link_baitap']) : null;

    // Xử lý upload file từ máy tính
    if (isset($_FILES['file_baitap']) && $_FILES['file_baitap']['error'] == 0) {
        $target_dir = "../uploads/";

        // Tạo thư mục nếu chưa có
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $ext       = pathinfo($_FILES['file_baitap']['name'], PATHINFO_EXTENSION);
        $file_name = "BT_" . $id_hocsinh . "_" . time() . "." . $ext;
        move_uploaded_file($_FILES['file_baitap']['tmp_name'], $target_dir . $file_name);
    }

    // Xác định giá trị lưu vào cột file_nop
    // Nếu có file upload thì ưu tiên file; nếu không thì lưu link
    $gia_tri_luu = $file_name ?? $link_nop;

    if ($gia_tri_luu !== null) {
        // Bảng: nop_bai | Cột: bai_tap_id, student_id, file_nop
        $sql = "INSERT INTO nop_bai (bai_tap_id, student_id, file_nop) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $id_baitap, $id_hocsinh, $gia_tri_luu);
        $stmt->execute();
    }

    // Quay lại phòng học sau khi nộp
    header("Location: phonghoc.php?id=" . $id_lop);
    exit();
} else {
    header("Location: index.php");
    exit();
}