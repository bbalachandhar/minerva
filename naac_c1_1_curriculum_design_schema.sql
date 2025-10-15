CREATE TABLE `naac_c1_1_curriculum_design` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `course_code` VARCHAR(50) NOT NULL,
    `course_title` VARCHAR(255) NOT NULL,
    `po_pso_co_relevance` TEXT,
    `curriculum_development_process` TEXT,
    `curriculum_revision_date` DATE,
    `document_link_syllabus` VARCHAR(255),
    `document_link_minutes` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);