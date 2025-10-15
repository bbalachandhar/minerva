CREATE TABLE `naac_c1_3_curriculum_enrichment` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `cross_cutting_issues_integrated` TEXT,
    `value_added_courses_offered` INT,
    `students_enrolled_value_added` INT,
    `project_field_work_details` TEXT,
    `document_link_value_added_syllabus` VARCHAR(255),
    `document_link_project_reports` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);