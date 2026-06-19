"""Quick test of the solver with a small school scenario."""

import json
import solver

test_data = {
    "working_days": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
    "periods": [
        {"id": 1, "name": "Period 1", "start": "09:00", "end": "09:40", "sort_order": 1},
        {"id": 2, "name": "Period 2", "start": "09:40", "end": "10:20", "sort_order": 2},
        {"id": 3, "name": "Period 3", "start": "10:30", "end": "11:10", "sort_order": 3},
        {"id": 4, "name": "Period 4", "start": "11:10", "end": "11:50", "sort_order": 4},
        {"id": 5, "name": "Period 5", "start": "12:30", "end": "13:10", "sort_order": 5},
        {"id": 6, "name": "Period 6", "start": "13:10", "end": "13:50", "sort_order": 6},
    ],
    "subject_loads": [
        # Class A: 6 subjects (29 periods + 1 joint = 30 = 5 days × 6 periods)
        {"class_id": 1, "section_id": 1, "sgs_id": 101, "sg_id": 10,
         "subject_name": "Tamil", "periods_per_week": 5, "consecutive": 1,
         "teacher_ids": [501], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 1, "section_id": 1, "sgs_id": 102, "sg_id": 10,
         "subject_name": "English", "periods_per_week": 5, "consecutive": 1,
         "teacher_ids": [502], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 1, "section_id": 1, "sgs_id": 103, "sg_id": 10,
         "subject_name": "Maths", "periods_per_week": 5, "consecutive": 1,
         "teacher_ids": [503], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 1, "section_id": 1, "sgs_id": 104, "sg_id": 10,
         "subject_name": "Science", "periods_per_week": 5, "consecutive": 1,
         "teacher_ids": [504], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 1, "section_id": 1, "sgs_id": 105, "sg_id": 10,
         "subject_name": "Social", "periods_per_week": 4, "consecutive": 1,
         "teacher_ids": [505], "all_teachers_required": False,
         "max_per_day": 1, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 1, "section_id": 1, "sgs_id": 106, "sg_id": 10,
         "subject_name": "Lab", "periods_per_week": 4, "consecutive": 2,
         "teacher_ids": [504], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": False, "priority": 8, "batch_id": None},

        # Class B: shares teachers 501, 502, 503 with Class A (29 + 1 joint = 30)
        {"class_id": 2, "section_id": 1, "sgs_id": 201, "sg_id": 20,
         "subject_name": "Tamil", "periods_per_week": 5, "consecutive": 1,
         "teacher_ids": [501], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 2, "section_id": 1, "sgs_id": 202, "sg_id": 20,
         "subject_name": "English", "periods_per_week": 5, "consecutive": 1,
         "teacher_ids": [502], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 2, "section_id": 1, "sgs_id": 203, "sg_id": 20,
         "subject_name": "Maths", "periods_per_week": 5, "consecutive": 1,
         "teacher_ids": [503], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 2, "section_id": 1, "sgs_id": 204, "sg_id": 20,
         "subject_name": "Science", "periods_per_week": 5, "consecutive": 1,
         "teacher_ids": [506], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 2, "section_id": 1, "sgs_id": 205, "sg_id": 20,
         "subject_name": "Social", "periods_per_week": 4, "consecutive": 1,
         "teacher_ids": [507], "all_teachers_required": False,
         "max_per_day": 1, "distribute_evenly": True, "priority": 5, "batch_id": None},
        {"class_id": 2, "section_id": 1, "sgs_id": 206, "sg_id": 20,
         "subject_name": "Art", "periods_per_week": 4, "consecutive": 2,
         "teacher_ids": [508], "all_teachers_required": False,
         "max_per_day": 2, "distribute_evenly": False, "priority": 8, "batch_id": None},
    ],
    "joint_lessons": [
        {"id": 1, "name": "Assembly", "subject_id": 99, "periods_per_week": 1,
         "consecutive": 1, "teacher_ids": [509], "all_teachers_required": False,
         "max_per_day": 1,
         "classes": [
             {"class_id": 1, "section_id": 1, "sgs_id": 0, "sg_id": 0},
             {"class_id": 2, "section_id": 1, "sgs_id": 0, "sg_id": 0},
         ],
         "fixed_slots": None},
    ],
    "teacher_constraints": {
        "501": {"max_per_day": 6, "max_per_week": 30, "avoid_first_period": False, "avoid_last_period": False, "max_consecutive": 0},
        "504": {"max_per_day": 6, "max_per_week": 30, "avoid_first_period": False, "avoid_last_period": True, "max_consecutive": 0},
    },
    "teacher_unavailability": {
        "501": {"Monday": [6], "Friday": [6]},
    },
    "class_unavailability": {},
    "locked_entries": [],
    "settings": {"max_same_subject_day": 2, "fill_free_periods": True},
    "time_limit": 30,
}

print("Running solver test with 2 classes, 12 subject loads, 1 joint lesson...")
print(f"Total periods to place: {sum(l['periods_per_week'] for l in test_data['subject_loads'])} + {sum(j['periods_per_week'] for j in test_data['joint_lessons'])} (joint)")
print()

result = solver.solve(test_data)

print(f"Status: {result['status']}")
print(f"Quality: {result.get('quality', 'N/A')}%")
print(f"Placed: {result.get('total_placed', 'N/A')}/{result.get('total_required', 'N/A')}")
print(f"Solve time: {result.get('solve_time_seconds', 'N/A')}s")
print(f"Entries: {len(result.get('entries', []))}")
print(f"Unplaced: {len(result.get('unplaced', []))}")
print()

if result["status"] == "success":
    # Verify constraints
    slot_map = {}  # (class_id, section_id, day, period_id) -> subject
    teacher_map = {}  # (teacher_id, day, period_id) -> subject

    for e in result["entries"]:
        key = (e["class_id"], e["section_id"], e["day"], e["period_id"])
        if key in slot_map:
            print(f"ERROR: Class-section clash at {key}: {slot_map[key]} vs {e['subject_group_subject_id']}")
        slot_map[key] = e["subject_group_subject_id"]

        if e["staff_id"]:
            tkey = (e["staff_id"], e["day"], e["period_id"])
            if tkey in teacher_map:
                print(f"ERROR: Teacher clash at {tkey}: {teacher_map[tkey]} vs {e['subject_group_subject_id']}")
            teacher_map[tkey] = e["subject_group_subject_id"]

    # Check teacher unavailability
    for e in result["entries"]:
        tid = str(e["staff_id"])
        if tid in test_data["teacher_unavailability"]:
            day = e["day"]
            if day in test_data["teacher_unavailability"][tid]:
                if e["period_id"] in test_data["teacher_unavailability"][tid][day]:
                    print(f"ERROR: Teacher {tid} assigned at unavailable slot {day} P{e['period_id']}")

    # Print timetable grid for Class A
    print("--- Class 1 Section 1 Timetable ---")
    sgs_names = {l["sgs_id"]: l["subject_name"] for l in test_data["subject_loads"]}
    for day in test_data["working_days"]:
        row = []
        for pid in [p["id"] for p in test_data["periods"]]:
            found = [e for e in result["entries"]
                     if e["class_id"] == 1 and e["section_id"] == 1
                     and e["day"] == day and e["period_id"] == pid]
            if found:
                name = sgs_names.get(found[0]["subject_group_subject_id"], "Joint")
                row.append(f"{name:>8}")
            else:
                row.append(f"{'---':>8}")
        print(f"{day:>10}: {' | '.join(row)}")

    print()
    print("--- Class 2 Section 1 Timetable ---")
    for day in test_data["working_days"]:
        row = []
        for pid in [p["id"] for p in test_data["periods"]]:
            found = [e for e in result["entries"]
                     if e["class_id"] == 2 and e["section_id"] == 1
                     and e["day"] == day and e["period_id"] == pid]
            if found:
                name = sgs_names.get(found[0]["subject_group_subject_id"], "Joint")
                row.append(f"{name:>8}")
            else:
                row.append(f"{'---':>8}")
        print(f"{day:>10}: {' | '.join(row)}")

    print()
    print("Constraint verification passed!" if "ERROR" not in str(result) else "ERRORS FOUND!")
else:
    print(f"Solver failed: {result.get('message', 'unknown')}")
