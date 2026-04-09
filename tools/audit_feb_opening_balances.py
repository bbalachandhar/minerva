#!/usr/bin/env python3
"""
Feb 2026 Leave Opening Balance Malpractice Audit
=================================================
Rules:
  CL  (id=3) : Correct = value from CSV. Flag if DB opening_balance != CSV.
  CPL (id=4) : Correct = value from CSV. Flag if DB opening_balance != CSV.
  OD  (id=1) : Correct = 0 (institution confirmed OD opening was zero for all staff).
               Flag if DB opening_balance > 0. Report also how many days the staff
               has via approved OD credit forms (for reference).
  ML  (id=5) : Correct = 0. Flag if DB opening_balance > 0.

Output: tools/feb_opening_balance_audit.xlsx
"""

import csv
import subprocess
import re
import sys
from decimal import Decimal, ROUND_HALF_UP
import xlsxwriter

DB_HOST = "localhost"
DB_USER = "root"
DB_NAME = "mcekknagar"
MYSQL  = "/Applications/XAMPP/xamppfiles/bin/mysql"
CSV_PATH = "/Applications/XAMPP/xamppfiles/htdocs/minerva/feb_leave_openingbalance.csv"
OUT_PATH = "/Applications/XAMPP/xamppfiles/htdocs/minerva/tools/feb_opening_balance_audit.xlsx"

LEAVE_TYPE_NAMES = {1: "OD", 3: "CL", 4: "CPL", 5: "ML"}

# ─── helpers ──────────────────────────────────────────────────────────────────

def run_query(sql):
    """Run a MySQL query and return list-of-dicts."""
    cmd = [MYSQL, f"-u{DB_USER}", DB_NAME, "-e", sql]
    result = subprocess.run(cmd, capture_output=True, text=True)
    if result.returncode != 0:
        print("MySQL error:", result.stderr, file=sys.stderr)
        sys.exit(1)
    lines = result.stdout.strip().split("\n")
    if len(lines) < 2:
        return []
    headers = lines[0].split("\t")
    rows = []
    for line in lines[1:]:
        values = line.split("\t")
        rows.append(dict(zip(headers, values)))
    return rows


def to_float(val):
    try:
        return float(val)
    except (TypeError, ValueError):
        return 0.0


def round2(f):
    return float(Decimal(str(f)).quantize(Decimal("0.00"), rounding=ROUND_HALF_UP))


# ─── 1. Load CSV (ground truth for CL and CPL) ────────────────────────────────

print("Loading CSV …")
csv_balances = {}   # {csv_identifier(employee_id/biometric_id) -> {leave_type_id -> balance}}

with open(CSV_PATH, newline="", encoding="utf-8-sig") as f:
    reader = csv.DictReader(f)
    for row in reader:
        emp_id = (row.get("employee_id") or "").strip()
        lt_id_raw = (row.get("leavetype_id") or "").strip()
        bal_raw   = (row.get("balance_days") or "").strip()
        if not emp_id or not lt_id_raw or not bal_raw:
            continue
        try:
            lt_id = int(lt_id_raw)
            bal   = to_float(bal_raw)
        except ValueError:
            continue
        if lt_id not in (3, 4):    # CSV only carries CL and CPL
            continue
        if emp_id not in csv_balances:
            csv_balances[emp_id] = {}
        # If duplicate employee_id+leave_type in CSV, take the LAST one
        csv_balances[emp_id][lt_id] = bal

print(f"  CSV rows loaded: {sum(len(v) for v in csv_balances.values())} entries "
      f"across {len(csv_balances)} employee IDs")


def get_csv_balance_for_staff(lt_id, employee_id, biometric_id):
    """Resolve CSV baseline by employee_id first, then biometric_id."""
    keys = []
    if employee_id:
        keys.append(employee_id)
    if biometric_id and biometric_id not in keys:
        keys.append(biometric_id)

    for key in keys:
        val = csv_balances.get(key, {}).get(lt_id, None)
        if val is not None:
            return val, key
    return None, ""


# ─── 2. Fetch Feb 2026 opening balances from DB ───────────────────────────────

