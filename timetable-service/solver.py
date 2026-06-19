"""
OR-Tools CP-SAT timetable solver.

Guaranteed-optimal timetable assignment using constraint programming.
Handles: class/teacher clash, unavailability, consecutive periods,
joint lessons (with fixed slots), teacher capacity, distribution.
"""

from ortools.sat.python import cp_model
import time
import logging

log = logging.getLogger("timetable-service")


def solve(data: dict) -> dict:
    t0 = time.time()
    timings = {}

    def _tick(label):
        timings[label] = time.time()

    _tick("start")
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
    # 0. Pre-compute blocked slots (avoid creating useless variables)
    # ---------------------------------------------------------------
    _tick("precompute")

    # Teacher → set of (day_idx, period_idx) that are blocked
    teacher_blocked = {}
    for tid_str, day_map in t_unavail.items():
        tid = int(tid_str)
        blocked = set()
        for day_name, blocked_pids in day_map.items():
            if day_name not in day_idx:
                continue
            d = day_idx[day_name]
            for bpid in blocked_pids:
                if bpid in pid_idx:
                    blocked.add((d, pid_idx[bpid]))
        if blocked:
            teacher_blocked[tid] = blocked

    # Class → set of (day_idx, period_idx) that are blocked
    class_blocked = {}
    for key_str, day_map in c_unavail.items():
        parts = key_str.split("_")
        if len(parts) != 2:
            continue
        ck = (int(parts[0]), int(parts[1]))
        blocked = set()
        for day_name, blocked_pids in day_map.items():
            if day_name not in day_idx:
                continue
            d = day_idx[day_name]
            for bpid in blocked_pids:
                if bpid in pid_idx:
                    blocked.add((d, pid_idx[bpid]))
        if blocked:
            class_blocked[ck] = blocked

    # Subject → set of blocked slots
    subject_blocked = {}
    for sgs_str, day_map in s_unavail.items():
        sgs_id = int(sgs_str)
        blocked = set()
        for day_name, blocked_pids in day_map.items():
            if day_name not in day_idx:
                continue
            d = day_idx[day_name]
            for bpid in blocked_pids:
                if bpid in pid_idx:
                    blocked.add((d, pid_idx[bpid]))
        if blocked:
            subject_blocked[sgs_id] = blocked

    # Build teacher→load index for fast lookup
    teacher_to_loads = {}  # tid -> list of load indices
    for i, load in enumerate(loads):
        for tid in load.get("teacher_ids", []):
            teacher_to_loads.setdefault(tid, []).append(i)

    teacher_to_joints = {}  # tid -> list of joint indices
    for j, joint in enumerate(joints):
        for tid in joint.get("teacher_ids", []):
            teacher_to_joints.setdefault(tid, []).append(j)

    # ---------------------------------------------------------------
    # 1. Variables (skip blocked slots)
    # ---------------------------------------------------------------
    _tick("variables")

    x = {}
    x_blocked = set()  # (i, d, p) slots forced to 0
    for i, load in enumerate(loads):
        ck = (load["class_id"], load["section_id"])
        c_blk = class_blocked.get(ck, set())
        s_blk = subject_blocked.get(load.get("sgs_id", 0), set())
        tids = load.get("teacher_ids", [])
        atr = load.get("all_teachers_required", False)
        # A slot is blocked if class OR subject is unavailable
        # For single-teacher non-pool: also blocked if teacher unavailable
        load_blocked = c_blk | s_blk
        if len(tids) == 1 or (len(tids) > 1 and atr):
            for tid in tids:
                load_blocked = load_blocked | teacher_blocked.get(tid, set())

        for d in range(D):
            for p in range(P):
                if (d, p) in load_blocked:
                    x_blocked.add((i, d, p))
                else:
                    x[i, d, p] = model.new_bool_var(f"x_{i}_{d}_{p}")

    jx = {}
    jx_blocked = set()
    for j, joint in enumerate(joints):
        j_blocked = set()
        for cls in joint.get("classes", []):
            j_blocked |= class_blocked.get((cls["class_id"], cls["section_id"]), set())
        tids = joint.get("teacher_ids", [])
        atr = joint.get("all_teachers_required", False)
        if len(tids) == 1 or (len(tids) > 1 and atr):
            for tid in tids:
                j_blocked |= teacher_blocked.get(tid, set())
        for d in range(D):
            for p in range(P):
                if (d, p) in j_blocked:
                    jx_blocked.add((j, d, p))
                else:
                    jx[j, d, p] = model.new_bool_var(f"jx_{j}_{d}_{p}")

    ZERO = model.new_constant(0)
    def _x(i, d, p):
        return x.get((i, d, p), ZERO)
    def _jx(j, d, p):
        return jx.get((j, d, p), ZERO)

    log.info("Variables: %d x, %d jx (skipped %d+%d blocked slots)",
             len(x), len(jx), len(x_blocked), len(jx_blocked))
    _tick("teacher_pick")

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
    _tick("constraints")

    # --- 2a. Total periods per load + consecutive grouping ---
    # Use <= (not ==) so the solver can find partial solutions when 100% is
    # impossible.  The objective heavily rewards full placement so the solver
    # will always reach 100% when feasible.
    block_starts_all = {}
    placement_vars = []  # (weight, var) to add to objective
    PLACE_WEIGHT = 10000

    for i, load in enumerate(loads):
        ppw = load["periods_per_week"]
        consec = load.get("consecutive", 1)
        total_i = sum(_x(i, d, p) for d in range(D) for p in range(P))
        placement_vars.append((PLACE_WEIGHT, total_i))

        if consec <= 1:
            model.add(total_i <= ppw)
        else:
            num_blocks = ppw // consec
            remainder = ppw % consec

            block_starts = {}
            for d in range(D):
                for sp in range(P - consec + 1):
                    bs = model.new_bool_var(f"bs_{i}_{d}_{sp}")
                    block_starts[i, d, sp] = bs
                    for k in range(consec):
                        model.add_implication(bs, _x(i, d, sp + k))
            block_starts_all.update(block_starts)

            model.add(sum(v for v in block_starts.values()) <= num_blocks)

            for d in range(D):
                for p in range(P):
                    covering = []
                    for sp in range(max(0, p - consec + 1), min(p + 1, P - consec + 1)):
                        if (i, d, sp) in block_starts:
                            covering.append(block_starts[i, d, sp])
                    model.add(_x(i, d, p) <= (sum(covering) if covering else 0))

            model.add(total_i <= ppw)

    # --- 2a-joint. Total periods per joint lesson + consecutive + fixed slots ---
    joint_block_starts_all = {}
    for j, joint in enumerate(joints):
        ppw = joint["periods_per_week"]
        consec = joint.get("consecutive", 1)
        total_j = sum(_jx(j, d, p) for d in range(D) for p in range(P))
        placement_vars.append((PLACE_WEIGHT, total_j))

        if consec <= 1:
            model.add(total_j <= ppw)
        else:
            num_blocks = ppw // consec
            jblock_starts = {}
            for d in range(D):
                for sp in range(P - consec + 1):
                    bs = model.new_bool_var(f"jbs_{j}_{d}_{sp}")
                    jblock_starts[j, d, sp] = bs
                    for k in range(consec):
                        model.add_implication(bs, _jx(j, d, sp + k))
            joint_block_starts_all.update(jblock_starts)
            model.add(sum(v for v in jblock_starts.values()) <= num_blocks)
            for d in range(D):
                for p in range(P):
                    covering = []
                    for sp in range(max(0, p - consec + 1), min(p + 1, P - consec + 1)):
                        if (j, d, sp) in jblock_starts:
                            covering.append(jblock_starts[j, d, sp])
                    model.add(_jx(j, d, p) <= (sum(covering) if covering else 0))
            model.add(total_j <= ppw)

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
                    model.add(_jx(j, d, p) == 1)

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
                        slot_vars.append(_x(idx, d, p))
                    else:
                        slot_vars.append(_jx(idx, d, p))
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
                        model.add_implication(y, _x(i, d, p))
                        model.add_implication(y, teacher_pick[i, ti])
                        model.add_bool_or(
                            [y, _x(i, d, p).negated(), teacher_pick[i, ti].negated()]
                        )
                        _add_tc(t, d, p, y)
        elif atr:
            for t in tids:
                for d in range(D):
                    for p in range(P):
                        _add_tc(t, d, p, _x(i, d, p))
        else:
            t = tids[0]
            for d in range(D):
                for p in range(P):
                    _add_tc(t, d, p, _x(i, d, p))

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
                        model.add_implication(y, _jx(j, d, p))
                        model.add_implication(y, joint_teacher_pick[j, ti])
                        model.add_bool_or(
                            [y, _jx(j, d, p).negated(), joint_teacher_pick[j, ti].negated()]
                        )
                        _add_tc(t, d, p, y)
        elif atr:
            for t in tids:
                for d in range(D):
                    for p in range(P):
                        _add_tc(t, d, p, _jx(j, d, p))
        else:
            t = tids[0]
            for d in range(D):
                for p in range(P):
                    _add_tc(t, d, p, _jx(j, d, p))

    for tid, slot_map in teacher_contribs.items():
        for (d, p), vars_list in slot_map.items():
            if len(vars_list) > 1:
                model.add(sum(vars_list) <= 1)

    # --- 2d. Teacher unavailability (pool loads only) ---
    # Non-pool loads already have blocked slots pre-filtered (no variable created).
    # Pool loads need per-teacher-option blocking via pool_y.
    _tick("unavailability")
    for tid, blocked_slots in teacher_blocked.items():
        for i in teacher_to_loads.get(tid, []):
            if i not in pool_load_set:
                continue
            tids = loads[i].get("teacher_ids", [])
            ti = tids.index(tid)
            for d, p in blocked_slots:
                if (i, ti, d, p) in pool_y:
                    model.add(pool_y[i, ti, d, p] == 0)
        for j in teacher_to_joints.get(tid, []):
            if j not in joint_pool_set:
                continue
            jtids = joints[j].get("teacher_ids", [])
            ti = jtids.index(tid)
            for d, p in blocked_slots:
                if (j, ti, d, p) in joint_pool_y:
                    model.add(joint_pool_y[j, ti, d, p] == 0)

    # Sections 2e (class unavailability) and 2e2 (subject unavailability)
    # are handled by pre-filtering: blocked slots have no variables created.

    # --- 2f. Max per day (per subject load) ---
    # Per-subject max_per_day takes priority. The global max_same_subject_day
    # only applies as a fallback when the subject doesn't set its own limit.
    for i, load in enumerate(loads):
        max_day = load.get("max_per_day", 0)
        if max_day and max_day > 0:
            pass  # use the subject's own setting
        elif max_same_subject_day and max_same_subject_day > 0:
            max_day = max_same_subject_day
        else:
            max_day = 2
        for d in range(D):
            model.add(sum(_x(i, d, p) for p in range(P)) <= max_day)

    for j, joint in enumerate(joints):
        max_day = joint.get("max_per_day", 2)
        if max_day is None or max_day <= 0:
            max_day = 2
        for d in range(D):
            model.add(sum(_jx(j, d, p) for p in range(P)) <= max_day)

    # --- 2g. Teacher max per day / per week ---
    all_teacher_ids_in_model = set(teacher_contribs.keys())

    def _get_tc(tid):
        return tc_map.get(str(tid), default_tc)

    # max_per_day is SOFT: allow +1 overflow with a heavy penalty.
    # This matches the old PHP engine which penalised but allowed exceeding
    # the daily limit when needed to place the last few subjects.
    OVERFLOW_PENALTY = 2000
    day_overflow_vars = []

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
                    # Hard ceiling: max_day + 1 (never exceed by more than 1)
                    model.add(sum(day_vars) <= max_day + 1)
                    # Soft penalty for the +1 overflow
                    overflow = model.new_bool_var(f"tdof_{tid}_{d}")
                    model.add(sum(day_vars) <= max_day).only_enforce_if(overflow.negated())
                    day_overflow_vars.append(overflow)

        if max_week and max_week > 0:
            week_vars = []
            for d in range(D):
                for p in range(P):
                    week_vars.extend(teacher_contribs[tid].get((d, p), []))
            if week_vars:
                model.add(sum(week_vars) <= max_week)

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
                model.add(_x(i, d, p) == 1)
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
            day_sum = sum(_x(i, d, p) for p in range(P))
            excess = model.new_int_var(0, P, f"excess_{i}_{d}")
            model.add(excess >= day_sum - upper)
            obj_terms.append((-WEIGHT_EVEN, excess))

    # --- 3b. Prefer earlier periods ---
    for i in range(len(loads)):
        for d in range(D):
            for p in range(P):
                obj_terms.append((WEIGHT_EARLY * (P - p), _x(i, d, p)))
    for j in range(len(joints)):
        for d in range(D):
            for p in range(P):
                obj_terms.append((WEIGHT_EARLY * (P - p), _jx(j, d, p)))

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
                day_sum = sum(_jx(j, d, p) for p in range(P))
                jexcess = model.new_int_var(0, P, f"jexcess_{j}_{d}")
                model.add(jexcess >= day_sum - upper)
                obj_terms.append((-WEIGHT_EVEN, jexcess))

    # Penalize teacher daily overflows (soft max_per_day)
    for ov in day_overflow_vars:
        obj_terms.append((-OVERFLOW_PENALTY, ov))

    all_obj = placement_vars + obj_terms
    model.maximize(sum(w * v for w, v in all_obj))

    # ---------------------------------------------------------------
    # 4. Solve
    # ---------------------------------------------------------------
    _tick("model_built")

    build_time = timings["model_built"] - timings["start"]
    log.info("Model built in %.1fs: %d vars (%d x + %d jx + %d pool_y), "
             "%d constraints, %d obj terms",
             build_time, len(x) + len(jx) + len(pool_y) + len(joint_pool_y),
             len(x), len(jx), len(pool_y) + len(joint_pool_y),
             model.proto.constraints.__len__() if hasattr(model.proto, 'constraints') else 0,
             len(all_obj))

    time_limit = data.get("time_limit", 120)
    solver = cp_model.CpSolver()
    solver.parameters.max_time_in_seconds = time_limit
    solver.parameters.num_workers = 4

    _tick("solve_start")
    status = solver.Solve(model)
    _tick("solve_end")
    solve_time = time.time() - t0

    log.info("Solve: %.1fs (build=%.1fs, solve=%.1fs), status=%s",
             solve_time,
             timings["model_built"] - timings["start"],
             timings["solve_end"] - timings["solve_start"],
             solver.status_name(status))

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
    unplaced = []
    labels = data.get("class_labels", {})

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
                if solver.value(_x(i, d, p)):
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

        if placed_blocks < placements_needed:
            ck = f"{load['class_id']}_{load['section_id']}"
            unplaced.append({
                "type": "no_slot",
                "class_id": load["class_id"],
                "section_id": load["section_id"],
                "subject": load.get("subject_name", "?"),
                "staff": load.get("teacher_name", ""),
                "staff_id": assigned_teacher,
                "reason": (
                    f"Could not place {placements_needed - placed_blocks} of "
                    f"{placements_needed} for {load.get('subject_name', '?')} "
                    f"in {labels.get(ck, ck)} — teacher or slot conflict."
                ),
            })

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
                if solver.value(_jx(j, d, p)):
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

        if placed_blocks < placements_needed:
            class_names = ", ".join(
                labels.get(f"{c['class_id']}_{c['section_id']}",
                           f"{c['class_id']}_{c['section_id']}")
                for c in joint.get("classes", [])
            )
            unplaced.append({
                "type": "no_slot",
                "class_id": 0,
                "section_id": 0,
                "subject": f"{joint.get('name', '?')} (Joint)",
                "staff": "",
                "staff_id": assigned_teacher,
                "reason": (
                    f"Could not place {placements_needed - placed_blocks} of "
                    f"{placements_needed} for joint {joint.get('name', '?')} "
                    f"across {class_names}."
                ),
            })

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

    detected_issues = _analyze_issues(data, days, periods_raw, loads, joints)

    return {
        "status": "success",
        "quality": quality,
        "total_required": total_required,
        "total_placed": total_placed,
        "entries": entries,
        "unplaced": unplaced,
        "issues": detected_issues,
        "solve_time_seconds": round(solve_time, 2),
        "solver_status": solver.status_name(status),
        "class_stats": list(class_stats.values()),
    }


