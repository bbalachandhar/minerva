CREATE TABLE `naac_c4_3_it_infrastructure` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `computer_student_ratio` DECIMAL(5,2),
    `internet_bandwidth_mbps` INT,
    `it_policy_description` TEXT,
    `e_content_development_facilities` TEXT,
    `wifi_availability_description` TEXT,
    `document_link_it_policy` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);