print("Querying DB for Feb 2026 opening balances …")
db_rows = run_query("""
    SELECT
        s.id           AS staff_id,
        s.employee_id,
        COALESCE(s.biometric_id, '') AS biometric_id,
        CONCAT(s.name, IF(s.surname != '', CONCAT(' ', s.surname), '')) AS full_name,
        d.department_name  AS department,
        des.designation    AS designation,
        slb.leave_type_id,
        slb.opening_balance,
        slb.earned_in_month,
        slb.admin_adjustment,
        slb.closing_balance,
        slb.used_for_lop_adjustment,
        slb.used_for_leave_application
    FROM staff_monthly_leave_balance slb
    JOIN staff s   ON s.id  = slb.staff_id
    LEFT JOIN department d         ON d.id  = s.department
    LEFT JOIN staff_designation des ON des.id = s.designation
    WHERE slb.month = 2 AND slb.year = 2026
      AND slb.leave_type_id IN (1, 3, 4, 5)
    ORDER BY s.employee_id, slb.leave_type_id
""")

print(f"  DB rows: {len(db_rows)}")


# ─── 3. Fetch approved OD & CPL credit forms (FEB-2026 only) per staff ──────────────

print("Querying approved OD & CPL credit forms for Feb 2026 …")
od_credit_rows = run_query("""
    SELECT
                slr.staff_id,
        SUM(slr.leave_days) AS total_approved_od_days
    FROM staff_leave_request slr
    WHERE slr.leave_type_id = 1
      AND slr.leave_direction = 'credit'
      AND slr.status IN ('approve', 'approved', 'Approved')
            AND slr.leave_from <= '2026-02-29'
            AND slr.leave_to >= '2026-02-01'
    GROUP BY slr.staff_id
""")
od_approved_by_staff_id = {int(r["staff_id"]): to_float(r["total_approved_od_days"]) for r in od_credit_rows}

cpl_credit_rows = run_query("""
    SELECT
                slr.staff_id,
        COUNT(*) AS cpl_form_count,
        SUM(slr.leave_days) AS total_approved_cpl_days
    FROM staff_leave_request slr
    WHERE slr.leave_type_id = 4
      AND slr.leave_direction = 'credit'
      AND slr.status IN ('approve', 'approved', 'Approved')
            AND slr.leave_from <= '2026-02-29'
            AND slr.leave_to >= '2026-02-01'
    GROUP BY slr.staff_id
""")
cpl_approved_by_staff_id = {
        int(r["staff_id"]): {"forms": int(r["cpl_form_count"]), "days": to_float(r["total_approved_cpl_days"])}
    for r in cpl_credit_rows
}


# ─── 4. Build per-staff data structure ────────────────────────────────────────

print("Building comparison …")

# Group DB rows by employee
staff_map = {}   # employee_id -> { staff_id, full_name, department, designation, leave_type_id -> {...} }

for row in db_rows:
    emp_id = row["employee_id"].strip()
    biometric_id = row["biometric_id"].strip() if row["biometric_id"] else ""
    if not emp_id or emp_id in ("9000",):   # skip super admin
        continue
    if emp_id not in staff_map:
        staff_map[emp_id] = {
            "staff_id":    row["staff_id"],
            "biometric_id": biometric_id,
            "full_name":   row["full_name"].strip(),
            "department":  row["department"].strip() if row["department"] else "",
            "designation": row["designation"].strip() if row["designation"] else "",
            "leave_types": {}
        }
    lt = int(row["leave_type_id"])
    staff_map[emp_id]["leave_types"][lt] = {
        "opening": to_float(row["opening_balance"]),
        "earned":  to_float(row["earned_in_month"]),
        "admin_adj": to_float(row["admin_adjustment"]),
        "closing": to_float(row["closing_balance"]),
    }


# ─── 5. Detect malpractice per staff ──────────────────────────────────────────

flags = []   # list of dicts — one row per malpractice finding

EPS = 0.001   # tolerance for float comparison

