-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 10, 2026 at 04:00 AM
-- Server version: 10.11.17-MariaDB-cll-lve
-- PHP Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recruitment_workflow`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_no` varchar(30) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `highest_qualification` varchar(150) DEFAULT NULL,
  `total_experience` decimal(5,2) DEFAULT 0.00,
  `current_company` varchar(150) DEFAULT NULL,
  `current_salary` decimal(12,2) DEFAULT NULL,
  `expected_salary` decimal(12,2) DEFAULT NULL,
  `position_applied` varchar(150) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `source_type` enum('walkin','qr','link','reference') DEFAULT 'walkin',
  `submitted_by_recruiter_id` int(10) UNSIGNED DEFAULT NULL,
  `current_status` varchar(50) NOT NULL DEFAULT 'submitted',
  `final_decision` enum('pending','selected','rejected') DEFAULT 'pending',
  `applied_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `source_reference_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `application_no`, `full_name`, `email`, `phone`, `alternate_phone`, `gender`, `dob`, `address`, `city`, `state`, `pincode`, `highest_qualification`, `total_experience`, `current_company`, `current_salary`, `expected_salary`, `position_applied`, `department`, `source_type`, `submitted_by_recruiter_id`, `current_status`, `final_decision`, `applied_at`, `updated_at`, `source_reference_name`) VALUES
(1, 'APP-20260709-2470', 'Abhay Sharma', 'hr@unire.co.in', '7073067817', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL, 'HR', NULL, '', NULL, 'rejected', 'rejected', '2026-07-09 13:15:09', '2026-07-10 06:34:06', '');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_documents`
--

CREATE TABLE `candidate_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `document_type` enum('resume','photo','id_proof','other') DEFAULT 'resume',
  `original_file_name` varchar(255) NOT NULL,
  `stored_file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate_documents`
--

INSERT INTO `candidate_documents` (`id`, `candidate_id`, `document_type`, `original_file_name`, `stored_file_name`, `file_path`, `file_size`, `uploaded_at`) VALUES
(1, 1, 'resume', 'paximum.docx', '', 'uploads/resumes/1783602909_resume_paximum.docx', NULL, '2026-07-09 13:15:09'),
(2, 1, 'photo', 'WhatsApp Image 2026-06-30 at 3.05.13 PM.jpeg', '', 'uploads/photos/1783602909_photo_WhatsApp_Image_2026-06-30_at_3.05.13_PM.jpeg', NULL, '2026-07-09 13:15:09');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_status_logs`
--

CREATE TABLE `candidate_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `round_id` int(10) UNSIGNED DEFAULT NULL,
  `action_by` int(10) UNSIGNED NOT NULL,
  `action_role` varchar(50) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate_status_logs`
--

INSERT INTO `candidate_status_logs` (`id`, `candidate_id`, `round_id`, `action_by`, `action_role`, `old_status`, `new_status`, `note`, `created_at`) VALUES
(1, 1, 1, 3, 'recruiter', 'submitted', 'sent_to_manager', 'Sent to manager', '2026-07-10 06:31:37'),
(2, 1, 1, 4, 'manager', 'sent_to_manager', 'manager_feedback_received', 'Manager feedback submitted: next_round', '2026-07-10 06:32:25'),
(3, 1, 2, 3, 'recruiter', 'manager_feedback_received', 'sent_to_manager', 'test2', '2026-07-10 06:33:07'),
(4, 1, 2, 4, 'manager', 'sent_to_manager', 'manager_feedback_received', 'Manager feedback submitted: reject', '2026-07-10 06:33:22'),
(5, 1, NULL, 3, 'recruiter', 'manager_feedback_received', 'rejected', 'final', '2026-07-10 06:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `interview_feedback`
--

CREATE TABLE `interview_feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `round_id` int(10) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `manager_id` int(10) UNSIGNED NOT NULL,
  `remark_text` text NOT NULL,
  `recommendation` enum('reject','select','next_round','hold') DEFAULT 'hold',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `interview_feedback`
--

