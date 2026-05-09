SET foreign_key_checks = 0;
SET sql_mode = '';

-- ── New Departments ────────────────────────────────────────────
INSERT IGNORE INTO department (department_name, is_active)
  VALUES ('Library', 'yes');
INSERT IGNORE INTO department (department_name, is_active)
  VALUES ('Office', 'yes');
INSERT IGNORE INTO department (department_name, is_active)
  VALUES ('Transport', 'yes');
INSERT IGNORE INTO department (department_name, is_active)
  VALUES ('Electrical', 'yes');

-- ── Staff + Users ─────────────────────────────────────────────
-- AMACE007: SHANMUGAM N 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE007', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE007');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE007', 'Mr', 'Diploma', '', '',
    '', '35', 'SHANMUGAM N', '', 'Nagarathinam E', 'Kanagavalli N',
    '9884796140', '9884796140', 'Shanmugamharish@gmail.com', '1921-05-30', 'Married',
    '1994-02-01', NULL, 'No 130 Vellalar street Thirupanakadu Vembakkam', 'No 130 Vellalar street Thirupanakadu Vembakkam', '', 'Male',
    '', '556002244', 'Indian Bank', 'IDIB000V038', 'Vembakkam', '', 15100.0,
    '', '', '', '',
    24, 24, 2, '458867433693', 'Hindu', 'BC', 'OB+',
    'India', 'Tamil Nadu', '604410', NULL, '100381460635', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE007' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE007');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE007' LIMIT 1)
  WHERE username = 'AMACE007' AND user_id = 0;

-- AMACE009: VENKATESAN G 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE009', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE009');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE009', 'Mr.', 'Diploma', 'Mechanical', '',
    '', '32', 'VENKATESAN G', '', 'Govindharaj', 'Andal',
    '9751006575', '9751006575', 'venkatesankarthik74@gmail.com', '1974-06-22', 'Married',
    '1994-01-01', NULL, '54, AB/1 Pandava Perumal Vadukku mada Street, Big Kancheepuram', '54, AB/1 Pandava Perumal Vadukku mada Street, Big Kancheepuram', '', 'Male',
    '', '556012605', 'Indian Bank', '', '', '', 15500.0,
    '', '', '', '',
    24, 27, 2, '', 'Hindu', 'BC', '',
    'India', 'Tamil Nadu', '', 11280.0, '100412081192', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE009' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE009');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE009' LIMIT 1)
  WHERE username = 'AMACE009' AND user_id = 0;

-- AMACE010: VENKATACHALAM V 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE010', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE010');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE010', 'Mr.', 'ITI', '', '',
    '', '30', 'VENKATACHALAM V', '', 'Vedhagiri', 'Pattu',
    '8870542325', '8870542325', 'venkatasalamvedagri@gmail.com', '1974-04-13', 'Married',
    '1994-01-01', NULL, '401, Bajanai Koil Street, Vadamavandal Nammandi Post, Vembakkam Taluk,', '401, Bajanai Koil Street, Vadamavandal Nammandi Post, Vembakkam Taluk,', '', 'Male',
    '', '555980973', 'Indian Bank', '', '', '', 13500.0,
    '', 'No', 'No', '',
    24, 27, 2, '', 'Hindu', 'MBC', '',
    'India', 'Tamil Nadu', '', NULL, '100412054074', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE010' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE010');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE010' LIMIT 1)
  WHERE username = 'AMACE010' AND user_id = 0;

-- AMACE030: RAGUPATHI G 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE030', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE030');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE030', 'Mr.', 'BA', 'MLISc', '',
    '', '32', 'RAGUPATHI G', '', 'Govindharaj', 'Kasthuri',
    '9843996749', '9843996749', 'ragupathiamace@gmail.com', '1974-06-03', 'Married',
    '1994-01-01', NULL, '124, Mudhaliyar Street, Cheyynoor Village, Vadaeluppai Post,', '124, Mudhaliyar Street, Cheyynoor Village, Vadaeluppai Post,', '', 'Male',
    '', '556013109', 'Indian Bank', 'IDIB000V038', 'Vembakkam', '', 20000.0,
    '', 'No', 'No', '',
    32, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Library') LIMIT 1), 2, '', 'Hindu', 'BC', 'B+',
    'India', 'Tamil Nadu', '632 511', 12000.0, '100317246170', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE030' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE030');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE030' LIMIT 1)
  WHERE username = 'AMACE030' AND user_id = 0;

