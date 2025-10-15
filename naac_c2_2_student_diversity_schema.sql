CREATE TABLE `naac_c2_2_student_diversity` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `learning_level_assessment_methods` TEXT,
    `advanced_learner_programs` TEXT,
    `slow_learner_programs` TEXT,
    `support_for_diverse_learners` TEXT,
    `document_link_support_policy` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);