#!/usr/bin/env python3
"""
amacedu_library_import.py
Reads Library Book details Part-1.xlsx and LIBRARY DETAILS.xlsx,
cleans the data, and generates an SQL import file for the amacedu production DB.

Books table columns used:
  book_title, book_no, barcode, category_name, subcategory_name, isbn_no,
  subject, rack_no, shelf_id, class_no, edition_type, publish_year,
  purchase_date, bill_no, bill_date, pages, department, publish,
  edition, medium, book_type, author, author2, qty, perunitcost,
  postdate, description, available, is_active, vendor

Lookup tables populated:
  library_categories, library_subcategories,
  library_position_racks, library_position_shelves,
  library_publishers, library_vendors,
  library_subjects, library_book_types
"""

import pandas as pd
import re
from datetime import datetime

# ── file paths ──────────────────────────────────────────────────────────────
FILE1 = '/Applications/XAMPP/xamppfiles/htdocs/minerva/Library Book details Part-1.xlsx'
FILE2 = '/Applications/XAMPP/xamppfiles/htdocs/minerva/LIBRARY DETAILS.xlsx'
OUT_SQL = '/Applications/XAMPP/xamppfiles/htdocs/minerva/tools/amacedu_library_import.sql'

# ── helpers ──────────────────────────────────────────────────────────────────

def s(val):
    """Return a SQL-safe escaped string literal, or NULL."""
    if val is None or (isinstance(val, float) and pd.isna(val)) or str(val).strip() in ('', '-', 'nan', 'NaN', 'NaT'):
        return 'NULL'
    v = str(val).strip()
    v = v.replace('\\', '\\\\').replace("'", "\\'")
    return f"'{v}'"

def n(val):
    """Return a SQL integer literal or NULL."""
    if val is None or (isinstance(val, float) and pd.isna(val)):
        return 'NULL'
    try:
        return str(int(float(str(val).strip())))
    except (ValueError, TypeError):
        return 'NULL'

def f(val):
    """Return SQL float literal or NULL."""
    if val is None or (isinstance(val, float) and pd.isna(val)):
        return 'NULL'
    try:
        return str(round(float(str(val).strip()), 2))
    except (ValueError, TypeError):
        return 'NULL'

DATE_FORMATS = [
    '%Y-%m-%d', '%d-%m-%Y', '%m-%d-%Y',
    '%Y/%m/%d', '%d/%m/%Y', '%m/%d/%Y',
    '%Y.%m.%d', '%d.%m.%Y', '%m.%d.%Y',
    '%d.%m%Y',   # malformed like 28.82022 → treat as 28.08.2022
]

def parse_date(raw):
    """Parse a date from various formats; return 'YYYY-MM-DD' or None."""
    if raw is None or (isinstance(raw, float) and pd.isna(raw)):
        return None
    raw = str(raw).strip()
    if not raw or raw in ('-', 'nan', 'NaN', 'NaT'):
        return None

    # pandas Timestamp or datetime object
    if hasattr(raw, 'strftime'):
        return raw.strftime('%Y-%m-%d')

    # If it looks like a number (Excel serial date), ignore it
    try:
        serial = float(raw)
        if 10000 < serial < 100000:
            # Excel date serial
            origin = pd.Timestamp('1899-12-30')
            dt = origin + pd.Timedelta(days=serial)
            return dt.strftime('%Y-%m-%d')
    except ValueError:
        pass

    # Remove ordinal suffixes: 1st, 2nd, 3rd, 4th …
    raw_clean = re.sub(r'(\d+)(st|nd|rd|th)', r'\1', raw, flags=re.IGNORECASE)

    # Attempt each format
    for fmt in DATE_FORMATS:
        try:
            dt = datetime.strptime(raw_clean, fmt)
            return dt.strftime('%Y-%m-%d')
        except ValueError:
            pass

    # Last-ditch: try pandas
    try:
        return pd.to_datetime(raw_clean, dayfirst=True).strftime('%Y-%m-%d')
    except Exception:
        return None

def d(raw):
    """Return SQL date literal or NULL."""
    v = parse_date(raw)
    return f"'{v}'" if v else 'NULL'

def yes_no(val):
    """Normalise yes/no values."""
    if val is None or (isinstance(val, float) and pd.isna(val)):
        return 'yes'
    v = str(val).strip().lower()
    if v in ('yes', 'y', '1', 'true'):
        return 'yes'
    if v in ('no', 'n', '0', 'false'):
        return 'no'
    return 'yes'

