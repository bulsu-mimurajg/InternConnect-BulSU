-- InternConnect-BulSU Database Schema and Seeders Export
-- Generated on: September 11, 2025
-- This file contains all migrations and seeder data converted to SQL

-- =====================================================
-- TABLE CREATION STATEMENTS (FROM MIGRATIONS)
-- =====================================================

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('verified','archived','unverified') NOT NULL DEFAULT 'unverified',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens table
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cache tables
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jobs tables
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subcategories table
CREATE TABLE IF NOT EXISTS `sub_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subcategory_name` varchar(100) NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sub_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `sub_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HTE table
CREATE TABLE IF NOT EXISTS `htes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `company_email` varchar(100) DEFAULT NULL,
  `cperson_fname` varchar(50) DEFAULT NULL,
  `cperson_lname` varchar(50) DEFAULT NULL,
  `cperson_position` varchar(50) DEFAULT NULL,
  `cperson_contactnum` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_submit` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `htes_user_id_foreign` (`user_id`),
  CONSTRAINT `htes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Internships table
CREATE TABLE IF NOT EXISTS `internships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hte_id` bigint(20) unsigned NOT NULL,
  `position_title` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `placement_description` text NOT NULL,
  `slot_count` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `internships_hte_id_foreign` (`hte_id`),
  CONSTRAINT `internships_hte_id_foreign` FOREIGN KEY (`hte_id`) REFERENCES `htes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subcategory weights table
CREATE TABLE IF NOT EXISTS `subcategory_weights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subcategory_id` bigint(20) unsigned NOT NULL,
  `hte_id` bigint(20) unsigned NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subcategory_weights_subcategory_id_foreign` (`subcategory_id`),
  KEY `subcategory_weights_hte_id_foreign` (`hte_id`),
  CONSTRAINT `subcategory_weights_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subcategory_weights_hte_id_foreign` FOREIGN KEY (`hte_id`) REFERENCES `htes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Questions table
CREATE TABLE IF NOT EXISTS `questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `subcategory_id` bigint(20) unsigned NOT NULL,
  `access` varchar(255) NOT NULL DEFAULT 'Student',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questions_subcategory_id_foreign` (`subcategory_id`),
  CONSTRAINT `questions_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deadlines table
CREATE TABLE IF NOT EXISTS `deadlines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `deadline_name` varchar(100) NOT NULL,
  `deadline_date` date NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permission tables
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sections table
CREATE TABLE IF NOT EXISTS `sections` (
  `section_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section_name` varchar(10) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`section_id`),
  UNIQUE KEY `sections_section_name_unique` (`section_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Students table
CREATE TABLE IF NOT EXISTS `students` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `section_id` int(10) unsigned NOT NULL,
  `specialization` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `birth_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_submit` tinyint(1) NOT NULL DEFAULT 0,
  `is_placed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `students_user_id_foreign` (`user_id`),
  KEY `students_section_id_foreign` (`section_id`),
  CONSTRAINT `students_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student score table
CREATE TABLE IF NOT EXISTS `student_score` (
  `student_id` bigint(20) unsigned NOT NULL,
  `sub_category_id` bigint(20) unsigned NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`student_id`,`sub_category_id`),
  KEY `student_score_sub_category_id_foreign` (`sub_category_id`),
  CONSTRAINT `student_score_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_score_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student matches table
CREATE TABLE IF NOT EXISTS `student_matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `internship_id` bigint(20) unsigned NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `compatibility_score` decimal(5,2) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_matches_student_id_foreign` (`student_id`),
  KEY `student_matches_internship_id_foreign` (`internship_id`),
  CONSTRAINT `student_matches_internship_id_foreign` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_matches_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student placements table
CREATE TABLE IF NOT EXISTS `student_placements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `internship_id` bigint(20) unsigned NOT NULL,
  `placement_date` date NOT NULL,
  `status` enum('active','completed','terminated') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_placements_student_id_foreign` (`student_id`),
  KEY `student_placements_internship_id_foreign` (`internship_id`),
  CONSTRAINT `student_placements_internship_id_foreign` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_placements_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Requests table
CREATE TABLE IF NOT EXISTS `requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `internship_id` bigint(20) unsigned NOT NULL,
  `request_type` enum('placement','transfer') NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reason` text,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `requests_student_id_foreign` (`student_id`),
  KEY `requests_internship_id_foreign` (`internship_id`),
  KEY `requests_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `requests_internship_id_foreign` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Academe accounts table
