#!/usr/bin/env bash
set -euo pipefail

# Create a clean package for onboarding a new institution:
# - Clean code copy (runtime + uploads sanitized)
# - DB schema dump (no data)
# - Seed-only data dump for platform/master tables

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
STAMP="$(date +%Y%m%d_%H%M%S)"
OUT_DIR_DEFAULT="$ROOT_DIR/dist/new_institution_$STAMP"
OUT_DIR="${OUT_DIR:-$OUT_DIR_DEFAULT}"

DB_HOST="${DB_HOST:-localhost}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-mcekknagar}"
DUMP_ROUTINES="${DUMP_ROUTINES:-0}"

MYSQL_BIN="${MYSQL_BIN:-}"
MYSQLDUMP_BIN="${MYSQLDUMP_BIN:-}"

if [[ -z "$MYSQL_BIN" ]]; then
  if command -v mysql >/dev/null 2>&1; then
    MYSQL_BIN="$(command -v mysql)"
  else
    MYSQL_BIN="/Applications/XAMPP/xamppfiles/bin/mysql"
  fi
fi

if [[ -z "$MYSQLDUMP_BIN" ]]; then
  if command -v mysqldump >/dev/null 2>&1; then
    MYSQLDUMP_BIN="$(command -v mysqldump)"
  else
    MYSQLDUMP_BIN="/Applications/XAMPP/xamppfiles/bin/mysqldump"
  fi
fi

if [[ ! -x "$MYSQL_BIN" ]]; then
  echo "ERROR: mysql client not found. Set MYSQL_BIN env var." >&2
  exit 1
fi

if [[ ! -x "$MYSQLDUMP_BIN" ]]; then
  echo "ERROR: mysqldump client not found. Set MYSQLDUMP_BIN env var." >&2
  exit 1
fi

if command -v rsync >/dev/null 2>&1; then
  RSYNC_BIN="$(command -v rsync)"
else
  echo "ERROR: rsync is required for packaging." >&2
  exit 1
fi

MYSQL_ARGS=("-h" "$DB_HOST" "-u" "$DB_USER")
MYSQLDUMP_ARGS=("-h" "$DB_HOST" "-u" "$DB_USER")
if [[ -n "$DB_PASS" ]]; then
  MYSQL_ARGS+=("-p$DB_PASS")
  MYSQLDUMP_ARGS+=("-p$DB_PASS")
fi

sanitize_sql_dump() {
  local sql_file="$1"
  local tmp_file
  tmp_file="${sql_file}.tmp"

  # Remove DEFINER clauses so imports work on shared/limited DB users.
  sed -E \
    -e 's/\/\*![0-9]{5}[[:space:]]+DEFINER=`[^`]+`@`[^`]+`[[:space:]]+SQL[[:space:]]+SECURITY[[:space:]]+DEFINER[[:space:]]*\*\//\/\* definer stripped for portability \*\//g' \
    -e 's/DEFINER=`[^`]+`@`[^`]+`[[:space:]]+//g' \
    -e 's/SQL SECURITY DEFINER/SQL SECURITY INVOKER/g' \
    "$sql_file" > "$tmp_file"

  mv "$tmp_file" "$sql_file"
}

SEED_TABLES=(
  addon_versions
  addons
  attendence_type
  certificates
  captcha
  currencies
  disable_reason
  email_config
  feetype
  fees_reminder
  filetypes
  holiday_type
  id_card
  incidental_fee_types
  languages
  migrations
  notification_setting
  online_admission_fields
  online_admission_universities
  payment_settings
  payroll_allowance_types
  payroll_settings
  permission_category
  permission_group
  permission_student
  print_headerfooter
  reference
  resume_additional_fields_settings
  resume_settings_fields
  roles
  roles_permissions
  staff_roles
  sch_settings
  sessions
  sidebar_menus
  sidebar_sub_menus
  source
  staff
  staff_attendance_type
  staff_designation
  staff_designation_category
  staff_id_card
  student_dashboard_settings
  student_edit_fields
  template_admitcards
  template_marksheets
  transport_feemaster
)

echo "[1/6] Preparing output directories..."
mkdir -p "$OUT_DIR"
mkdir -p "$OUT_DIR/db"

PACKAGE_CODE_DIR="$OUT_DIR/code"
mkdir -p "$PACKAGE_CODE_DIR"

