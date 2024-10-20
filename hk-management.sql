-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2024 at 08:45 AM
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
-- Database: `admin`
--
CREATE DATABASE IF NOT EXISTS `admin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `admin`;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `Course` varchar(255) DEFAULT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `hk_number` varchar(64) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `user` (`id`);
--
-- Database: `hk-management`
--
CREATE DATABASE IF NOT EXISTS `hk-management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hk-management`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `admin_id`, `name`, `email`, `profile_pic`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', 'admin', 'admin@example.com', NULL, '2024-10-08 10:31:28', '2024-10-08 10:46:14');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `schedule_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `attendance_status` enum('Pending','Present','Absent') DEFAULT 'Pending',
  `subject` varchar(255) NOT NULL,
  `classroom` varchar(255) NOT NULL,
  `assigned_by` varchar(64) NOT NULL,
  `total_duration` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`schedule_id`, `user_id`, `date`, `start_time`, `end_time`, `attendance_status`, `subject`, `classroom`, `assigned_by`, `total_duration`) VALUES
(63, 36, '2024-10-19', '15:00:00', '17:00:00', 'Present', 'ITE314', 'PTC307', '', ''),
(64, 36, '2024-10-19', '16:00:00', '18:00:00', 'Absent', 'ITE315', 'PTC306', '', ''),
(65, 37, '2024-10-20', '15:00:00', '17:30:00', 'Present', 'ITE314', 'PTC306', 'Angelica Vidal', ''),
(66, 37, '2024-10-19', '16:30:00', '18:00:00', 'Present', 'ITE314', 'ITS201', 'Angelica Vidal', ''),
(72, 39, '2024-10-20', '15:00:00', '16:30:00', 'Present', 'ITE314', 'ITS201', 'Angelica Vidal', ''),
(73, 27, '2024-10-20', '16:00:00', '17:00:00', 'Present', 'ITE314', 'ITS201', 'Angelica Vidal', '1'),
(74, 32, '2024-10-20', '13:30:00', '15:00:00', 'Present', 'ITE314', 'ITS201', 'Angelica Vidal', '1.5');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_logs`
--

CREATE TABLE `schedule_logs` (
  `log_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_field` varchar(255) NOT NULL,
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_logs`
--

