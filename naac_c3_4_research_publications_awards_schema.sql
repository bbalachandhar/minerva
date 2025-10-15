CREATE TABLE `naac_c3_4_research_publications_awards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `author_name` VARCHAR(255) NOT NULL,
    `publication_title` VARCHAR(255) NOT NULL,
    `journal_name` VARCHAR(255),
    `ugc_care_list` ENUM('Yes', 'No'),
    `indexed_in` VARCHAR(255),
    `award_name` VARCHAR(255),
    `awarding_agency` VARCHAR(255),
    `document_link_publication` VARCHAR(255),
    `document_link_award` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);