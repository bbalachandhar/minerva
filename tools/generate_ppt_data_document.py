"""
generate_ppt_data_document.py
Produces a detailed Word-style .txt document explaining every data point
in CEO_Appraisal_Presentation.pptx — source sheet, column, and derivation logic.
Run: source .venv/bin/activate && python tools/generate_ppt_data_document.py
"""
import openpyxl
from collections import Counter, defaultdict
from datetime import datetime

BASE = "/Applications/XAMPP/xamppfiles/htdocs/minerva"
SRC  = f"{BASE}/Book1 (003).xlsx"
OUT  = f"{BASE}/tools/CEO_PPT_Data_Derivation_Document.txt"

def load(sheet_name):
    wb = openpyxl.load_workbook(SRC, read_only=True)
    rows = list(wb[sheet_name].iter_rows(values_only=True))
    hdrs = rows[0]
    return [dict(zip(hdrs, r)) for r in rows[1:] if any(v for v in r)], hdrs

def g(row, key):
    for k in [key, key.strip(), key + ' ']:
        if k in row and row[k] is not None:
            return str(row[k]).strip()
    return ''

ad, ad_hdrs = load('Apprisal Data')
ald, al_hdrs = load('AL')

def compute(data):
    total    = len(data)
    ratings  = Counter(g(r,'Rating') for r in data)
    promos   = Counter(g(r,'Promotion') for r in data)
    appr     = Counter(g(r,'Apprisal') for r in data)
    bands    = Counter(r.get('Band') for r in data)
    directors= Counter(g(r,'Director') for r in data if g(r,'Director'))
    clients  = Counter(g(r,'Client') for r in data if g(r,'Client'))
    desigs   = Counter(g(r,'Actual Designation') for r in data if g(r,'Actual Designation'))
    supers   = Counter(g(r,'Supervisor') for r in data if g(r,'Supervisor'))
    from collections import Counter as _C
    grades   = _C(str(r.get('Grade','')).strip() for r in data if r.get('Grade'))
    mis      = Counter(g(r,'MIS Remarks') for r in data)
    dir_r = defaultdict(Counter); dir_p = defaultdict(Counter)
    band_r= defaultdict(Counter); band_p= defaultdict(Counter)
    desig_p = defaultdict(Counter)
    for row in data:
        d = g(row,'Director')
        dir_r[d][g(row,'Rating')] += 1
        dir_p[d][g(row,'Promotion')] += 1
        band_r[row.get('Band')][g(row,'Rating')] += 1
        band_p[row.get('Band')][g(row,'Promotion')] += 1
        desig_p[g(row,'Actual Designation')][g(row,'Promotion')] += 1
    rated = ratings.get('4',0)+ratings.get('3',0)+ratings.get('2',0)
    return dict(
        total=total, ratings=ratings, promos=promos, appr=appr,
        bands=bands, directors=directors, clients=clients, desigs=desigs,
        supers=supers, grades=grades, mis=mis,
        dir_r=dir_r, dir_p=dir_p, band_r=band_r, band_p=band_p, desig_p=desig_p,
        r4=ratings.get('4',0), r3=ratings.get('3',0), r2=ratings.get('2',0),
        na=ratings.get('NA',0), yes=promos.get('Yes',0), no=promos.get('No',0),
        full=appr.get('Fully Eligible',0), part=appr.get('Partially Eligible',0),
        noel=appr.get('Not Eligible',0), rated=rated,
    )

A  = compute(ad)
AL = compute(ald)

lines = []
w  = lines.append
wl = lambda: lines.append("")

def section(title):
    wl(); w("╔" + "═"*78 + "╗")
    w("║  " + title.upper().ljust(76) + "║")
    w("╚" + "═"*78 + "╝"); wl()

def sub(title):
    wl(); w("┌─ " + title + " " + "─"*(76-len(title))); wl()

def row(label, value, note=""):
    label_s = f"  {label:<38}"
    val_s   = f"{str(value):<18}"
    note_s  = f"← {note}" if note else ""
    w(f"{label_s} {val_s} {note_s}")

