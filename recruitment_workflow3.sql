-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 15, 2026 at 11:08 PM
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

INSERT INTO `candidates` (`id`, `application_no`, `full_name`, `email`, `phone`, `alternate_phone`, `gender`, `dob`, `address`, `city`, `state`, `pincode`, `highest_qualification`, `total_experience`, `current_company`, `current_salary`, `expected_salary`, `position_applied`, `department`, `source_type`, `submitted_by_recruiter_id`, `current_status`, `final_decision`, `applied_at`, `updated_at`, `source_reference_name`, `photo_path`, `resume_path`, `father_husband_name`, `emergency_no`, `age`, `marital_status`, `permanent_address`, `scheduled_exam`, `career_goals`, `interest_in_field`, `notice_period`, `notice_period_specify`, `strengths`, `weakness`, `epf_registered`, `uan_no`, `esic_registered`, `ip_no`, `aadhaar_no`, `pan_no`, `bank_account_no`, `ifsc_code`, `hobbies`, `computer_knowledge`, `weekly_working_days`, `medical_issue`, `smoking`, `self_vehicle`, `driving_licence`) VALUES
(1, 'APP-20260709-2470', 'Abhay Sharma', 'hr@unire.co.in', '7073067817', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL, 'HR', NULL, '', NULL, 'rejected', 'rejected', '2026-07-09 13:15:09', '2026-07-10 06:34:06', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'APP-20260710-3787', 'Aru sharma', 'sharma95.0000@gmail.com', '9358000971', NULL, NULL, NULL, '23', NULL, NULL, NULL, NULL, 3.00, NULL, NULL, NULL, 'IT support', NULL, '', NULL, 'selected', 'selected', '2026-07-10 11:13:35', '2026-07-13 13:09:06', 'test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'APP-20260710-9591', 'Aru sharma', 'sharma95.0000@gmail.com', '9358000971', NULL, NULL, NULL, 'testt', NULL, NULL, NULL, NULL, 3.00, NULL, NULL, NULL, 'IT support', NULL, '', NULL, 'manager_rejected', 'pending', '2026-07-10 11:14:56', '2026-07-13 13:16:45', 'test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'APP-20260710-3469', 'Aru sharma', 'sharma95.0000@gmail.com', '123456789', NULL, NULL, NULL, '123 tfyjtyj sff', NULL, NULL, NULL, NULL, 7.00, NULL, NULL, NULL, 'IT support', NULL, '', NULL, 'submitted', 'pending', '2026-07-10 11:22:33', '2026-07-10 11:22:33', 'testt', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'APP202607100001', 'g', 'gf@gmail.com', '1234567890', '123456789', 'Male', '2000-07-07', 'Rajasthan', 'Jaipur', 'Rajasthan', '302020', NULL, 3.00, 'rter', 333333.00, 33333333.00, 'it', 'it', 'walkin', NULL, 'sent_to_manager', 'pending', '2026-07-10 12:20:26', '2026-07-10 12:38:17', 'vvnvn', 'uploads/photos/1783686026_photo_test.png', 'uploads/resumes/1783686026_resume_Job_Application_Form_NEW.pdf', 'gf', '123456789', 26, 'Married', '124/270', 'no', 'ryrty', 'rtyrty', '15', '15', 'vbnvnvb', 'vnvbnvnv', 'No', NULL, 'Yes', '1234567890', '435654747', 'chfg78676dg', '34636363', '34534cvds', 'sacscsc', NULL, '5', 'nooo', 'Yes', 'Yes', 'Yes'),
(6, 'APP202607130001', 'Phalguni', 'phalguni1301@gmail.com', '7357560978', '9251169784', 'Female', '2002-01-13', 'Shipra path', 'Alwar', 'Rajasthan', '301001', 'MBA', 1.00, 'Unire', 0.00, 7.00, 'HR', 'HR', 'walkin', NULL, 'sent_to_manager', 'pending', '2026-07-13 10:01:17', '2026-07-14 08:02:50', 'Priyanshu', NULL, NULL, 'Mahendra Singh', '9588245875', 24, 'Single', 'Alwar', 'No', 'NA', 'HR', 'immediate', 'NA', NULL, NULL, 'Yes', 'NA', 'Yes', 'No', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'APP202607140001', 'Aishwarya Singh', 'aishwarya.singh@unire.co.in', '8696124418', 'NA', 'Female', '1994-03-18', 'NA', 'Jaipur', 'Rajasthan', '302020', NULL, 2.00, 'ABX', 34000.00, 45000.00, 'Reservation', NULL, 'walkin', NULL, 'manager_next_round', 'pending', '2026-07-14 13:25:27', '2026-07-14 13:27:56', 'NA', 'uploads/photos/1784035527_photo_atm8.jpg', 'uploads/resumes/1784035527_resume_Laguna_Beach_Hotel_and_Spa_USD_2026-2027_ASIA.pdf', 'Sushobhan Singh', '', 32, 'Married', 'NA', 'NA', 'NA', NULL, '30', NULL, 'Strong hold on Communication', 'Attention to detail', NULL, NULL, NULL, NULL, '1000 0001 9999', NULL, NULL, NULL, 'Watching crime documentaries', NULL, NULL, 'NA', NULL, NULL, NULL),
(8, 'APP202607150001', 'Malik Lee', 'filizukyh@mailinator.com', '+1 (605) 581-7362', '+1 (999) 875-8621', 'Other', '1974-10-07', 'Dolore voluptas sunt', 'Surat', 'Gujarat', 'Sunt aliquip molesti', 'Fugiat esse qui par', 0.00, 'Bond and Henderson Traders', 0.00, 0.00, 'Accounts', 'Sequi dolor exceptur', 'reference', NULL, 'rejected', 'rejected', '2026-07-15 05:59:12', '2026-07-15 06:00:21', 'Otto Cunningham', 'uploads/photos/1784095151_photo_photo.png', 'uploads/resumes/1784095151_resume_Read-Me-Font-Installation.pdf', 'Claire Mckee', 'Ea non necessitatibu', 51, 'Widow', 'Optio recusandae T', 'Ex odio consequatur', 'Deserunt quis nulla', 'Excepteur in corrupt', 'Non quod autem conse', 'Itaque sunt autem ac', 'Possimus qui tenetu', 'Eveniet delectus v', NULL, NULL, NULL, NULL, 'Quibusdam quaerat nu', NULL, NULL, NULL, 'Laborum Labore vel', 'Atque velit quia ex', NULL, 'Id nemo velit alias', NULL, NULL, NULL),
(9, 'APP202607150002', 'Malik Lee', 'filizukyh@mailinator.com', '+1 (605) 581-7362', '+1 (999) 875-8621', 'Other', '1974-10-07', 'Dolore voluptas sunt', 'Surat', 'Gujarat', 'Sunt aliquip molesti', 'Fugiat esse qui par', 0.00, 'Bond and Henderson Traders', 0.00, 0.00, 'Accounts', 'Sequi dolor exceptur', 'reference', NULL, 'selected', 'selected', '2026-07-15 06:00:55', '2026-07-15 06:04:06', 'Otto Cunningham', 'uploads/photos/1784095255_photo_photo.png', 'uploads/resumes/1784095255_resume_Read-Me-Font-Installation.pdf', 'Claire Mckee', 'Ea non necessitatibu', 51, 'Widow', 'Optio recusandae T', 'Ex odio consequatur', 'Deserunt quis nulla', 'Excepteur in corrupt', 'Non quod autem conse', 'Itaque sunt autem ac', 'Possimus qui tenetu', 'Eveniet delectus v', NULL, NULL, NULL, NULL, 'Quibusdam quaerat nu', NULL, NULL, NULL, 'Laborum Labore vel', 'Atque velit quia ex', NULL, 'Id nemo velit alias', NULL, NULL, NULL),
(10, 'APP202607150003', 'Brenda Perez', 'nakojowif@mailinator.com', '+1 (536) 815-6055', '+1 (147) 297-6105', 'Female', '2003-11-16', 'Perspiciatis labore', 'Gangtok', 'Sikkim', 'Perspiciatis quam i', 'Error rem quia repre', 0.00, 'Rosa and Alexander Co', 0.00, 0.00, 'Reservation', 'Aut et voluptas sit', 'reference', NULL, 'selected', 'selected', '2026-07-15 06:01:23', '2026-07-15 06:07:38', 'Kylee Webb', 'uploads/photos/1784095283_photo_photo.png', 'uploads/resumes/1784095283_resume_Read-Me-Font-Installation.pdf', 'Macaulay Hanson', 'Eveniet in in labor', 22, 'Married', 'Perspiciatis labore', 'Occaecat id tempor m', 'Occaecat temporibus', 'Sunt occaecat nulla', 'Ad doloribus enim ha', 'Aliqua Quaerat nequ', 'Culpa lorem culpa m', 'Quis non minim volup', NULL, NULL, NULL, NULL, 'Deserunt ea officia', NULL, NULL, NULL, 'Possimus voluptas o', 'Dignissimos veniam', NULL, 'Ipsum dolores accusa', NULL, NULL, NULL),
(11, 'APP202607160001', 'Abhay Sharma', 'abhaysharma2399@gmail.com', '7073067817', '7073067817', 'Male', '1999-10-23', 'Plot No. 267, CD Block, Dadu Dayal Nagar, Near Water Tank, Mansarovar, Jaipur, Rajasthan 302020', 'Jaipur', 'Rajasthan', '302020', 'Bba', 6.00, 'Ubs', 10.00, 20.00, 'Operations', 'REMOVE THIS', 'reference', NULL, 'submitted', 'pending', '2026-07-16 05:24:59', '2026-07-16 05:24:59', 'Arvind', 'uploads/photos/1784179499_photo_IMG_7809.jpeg', 'uploads/resumes/1784179499_resume_Mivi.pdf', 'Ajay sharma', '1234567890', 26, 'Single', 'Plot No. 267, CD Block, Dadu Dayal Nagar, Near Water Tank, Mansarovar, Jaipur, Rajasthan 302020', 'Put a drop down yes or na ka', 'Hr', 'Hr', '15 days', 'Remove this', 'Body building', 'Body not building', NULL, NULL, NULL, NULL, '1234567890', NULL, NULL, NULL, 'Building', 'Very high', NULL, 'No', NULL, NULL, NULL);

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
(35, 11, 'DIPLOMA', NULL, NULL, NULL, NULL, NULL, '2026-07-16 05:24:59');

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
(2, 1, 'photo', 'WhatsApp Image 2026-06-30 at 3.05.13 PM.jpeg', '', 'uploads/photos/1783602909_photo_WhatsApp_Image_2026-06-30_at_3.05.13_PM.jpeg', NULL, '2026-07-09 13:15:09'),
(3, 2, 'resume', '[AM01] Ananea Madivaru Maldives (11.01.27 - 10.01.28) [USD].pdf', '', 'uploads/resumes/1783682015_resume__AM01__Ananea_Madivaru_Maldives__11.01.27_-_10.01.28___USD_.pdf', NULL, '2026-07-10 11:13:35'),
(4, 2, 'photo', 'test.png', '', 'uploads/photos/1783682015_photo_test.png', NULL, '2026-07-10 11:13:35'),
(5, 3, 'resume', '[AM01] Ananea Madivaru Maldives (11.01.27 - 10.01.28) [USD].pdf', '', 'uploads/resumes/1783682097_resume__AM01__Ananea_Madivaru_Maldives__11.01.27_-_10.01.28___USD_.pdf', NULL, '2026-07-10 11:14:57'),
(6, 3, 'photo', 'test.png', '', 'uploads/photos/1783682097_photo_test.png', NULL, '2026-07-10 11:14:57'),
(7, 4, 'photo', 'test.png', '', 'uploads/photos/1783682553_photo_test.png', NULL, '2026-07-10 11:22:33');

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
(6, 11, 'Ubs', 'Am', 'Calendar lagao', 'Calendar lagao', '10,000', NULL, '2026-07-16 05:24:59');

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
(7, 11, 'Abc', 'Drop down', 'Test', '2026-07-16 05:24:59');

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
(1, 8, 'Fritz Dixon', 'No', 'No', 'Yes', '2026-07-15 05:59:12'),
(2, 9, 'Fritz Dixon', 'No', 'No', 'Yes', '2026-07-15 06:00:56'),
(3, 10, 'Judah Love', 'No', 'Yes', 'Yes', '2026-07-15 06:01:24'),
(4, 11, 'English', 'Yes', 'Yes', 'No', '2026-07-16 05:24:59');

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
(8, 10, 'Dillon Merrill', 'Nemo et dolorum sit', 'gasep@mailinator.com', 'Itaque eos amet cup', '2026-07-15 06:01:23');

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
(1, 1, 1, 3, 'recruiter', 'submitted', 'sent_to_manager', 'Sent to manager', '2026-07-10 06:31:37'),
(2, 1, 1, 4, 'manager', 'sent_to_manager', 'manager_feedback_received', 'Manager feedback submitted: next_round', '2026-07-10 06:32:25'),
(3, 1, 2, 3, 'recruiter', 'manager_feedback_received', 'sent_to_manager', 'test2', '2026-07-10 06:33:07'),
(4, 1, 2, 4, 'manager', 'sent_to_manager', 'manager_feedback_received', 'Manager feedback submitted: reject', '2026-07-10 06:33:22'),
(5, 1, NULL, 3, 'recruiter', 'manager_feedback_received', 'rejected', 'final', '2026-07-10 06:34:06'),
(6, 5, NULL, 1, 'admin', NULL, 'submitted', 'Candidate applied from public form', '2026-07-10 12:20:27'),
(7, 5, 3, 3, 'recruiter', 'submitted', 'sent_to_manager', 'Sent to manager', '2026-07-10 12:38:17'),
(8, 6, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-13 10:01:17'),
(9, 2, NULL, 3, 'recruiter', 'submitted', 'selected', 'sdfbsdhf', '2026-07-13 13:09:06'),
(10, 3, 4, 3, 'recruiter', 'submitted', 'sent_to_manager', 'send to manager', '2026-07-13 13:09:53'),
(11, 3, 4, 4, 'manager', 'sent_to_manager', 'manager_selected', 'Manager feedback submitted: select | oky', '2026-07-13 13:10:32'),
(12, 3, 5, 3, 'recruiter', 'manager_selected', 'sent_to_manager', 'again', '2026-07-13 13:16:23'),
(13, 3, 5, 4, 'manager', 'sent_to_manager', 'manager_rejected', 'Manager feedback submitted: reject | oky', '2026-07-13 13:16:45'),
(14, 6, 6, 3, 'recruiter', 'submitted', 'sent_to_manager', 'Send to Manager - Reservation', '2026-07-14 08:02:50'),
(15, 7, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-14 13:25:27'),
(16, 7, 7, 3, 'recruiter', 'submitted', 'sent_to_manager', NULL, '2026-07-14 13:27:14'),
(17, 7, 7, 4, 'manager', 'sent_to_manager', 'manager_next_round', 'Manager feedback submitted: next_round | She is a perfect candidate, will give stability', '2026-07-14 13:27:56'),
(18, 8, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-15 05:59:12'),
(19, 8, NULL, 3, 'recruiter', 'submitted', 'rejected', 'Direct  Reject', '2026-07-15 06:00:21'),
(20, 9, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-15 06:00:56'),
(21, 10, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-15 06:01:24'),
(22, 9, NULL, 3, 'recruiter', 'submitted', 'selected', 'rejoin', '2026-07-15 06:04:07'),
(23, 10, 8, 3, 'recruiter', 'submitted', 'sent_to_manager', 'Round-1', '2026-07-15 06:04:57'),
(24, 10, 8, 4, 'manager', 'sent_to_manager', 'manager_selected', 'Manager feedback submitted: select | hi is prompt, specific, and actionable. It should balance a genuine strength with clear areas for improvement. Avoid vague statements like \"not a fit\". Instead, detail exactly what the candidate did well and what they should practice before their next interview.', '2026-07-15 06:06:50'),
(25, 10, NULL, 3, 'recruiter', 'manager_selected', 'selected', 'Select', '2026-07-15 06:07:38'),
(26, 11, NULL, NULL, 'candidate', NULL, 'submitted', 'Candidate applied from public form', '2026-07-16 05:24:59');

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
(2, 2, 1, 4, 'test', 'reject', '2026-07-10 06:33:22'),
(3, 4, 3, 4, 'oky', 'select', '2026-07-13 13:10:31'),
(4, 5, 3, 4, 'oky', 'reject', '2026-07-13 13:16:45'),
(5, 7, 7, 4, 'She is a perfect candidate, will give stability', 'next_round', '2026-07-14 13:27:56'),
(6, 8, 10, 4, 'hi is prompt, specific, and actionable. It should balance a genuine strength with clear areas for improvement. Avoid vague statements like \"not a fit\". Instead, detail exactly what the candidate did well and what they should practice before their next interview.', 'select', '2026-07-15 06:06:50');

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
(2, 1, 2, 'Round 2', 3, 4, '2026-07-10 06:33:07', 'returned_to_recruiter', '2026-07-10 13:04:00', '2026-07-09 23:33:22', '2026-07-09 23:33:22'),
(3, 5, 1, 'Round 1', 3, 4, '2026-07-10 12:38:17', 'assigned', '2026-07-10 18:08:00', NULL, NULL),
(4, 3, 1, 'Round 1', 3, 4, '2026-07-13 13:09:52', 'returned_to_recruiter', '2026-07-14 18:39:00', '2026-07-13 06:10:31', '2026-07-13 06:10:31'),
(5, 3, 2, 'Round 2', 3, 4, '2026-07-13 13:16:23', 'returned_to_recruiter', '2026-07-13 18:46:00', '2026-07-13 06:16:45', '2026-07-13 06:16:45'),
(6, 6, 1, 'Round 1', 3, 4, '2026-07-14 08:02:50', 'assigned', '2026-07-16 13:32:00', NULL, NULL),
(7, 7, 1, 'Round 1', 3, 4, '2026-07-14 13:27:14', 'returned_to_recruiter', '2026-07-14 18:57:00', '2026-07-14 06:27:56', '2026-07-14 06:27:56'),
(8, 10, 1, 'Round 1', 3, 4, '2026-07-15 06:04:56', 'returned_to_recruiter', '2026-07-15 11:34:00', '2026-07-14 23:06:50', '2026-07-14 23:06:50');

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `candidate_academics`
--
ALTER TABLE `candidate_academics`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `candidate_documents`
--
ALTER TABLE `candidate_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `candidate_experiences`
--
ALTER TABLE `candidate_experiences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `candidate_family_details`
--
ALTER TABLE `candidate_family_details`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `candidate_languages`
--
ALTER TABLE `candidate_languages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `candidate_references`
--
ALTER TABLE `candidate_references`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `candidate_status_logs`
--
ALTER TABLE `candidate_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `interview_feedback`
--
ALTER TABLE `interview_feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `interview_rounds`
--
ALTER TABLE `interview_rounds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