echo "[2/6] Copying codebase (excluding volatile and institution assets)..."
"$RSYNC_BIN" -a \
  --exclude '.git' \
  --exclude '.venv' \
  --exclude '.vscode' \
  --exclude 'dist' \
  --exclude 'uploads/*' \
  --exclude 'application/logs/*' \
  --exclude 'application/cache/*' \
  --exclude 'application/sessions/*' \
  --exclude 'application/tmp/*' \
  "$ROOT_DIR/" "$PACKAGE_CODE_DIR/"

echo "[3/6] Recreating clean runtime and uploads directories..."
mkdir -p "$PACKAGE_CODE_DIR/uploads"
mkdir -p "$PACKAGE_CODE_DIR/application/logs"
mkdir -p "$PACKAGE_CODE_DIR/application/cache"
mkdir -p "$PACKAGE_CODE_DIR/application/sessions"
mkdir -p "$PACKAGE_CODE_DIR/application/tmp"

# Keep CI directory protection files where available.
for rel in \
  "uploads/index.html" \
  "application/logs/index.html" \
  "application/cache/index.html" \
  "application/sessions/index.html" \
  "application/tmp/index.html"; do
  if [[ -f "$ROOT_DIR/$rel" ]]; then
    cp "$ROOT_DIR/$rel" "$PACKAGE_CODE_DIR/$rel"
  else
    mkdir -p "$(dirname "$PACKAGE_CODE_DIR/$rel")"
    echo "<html><body></body></html>" > "$PACKAGE_CODE_DIR/$rel"
  fi
done

# Create common clean upload folders expected by app flows.
COMMON_UPLOAD_DIRS=(
  communicate
  front_office
  gallery
  homework
  logos
  payroll_import
  print_headerfooter
  sample_files
  school_content
  school_income
  staff_documents
  staff_id_card
  staff_images
  student_id_card
  student_images
)
for d in "${COMMON_UPLOAD_DIRS[@]}"; do
  mkdir -p "$PACKAGE_CODE_DIR/uploads/$d"
  [[ -f "$PACKAGE_CODE_DIR/uploads/$d/index.html" ]] || echo "<html><body></body></html>" > "$PACKAGE_CODE_DIR/uploads/$d/index.html"
done

# Keep non-sensitive bulk upload sample files from uploads.
UPLOAD_TEMPLATE_FILES=(
  "uploads/sample_leave_allotment.csv"
)
for rel in "${UPLOAD_TEMPLATE_FILES[@]}"; do
  if [[ -f "$ROOT_DIR/$rel" ]]; then
    mkdir -p "$(dirname "$PACKAGE_CODE_DIR/$rel")"
    cp "$ROOT_DIR/$rel" "$PACKAGE_CODE_DIR/$rel"
  fi
done

