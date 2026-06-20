# Timetable Solver Microservice — Setup & Configuration Guide

## Overview

The Timetable Solver is a Python microservice that generates school/college timetables using Google OR-Tools CP-SAT (Constraint Programming - Satisfiability) solver. It runs as a systemd service on the EC2 server and is called by the Minerva PHP application via HTTP.

```
Browser → PHP (CodeIgniter) → HTTP POST localhost:5050/generate → Python Solver → JSON response → PHP saves to DB
```

The solver is **stateless** — PHP sends all data (loads, joints, teachers, constraints) as JSON. No database credentials are shared with the solver.

---

## Architecture

```
timetable-service/
├── app.py                      # FastAPI server — endpoints: /health, /generate, /debug-joints
├── solver.py                   # OR-Tools CP-SAT model, post-solve repair, issue analysis (~1100 lines)
├── requirements.txt            # Python dependencies
├── timetable-solver.service    # systemd unit file
├── deploy.sh                   # One-command deployment script
├── test_solver.py              # Test file
└── SETUP.md                    # This file
```

**Key PHP files that interact with the solver:**
- `application/models/Tt_generator_model.php` — `_generateViaMicroservice()` sends HTTP POST to solver
- `application/controllers/admin/Tt.php` — `run_generate()`, `test_generate()` endpoints
- `application/views/admin/tt/generate.php` — UI with time limit dropdown, progress timer

---

## Current Production Setup

| Item | Value |
|------|-------|
| EC2 Instance | t3.medium (2 vCPU, 4 GB RAM) |
| OS | Amazon Linux 2023 |
| Python | 3.9.x |
| IP | 13.234.255.106 |
| Service Port | 5050 (localhost only — not exposed to internet) |
| Install Path | `/var/www/timetable-service/` |
| systemd Unit | `timetable-solver.service` |
| PHP Instances | 7 (mce, rosy, maasc, maptc, amace, amacedu, minervademo) at `/var/www/{name}/` |
| SSH Key | `/Volumes/WORK/aws ec2 connect/minerva_prod.pem` |

---

## Fresh EC2 Setup (New Server)

### Step 1: Launch EC2 Instance

- **Recommended instance**: `c6i.xlarge` (4 vCPU, 8 GB) for best solver performance
- **Minimum**: `t3.medium` (2 vCPU, 4 GB) — works but slower solves
- **AMI**: Amazon Linux 2023
- **Security Group**: Open port 22 (SSH), 80 (HTTP), 443 (HTTPS). Port 5050 does NOT need to be open (solver is localhost only)
- **Storage**: 30 GB gp3 minimum

### Step 2: Install Prerequisites

```bash
# SSH into the new instance
ssh -i "your-key.pem" ec2-user@<NEW_IP>

# Install Python 3 and pip
sudo yum install -y python3 python3-pip git

# Install Apache/Nginx + PHP (for Minerva)
# (follow your standard Minerva PHP setup)
```

### Step 3: Deploy the Solver

**Option A — Using deploy.sh (recommended):**

Edit `deploy.sh` and update the variables at the top:

```bash
EC2_HOST="ec2-user@<NEW_IP>"
SSH_KEY="/path/to/your-key.pem"
```

Then run:

```bash
cd timetable-service/
chmod +x deploy.sh
./deploy.sh
```

This will:
1. Create `/var/www/timetable-service/` on the server
2. Upload `app.py`, `solver.py`, `requirements.txt`, and the service file
3. Create Python virtual environment and install dependencies
4. Configure and start the systemd service
5. Verify with a health check

**Option B — Manual setup:**

```bash
# On the EC2 instance:

# Create directory
sudo mkdir -p /var/www/timetable-service
sudo chown ec2-user:ec2-user /var/www/timetable-service
cd /var/www/timetable-service

# Copy files from your local machine (run locally):
scp -i "your-key.pem" app.py solver.py requirements.txt timetable-solver.service ec2-user@<NEW_IP>:/var/www/timetable-service/

# Back on EC2 — create venv and install dependencies
python3 -m venv venv
venv/bin/pip install --upgrade pip
venv/bin/pip install -r requirements.txt

# Install systemd service
sudo cp timetable-solver.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable timetable-solver
sudo systemctl start timetable-solver

# Verify
sudo systemctl status timetable-solver
curl http://127.0.0.1:5050/health
# Should return: {"status":"ok","service":"timetable-solver"}
```

### Step 4: Configure PHP to Use the Solver

The PHP app calls `localhost:5050` by default. No config change needed if solver runs on the same EC2 as PHP.

If the solver runs on a **different server**, edit `Tt_generator_model.php`:

```php
// In _generateViaMicroservice(), change:
$url = 'http://localhost:5050/generate';
// To:
$url = 'http://<SOLVER_IP>:5050/generate';
```

And open port 5050 in the solver server's security group (limit to PHP server's IP only).

---

## Day-to-Day Operations

### Deploy Code Updates

```bash
# From your local machine:
scp -i "/Volumes/WORK/aws ec2 connect/minerva_prod.pem" \
    timetable-service/solver.py timetable-service/app.py \
    ec2-user@13.234.255.106:/tmp/

ssh -i "/Volumes/WORK/aws ec2 connect/minerva_prod.pem" ec2-user@13.234.255.106 \
    "sudo cp /tmp/solver.py /tmp/app.py /var/www/timetable-service/ && sudo systemctl restart timetable-solver"
```