for emp_id, info in sorted(staff_map.items()):
    lt_data = info["leave_types"]
    staff_id = int(info["staff_id"])
    biometric_id = info.get("biometric_id", "")

    row_base = {
        "employee_id": emp_id,
        "name":        info["full_name"],
        "department":  info["department"],
        "designation": info["designation"],
    }

    # --- CL (id=3) ---
    db_cl = lt_data.get(3, {}).get("opening", 0.0)
    csv_cl, cl_matched_key = get_csv_balance_for_staff(3, emp_id, biometric_id)

    if csv_cl is None:
        # No baseline in CSV for this identifier; do not accuse as inflated.
        csv_cl_display = 0.0
        cl_flag = False
    else:
        csv_cl_display = csv_cl
        cl_flag = abs(db_cl - csv_cl) > EPS

    # --- CPL (id=4) ---
    db_cpl = lt_data.get(4, {}).get("opening", 0.0)
    csv_cpl, cpl_matched_key = get_csv_balance_for_staff(4, emp_id, biometric_id)

    if csv_cpl is None:
        csv_cpl_display = 0.0
        cpl_flag = False
    else:
        csv_cpl_display = csv_cpl
        cpl_flag = abs(db_cpl - csv_cpl) > EPS

    # --- OD (id=1) ---
    db_od  = lt_data.get(1, {}).get("opening", 0.0)
    csv_od_display = 0.0   # should always be 0
    od_approved_days = od_approved_by_staff_id.get(staff_id, 0.0)
    od_flag = db_od > EPS

    # --- ML (id=5) ---
    db_ml  = lt_data.get(5, {}).get("opening", 0.0)
    csv_ml_display = 0.0
    ml_flag = db_ml > EPS

    if cl_flag or cpl_flag or od_flag or ml_flag:
        flags.append({
            **row_base,
            # CL
            "csv_cl":  round2(csv_cl_display),
            "db_cl":   round2(db_cl),
            "diff_cl": round2(db_cl - csv_cl_display),
            "cl_baseline_key": cl_matched_key,
            "cl_earned": round2(lt_data.get(3, {}).get("earned", 0.0)),
            "cl_admin": round2(lt_data.get(3, {}).get("admin_adj", 0.0)),
            "flag_cl": cl_flag,
            # CPL
            "csv_cpl":  round2(csv_cpl_display),
            "db_cpl":   round2(db_cpl),
            "diff_cpl": round2(db_cpl - csv_cpl_display),
            "cpl_baseline_key": cpl_matched_key,
            "cpl_earned": round2(lt_data.get(4, {}).get("earned", 0.0)),
            "cpl_admin": round2(lt_data.get(4, {}).get("admin_adj", 0.0)),
            "cpl_forms": cpl_approved_by_staff_id.get(staff_id, {}).get("forms", 0),
            "flag_cpl": cpl_flag,
            # OD
            "csv_od":          0.0,
            "db_od":           round2(db_od),
            "diff_od":         round2(db_od),
            "od_approved_ref": round2(od_approved_days),
            "flag_od":         od_flag,
            # ML
            "csv_ml": 0.0,
            "db_ml":  round2(db_ml),
            "diff_ml": round2(db_ml),
            "flag_ml": ml_flag,
        })

print(f"  Malpractice suspects found: {len(flags)}")


# ─── 6. Also build a summary-all sheet (every staff, flagged or not) ───────────

all_rows = []
for emp_id, info in sorted(staff_map.items()):
    lt_data = info["leave_types"]
    staff_id = int(info["staff_id"])
    biometric_id = info.get("biometric_id", "")
    db_cl  = lt_data.get(3, {}).get("opening", 0.0)
    db_cpl = lt_data.get(4, {}).get("opening", 0.0)
    db_od  = lt_data.get(1, {}).get("opening", 0.0)
    db_ml  = lt_data.get(5, {}).get("opening", 0.0)

    csv_cl, cl_matched_key = get_csv_balance_for_staff(3, emp_id, biometric_id)
    csv_cpl, cpl_matched_key = get_csv_balance_for_staff(4, emp_id, biometric_id)
    csv_cl = 0.0 if csv_cl is None else csv_cl
    csv_cpl = 0.0 if csv_cpl is None else csv_cpl

    cl_diff  = round2(db_cl  - csv_cl)
    cpl_diff = round2(db_cpl - csv_cpl)
    od_diff  = round2(db_od)
    ml_diff  = round2(db_ml)

    cl_flag  = (cl_matched_key != "") and (abs(db_cl  - csv_cl) > EPS)
    cpl_flag = (cpl_matched_key != "") and (abs(db_cpl - csv_cpl) > EPS)
    od_flag  = db_od > EPS
    ml_flag  = db_ml > EPS

    all_rows.append({
        "employee_id": emp_id,
        "name":        info["full_name"],
        "department":  info["department"],
        "designation": info["designation"],
        "csv_cl":   round2(csv_cl),
        "db_cl":    round2(db_cl),
        "diff_cl":  cl_diff,
        "cl_baseline_key": cl_matched_key,
        "cl_earned": round2(lt_data.get(3, {}).get("earned", 0.0)),
        "cl_admin": round2(lt_data.get(3, {}).get("admin_adj", 0.0)),
        "flag_cl":  cl_flag,
        "csv_cpl":  round2(csv_cpl),
        "db_cpl":   round2(db_cpl),
        "diff_cpl": cpl_diff,
        "cpl_baseline_key": cpl_matched_key,
        "cpl_earned": round2(lt_data.get(4, {}).get("earned", 0.0)),
        "cpl_admin": round2(lt_data.get(4, {}).get("admin_adj", 0.0)),
        "cpl_forms": cpl_approved_by_staff_id.get(staff_id, {}).get("forms", 0),
        "flag_cpl": cpl_flag,
        "db_od":    round2(db_od),
        "diff_od":  od_diff,
        "od_approved_ref": round2(od_approved_by_staff_id.get(staff_id, 0.0)),
        "flag_od":  od_flag,
        "db_ml":    round2(db_ml),
        "diff_ml":  ml_diff,
        "flag_ml":  ml_flag,
        "any_flag": cl_flag or cpl_flag or od_flag or ml_flag,
    })


