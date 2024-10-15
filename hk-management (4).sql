-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2024 at 08:07 PM
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
  `user_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `attendance_status` enum('Pending','Approved','Absent') DEFAULT 'Pending',
  `subject` varchar(255) NOT NULL,
  `classroom` varchar(255) NOT NULL,
  `assigned_by` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`user_id`, `date`, `start_time`, `end_time`, `attendance_status`, `subject`, `classroom`, `assigned_by`) VALUES
(16, '2024-10-15', '22:54:00', '22:00:00', 'Pending', 'ITE-314', 'PTC-306', ''),
(19, '2024-10-16', '16:00:00', '18:00:00', 'Pending', 'ITE-314', 'PTC-305', '');

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
(18, 'dasd', 'dsaasd', 'sadsa@gmail.com', 'asdasd', 0, 'HK50', 92, 'active'),
(19, '03-2223-038870', 'Arvin Wayne Guevara Lim', 'argu.lim.up@phinmaed.com', 'BSIT', 0, 'HK100', 150, 'inactive'),
(20, '123456789', 'wow', 'qwe@gmail.com', 'qwe', 0, 'HK100', 150, 'active'),
(21, 'asd', 'asd', 'asdasd@gmail.com', 'asd', 0, 'HK75', 120, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `teacher_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
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
(18, 'dasd', '$2y$10$XkDfjZy1lD0r4JmXXI2lEOyN0yIeN1kjH2fagcGlnrvq5lkjOGgei', 'sadsa@gmail.com', 'student', '2024-10-09 14:38:29'),
(19, '03-2223-038870', '$2y$10$bi0no6oTwrdsl4c.VEGrc.YgcwQ3ivYKeoA7oE9LN9a.BgTQQadXW', 'argu.lim.up@phinmaed.com', 'student', '2024-10-14 08:28:18'),
(20, '123456789', '$2y$10$s4rs34JvnrUIBNPtc3MybeztowSsEsp7oC8lCzbfNuokGzQ6gkvuG', 'qwe@gmail.com', 'student', '2024-10-14 08:31:46'),
(21, 'asd', '$2y$10$2FAmfn6AIT5iLOc7IpiDv.pMRkoP53yfsKamPiDxR3VdN.wSShm8a', 'asdasd@gmail.com', 'student', '2024-10-14 08:39:47'),
(22, '123456', '$2y$10$wuorneurQ5m8tjCHwpclj.6BcStHleXj8VkBCtTR7yquWWwjO4Ulm', 'angelicavidal@gmail.com', 'teacher', '2024-10-14 10:41:14');

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
  ADD PRIMARY KEY (`user_id`);

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
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `timeslots`
--
ALTER TABLE `timeslots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
