CREATE TABLE `naac_criterion1` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `course_code` VARCHAR(50) NOT NULL,
    `course_title` VARCHAR(255) NOT NULL,
    `learning_outcomes` TEXT,
    `curriculum_revision_date` DATE,
    `stakeholder_feedback_mechanism` TEXT,
    `document_link` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);