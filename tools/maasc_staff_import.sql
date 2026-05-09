SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ───── New Departments ─────
INSERT INTO department (department_name, is_active)
  SELECT 'Business Administration', '1'
  WHERE NOT EXISTS (SELECT 1 FROM department WHERE UPPER(department_name) = 'BUSINESS ADMINISTRATION');
INSERT INTO department (department_name, is_active)
  SELECT 'Bba', '1'
  WHERE NOT EXISTS (SELECT 1 FROM department WHERE UPPER(department_name) = 'BBA');
INSERT INTO department (department_name, is_active)
  SELECT 'Computer Applications', '1'
  WHERE NOT EXISTS (SELECT 1 FROM department WHERE UPPER(department_name) = 'COMPUTER APPLICATIONS');
INSERT INTO department (department_name, is_active)
  SELECT 'Computer Science', '1'
  WHERE NOT EXISTS (SELECT 1 FROM department WHERE UPPER(department_name) = 'COMPUTER SCIENCE');

-- ───── Staff Inserts ─────
-- MAASC001: Dr  DHANASEKARAN G
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'MAASC001', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'MAASC001');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'MAASC001', '5001', 'Dr', 'B.Sc. ELECTRONICS', 'MBA, MMM', 'M.Phil, Ph.D',
    'YES', 'BUSINESS ADMINISTRATION, MARKETING', 'PGDCA', '', '29 Years',
    'Dr  DHANASEKARAN', 'G', 'GOVINDARAJAN P', 'MANIYAMMAL C', '8056191592', '8056191592', 'drgdsekar@gmail.com',
    '1971-05-23', 'Married', '2023-08-16', NULL, 'NO-6, ANNAI NAGAR, THANDARAI X ROAD, THANDARAI, UTHIRAMERUR TK, 603403', 'NO-6, ANNAI NAGAR,             THANDARAI X ROAD, THANDARAI, UTHIRAMERUR TK, 603403',
    '', 'MALE', 'SAVINGS', '7587149703', 'INDIAN BANK', 'IDIB000U032', 'UTHIRAMERUR',
    '59000', 23600.0, NULL, 'Full Time', '1', 'Uthiramerur',
    '', '', '', '', 'YES', 'YES', 'NO',
    6, (SELECT id FROM department WHERE UPPER(department_name) = 'BUSINESS ADMINISTRATION' LIMIT 1), '814066945986', 'HINDU', 'BC', 'A1B+VE', 'INDIA', 'TAMILNADU', '603406',
    55000.0, '101774527758', 'AGTPD7695M', 'WISDOM EDUCATIONAL INSTITUTION, CHEYYAR', 'MARKETING',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'MAASC001' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'MAASC001');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'MAASC001' LIMIT 1)
  WHERE username = 'MAASC001' AND user_id = 0;

-- MAASC007: SANTHI VIRGINIA A J M J GEORGE
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'MAASC007', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'MAASC007');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'MAASC007', '5006', 'Mrs', 'B/DIT', 'MBA', 'MBA',
    '', 'HR/MARKETING', 'SHOT HAND TYPING,COUNSELLING', 'MBA', '13 YRS',
    'SANTHI VIRGINIA A J', 'M J GEORGE', 'M L ANTHIAH', 'A KANIKAI MARY', '9655040234', '9004957122', 'virginia888g@gmail.com',
    NULL, 'Married', '2013-07-29', NULL, 'No.2/57,madha koil street,manampathy kandikai ,manampathy,uthitamerur', 'No.2/57,madha koil street,manampathy kandikai ,manampathy,uthitamerur',
    '', 'FEMALE', 'SAVINGS', '587551335', 'INDIAN BANK', 'IDIB000M096', 'MANAMPATHY',
    '21000', 8400.0, NULL, '', '', 'MANAMPATHY',
    'NO', 'NO', 'NO', 'NO', '', '', '',
    46, (SELECT id FROM department WHERE UPPER(department_name) = 'BBA' LIMIT 1), '211823158018', 'CHRISTIAN', 'BC', 'O+', 'INDIA', 'TAMIL NADU', '603403',
    NULL, '100794694798', 'DXTPS7335R', 'NO', 'MANAGEMENT,ACCOUNTS',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'MAASC007' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'MAASC007');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'MAASC007' LIMIT 1)
  WHERE username = 'MAASC007' AND user_id = 0;

