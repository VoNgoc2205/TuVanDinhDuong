-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 14, 2026 lúc 10:49 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";




CREATE TABLE `ai_services` (
  `id` int(11) NOT NULL,
  `ten_ai` varchar(100) DEFAULT NULL,
  `chuc_nang` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `trang_thai` varchar(50) DEFAULT 'Hoạt động',
  `so_lan_dung` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `benh_an` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `loai` enum('tieu_su','dau_hieu','kham','chi_so') NOT NULL,
  `ten_muc` varchar(100) NOT NULL,
  `gia_tri` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(160) NOT NULL DEFAULT 'Hội thoại mới',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `conversation_id` int(11) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `ketqua_ai` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ten_mon` varchar(255) DEFAULT NULL,
  `calo` float DEFAULT NULL,
  `protein` float DEFAULT NULL,
  `carb` float DEFAULT NULL,
  `fat` float DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `meals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `bua` varchar(50) DEFAULT NULL,
  `thoigian` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `meal_items` (
  `id` int(11) NOT NULL,
  `meal_id` int(11) DEFAULT NULL,
  `ten_mon` varchar(255) DEFAULT NULL,
  `calo` int(11) DEFAULT NULL,
  `protein` int(11) DEFAULT NULL,
  `carb` int(11) DEFAULT NULL,
  `fat` int(11) DEFAULT NULL,
  `gram` double DEFAULT 0,
  `fiber` double DEFAULT 0,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `thanh_phan_json` longtext DEFAULT NULL,
  `nutrition_json` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `users` (`id`, `name`, `phone`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'ngoc', '0858322205', 'ngocvovl2205@gmail.com', '$2y$10$TElUo9B7SEwxNFVV24YLROKV8M1OFj5dgauU3J2cluW4xvPh4eT.y', 'user', '2026-04-25 03:50:09'),
(2, 'Admin', '1234567890', 'admin@gmail.com', '$2y$10$5k/IDtSoZP1cun/l/74TCOJ53yMyqWhatP77TpmJt47dbi2SH/mde', 'admin', '2026-04-30 15:25:02'),
(3, 'Như', '0234567890', 'nhu@gmail.com', '$2y$10$ya/Bd6vsneo1fVE73XNdd.oPAru1ipJlpTXVlF.NFs9EGG26ywV0W', 'user', '2026-04-30 15:31:17');



CREATE TABLE `user_feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `category` varchar(80) NOT NULL DEFAULT 'general',
  `title` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `chieucao` float DEFAULT 0,
  `cannang` float DEFAULT 0,
  `tuoi` int(11) DEFAULT 0,
  `gioitinh` varchar(10) DEFAULT NULL,
  `tilemo` float DEFAULT 0,
  `muctieu` varchar(255) DEFAULT NULL,
  `muctieu_cannang` float DEFAULT NULL,
  `tinhtrang_suckhoe` text DEFAULT NULL,
  `chedo_an` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `kcal_target` int(11) DEFAULT 2000,
  `medical_record_image` varchar(255) DEFAULT NULL,
  `medical_analysis` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `weight_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `can_nang` float DEFAULT NULL,
  `ngay` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `ai_services`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `benh_an`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_conversations_user_updated` (`user_id`,`updated_at`);


ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_messages_conversation` (`conversation_id`);


ALTER TABLE `ketqua_ai`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `meal_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_id` (`meal_id`);


ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `user_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_feedback_status_created` (`status`,`created_at`);


ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);


ALTER TABLE `weight_logs`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `ai_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `benh_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;


ALTER TABLE `chat_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;


ALTER TABLE `ketqua_ai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;


ALTER TABLE `meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

ALTER TABLE `meal_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;


ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `user_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


ALTER TABLE `weight_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;




ALTER TABLE `benh_an`
  ADD CONSTRAINT `benh_an_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


ALTER TABLE `meal_items`
  ADD CONSTRAINT `meal_items_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE;


ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

