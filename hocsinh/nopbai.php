<?php
session_start();
include '../dp.php'; // Kết nối CSDL

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../trangdangnhap.php");
    exit();
}

$id_hocsinh = $_SESSION['user_id'];
$id_baitap = isset($_GET['id_baitap']) ? intval($_GET['id_baitap']) : 0;

// 1. LẤY THÔNG TIN BÀI TẬP VÀ KIỂM TRA HẠN NỘP
$sql_bt = "SELECT b.*, l.ten_lop 
        FROM baitap b 
        JOIN lop_hoc l ON b.id_lop = l.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql_bt);
$stmt->bind_param("i", $id_baitap);
$stmt->execute();
$baitap = $stmt->get_result()->fetch_assoc();

if (!$baitap) {
    die("Lỗi: Bài tập không tồn tại!");
}

// Kiểm tra xem đã nộp trước đó chưa
$sql_check = "SELECT id FROM nop_bai_tap WHERE id_hocsinh = ? AND id_baitap = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_hocsinh, $id_baitap);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    die("Em đã nộp bài tập này rồi!");
}

// 2. XỬ LÝ KHI HỌC SINH NHẤN NÚT NỘP BÀI
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['file_bailam']) && $_FILES['file_bailam']['error'] == 0) {
        
        // Tạo thư mục uploads ngoài gốc nếu chưa có
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Đổi tên file để tránh trùng lặp: idHocSinh_idBaiTap_ThoiGian_TenFile
        $file_name = time() . "_" . basename($_FILES["file_bailam"]["name"]);
        $target_file = $target_dir . $file_name;

        // Tiến hành di chuyển file vào thư mục uploads
        if (move_uploaded_location($_FILES["file_bailam"]["tmp_name"], $target_file)) {
            // Lưu đường dẫn file và thời gian vào database
            $sql_insert = "INSERT INTO nop_bai_tap (id_hocsinh, id_baitap, file_path, ngay_nop) VALUES (?, ?, ?, NOW())";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("iis", $id_hocsinh, $id_baitap, $file_name);
            
            if ($stmt_insert->execute()) {
                // Thành công quay về phòng học
                header("Location: phonghoc.php?id=" . $baitap['id_lop']);
                exit();
            } else {
                $error = "Lỗi lưu dữ liệu vào cơ sở dữ liệu.";
            }
        } else {
            $error = "Có lỗi xảy ra khi tải file lên máy chủ.";
        }
    } else {
        $error = "Vui lòng chọn file bài làm của em!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nộp bài: <?php echo htmlspecialchars($baitap['ten_bai_tap']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .upload-box { background: white; width: 100%; max-width: 550px; padding: 40px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #eceff1; border-top: 4px solid #0288d1; }
        h1 { color: #263238; font-size: 22px; font-weight: 800; margin-bottom: 10px; }
        .meta-info { color: #78909c; font-size: 14px; font-weight: 600; margin-bottom: 25px; line-height: 1.6; }
        .file-input-wrapper { margin-bottom: 25px; }
        .file-label { display: block; font-weight: 700; color: #263238; margin-bottom: 10px; }
        .file-select { width: 100%; padding: 15px; border: 2px dashed #cfd8dc; background: #fafbfc; border-radius: 8px; cursor: pointer; text-align: center; }
        .btn-submit { width: 100%; padding: 14px; background: #0288d1; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; }
        .btn-submit:hover { background: #01579b; }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #546e7a; text-decoration: none; font-size: 14px; font-weight: 600; }
        .alert-error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 700; font-size: 14px; }
    </style>
</head>
<body>

    <div class="upload-box">
        <h1>Nộp bài tập trực tuyến</h1>
        <div class="meta-info">
            Lớp học: <strong><?php echo htmlspecialchars($baitap['ten_lop']); ?></strong><br>
            Bài tập: <strong><?php echo htmlspecialchars($baitap['ten_bai_tap']); ?></strong><br>
            Hạn chót: <span style="color:#e53935;"><?php echo date("H:i d/m/Y", strtotime($baitap['han_nop'])); ?></span>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="file-input-wrapper">
                <label class="file-label">Tải tài liệu bài làm của em lên đây:</label>
                <input type="file" name="file_bailam" class="file-select" required>
            </div>
            <button type="submit" class="btn-submit">Xác nhận nộp bài tập ➔</button>
        </form>
        
        <a href="phonghoc.php?id=<?php echo $baitap['id_lop']; ?>" class="btn-cancel">Hủy bỏ và quay lại</a>
    </div>

</body>
</html>