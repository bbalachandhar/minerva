#!/usr/bin/env python3
import csv
import subprocess
from collections import defaultdict
import xlsxwriter

MYSQL = "/Applications/XAMPP/xamppfiles/bin/mysql"
CSV_PATH = "/Applications/XAMPP/xamppfiles/htdocs/minerva/feb_leave_openingbalance.csv"
OUT_PATH = "/Applications/XAMPP/xamppfiles/htdocs/minerva/tools/feb_opening_balance_management.xlsx"


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


print("Loading CSV baselines...")
# CSV baseline keyed by the identifier present in CSV (employee_id or biometric_id)
csv_map = defaultdict(dict)
with open(CSV_PATH, newline="", encoding="utf-8-sig") as f:
    for row in csv.DictReader(f):
        key = (row.get("employee_id") or "").strip()
        lt = (row.get("leavetype_id") or "").strip()
        bal = (row.get("balance_days") or "").strip()
        if not key or lt not in ("3", "4"):
            continue
        csv_map[key][int(lt)] = to_float(bal)

print("Querying Feb 2026 balances...")
rows = run_query(
    """
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
WHERE slb.month = 2
  AND slb.year = 2026
  AND slb.leave_type_id IN (1, 3, 4, 5)
  AND s.employee_id != '9000'
ORDER BY s.employee_id, slb.leave_type_id
"""
)

print("Querying Feb-approved OD/CPL forms...")
od_rows = run_query(
    """
SELECT staff_id, SUM(leave_days) AS approved_od_days
FROM staff_leave_request
WHERE leave_type_id = 1
  AND leave_direction = 'credit'
  AND status IN ('approve', 'approved', 'Approved')
  AND leave_from <= '2026-02-29'
  AND leave_to >= '2026-02-01'
GROUP BY staff_id
"""
)

