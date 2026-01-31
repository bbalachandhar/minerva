<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();?>
<style type="text/css">
    .borderwhite{border-top-color: #fff !important;}
    .box-header>.box-tools {display: none;}
    .sidebar-collapse #barChart{height: 100% !important;}
    .sidebar-collapse #lineChart{height: 100% !important;}
    .tooltip-inner {max-width: 135px;}
    #calendar {
        height: 80%;
    }
    #calendar .fc-view-container {
        height: 100%;
    }
    .fo-skeleton {
        position: relative;
        color: transparent;
        background: #e6e6e6;
        border-radius: 4px;
        display: inline-block;
        min-width: 40px;
    }
    .fo-skeleton.fo-line {
        min-width: 120px;
        height: 12px;
        vertical-align: middle;
    }
    .fo-skeleton::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
        animation: fo-shimmer 1.2s infinite;
    }
    @keyframes fo-shimmer {
        100% { transform: translateX(200%); }
    }

            /* Original birthday ticker styles */
            .birthday-ticker-container {
                position: relative;
                height: 82%; /* Will take 82% of parent's fixed height */
            }

            .birthday-ticker-content {
                animation: ticker-scroll var(--ticker-duration, 20s) linear infinite; /* Use CSS variable for duration */
                /* height: 200%; -- Removed for dynamic height calculation */
            }            .birthday-ticker-clipper {
                overflow: hidden;
                overflow-y: hidden;
                max-height: 100%;
                height: 100%;
                position: relative; /* Ensure it's a positioning context */
            }
            .birthday-ticker-content ul {
                padding: 0;
                margin: 0;
            }
            .birthday-ticker-content li {
                list-style: none;
                /* white-space: nowrap; uncomment if text should not wrap */
            }
            .mediarow {
                overflow: hidden;
            }

            @keyframes ticker-scroll {
                0% {
                    transform: translateY(0);
                }
                100% {
                    transform: translateY(var(--ticker-translate-y, -50%)); /* Dynamic translation with fallback */
                }
            }
            
            /* Equal Height Row styles (these were mostly fine but ensuring consistency) */
            .equal-height-row {
              display: -webkit-box;
              display: -ms-flexbox;
              display: flex;
              -ms-flex-wrap: nowrap !important;
                  flex-wrap: wrap;
            }
            .equal-height-row > [class*='col-'] {
              display: -webkit-box;
              display: -ms-flexbox;
              display: flex;
              -webkit-box-orient: vertical;
              -webkit-box-direction: normal;
                  -ms-flex-direction: column;
                      flex-direction: column;
            }
            .equal-height-row > [class*='col-'] > .topprograssstart {
                /* Revert flex-grow property for static height */
                -webkit-box-flex: 0; 
                -ms-flex: 0 0 auto !important; 
                flex: 0 0 auto !important;
                height: 275px; /* Fixed height based on user request */
                display: block; /* Override flex display if applied earlier */
            }
            
            /* Staff/Student card specific styles */
            .staffleft-box {
                position: relative;
            }

            .birthday-date {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: rgba(255, 255, 255, 0.8);
                color: #000;
                text-align: center;
                padding: 2px;
                font-size: 12px;
                font-weight: bold;
                z-index: 10; /* Bring to front */
            }
            
            /* General flex card styles (reverting these to avoid dynamic height) */
            .topprograssstart.flex-card {
                /* Removed display: flex, flex-direction: column, height: 100% to revert to static block behavior */
                display: block; /* Ensure it behaves as a block element */
                height: 100%; /* Keep 100% to fill parent, but parent col-* will now have fixed height */
            }

            .topprograssstart.flex-card h5.pro-border {
                /* Removed flex-shrink: 0; */
                margin: 0;
            }

            .topprograssstart.flex-card .birthday-ticker-container {
                /* Removed flex-grow: 1; */
                height: 82%; /* Will take 100% of parent's fixed height */
            }
        </style>
<div class="content-wrapper">
    <section class="content">
        <div class="">
            <?php if (ENVIRONMENT != 'production') { ?>
                <div class="alert alert-danger">
                    Environment set to <?php echo ENVIRONMENT ;?>! <br>
                    Don't forget to set back to production in the main index.php file after finishing your tests or <?php echo ENVIRONMENT ;?>. <br>
                    Please be aware that in <?php echo ENVIRONMENT ;?> mode you may see some errors and deprecation warnings, for this reason, it's always recommended to set the environment to "production" if you are not actually developing some features/modules or trying to test some code.
                </div>
            <?php } ?>
                
            <?php if ($mysqlVersion && $sqlMode && strpos($sqlMode->mode, 'ONLY_FULL_GROUP_BY') !== false) {?>
                <div class="alert alert-danger">
                    Minerva may not work properly because ONLY_FULL_GROUP_BY is enabled, consult with your hosting provider to disable ONLY_FULL_GROUP_BY in sql_mode configuration.
                </div>
            <?php }?>

            <?php
