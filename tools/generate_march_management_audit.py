#!/usr/bin/env python3
import subprocess
import xlsxwriter

MYSQL = "/Applications/XAMPP/xamppfiles/bin/mysql"
OUT_PATH = "/Applications/XAMPP/xamppfiles/htdocs/minerva/tools/march_opening_balance_management.xlsx"

TARGET_YEAR = 2026
TARGET_MONTH = 3
PREV_MONTH = 2


def run_query(sql):
    r = subprocess.run([MYSQL, "-uroot", "mcekknagar", "-e", sql], capture_output=True, text=True)
    if r.returncode != 0:
        raise RuntimeError(r.stderr)
    lines = r.stdout.strip().split("\n")
    if len(lines) < 2:
        return []
    headers = lines[0].split("\t")
    return [dict(zip(headers, ln.split("\t"))) for ln in lines[1:]]


def to_float(v):
    try:
        return float(v)
    except Exception:
        return 0.0


print("Querying March 2026 balances...")
rows = run_query(
    f"""
SELECT
  s.id AS staff_id,
  s.employee_id,
  COALESCE(s.biometric_id, '') AS biometric_id,
  CONCAT(s.name, IF(s.surname != '', CONCAT(' ', s.surname), '')) AS full_name,
  COALESCE(d.department_name, '') AS department,
  COALESCE(des.designation, '') AS designation,
  slb.leave_type_id,
  slb.opening_balance,
  slb.earned_in_month,
  COALESCE(slb.admin_adjustment, 0) AS admin_adjustment,
  COALESCE(slb.used_for_lop_adjustment, 0) AS used_for_lop_adjustment,
  slb.closing_balance
FROM staff_monthly_leave_balance slb
JOIN staff s ON s.id = slb.staff_id
LEFT JOIN department d ON d.id = s.department
LEFT JOIN staff_designation des ON des.id = s.designation
WHERE slb.month = {TARGET_MONTH}
  AND slb.year = {TARGET_YEAR}
  AND slb.leave_type_id IN (1, 3, 4, 5)
  AND s.employee_id != '9000'
ORDER BY s.employee_id, slb.leave_type_id
"""
)

print("Querying February closing balances for carry-forward baseline...")
prev_rows = run_query(
    f"""
SELECT
  s.employee_id,
  COALESCE(s.biometric_id, '') AS biometric_id,
  slb.leave_type_id,
  slb.closing_balance
FROM staff_monthly_leave_balance slb
JOIN staff s ON s.id = slb.staff_id
WHERE slb.month = {PREV_MONTH}
  AND slb.year = {TARGET_YEAR}
  AND slb.leave_type_id IN (3, 4)
  AND s.employee_id != '9000'
"""
)

print("Querying March-approved OD/CPL forms...")
od_rows = run_query(
    f"""
SELECT staff_id, SUM(leave_days) AS approved_od_days
FROM staff_leave_request
WHERE leave_type_id = 1
  AND leave_direction = 'credit'
  AND status IN ('approve', 'approved', 'Approved')
  AND leave_from <= '{TARGET_YEAR}-03-31'
  AND leave_to >= '{TARGET_YEAR}-03-01'
GROUP BY staff_id
"""
)

cpl_rows = run_query(
    f"""
SELECT staff_id, COUNT(*) AS approved_cpl_forms, SUM(leave_days) AS approved_cpl_days
FROM staff_leave_request
WHERE leave_type_id = 4
  AND leave_direction = 'credit'
  AND status IN ('approve', 'approved', 'Approved')
  AND leave_from <= '{TARGET_YEAR}-03-31'
  AND leave_to >= '{TARGET_YEAR}-03-01'
GROUP BY staff_id
"""
)

od_map = {int(r["staff_id"]): to_float(r["approved_od_days"]) for r in od_rows}
cpl_map = {
    int(r["staff_id"]): {
        "forms": int(r["approved_cpl_forms"]),
        "days": to_float(r["approved_cpl_days"]),
    }
    for r in cpl_rows
}

# Build previous-month closing lookup by identifier
prev_close = {}
for r in prev_rows:
    emp = (r.get("employee_id") or "").strip()
    bid = (r.get("biometric_id") or "").strip()
    lt = int(r["leave_type_id"])
    val = to_float(r["closing_balance"])
    if emp:
        prev_close[(emp, lt)] = val
    if bid:
        prev_close[(bid, lt)] = val


# build per-staff March records
staff = {}
for r in rows:
    emp = (r.get("employee_id") or "").strip()
    if not emp:
        continue
    rec = staff.setdefault(
        emp,
        {
            "staff_id": int(r["staff_id"]),
            "employee_id": emp,
            "biometric_id": (r.get("biometric_id") or "").strip(),
            "name": (r.get("full_name") or "").strip(),
            "department": (r.get("department") or "").strip(),
            "designation": (r.get("designation") or "").strip(),
            "lt": {},
        },
    )
    ltid = int(r["leave_type_id"])
    rec["lt"][ltid] = {
        "opening": to_float(r["opening_balance"]),
        "earned": to_float(r["earned_in_month"]),
        "admin": to_float(r["admin_adjustment"]),
        "lop_adj": to_float(r["used_for_lop_adjustment"]),
        "closing": to_float(r["closing_balance"]),
    }


