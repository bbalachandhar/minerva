CREATE TABLE `naac_c3_2_resource_mobilization` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `teacher_name` VARCHAR(255) NOT NULL,
    `project_title` VARCHAR(255) NOT NULL,
    `funding_agency` VARCHAR(255),
    `amount_received_lakhs` DECIMAL(10,2),
    `project_type` VARCHAR(50),
    `document_link_sanction_letter` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);