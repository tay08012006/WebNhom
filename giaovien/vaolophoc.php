<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php'; // Kết nối tới database của bạn

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../trangdangnhap.php?error=Bạn cần đăng nhập!");
    exit;
}

$class_info = null;
$students_result = null;
$total_students = 0;
$error_msg = "";
$success_msg = "";

// 2. Xử lý khi Học sinh bấm nút "Vào Lớp Học" (Gửi mã lớp lên)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ma_lop'])) {
    $ma_lop = strtoupper(trim($_POST['ma_lop']));
    
    if (empty($ma_lop)) {
        $error_msg = "Vui lòng nhập mã lớp học!";
    } else {
        // Kiểm tra xem mã lớp có tồn tại trong hệ thống không
        $stmt = $conn->prepare("SELECT id, ten_lop, giaovien_id FROM classes WHERE ma_lop = ?");
        $stmt->bind_param("s", $ma_lop);
        $stmt->execute();
        $class_info = $stmt->get_result()->fetch_assoc();

        if ($class_info) {
            $class_id = $class_info['id'];
            $student_id = $_SESSION['user_id'];

            // Kiểm tra xem học sinh này đã tham gia lớp này từ trước chưa
            $check_stmt = $conn->prepare("SELECT id FROM class_enrollments WHERE user_id = ? AND class_id = ?");
            $check_stmt->bind_param("ii", $student_id, $class_id);
            $check_stmt->execute();
            $is_enrolled = $check_stmt->get_result()->fetch_assoc();

            if (!$is_enrolled) {
                // Nếu chưa tham gia -> Tiến hành lưu thông tin vào SQL (bảng class_enrollments)
                $enroll_stmt = $conn->prepare("INSERT INTO class_enrollments (user_id, class_id) VALUES (?, ?)");
                $enroll_stmt->bind_param("ii", $student_id, $class_id);
                if ($enroll_stmt->execute()) {
                    $success_msg = "Tham gia lớp học thành công!";
                } else {
                    $error_msg = "Có lỗi xảy ra khi lưu vào cơ sở dữ liệu.";
                }
            } else {
                $success_msg = "Bạn đã ở trong lớp học này rồi!";
            }

            // Lấy số lượng và danh sách ID, Tên đầy đủ của các học sinh trong lớp từ SQL
            $sql_students = "SELECT u.id, u.hoten, u.email 
                             FROM class_enrollments ce
                             JOIN users u ON ce.user_id = u.id
                             WHERE ce.class_id = ? AND u.role = 'student'
                             ORDER BY u.hoten ASC";
            $stmt_students = $conn->prepare($sql_students);
            $stmt_students->bind_param("i", $class_id);
            $stmt_students->execute();
            $students_result = $stmt_students->get_result();
            $total_students = $students_result->num_rows; // Lấy tổng số lượng học sinh

        } else {
            $error_msg = "Mã lớp học không chính xác hoặc không tồn tại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tham Gia Lớp Học</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .wrapper { max-width: 700px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #0288d1; text-align: center; margin-bottom: 25px; font-weight: 800; }
        
        /* Form nhập mã */
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px; }
        label { font-weight: 700; color: #555; }
        input[type="text"] { padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; font-weight: 600; text-transform: uppercase; text-align: center; letter-spacing: 2px; }
        input[type="text"]:focus { border-color: #0288d1; outline: none; }
        .btn-submit { background-color: #0288d1; color: white; border: none; padding: 12px; font-size: 16px; font-weight: 700; border-radius: 8px; cursor: pointer; transition: 0.3s; width: 100%; }
        .btn-submit:hover { background-color: #02669c; }
        
        /* Thông báo lỗi / thành công */
        .alert { padding: 12px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; text-align: center; }
        .alert-danger { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

        /* Kết quả hiển thị */
        .class-box { background: #f0f4f8; padding: 15px; border-radius: 8px; margin-top: 25px; border-left: 5px solid #0288d1; }
        .class-box h3 { margin: 0 0 5px 0; color: #0288d1; }
        .count-badge { background: #0288d1; color: white; padding: 3px 8px; border-radius: 20px; font-size: 13px; font-weight: 700; }

        /* Bảng danh sách học sinh */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #0288d1; color: white; font-weight: 700; }
        tr:hover { background-color: #f9f9f9; }
        .no-data { text-align: center; color: #888; font-style: italic; padding: 20px; }
    </style>
</head>
<body>

<div class="wrapper">
    <h2>🚪 THAM GIA LỚP HỌC</h2>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="ma_lop">Nhập mã lớp học (Gồm 6 ký tự):</label>
            <input type="text" id="ma_lop" name="ma_lop" maxlength="6" placeholder="Ví dụ: A1B2C3" value="<?= isset($_POST['ma_lop']) ? htmlspecialchars($_POST['ma_lop']) : '' ?>" required>
        </div>
        <button type="submit" class="btn-submit">Xác Nhận Vào Lớp</button>
    </form>

    <?php if ($class_info): ?>
        <div class="class-box">
            <h3>Lớp: <?= htmlspecialchars($class_info['ten_lop']) ?></h3>
            <p>Mã số lớp: <b><?= htmlspecialchars($ma_lop) ?></b></p>
            <p>Sức chứa hiện tại: <span class="count-badge"><?= $total_students ?> học sinh đã tham gia</span></p>
        </div>

        <h4 style="margin-top: 20px; margin-bottom: 10px; color: #555;">Danh sách thành viên trong lớp:</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">STT</th>
                    <th style="width: 25%;">ID Học Sinh</th>
                    <th style="width: 65%;">Họ Và Tên Đầy Đủ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_students > 0): ?>
                    <?php $stt = 1; while ($row = $students_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $stt++ ?></td>
                            <td><strong>#<?= $row['id'] ?></strong></td> 
                            <td><?= htmlspecialchars($row['hoten']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="no-data">Chưa có học sinh nào khác trong lớp này.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>