INSERT INTO `schedule_logs` (`log_id`, `schedule_id`, `changed_by`, `changed_field`, `old_value`, `new_value`, `timestamp`) VALUES
(1, 65, 37, 'classroom', 'PTC303', 'PTC306', '2024-10-19 20:14:57'),
(2, 65, 37, 'date', '2024-10-19', '2024-10-20', '2024-10-19 20:41:50'),
(3, 65, 37, 'end_time', '17:00:00', '16:30', '2024-10-20 00:35:12'),
(4, 65, 37, 'end_time', '16:30:00', '17:30', '2024-10-20 01:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `user_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `hk_status` enum('HK25','HK50','HK75','HK100') DEFAULT NULL,
  `total_hours` decimal(64,0) NOT NULL,
  `status` enum('active','inactive','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`user_id`, `student_id`, `name`, `email`, `course`, `level`, `hk_status`, `total_hours`, `status`) VALUES
(27, '03-1234-5678', 'John Doe', 'jodo.up@phinmaed.com', 'BSIT', 3, 'HK25', -7, 'active'),
(28, '03-2345-6789', 'Jane Smith', 'jasm.up@phinmaed.com', 'BSCS', 4, 'HK50', 90, 'active'),
(29, '03-3456-7890', 'Alice Johnson', 'aljo.up@phinmaed.com', 'BSN', 2, 'HK25', 25, 'active'),
(30, '03-4567-8901', 'Bob Brown', 'bobr.up@phinmaed.com', 'BSCE', 1, 'HK100', 150, 'active'),
(31, '03-5678-9012', 'Charlie Davis', 'chda.up@phinmaed.com', 'BSIT', 5, 'HK25', 25, 'active'),
(32, '03-6789-0123', 'David Evans', 'daev.up@phinmaed.com', 'BSCS', 3, 'HK50', 39, 'active'),
(33, '03-7890-1234', 'ellen walker', 'elwa.up@phinmaed.com', 'bsar', 4, 'HK25', 25, 'active'),
(34, '03-8901-2345', 'Frank harris', 'frha.up@phinmaed.com', 'Bsce', 2, 'HK100', 150, 'active'),
(35, 'Grace Lee', 'Grace Lee', 'grle.up@phinmaed.com', 'BSIT', 2, 'HK25', 25, 'active'),
(36, '03-2021-01625', 'Richa andrea aliado', 'rica.aliado.up@phinmaed.com', 'bsit', 3, 'HK25', 16, 'active'),
(37, '09-2223-039845', 'Kristel Jeanne BAutista', 'krid.bautista.up@phinmaed.com', 'BSIT', 3, 'HK50', 7, 'active'),
(38, '03-1718-02852', 'Aaron James Christopher Christian Macaraeg ', 'chmo.aquino.up@phinmaed.com', 'BSIT', 3, 'HK25', 25, 'active'),
(39, '03-1234-56789', 'Jane Doe', 'jado.doe.up@phinmaed.com', 'BSAR', 3, 'HK75', 0, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `teacher_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` enum('CITE','CELA','CAS','CMA','CEA','CAHS','CCJE') NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `teacher_id`, `name`, `department`, `user_id`) VALUES
(2, 'teacher', 'teacher', 'CITE', 9),
(3, '123456', 'Angelica Vidal', 'CITE', 22);

-- --------------------------------------------------------

--
-- Table structure for table `timeslots`
--

CREATE TABLE `timeslots` (
  `id` int(11) NOT NULL,
  `time` time NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeslots`
--

INSERT INTO `timeslots` (`id`, `time`, `start_time`, `end_time`) VALUES
(1, '07:30:00', '00:00:00', '00:00:00'),
(2, '08:00:00', '00:00:00', '00:00:00'),
(3, '08:30:00', '00:00:00', '00:00:00'),
(4, '09:00:00', '00:00:00', '00:00:00'),
(5, '09:30:00', '00:00:00', '00:00:00'),
(6, '10:00:00', '00:00:00', '00:00:00'),
(7, '10:30:00', '00:00:00', '00:00:00'),
(8, '11:00:00', '00:00:00', '00:00:00'),
(9, '11:30:00', '00:00:00', '00:00:00'),
(10, '12:00:00', '00:00:00', '00:00:00'),
(11, '12:30:00', '00:00:00', '00:00:00'),
(12, '13:00:00', '00:00:00', '00:00:00'),
(13, '13:30:00', '00:00:00', '00:00:00'),
(14, '14:00:00', '00:00:00', '00:00:00'),
(15, '14:30:00', '00:00:00', '00:00:00'),
(16, '15:00:00', '00:00:00', '00:00:00'),
(17, '15:30:00', '00:00:00', '00:00:00'),
(18, '16:00:00', '00:00:00', '00:00:00'),
(19, '16:30:00', '00:00:00', '00:00:00'),
(20, '17:00:00', '00:00:00', '00:00:00'),
(21, '17:30:00', '00:00:00', '00:00:00'),
(22, '18:00:00', '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$uollR6GrtowrUVTgR00S6.Mg4R/uGEJEBNXZHzu0.Cu6omoZEAAIe', 'admin@example.com', 'admin', '2024-10-08 06:13:58'),
(2, 'student', '$2y$10$l6At7grxha4OSPlPYdCIx.ROxqbeOI5hj9R0XJIhUS9vtMEqt8XaW', 'student@example.com', 'student', '2024-10-08 06:13:58'),
(9, 'teacher', '$2y$10$lNiX2oF0m5XT1CXf3MPxf.HB6Z2DYUtnRgHoO5oAej5DskjigMPVi', 'teacher@example.com', 'teacher', '2024-10-08 11:41:01'),
(22, '123456', '$2y$10$wuorneurQ5m8tjCHwpclj.6BcStHleXj8VkBCtTR7yquWWwjO4Ulm', 'angelicavidal@gmail.com', 'teacher', '2024-10-14 10:41:14'),
(27, '03-1234-5678', '$2y$10$nm7Z/vCQ7wekZswcJI0HfudDb9z.gkfKxR7ZeyA7m0C23nWjaKNOO', 'jodo.up@phinmaed.com', 'student', '2024-10-18 16:44:09'),
(28, '03-2345-6789', '$2y$10$B7TKHC29lpm6MfxPL7o3LOv6pBMtQ0EHPgmqG312RdIRmdKro4mHG', 'jasm.up@phinmaed.com', 'student', '2024-10-18 16:44:53'),
(29, '03-3456-7890', '$2y$10$TNmytoeQ.Z.Uuf2EzekXVO01Q13e2izweaWEFsEFTmfIb2zukoXFq', 'aljo.up@phinmaed.com', 'student', '2024-10-18 16:45:41'),
(30, '03-4567-8901', '$2y$10$ETE51rwjj/PDNZhspnNGbe/9kEzVx3QAFuqQscHsv9cEpTfu8EjNW', 'bobr.up@phinmaed.com', 'student', '2024-10-18 16:46:17'),
(31, '03-5678-9012', '$2y$10$pM9t1jtH2nTumKLHHrpi2u/997Ut386AiTzdjsdZw8HNn4V48AzcC', 'chda.up@phinmaed.com', 'student', '2024-10-18 16:46:59'),
(32, '03-6789-0123', '$2y$10$zu7xjQDt5MQNvBPWabmCv.olzlGkIVg8qefWzoXaEmc.FU.VpWSGe', 'daev.up@phinmaed.com', 'student', '2024-10-18 16:48:02'),
(33, '03-7890-1234', '$2y$10$vlN7Dqou8PiaR3Tc9QK5bOQz7pOJLTxfzH1axily45HwrEz6J7Xfy', 'elwa.up@phinmaed.com', 'student', '2024-10-18 16:51:11'),
(34, '03-8901-2345', '$2y$10$JjIrqABAmvUBF1ugyx2GjOV9fG4WzUR9C8Tz2NXkr1CKXwncB9ulq', 'frha.up@phinmaed.com', 'student', '2024-10-18 16:52:09'),
(35, 'Grace Lee', '$2y$10$4YWM/KDaImIqllqDSVXTwu53At..nuB0JO/Yeud5.p2hdmHclAMZu', 'grle.up@phinmaed.com', 'student', '2024-10-18 16:53:00'),
(36, '03-2021-01625', '$2y$10$TRczWI48g3pZrmssGdBNQuWhOEdfn0pnfixqK68vXwCkB.ayEIsUe', 'rica.aliado.up@phinmaed.com', 'student', '2024-10-18 23:15:06'),
(37, '09-2223-039845', '$2y$10$o6Yg7VCdkCsuO63y1Vql9e7MFtfYWIW.4/sb5R4d12Y3gdKALAwMS', 'krid.bautista.up@phinmaed.com', 'student', '2024-10-18 23:19:40'),
(38, '03-1718-02852', '$2y$10$FvBNknrIdEwuqhs2MyDgy.DP9XrZjiUe9Ogry74/xso4hwUBQqW22', 'chmo.aquino.up@phinmaed.com', 'student', '2024-10-18 23:25:03'),
(39, '03-1234-56789', '$2y$10$RZ3imXE3QgEjC24C.4Wg3O9GEpKLQqAPJS1FQ4oS0hXLgqMAGl2mi', 'jado.doe.up@phinmaed.com', 'student', '2024-10-18 23:26:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `schedule_logs`
--
ALTER TABLE `schedule_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `timeslots`
--
ALTER TABLE `timeslots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `schedule_logs`
--
ALTER TABLE `schedule_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `timeslots`
--
ALTER TABLE `timeslots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_logs`
--
ALTER TABLE `schedule_logs`
  ADD CONSTRAINT `schedule_logs_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`schedule_id`),
  ADD CONSTRAINT `schedule_logs_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `students` (`user_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
--
-- Database: `login_db`
--
CREATE DATABASE IF NOT EXISTS `login_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `login_db`;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `studentno` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `email`, `studentno`, `password`, `created_at`) VALUES
(1, 'realtbnrfrags20@gmail.com', '03-2223-038870', '$2y$10$jZY4wBS86sr.PbIbvhVPzeaxRdfkXt/iQNzaAKSw3KBvmLYQFuMwu', '2024-10-02 09:12:21'),
(2, 'limsanity2073@gmail.com', '03-2223-038871', '$2y$10$FSX2tbNc2P9uB3D75yGD6OhGsraetYXI3ka4XHi2t8S2GkhCoQblK', '2024-10-02 09:19:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `student_no` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `studentno` (`studentno`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_no` (`student_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"hk-management\",\"table\":\"schedule\"},{\"db\":\"hk-management\",\"table\":\"students\"},{\"db\":\"hk-management\",\"table\":\"users\"},{\"db\":\"hk-management\",\"table\":\"schedule_logs\"},{\"db\":\"hk-management\",\"table\":\"notifications\"},{\"db\":\"hk-management\",\"table\":\"timeslots\"},{\"db\":\"hk-management\",\"table\":\"admins\"},{\"db\":\"hk-management\",\"table\":\"teachers\"},{\"db\":\"login_db\",\"table\":\"users\"},{\"db\":\"hk-management\",\"table\":\"Schedule\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

--
-- Dumping data for table `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'hk-management', 'schedule', '{\"sorted_col\":\"`schedule`.`total_duration` ASC\"}', '2024-10-19 17:13:01'),
('root', 'hk-management', 'students', '{\"sorted_col\":\"`hk_status` DESC\"}', '2024-10-19 16:58:14'),
('root', 'hk-management', 'teachers', '{\"sorted_col\":\"`teachers`.`name` ASC\"}', '2024-10-11 12:39:06'),
('root', 'hk-management', 'users', '[]', '2024-10-14 09:47:53'),
('root', 'login_db', 'user', '[]', '2024-10-02 07:56:08'),
('root', 'scheduling', 'students', '{\"sorted_col\":\"`students`.`status` ASC\"}', '2024-10-09 13:40:19');

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2024-10-20 06:44:45', '{\"Console\\/Mode\":\"collapse\",\"NavigationWidth\":218,\"Console\\/Height\":0}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Database: `scheduling`
--
CREATE DATABASE IF NOT EXISTS `scheduling` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `scheduling`;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `Course` varchar(255) DEFAULT NULL,
  `year_level` varchar(255) DEFAULT NULL,
  `hk_number` varchar(64) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `student_id` varchar(64) NOT NULL,
  `total_hours` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timeslots`
--

CREATE TABLE `timeslots` (
  `id` int(11) NOT NULL,
  `time` time NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeslots`
--

INSERT INTO `timeslots` (`id`, `time`, `start_time`, `end_time`) VALUES
(1, '07:30:00', '00:00:00', '00:00:00'),
(2, '08:00:00', '00:00:00', '00:00:00'),
(3, '08:30:00', '00:00:00', '00:00:00'),
(4, '09:00:00', '00:00:00', '00:00:00'),
(5, '09:30:00', '00:00:00', '00:00:00'),
(6, '10:00:00', '00:00:00', '00:00:00'),
(7, '10:30:00', '00:00:00', '00:00:00'),
(8, '11:00:00', '00:00:00', '00:00:00'),
(9, '11:30:00', '00:00:00', '00:00:00'),
(10, '12:00:00', '00:00:00', '00:00:00'),
(11, '12:30:00', '00:00:00', '00:00:00'),
(12, '13:00:00', '00:00:00', '00:00:00'),
(13, '13:30:00', '00:00:00', '00:00:00'),
(14, '14:00:00', '00:00:00', '00:00:00'),
(15, '14:30:00', '00:00:00', '00:00:00'),
(16, '15:00:00', '00:00:00', '00:00:00'),
(17, '15:30:00', '00:00:00', '00:00:00'),
(18, '16:00:00', '00:00:00', '00:00:00'),
(19, '16:30:00', '00:00:00', '00:00:00'),
(20, '17:00:00', '00:00:00', '00:00:00'),
(21, '17:30:00', '00:00:00', '00:00:00'),
(22, '18:00:00', '00:00:00', '00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timeslots`
--
ALTER TABLE `timeslots`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timeslots`
--
ALTER TABLE `timeslots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- Database: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_datetime`) VALUES
(1, '2024-10-07 09:20:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Database: `user_management`
--
CREATE DATABASE IF NOT EXISTS `user_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `user_management`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin','instructor') NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
