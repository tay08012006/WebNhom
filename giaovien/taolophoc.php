<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_mon = $_POST['ten_mon'];
    $hoc_ky = $_POST['hoc_ky'];
    $ma_lop = strtoupper(substr(md5(uniqid()), 0, 6));

    $_SESSION['classes'][] = [
        'ma_lop' => $ma_lop,
        'ten_mon' => $ten_mon,
        'hoc_ky' => $hoc_ky
    ];
}

header("Location: index.php");
exit;
?>