def derivation(label, formula, col, sheet):
    w(f"  {'Metric':<20}: {label}")
    w(f"  {'Source Sheet':<20}: {sheet}")
    w(f"  {'Source Column':<20}: {col}")
    w(f"  {'Formula / Logic':<20}: {formula}")
    wl()

# ─────────────────────────────────────────────────────────────────────────────
w("=" * 80)
w("  CEO APPRAISAL PRESENTATION — DATA DERIVATION & METHODOLOGY DOCUMENT")
w("=" * 80)
w(f"  Presentation File : CEO_Appraisal_Presentation.pptx")
w(f"  Source File       : Book1 (003).xlsx")
w(f"  Sheets Used       : 'Apprisal Data'  (Sheet 1)  and  'AL'  (Sheet 2)")
w(f"  Document Prepared : {datetime.now().strftime('%d %B %Y, %H:%M')}")
w(f"  Total Records     : Apprisal Data = {A['total']}  |  AL = {AL['total']}")
w("=" * 80)

# ─────────────────────────────────────────────────────────────────────────────
section("PART A — SOURCE DATA OVERVIEW")
# ─────────────────────────────────────────────────────────────────────────────

sub("A1. Sheet Structure — 'Apprisal Data' (Sheet 1)")
w(f"  Total data rows : {A['total']}  (row 2 to row {A['total']+1} in Excel)")
w(f"  Columns present : {len(ad_hdrs)}")
wl()
w("  No.  Column Name (exact)       Description / Used For")
w("  " + "-"*74)
col_desc = {
    'Empcode':            'Unique employee identifier',
    'Name':               'Employee full name',
    'DOJ':                'Date of Joining — used to determine eligibility',
    'Supervisor':         'Immediate reporting manager',
    'Director':           'Director responsible — used for director-level analysis',
    'Actual Designation': 'Current role/title — used for designation breakdown & promotions',
    'Client':             'Client account employee is mapped to',
    'Band':               'Seniority band (1=front-line, 2=mid, 3=senior)',
    'Grade':              'Grade code (e.g. 1C1, 2A2) — used in grade distribution',
    'Rating':             'Performance rating (4 / 3 / 2 / NA) — core appraisal metric',
    'Promotion':          'Promotion decision (Yes / No)',
    'Apprisal':           'Eligibility status (Fully Eligible / Partially Eligible / Not Eligible)',
    'MIS Remarks':        'MIS note explaining eligibility status',
}
for i, col in enumerate(ad_hdrs, 1):
    if col:
        col_clean = str(col).strip()
        desc = col_desc.get(col_clean, col_desc.get(col_clean.rstrip(), "Additional reference field"))
        w(f"  {i:>2}.  {col_clean:<28} {desc}")
wl()

sub("A2. Sheet Structure — 'AL' (Sheet 2)")
w(f"  Total data rows : {AL['total']}  (Asset Living LLC employees only)")
w(f"  Columns         : Identical to 'Apprisal Data' sheet")
w(f"  Scope           : All 505 rows belong to Client = 'Asset Living LLC'")
w(f"  Purpose         : Dedicated client-level performance spotlight for the last slide")
wl()
w("  NOTE: 'Apprisal Data' contains ALL clients (including AL employees).")
w("  The 'AL' sheet is a client-filtered subset — used for the final CEO spotlight slide.")
wl()

# ─────────────────────────────────────────────────────────────────────────────
section("PART B — METRIC DERIVATION LOGIC (SLIDE BY SLIDE)")
# ─────────────────────────────────────────────────────────────────────────────

# ── SLIDE 1 ──────────────────────────────────────────────────────────────────
sub("SLIDE 1 — Title Slide")
w("  Content    : Organisation name, presentation title, date, total headcount")
wl()
w(f"  ┌ Total Employees Covered: {A['total']}")
w(f"  │ Source Sheet  : Apprisal Data")
w(f"  │ Derivation    : COUNT of all non-blank rows in the sheet (rows 2 onwards)")
w(f"  │ Formula       : len([row for row in sheet if any value in row])")
w(f"  └ Result        : {A['total']}")
wl()

