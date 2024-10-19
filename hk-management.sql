-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2024 at 03:55 AM
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
-- Database: `hk-management`
--

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
  `assigned_by` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`schedule_id`, `user_id`, `date`, `start_time`, `end_time`, `attendance_status`, `subject`, `classroom`, `assigned_by`) VALUES
(1, 28, '2024-11-03', '12:00:00', '14:00:00', 'Pending', 'ITE314', 'ITS-201', ''),
(2, 32, '2024-11-17', '16:00:00', '18:00:00', 'Present', 'ite353', 'PTC-306', ''),
(3, 30, '2024-11-13', '13:00:00', '15:00:00', 'Present', 'ite-307', 'PTC405', ''),
(4, 35, '2024-10-21', '15:00:00', '17:00:00', 'Absent', 'ite302', 'ITS-200', ''),
(5, 27, '2024-10-22', '09:00:00', '11:00:00', 'Present', 'ITE 314', 'PTC  305', ''),
(6, 30, '2024-10-23', '09:00:00', '11:00:00', 'Present', 'Bar 004', 'MBA 250', ''),
(7, 31, '2024-10-29', '16:00:00', '18:00:00', 'Pending', 'CEE-254', 'MBA515', ''),
(8, 34, '2024-11-09', '13:00:00', '15:00:00', 'Present', 'cee120', 'NH215', ''),
(9, 32, '2024-10-25', '09:00:00', '11:00:00', 'Absent', 'ITE314', 'PTC-403', ''),
(10, 27, '2024-11-16', '10:00:00', '12:00:00', 'Pending', 'ITE383', 'its201', ''),
(11, 27, '2024-11-20', '09:00:00', '11:00:00', 'Present', 'ITE314', 'ITS-201', ''),
(12, 28, '2024-11-23', '13:00:00', '15:00:00', 'Absent', 'ITE302', 'PTC-306', ''),
(13, 29, '2024-11-10', '14:00:00', '16:00:00', 'Pending', 'CEE-254', 'MBA-250', ''),
(14, 30, '2024-11-06', '10:00:00', '12:00:00', 'Present', 'Bar 004', 'PTC-305', ''),
(15, 31, '2024-11-05', '15:00:00', '17:00:00', 'Absent', 'ITE383', 'NH215', ''),
(16, 32, '2024-10-31', '16:00:00', '18:00:00', 'Pending', 'ITE353', 'ITS-101', ''),
(17, 33, '2024-11-09', '08:00:00', '10:00:00', 'Present', 'CEE120', 'PTC-403', ''),
(18, 34, '2024-11-12', '12:00:00', '14:00:00', 'Absent', 'ITE314', 'MBA515', ''),
(19, 35, '2024-11-18', '10:00:00', '12:00:00', 'Present', 'ITE307', 'Lab-102', ''),
(20, 36, '2024-11-25', '09:00:00', '11:00:00', 'Pending', 'ITE302', 'ITS-201', ''),
(21, 37, '2024-11-27', '10:00:00', '12:00:00', 'Absent', 'ITE383', 'NH215', ''),
(22, 38, '2024-12-01', '15:00:00', '17:00:00', 'Present', 'ITE353', 'PTC-306', ''),
(23, 39, '2024-12-03', '08:00:00', '10:00:00', 'Pending', 'Bar 004', 'MBA-250', ''),
(24, 27, '2024-12-05', '13:00:00', '15:00:00', 'Present', 'ITE307', 'PTC-403', ''),
(25, 28, '2024-12-07', '09:00:00', '11:00:00', 'Absent', 'CEE-254', 'ITS-101', ''),
(26, 29, '2024-12-10', '10:00:00', '12:00:00', 'Pending', 'ITE302', 'MBA-250', ''),
(27, 30, '2024-12-12', '14:00:00', '16:00:00', 'Present', 'ITE383', 'PTC-305', ''),
(28, 31, '2024-12-15', '08:00:00', '10:00:00', 'Absent', 'ITE314', 'NH215', ''),
(29, 32, '2024-12-17', '12:00:00', '14:00:00', 'Present', 'ITE353', 'Lab-102', ''),
(30, 33, '2024-12-19', '15:00:00', '17:00:00', 'Pending', 'CEE120', 'PTC-306', ''),
(31, 34, '2024-12-22', '09:00:00', '11:00:00', 'Present', 'Bar 004', 'MBA-515', ''),
(32, 35, '2024-12-23', '16:00:00', '18:00:00', 'Absent', 'ITE302', 'ITS-201', ''),
(33, 36, '2024-12-25', '10:00:00', '12:00:00', 'Pending', 'ITE307', 'PTC-305', ''),
(34, 37, '2024-12-27', '13:00:00', '15:00:00', 'Present', 'ITE383', 'NH215', ''),
(35, 38, '2024-12-29', '08:00:00', '10:00:00', 'Absent', 'CEE120', 'Lab-102', ''),
(36, 39, '2024-12-30', '14:00:00', '16:00:00', 'Present', 'ITE314', 'PTC-403', ''),
(37, 27, '2024-11-29', '13:00:00', '15:00:00', 'Present', 'ITE302', 'PTC-305', ''),
(38, 28, '2024-11-30', '10:00:00', '12:00:00', 'Absent', 'ITE383', 'MBA-515', ''),
(39, 29, '2024-12-01', '08:00:00', '10:00:00', 'Pending', 'CEE120', 'ITS-201', ''),
(40, 30, '2024-12-02', '14:00:00', '16:00:00', 'Present', 'ITE307', 'PTC-403', ''),
(41, 31, '2024-12-03', '09:00:00', '11:00:00', 'Present', 'ITE314', 'NH215', ''),
(42, 32, '2024-12-04', '12:00:00', '14:00:00', 'Absent', 'ITE353', 'PTC-306', ''),
(43, 33, '2024-12-05', '16:00:00', '18:00:00', 'Pending', 'Bar 004', 'MBA-250', ''),
(44, 34, '2024-12-06', '10:00:00', '12:00:00', 'Present', 'ITE302', 'Lab-102', ''),
(45, 35, '2024-12-07', '09:00:00', '11:00:00', 'Absent', 'CEE254', 'PTC-305', ''),
(46, 36, '2024-12-08', '13:00:00', '15:00:00', 'Present', 'ITE307', 'NH215', ''),
(47, 37, '2024-12-09', '15:00:00', '17:00:00', 'Pending', 'ITE314', 'PTC-403', ''),
(48, 38, '2024-12-10', '08:00:00', '10:00:00', 'Present', 'ITE353', 'ITS-101', ''),
(49, 39, '2024-12-11', '10:00:00', '12:00:00', 'Absent', 'ITE383', 'PTC-306', ''),
(50, 27, '2024-12-13', '12:00:00', '14:00:00', 'Present', 'ITE307', 'PTC-305', ''),
(51, 28, '2024-12-15', '10:00:00', '12:00:00', 'Absent', 'ITE302', 'ITS-201', ''),
(52, 29, '2024-12-17', '08:00:00', '10:00:00', 'Pending', 'CEE254', 'MBA-250', ''),
(53, 30, '2024-12-19', '14:00:00', '16:00:00', 'Present', 'ITE383', 'PTC-306', ''),
(54, 31, '2024-12-20', '09:00:00', '11:00:00', 'Present', 'ITE314', 'NH215', ''),
(55, 32, '2024-12-22', '16:00:00', '18:00:00', 'Absent', 'ITE353', 'PTC-403', ''),
(56, 33, '2024-12-23', '10:00:00', '12:00:00', 'Pending', 'Bar 004', 'Lab-102', ''),
(57, 34, '2024-12-25', '13:00:00', '15:00:00', 'Present', 'CEE120', 'MBA-515', ''),
(58, 35, '2024-12-26', '08:00:00', '10:00:00', 'Absent', 'ITE302', 'PTC-305', ''),
(59, 36, '2024-12-27', '09:00:00', '11:00:00', 'Present', 'ITE307', 'ITS-101', ''),
(60, 37, '2024-12-29', '15:00:00', '17:00:00', 'Pending', 'CEE254', 'PTC-403', ''),
(61, 38, '2024-12-30', '12:00:00', '14:00:00', 'Absent', 'ITE383', 'NH215', ''),
(62, 39, '2024-12-31', '10:00:00', '12:00:00', 'Present', 'ITE314', 'PTC-306', '');

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
(27, '03-1234-5678', 'John Doe', 'jodo.up@phinmaed.com', 'BSIT', 3, 'HK25', 25, 'active'),
(28, '03-2345-6789', 'Jane Smith', 'jasm.up@phinmaed.com', 'BSCS', 4, 'HK50', 90, 'active'),
(29, '03-3456-7890', 'Alice Johnson', 'aljo.up@phinmaed.com', 'BSN', 2, 'HK25', 25, 'active'),
(30, '03-4567-8901', 'Bob Brown', 'bobr.up@phinmaed.com', 'BSCE', 1, 'HK100', 150, 'active'),
(31, '03-5678-9012', 'Charlie Davis', 'chda.up@phinmaed.com', 'BSIT', 5, 'HK25', 25, 'active'),
(32, '03-6789-0123', 'David Evans', 'daev.up@phinmaed.com', 'BSCS', 3, 'HK50', 90, 'active'),
(33, '03-7890-1234', 'ellen walker', 'elwa.up@phinmaed.com', 'bsar', 4, 'HK25', 25, 'active'),
(34, '03-8901-2345', 'Frank harris', 'frha.up@phinmaed.com', 'Bsce', 2, 'HK100', 150, 'active'),
(35, 'Grace Lee', 'Grace Lee', 'grle.up@phinmaed.com', 'BSIT', 2, 'HK25', 25, 'active'),
(36, '03-2021-01625', 'Richa andrea aliado', 'rica.aliado.up@phinmaed.com', 'bsit', 3, 'HK25', 25, 'active'),
(37, '09-2223-039845', 'Kristel Jeanne BAutista', 'krid.bautista.up@phinmaed.com', 'BSIT', 3, 'HK50', 90, 'active'),
(38, '03-1718-02852', 'Aaron James Christopher Christian Macaraeg ', 'chmo.aquino.up@phinmaed.com', 'BSIT', 3, 'HK25', 25, 'active'),
(39, '03-1234-56789', 'Jane Doe', 'jado.doe.up@phinmaed.com', 'BSAR', 3, 'HK75', 120, 'active');

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
(37, '09-2223-039845', '$2y$10$07pwva8.LQMHNLnOOKuAHe8QW5/UewzRq86w9nN3CP/budKoyyx0a', 'krid.bautista.up@phinmaed.com', 'student', '2024-10-18 23:19:40'),
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
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

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
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
