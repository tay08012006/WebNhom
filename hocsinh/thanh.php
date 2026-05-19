<?php
// Khởi động session nếu chưa có để đảm bảo đọc được tên tài khoản đăng nhập trên mọi trang
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy tên file hiện tại để kích hoạt trạng thái menu tích cực
$current_page = basename($_SERVER['PHP_SELF']);

// Kiểm tra sự tồn tại của Session họ tên để hiển thị lên thanh màu trắng đục
$avatar_name = (isset($_SESSION['ho_ten']) && !empty($_SESSION['ho_ten'])) ? $_SESSION['ho_ten'] : 'Học Sinh';
$avatar_role = (isset($_SESSION['role']) && $_SESSION['role'] == 'student') ? 'Học sinh' : 'Người dùng';

// Tách lấy chữ cái đầu tiên của Tên chính một cách chuẩn xác
$ten_cat = trim($avatar_name);
$mang_chu = explode(' ', $ten_cat);
$chu_cai_dau = mb_substr(end($mang_chu), 0, 1, 'UTF-8'); 
?>

<style>
    /* 1. THANH HEADER NGANG (NAVBAR) MÀU TRẮNG ĐỤC */
    .header-ngang {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: rgba(255, 255, 255, 0.92); /* Màu trắng đục */
        backdrop-filter: blur(10px); /* Hiệu ứng mờ kính */
        border-bottom: 1px solid #e2e8f0;
        z-index: 99; /* Nằm dưới nút ba gạch và sidebar nhưng đè lên nội dung */
        display: flex;
        align-items: center;
        justify-content: flex-end; /* Đẩy các nút chức năng về góc phải */
        padding: 0 25px;
        box-sizing: border-box;
        gap: 15px; /* Khoảng cách giữa nút Tham gia lớp học và Hình tròn Avatar */
    }

    /* ĐỊNH DẠNG NÚT "+ THAM GIA LỚP HỌC BẰNG MÃ" TRÊN HEADER */
    .btn-tham-gia-header {
        background-color: #0284c7;
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        transition: background-color 0.2s, transform 0.1s;
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.15);
    }
    .btn-tham-gia-header:hover {
        background-color: #0369a1;
    }

    /* 2. NÚT BA DẤU GẠCH GHIM TRÊN ĐẦU HEADER */
    .nut-ba-gạch {
        position: fixed;
        top: 10px;
        left: 20px; 
        z-index: 1000; /* Luôn luôn nổi lên trên cùng cao nhất */
        background: #0284c7;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(2, 132, 199, 0.3);
        transition: background 0.2s;
    }
    .nut-ba-gạch:hover { background: #0369a1; }

    /* 3. KHU VỰC AVATAR TÀI KHOẢN VÀ DROPDOWN THÔNG TIN */
    .vung-tai-khoan {
        position: relative;
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    /* Hình tròn nhỏ vừa vặn trong thanh màu trắng đục */
    .avatar-tron {
        width: 38px;
        height: 38px;
        background: #0284c7;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        border: 2px solid #e2e8f0;
        text-transform: uppercase;
        user-select: none;
    }

    /* Khối menu thông tin thả xuống khi nhấn vào hình tròn avatar */
    .dropdown-thong-tin {
        position: absolute;
        top: 48px;
        right: 0;
        width: 230px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        padding: 15px;
        display: none; /* Mặc định ẩn đi */
        flex-direction: column;
        gap: 10px;
        z-index: 1001;
    }
    
    .dropdown-thong-tin.hien-thi {
        display: flex; /* Hiện lên khi kích hoạt kích chuột */
    }

    .info-user-detail h4 { color: #1e293b; font-size: 15px; font-weight: 700; margin-bottom: 2px; margin-top: 0; }
    .info-user-detail p { color: #64748b; font-size: 12px; font-weight: 600; margin-bottom: 5px; margin-top: 0; }
    .gach-ngang-chia { border-top: 1px solid #f1f5f9; margin: 2px 0; }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        color: #475569;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        border-radius: 8px;
        transition: background 0.2s;
    }
    .dropdown-item:hover { background: #f1f5f9; color: #0284c7; }
    .dropdown-item.text-do:hover { background: #fff5f5; color: #ef4444; }

    /* 4. THANH SIDEBAR MENU CHÍNH */
    .thanh { 
        width: 260px; 
        background: white; 
        padding: 80px 15px 30px 15px; 
        border-right: 1px solid #e2e8f0; 
        display: flex; 
        flex-direction: column; 
        position: fixed;
        top: 0; 
        left: 0; 
        bottom: 0;
        height: 100vh;
        z-index: 100; /* Nằm trên Header ngang màu trắng đục */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        box-sizing: border-box;
        transition: transform 0.3s ease;
    }
    
    .thanh.thut-vao { transform: translateX(-260px); }
    .thanh h2 { color: #0284c7; font-weight: 800; font-size: 22px; margin-bottom: 35px; text-align: center; margin-top: 0; }
    .menu-links { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    
    .menu-item { 
        display: block !important;
        width: 100%; 
        padding: 12px 18px; 
        text-decoration: none; 
        color: #64748b; 
        font-weight: 600; 
        border-radius: 10px; 
        font-size: 15px; 
        box-sizing: border-box;
        transition: all 0.2s ease;
        text-align: left;
    }
    .menu-item.active, .menu-item:hover { background: #f0f9ff; color: #0284c7; font-weight: 700; }
</style>

<button class="nut-ba-gạch" onclick="chuyenDoiMenu()">☰</button>

<div class="header-ngang">
    
    <a href="thamgialop.php" class="btn-tham-gia-header">+ Tham gia lớp học bằng mã</a>

    <div class="vung-tai-khoan" onclick="chuyenDoiDropdown(event)">
        <div class="avatar-tron">
            <?php echo htmlspecialchars($chu_cai_dau); ?>
        </div>
        
        <div class="dropdown-thong-tin" id="dropdownProfile">
            <div class="info-user-detail">
                <h4><?php echo htmlspecialchars($avatar_name); ?></h4>
                <p>Vai trò: <?php echo htmlspecialchars($avatar_role); ?></p>
            </div>
            <div class="gach-ngang-chia"></div>
            <a href="profile.php" class="dropdown-item">⚙️ Hồ sơ cá nhân</a>
            <a href="logout.php" class="dropdown-item text-do" style="color: #ef4444;">🚪 Đăng xuất</a>
        </div>
    </div>
</div>

<div class="thanh" id="vungMenu">
    <h2>Góc Học Tập</h2>
    <div class="menu-links">
        <a href="index.php" class="menu-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Trang chủ</a>
        <a href="lophoc.php" class="menu-item <?php echo ($current_page == 'lophoc.php') ? 'active' : ''; ?>">Lớp học của tôi</a>
        </div>
</div>

<script>
// Hàm ẩn/hiện mở rộng Sidebar đóng mở
function chuyenDoiMenu() {
    var menu = document.getElementById("vungMenu");
    var noiDung = document.querySelector(".main-content");

    menu.classList.toggle("thut-vao");
    if (noiDung) {
        noiDung.classList.toggle("mo-rong");
    }
}

// Hàm bấm vào Avatar hiển thị dropdown thông tin tài khoản đăng nhập
function chuyenDoiDropdown(event) {
    event.stopPropagation(); // Ngăn sự kiện click bị lan ra ngoài
    var dd = document.getElementById("dropdownProfile");
    dd.classList.toggle("hien-thi");
}

// Tự động đóng dropdown nếu click ra ngoài vùng hiển thị
window.onclick = function(event) {
    var dd = document.getElementById("dropdownProfile");
    if (dd && dd.classList.contains('hien-thi')) {
        dd.classList.remove('hien-thi');
    }
}
</script>