# ── SLIDE 2 ──────────────────────────────────────────────────────────────────
sub("SLIDE 2 — Executive Snapshot (KPI Dashboard)")
w("  Source Sheet  : Apprisal Data (all 593 records)")
w("  All KPIs are derived from this sheet unless stated otherwise.")
wl()
w("  ─── ROW 1 KPI CARDS ───────────────────────────────────────────────────")
wl()
derivation("Total Employees",
    f"COUNT of all data rows → {A['total']}",
    "All rows", "Apprisal Data")

derivation("Fully Eligible",
    f"COUNT WHERE 'Apprisal' column = 'Fully Eligible' → {A['full']} ({A['full']*100//A['total']}%)",
    "'Apprisal' column", "Apprisal Data")

derivation("Partially Eligible",
    f"COUNT WHERE 'Apprisal' column = 'Partially Eligible' → {A['part']} ({A['part']*100//A['total']}%)",
    "'Apprisal' column", "Apprisal Data")

derivation("Not Eligible",
    f"COUNT WHERE 'Apprisal' column = 'Not Eligible' → {A['noel']} ({A['noel']*100//A['total']}%)\n"
    f"  {'':20}  These employees have Rating = 'NA' and joined after 1 Oct 2025\n"
    f"  {'':20}  MIS Remarks confirms: 'Joined after 1st Oct 2025'",
    "'Apprisal' column + 'MIS Remarks' column", "Apprisal Data")

w("  ─── ROW 2 KPI CARDS ───────────────────────────────────────────────────")
wl()
derivation("Rated Employees",
    f"COUNT WHERE 'Rating' column IN (4, 3, 2) — excludes 'NA'\n"
    f"  {'':20}  → {A['rated']} ({A['rated']*100//A['total']}% of total)",
    "'Rating' column", "Apprisal Data")

derivation("Rating 4 — Exceeds",
    f"COUNT WHERE 'Rating' = '4' → {A['r4']} ({A['r4']*100//A['total']}%)",
    "'Rating' column", "Apprisal Data")

derivation("Rating 3 — Meets",
    f"COUNT WHERE 'Rating' = '3' → {A['r3']} ({A['r3']*100//A['total']}%)",
    "'Rating' column", "Apprisal Data")

derivation("Promoted",
    f"COUNT WHERE 'Promotion' column = 'Yes' → {A['yes']} ({A['yes']*100//A['total']}%)",
    "'Promotion' column", "Apprisal Data")

w("  ─── BAND SUMMARY BAR ────────────────────────────────────────────────────")
wl()
for b in [1,2,3]:
    derivation(f"Band {b} Count",
        f"COUNT WHERE 'Band' = {b} → {A['bands'].get(b,0)}",
        "'Band' column", "Apprisal Data")

w("  ─── DIRECTOR PORTFOLIO BAR ──────────────────────────────────────────────")
wl()
for d, cnt in A['directors'].most_common():
    derivation(f"{d}",
        f"COUNT WHERE 'Director' = '{d}' → {cnt} ({cnt*100//A['total']}%)",
        "'Director' column", "Apprisal Data")

# ── SLIDE 3 ──────────────────────────────────────────────────────────────────
sub("SLIDE 3 — Eligibility & Performance Ratings")
w("  Source Sheet  : Apprisal Data")
w("  Two-panel layout: Pie chart (eligibility) on left | Column chart (ratings) on right")
wl()
w("  ─── LEFT PANEL: ELIGIBILITY PIE ─────────────────────────────────────────")
wl()
w("  The 'Apprisal' column uses the following exact text values:")
w(f"    'Fully Eligible'     → {A['full']:>4} employees")
w(f"    'Partially Eligible' → {A['part']:>4} employees")
w(f"    'Not Eligible'       → {A['noel']:>4} employees")
wl()
w("  Eligibility is determined by 'DOJ' (Date of Joining) relative to the appraisal cycle:")
w("    • Fully Eligible     : DOJ on or before 1 April 2025 (full year in service)")
w("    • Partially Eligible : DOJ between 1 October 2024 and 31 March 2025")
w("    • Not Eligible       : DOJ after 1 October 2025 (MIS marks them NA)")
w("  The 'Apprisal' column stores the pre-computed eligibility label.")
w("  The 'MIS Remarks' column stores the reason text confirming each category.")
wl()
w("  ─── RIGHT PANEL: RATING DISTRIBUTION ────────────────────────────────────")
wl()
w("  Source Column : 'Rating'  (note: column header has a trailing space in Excel)")
w("  Distinct values found:")
for rating, cnt in sorted(A['ratings'].items()):
    pct = cnt*100//A['total']
    w(f"    Rating = '{rating}' → {cnt:>4} employees  ({pct}%)")
