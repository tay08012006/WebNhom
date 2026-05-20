<?php
session_start();

// Xóa toàn bộ các biến session
$_SESSION = array();

// Nếu sử dụng cookie để lưu session ID thì xóa nó đi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session hoàn toàn
session_destroy();

header("Location: ../trangdangnhap.php");
exit();
?>