def _analyze_issues(data, days, periods, loads, joints):
    """Analyze scheduling data and return structured issues with fix instructions."""
    D = len(days)
    P = len(periods)
    total_slots = D * P
    issues = []
    labels = data.get("class_labels", {})
    settings = data.get("settings", {})
    max_same_subject_day = settings.get("max_same_subject_day", 2)

    def _cn(ck):
        return labels.get(ck, ck)

    teacher_names = {}
    for load in loads:
        for tid in load.get("teacher_ids", []):
            if tid not in teacher_names and load.get("teacher_name"):
                teacher_names[tid] = load["teacher_name"]
    for j in joints:
        j_tids = j.get("teacher_ids", [])
        j_tnames = j.get("teacher_names", [])
        for ti, tid in enumerate(j_tids):
            if tid not in teacher_names and ti < len(j_tnames):
                teacher_names[tid] = j_tnames[ti]

    def _tn(tid):
        return teacher_names.get(tid, f"Staff #{tid}")

    tc_map = data.get("teacher_constraints", {})
    t_unavail = data.get("teacher_unavailability", {})
    default_max_week = 36
    default_max_day = 6

    # ── Collect teacher workload ──
    teacher_loads = {}   # tid -> list of {label, ppw, subject, class_key}
    teacher_ppw = {}     # tid -> total ppw
    for load in loads:
        for tid in load.get("teacher_ids", []):
            ck = f"{load['class_id']}_{load['section_id']}"
            teacher_loads.setdefault(tid, []).append({
                "label": f"{_cn(ck)} — {load.get('subject_name', '?')} ({load['periods_per_week']}ppw)",
                "ppw": load["periods_per_week"],
                "subject": load.get("subject_name", "?"),
                "class_key": ck,
            })
            teacher_ppw[tid] = teacher_ppw.get(tid, 0) + load["periods_per_week"]
    for j in joints:
        for tid in j.get("teacher_ids", []):
            classes = ", ".join(_cn(f"{c['class_id']}_{c['section_id']}") for c in j.get("classes", []))
            teacher_loads.setdefault(tid, []).append({
                "label": f"Joint: {j.get('name', '?')} [{classes}] ({j['periods_per_week']}ppw)",
                "ppw": j["periods_per_week"],
                "subject": j.get("name", "?"),
                "class_key": "joint",
            })
            teacher_ppw[tid] = teacher_ppw.get(tid, 0) + j["periods_per_week"]

    # ── 1. Class overflow ──
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
            over = demand - total_slots
            issues.append({
                "type": "class_overflow",
                "severity": "error",
                "title": f"{_cn(ck)}: Too many periods assigned",
                "detail": (
                    f"This class needs {demand} periods per week, but only "
                    f"{total_slots} time slots exist ({D} days × {P} periods per day)."
                ),
                "fix": (
                    f"Go to Auto Timetable → Subject Load for {_cn(ck)}. "
                    f"Remove or reduce subject periods to free up at least {over} period(s). "
                    f"For example, reduce a subject from 6 periods/week to {6 - over} periods/week."
                ),
            })

    # ── 2. Subject max-per-day impossible ──
    for load in loads:
        ppw = load["periods_per_week"]
        subj_max_day = load.get("max_per_day", 0)
        if subj_max_day and subj_max_day > 0:
            effective_max = subj_max_day
        elif max_same_subject_day and max_same_subject_day > 0:
            effective_max = max_same_subject_day
        else:
            effective_max = 2
        if ppw > effective_max * D:
            ck = f"{load['class_id']}_{load['section_id']}"
            needed_max = -(-ppw // D)
            issues.append({
                "type": "subject_max_per_day",
                "severity": "error",
                "title": f"{_cn(ck)} — {load.get('subject_name', '?')}: Cannot fit in the week",
                "detail": (
                    f"This subject needs {ppw} periods per week, but it's limited to "
                    f"{effective_max} period(s) per day. With {D} working days, "
                    f"the maximum possible is {effective_max} × {D} = {effective_max * D} periods."
                ),
                "fix": (
                    f"Option A: Go to Auto Timetable → Subject Load → {_cn(ck)} → "
                    f"find \"{load.get('subject_name', '?')}\" → change 'Max Per Day' to {needed_max}.\n"
                    f"Option B: Reduce this subject's periods per week to {effective_max * D} or less.\n"
                    f"Option C: If using generation setting 'Max Same Subject Per Day = {max_same_subject_day}', "
                    f"try increasing it to {needed_max}."
                ),
            })

    for j in joints:
        ppw = j["periods_per_week"]
        max_day = j.get("max_per_day", 2)
        if max_day and max_day > 0 and ppw > max_day * D:
            classes = ", ".join(_cn(f"{c['class_id']}_{c['section_id']}") for c in j.get("classes", []))
            needed_max = -(-ppw // D)
            issues.append({
                "type": "subject_max_per_day",
                "severity": "error",
                "title": f"Joint Lesson \"{j.get('name', '?')}\": Cannot fit in the week",
                "detail": (
                    f"This joint lesson ({classes}) needs {ppw} periods per week, "
                    f"but is limited to {max_day} per day. Maximum possible = {max_day * D}."
                ),
                "fix": (
                    f"Go to Auto Timetable → Joint Lessons → \"{j.get('name', '?')}\" → "
                    f"change 'Max Per Day' to {needed_max}, or reduce periods per week to {max_day * D}."
                ),
            })

    # ── 3. Teacher weekly overload ──
    for tid, total in teacher_ppw.items():
        tc = tc_map.get(str(tid), {})
        cap = tc.get("max_per_week", default_max_week)
        if cap and total > cap:
            over = total - cap
            assigns = "\n".join(f"  • {e['label']}" for e in teacher_loads[tid])
            issues.append({
                "type": "teacher_weekly_overload",
                "severity": "error",
                "title": f"{_tn(tid)}: Assigned more periods than allowed per week",
                "detail": (
                    f"{_tn(tid)} is assigned {total} periods per week, but their maximum "
                    f"limit is {cap} periods per week. They are overloaded by {over} period(s).\n"
                    f"Current assignments:\n{assigns}"
                ),
                "fix": (
                    f"Option A: Go to Auto Timetable → Teacher Constraints → "
                    f"find \"{_tn(tid)}\" → increase 'Max Periods Per Week' to at least {total}.\n"
                    f"Option B: Go to Subject Load and remove {over} period(s) from "
                    f"this teacher's assignments (reassign some subjects to another teacher)."
                ),
            })

    # ── 4. Teacher daily overload ──
    for tid, total in teacher_ppw.items():
        if tid in [i.get("_tid") for i in issues if i.get("type") == "teacher_weekly_overload"]:
            continue
        tc = tc_map.get(str(tid), {})
        t_max_day = tc.get("max_per_day", default_max_day)
        if not t_max_day or t_max_day <= 0:
            continue

        blocked_days = 0
        for day_name in days:
            blocked_pids = t_unavail.get(str(tid), {}).get(day_name, [])
            if len(blocked_pids) >= P:
                blocked_days += 1
        available_days = D - blocked_days
        max_possible = t_max_day * available_days

        if total > max_possible:
            assigns = "\n".join(f"  • {e['label']}" for e in teacher_loads[tid])
            needed_max_day = -(-total // available_days) if available_days > 0 else total
            issues.append({
                "type": "teacher_daily_overload",
                "severity": "error",
                "title": f"{_tn(tid)}: Cannot fit all periods within daily limit",
                "detail": (
                    f"{_tn(tid)} needs to teach {total} periods per week, but is limited to "
                    f"{t_max_day} periods per day. With {available_days} available working days, "
                    f"the maximum they can teach is {t_max_day} × {available_days} = {max_possible} periods.\n"
                    f"Current assignments:\n{assigns}"
                ),
                "fix": (
                    f"Option A: Go to Auto Timetable → Teacher Constraints → "
                    f"find \"{_tn(tid)}\" → increase 'Max Periods Per Day' to {needed_max_day}.\n"
                    f"Option B: Reduce this teacher's total load by reassigning "
                    f"{total - max_possible} period(s) to another teacher."
                ),
            })

    # ── 5. Teacher unavailability squeeze ──
    tids_with_issues = {i.get("_tid") for i in issues}
    for tid, total in teacher_ppw.items():
        blocked = 0
        for day_map in t_unavail.get(str(tid), {}).values():
            blocked += len(day_map)
        available = total_slots - blocked
        if total > available and tid not in tids_with_issues:
            assigns = "\n".join(f"  • {e['label']}" for e in teacher_loads[tid])
            issues.append({
                "type": "teacher_unavailable",
                "severity": "error",
                "title": f"{_tn(tid)}: Too many unavailable slots",
                "detail": (
                    f"{_tn(tid)} needs {total} free slots to teach, but {blocked} slots "
                    f"are marked as unavailable, leaving only {available} available."
                ),
                "fix": (
                    f"Option A: Go to Auto Timetable → Teacher Unavailability → "
                    f"\"{_tn(tid)}\" → remove some blocked time slots.\n"
                    f"Option B: Reduce this teacher's workload by reassigning "
                    f"{total - available} period(s) to another teacher."
                ),
            })

    # ── 6. Joint lesson bottlenecks ──
    # A joint lesson needs a time slot where ALL participating classes AND all
    # teachers are free simultaneously. When teachers are near capacity and
    # the joint spans many classes, common free slots become very scarce.
    class_demand = {}
    for load in loads:
        key = f"{load['class_id']}_{load['section_id']}"
        class_demand[key] = class_demand.get(key, 0) + load["periods_per_week"]
    for j in joints:
        for cls in j.get("classes", []):
            key = f"{cls['class_id']}_{cls['section_id']}"
            class_demand[key] = class_demand.get(key, 0) + j["periods_per_week"]

    c_unavail = data.get("class_unavailability", {})
    for j in joints:
        tids = j.get("teacher_ids", [])
        classes = j.get("classes", [])
        n_classes = len(classes)
        ppw = j["periods_per_week"]
        j_name = j.get("name", "?")

        if n_classes < 3 and not tids:
            continue

        # Estimate available common slots: for each (day, period), the slot is
        # free only if EVERY class AND EVERY teacher can use it.
        free_slots = 0
        for di, day_name in enumerate(days):
            for pi in range(P):
                pid = periods[pi]["id"]
                slot_ok = True
                # Check all classes
                for cls in classes:
                    ck = f"{cls['class_id']}_{cls['section_id']}"
                    if pid in c_unavail.get(ck, {}).get(day_name, []):
                        slot_ok = False
                        break
                if not slot_ok:
                    continue
                # Check all teachers
                for tid in tids:
                    if pid in t_unavail.get(str(tid), {}).get(day_name, []):
                        slot_ok = False
                        break
                if slot_ok:
                    free_slots += 1

        # How many of those free slots will actually be available after other
        # subjects fill in?  Estimate: each class uses ~(demand/total_slots)
        # fraction of its slots for other subjects.
        if free_slots > 0 and n_classes >= 3:
            worst_class_demand = 0
            worst_class_name = ""
            for cls in classes:
                ck = f"{cls['class_id']}_{cls['section_id']}"
                d = class_demand.get(ck, 0) - ppw
                if d > worst_class_demand:
                    worst_class_demand = d
                    worst_class_name = _cn(ck)

            worst_teacher_name = ""
            worst_teacher_ppw = 0
            worst_teacher_cap = 0
            for tid in tids:
                t_total = teacher_ppw.get(tid, 0) - ppw
                tc = tc_map.get(str(tid), {})
                t_cap = tc.get("max_per_week", default_max_week) or default_max_week
                if t_total > worst_teacher_ppw:
                    worst_teacher_ppw = t_total
                    worst_teacher_cap = t_cap
                    worst_teacher_name = _tn(tid)

            # Heuristic: if common free slots barely exceed demand, flag it
            if free_slots < ppw * 2 or (n_classes >= 5 and worst_teacher_ppw >= worst_teacher_cap * 0.8):
                severity = "error" if free_slots <= ppw else "warning"
                class_names = ", ".join(_cn(f"{c['class_id']}_{c['section_id']}") for c in classes)
                teacher_names_str = ", ".join(_tn(t) for t in tids) if tids else "No teacher assigned"

                detail_parts = [
                    f"This joint lesson links {n_classes} classes ({class_names}) "
                    f"and needs {ppw} common time slot(s) per week where all classes "
                    f"and teachers are free at the same time.",
                    f"Available common slots: only {free_slots} out of {total_slots} "
                    f"(most slots are already taken by other subjects).",
                    f"Teacher(s): {teacher_names_str}.",
                ]
                if worst_teacher_name:
                    detail_parts.append(
                        f"Busiest teacher: {worst_teacher_name} "
                        f"({worst_teacher_ppw}/{worst_teacher_cap} periods/week from other assignments)."
                    )

                fix_parts = []
                if worst_teacher_name and worst_teacher_ppw >= worst_teacher_cap * 0.8:
                    fix_parts.append(
                        f"Option A: Go to Auto Timetable → Teacher Constraints → "
                        f"\"{worst_teacher_name}\" → increase 'Max Periods Per Week' "
                        f"from {worst_teacher_cap} to {worst_teacher_ppw + ppw + 2}. "
                        f"This gives the solver more room to fit this joint lesson."
                    )
                if n_classes >= 5:
                    fix_parts.append(
                        f"Option B: Go to Auto Timetable → Joint Lessons → \"{j_name}\" → "
                        f"split into 2 smaller groups with fewer classes "
                        f"(e.g., {n_classes // 2} + {n_classes - n_classes // 2} classes). "
                        f"Smaller groups are much easier to schedule."
                    )
                fix_parts.append(
                    f"Option {'C' if len(fix_parts) == 2 else 'B' if len(fix_parts) == 1 else 'A'}: "
                    f"Check if any teacher's 'Max Periods Per Day' is too low. "
                    f"Go to Teacher Constraints and increase from 6 to 7 for the busiest teachers."
                )

                issues.append({
                    "type": "joint_bottleneck",
                    "severity": severity,
                    "title": f"Joint \"{j_name}\": Hard to schedule across {n_classes} classes",
                    "detail": "\n".join(detail_parts),
                    "fix": "\n".join(fix_parts),
                })

    # ── 7. Teacher near capacity (warning) ──
    tids_with_errors = set()
    for i in issues:
        for tid_check in teacher_ppw:
            if _tn(tid_check) in i.get("title", ""):
                tids_with_errors.add(tid_check)

    for tid, total in teacher_ppw.items():
        if tid in tids_with_errors:
            continue
        tc = tc_map.get(str(tid), {})
        cap = tc.get("max_per_week", default_max_week) or default_max_week
        slack = cap - total
        if 0 <= slack <= 2 and total >= 20:
            assigns = "\n".join(f"  • {e['label']}" for e in teacher_loads[tid])
            issues.append({
                "type": "teacher_near_capacity",
                "severity": "warning",
                "title": f"{_tn(tid)}: Almost at full capacity ({total}/{cap} periods/week)",
                "detail": (
                    f"{_tn(tid)} has only {slack} period(s) of slack in their weekly schedule. "
                    f"This makes it hard to find slots without conflicts.\n"
                    f"Current assignments:\n{assigns}"
                ),
                "fix": (
                    f"This is a warning, not an error. If this teacher's subjects are not fully placed:\n"
                    f"Option A: Go to Teacher Constraints → \"{_tn(tid)}\" → "
                    f"increase 'Max Periods Per Week' by 2–4.\n"
                    f"Option B: Reassign 1–2 subjects to a different teacher with more availability."
                ),
            })

    return issues


def _diagnose_infeasibility(data, days, periods, loads, joints):
    """Legacy wrapper — returns a single message string for infeasible results."""
    issues = _analyze_issues(data, days, periods, loads, joints)
    if issues:
        return " | ".join(
            f"{i['title']}: {i['detail'].split(chr(10))[0]}" for i in issues
        )
    return (
        "The combination of constraints makes a complete timetable impossible. "
        "Check teacher max-per-day limits, subject max-per-day, and teacher "
        "unavailability — these interact to block placement."
    )