wl()
w("  Logic:")
w("    Rating 4 (Exceeds) : 'Rating' = '4'  — awarded to top performers")
w("    Rating 3 (Meets)   : 'Rating' = '3'  — awarded to on-track performers")
w("    Rating 2 (Below)   : 'Rating' = '2'  — below expectations")
w("    NA                 : 'Rating' = 'NA' — Not Applicable; assigned to Not Eligible")
wl()
w("  % calculation : (count of rating / total rows) × 100")
wl()

# ── SLIDE 4 ──────────────────────────────────────────────────────────────────
sub("SLIDE 4 — Director Performance")
w("  Source Sheet  : Apprisal Data")
w("  Grouped by 'Director' column — cross-tabulated with 'Rating' and 'Promotion'")
wl()
w("  ─── COLUMN CHART: Rating by Director ───────────────────────────────────")
wl()
w(f"  {'Director':<28} {'Total':>6} {'R4':>5} {'R3':>5} {'NA':>5} {'Promoted':>9} {'Promo%':>7}")
w("  " + "-"*68)
for d, _ in A['directors'].most_common():
    if not d: continue
    dr = A['dir_r'][d]; dp = A['dir_p'][d]
    tot = sum(dr.values())
    pct = f"{dp.get('Yes',0)*100//tot}%" if tot else "0%"
    w(f"  {d:<28} {tot:>6} {dr.get('4',0):>5} {dr.get('3',0):>5} {dr.get('NA',0):>5} {dp.get('Yes',0):>9} {pct:>7}")
wl()
w("  Derivation for each cell:")
w("    Total       : COUNT WHERE 'Director' = [name]")
w("    R4          : COUNT WHERE 'Director' = [name] AND 'Rating' = '4'")
w("    R3          : COUNT WHERE 'Director' = [name] AND 'Rating' = '3'")
w("    NA          : COUNT WHERE 'Director' = [name] AND 'Rating' = 'NA'")
w("    Promoted    : COUNT WHERE 'Director' = [name] AND 'Promotion' = 'Yes'")
w("    Promo%      : (Promoted / Total) × 100")
wl()
w("  ─── BAND TABLE ──────────────────────────────────────────────────────────")
wl()
w(f"  {'Band':<10} {'Total':>6} {'R4':>5} {'R3':>5} {'NA':>5} {'Promoted':>9}")
w("  " + "-"*44)
for b in [1,2,3]:
    br = A['band_r'].get(b,Counter()); bp = A['band_p'].get(b,Counter())
    tot_b = A['bands'].get(b,0)
    w(f"  Band {b}     {tot_b:>6} {br.get('4',0):>5} {br.get('3',0):>5} {br.get('NA',0):>5} {bp.get('Yes',0):>9}")
wl()
w("  Derivation: Same logic as Director table but grouped by 'Band' column (value 1, 2, or 3)")
wl()