-- AMACE031: R.KESAVAN 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE031', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE031');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE031', 'Mr', '8th', '', '',
    '', '25', 'R.KESAVAN', '', 'Ramakrishnan M', 'Selvi M',
    '8098735066', '8098735066', 'kalaimagalkesavan@gmail.com', '1980-06-04', 'Married',
    '2009-07-17', NULL, 'No 385 Bajanai Koil Stree Vadavandal Namandi Pt Vembakkam T.V Malai dist', 'No 385 Bajanai Koil Stree Vadavandal Namandi Pt Vembakkam T.V Malai dist', '', 'Male',
    '', '854095718', 'Indian Bank', 'IDIB000V038', 'Vembakkam', '', 12000.0,
    '', 'No', 'No', '',
    22, NULL, 2, '369586905052', 'Hindu', 'MBC', 'B+',
    'India', 'Tamil Nadu', '604410', NULL, '100200492739', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE031' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE031');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE031' LIMIT 1)
  WHERE username = 'AMACE031' AND user_id = 0;

-- AMACE033: VIJAYAKUMAR C 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE033', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE033');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE033', 'Mr', 'Bsc,Bed', 'MCA', 'Mphil',
    '', '25', 'VIJAYAKUMAR C', '', 'Chinadurai C', 'Dhanamathi V',
    '', '9042725431', 'vijayakumar.vijayakumar.c@gmail.com', '1973-11-10', 'Married',
    '2011-03-09', NULL, 'No 18/7B Sathiyamoorthy street iyyappan nagag kancheepuram', 'No 18/7B Sathiyamoorthy street iyyappan nagag kancheepuram', '', 'Male',
    '', '824428757', 'Indian Bank', 'IDIB000V038', 'Vembakkam', '', 25000.0,
    '', 'No', 'No', '',
    55, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 2, '501996676726', 'Hindu', 'OC', 'O+',
    'India', 'Tamil Nadu', '601502', NULL, '100412559398', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE033' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE033');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE033' LIMIT 1)
  WHERE username = 'AMACE033' AND user_id = 0;

-- AMACE034: PAVITHRA V 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE034', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE034');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE034', 'Mrs.', 'Diploma', 'Msc', '',
    '', '21', 'PAVITHRA V', '', 'Vinayagamoorthy', 'Rajakumari',
    '9790025541', '9790025541', 'pavidharma04@gmail.com', '1983-08-18', 'Married',
    '2009-02-19', NULL, 'E 6Pallavan Nagar TNHB Kancheepuram', 'E 6Pallavan Nagar TNHB Kancheepuram', '', 'Female',
    '', '556030703', 'Indian Bank', 'IDIB000V038', 'Vembakkam', '', 15000.0,
    '', 'No', 'No', '',
    8, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 2, '320456441826', 'Hindu', 'MBC', 'O+',
    'India', 'Tamil Nadu', '601502', NULL, '100284346400', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE034' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE034');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE034' LIMIT 1)
  WHERE username = 'AMACE034' AND user_id = 0;

-- AMACE035: PARAMESWARAN P 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE035', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE035');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE035', 'Mr', 'BA, BLISC', '', '',
    '', '32', 'PARAMESWARAN P', '', 'Palani C', 'Pushpa P',
    '9943708112', '9943708112', 'parameshveng@gmail.com', '1971-07-28', 'Married',
    '1994-01-01', NULL, 'No1/41 Kamalar Street Vengalathur Vembakkam 604410', 'No1/41 Kamalar Street Vengalathur Vembakkam 604410', '', 'Male',
    '', '555984399', 'Indian Bank', 'IDIB000V038', 'Vembakkam', '', 14500.0,
    '', 'No', 'No', '',
    20, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 2, '614029718092', 'Hindu', 'BC', 'AB+',
    'India', 'Tamil Nadu', '604410', NULL, '100283943742', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE035' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE035');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE035' LIMIT 1)
  WHERE username = 'AMACE035' AND user_id = 0;

-- AMACE036: UMAMAGESHWARI M 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE036', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE036');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE036', 'Mrs.', 'B.Com', '', '',
    '', '2', 'UMAMAGESHWARI M', '', 'Asokan', 'Amsa',
    '9791266877', '7639891887', 'umaasokan1999@gmail.com', '1999-06-15', 'Married',
    '2024-12-24', NULL, '557/1, Road Street, Vadamavandal Village Namandi Post, Vembakkam Taluk,', '557/1, Road Street, Vadamavandal Village Namandi Post, Vembakkam Taluk,', '', 'Female',
    '', '7919016859', 'Indian Bank', '', '', '', 10000.0,
    '', 'No', 'No', '',
    20, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 2, '3089 9530 1294', 'Hindu', 'MBC', 'O+',
    'India', 'Tamil Nadu', '604 410', 10000.0, '102149083063', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE036' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE036');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE036' LIMIT 1)
  WHERE username = 'AMACE036' AND user_id = 0;

