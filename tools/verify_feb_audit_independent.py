#!/usr/bin/env python3
import csv
import subprocess as sp
from collections import defaultdict

MYSQL = "/Applications/XAMPP/xamppfiles/bin/mysql"


def q(sql):
    r = sp.run([MYSQL, "-uroot", "mcekknagar", "-e", sql], capture_output=True, text=True)
    if r.returncode != 0:
        raise RuntimeError(r.stderr)
    lines = r.stdout.strip().split("\n")
    if len(lines) < 2:
        return []
    hdr = lines[0].split("\t")
    return [dict(zip(hdr, ln.split("\t"))) for ln in lines[1:]]


csv_map = defaultdict(dict)
with open("/Applications/XAMPP/xamppfiles/htdocs/minerva/feb_leave_openingbalance.csv", newline="", encoding="utf-8-sig") as f:
    for r in csv.DictReader(f):
        key = (r.get("employee_id") or "").strip()
        lt = (r.get("leavetype_id") or "").strip()
        bal = (r.get("balance_days") or "").strip()
        if not key or lt not in ("3", "4"):
            continue
        try:
            csv_map[key][int(lt)] = float(bal)
        except ValueError:
            continue

rows = q(
    """
SELECT s.id AS staff_id, s.employee_id, COALESCE(s.biometric_id,'') AS biometric_id,
       s.name, slb.leave_type_id, slb.opening_balance
FROM staff_monthly_leave_balance slb
JOIN staff s ON s.id=slb.staff_id
WHERE slb.month=2 AND slb.year=2026 AND slb.leave_type_id IN (1,3,4,5)
  AND s.employee_id!='9000'
ORDER BY s.employee_id, slb.leave_type_id
"""
)

staff = {}
for r in rows:
    eid = r["employee_id"].strip()
    if not eid:
        continue
    rec = staff.setdefault(
        eid,
        {
            "staff_id": int(r["staff_id"]),
            "employee_id": eid,
            "biometric_id": (r.get("biometric_id") or "").strip(),
            "name": r.get("name") or "",
            "lt": {},
        },
    )
    rec["lt"][int(r["leave_type_id"])] = float(r["opening_balance"])

od = q(
    """
SELECT staff_id, SUM(leave_days) AS days
FROM staff_leave_request
WHERE leave_type_id=1 AND leave_direction='credit' AND status IN ('approve','approved','Approved')
  AND leave_from <= '2026-02-29' AND leave_to >= '2026-02-01'
GROUP BY staff_id
"""
)
od_map = {int(r["staff_id"]): float(r["days"]) for r in od}

EPS = 0.001
flags = []

for eid, s in staff.items():
    bid = s["biometric_id"]

    def base(lt):
        if eid in csv_map and lt in csv_map[eid]:
            return csv_map[eid][lt], eid
        if bid and bid in csv_map and lt in csv_map[bid]:
            return csv_map[bid][lt], bid
        return None, ""

    cl_db = s["lt"].get(3, 0.0)
    cl_csv, cl_key = base(3)
    cl_flag = (cl_key != "") and abs(cl_db - cl_csv) > EPS

    cpl_db = s["lt"].get(4, 0.0)
    cpl_csv, cpl_key = base(4)
    cpl_flag = (cpl_key != "") and abs(cpl_db - cpl_csv) > EPS

    od_db = s["lt"].get(1, 0.0)
    ml_db = s["lt"].get(5, 0.0)
    od_flag = od_db > EPS
    ml_flag = ml_db > EPS

    if cl_flag or cpl_flag or od_flag or ml_flag:
        flags.append(
            {
                "employee_id": eid,
                "name": s["name"],
                "cl_csv": 0.0 if cl_csv is None else cl_csv,
                "cl_db": cl_db,
                "cl_baseline": cl_key if cl_key else "NO_BASELINE",
                "cl_flag": cl_flag,
                "cpl_csv": 0.0 if cpl_csv is None else cpl_csv,
                "cpl_db": cpl_db,
                "cpl_baseline": cpl_key if cpl_key else "NO_BASELINE",
                "cpl_flag": cpl_flag,
                "od_db": od_db,
                "od_feb_approved_days": od_map.get(s["staff_id"], 0.0),
                "od_flag": od_flag,
                "ml_db": ml_db,
                "ml_flag": ml_flag,
            }
        )

print("INDEPENDENT RECOMPUTE")
print("Total staff with Feb rows:", len(staff))
print("Flagged suspects:", len(flags))
print("  CL mismatches:", sum(1 for r in flags if r["cl_flag"]))
print("  CPL mismatches:", sum(1 for r in flags if r["cpl_flag"]))
print("  OD non-zero:", sum(1 for r in flags if r["od_flag"]))
print("  ML non-zero:", sum(1 for r in flags if r["ml_flag"]))

for target in ("MCE2002EIE001", "MCE2005ITN001"):
    s = staff[target]
    bid = s["biometric_id"]

    def b(lt):
        if target in csv_map and lt in csv_map[target]:
            return csv_map[target][lt], target
        if bid and bid in csv_map and lt in csv_map[bid]:
            return csv_map[bid][lt], bid
        return None, ""

    cl_csv, cl_key = b(3)
    cpl_csv, cpl_key = b(4)
    print("\n", target)
    print(" CL: csv=", 0.0 if cl_csv is None else cl_csv, "db=", s["lt"].get(3, 0.0), "baseline=", cl_key or "NO_BASELINE")
    print(" CPL: csv=", 0.0 if cpl_csv is None else cpl_csv, "db=", s["lt"].get(4, 0.0), "baseline=", cpl_key or "NO_BASELINE")
    print(" OD db=", s["lt"].get(1, 0.0), "OD Feb approved=", od_map.get(s["staff_id"], 0.0))

proof_path = "/Applications/XAMPP/xamppfiles/htdocs/minerva/tools/feb_opening_balance_audit_proof.csv"
with open(proof_path, "w", newline="", encoding="utf-8") as f:
    w = csv.DictWriter(f, fieldnames=list(flags[0].keys()))
    w.writeheader()
    w.writerows(flags)
print("\nProof CSV:", proof_path)
