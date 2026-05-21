<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Kết nối DB nếu chưa có sẵn
if (!isset($conn)) {
    require_once '../config.php';
}

$user_id = $_SESSION['user_id'] ?? 0;

// Tương thích cả 2 cách đặt tên session key (ho_ten hoặc hoten)
$ten_gv = $_SESSION['ho_ten'] ?? $_SESSION['hoten'] ?? $_SESSION['user_name'] ?? 'Giáo Viên';

// Luôn lấy avatar MỚI NHẤT từ DATABASE
$gv_avatar = '';
if ($user_id) {
    $stmt_av = $conn->prepare("SELECT hoten, avatar FROM users WHERE id = ?");
    $stmt_av->bind_param("i", $user_id);
    $stmt_av->execute();
    $row_av = $stmt_av->get_result()->fetch_assoc();
    
    // Nếu tên trong DB tốt hơn session thì dùng luôn
    if (!empty($row_av['hoten'])) {
        $ten_gv = $row_av['hoten'];
        // Đồng bộ lại session để nhất quán
        $_SESSION['ho_ten'] = $ten_gv;
        $_SESSION['hoten']  = $ten_gv;
    }
    $gv_avatar = $row_av['avatar'] ?? '';
}
// Nếu chưa có avatar → dùng avatar chữ cái đầu
if (empty($gv_avatar)) {
    $gv_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($ten_gv) . '&background=0288d1&color=fff&bold=true';
}
?>
<style>
    .avatar-wrapper { position: relative; display: inline-block; cursor: pointer; }
    .avatar-img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #0288d1; box-shadow: 0 2px 6px rgba(0,0,0,0.1); transition: all 0.3s ease; vertical-align: middle; }
    .avatar-wrapper:hover .avatar-img { filter: brightness(0.85); transform: scale(1.05); }
    .camera-badge { position: absolute; bottom: -2px; right: -2px; background: #fff; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); color: #0288d1; }
    .modal-overlay-av { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.45); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
    .modal-box-av { background: #fff; padding: 35px 25px; border-radius: 20px; width: 340px; text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,0.15); position: relative; animation: avSlideUp 0.3s ease-out; }
    .av-close-btn { position: absolute; top: 12px; right: 18px; font-size: 26px; color: #90a4ae; cursor: pointer; font-family: monospace; line-height: 1; }
    .av-close-btn:hover { color: #d32f2f; }
    .av-preview { width: 160px; height: 160px; border-radius: 50%; object-fit: cover; border: 4px solid #e1f5fe; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); display: block; margin-left: auto; margin-right: auto; }
    .av-btn-choose { background: #f4f7f9; color: #455a64; border: 2px dashed #cfd8dc; padding: 12px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s; margin-bottom: 15px; font-size: 14px; font-family: inherit; }
    .av-btn-choose:hover { border-color: #0288d1; color: #0288d1; background: #f0f8ff; }
    .av-btn-save { background: linear-gradient(135deg, #0288d1, #0277bd); color: white; border: none; padding: 14px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 800; font-size: 15px; display: none; transition: 0.3s; box-shadow: 0 4px 12px rgba(2,136,209,0.3); font-family: inherit; }
    .av-btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(2,136,209,0.4); }
    @keyframes avSlideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div style="display:flex;align-items:center;gap:10px;">
    <span style="font-weight:700;color:#37474f;font-size:15px;"><?php echo htmlspecialchars($ten_gv); ?></span>

    <div class="avatar-wrapper" onclick="moPopupAvatar()" title="Đổi ảnh đại diện">
        <img src="<?php echo htmlspecialchars($gv_avatar); ?>" alt="Avatar" class="avatar-img"
             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($ten_gv); ?>&background=0288d1&color=fff&bold=true'">
        <div class="camera-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                <circle cx="12" cy="13" r="4"></circle>
            </svg>
        </div>
    </div>
</div>
<div id="modalAvatar" class="modal-overlay-av" onclick="if(event.target===this)dongPopupAvatar()">
    <div class="modal-box-av">
        <span class="av-close-btn" onclick="dongPopupAvatar()">&times;</span>
        <h3 style="margin-top:0;color:#263238;font-size:20px;font-weight:800;margin-bottom:25px;">Đổi Ảnh Đại Diện</h3>

        <img id="avXemTruoc"
             src="<?php echo htmlspecialchars($gv_avatar); ?>"
             class="av-preview"
             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($ten_gv); ?>&background=0288d1&color=fff&bold=true'">

        <form action="xuly_avatar.php" method="POST" enctype="multipart/form-data">
            <input type="file" id="avInputFile" name="file_avatar"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   style="display:none;"
                   onchange="avXemTruoc(event)">

            <button type="button" class="av-btn-choose" onclick="document.getElementById('avInputFile').click()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                Chọn ảnh từ thiết bị
            </button>

            <button type="submit" id="avBtnLuu" class="av-btn-save">Cập Nhật Ảnh Mới</button>
        </form>
    </div>
</div>
<script>
function moPopupAvatar()  { document.getElementById('modalAvatar').style.display = 'flex'; }
function dongPopupAvatar() {
    document.getElementById('modalAvatar').style.display = 'none';
    document.getElementById('avInputFile').value = '';
    document.getElementById('avXemTruoc').src = <?php echo json_encode($gv_avatar); ?>;
    document.getElementById('avBtnLuu').style.display = 'none';
}
function avXemTruoc(e) {
    var file = e.target.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('avXemTruoc').src = ev.target.result;
        document.getElementById('avBtnLuu').style.display = 'block';
    };
    reader.readAsDataURL(file);
}
</script>