-- AMACE037: MAYAKANNAN S 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE037', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE037');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE037', 'Mr', '8th', '', '',
    '', '11', 'MAYAKANNAN S', '', 'Subramaniyan G', 'Dhanamal S',
    '9994352286', '9994352286', 'mayakannan7679@gmail.com', '1979-06-07', 'Married',
    '2009-07-02', NULL, 'No Pillathangal Village & Post ,Vetaikaran Street Vembakkam Tk T.V Malai Dist', 'No Pillathangal Village & Post ,Vetaikaran Street Vembakkam Tk T.V Malai Dist', '', 'Male',
    '', '848030871', 'Indian Bank', '', '', '', 15500.0,
    '', 'No', 'No', '',
    18, NULL, 2, '', 'Hindu', '', '',
    'India', 'Tamil Nadu', '', NULL, '100241974425', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE037' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE037');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE037' LIMIT 1)
  WHERE username = 'AMACE037' AND user_id = 0;

-- AMACE043: BOOPALAN M 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE043', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE043');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE043', 'Mr', '7', '', '',
    '', '32', 'BOOPALAN M', '', 'Muthupillai', 'amaravadhi',
    '9786652939', '9786652939', 'boopalan060473@gmail.com', '1973-04-06', 'Married',
    '1994-01-01', NULL, '182, Road Street, Vellkulam  Vembakkamm Talul, Tiruvannamalai Dist', '182, Road Street, Vellkulam  Vembakkamm Talul, Tiruvannamalai Dist', '', 'Male',
    '', '555968242', 'Indian Bank', '', '', '', 18500.0,
    '', 'No', 'No', '',
    30, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Transport') LIMIT 1), 2, '3609 9999 2922', 'Hindu', 'BC', 'O+',
    'India', 'Tamil Nadu', '604 410', NULL, '100120020870', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE043' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE043');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE043' LIMIT 1)
  WHERE username = 'AMACE043' AND user_id = 0;

-- AMACE044: BALASUNDARAM M 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE044', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE044');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE044', 'Mr.', '10', '12', '',
    '', '19', 'BALASUNDARAM M', '', 'Murugan', 'Kannammal',
    '9789648928', '9789648928', 'balasundaram090670@gmail.com', '1970-05-09', 'Married',
    '2011-03-09', NULL, '543, Mariyamman Koil Street,  Vayaloor Village Vembakkam Taluk,', '543, Mariyamman Koil Street,  Vayaloor Village Vembakkam Taluk,', '', 'Male',
    '', '9491268744', 'Indian Bank', '', '', '', 9950.0,
    '', 'No', 'No', '',
    15, 22, 2, '', 'Hindu', 'MBC', '',
    'India', 'Tamil Nadu', '', 5600.0, '100118587016', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE044' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE044');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE044' LIMIT 1)
  WHERE username = 'AMACE044' AND user_id = 0;

-- AMACE047: P.SANKARANARAYANAN 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE047', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE047');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE047', 'Mr.', 'B.LIT(Tamil)', '', '',
    '', '24', 'P.SANKARANARAYANAN', '', 'Periyasamamy Pillai', 'Pachiyammal',
    '9159126222', '6379797250', 'anithasankaran16@gmail.com', '1970-06-05', 'Married',
    '2004-08-01', NULL, 'No 94 Kilandai Street Azhividaithangi Village Vembakkam T.V.Malai Dist', 'No 94 Kilandai Street Azhividaithangi Village Vembakkam T.V.Malai Dist', '', 'Male',
    '', '556013541', 'Indian Bank', '', 'Vembakkam', '', 11000.0,
    '', 'No', 'No', '',
    15, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 2, '8985 9012 5793', 'Hindu', 'BC', 'O+',
    'India', 'Tamil Nadu', '604 402', 6000.0, '100379475563', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE047' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE047');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE047' LIMIT 1)
  WHERE username = 'AMACE047' AND user_id = 0;

-- AMACE048: K.SARAVANAN 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE048', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE048');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE048', 'Mr', '12th', '', '',
    '', '20', 'K.SARAVANAN', '', 'Kuppan K', 'Gangabai K',
    '9655035403', '9655035403', 'saravanan12041979@gmail.com', '1979-04-12', 'Married',
    '2011-12-09', NULL, 'No 72 Road Street Vellakulam Village Vembakkam Tk,T.V Malai Dist', 'No 72 Road Street Vellakulam Village Vembakkam Tk,T.V Malai Dist', '', 'Male',
    '', '711242705', 'Indian Bank', '', 'Vembakkam', '', 9000.0,
    '', '', '', '',
    15, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 2, '268745722771', 'Hindu', 'BC', 'O+',
    'India', 'Tamil Nadu', '604410', NULL, '100379979025', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE048' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE048');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE048' LIMIT 1)
  WHERE username = 'AMACE048' AND user_id = 0;

