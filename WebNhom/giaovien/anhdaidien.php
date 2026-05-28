<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'GV_SESSION');
session_start();
}
if (!isset($conn)) {
    require_once '../config.php';
}

$user_id = $_SESSION['user_id'] ?? 0;
$ten_gv  = $_SESSION['ho_ten'] ?? $_SESSION['hoten'] ?? $_SESSION['user_name'] ?? 'Giáo Viên';

$gv_avatar = '';
if ($user_id) {
    $stmt_av = $conn->prepare("SELECT hoten, avatar FROM users WHERE id = ?");
    $stmt_av->bind_param("i", $user_id);
    $stmt_av->execute();
    $row_av = $stmt_av->get_result()->fetch_assoc();
    if (!empty($row_av['hoten'])) {
        $ten_gv = $row_av['hoten'];
        $_SESSION['ho_ten'] = $ten_gv;
        $_SESSION['hoten']  = $ten_gv;
    }
    $gv_avatar = $row_av['avatar'] ?? '';
}

// Nếu avatar là tên file → thêm đường dẫn uploads/
if (!empty($gv_avatar) && !str_starts_with($gv_avatar, 'http')) {
    $gv_avatar_src = '../uploads/' . $gv_avatar;
} elseif (!empty($gv_avatar)) {
    $gv_avatar_src = $gv_avatar;
} else {
    $gv_avatar_src = 'https://ui-avatars.com/api/?name=' . urlencode($ten_gv) . '&background=0288d1&color=fff&bold=true';
}

$fallback_av = 'https://ui-avatars.com/api/?name=' . urlencode($ten_gv) . '&background=0288d1&color=fff&bold=true';

// Ghi đè $gv_avatar thành đường dẫn đầy đủ để các file khác (index.php, phonghoc.php...) dùng luôn
$gv_avatar = $gv_avatar_src;

// Xác định URL đăng xuất đúng dù được include từ đâu
$logout_url = (basename($_SERVER['PHP_SELF']) === 'phonghoc.php')
    ? 'index.php?action=logout'
    : '?action=logout';
?>
<style>
/* ===== AVATAR DROPDOWN ===== */
.av-wrap { position: relative; display: inline-flex; align-items: center; gap: 10px; }
.av-name  { font-weight: 700; color: #37474f; font-size: 15px; cursor: pointer; user-select: none; }
.av-trigger { position: relative; cursor: pointer; display: inline-block; }
.av-img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover;
          border: 2px solid #0288d1; box-shadow: 0 2px 6px rgba(0,0,0,0.12);
          transition: all .25s; vertical-align: middle; display: block; }
.av-trigger:hover .av-img { filter: brightness(.85); transform: scale(1.05); }
.av-badge { position: absolute; bottom: -2px; right: -2px; background: #fff; border-radius: 50%;
            width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,.2); color: #0288d1; pointer-events: none; }

/* Dropdown menu */
.av-menu { display: none; position: absolute; right: 0; top: calc(100% + 10px);
           background: #fff; border-radius: 14px; min-width: 200px; z-index: 9999;
           box-shadow: 0 8px 28px rgba(0,0,0,.14); overflow: hidden;
           animation: avFadeIn .18s ease; }
.av-menu.open { display: block; }
@keyframes avFadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