def get_prev_closing(emp_id, biometric_id, leave_type_id):
    # priority: employee_id, then biometric_id
    if (emp_id, leave_type_id) in prev_close:
        return prev_close[(emp_id, leave_type_id)], emp_id
    if biometric_id and (biometric_id, leave_type_id) in prev_close:
        return prev_close[(biometric_id, leave_type_id)], biometric_id
    return None, ""


EPS = 0.001
valid_mismatch_rows = []
od_ml_rows = []
no_baseline_rows = []

for emp_id in sorted(staff.keys()):
    s = staff[emp_id]
    sid = s["staff_id"]
    bid = s["biometric_id"]

    cl_db = s["lt"].get(3, {}).get("opening", 0.0)
    cpl_db = s["lt"].get(4, {}).get("opening", 0.0)
    od_db = s["lt"].get(1, {}).get("opening", 0.0)
    ml_db = s["lt"].get(5, {}).get("opening", 0.0)
    cl_close = s["lt"].get(3, {}).get("closing", 0.0)
    cpl_close = s["lt"].get(4, {}).get("closing", 0.0)
    od_close = s["lt"].get(1, {}).get("closing", 0.0)
    ml_close = s["lt"].get(5, {}).get("closing", 0.0)

    cl_prev, cl_key = get_prev_closing(emp_id, bid, 3)
    cpl_prev, cpl_key = get_prev_closing(emp_id, bid, 4)

    cl_has_base = cl_key != ""
    cpl_has_base = cpl_key != ""

    cl_diff = None if not cl_has_base else cl_db - cl_prev
    cpl_diff = None if not cpl_has_base else cpl_db - cpl_prev

    cl_mismatch = cl_has_base and abs(cl_diff) > EPS
    cpl_mismatch = cpl_has_base and abs(cpl_diff) > EPS
    od_violation = od_db > EPS
    ml_violation = ml_db > EPS

    base_info = {
        "Employee ID": emp_id,
        "Biometric ID": bid,
        "Name": s["name"],
        "Department": s["department"],
        "Designation": s["designation"],
    }

    if cl_mismatch or cpl_mismatch:
        valid_mismatch_rows.append(
            {
                **base_info,
                "CL Prev Closing (Feb)": 0.0 if cl_prev is None else cl_prev,
                "CL DB Opening (Mar)": cl_db,
                "CL Diff": 0.0 if cl_diff is None else cl_diff,
                "CL Baseline Key": cl_key,
                "CL Earned (Mar)": s["lt"].get(3, {}).get("earned", 0.0),
                "CL Admin Adj (Mar)": s["lt"].get(3, {}).get("admin", 0.0),
                "CL Adjusted for Mar": s["lt"].get(3, {}).get("lop_adj", 0.0),
                "CL Closing (Mar)": cl_close,
                "CPL Prev Closing (Feb)": 0.0 if cpl_prev is None else cpl_prev,
                "CPL DB Opening (Mar)": cpl_db,
                "CPL Diff": 0.0 if cpl_diff is None else cpl_diff,
                "CPL Baseline Key": cpl_key,
                "CPL Earned (Mar)": s["lt"].get(4, {}).get("earned", 0.0),
                "CPL Admin Adj (Mar)": s["lt"].get(4, {}).get("admin", 0.0),
                "CPL Adjusted for Mar": s["lt"].get(4, {}).get("lop_adj", 0.0),
                "CPL Closing (Mar)": cpl_close,
                "CPL Approved Forms (Mar)": cpl_map.get(sid, {}).get("forms", 0),
                "OD Adjusted for Mar": s["lt"].get(1, {}).get("lop_adj", 0.0),
                "OD Closing (Mar)": od_close,
            }
        )

    if od_violation or ml_violation:
        od_ml_rows.append(
            {
                **base_info,
                "OD DB Opening (Mar)": od_db,
                "OD Approved Days (Mar)": od_map.get(sid, 0.0),
                "OD Adjusted for Mar": s["lt"].get(1, {}).get("lop_adj", 0.0),
                "OD Closing (Mar)": od_close,
                "OD Violation": "YES" if od_violation else "NO",
                "ML DB Opening (Mar)": ml_db,
                "ML Adjusted for Mar": s["lt"].get(5, {}).get("lop_adj", 0.0),
                "ML Closing (Mar)": ml_close,
                "ML Violation": "YES" if ml_violation else "NO",
                "CL Carryforward Baseline Found": "YES" if cl_has_base else "NO",
                "CPL Carryforward Baseline Found": "YES" if cpl_has_base else "NO",
            }
        )

    # review queue: any leave type where previous month baseline is absent
    if not cl_has_base or not cpl_has_base:
        no_baseline_rows.append(
            {
                **base_info,
                "CL Prev Closing (Feb)": "FOUND" if cl_has_base else "MISSING",
                "CL DB Opening (Mar)": cl_db,
                "CL Adjusted for Mar": s["lt"].get(3, {}).get("lop_adj", 0.0),
                "CL Closing (Mar)": cl_close,
                "CPL Prev Closing (Feb)": "FOUND" if cpl_has_base else "MISSING",
                "CPL DB Opening (Mar)": cpl_db,
                "CPL Adjusted for Mar": s["lt"].get(4, {}).get("lop_adj", 0.0),
                "CPL Closing (Mar)": cpl_close,
                "OD DB Opening (Mar)": od_db,
                "OD Adjusted for Mar": s["lt"].get(1, {}).get("lop_adj", 0.0),
                "OD Closing (Mar)": od_close,
                "ML DB Opening (Mar)": ml_db,
                "ML Adjusted for Mar": s["lt"].get(5, {}).get("lop_adj", 0.0),
                "ML Closing (Mar)": ml_close,
                "Reason": "No previous month closing found for one or more carryforward leave types",
            }
        )