$show    = false;
$role    = $this->customlib->getStaffRole();
$role_id = json_decode($role)->id;
foreach ($notifications as $notice_key => $notice_value) {

    if ($role_id == 7) {
        $show = true;
    } elseif (date($this->customlib->getSchoolDateFormat()) >= date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($notice_value->publish_date))) {
        $show = true;
    }
    if ($show) {
        ?>
                    <div class="dashalert alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="alertclose close close_notice" data-dismiss="alert" aria-label="Close" data-noticeid="<?php echo $notice_value->id; ?>"><span aria-hidden="true">&times;</span></button>
                        <a href="<?php echo site_url('admin/notification') ?>"><?php echo $notice_value->title; ?></a>
                    </div>
                    <?php
}
}
?>
        </div>
        <style type="text/css">
            @media (min-width: 1200px) { /* Apply only on large screens */
                .widget-five-col {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-around; /* Distribute space evenly around items */
                }
                .widget-five-col > div {
                    flex: 0 0 19%; /* Make each item take approximately 1/5th width */
                    max-width: 19%;
                    padding: 0 5px; /* Add some padding between items */
                    margin-bottom: 10px; /* Maintain vertical spacing */
                }
                .widget-five-col > div.col-lg-4, /* Reset default Bootstrap column padding */
                .widget-five-col > div.col-md-6,
                .widget-five-col > div.col-sm-6 {
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }
            }
            .equal-height-row {
              display: -webkit-box;
              display: -ms-flexbox;
              display: flex;
              -ms-flex-wrap: wrap;
                  flex-wrap: wrap;
            }
            .equal-height-row > [class*='col-'] {
              display: -webkit-box;
              display: -ms-flexbox;
              display: flex;
              -webkit-box-orient: vertical;
              -webkit-box-direction: normal;
                  -ms-flex-direction: column;
                      flex-direction: column;
            }
            .equal-height-row > [class*='col-'] > .topprograssstart {
                -webkit-box-flex: 1;
                -ms-flex: 1 0 auto !important;
                flex: 1 0 auto !important;
            }
            .staffleft-box {
                position: relative;
            }

            .birthday-date {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: rgba(255, 255, 255, 0.8);
                color: #000;
                text-align: center;
                padding: 2px;
                font-size: 12px;
                font-weight: bold;
                z-index: 10; /* Bring to front */
            }
            .topprograssstart.flex-card {
                display: flex;
                flex-direction: column;
                height: 100%;
            }

            .topprograssstart.flex-card .birthday-ticker-container {
                flex-grow: 1;
                min-height: 150px; /* A sensible minimum height */
            }
        </style>
        <div class="row equal-height-row">
            <div class="col-md-3 col-sm-6 mb10">
                <div class="topprograssstart flex-card">
                    <h5 class="pro-border">Students Today's Birthday - <?php echo count($student_birthdays); ?></h5>
                    <div class="birthday-ticker-container">
                        <div class="birthday-ticker-clipper">
                        <div class="birthday-ticker-content" style="animation-duration: 20s;">                        
                          <?php if (!empty($student_birthdays)) {?>
                            <div class="mediarow">
                                <div class="row">
                                    <?php foreach (array_merge($student_birthdays, $student_birthdays) as $student) { ?>
                                        <div class="col-lg-12 col-md-12 col-sm-12 img_div_modal">
                                            <div class="staffinfo-box">
                                                <div class="staffleft-box">
                                                    <?php
                                                        if (!empty($student["image"])) {
                                                            $image = "uploads/student_images/" . $student["image"];
                                                        } else {
                                                            $image = "uploads/student_images/no_image.png";
                                                        }
                                                    ?>
                                                    <img src="<?php echo base_url() . $image ?>" alt="User Image">
                                                </div>
                                                <div class="staffleft-content">
                                                    <h5><span><?php echo $student["firstname"] . " " . $student["lastname"]; ?></span></h5>
                                                    <p><font><?php echo $student["class"] . " (" . $student["section"] . ")" ?></font></p>
                                                    <p><font><?php echo $student["mobileno"] ?></font></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <p class="text-center"><?php echo $this->lang->line('no_record_found'); ?></p>
                        <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="col-md-3 col-sm-6 mb10">
                <div class="topprograssstart flex-card">
                    <h5 class="pro-border">Current Week Staff Birthdays - <?php echo count($staff_birthdays); ?></h5>
                    <div class="birthday-ticker-container">
                         <?php 
                            $staff_birthday_count = count($staff_birthdays);
                            $staff_scroll_duration = ($staff_birthday_count > 0) ? $staff_birthday_count * 2 : 50;
                        ?>
                        <div class="birthday-ticker-clipper">
                            <div class="birthday-ticker-content" style="animation-duration: 20s;">
                                <?php if (!empty($staff_birthdays)) { ?>
                                     <div class="mediarow">
                                        <div class="row">
                                            <?php foreach (array_merge($staff_birthdays, $staff_birthdays) as $staff) { ?>
                                                <div class="col-lg-12 col-md-12 col-sm-12 img_div_modal">
                                                    <div class="staffinfo-box">
                                                        <div class="staffleft-box">
                                                            <?php
                                                                if (!empty($staff["image"])) {
                                                                    $image = "uploads/staff_images/" . $staff["image"];
                                                                } else {
                                                                     if ($staff['gender'] == 'Male') {
                                                                        $image = "uploads/staff_images/default_male.jpg";
                                                                    } else {
                                                                        $image = "uploads/staff_images/default_female.jpg";
                                                                    }
                                                                }
                                                            ?>
                                                            <img src="<?php echo base_url() . $image ?>" alt="User Image">
                                                            <div class="birthday-date">
                                                                <?php echo date('d M', strtotime($staff['dob'])); ?>
                                                            </div>
                                                        </div>
                                                        <div class="staffleft-content">
                                                            <h5><span><?php echo $staff["name"] . " " . $staff["surname"]; ?></span></h5>
                                                            <p><font><?php echo $staff["employee_id"] ?></font></p>
                                                            <p><font><?php echo $staff["contact_no"] ?></font></p>
                                                            <p><font><?php echo $staff["department"]; ?></font></p>
                                                            <p class="staffsub" ><span data-toggle="tooltip" title="<?php echo $this->lang->line('role'); ?>"><?php echo $staff["role"] ?></span> <span data-toggle="tooltip" title="<?php echo 'Designation'; ?>"> <?php echo $staff["designation"] ?></span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <p class="text-center"><?php echo $this->lang->line('no_record_found'); ?></p>
                                <?php } ?>
                            </div> <!-- Close birthday-ticker-content -->
                        </div> <!-- Close birthday-ticker-clipper -->
                        </div>
                    </div>
            </div>
            <?php
            if ($this->module_lib->hasActive('student_attendance')) {
                if ($this->rbac->hasPrivilege('today_attendance_widegts', 'can_view')) {
                    ?>
                                <div class="col-md-2 col-sm-6 mb10">
                                    <div class="topprograssstart flex-card">
                                        <h5 class="pro-border"> <?php echo $this->lang->line('student_today_attendance'); ?></h5>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_present']; ?> <?php echo $this->lang->line('present'); ?><span class="pull-right"><?php echo $attendence_data['present']; ?></span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $attendence_data['present']; ?>"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_late']; ?> <?php echo $this->lang->line('late') ?><span class="pull-right"><?php echo $attendence_data['late']; ?></span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $attendence_data['late']; ?>"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_absent']; ?> <?php echo $this->lang->line('absent'); ?><span class="pull-right"><?php echo $attendence_data['absent']; ?></span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $attendence_data['absent']; ?>"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_half_day']; ?> <?php echo $this->lang->line('half_day'); ?><span class="pull-right"><?php echo $attendence_data['half_day']; ?></span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $attendence_data['half_day']; ?>"></div>
                                            </div>
                                        </div>
                                    </div><!--./topprograssstart-->
                                </div><!--./col-md-2-->
                                <?php
            }
            }
            if ($this->rbac->hasPrivilege('staff_today_attendance', 'can_view')) {
            ?>
                                <div class="col-md-2 col-sm-6 mb10">
                                    <div class="topprograssstart flex-card">
                                        <h5 class="pro-border"> Staff Today Attendance</h5>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $staff_attendance_details['total_present']; ?> Present<span class="pull-right"><?php echo $staff_attendance_details['present']; ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $staff_attendance_details['present']; ?>%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $staff_attendance_details['total_late']; ?> Late<span class="pull-right"><?php echo $staff_attendance_details['late']; ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $staff_attendance_details['late']; ?>%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $staff_attendance_details['total_absent']; ?> Absent<span class="pull-right"><?php echo $staff_attendance_details['absent']; ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $staff_attendance_details['absent']; ?>%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $staff_attendance_details['total_half_day']; ?> Half Day<span class="pull-right"><?php echo $staff_attendance_details['half_day']; ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $staff_attendance_details['half_day']; ?>%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $staff_attendance_details['total_permission']; ?> Permissions<span class="pull-right"><?php echo $staff_attendance_details['permission']; ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar" style="width: <?php echo $staff_attendance_details['permission']; ?>%"></div>
                                            </div>
                                        </div>
                                    </div><!--./topprograssstart-->
                                </div><!--./col-md-2-->
            <?php
            }
            if ($this->module_lib->hasActive('front_office')) {
                if ($this->rbac->hasPrivilege('enquiry_overview_widegts', 'can_view')) {
                    ?>
                                <div class="col-md-2 col-sm-6 mb10">
                                    <div class="topprograssstart flex-card">
                                        <h5 class="pro-border"><?php echo $this->lang->line('enquiry_overview'); ?></h5>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['active']; ?> <?php echo $this->lang->line('active') ?><span class="pull-right"><?php echo round($enquiry_overview['active_progress'], 2); ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-red" style="width: <?php echo $enquiry_overview['active_progress']; ?>%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['won']; ?> <?php echo $this->lang->line('won') ?><span class="pull-right"><?php echo round($enquiry_overview['won_progress'], 2); ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-yellow" style="width: <?php echo $enquiry_overview['won_progress']; ?>%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['passive']; ?> <?php echo $this->lang->line('passive') ?><span class="pull-right"><?php echo round($enquiry_overview['passive_progress'], 2); ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-yellow" style="width: <?php echo $enquiry_overview['passive_progress']; ?>%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['lost']; ?> <?php echo $this->lang->line('lost') ?><span class="pull-right"><?php echo round($enquiry_overview['lost_progress'], 2); ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-yellow" style="width: <?php echo $enquiry_overview['lost_progress']; ?>%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['dead']; ?> <?php echo $this->lang->line('dead') ?><span class="pull-right"><?php echo round($enquiry_overview['dead_progress'], 2); ?>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-yellow" style="width: <?php echo $enquiry_overview['dead_progress']; ?>%"></div>
                                            </div>
                                        </div>
                                    </div><!--./topprograssstart-->
                                </div><!--./col-md-2-->
                    <?php
            }
            }
            ?>
        </div>
        <div class="row">
            <?php
            if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('fees_awaiting_payment_widegts', 'can_view')) {
            ?>
                <div class="col-md-3 col-sm-6">
                    <div class="topprograssstart">
                        <p class="mt5 clearfix font14"><i class="fa fa-money ftlayer"></i><?php echo $this->lang->line('fees_awaiting_payment'); ?>
                            <span class="pull-right">
                                <span class="fees-awaiting-amount fo-skeleton fo-line">0</span>
                            </span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar progress-bar-red fees-awaiting-progress-bar" style="width: <?php echo round(isset($fees_awaiting_progress) ? $fees_awaiting_progress : 0, 2); ?>%"></div>
                            </div>
                        </div>
                    </div><!--./topprograssstart-->
                </div><!--./widget-item-->
            <?php }
            ?>
                                        
                                        <?php 
                                            if ($this->rbac->hasPrivilege('staff_approved_leave_widegts', 'can_view')) {
                                                ?>
                                                            <div class="col-md-3 col-sm-6">
                                                                <div class="topprograssstart shadow">
                                                                    <p class="mt5 font14"><i class="fa fa-ioxhost ftlayer"></i><?php echo $this->lang->line('staff_approved_leave'); ?><span class="pull-right"><?php echo ($getStaffApproveMonthlyLeave) + 0; ?>/<?php echo ($getStaffMonthlyLeave); ?></span>
                                                                    </p>
                                                                    <div class="progress-group">
                                                                        <div class="progress progress-minibar">
                                                                            <div class="progress-bar progress-bar-lris-blue" style="width: <?php echo $staffapprovemonthlyleave; ?>%"></div>
                                                                        </div>
                                                                    </div>
                                                                </div><!--./topprograssstart-->
                                                            </div><!--./widget-item-->
                                                            <?php
                                        }
                                         ?>
 
<?php
    if ($this->rbac->hasPrivilege('student_approved_leave_widegts', 'can_view')) {
        ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="topprograssstart shadow">
                            <p class="mt5 font14"><i class="fa fa-ioxhost ftlayer"></i><?php echo $this->lang->line('student_approved_leave'); ?><span class="pull-right"><?php echo ($getStudentApproveMonthlyLeave) + 0; ?>/<?php echo ($getStudentMonthlyLeave); ?></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar" style="width: <?php echo $studentapprovemonthlyleave; ?>%"></div>
                                </div>
                            </div>
                        </div><!--./topprograssstart-->
                    </div><!--./widget-item-->
                    <?php
}
  ?>

            <?php
if ($this->module_lib->hasActive('front_office')) {
    if ($this->rbac->hasPrivilege('conveted_leads_widegts', 'can_view')) {
        ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="topprograssstart">
                            <p class="mt5 clearfix font14"><i class="fa fa-ioxhost ftlayer"></i><?php echo $this->lang->line('converted_leads'); ?><span class="pull-right"><?php echo $total_complete + 0; ?>/<?php echo $total_enquiry; ?></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-red" style="width: <?php echo $fenquiryprogressbar; ?>%"></div>
                                </div>
                            </div>
                        </div><!--./topprograssstart-->
                    </div><!--./widget-item-->
                    <?php
}
} ?>
        <div class="row">
            <?php
$bar_chart = true;

if (($this->module_lib->hasActive('fees_collection')) || ($this->module_lib->hasActive('expense'))) {
    if ($this->rbac->hasPrivilege('fees_collection_and_expense_monthly_chart', 'can_view')) {

        $div_rol  = 3;
        $userdata = $this->customlib->getUserData();
        ?>
                    <div class="col-lg-7 col-md-7 col-sm-12 col60">
                        <div class="box box-primary borderwhite">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php echo $this->lang->line('fees_collection_expenses_for'); ?> <?php echo $this->lang->line(strtolower(date('F'))) . " " . date('Y');

        ?></h3>
                                
                            </div>
                            <div class="box-body">
                                <div class="chart">
                                    <canvas id="barChart" height="98"></canvas>
                                </div>
                            </div>
                        </div>
                    </div><!--./col-lg-7-->
                <?php }
}
?>
            <?php
if ($this->module_lib->hasActive('income')) {
    if ($this->rbac->hasPrivilege('income_donut_graph', 'can_view')) {
        ?>
                    <div class="col-lg-5 col-md-5 col-sm-12 col40">
                        <div class="box box-primary borderwhite">
                            <div class="box-header with-border"><h3 class="box-title"><?php echo $this->lang->line('income') . " - " . $this->lang->line(strtolower(date('F'))) . " " . date('Y');  ?></h3></div>
                            <div class="box-body">
                                <div class="chart-responsive">
                                    <canvas id="doughnut-chart" class="pb20" height="150"></canvas>
                                </div>
                            </div>
                        </div><!--./col-md-6-->
                    </div><!--./col-lg-5-->
    <?php
}
}
?>
        </div><!--./row-->
        <div class="row">
            <?php
$line_chart = true;
if (($this->module_lib->hasActive('fees_collection')) || ($this->module_lib->hasActive('expense'))) {
    if ($this->rbac->hasPrivilege('fees_collection_and_expense_yearly_chart', 'can_view')) {
        $div_rol = 3;
        ?>
                    <div class="col-lg-7 col-md-7 col-sm-12 col60">
                        <div class="box box-info borderwhite">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php echo $this->lang->line('fees_collection_expenses_for_session'); ?> <?php echo $this->setting_model->getCurrentSessionName(); ?></h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="chart">
                                    <canvas id="lineChart" height="98"></canvas>
                                </div>
                            </div>
                        </div>
                    </div><!--./col-lg-7-->
                    <?php
}
}
if ($this->module_lib->hasActive('expense')) {
    ?>
    <?php if ($this->rbac->hasPrivilege('expense_donut_graph', 'can_view')) {
        ?>
                    <div class="col-lg-5 col-md-5 col-sm-12 col40">
                        <div class="box box-primary borderwhite">
                            <div class="box-header with-border"><h3 class="box-title"><?php echo $this->lang->line('expense') . " - " . $this->lang->line(strtolower(date('F'))) . " " . date('Y');  ?></h3>
                            </div><!--./info-box-->
                            <div class="box-body">
                                <div class="chart-responsive">
                                    <canvas id="doughnut-chart1" class="pb20" height="150"></canvas>
                                </div>
                            </div>
                        </div>
                    </div><!--./col-lg-5-->
    <?php }
}
?>
        </div><!--./row-->
                    <div class="row row-flex3">
        
        <?php
        if ($this->module_lib->hasActive('fees_collection')) {
            if ($this->rbac->hasPrivilege('fees_overview_widegts', 'can_view')) {
                ?>
                <div class="col-md-3 col-sm-6 mb10">
                    <div class="topprograssstart flex-card" id="fees-overview-widget" data-url="<?php echo site_url('admin/admin/fees_overview_widget'); ?>">
                        <h5 class="pro-border"><?php echo $this->lang->line('fees_overview'); ?></h5>
                        <p class="text-uppercase mt10 clearfix">
                            <strong><?php echo $this->lang->line('unpaid'); ?>:</strong> <span class="fo-total-unpaid fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-unpaid-progress fo-skeleton">0</span>%</span><br/>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-unpaid-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar fo-unpaid-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        <p class="text-uppercase mt10 clearfix">
                            <strong><?php echo $this->lang->line('partial'); ?>:</strong> <span class="fo-total-partial fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-partial-progress fo-skeleton">0</span>%</span><br/>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-partial-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar progress-bar-aqua fo-partial-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        <p class="text-uppercase mt10 clearfix">
                            <strong><?php echo $this->lang->line('paid'); ?>:</strong> <span class="fo-total-paid fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-paid-progress fo-skeleton">0</span>%</span><br/>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-paid-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar progress-bar-aqua fo-paid-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        <hr/>
                        <p class="text-uppercase mt10 clearfix" style="font-size:14px;">
                            <strong>Total Demand:</strong> <span class="fo-total-demand fo-skeleton fo-line">0</span><br/>
                            <strong>Total Collection:</strong> <span class="fo-total-collection fo-skeleton fo-line">0</span><br/>
                            <strong>Total Awaiting:</strong> <span class="fo-total-awaiting fo-skeleton fo-line">0</span>
                        </p>
                    </div><!--./topprograssstart-->
                </div><!--./col-md-3-->
                <?php
            }
        }
        
        if ($this->rbac->hasPrivilege('student_head_count_widget', 'can_view')) {  ?>
                            <div class="col-md-2 col-sm-6 mb10">
                                <div class="topprograssstart flex-card">
                                    <h5 class="pro-border"><?php echo $this->lang->line('student_head_count'); ?> <span class="pull-right" style="font-size: 18px; font-weight: bold;"><?php echo $total_students_heads; ?></span></h5>
                                    <p class="text-uppercase mt10 clearfix" style="font-size: 12px;">
                                        <i class="fa fa-male" style="color: #3c8dbc;"></i> Male: <?php echo $male_students; ?> <span class="pull-right"><?php echo ($total_students_heads > 0) ? round(($male_students * 100 / $total_students_heads), 2) : 0; ?>%</span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar" style="width: <?php echo ($total_students_heads > 0) ? ($male_students * 100 / $total_students_heads) : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <p class="text-uppercase mt10 clearfix" style="font-size: 12px;">
                                        <i class="fa fa-female" style="color: #dd4b39;"></i> Female: <?php echo $female_students; ?> <span class="pull-right"><?php echo ($total_students_heads > 0) ? round(($female_students * 100 / $total_students_heads), 2) : 0; ?>%</span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-red" style="width: <?php echo ($total_students_heads > 0) ? ($female_students * 100 / $total_students_heads) : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <?php if ($other_students > 0) { ?>
                                    <p class="text-uppercase mt10 clearfix" style="font-size: 12px;">
                                        Other: <?php echo $other_students; ?>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-yellow" style="width: <?php echo ($total_students_heads > 0) ? ($other_students * 100 / $total_students_heads) : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <?php if (!empty($unspecified_students) && $unspecified_students > 0) { ?>
                                    <p class="text-uppercase mt10 clearfix" style="font-size: 12px;">
                                        <i class="fa fa-question-circle" style="color: #999;"></i> Not Specified: <?php echo $unspecified_students; ?>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar" style="background-color: #999; width: <?php echo ($total_students_heads > 0) ? ($unspecified_students * 100 / $total_students_heads) : 0; ?>%"></div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div><!--./topprograssstart-->
                            </div><!--./col-md-2-->
        <?php } ?>
        
        <?php if ($this->module_lib->hasActive('library')) {
            if ($this->rbac->hasPrivilege('book_overview_widegts', 'can_view')) {
                ?>
                            <div class="col-md-2 col-sm-6 mb10">
                                <div class="topprograssstart flex-card">
                                    <h5 class="pro-border"><?php echo $this->lang->line('library_overview'); ?></h5>
                                    <p class="text-uppercase mt10 clearfix"><?php echo $book_overview['dueforreturn']; ?> <?php echo $this->lang->line('due_for_return'); ?><span class="pull-right"></span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-green" style="width: <?php echo $book_overview['dueforreturn']; ?>%"></div>
                                        </div>
                                    </div>
                                    <p class="text-uppercase mt10 clearfix"><?php echo $book_overview['forreturn']; ?> <?php echo $this->lang->line('returned') ?><span class="pull-right"></span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-green" style="width: <?php echo $book_overview['forreturn']; ?>%"></div>
                                        </div>
                                    </div>
                                    <p class="text-uppercase mt10 clearfix"><?php echo $book_overview['total_issued']; ?> <?php echo $this->lang->line('issued_out_of'); ?> <?php echo $book_overview['total'] ?><span class="pull-right"><?php echo $book_overview['issued_progress']; ?>%</span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-green" style="width: <?php echo $book_overview['issued_progress']; ?>%"></div>
                                        </div>
                                    </div>
                                    <p class="text-uppercase mt10 clearfix"><?php echo $book_overview['availble']; ?> <?php echo $this->lang->line('available_out_of') ?> <?php echo $book_overview['total']; ?><span class="pull-right"><?php echo $book_overview['availble_progress']; ?>%</span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-green" style="width: <?php echo $book_overview['availble_progress']; ?>%"></div>
                                        </div>
                                    </div>
                                </div><!--./topprograssstart-->
                            </div><!--./col-md-2-->
                <?php
        }
        }


        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        
        $div_col    = 12;
        $div_rol    = 12;
        $bar_chart  = true;
        $line_chart = true;if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) {
    $div_col = 9;
    $div_rol = 12;
}

$widget_col = array();
if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) {
    $widget_col[0] = 1;
    $div_rol       = 3;
}

if ($this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) {
    $widget_col[1] = 2;
    $div_rol       = 3;
}

if ($this->rbac->hasPrivilege('student_count_widget', 'can_view')) {
    $widget_col[2] = 3;
    $div_rol       = 3;
}
$div = sizeof($widget_col);
if (!empty($widget_col)) {
    $widget = 12 / $div;
} else {

    $widget = 12;
}
?>

            <div class="row">
                <div class="col-lg-9 col-md-9 col-sm-12 col80">
                    <div class="row">
<?php
if ($this->module_lib->hasActive('fees_collection')) {
    if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) {
        ?>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="info-box">
                                        <a href="<?php echo site_url('studentfee') ?>">
                                            <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo $this->lang->line('monthly_fees_collection'); ?></span>
                                                <span class="info-box-number"><?php if($month_collection){ echo $currency_symbol . amountFormat($month_collection); } ?></span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
    <?php }
}
?>
<?php
if ($this->module_lib->hasActive('expense')) {
    if ($this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) {
        ?>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="info-box">
                                        <a href="<?php echo site_url('admin/expense') ?>">
                                            <span class="info-box-icon"><i class="fa fa-credit-card"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo $this->lang->line('monthly_expenses'); ?></span>
                                                <span class="info-box-number"><?php if($month_expense){ echo $currency_symbol . amountFormat($month_expense); } ?></span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
    <?php
}
}
?>

                    </div>

