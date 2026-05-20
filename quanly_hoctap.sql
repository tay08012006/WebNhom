-- =============================================
-- SQL SCRIPT - Cập nhật đầy đủ database quanly_hoctap
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

USE `quanly_hoctap`;

-- Xóa các bảng cũ nếu tồn tại (an toàn khi chạy lại)
DROP TABLE IF EXISTS `nop_bai`;
DROP TABLE IF EXISTS `class_enrollments`;
DROP TABLE IF EXISTS `bai_tap`;
DROP TABLE IF EXISTS `bang_tin`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `users`;

-- =============================================
-- Tạo lại bảng users
-- =============================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hoten` varchar(100) NOT NULL,
  `gioitinh` enum('nam','nu','khac') DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `matkhau` varchar(255) NOT NULL,
  `role` enum('student','teacher') NOT NULL,
  `monhoc_yeuthich` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tạo lại bảng classes
-- =============================================
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_lop` varchar(50) NOT NULL,
  `ten_lop` varchar(255) NOT NULL,
  `hoc_ky` varchar(100) DEFAULT NULL,
  `giaovien_id` int(11) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_lop` (`ma_lop`),
  KEY `giaovien_id` (`giaovien_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`giaovien_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tạo lại bảng bai_tap
-- =============================================
CREATE TABLE `bai_tap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text NOT NULL,
  `file_dinh_kem` varchar(500) DEFAULT NULL,
  `han_nop` datetime DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_chinh_sua` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `chinh_sua` tinyint(1) NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  KEY `idx_class_id` (`class_id`),
  KEY `idx_han_nop` (`han_nop`),
  CONSTRAINT `fk_baitap_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tạo lại bảng bang_tin
-- =============================================
CREATE TABLE `bang_tin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `file_dinh_kem` varchar(500) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_chinh_sua` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `chinh_sua` tinyint(1) NOT NULL DEFAULT 0,

  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `fk_bangtin_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tạo lại bảng class_enrollments
-- =============================================
CREATE TABLE `class_enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `ngay_tham_gia` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_class` (`user_id`,`class_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `class_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tạo lại bảng nop_bai
-- =============================================
CREATE TABLE `nop_bai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bai_tap_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_nop` varchar(500) NOT NULL,
  `diem` float DEFAULT NULL,
  `nhan_xet` text DEFAULT NULL,
  `ngay_nop` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `bai_tap_id` (`bai_tap_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `nop_bai_ibfk_1` FOREIGN KEY (`bai_tap_id`) REFERENCES `bai_tap` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nop_bai_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Insert dữ liệu mẫu (có thể bỏ qua nếu không muốn)
-- =============================================
-- Bạn có thể paste phần INSERT từ file cũ nếu cần giữ dữ liệu

COMMIT;