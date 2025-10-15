CREATE TABLE `naac_c5_1_student_support` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `total_students_benefited_scholarships` INT,
    `total_amount_scholarships_lakhs` DECIMAL(10,2),
    `support_mechanisms_description` TEXT,
    `capacity_building_skills_enhancement` TEXT,
    `document_link_scholarship_policy` VARCHAR(255),
    `document_link_support_services` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);