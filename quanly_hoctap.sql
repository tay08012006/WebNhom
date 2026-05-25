-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 24, 2026 lúc 03:36 AM
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
(13, 22, 'bài tập cuối kì', '', '1779584904_1234.zip', '2026-05-30 08:08:00', '2026-05-24 01:08:24', NULL, 0);

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
(15, 22, 'chào các e\r\n', '', '2026-05-24 00:57:54', NULL, 0);

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
(12, 15, 56, 'dạ', '2026-05-24 01:28:23');

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
(22, '96D6EC', 'Lập trình web', 'Học kì 2-2026', 61, 'chào các em', '2026-05-24 00:56:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `class_enrollments`
--

CREATE TABLE `class_enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `ngay_tham_gia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `class_enrollments`
--

INSERT INTO `class_enrollments` (`id`, `user_id`, `class_id`, `ngay_tham_gia`) VALUES
(12, 56, 22, '2026-05-24 01:08:38'),
(13, 57, 22, '2026-05-24 01:14:07'),
(14, 59, 22, '2026-05-24 01:14:43'),
(15, 60, 22, '2026-05-24 01:15:14'),
(16, 58, 22, '2026-05-24 01:15:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nop_bai`
--

CREATE TABLE `nop_bai` (
  `id` int(11) NOT NULL,
  `bai_tap_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_nop` varchar(500) NOT NULL,
  `link_nop` varchar(500) DEFAULT NULL,
  `diem` float DEFAULT NULL,
  `nhan_xet` text DEFAULT NULL,
  `ngay_nop` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nop_bai`
--

INSERT INTO `nop_bai` (`id`, `bai_tap_id`, `student_id`, `file_nop`, `link_nop`, `diem`, `nhan_xet`, `ngay_nop`) VALUES
(11, 13, 56, 'BAITAP_56_1779584978_6a124fd253563.php', '', NULL, NULL, '2026-05-24 01:09:38');

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
(17, 61, '588855', '2026-05-24 02:59:46', 1, '2026-05-24 00:54:46'),
(19, 57, '216525', '2026-05-24 03:16:13', 1, '2026-05-24 01:11:13'),
(20, 59, '433666', '2026-05-24 03:19:28', 1, '2026-05-24 01:14:28'),
(21, 60, '077109', '2026-05-24 03:20:02', 1, '2026-05-24 01:15:02'),
(22, 58, '306127', '2026-05-24 03:20:22', 1, '2026-05-24 01:15:22'),
(24, 56, '382226', '2026-05-24 03:32:59', 1, '2026-05-24 01:27:59');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quizzes`
--

INSERT INTO `quizzes` (`id`, `class_id`, `title`, `duration_minutes`, `created_at`) VALUES
(16, 22, 'web cơ bản', 30, '2026-05-24 01:07:44');

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
(2, 2, 21, 'A', 1),
(3, 2, 22, '', 0),
(4, 2, 23, '', 0),
(5, 2, 24, '', 0),
(6, 2, 25, '', 0);

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
(2, 16, 56, 5, 1, 2, NULL, '2026-05-24 01:10:48');

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
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `hoten`, `gioitinh`, `email`, `matkhau`, `role`, `monhoc_yeuthich`, `avatar`, `ngay_tao`, `verified`, `otp_code`, `otp_expires`, `verification_token`, `token_expiry`, `failed_attempts`, `last_attempt`, `last_login`, `login_ip`) VALUES
(56, 'Hứa Phan Tấn Dũng', 'nam', 'dtan8897@gmail.com', '$2y$10$mFM.1K7hQhVW0Obx.bNUmuJYGx2k8kK.6aZG1oJoBeb8kwqRCYeqS', 'student', 'web', NULL, '2026-05-24 00:43:48', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(57, 'Phan Nhựt', 'nam', 'nhut@gmail.com', '$2y$10$mIGBq7GFg4b50IhmX8PwOOu3esZx7dCWmKMM2.Vu457HPdeAXH/a2', 'student', 'web', NULL, '2026-05-24 00:44:33', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(58, 'Trương Lưu Tây', 'nam', 'tay@gmail.com', '$2y$10$O/1n6fSIkDAdlQc7UUHOiupRvXp8lGS5npEGqDbutcPIbhqXQnx6.', 'student', 'web', NULL, '2026-05-24 00:45:16', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(59, 'Nay Chương', 'nam', 'chuong@gmail.com', '$2y$10$60s4lhxwjCg6.uWUjy6KQOZl44bPR5vIJinngT332iddrhn6bbvHq', 'student', 'web', NULL, '2026-05-24 00:45:47', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(60, 'Lê Cao Quốc Hiệp', 'nam', 'hiep@gmail.com', '$2y$10$jKBiqTgy8IEsSglnjzGojulZwOc7qL80tUP5pxYQEQ00fhM9MqG0m', 'student', 'web', NULL, '2026-05-24 00:46:25', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(61, 'Võ Thị Mỹ', 'nu', 'my@gmail.com', '$2y$10$V8FUbu7iuHzOp1Izrs4bZ.i7m4YMU43c77fJm4F0v.LlUUjnmxgzK', 'teacher', 'web', NULL, '2026-05-24 00:47:03', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(62, 'bin', 'nam', 'bin@gmail.com', '$2y$10$k2d5A54MZjLZvnVEhUDg5eo42NAIk/hrCKOJvq9zwTM1LHYbrePUG', 'student', 'web', NULL, '2026-05-24 01:30:36', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL);

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
  ADD KEY `class_id` (`class_id`);

--
-- Chỉ mục cho bảng `nop_bai`
--
ALTER TABLE `nop_bai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bai_tap_id` (`bai_tap_id`),
  ADD KEY `student_id` (`student_id`);

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
-- Chỉ mục cho bảng `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_quiz` (`student_id`,`quiz_id`),
  ADD KEY `quiz_id` (`quiz_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `bang_tin`
--
ALTER TABLE `bang_tin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `class_enrollments`
--
ALTER TABLE `class_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `nop_bai`
--
ALTER TABLE `nop_bai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `trusted_devices`
--
ALTER TABLE `trusted_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- Thêm cột matkhau_plain (nếu chưa có) — dùng để xem mật khẩu khi test
-- Chạy lệnh này trong phpMyAdmin nếu DB đã tồn tại trước đó:
--
-- ALTER TABLE `users` ADD COLUMN `matkhau_plain` VARCHAR(255) DEFAULT NULL
--   COMMENT 'Chỉ dùng để xem khi test — xóa cột này khi lên production'
--   AFTER `matkhau`;

--
-- AUTO_INCREMENT cho bảng `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- ============================================================
-- UPDATE DATABASE (chạy 1 lần trong phpMyAdmin nếu DB đã có)
-- ============================================================