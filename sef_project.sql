-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2026 at 10:50 AM
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
  `room_code` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`id`, `title`, `description`, `deadline`, `room_code`) VALUES
(1, 'Software Quality Assurance', 'Testing methods for accurate results', '2026-02-28', 'TEST1234'),
(2, 'Cybersecurity Audit', 'Annual security assessment', '2026-06-15', 'CYBER99'),
(3, 'SEF', 'Prsentation ', '2026-02-19', 'KWUI5WJ1'),
(4, 'IKMALIA\'S ROOM', 'Test done by Ikmalia', '2026-02-26', 'F8HY0JTT');

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
  `scope_of_work` text DEFAULT NULL,
  `budget_amount` decimal(12,2) DEFAULT 0.00,
  `timeline_weeks` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `team_size` int(11) DEFAULT NULL,
  `methodology` varchar(100) DEFAULT NULL,
  `deliverables` text DEFAULT NULL,
  `technical_requirements` text DEFAULT NULL,
  `risk_assessment` text DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('Under Review','Approved','Rejected') DEFAULT 'Under Review',
  `reviewer_feedback` text DEFAULT NULL,
  `reviewer_score` decimal(4,1) DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `user_id`, `room_code`, `title`, `description`, `scope_of_work`, `budget_amount`, `timeline_weeks`, `start_date`, `end_date`, `team_size`, `methodology`, `deliverables`, `technical_requirements`, `risk_assessment`, `payment_terms`, `file_path`, `status`, `reviewer_feedback`, `reviewer_score`, `submitted_at`, `reviewed_at`) VALUES
(1, 2, 'TEST1234', 'Automated Testing Suite', 'Proposal to implement Selenium for the SQA project.', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Under Review', NULL, NULL, '2026-02-03 16:37:19', NULL),
(2, 4, 'CYBER99', 'Firewall Protocol Update', 'Security patch for the main perimeter server.', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Under Review', NULL, NULL, '2026-02-03 16:37:19', NULL),
(3, 2, 'TEST1234', 'Proposal Draft', 'This is a draft', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1770108042_proposal.txt', '', 'Amazing', 8.5, '2026-02-03 16:40:42', '2026-02-03 16:54:43'),
(4, 5, 'KWUI5WJ1', 'Proposal 1', 'Draft 2', 'scopee', 80.00, 67, '2026-02-13', '2026-02-17', 67, 'Waterfall', 'asjbdj', 'badhd', 'sabdjhsb', '30-40-30', '1770454049_Lab6_2530.pdf', 'Approved', NULL, NULL, '2026-02-07 16:47:29', '2026-02-07 16:48:19'),
(5, 5, 'KWUI5WJ1', 'Proposal 2', 'Draft 2', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1770171926_proposal.txt', 'Approved', 'Good', 10.0, '2026-02-04 10:25:26', '2026-02-04 10:26:21'),
(6, 5, 'KWUI5WJ1', 'TEST TEST TEST', 'desc', 'scope', 6800.00, 67, '2026-02-13', '2026-02-18', 67, 'Scrum', 'codee', 'NOde', 'ok', 'Full Upfront', '1770451676_Lab5_2530__1_.pdf', 'Approved', 'Nahh ts bad', 2.0, '2026-02-07 16:07:56', '2026-02-07 17:32:35');

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
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `room_code`, `status`) VALUES
(2, 'John Doe', 'john@example.com', 'pass123', 'Reviewer', 'KWUI5WJ1', 'active'),
(3, 'Alice Smith', 'alice@example.com', 'pass123', 'Executive Approver', 'F8HY0JTT', 'active'),
(4, 'Sarah Connor', 'sarah@example.com', 'pass123', 'Executive Approver', 'KWUI5WJ1', 'active'),
(5, 'TEST', 'test@gmail.com', 'hello123', 'Project Lead', 'KWUI5WJ1', 'active');

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
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_code` (`room_code`);

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
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposals_ibfk_2` FOREIGN KEY (`room_code`) REFERENCES `project` (`room_code`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
