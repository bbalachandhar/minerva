-- Add missing column for online admission processing charge
-- Date: 2026-02-03
-- Purpose: Store the processing charge amount for online admissions
-- Note: Must be applied to all branch databases

-- For main database (mcekknagar)
USE mcekknagar;
ALTER TABLE sch_settings ADD COLUMN IF NOT EXISTS online_admission_processing_charge FLOAT(10,2) DEFAULT 0 AFTER online_admission_processing_charge_type;

-- For branch database (amacedu)
USE amacedu;
ALTER TABLE sch_settings ADD COLUMN IF NOT EXISTS online_admission_processing_charge FLOAT(10,2) DEFAULT 0 AFTER online_admission_processing_charge_type;

-- Verify the column was added
-- SHOW COLUMNS FROM sch_settings WHERE Field = 'online_admission_processing_charge';
