-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2026 at 10:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quanly_hoctap`
--

-- --------------------------------------------------------

--
-- Table structure for table `bai_tap`
--

CREATE TABLE `bai_tap` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text NOT NULL,
  `file_dinh_kem` varchar(500) DEFAULT NULL,
  `han_nop` datetime DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_chinh_sua` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `chinh_sua` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bai_tap`
--

INSERT INTO `bai_tap` (`id`, `class_id`, `tieu_de`, `noi_dung`, `file_dinh_kem`, `han_nop`, `ngay_tao`, `ngay_chinh_sua`, `chinh_sua`) VALUES
(13, 22, 'bài tập cuối kì', '', '1779584904_1234.zip', '2026-05-30 08:08:00', '2026-05-24 01:08:24', NULL, 0),
(14, 22, '[BÀI TẬP NHÓM] Bài giữa kì', 'làm chăm chút', NULL, '2026-05-26 11:07:00', '2026-05-25 04:07:40', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bang_tin`
--

CREATE TABLE `bang_tin` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `file_dinh_kem` varchar(500) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_chinh_sua` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `chinh_sua` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bang_tin`
--

INSERT INTO `bang_tin` (`id`, `class_id`, `noi_dung`, `file_dinh_kem`, `ngay_tao`, `ngay_chinh_sua`, `chinh_sua`) VALUES
(14, 22, 'mai học đầy đủ nhé các em\r\n', '', '2026-05-24 00:57:14', '2026-05-24 00:57:20', 1),
(15, 22, 'chào các e\r\n', '', '2026-05-24 00:57:54', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `binh_luan`
--

CREATE TABLE `binh_luan` (
  `id` int(11) NOT NULL,
  `id_bangtin` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `binh_luan`
--

INSERT INTO `binh_luan` (`id`, `id_bangtin`, `id_user`, `noi_dung`, `ngay_tao`) VALUES
(12, 15, 56, 'dạ', '2026-05-24 01:28:23'),
(13, 15, 63, 'chó dũng', '2026-05-25 02:17:07');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `ma_lop` varchar(50) NOT NULL,
  `ten_lop` varchar(255) NOT NULL,
  `hoc_ky` varchar(100) DEFAULT NULL,
  `giaovien_id` int(11) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `ma_lop`, `ten_lop`, `hoc_ky`, `giaovien_id`, `mo_ta`, `ngay_tao`) VALUES
(22, '96D6EC', 'Lập trình web', 'Học kì 2-2026', 61, 'chào các em', '2026-05-24 00:56:39'),
(23, '64B689', 'Cấu trúc dữ liệu', '2-2026', 61, '', '2026-05-28 07:44:53');

-- --------------------------------------------------------

--
-- Table structure for table `class_enrollments`
--

CREATE TABLE `class_enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `ngay_tham_gia` timestamp NOT NULL DEFAULT current_timestamp(),
  `nhom_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_enrollments`
--

INSERT INTO `class_enrollments` (`id`, `user_id`, `class_id`, `ngay_tham_gia`, `nhom_id`) VALUES
(12, 56, 22, '2026-05-24 01:08:38', NULL),
(13, 57, 22, '2026-05-24 01:14:07', NULL),
(14, 59, 22, '2026-05-24 01:14:43', NULL),
(15, 60, 22, '2026-05-24 01:15:14', NULL),
(16, 58, 22, '2026-05-24 01:15:32', NULL),
(17, 63, 22, '2026-05-25 02:13:53', NULL),
(18, 63, 23, '2026-05-28 08:13:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `danh_gia`
--

CREATE TABLE `danh_gia` (
  `id` int(11) NOT NULL,
  `id_lop_hoc` int(11) NOT NULL,
  `id_hoc_sinh` int(11) NOT NULL,
  `sao_kien_thuc` int(11) NOT NULL DEFAULT 5,
  `sao_su_pham` int(11) NOT NULL DEFAULT 5,
  `sao_ho_tro` int(11) NOT NULL DEFAULT 5,
  `muc_do_hieu_bai` varchar(50) DEFAULT NULL,
  `nhan_xet` text DEFAULT NULL,
  `ngay_danh_gia` timestamp NOT NULL DEFAULT current_timestamp(),
  `binh_luan` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `danh_gia`
--

INSERT INTO `danh_gia` (`id`, `id_lop_hoc`, `id_hoc_sinh`, `sao_kien_thuc`, `sao_su_pham`, `sao_ho_tro`, `muc_do_hieu_bai`, `nhan_xet`, `ngay_danh_gia`, `binh_luan`, `ngay_tao`) VALUES
(1, 22, 0, 5, 5, 5, NULL, NULL, '2026-05-28 08:10:23', 'cô dạy hay', '2026-05-28 08:10:23'),
(2, 23, 0, 5, 5, 5, NULL, NULL, '2026-05-28 08:13:39', 'cô dạy hay ạ', '2026-05-28 08:13:39');

-- --------------------------------------------------------

--
-- Table structure for table `nhom_hoc`
--

CREATE TABLE `nhom_hoc` (
  `id` int(11) NOT NULL,
  `ten_nhom` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `id_truong_nhom` int(11) NOT NULL,
  `so_luong_toi_da` int(11) DEFAULT 5,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `nhom_id` int(11) GENERATED ALWAYS AS (`id`) VIRTUAL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nhom_hoc`
--

INSERT INTO `nhom_hoc` (`id`, `ten_nhom`, `class_id`, `id_truong_nhom`, `so_luong_toi_da`, `ngay_tao`) VALUES
(1, 'Tây đẹp trai', 22, 0, 5, '2026-05-25 03:29:54'),
(4, 'Nhóm web', 22, 58, 5, '2026-05-26 13:59:14');

-- --------------------------------------------------------

--
-- Table structure for table `nop_bai`
--

CREATE TABLE `nop_bai` (
  `id` int(11) NOT NULL,
  `bai_tap_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `nhom_id` int(11) DEFAULT NULL,
  `file_nop` varchar(500) NOT NULL,
  `link_nop` varchar(500) DEFAULT NULL,
  `diem` float DEFAULT NULL,
  `nhan_xet` text DEFAULT NULL,
  `ngay_nop` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nop_bai`
--

INSERT INTO `nop_bai` (`id`, `bai_tap_id`, `student_id`, `nhom_id`, `file_nop`, `link_nop`, `diem`, `nhan_xet`, `ngay_nop`) VALUES
(11, 13, 56, NULL, 'BAITAP_56_1779584978_6a124fd253563.php', '', NULL, NULL, '2026-05-24 01:09:38'),
(12, 14, 58, 4, '1779805571_bai11.php', '', 5, 'được', '2026-05-26 14:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` char(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `user_id`, `otp_code`, `expires_at`, `used`, `created_at`) VALUES
(17, 61, '588855', '2026-05-24 02:59:46', 1, '2026-05-24 00:54:46'),
(19, 57, '216525', '2026-05-24 03:16:13', 1, '2026-05-24 01:11:13'),
(20, 59, '433666', '2026-05-24 03:19:28', 1, '2026-05-24 01:14:28'),
(21, 60, '077109', '2026-05-24 03:20:02', 1, '2026-05-24 01:15:02'),
(22, 58, '306127', '2026-05-24 03:20:22', 1, '2026-05-24 01:15:22'),
(24, 56, '382226', '2026-05-24 03:32:59', 1, '2026-05-24 01:27:59'),
(25, 63, '498615', '2026-05-25 04:05:49', 1, '2026-05-25 02:00:49'),
(26, 64, '494343', '2026-05-25 04:12:30', 1, '2026-05-25 02:07:30'),
(27, 65, '758485', '2026-05-26 16:10:03', 0, '2026-05-26 14:05:03'),
(28, 66, '405964', '2026-05-26 16:11:54', 0, '2026-05-26 14:06:54');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `ans_a` varchar(255) NOT NULL,
  `ans_b` varchar(255) NOT NULL,
  `ans_c` varchar(255) NOT NULL,
  `ans_d` varchar(255) NOT NULL,
  `correct_ans` varchar(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `ans_a`, `ans_b`, `ans_c`, `ans_d`, `correct_ans`) VALUES
(21, 16, 'HTML dùng để làm gì trong website?', 'Tạo cấu trúc trang web', 'Kết nối internet', '. Lưu dữ liệu', 'Tăng tốc máy tính', 'A'),
(22, 16, 'CSS có chức năng gì?', 'Chạy chương trình', 'Thiết kế giao diện trang web', 'Tạo cơ sở dữ liệu', 'Kiểm tra virus', 'B'),
(23, 16, 'JavaScript thường được dùng để làm gì?', 'Trang trí văn bản', 'Tạo tương tác cho website', 'Cài hệ điều hành', 'Tạo file Word', 'B'),
(24, 16, 'Phần mở rộng phổ biến của file HTML là gì?', 'docx', 'mp3', 'html', '', 'C'),
(25, 16, 'Database dùng để làm gì trong web?', 'chơi game', 'Lưu trữ dữ liệu', 'Vẽ hình', 'Tăng âm lượng máy tính', 'B');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration_minutes` int(11) DEFAULT 15,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `class_id`, `title`, `duration_minutes`, `created_at`) VALUES
(16, 22, 'web cơ bản', 30, '2026-05-24 01:07:44');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `student_answer` varchar(500) NOT NULL DEFAULT '',
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_answers`
--

INSERT INTO `quiz_answers` (`id`, `result_id`, `question_id`, `student_answer`, `is_correct`) VALUES
(2, 2, 21, 'A', 1),
(3, 2, 22, '', 0),
(4, 2, 23, '', 0),
(5, 2, 24, '', 0),
(6, 2, 25, '', 0),
(7, 3, 21, 'A', 1),
(8, 3, 22, 'B', 1),
(9, 3, 23, 'B', 1),
(10, 3, 24, 'C', 1),
(11, 3, 25, 'B', 1);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL DEFAULT 0,
  `correct_count` int(11) NOT NULL DEFAULT 0,
  `score` float NOT NULL DEFAULT 0,
  `nhan_xet_gv` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `quiz_id`, `student_id`, `total_questions`, `correct_count`, `score`, `nhan_xet_gv`, `submitted_at`) VALUES
(2, 16, 56, 5, 1, 2, NULL, '2026-05-24 01:10:48'),
(3, 16, 63, 5, 5, 10, NULL, '2026-05-25 02:15:52');

-- --------------------------------------------------------

--
-- Table structure for table `thanh_vien_nhom`
--

CREATE TABLE `thanh_vien_nhom` (
  `id` int(11) NOT NULL,
  `nhom_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `vai_tro` varchar(50) NOT NULL DEFAULT 'thanh_vien',
  `ngay_tham_gia` timestamp NOT NULL DEFAULT current_timestamp(),
  `trang_thai` int(1) NOT NULL DEFAULT 1 COMMENT '1: Chinh thuc, 0: Cho duyet'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `thanh_vien_nhom`
--

INSERT INTO `thanh_vien_nhom` (`id`, `nhom_id`, `student_id`, `vai_tro`, `ngay_tham_gia`, `trang_thai`) VALUES
(3, 4, 58, 'nhom_truong', '2026-05-26 13:59:14', 1),
(6, 4, 63, 'thanh_vien', '2026-05-26 14:25:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `thong_bao`
--

CREATE TABLE `thong_bao` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `nguoi_gui_id` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trusted_devices`
--

CREATE TABLE `trusted_devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `hoten` varchar(100) NOT NULL,
  `gioitinh` enum('nam','nu','khac') DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `matkhau` varchar(255) NOT NULL,
  `matkhau_plain` varchar(255) DEFAULT NULL COMMENT 'Chỉ dùng để xem khi test — xóa cột này khi lên production',
  `role` enum('student','teacher') NOT NULL,
  `monhoc_yeuthich` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `hoten`, `gioitinh`, `email`, `matkhau`, `matkhau_plain`, `role`, `monhoc_yeuthich`, `avatar`, `ngay_tao`, `verified`, `otp_code`, `otp_expires`, `verification_token`, `token_expiry`, `failed_attempts`, `last_attempt`, `last_login`, `login_ip`) VALUES
(56, 'Hứa Phan Tấn Dũng', 'nam', 'dtan8897@gmail.com', '$2y$10$mFM.1K7hQhVW0Obx.bNUmuJYGx2k8kK.6aZG1oJoBeb8kwqRCYeqS', NULL, 'student', 'web', NULL, '2026-05-24 00:43:48', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(57, 'Phan Nhựt', 'nam', 'nhut@gmail.com', '$2y$10$mIGBq7GFg4b50IhmX8PwOOu3esZx7dCWmKMM2.Vu457HPdeAXH/a2', NULL, 'student', 'web', NULL, '2026-05-24 00:44:33', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(58, 'Trương Lưu Tây', 'nam', 'tay@gmail.com', '$2y$10$O/1n6fSIkDAdlQc7UUHOiupRvXp8lGS5npEGqDbutcPIbhqXQnx6.', NULL, 'student', 'web', NULL, '2026-05-24 00:45:16', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(59, 'Nay Chương', 'nam', 'chuong@gmail.com', '$2y$10$60s4lhxwjCg6.uWUjy6KQOZl44bPR5vIJinngT332iddrhn6bbvHq', NULL, 'student', 'web', NULL, '2026-05-24 00:45:47', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(60, 'Lê Cao Quốc Hiệp', 'nam', 'hiep@gmail.com', '$2y$10$jKBiqTgy8IEsSglnjzGojulZwOc7qL80tUP5pxYQEQ00fhM9MqG0m', NULL, 'student', 'web', NULL, '2026-05-24 00:46:25', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(61, 'Võ Thị Mỹ', 'nu', 'my@gmail.com', '$2y$10$V8FUbu7iuHzOp1Izrs4bZ.i7m4YMU43c77fJm4F0v.LlUUjnmxgzK', NULL, 'teacher', 'web', 'AVATAR_61_1779953782.png', '2026-05-24 00:47:03', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(62, 'bin', 'nam', 'bin@gmail.com', '$2y$10$k2d5A54MZjLZvnVEhUDg5eo42NAIk/hrCKOJvq9zwTM1LHYbrePUG', NULL, 'student', 'web', NULL, '2026-05-24 01:30:36', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(63, 'Lưu Tây', 'nam', 'taynguyen@gmail.com', '$2y$10$IdEtPXUUpPhpajxakQYbxek6lVmypZbATTVOHrDFs8sIOy3.5Bnwa', NULL, 'student', 'Toán', 'HS_63_1779954844.jpg', '2026-05-25 02:00:49', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(64, 'Nhã Vi', 'nu', 'nhavi@gmail.com', '$2y$10$7WIs/Csp0qtCS6Rln.AE.ObWuQIM8jhtOD3SbeWSr8.Yrd4wBogda', NULL, 'teacher', 'Tiếng Hàn', NULL, '2026-05-25 02:07:30', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(65, 'Nhã Vi', 'nu', 'vivi@gmail.com', '$2y$10$f1P2vl9YnSZOZSq8iQCYC.doAcK3TcbJqJ5Lxk6pwVRd8NUJYGZCW', NULL, 'student', '', NULL, '2026-05-26 14:05:03', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(66, 'Nguyên', 'nam', 'nguyen@gmail.com', '$2y$10$YsSvVKMNf7dnEUtBGp44jORTgqCJO7ghjuyFuL9H5ZSyEcJ9jqdre', NULL, 'student', 'Thể Dục', NULL, '2026-05-26 14:06:54', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `giaovien_id` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `youtube_url` varchar(500) NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_han_nop` (`han_nop`);

--
-- Indexes for table `bang_tin`
--
ALTER TABLE `bang_tin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_binhluan_user` (`id_user`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_lop` (`ma_lop`),
  ADD KEY `giaovien_id` (`giaovien_id`);

--
-- Indexes for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_class` (`user_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nhom_hoc`
--
ALTER TABLE `nhom_hoc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nop_bai`
--
ALTER TABLE `nop_bai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bai_tap_id` (`bai_tap_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `result_id` (`result_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_quiz` (`student_id`,`quiz_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_group_student` (`nhom_id`,`student_id`);

--
-- Indexes for table `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `giaovien_id` (`giaovien_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bai_tap`
--
ALTER TABLE `bai_tap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `bang_tin`
--
ALTER TABLE `bang_tin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `binh_luan`
--
ALTER TABLE `binh_luan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `danh_gia`
--
ALTER TABLE `danh_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `nhom_hoc`
--
ALTER TABLE `nhom_hoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `nop_bai`
--
ALTER TABLE `nop_bai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD CONSTRAINT `fk_baitap_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bang_tin`
--
ALTER TABLE `bang_tin`
  ADD CONSTRAINT `fk_bangtin_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD CONSTRAINT `fk_binhluan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`giaovien_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD CONSTRAINT `class_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nop_bai`
--
ALTER TABLE `nop_bai`
  ADD CONSTRAINT `nop_bai_ibfk_1` FOREIGN KEY (`bai_tap_id`) REFERENCES `bai_tap` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nop_bai_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD CONSTRAINT `otp_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_questions_quizzes` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_answers_result` FOREIGN KEY (`result_id`) REFERENCES `quiz_results` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `fk_results_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_results_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD CONSTRAINT `thong_bao_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trusted_devices`
--
ALTER TABLE `trusted_devices`
  ADD CONSTRAINT `trusted_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `fk_video_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_video_gv` FOREIGN KEY (`giaovien_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