-- AMACE049: VARADHAN V 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE049', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE049');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE049', 'Mr', '8th', '', '',
    '', '35', 'VARADHAN V', '', 'Vijayragavan', 'Bakiyam V',
    '7358914690', '7358914690', 'varadhan01011994@gmail.com', '1969-03-05', 'Married',
    '1994-01-01', NULL, 'No 497 Shengani Amman Kovil Street Namandi T.V Malai Dist', 'No 497 Shengani Amman Kovil Street Namandi T.V Malai Dist', '', 'Male',
    '', '555980917', 'Indian Bank', '', 'Vembakkam', '', 12500.0,
    '', '', '', '',
    15, 23, 2, '700359708208', 'Hindu', 'SC', 'B+',
    'India', 'Tamil Nadu', '604410', NULL, '100411707758', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE049' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE049');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE049' LIMIT 1)
  WHERE username = 'AMACE049' AND user_id = 0;

-- AMACE050: KALAIYARASI S 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE050', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE050');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE050', 'Mr.', '10', '', '',
    '', '15', 'KALAIYARASI S', '', 'Anandhan', 'Dhanalakshmi',
    '9786134153', '9786134153', 'kalaiyarasi01082004@gmail.com', '1973-02-21', 'Married',
    '2004-08-01', NULL, 'Pallavan Polytechnic J J Nagar Iyanagar Kulam Kanchipuram dist', 'Pallavan Polytechnic J J Nagar Iyanagar Kulam Kanchipuram dist', '', 'Female',
    '', '960119424', 'Indian Bank', '', '', '', 9950.0,
    '', '', '', '',
    15, 24, 2, '4130 2494 2930', 'Hindu', 'SC', 'B+',
    'India', 'Tamil Nadu', '', NULL, '100188486289', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE050' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE050');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE050' LIMIT 1)
  WHERE username = 'AMACE050' AND user_id = 0;

-- AMACE052: THIYAGARAJAN R 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE052', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE052');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE052', 'Mr.', '8', '', '',
    '', '18', 'THIYAGARAJAN R', '', 'Rathananm', 'Vasantha',
    '9943588258', '9943588258', 'thiyagarajan01061980@gmail.com', '1980-06-01', 'Married',
    '2008-11-12', NULL, '1/415, Bajanai Koil Street, Vadamavandal Namandi Post, Vambakkam Taluk, Tiruvannamalai Dist,', '1/415, Bajanai Koil Street, Vadamavandal Namandi Post, Vambakkam Taluk, Tiruvannamalai Dist,', '', 'Male',
    '', '811527548', 'Indian Bank', '', '', '', 10250.0,
    '', '', '', '',
    15, 31, 2, '9878 7126 4796', 'Hindu', 'MBC', 'AB+',
    'India', 'Tamil Nadu', '604 410', NULL, '100393488867', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE052' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE052');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE052' LIMIT 1)
  WHERE username = 'AMACE052' AND user_id = 0;

-- AMACE053: VASANTHA P 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE053', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE053');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE053', 'Ms.', 'No', 'No', '',
    '', '35', 'VASANTHA P', '', 'Pachiyappan', 'Balammal',
    '9943820469', '9943820469', 'vasantha14071968@gmail.com', '1968-07-14', 'Unmarried',
    '2006-01-27', NULL, '464, Eswaran Kovil Street,  Vadamavandal Nammandi Post, Vembakkam Taluk,', '464, Eswaran Kovil Street,  Vadamavandal Nammandi Post, Vembakkam Taluk,', '', 'Female',
    '', '556017738', 'Indian Bank', 'IDIB000V038', 'Vembakkam', '', 9000.0,
    '', 'NO', 'NO', '',
    51, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 2, '443080418887', 'Hindu', 'MBC', '',
    'India', 'Tamil Nadu', '604 410', 3900.0, '100411807208', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE053' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE053');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE053' LIMIT 1)
  WHERE username = 'AMACE053' AND user_id = 0;

