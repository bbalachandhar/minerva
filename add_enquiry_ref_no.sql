-- Add 'ref_no' field to the 'enquiry' table for storing reference numbers
ALTER TABLE `enquiry`
ADD COLUMN `ref_no` VARCHAR(32) NOT NULL AFTER `status`;

-- Optional: Add an index for faster lookup (if needed)
-- CREATE INDEX idx_enquiry_ref_no ON `enquiry` (`ref_no`);