# ─── 7. Write Excel ───────────────────────────────────────────────────────────

print(f"Writing Excel to {OUT_PATH} …")
wb = xlsxwriter.Workbook(OUT_PATH)

# ── Formats ──
fmt_title = wb.add_format({
    "bold": True, "font_size": 14, "bg_color": "#1F3864", "font_color": "#FFFFFF",
    "border": 1
})
fmt_section_cl  = wb.add_format({"bold": True, "bg_color": "#BDD7EE", "border": 1, "align": "center"})
fmt_section_cpl = wb.add_format({"bold": True, "bg_color": "#E2EFDA", "border": 1, "align": "center"})
fmt_section_od  = wb.add_format({"bold": True, "bg_color": "#FCE4D6", "border": 1, "align": "center"})
fmt_section_ml  = wb.add_format({"bold": True, "bg_color": "#FFF2CC", "border": 1, "align": "center"})
fmt_hdr = wb.add_format({
    "bold": True, "bg_color": "#2E4057", "font_color": "#FFFFFF",
    "border": 1, "align": "center", "valign": "vcenter", "text_wrap": True
})
fmt_norm = wb.add_format({"border": 1, "valign": "vcenter"})
fmt_num  = wb.add_format({"border": 1, "valign": "vcenter", "num_format": "0.00"})
fmt_flag_cell = wb.add_format({
    "border": 1, "bold": True, "bg_color": "#FF0000", "font_color": "#FFFFFF",
    "num_format": "0.00", "valign": "vcenter"
})
fmt_ok_cell = wb.add_format({
    "border": 1, "bg_color": "#C6EFCE", "num_format": "0.00", "valign": "vcenter"
})
fmt_diff_bad = wb.add_format({
    "border": 1, "bold": True, "bg_color": "#FFEB9C", "font_color": "#9C5700",
    "num_format": "0.00", "valign": "vcenter"
})
fmt_diff_ok = wb.add_format({
    "border": 1, "bg_color": "#C6EFCE", "num_format": "0.00", "valign": "vcenter"
})
fmt_no_baseline = wb.add_format({
    "border": 1, "bg_color": "#E7E6E6", "font_color": "#404040", "valign": "vcenter", "align": "center"
})
fmt_flag_yes = wb.add_format({"border": 1, "bold": True, "bg_color": "#FF0000", "font_color": "#FFFFFF", "align": "center"})
fmt_flag_ok = wb.add_format({"border": 1, "bg_color": "#C6EFCE", "font_color": "#375623", "align": "center"})
fmt_suspect_row = wb.add_format({
    "border": 1, "bg_color": "#FFF2CC", "valign": "vcenter"
})
fmt_suspect_num = wb.add_format({
    "border": 1, "bg_color": "#FFF2CC", "num_format": "0.00", "valign": "vcenter"
})
fmt_sno = wb.add_format({
    "border": 1, "align": "center", "valign": "vcenter", "bg_color": "#D9E1F2"
})
fmt_sno_suspect = wb.add_format({
    "border": 1, "align": "center", "valign": "vcenter", "bg_color": "#FFF2CC", "bold": True
})
fmt_note = wb.add_format({"italic": True, "font_color": "#595959", "font_size": 9})