CREATE TABLE IF NOT EXISTS `academe_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academe_accounts_user_id_foreign` (`user_id`),
  CONSTRAINT `academe_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Advisers table
CREATE TABLE IF NOT EXISTS `advisers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `advisers_user_id_foreign` (`user_id`),
  CONSTRAINT `advisers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email verification attempts table
CREATE TABLE IF NOT EXISTS `email_verification_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_verification_attempts_user_id_foreign` (`user_id`),
  KEY `email_verification_attempts_email_index` (`email`),
  KEY `email_verification_attempts_token_unique` (`token`),
  CONSTRAINT `email_verification_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEEDER DATA
-- =====================================================

-- Insert default users (admin, hte, adviser, student)
INSERT INTO `users` (`username`, `email`, `email_verified_at`, `password`, `status`, `created_at`, `updated_at`) VALUES
('faye', 'faye@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified', NOW(), NOW()),
('maria', 'maria@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified', NOW(), NOW()),
('emman', 'emman@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified', NOW(), NOW()),
('clairo', 'clairo@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'unverified', NOW(), NOW());

-- Insert sections
INSERT INTO `sections` (`section_name`, `status`, `created_at`, `updated_at`) VALUES
('3A-G1', 'active', NOW(), NOW()),
('3A-G2', 'active', NOW(), NOW()),
('3A-G3', 'active', NOW(), NOW()),
('3A-G4', 'active', NOW(), NOW()),
('3B-G1', 'active', NOW(), NOW()),
('3B-G2', 'active', NOW(), NOW()),
('3B-G3', 'active', NOW(), NOW()),
('3B-G4', 'active', NOW(), NOW()),
('3C-G1', 'active', NOW(), NOW()),
('3C-G2', 'active', NOW(), NOW()),
('3C-G3', 'active', NOW(), NOW()),
('3C-G4', 'active', NOW(), NOW()),
('BSIT-4A', 'active', NOW(), NOW()),
('BSIT-4B', 'active', NOW(), NOW()),
('BSIT-4C', 'active', NOW(), NOW());

-- Insert categories
INSERT INTO `categories` (`category_name`, `created_at`, `updated_at`) VALUES
('Language Proficiency', NOW(), NOW()),
('Technical Skill', NOW(), NOW()),
('Soft Skill', NOW(), NOW());

-- Insert subcategories for Language Proficiency
INSERT INTO `sub_categories` (`subcategory_name`, `category_id`, `created_at`, `updated_at`) VALUES
('Java', 1, NOW(), NOW()),
('C++', 1, NOW(), NOW()),
('Python', 1, NOW(), NOW()),
('HTML/CSS', 1, NOW(), NOW()),
('JavaScript', 1, NOW(), NOW()),
('PHP', 1, NOW(), NOW()),
('SQL', 1, NOW(), NOW());

-- Insert subcategories for Technical Skill
INSERT INTO `sub_categories` (`subcategory_name`, `category_id`, `created_at`, `updated_at`) VALUES
('Database Management', 2, NOW(), NOW()),
('Web Development', 2, NOW(), NOW()),
('System and Software Development', 2, NOW(), NOW());

-- Insert subcategories for Soft Skill
INSERT INTO `sub_categories` (`subcategory_name`, `category_id`, `created_at`, `updated_at`) VALUES
('Communication Skills', 3, NOW(), NOW()),
('Problem-Solving and Analytical Skills', 3, NOW(), NOW()),
('Time Management', 3, NOW(), NOW()),
('Adaptability and Learning', 3, NOW(), NOW()),
('Ethical Decision-Making', 3, NOW(), NOW()),
('Professionalism', 3, NOW(), NOW());

-- Insert questions for Java
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('What is your proficiency level in Java?', 1, 'Student', 1, NOW(), NOW()),
('Have you worked with Java frameworks such as Spring?', 1, 'Student', 1, NOW(), NOW()),
('Can you write Java programs following OOP principles?', 1, 'Student', 1, NOW(), NOW());

-- Insert questions for C++
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Are you comfortable with C++ memory management?', 2, 'Student', 1, NOW(), NOW()),
('Have you used STL in C++ programming?', 2, 'Student', 1, NOW(), NOW()),
('Can you develop applications using C++ classes and objects?', 2, 'Student', 1, NOW(), NOW());

-- Insert questions for Python
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Do you have experience with Python scripting?', 3, 'Student', 1, NOW(), NOW()),
('Have you worked with Python frameworks like Django or Flask?', 3, 'Student', 1, NOW(), NOW()),
('Can you automate tasks using Python?', 3, 'Student', 1, NOW(), NOW());

-- Insert questions for HTML/CSS
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Are you proficient in writing semantic HTML?', 4, 'Student', 1, NOW(), NOW()),
('Can you style websites effectively using CSS?', 4, 'Student', 1, NOW(), NOW()),
('Have you worked with CSS preprocessors like SASS or LESS?', 4, 'Student', 1, NOW(), NOW());

-- Insert questions for JavaScript
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Do you have experience with vanilla JavaScript?', 5, 'Student', 1, NOW(), NOW()),
('Have you used any JS frameworks like React or Angular?', 5, 'Student', 1, NOW(), NOW()),
('Can you manipulate the DOM with JavaScript?', 5, 'Student', 1, NOW(), NOW());

-- Insert questions for PHP
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Are you familiar with PHP syntax and features?', 6, 'Student', 1, NOW(), NOW()),
('Have you developed web applications using PHP?', 6, 'Student', 1, NOW(), NOW()),
('Can you work with PHP frameworks such as Laravel or CodeIgniter?', 6, 'Student', 1, NOW(), NOW());

-- Insert questions for SQL
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Can you write complex SQL queries?', 7, 'Student', 1, NOW(), NOW()),
('Have you worked with database normalization?', 7, 'Student', 1, NOW(), NOW()),
('Do you know how to optimize SQL queries for performance?', 7, 'Student', 1, NOW(), NOW());

-- Insert questions for Database Management
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Designing Databases', 8, 'Student', 1, NOW(), NOW()),
('Writing SQL Queries', 8, 'Student', 1, NOW(), NOW()),
('Database Administration', 8, 'Student', 1, NOW(), NOW()),
('Using tools like MySQL, Oracle etc.', 8, 'Student', 1, NOW(), NOW());

-- Insert questions for Web Development
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Designing user interfaces (UI)', 9, 'Student', 1, NOW(), NOW()),
('Developing responsive websites', 9, 'Student', 1, NOW(), NOW()),
('Using front-end frameworks (e.g., Bootstrap, React)', 9, 'Student', 1, NOW(), NOW()),
('Back-end development (e.g., Node.js, Django)', 9, 'Student', 1, NOW(), NOW());

-- Insert questions for System and Software Development
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Gathering and analyzing requirements', 10, 'Student', 1, NOW(), NOW()),
('Software design and architecture', 10, 'Student', 1, NOW(), NOW()),
('Development using Agile/Scrum', 10, 'Student', 1, NOW(), NOW()),
('Testing and debugging applications', 10, 'Student', 1, NOW(), NOW()),
('System maintenance and troubleshooting', 10, 'Student', 1, NOW(), NOW());

-- Insert questions for Communication Skills
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Explaining technical concepts to non-technical people', 11, 'Student', 1, NOW(), NOW()),
('Collaborating with team members', 11, 'Student', 1, NOW(), NOW()),
('Writing clear documentation and reports', 11, 'Student', 1, NOW(), NOW());

-- Insert questions for Problem-Solving and Analytical Skills
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Independently solve complex problems or debug issues?', 12, 'Student', 1, NOW(), NOW()),
('Research solutions before seeking help from others?', 12, 'Student', 1, NOW(), NOW()),
('Think critically when troubleshooting technical problems?', 12, 'Student', 1, NOW(), NOW());

-- Insert questions for Time Management
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Prioritizing tasks effectively', 13, 'Student', 1, NOW(), NOW()),
('Meeting project deadlines', 13, 'Student', 1, NOW(), NOW());

-- Insert questions for Adaptability and Learning
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Adapt to new tools and technologies quickly?', 14, 'Student', 1, NOW(), NOW()),
('Show a willingness to learn independently?', 14, 'Student', 1, NOW(), NOW()),
('Stay updated on emerging IT trends?', 14, 'Student', 1, NOW(), NOW());

-- Insert questions for Ethical Decision-Making
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Data privacy and security protocols?', 15, 'Student', 1, NOW(), NOW()),
('Ethical issues like intellectual property rights?', 15, 'Student', 1, NOW(), NOW());

-- Insert questions for Professionalism
INSERT INTO `questions` (`question`, `subcategory_id`, `access`, `is_active`, `created_at`, `updated_at`) VALUES
('Punctuality and reliability', 16, 'Student', 1, NOW(), NOW()),
('Following company policies and procedures', 16, 'Student', 1, NOW(), NOW()),
('Being receptive to constructive feedback and improving performance accordingly', 16, 'Student', 1, NOW(), NOW());

-- =====================================================
-- ADDITIONAL SEEDER DATA PLACEHOLDERS
-- =====================================================

-- Note: The following seeders contain dynamic data that would typically be generated at runtime.
-- You may need to run the Laravel seeders individually to get the complete dataset:
--
-- php artisan db:seed --class=RolePermissionSeeder
-- php artisan db:seed --class=AcademeAccountSeeder
-- php artisan db:seed --class=HTESeeder
-- php artisan db:seed --class=AdviserSeeder
-- php artisan db:seed --class=InternshipSeeder
-- php artisan db:seed --class=ExtendedInternshipSeeder
-- php artisan db:seed --class=StudentSeeder
-- php artisan db:seed --class=ExtendedStudentSeeder
-- php artisan db:seed --class=InternshipCriteriaSeeder
-- php artisan db:seed --class=StudentScoreSeeder
-- php artisan db:seed --class=StudentMatchSeeder
-- php artisan db:seed --class=PlacementSeeder

-- Example HTE entry (you would need to run the actual seeder for complete data)
-- INSERT INTO `htes` (`user_id`, `company_name`, `is_active`, `is_submit`, `created_at`, `updated_at`) VALUES
-- (2, 'Tech Solutions Inc.', 1, 1, NOW(), NOW());

-- Example Student entry (you would need to run the actual seeder for complete data)
-- INSERT INTO `students` (`user_id`, `student_number`, `first_name`, `last_name`, `phone`, `section_id`, `specialization`, `address`, `birth_date`, `created_at`, `updated_at`) VALUES
-- (4, '2021001', 'John', 'Doe', '+1234567890', 13, 'Web Development', '123 Main St', '2000-01-01', NOW(), NOW());

-- =====================================================
-- INDEXES AND OPTIMIZATIONS
-- =====================================================

-- Add any additional indexes that might be beneficial for performance
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_email_verified ON users(email_verified_at);
CREATE INDEX idx_students_section ON students(section_id);
CREATE INDEX idx_students_active ON students(is_active, is_submit, is_placed);
CREATE INDEX idx_internships_active ON internships(is_active);
CREATE INDEX idx_student_matches_status ON student_matches(status);
CREATE INDEX idx_questions_active ON questions(is_active);
CREATE INDEX idx_subcategory_weights_hte ON subcategory_weights(hte_id);

-- =====================================================
-- END OF SQL EXPORT
-- =====================================================
