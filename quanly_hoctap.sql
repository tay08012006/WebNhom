-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 05, 2026 lúc 12:09 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanly_hoctap`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bai_tap`
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
-- Đang đổ dữ liệu cho bảng `bai_tap`
--

INSERT INTO `bai_tap` (`id`, `class_id`, `tieu_de`, `noi_dung`, `file_dinh_kem`, `han_nop`, `ngay_tao`, `ngay_chinh_sua`, `chinh_sua`) VALUES
(20, 25, 'bài tập cuối kì 1', 'a', '1780327653_M01.docx', '2026-06-05 01:35:00', '2026-05-30 18:35:55', '2026-06-01 15:27:33', 1),
(22, 22, '[BÀI TẬP NHÓM] bài tập cuối kì', 'sss', NULL, '2026-07-31 10:54:00', '2026-06-01 03:54:44', NULL, 0),
(23, 27, 'chương2', '', '1780543385_BocoNhm4.docx', '2026-06-11 07:00:00', '2026-06-04 03:23:05', NULL, 0),
(24, 27, '[BÀI TẬP NHÓM] chủ đề 3', 'tạo 1 project về đăng nhập và đăng kí', NULL, '2026-06-20 10:38:00', '2026-06-04 03:38:59', NULL, 0),
(25, 28, 'bài thực hành 1', '', '1780546041_ltcb.txt', '2026-06-27 11:07:00', '2026-06-04 04:07:21', NULL, 0),
(26, 28, 'cuối kì 2', '123', '', '2026-06-19 11:07:00', '2026-06-04 04:07:38', '2026-06-04 04:07:45', 1),
(27, 27, 'cuối kì 2', '', '', '2026-06-20 13:09:00', '2026-06-04 06:09:52', NULL, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bang_tin`
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
-- Đang đổ dữ liệu cho bảng `bang_tin`
--

INSERT INTO `bang_tin` (`id`, `class_id`, `noi_dung`, `file_dinh_kem`, `ngay_tao`, `ngay_chinh_sua`, `chinh_sua`) VALUES
(14, 22, 'mai học đầy đủ nhé các em\r\n', '', '2026-05-24 00:57:14', '2026-05-24 00:57:20', 1),
(16, 25, 'xin chào các em\r\n', '', '2026-06-02 15:43:08', NULL, 0),
(17, 27, 'hii\r\n', '', '2026-06-04 03:22:32', NULL, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binh_luan`
--

CREATE TABLE `binh_luan` (
  `id` int(11) NOT NULL,
  `id_bangtin` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `binh_luan`
--

INSERT INTO `binh_luan` (`id`, `id_bangtin`, `id_user`, `noi_dung`, `ngay_tao`) VALUES
(13, 15, 60, 'hii', '2026-05-24 02:10:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `classes`
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
-- Đang đổ dữ liệu cho bảng `classes`
--

INSERT INTO `classes` (`id`, `ma_lop`, `ten_lop`, `hoc_ky`, `giaovien_id`, `mo_ta`, `ngay_tao`) VALUES
(22, '96D6EC', 'Lập trình web', 'Học kì 2-2026', 61, 'chào các em', '2026-05-24 00:56:39'),
(25, '4856CF', 'Toán Rời Rạc', 'Học kì 1-2025', 61, 'hi', '2026-05-28 08:49:18'),
(26, '1A2ED2', 'Lập trình cơ bản', 'Học kì 1-2025', 61, 'c++', '2026-06-04 03:06:06'),
(27, 'F6DEEA', 'Lập trình hướng đối tượng', 'Học kì 2-2025', 61, '', '2026-06-04 03:12:11'),
(28, 'D382AD', 'Cấu trúc dữ liệu', 'Học kì 2-2026', 61, 'hi', '2026-06-04 03:12:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `class_enrollments`
--

CREATE TABLE `class_enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `nhom_id` int(11) DEFAULT NULL,
  `ngay_tham_gia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `class_enrollments`
--

INSERT INTO `class_enrollments` (`id`, `user_id`, `class_id`, `nhom_id`, `ngay_tham_gia`) VALUES
(13, 57, 22, NULL, '2026-05-24 01:14:07'),
(14, 59, 22, NULL, '2026-05-24 01:14:43'),
(15, 60, 22, NULL, '2026-05-24 01:15:14'),
(16, 58, 22, NULL, '2026-05-24 01:15:32'),
(19, 81, 22, NULL, '2026-05-26 08:27:43'),
(20, 82, 22, NULL, '2026-05-28 09:35:29'),
(21, 82, 25, NULL, '2026-05-28 09:40:22'),
(22, 82, 28, NULL, '2026-06-04 03:14:10'),
(23, 82, 26, NULL, '2026-06-04 03:14:19'),
(24, 82, 27, NULL, '2026-06-04 03:14:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_gia`
--

CREATE TABLE `danh_gia` (
  `id` int(11) NOT NULL,
  `id_lop_hoc` int(11) NOT NULL,
  `sao_kien_thuc` tinyint(1) NOT NULL COMMENT '1-5 sao: Chất lượng bài giảng',
  `sao_su_pham` tinyint(1) NOT NULL COMMENT '1-5 sao: Phương pháp sư phạm',
  `sao_ho_tro` tinyint(1) NOT NULL COMMENT '1-5 sao: Tương tác và hỗ trợ',
  `binh_luan` text DEFAULT NULL COMMENT 'Ý kiến văn bản ẩn danh',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_gia`
--

INSERT INTO `danh_gia` (`id`, `id_lop_hoc`, `sao_kien_thuc`, `sao_su_pham`, `sao_ho_tro`, `binh_luan`, `ngay_tao`) VALUES
(2, 22, 4, 4, 5, 'aaaa', '2026-05-27 16:36:50'),
(3, 22, 4, 5, 4, 'hay', '2026-05-28 02:16:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhom_hoc`
--

CREATE TABLE `nhom_hoc` (
  `id` int(11) NOT NULL,
  `ten_nhom` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `id_truong_nhom` int(11) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhom_hoc`
--

INSERT INTO `nhom_hoc` (`id`, `ten_nhom`, `class_id`, `id_truong_nhom`, `ngay_tao`) VALUES
(6, 'vnvn', 22, 81, '2026-06-01 03:55:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nop_bai`
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
-- Đang đổ dữ liệu cho bảng `nop_bai`
--

INSERT INTO `nop_bai` (`id`, `bai_tap_id`, `student_id`, `nhom_id`, `file_nop`, `link_nop`, `diem`, `nhan_xet`, `ngay_nop`) VALUES
(18, 22, 81, 6, '1780286111_MÃ ĐỀ 01.docx', '', 4, 'ddd', '2026-06-01 03:55:11'),
(19, 22, 82, NULL, 'BAITAP_82_1780327858_6a1da5b259617.docx', '', NULL, NULL, '2026-06-01 15:30:58'),
(21, 23, 82, NULL, 'BAITAP_82_1780543436_6a20efccd75f2.docx', '', 10, 'giỏi', '2026-06-04 03:23:56'),
(22, 20, 82, NULL, 'BAITAP_82_1780544809_6a20f5291628a.sql', '', NULL, NULL, '2026-06-04 03:46:49'),
(23, 24, 82, NULL, 'BAITAP_82_1780545375_6a20f75f20d23.zip', '', 2, 'dở', '2026-06-04 03:56:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `otp_codes`
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
-- Đang đổ dữ liệu cho bảng `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `user_id`, `otp_code`, `expires_at`, `used`, `created_at`) VALUES
(20, 59, '433666', '2026-05-24 03:19:28', 1, '2026-05-24 01:14:28'),
(22, 58, '306127', '2026-05-24 03:20:22', 1, '2026-05-24 01:15:22'),
(27, 61, '252528', '2026-05-24 03:52:08', 1, '2026-05-24 01:47:08'),
(28, 60, '428884', '2026-05-24 04:15:26', 1, '2026-05-24 02:10:26'),
(29, 57, '081182', '2026-05-24 04:32:25', 0, '2026-05-24 02:27:25'),
(67, 81, '176531', '2026-05-26 10:31:13', 1, '2026-05-26 08:26:13'),
(70, 82, '774546', '2026-05-28 11:38:10', 1, '2026-05-28 09:33:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `questions`
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
-- Đang đổ dữ liệu cho bảng `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `ans_a`, `ans_b`, `ans_c`, `ans_d`, `correct_ans`) VALUES
(21, 16, 'HTML dùng để làm gì trong website?', 'Tạo cấu trúc trang web', 'Kết nối internet', '. Lưu dữ liệu', 'Tăng tốc máy tính', 'A'),
(22, 16, 'CSS có chức năng gì?', 'Chạy chương trình', 'Thiết kế giao diện trang web', 'Tạo cơ sở dữ liệu', 'Kiểm tra virus', 'B'),
(23, 16, 'JavaScript thường được dùng để làm gì?', 'Trang trí văn bản', 'Tạo tương tác cho website', 'Cài hệ điều hành', 'Tạo file Word', 'B'),
(24, 16, 'Phần mở rộng phổ biến của file HTML là gì?', 'docx', 'mp3', 'html', '', 'C'),
(25, 16, 'Database dùng để làm gì trong web?', 'chơi game', 'Lưu trữ dữ liệu', 'Vẽ hình', 'Tăng âm lượng máy tính', 'B');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration_minutes` int(11) DEFAULT 15,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quiz_mode` varchar(10) NOT NULL DEFAULT 'fixed' COMMENT 'fixed = đề cố định, random = bốc đề ngẫu nhiên',
  `questions_per_exam` int(11) NOT NULL DEFAULT 0 COMMENT 'Số câu bốc mỗi lần thi (0 = lấy tất cả)',
  `shuffle_answers` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Xáo trộn thứ tự đáp án',
  `shuffle_questions` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Xáo trộn thứ tự câu hỏi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quizzes`
--

INSERT INTO `quizzes` (`id`, `class_id`, `title`, `duration_minutes`, `created_at`, `quiz_mode`, `questions_per_exam`, `shuffle_answers`, `shuffle_questions`) VALUES
(16, 22, 'web cơ bản', 30, '2026-05-24 01:07:44', 'fixed', 0, 1, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `student_answer` varchar(500) NOT NULL DEFAULT '',
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quiz_answers`
--

INSERT INTO `quiz_answers` (`id`, `result_id`, `question_id`, `student_answer`, `is_correct`) VALUES
(26, 12, 21, '', 0),
(27, 12, 22, '', 0),
(28, 12, 23, '', 0),
(29, 12, 24, '', 0),
(30, 12, 25, '', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quiz_cheating_log`
--

CREATE TABLE `quiz_cheating_log` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `so_lan_vi_pham` int(11) NOT NULL DEFAULT 1,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `ghi_chu` text DEFAULT NULL,
  `cap_nhat_luc` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ghi nhận học sinh thoát tab khi làm bài trắc nghiệm';

--
-- Đang đổ dữ liệu cho bảng `quiz_cheating_log`
--

INSERT INTO `quiz_cheating_log` (`id`, `quiz_id`, `student_id`, `so_lan_vi_pham`, `is_locked`, `ghi_chu`, `cap_nhat_luc`) VALUES
(1, 19, 81, 3, 1, NULL, '2026-05-27 03:21:35'),
(2, 20, 81, 3, 1, NULL, '2026-05-27 03:46:23'),
(3, 21, 81, 3, 1, NULL, '2026-05-27 03:54:25'),
(4, 21, 80, 3, 1, NULL, '2026-05-27 03:59:06'),
(5, 22, 80, 3, 1, NULL, '2026-05-27 03:59:44');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quiz_results`
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
-- Đang đổ dữ liệu cho bảng `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `quiz_id`, `student_id`, `total_questions`, `correct_count`, `score`, `nhan_xet_gv`, `submitted_at`) VALUES
(12, 16, 81, 5, 0, 0, '', '2026-05-28 17:08:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanh_vien_nhom`
--

CREATE TABLE `thanh_vien_nhom` (
  `id` int(11) NOT NULL,
  `nhom_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `vai_tro` enum('nhom_truong','thanh_vien') NOT NULL DEFAULT 'thanh_vien',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=chờ duyệt, 1=đã duyệt',
  `ngay_tham_gia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thanh_vien_nhom`
--

INSERT INTO `thanh_vien_nhom` (`id`, `nhom_id`, `student_id`, `vai_tro`, `trang_thai`, `ngay_tham_gia`) VALUES
(8, 6, 81, 'nhom_truong', 1, '2026-06-01 03:55:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao`
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
-- Cấu trúc bảng cho bảng `trusted_devices`
--

CREATE TABLE `trusted_devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `trusted_devices`
--

INSERT INTO `trusted_devices` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(2, 61, 'a3363c83d63152c8069245d932f5d145f47a23ec3a5fc482fd03e0edfbb669c3', '2026-06-23 03:47:22', '2026-05-24 08:47:22'),
(3, 60, 'e99d800d0ece6ee3176d517aac617931feefaecec41fec6ac281657a616b58a6', '2026-06-23 04:10:38', '2026-05-24 09:10:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `hoten` varchar(100) NOT NULL,
  `gioitinh` enum('nam','nu','khac') DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `matkhau` varchar(255) NOT NULL,
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
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `hoten`, `gioitinh`, `email`, `matkhau`, `role`, `monhoc_yeuthich`, `avatar`, `ngay_tao`, `verified`, `otp_code`, `otp_expires`, `verification_token`, `token_expiry`, `failed_attempts`, `last_attempt`, `last_login`, `login_ip`) VALUES
(57, 'Phan Nhựt', 'nam', 'nhut@gmail.com', '$2y$10$mIGBq7GFg4b50IhmX8PwOOu3esZx7dCWmKMM2.Vu457HPdeAXH/a2', 'student', 'web', NULL, '2026-05-24 00:44:33', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(58, 'Trương Lưu Tây', 'nam', 'tay@gmail.com', '$2y$10$O/1n6fSIkDAdlQc7UUHOiupRvXp8lGS5npEGqDbutcPIbhqXQnx6.', 'student', 'web', NULL, '2026-05-24 00:45:16', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(59, 'Nay Chương', 'nam', 'chuong@gmail.com', '$2y$10$60s4lhxwjCg6.uWUjy6KQOZl44bPR5vIJinngT332iddrhn6bbvHq', 'student', 'web', NULL, '2026-05-24 00:45:47', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(60, 'Lê Cao Quốc Hiệp', 'nam', 'hiep@gmail.com', '$2y$10$jKBiqTgy8IEsSglnjzGojulZwOc7qL80tUP5pxYQEQ00fhM9MqG0m', 'student', 'web', NULL, '2026-05-24 00:46:25', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(61, 'Võ Thị Mỹ', 'nu', 'my@gmail.com', '$2y$10$V8FUbu7iuHzOp1Izrs4bZ.i7m4YMU43c77fJm4F0v.LlUUjnmxgzK', 'teacher', 'web', 'AVATAR_61_1779947582.png', '2026-05-24 00:47:03', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(81, 'binbin', 'nam', 'trgibin2006@gmail.com', '$2y$10$tV9e8e/bYo7q3YH0toB/8uJ27oAe5/g.8ufwdJeDfev8J1vH4xqSO', 'student', 'tón', 'HS_81_1779936208.png', '2026-05-26 08:26:13', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(82, 'Hứa Phan Tấn Dũng', 'nam', 'dtan8897@gmail.com', '$2y$10$RdE84Q4FkoYoKJLZGwO8IeTzF64lyDhkjhqbLcI.f2RyBoakD7Luu', 'student', 'web', NULL, '2026-05-28 09:33:10', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `videos`
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
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_class_id` (`class_id`),
  ADD KEY `idx_han_nop` (`han_nop`);

--
-- Chỉ mục cho bảng `bang_tin`
--
ALTER TABLE `bang_tin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Chỉ mục cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_binhluan_user` (`id_user`);

--
-- Chỉ mục cho bảng `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_lop` (`ma_lop`),
  ADD KEY `giaovien_id` (`giaovien_id`);

--
-- Chỉ mục cho bảng `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_class` (`user_id`,`class_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `fk_enroll_nhom` (`nhom_id`);

--
-- Chỉ mục cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_lop_hoc` (`id_lop_hoc`);

--
-- Chỉ mục cho bảng `nhom_hoc`
--
ALTER TABLE `nhom_hoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `id_truong_nhom` (`id_truong_nhom`);

--
-- Chỉ mục cho bảng `nop_bai`
--
ALTER TABLE `nop_bai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bai_tap_id` (`bai_tap_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `nhom_id` (`nhom_id`);

--
-- Chỉ mục cho bảng `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Chỉ mục cho bảng `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Chỉ mục cho bảng `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `result_id` (`result_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Chỉ mục cho bảng `quiz_cheating_log`
--
ALTER TABLE `quiz_cheating_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quiz_student` (`quiz_id`,`student_id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Chỉ mục cho bảng `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_quiz` (`student_id`,`quiz_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Chỉ mục cho bảng `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nhom_id` (`nhom_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Chỉ mục cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Chỉ mục cho bảng `trusted_devices`
--
ALTER TABLE `trusted_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `giaovien_id` (`giaovien_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bai_tap`
--
ALTER TABLE `bai_tap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `bang_tin`
--
ALTER TABLE `bang_tin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `class_enrollments`
--
ALTER TABLE `class_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `nhom_hoc`
--
ALTER TABLE `nhom_hoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `nop_bai`
--
ALTER TABLE `nop_bai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT cho bảng `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT cho bảng `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT cho bảng `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT cho bảng `quiz_cheating_log`
--
ALTER TABLE `quiz_cheating_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `trusted_devices`
--
ALTER TABLE `trusted_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT cho bảng `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD CONSTRAINT `fk_baitap_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `bang_tin`
--
ALTER TABLE `bang_tin`
  ADD CONSTRAINT `fk_bangtin_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD CONSTRAINT `fk_binhluan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`giaovien_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD CONSTRAINT `class_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nhom_hoc`
--
ALTER TABLE `nhom_hoc`
  ADD CONSTRAINT `fk_nhom_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nhom_truong` FOREIGN KEY (`id_truong_nhom`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `nop_bai`
--
ALTER TABLE `nop_bai`
  ADD CONSTRAINT `nop_bai_ibfk_1` FOREIGN KEY (`bai_tap_id`) REFERENCES `bai_tap` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nop_bai_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD CONSTRAINT `otp_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_questions_quizzes` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quiz_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_answers_result` FOREIGN KEY (`result_id`) REFERENCES `quiz_results` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `fk_results_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_results_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  ADD CONSTRAINT `fk_tv_nhom` FOREIGN KEY (`nhom_id`) REFERENCES `nhom_hoc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tv_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD CONSTRAINT `thong_bao_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `trusted_devices`
--
ALTER TABLE `trusted_devices`
  ADD CONSTRAINT `trusted_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `fk_video_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_video_gv` FOREIGN KEY (`giaovien_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