def clean_year(val):
    """Extract 4-digit year."""
    if val is None or (isinstance(val, float) and pd.isna(val)):
        return None
    raw = str(val).strip()
    # If it's a full date-like string, extract year
    if re.match(r'\d{4}-\d{2}-\d{2}', raw):
        return raw[:4]
    m = re.search(r'(\d{4})', raw)
    return m.group(1) if m else None

# ── Load Excel files ─────────────────────────────────────────────────────────
print("Loading Part-1 ...")
df1 = pd.read_excel(FILE1)

print("Loading LIBRARY DETAILS ...")
df2 = pd.read_excel(FILE2)

# Normalise column names (lower, strip, replace spaces with underscore)
df1.columns = [c.strip().lower().replace(' ', '_') for c in df1.columns]
df2.columns = [c.strip().lower().replace(' ', '_') for c in df2.columns]

# Rename df2 'edition_type' alias (it comes as 'edition type' → 'edition_type' after normalise)
# already handled by normalise above

# Drop unnamed columns
df1 = df1.loc[:, ~df1.columns.str.startswith('unnamed')]
df2 = df2.loc[:, ~df2.columns.str.startswith('unnamed')]

# Combine
df = pd.concat([df1, df2], ignore_index=True, sort=False)
print(f"Combined rows: {len(df)}")

# Drop rows with no book_no or book_title
df['book_no']    = df['book_no'].astype(str).str.strip()
df['book_title'] = df['book_title'].astype(str).str.strip()
before = len(df)
df = df[df['book_no'].notna() & (df['book_no'] != '') & (df['book_no'] != 'nan')]
df = df[df['book_title'].notna() & (df['book_title'] != '') & (df['book_title'] != 'nan')]
print(f"Rows after dropping empty book_no/title: {len(df)} (dropped {before - len(df)})")

# Drop exact duplicate (book_no + book_title)
before = len(df)
df = df.drop_duplicates(subset=['book_no', 'book_title'], keep='first')
print(f"Rows after dedup (book_no+title): {len(df)} (dropped {before - len(df)})")

# ── Build lookup dictionaries ────────────────────────────────────────────────
# category_name → id
# subcategory_name (category_name) → id
# rack_name → id
# shelf_name (rack_name) → id
# publisher → id
# vendor → id
# subject → id
# book_type → id

def clean_str(val):
    if val is None or (isinstance(val, float) and pd.isna(val)):
        return ''
    v = str(val).strip()
    return '' if v in ('-', 'nan', 'NaN') else v

def case_insensitive_dedup(values):
    """Return unique values, keeping first occurrence per lower-case key."""
    seen = {}
    for v in values:
        k = v.lower()
        if k not in seen:
            seen[k] = v
    return seen  # {lower → canonical}

df['_cat']     = df['category_name'].apply(clean_str)
df['_subcat']  = df['subcategory_name'].apply(clean_str)
df['_rack']    = df['rack_name'].apply(clean_str)
df['_shelf']   = df['shelf_name'].apply(clean_str)
df['_pub']     = df['publisher_name'].apply(clean_str)
df['_vendor']  = df['vendor'].apply(clean_str)
df['_subject'] = df['subject'].apply(clean_str)
df['_btype']   = df['book_type_name'].apply(clean_str)

# Unique categories (case-insensitive dedup → canonical value)
cat_canon  = case_insensitive_dedup(sorted({v for v in df['_cat'] if v}))
categories = sorted(cat_canon.values())  # canonical list for INSERT

# Map each raw cat to canonical
def to_canon_cat(v):
    return cat_canon.get(v.lower(), v) if v else v
df['_cat'] = df['_cat'].apply(to_canon_cat)

# Unique subcategories per canonical category  (set of (subcat, cat))
raw_subcat_pairs = sorted({(r['_subcat'], r['_cat']) for _, r in df.iterrows() if r['_subcat'] and r['_cat']})
# Dedup subcategory per category case-insensitively
subcat_seen = {}
subcat_pairs = []
for (sc, cat) in raw_subcat_pairs:
    key = (sc.lower(), cat.lower())
    if key not in subcat_seen:
        subcat_seen[key] = True
        subcat_pairs.append((sc, cat))

# Unique racks (case-insensitive dedup)
rack_canon = case_insensitive_dedup(sorted({v for v in df['_rack'] if v}))
racks = sorted(rack_canon.values())

def to_canon_rack(v):
    return rack_canon.get(v.lower(), v) if v else v
df['_rack'] = df['_rack'].apply(to_canon_rack)

# Unique shelves per canonical rack (case-insensitive dedup)
raw_shelf_pairs = sorted({(r['_shelf'], r['_rack']) for _, r in df.iterrows() if r['_shelf'] and r['_rack']})
shelf_seen = {}
shelf_pairs = []
for (sh, rack) in raw_shelf_pairs:
    key = (sh.lower(), rack.lower())
    if key not in shelf_seen:
        shelf_seen[key] = True
        shelf_pairs.append((sh, rack))

