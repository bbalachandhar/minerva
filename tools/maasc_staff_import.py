#!/usr/bin/env python3
"""
maasc_staff_import.py
Import staff from staff_maasc/ Excel files into maasc production DB.
Skips any staff whose employee_id already exists.
Usage:
    python3 tools/maasc_staff_import.py            # generate SQL only
    python3 tools/maasc_staff_import.py --deploy   # generate + deploy
"""

import os, re, subprocess, sys, calendar
from datetime import datetime
import openpyxl

# ─── Config ───────────────────────────────────────────────────────────
DATA_DIR    = 'staff_maasc'
SQL_OUT     = 'tools/maasc_staff_import.sql'
EC2_HOST    = 'ec2-user@13.234.255.106'
PEM         = '/Volumes/WORK/aws ec2 connect/minerva_prod.pem'
REMOTE_SQL  = '/tmp/maasc_staff_import.sql'
DB_USER     = 'meenakshi'
DB_PASS     = "3gw4*86*!zno-EMO"
DB_NAME     = 'maasc'

LANG_ID     = 4
CURRENCY_ID = 68
BCRYPT_PW   = '$2y$12$mUoUzS1Fgc0a.qsXU1jIf.V37EhCavy82beMW9K9Zd9lqFQ9NMgyK'
PLAIN_PW    = 'Welcome@123'

# Process in this order; CS STAFF DETAILS.xlsx is intentionally omitted
# (CS STAFF UPDATED.xlsx supersedes it with complete biometric_ids).
FILE_CONFIGS = [
    'PRINCIPAL DETAILS.xlsx',
    'BBA STAFF - (2).xlsx',
    'BCA staff.xlsx',
    'CS STAFF UPDATED.xlsx',
    'Chemistry staff details new.xlsx',
    'STAFF - MATHS-2.xlsx',
    'TAMIL DEPARTMENT - STAFF DETAILS .XLSX.xlsx',
]

# employee_ids already in DB — these rows are silently skipped.
# The WHERE NOT EXISTS guard in SQL also protects idempotency.
EXISTING_EMP_IDS = {
    '9000',
    'MAASCCO16', 'MAASCC075', 'MAASCO82',          # Chemistry
    'MAASC005', 'MAASC022', 'MAASC056', 'MAAAC073', # Maths
    'MAASC013', 'MAASC014', 'MAASCO53', 'MAASC060', 'MAASC076',  # Tamil
}

# Designation text → existing staff_designation.id
DESIG_MAP = {
    'PRINCIPAL':                       6,
    'ASSISTANT PROFESSOR':             37,
    'ASSOCIATE PROFESSOR':             36,
    'PROFESSOR':                       38,
    'HOD':                             46,
    'HEAD & ASSISTANT PROFESSOR':      46,
    'HEAD COME ASSISTANT PROFESSOR':   46,
    'ASSISTANT PROFESSOR AND HOD':     46,
    'ASSISTANT PROFESSOR (SG)':        40,
    'TEACHER':                         37,
    'LECTURER':                        37,
    'ASSISTANT PROFESSOR ':            37,  # trailing space variant
}

# Known existing department.id values (from DB)
EXISTING_DEPTS = {
    'CHEMISTRY': 23,
    'MATHEMATICS': 24,
    'TAMIL': 25,
    'ARTS': 25,
}

# ─── Helpers ──────────────────────────────────────────────────────────

def esc(v):
    if v is None or str(v).strip() == '':
        return "''"
    s = str(v).replace('\\', '\\\\').replace("'", "\\'")
    return f"'{s}'"

def esc_null(v):
    """Like esc but returns NULL for empty/None."""
    if v is None:
        return 'NULL'
    s = str(v).strip()
    if not s or s.lower() in ('none', 'nil', 'null', '-', 'n/a'):
        return 'NULL'
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"

def clean_str(v, maxlen=None):
    if v is None:
        return ''
    s = str(v).strip()
    if s.lower() in ('none', 'nil', 'null', '-', 'n/a'):
        s = ''
    if maxlen:
        s = s[:maxlen]
    return s

