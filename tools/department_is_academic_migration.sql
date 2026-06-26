-- Add is_academic flag to department table
-- 1 = academic (default), 0 = non-academic (admin office, transport, etc.)
ALTER TABLE department
  ADD COLUMN IF NOT EXISTS is_academic TINYINT(1) NOT NULL DEFAULT 1
  AFTER dept_head_id;

-- Mark known non-academic departments
UPDATE department SET is_academic = 0
WHERE LOWER(department_name) IN (
  'admin office',
  'transport',
  'house keeping',
  'housekeeping',
  'it support team',
  'it support',
  'admission',
  'admissions',
  'accounts',
  'account'
);
