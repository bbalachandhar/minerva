CREATE TABLE `naac_c2_4_teacher_profile_quality` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `teacher_name` VARCHAR(255) NOT NULL,
    `highest_qualification` VARCHAR(255),
    `years_of_experience` INT,
    `phd_status` ENUM('Yes', 'No'),
    `professional_development_activities` TEXT,
    `document_link_cv` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);