def clean_phone(v, maxlen=20):
    if v is None:
        return ''
    s = str(v).strip()
    if s.endswith('.0'):
        s = s[:-2]
    digits = re.sub(r'\D', '', s)
    return digits[:maxlen]

def clean_numeric_str(v, maxlen=200):
    """Clean a field that may be stored as float (e.g. account numbers)."""
    if v is None:
        return ''
    s = str(v).strip()
    if s.endswith('.0'):
        s = s[:-2]
    return s[:maxlen]

def _valid_date(y, mo, d):
    if not (1900 <= y <= 2100 and 1 <= mo <= 12):
        return False
    return 1 <= d <= calendar.monthrange(y, mo)[1]

def fmt_date(v):
    if v is None:
        return 'NULL'
    if isinstance(v, datetime):
        y, mo, d = v.year, v.month, v.day
        return f"'{y:04d}-{mo:02d}-{d:02d}'" if _valid_date(y, mo, d) else 'NULL'
    s = str(v).strip()
    if not s or s.lower() in ('none', 'nil', 'null', '-'):
        return 'NULL'
    for pattern, order in [
        (r'^(\d{1,2})\.(\d{1,2})\.(\d{4})$', 'dmy'),
        (r'^(\d{1,2})-(\d{1,2})-(\d{4})$',   'dmy'),
        (r'^(\d{1,2})/(\d{1,2})/(\d{4})$',   'dmy'),
        (r'^(\d{4})-(\d{2})-(\d{2})$',        'ymd'),
    ]:
        m = re.match(pattern, s.strip())
        if m:
            if order == 'dmy':
                d, mo, y = int(m[1]), int(m[2]), int(m[3])
            else:
                y, mo, d = int(m[1]), int(m[2]), int(m[3])
            return f"'{y:04d}-{mo:02d}-{d:02d}'" if _valid_date(y, mo, d) else 'NULL'
    return 'NULL'

def clean_salary(v):
    if v is None:
        return 'NULL'
    s = str(v).strip()
    if s.lower() in ('none', 'nil', 'null', '-', 'n/a', 'no', 'nil`'):
        return 'NULL'
    try:
        return str(round(float(s), 2))
    except ValueError:
        return 'NULL'

def norm_desig_id(text):
    if not text:
        return 37
    t = text.strip().upper()
    if t in DESIG_MAP:
        return DESIG_MAP[t]
    if 'HOD' in t or ('HEAD' in t and 'PROFESSOR' in t):
        return 46
    if 'PRINCIPAL' in t:
        return 6
    if 'PROFESSOR' in t or 'LECTURER' in t or 'TEACHER' in t:
        return 37
    return 37

def norm_dept_key(text):
    if not text:
        return None
    return text.strip().upper()

def safe_resume(v):
    s = clean_str(v, 200)
    return '' if s.startswith('=DISPIMG') else s

# ─── Load staff records ───────────────────────────────────────────────

HEADER = [
    'biometric_id', 'employee_id', 'prefix', 'ug_qualification', 'pg_qualification',
    'higher_qualification', 'qualified_exam', 'subject_specialization',
    'additional_qualification', 'qualification', 'work_exp', 'name', 'surname',
    'father_name', 'mother_name', 'contact_no', 'emergency_contact_no', 'email',
    'dob', 'marital_status', 'date_of_joining', 'date_of_leaving',
    'local_address', 'permanent_address', 'note', 'gender', 'account_title',
    'bank_account_no', 'bank_name', 'ifsc_code', 'bank_branch', 'payscale',
    'basic_salary', 'esi_no', 'contract_type', 'shift', 'location',
    'facebook', 'twitter', 'linkedin', 'instagram', 'resume', 'joining_letter',
    'resignation_letter', 'designation', 'department', 'category_id', 'aadhaar_no',
    'religion', 'caste', 'blood_group', 'country', 'state', 'pincode',
    'previous_salary', 'uan_no', 'pan_no', 'previous_institution',
    'subject_expertise', 'role',
]

