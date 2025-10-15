CREATE TABLE `naac_c4_1_physical_facilities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `classrooms_ict_enabled_percentage` DECIMAL(5,2),
    `seminar_halls_ict_enabled_percentage` DECIMAL(5,2),
    `physical_facilities_description` TEXT,
    `facilities_for_cultural_sports` TEXT,
    `document_link_facilities_audit` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);