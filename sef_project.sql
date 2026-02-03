-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2026 at 08:02 AM
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
(3, 'PROJECT TEST 1', 'example descriptionnn', '2026-02-19', 'DC0I8Q0F');

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
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `user_id`, `room_code`, `title`, `description`, `file_path`, `status`, `reviewer_feedback`, `submitted_at`) VALUES
(1, 2, 'TEST1234', 'Automated Testing Suite', 'Proposal to implement Selenium for the SQA project.', NULL, 'Under Review', NULL, '2026-02-03 14:33:22'),
(2, 4, 'CYBER99', 'Firewall Protocol Update', 'Security patch for the main perimeter server.', NULL, 'Under Review', NULL, '2026-02-03 14:33:22'),
(3, 5, 'DC0I8Q0F', 'My proposal 1', 'yippee', '1770101182_Screenshot (1).png', 'Under Review', NULL, '2026-02-03 14:46:22');

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
(2, 'John Doe', 'john@example.com', 'pass123', 'Project Lead', 'TEST1234', 'active'),
(3, 'Alice Smith', 'alice@example.com', 'pass123', 'Reviewer', 'CYBER99', 'active'),
(4, 'Sarah Connor', 'sarah@example.com', 'pass123', 'Executive Approver', 'DC0I8Q0F', 'active'),
(5, 'Ali bin Abu', 'Ali@gmail.com', 'User@12345', 'Project Lead', 'DC0I8Q0F', 'active'),
(6, 'DIGI - Alia', 'alia@gmail.com', 'User@12345', 'Project Lead', 'DC0I8Q0F', 'active');

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
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
