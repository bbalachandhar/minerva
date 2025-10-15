CREATE TABLE `naac_c1_4_feedback_system` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `stakeholder_type` VARCHAR(50) NOT NULL,
    `feedback_mechanism` TEXT,
    `feedback_analysis_report` TEXT,
    `action_taken_report` TEXT,
    `document_link_feedback_forms` VARCHAR(255),
    `document_link_analysis_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);