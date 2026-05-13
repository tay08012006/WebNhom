<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ma_lop = $_GET['malop'] ?? '';
$ds_hoc_sinh = [];
if (isset($_SESSION['danh_sach_hoc_sinh']) && is_array($_SESSION['danh_sach_hoc_sinh'])) {
    foreach ($_SESSION['danh_sach_hoc_sinh'] as $hs) {
        if (isset($hs['ma_lop']) && $hs['ma_lop'] === $ma_lop) {
            $ds_hoc_sinh[] = $hs;
        }
    }
}

// 2. HÀM SẮP XẾP HỌC SINH THEO BẢNG CHỮ CÁI (Chuẩn tên tiếng Việt)
function layTenChoSapXep($hoTen) {
    $parts = explode(' ', trim($hoTen));
    return end($parts); // Lấy từ cuối cùng làm Tên chính
}

usort($ds_hoc_sinh, function($a, $b) {
    // Nếu mảng của bạn dùng key khác cho tên, hãy đổi 'ten_hs' thành key tương ứng
    $tenA = layTenChoSapXep($a['ten_hs'] ?? '');
    $tenB = layTenChoSapXep($b['ten_hs'] ?? '');
    
    // Nếu trùng tên thì sắp xếp theo toàn bộ Họ và Tên
    if ($tenA === $tenB) {
        return strcasecmp($a['ten_hs'] ?? '', $b['ten_hs'] ?? '');
    }
    return strcasecmp($tenA, $tenB);
});

$so_luong_hs = count($ds_hoc_sinh);

// Hàm tạo màu ngẫu nhiên cho Avatar dựa trên chữ cái đầu
function getAvatarColor($char) {
    $colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#009688', '#ff9800', '#795548', '#0288d1'];
    $index = ord(strtoupper($char)) % count($colors);
    return $colors[$index];
}
?>

<style>
    .people-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 15px;
        margin-top: 40px;
        margin-bottom: 10px;
        border-bottom: 2px solid #0288d1;
    }
    
    .section-header h2 {
        color: #0288d1;
        font-size: 28px;
        font-weight: 400;
        margin: 0;
    }
    
    .student-count {
        color: #0288d1;
        font-size: 14px;
        font-weight: bold;
    }
    
    .person-row {
        display: flex;
        align-items: center;
        padding: 15px 10px;
        border-bottom: 1px solid #e0e0e0;
        transition: background-color 0.2s;
        border-radius: 8px;
    }
    
    .person-row:hover {
        background-color: #f8f9fa;
    }
    
    .person-row:last-child {
        border-bottom: none;
    }
    
    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 18px;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .person-name {
        font-size: 15px;
        color: #3c4043;
        font-weight: 500;
        flex-grow: 1;
    }

    .empty-students {
        text-align: center;
        padding: 40px;
        color: #5f6368;
        font-size: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 15px;
    }
</style>

<div class="people-container">
    
    <div class="section-header" style="margin-top: 10px;">
        <h2>Giáo viên</h2>
    </div>
    
    <div class="person-row">
        <div class="avatar" style="background-color: #0288d1;">
            G
        </div>
        <div class="person-name">Giáo viên của lớp</div>
        </div>

    <div class="section-header">
        <h2>Học sinh</h2>
        <div class="student-count"><?= $so_luong_hs ?> sinh viên</div>
    </div>
    
    <div class="students-list">
        <?php if ($so_luong_hs > 0): ?>
            <?php foreach ($ds_hoc_sinh as $hs): 
                $ten_hien_thi = htmlspecialchars($hs['ten_hs'] ?? 'Học sinh ẩn danh');
                $chu_cai_dau = mb_substr(layTenChoSapXep($ten_hien_thi), 0, 1, "UTF-8");
                $mau_avatar = getAvatarColor($chu_cai_dau);
            ?>
                <div class="person-row">
                    <div class="avatar" style="background-color: <?= $mau_avatar ?>;">
                        <?= mb_strtoupper($chu_cai_dau, "UTF-8") ?>
                    </div>
                    <div class="person-name"><?= $ten_hien_thi ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-students">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#bdc1c6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <br>Chưa có sinh viên nào tham gia lớp học này.
                <br><small style="color: #80868b; margin-top: 5px; display: block;">Mời sinh viên bằng mã lớp: <b><?= htmlspecialchars($ma_lop) ?></b></small>
            </div>
        <?php endif; ?>
    </div>

</div>