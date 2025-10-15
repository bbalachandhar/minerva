CREATE TABLE `naac_c5_4_alumni_engagement` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `alumni_association_registered` ENUM('Yes', 'No'),
    `alumni_contribution_description` TEXT,
    `alumni_engagement_activities` TEXT,
    `document_link_alumni_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);