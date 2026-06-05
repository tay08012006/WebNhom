<?php

// MỤC 1: KHỞI TẠO SESSION VÀ CẤU HÌNH HEADER TRẢ VỀ DỮ LIỆU JSON
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../../config.php';
// Đặt Header để trình duyệt hiểu máy chủ sẽ trả về dữ liệu định dạng JSON (đáp ứng yêu cầu từ fetch/AJAX)
header('Content-Type: application/json');

// MỤC 2: XÁC THỰC QUYỀN TRUY CẬP CỦA GIÁO VIÊN (BẢO MẬT PHIÊN ĐĂNG NHẬP)
// Ngăn chặn truy cập trái phép nếu chưa đăng nhập hoặc không phải là giáo viên
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.']);
    exit;
}

// MỤC 3: KIỂM TRA PHƯƠNG THỨC GỬI DỮ LIỆU (CHỈ CHẤP NHẬN POST)
// Đảm bảo dữ liệu nhận xét chỉ được gửi qua phương thức POST để bảo mật và toàn vẹn dữ liệu
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit;
}

// MỤC 4: TIẾP NHẬN VÀ LÀM SẠCH DỮ LIỆU ĐẦU VÀO TỪ CLIENT
$result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;
$nhan_xet  = isset($_POST['nhan_xet'])  ? trim($_POST['nhan_xet'])   : '';

// Kiểm tra tính hợp lệ của ID bài làm (phải là số nguyên dương lớn hơn 0)
if ($result_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID kết quả không hợp lệ.']);
    exit;
}

// MỤC 5: KIỂM TRA CHÉO BẢO MẬT (NGĂN CHẶN SỬA ĐIỂM/NHẬN XÉT CỦA LỚP KHÁC)
// Truy vấn xem kết quả bài thi này có thực sự thuộc về lớp do giáo viên hiện tại quản lý hay không
$stmt_check = $conn->prepare(
    "SELECT qr.id FROM quiz_results qr
     JOIN quizzes q ON q.id = qr.quiz_id
     JOIN classes c ON c.id = q.class_id
     WHERE qr.id = ? AND c.giaovien_id = ?"
);
$stmt_check->bind_param("ii", $result_id, $_SESSION['user_id']);
$stmt_check->execute();

// Nếu không tìm thấy kết quả khớp, lập tức chặn quyền chỉnh sửa và báo lỗi
if ($stmt_check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền chỉnh sửa nhận xét này.']);
    exit;
}

// MỤC 6: THỰC THI CẬP NHẬT NHẬN XÉT VÀO CƠ SỞ DỮ LIỆU VÀ TRẢ VỀ KẾT QUẢ
$stmt = $conn->prepare("UPDATE quiz_results SET nhan_xet_gv = ? WHERE id = ?");
$stmt->bind_param("si", $nhan_xet, $result_id);

// Trả về phản hồi cho Javascript (AJAX) biết quá trình lưu thành công hay thất bại để cập nhật giao diện
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Lưu nhận xét thành công.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
}
// Đóng kết nối statement để giải phóng tài nguyên hệ thống
$stmt->close();
?>