new_staff = {}   # UPPER(employee_id) → row dict
skipped   = []

for fname in FILE_CONFIGS:
    fpath = os.path.join(DATA_DIR, fname)
    wb = openpyxl.load_workbook(fpath, data_only=True)
    ws = wb.active
    rows = list(ws.iter_rows(values_only=True))
    header = rows[0]
    col_idx = {str(h).strip().lower(): i for i, h in enumerate(header) if h is not None}

    loaded = 0
    for row in rows[1:]:
        if all(c is None for c in row):
            continue
        emp_raw = row[col_idx.get('employee_id', 1)]
        if emp_raw is None:
            continue
        emp_id  = str(emp_raw).strip()
        emp_key = emp_id.upper()

        if emp_key in EXISTING_EMP_IDS:
            skipped.append(emp_id)
            continue
        if emp_key in new_staff:
            print(f"  SKIP duplicate in files: {emp_id}")
            continue

        d = {}
        for col in HEADER:
            idx = col_idx.get(col)
            d[col] = row[idx] if idx is not None else None
        d['employee_id'] = emp_id
        new_staff[emp_key] = d
        loaded += 1

    print(f"  {fname}: {loaded} new staff loaded")
    wb.close()

print(f"\nExisting staff skipped: {len(skipped)} ({', '.join(skipped)})")
print(f"New staff to insert:    {len(new_staff)}")
for k, d in new_staff.items():
    print(f"  {d['employee_id']} — {clean_str(d['name'])} {clean_str(d['surname'])} | {clean_str(d['department'])} | {clean_str(d['designation'])}")

# ─── Build SQL ────────────────────────────────────────────────────────

lines = [
    "SET NAMES utf8mb4;",
    "SET foreign_key_checks = 0;",
    "",
]

# Departments needed by new staff that aren't already in DB
new_depts = {}  # UPPER_KEY → display name
for d in new_staff.values():
    dept_raw = clean_str(d.get('department', ''))
    key = norm_dept_key(dept_raw)
    if key and key not in EXISTING_DEPTS and key not in new_depts:
        # Title-case the display name
        new_depts[key] = dept_raw.strip().title()

if new_depts:
    lines.append("-- ───── New Departments ─────")
    for key, display in new_depts.items():
        lines.append(f"INSERT INTO department (department_name, is_active)")
        lines.append(f"  SELECT {esc(display)}, '1'")
        lines.append(f"  WHERE NOT EXISTS (SELECT 1 FROM department WHERE UPPER(department_name) = {esc(key)});")
    lines.append("")

