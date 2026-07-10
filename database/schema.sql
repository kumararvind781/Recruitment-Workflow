CREATE DATABASE IF NOT EXISTS recruitment_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recruitment_system;

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resource VARCHAR(100) NOT NULL,
    action_name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_resource_action (resource, action_name)
);

CREATE TABLE role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE candidates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_no VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    alternate_phone VARCHAR(20) NULL,
    gender VARCHAR(20) NULL,
    dob DATE NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(20) NULL,
    highest_qualification VARCHAR(150) NULL,
    total_experience DECIMAL(5,2) DEFAULT 0.00,
    current_company VARCHAR(150) NULL,
    current_salary DECIMAL(12,2) NULL,
    expected_salary DECIMAL(12,2) NULL,
    position_applied VARCHAR(150) NOT NULL,
    department VARCHAR(100) NULL,
    source_type ENUM('walkin','qr','link','reference') DEFAULT 'walkin',
    submitted_by_recruiter_id INT UNSIGNED NULL,
    current_status VARCHAR(50) NOT NULL DEFAULT 'submitted',
    final_decision ENUM('pending','selected','rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_candidate_recruiter FOREIGN KEY (submitted_by_recruiter_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE candidate_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT UNSIGNED NOT NULL,
    document_type ENUM('resume','photo','id_proof','other') DEFAULT 'resume',
    original_file_name VARCHAR(255) NOT NULL,
    stored_file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doc_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);

CREATE TABLE interview_rounds (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT UNSIGNED NOT NULL,
    round_no INT NOT NULL,
    round_name VARCHAR(100) NOT NULL DEFAULT 'Interview Round',
    recruiter_id INT UNSIGNED NOT NULL,
    manager_id INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    interview_status ENUM('assigned','under_review','feedback_submitted','returned_to_recruiter','closed') DEFAULT 'assigned',
    scheduled_at DATETIME NULL,
    feedback_submitted_at DATETIME NULL,
    closed_at DATETIME NULL,
    CONSTRAINT fk_round_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    CONSTRAINT fk_round_recruiter FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_round_manager FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_candidate_round (candidate_id, round_no)
);

CREATE TABLE interview_feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    round_id INT UNSIGNED NOT NULL,
    candidate_id INT UNSIGNED NOT NULL,
    manager_id INT UNSIGNED NOT NULL,
    remark_text TEXT NOT NULL,
    recommendation ENUM('reject','select','next_round','hold') DEFAULT 'hold',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_round FOREIGN KEY (round_id) REFERENCES interview_rounds(id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_manager FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE TABLE candidate_status_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT UNSIGNED NOT NULL,
    round_id INT UNSIGNED NULL,
    action_by INT UNSIGNED NOT NULL,
    action_role VARCHAR(50) NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    CONSTRAINT fk_log_round FOREIGN KEY (round_id) REFERENCES interview_rounds(id) ON DELETE SET NULL,
    CONSTRAINT fk_log_user FOREIGN KEY (action_by) REFERENCES users(id) ON DELETE RESTRICT
);

ALTER TABLE users ADD COLUMN password_changed_at DATETIME NULL;
ALTER TABLE candidates ADD COLUMN source_reference_name VARCHAR(255) NULL;
