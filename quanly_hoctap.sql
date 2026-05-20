-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 02:58 AM
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
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `han_nop` datetime DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bang_tin`
--

CREATE TABLE `bang_tin` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(6, 'CCA445', 'Lập trình', 'Học kì 2 - 2026', 3, '1', '2026-05-16 05:33:17'),
(7, '8A2169', 'ư', 'ư', 3, 'ư', '2026-05-16 06:14:31'),
(8, '6BC6D1', 'lập trình web', 'Học kì 2-2026', 5, 'chào các em', '2026-05-17 14:46:13'),
(13, '0EF770', 'lý', 'Học kì 2', 5, 'he', '2026-05-18 01:46:49');

-- --------------------------------------------------------

--
-- Table structure for table `class_enrollments`
--

CREATE TABLE `class_enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `ngay_tham_gia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_enrollments`
--

INSERT INTO `class_enrollments` (`id`, `user_id`, `class_id`, `ngay_tham_gia`) VALUES
(1, 7, 13, '2026-05-18 03:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `nop_bai`
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `hoten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `matkhau` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL COMMENT 'student hoặc teacher',
  `avatar` varchar(255) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `hoten`, `email`, `matkhau`, `role`, `avatar`, `ngay_tao`) VALUES
(1, 'tấn dũng', 'dtan8897@gmail.com', '1', 'student', NULL, '2026-05-11 03:00:39'),
(2, 'tấn dũng', 'giangvien@test.com', '1', 'student', NULL, '2026-05-11 03:01:09'),
(3, 'he', 'hu@gmail.com', '1', 'teacher', 'uploads/USER_3_1778913537.png', '2026-05-11 03:04:29'),
(4, 'cc', 'cc@gmail.com', '1', 'student', NULL, '2026-05-11 03:07:24'),
(5, 'thaydung', 'dung@gmail.com', '123', 'teacher', 'uploads/USER_5_1779068855.png', '2026-05-17 14:44:42'),
(6, 'tấn dũng', 'a@gmail.com', '1221', 'student', NULL, '2026-05-18 02:46:09'),
(7, 'saooo', 'hi@gmail.com', '00', 'student', NULL, '2026-05-18 03:53:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `bang_tin`
--
ALTER TABLE `bang_tin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

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
-- Indexes for table `nop_bai`
--
ALTER TABLE `nop_bai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bai_tap_id` (`bai_tap_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bai_tap`
--
ALTER TABLE `bai_tap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bang_tin`
--
ALTER TABLE `bang_tin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nop_bai`
--
ALTER TABLE `nop_bai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bai_tap`
--
ALTER TABLE `bai_tap`
  ADD CONSTRAINT `bai_tap_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bang_tin`
--
ALTER TABLE `bang_tin`
  ADD CONSTRAINT `bang_tin_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`giaovien_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `class_enrollments`
--
ALTER TABLE `class_enrollments`
  ADD CONSTRAINT `class_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `class_enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `nop_bai`
--
ALTER TABLE `nop_bai`
  ADD CONSTRAINT `nop_bai_ibfk_1` FOREIGN KEY (`bai_tap_id`) REFERENCES `bai_tap` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nop_bai_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
