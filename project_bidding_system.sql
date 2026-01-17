-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2026 at 07:23 AM
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
-- Database: `project_bidding_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `Comment_ID` int(11) NOT NULL,
  `Reviewer_ID` int(11) DEFAULT NULL,
  `Proposal_ID` int(11) DEFAULT NULL,
  `Project_Lead_ID` int(11) DEFAULT NULL,
  `Comment_Date` datetime DEFAULT NULL,
  `Comment_Text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_coordinator`
--

CREATE TABLE `content_coordinator` (
  `Admin_ID` int(11) NOT NULL,
  `Role_ID` int(11) DEFAULT NULL,
  `Admin_Name` varchar(100) DEFAULT NULL,
  `Admin_Email` varchar(150) DEFAULT NULL,
  `Admin_Password` varchar(255) DEFAULT NULL,
  `Room_Code` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `executive_approver`
--

CREATE TABLE `executive_approver` (
  `Approver_ID` int(11) NOT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  `Role_ID` int(11) DEFAULT NULL,
  `Approver_Name` varchar(100) DEFAULT NULL,
  `Approver_Email` varchar(150) DEFAULT NULL,
  `Approver_Password` varchar(255) DEFAULT NULL,
  `Room_Code` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_lead`
--

CREATE TABLE `project_lead` (
  `Project_Lead_ID` int(11) NOT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  `Role_ID` int(11) DEFAULT NULL,
  `Company_Name` varchar(150) DEFAULT NULL,
  `Project_Lead_Email` varchar(150) DEFAULT NULL,
  `Project_Lead_Password` varchar(255) DEFAULT NULL,
  `Room_Code` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_openings`
--

CREATE TABLE `project_openings` (
  `Opening_ID` int(11) NOT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  `Title` varchar(150) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal`
--

CREATE TABLE `proposal` (
  `Proposal_ID` int(11) NOT NULL,
  `Project_Lead_ID` int(11) DEFAULT NULL,
  `Approver_ID` int(11) DEFAULT NULL,
  `Opening_ID` int(11) DEFAULT NULL,
  `Title` varchar(150) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Submitted_By` varchar(100) DEFAULT NULL,
  `Submitted_Date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviewer`
--

CREATE TABLE `reviewer` (
  `Reviewer_ID` int(11) NOT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  `Comment_ID` int(11) DEFAULT NULL,
  `Role_ID` int(11) DEFAULT NULL,
  `Review_Comment` text DEFAULT NULL,
  `Review_Date` datetime DEFAULT NULL,
  `Reviewer_Name` varchar(100) DEFAULT NULL,
  `Reviewer_Email` varchar(150) DEFAULT NULL,
  `Reviewer_Password` varchar(255) DEFAULT NULL,
  `Room_Code` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `Role_ID` int(11) NOT NULL,
  `Role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`Role_ID`, `Role_name`) VALUES
(1, 'Admin'),
(2, 'Project Lead'),
(3, 'Reviewer'),
(4, 'Executive Approver');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`Comment_ID`),
  ADD KEY `Proposal_ID` (`Proposal_ID`),
  ADD KEY `Project_Lead_ID` (`Project_Lead_ID`);

--
-- Indexes for table `content_coordinator`
--
ALTER TABLE `content_coordinator`
  ADD PRIMARY KEY (`Admin_ID`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- Indexes for table `executive_approver`
--
ALTER TABLE `executive_approver`
  ADD PRIMARY KEY (`Approver_ID`),
  ADD KEY `Admin_ID` (`Admin_ID`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- Indexes for table `project_lead`
--
ALTER TABLE `project_lead`
  ADD PRIMARY KEY (`Project_Lead_ID`),
  ADD KEY `Admin_ID` (`Admin_ID`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- Indexes for table `project_openings`
--
ALTER TABLE `project_openings`
  ADD PRIMARY KEY (`Opening_ID`),
  ADD KEY `Admin_ID` (`Admin_ID`);

--
-- Indexes for table `proposal`
--
ALTER TABLE `proposal`
  ADD PRIMARY KEY (`Proposal_ID`),
  ADD KEY `Project_Lead_ID` (`Project_Lead_ID`),
  ADD KEY `Approver_ID` (`Approver_ID`),
  ADD KEY `Opening_ID` (`Opening_ID`);

--
-- Indexes for table `reviewer`
--
ALTER TABLE `reviewer`
  ADD PRIMARY KEY (`Reviewer_ID`),
  ADD KEY `Admin_ID` (`Admin_ID`),
  ADD KEY `Comment_ID` (`Comment_ID`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`Role_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `Comment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_coordinator`
--
ALTER TABLE `content_coordinator`
  MODIFY `Admin_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `executive_approver`
--
ALTER TABLE `executive_approver`
  MODIFY `Approver_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_lead`
--
ALTER TABLE `project_lead`
  MODIFY `Project_Lead_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_openings`
--
ALTER TABLE `project_openings`
  MODIFY `Opening_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposal`
--
ALTER TABLE `proposal`
  MODIFY `Proposal_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviewer`
--
ALTER TABLE `reviewer`
  MODIFY `Reviewer_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `Role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`Proposal_ID`) REFERENCES `proposal` (`Proposal_ID`),
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`Project_Lead_ID`) REFERENCES `project_lead` (`Project_Lead_ID`);

--
-- Constraints for table `content_coordinator`
--
ALTER TABLE `content_coordinator`
  ADD CONSTRAINT `content_coordinator_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `role` (`Role_ID`);

--
-- Constraints for table `executive_approver`
--
ALTER TABLE `executive_approver`
  ADD CONSTRAINT `executive_approver_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `content_coordinator` (`Admin_ID`),
  ADD CONSTRAINT `executive_approver_ibfk_2` FOREIGN KEY (`Role_ID`) REFERENCES `role` (`Role_ID`);

--
-- Constraints for table `project_lead`
--
ALTER TABLE `project_lead`
  ADD CONSTRAINT `project_lead_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `content_coordinator` (`Admin_ID`),
  ADD CONSTRAINT `project_lead_ibfk_2` FOREIGN KEY (`Role_ID`) REFERENCES `role` (`Role_ID`);

--
-- Constraints for table `project_openings`
--
ALTER TABLE `project_openings`
  ADD CONSTRAINT `project_openings_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `content_coordinator` (`Admin_ID`);

--
-- Constraints for table `proposal`
--
ALTER TABLE `proposal`
  ADD CONSTRAINT `proposal_ibfk_1` FOREIGN KEY (`Project_Lead_ID`) REFERENCES `project_lead` (`Project_Lead_ID`),
  ADD CONSTRAINT `proposal_ibfk_2` FOREIGN KEY (`Approver_ID`) REFERENCES `executive_approver` (`Approver_ID`),
  ADD CONSTRAINT `proposal_ibfk_3` FOREIGN KEY (`Opening_ID`) REFERENCES `project_openings` (`Opening_ID`);

--
-- Constraints for table `reviewer`
--
ALTER TABLE `reviewer`
  ADD CONSTRAINT `reviewer_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `content_coordinator` (`Admin_ID`),
  ADD CONSTRAINT `reviewer_ibfk_2` FOREIGN KEY (`Comment_ID`) REFERENCES `comment` (`Comment_ID`),
  ADD CONSTRAINT `reviewer_ibfk_3` FOREIGN KEY (`Role_ID`) REFERENCES `role` (`Role_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
