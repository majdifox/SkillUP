-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2025 at 04:44 PM
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
-- Database: `skillup`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `created_at`) VALUES
(1, 'coding', '2025-01-19 15:50:21'),
(2, 'photography', '2025-01-19 15:50:36'),
(6, 'cv vc', '2025-01-20 10:10:41'),
(7, 'asdvfb', '2025-01-20 10:10:51'),
(9, 'hbhghg', '2025-01-20 10:11:23'),
(10, 'asd', '2025-01-20 10:12:32'),
(14, 'ijmnjmnj', '2025-01-20 10:13:28'),
(16, 'dsf', '2025-01-20 10:13:44'),
(18, 'o;idgndsng', '2025-01-20 10:14:24'),
(23, 'a', '2025-01-20 10:20:13'),
(25, '444', '2025-01-20 10:20:29');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `difficulty_level` enum('beginner','intermediate','experienced') NOT NULL,
  `duration_type` enum('hours','minutes','days','weeks') NOT NULL,
  `duration_value` int(11) NOT NULL,
  `content_type` enum('document','video') NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `document_pages` int(11) DEFAULT NULL,
  `video_length` int(11) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `status` enum('in_progress','accepted','refused') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `document_url` varchar(255) DEFAULT NULL,
  `tag_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `title`, `description`, `thumbnail_url`, `difficulty_level`, `duration_type`, `duration_value`, `content_type`, `teacher_id`, `category_id`, `document_pages`, `video_length`, `video_url`, `status`, `created_at`, `updated_at`, `document_url`, `tag_id`) VALUES
(1, 'pythonxxx', 'loopssdfsggb', 'uploads/thumbnails/678d4eb4d18d5_465379466_9086438858055348_5551983675326159778_n.jpg', 'experienced', 'days', 1754, 'video', 11, 2, NULL, 12541, 'uploads/videos/678d4eb4d1ac8_Would you say that this is one of Tom’s greatest moments..mp4', 'accepted', '2025-01-19 16:00:31', '2025-01-19 19:12:52', NULL, NULL),
(3, 'test tags', 'sdaafsvbgdh', 'uploads/thumbnails/678d7762a2702_472664056_1016601340497479_4875382308184601047_n.jpg', 'intermediate', 'hours', 15, 'document', 11, 2, 11, NULL, NULL, 'accepted', '2025-01-19 22:06:26', '2025-01-20 22:28:11', NULL, NULL),
(4, 'doc url', 'dcfv ', 'uploads/thumbnails/678d7addd0a2e_472791149_1012040467610965_183739758930396493_n.jpg', '', 'days', 921, 'document', 11, 1, 11, NULL, NULL, 'refused', '2025-01-19 22:21:17', '2025-01-20 22:28:25', 'uploads/documents/678d7addd0d2e_Plan d’action.pdf', NULL),
(5, 'sdcfv', 'cdsfv', 'uploads/thumbnails/678d7b96cbe55_472716482_1145522976931219_2349526282517082807_n.jpg', '', 'hours', 5147, 'document', 11, 1, 11, NULL, NULL, '', '2025-01-19 22:24:22', '2025-01-20 22:36:55', 'uploads/documents/678d7b96cc0e7_Plan d’action.pdf', NULL),
(6, 'dxfbgx', 'zdfbgxnh ch', 'uploads/thumbnails/678d801f4cd08_473570956_938698938407810_8322205052709657268_n.jpg', 'intermediate', 'days', 44, 'document', 11, 1, 13, NULL, NULL, 'accepted', '2025-01-19 22:43:43', '2025-01-20 22:58:18', 'uploads/documents/678d801f4cf3b_Plan d’action.pdf', NULL),
(7, 'dxfbgx', 'zdfbgxnh ch', 'uploads/thumbnails/678d8056a6dd0_473570956_938698938407810_8322205052709657268_n.jpg', 'intermediate', 'days', 44, 'document', 11, 1, 13, NULL, NULL, 'accepted', '2025-01-19 22:44:38', '2025-01-20 22:32:00', 'uploads/documents/678d8056a6fe1_Plan d’action.pdf', NULL),
(8, 'hbjnk', 'ghjn km', 'uploads/thumbnails/678d80d54d3ce_472226127_1568823733819671_2802442419599410814_n.jpg', 'beginner', 'hours', 45, 'document', 11, 2, 56, NULL, NULL, 'accepted', '2025-01-19 22:46:45', '2025-01-20 00:18:26', 'uploads/documents/678d80d54d603_Plan d’action.pdf', NULL),
(9, 'dsfvbg', 'dfgsdhnf', 'uploads/thumbnails/678d82ebdf5cb_472534065_122117875742419442_779303583597161280_n.jpg', 'beginner', 'hours', 455, 'document', NULL, 2, 4, NULL, NULL, 'refused', '2025-01-19 22:55:39', '2025-01-20 22:56:34', 'uploads/documents/678d82ebdf8b3_Plan d’action.pdf', NULL),
(10, 'mehdi', 'majdi', 'uploads/thumbnails/678d91587a2a3_472397028_122129202134474700_5263703386177302555_n.jpg', '', 'hours', 13, 'document', NULL, 2, 13, NULL, NULL, 'accepted', '2025-01-19 23:57:12', '2025-01-20 22:56:30', 'uploads/documents/678d91587a4f7_Plan d’action.pdf', NULL),
(11, 'zzz', 'zxcv', 'uploads/thumbnails/678d95e2a309d_471948698_907158841534364_338105901651151082_n.jpg', 'beginner', 'hours', 123, 'document', NULL, 1, 44, NULL, NULL, '', '2025-01-20 00:16:34', '2025-01-20 22:36:51', 'uploads/documents/678d95e2a33dc_Plan d’action.pdf', NULL),
(12, 'zzz2', 'ghbj', 'uploads/thumbnails/678d9612739c0_473226061_122215380680077572_1206601066013171697_n.jpg', 'beginner', 'hours', 15, 'video', NULL, 1, NULL, 12, 'uploads/videos/678d961273c12_cute.mp4', 'accepted', '2025-01-20 00:17:22', '2025-01-20 11:02:40', NULL, NULL),
(25, '101 web devx', 'qwertyu', 'uploads/thumbnails/678fbf9850e1b_472534065_122117875742419442_779303583597161280_n.jpg', 'beginner', 'days', 30, 'video', 11, 2, NULL, 60, 'uploads/videos/678fbf9851027_cute.mp4', 'accepted', '2025-01-21 15:39:04', '2025-01-21 15:40:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_tags`
--

