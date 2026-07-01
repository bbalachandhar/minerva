<style>
.report-nav { margin-bottom: 24px; }
.report-nav-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.report-nav-header .nav-icon { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #5b73e8, #7c5ce7); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; }
.report-nav-header h3 { margin: 0; font-size: 18px; font-weight: 700; color: #2c3e50; }
.report-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; }
.report-tile { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 8px; background: #fff; border: 1px solid #eef0f3; text-decoration: none; color: #495057; font-size: 13px; font-weight: 500; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.report-tile:hover { border-color: #5b73e8; background: #f5f7ff; color: #5b73e8; text-decoration: none; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(91,115,232,.12); }
.report-tile.active { background: linear-gradient(135deg, #5b73e8, #7c5ce7); color: #fff; border-color: transparent; box-shadow: 0 4px 12px rgba(91,115,232,.25); }
.report-tile.active .tile-icon { background: rgba(255,255,255,.2); color: #fff; }
.tile-icon { width: 32px; height: 32px; border-radius: 7px; background: #f0f2ff; color: #5b73e8; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.report-tile:hover .tile-icon { background: #e0e5ff; }
/* Dashboard hero tile */
.tile-dashboard { background: linear-gradient(135deg,#5b73e8,#7c5ce7) !important; color:#fff !important; border-color:transparent !important; box-shadow:0 4px 14px rgba(91,115,232,.3) !important; }
.tile-dashboard .tile-icon { background:rgba(255,255,255,.2) !important; color:#fff !important; }
.tile-dashboard:hover { transform:translateY(-3px) !important; box-shadow:0 6px 18px rgba(91,115,232,.4) !important; }
</style>
<div class="report-nav">
    <div class="report-nav-header">
        <span class="nav-icon"><i class="fa fa-check-square-o"></i></span>
        <h3><?php echo $this->lang->line('attendance_report'); ?></h3>
    </div>
    <div class="report-tiles">

        <!-- ── Hero: Dashboard ───────────────────────────────── -->
        <?php if ($this->rbac->hasPrivilege('student_attendance_dashboard', 'can_view')) { ?>
        <a href="<?php echo site_url('admin/attendancedashboard/index'); ?>" class="report-tile tile-dashboard <?php echo set_SubSubmenu('admin/attendancedashboard/index'); ?>">
            <span class="tile-icon"><i class="fa fa-bar-chart"></i></span>
            Live Dashboard
        </a>
        <?php } ?>

        <!-- ── Day-wise student reports ──────────────────────── -->
        <?php if (!is_subAttendence()) {
            if ($this->rbac->hasPrivilege('attendance_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>attendencereports/classattendencereport" class="report-tile <?php echo set_SubSubmenu('Reports/attendance/attendance_report'); ?>">
            <span class="tile-icon"><i class="fa fa-calendar-check-o"></i></span>
            <?php echo $this->lang->line('attendance_report'); ?>
        </a>
            <?php } if ($this->rbac->hasPrivilege('student_attendance_type_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>attendencereports/attendancereport" class="report-tile <?php echo set_SubSubmenu('Reports/attendence/attendancereport'); ?>">
            <span class="tile-icon"><i class="fa fa-users"></i></span>
            <?php echo $this->lang->line('student_attendance_type_report'); ?>
        </a>
            <?php } if ($this->rbac->hasPrivilege('daily_attendance_report', 'can_view')) { ?>
        <a href="<?php echo site_url('attendencereports/daily_attendance_report'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/attendance/daily_attendance_report'); ?>">
            <span class="tile-icon"><i class="fa fa-calendar"></i></span>
            <?php echo $this->lang->line('daily_attendance_report'); ?>
        </a>
        <a href="<?php echo site_url('attendencereports/daywiseattendancereport'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/attendance/daywiseattendancereport'); ?>">
            <span class="tile-icon"><i class="fa fa-th"></i></span>
            <?php echo $this->lang->line('student_day_wise_attendance_report'); ?>
        </a>
            <?php }
        } ?>

        <!-- ── Period-wise student reports ──────────────────── -->
        <?php if (is_subAttendence()) {
            if ($this->rbac->hasPrivilege('student_period_attendance_report', 'can_view')) { ?>
        <a href="<?php echo site_url('attendencereports/reportbymonth'); ?>" class="report-tile <?php echo set_subSubmenu('Reports/attendence/reportbymonth'); ?>">
            <span class="tile-icon"><i class="fa fa-th"></i></span>
            Class Attendance Matrix
        </a>
        <a href="<?php echo site_url('attendencereports/reportbymonthstudent'); ?>" class="report-tile <?php echo set_subSubmenu('Reports/attendence/reportbymonthstudent'); ?>">
            <span class="tile-icon"><i class="fa fa-user-circle"></i></span>
            <?php echo $this->lang->line('student_period_attendance'); ?>
        </a>
            <?php }
            if ($this->rbac->hasPrivilege('teacher_marking_coverage', 'can_view')) { ?>
        <a href="<?php echo site_url('attendencereports/teachermarkingcoverage'); ?>" class="report-tile <?php echo set_subSubmenu('Reports/attendence/teachermarkingcoverage'); ?>">
            <span class="tile-icon" style="background:#fef9e7;color:#f39c12;"><i class="fa fa-user-circle-o"></i></span>
            Teacher Coverage
        </a>
            <?php }
        } ?>

        <!-- ── Staff attendance ──────────────────────────────── -->
        <?php if ($this->rbac->hasPrivilege('staff_attendance_report', 'can_view')) { ?>
        <a href="<?php echo site_url('attendencereports/staffdaywiseattendancereport'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/attendance/staffdaywiseattendancereport'); ?>">
            <span class="tile-icon" style="background:#e8fdf4;color:#27ae60;"><i class="fa fa-id-badge"></i></span>
            <?php echo $this->lang->line('staff_day_wise_attendance_report'); ?>
        </a>
        <a href="<?php echo base_url(); ?>attendencereports/staffattendancereport" class="report-tile <?php echo set_SubSubmenu('Reports/attendance/staff_attendance_report'); ?>">
            <span class="tile-icon" style="background:#e8fdf4;color:#27ae60;"><i class="fa fa-address-card"></i></span>
            <?php echo $this->lang->line('staff_attendance_report'); ?>
        </a>
        <a href="<?php echo base_url(); ?>attendencereports/staffattendancewithpunchreport" class="report-tile <?php echo set_SubSubmenu('Reports/attendance/staff_attendance_with_punch_report'); ?>">
            <span class="tile-icon" style="background:#e8fdf4;color:#27ae60;"><i class="fa fa-hand-pointer-o"></i></span>
            Staff Attendance with Punch
        </a>
        <?php } ?>

        <!-- ── Biometric ─────────────────────────────────────── -->
        <?php if ($this->customlib->is_biometricAttendence() && $this->rbac->hasPrivilege('biometric_attendance_log', 'can_view')) { ?>
        <a href="<?php echo site_url('attendencereports/biometric_attlog'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/attendence/biometric_attlog'); ?>">
            <span class="tile-icon"><i class="fa fa-hand-paper-o"></i></span>
            <?php echo $this->lang->line('biometric_attendance_log'); ?>
        </a>
        <?php } ?>

    </div>
</div>
