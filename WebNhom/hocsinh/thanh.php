<?php
// Khởi động session nếu chưa có để đảm bảo đọc được tên tài khoản đăng nhập trên mọi trang
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'HS_SESSION');
    session_start();
}

// Lấy tên file hiện tại để kích hoạt trạng thái menu tích cực
$current_page = basename($_SERVER['PHP_SELF']);

// Kiểm tra sự tồn tại của Session họ tên để lấy chữ cái đầu
$avatar_name = (isset($_SESSION['ho_ten']) && !empty($_SESSION['ho_ten'])) ? $_SESSION['ho_ten'] : 'Học Sinh';
$ten_cat = trim($avatar_name);
$mang_chu = explode(' ', $ten_cat);
$chu_cai_dau = mb_substr(end($mang_chu), 0, 1, 'UTF-8'); 

// Đọc avatar từ database để hiển thị ảnh thật
$hs_avatar_src = '';
if (isset($_SESSION['user_id'])) {
    if (!isset($conn)) {
        include_once dirname(__DIR__) . '/config.php';
    }
    if (isset($conn)) {
        $stmt_nav_av = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt_nav_av->bind_param("i", $_SESSION['user_id']);
        $stmt_nav_av->execute();
        $row_nav_av = $stmt_nav_av->get_result()->fetch_assoc();
        if (!empty($row_nav_av['avatar'])) {
            $hs_avatar_src = '../uploads/' . htmlspecialchars($row_nav_av['avatar']);
            $_SESSION['avatar'] = $row_nav_av['avatar'];
        }
    }
}
?>

<script>
    (function() {
        if (localStorage.getItem('dark-mode') === 'enabled') {
            document.documentElement.classList.add('dark-mode');
        }
    })();
</script>