INSERT INTO `interview_feedback` (`id`, `round_id`, `candidate_id`, `manager_id`, `remark_text`, `recommendation`, `created_at`) VALUES
(1, 1, 1, 4, 'test', 'next_round', '2026-07-10 06:32:25'),
(2, 2, 1, 4, 'test', 'reject', '2026-07-10 06:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `interview_rounds`
--

CREATE TABLE `interview_rounds` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `round_no` int(11) NOT NULL,
  `round_name` varchar(100) NOT NULL DEFAULT 'Interview Round',
  `recruiter_id` int(10) UNSIGNED NOT NULL,
  `manager_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  `interview_status` enum('assigned','under_review','feedback_submitted','returned_to_recruiter','closed') DEFAULT 'assigned',
  `scheduled_at` datetime DEFAULT NULL,
  `feedback_submitted_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `interview_rounds`
--

INSERT INTO `interview_rounds` (`id`, `candidate_id`, `round_no`, `round_name`, `recruiter_id`, `manager_id`, `assigned_at`, `interview_status`, `scheduled_at`, `feedback_submitted_at`, `closed_at`) VALUES
(1, 1, 1, 'Round 1', 3, 4, '2026-07-10 06:31:37', 'returned_to_recruiter', '2026-07-10 12:00:00', '2026-07-09 23:32:25', '2026-07-09 23:32:25'),
(2, 1, 2, 'Round 2', 3, 4, '2026-07-10 06:33:07', 'returned_to_recruiter', '2026-07-10 13:04:00', '2026-07-09 23:33:22', '2026-07-09 23:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `resource` varchar(100) NOT NULL,
  `action_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `resource`, `action_name`, `description`, `created_at`) VALUES
(1, 'dashboard', 'view', 'View dashboard', '2026-07-09 12:31:45'),
(2, 'users', 'manage', 'Manage users', '2026-07-09 12:31:45'),
(3, 'candidates', 'create', 'Create candidate', '2026-07-09 12:31:45'),
(4, 'candidates', 'view', 'View candidates', '2026-07-09 12:31:45'),
(5, 'candidates', 'edit', 'Edit candidate', '2026-07-09 12:31:45'),
(6, 'candidates', 'assign_manager', 'Assign manager', '2026-07-09 12:31:45'),
(7, 'candidates', 'reject', 'Reject candidate with reason', '2026-07-09 12:31:45'),
(8, 'candidates', 'select', 'Select candidate', '2026-07-09 12:31:45'),
(9, 'feedback', 'add', 'Add feedback', '2026-07-09 12:31:45'),
(10, 'timeline', 'view', 'View timeline', '2026-07-09 12:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'admin', 'Super user with all access', '2026-07-09 12:31:45'),
(2, 'recruiter', 'Full recruitment workflow access', '2026-07-09 12:31:45'),
(3, 'manager', 'Can review assigned candidates and add remarks', '2026-07-09 12:31:45');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(2, 1),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(3, 1),
(3, 4),
(3, 9),
(3, 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password_changed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `status`, `created_at`, `updated_at`, `password_changed_at`) VALUES
(1, 'admin', 'admin@unire.co.in', '9999999999', '$2y$10$D1ySgmteoDPhAn/kGnHgdOGPO8BaB3ci32KRvWI8MyYleqJ2zDyfi', 'active', '2026-07-09 12:35:11', '2026-07-09 13:01:58', '2026-07-09 06:01:58'),
(3, 'Abhay', 'abhay@unire.co.in', '123456789', '$2y$10$9vUuWNXnD9a9XKr/r/vnY.LYiMncPrk6dAEq62RAybhjT.aQJYWem', 'active', '2026-07-09 13:17:40', '2026-07-09 13:17:40', '2026-07-09 06:17:40'),
(4, 'abhay1', 'abhay1@unire.co.in', '123456789', '$2y$10$Ez5zLSxhuXZ43BiGE1lAq.V84SD76xjZnK0D7VQKDnpXtGkwgwZx6', 'active', '2026-07-10 04:36:15', '2026-07-10 04:36:15', '2026-07-09 21:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`) VALUES
(1, 1, '2026-07-09 13:05:58'),
(3, 2, '2026-07-09 13:17:40'),
(4, 3, '2026-07-10 04:36:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_no` (`application_no`),
  ADD KEY `fk_candidate_recruiter` (`submitted_by_recruiter_id`);

--
-- Indexes for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_doc_candidate` (`candidate_id`);

--
-- Indexes for table `candidate_status_logs`
--
ALTER TABLE `candidate_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_candidate` (`candidate_id`),
  ADD KEY `fk_log_round` (`round_id`),
  ADD KEY `fk_log_user` (`action_by`);

--
-- Indexes for table `interview_feedback`
--
ALTER TABLE `interview_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_feedback_round` (`round_id`),
  ADD KEY `fk_feedback_candidate` (`candidate_id`),
  ADD KEY `fk_feedback_manager` (`manager_id`);

--
-- Indexes for table `interview_rounds`
--
ALTER TABLE `interview_rounds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_candidate_round` (`candidate_id`,`round_no`),
  ADD KEY `fk_round_recruiter` (`recruiter_id`),
  ADD KEY `fk_round_manager` (`manager_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_resource_action` (`resource`,`action_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_rp_permission` (`permission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_ur_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `candidate_status_logs`
--
ALTER TABLE `candidate_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `interview_feedback`
--
ALTER TABLE `interview_feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `interview_rounds`
--
ALTER TABLE `interview_rounds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `fk_candidate_recruiter` FOREIGN KEY (`submitted_by_recruiter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  ADD CONSTRAINT `fk_doc_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_status_logs`
--
ALTER TABLE `candidate_status_logs`
  ADD CONSTRAINT `fk_log_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_round` FOREIGN KEY (`round_id`) REFERENCES `interview_rounds` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`action_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `interview_feedback`
--
ALTER TABLE `interview_feedback`
  ADD CONSTRAINT `fk_feedback_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feedback_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_feedback_round` FOREIGN KEY (`round_id`) REFERENCES `interview_rounds` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interview_rounds`
--
ALTER TABLE `interview_rounds`
  ADD CONSTRAINT `fk_round_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_round_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_round_recruiter` FOREIGN KEY (`recruiter_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
