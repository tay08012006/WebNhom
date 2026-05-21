<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$ma_lop = $_GET['malop'] ?? '';
$ds_hoc_sinh = [];

// Lấy danh sách học sinh từ DB, thêm cả user_id để dùng cho nút xóa
if (isset($conn) && !empty($ma_lop)) {
    $sql_get_hs = "SELECT u.id AS user_id, u.hoten AS ten_hs, u.avatar 
                   FROM class_enrollments ce 
                   JOIN users u ON ce.user_id = u.id
                   JOIN classes c ON ce.class_id = c.id
                   WHERE c.ma_lop = ? AND u.role = 'student'";
    $stmt_get_hs = $conn->prepare($sql_get_hs);
    if ($stmt_get_hs) {
        $stmt_get_hs->bind_param("s", $ma_lop);
        $stmt_get_hs->execute();
        $res_hs = $stmt_get_hs->get_result();
        while ($row_hs = $res_hs->fetch_assoc()) {
            $ds_hoc_sinh[] = [
                'user_id' => $row_hs['user_id'],
                'ten_hs'  => $row_hs['ten_hs'],
                'avatar'  => $row_hs['avatar'],
                'ma_lop'  => $ma_lop
            ];
        }
    }
}

// Dự phòng từ Session nếu không có DB
if (empty($ds_hoc_sinh) && isset($_SESSION['danh_sach_hoc_sinh']) && is_array($_SESSION['danh_sach_hoc_sinh'])) {
    foreach ($_SESSION['danh_sach_hoc_sinh'] as $hs) {
        if (isset($hs['ma_lop']) && $hs['ma_lop'] === $ma_lop) {
            $ds_hoc_sinh[] = $hs;
        }
    }
}

// Sắp xếp theo tên
if (!function_exists('layTenChoSapXep')) {
    function layTenChoSapXep($hoTen) {
        if (empty($hoTen)) return 'A';
        $parts = explode(' ', trim($hoTen));
        return end($parts);
    }
}

usort($ds_hoc_sinh, function($a, $b) {
    $tenA = layTenChoSapXep($a['ten_hs'] ?? '');
    $tenB = layTenChoSapXep($b['ten_hs'] ?? '');
    if ($tenA === $tenB) return strcasecmp($a['ten_hs'] ?? '', $b['ten_hs'] ?? '');
    return strcasecmp($tenA, $tenB);
});
$so_luong_hs = count($ds_hoc_sinh);

if (!function_exists('getAvatarColor')) {
    function getAvatarColor($char) {
        if (empty($char)) return '#0288d1';
        $colors = ['#f44336','#e91e63','#9c27b0','#673ab7','#3f51b5','#009688','#ff9800','#795548','#0288d1'];
        return $colors[ord(strtoupper($char)) % count($colors)];
    }
}
?>