# ── SLIDE 5 ──────────────────────────────────────────────────────────────────
sub("SLIDE 5 — Promotions Analysis")
w("  Source Sheet  : Apprisal Data")
w("  Source Column : 'Promotion'  (note: trailing space in header)")
wl()
w(f"  Overall:  Yes = {A['yes']}  ({A['yes']*100//A['total']}%)  |  No = {A['no']}  ({A['no']*100//A['total']}%)")
wl()
w("  ─── PIE CHART: Promoted vs Not ─────────────────────────────────────────")
wl()
w(f"    Promoted (Yes) : COUNT WHERE 'Promotion' = 'Yes' → {A['yes']}")
w(f"    Not Promoted   : COUNT WHERE 'Promotion' = 'No'  → {A['no']}")
wl()
w("  ─── COLUMN CHART: Promotions by Band ──────────────────────────────────")
wl()
w(f"  {'Band':<10} {'Promoted':>10} {'Not Promoted':>14} {'Total':>7} {'Promo Rate':>11}")
w("  " + "-"*54)
for b in [1,2,3]:
    bp = A['band_p'].get(b,Counter()); tot_b = A['bands'].get(b,0)
    pct = f"{bp.get('Yes',0)*100//tot_b}%" if tot_b else "0%"
    w(f"  Band {b}      {bp.get('Yes',0):>10} {bp.get('No',0):>14} {tot_b:>7} {pct:>11}")
wl()
w("    Derivation: COUNT WHERE 'Band' = [b] AND 'Promotion' = 'Yes' / 'No'")
wl()
w("  ─── BAR CHART: Top Promoted Designations ───────────────────────────────")
wl()
top_promo = sorted([(d,c.get('Yes',0)) for d,c in A['desig_p'].items() if c.get('Yes',0)>0],
                   key=lambda x:-x[1])[:12]
w(f"  {'Designation':<35} {'Promoted':>10}")
w("  " + "-"*47)
for desig, cnt in top_promo:
    w(f"  {desig:<35} {cnt:>10}")
wl()
w("    Derivation: COUNT WHERE 'Promotion' = 'Yes' grouped by 'Actual Designation'")
w("    Sorted: Descending by promoted count; top 10 shown as horizontal bars")
wl()

# ── SLIDE 6 ──────────────────────────────────────────────────────────────────
sub("SLIDE 6 — Client & Grade Distribution")
w("  Source Sheet  : Apprisal Data")
wl()
w("  ─── BAR CHART: Headcount by Client ────────────────────────────────────")
wl()
w(f"  {'Client':<40} {'Count':>7} {'%':>6}")
w("  " + "-"*55)
for cli, cnt in A['clients'].most_common():
    w(f"  {cli:<40} {cnt:>7} {cnt*100//A['total']:>5}%")
wl()
w("    Derivation: COUNT of rows grouped by 'Client' column, sorted descending")
wl()
w("  ─── TABLE: Grade Breakdown ─────────────────────────────────────────────")
wl()
from collections import Counter as _C2
grades_c = _C2(str(r.get('Grade','')).strip() for r in ad if r.get('Grade'))
gp = defaultdict(int)
for row in ad:
    if g(row,'Promotion')=='Yes':
        gp[str(row.get('Grade','')).strip()] += 1
w(f"  {'Grade':<12} {'Count':>7} {'%':>6} {'Promoted':>10}")
w("  " + "-"*38)
for grade, cnt in grades_c.most_common(14):
    w(f"  {grade:<12} {cnt:>7} {cnt*100//A['total']:>5}% {gp.get(grade,0):>10}")
wl()
w("    Derivation:")
w("      Count    : COUNT of rows grouped by 'Grade' column")
w("      %        : (count / total) × 100")
w("      Promoted : COUNT WHERE 'Grade' = [grade] AND 'Promotion' = 'Yes'")
wl()

