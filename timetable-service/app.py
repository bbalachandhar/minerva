"""
Timetable generation microservice.

FastAPI server that accepts timetable data and returns optimised assignments
via OR-Tools CP-SAT solver.  Runs on port 5050 (localhost only).
"""

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import Optional
import logging
import solver

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
)
log = logging.getLogger("timetable-service")

app = FastAPI(title="Minerva Timetable Solver", version="1.0.0")


# ── Request / Response Models ──────────────────────────────────────

class Period(BaseModel):
    id: int
    name: str = ""
    start: str = ""
    end: str = ""
    sort_order: int = 0

class ClassSection(BaseModel):
    class_id: int
    section_id: int
    sgs_id: int = 0
    sg_id: int = 0

class SubjectLoad(BaseModel):
    class_id: int
    section_id: int
    sgs_id: int
    sg_id: int
    subject_name: str = ""
    periods_per_week: int
    consecutive: int = 1
    teacher_ids: list[int] = []
    all_teachers_required: bool = False
    max_per_day: int = 2
    distribute_evenly: bool = False
    priority: int = 5
    batch_id: Optional[int] = None

class JointLesson(BaseModel):
    id: int
    name: str = ""
    subject_id: int = 0
    periods_per_week: int
    consecutive: int = 1
    teacher_ids: list[int] = []
    teacher_names: list[str] = []
    all_teachers_required: bool = False
    max_per_day: int = 2
    classes: list[ClassSection] = []
    fixed_slots: Optional[list] = None

class LockedEntry(BaseModel):
    class_id: int
    section_id: int
    sgs_id: int = 0
    subject_group_subject_id: int = 0
    staff_id: Optional[int] = None
    period_id: int
    day: str

class Settings(BaseModel):
    max_same_subject_day: int = 2
    fill_free_periods: bool = True

class GenerateRequest(BaseModel):
    working_days: list[str]
    periods: list[Period]
    subject_loads: list[SubjectLoad]
    joint_lessons: list[JointLesson] = []
    teacher_constraints: dict = {}
    teacher_unavailability: dict = {}
    class_unavailability: dict = {}
    subject_unavailability: dict = {}
    locked_entries: list[LockedEntry] = []
    class_labels: dict = {}
    settings: Settings = Settings()
    time_limit: int = 60


# ── Endpoints ──────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {"status": "ok", "service": "timetable-solver"}


@app.post("/generate")
def generate(req: GenerateRequest):
    log.info(
        "Generate request: %d loads, %d joints, %d days × %d periods",
        len(req.subject_loads),
        len(req.joint_lessons),
        len(req.working_days),
        len(req.periods),
    )

    data = {
        "working_days": req.working_days,
        "periods": [p.model_dump() for p in req.periods],
        "subject_loads": [l.model_dump() for l in req.subject_loads],
        "joint_lessons": [j.model_dump() for j in req.joint_lessons],
        "teacher_constraints": req.teacher_constraints,
        "teacher_unavailability": req.teacher_unavailability,
        "class_unavailability": req.class_unavailability,
        "subject_unavailability": req.subject_unavailability,
        "locked_entries": [e.model_dump() for e in req.locked_entries],
        "class_labels": req.class_labels,
        "settings": req.settings.model_dump(),
        "time_limit": req.time_limit,
    }

    try:
        result = solver.solve(data)
    except Exception as e:
        log.exception("Solver error")
        raise HTTPException(status_code=500, detail=str(e))

    log.info(
        "Result: status=%s quality=%.2f placed=%d/%d in %.1fs",
        result.get("status"),
        result.get("quality", 0),
        result.get("total_placed", 0),
        result.get("total_required", 0),
        result.get("solve_time_seconds", 0),
    )

    for u in result.get("unplaced", []):
        log.info("  UNPLACED: %s", u.get("reason", u))

    return result


@app.post("/debug-joints")
def debug_joints(req: GenerateRequest):
    """Solve ONLY joint lessons (no regular subjects) to check if they can coexist."""
    log.info("DEBUG-JOINTS: %d joints, %d days × %d periods",
             len(req.joint_lessons), len(req.working_days), len(req.periods))
    data = {
        "working_days": req.working_days,
        "periods": [p.model_dump() for p in req.periods],
        "subject_loads": [],
        "joint_lessons": [j.model_dump() for j in req.joint_lessons],
        "teacher_constraints": req.teacher_constraints,
        "teacher_unavailability": req.teacher_unavailability,
        "class_unavailability": req.class_unavailability,
        "subject_unavailability": req.subject_unavailability,
        "locked_entries": [],
        "class_labels": req.class_labels,
        "settings": req.settings.model_dump(),
        "time_limit": 30,
    }
    try:
        result = solver.solve(data)
    except Exception as e:
        log.exception("Debug-joints error")
        raise HTTPException(status_code=500, detail=str(e))

    log.info("DEBUG-JOINTS result: %s quality=%.2f placed=%d/%d in %.1fs",
             result.get("status"), result.get("quality", 0),
             result.get("total_placed", 0), result.get("total_required", 0),
             result.get("solve_time_seconds", 0))
    for u in result.get("unplaced", []):
        log.info("  UNPLACED: %s", u.get("reason", u))
    return result


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=5050)
