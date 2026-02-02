-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 07:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sef_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `deadline` date NOT NULL,
  `room_code` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`id`, `title`, `description`, `deadline`, `room_code`) VALUES
(1, 'Software Quality Assurance', 'Software QA Testing methods for accurate result', '2026-02-19', '34DRWDVB'),
(3, 'Object Oriented Competition', 'Yesss', '2026-04-09', 'B4XH9VDK'),
(4, 'NEW PROJECTTT', 'AAAAAAAAAAAAAAAAA', '2026-02-12', 'MP98JFTC');

-- --------------------------------------------------------

--
-- Table structure for table `proposal`
--

CREATE TABLE `proposal` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('under_review','reviewed','approved','rejected') DEFAULT 'under_review',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposal`
--

INSERT INTO `proposal` (`id`, `project_id`, `lead_id`, `title`, `description`, `deadline`, `status`, `created_at`, `updated_at`) VALUES
(4, 1, 2, 'pls pls ', 'aaaaa', NULL, '', '2026-02-02 16:40:14', '2026-02-02 16:40:14');

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('Under Review','Approved','Rejected') DEFAULT 'Under Review',
  `reviewer_feedback` text DEFAULT NULL,
  `submitted_at` datetime NOT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `user_id`, `room_code`, `title`, `description`, `file_path`, `status`, `reviewer_feedback`, `submitted_at`, `reviewed_at`) VALUES
(1, 5, 'B4XH9VDK', 'FIRST PROPOSAL', 'omg pls yes', '1770054625_Screenshot (1).png', 'Under Review', NULL, '2026-02-03 01:50:25', NULL),
(2, 2, '34DRWDVB', 'SECOND PROPOSAL', 'okay test test', '1770054901_Screenshot 2025-12-01 184058.png', 'Under Review', NULL, '2026-02-03 01:55:01', NULL),
(3, 2, '34DRWDVB', 'OOPS I MEAN FISRT FOR ALI', 'YAYY', '', 'Under Review', NULL, '2026-02-03 01:55:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `proposal_attachments`
--

CREATE TABLE `proposal_attachments` (
  `id` int(11) NOT NULL,
  `proposal_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposal_attachments`
--

INSERT INTO `proposal_attachments` (`id`, `proposal_id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(4, 4, 'Screenshot (1).png', 'uploads/proposals/1770050414_Screenshot (1).png', '2026-02-02 16:40:14');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_reviews`
--

CREATE TABLE `proposal_reviews` (
  `id` int(11) NOT NULL,
  `proposal_id` int(11) DEFAULT NULL,
  `reviewer_id` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `score` decimal(3,1) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `reviewed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposal_reviews`
--

INSERT INTO `proposal_reviews` (`id`, `proposal_id`, `reviewer_id`, `comments`, `score`, `status`, `reviewed_at`) VALUES
(1, 1, 1, 'This is fantastic', 9.0, 'reviewed', '2026-02-02 17:22:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `room_code` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `room_code`, `status`) VALUES
(1, 'Content Coordinator', 'user@admin.com', 'admin123', 'Admin', NULL, 'active'),
(2, 'Ali  bin Abu', 'Ali@gmail.com', 'User@12345', 'Project Lead', '34DRWDVB', 'active'),
(3, 'Arya - PETRONAS', 'Arya@gmail.com', 'user@12345', 'Project Lead', 'B4XH9VDK', 'active'),
(4, 'Thomas', 'Tom@gmail.com', 'User@12345', 'Reviewer', '34DRWDVB', 'active'),
(5, 'CELCOM - Caitlyn', 'cait@gmail.com', 'User@12345', 'Project Lead', 'B4XH9VDK', 'active'),
(6, 'vi', 'vi@gmail.com', 'User@12345', 'Project Lead', 'MP98JFTC', 'active'),
(7, 'Ikma', 'ikma@gmail.com', 'User@12345', 'Project Lead', 'B4XH9VDK', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_code` (`room_code`);

--
-- Indexes for table `proposal`
--
ALTER TABLE `proposal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `lead_id` (`lead_id`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_room_code` (`room_code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `proposal_attachments`
--
ALTER TABLE `proposal_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposal_id` (`proposal_id`);

--
-- Indexes for table `proposal_reviews`
--
ALTER TABLE `proposal_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`proposal_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

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
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `proposal`
--
ALTER TABLE `proposal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `proposal_attachments`
--
ALTER TABLE `proposal_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `proposal_reviews`
--
ALTER TABLE `proposal_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `proposal`
--
ALTER TABLE `proposal`
  ADD CONSTRAINT `proposal_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `proposal_ibfk_2` FOREIGN KEY (`lead_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proposal_attachments`
--
ALTER TABLE `proposal_attachments`
  ADD CONSTRAINT `proposal_attachments_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `proposal` (`id`);

--
-- Constraints for table `proposal_reviews`
--
ALTER TABLE `proposal_reviews`
  ADD CONSTRAINT `proposal_reviews_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `project` (`id`),
  ADD CONSTRAINT `proposal_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
