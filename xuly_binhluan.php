<?php
session_start();
include '../dp.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_bangtin = intval($_POST['id_bangtin']);
    $id_lop = intval($_POST['id_lop']);
    $id_user = $_SESSION['user_id'];
    $noi_dung = trim($_POST['noi_dung_bl']);

    if (!empty($noi_dung)) {
        $sql = "INSERT INTO binh_luan (id_bangtin, id_user, noi_dung) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $id_bangtin, $id_user, $noi_dung);
        $stmt->execute();
    }

    // Bình luận xong load lại trang phòng học ban đầu
    header("Location: phonghoc.php?id=" . $id_lop);
    exit();
}