lines.append("-- ───── Staff Inserts ─────")
for emp_key, d in new_staff.items():
    emp_id   = d['employee_id']
    name     = clean_str(d['name'], 200)
    surname  = clean_str(d['surname'], 200)
    username = emp_id

    # Department ID expr
    dept_key = norm_dept_key(clean_str(d.get('department', '')))
    if dept_key in EXISTING_DEPTS:
        dept_expr = str(EXISTING_DEPTS[dept_key])
    elif dept_key:
        dept_expr = f"(SELECT id FROM department WHERE UPPER(department_name) = {esc(dept_key)} LIMIT 1)"
    else:
        dept_expr = 'NULL'

    desig_id = norm_desig_id(clean_str(d.get('designation', '')))

    # Field values
    biometric   = clean_numeric_str(d['biometric_id'], 255)
    prefix      = clean_str(d['prefix'], 10)
    ug_qual     = clean_str(d['ug_qualification'], 100)
    pg_qual     = clean_str(d['pg_qualification'], 100)
    higher_qual = clean_str(d['higher_qualification'], 100)
    qual_exam   = clean_str(d['qualified_exam'], 100)
    subj_spec   = clean_str(d['subject_specialization'], 255)
    add_qual    = clean_str(d['additional_qualification'])
    qual        = clean_str(d['qualification'], 200)
    work_exp    = clean_str(d['work_exp'], 200)
    father_name = clean_str(d['father_name'], 200)
    mother_name = clean_str(d['mother_name'], 200)
    contact_no  = clean_phone(d['contact_no'], 200)
    emerg_no    = clean_phone(d['emergency_contact_no'], 20)
    email       = clean_str(d['email'], 200)
    dob         = fmt_date(d['dob'])
    marital     = clean_str(d['marital_status'], 100)
    doj         = fmt_date(d['date_of_joining'])
    dol         = fmt_date(d['date_of_leaving'])
    local_addr  = clean_str(d['local_address'], 300)
    perm_addr   = clean_str(d['permanent_address'], 200)
    note        = clean_str(d['note'], 200)
    gender      = clean_str(d['gender'], 50)
    acct_title  = clean_str(d['account_title'], 200)
    bank_acct   = clean_numeric_str(d['bank_account_no'], 200)
    bank_name   = clean_str(d['bank_name'], 200)
    ifsc        = clean_str(d['ifsc_code'], 200)
    bank_branch = clean_str(d['bank_branch'], 100)
    payscale    = clean_numeric_str(d['payscale'], 200)
    basic_sal   = clean_salary(d['basic_salary'])
    contract    = clean_str(d['contract_type'], 100)
    shift       = clean_str(d['shift'], 100)
    location    = clean_str(d['location'], 100)
    facebook    = clean_str(d['facebook'], 200)
    twitter     = clean_str(d['twitter'], 200)
    linkedin    = clean_str(d['linkedin'], 200)
    instagram   = clean_str(d['instagram'], 200)
    resume      = safe_resume(d['resume'])
    join_ltr    = safe_resume(d['joining_letter'])
    resign_ltr  = safe_resume(d['resignation_letter'])
    aadhaar     = re.sub(r'[\s\-]', '', clean_numeric_str(d['aadhaar_no'], 20))
    religion    = clean_str(d['religion'], 255)
    caste       = clean_str(d['caste'], 255)
    blood_group = clean_str(d['blood_group'], 255)
    country     = clean_str(d['country'], 255)
    state       = clean_str(d['state'], 255)
    pincode     = clean_numeric_str(d['pincode'], 255)
    prev_sal    = clean_salary(d['previous_salary'])
    uan_no      = clean_numeric_str(d['uan_no'], 255)
    pan_no      = clean_str(d['pan_no'], 255)
    prev_inst   = clean_str(d['previous_institution'], 255)
    subj_exp    = clean_str(d['subject_expertise'], 255)

    bm_expr = esc(biometric) if biometric else 'NULL'

    lines.append(f"-- {emp_id}: {name} {surname}")
    # User account (user_id=0 placeholder; updated after staff insert)
    lines.append(f"INSERT INTO users (user_id, username, password, childs, role, lang_id, currency_id, verification_code, is_active)")
    lines.append(f"  SELECT 0, {esc(username)}, {esc(PLAIN_PW)}, '', 'Teacher', {LANG_ID}, {CURRENCY_ID}, '', 'yes'")
    lines.append(f"  WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = {esc(username)});")
    # Staff record
    lines.append( "INSERT INTO staff")
    lines.append( "  (employee_id, biometric_id, prefix, ug_qualification, pg_qualification, higher_qualification,")
    lines.append( "   qualified_exam, subject_specialization, additional_qualification, qualification, work_exp,")
    lines.append( "   name, surname, father_name, mother_name, contact_no, emergency_contact_no, email,")
    lines.append( "   dob, marital_status, date_of_joining, date_of_leaving, local_address, permanent_address,")
    lines.append( "   note, gender, account_title, bank_account_no, bank_name, ifsc_code, bank_branch,")
    lines.append( "   payscale, basic_salary, esi_no, contract_type, shift, location,")
    lines.append( "   facebook, twitter, linkedin, instagram, resume, joining_letter, resignation_letter,")
    lines.append( "   designation, department, aadhaar_no, religion, caste, blood_group, country, state, pincode,")
    lines.append( "   previous_salary, uan_no, pan_no, previous_institution, subject_expertise,")
    lines.append(f"   lang_id, currency_id, password, image, is_active, user_id, verification_code,")
    lines.append(f"   other_document_name, other_document_file)")
    lines.append( "  SELECT")
    lines.append(f"    {esc(emp_id)}, {bm_expr}, {esc(prefix)}, {esc(ug_qual)}, {esc(pg_qual)}, {esc(higher_qual)},")
    lines.append(f"    {esc(qual_exam)}, {esc(subj_spec)}, {esc(add_qual)}, {esc(qual)}, {esc(work_exp)},")
    lines.append(f"    {esc(name)}, {esc(surname)}, {esc(father_name)}, {esc(mother_name)}, {esc(contact_no)}, {esc(emerg_no)}, {esc(email)},")
    lines.append(f"    {dob}, {esc(marital)}, {doj}, {dol}, {esc(local_addr)}, {esc(perm_addr)},")
    lines.append(f"    {esc(note)}, {esc(gender)}, {esc(acct_title)}, {esc(bank_acct)}, {esc(bank_name)}, {esc(ifsc)}, {esc(bank_branch)},")
    lines.append(f"    {esc(payscale)}, {basic_sal}, NULL, {esc(contract)}, {esc(shift)}, {esc(location)},")
    lines.append(f"    {esc(facebook)}, {esc(twitter)}, {esc(linkedin)}, {esc(instagram)}, {esc(resume)}, {esc(join_ltr)}, {esc(resign_ltr)},")
    lines.append(f"    {desig_id}, {dept_expr}, {esc(aadhaar)}, {esc(religion)}, {esc(caste)}, {esc(blood_group)}, {esc(country)}, {esc(state)}, {esc(pincode)},")
    lines.append(f"    {prev_sal}, {esc(uan_no)}, {esc(pan_no)}, {esc(prev_inst)}, {esc(subj_exp)},")
    lines.append(f"    {LANG_ID}, {CURRENCY_ID}, {esc(BCRYPT_PW)}, '', 1,")
    lines.append(f"    (SELECT id FROM users WHERE username = {esc(username)} LIMIT 1), '',")
    lines.append(f"    '', ''")
    lines.append(f"  WHERE NOT EXISTS (SELECT 1 FROM staff WHERE employee_id = {esc(emp_id)});")
    # Update users.user_id = staff.id (back-reference)
    lines.append(f"UPDATE users SET user_id = (SELECT id FROM staff WHERE employee_id = {esc(emp_id)} LIMIT 1)")
    lines.append(f"  WHERE username = {esc(username)} AND user_id = 0;")
    lines.append("")