<?php
if ($this->module_lib->hasActive('calendar_to_do_list')) {
    if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_view')) {
        $div_rol = 3;
        ?>
                        <div class="box box-primary borderwhite">
                            <div class="box-body">
                                <!-- THE CALENDAR -->
                                <div id="calendar"></div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /. box -->
                    <?php }}?>
                </div><!--./col-lg-9-->
<?php
if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) {
    ?>
                    <div class="col-lg-3 col-md-3 col-sm-12 col20">
    <?php foreach ($roles as $key => $value) {
        ?>
                            <div class="info-box">
                                <a href="#">
                                    <span class="info-box-icon"><i class="fa fa-user-secret"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?php echo $key; ?></span>
                                        <span class="info-box-number"><?php echo $value; ?></span>
                                    </div>
                                </a>
                            </div>
    <?php }?>
                    </div><!--./col-lg-3-->
<?php }?>
            </div><!--./row-->
        </div><!--./row-->
</div>
<div id="newEventModal" class="modal fade " role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line("add_new_event"); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" id="addevent_form" method="post" enctype="multipart/form-data" action="">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('event_title'); ?></label><small class="req"> *</small>
                                <input class="form-control" name="title" id="input-field">
                                <span class="text-danger"><?php echo form_error('title'); ?></span>
                            </div>    
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('description'); ?></label>
                                <textarea name="description" class="form-control" id="desc-field"></textarea>
                            </div>    
                        </div>
                    <div class="col-md-12 col-lg-12 col-sm-12">        
                         <div class="row">
                            <div class="col-md-6 col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('event_from'); ?><small class="req"> *</small></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                        <input type="text" autocomplete="off" name="event_from" class="form-control pull-right event_from">
                                    </div>
                                </div>    
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('event_to'); ?><small class="req"> *</small></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                        <input type="text" autocomplete="off" name="event_to" class="form-control pull-right event_to">
                                    </div>
                                </div>    
                            </div>
                        </div>
                    </div>    
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('event_color'); ?></label>
                                <input type="hidden" name="eventcolor" autocomplete="off" id="eventcolor" class="form-control">
                            </div>    
                        </div>
                        <div class="col-md-12">
                           <div class="form-group"> 
                            <?php