-- AMACE102: VALARMATHI S 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE102', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE102');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE102', 'Mr', 'B.Com', '', '',
    '', '', 'VALARMATHI S', '', 'Subramaniyan R', 'Naveenatham S',
    '9786480014', '9786480014', 'valarsaran1986@gmail.com', '1986-07-14', 'Married',
    '2025-11-04', NULL, 'No 33C/8 Pillayar palam Gandhi Nagar Kncheepuram', 'No 33C/8 Pillayar palam Gandhi Nagar Kncheepuram', '', 'Female',
    '', '6127305213', 'Indian Bank', 'IDIB000V038', 'Vembakkam', '', 13200.0,
    '', '', '', '',
    21, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 2, '572649674537', 'Hindu', 'BC', 'B-',
    'India', 'Tamil Nadu', '601502', NULL, '101235292061', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE102' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE102');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE102' LIMIT 1)
  WHERE username = 'AMACE102' AND user_id = 0;

-- AMACE032: SANKAR G 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE032', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE032');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE032', 'Mr.', 'No', 'No', '',
    '', '10', 'SANKAR G', '', 'Ganesan', 'Kannammal',
    '9943182265', '9943182265', 'sankar154177@gmail.com', '1977-04-15', 'Married',
    '2025-02-11', NULL, '49/A, Mariyamman Koil Street, Tirupanamoor Mettu Nagar, Pillanthangal Post, Vembakkam Taluk,', '49/A, Mariyamman Koil Street, Tirupanamoor Mettu Nagar, Pillanthangal Post, Vembakkam Taluk,', '', 'Male',
    '', '822480094', 'Indian Bank', '', 'Vembakkam', '', 10600.0,
    '', 'No', 'No', '',
    22, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Electrical') LIMIT 1), 4, '9212 1235 9779', 'Hindu', 'SC', 'B+',
    'India', 'Tamil Nadu', '604 410', 9000.0, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE032' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE032');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE032' LIMIT 1)
  WHERE username = 'AMACE032' AND user_id = 0;

-- AMACE038: MOHANAVELU A 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE038', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE038');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE038', 'Mr.', '10', '', '',
    '', '27', 'MOHANAVELU A', '', 'Annamalai', 'Kasiyammal',
    '9843745810', '9843745810', 'amohnavelu@gmail.com', '1969-05-22', 'Married',
    '2021-03-31', NULL, '84, Kuyavar Street, Vembakkam Taluk, Brammadesam Village Post,  Tiruvannamalai Dist 632 511', '84, Kuyavar Street, Vembakkam Taluk, Brammadesam Village Post,  Tiruvannamalai Dist 632 511', '', 'Male',
    '', '555991861', 'Indian Bank', '', 'Vembakkam', '', 15000.0,
    '', '', '', '',
    30, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Transport') LIMIT 1), 4, '7837 3248 3371', 'Hindu', 'BC', 'B+',
    'India', 'Tamil Nadu', '632 511', NULL, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE038' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE038');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE038' LIMIT 1)
  WHERE username = 'AMACE038' AND user_id = 0;

-- AMACE040: VENKATESAN R 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE040', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE040');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE040', 'Mr.', '11', '', '',
    '', '28', 'VENKATESAN R', '', 'Rathanam', 'Jayalakshmi',
    '7826812192', '7826812192', 'venkatesanr22112021@gmail.com', '1954-01-17', 'Married',
    '2021-11-22', NULL, '267, Velalar Street, Pillanthangal, Tiruvannamalai Dist, 604 410', '267, Velalar Street, Pillanthangal, Tiruvannamalai Dist, 604 410', '', 'Male',
    '', '556013256', 'Indian Bank', '', 'Vembakkam', '', 10500.0,
    '', 'Yes', 'No', '',
    18, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 4, '4924 5573 6844', 'Hindu', 'BC', '',
    'India', 'Tamil Nadu', '604 410', 9000.0, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE040' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE040');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE040' LIMIT 1)
  WHERE username = 'AMACE040' AND user_id = 0;

-- AMACE045: GANAPATHI M 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE045', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE045');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE045', 'Mr', '10', '', '',
    '', '27', 'GANAPATHI M', '', 'Munusamy N', 'Lakshmi Ammal',
    '9787415239', '9787415239', 'ganapathim06051967@gmail.com', '1967-05-06', 'Married',
    '1999-01-01', NULL, 'No Thalikal Village Thirupanakadu Vembakkam 604410', 'No Thalikal Village Thirupanakadu Vembakkam 604410', '', 'Male',
    '', '555989669', 'Indian Bank', '', 'Vembakkam', '', 11650.0,
    '', '', '', '',
    15, 32, 4, '945591813095', 'Hindu', 'SC', 'B+',
    'Indian', 'Tamil Nadu', '604 410', NULL, '100158408014', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE045' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE045');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE045' LIMIT 1)
  WHERE username = 'AMACE045' AND user_id = 0;

