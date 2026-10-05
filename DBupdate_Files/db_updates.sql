-- Generated database update file
-- Review this file before running on LIVE.


-- =====================================================
-- DATABASE UPDATE
-- Generated: 2026-10-05 07:51:53
-- Database: recruitment_workflow
-- =====================================================

-- NEW TABLE: exam_access
CREATE TABLE `exam_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` int unsigned NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_exam_access_candidate` (`candidate_id`),
  UNIQUE KEY `uq_exam_access_username` (`username`),
  KEY `idx_exam_access_candidate` (`candidate_id`),
  CONSTRAINT `fk_exam_access_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- NEW TABLE: exam_answers
CREATE TABLE `exam_answers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` int unsigned NOT NULL,
  `question_id` int unsigned NOT NULL,
  `question_order` int NOT NULL,
  `candidate_answer` enum('A','B','C','D') DEFAULT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `marks_obtained` decimal(5,2) NOT NULL DEFAULT '0.00',
  `answered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attempt_question` (`attempt_id`,`question_id`),
  KEY `idx_exam_answers_attempt` (`attempt_id`),
  KEY `idx_exam_answers_question` (`question_id`),
  CONSTRAINT `fk_exam_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_exam_answers_question` FOREIGN KEY (`question_id`) REFERENCES `exam_questions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;

-- NEW TABLE: exam_attempts
CREATE TABLE `exam_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` int unsigned NOT NULL,
  `application_no` varchar(30) NOT NULL,
  `exam_type` varchar(50) NOT NULL DEFAULT 'process_associate',
  `total_questions` int NOT NULL DEFAULT '20',
  `attempted_questions` int NOT NULL DEFAULT '0',
  `correct_answers` int NOT NULL DEFAULT '0',
  `wrong_answers` int NOT NULL DEFAULT '0',
  `unattempted_questions` int NOT NULL DEFAULT '0',
  `total_marks` decimal(7,2) NOT NULL DEFAULT '0.00',
  `obtained_marks` decimal(7,2) NOT NULL DEFAULT '0.00',
  `percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('started','completed','expired') NOT NULL DEFAULT 'started',
  `started_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_exam_attempts_candidate` (`candidate_id`),
  KEY `idx_exam_attempts_application` (`application_no`),
  CONSTRAINT `fk_exam_attempts_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- NEW TABLE: exam_questions
CREATE TABLE `exam_questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `exam_type` varchar(50) NOT NULL DEFAULT 'process_associate',
  `question` text NOT NULL,
  `option_a` varchar(500) NOT NULL,
  `option_b` varchar(500) NOT NULL,
  `option_c` varchar(500) NOT NULL,
  `option_d` varchar(500) NOT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `marks` decimal(5,2) NOT NULL DEFAULT '1.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_exam_questions_type_active` (`exam_type`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

-- End of generated changes
