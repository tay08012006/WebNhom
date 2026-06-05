<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../config.php';
include 'thanh.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../trangdangnhap.php");
    exit;
}

$ma_lop = $_GET['malop'] ?? '';

$stmt = $conn->prepare("SELECT * FROM classes WHERE ma_lop = ? AND giaovien_id = ?");
$stmt->bind_param("si", $ma_lop, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Lớp học không tồn tại hoặc bạn không có quyền truy cập!");
}

$current_class = $result->fetch_assoc();
$tab = $_GET['tab'] ?? 'bang-tin';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($current_class['ten_lop']) ?> | Góc Học Tập</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Nunito',sans-serif;
        }

        body{
            background:#f4f7f9;
            color:#333;
            min-height:100vh;
        }

        /* Main */
        .main-content{
            margin-left:260px;
            padding-top:70px;
            min-height:100vh;
            transition:margin-left 0.3s ease;
        }

        .main-content.mo-rong{
            margin-left:0 !important;
        }

        /* Navbar phụ */
        .navbar-phu{
            display:flex;
            align-items:center;
            gap:20px;

            background:white;
            padding:12px 30px;

            box-shadow:0 2px 8px rgba(0,0,0,0.06);

            position:sticky;
            top:60px;
            z-index:50;
        }

        .btn-back{
            display:inline-flex;
            align-items:center;

            background:#f0f4f8;
            color:#555;

            text-decoration:none;

            padding:8px 16px;
            border-radius:20px;

            font-weight:700;
            font-size:14px;

            transition:0.2s;
        }

        .btn-back:hover{
            background:#e1e8ed;
            color:#333;
        }

        .container{
            max-width:1000px;
            margin:30px auto;
            padding:0 20px;
        }

        /* Banner lớp */
        .class-banner{
            background:linear-gradient(135deg,#0277bd 0%,#03a9f4 100%);
            color:white;

            padding:28px 30px;
            border-radius:16px;

            margin-bottom:25px;

            box-shadow:0 8px 25px rgba(2,119,189,0.35);

            position:relative;
            overflow:hidden;
        }

        .class-banner::before{
            content:'';
            position:absolute;

            top:0;
            left:0;
            right:0;
            bottom:0;

            background:linear-gradient(
                135deg,
                rgba(255,255,255,0.15),
                transparent
            );

            pointer-events:none;
        }

        .class-banner h1{
            font-size:28px;
            font-weight:800;
            margin:0;

            text-shadow:0 3px 6px rgba(0,0,0,0.2);
        }

        .class-banner p{
            color:rgba(255,255,255,0.92);
            font-size:15px;
            margin-top:8px;
            font-weight:500;
        }

        /* Tabs */
        .class-nav{
            display:flex;
            align-items:center;
            flex-wrap:wrap;

            background:linear-gradient(135deg,#0277bd,#03a9f4);

            border-radius:12px;

            margin-bottom:25px;

            box-shadow:0 6px 16px rgba(2,119,189,0.25);

            overflow:hidden;
        }

        .class-nav a{
            padding:16px 22px;

            color:rgba(255,255,255,0.85);

            text-decoration:none;

            font-weight:700;
            font-size:14px;

            border-bottom:3px solid transparent;

            transition:all 0.2s ease;

            display:flex;
            align-items:center;

            white-space:nowrap;
        }

        .class-nav a:hover{
            color:#fff;
            background:rgba(255,255,255,0.08);
        }

        .class-nav a.active{
            color:#fff;
            border-bottom:3px solid #fff;
        }

        /* Đăng video nằm sát Xếp hạng */
        .class-nav a.nav-video{
            margin-left:0;
            color:rgba(255,255,255,0.85);
        }

        .content{
            padding:10px 0;
        }

        /* Responsive */
        @media(max-width:768px){

            .main-content{
                margin-left:0;
            }

            .navbar-phu{
                padding:12px 16px;
                flex-wrap:wrap;
            }

            .class-nav{
                overflow-x:auto;
            }

            .class-nav a{
                padding:14px 18px;
                font-size:13px;
            }

            .class-banner{
                padding:22px;
            }

            .class-banner h1{
                font-size:24px;
            }
        }
    </style>
</head>

<body>

<div class="main-content" id="mainContent">

    <!-- Navbar phụ -->
    <div class="navbar-phu">

        <a href="index.php" class="btn-back">
            ← Quay lại danh sách lớp
        </a>

        <span style="font-weight:800;color:#0277bd;font-size:18px;">
            Quản lý:
            <?= htmlspecialchars($current_class['ten_lop']) ?>
        </span>

    </div>

    <div class="container">

        <!-- Banner -->
        <div class="class-banner">

            <div style="display:flex;align-items:center;gap:14px;">

                <img
                    src="<?= htmlspecialchars($gv_avatar ?? '') ?>"
                    style="
                        width:52px;
                        height:52px;
                        border-radius:50%;
                        object-fit:cover;
                        border:3px solid rgba(255,255,255,0.85);
                        flex-shrink:0;
                        box-shadow:0 4px 8px rgba(0,0,0,0.15);
                    "
                    alt="Avatar Giáo Viên"
                    onerror="this.style.display='none'"
                >

                <div>
                    <h1>
                        <?= htmlspecialchars($current_class['ten_lop']) ?>
                    </h1>

                    <p>
                        <?= htmlspecialchars($current_class['hoc_ky'] ?? 'Chưa cập nhật học kỳ') ?>
                        |
                        Mã lớp:
                        <b><?= htmlspecialchars($current_class['ma_lop']) ?></b>
                    </p>
                </div>

            </div>

        </div>

        <!-- Tabs -->
        <div class="class-nav">

            <a
                href="?malop=<?= $ma_lop ?>&tab=bang-tin"
                class="<?= $tab == 'bang-tin' ? 'active' : '' ?>"
            >
                Bảng tin
            </a>

            <a
                href="?malop=<?= $ma_lop ?>&tab=bai-tap"
                class="<?= $tab == 'bai-tap' ? 'active' : '' ?>"
            >
                Bài tập trên lớp
            </a>

            <a
                href="?malop=<?= $ma_lop ?>&tab=moi-nguoi"
                class="<?= $tab == 'moi-nguoi' ? 'active' : '' ?>"
            >
                Mọi người
            </a>

            <a
                href="?malop=<?= $ma_lop ?>&tab=nhom"
                class="<?= $tab == 'nhom' ? 'active' : '' ?>"
            >
                Bài tập Nhóm
            </a>

            <a
                href="?malop=<?= $ma_lop ?>&tab=xephang"
                class="<?= $tab == 'xephang' ? 'active' : '' ?>"
            >
                Xếp hạng
            </a>

            <a
                href="dangvideo.php?malop=<?= $ma_lop ?>"
                class="nav-video"
            >
                Đăng Video
            </a>

        </div>

        <!-- Nội dung -->
        <div class="content">

            <?php
            if ($tab === 'bang-tin') {

                include 'taobangtin.php';

            } elseif ($tab === 'bai-tap') {

                include 'taobaitap.php';

            } elseif ($tab === 'moi-nguoi') {

                include 'taomoinguoi.php';

            } elseif ($tab === 'nhom') {

                include 'quanly_nhom.php';

            } elseif ($tab === 'xephang') {

                include 'xephang.php';

            }
            ?>

        </div>

    </div>

</div>

</body>
</html>