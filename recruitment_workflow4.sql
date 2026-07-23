-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 23, 2026 at 12:34 AM
-- Server version: 10.11.18-MariaDB-cll-lve
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
  `form_last_updated_by` int(10) UNSIGNED DEFAULT NULL,
  `form_last_updated_role` varchar(50) DEFAULT NULL,
  `form_last_updated_at` timestamp NULL DEFAULT NULL,
  `source_reference_name` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `father_husband_name` varchar(150) DEFAULT NULL,
  `emergency_no` varchar(30) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `scheduled_exam` varchar(255) DEFAULT NULL,
  `career_goals` text DEFAULT NULL,
  `interest_in_field` text DEFAULT NULL,
  `notice_period` varchar(100) DEFAULT NULL,
  `notice_period_specify` varchar(255) DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `weakness` text DEFAULT NULL,
  `epf_registered` varchar(10) DEFAULT NULL,
  `uan_no` varchar(50) DEFAULT NULL,
  `esic_registered` varchar(10) DEFAULT NULL,
  `ip_no` varchar(50) DEFAULT NULL,
  `aadhaar_no` varchar(30) DEFAULT NULL,
  `pan_no` varchar(20) DEFAULT NULL,
  `bank_account_no` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `computer_knowledge` text DEFAULT NULL,
  `weekly_working_days` varchar(10) DEFAULT NULL,
  `medical_issue` varchar(255) DEFAULT NULL,
  `smoking` varchar(10) DEFAULT NULL,
  `self_vehicle` varchar(10) DEFAULT NULL,
  `driving_licence` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `application_no`, `full_name`, `email`, `phone`, `alternate_phone`, `gender`, `dob`, `address`, `city`, `state`, `pincode`, `highest_qualification`, `total_experience`, `current_company`, `current_salary`, `expected_salary`, `position_applied`, `department`, `source_type`, `submitted_by_recruiter_id`, `current_status`, `final_decision`, `applied_at`, `updated_at`, `form_last_updated_by`, `form_last_updated_role`, `form_last_updated_at`, `source_reference_name`, `photo_path`, `resume_path`, `father_husband_name`, `emergency_no`, `age`, `marital_status`, `permanent_address`, `scheduled_exam`, `career_goals`, `interest_in_field`, `notice_period`, `notice_period_specify`, `strengths`, `weakness`, `epf_registered`, `uan_no`, `esic_registered`, `ip_no`, `aadhaar_no`, `pan_no`, `bank_account_no`, `ifsc_code`, `hobbies`, `computer_knowledge`, `weekly_working_days`, `medical_issue`, `smoking`, `self_vehicle`, `driving_licence`) VALUES
