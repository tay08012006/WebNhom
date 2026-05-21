<?php
session_start();
include '../config.php'; // ← Sửa từ dp.php → config.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_bangtin = intval($_POST['id_bangtin']);
    $id_lop     = intval($_POST['id_lop']);
    $id_user    = $_SESSION['user_id'];
    $noi_dung   = trim($_POST['noi_dung_bl']);

    if (!empty($noi_dung)) {
        /*
         * Bảng binh_luan chưa có trong file SQL.
         * Chạy câu lệnh này trong phpMyAdmin trước khi dùng chức năng bình luận:
         *
         * CREATE TABLE `binh_luan` (
         *   `id` int(11) NOT NULL AUTO_INCREMENT,
         *   `bang_tin_id` int(11) NOT NULL,
         *   `user_id` int(11) NOT NULL,
         *   `noi_dung` text NOT NULL,
         *   `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
         *   PRIMARY KEY (`id`),
         *   KEY `bang_tin_id` (`bang_tin_id`),
         *   KEY `user_id` (`user_id`)
         * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
         */
        $sql  = "INSERT INTO binh_luan (bang_tin_id, user_id, noi_dung) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iis", $id_bangtin, $id_user, $noi_dung);
            $stmt->execute();
        }
    }

    // Quay lại phòng học sau khi bình luận
    header("Location: phonghoc.php?id=" . $id_lop);
    exit();
} else {
    header("Location: index.php");
    exit();
}