def write_sheet(ws, data, title, include_sno=True):
    """Write a formatted table to a worksheet."""
    # Title row
    ws.merge_range("A1:AB1", title, fmt_title)
    ws.set_row(0, 25)

    # Section header row (row 2, index 1)
    ws.merge_range(1, 0, 1, 3, "", fmt_hdr)
    ws.merge_range(1, 4, 1, 10,  "CL – Casual Leave (with earned/admin breakdown)",    fmt_section_cl)
    ws.merge_range(1, 11, 1, 18, "CPL – Compensatory PL (with earned/admin/forms)",    fmt_section_cpl)
    ws.merge_range(1, 19, 1, 23, "OD – On Duty (with approved forms ref)",             fmt_section_od)
    ws.merge_range(1, 24, 1, 26, "ML – Medical Leave",                                 fmt_section_ml)
    ws.set_row(1, 22)

    # Column headers (row 3, index 2)
    col_headers = [
        "S.No", "Employee ID", "Name", "Department / Designation",
        # CL (7 cols)
        "CSV\n(Correct)", "DB\n(Actual)", "Difference", "Earned\nIn Month", "Admin\nAdjusted", "Flag", "",
        # CPL (8 cols)
        "CSV\n(Correct)", "DB\n(Actual)", "Difference", "Earned\nIn Month", "Admin\nAdjusted", "Approved\nForms", "Flag", "",
        # OD (5 cols)
        "Correct\n(should be 0)", "DB\n(Actual)", "Approved\nForms Ref.", "Flag", "",
        # ML (3 cols)
        "Correct\n(should be 0)", "DB\n(Actual)", "Flag",
    ]
    for ci, h in enumerate(col_headers):
        ws.write(2, ci, h, fmt_hdr)
    ws.set_row(2, 40)

    # Column widths
    ws.set_column(0, 0, 6)    # S.No
    ws.set_column(1, 1, 18)   # Employee ID
    ws.set_column(2, 2, 25)   # Name
    ws.set_column(3, 3, 30)   # Dept/Desig
    ws.set_column(4, 26, 11)  # leave type cols

    # Data rows
    row = 3
    for sno, d in enumerate(data, start=1):
        is_suspect = d.get("any_flag", False)
        f_norm = fmt_suspect_row if is_suspect else fmt_norm
        f_num  = fmt_suspect_num if is_suspect else fmt_num
        f_sno_ = fmt_sno_suspect if is_suspect else fmt_sno

        dept_desig = d["department"]
        if d["designation"]:
            dept_desig = f"{d['department']}\n{d['designation']}" if d['department'] else d['designation']

        ws.write(row, 0, sno,           f_sno_)
        ws.write(row, 1, d["employee_id"], f_norm)
        ws.write(row, 2, d["name"],     f_norm)
        ws.write(row, 3, dept_desig,    f_norm)

        # CL (cols 4-10)
        cl_has_baseline = bool(d.get("cl_baseline_key", ""))
        if cl_has_baseline:
            ws.write_number(row, 4, d["csv_cl"], fmt_num)
            ws.write_number(row, 6, d["diff_cl"], fmt_diff_bad if d["flag_cl"] else fmt_diff_ok)
        else:
            ws.write(row, 4, "NO BASELINE", fmt_no_baseline)
            ws.write(row, 6, "N/A", fmt_no_baseline)

        ws.write_number(row, 5, d["db_cl"], fmt_flag_cell if d["flag_cl"] else (fmt_ok_cell if cl_has_baseline else fmt_no_baseline))
        ws.write_number(row, 7, d["cl_earned"], fmt_num)  # Earned In Month
        ws.write_number(row, 8, d["cl_admin"],  fmt_num)  # Admin Adjusted
        if not cl_has_baseline:
            ws.write(row, 9, "NO BASELINE", fmt_no_baseline)
        else:
            ws.write(row, 9, "YES ⚠" if d["flag_cl"] else "OK", fmt_flag_yes if d["flag_cl"] else fmt_flag_ok)
        ws.write(row, 10, "", f_norm)  # Spacer

        # CPL (cols 11-18)
        cpl_has_baseline = bool(d.get("cpl_baseline_key", ""))
        if cpl_has_baseline:
            ws.write_number(row, 11, d["csv_cpl"], fmt_num)
            ws.write_number(row, 13, d["diff_cpl"], fmt_diff_bad if d["flag_cpl"] else fmt_diff_ok)
        else:
            ws.write(row, 11, "NO BASELINE", fmt_no_baseline)
            ws.write(row, 13, "N/A", fmt_no_baseline)

        ws.write_number(row, 12, d["db_cpl"], fmt_flag_cell if d["flag_cpl"] else (fmt_ok_cell if cpl_has_baseline else fmt_no_baseline))
        ws.write_number(row, 14, d["cpl_earned"], fmt_num)  # Earned In Month
        ws.write_number(row, 15, d["cpl_admin"],  fmt_num)  # Admin Adjusted
        ws.write_number(row, 16, d["cpl_forms"],  fmt_num)  # Approved Forms count
        if not cpl_has_baseline:
            ws.write(row, 17, "NO BASELINE", fmt_no_baseline)
        else:
            ws.write(row, 17, "YES ⚠" if d["flag_cpl"] else "OK", fmt_flag_yes if d["flag_cpl"] else fmt_flag_ok)
        ws.write(row, 18, "", f_norm)  # Spacer

        # OD (cols 19-23)
        ws.write_number(row, 19, 0.0,              fmt_num)
        ws.write_number(row, 20, d["db_od"],        fmt_flag_cell if d["flag_od"] else fmt_ok_cell)
        ws.write_number(row, 21, d["od_approved_ref"], fmt_num)
        ws.write(row, 22, "YES ⚠" if d["flag_od"] else "OK", fmt_flag_yes if d["flag_od"] else fmt_flag_ok)
        ws.write(row, 23, "", f_norm)  # Spacer

        # ML (cols 24-26)
        ws.write_number(row, 24, 0.0,            fmt_num)
        ws.write_number(row, 25, d["db_ml"],      fmt_flag_cell if d["flag_ml"] else fmt_ok_cell)
        ws.write(row, 26, "YES ⚠" if d["flag_ml"] else "OK", fmt_flag_yes if d["flag_ml"] else fmt_flag_ok)

        ws.set_row(row, 22)
        row += 1

    # Freeze panes at row 3 col 4
    ws.freeze_panes(3, 4)

    # Notes
    ws.write(row + 1, 0,
        "Notes: "
        "• CL and CPL — Correct values are from the official Feb 2026 opening balance sheet provided by the institution. "
        "• If CSV baseline is not found for a staff (by employee_id/biometric_id), status shows 'NO BASELINE' and is not treated as inflation. "
        "• OD and ML — Correct opening should be 0.00 for all staff (new system started Feb 2026). "
        "• Earned In Month = legitimate monthly leave credit earned by the system (e.g., monthly CL accrual, HOD auto-grant for CPL, or approved credit forms). "
        "• Admin Adjusted = leave credits added directly by admin WITHOUT submitting an approval form (direct entry by admin). "
        "  ⚠ HIGH RISK: If a staff's inflated opening balance includes admin_adjusted values, it indicates admin may have manually inflated it. "
        "• Approved Forms Ref. (OD/CPL) = total approved credit forms submitted by the staff (for cross-reference only; opening balance should NOT be inflated based on forms alone). "
        "• Difference = DB Actual − Correct. A positive difference means the staff entered MORE than authorised.",
        fmt_note)


