<?php
session_start();
// Kết nối với database (đảm bảo đường dẫn tới file config.php là chính xác)
require_once '../config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Nhận dữ liệu từ form gửi sang
    // (Thay đổi 'nop_bai_id', 'diem', 'nhan_xet' cho khớp với name="" trong thẻ HTML của bạn)
    $id_nop_bai = isset($_POST['nop_bai_id']) ? $_POST['nop_bai_id'] : null; 
    $diem = isset($_POST['diem']) ? $_POST['diem'] : null;
    $nhan_xet = isset($_POST['nhan_xet']) ? trim($_POST['nhan_xet']) : '';
    
    // Lấy link trang hiện tại để quay về sau khi chấm xong
    $return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'phonghoc.php';

    if ($id_nop_bai && $diem !== null) {
        // 2. Viết câu lệnh UPDATE vào CSDL (bảng nop_bai)
        $sql = "UPDATE nop_bai SET diem = ?, nhan_xet = ? WHERE id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // "dsi" tương ứng: d = double (điểm), s = string (nhận xét), i = integer (id)
            $stmt->bind_param("dsi", $diem, $nhan_xet, $id_nop_bai);
            
            if ($stmt->execute()) {
                // Chấm điểm thành công, điều hướng quay lại trang cũ
                header("Location: " . $return_url);
                exit();
            } else {
                echo "Lỗi khi cập nhật dữ liệu: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        echo "Thiếu ID bài nộp hoặc điểm số chưa được nhập!";
    }
} else {
    echo "Phương thức yêu cầu không hợp lệ!";
}
?>