-- maasc020: THANIGAIVASAN B 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'maasc020', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'maasc020');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'maasc020', '5014', 'Mr', 'B.Sc Maths', 'MBA', '(PH.D)',
    '', 'HR/MARKETING', '', '', '10 YRS',
    'THANIGAIVASAN B', '', 'BALAJI K', 'VIJAYALAKSHMI P', '9629328989', '9443641624', 'btvasanlic@gmail.com',
    '1986-06-23', 'Married', '2017-01-18', NULL, 'NO.8/26,METTU STREET, UTHIRAMERUR', 'NO.8/26,METTU STREET, UTHIRAMERUR',
    '', 'MALE', 'SAVINGS', '7133502668', 'INDIAN BANK', 'IDIB000U032', 'UTHIRAMERUR',
    '20000', 7857.0, NULL, '', '', 'UTHIRAMERUR',
    'YES', 'YES', 'NO', 'YES', '', '', '',
    37, (SELECT id FROM department WHERE UPPER(department_name) = 'BBA' LIMIT 1), '255395598789', 'HINDU', 'MBC', 'O+', 'INDIA', 'TAMIL NADU', '603406',
    21000.0, '101051722285', 'ALNPT4512A', 'KANCHI PALLAVAN ENGINEERING COLLEGE', 'MANAGEMENT SUBJECTS',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'maasc020' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'maasc020');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'maasc020' LIMIT 1)
  WHERE username = 'maasc020' AND user_id = 0;

-- MAASC090: RUBINI JAGAN
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'MAASC090', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'MAASC090');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'MAASC090', '5049', 'Mrs', 'BBA', 'MBA', 'M.PHIL',
    '', 'HR/MARKETING', 'TALLY ERP 9', 'M.PHIL', '3 YRS',
    'RUBINI', 'JAGAN', 'JOHN', 'JENIFER', '8778226954', '9884292451', 'rubyjohn1722@gmail.com',
    NULL, 'Married', NULL, NULL, 'NO.22, AP CHATHIRAM,UTHIRAMERUR,KANCHIPURAM.', 'NO.22, AP CHATHIRAM,UTHIRAMERUR,KANCHIPURAM.',
    '', 'FEMALE', 'SAVINGS', '6730543421', 'INDIAN BANK', 'IDIB000U032', 'UTHIRAMERUR',
    '16500', 6600.0, NULL, '', '', 'AP CHATHIRAM',
    'NO', 'NO', 'Rubyjohn@1722', 'jaganwife-@17', '', '', '',
    37, (SELECT id FROM department WHERE UPPER(department_name) = 'BBA' LIMIT 1), '833572047107', 'CHRISTIAN', 'BC', 'O+', 'INDIA', 'TAMIL NADU', '603406',
    22204.0, '102254210682', 'CMMPR0383K', 'AKSHAYA COLLEGE OF ARTS AND SCIENCE,PUZHUTHIVAKKAM', 'MANAGEMENT,ACCOUNTS',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'MAASC090' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'MAASC090');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'MAASC090' LIMIT 1)
  WHERE username = 'MAASC090' AND user_id = 0;

-- 5003: Rajkumar G 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, '5003', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = '5003');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    '5003', '5003', 'Mr.', 'B.Sc.,', 'MCA', '',
    '', '', '', '', '26 Yrs',
    'Rajkumar G', '', 'Govintharaman M N', 'Dhanalakshmi G', '9842178706', '9442784744', 'govid.rajkumar1972@gmail.com',
    '1972-05-15', 'Married', '2007-07-02', NULL, 'F-3-P,block B,Krishu apartments,athanur road, varadharajapuram, Mudichur, Kancheepuram District-600048.', 'F-3-P,block B,Krishu apartments,athanur road, varadharajapuram, Mudichur, Kancheepuram District-600048.',
    '', 'Male', 'Saving Bank', '8218732995', 'Indian Bank', 'IDIB000U032', 'Uthiramerur',
    '22000', 8800.0, NULL, '', '', 'Vandaloor',
    '', '', '', '', '', '', '',
    46, (SELECT id FROM department WHERE UPPER(department_name) = 'COMPUTER APPLICATIONS' LIMIT 1), '632411309265', 'Hindu', 'MBC', 'A1+', 'India', 'Tamilnadu', '600048',
    6500.0, '100794702403', 'EEAPR2855N', 'Annai therasa arts and science college', 'RDBMS',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = '5003' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = '5003');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = '5003' LIMIT 1)
  WHERE username = '5003' AND user_id = 0;