print("Writing March management workbook...")
wb = xlsxwriter.Workbook(OUT_PATH)

fmt_header = wb.add_format({"bold": True, "bg_color": "#1F3864", "font_color": "#FFFFFF", "border": 1})
fmt_cell = wb.add_format({"border": 1})
fmt_num = wb.add_format({"border": 1, "num_format": "0.00"})
fmt_warn = wb.add_format({"border": 1, "bg_color": "#FFF2CC"})
fmt_title = wb.add_format({"bold": True, "font_size": 13})
fmt_note = wb.add_format({"italic": True, "font_color": "#666666"})


def write_sheet(name, rows_data):
    ws = wb.add_worksheet(name)
    ws.set_zoom(90)

    if not rows_data:
        ws.write(0, 0, "No records", fmt_title)
        return

    headers = list(rows_data[0].keys())
    ws.write(0, 0, name, fmt_title)
    ws.write(1, 0, "Generated from strict March carry-forward rules with employee_id/biometric_id baseline mapping", fmt_note)

    for c, h in enumerate(headers):
        ws.write(3, c, h, fmt_header)
        ws.set_column(c, c, max(14, min(36, len(h) + 3)))

    r = 4
    for row in rows_data:
        for c, h in enumerate(headers):
            v = row[h]
            if isinstance(v, (int, float)):
                ws.write_number(r, c, float(v), fmt_num)
            else:
                style = fmt_warn if ("MISSING" in str(v) or str(v) == "YES") else fmt_cell
                ws.write(r, c, v, style)
        r += 1

    ws.freeze_panes(4, 2)


# sheet 1: only valid carry-forward mismatches
write_sheet("Valid Carryforward Mismatch", valid_mismatch_rows)

# sheet 2: OD/ML violations
write_sheet("OD_ML Violations", od_ml_rows)

# sheet 3: manual review queue for missing carry-forward baseline
write_sheet("No Previous Month Review", no_baseline_rows)

# sheet 4: summary
summary = wb.add_worksheet("Summary")
summary.write(0, 0, "March 2026 Payroll Carry-forward Management Summary", fmt_title)
summary.write(2, 0, "Total staff processed", fmt_header)
summary.write(2, 1, len(staff), fmt_num)
summary.write(3, 0, "Valid carry-forward mismatches (CL/CPL)", fmt_header)
summary.write(3, 1, len(valid_mismatch_rows), fmt_num)
summary.write(4, 0, "OD/ML violations", fmt_header)
summary.write(4, 1, len(od_ml_rows), fmt_num)
summary.write(5, 0, "No previous month manual review", fmt_header)
summary.write(5, 1, len(no_baseline_rows), fmt_num)
summary.write(7, 0, "Rule", fmt_header)
summary.write(7, 1, "Meaning", fmt_header)
summary.write(8, 0, "Valid Carryforward Mismatch", fmt_cell)
summary.write(8, 1, "Only staff where February closing exists and March opening differs", fmt_cell)
summary.write(9, 0, "OD_ML Violations", fmt_cell)
summary.write(9, 1, "OD/ML March opening should be 0; non-zero is listed", fmt_cell)
summary.write(10, 0, "No Previous Month Review", fmt_cell)
summary.write(10, 1, "February closing missing for CL/CPL; do not accuse until reviewed", fmt_cell)
summary.set_column(0, 0, 38)
summary.set_column(1, 1, 90)

wb.close()
print("Done:", OUT_PATH)
print("Valid carry-forward mismatches:", len(valid_mismatch_rows))
print("OD/ML violations:", len(od_ml_rows))
print("No previous month review:", len(no_baseline_rows))