cpl_rows = run_query(
    """
SELECT staff_id, COUNT(*) AS approved_cpl_forms, SUM(leave_days) AS approved_cpl_days
FROM staff_leave_request
WHERE leave_type_id = 4
  AND leave_direction = 'credit'
  AND status IN ('approve', 'approved', 'Approved')
  AND leave_from <= '2026-02-29'
  AND leave_to >= '2026-02-01'
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

# build per staff
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


def get_baseline(emp_id, biometric_id, leave_type_id):
    # priority: employee_id, then biometric_id
    if emp_id in csv_map and leave_type_id in csv_map[emp_id]:
        return csv_map[emp_id][leave_type_id], emp_id
    if biometric_id and biometric_id in csv_map and leave_type_id in csv_map[biometric_id]:
        return csv_map[biometric_id][leave_type_id], biometric_id
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

    cl_csv, cl_key = get_baseline(emp_id, bid, 3)
    cpl_csv, cpl_key = get_baseline(emp_id, bid, 4)

    cl_has_base = cl_key != ""
    cpl_has_base = cpl_key != ""

    cl_diff = None if not cl_has_base else cl_db - cl_csv
    cpl_diff = None if not cpl_has_base else cpl_db - cpl_csv

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
                "CL CSV": 0.0 if cl_csv is None else cl_csv,
                "CL DB": cl_db,
                "CL Diff": 0.0 if cl_diff is None else cl_diff,
                "CL Baseline Key": cl_key,
                "CL Earned": s["lt"].get(3, {}).get("earned", 0.0),
                "CL Admin Adj": s["lt"].get(3, {}).get("admin", 0.0),
                "CL Adjusted for Feb": s["lt"].get(3, {}).get("lop_adj", 0.0),
                "CPL CSV": 0.0 if cpl_csv is None else cpl_csv,
                "CPL DB": cpl_db,
                "CPL Diff": 0.0 if cpl_diff is None else cpl_diff,
                "CPL Baseline Key": cpl_key,
                "CPL Earned": s["lt"].get(4, {}).get("earned", 0.0),
                "CPL Admin Adj": s["lt"].get(4, {}).get("admin", 0.0),
                "CPL Adjusted for Feb": s["lt"].get(4, {}).get("lop_adj", 0.0),
                "CPL Approved Forms (Feb)": cpl_map.get(sid, {}).get("forms", 0),
                "OD Adjusted for Feb": s["lt"].get(1, {}).get("lop_adj", 0.0),
            }
        )

    if od_violation or ml_violation:
        od_ml_rows.append(
            {
                **base_info,
                "OD DB Opening": od_db,
                "OD Approved Days (Feb)": od_map.get(sid, 0.0),
                "OD Adjusted for Feb": s["lt"].get(1, {}).get("lop_adj", 0.0),
                "OD Violation": "YES" if od_violation else "NO",
                "ML DB Opening": ml_db,
                "ML Adjusted for Feb": s["lt"].get(5, {}).get("lop_adj", 0.0),
                "ML Violation": "YES" if ml_violation else "NO",
                "CL Baseline Found": "YES" if cl_has_base else "NO",
                "CPL Baseline Found": "YES" if cpl_has_base else "NO",
            }
        )

    # review queue: any leave type where baseline is absent
    if not cl_has_base or not cpl_has_base:
        no_baseline_rows.append(
            {
                **base_info,
                "CL Baseline": "FOUND" if cl_has_base else "MISSING",
                "CL DB Opening": cl_db,
                "CL Adjusted for Feb": s["lt"].get(3, {}).get("lop_adj", 0.0),
                "CPL Baseline": "FOUND" if cpl_has_base else "MISSING",
                "CPL DB Opening": cpl_db,
                "CPL Adjusted for Feb": s["lt"].get(4, {}).get("lop_adj", 0.0),
                "OD DB Opening": od_db,
                "OD Adjusted for Feb": s["lt"].get(1, {}).get("lop_adj", 0.0),
                "ML DB Opening": ml_db,
                "ML Adjusted for Feb": s["lt"].get(5, {}).get("lop_adj", 0.0),
                "Reason": "CSV does not contain this identifier for one or more baseline leave types",
            }
        )

print("Writing management workbook...")
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
    ws.write(1, 0, "Generated from strict February rules with employee_id/biometric_id CSV baseline mapping", fmt_note)

    for c, h in enumerate(headers):
        ws.write(3, c, h, fmt_header)
        ws.set_column(c, c, max(14, min(34, len(h) + 3)))

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


# sheet 1: only valid baseline mismatches
write_sheet("Valid Baseline Mismatch", valid_mismatch_rows)

# sheet 2: OD/ML violations independent of CL/CPL baseline
write_sheet("OD_ML Violations", od_ml_rows)

# sheet 3: manual review queue for missing baselines
write_sheet("No Baseline Review", no_baseline_rows)

# sheet 4: summary
summary = wb.add_worksheet("Summary")
summary.write(0, 0, "February 2026 Payroll Baseline Management Summary", fmt_title)
summary.write(2, 0, "Total staff processed", fmt_header)
summary.write(2, 1, len(staff), fmt_num)
summary.write(3, 0, "Valid baseline mismatches (CL/CPL)", fmt_header)
summary.write(3, 1, len(valid_mismatch_rows), fmt_num)
summary.write(4, 0, "OD/ML violations", fmt_header)
summary.write(4, 1, len(od_ml_rows), fmt_num)
summary.write(5, 0, "No baseline manual review", fmt_header)
summary.write(5, 1, len(no_baseline_rows), fmt_num)
summary.write(7, 0, "Rule", fmt_header)
summary.write(7, 1, "Meaning", fmt_header)
summary.write(8, 0, "Valid Baseline Mismatch", fmt_cell)
summary.write(8, 1, "Only staff where CSV baseline exists (employee_id/biometric_id) and DB differs", fmt_cell)
summary.write(9, 0, "OD_ML Violations", fmt_cell)
summary.write(9, 1, "OD/ML Feb opening should be 0; non-zero is listed", fmt_cell)
summary.write(10, 0, "No Baseline Review", fmt_cell)
summary.write(10, 1, "CSV has no CL/CPL baseline for this identifier; do not accuse until reviewed", fmt_cell)
summary.set_column(0, 0, 34)
summary.set_column(1, 1, 85)

wb.close()
print("Done:", OUT_PATH)
print("Valid baseline mismatches:", len(valid_mismatch_rows))
print("OD/ML violations:", len(od_ml_rows))
print("No baseline review:", len(no_baseline_rows))