-- 5048: REKHA M 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, '5048', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = '5048');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    '5048', '5048', 'Mrs.', 'B.Sc.,', 'MCA', '',
    '', '', '', 'M.ED', '4 yrs',
    'REKHA M', '', 'MURUGAN V', 'GOVINDHAMMAL M', '8825561145', '6383938907', 'rekham1419@gmail.com',
    '1983-05-19', 'Married', '2025-09-08', NULL, 'No.64 Ealonga Street,  Kadaperi , Maduranthakam, Chengalpet District-603306', 'No.64 Ealonga Street,  Kadaperi , Maduranthakam, Chengalpet District-603306',
    '', 'Female', 'Saving Bank', '6754546516', 'Indian Bank', 'IDIB000M072', 'Maduranthagam',
    '12500', 5000.0, NULL, '', '', 'Maduranthagam',
    '', '', '', '', '', '', '',
    37, (SELECT id FROM department WHERE UPPER(department_name) = 'COMPUTER APPLICATIONS' LIMIT 1), '959302881553', 'Christian', 'SC', 'O-', 'India', 'Tamilnadu', '603306',
    18000.0, '101838571726', 'CGHPM6637A', 'Govt.Hr.Sec. School,Endathur.', 'C++',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = '5048' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = '5048');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = '5048' LIMIT 1)
  WHERE username = '5048' AND user_id = 0;

-- 5046: Keerthika K 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, '5046', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = '5046');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    '5046', '5046', 'Ms', 'B.Sc.,', 'M.Sc.,', '',
    '', '', '', '', '1 Yr',
    'Keerthika K', '', 'Kirushnan M', 'Poongodi K', '9150680582', '9884211793', 'kkeerthi1960@gmail.com',
    '2003-06-19', 'Single', '2025-08-04', NULL, 'No 108,Samanthipura Street, Vedavakkam Villege,Maduranthagam Taluk,Chengalpattu District-603303', 'No 108,Samanthipura Street, Vedavakkam Villege,Maduranthagam Taluk,Chengalpattu District-603303',
    '', 'Female', 'Saving Bank', '35874465587', 'State Bank Of India', 'SBIN0003128', 'Karunguzhi',
    '12000', 4800.0, NULL, '', '', 'Vedavakkam',
    '', '', '', '', '', '', '',
    37, (SELECT id FROM department WHERE UPPER(department_name) = 'COMPUTER APPLICATIONS' LIMIT 1), '356210248868', 'Hindu', 'MBC', 'A-', 'India', 'Tamilnadu', '603303',
    NULL, '101974719983', 'QGPPK3437N', '', 'Data Structures',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = '5046' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = '5046');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = '5046' LIMIT 1)
  WHERE username = '5046' AND user_id = 0;

-- MAASC004: JOHNSIRANI J
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'MAASC004', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'MAASC004');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'MAASC004', '5004', 'Mrs.', 'B.Sc.', 'M.Sc.', 'Ph.D Part Time',
    '', 'COMPUTER SCIENCE', 'PGDCHS', 'M.Ed.', '15 Years',
    'JOHNSIRANI', 'J', 'P P JAYARAMAN', 'E J KRISHNAVENI', '9965483386', '8778584306', 'jjohnsiraniaru@gmail.com',
    '1981-07-13', 'Married', '2011-07-01', NULL, 'SF-23, PALLAVAN NAGAR,                   NEAR OPPOSITE INDIRA THEATRE, KANCHEEPURAM - 631501', 'SF-23, PALLAVAN NAGAR,                   NEAR OPPOSITE INDIRA THEATRE, KANCHEEPURAM - 631501',
    '', 'Female', 'SAVINGS', '7133383355', 'INDIAN BANK', 'IDIB000U032', 'UTHIRAMERUR',
    '21000', 8400.0, NULL, 'Full Time', '1', 'Uthiramerur',
    '', '', '', '', 'YES', 'YES', 'NO',
    46, (SELECT id FROM department WHERE UPPER(department_name) = 'COMPUTER SCIENCE' LIMIT 1), '729293251069', 'HINDU', 'SC', 'O +VE', 'INDIA', 'TAMILNADU', '603406',
    18000.0, '100794699547', 'AWKPJ2894F', 'Tiruttani Polytechnic College, Tiruttani.', 'COMPUTER SCIENCE',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'MAASC004' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'MAASC004');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'MAASC004' LIMIT 1)
  WHERE username = 'MAASC004' AND user_id = 0;

