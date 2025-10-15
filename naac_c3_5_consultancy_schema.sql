CREATE TABLE `naac_c3_5_consultancy` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `consultant_name` VARCHAR(255) NOT NULL,
    `client_organization` VARCHAR(255) NOT NULL,
    `consultancy_area` TEXT,
    `revenue_generated_lakhs` DECIMAL(10,2),
    `document_link_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);