-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 08, 2026 at 08:03 AM
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
(5, 'E-Learning Platform Enhancement', 'The project aims to improve our existing e-learning platform by adding features such as interactive quizzes, progress tracking, and user-friendly dashboards. The selected team will be responsible for designing, developing, and testing these features within 6 weeks. Strong documentation and version control are required.', '2026-07-22', '0WBLZHLG');

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
(7, 3, '0WBLZHLG', 'Campus Event Management System', 'Develop a web-based system to manage campus events, including registration, scheduling, and notifications. The system will improve event organization and streamline communication between organizers and participants.', 'Design and develop a user-friendly web interface\r\n\r\nImplement event creation, registration, and notification modules\r\n\r\nIntegrate a calendar and reporting system for events\r\n\r\nTest and deploy the system on the university server', 13000.00, 6, '2026-02-12', '2026-03-13', 45, 'Agile', 'Fully functional event management system\r\n\r\nTechnical documentation and user manual\r\n\r\nTested and deployed web application\r\n\r\nSource code in a Git repository', 'Frontend: HTML, CSS, JavaScript (React or Vue)\r\n\r\nBackend: Node.js or PHP with MySQL database\r\n\r\nResponsive design for desktop and mobile\r\n\r\nAuthentication system for admin and users\r\n\r\nVersion control using Git', 'Technical Risk: Delays due to integration issues → Mitigation: Weekly code reviews and testing\r\n\r\nSchedule Risk: Timeline may be affected by unexpected bugs → Mitigation: Allocate buffer time in the project plan\r\n\r\nUser Adoption Risk: Users may find system complex → Mitigation: Provide clear user manual and tutorial', 'Milestone-Based', '1770482287_proposal.txt', 'Approved', NULL, NULL, '2026-02-08 00:38:07', '2026-02-08 01:13:56'),
(8, 3, '0WBLZHLG', 'Smart Library Management System', 'Create a system to manage library resources efficiently, including book checkouts, returns, and digital catalog search. The system aims to reduce manual errors and improve user experience for students and staff.', 'Design database for books, users, and transactions\r\n\r\nImplement book search, checkout, and return modules\r\n\r\nProvide reports on book availability and overdue items\r\n\r\nDeploy system on campus server', 70000.00, 7, '2026-02-27', '2026-04-08', 67, 'Scrum', 'Working library management system\r\n\r\nTechnical documentation and user manual\r\n\r\nSource code repository with version control\r\n\r\nTest reports', 'Frontend: HTML, CSS, JavaScript (React)\r\n\r\nBackend: PHP or Python (Flask/Django) with MySQL\r\n\r\nAuthentication for students and library staff\r\n\r\nMobile-friendly interface', 'Data Loss: Risk of database errors → Mitigation: Daily backups\r\n\r\nUser Error: Students may misuse system → Mitigation: Implement validation & user training\r\n\r\nTime Overrun: Delays due to complex queries → Mitigation: Agile development with weekly milestones', 'Milestone-Based', '1770482665_proposal.txt', 'Rejected', NULL, NULL, '2026-02-08 00:44:25', '2026-02-08 00:47:45'),
(9, 3, '0WBLZHLG', 'Campus Feedback & Survey Platform', 'Develop a platform for collecting feedback and conducting surveys among students and staff. The platform will allow anonymous responses and generate reports for better decision-making.', 'Build survey creation and management module\r\n\r\nEnable anonymous submission and analytics dashboard\r\n\r\nGenerate downloadable reports and visual charts\r\n\r\nDeploy on university intranet', 15000.00, 4, '2026-02-10', '2026-03-05', 34, 'Waterfall', 'Functional survey and feedback platform\r\n\r\nAdmin dashboard with analytics\r\n\r\nDocumentation and user guides\r\n\r\nSource code in Git repository', 'Frontend: HTML, CSS, JS (Vue.js or React)\r\n\r\nBackend: Node.js or PHP with PostgreSQL\r\n\r\nUser authentication and role-based access\r\n\r\nData visualization library for charts (Chart.js / D3.js)', 'Low Participation: Students may not respond → Mitigation: Send reminders & incentives\r\n\r\nData Privacy: Risk of exposing sensitive info → Mitigation: Ensure anonymity and secure storage\r\n\r\nTechnical Issues: Bugs or downtime → Mitigation: Test thoroughly before deployment', 'Full Upfront', '1770482801_proposal.txt', 'Approved', 'Your proposal is well-structured and clearly explains the project scope. However, the technical requirements section could be more detailed regarding the frameworks and tools you plan to use. Please clarify the risk mitigation strategies for potential delays in the timeline.', 7.0, '2026-02-08 00:46:41', '2026-02-08 00:47:37');

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
(2, 'John Doe', 'john@example.com', 'pass123', 'Reviewer', '0WBLZHLG', 'active'),
(3, 'Alice Smith', 'alice@example.com', 'pass123', 'Project Lead', '0WBLZHLG', 'active'),
(4, 'Sarah Connor', 'sarah@example.com', 'pass123', 'Executive Approver', '0WBLZHLG', 'active');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