-- AMACE046: BALASUNDARAM A 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE046', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE046');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE046', 'Mr.', '10', '12', '',
    '', '28', 'BALASUNDARAM A', '', 'Arumugam', 'Elammal',
    '9047297665', '9047297665', 'balasundarama01011998@gmail.com', '1967-06-06', 'Married',
    '1998-01-01', NULL, 'Melandai Street, Tiruppanagadu Village, Vembakkam Taluk,', 'Melandai Street, Tiruppanagadu Village, Vembakkam Taluk,', '', 'Male',
    '', '555991351', 'Indian Bank', '', 'Vembakkam', '', 11950.0,
    '', 'NO', 'NO', '',
    15, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Library') LIMIT 1), 4, '2955 2453 0766', 'Hindu', 'SC', 'A+',
    'India', 'Tamil Nadu', '604 410', 6300.0, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE046' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE046');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE046' LIMIT 1)
  WHERE username = 'AMACE046' AND user_id = 0;

-- AMACE051: RAVISHANKAR K 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE051', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE051');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE051', 'Mr.', '9', '', '',
    '', '32', 'RAVISHANKAR K', '', 'Kodhai', 'Muniyammal',
    '6384997648', '6384997648', 'ravishankark01011994@gmail.com', '1967-05-19', 'Married',
    '1994-01-01', NULL, '63, Bajanai Kovil Street, Cheyyanoor  Vadaeluppai Post,', '63, Bajanai Kovil Street, Cheyyanoor Village', '', 'Male',
    '', '555991168', 'Indian Bank', '', '', '', 12500.0,
    '', '', '', '',
    18, NULL, 4, '5099 0216 0703', 'Hindu', 'SC', '',
    'India', 'Tamil Nadu', '632 511', 6140.0, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE051' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE051');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE051' LIMIT 1)
  WHERE username = 'AMACE051' AND user_id = 0;

-- AMACE054: BANUMATHI R 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE054', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE054');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE054', 'Mrs.', '10', '', '',
    '', '', 'BANUMATHI R', '', 'Thanigasalam', 'Ponnammal',
    '9025335198', '9025335198', 'banumathir05051981@gmail.com', '1980-05-05', 'Married',
    '2024-10-12', NULL, '240, Thenadumettu Mettu Street, Vengalathur, Tiruvannamalai Dist, 604410', '240, Thenadumettu Mettu Street, Vengalathur, Tiruvannamalai Dist, 604410', '', 'Female',
    '', '6404573764', 'Indian Bank', '', 'Vembakkam', '', 6000.0,
    '', 'Yes', 'No', '',
    51, NULL, 4, '3337 1035 9398', 'Hindu', 'MBC', 'A1+',
    'India', 'Tamil Nadu', '604 410', 5000.0, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE054' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE054');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE054' LIMIT 1)
  WHERE username = 'AMACE054' AND user_id = 0;

-- AMACE055: PADMA P 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE055', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE055');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE055', 'Mrs.', '7', '', '',
    '', '5', 'PADMA P', '', 'Kailasam', 'Parvadhi',
    '8148052236', '8148052236', '', '1980-10-16', 'Married',
    '2025-03-12', NULL, '4, Periya Street, Thenkalani Village, Vembakkam Taluk, Tiruvannmalai Dist 604 410', '4, Periya Street, Thenkalani Village, Vembakkam Taluk, Tiruvannmalai Dist 604 410', '', 'Female',
    '', '870337227', 'Indian Bank', '', 'Vembakkam', '', 6000.0,
    '', 'Yes', 'No', '',
    51, NULL, 4, '', 'Hindu', 'MBC', '',
    'India', 'Tamil Nadu', '604 410', 5500.0, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE055' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE055');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE055' LIMIT 1)
  WHERE username = 'AMACE055' AND user_id = 0;

-- AMACE056: NIRMALA K 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE056', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE056');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE056', 'Mrs.', '5', '', '',
    '', '5', 'NIRMALA K', '', 'Gopal', 'Venda',
    '7639905858', '7639905858', '', '1981-06-03', 'Married',
    '2020-12-27', NULL, '92, Puthu Street, Thenkazhani Azanampetai Post, Vembakkam Taluk, Tiruvannamalai Dist', '92, Puthu Street, Thenkazhani Azanampetai Post, Vembakkam Taluk, Tiruvannamalai Dist', '', 'Female',
    '', '823503260', 'Indian Bank', '', 'Vembakkam', '', 6000.0,
    '', 'Yes', 'No', '',
    51, NULL, 4, '9926 3055 8627', 'Hindu', 'MBC', '',
    'India', 'Tamil Nadu', '604 410', 5000.0, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE056' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE056');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE056' LIMIT 1)
  WHERE username = 'AMACE056' AND user_id = 0;

