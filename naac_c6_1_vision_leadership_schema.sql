CREATE TABLE `naac_c6_1_vision_leadership` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `governance_vision_mission_alignment` TEXT,
    `leadership_effectiveness_description` TEXT,
    `decentralization_participative_management` TEXT,
    `document_link_vision_mission` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);