# ── SLIDE 7 ──────────────────────────────────────────────────────────────────
sub("SLIDE 7 — Key Observations & Recommendations")
w("  Source Sheet  : Apprisal Data (all derived metrics from earlier analysis)")
w("  This slide is an interpretive summary — no new raw column is introduced.")
w("  Each observation references computed metrics described in earlier slides.")
wl()
obs_details = [
    ("01 — Eligibility Gap",
     f"'Apprisal' column + 'MIS Remarks'\n"
     f"     {A['noel']} employees = Not Eligible. Joined after Oct 2025.\n"
     f"     Percentage = {A['noel']}/{A['total']} = {A['noel']*100//A['total']}%"),
    ("02 — Rating Concentration",
     f"'Rating' column values.\n"
     f"     R3={A['r3']} vs R4={A['r4']}. Ratio = {A['r3']}/{A['r4']} = {A['r3']//max(A['r4'],1):.1f}× more at R3 than R4.\n"
     f"     Recommendation is interpretive — signals calibration need."),
    ("03 — Promotion Rate",
     f"'Promotion' column + 'Band' column.\n"
     f"     {A['yes']} promoted out of {A['total']} = {A['yes']*100//A['total']}%.\n"
     f"     Band 1 rate = {A['band_p'].get(1,Counter()).get('Yes',0)}/{A['bands'].get(1,0)} = "
     f"{A['band_p'].get(1,Counter()).get('Yes',0)*100//max(A['bands'].get(1,0),1)}%"),
    ("04 — Director Portfolio Concentration",
     f"'Director' column.\n"
     f"     Muzzafar Sheriff manages {A['directors'].get('Muzzafar Sheriff',0)} employees = "
     f"{A['directors'].get('Muzzafar Sheriff',0)*100//A['total']}% of total workforce.\n"
     f"     Promotions under Sheriff = {A['dir_p']['Muzzafar Sheriff'].get('Yes',0)}"),
    ("05 — Partial Eligibility Action",
     f"'Apprisal' column.\n"
     f"     {A['part']} employees are Partially Eligible = {A['part']*100//A['total']}%.\n"
     f"     Requires pro-rated increment computation based on months of service."),
]
for title, detail in obs_details:
    w(f"  ● {title}")
    for line in detail.split('\n'):
        w(f"     {line}")
    wl()

# ── SLIDE 8 ──────────────────────────────────────────────────────────────────
sub("SLIDE 8 — Asset Living LLC (AL Sheet) — Final Slide")
w("  Source Sheet  : AL  (second sheet in Book1 (003).xlsx)")
w(f"  Total Records : {AL['total']}")
w("  Scope         : All 505 records belong to Client = 'Asset Living LLC'")
w("  Column structure is identical to 'Apprisal Data'")
wl()
w("  ─── 6 KPI CARDS ─────────────────────────────────────────────────────────")
wl()
al_kpis = [
    ("Total Headcount",    f"{AL['total']}",                            "COUNT of all rows in AL sheet"),
    ("Fully Eligible",     f"{AL['full']} ({AL['full']*100//AL['total']}%)", "COUNT WHERE 'Apprisal' = 'Fully Eligible'"),
    ("Partially Eligible", f"{AL['part']} ({AL['part']*100//AL['total']}%)", "COUNT WHERE 'Apprisal' = 'Partially Eligible'"),
    ("Not Eligible",       f"{AL['noel']} ({AL['noel']*100//AL['total']}%)", "COUNT WHERE 'Apprisal' = 'Not Eligible'  (Rating = NA)"),
    ("Rating 4 (Top)",     f"{AL['r4']} ({AL['r4']*100//AL['total']}%)",     "COUNT WHERE 'Rating' = '4'"),
    ("Promoted",           f"{AL['yes']} ({AL['yes']*100//AL['total']}%)",   "COUNT WHERE 'Promotion' = 'Yes'"),
]
for kpi_name, value, derivtn in al_kpis:
    w(f"  {kpi_name:<25} = {value:<18} ← {derivtn}")
wl()

w("  ─── DIRECTOR RATING CHART ──────────────────────────────────────────────")
wl()
al_dirs = [d for d,_ in AL['directors'].most_common() if d]
w(f"  {'Director':<28} {'Total':>6} {'R4':>5} {'R3':>5} {'NA':>5} {'Promoted':>9} {'Promo%':>7}")
w("  " + "-"*68)
for d in al_dirs:
    dr = AL['dir_r'][d]; dp = AL['dir_p'][d]
    tot = sum(dr.values())
    pct = f"{dp.get('Yes',0)*100//tot}%" if tot else "0%"
    w(f"  {d:<28} {tot:>6} {dr.get('4',0):>5} {dr.get('3',0):>5} {dr.get('NA',0):>5} {dp.get('Yes',0):>9} {pct:>7}")
wl()
w("    Source Column : 'Director' cross-tabulated with 'Rating' and 'Promotion'")
w("    Same derivation logic as Slide 4 but applied to the AL sheet only")
wl()

