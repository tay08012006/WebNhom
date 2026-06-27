<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $class_id = intval($_POST['class_id']); // Ép kiểu an toàn
    $user_id = $_SESSION['user_id'];

    if ($action == 'create') {
        // HÀNH ĐỘNG: TẠO NHÓM MỚI
        $ten_nhom = trim($_POST['ten_nhom']);
        
        // 1. Thêm nhóm vào bảng nhom_hoc
        $sql_tao = "INSERT INTO nhom_hoc (ten_nhom, class_id) VALUES (?, ?)";
        $stmt_tao = $conn->prepare($sql_tao);
        $stmt_tao->bind_param("si", $ten_nhom, $class_id);
        
        if ($stmt_tao->execute()) {
            $new_nhom_id = $stmt_tao->insert_id;
            
            // 2. Cập nhật nhom_id cho học sinh
            $sql_update = "UPDATE class_enrollments SET nhom_id = ? WHERE user_id = ? AND class_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("iii", $new_nhom_id, $user_id, $class_id);
            $stmt_update->execute();
            
            // QUAN TRỌNG: Điều hướng về URL có ?id=...
            echo "<script>alert('Tạo nhóm thành công!'); window.location.href='phonghoc.php?id={$class_id}&tab=nhom';</script>";
        }

    } elseif ($action == 'join') {
        // HÀNH ĐỘNG: XIN VÀO NHÓM
        $nhom_id = intval($_POST['nhom_id']);

        $sql_join = "UPDATE class_enrollments SET nhom_id = ? WHERE user_id = ? AND class_id = ?";
        $stmt_join = $conn->prepare($sql_join);
        $stmt_join->bind_param("iii", $nhom_id, $user_id, $class_id);
        
        if ($stmt_join->execute()) {
            echo "<script>alert('Gia nhập nhóm thành công!'); window.location.href='phonghoc.php?id={$class_id}&tab=nhom';</script>";
        }
    }
}
?>