lines.append("SET foreign_key_checks = 1;")

sql_content = '\n'.join(lines)
with open(SQL_OUT, 'w') as f:
    f.write(sql_content)

print(f"\nSQL written: {SQL_OUT}  ({len(lines)} lines)")

# ─── Deploy ───────────────────────────────────────────────────────────
if '--deploy' not in sys.argv:
    print(f"\nTo deploy:\n  python3 {__file__} --deploy")
    sys.exit(0)

print("\nDeploying to EC2...")
scp = subprocess.run(
    ['scp', '-i', PEM, '-o', 'StrictHostKeyChecking=no', SQL_OUT, f'{EC2_HOST}:{REMOTE_SQL}'],
    capture_output=True, text=True
)
if scp.returncode != 0:
    print(f"SCP failed:\n{scp.stderr}")
    sys.exit(1)
print("SCP OK. Running SQL...")

ssh = subprocess.run(
    ['ssh', '-i', PEM, '-o', 'StrictHostKeyChecking=no', EC2_HOST,
     f"mysql -u {DB_USER} -p'{DB_PASS}' {DB_NAME} < {REMOTE_SQL}"],
    capture_output=True, text=True
)
out = '\n'.join(l for l in (ssh.stdout + ssh.stderr).splitlines()
                if 'password on the command line' not in l).strip()
if ssh.returncode != 0:
    print(f"MySQL failed: {out}")
    sys.exit(1)
print(f"Done! MySQL output: {out or '(no output = success)'}")