CREATE TABLE `course_tags` (
  `course_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_tags`
--

INSERT INTO `course_tags` (`course_id`, `tag_id`) VALUES
(10, 3),
(12, 2),
(12, 3),
(25, 1),
(25, 2),
(25, 3);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_accessed` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `course_id`, `enrolled_at`, `last_accessed`) VALUES
(1, 12, 8, '2025-01-20 10:56:14', NULL),
(2, 12, 12, '2025-01-20 11:02:48', NULL),
(3, 12, 1, '2025-01-20 11:11:46', NULL),
(4, 12, 3, '2025-01-20 23:18:19', NULL),
(6, 12, 25, '2025-01-21 15:40:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`tag_id`, `name`, `created_at`) VALUES
(1, 'dev', '2025-01-19 21:19:37'),
(2, 'coding', '2025-01-19 21:19:46'),
(3, 'programming', '2025-01-19 21:19:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','instructor','admin') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('in_progress','accepted','refused') DEFAULT 'in_progress'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `is_active`, `created_at`, `status`) VALUES
(1, 'mehdi', 'majmehdi12@gmail.com', '$2y$10$esZK3tVTSXckbqUARO7QKeQMGq5Ee3n773rlDJ2WIDEpHz0J2b4im', 'student', 1, '2025-01-19 01:26:16', 'in_progress'),
(6, '', '', '$2y$10$DakuUChI2GSRnKyX8CigAeZ0Ao6NmepbIqbnqtNXvM2.5vIkiwMpm', 'student', 1, '2025-01-19 01:34:45', 'accepted'),
(10, 'majmehdi', 'majmehdixx@gmail.com', '$2y$10$VVN0pXS4j1mvMV3OJb.e4.3WoFyizUBnfR9F0kWUQXPIy2fXT.alS', 'admin', 1, '2025-01-19 01:39:16', 'accepted'),
(11, 'teacher', 'teacherxx@gmail.com', '$2y$10$uFU0ncxKLepm.tJ1nEqoxO4PFvM/OHv9sAYMmP2UXSRn97hlxHN0a', 'instructor', 1, '2025-01-19 15:21:36', 'accepted'),
(12, 'nizar', 'nizar@gmail.com', '$2y$10$B6ggyX1tYKxKNDfyx22KK.0tewhJEnD/PxR6LZ6EgfMP4.qnZZNsS', 'student', 1, '2025-01-20 10:55:38', 'accepted');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `course_tags`
--
ALTER TABLE `course_tags`
  ADD PRIMARY KEY (`course_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`);

--
-- Constraints for table `course_tags`
--
ALTER TABLE `course_tags`
  ADD CONSTRAINT `course_tags_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
