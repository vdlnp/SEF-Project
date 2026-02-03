-- SE Project: Full Unified Schema with Detailed Example Users
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- 1. Setup the Project Table
DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `deadline` date NOT NULL,
  `room_code` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_code` (`room_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Setup the Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `room_code` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Setup the Proposals Table (for the Project Leads)
DROP TABLE IF EXISTS `proposals`;
CREATE TABLE `proposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('Under Review','Approved','Rejected') DEFAULT 'Under Review',
  `reviewer_feedback` text DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- 4. THE EXAMPLES (John Doe Style)
-- ==========================================================
-- Standardized Data for AdminMain.php Logic
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- Clear old data to avoid duplicates
DELETE FROM users;
DELETE FROM project;


-- 1. Ensure the Project Rooms exist
-- These are the "Room Codes" your PL and Reviewer need to see their data
INSERT INTO `project` (`id`, `title`, `description`, `deadline`, `room_code`) VALUES
(1, 'Software Quality Assurance', 'Testing methods for accurate results', '2026-02-28', 'TEST1234'),
(2, 'Cybersecurity Audit', 'Annual security assessment', '2026-06-15', 'CYBER99');

-- 2. Project Lead (PL) Example - John Doe
-- Assigned to Room 'TEST1234'
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `room_code`, `status`) VALUES
(2, 'John Doe', 'john@example.com', 'pass123', 'Project Lead', 'TEST1234', 'active');

-- 3. Reviewer Example - Alice Smith
-- Assigned to Room 'CYBER99'
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `room_code`, `status`) VALUES
(3, 'Alice Smith', 'alice@example.com', 'pass123', 'Reviewer', 'CYBER99', 'active');

-- Add a new pending user for Admin Role Management
INSERT INTO `users` (`name`, `email`, `password`, `role`, `room_code`, `status`) 
VALUES ('Sarah Connor', 'sarah@example.com', 'pass123', NULL, NULL, 'pending');

-- 4. Examples of Proposals for them to interact with
-- This proposal is for John Doe (PL) to see in his "My Submissions"
INSERT INTO `proposals` (`user_id`, `room_code`, `title`, `description`, `status`) VALUES 
(2, 'TEST1234', 'Automated Testing Suite', 'Proposal to implement Selenium for the SQA project.', 'Under Review');

-- This proposal is for Alice Smith (Reviewer) to see in her "Pending Reviews"
INSERT INTO `proposals` (`user_id`, `room_code`, `title`, `description`, `status`) VALUES 
(4, 'CYBER99', 'Firewall Protocol Update', 'Security patch for the main perimeter server.', 'Under Review');

COMMIT;