$i      = 0;
$colors = '';
foreach ($event_colors as $color) {
    $color_selected_class = 'cpicker-small';
    if ($i == 0) {
        $color_selected_class = 'cpicker-big';
    }
    $colors .= "<div class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";
    $i++;
}
echo '<div class="cpicker-wrapper">';
echo $colors;
echo '</div>';
?>
                           </div> 
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="pt15 displayblock overflow-hidden w-100"><?php echo $this->lang->line('event_type'); ?></label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="public" id="public"><?php echo $this->lang->line('public'); ?>
                                </label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="private" checked id="private"><?php echo $this->lang->line('private'); ?>
                                </label>
                                <label class="radio-inline w-xs-45 ml-xs-0">
                                    <input type="radio" name="event_type" value="sameforall" id="public"><?php echo $this->lang->line('all'); ?> <?php echo json_decode($role)->name; ?>
                                </label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="protected" id="public"><?php echo $this->lang->line('protected'); ?>
                                </label>
                            </div>    
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <input type="submit" class="btn btn-primary submit_addevent pull-right" value="<?php echo $this->lang->line('save'); ?>"></div> 
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="viewEventModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('edit_event'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" method="post" id="updateevent_form" enctype="multipart/form-data" action="">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_title') ?></label>
                            <input class="form-control" name="title" placeholder="" id="event_title">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('description') ?></label>
                            <textarea name="description" class="form-control" placeholder="" id="event_desc"></textarea></div>
                      <div class="row">
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_from'); ?></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_from" class="form-control pull-right event_from">
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_to'); ?></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_to" class="form-control pull-right event_to">
                            </div>
                        </div>
                            </div>
                        <input type="hidden" name="eventid" id="eventid">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_color') ?></label>
                            <input type="hidden" name="eventcolor" autocomplete="off" placeholder="Event Color" id="event_color" class="form-control">
                        </div>
                        <div class="form-group col-md-12">
                            <?php
