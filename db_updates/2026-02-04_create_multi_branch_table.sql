-- Create multi_branch table for multibranch functionality
-- Date: 2026-02-04

CREATE TABLE IF NOT EXISTS `multi_branch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(255) NOT NULL,
  `branch_url` varchar(255) NOT NULL,
  `hostname` varchar(255) NOT NULL DEFAULT 'localhost',
  `username` varchar(255) NOT NULL DEFAULT 'root',
  `password` varchar(255) NOT NULL DEFAULT '',
  `database_name` varchar(255) NOT NULL,
  `is_verified` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert sample branch data (optional - adjust values as needed)
-- INSERT INTO `multi_branch` 
-- VALUES 
-- (1, 'Branch 1', 'branch1.local', 'localhost', 'root', '', 'branch_1_db', 1),
-- (2, 'Branch 2', 'branch2.local', 'localhost', 'root', '', 'branch_2_db', 1);