-- AMACE057: MYTHILI A 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE057', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE057');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE057', 'Mrs.', '8', '', '',
    '', '4', 'MYTHILI A', '', 'Arumugam', 'Mahalakshmi',
    '9943475645', '9943475645', 'mythili1983@gmail.com', '1985-10-07', 'Married',
    '2024-10-12', NULL, '97, bajanai Koil street, Arasankuppam Vembakkam, Tiruvannamalai 604 410', '97, bajanai Koil street, Arasankuppam Vembakkam, Tiruvannamalai 604 410', '', 'Female',
    '', '6047589101', 'Indian Bank', '', 'Vembakkam', '', 6000.0,
    '', '', '', '',
    51, NULL, 4, '7852 6382 3134', 'Hindu', 'SC', '',
    'India', 'Tamil Nadu', '604 410', NULL, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE057' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE057');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE057' LIMIT 1)
  WHERE username = 'AMACE057' AND user_id = 0;

-- AMACE058: SARITHA T 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE058', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE058');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE058', 'Mrs.', '', '', '',
    '', '4', 'SARITHA T', '', 'Gopal A', 'Venda G',
    '', '', 'saritht31011988@gmail.com', NULL, 'Married',
    '2025-05-02', NULL, '', '', '', 'Female',
    '', '6124893727', 'Indian Bank', '', 'Vembakkam', '', 6000.0,
    '', '', '', '',
    18, NULL, 4, '', 'Hindu', '', '',
    '', '', '', NULL, '', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE058' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE058');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE058' LIMIT 1)
  WHERE username = 'AMACE058' AND user_id = 0;

-- AMACE065: NARAYANAN D 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE065', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE065');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE065', 'Mr', '4', '', '',
    '', '6', 'NARAYANAN D', '', 'Dhurasamy Nayidu', 'Papamma',
    '9787173881', '9787173881', 'narayanand18061962@gmail.com', NULL, 'Married',
    '2025-09-01', NULL, 'Thirupanavu Vembakkam', 'Thirupanavu Vembakkam', '', 'Male',
    '', '833964026', 'Indian Bank', '', 'Vembakkam', '', 6000.0,
    '', '', '', '',
    18, NULL, 4, '', 'Hindu', '', '',
    '', '', '', NULL, '', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE065' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE065');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE065' LIMIT 1)
  WHERE username = 'AMACE065' AND user_id = 0;

-- AMACE071: KARTHI P 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE071', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE071');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE071', 'Mr.', '5', '', '',
    '', '2', 'KARTHI P', '', 'Ponnusamy Pillai', 'Poduamma',
    '7868933278', '7868933278', 'karthip1981@gmail.com', '1985-04-23', 'Married',
    '2025-07-07', NULL, '170, Road Street, Vellakulam Tiruppanangadu Tiruvannamalai 604 410', '170, Road Street, Vellakulam Tiruppanangadu Tiruvannamalai 604 410', '', 'Male',
    '', '8001020585', 'Indian Bank', '', '', '', 9500.0,
    '', '', '', '',
    53, NULL, 4, '2186 1592 2699', 'Hindu', 'BC', '',
    '', '', '', NULL, '', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE071' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE071');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE071' LIMIT 1)
  WHERE username = 'AMACE071' AND user_id = 0;

-- AMACE094: Mr.SUBRAMANI P 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE094', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE094');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE094', 'Mr.', '8', '', '',
    '', '9', 'Mr.SUBRAMANI P', '', 'Ponnusamy', 'Kaliyammal',
    '8489997209', '8489997209', 'subramanikutty01@gmail.com', '1973-03-04', 'Married',
    '2025-09-04', NULL, '15A, New Colony, Arigilapadi Ranipet Dist', '15A, New Colony, Arigilapadi Ranipet Dist', '', 'Male',
    '', '6364611333', 'Indian Bank', '', 'Vembakkam', '', 14000.0,
    '', '', '', '',
    30, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Transport') LIMIT 1), 4, '7690 6347 9877', 'Hindu', 'SC', '',
    'Indian', 'Tamil Nadu', '', NULL, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE094' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE094');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE094' LIMIT 1)
  WHERE username = 'AMACE094' AND user_id = 0;