.av-menu-header { padding: 14px 16px 10px; border-bottom: 1px solid #f0f4f8; }
.av-menu-header strong { display: block; font-size: 14px; color: #263238; font-weight: 800; }
.av-menu-header span   { font-size: 12px; color: #90a4ae; }

.av-menu-item { display: flex; align-items: center; gap: 10px; padding: 11px 16px;
                font-size: 14px; font-weight: 700; color: #455a64; cursor: pointer;
                text-decoration: none; transition: background .15s; font-family: inherit;
                border: none; background: none; width: 100%; text-align: left; }
.av-menu-item:hover { background: #f4f7f9; color: #0288d1; }
.av-menu-item.danger { color: #d32f2f; }
.av-menu-item.danger:hover { background: #fff3f3; color: #c62828; }
.av-menu-divider { height: 1px; background: #f0f4f8; margin: 4px 0; }

/* ===== MODAL ĐỔI ẢNH ===== */
.modal-overlay-av { display: none; position: fixed; z-index: 10999; left: 0; top: 0;
                    width: 100%; height: 100%; background: rgba(0,0,0,.45);
                    backdrop-filter: blur(4px); align-items: center; justify-content: center; }
.modal-box-av { background: #fff; padding: 35px 25px; border-radius: 20px; width: 340px;
                text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,.15);
                position: relative; animation: avSlideUp .3s ease-out; }
@keyframes avSlideUp { from { transform:translateY(20px);opacity:0; } to { transform:translateY(0);opacity:1; } }
.av-close-btn { position: absolute; top: 12px; right: 18px; font-size: 28px; color: #90a4ae;
                cursor: pointer; font-family: monospace; line-height: 1; background: none;
                border: none; transition: color .2s; }
.av-close-btn:hover { color: #d32f2f; }
.av-preview { width: 160px; height: 160px; border-radius: 50%; object-fit: cover;
              border: 4px solid #e1f5fe; margin: 0 auto 25px; display: block;
              box-shadow: 0 8px 20px rgba(0,0,0,.08); }
.av-btn-choose { background: #f4f7f9; color: #455a64; border: 2px dashed #cfd8dc;
                 padding: 12px; border-radius: 12px; cursor: pointer; width: 100%;
                 font-weight: 700; display: flex; align-items: center; justify-content: center;
                 gap: 8px; transition: all .3s; margin-bottom: 15px;
                 font-size: 14px; font-family: inherit; }
.av-btn-choose:hover { border-color: #0288d1; color: #0288d1; background: #f0f8ff; }
.av-btn-save { background: linear-gradient(135deg,#0288d1,#0277bd); color: white;
               border: none; padding: 14px; border-radius: 12px; cursor: pointer;
               width: 100%; font-weight: 800; font-size: 15px; display: none;
               transition: .3s; box-shadow: 0 4px 12px rgba(2,136,209,.3); font-family: inherit; }
.av-btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(2,136,209,.4); }
</style>

<div class="av-wrap">
    <span class="av-name" onclick="toggleAvatarMenu(event)"><?php echo htmlspecialchars($ten_gv); ?></span>

    <div class="av-trigger" onclick="toggleAvatarMenu(event)">
        <img id="avImgMain"
             src="<?php echo htmlspecialchars($gv_avatar_src); ?>"
             alt="Avatar" class="av-img"
             onerror="this.src='<?php echo $fallback_av; ?>'">
        <div class="av-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
        </div>

        <!-- Dropdown menu -->
        <div class="av-menu" id="avatarMenu">
            <div class="av-menu-header">
                <strong><?php echo htmlspecialchars($ten_gv); ?></strong>
                <span>Tài khoản của bạn</span>
            </div>
            <button class="av-menu-item" onclick="moPopupAvatar(); closeAvatarMenu();">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                Đổi ảnh đại diện
            </button>
            <div class="av-menu-divider"></div>
            <a class="av-menu-item danger"
               href="<?php echo $logout_url; ?>"
               onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Đăng xuất
            </a>
        </div>
    </div>
</div>

<!-- Modal đổi ảnh -->
<div id="modalAvatar" class="modal-overlay-av" onclick="if(event.target===this)dongPopupAvatar()">
    <div class="modal-box-av">
        <button class="av-close-btn" onclick="dongPopupAvatar()">&times;</button>
        <h3 style="margin-top:0;color:#263238;font-size:20px;font-weight:800;margin-bottom:25px;">Đổi Ảnh Đại Diện</h3>

        <img id="avXemTruoc"
             src="<?php echo htmlspecialchars($gv_avatar_src); ?>"
             class="av-preview"
             onerror="this.src='<?php echo $fallback_av; ?>'">

        <form id="avForm" action="xuly_avatar.php" method="POST" enctype="multipart/form-data">
            <input type="file" id="avInputFile" name="file_avatar"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   style="display:none;" onchange="avXemTruoc(event)">

            <button type="button" class="av-btn-choose" onclick="document.getElementById('avInputFile').click()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                Chọn ảnh từ thiết bị
            </button>

            <button type="submit" id="avBtnLuu" class="av-btn-save">Cập Nhật Ảnh Mới</button>
        </form>
    </div>
</div>

<script>
// ===== Dropdown avatar =====
function toggleAvatarMenu(e) {
    e && e.stopPropagation();
    document.getElementById('avatarMenu').classList.toggle('open');
}
function closeAvatarMenu() {
    document.getElementById('avatarMenu').classList.remove('open');
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('avatarMenu');
    if (menu && !menu.closest('.av-trigger').contains(e.target)) {
        menu.classList.remove('open');
    }
});

// ===== Modal đổi ảnh =====
var _avOrigSrc = <?php echo json_encode($gv_avatar_src); ?>;

function moPopupAvatar()  {
    document.getElementById('modalAvatar').style.display = 'flex';
}
function dongPopupAvatar() {
    document.getElementById('modalAvatar').style.display = 'none';
    document.getElementById('avInputFile').value = '';
    document.getElementById('avXemTruoc').src = _avOrigSrc;
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