-- MAASC010: KAPILKRISHNAN A B
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'MAASC010', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'MAASC010');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'MAASC010', '5008', 'Mr.', 'B.Sc.', 'M.Sc.', 'M.Phil',
    '', 'COMPUTER SCIENCE', '', 'B.Ed.', '12 Years',
    'KAPILKRISHNAN', 'A B', 'A S BABU', 'K JEYANTHI', '9715043373', '9597221104', 'kapilkrishnanab@gmail.com',
    '1988-06-18', 'Married', '2014-06-30', NULL, 'S-145, SRI VENKATESWARA NAGAR, NEAR GOKULAM PUBLIC SCHOOL, NENMELI VILLAGE,                   CHENGALPATTU - 603003.', 'S-145, SRI VENKATESWARA NAGAR, NEAR GOKULAM PUBLIC SCHOOL, NENMELI VILLAGE,                   CHENGALPATTU - 603003.',
    '', 'Male', 'SAVINGS', '766022445', 'INDIAN BANK', 'IDIB000C061', 'CHENGALPATTU',
    '18000', 7286.0, NULL, 'Full Time', '1', 'Uthiramerur',
    '', '', '', '', 'YES', 'YES', 'NO',
    37, (SELECT id FROM department WHERE UPPER(department_name) = 'COMPUTER SCIENCE' LIMIT 1), '940686272848', 'HINDU', 'FC', 'O +VE', 'INDIA', 'TAMILNADU', '603406',
    15000.0, '100794027026', 'BKSPA9691N', 'St. Ann\'s Matric Hr Sec School, Chengalpattu.', 'COMPUTER SCIENCE',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'MAASC010' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'MAASC010');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'MAASC010' LIMIT 1)
  WHERE username = 'MAASC010' AND user_id = 0;

-- MAASC083: BHARATHI S
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'MAASC083', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'MAASC083');
INSERT INTO staff
  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,
   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,
   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,
   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,
   payscale, basic_salary, esi_no, contract_type, shift, location,
   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,
   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,
   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'MAASC083', '5042', 'Ms.', 'B.Sc.', 'M.Sc.', 'M.Sc.',
    '', 'COMPUTER SCIENCE', '', 'B.Ed.', '1 Year',
    'BHARATHI', 'S', 'V SEKAR', 'S ANJALAI', '9384367305', '9952608359', 'bs562186@gmail.com',
    '2002-07-16', 'Single', '2025-07-09', NULL, '1/15, MARIYAMMAN KOIL STREET, SOZHANTHANGAL VILLAGE, ARUNGUNAM POST, MADHURANTHAGAM TALUK, CHENGALPATTU DISTRICT - 603306.', '1/15, MARIYAMMAN KOIL STREET, SOZHANTHANGAL VILLAGE, ARUNGUNAM POST, MADHURANTHAGAM TALUK, CHENGALPATTU DISTRICT - 603306.',
    '', 'Female', 'SAVINGS', '7067059729', 'INDIAN BANK', 'IDIB000M072', 'MADHURANTHAGAM',
    '12000', 4371.0, NULL, 'Full Time', '1', 'Uthiramerur',
    '', '', '', '', 'YES', 'YES', 'NO',
    37, (SELECT id FROM department WHERE UPPER(department_name) = 'COMPUTER SCIENCE' LIMIT 1), '392623023907', 'HINDU', 'SC', 'O +VE', 'INDIA', 'TAMILNADU', '603406',
    12000.0, '', 'QDVPS4597C', 'Sri Santhoshi College of Arts and Science ,                         Maduranthagam.', 'COMPUTER SCIENCE',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'MAASC083' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'MAASC083');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'MAASC083' LIMIT 1)
  WHERE username = 'MAASC083' AND user_id = 0;

SET foreign_key_checks = 1;