$i      = 0;
$colors = '';
foreach ($event_colors as $color) {
    $colorid              = trim($color, "#");
    $color_selected_class = 'cpicker-small';
    if ($i == 0) {
        $color_selected_class = 'cpicker-big';
    }
    $colors .= "<div id=" . $colorid . " class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";
    $i++;
}
echo '<div class="cpicker-wrapper selectevent">';
echo $colors;
echo '</div>';
?>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_type') ?></label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="public" id="public"><?php echo $this->lang->line('public') ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="private" id="private"><?php echo $this->lang->line('private') ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="sameforall" id="public"><?php echo $this->lang->line('all') ?> <?php echo json_decode($role)->name; ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="protected" id="public"><?php echo $this->lang->line('protected') ?>
                            </label>
                        </div>
                        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11">
                            <input type="submit" class="btn btn-primary submit_update pull-right" value="<?php echo $this->lang->line('save'); ?>">
                        </div>
                        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
<?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_delete')) {?>
                                <input type="button" id="delete_event" class="btn btn-primary submit_delete pull-right" value="<?php echo $this->lang->line('delete'); ?>">
<?php }?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () { 
    $('#viewEventModal,#newEventModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });
});
</script> 

<style>
    canvas {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
<script type="text/javascript">
 <?php if ($this->rbac->hasPrivilege('income_donut_graph', 'can_view') && ($this->module_lib->hasActive('income'))) {
    ?>
    new Chart(document.getElementById("doughnut-chart"), {
    type: 'doughnut',
            data: {
            labels: [<?php foreach ($incomegraph as $value) {?>"<?php echo $value['income_category']; ?>", <?php }?> ],
                    datasets: [
                    {
                    label: "Income",
                            backgroundColor: [<?php $s = 1;
    foreach ($incomegraph as $value) {
        ?>"<?php echo incomegraphColors($s++); ?>", <?php
if ($s == 8) {
            $s = 1;
        }
    }
    ?> ],
                            data: [<?php $s = 1;
    foreach ($incomegraph as $value) {
        ?><?php echo $value['total']; ?>, <?php }?>]
                    }
                    ]
            },
            options: {
            responsive: true,
                    circumference: Math.PI,
                    rotation: - Math.PI,
                    legend: {
                    position: 'top',
                    },
                    title: {
                    display: true,
                    },
                    animation: {
                    animateScale: true,
                            animateRotate: true
                    }
            }
    });
   <?php
}if (($this->rbac->hasPrivilege('expense_donut_graph', 'can_view')) && ($this->module_lib->hasActive('expense'))) {
    ?>
    new Chart(document.getElementById("doughnut-chart1"), {
    type: 'doughnut',
            data: {
            labels: [<?php foreach ($expensegraph as $value) {?>"<?php echo $value['exp_category']; ?>", <?php }?>],
                    datasets: [
                    {
                    label: "Population (millions)",
                            backgroundColor: [<?php $ss = 1;
    foreach ($expensegraph as $value) {
        ?>"<?php echo expensegraphColors($ss++); ?>", <?php
if ($ss == 8) {
            $ss = 1;
        }
    }
    ?>],
                            data: [<?php foreach ($expensegraph as $value) {?><?php echo $value['total']; ?>, <?php }?>]
                    }
                    ]
            },
            options: {
            responsive: true,
                    circumference: Math.PI,
                    rotation: - Math.PI,
                    legend: {
                    position: 'top',
                    },
                    title: {
                    display: true,
                    },
                    animation: {
                    animateScale: true,
                            animateRotate: true
                    }
            }
    });
<?php
}
if (($this->module_lib->hasActive('fees_collection')) || ($this->module_lib->hasActive('expense')) || ($this->module_lib->hasActive('income'))) {
    ?>
        $(function () {
        var areaChartOptions = {
        showScale: true,
                scaleShowGridLines: false,
                scaleGridLineColor: "rgba(0,0,0,.05)",
                scaleGridLineWidth: 1,
                scaleShowHorizontalLines: true,
                scaleShowVerticalLines: true,
                bezierCurve: true,
                bezierCurveTension: 0.3,
                pointDot: false,
                pointDotRadius: 4,
                pointDotStrokeWidth: 1,
                pointHitDetectionRadius: 20,
                datasetStroke: true,
                datasetStrokeWidth: 2,
                datasetFill: true,
                maintainAspectRatio: true,
                responsive: true
        };
        var bar_chart = "<?php echo $bar_chart ?>";
        var line_chart = "<?php echo $line_chart ?>";
         <?php
if ($this->rbac->hasPrivilege('fees_collection_and_expense_yearly_chart', 'can_view')) {
        ?>
        if (line_chart) {

        var lineChartCanvas = $("#lineChart").get(0).getContext("2d");
        var lineChart = new Chart(lineChartCanvas);
        var lineChartOptions = areaChartOptions;
        lineChartOptions.datasetFill = false;
        var yearly_collection_array = <?php echo json_encode($yearly_collection) ?>;
        var yearly_expense_array = <?php echo json_encode($yearly_expense) ?>;
        var total_month = <?php echo json_encode($total_month) ?>;
        /* jshint ignore:start */
        var areaChartData_expense_Income = {
        labels: total_month,
                datasets: [
                <?php if(($this->module_lib->hasActive('expense'))){?>												   
                {
                label: "Expense",
                        fillColor: "rgba(215, 44, 44, 0.7)",
                        strokeColor: "rgba(215, 44, 44, 0.7)",
                        pointColor: "rgba(233, 30, 99, 0.9)",
                        pointStrokeColor: "#c1c7d1",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(220,220,220,1)",
                        data: yearly_expense_array
                },
                <?php } ?>
            <?php if(($this->module_lib->hasActive('income'))){?>
            {
            label: "Collection",
                    fillColor: "rgba(102, 170, 24, 0.6)",
                    strokeColor: "rgba(102, 170, 24, 0.6)",
                    pointColor: "rgba(102, 170, 24, 0.9)",
                    pointStrokeColor: "rgba(102, 170, 24, 0.6)",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(60,141,188,1)",
                    data: yearly_collection_array
            }
             <?php } ?>
            ]
        };
        lineChart.Line(areaChartData_expense_Income, lineChartOptions);
        }
        /* jshint ignore:end */

        var current_month_days = <?php echo json_encode($current_month_days) ?>;
        var days_collection = <?php echo json_encode($days_collection) ?>;
        var days_expense = <?php echo json_encode($days_expense) ?>;
        /* jshint ignore:start */
        var areaChartData_classAttendence = {
        labels: current_month_days,
                datasets: [
				 <?php if(($this->module_lib->hasActive('income'))){?>												  
                {
                label: "Electronics",
                        fillColor: "rgba(102, 170, 24, 0.6)",
                        strokeColor: "rgba(102, 170, 24, 0.6)",
                        pointColor: "rgba(102, 170, 24, 0.6)",
                        pointStrokeColor: "#c1c7d1",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(220,220,220,1)",
                        data: days_collection
                }<?php if(($this->module_lib->hasActive('expense'))){?>,<?php } ?>
                <?php } ?>
                <?php if(($this->module_lib->hasActive('expense'))){?>
                {
                label: "Digital Goods",
                        fillColor: "rgba(233, 30, 99, 0.9)",
                        strokeColor: "rgba(233, 30, 99, 0.9)",
                        pointColor: "rgba(233, 30, 99, 0.9)",
                        pointStrokeColor: "rgba(233, 30, 99, 0.9)",
                        pointHighlightFill: "rgba(233, 30, 99, 0.9)",
                        pointHighlightStroke: "rgba(60,141,188,1)",
                        data: days_expense
                }
                <?php } ?> 
                ]
        };
        /* jshint ignore:end */
         
        <?php }if ($this->rbac->hasPrivilege('fees_collection_and_expense_monthly_chart', 'can_view')) {?>
        if (bar_chart) {
            var current_month_days = <?php echo json_encode($current_month_days) ?>;
        var days_collection = <?php echo json_encode($days_collection) ?>;
        var days_expense = <?php echo json_encode($days_expense) ?>;

        /* jshint ignore:start */
        var areaChartData_classAttendence = {
        labels: current_month_days,
                datasets: [
                <?php if(($this->module_lib->hasActive('income'))){?>											 
                {
                label: "Electronics",
                        fillColor: "rgba(102, 170, 24, 0.6)",
                        strokeColor: "rgba(102, 170, 24, 0.6)",
                        pointColor: "rgba(102, 170, 24, 0.6)",
                        pointStrokeColor: "#c1c7d1",
                        pointHighlightFill: "#fff",
                        pointHighlightStroke: "rgba(220,220,220,1)",
                        data: days_collection
                }<?php if(($this->module_lib->hasActive('expense'))){?>,<?php } ?>
                <?php } ?>
                <?php if(($this->module_lib->hasActive('expense'))){ ?>
                ,{
                label: "Digital Goods",
                        fillColor: "rgba(233, 30, 99, 0.9)",
                        strokeColor: "rgba(233, 30, 99, 0.9)",
                        pointColor: "rgba(233, 30, 99, 0.9)",
                        pointStrokeColor: "rgba(233, 30, 99, 0.9)",
                        pointHighlightFill: "rgba(233, 30, 99, 0.9)",
                        pointHighlightStroke: "rgba(60,141,188,1)",
                        data: days_expense
                }
				<?php } ?> 
                ]
        };
        /* jshint ignore:end */
        var barChartCanvas = $("#barChart").get(0).getContext("2d");
        var barChart = new Chart(barChartCanvas);
        var barChartData = areaChartData_classAttendence;
        // barChartData.datasets[1].fillColor = "rgba(233, 30, 99, 0.9)";
        // barChartData.datasets[1].strokeColor = "rgba(233, 30, 99, 0.9)";
        // barChartData.datasets[1].pointColor = "rgba(233, 30, 99, 0.9)";
        var barChartOptions = {
        scaleBeginAtZero: true,
                scaleShowGridLines: true,
                scaleGridLineColor: "rgba(0,0,0,.05)",
                scaleGridLineWidth: 1,
                scaleShowHorizontalLines: false,
                scaleShowVerticalLines: false,
                barShowStroke: true,
                barStrokeWidth: 2,
                barValueSpacing: 5,
                barDatasetSpacing: 1,
                responsive: true,
                maintainAspectRatio: true
        };
        barChartOptions.datasetFill = false;
        barChart.Bar(barChartData, barChartOptions);
        }
         <?php }?>
        });
    <?php
}
?>

    $(document).ready(function () {
        $(document).on('click', '.close_notice', function () {
        var data = $(this).data();
        $.ajax({
        type: "POST",
                url: base_url + "admin/notification/read",
                data: {'notice': data.noticeid},
                dataType: "json",
                success: function (data) {
                if (data.status == "fail") {

                errorMsg(data.msg);
                } else {
                successMsg(data.msg);
                }

                }
        });
        });

        // Force flex-wrap: nowrap for equal-height-row elements
        $('.equal-height-row').css('flex-wrap', 'nowrap');
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Function to update ticker animation properties
        function updateTickerAnimation() {
            $('.birthday-ticker-content').each(function() {
                var $tickerContent = $(this);
                
                // Temporarily pause animation and reset transform to measure true scrollHeight
                // Also set height to auto temporarily to get natural scrollHeight
                $tickerContent.css({
                    'animation-play-state': 'paused',
                    'transform': 'translateY(0)',
                    'height': 'auto' // Allow content to determine height for measurement
                });

                // Calculate the height of one full set of unique items
                // The content is duplicated, so scrollHeight contains two sets of data
                var totalContentHeight = $tickerContent[0].scrollHeight;
                var singleCycleHeight = totalContentHeight / 2;
                console.log('Ticker Element:', $tickerContent);
                console.log('Total Content Height:', totalContentHeight);
                console.log('Single Cycle Height:', singleCycleHeight);

                // Set the CSS variables for translation and duration
                $tickerContent.css({
                    '--ticker-translate-y': -singleCycleHeight + 'px',
                    '--ticker-duration': '20s' // Enforce 20s duration dynamically
                });

                // Set explicit height to prevent reflow during animation
                // This ensures the 200% logic works as intended from the CSS perspective
                $tickerContent.css('height', totalContentHeight + 'px'); 

                // Resume animation
                $tickerContent.css('animation-play-state', 'running');
            });
        }

        // Run on document ready
        updateTickerAnimation();

        // Run on window resize, with a debounce for performance
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                updateTickerAnimation();
            }, 250); // Debounce to prevent excessive calls
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $widget = $('#fees-overview-widget');
        if (!$widget.length) {
            return;
        }

        var url = $widget.data('url');
        if (!url) {
            return;
        }

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json'
        }).done(function(resp) {
            if (!resp || resp.status !== 'success' || !resp.data) {
                return;
            }

            var d = resp.data;
            $widget.find('.fo-total-unpaid').text(d.total_unpaid);
            $widget.find('.fo-unpaid-progress').text(d.unpaid_progress);
            $widget.find('.fo-unpaid-sum').text(d.unpaid_sum_formatted);
            $widget.find('.fo-unpaid-bar').css('width', d.unpaid_progress + '%');

            $widget.find('.fo-total-partial').text(d.total_partial);
            $widget.find('.fo-partial-progress').text(d.partial_progress);
            $widget.find('.fo-partial-sum').text(d.partial_sum_formatted);
            $widget.find('.fo-partial-bar').css('width', d.partial_progress + '%');

            $widget.find('.fo-total-paid').text(d.total_paid);
            $widget.find('.fo-paid-progress').text(d.paid_progress);
            $widget.find('.fo-paid-sum').text(d.paid_sum_formatted);
            $widget.find('.fo-paid-bar').css('width', d.paid_progress + '%');

            $widget.find('.fo-total-demand').text(d.total_demand_formatted);
            $widget.find('.fo-total-collection').text(d.total_collection_formatted);
            $widget.find('.fo-total-awaiting').text(d.total_awaiting_formatted);

            $('.fees-awaiting-amount').text(d.fees_awaiting_total_net_balance_formatted);

            $widget.find('.fo-skeleton').removeClass('fo-skeleton');

            $('.fees-awaiting-amount').removeClass('fo-skeleton');

            var $awaitingBar = $('.fees-awaiting-progress-bar');
            if ($awaitingBar.length && typeof d.fees_awaiting_progress !== 'undefined') {
                $awaitingBar.css('width', d.fees_awaiting_progress + '%');
            }
        }).fail(function() {
            $widget.find('.fo-skeleton').removeClass('fo-skeleton');
        });
    });
</script>