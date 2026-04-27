<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy tên GV từ session, nếu không có dùng mặc định
$ten_gv = $_SESSION['ho_ten'] ?? 'Giáo Viên Demo';

// Lấy đường dẫn avatar từ Session (nếu GV đã up ảnh). 
// Nếu chưa có thì dùng ảnh mặc định tạo từ chữ cái đầu (chữ màu trắng, nền xanh).
$gv_avatar = $_SESSION['gv_avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($ten_gv) . '&background=0288d1&color=fff&bold=true'; 
?>

<div style="display: flex; align-items: center; gap: 10px; font-weight: 700;">
    <span>GV: <?= htmlspecialchars($ten_gv) ?></span>
    
    <label style="cursor: pointer; position: relative; display: inline-block;" title="Nhấn để đổi ảnh đại diện">
        <img src="<?= $gv_avatar ?>" alt="Avatar" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #0288d1; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
        
        <form id="form_doi_avatar" action="xuly_avatar.php" method="POST" enctype="multipart/form-data" style="display: none;">
            <input type="file" name="file_avatar" accept="image/*" onchange="document.getElementById('form_doi_avatar').submit();">
        </form>
    </label>
</div>