w("  ─── BAND BREAKDOWN TABLE ───────────────────────────────────────────────")
wl()
w(f"  {'Band':<10} {'Total':>6} {'R4':>5} {'R3':>5} {'NA':>5} {'Promoted':>9} {'Promo%':>7}")
w("  " + "-"*50)
for b in [1,2,3]:
    br = AL['band_r'].get(b,Counter()); bp = AL['band_p'].get(b,Counter())
    tot_b = AL['bands'].get(b,0)
    pct = f"{bp.get('Yes',0)*100//tot_b}%" if tot_b else "0%"
    w(f"  Band {b}     {tot_b:>6} {br.get('4',0):>5} {br.get('3',0):>5} {br.get('NA',0):>5} {bp.get('Yes',0):>9} {pct:>7}")
wl()
w("    Source Columns: 'Band', 'Rating', 'Promotion'")
w("    Derivation    : Grouped COUNT with cross-filter on each column")
wl()

w("  ─── TOP PROMOTED DESIGNATIONS ──────────────────────────────────────────")
wl()
al_top = sorted([(d,c.get('Yes',0)) for d,c in AL['desig_p'].items() if c.get('Yes',0)>0],
                key=lambda x:-x[1])[:10]
w(f"  {'Designation':<35} {'Promoted':>10}")
w("  " + "-"*47)
for desig, cnt in al_top:
    w(f"  {desig:<35} {cnt:>10}")
wl()
w("    Source Column : 'Actual Designation' cross-tabulated with 'Promotion'")
w("    Derivation    : COUNT WHERE 'Promotion' = 'Yes' grouped by designation")
w("    Display       : Horizontal bars — bar width scaled to max promoted count")
wl()

# ─────────────────────────────────────────────────────────────────────────────
section("PART C — COMPLETE DATA DICTIONARY")
# ─────────────────────────────────────────────────────────────────────────────

sub("C1. Column Reference Table")
w(f"  {'Column':<28} {'Distinct Values / Type':<35} {'Used In Slides'}")
w("  " + "-"*78)
col_meta = [
    ('Empcode',           'Alphanumeric ID',                             'None (identifier only)'),
    ('Name',              'Free text — employee name',                    'None (identifier only)'),
    ('DOJ',               'Date (datetime type in Excel)',                'Eligibility note reference'),
    ('Supervisor',        'Text — manager name',                          'Slide 4 reference'),
    ('Director',          'Text — 3 directors present',                   'Slides 2, 4, 8'),
    ('Actual Designation','Text — approx 15+ role titles',               'Slides 5, 6, 8'),
    ('Client',            'Text — 7 distinct clients',                    'Slides 2, 6'),
    ('Band',              'Integer 1, 2, 3',                              'Slides 2, 4, 5, 8'),
    ('Grade',             'Alphanumeric code (e.g. 1C1, 2A2)',           'Slide 6'),
    ('Rating',            "'4' / '3' / '2' / 'NA'",                     'Slides 2, 3, 4, 8'),
    ('Promotion',         "'Yes' / 'No'",                                 'Slides 2, 5, 8'),
    ('Apprisal',          "'Fully Eligible' / 'Partially Eligible' / 'Not Eligible'", 'Slides 2, 3'),
    ('MIS Remarks',       'Text — reason for eligibility status',         'Slide 3 notes'),
]
for col, dtype, usage in col_meta:
    w(f"  {col:<28} {dtype:<35} {usage}")
wl()

sub("C2. Percentage Calculation Method")
w("  All percentages in the presentation use integer floor division:")
w("  Formula: ( count / total_rows ) × 100  [floor — no rounding]")
w("  Example: 74 promoted / 593 total = 12% (displayed)")
w("           Actual: 12.48% — displayed as 12% (floor)")
wl()

sub("C3. Director Name Mapping")
w("  Exact values in 'Director' column → displayed in charts:")
w("    'Muzzafar Sheriff'  → 'Sheriff'  (truncated to last name in charts)")
w("    'Payal Jain'        → 'Jain'")
w("    'Mohit Agrawal'     → 'Agrawal'")
wl()