# Deduplicate others case-insensitively
pub_canon  = case_insensitive_dedup(sorted({v for v in df['_pub'] if v}))
ven_canon  = case_insensitive_dedup(sorted({v for v in df['_vendor'] if v}))
sub_canon  = case_insensitive_dedup(sorted({v for v in df['_subject'] if v}))
bt_canon   = case_insensitive_dedup(sorted({v for v in df['_btype'] if v}))

publishers = sorted(pub_canon.values())
vendors    = sorted(ven_canon.values())
subjects   = sorted(sub_canon.values())
btypes     = sorted(bt_canon.values())

print(f"Categories: {len(categories)}, Subcategories: {len(subcat_pairs)}, "
      f"Racks: {len(racks)}, Shelves: {len(shelf_pairs)}, "
      f"Publishers: {len(publishers)}, Vendors: {len(vendors)}, "
      f"Subjects: {len(subjects)}, Book types: {len(btypes)}")

# ── Generate SQL ─────────────────────────────────────────────────────────────
lines = []

lines.append("-- =============================================================")
lines.append("-- amacedu Library Import")
lines.append(f"-- Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
lines.append(f"-- Books total: {len(df)}")
lines.append("-- =============================================================")
lines.append("SET NAMES utf8mb4;")
lines.append("SET foreign_key_checks = 0;")
lines.append("START TRANSACTION;")
lines.append("")

# ── library_categories ───────────────────────────────────────────────────────
lines.append("-- ── library_categories ──────────────────────────────────────")
for cat in categories:
    lines.append(
        f"INSERT INTO library_categories (category_name) "
        f"SELECT {s(cat)} WHERE NOT EXISTS "
        f"(SELECT 1 FROM library_categories WHERE LOWER(category_name) = LOWER({s(cat)}));"
    )
lines.append("")

# ── library_subcategories ────────────────────────────────────────────────────
lines.append("-- ── library_subcategories ──────────────────────────────────")
for (subcat, cat) in subcat_pairs:
    lines.append(
        f"INSERT INTO library_subcategories (subcategory_name, category_id) "
        f"SELECT {s(subcat)}, id FROM library_categories "
        f"WHERE LOWER(category_name) = LOWER({s(cat)}) "
        f"AND NOT EXISTS ("
        f"SELECT 1 FROM library_subcategories sc "
        f"JOIN library_categories cc ON cc.id = sc.category_id "
        f"WHERE LOWER(sc.subcategory_name) = LOWER({s(subcat)}) "
        f"AND LOWER(cc.category_name) = LOWER({s(cat)})) LIMIT 1;"
    )
lines.append("")

# ── library_position_racks ───────────────────────────────────────────────────
lines.append("-- ── library_position_racks ──────────────────────────────────")
for rack in racks:
    lines.append(
        f"INSERT INTO library_position_racks (rack_name) "
        f"SELECT {s(rack)} WHERE NOT EXISTS "
        f"(SELECT 1 FROM library_position_racks WHERE LOWER(rack_name) = LOWER({s(rack)}));"
    )
lines.append("")

# ── library_position_shelves ─────────────────────────────────────────────────
lines.append("-- ── library_position_shelves ────────────────────────────────")
for (shelf, rack) in shelf_pairs:
    lines.append(
        f"INSERT INTO library_position_shelves (shelf_name, rack_id) "
        f"SELECT {s(shelf)}, r.id FROM library_position_racks r "
        f"WHERE LOWER(r.rack_name) = LOWER({s(rack)}) "
        f"AND NOT EXISTS ("
        f"SELECT 1 FROM library_position_shelves sh "
        f"JOIN library_position_racks rr ON rr.id = sh.rack_id "
        f"WHERE LOWER(sh.shelf_name) = LOWER({s(shelf)}) "
        f"AND LOWER(rr.rack_name) = LOWER({s(rack)})) LIMIT 1;"
    )
lines.append("")

# ── library_publishers ───────────────────────────────────────────────────────
lines.append("-- ── library_publishers ─────────────────────────────────────")
for pub in publishers:
    lines.append(
        f"INSERT INTO library_publishers (publisher_name) "
        f"SELECT {s(pub)} WHERE NOT EXISTS "
        f"(SELECT 1 FROM library_publishers WHERE LOWER(publisher_name) = LOWER({s(pub)}));"
    )
lines.append("")

# ── library_vendors ──────────────────────────────────────────────────────────
lines.append("-- ── library_vendors ────────────────────────────────────────")
for v in vendors:
    lines.append(
        f"INSERT INTO library_vendors (vendor_name) "
        f"SELECT {s(v)} WHERE NOT EXISTS "
        f"(SELECT 1 FROM library_vendors WHERE LOWER(vendor_name) = LOWER({s(v)}));"
    )
lines.append("")

# ── library_subjects ─────────────────────────────────────────────────────────
lines.append("-- ── library_subjects ───────────────────────────────────────")
for sub in subjects:
    lines.append(
        f"INSERT INTO library_subjects (subject_name) "
        f"SELECT {s(sub)} WHERE NOT EXISTS "
        f"(SELECT 1 FROM library_subjects WHERE LOWER(subject_name) = LOWER({s(sub)}));"
    )
lines.append("")

# ── library_book_types ───────────────────────────────────────────────────────
lines.append("-- ── library_book_types ─────────────────────────────────────")
for bt in btypes:
    lines.append(
        f"INSERT INTO library_book_types (book_type_name) "
        f"SELECT {s(bt)} WHERE NOT EXISTS "
        f"(SELECT 1 FROM library_book_types WHERE LOWER(book_type_name) = LOWER({s(bt)}));"
    )
lines.append("")

# ── books ────────────────────────────────────────────────────────────────────
lines.append("-- ── books ──────────────────────────────────────────────────")

skipped = 0
inserted = 0

for idx, row in df.iterrows():
    book_no    = clean_str(row.get('book_no', ''))
    book_title = clean_str(row.get('book_title', ''))

    if not book_no or not book_title:
        skipped += 1
        continue

    barcode        = clean_str(row.get('barcode', '')) or book_no
    category_name  = row['_cat']
    subcat_name    = row['_subcat']
    isbn_no        = clean_str(row.get('isbn_no', '')) or ''  # NOT NULL in DB
    subject        = row['_subject']
    rack_name      = row['_rack']
    shelf_name     = row['_shelf']
    class_no       = clean_str(row.get('class_no', ''))
    publisher_name = row['_pub']
    author         = clean_str(row.get('author', ''))
    author2        = clean_str(row.get('author2', ''))
    edition        = clean_str(row.get('edition', ''))
    edition_type   = clean_str(row.get('edition_type', ''))
    medium         = clean_str(row.get('medium', ''))
    book_type      = row['_btype']
    publish_year   = clean_year(row.get('publish_year', ''))
    perunitcost    = f(row.get('perunitcost', ''))
    purchase_date  = d(row.get('purchase_date', ''))
    bill_no        = clean_str(row.get('bill_no', ''))
    bill_date      = d(row.get('bill_date', ''))
    pages          = n(row.get('pages', ''))
    department     = clean_str(row.get('department', ''))
    description    = clean_str(row.get('description', ''))
    available      = yes_no(row.get('available', 'yes'))
    is_active      = yes_no(row.get('is_active', 'yes'))
    postdate       = d(row.get('postdate', ''))
    vendor         = row['_vendor']

    # isbn_no is NOT NULL in DB — fall back to empty string
    isbn_sql = s(isbn_no) if isbn_no else "''"

    lines.append(
        f"INSERT INTO books "
        f"(book_title, book_no, barcode, category_name, subcategory_name, isbn_no, "
        f"subject, rack_no, shelf_id, class_no, author, author2, "
        f"edition, edition_type, medium, book_type, publish_year, perunitcost, "
        f"purchase_date, bill_no, bill_date, pages, department, description, "
        f"available, is_active, publish, postdate, vendor, qty) "
        f"VALUES ("
        f"{s(book_title)}, {s(book_no)}, {s(barcode)}, {s(category_name)}, {s(subcat_name)}, "
        f"{isbn_sql}, "
        f"{s(subject)}, {s(rack_name)}, {s(shelf_name)}, {s(class_no)}, "
        f"{s(author)}, {s(author2)}, {s(edition)}, {s(edition_type)}, "
        f"{s(medium)}, {s(book_type)}, {s(publish_year)}, {perunitcost}, "
        f"{purchase_date}, {s(bill_no)}, {bill_date}, {pages}, {s(department)}, "
        f"{s(description)}, {s(available)}, {s(is_active)}, {s(publisher_name)}, "
        f"{postdate}, {s(vendor)}, 1);"
    )
    inserted += 1

lines.append("")
lines.append("COMMIT;")
lines.append(f"-- Done. Inserted: {inserted}, Skipped: {skipped}")
lines.append("SET foreign_key_checks = 1;")

# Write
with open(OUT_SQL, 'w', encoding='utf-8') as fh:
    fh.write('\n'.join(lines))

print(f"\nSQL written to: {OUT_SQL}")
print(f"Total book INSERT rows: {inserted}  |  Skipped: {skipped}")
