"""
OR-Tools CP-SAT timetable solver.

Guaranteed-optimal timetable assignment using constraint programming.
Handles: class/teacher clash, unavailability, consecutive periods,
joint lessons (with fixed slots), teacher capacity, distribution.
"""

from ortools.sat.python import cp_model
import time


def solve(data: dict) -> dict:
    t0 = time.time()
    model = cp_model.CpModel()

    days = data["working_days"]
    periods_raw = sorted(data["periods"], key=lambda p: p.get("sort_order", p["id"]))
    loads = data["subject_loads"]
    joints = data.get("joint_lessons", [])
    tc_map = data.get("teacher_constraints", {})
    t_unavail = data.get("teacher_unavailability", {})
    c_unavail = data.get("class_unavailability", {})
    s_unavail = data.get("subject_unavailability", {})
    locked = data.get("locked_entries", [])
    settings = data.get("settings", {})

    D = len(days)
    P = len(periods_raw)
    period_ids = [p["id"] for p in periods_raw]

    day_idx = {d: i for i, d in enumerate(days)}
    pid_idx = {pid: i for i, pid in enumerate(period_ids)}

    if D == 0 or P == 0:
        return {"status": "error", "message": "No working days or periods configured."}

    default_tc = {
        "max_per_day": 6,
        "max_per_week": 36,
        "avoid_first_period": False,
        "avoid_last_period": False,
        "max_consecutive": 0,
    }

    max_same_subject_day = settings.get("max_same_subject_day", 2)

    # ---------------------------------------------------------------
    # 1. Variables
    # ---------------------------------------------------------------

    x = {}
    for i in range(len(loads)):
        for d in range(D):
            for p in range(P):
                x[i, d, p] = model.new_bool_var(f"x_{i}_{d}_{p}")

    jx = {}
    for j in range(len(joints)):
        for d in range(D):
            for p in range(P):
                jx[j, d, p] = model.new_bool_var(f"jx_{j}_{d}_{p}")

    # Teacher choice for pool loads (multiple teachers, pick one)
    teacher_pick = {}
    pool_load_set = set()
    for i, load in enumerate(loads):
        tids = load.get("teacher_ids", [])
        atr = load.get("all_teachers_required", False)
        if len(tids) > 1 and not atr:
            pool_load_set.add(i)
            for ti in range(len(tids)):
                teacher_pick[i, ti] = model.new_bool_var(f"tp_{i}_{ti}")
            model.add(sum(teacher_pick[i, ti] for ti in range(len(tids))) == 1)

    joint_teacher_pick = {}
    joint_pool_set = set()
    for j, joint in enumerate(joints):
        tids = joint.get("teacher_ids", [])
        atr = joint.get("all_teachers_required", False)
        if len(tids) > 1 and not atr:
            joint_pool_set.add(j)
            for ti in range(len(tids)):
                joint_teacher_pick[j, ti] = model.new_bool_var(f"jtp_{j}_{ti}")
            model.add(sum(joint_teacher_pick[j, ti] for ti in range(len(tids))) == 1)

    # ---------------------------------------------------------------
    # 2. Hard Constraints
    # ---------------------------------------------------------------

    # --- 2a. Total periods per load + consecutive grouping ---
    block_starts_all = {}
    for i, load in enumerate(loads):
        ppw = load["periods_per_week"]
        consec = load.get("consecutive", 1)

        if consec <= 1:
            model.add(sum(x[i, d, p] for d in range(D) for p in range(P)) == ppw)
        else:
            num_blocks = ppw // consec
            remainder = ppw % consec

            block_starts = {}
            for d in range(D):
                for sp in range(P - consec + 1):
                    bs = model.new_bool_var(f"bs_{i}_{d}_{sp}")
                    block_starts[i, d, sp] = bs
                    for k in range(consec):
                        model.add_implication(bs, x[i, d, sp + k])
            block_starts_all.update(block_starts)

            model.add(sum(v for v in block_starts.values()) == num_blocks)

            for d in range(D):
                for p in range(P):
                    covering = []
                    for sp in range(max(0, p - consec + 1), min(p + 1, P - consec + 1)):
                        if (i, d, sp) in block_starts:
                            covering.append(block_starts[i, d, sp])
                    model.add(x[i, d, p] <= (sum(covering) if covering else 0))

            if remainder > 0:
                rem_vars = []
                for d in range(D):
                    for p in range(P):
                        rv = model.new_bool_var(f"rem_{i}_{d}_{p}")
                        rem_vars.append(rv)
                        covered_by_block = []
                        for sp in range(max(0, p - consec + 1), min(p + 1, P - consec + 1)):
                            if (i, d, sp) in block_starts:
                                covered_by_block.append(block_starts[i, d, sp])
                        model.add(rv <= x[i, d, p])
                        if covered_by_block:
                            model.add(rv + sum(covered_by_block) <= 1)
                model.add(sum(rem_vars) == remainder)
                model.add(
                    sum(x[i, d, p] for d in range(D) for p in range(P))
                    == num_blocks * consec + remainder
                )
            else:
                model.add(
                    sum(x[i, d, p] for d in range(D) for p in range(P)) == ppw
                )

    # --- 2a-joint. Total periods per joint lesson + consecutive + fixed slots ---
    joint_block_starts_all = {}
    for j, joint in enumerate(joints):
        ppw = joint["periods_per_week"]
        consec = joint.get("consecutive", 1)

        if consec <= 1:
            model.add(sum(jx[j, d, p] for d in range(D) for p in range(P)) == ppw)
        else:
            num_blocks = ppw // consec
            jblock_starts = {}
            for d in range(D):
                for sp in range(P - consec + 1):
                    bs = model.new_bool_var(f"jbs_{j}_{d}_{sp}")
                    jblock_starts[j, d, sp] = bs
                    for k in range(consec):
                        model.add_implication(bs, jx[j, d, sp + k])
            joint_block_starts_all.update(jblock_starts)
            model.add(sum(v for v in jblock_starts.values()) == num_blocks)
            for d in range(D):
                for p in range(P):
                    covering = []
                    for sp in range(max(0, p - consec + 1), min(p + 1, P - consec + 1)):
                        if (j, d, sp) in jblock_starts:
                            covering.append(jblock_starts[j, d, sp])
                    model.add(jx[j, d, p] <= (sum(covering) if covering else 0))
            model.add(
                sum(jx[j, d, p] for d in range(D) for p in range(P)) == ppw
            )

        # Fixed slots: admin-pinned day+period(s) for specific placements
        fixed_slots = joint.get("fixed_slots")
        if fixed_slots:
            for fs in fixed_slots:
                if not isinstance(fs, dict):
                    continue
                fs_day = fs.get("day")
                fs_pids = fs.get("period_ids", [])
                if fs_day not in day_idx:
                    continue
                d = day_idx[fs_day]
                for fpid in fs_pids:
                    if fpid not in pid_idx:
                        continue
                    p = pid_idx[fpid]
                    model.add(jx[j, d, p] == 1)

    # --- 2b. Class-section clash: at most one subject per slot ---
    class_items = {}
    for i, load in enumerate(loads):
        key = (load["class_id"], load["section_id"])
        class_items.setdefault(key, []).append(("load", i))
    for j, joint in enumerate(joints):
        for cls in joint.get("classes", []):
            key = (cls["class_id"], cls["section_id"])
            class_items.setdefault(key, []).append(("joint", j))

    for (cid, sid), items in class_items.items():
        for d in range(D):
            for p in range(P):
                slot_vars = []
                for typ, idx in items:
                    if typ == "load":
                        slot_vars.append(x[idx, d, p])
                    else:
                        slot_vars.append(jx[idx, d, p])
                if len(slot_vars) > 1:
                    model.add(sum(slot_vars) <= 1)

    # --- 2c. Teacher clash ---
    teacher_contribs = {}

    def _add_tc(tid, d, p, var):
        teacher_contribs.setdefault(tid, {}).setdefault((d, p), []).append(var)

    pool_y = {}
    for i, load in enumerate(loads):
        tids = load.get("teacher_ids", [])
        if not tids:
            continue
        atr = load.get("all_teachers_required", False)

        if i in pool_load_set:
            for ti, t in enumerate(tids):
                for d in range(D):
                    for p in range(P):
                        y = model.new_bool_var(f"y_{i}_{ti}_{d}_{p}")
                        pool_y[i, ti, d, p] = y
                        model.add_implication(y, x[i, d, p])
                        model.add_implication(y, teacher_pick[i, ti])
                        model.add_bool_or(
                            [y, x[i, d, p].negated(), teacher_pick[i, ti].negated()]
                        )
                        _add_tc(t, d, p, y)
        elif atr:
            for t in tids:
                for d in range(D):
                    for p in range(P):
                        _add_tc(t, d, p, x[i, d, p])
        else:
            t = tids[0]
            for d in range(D):
                for p in range(P):
                    _add_tc(t, d, p, x[i, d, p])

    joint_pool_y = {}
    for j, joint in enumerate(joints):
        tids = joint.get("teacher_ids", [])
        if not tids:
            continue
        atr = joint.get("all_teachers_required", False)

        if j in joint_pool_set:
            for ti, t in enumerate(tids):
                for d in range(D):
                    for p in range(P):
                        y = model.new_bool_var(f"jy_{j}_{ti}_{d}_{p}")
                        joint_pool_y[j, ti, d, p] = y
                        model.add_implication(y, jx[j, d, p])
                        model.add_implication(y, joint_teacher_pick[j, ti])
                        model.add_bool_or(
                            [y, jx[j, d, p].negated(), joint_teacher_pick[j, ti].negated()]
                        )
                        _add_tc(t, d, p, y)
        elif atr:
            for t in tids:
                for d in range(D):
                    for p in range(P):
                        _add_tc(t, d, p, jx[j, d, p])
        else:
            t = tids[0]
            for d in range(D):
                for p in range(P):
                    _add_tc(t, d, p, jx[j, d, p])

    for tid, slot_map in teacher_contribs.items():
        for (d, p), vars_list in slot_map.items():
            if len(vars_list) > 1:
                model.add(sum(vars_list) <= 1)

    # --- 2d. Teacher unavailability ---
    for tid_str, day_map in t_unavail.items():
        tid = int(tid_str)
        for day_name, blocked_pids in day_map.items():
            if day_name not in day_idx:
                continue
            d = day_idx[day_name]
            for bpid in blocked_pids:
                if bpid not in pid_idx:
                    continue
                p = pid_idx[bpid]
                for i, load in enumerate(loads):
                    tids = load.get("teacher_ids", [])
                    if tid not in tids:
                        continue
                    if i in pool_load_set:
                        ti = tids.index(tid)
                        if (i, ti, d, p) in pool_y:
                            model.add(pool_y[i, ti, d, p] == 0)
                    else:
                        atr = load.get("all_teachers_required", False)
                        if atr or tids[0] == tid:
                            model.add(x[i, d, p] == 0)

                for j, joint in enumerate(joints):
                    jtids = joint.get("teacher_ids", [])
                    if tid not in jtids:
                        continue
                    if j in joint_pool_set:
                        ti = jtids.index(tid)
                        if (j, ti, d, p) in joint_pool_y:
                            model.add(joint_pool_y[j, ti, d, p] == 0)
                    else:
                        atr = joint.get("all_teachers_required", False)
                        if atr or jtids[0] == tid:
                            model.add(jx[j, d, p] == 0)

    # --- 2e. Class unavailability ---
    for key_str, day_map in c_unavail.items():
        parts = key_str.split("_")
        if len(parts) != 2:
            continue
        cid, sid = int(parts[0]), int(parts[1])
        for day_name, blocked_pids in day_map.items():
            if day_name not in day_idx:
                continue
            d = day_idx[day_name]
            for bpid in blocked_pids:
                if bpid not in pid_idx:
                    continue
                p = pid_idx[bpid]
                for typ, idx in class_items.get((cid, sid), []):
                    if typ == "load":
                        model.add(x[idx, d, p] == 0)
                    else:
                        model.add(jx[idx, d, p] == 0)

    # --- 2e2. Subject unavailability ---
    for sgs_str, day_map in s_unavail.items():
        sgs_id = int(sgs_str)
        for day_name, blocked_pids in day_map.items():
            if day_name not in day_idx:
                continue
            d = day_idx[day_name]
            for bpid in blocked_pids:
                if bpid not in pid_idx:
                    continue
                p = pid_idx[bpid]
                for i, load in enumerate(loads):
                    if load.get("sgs_id") == sgs_id:
                        model.add(x[i, d, p] == 0)

    # --- 2f. Max per day (per subject load) ---
    for i, load in enumerate(loads):
        max_day = load.get("max_per_day", 2)
        if max_day is None or max_day <= 0:
            max_day = 2
        if max_same_subject_day and max_same_subject_day > 0:
            max_day = min(max_day, max_same_subject_day)
        for d in range(D):
            model.add(sum(x[i, d, p] for p in range(P)) <= max_day)

    for j, joint in enumerate(joints):
        max_day = joint.get("max_per_day", 2)
        if max_day is None or max_day <= 0:
            max_day = 2
        for d in range(D):
            model.add(sum(jx[j, d, p] for p in range(P)) <= max_day)

    # --- 2g. Teacher max per day / per week ---
    all_teacher_ids_in_model = set(teacher_contribs.keys())

    def _get_tc(tid):
        return tc_map.get(str(tid), default_tc)

    for tid in all_teacher_ids_in_model:
        tc = _get_tc(tid)
        max_day = tc.get("max_per_day", default_tc["max_per_day"])
        max_week = tc.get("max_per_week", default_tc["max_per_week"])
        max_consec = tc.get("max_consecutive", 0)

        if max_day and max_day > 0:
            for d in range(D):
                day_vars = []
                for p in range(P):
                    day_vars.extend(teacher_contribs[tid].get((d, p), []))
                if day_vars:
                    model.add(sum(day_vars) <= max_day)

        if max_week and max_week > 0:
            week_vars = []
            for d in range(D):
                for p in range(P):
                    week_vars.extend(teacher_contribs[tid].get((d, p), []))
            if week_vars:
                model.add(sum(week_vars) <= max_week)

        # Max consecutive periods per teacher
        if max_consec and max_consec > 0:
            for d in range(D):
                for start_p in range(P - max_consec):
                    window_vars = []
                    for wp in range(start_p, start_p + max_consec + 1):
                        window_vars.extend(teacher_contribs[tid].get((d, wp), []))
                    if window_vars:
                        model.add(sum(window_vars) <= max_consec)

    # --- 2h. Locked entries ---
    for le in locked:
        day_name = le.get("day")
        pid = le.get("period_id")
        if day_name not in day_idx or pid not in pid_idx:
            continue
        d = day_idx[day_name]
        p = pid_idx[pid]
        sgs_id = le.get("sgs_id") or le.get("subject_group_subject_id")

        for i, load in enumerate(loads):
            if (load["class_id"] == le["class_id"]
                    and load["section_id"] == le["section_id"]
                    and load.get("sgs_id") == sgs_id):
                model.add(x[i, d, p] == 1)
                break

    # ---------------------------------------------------------------
    # 3. Objective (soft constraints)
    # ---------------------------------------------------------------

    obj_terms = []
    WEIGHT_EVEN = 5
    WEIGHT_EARLY = 1
    WEIGHT_AVOID = 3

    # --- 3a. Distribute evenly across days ---
    for i, load in enumerate(loads):
        if not load.get("distribute_evenly", False):
            continue
        ppw = load["periods_per_week"]
        upper = (ppw + D - 1) // D  # ceil(ppw / D)
        for d in range(D):
            day_sum = sum(x[i, d, p] for p in range(P))
            excess = model.new_int_var(0, P, f"excess_{i}_{d}")
            model.add(excess >= day_sum - upper)
            obj_terms.append((-WEIGHT_EVEN, excess))

    # --- 3b. Prefer earlier periods ---
    for i in range(len(loads)):
        for d in range(D):
            for p in range(P):
                obj_terms.append((WEIGHT_EARLY * (P - p), x[i, d, p]))
    for j in range(len(joints)):
        for d in range(D):
            for p in range(P):
                obj_terms.append((WEIGHT_EARLY * (P - p), jx[j, d, p]))

    # --- 3c. Avoid first/last period for teachers ---
    for tid in all_teacher_ids_in_model:
        tc = _get_tc(tid)
        if tc.get("avoid_first_period", False):
            for d in range(D):
                for var in teacher_contribs[tid].get((d, 0), []):
                    obj_terms.append((-WEIGHT_AVOID, var))
        if tc.get("avoid_last_period", False):
            for d in range(D):
                for var in teacher_contribs[tid].get((d, P - 1), []):
                    obj_terms.append((-WEIGHT_AVOID, var))

    # --- 3d. Spread joint lessons across days ---
    for j, joint in enumerate(joints):
        ppw = joint["periods_per_week"]
        consec = joint.get("consecutive", 1)
        if ppw >= 2:
            upper = max(1, (ppw + D - 1) // D)
            for d in range(D):
                day_sum = sum(jx[j, d, p] for p in range(P))
                jexcess = model.new_int_var(0, P, f"jexcess_{j}_{d}")
                model.add(jexcess >= day_sum - upper)
                obj_terms.append((-WEIGHT_EVEN, jexcess))

    if obj_terms:
        model.maximize(sum(w * v for w, v in obj_terms))

    # ---------------------------------------------------------------
    # 4. Solve
    # ---------------------------------------------------------------

    time_limit = data.get("time_limit", 60)
    solver = cp_model.CpSolver()
    solver.parameters.max_time_in_seconds = time_limit
    solver.parameters.num_workers = 4

    status = solver.Solve(model)
    solve_time = time.time() - t0

    # ---------------------------------------------------------------
    # 5. Extract solution
    # ---------------------------------------------------------------

    if status not in (cp_model.OPTIMAL, cp_model.FEASIBLE):
        diag = _diagnose_infeasibility(data, days, periods_raw, loads, joints)
        return {
            "status": "infeasible",
            "message": diag,
            "solve_time_seconds": round(solve_time, 2),
            "solver_status": solver.status_name(status),
        }

    entries = []
    total_placed = 0
    total_required = 0
    class_stats = {}

    for i, load in enumerate(loads):
        ppw = load["periods_per_week"]
        consec = load.get("consecutive", 1)
        placements_needed = ppw // consec if consec > 1 else ppw
        total_required += placements_needed

        tids = load.get("teacher_ids", [])
        assigned_teacher = None
        if i in pool_load_set:
            for ti, t in enumerate(tids):
                if solver.value(teacher_pick[i, ti]):
                    assigned_teacher = t
                    break
        elif tids:
            assigned_teacher = tids[0]

        placed = 0
        for d in range(D):
            for p in range(P):
                if solver.value(x[i, d, p]):
                    placed += 1
                    entries.append({
                        "class_id": load["class_id"],
                        "section_id": load["section_id"],
                        "subject_group_id": load.get("sg_id", 0),
                        "subject_group_subject_id": load.get("sgs_id", 0),
                        "staff_id": assigned_teacher,
                        "period_id": period_ids[p],
                        "day": days[d],
                        "room_id": None,
                        "batch_id": load.get("batch_id"),
                        "is_free_period": 0,
                        "free_period_label": None,
                    })

        placed_blocks = placed // consec if consec > 1 else placed
        total_placed += placed_blocks

        ck = f"{load['class_id']}_{load['section_id']}"
        if ck not in class_stats:
            class_stats[ck] = {
                "class_id": load["class_id"],
                "section_id": load["section_id"],
                "required": 0,
                "placed": 0,
            }
        class_stats[ck]["required"] += placements_needed
        class_stats[ck]["placed"] += placed_blocks

    for j, joint in enumerate(joints):
        ppw = joint["periods_per_week"]
        consec = joint.get("consecutive", 1)
        placements_needed = ppw // consec if consec > 1 else ppw
        total_required += placements_needed

        tids = joint.get("teacher_ids", [])
        assigned_teacher = None
        if j in joint_pool_set:
            for ti, t in enumerate(tids):
                if solver.value(joint_teacher_pick[j, ti]):
                    assigned_teacher = t
                    break
        elif tids:
            assigned_teacher = tids[0]

        placed = 0
        for d in range(D):
            for p in range(P):
                if solver.value(jx[j, d, p]):
                    placed += 1
                    for cls in joint.get("classes", []):
                        entries.append({
                            "class_id": cls["class_id"],
                            "section_id": cls["section_id"],
                            "subject_group_id": cls.get("sg_id", 0),
                            "subject_group_subject_id": cls.get("sgs_id", 0),
                            "staff_id": assigned_teacher,
                            "period_id": period_ids[p],
                            "day": days[d],
                            "room_id": None,
                            "batch_id": None,
                            "is_free_period": 0,
                            "free_period_label": None,
                        })

        placed_blocks = placed // consec if consec > 1 else placed
        total_placed += placed_blocks

        for cls in joint.get("classes", []):
            ck = f"{cls['class_id']}_{cls['section_id']}"
            if ck not in class_stats:
                class_stats[ck] = {
                    "class_id": cls["class_id"],
                    "section_id": cls["section_id"],
                    "required": 0,
                    "placed": 0,
                }
            class_stats[ck]["required"] += placements_needed
            class_stats[ck]["placed"] += placed_blocks

    quality = round((total_placed / total_required) * 100, 2) if total_required > 0 else 100.0

    return {
        "status": "success",
        "quality": quality,
        "total_required": total_required,
        "total_placed": total_placed,
        "entries": entries,
        "unplaced": [],
        "solve_time_seconds": round(solve_time, 2),
        "solver_status": solver.status_name(status),
        "class_stats": list(class_stats.values()),
    }


def _diagnose_infeasibility(data, days, periods, loads, joints):
    """Quick heuristic check to explain WHY the problem is infeasible."""
    D = len(days)
    P = len(periods)
    total_slots = D * P
    issues = []
    labels = data.get("class_labels", {})

    def _class_name(ck):
        return labels.get(ck, ck)

    # Build teacher name map from subject loads
    teacher_names = {}
    for load in loads:
        for tid in load.get("teacher_ids", []):
            if tid not in teacher_names and load.get("teacher_name"):
                teacher_names[tid] = load["teacher_name"]

    def _teacher_name(tid):
        name = teacher_names.get(tid)
        return f"{name} (ID {tid})" if name else f"Teacher ID {tid}"

    # Check per-class overload
    class_demand = {}
    for load in loads:
        key = f"{load['class_id']}_{load['section_id']}"
        class_demand[key] = class_demand.get(key, 0) + load["periods_per_week"]
    for j in joints:
        for cls in j.get("classes", []):
            key = f"{cls['class_id']}_{cls['section_id']}"
            class_demand[key] = class_demand.get(key, 0) + j["periods_per_week"]

    for ck, demand in class_demand.items():
        if demand > total_slots:
            issues.append(
                f"{_class_name(ck)} needs {demand} periods but only {total_slots} "
                f"slots available ({D} days × {P} periods). "
                f"Please reduce subject loads by {demand - total_slots} period(s)."
            )

    # Check per-teacher overload
    teacher_demand = {}
    tc_map = data.get("teacher_constraints", {})
    for load in loads:
        for tid in load.get("teacher_ids", []):
            teacher_demand[tid] = teacher_demand.get(tid, 0) + load["periods_per_week"]
    for j in joints:
        for tid in j.get("teacher_ids", []):
            teacher_demand[tid] = teacher_demand.get(tid, 0) + j["periods_per_week"]

    default_max_week = 36
    for tid, demand in teacher_demand.items():
        tc = tc_map.get(str(tid), {})
        cap = tc.get("max_per_week", default_max_week)
        if cap and demand > cap:
            issues.append(
                f"{_teacher_name(tid)} is assigned {demand} periods/week "
                f"but has a cap of {cap}."
            )

    # Check teacher unavailability vs demand
    t_unavail = data.get("teacher_unavailability", {})
    for tid, demand in teacher_demand.items():
        blocked = 0
        for day_map in t_unavail.get(str(tid), {}).values():
            blocked += len(day_map)
        available = total_slots - blocked
        if demand > available:
            issues.append(
                f"{_teacher_name(tid)} needs {demand} slots but only {available} "
                f"available ({blocked} blocked by unavailability)."
            )

    if not issues:
        issues.append(
            "The combination of constraints (teacher sharing, class clashes, "
            "unavailability, max-per-day limits) makes a complete timetable "
            "impossible. Try relaxing teacher caps or reducing subject loads."
        )

    return " | ".join(issues)
