-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 20, 2026 lúc 10:08 AM
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
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `han_nop` datetime DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `chinh_sua` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `bai_tap`
--

INSERT INTO `bai_tap` (`id`, `class_id`, `tieu_de`, `noi_dung`, `file_dinh_kem`, `han_nop`, `ngay_tao`, `chinh_sua`) VALUES
(6, 13, 'cxzxczxczGFG', 'cxzxzcczx', '', '2026-05-13 08:09:00', '2026-05-20 01:09:48', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bang_tin`
--

CREATE TABLE `bang_tin` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `chinh_sua` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(13, '0EF770', 'lý', 'Học kì 2', 5, 'he', '2026-05-18 01:46:49'),
(15, '52FB01', 'toán', 'Học kì 1-2025', 3, 'chào class', '2026-05-20 03:06:16');

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
(3, 9, 13, '2026-05-20 02:49:47'),
(4, 9, 15, '2026-05-20 03:06:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nop_bai`
--

CREATE TABLE `nop_bai` (
  `id` int(11) NOT NULL,
  `bai_tap_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_nop` varchar(255) NOT NULL,
  `diem` float DEFAULT NULL COMMENT 'Điểm giáo viên chấm',
  `nhan_xet` text DEFAULT NULL COMMENT 'Lời phê của giáo viên',
  `ngay_nop` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `hoten` varchar(100) NOT NULL,
  `gioitinh` varchar(20) DEFAULT NULL,
  `monhoc_yeuthich` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `matkhau` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL COMMENT 'student hoặc teacher',
  `avatar` varchar(255) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `hoten`, `gioitinh`, `monhoc_yeuthich`, `email`, `matkhau`, `role`, `avatar`, `ngay_tao`) VALUES
(3, 'he', NULL, NULL, 'hu@gmail.com', '1', 'teacher', 'uploads/USER_3_1778913537.png', '2026-05-11 03:04:29'),
(5, 'thaydung', NULL, NULL, 'dung@gmail.com', '123', 'teacher', 'uploads/USER_5_1779068855.png', '2026-05-17 14:44:42'),
(7, 'saooo', NULL, NULL, 'hi@gmail.com', '00', 'student', NULL, '2026-05-18 03:53:16'),
(8, 'tấn dũng', NULL, NULL, 'he@gmail.com', '123', 'student', NULL, '2026-05-20 02:12:33'),
(9, 'phan nhựt', NULL, NULL, 'nhut@gmail.com', '1', 'student', NULL, '2026-05-20 02:49:03'),
(10, 'Hưa Phan Tấn DŨng', NULL, NULL, 'dsa@gmail.com', '123', 'student', NULL, '2026-05-20 03:45:50'),
(15, 'phan nhựt', NULL, NULL, 'nhut5@gmail.com', '1', 'student', NULL, '2026-05-20 03:52:24'),
(16, '123', NULL, NULL, 'nn@gmail.com', '1', 'student', NULL, '2026-05-20 03:54:51'),
(17, 'phan nhựt', NULL, NULL, 'p@gmail.com', '1', 'student', NULL, '2026-05-20 03:55:51'),
(20, 'lưu tây', NULL, NULL, 'tay2@gmail.com', '000000', 'student', NULL, '2026-05-20 07:43:46'),
(21, 'gia bin', NULL, NULL, 'bin@gmail.com', 'binbin', 'student', NULL, '2026-05-20 07:53:40'),
(23, 'nay chương', NULL, NULL, 'chuong123@gmail.com', '000000', 'teacher', NULL, '2026-05-20 07:58:31');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Chỉ mục cho bảng `bang_tin`
--
ALTER TABLE `bang_tin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Chỉ mục cho bảng `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_lop` (`ma_lop`),
  ADD KEY `classes_ibfk_1` (`giaovien_id`);

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
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bai_tap`
--
ALTER TABLE `bai_tap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `bang_tin`
--
ALTER TABLE `bang_tin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `class_enrollments`
--
ALTER TABLE `class_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `nop_bai`
--
ALTER TABLE `nop_bai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD CONSTRAINT `bai_tap_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `bang_tin`
--
ALTER TABLE `bang_tin`
  ADD CONSTRAINT `bang_tin_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `class_enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Các ràng buộc cho bảng `nop_bai`
--
ALTER TABLE `nop_bai`
  ADD CONSTRAINT `nop_bai_ibfk_1` FOREIGN KEY (`bai_tap_id`) REFERENCES `bai_tap` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nop_bai_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
