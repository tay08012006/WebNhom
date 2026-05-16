<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php'; 

$user_id = $_SESSION['user_id'] ?? 0;
$ten_gv = $_SESSION['ho_ten'] ?? 'Giáo Viên';
$gv_avatar = '';

// Ưu tiên lấy avatar mới nhất từ Database
if ($user_id) {
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $gv_avatar = $row['avatar'];
    }
}

// Nếu chưa có avatar trong DB thì lấy chữ cái đầu làm avatar mặc định
if (empty($gv_avatar)) {
    $gv_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($ten_gv) . '&background=0288d1&color=fff&bold=true'; 
}
?>

<style>
    /* CSS Làm đẹp giao diện Avatar */
    .avatar-wrapper { position: relative; display: inline-block; cursor: pointer; }
    .avatar-img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #0288d1; box-shadow: 0 2px 6px rgba(0,0,0,0.1); transition: all 0.3s ease; }
    .avatar-wrapper:hover .avatar-img { filter: brightness(0.85); transform: scale(1.05); }
    
    /* Icon camera nhỏ góc phải */
    .camera-badge { position: absolute; bottom: -2px; right: -2px; background: #fff; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); color: #0288d1; }
    
    /* Giao diện Popup */
    .modal-overlay { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center; animation: fadeIn 0.2s ease-out; }
    .modal-box-avatar { background: #fff; padding: 35px 25px; border-radius: 20px; width: 340px; text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,0.15); position: relative; animation: slideUp 0.3s ease-out; }
    
    .close-btn { position: absolute; top: 12px; right: 18px; font-size: 26px; color: #90a4ae; cursor: pointer; transition: 0.2s; font-family: monospace; }
    .close-btn:hover { color: #d32f2f; transform: scale(1.1); }
    
    .preview-img { width: 160px; height: 160px; border-radius: 50%; object-fit: cover; border: 4px solid #e1f5fe; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); transition: 0.3s; }
    
    .btn-choose-file { background: #f4f7f9; color: #455a64; border: 2px dashed #cfd8dc; padding: 12px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s; margin-bottom: 15px; font-size: 14px; }
    .btn-choose-file:hover { background: #f0f4f8; border-color: #0288d1; color: #0288d1; }
    
    .btn-save-avatar { background: linear-gradient(135deg, #0288d1, #0277bd); color: white; border: none; padding: 14px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 800; font-size: 15px; display: none; transition: 0.3s; box-shadow: 0 4px 12px rgba(2, 136, 209, 0.3); }
    .btn-save-avatar:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(2, 136, 209, 0.4); }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #37474f;">
    <span><?= htmlspecialchars($ten_gv) ?></span>
    
    <div class="avatar-wrapper" onclick="moPopupDoiAnh()" title="Đổi ảnh đại diện">
        <img src="<?= $gv_avatar ?>" alt="Avatar" class="avatar-img">
        <div class="camera-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
        </div>
    </div>
</div>

<div id="modalDoiAnh" class="modal-overlay">
    <div class="modal-box-avatar">
        
        <span class="close-btn" onclick="dongPopupDoiAnh()">&times;</span>
        
        <h3 style="margin-top: 0; color: #263238; font-size: 20px; font-weight: 800; margin-bottom: 25px;">Đổi Ảnh Đại Diện</h3>

        <img id="anhXemTruoc" src="<?= $gv_avatar ?>" class="preview-img">

        <form action="xuly_avatar.php" method="POST" enctype="multipart/form-data">
            
            <input type="file" id="input_file_modal" name="file_avatar" accept="image/jpeg, image/png, image/gif, image/webp" style="display: none;" onchange="hienThiAnhXemTruoc(event)">

            <button type="button" class="btn-choose-file" onclick="document.getElementById('input_file_modal').click()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                Chọn ảnh từ thiết bị
            </button>

            <button type="submit" id="btn_luu_anh" class="btn-save-avatar">
                Cập Nhật Ảnh Mới
            </button>
            
        </form>
    </div>
</div>

<script>
    function moPopupDoiAnh() {
        document.getElementById('modalDoiAnh').style.display = 'flex';
    }

    function dongPopupDoiAnh() {
        document.getElementById('modalDoiAnh').style.display = 'none';
        document.getElementById('input_file_modal').value = ""; 
        document.getElementById('anhXemTruoc').src = "<?= $gv_avatar ?>"; 
        document.getElementById('btn_luu_anh').style.display = 'none'; 
    }

    function hienThiAnhXemTruoc(event) {
        var file = event.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('anhXemTruoc').src = e.target.result;
                document.getElementById('btn_luu_anh').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    }
</script>