(19, 'APP202607220001', 'Arvind Sharma', 'humuc@mailinator.com', '+1 (478) 933-7527', '+1 (461) 579-8066', 'Other', '1974-04-23', 'Obcaecati voluptatem', 'Durg', 'Chhattisgarh', 'Voluptatem nisi ea c', 'Culpa duis perspici', 0.00, 'Whitaker Guzman Associates', 0.00, 0.00, 'Process Associate', 'Consectetur ab nesc', 'walkin', NULL, 'manager_feedback_received', 'pending', '2026-07-22 12:07:36', '2026-07-22 12:34:02', NULL, NULL, NULL, 'Tucker Ayala', 'uploads/photos/1784722056_photo_photo.png', 'uploads/resumes/1784722056_resume_Read-Me-Font-Installation.pdf', 'Dominic Jacobs', 'Deserunt voluptate d', 52, 'Single', 'Obcaecati voluptatem', 'Exercitationem earum', 'Voluptatem non cupi', 'Repellendus Aut pro', 'Enim labore asperior', 'Maiores repellendus', 'Velit architecto eu', 'Vitae rerum iste imp', '', '', '', '', 'Doloremque nisi ut m', '', '', '', 'Officia vitae ipsum', 'Id ut nostrud mollit', '', 'Tempor delectus sun', '', '', ''),
(20, 'APP202607230001', 'Zelenia Hawkins', 'humuc@mailinator.com', '+1 (478) 933-7527', '+1 (461) 579-8066', 'Other', '1974-04-23', 'Obcaecati voluptatem', 'Durg', 'Chhattisgarh', 'Voluptatem nisi ea c', 'Culpa duis perspici', 0.00, 'Whitaker Guzman Associates', 0.00, 0.00, 'Process Associate', 'Consectetur ab nesc', 'walkin', NULL, 'rejected', 'rejected', '2026-07-23 06:52:46', '2026-07-23 06:57:06', NULL, NULL, NULL, 'Tucker Ayala', 'uploads/photos/1784789562_photo_photo.png', 'uploads/resumes/1784789562_resume_Read-Me-Font-Installation.pdf', 'Dominic Jacobs', 'Deserunt voluptate d', 52, 'Single', 'Obcaecati voluptatem', 'Exercitationem earum', 'Voluptatem non cupi', 'Repellendus Aut pro', 'Enim labore asperior', 'Maiores repellendus', 'Velit architecto eu', 'Vitae rerum iste imp', NULL, NULL, NULL, NULL, 'Doloremque nisi ut m', NULL, NULL, NULL, 'Officia vitae ipsum', 'Id ut nostrud mollit', NULL, 'Tempor delectus sun', NULL, NULL, NULL),
(21, 'APP202607230002', 'Hilary Whitehead', 'pegibepita@mailinator.com', '+1 (202) 795-4774', '+1 (886) 792-7056', 'Other', '1983-10-03', 'Dolorum pariatur Co', 'Itanagar', 'Arunachal Pradesh', 'Voluptate labore qua', 'Et et hic anim rerum', 0.00, 'Gallegos Travis Trading', 0.00, 0.00, 'Process Associate', 'Consectetur atque d', 'qr', NULL, 'selected', 'selected', '2026-07-23 06:53:14', '2026-07-23 06:58:02', NULL, NULL, NULL, 'Hammett Freeman', 'uploads/photos/1784789590_photo_photo.png', 'uploads/resumes/1784789590_resume_Read-Me-Font-Installation.pdf', 'Hunter Burns', 'Minima dolorum aut i', 42, 'Widow', 'Est officiis nulla', 'Sapiente voluptates', 'Est elit aute et v', 'Dolorem voluptatem', 'Quas aute nihil cill', 'Sint cillum mollit v', 'Quae odit fugiat sin', 'Harum cum veritatis', NULL, NULL, NULL, NULL, 'Lorem autem laborios', NULL, NULL, NULL, 'Temporibus necessita', 'Commodo do ut dignis', NULL, 'Officia sed praesent', NULL, NULL, NULL),
(22, 'APP202607230003', 'Arvind Sharma', 'pegibepita@mailinator.com', '+1 (202) 795-4774', '+1 (886) 792-7056', 'Other', '1983-10-03', 'Dolorum pariatur Co', 'Itanagar', 'Arunachal Pradesh', 'Voluptate labore qua', 'Et et hic anim rerum', 0.00, 'Gallegos Travis Trading', 0.00, 0.00, 'Process Associate', 'Consectetur atque d', 'qr', NULL, 'selected', 'selected', '2026-07-23 06:58:34', '2026-07-23 07:20:49', NULL, NULL, NULL, 'Hammett Freeman', 'uploads/photos/1784789910_photo_photo.png', 'uploads/resumes/1784789910_resume_Read-Me-Font-Installation.pdf', 'Hunter Burns', 'Minima dolorum aut i', 42, '', 'Est officiis nulla', 'Sapiente voluptates', 'Est elit aute et v', 'Dolorem voluptatem', 'Quas aute nihil cill', 'Sint cillum mollit v', 'Quae odit fugiat sin', 'Harum cum veritatis', '', '', '', '', 'Lorem autem laborios', '', '', '', 'Temporibus necessita', 'Commodo do ut dignis', '', 'Officia sed praesent', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_academics`
--

CREATE TABLE `candidate_academics` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `level_name` varchar(50) NOT NULL,
  `board_name` varchar(150) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `institute` varchar(255) DEFAULT NULL,
  `passing_year` varchar(20) DEFAULT NULL,
  `percentage` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate_academics`
--

INSERT INTO `candidate_academics` (`id`, `candidate_id`, `level_name`, `board_name`, `subject`, `institute`, `passing_year`, `percentage`, `created_at`) VALUES
(1, 5, '10th', NULL, 'rbsc', 'rbsc', '2000', '90', '2026-07-10 12:20:26'),
(2, 5, '12th', NULL, 'rbsc', 'rbsc', '2009', '90', '2026-07-10 12:20:26'),
(3, 5, 'Graduation', NULL, 'rbsc', 'rbsc', '2011', '90', '2026-07-10 12:20:26'),
(4, 5, 'Post Graduation', NULL, NULL, NULL, NULL, NULL, '2026-07-10 12:20:26'),
(5, 5, 'Diploma', NULL, NULL, NULL, NULL, NULL, '2026-07-10 12:20:26'),
(6, 6, '10th', NULL, 'All', 'Arya public school', NULL, NULL, '2026-07-13 10:01:17'),
(7, 6, '12th', NULL, 'Commerce', 'Shri oswal jain college', NULL, NULL, '2026-07-13 10:01:17'),
(8, 6, 'Graduation', NULL, 'B.com', 'Adinath jain college', NULL, NULL, '2026-07-13 10:01:17'),
(9, 6, 'Post Graduation', NULL, 'MBA', 'MITRC', NULL, NULL, '2026-07-13 10:01:17'),
(10, 6, 'Diploma', NULL, NULL, NULL, NULL, NULL, '2026-07-13 10:01:17'),
(11, 7, '10th', 'CBSE (Central)', 'All', NULL, '2010', '8.4 CGPA', '2026-07-14 13:25:27'),
(12, 7, '12th', 'RBSE / BSER (Rajasthan)', 'Sci-Maths', NULL, '2012', '67%', '2026-07-14 13:25:27'),
(13, 7, 'Graduation', 'RBSE / BSER (Rajasthan)', 'BSc', NULL, '2015', '67%', '2026-07-14 13:25:27'),
(14, 7, 'Post Graduation', 'RBSE / BSER (Rajasthan)', 'M.A - Geography', NULL, '2017', '60%', '2026-07-14 13:25:27'),
(15, 7, 'Diploma', NULL, NULL, NULL, NULL, NULL, '2026-07-14 13:25:27'),
(16, 8, '10TH', NULL, 'CISCE (Central) - Veniam id suscipit', 'Nemo quo eu voluptat', '1975', 'Et itaque nulla occa', '2026-07-15 05:59:12'),
(17, 8, '12TH', NULL, 'UPMSP (Uttar Pradesh) - Commodi ut est praes', 'Blanditiis elit eiu', '2008', 'Pariatur Asperiores', '2026-07-15 05:59:12'),
(18, 8, 'GRADUATION', NULL, 'Velit magnam sit v - Nihil consequatur V', 'Ratione autem maxime', '1984', 'Ad neque temporibus', '2026-07-15 05:59:12'),
(19, 8, 'POST GRADUATION', NULL, 'Et quia animi dolor - Tempor ratione volup', 'Perspiciatis nemo u', '1982', 'Qui labore voluptate', '2026-07-15 05:59:12'),
(20, 8, 'DIPLOMA', NULL, 'Cum hic dolorem volu - Consequatur sint h', 'Voluptatum non ipsa', '2008', 'Eos quidem omnis in', '2026-07-15 05:59:12'),
(21, 9, '10TH', NULL, 'CISCE (Central) - Veniam id suscipit', 'Nemo quo eu voluptat', '1975', 'Et itaque nulla occa', '2026-07-15 06:00:55'),
(22, 9, '12TH', NULL, 'UPMSP (Uttar Pradesh) - Commodi ut est praes', 'Blanditiis elit eiu', '2008', 'Pariatur Asperiores', '2026-07-15 06:00:55'),
(23, 9, 'GRADUATION', NULL, 'Velit magnam sit v - Nihil consequatur V', 'Ratione autem maxime', '1984', 'Ad neque temporibus', '2026-07-15 06:00:55'),
(24, 9, 'POST GRADUATION', NULL, 'Et quia animi dolor - Tempor ratione volup', 'Perspiciatis nemo u', '1982', 'Qui labore voluptate', '2026-07-15 06:00:56'),
(25, 9, 'DIPLOMA', NULL, 'Cum hic dolorem volu - Consequatur sint h', 'Voluptatum non ipsa', '2008', 'Eos quidem omnis in', '2026-07-15 06:00:56'),
(26, 10, '10TH', NULL, 'RBSE / BSER (Rajasthan) - Enim odit id labori', 'Amet quisquam facil', '2010', 'Cumque eos vitae la', '2026-07-15 06:01:23'),
(27, 10, '12TH', NULL, 'BIEAP (Andhra Pradesh) - Nisi nobis dolor exe', 'Dolor elit explicab', '1999', 'Nisi ea dolore hic i', '2026-07-15 06:01:23'),
(28, 10, 'GRADUATION', NULL, 'Eius excepturi facil - Fugiat ex voluptate', 'Fugit qui veniam e', '1980', 'Ut id nisi nemo sunt', '2026-07-15 06:01:23'),
(29, 10, 'POST GRADUATION', NULL, 'Fugiat est reiciend - Incidunt ut laboris', 'Qui at sunt ut labor', '1974', 'Magnam et molestias', '2026-07-15 06:01:23'),
(30, 10, 'DIPLOMA', NULL, 'Sint corporis cillum - Sit repudiandae tene', 'Ex excepteur sit et', '1992', 'Excepturi iusto aliq', '2026-07-15 06:01:23'),
(31, 11, '10TH', NULL, 'CBSE (Central)', 'Cchs', NULL, NULL, '2026-07-16 05:24:59'),
(32, 11, '12TH', NULL, 'CBSE (Central)', 'Cchs', NULL, NULL, '2026-07-16 05:24:59'),
(33, 11, 'GRADUATION', NULL, 'Ru', 'St', NULL, NULL, '2026-07-16 05:24:59'),
(34, 11, 'POST GRADUATION', NULL, NULL, NULL, NULL, NULL, '2026-07-16 05:24:59'),
(35, 11, 'DIPLOMA', NULL, NULL, NULL, NULL, NULL, '2026-07-16 05:24:59'),
(36, 12, '10TH', NULL, 'BSEB (Bihar) - Quibusdam velit exer', 'Quam sed earum debit', '1988', 'Cillum non aliquip s', '2026-07-16 06:25:52'),
(37, 12, '12TH', NULL, 'BIEAP (Andhra Pradesh) - A accusamus consecte', 'Sunt ex sint dolore', '1996', 'Quos minim odit inve', '2026-07-16 06:25:52'),
(38, 12, 'GRADUATION', NULL, 'Est velit quia cum - Dolor animi quaerat', 'Maiores vero ratione', '1991', 'Commodi molestiae an', '2026-07-16 06:25:52'),
(39, 12, 'POST GRADUATION', NULL, 'Dolor eligendi quis - Voluptatibus ad volu', 'Veritatis sit sunt', '1998', 'Ea adipisicing cum e', '2026-07-16 06:25:52'),
(40, 12, 'DIPLOMA', NULL, 'Quaerat Nam vel exer - Exercitationem lauda', 'Voluptas qui est ali', '2008', 'Rerum dolor vero sol', '2026-07-16 06:25:52'),
(41, 13, '10TH', NULL, NULL, NULL, NULL, NULL, '2026-07-21 08:01:25'),
(42, 13, '12TH', NULL, NULL, NULL, NULL, NULL, '2026-07-21 08:01:25'),
(43, 13, 'GRADUATION', NULL, NULL, NULL, NULL, NULL, '2026-07-21 08:01:25'),
(44, 13, 'POST GRADUATION', NULL, NULL, NULL, NULL, NULL, '2026-07-21 08:01:25'),
(45, 13, 'DIPLOMA', NULL, NULL, NULL, NULL, NULL, '2026-07-21 08:01:25'),
(46, 14, '10TH', NULL, '', '', '', '', '2026-07-21 09:06:25'),
(47, 14, '12TH', NULL, '', '', '', '', '2026-07-21 09:06:25'),
(48, 14, 'GRADUATION', NULL, '', '', '', '', '2026-07-21 09:06:25'),
(49, 14, 'POST GRADUATION', NULL, '', '', '', '', '2026-07-21 09:06:25'),
(50, 14, 'DIPLOMA', NULL, '', '', '', '', '2026-07-21 09:06:25'),
(51, 15, '10TH', NULL, 'CBSE (Central) - hvcghv', 'KV No.5', '2002', 'nhvf', '2026-07-21 09:39:36'),
(52, 15, '12TH', NULL, 'CBSE (Central) - gdfh', 'KV No.1', '2004', 'bjhf', '2026-07-21 09:39:36'),
(53, 15, 'GRADUATION', NULL, 'Rajasthan University - B Com', 'Kanoria College', '2007', 'vgdv', '2026-07-21 09:39:36'),
(54, 15, 'POST GRADUATION', NULL, 'JNU - MBA', 'Seedling', '2009', 'bhjdgyu', '2026-07-21 09:39:36'),
(55, 15, 'DIPLOMA', NULL, 'NA', NULL, NULL, NULL, '2026-07-21 09:39:36'),
(56, 16, '10TH', NULL, NULL, NULL, NULL, NULL, '2026-07-21 12:23:20'),
(57, 16, '12TH', NULL, NULL, NULL, NULL, NULL, '2026-07-21 12:23:20'),
(58, 16, 'GRADUATION', NULL, NULL, NULL, NULL, NULL, '2026-07-21 12:23:20'),
(59, 16, 'POST GRADUATION', NULL, NULL, NULL, NULL, NULL, '2026-07-21 12:23:20'),
(60, 16, 'DIPLOMA', NULL, NULL, NULL, NULL, NULL, '2026-07-21 12:23:20'),
(61, 17, '10TH', NULL, 'WBBSE (West Bengal) - Et sint sunt neque', 'Consequatur Omnis d', '2005', 'Quia nihil rerum ape', '2026-07-22 06:55:33'),
(62, 17, '12TH', NULL, 'TNBSE / TNDGE (Tamil Nadu) - Nostrum quod quisqua', 'Non anim in inventor', '1975', 'Doloremque alias vol', '2026-07-22 06:55:33'),
(63, 17, 'GRADUATION', NULL, 'Est nihil totam vero - Rerum nemo laboris i', 'Illum est quasi ali', '2004', 'Illo tenetur ipsam d', '2026-07-22 06:55:33'),
(64, 17, 'POST GRADUATION', NULL, 'In ipsum nostrum iur - Molestiae voluptate', 'Veniam iure occaeca', '2019', 'Velit debitis fugit', '2026-07-22 06:55:33'),
(65, 17, 'DIPLOMA', NULL, 'Nulla vero vitae aut - Anim distinctio Mol', 'Dolore veniam natus', '1997', 'Aperiam asperiores d', '2026-07-22 06:55:33'),
(66, 18, '10TH', NULL, 'CISCE (Central) - Eum incididunt aut q', 'Expedita ea voluptat', '2000', 'Eos ratione sunt ear', '2026-07-22 11:40:03'),
(67, 18, '12TH', NULL, 'PSEB (Punjab) - Aut deleniti fugit', 'Alias aliquip volupt', '2007', 'Non sed nihil obcaec', '2026-07-22 11:40:03'),
(68, 18, 'GRADUATION', NULL, 'Quae odio est maior - Commodi occaecat dol', 'Aliquam enim et nisi', '2019', 'Eos aliquid et mini', '2026-07-22 11:40:03'),
(69, 18, 'POST GRADUATION', NULL, 'Anim qui laborum Ac - Est aliquid odio mo', 'Labore libero quae n', '1991', 'Rem qui nulla animi', '2026-07-22 11:40:03'),
(70, 18, 'DIPLOMA', NULL, 'Dolor aliqua Volupt - Sed et eu id libero', 'Voluptate esse volu', '2005', 'Quisquam velit minim', '2026-07-22 11:40:03'),
(71, 19, '10TH', NULL, 'MPBSE (Madhya Pradesh) - Autem et in Nam sit', 'Possimus quo nostru', '1980', 'Molestiae in consequ', '2026-07-22 12:07:36'),
(72, 19, '12TH', NULL, 'MSBSHSE (Maharashtra) - Autem alias rerum qu', 'Incididunt dolores q', '1989', 'Lorem ea laboriosam', '2026-07-22 12:07:36'),
(73, 19, 'GRADUATION', NULL, 'Veniam nisi possimu - Cum nisi qui qui ad', 'Et quis nemo aut tem', '2006', 'Incidunt sequi labo', '2026-07-22 12:07:36'),
(74, 19, 'POST GRADUATION', NULL, 'Praesentium voluptat - Sapiente libero labo', 'Ea natus quis consec', '1992', 'Nostrum odio enim no', '2026-07-22 12:07:36'),
(75, 19, 'DIPLOMA', NULL, 'Minus inventore dolo - Deleniti eaque adipi', 'Dignissimos ut sunt', '2004', 'Asperiores id veniam', '2026-07-22 12:07:36'),
(76, 20, '10TH', NULL, 'MPBSE (Madhya Pradesh) - Autem et in Nam sit', 'Possimus quo nostru', '1980', 'Molestiae in consequ', '2026-07-23 06:52:46'),
(77, 20, '12TH', NULL, 'MSBSHSE (Maharashtra) - Autem alias rerum qu', 'Incididunt dolores q', '1989', 'Lorem ea laboriosam', '2026-07-23 06:52:46'),
(78, 20, 'GRADUATION', NULL, 'Veniam nisi possimu - Cum nisi qui qui ad', 'Et quis nemo aut tem', '2006', 'Incidunt sequi labo', '2026-07-23 06:52:46'),
(79, 20, 'POST GRADUATION', NULL, 'Praesentium voluptat - Sapiente libero labo', 'Ea natus quis consec', '1992', 'Nostrum odio enim no', '2026-07-23 06:52:46'),
(80, 20, 'DIPLOMA', NULL, 'Minus inventore dolo - Deleniti eaque adipi', 'Dignissimos ut sunt', '2004', 'Asperiores id veniam', '2026-07-23 06:52:46'),
(81, 21, '10TH', NULL, 'BSEB (Bihar) - Voluptas earum sed r', 'Mollit asperiores al', '1984', 'Autem sed quia deser', '2026-07-23 06:53:14'),
(82, 21, '12TH', NULL, 'MSBSHSE (Maharashtra) - Vitae nihil veniam', 'Nesciunt labore do', '2006', 'Consequat Aliqua D', '2026-07-23 06:53:14'),
(83, 21, 'GRADUATION', NULL, 'Ipsum et ut sed irur - Harum natus vero con', 'Error eaque non mini', '1992', 'Do ad quo error dolo', '2026-07-23 06:53:14'),
(84, 21, 'POST GRADUATION', NULL, 'Omnis qui quidem vol - In nisi molestiae ni', 'Et nisi dolores atqu', '1994', 'Sint tempore archi', '2026-07-23 06:53:14'),
(85, 21, 'DIPLOMA', NULL, 'Velit nihil dolore - Ut nihil voluptatem', 'Sapiente eos quia vo', '1985', 'Est minim quis tempo', '2026-07-23 06:53:14'),
(86, 22, '10TH', NULL, 'BSEB (Bihar) - Voluptas earum sed r', 'Mollit asperiores al', '1984', 'Autem sed quia deser', '2026-07-23 06:58:34'),
(87, 22, '12TH', NULL, 'MSBSHSE (Maharashtra) - Vitae nihil veniam', 'Nesciunt labore do', '2006', 'Consequat Aliqua D', '2026-07-23 06:58:34'),
(88, 22, 'GRADUATION', NULL, 'Ipsum et ut sed irur - Harum natus vero con', 'Error eaque non mini', '1992', 'Do ad quo error dolo', '2026-07-23 06:58:34'),
(89, 22, 'POST GRADUATION', NULL, 'Omnis qui quidem vol - In nisi molestiae ni', 'Et nisi dolores atqu', '1994', 'Sint tempore archi', '2026-07-23 06:58:34'),
(90, 22, 'DIPLOMA', NULL, 'Velit nihil dolore - Ut nihil voluptatem', 'Sapiente eos quia vo', '1985', 'Est minim quis tempo', '2026-07-23 06:58:34');

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

-- --------------------------------------------------------

--
-- Table structure for table `candidate_edit_logs`
--

CREATE TABLE `candidate_edit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `edited_by` int(10) UNSIGNED DEFAULT NULL,
  `edited_role` varchar(50) DEFAULT 'recruiter',
  `section_name` varchar(100) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_experiences`
--

CREATE TABLE `candidate_experiences` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `from_date` varchar(30) DEFAULT NULL,
  `to_date` varchar(30) DEFAULT NULL,
  `salary_ctc` varchar(50) DEFAULT NULL,
  `reason_for_leaving` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate_experiences`
--

INSERT INTO `candidate_experiences` (`id`, `candidate_id`, `company_name`, `designation`, `from_date`, `to_date`, `salary_ctc`, `reason_for_leaving`, `created_at`) VALUES
(1, 5, 'sdfdfdf', 'ffhfgh', '2009', '2009', '343444', 'fgdfgfd', '2026-07-10 12:20:27'),
(2, 7, 'ABX', 'Executive', 'May-2020', 'May-2025', '34000/-', 'No appraisal', '2026-07-14 13:25:27'),
(3, 8, 'Parsons and Blair Traders', 'Cupidatat anim neces', 'In aut consequatur', 'Pariatur Molestias', 'Voluptas ex exceptur', 'Commodo corrupti qu', '2026-07-15 05:59:12'),
(4, 9, 'Parsons and Blair Traders', 'Cupidatat anim neces', 'In aut consequatur', 'Pariatur Molestias', 'Voluptas ex exceptur', 'Commodo corrupti qu', '2026-07-15 06:00:56'),
(5, 10, 'Gomez and Reilly Co', 'Non amet dolorem co', 'Eius dolor aperiam a', 'Aut quis consequuntu', 'Quo praesentium veli', 'Esse voluptates mol', '2026-07-15 06:01:23'),
(6, 11, 'Ubs', 'Am', 'Calendar lagao', 'Calendar lagao', '10,000', NULL, '2026-07-16 05:24:59'),
(7, 12, 'Lynn and Maddox Co', 'Iste dolorem enim ne', 'Omnis corporis lorem', 'Sit quam incididunt', 'Ut veniam consectet', 'Sit quo dolor qui d', '2026-07-16 06:25:53'),
(8, 15, 'fhibf', 'njjfbg', 'Jan 2002', 'Oct 2004', NULL, NULL, '2026-07-21 09:39:36'),
(9, 17, 'Oneal Cameron Trading', 'Possimus iusto dolo', 'Iste recusandae Nec', 'Quia reprehenderit d', 'Iusto laudantium vo', 'Qui quisquam labore', '2026-07-22 06:55:33'),
(10, 18, 'Benton Maldonado LLC', 'Eos soluta quia num', 'Officiis culpa exer', 'Quod ratione proiden', 'Quis recusandae Qua', 'Ut dolor sunt amet', '2026-07-22 11:40:03'),
(11, 19, 'Valencia Newton Inc', 'Incididunt aliquid m', 'Quis sunt velit in', 'Sint tempora adipisi', 'Similique molestias', 'Repellendus Deserun', '2026-07-22 12:07:37'),
(12, 20, 'Valencia Newton Inc', 'Incididunt aliquid m', 'Quis sunt velit in', 'Sint tempora adipisi', 'Similique molestias', 'Repellendus Deserun', '2026-07-23 06:52:46'),
(13, 21, 'Humphrey and Mcclure LLC', 'Dolor saepe voluptas', 'Ad quasi delectus a', 'Vel asperiores tempo', 'Eu et repudiandae ut', 'Ut autem soluta volu', '2026-07-23 06:53:14'),
(14, 22, 'Humphrey and Mcclure LLC', 'Dolor saepe voluptas', 'Ad quasi delectus a', 'Vel asperiores tempo', 'Eu et repudiandae ut', 'Ut autem soluta volu', '2026-07-23 06:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_family_details`
--

CREATE TABLE `candidate_family_details` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(150) DEFAULT NULL,
  `relation_name` varchar(100) DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate_family_details`
--

INSERT INTO `candidate_family_details` (`id`, `candidate_id`, `member_name`, `relation_name`, `occupation`, `created_at`) VALUES
(1, 5, 'vbnvbn', 'vbnvbn', 'vnvbnv', '2026-07-10 12:20:27'),
(2, 5, 'nvbnb', 'vnvbnvnv', 'vbnvbn', '2026-07-10 12:20:27'),
(3, 7, 'Sushobhan Singh', 'Spouse', 'Animation', '2026-07-14 13:25:27'),
(4, 8, 'Brittany Bartlett', 'Sunt sed possimus', 'Lorem omnis voluptas', '2026-07-15 05:59:12'),
(5, 9, 'Brittany Bartlett', 'Sunt sed possimus', 'Lorem omnis voluptas', '2026-07-15 06:00:56'),
(6, 10, 'Kay Malone', 'Ad quod ducimus omn', 'Nostrum ab qui autem', '2026-07-15 06:01:23'),
(7, 11, 'Abc', 'Drop down', 'Test', '2026-07-16 05:24:59'),
(8, 12, 'Jenette Kirkland', 'Doloribus labore har', 'Obcaecati facere rec', '2026-07-16 06:25:53'),
(9, 17, 'Janna Horn', 'Sint temporibus sint', 'Quae sint at qui bla', '2026-07-22 06:55:33'),
(10, 18, 'Libby Hodge', 'Provident autem min', 'Anim excepteur ut sa', '2026-07-22 11:40:04'),
(11, 19, 'Dominic Suarez', 'Sed tempore quis vo', 'Ea quasi quasi liber', '2026-07-22 12:07:37'),
(12, 20, 'Dominic Suarez', 'Sed tempore quis vo', 'Ea quasi quasi liber', '2026-07-23 06:52:46'),
(13, 21, 'McKenzie Bradley', 'Quisquam perspiciati', 'Laborum Accusamus q', '2026-07-23 06:53:14'),
(14, 22, 'McKenzie Bradley', 'Quisquam perspiciati', 'Laborum Accusamus q', '2026-07-23 06:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_languages`
--

CREATE TABLE `candidate_languages` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `language_name` varchar(100) DEFAULT NULL,
  `can_read` varchar(10) DEFAULT NULL,
  `can_write` varchar(10) DEFAULT NULL,
  `can_speak` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate_languages`
--

INSERT INTO `candidate_languages` (`id`, `candidate_id`, `language_name`, `can_read`, `can_write`, `can_speak`, `created_at`) VALUES
(9, 19, 'Ulysses Sanchez', 'Yes', 'No', 'Yes', '2026-07-22 12:07:37'),
(10, 20, 'Ulysses Sanchez', 'Yes', 'No', 'Yes', '2026-07-23 06:52:46'),
(11, 21, 'Charissa French', 'No', 'Yes', 'No', '2026-07-23 06:53:15'),
(12, 22, 'Charissa French', 'No', 'Yes', 'No', '2026-07-23 06:58:35');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_references`
--

CREATE TABLE `candidate_references` (
  `id` int(10) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `ref_name` varchar(150) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate_references`
--

INSERT INTO `candidate_references` (`id`, `candidate_id`, `ref_name`, `designation`, `email`, `mobile`, `created_at`) VALUES
(1, 5, 'fgfg', 'gdfgdfg', 'sharma95.0000@gmail.com', '1234567890', '2026-07-10 12:20:27'),
(2, 7, 'MR Doe', 'Manager', 'doe.doe@email.com', '000000000', '2026-07-14 13:25:27'),
(3, 8, 'Phelan Chandler', 'Voluptate excepturi', 'xeresyrif@mailinator.com', 'Ut est enim tempora', '2026-07-15 05:59:12'),
(4, 8, 'Isabelle Terry', 'Eos sint velit odi', 'balir@mailinator.com', 'Omnis excepteur dolo', '2026-07-15 05:59:12'),
(5, 9, 'Phelan Chandler', 'Voluptate excepturi', 'xeresyrif@mailinator.com', 'Ut est enim tempora', '2026-07-15 06:00:56'),
(6, 9, 'Isabelle Terry', 'Eos sint velit odi', 'balir@mailinator.com', 'Omnis excepteur dolo', '2026-07-15 06:00:56'),
(7, 10, 'Axel Riddle', 'Aut lorem voluptas e', 'sowabojov@mailinator.com', 'Voluptas non molesti', '2026-07-15 06:01:23'),
(8, 10, 'Dillon Merrill', 'Nemo et dolorum sit', 'gasep@mailinator.com', 'Itaque eos amet cup', '2026-07-15 06:01:23'),
(9, 12, 'Amir House', 'Consequuntur rerum q', 'dokavanax@mailinator.com', 'Neque omnis sunt ul', '2026-07-16 06:25:53'),
(10, 12, 'Robert Williamson', 'Omnis quam libero ei', 'diqegikep@mailinator.com', 'Aspernatur dolorem e', '2026-07-16 06:25:53'),
(11, 17, 'Branden Hogan', 'Vel libero exercitat', 'nihuloz@mailinator.com', 'Consequatur officia', '2026-07-22 06:55:33'),
(12, 17, 'Yolanda Casey', 'Excepteur cumque nos', 'pyjugylit@mailinator.com', 'Velit eveniet duci', '2026-07-22 06:55:33'),
(13, 18, 'Kirk Brooks', 'Consequuntur consequ', 'hihecaz@mailinator.com', 'Vel enim mollitia mo', '2026-07-22 11:40:03'),
(14, 18, 'September Frost', 'Dolor reiciendis ut', 'remu@mailinator.com', 'Nesciunt maxime dol', '2026-07-22 11:40:04'),
(15, 19, 'Carl Williamson', 'Duis in omnis dolore', 'lupyhozek@mailinator.com', 'Laboris ullam ullam', '2026-07-22 12:07:37'),
(16, 19, 'Taylor Kennedy', 'Porro placeat quide', 'safoq@mailinator.com', 'Voluptate dolores fa', '2026-07-22 12:07:37'),
(17, 20, 'Carl Williamson', 'Duis in omnis dolore', 'lupyhozek@mailinator.com', 'Laboris ullam ullam', '2026-07-23 06:52:46'),
(18, 20, 'Taylor Kennedy', 'Porro placeat quide', 'safoq@mailinator.com', 'Voluptate dolores fa', '2026-07-23 06:52:46'),
(19, 21, 'Odysseus Mccullough', 'Velit eos sit labo', 'vamoge@mailinator.com', 'Ut ex est dolorem oc', '2026-07-23 06:53:14'),
(20, 21, 'Vaughan Church', 'Incidunt non ea bea', 'rihusarala@mailinator.com', 'Quaerat obcaecati co', '2026-07-23 06:53:14'),
(21, 22, 'Odysseus Mccullough', 'Velit eos sit labo', 'vamoge@mailinator.com', 'Ut ex est dolorem oc', '2026-07-23 06:58:34'),
(22, 22, 'Vaughan Church', 'Incidunt non ea bea', 'rihusarala@mailinator.com', 'Quaerat obcaecati co', '2026-07-23 06:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_status_logs`
--

CREATE TABLE `candidate_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` int(10) UNSIGNED NOT NULL,
  `round_id` int(10) UNSIGNED DEFAULT NULL,
  `action_by` int(10) UNSIGNED DEFAULT NULL,
  `action_role` varchar(50) DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidate_status_logs`
--

INSERT INTO `candidate_status_logs` (`id`, `candidate_id`, `round_id`, `action_by`, `action_role`, `old_status`, `new_status`, `note`, `created_at`) VALUES
(57, 19, NULL, 5, 'manager', NULL, 'submitted', 'Candidate applied from public form', '2026-07-22 12:07:37'),
(58, 19, 18, 3, 'recruiter', 'submitted', 'sent_to_manager', 'Round_1', '2026-07-22 12:09:30'),
(59, 19, 18, 4, 'manager', 'sent_to_manager', 'manager_feedback_received', 'Manager feedback submitted: next_round', '2026-07-22 12:10:55'),
(60, 19, 19, 3, 'recruiter', 'manager_feedback_received', 'sent_to_manager', 'next round', '2026-07-22 12:32:57'),
(61, 19, 19, 5, 'manager', 'sent_to_manager', 'manager_feedback_received', 'Manager feedback submitted: select', '2026-07-22 12:34:02'),
(62, 20, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-23 06:52:46'),
(63, 21, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-23 06:53:15'),
(64, 20, NULL, 3, 'recruiter', 'submitted', 'rejected', ',jhdgfdusyfgsjdfgdsjfgjhs', '2026-07-23 06:57:07'),
(65, 21, NULL, 3, 'recruiter', 'submitted', 'selected', 'rejoin', '2026-07-23 06:58:02'),
(66, 22, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-23 06:58:35'),
(67, 22, 20, 3, 'recruiter', 'submitted', 'sent_to_manager', 'Round -1', '2026-07-23 07:06:23'),
(68, 22, 20, 4, 'manager', 'sent_to_manager', 'manager_feedback_received', 'Manager feedback submitted: next_round', '2026-07-23 07:08:45'),
(69, 22, 21, 3, 'recruiter', 'manager_feedback_received', 'sent_to_manager', 'Round-2', '2026-07-23 07:09:37'),
(70, 22, 21, 5, 'manager', 'sent_to_manager', 'manager_feedback_received', 'Manager feedback submitted: select', '2026-07-23 07:13:10'),
(71, 22, NULL, 3, 'recruiter', 'manager_feedback_received', 'selected', 'Recruitervfinal  select', '2026-07-23 07:20:49');

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
(19, 18, 19, 4, 'next round this nscslnclzcadlalsladjhdl,bsfsdjfsgdjfzxvbcxbvn vnbdvjkxcbvnb', 'next_round', '2026-07-22 12:10:55'),
(20, 19, 19, 5, 'sdfsdfsdgfhdsgfhdsbfhsgfksf\r\nhfskfksdhfksjhfksjhfksjdfhbmnxbvfsgfbmdsm', 'select', '2026-07-22 12:34:02'),
(21, 20, 22, 4, 'A solid, durable shoe for trail running. Highly recommended for anyone navigating uneven terrain, as long as you are willing to break them in for a few days.', 'next_round', '2026-07-23 07:08:45'),
(22, 21, 22, 5, 'uneven terrain, as long as you are willing to break them in for a few days.A solid, durable shoe for trail running. Highly recommended for anyone navigating uneven terrain, as long as you are willing to break them in for a few days.', 'select', '2026-07-23 07:13:10');

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
(18, 19, 1, 'Round 1', 3, 4, '2026-07-22 12:09:30', 'returned_to_recruiter', '2026-07-22 17:38:00', '2026-07-22 05:10:55', '2026-07-22 05:10:55'),
(19, 19, 2, 'Round 2', 3, 5, '2026-07-22 12:32:57', 'returned_to_recruiter', '2026-07-23 18:02:00', '2026-07-22 05:34:02', '2026-07-22 05:34:02'),
(20, 22, 1, 'Round 1', 3, 4, '2026-07-23 07:06:23', 'returned_to_recruiter', '2026-07-23 12:36:00', '2026-07-23 00:08:45', '2026-07-23 00:08:45'),
(21, 22, 2, 'Round 2', 3, 5, '2026-07-23 07:09:37', 'returned_to_recruiter', '2026-07-23 12:39:00', '2026-07-23 00:13:10', '2026-07-23 00:13:10');

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
(4, 'abhay1', 'abhay1@unire.co.in', '123456789', '$2y$10$Ez5zLSxhuXZ43BiGE1lAq.V84SD76xjZnK0D7VQKDnpXtGkwgwZx6', 'active', '2026-07-10 04:36:15', '2026-07-10 04:36:15', '2026-07-09 21:36:15'),
(5, 'testmanager', 'sharma95.0000@gmail.com', '9358000971', '$2y$10$vWrN.3tQqWYuWxxQ80KrqejCj/0.lloQmND0Fu4caNIDSGF7xHCcK', 'active', '2026-07-22 11:36:39', '2026-07-22 11:36:39', '2026-07-22 04:36:39');

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
(4, 3, '2026-07-10 04:36:15'),
(5, 3, '2026-07-22 11:36:39');

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
-- Indexes for table `candidate_academics`
--
ALTER TABLE `candidate_academics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_doc_candidate` (`candidate_id`);

--
-- Indexes for table `candidate_edit_logs`
--
ALTER TABLE `candidate_edit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_candidate_edit_logs_candidate` (`candidate_id`),
  ADD KEY `fk_candidate_edit_logs_user` (`edited_by`);

--
-- Indexes for table `candidate_experiences`
--
ALTER TABLE `candidate_experiences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidate_family_details`
--
ALTER TABLE `candidate_family_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidate_languages`
--
ALTER TABLE `candidate_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `candidate_references`
--
ALTER TABLE `candidate_references`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `candidate_academics`
--
ALTER TABLE `candidate_academics`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `candidate_edit_logs`
--
ALTER TABLE `candidate_edit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidate_experiences`
--
ALTER TABLE `candidate_experiences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `candidate_family_details`
--
ALTER TABLE `candidate_family_details`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `candidate_languages`
--
ALTER TABLE `candidate_languages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `candidate_references`
--
ALTER TABLE `candidate_references`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `candidate_status_logs`
--
ALTER TABLE `candidate_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `interview_feedback`
--
ALTER TABLE `interview_feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `interview_rounds`
--
ALTER TABLE `interview_rounds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Constraints for table `candidate_edit_logs`
--
ALTER TABLE `candidate_edit_logs`
  ADD CONSTRAINT `fk_candidate_edit_logs_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidate_edit_logs_user` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `candidate_languages`
--
ALTER TABLE `candidate_languages`
  ADD CONSTRAINT `fk_candidate_languages_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

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