if [[ -d "$ROOT_DIR/uploads/payroll_import" ]]; then
  for ext in csv xls xlsx; do
    for f in "$ROOT_DIR"/uploads/payroll_import/*."$ext"; do
      if [[ -f "$f" ]]; then
        cp "$f" "$PACKAGE_CODE_DIR/uploads/payroll_import/"
      fi
    done
  done
fi

# Subject bulk upload sample file is referenced from uploads/sample_files.
SUBJECT_SAMPLE_REL="uploads/sample_files/sample_subjects.csv"
if [[ -f "$ROOT_DIR/$SUBJECT_SAMPLE_REL" ]]; then
  cp "$ROOT_DIR/$SUBJECT_SAMPLE_REL" "$PACKAGE_CODE_DIR/$SUBJECT_SAMPLE_REL"
else
  cat > "$PACKAGE_CODE_DIR/$SUBJECT_SAMPLE_REL" <<'CSV'
name,code,type,department_name,teacher_ids
Mathematics,MATH101,theory,Science,EMP001
Physics,PHY101,theory,Science,EMP002
Chemistry Lab,CHEM-LAB,practical,Science,
CSV
fi

# Keep starter logo files so first login/setup has branding placeholders.
if [[ -d "$ROOT_DIR/uploads/logos" ]]; then
  for ext in png jpg jpeg webp gif svg ico; do
    for f in "$ROOT_DIR"/uploads/logos/*."$ext"; do
      if [[ -f "$f" ]]; then
        cp "$f" "$PACKAGE_CODE_DIR/uploads/logos/"
      fi
    done
  done
fi

# Keep starter logos used by school branding screens.
for sub in logo admin_logo admin_small_logo; do
  src_dir="$ROOT_DIR/uploads/school_content/$sub"
  dst_dir="$PACKAGE_CODE_DIR/uploads/school_content/$sub"
  if [[ -d "$src_dir" ]]; then
    mkdir -p "$dst_dir"
    for ext in png jpg jpeg webp gif svg ico; do
      for f in "$src_dir"/*."$ext"; do
        if [[ -f "$f" ]]; then
          cp "$f" "$dst_dir/"
        fi
      done
    done
    [[ -f "$dst_dir/index.html" ]] || echo "<html><body></body></html>" > "$dst_dir/index.html"
  fi
done

# Hostel room import downloads this sample from uploads/school_content.
HOSTEL_SAMPLE_REL="uploads/school_content/hostel_room_sample.csv"
if [[ -f "$ROOT_DIR/$HOSTEL_SAMPLE_REL" ]]; then
  cp "$ROOT_DIR/$HOSTEL_SAMPLE_REL" "$PACKAGE_CODE_DIR/$HOSTEL_SAMPLE_REL"
else
  cat > "$PACKAGE_CODE_DIR/$HOSTEL_SAMPLE_REL" <<'CSV'
hostel_id,room_type_id,room_no,no_of_bed,cost_per_bed,description
1,1,A-101,4,1500,Sample hostel room import row
CSV
fi

echo "[4/6] Exporting schema-only database..."
SCHEMA_DUMP_ARGS=(
  --single-transaction
  --triggers
  --no-data
)
if [[ "$DUMP_ROUTINES" == "1" ]]; then
  SCHEMA_DUMP_ARGS+=(--routines)
fi

"$MYSQLDUMP_BIN" "${MYSQLDUMP_ARGS[@]}" \
  "${SCHEMA_DUMP_ARGS[@]}" \
  "$DB_NAME" > "$OUT_DIR/db/01_schema_only.sql"

sanitize_sql_dump "$OUT_DIR/db/01_schema_only.sql"

echo "[5/6] Exporting master/seed tables only..."
EXISTING_SEEDS=()
for t in "${SEED_TABLES[@]}"; do
  if "$MYSQL_BIN" "${MYSQL_ARGS[@]}" -D "$DB_NAME" -Nse "SHOW TABLES LIKE '$t'" | grep -q "^$t$"; then
    EXISTING_SEEDS+=("$t")
  fi
done

printf "%s\n" "${EXISTING_SEEDS[@]}" > "$OUT_DIR/db/seed_tables_exported.txt"

if [[ ${#EXISTING_SEEDS[@]} -gt 0 ]]; then
  BASE_SEEDS=()
  for t in "${EXISTING_SEEDS[@]}"; do
    if [[ "$t" != "staff" && "$t" != "staff_roles" ]]; then
      BASE_SEEDS+=("$t")
    fi
  done

  "$MYSQLDUMP_BIN" "${MYSQLDUMP_ARGS[@]}" \
    --single-transaction \
    --no-create-info \
    --skip-triggers \
    "$DB_NAME" "${BASE_SEEDS[@]}" > "$OUT_DIR/db/02_seed_master_data.sql"

  if printf "%s\n" "${EXISTING_SEEDS[@]}" | grep -qx "staff"; then
    SUPERADMIN_IDS="$($MYSQL_BIN "${MYSQL_ARGS[@]}" -D "$DB_NAME" -Nse "SELECT GROUP_CONCAT(DISTINCT s.id ORDER BY s.id) FROM staff s INNER JOIN staff_roles sr ON sr.staff_id = s.id INNER JOIN roles r ON r.id = sr.role_id WHERE r.id = 7 OR LOWER(r.name) LIKE '%super%admin%';")"

    {
      echo ""
      echo "-- Superadmin-only staff seed rows"
    } >> "$OUT_DIR/db/02_seed_master_data.sql"

    if [[ -n "$SUPERADMIN_IDS" ]]; then
      "$MYSQLDUMP_BIN" "${MYSQLDUMP_ARGS[@]}" \
        --single-transaction \
        --no-create-info \
        --skip-triggers \
        --where="id IN ($SUPERADMIN_IDS)" \
        "$DB_NAME" staff >> "$OUT_DIR/db/02_seed_master_data.sql"
      echo "staff(superadmin_only): $SUPERADMIN_IDS" >> "$OUT_DIR/db/seed_tables_exported.txt"
    else
      echo "-- No superadmin staff rows found to export." >> "$OUT_DIR/db/02_seed_master_data.sql"
      echo "staff(superadmin_only): none" >> "$OUT_DIR/db/seed_tables_exported.txt"
    fi

    if printf "%s\n" "${EXISTING_SEEDS[@]}" | grep -qx "staff_roles"; then
      {
        echo ""
        echo "-- Superadmin-only staff role mappings"
      } >> "$OUT_DIR/db/02_seed_master_data.sql"

      if [[ -n "$SUPERADMIN_IDS" ]]; then
        "$MYSQLDUMP_BIN" "${MYSQLDUMP_ARGS[@]}" \
          --single-transaction \
          --no-create-info \
          --skip-triggers \
          --where="staff_id IN ($SUPERADMIN_IDS)" \
          "$DB_NAME" staff_roles >> "$OUT_DIR/db/02_seed_master_data.sql"
        echo "staff_roles(superadmin_only): $SUPERADMIN_IDS" >> "$OUT_DIR/db/seed_tables_exported.txt"
      else
        echo "-- No staff_roles rows exported because no superadmin staff rows were found." >> "$OUT_DIR/db/02_seed_master_data.sql"
        echo "staff_roles(superadmin_only): none" >> "$OUT_DIR/db/seed_tables_exported.txt"
      fi
    fi
  fi
else
  echo "-- No configured seed tables found." > "$OUT_DIR/db/02_seed_master_data.sql"
fi

sanitize_sql_dump "$OUT_DIR/db/02_seed_master_data.sql"

echo "[6/6] Writing setup notes..."
cat > "$OUT_DIR/README.txt" <<'EOF'
New Institution Package
=======================

Contents:
- code/                      Clean code copy
- db/01_schema_only.sql      Database schema only
- db/02_seed_master_data.sql Master/seed data only
- db/seed_tables_exported.txt

Import order:
1) Create an empty database.
2) Import db/01_schema_only.sql
3) Import db/02_seed_master_data.sql
4) Update application/config/database.php for the target DB
5) Configure institution-specific settings from Admin UI (school profile, SMS/email, payment, etc.)

Write permissions (required):
- application/logs
- application/cache
- application/sessions
- application/tmp
- uploads (and its subfolders)

Example commands (Linux/macOS):
- chmod -R 775 application/logs application/cache application/sessions application/tmp uploads
- find application/logs application/cache application/sessions application/tmp uploads -type d -exec chmod 775 {} \;
- find application/logs application/cache application/sessions application/tmp uploads -type f -exec chmod 664 {} \;

Note:
- Institution-specific business data is intentionally excluded
  (students, staff, fees, payroll transactions, attendance, images, documents).
- uploads/ is recreated clean; only approved bulk upload sample files are retained.
- SQL files are generated without `DEFINER` clauses for compatibility with restricted DB users.
EOF

cat > "$OUT_DIR/SETUP_INSTRUCTIONS.md" <<'EOF'
# New Institution Setup Instructions

## 1. Import Database
1. Create a new empty database.
2. Import `db/01_schema_only.sql`.
3. Import `db/02_seed_master_data.sql`.

## 2. Configure Application
1. Update `code/application/config/database.php` with target DB host/user/password/database.
2. Point web server document root to `code/` (or copy `code/*` into your target project root).

## 3. Set Required Write Permissions
These directories must be writable by the web server user:
- `application/logs`
- `application/cache`
- `application/sessions`
- `application/tmp`
- `uploads` and all upload subdirectories

Run from inside the `code/` directory:

```bash
chmod -R 775 application/logs application/cache application/sessions application/tmp uploads
find application/logs application/cache application/sessions application/tmp uploads -type d -exec chmod 775 {} \;
find application/logs application/cache application/sessions application/tmp uploads -type f -exec chmod 664 {} \;
```

If ownership is required (Linux):

```bash
sudo chown -R www-data:www-data application/logs application/cache application/sessions application/tmp uploads
```

## 4. First Login and Validation
1. Login with the seeded superadmin account.
2. Verify menus/permissions load correctly.
3. Verify bulk upload sample files are downloadable.
4. Verify logo assets are visible in branding settings.

## 5. Client-Specific Setup
1. Update school profile and branding.
2. Replace logos/header-footer assets.
3. Configure SMS, email, and payment gateways.
4. Create staff/students and institution operational masters.

## Notes
- Package intentionally excludes institution transaction data.
- Seed includes superadmin `staff` and `staff_roles` mapping for first login.
EOF

echo "Done. Package created at: $OUT_DIR"
