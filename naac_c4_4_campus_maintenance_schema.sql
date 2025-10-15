CREATE TABLE `naac_c4_4_campus_maintenance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `expenditure_on_maintenance_lakhs` DECIMAL(10,2),
    `maintenance_systems_procedures` TEXT,
    `utilization_of_facilities` TEXT,
    `document_link_audited_statements` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);