<style>
    .people-container { max-width: 800px; margin: 0 auto; padding: 20px; font-family: 'Nunito', sans-serif; }
    .section-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; margin-top: 40px; margin-bottom: 10px; border-bottom: 2px solid #0288d1; }
    .section-header h2 { color: #0288d1; font-size: 28px; font-weight: 400; margin: 0; }
    .student-count { color: #0288d1; font-size: 14px; font-weight: bold; }

    /* Hàng người dùng */
    .person-row { display: flex; align-items: center; padding: 15px 10px; border-bottom: 1px solid #e0e0e0; transition: background-color 0.2s; border-radius: 8px; position: relative; }
    .person-row:hover { background-color: #f8f9fa; }
    .person-row:last-child { border-bottom: none; }

    /* Avatar */
    .avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px; margin-right: 15px; flex-shrink: 0; overflow: hidden; }
    .avatar img { width: 100%; height: 100%; object-fit: cover; }
    .person-name { font-size: 15px; color: #3c4043; font-weight: 500; flex-grow: 1; }

    /* ===== MENU 3 CHẤM ===== */
    .more-btn-wrap { position: relative; margin-left: auto; flex-shrink: 0; }
    .more-btn {
        background: none; border: none; cursor: pointer;
        width: 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #80868b; transition: background 0.2s;
    }
    .more-btn:hover { background: #e8eaed; color: #3c4043; }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0; top: 38px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        min-width: 180px;
        z-index: 999;
        overflow: hidden;
        animation: fadeInDrop 0.15s ease;
    }
    .dropdown-menu.open { display: block; }

    @keyframes fadeInDrop {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .dropdown-item {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px;
        font-size: 14px; font-weight: 600; font-family: 'Nunito', sans-serif;
        color: #3c4043; text-decoration: none; cursor: pointer;
        transition: background 0.15s;
        border: none; background: none; width: 100%; text-align: left;
    }
    .dropdown-item:hover { background: #f8f9fa; }
    .dropdown-item.danger { color: #d32f2f; }
    .dropdown-item.danger:hover { background: #fff3f3; }
    /* ===== END MENU ===== */

    .empty-students { text-align: center; padding: 40px; color: #5f6368; font-size: 15px; background: #f8f9fa; border-radius: 8px; margin-top: 15px; }
</style>

<div class="people-container">
    <!-- Giáo viên -->
    <div class="section-header" style="margin-top: 10px;">
        <h2>Giáo viên</h2>
    </div>
    <?php
        $ten_gv  = $_SESSION['hoten'] ?? $_SESSION['ho_ten'] ?? $_SESSION['user_name'] ?? 'Giáo viên';
        $gv_avatar = '';
        if (isset($conn) && isset($_SESSION['user_id'])) {
            $s = $conn->prepare("SELECT avatar, hoten FROM users WHERE id = ?");
            $s->bind_param("i", $_SESSION['user_id']);
            $s->execute();
            $r = $s->get_result()->fetch_assoc();
            $gv_avatar = $r['avatar'] ?? '';
            if (!empty($r['hoten'])) $ten_gv = $r['hoten'];
        }
        $gv_chu  = mb_substr(layTenChoSapXep($ten_gv), 0, 1, "UTF-8");
        $gv_mau  = getAvatarColor($gv_chu);
    ?>
    <div class="person-row">
        <?php if (!empty($gv_avatar)): ?>
            <div class="avatar"><img src="<?= htmlspecialchars($gv_avatar) ?>" alt="Avatar GV"></div>
        <?php else: ?>
            <div class="avatar" style="background-color:<?= $gv_mau ?>;"><?= mb_strtoupper($gv_chu, "UTF-8") ?></div>
        <?php endif; ?>
        <div class="person-name"><?= htmlspecialchars($ten_gv) ?></div>
    </div>

    <!-- Học sinh -->
    <div class="section-header">
        <h2>Học sinh</h2>
        <div class="student-count"><?= $so_luong_hs ?> học sinh</div>
    </div>

    <div class="students-list">
        <?php if ($so_luong_hs > 0): ?>
            <?php foreach ($ds_hoc_sinh as $hs):
                $ten_hien_thi = htmlspecialchars($hs['ten_hs'] ?? 'Học sinh ẩn danh');
                $hs_avatar    = $hs['avatar'] ?? '';
                $chu_cai      = mb_substr(layTenChoSapXep($ten_hien_thi), 0, 1, "UTF-8");
                $mau_av       = getAvatarColor($chu_cai);
                $uid          = (int)($hs['user_id'] ?? 0);
                $menu_id      = 'menu_' . $uid;
            ?>
            <div class="person-row">
                <!-- Avatar -->
                <?php if (!empty($hs_avatar)): ?>
                    <div class="avatar"><img src="<?= htmlspecialchars($hs_avatar) ?>" alt="Avatar"></div>
                <?php else: ?>
                    <div class="avatar" style="background-color:<?= $mau_av ?>;"><?= mb_strtoupper($chu_cai, "UTF-8") ?></div>
                <?php endif; ?>

                <div class="person-name"><?= $ten_hien_thi ?></div>

                <!-- Nút 3 chấm -->
                <?php if ($uid > 0): ?>
                <div class="more-btn-wrap">
                    <button class="more-btn" onclick="toggleMenu('<?= $menu_id ?>', event)" title="Tùy chọn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="5"  r="1.8"/>
                            <circle cx="12" cy="12" r="1.8"/>
                            <circle cx="12" cy="19" r="1.8"/>
                        </svg>
                    </button>
                    <div class="dropdown-menu" id="<?= $menu_id ?>">
                        <a class="dropdown-item danger"
                           href="xoahocsinh.php?student_id=<?= $uid ?>&malop=<?= urlencode($ma_lop) ?>"
                           onclick="return confirm('Bạn có chắc muốn xóa <?= addslashes($ten_hien_thi) ?> khỏi lớp không?')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                            </svg>
                            Xóa khỏi lớp học
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-students">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#bdc1c6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:10px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <br>Chưa có học sinh nào tham gia lớp học này.
                <br><small style="color:#80868b;margin-top:5px;display:block;">Mời học sinh bằng mã lớp: <b><?= htmlspecialchars($ma_lop) ?></b></small>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Mở/đóng dropdown menu 3 chấm
function toggleMenu(id, event) {
    event.stopPropagation();
    // Đóng tất cả menu đang mở khác
    document.querySelectorAll('.dropdown-menu.open').forEach(function(m) {
        if (m.id !== id) m.classList.remove('open');
    });
    document.getElementById(id).classList.toggle('open');
}
// Click ra ngoài thì đóng tất cả menu
document.addEventListener('click', function() {
    document.querySelectorAll('.dropdown-menu.open').forEach(function(m) {
        m.classList.remove('open');
    });
});
</script>