### Pull PHP Updates (all instances)

```bash
ssh -i "/Volumes/WORK/aws ec2 connect/minerva_prod.pem" ec2-user@13.234.255.106 \
    "for inst in mce rosy maasc maptc amace amacedu minervademo; do
        echo \"--- \$inst ---\"
        cd /var/www/\$inst && sudo git pull origin main
    done"
```

### Check Service Status

```bash
sudo systemctl status timetable-solver
```

### View Logs

```bash
# Recent logs
sudo journalctl -u timetable-solver --since "10 min ago" --no-pager

# Follow live logs during a generate run
sudo journalctl -u timetable-solver -f

# Check time limit and worker count
sudo journalctl -u timetable-solver --since "5 min ago" | grep "time limit"
```

### Restart Service

```bash
sudo systemctl restart timetable-solver
```

### Health Check

```bash
curl http://127.0.0.1:5050/health
# Returns: {"status":"ok","service":"timetable-solver"}
```

---

## Configuration & Tuning

### Solver Workers

The solver auto-detects CPU count (`os.cpu_count()`). More vCPUs = more parallel search workers = faster solves.

| Instance Type | vCPUs | Workers | Expected Solve Time (rosy, 23 classes) |
|--------------|-------|---------|---------------------------------------|
| t3.medium | 2 | 2 | 3-5 min |
| c6i.xlarge | 4 | 4 | 1-2 min |
| c6i.2xlarge | 8 | 8 | 30-60 sec |

### Time Limits

The solver time limit is set by the user via a dropdown on the Generate page (1-10 minutes). The timeout chain:

```
Browser AJAX timeout = time_limit + 120s
PHP set_time_limit  = time_limit + 120s
PHP CURL timeout    = time_limit + 60s
Solver CP-SAT       = time_limit (exact)
```

### Key Solver Parameters (in solver.py)

```python
PLACE_WEIGHT = 10000      # Priority for placing regular subjects
JOINT_WEIGHT = 100000     # Priority for placing joint lessons (10x regular)
stagnation_limit = 60     # Stop if no improvement for 60s (when within 3 of target)
```

### Memory Requirements

- Solver uses ~200-500 MB for a typical school (20-30 classes)
- For large schools (100+ classes, 4000 students), expect ~1-2 GB
- Minimum 4 GB total RAM recommended (OS + 7 PHP instances + solver)

---

## Endpoints

### GET /health
Returns service status. Use for monitoring/health checks.

```json
{"status": "ok", "service": "timetable-solver"}
```

### POST /generate
Main timetable generation endpoint. Receives all school data as JSON, returns generated timetable entries.

**Request body** (sent by PHP):
- `working_days` — list of day names
- `periods` — period definitions with break flags
- `subject_loads` — subjects per class with teacher assignments
- `joint_lessons` — multi-class joint lessons
- `teacher_constraints` — max per day/week per teacher
- `teacher_unavailability` — blocked slots per teacher
- `time_limit` — solver time in seconds

**Response:**
- `status` — "success" or "infeasible"
- `quality` — percentage placed (0-100)
- `entries` — list of timetable entries to save
- `unplaced` — entries that could not be placed
- `issues` — detected data problems with fix suggestions
- `solve_time_seconds` — actual time taken

### POST /debug-joints
Debug endpoint to test joint lesson placement in isolation.

---

## Troubleshooting

### Solver not responding (CURL error in PHP)

```bash
# Check if service is running
sudo systemctl status timetable-solver

# If dead, check why
sudo journalctl -u timetable-solver --since "1 hour ago" --no-pager

# Restart
sudo systemctl restart timetable-solver
```

### Solver returns "infeasible"

The data has conflicting constraints. Common causes:
- A teacher is assigned more periods than their max_per_week
- A class has more subject periods than available slots
- Teacher unavailability blocks too many slots

Use the **Verify Constraints** button on the Generate page to identify issues before generating.

### Solver runs full time limit but quality < 100%

- Check if classes are "packed" (total periods == available slots, zero free periods)
- Check if bottleneck teachers are near capacity (85%+ utilization)
- Try increasing the time limit to 5-10 minutes
- Fix data: reduce loads or add more teachers to spread the load

### Service won't start after Python/OS update

```bash
# Recreate venv
cd /var/www/timetable-service
rm -rf venv
python3 -m venv venv
venv/bin/pip install -r requirements.txt
sudo systemctl restart timetable-solver
```

### Port 5050 already in use

```bash
# Find what's using the port
sudo lsof -i :5050

# Kill it if it's a stale process
sudo kill <PID>
sudo systemctl restart timetable-solver
```

---

## Adding a New School Instance

To add a new school (e.g., "newschool") on the same EC2:

1. **Set up the PHP instance** at `/var/www/newschool/` (clone repo, configure DB)
2. **No solver changes needed** — all instances share the same solver on localhost:5050
3. The solver is stateless; each request contains all the school's data

To add schools on a **separate EC2**:

1. Follow the "Fresh EC2 Setup" steps above
2. Deploy the solver on the new EC2
3. Deploy Minerva PHP instances on the new EC2
4. The solver runs on localhost:5050 on each EC2 independently
