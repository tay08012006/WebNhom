<?php
// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'GV_SESSION');
    session_start();
}

// Kết nối DATABASE nếu chưa có
if (!isset($conn)) {
    require_once '../config.php';
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_id = $_SESSION['user_id'] ?? 0;

// XỬ LÝ LỖI AVATAR (Array to String)
$avatar_name = 'Giáo Viên';
if (isset($_SESSION['ho_ten'])) {
    if (is_string($_SESSION['ho_ten'])) $avatar_name = $_SESSION['ho_ten'];
    elseif (is_array($_SESSION['ho_ten']) && isset($_SESSION['ho_ten']['ho_ten'])) $avatar_name = $_SESSION['ho_ten']['ho_ten'];
} elseif (isset($_SESSION['hoten'])) {
    if (is_string($_SESSION['hoten'])) $avatar_name = $_SESSION['hoten'];
} elseif (isset($_SESSION['user_name'])) {
    if (is_string($_SESSION['user_name'])) $avatar_name = $_SESSION['user_name'];
}

$gv_avatar = '';
if ($user_id) {
    $stmt_av = $conn->prepare("SELECT hoten, avatar, role FROM users WHERE id = ?");
    $stmt_av->bind_param("i", $user_id);
    $stmt_av->execute();
    $row_av = $stmt_av->get_result()->fetch_assoc();
    if (!empty($row_av['hoten'])) {
        $avatar_name = $row_av['hoten'];
        $_SESSION['ho_ten'] = $avatar_name;
        $_SESSION['hoten']  = $avatar_name;
    }
    $gv_avatar = $row_av['avatar'] ?? '';
    $user_role = $row_av['role'] ?? $_SESSION['role'] ?? 'teacher';
    $avatar_role = ($user_role == 'teacher') ? 'Giáo viên' : 'Học sinh';
} else {
    $avatar_role = 'Người dùng';
}

// Xử lý link ảnh
if (!empty($gv_avatar) && !str_starts_with($gv_avatar, 'http')) {
    $gv_avatar_src = '../uploads/' . $gv_avatar;
} elseif (!empty($gv_avatar)) {
    $gv_avatar_src = $gv_avatar;
} else {
    $gv_avatar_src = 'https://ui-avatars.com/api/?name=' . urlencode($avatar_name) . '&background=0284c7&color=fff&bold=true';
}

$fallback_av = 'https://ui-avatars.com/api/?name=' . urlencode($avatar_name) . '&background=0284c7&color=fff&bold=true';
$logout_url = ($current_page === 'phonghoc.php') ? 'index.php?action=logout' : '?action=logout';
?>

<script>
    (function() {
        if (localStorage.getItem('dark-mode') === 'enabled') {
            document.documentElement.classList.add('dark-mode');
        }
    })();
</script>

<style>
    :root {
        --bg-header: rgba(255, 255, 255, 0.92);
        --bg-sidebar: #ffffff;
        --bg-dropdown: #ffffff;
        --bg-modal: #ffffff;
        --bg-modal-overlay: rgba(0, 0, 0, 0.5);
        --text-chinh: #1e293b;
        --text-phu: #64748b;
        --border-mau: #e2e8f0;
        --bg-hover: #f1f5f9;
        --bg-item-active: #f0f9ff;
    }

    html.dark-mode {
        --bg-header: rgba(15, 23, 42, 0.92);
        --bg-sidebar: #1e293b;
        --bg-dropdown: #1e293b;
        --bg-modal: #1e293b;
        --bg-modal-overlay: rgba(0, 0, 0, 0.75);
        --text-chinh: #f8fafc;
        --text-phu: #94a3b8;
        --border-mau: #334155;
        --bg-hover: #334155;
        --bg-item-active: #0c4a6e;
    }
    html.dark-mode body { background-color: #0f172a; color: var(--text-chinh); }

    .header-ngang {
        position: fixed; top: 0; left: 0; right: 0; height: 60px;
        background: var(--bg-header); backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--border-mau); z-index: 99;
        display: flex; align-items: center; justify-content: flex-end;
        padding: 0 25px; box-sizing: border-box; gap: 15px;
        transition: background 0.3s, border 0.3s;
    }
    .btn-tham-gia-header {
        background-color: #0284c7; color: white; text-decoration: none;
        padding: 8px 18px; border-radius: 8px; font-weight: 600; font-size: 14px;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.15); display: flex; align-items: center; gap: 6px;
        transition: background 0.2s;
    }
    .btn-tham-gia-header:hover { background-color: #0369a1; }

    .nut-ba-gạch {
        position: fixed; top: 10px; left: 20px; z-index: 9999;
        background: var(--bg-header); color: var(--text-chinh); border: none;
        width: 40px; height: 40px; border-radius: 8px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: background 0.2s;
    }
    .nut-ba-gạch:hover { background: var(--bg-hover); }

    .av-wrap { position: relative; display: inline-flex; align-items: center; gap: 10px; }
    .av-name { font-weight: 700; color: var(--text-chinh); font-size: 15px; cursor: pointer; user-select: none; }
    .av-trigger { position: relative; cursor: pointer; display: inline-block; }
    .av-img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover;
              border: 2px solid #0284c7; box-shadow: 0 2px 6px rgba(0,0,0,0.12);
              transition: all .25s; vertical-align: middle; display: block; }
    .av-trigger:hover .av-img { filter: brightness(.85); transform: scale(1.05); }
    .av-badge { position: absolute; bottom: -2px; right: -2px; background: var(--bg-dropdown); border-radius: 50%;
                width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;
                box-shadow: 0 2px 4px rgba(0,0,0,.2); color: #0284c7; pointer-events: none; border: 1px solid var(--border-mau); }

    .av-menu { display: none; position: absolute; right: 0; top: calc(100% + 10px);
               background: var(--bg-dropdown); border-radius: 14px; min-width: 200px; z-index: 9999;
               box-shadow: 0 8px 28px rgba(0,0,0,.14); overflow: hidden; border: 1px solid var(--border-mau);
               animation: avFadeIn .18s ease; }
    .av-menu.open { display: block; }
    @keyframes avFadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
    .av-menu-header { padding: 14px 16px 10px; border-bottom: 1px solid var(--border-mau); text-align: left; }
    .av-menu-header strong { display: block; font-size: 14px; color: var(--text-chinh); font-weight: 800; }
    .av-menu-header span   { font-size: 12px; color: var(--text-phu); }
    .av-menu-item { display: flex; align-items: center; gap: 10px; padding: 11px 16px;
                    font-size: 14px; font-weight: 700; color: var(--text-chinh); cursor: pointer;
                    text-decoration: none; transition: background .15s; font-family: inherit;
                    border: none; background: none; width: 100%; text-align: left; box-sizing: border-box; }
    .av-menu-item:hover { background: var(--bg-hover); color: #0284c7; }
    .av-menu-item.danger { color: #ef4444; }
    .av-menu-item.danger:hover { background: rgba(239, 68, 68, 0.08); color: #dc2626; }
    .av-menu-divider { height: 1px; background: var(--border-mau); margin: 4px 0; }

    .thanh { 
        width: 260px; background: var(--bg-sidebar); padding: 80px 15px 30px 15px; 
        border-right: 1px solid var(--border-mau); display: flex; flex-direction: column; 
        position: fixed; top: 0; left: 0; bottom: 0; height: 100vh; z-index: 100;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; box-sizing: border-box;
        transition: transform 0.3s ease, background 0.3s, border 0.3s;
    }
    .thanh.thut-vao { transform: translateX(-260px); }
    .thanh h2 { color: #0284c7; font-weight: 800; font-size: 20px; margin-bottom: 35px; text-align: center; margin-top: 0; letter-spacing: 0.5px;}
    .menu-links { flex: 1; display: flex; flex-direction: column; gap: 6px; }
    
    .menu-item { 
        display: flex !important; align-items: center; gap: 12px; width: 100%; padding: 12px 18px; 
        text-decoration: none; color: var(--text-phu); font-weight: 600; border-radius: 10px; 
        font-size: 14.5px; box-sizing: border-box; transition: all 0.2s ease; cursor: pointer; border: none; background: none; text-align: left;
    }
    .menu-item.active, .menu-item:hover { background: var(--bg-item-active); color: #0284c7; }
    .duong-ke-menu { border-top: 1px solid var(--border-mau); margin: 12px 10px; }

    /* Modals Avatar & Setting */
    .modal-overlay-av { display: none; position: fixed; z-index: 10999; left: 0; top: 0; width: 100%; height: 100%; background: var(--bg-modal-overlay); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
    .modal-box-av { background: var(--bg-modal); padding: 35px 25px; border-radius: 20px; width: 340px; text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,.15); border: 1px solid var(--border-mau); position: relative; animation: avSlideUp .3s ease-out; box-sizing: border-box; color: var(--text-chinh); }
    @keyframes avSlideUp { from { transform:translateY(20px);opacity:0; } to { transform:translateY(0);opacity:1; } }
    .av-close-btn { position: absolute; top: 12px; right: 18px; font-size: 28px; color: var(--text-phu); cursor: pointer; font-family: monospace; line-height: 1; background: none; border: none; transition: color .2s; }
    .av-close-btn:hover { color: #ef4444; }
    .av-preview { width: 160px; height: 160px; border-radius: 50%; object-fit: cover; border: 4px solid #e1f5fe; margin: 0 auto 25px; display: block; box-shadow: 0 8px 20px rgba(0,0,0,.08); }
    .av-btn-choose { background: var(--bg-hover); color: var(--text-chinh); border: 2px dashed var(--border-mau); padding: 12px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .3s; margin-bottom: 15px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
    .av-btn-choose:hover { border-color: #0284c7; color: #0284c7; background: var(--bg-item-active); }
    .av-btn-save { background: linear-gradient(135deg,#0288d1,#0277bd); color: white; border: none; padding: 14px; border-radius: 12px; cursor: pointer; width: 100%; font-weight: 800; font-size: 15px; display: none; transition: .3s; box-shadow: 0 4px 12px rgba(2,136,209,.3); font-family: inherit; box-sizing: border-box; }
    .av-btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(2,136,209,.4); }

    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: var(--bg-modal-overlay); z-index: 9999; display: none; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .modal-overlay.open { display: flex; }
    .modal-cai-dat { background: var(--bg-modal); border: 1px solid var(--border-mau); width: 100%; max-width: 680px; max-height: 85vh; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.25); display: flex; flex-direction: column; animation: moModal 0.25s ease-out; color: var(--text-chinh); }
    @keyframes moModal { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .modal-header { padding: 18px 24px; border-bottom: 1px solid var(--border-mau); display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: var(--text-chinh); display: flex; align-items: center; gap: 10px; }
    .nut-dong-modal { background: none; border: none; color: var(--text-phu); cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px; border-radius: 6px; transition: 0.2s; }
    .nut-dong-modal:hover { background: var(--bg-hover); color: #ef4444; }
    .modal-body { padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 24px; font-family: sans-serif; }
    .setting-section-title { font-size: 15px; font-weight: 700; color: #0284c7; margin: 0 0 4px 0; }
    .setting-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px dashed var(--border-mau); }
    .setting-row:last-child { border-bottom: none; }
    .setting-label { font-size: 14px; font-weight: 500; color: var(--text-chinh); padding-right: 15px; display: flex; align-items: center; gap: 10px; }
    .nut-gat-switch { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
    .nut-gat-switch input { opacity: 0; width: 0; height: 0; }
    .slider-tron { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .25s; border-radius: 34px; }
    .slider-tron:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .25s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
    html.dark-mode .slider-tron { background-color: #475569; }
    input:checked + .slider-tron { background-color: #2563eb; }
    input:checked + .slider-tron:before { transform: translateX(20px); }
</style>

<button class="nut-ba-gạch" onclick="chuyenDoiMenu()">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
</button>

<div class="header-ngang">
    <?php if ($current_page === 'index.php'): ?>
    <a href="#" class="btn-tham-gia-header" onclick="openModal(); return false;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Tạo lớp học
    </a>
    <?php endif; ?>

    <div class="av-wrap">
        <span class="av-name" onclick="toggleAvatarMenu(event)"><?php echo htmlspecialchars($avatar_name); ?></span>
        <div class="av-trigger" onclick="toggleAvatarMenu(event)">
            <img id="avImgMain" src="<?php echo htmlspecialchars($gv_avatar_src); ?>" alt="Avatar" class="av-img" onerror="this.src='<?php echo $fallback_av; ?>'">
            <div class="av-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
            </div>
            <div class="av-menu" id="avatarMenu">
                <div class="av-menu-header">
                    <strong><?php echo htmlspecialchars($avatar_name); ?></strong>
                    <span>Vai trò: <?php echo htmlspecialchars($avatar_role); ?></span>
                </div>
                <button class="av-menu-item" onclick="moPopupAvatar(); closeAvatarMenu();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    Đổi ảnh đại diện
                </button>
                <div class="av-menu-divider"></div>
                <a class="av-menu-item danger" href="<?php echo $logout_url; ?>" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Đăng xuất
                </a>
            </div>
        </div>
    </div>
</div>

<div class="thanh" id="vungMenu">
    <h2>Góc Học Tập</h2>
    <div class="menu-links">
        <a href="index.php" class="menu-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            Trang chủ
        </a>
        <div class="duong-ke-menu"></div>
        <a href="profile.php" class="menu-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Hồ sơ cá nhân
        </a>
        <button class="menu-item" onclick="moCaiDat(event)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            Cài đặt hệ thống
        </button>
    </div>
</div>

<div id="modalAvatar" class="modal-overlay-av" onclick="if(event.target===this)dongPopupAvatar()">
    <div class="modal-box-av">
        <button class="av-close-btn" onclick="dongPopupAvatar()">&times;</button>
        <h3 style="margin-top:0;color:#263238;font-size:20px;font-weight:800;margin-bottom:25px;">Đổi Ảnh Đại Diện</h3>
        <img id="avXemTruoc" src="<?php echo htmlspecialchars($gv_avatar_src); ?>" class="av-preview" onerror="this.src='<?php echo $fallback_av; ?>'">
        <form id="avForm" action="xuly_avatar.php" method="POST" enctype="multipart/form-data">
            <input type="file" id="avInputFile" name="file_avatar" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;" onchange="avXemTruoc(event)">
            <button type="button" class="av-btn-choose" onclick="document.getElementById('avInputFile').click()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Chọn ảnh từ thiết bị
            </button>
            <button type="submit" id="avBtnLuu" class="av-btn-save">Cập Nhật Ảnh Mới</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalCaiDat" onclick="dongCaiDat()">
    <div class="modal-cai-dat" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>⚙️ Cấu hình hệ thống lớp học</h3>
            <button class="nut-dong-modal" onclick="dongCaiDat()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="modal-body">
            <div>
                <h4 class="setting-section-title">Giao diện</h4>
                <div class="setting-row">
                    <span class="setting-label">Chế độ nền tối (Dark Mode)</span>
                    <label class="nut-gat-switch">
                        <input type="checkbox" id="chkDarkMode" onchange="chuyenDoiGiaoDien(this)">
                        <span class="slider-tron"></span>
                    </label>
                </div>
            </div>
            <div>
                <h4 class="setting-section-title">Thông báo</h4>
                <div class="setting-row">
                    <span class="setting-label">Cho phép thông báo qua email</span>
                    <label class="nut-gat-switch"><input type="checkbox" id="notify_email" class="chk-thong-bao"><span class="slider-tron"></span></label>
                </div>
                <div class="setting-row">
                    <span class="setting-label">Nhận xét về bài đăng</span>
                    <label class="nut-gat-switch"><input type="checkbox" id="notify_comment_post" class="chk-thong-bao"><span class="slider-tron"></span></label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAvatarMenu(e) { e && e.stopPropagation(); document.getElementById('avatarMenu').classList.toggle('open'); }
function closeAvatarMenu() { document.getElementById('avatarMenu').classList.remove('open'); }
document.addEventListener('click', function(e) {
    var menu = document.getElementById('avatarMenu');
    var wrap = document.querySelector('.av-wrap');
    if (menu && menu.classList.contains('open') && wrap && !wrap.contains(e.target)) menu.classList.remove('open');
});

var _avOrigSrc = "<?php echo addslashes($gv_avatar_src); ?>";
function moPopupAvatar() { document.getElementById('modalAvatar').style.display = 'flex'; }
function dongPopupAvatar() {
    document.getElementById('modalAvatar').style.display = 'none';
    document.getElementById('avInputFile').value = '';
    document.getElementById('avXemTruoc').src = _avOrigSrc;
    document.getElementById('avBtnLuu').style.display = 'none';
}
function avXemTruoc(e) {
    var file = e.target.files[0]; if (!file) return;
    var reader = new FileReader();
    reader.onload = function(ev) { document.getElementById('avXemTruoc').src = ev.target.result; document.getElementById('avBtnLuu').style.display = 'block'; };
    reader.readAsDataURL(file);
}

function chuyenDoiMenu() {
    document.getElementById("vungMenu").classList.toggle("thut-vao");
    var n = document.querySelector(".main-content");
    if (n) n.classList.toggle("mo-rong");
}

function moCaiDat(event) { event.preventDefault(); document.getElementById("modalCaiDat").classList.add("open"); }
function dongCaiDat() { document.getElementById("modalCaiDat").classList.remove("open"); }

function chuyenDoiGiaoDien(checkbox) {
    if (checkbox.checked) { document.documentElement.classList.add('dark-mode'); localStorage.setItem('dark-mode', 'enabled'); }
    else { document.documentElement.classList.remove('dark-mode'); localStorage.setItem('dark-mode', 'disabled'); }
}

document.addEventListener("DOMContentLoaded", function() {
    var chkDark = document.getElementById("chkDarkMode");
    if (chkDark) chkDark.checked = localStorage.getItem('dark-mode') === 'enabled';
    document.querySelectorAll(".chk-thong-bao").forEach(function(nut) {
        var s = localStorage.getItem(nut.id);
        nut.checked = s === null ? true : (s === 'true');
        nut.addEventListener("change", function() { localStorage.setItem(nut.id, nut.checked); });
    });
});
</script>