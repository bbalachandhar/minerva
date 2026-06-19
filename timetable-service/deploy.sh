#!/usr/bin/env bash
set -euo pipefail

# Deploy the Timetable Solver microservice to EC2
# Usage: ./deploy.sh
#
# Prerequisites:
#   SSH key at /Volumes/WORK/aws\ ec2\ connect/minerva_prod.pem
#   Access to ec2-user@13.234.255.106

EC2_HOST="ec2-user@13.234.255.106"
SSH_KEY="/Volumes/WORK/aws ec2 connect/minerva_prod.pem"
REMOTE_DIR="/var/www/timetable-service"
LOCAL_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "=== Deploying Timetable Solver to $EC2_HOST ==="

# 1. Create remote directory
echo "[1/5] Creating remote directory..."
ssh -i "$SSH_KEY" "$EC2_HOST" "sudo mkdir -p $REMOTE_DIR && sudo chown ec2-user:ec2-user $REMOTE_DIR"

# 2. Copy files
echo "[2/5] Uploading files..."
scp -i "$SSH_KEY" \
    "$LOCAL_DIR/app.py" \
    "$LOCAL_DIR/solver.py" \
    "$LOCAL_DIR/requirements.txt" \
    "$LOCAL_DIR/timetable-solver.service" \
    "$EC2_HOST:$REMOTE_DIR/"

# 3. Install Python venv + dependencies
echo "[3/5] Installing Python dependencies..."
ssh -i "$SSH_KEY" "$EC2_HOST" << 'REMOTE_SCRIPT'
set -euo pipefail
cd /var/www/timetable-service

# Install Python 3 if not present
if ! command -v python3 &>/dev/null; then
    sudo yum install -y python3 python3-pip
fi

# Create venv if not present
if [ ! -d venv ]; then
    python3 -m venv venv
fi

# Install/upgrade dependencies
venv/bin/pip install --upgrade pip
venv/bin/pip install -r requirements.txt
REMOTE_SCRIPT

# 4. Install and start systemd service
echo "[4/5] Configuring systemd service..."
ssh -i "$SSH_KEY" "$EC2_HOST" << 'REMOTE_SCRIPT'
set -euo pipefail
sudo cp /var/www/timetable-service/timetable-solver.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable timetable-solver
sudo systemctl restart timetable-solver
sleep 2
sudo systemctl status timetable-solver --no-pager || true
REMOTE_SCRIPT

# 5. Verify
echo "[5/5] Verifying service..."
ssh -i "$SSH_KEY" "$EC2_HOST" "curl -s http://127.0.0.1:5050/health"

echo ""
echo "=== Deployment complete ==="
