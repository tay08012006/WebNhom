<?php
// Bật thông báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (isset($_GET['malop'])) {
    $ma_lop_can_xoa = $_GET['malop'];
    
    foreach ($_SESSION['classes'] as $key => $class) {
        if ($class['ma_lop'] === $ma_lop_can_xoa) {
            unset($_SESSION['classes'][$key]); 
            break; 
        }
    }
}

header("Location: index.php");
exit;
?>