sub("C4. Data Quality Notes")
w("  • The column headers 'Rating', 'Promotion', and 'MIS Remarks' have a")
w("    trailing space character in the Excel file.")
w("    Resolution: Code strips whitespace from column names before matching.")
wl()
w("  • 'Apprisal' column is misspelled in the source (should be 'Appraisal').")
w("    This is treated as-is; the document and code use the exact source spelling.")
wl()
w("  • The 'AL' sheet is a subset of 'Apprisal Data' filtered to Asset Living LLC.")
w("    Metrics in Slide 8 will add up to a sub-total, not the full 593.")
wl()
w("  • Rating '2' appears only once in 'Apprisal Data' (0 in AL sheet).")
w("    It is included for completeness but is not visible in bar charts due to scale.")
wl()

sub("C5. Eligibility Logic Detail")
w("  The 'Apprisal' column stores computed eligibility. The underlying logic is:")
wl()
w("  IF DOJ <= 1 April 2025         → 'Fully Eligible'     (full 12 months in cycle)")
w("  IF DOJ between 1 Oct 2024")
w("     and 31 March 2025           → 'Partially Eligible' (6–11 months in cycle)")
w("  IF DOJ > 1 October 2025        → 'Not Eligible'       (< 6 months; MIS = joined after Oct 2025)")
wl()
w("  Pro-rata increments: Partially Eligible employees receive a merit increment")
w("  proportional to the number of months worked in the appraisal year.")
wl()
w("")

# ─────────────────────────────────────────────────────────────────────────────
section("PART D — SUMMARY COMPARISON: APPRISAL DATA vs AL SHEET")
# ─────────────────────────────────────────────────────────────────────────────

w(f"  {'Metric':<35} {'Apprisal Data':>16} {'AL Sheet':>12} {'Difference':>12}")
w("  " + "-"*78)
metrics_cmp = [
    ("Total Employees",        A['total'],   AL['total'],  None),
    ("Fully Eligible",         A['full'],    AL['full'],   None),
    ("Partially Eligible",     A['part'],    AL['part'],   None),
    ("Not Eligible",           A['noel'],    AL['noel'],   None),
    ("Rated (R4+R3+R2)",       A['rated'],   AL['rated'],  None),
    ("Rating 4 (Exceeds)",     A['r4'],      AL['r4'],     None),
    ("Rating 3 (Meets)",       A['r3'],      AL['r3'],     None),
    ("Rating 2 (Below)",       A['r2'],      AL['r2'],     None),
    ("Not Applicable (NA)",    A['na'],      AL['na'],     None),
    ("Promoted (Yes)",         A['yes'],     AL['yes'],    None),
    ("Not Promoted (No)",      A['no'],      AL['no'],     None),
    ("Band 1 Employees",       A['bands'].get(1,0), AL['bands'].get(1,0), None),
    ("Band 2 Employees",       A['bands'].get(2,0), AL['bands'].get(2,0), None),
    ("Band 3 Employees",       A['bands'].get(3,0), AL['bands'].get(3,0), None),
]
for lbl, av, alv, _ in metrics_cmp:
    diff = av - alv
    av_pct  = f"{av} ({av*100//A['total']}%)"
    alv_pct = f"{alv} ({alv*100//AL['total']}%)"
    w(f"  {lbl:<35} {av_pct:>16} {alv_pct:>12} {diff:>+12}")
wl()
w("  NOTE: AL Sheet is a SUBSET of Apprisal Data (Asset Living LLC employees only).")
w("        Differences represent non-AL employees in the Apprisal Data sheet.")
wl()

# ─────────────────────────────────────────────────────────────────────────────
w("=" * 80)
w("END OF DATA DERIVATION DOCUMENT")
w(f"Generated: {datetime.now().strftime('%d %B %Y, %H:%M')}")
w("File: CEO_Appraisal_Presentation.pptx  |  Source: Book1 (003).xlsx")
w("=" * 80)

with open(OUT, 'w', encoding='utf-8') as f:
    f.write('\n'.join(lines))

print(f"✅  Document saved → {OUT}")
