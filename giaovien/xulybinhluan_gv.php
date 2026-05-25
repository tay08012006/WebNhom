<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php';

// Chỉ cho phép giáo viên đã đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bangtin = intval($_POST['id_bangtin'] ?? 0);
    $ma_lop     = trim($_POST['ma_lop'] ?? '');
    $noi_dung   = trim($_POST['noi_dung_bl'] ?? '');
    $id_user    = $_SESSION['user_id'];

    if (!empty($noi_dung) && $id_bangtin > 0) {
        $stmt = $conn->prepare("INSERT INTO binh_luan (id_bangtin, id_user, noi_dung) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_bangtin, $id_user, $noi_dung);
        $stmt->execute();
    }

    header("Location: phonghoc.php?malop=" . urlencode($ma_lop) . "&tab=bang-tin#post-" . $id_bangtin);
    exit;
}

header("Location: ../trangdangnhap.php");
exit;
?>