<style>
    /* BỘ BIẾN MÀU SẮC GIAO DIỆN SÁNG/TỐI */
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

    /* 1. THANH HEADER NGANG (NAVBAR) */
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

    /* 2. NÚT BA DẤU GẠCH SIDEBAR */
    .nut-ba-gạch {
        position: fixed; top: 10px; left: 20px; z-index: 1000;
        background: none; color: var(--text-chinh); border: none;
        width: 40px; height: 40px; border-radius: 8px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.2s;
    }
    .nut-ba-gạch:hover { background: var(--bg-hover); }

    /* 3. AVATAR VÀ DROPDOWN (CHỈ CÓ ĐĂNG XUẤT) */
    .vung-tai-khoan { position: relative; display: flex; align-items: center; cursor: pointer; }
    .avatar-tron {
        width: 38px; height: 38px; background: #0284c7; color: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 16px; border: 2px solid var(--border-mau);
        text-transform: uppercase; user-select: none;
    }
    .dropdown-thong-tin {
        position: absolute; top: 48px; right: 0; width: 160px;
        background: var(--bg-dropdown); border: 1px solid var(--border-mau);
        border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        padding: 8px; display: none; flex-direction: column; z-index: 1001;
    }
    .dropdown-thong-tin.hien-thi { display: flex; }
    .dropdown-item {
        display: flex; align-items: center; justify-content: flex-start; padding: 10px 15px; 
        color: var(--text-chinh); text-decoration: none; font-size: 14px; gap: 10px;
        font-weight: 600; border-radius: 8px; transition: background 0.2s;
    }
    .dropdown-item.text-do { color: #ef4444; }
    .dropdown-item.text-do:hover { background: #fff5f5; }

    /* 4. THANH SIDEBAR MENU CHÍNH */
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
        font-size: 14.5px; box-sizing: border-box; transition: all 0.2s ease; cursor: pointer;
    }
    .menu-item.active, .menu-item:hover { background: var(--bg-item-active); color: #0284c7; }
    .duong-ke-menu { border-top: 1px solid var(--border-mau); margin: 12px 10px; }

    /* ==================== GIAO DIỆN MODAL CÀI ĐẶT ==================== */
    .modal-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: var(--bg-modal-overlay); z-index: 9999;
        display: none; align-items: center; justify-content: center; backdrop-filter: blur(4px);
    }
    .modal-overlay.open { display: flex; }
    .modal-cai-dat {
        background: var(--bg-modal); border: 1px solid var(--border-mau);
        width: 100%; max-width: 680px; max-height: 85vh; border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.25); display: flex; flex-direction: column;
        animation: moModal 0.25s ease-out; color: var(--text-chinh);
    }
    @keyframes moModal { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    
    .modal-header { padding: 18px 24px; border-bottom: 1px solid var(--border-mau); display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: var(--text-chinh); display: flex; align-items: center; gap: 10px; }
    .nut-dong-modal { background: none; border: none; color: var(--text-phu); cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 4px; border-radius: 6px; transition: 0.2s; }
    .nut-dong-modal:hover { background: var(--bg-hover); color: #ef4444; }
    
    .modal-body { padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 24px; font-family: sans-serif; }
    .setting-section-title { font-size: 15px; font-weight: 700; color: #0284c7; margin: 0 0 4px 0; }
    .setting-section-desc { font-size: 13px; color: var(--text-phu); margin: 0 0 16px 0; }
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
    <a href="thamgialop.php" class="btn-tham-gia-header">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Tham gia lớp học
    </a>

    <div class="vung-tai-khoan" onclick="chuyenDoiDropdown(event)">
        <div class="avatar-tron" style="overflow:hidden;padding:0;">
            <?php if (!empty($hs_avatar_src)): ?>
                <img src="<?php echo $hs_avatar_src; ?>" 
                     style="width:100%;height:100%;object-fit:cover;border-radius:50%;"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;"><?php echo htmlspecialchars($chu_cai_dau); ?></span>
            <?php else: ?>
                <?php echo htmlspecialchars($chu_cai_dau); ?>
            <?php endif; ?>
        </div>
        
        <div class="dropdown-thong-tin" id="dropdownProfile">
            <a href="logout.php" class="dropdown-item text-do">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Đăng xuất
            </a>
        </div>
    </div>
</div>

<div class="thanh" id="vungMenu">
    <h2>Góc Học Tập</h2>
    <div class="menu-links">
        <a href="index.php" class="menu-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Trang chủ
        </a>
        <a href="lophoc.php" class="menu-item <?php echo ($current_page == 'lophoc.php') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            Lớp học của tôi
        </a>
        
        <div class="duong-ke-menu"></div>
        
        <a href="profile.php" class="menu-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Hồ sơ cá nhân
        </a>
        <a class="menu-item" onclick="moCaiDat(event)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            Cài đặt hệ thống
        </a>
    </div>
</div>

<div class="modal-overlay" id="modalCaiDat" onclick="dongCaiDat()">
    <div class="modal-cai-dat" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                Cấu hình hệ thống lớp học
            </h3>
            <button class="nut-dong-modal" onclick="dongCaiDat()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            
            <div>
                <h4 class="setting-section-title">Giao diện lớp học</h4>
                <div class="setting-row">
                    <span class="setting-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #64748b;">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                        Chế độ nền tối (Dark Mode)
                    </span>
                    <label class="nut-gat-switch">
                        <input type="checkbox" id="chkDarkMode" onchange="chuyenDoiGiaoDien(this)">
                        <span class="slider-tron"></span>
                    </label>
                </div>
            </div>

            <div>
                <h4 class="setting-section-title">Email</h4>
                <div class="setting-row">
                    <span class="setting-label">Cho phép thông báo qua email</span>
                    <label class="nut-gat-switch">
                        <input type="checkbox" id="notify_email" class="chk-thong-bao">
                        <span class="slider-tron"></span>
                    </label>
                </div>
            </div>

            <div>
                <h4 class="setting-section-title">Nhận xét</h4>
                <div class="setting-row">
                    <span class="setting-label">Các nhận xét về bài đăng của bạn</span>
                    <label class="nut-gat-switch">
                        <input type="checkbox" id="notify_comment_post" class="chk-thong-bao">
                        <span class="slider-tron"></span>
                    </label>
                </div>
                <div class="setting-row">
                    <span class="setting-label">Nhận xét riêng tư về bài tập</span>
                    <label class="nut-gat-switch">
                        <input type="checkbox" id="notify_private_comment" class="chk-thong-bao">
                        <span class="slider-tron"></span>
                    </label>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
// Menu Sidebar
function chuyenDoiMenu() {
    var menu = document.getElementById("vungMenu");
    var noiDung = document.querySelector(".main-content");
    menu.classList.toggle("thut-vao");
    if (noiDung) noiDung.classList.toggle("mo-rong");
}

// Avatar Dropdown (Chỉ đăng xuất)
function chuyenDoiDropdown(event) {
    event.stopPropagation();
    var dd = document.getElementById("dropdownProfile");
    dd.classList.toggle("hien-thi");
}

window.onclick = function(event) {
    var dd = document.getElementById("dropdownProfile");
    if (dd && dd.classList.contains('hien-thi')) dd.classList.remove('hien-thi');
}

// Modal Cài đặt
function moCaiDat(event) {
    event.preventDefault();
    document.getElementById("modalCaiDat").classList.add("open");
}

function dongCaiDat() {
    document.getElementById("modalCaiDat").classList.remove("open");
}

// Dark Mode
function chuyenDoiGiaoDien(checkbox) {
    if (checkbox.checked) {
        document.documentElement.classList.add('dark-mode');
        localStorage.setItem('dark-mode', 'enabled');
    } else {
        document.documentElement.classList.remove('dark-mode');
        localStorage.setItem('dark-mode', 'disabled');
    }
}

// Sync LocalStorage
document.addEventListener("DOMContentLoaded", function() {
    var isDarkMode = localStorage.getItem('dark-mode') === 'enabled';
    document.getElementById("chkDarkMode").checked = isDarkMode;

    var cacNutThongBao = document.querySelectorAll(".chk-thong-bao");
    cacNutThongBao.forEach(function(nut) {
        var trangThaiLuu = localStorage.getItem(nut.id);
        nut.checked = trangThaiLuu === null ? true : (trangThaiLuu === 'true');
        nut.addEventListener("change", function() {
            localStorage.setItem(nut.id, nut.checked);
        });
    });
});
</script>