-- AMACE096: Mrs.ELLAMMAL S 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE096', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE096');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE096', 'Mrs.', '5', '', '',
    '', '', 'Mrs.ELLAMMAL S', '', 'Mannarsamy', 'Muniyammal',
    '9043530778', '9043530778', 'ellammals27051972@gmail.com', NULL, 'Married',
    '2025-09-03', NULL, 'Perungattur Kalani Poongavanam Street,Cheyyar Taluk, Tiruvannamalai Dist', 'Perungattur Kalani Poongavanam Street,Cheyyar Taluk, Tiruvannamalai Dist', '', 'Female',
    '', '905301148', 'Indian Bank', '', 'Perungattur', '', 7500.0,
    '', 'Yes', '', '',
    51, NULL, 4, '7765 7763 9982', 'Hindu', 'SC', '',
    'Indian', 'Tamil Nadu', '604 410', 4500.0, '', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE096' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE096');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE096' LIMIT 1)
  WHERE username = 'AMACE096' AND user_id = 0;

-- AMACE099: Mr.MUNIAPPAN N 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE099', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE099');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE099', 'Mr.', 'B.com', 'ICWA', '',
    '', '2', 'Mr.MUNIAPPAN N', '', 'Nadhamunipillai M', 'Kanagavali N',
    '9994771245', '9994771245', '', '1952-06-15', 'Married',
    '2025-09-26', NULL, '50, Thiruneermalai Main Road, LIC  Colony Pammal Chennai 600 075', '50, Thiruneermalai Main Road, LIC  Colony Pammal Chennai 600 075', '', 'Male',
    '', '928357107', 'Indian Bank', '', 'Kancheepuram', '', 30000.0,
    '', '', '', '',
    55, (SELECT id FROM department WHERE LOWER(department_name)=LOWER('Office') LIMIT 1), 4, '5016 28847803', 'Hindu', 'BC', '',
    'Indian', 'Tamil Nadu', '', NULL, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE099' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE099');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE099' LIMIT 1)
  WHERE username = 'AMACE099' AND user_id = 0;

-- AMACE105: Mrs.VIJAYAKUMARI 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMACE105', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMACE105');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMACE105', 'Mrs.', '3', '', '',
    '', '4(Month)', 'Mrs.VIJAYAKUMARI', '', 'Subramani', 'Muniyammal',
    '9159920532', '9159920532', 'vijayakumar25021970@gmail.com', '1970-02-25', 'Married',
    '2025-11-25', NULL, '374, Mariyamman Koil Backsaide, Azhividaithangal Post, Tandappanthangal Cheyyar, 604 402', '374, Mariyamman Koil Backsaide, Azhividaithangal Post, Tandappanthangal Cheyyar, 604 402', '', 'Female',
    '', '869229045', 'Indian Bank', '', 'Perungattur', '', 6000.0,
    '', '', '', '',
    51, NULL, 4, '8826 7407 8918', 'Hindu', 'SC', '',
    '', '', '', NULL, '', '',
    'AMACE', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMACE105' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMACE105');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMACE105' LIMIT 1)
  WHERE username = 'AMACE105' AND user_id = 0;

-- AMAEE166: Mrs.SHANTHI A 
INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)
  SELECT 0, 'AMAEE166', 'Welcome@123', '', 'Teacher', 4, 68, '', 'yes'
  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'AMAEE166');
INSERT INTO staff (employee_id, prefix, ug_qualification, pg_qualification, higher_qualification,
   qualification, work_exp, name, surname, father_name, mother_name,
   contact_no, emergency_contact_no, email, dob, marital_status,
   date_of_joining, date_of_leaving, local_address, permanent_address, note, gender,
   account_title, bank_account_no, bank_name, ifsc_code, bank_branch, payscale, basic_salary,
   esi_no, contract_type, shift, location,
   designation, department, category_id, aadhaar_no, religion, caste, blood_group,
   country, state, pincode, previous_salary, uan_no, pan_no,
   previous_institution, subject_expertise,
   lang_id, currency_id, password, image, is_active, user_id, verification_code,
   other_document_name, other_document_file)
  SELECT
    'AMAEE166', 'Mrs.', '', '', '',
    '', '', 'Mrs.SHANTHI A', '', '', '',
    '', '', '', NULL, '',
    NULL, NULL, '', '', '', '',
    '', '', '', '', '', '', 6000.0,
    '', '', '', '',
    18, NULL, NULL, '', '', '', '',
    '', '', '', NULL, '', '',
    '', '',
    4, 68, '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK', '', 1,
    (SELECT id FROM users WHERE username = 'AMAEE166' LIMIT 1), '',
    '', ''
  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = 'AMAEE166');
UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = 'AMAEE166' LIMIT 1)
  WHERE username = 'AMAEE166' AND user_id = 0;

SET foreign_key_checks = 1;