# Sheet 1: Malpractice suspects only
ws1 = wb.add_worksheet("Malpractice Suspects")
ws1.set_zoom(90)
# Add any_flag for flags list
for f in flags:
    f["any_flag"] = True
write_sheet(ws1, flags,
    f"Feb 2026 Leave Opening Balance — Malpractice Suspects Only  ({len(flags)} staff flagged)")

# Sheet 2: All staff
ws2 = wb.add_worksheet("All Staff (Full Audit)")
ws2.set_zoom(85)
write_sheet(ws2, all_rows,
    f"Feb 2026 Leave Opening Balance — Full Audit  ({len(all_rows)} staff)")

# Sheet 3: Legend / Notes
ws3 = wb.add_worksheet("Legend & Rules")
ws3.set_column(0, 0, 35)
ws3.set_column(1, 1, 80)

legend_fmt_hdr = wb.add_format({"bold": True, "bg_color": "#1F3864", "font_color": "#FFFFFF", "border": 1, "font_size": 12})
legend_fmt_key = wb.add_format({"bold": True, "border": 1, "bg_color": "#D9E1F2"})
legend_fmt_val = wb.add_format({"border": 1, "text_wrap": True})

ws3.merge_range("A1:B1", "Legend & Audit Rules", legend_fmt_hdr)
legend_rows = [
    ("Document", "Feb 2026 Leave Opening Balance Malpractice Audit"),
    ("Generated", "April 2026"),
    ("Database", "mcekknagar"),
    ("", ""),
    ("RULE — CL (Casual Leave)",
     "Correct value = institution-provided CSV balance. "
     "Flagged if DB opening_balance ≠ CSV value (tolerance: 0.001 days)."),
    ("RULE — CPL (Compensatory Leave)",
     "Correct value = institution-provided CSV balance. "
     "Flagged if DB opening_balance ≠ CSV value."),
    ("RULE — OD (On Duty)",
     "Correct opening = 0.00 for ALL staff. "
     "The institution confirmed OD balance was zero when the system went live in February 2026. "
     "Any staff with OD opening_balance > 0 in the DB is flagged as malpractice. "
     "'Approved Forms Ref.' column shows total approved OD credit forms in the system — "
     "this is for cross-reference only and does NOT justify a non-zero opening balance."),
    ("RULE — ML (Medical Leave)",
     "Correct opening = 0.00 for ALL staff. "
     "Flagged if DB opening_balance > 0."),
    ("", ""),
    ("Earned In Month (NEW)",
     "Legitimate leave credits earned/granted by the system during February 2026. "
     "Examples: monthly CL accrual, HOD auto-grant for CPL, or system processing of approved credit forms. "
     "This is the EXPECTED source of leave balance growth."),
    ("Admin Adjusted (NEW) ⚠ HIGH RISK",
     "Leave credits added DIRECTLY by admin WITHOUT submitting an approval form. "
     "These are manual entries by the admin/HR team. "
     "If a flagged staff member has a non-zero admin_adjusted value, it suggests the balance may have been manually inflated. "
     "Cross-check: Did admin approve this direct adjustment? Or was it entered by the staff or third party?"),
    ("Approved Forms Ref. (OD/CPL) (NEW)",
     "Total count of approved credit forms (OD or CPL) submitted by the staff in the system. "
     "For reference only. Does NOT justify opening balance — forms grant EARNED leave, not higher opening balance."),
    ("", ""),
    ("Flag — YES ⚠ (RED)",
     "DB value does not match the authorised correct value. "
     "This staff member likely entered an inflated figure while the self-edit option was enabled "
     "OR admin inflated it. Check the 'Admin Adjusted' column to differentiate."),
    ("Flag — OK (GREEN)", "DB value matches the authorised correct value. No action needed."),
    ("", ""),
    ("Sheet: Malpractice Suspects", "Contains only the staff with at least one flagged leave type. Focus your investigation here."),
    ("Sheet: All Staff (Full Audit)",
     "Complete audit of all staff with Feb 2026 monthly balance records. "
     "Rows highlighted in yellow have at least one flag."),
    ("Sheet: Legend & Rules", "This sheet. Documentation and column definitions."),
    ("", ""),
    ("Difference column",
     "DB Actual − Correct. Positive = staff entered MORE days than authorised. "
     "Negative = staff entered FEWER (under-reporting, usually not a discipline concern). "
     "Use 'Admin Adjusted' and 'Earned In Month' columns to trace WHERE the difference came from."),
    ("", ""),
    ("NEXT STEPS",
     "1. Review 'Malpractice Suspects' sheet. "
     "2. For each flagged staff, check 'Admin Adjusted' — if > 0, admin may have inflated. "
     "3. For self-edit malpractice: check 'Earned In Month' — staff should not have inflated earned credits either. "
     "4. Cross-reference 'Approved Forms Ref.' — approved forms should have generated legitimate earned credits. "
     "5. Investigate staff who edited their details during the editable window (Feb 1-X 2026) and compare to forms submitted."),
]
for ri, (k, v) in enumerate(legend_rows, start=1):
    ws3.write(ri, 0, k, legend_fmt_key)
    ws3.write(ri, 1, v, legend_fmt_val)
    ws3.set_row(ri, 30)

wb.close()
print(f"\nDone. Output: {OUT_PATH}")
print(f"  Sheet 'Malpractice Suspects': {len(flags)} staff")
print(f"  Sheet 'All Staff (Full Audit)': {len(all_rows)} staff")
