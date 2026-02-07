        <?php if (isset($extra_students) && count($extra_students) > 0) { ?>
            <div class="alert alert-warning" style="margin-top:10px;">
                <strong>Extra students (not male/female):</strong>
                <ul style="max-height:120px;overflow:auto;">
                    <?php foreach ($extra_students as $stu) { ?>
                        <li><?php echo htmlspecialchars($stu['firstname'].' '.$stu['lastname'].' (ID: '.$stu['id'].', Gender: '.($stu['gender'] ?: 'Not specified').')'); ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
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
            .chart-async {
                position: relative;
                min-height: 120px;
            }
            .chart-async.is-loading canvas {
                opacity: 0.35;
            }
            .chart-async-loader {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: none;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.7);
                z-index: 2;
            }
            .chart-async-spinner {
                width: 28px;
                height: 28px;
                border-radius: 50%;
                border: 3px solid rgba(60, 141, 188, 0.2);
                border-top-color: rgba(60, 141, 188, 0.9);
                animation: chart-spin 0.9s linear infinite;
            }
            @keyframes chart-spin {
                to { transform: rotate(360deg); }
            }
            .chart-async.is-loading .chart-async-loader {
                display: flex;
            }
            .widget-header-color {
                color: #fff;
                padding: 8px 10px;
                border-radius: 4px;
            }
            .widget-header-student-bday {
                background: #5c6bc0;
            }
            .widget-header-staff-bday {
                background: #26a69a;
            }
            .widget-header-student-att {
                background: #42a5f5;
            }
            .widget-header-staff-att {
                background: #ff7043;
            }
            .widget-header-enquiry {
                background: #7e57c2;
            }
        </style>
        <div class="row equal-height-row">
            <div class="col-md-3 col-sm-6 mb10">
                <div class="topprograssstart flex-card" id="student-birthday-widget" data-url="<?php echo site_url('admin/admin/student_birthdays_widget'); ?>">
                    <h5 class="pro-border widget-header-color widget-header-student-bday">Students Today's Birthday - <span class="student-birthday-count">0</span></h5>
                    <div class="birthday-ticker-container birthday-widget-body">
                        <div class="fo-skeleton fo-line" style="width:80%;margin:10px auto;"></div>
                        <div class="fo-skeleton fo-line" style="width:70%;margin:10px auto;"></div>
                        <div class="fo-skeleton fo-line" style="width:60%;margin:10px auto;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb10">
                <div class="topprograssstart flex-card" id="staff-birthday-widget" data-url="<?php echo site_url('admin/admin/staff_birthdays_widget'); ?>">
                    <h5 class="pro-border widget-header-color widget-header-staff-bday">Current Week Staff Birthdays - <span class="staff-birthday-count">0</span></h5>
                    <div class="birthday-ticker-container birthday-widget-body">
                        <div class="fo-skeleton fo-line" style="width:80%;margin:10px auto;"></div>
                        <div class="fo-skeleton fo-line" style="width:70%;margin:10px auto;"></div>
                        <div class="fo-skeleton fo-line" style="width:60%;margin:10px auto;"></div>
                    </div>
                </div>
            </div>
            <?php
            if ($this->module_lib->hasActive('student_attendance')) {
                if ($this->rbac->hasPrivilege('today_attendance_widegts', 'can_view')) {
                    ?>
                                <div class="col-md-2 col-sm-6 mb10">
                                    <div class="topprograssstart flex-card" id="student-attendance-widget" data-url="<?php echo site_url('admin/admin/student_today_attendance_widget'); ?>">
                                        <h5 class="pro-border widget-header-color widget-header-student-att"> <?php echo $this->lang->line('student_today_attendance'); ?></h5>
                                        <p class="text-uppercase mt10 clearfix"><span class="sta-present-count fo-skeleton">0</span> <?php echo $this->lang->line('present'); ?><span class="pull-right"><span class="sta-present-percent fo-skeleton">0%</span></span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sta-present-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="sta-late-count fo-skeleton">0</span> <?php echo $this->lang->line('late') ?><span class="pull-right"><span class="sta-late-percent fo-skeleton">0%</span></span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sta-late-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="sta-absent-count fo-skeleton">0</span> <?php echo $this->lang->line('absent'); ?><span class="pull-right"><span class="sta-absent-percent fo-skeleton">0%</span></span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sta-absent-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="sta-halfday-count fo-skeleton">0</span> <?php echo $this->lang->line('half_day'); ?><span class="pull-right"><span class="sta-halfday-percent fo-skeleton">0%</span></span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sta-halfday-bar" style="width: 0%"></div>
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
                                    <div class="topprograssstart flex-card" id="staff-attendance-widget" data-url="<?php echo site_url('admin/admin/staff_today_attendance_widget'); ?>">
                                        <h5 class="pro-border widget-header-color widget-header-staff-att"> Staff Today Attendance</h5>
                                        <p class="text-uppercase mt10 clearfix"><span class="sfa-present-count fo-skeleton">0</span> Present<span class="pull-right"><span class="sfa-present-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sfa-present-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="sfa-late-count fo-skeleton">0</span> Late<span class="pull-right"><span class="sfa-late-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sfa-late-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="sfa-absent-count fo-skeleton">0</span> Absent<span class="pull-right"><span class="sfa-absent-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sfa-absent-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="sfa-halfday-count fo-skeleton">0</span> Half Day<span class="pull-right"><span class="sfa-halfday-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sfa-halfday-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="sfa-permission-count fo-skeleton">0</span> Permissions<span class="pull-right"><span class="sfa-permission-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar sfa-permission-bar" style="width: 0%"></div>
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
                                    <div class="topprograssstart flex-card" id="enquiry-overview-widget" data-url="<?php echo site_url('admin/admin/enquiry_overview_widget'); ?>">
                                        <h5 class="pro-border widget-header-color widget-header-enquiry"><?php echo $this->lang->line('enquiry_overview'); ?></h5>
                                        <p class="text-uppercase mt10 clearfix"><span class="eo-active-count fo-skeleton">0</span> <?php echo $this->lang->line('active') ?><span class="pull-right"><span class="eo-active-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-red eo-active-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="eo-won-count fo-skeleton">0</span> <?php echo $this->lang->line('won') ?><span class="pull-right"><span class="eo-won-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-yellow eo-won-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="eo-passive-count fo-skeleton">0</span> <?php echo $this->lang->line('passive') ?><span class="pull-right"><span class="eo-passive-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-yellow eo-passive-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="eo-lost-count fo-skeleton">0</span> <?php echo $this->lang->line('lost') ?><span class="pull-right"><span class="eo-lost-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-yellow eo-lost-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-uppercase mt10 clearfix"><span class="eo-dead-count fo-skeleton">0</span> <?php echo $this->lang->line('dead'); ?><span class="pull-right"><span class="eo-dead-percent fo-skeleton">0</span>%</span>
                                        </p>
                                        <div class="progress-group">
                                            <div class="progress progress-minibar">
                                                <div class="progress-bar progress-bar-yellow eo-dead-bar" style="width: 0%"></div>
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
                                                                <div class="topprograssstart shadow" id="staff-approved-leave-widget" data-url="<?php echo site_url('admin/admin/staff_approved_leave_widget'); ?>">
                                                                    <p class="mt5 font14"><i class="fa fa-ioxhost ftlayer"></i><?php echo $this->lang->line('staff_approved_leave'); ?><span class="pull-right"><span class="sal-approved fo-skeleton">0</span>/<span class="sal-total fo-skeleton">0</span></span>
                                                                    </p>
                                                                    <div class="progress-group">
                                                                        <div class="progress progress-minibar">
                                                                            <div class="progress-bar progress-bar-lris-blue sal-progress" style="width: 0%"></div>
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
                        <div class="topprograssstart shadow" id="student-approved-leave-widget" data-url="<?php echo site_url('admin/admin/student_approved_leave_widget'); ?>">
                            <p class="mt5 font14"><i class="fa fa-ioxhost ftlayer"></i><?php echo $this->lang->line('student_approved_leave'); ?><span class="pull-right"><span class="stl-approved fo-skeleton">0</span>/<span class="stl-total fo-skeleton">0</span></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar stl-progress" style="width: 0%"></div>
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
                        <div class="topprograssstart" id="converted-leads-widget" data-url="<?php echo site_url('admin/admin/converted_leads_widget'); ?>">
                            <p class="mt5 clearfix font14"><i class="fa fa-ioxhost ftlayer"></i><?php echo $this->lang->line('converted_leads'); ?><span class="pull-right"><span class="cl-complete fo-skeleton">0</span>/<span class="cl-total fo-skeleton">0</span></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-red cl-progress" style="width: 0%"></div>
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
                                <div class="chart chart-async" id="fees-collection-expenses-monthly">
                                    <div class="chart-async-loader"><span class="chart-async-spinner"></span></div>
                                    <canvas id="barChart" height="98" data-url="<?php echo site_url('admin/admin/fees_collection_expenses_monthly_widget'); ?>"></canvas>
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
                                <div class="chart chart-async" id="fees-collection-expenses-session">
                                    <div class="chart-async-loader"><span class="chart-async-spinner"></span></div>
                                    <canvas id="lineChart" height="98" data-url="<?php echo site_url('admin/admin/fees_collection_expenses_session_widget'); ?>"></canvas>
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
                            <span style="font-size:12px;color:#888;">BAL SUM: <span class="fo-unpaid-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar fo-unpaid-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        <p class="text-uppercase mt10 clearfix">
                            <strong><?php echo $this->lang->line('partial'); ?>:</strong> <span class="fo-total-partial fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-partial-progress fo-skeleton">0</span>%</span><br/>
                            <span style="font-size:12px;color:#888;">BAL SUM: <span class="fo-partial-sum fo-skeleton fo-line">0</span></span>
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
                        <p class="text-uppercase mt10 clearfix">
                            <strong>Total Demand:</strong> <span class="fo-total-demand-count fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-demand-progress fo-skeleton">0</span>%</span><br>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-demand-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar fo-demand-bar fo-skeleton progress-bar-blue" style="width: 0%"></div>
                            </div>
                        </div>
                        <p class="text-uppercase mt10 clearfix">
                            <strong>Total Collection:</strong> <span class="fo-total-collection-count fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-collection-progress fo-skeleton">0</span>%</span><br>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-collection-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar fo-collection-bar fo-skeleton progress-bar-green" style="width: 0%"></div>
                            </div>
                        </div>
                        <p class="text-uppercase mt10 clearfix">
                            <strong>Total Awaiting:</strong> <span class="fo-total-awaiting-count fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-awaiting-progress fo-skeleton">0</span>%</span><br>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-awaiting-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar fo-awaiting-bar fo-skeleton progress-bar-yellow" style="width: 0%"></div>
                            </div>
                        </div>
                        <p class="text-uppercase mt10 clearfix">
                            <strong>Last Year Pending Demand:</strong> <span class="fo-total-cfdemand-count fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-cfdemand-progress fo-skeleton">0</span>%</span><br>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-cfdemand-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar fo-cfdemand-bar fo-skeleton progress-bar-red" style="width: 0%"></div>
                            </div>
                        </div>
                        <p class="text-uppercase mt10 clearfix">
                            <strong>Last Year Pending Collection:</strong> <span class="fo-total-cfcollection-count fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-cfcollection-progress fo-skeleton">0</span>%</span><br>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-cfcollection-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar fo-cfcollection-bar fo-skeleton progress-bar-purple" style="width: 0%"></div>
                            </div>
                        </div>
                        <p class="text-uppercase mt10 clearfix">
                            <strong>Last Year Pending:</strong> <span class="fo-total-cfbalance-count fo-skeleton">0</span>
                            <span class="pull-right"><span class="fo-cfbalance-progress fo-skeleton">0</span>%</span><br>
                            <span style="font-size:12px;color:#888;">Sum: <span class="fo-cfbalance-sum fo-skeleton fo-line">0</span></span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar fo-cfbalance-bar fo-skeleton progress-bar-orange" style="width: 0%"></div>
                                .progress-bar-blue { background-color: #007bff !important; }
                                .progress-bar-green { background-color: #28a745 !important; }
                                .progress-bar-yellow { background-color: #ffc107 !important; }
                                .progress-bar-red { background-color: #dc3545 !important; }
                                .progress-bar-purple { background-color: #6f42c1 !important; }
                                .progress-bar-orange { background-color: #fd7e14 !important; }
                            </div>
                        </div>
                    </div><!--./topprograssstart-->
                </div><!--./col-md-3-->
                <?php
            }
        }
        
        if ($this->rbac->hasPrivilege('student_head_count_widget', 'can_view')) {
        ?>
            <div class="col-md-2 col-sm-6 mb10">
                <div class="topprograssstart flex-card" id="student-headcount-widget" data-url="<?php echo site_url('admin/admin/student_head_count_widget'); ?>">
                    <h5 class="pro-border"><?php echo $this->lang->line('student_head_count'); ?> <span class="pull-right shc-total fo-skeleton" style="font-size: 18px; font-weight: bold;">0</span></h5>
                    <p class="text-uppercase mt10 clearfix" style="font-size: 12px;">
                        <i class="fa fa-male" style="color: #3c8dbc;"></i> Male: <span class="shc-male-count fo-skeleton">0</span> <span class="pull-right"><span class="shc-male-percent fo-skeleton">0</span>%</span>
                    </p>
                    <div class="progress-group">
                        <div class="progress progress-minibar">
                            <div class="progress-bar shc-male-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    <p class="text-uppercase mt10 clearfix" style="font-size: 12px;">
                        <i class="fa fa-female" style="color: #dd4b39;"></i> Female: <span class="shc-female-count fo-skeleton">0</span> <span class="pull-right"><span class="shc-female-percent fo-skeleton">0</span>%</span>
                    </p>
                    <div class="progress-group">
                        <div class="progress progress-minibar">
                            <div class="progress-bar progress-bar-red shc-female-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="widget-others-under-female shc-others" style="margin-top: 10px; display: none;">
                        <p class="text-uppercase mt10 clearfix" style="font-size: 12px;">
                            <i class="fa fa-genderless" style="color: #f39c12;"></i> Others: <span class="shc-other-count">0</span>
                            <span class="pull-right"><span class="shc-other-percent">0</span>%</span>
                        </p>
                        <div class="progress-group">
                            <div class="progress progress-minibar">
                                <div class="progress-bar progress-bar-yellow shc-other-bar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div><!--./topprograssstart-->
            </div><!--./col-md-2-->
        <?php } ?>
        
        <?php if ($this->module_lib->hasActive('library')) {
            if ($this->rbac->hasPrivilege('book_overview_widegts', 'can_view')) {
                ?>
                            <div class="col-md-2 col-sm-6 mb10">
                                <div class="topprograssstart flex-card" id="library-overview-widget" data-url="<?php echo site_url('admin/admin/library_overview_widget'); ?>">
                                    <h5 class="pro-border"><?php echo $this->lang->line('library_overview'); ?></h5>
                                    <p class="text-uppercase mt10 clearfix"><span class="lib-dueforreturn fo-skeleton">0</span> <?php echo $this->lang->line('due_for_return'); ?><span class="pull-right"></span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-green lib-dueforreturn-bar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <p class="text-uppercase mt10 clearfix"><span class="lib-forreturn fo-skeleton">0</span> <?php echo $this->lang->line('returned') ?><span class="pull-right"></span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-green lib-forreturn-bar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <p class="text-uppercase mt10 clearfix"><span class="lib-total-issued fo-skeleton">0</span> <?php echo $this->lang->line('issued_out_of'); ?> <span class="lib-total fo-skeleton">0</span><span class="pull-right"><span class="lib-issued-progress fo-skeleton">0</span>%</span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-green lib-issued-bar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <p class="text-uppercase mt10 clearfix"><span class="lib-availble fo-skeleton">0</span> <?php echo $this->lang->line('available_out_of') ?> <span class="lib-total fo-skeleton">0</span><span class="pull-right"><span class="lib-availble-progress fo-skeleton">0</span>%</span>
                                    </p>
                                    <div class="progress-group">
                                        <div class="progress progress-minibar">
                                            <div class="progress-bar progress-bar-green lib-availble-bar" style="width: 0%"></div>
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
                                    <div class="info-box" id="monthly-fees-collection-widget" data-url="<?php echo site_url('admin/admin/monthly_fees_collection_widget'); ?>">
                                        <a href="<?php echo site_url('studentfee') ?>">
                                            <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo $this->lang->line('monthly_fees_collection'); ?></span>
                                                <span class="info-box-number mfc-amount fo-skeleton">0</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
    <?php }
}
?>
<?php
if ($this->module_lib->hasActive('income')) {
    if ($this->rbac->hasPrivilege('monthly_income_widget', 'can_view')) {
        ?>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <div class="info-box" id="monthly-income-widget" data-url="<?php echo site_url('admin/admin/monthly_income_widget'); ?>">
                                        <a href="<?php echo site_url('admin/income') ?>">
                                            <span class="info-box-icon"><i class="fa fa-bank"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo 'Monthly ' . $this->lang->line('income'); ?></span>
                                                <span class="info-box-number mi-amount fo-skeleton">0</span>
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
                                    <div class="info-box" id="monthly-expense-widget" data-url="<?php echo site_url('admin/admin/monthly_expense_widget'); ?>">
                                        <a href="<?php echo site_url('admin/expense') ?>">
                                            <span class="info-box-icon"><i class="fa fa-credit-card"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text"><?php echo $this->lang->line('monthly_expenses'); ?></span>
                                                <span class="info-box-number me-amount fo-skeleton">0</span>
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
        var hasIncome = <?php echo ($this->module_lib->hasActive('income')) ? 'true' : 'false'; ?>;
        var hasExpense = <?php echo ($this->module_lib->hasActive('expense')) ? 'true' : 'false'; ?>;

        if (line_chart) {
            var $lineCanvas = $("#lineChart");
            var lineUrl = $lineCanvas.data('url');
            if ($lineCanvas.length && lineUrl) {
                var $lineWrapper = $lineCanvas.closest('.chart-async');
                $lineWrapper.addClass('is-loading');
                $.ajax({
                    url: lineUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var labels = resp.data.labels || [];
                    var collection = resp.data.collection || [];
                    var expense = resp.data.expense || [];

                    var datasets = [];
                    if (hasExpense) {
                        datasets.push({
                            label: "Expense",
                            fillColor: "rgba(215, 44, 44, 0.7)",
                            strokeColor: "rgba(215, 44, 44, 0.7)",
                            pointColor: "rgba(233, 30, 99, 0.9)",
                            pointStrokeColor: "#c1c7d1",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(220,220,220,1)",
                            data: expense
                        });
                    }
                    if (hasIncome) {
                        datasets.push({
                            label: "Collection",
                            fillColor: "rgba(102, 170, 24, 0.6)",
                            strokeColor: "rgba(102, 170, 24, 0.6)",
                            pointColor: "rgba(102, 170, 24, 0.9)",
                            pointStrokeColor: "rgba(102, 170, 24, 0.6)",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(60,141,188,1)",
                            data: collection
                        });
                    }

                    var areaChartData_expense_Income = {
                        labels: labels,
                        datasets: datasets
                    };

                    var lineChartCanvas = $lineCanvas.get(0).getContext("2d");
                    var lineChart = new Chart(lineChartCanvas);
                    var lineChartOptions = areaChartOptions;
                    lineChartOptions.datasetFill = false;
                    lineChart.Line(areaChartData_expense_Income, lineChartOptions);
                    $lineWrapper.removeClass('is-loading');
                }).fail(function() {
                    $lineWrapper.removeClass('is-loading');
                });
            }
        }

        if (bar_chart) {
            var $barCanvas = $("#barChart");
            var barUrl = $barCanvas.data('url');
            if ($barCanvas.length && barUrl) {
                var $barWrapper = $barCanvas.closest('.chart-async');
                $barWrapper.addClass('is-loading');
                $.ajax({
                    url: barUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var labels = resp.data.labels || [];
                    var collection = resp.data.collection || [];
                    var expense = resp.data.expense || [];

                    var datasets = [];
                    if (hasIncome) {
                        datasets.push({
                            label: "Electronics",
                            fillColor: "rgba(102, 170, 24, 0.6)",
                            strokeColor: "rgba(102, 170, 24, 0.6)",
                            pointColor: "rgba(102, 170, 24, 0.6)",
                            pointStrokeColor: "#c1c7d1",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(220,220,220,1)",
                            data: collection
                        });
                    }
                    if (hasExpense) {
                        datasets.push({
                            label: "Digital Goods",
                            fillColor: "rgba(233, 30, 99, 0.9)",
                            strokeColor: "rgba(233, 30, 99, 0.9)",
                            pointColor: "rgba(233, 30, 99, 0.9)",
                            pointStrokeColor: "rgba(233, 30, 99, 0.9)",
                            pointHighlightFill: "rgba(233, 30, 99, 0.9)",
                            pointHighlightStroke: "rgba(60,141,188,1)",
                            data: expense
                        });
                    }

                    var barChartData = {
                        labels: labels,
                        datasets: datasets
                    };

                    var barChartCanvas = $barCanvas.get(0).getContext("2d");
                    var barChart = new Chart(barChartCanvas);
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
                    $barWrapper.removeClass('is-loading');
                }).fail(function() {
                    $barWrapper.removeClass('is-loading');
                });
            }
        }
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
                // Add skeleton loading to new card fields on AJAX start
                $(document).ajaxStart(function() {
                    var $widget = $('#fees-overview-widget');
                    $widget.find('.fo-total-demand-count, .fo-demand-progress, .fo-demand-sum, .fo-demand-bar, .fo-total-collection-count, .fo-collection-progress, .fo-collection-sum, .fo-collection-bar, .fo-total-awaiting-count, .fo-awaiting-progress, .fo-awaiting-sum, .fo-awaiting-bar, .fo-total-cfdemand-count, .fo-cfdemand-progress, .fo-cfdemand-sum, .fo-cfdemand-bar, .fo-total-cfcollection-count, .fo-cfcollection-progress, .fo-cfcollection-sum, .fo-cfcollection-bar, .fo-total-cfbalance-count, .fo-cfbalance-progress, .fo-cfbalance-sum, .fo-cfbalance-bar').addClass('fo-skeleton');

                    var $headcount = $('#student-headcount-widget');
                    $headcount.find('.shc-total, .shc-male-count, .shc-male-percent, .shc-female-count, .shc-female-percent').addClass('fo-skeleton');

                    var $studentAttendance = $('#student-attendance-widget');
                    $studentAttendance.find('.sta-present-count, .sta-present-percent, .sta-late-count, .sta-late-percent, .sta-absent-count, .sta-absent-percent, .sta-halfday-count, .sta-halfday-percent').addClass('fo-skeleton');

                    var $staffAttendance = $('#staff-attendance-widget');
                    $staffAttendance.find('.sfa-present-count, .sfa-present-percent, .sfa-late-count, .sfa-late-percent, .sfa-absent-count, .sfa-absent-percent, .sfa-halfday-count, .sfa-halfday-percent, .sfa-permission-count, .sfa-permission-percent').addClass('fo-skeleton');

                    var $enquiryOverview = $('#enquiry-overview-widget');
                    $enquiryOverview.find('.eo-active-count, .eo-active-percent, .eo-won-count, .eo-won-percent, .eo-passive-count, .eo-passive-percent, .eo-lost-count, .eo-lost-percent, .eo-dead-count, .eo-dead-percent').addClass('fo-skeleton');

                    var $libraryOverview = $('#library-overview-widget');
                    $libraryOverview.find('.lib-dueforreturn, .lib-forreturn, .lib-total-issued, .lib-total, .lib-issued-progress, .lib-availble, .lib-availble-progress').addClass('fo-skeleton');

                    $('#monthly-fees-collection-widget .mfc-amount, #monthly-income-widget .mi-amount, #monthly-expense-widget .me-amount').addClass('fo-skeleton');

                    $('#staff-approved-leave-widget .sal-approved, #staff-approved-leave-widget .sal-total').addClass('fo-skeleton');
                    $('#student-approved-leave-widget .stl-approved, #student-approved-leave-widget .stl-total').addClass('fo-skeleton');
                    $('#converted-leads-widget .cl-complete, #converted-leads-widget .cl-total').addClass('fo-skeleton');
                });
                // Remove skeleton loading on AJAX complete
                $(document).ajaxStop(function() {
                    var $widget = $('#fees-overview-widget');
                    $widget.find('.fo-total-demand-count, .fo-demand-progress, .fo-demand-sum, .fo-demand-bar, .fo-total-collection-count, .fo-collection-progress, .fo-collection-sum, .fo-collection-bar, .fo-total-awaiting-count, .fo-awaiting-progress, .fo-awaiting-sum, .fo-awaiting-bar, .fo-total-cfdemand-count, .fo-cfdemand-progress, .fo-cfdemand-sum, .fo-cfdemand-bar, .fo-total-cfcollection-count, .fo-cfcollection-progress, .fo-cfcollection-sum, .fo-cfcollection-bar, .fo-total-cfbalance-count, .fo-cfbalance-progress, .fo-cfbalance-sum, .fo-cfbalance-bar').removeClass('fo-skeleton');

                    var $headcount = $('#student-headcount-widget');
                    $headcount.find('.shc-total, .shc-male-count, .shc-male-percent, .shc-female-count, .shc-female-percent').removeClass('fo-skeleton');

                    var $studentAttendance = $('#student-attendance-widget');
                    $studentAttendance.find('.sta-present-count, .sta-present-percent, .sta-late-count, .sta-late-percent, .sta-absent-count, .sta-absent-percent, .sta-halfday-count, .sta-halfday-percent').removeClass('fo-skeleton');

                    var $staffAttendance = $('#staff-attendance-widget');
                    $staffAttendance.find('.sfa-present-count, .sfa-present-percent, .sfa-late-count, .sfa-late-percent, .sfa-absent-count, .sfa-absent-percent, .sfa-halfday-count, .sfa-halfday-percent, .sfa-permission-count, .sfa-permission-percent').removeClass('fo-skeleton');

                    var $enquiryOverview = $('#enquiry-overview-widget');
                    $enquiryOverview.find('.eo-active-count, .eo-active-percent, .eo-won-count, .eo-won-percent, .eo-passive-count, .eo-passive-percent, .eo-lost-count, .eo-lost-percent, .eo-dead-count, .eo-dead-percent').removeClass('fo-skeleton');

                    var $libraryOverview = $('#library-overview-widget');
                    $libraryOverview.find('.lib-dueforreturn, .lib-forreturn, .lib-total-issued, .lib-total, .lib-issued-progress, .lib-availble, .lib-availble-progress').removeClass('fo-skeleton');

                    $('#monthly-fees-collection-widget .mfc-amount, #monthly-income-widget .mi-amount, #monthly-expense-widget .me-amount').removeClass('fo-skeleton');

                    $('#staff-approved-leave-widget .sal-approved, #staff-approved-leave-widget .sal-total').removeClass('fo-skeleton');
                    $('#student-approved-leave-widget .stl-approved, #student-approved-leave-widget .stl-total').removeClass('fo-skeleton');
                    $('#converted-leads-widget .cl-complete, #converted-leads-widget .cl-total').removeClass('fo-skeleton');
                });
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
        window.updateTickerAnimation = updateTickerAnimation;

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


            // Use new backend fields for each card
            $widget.find('.fo-total-demand-count').text(d.demand_count || 0);
            $widget.find('.fo-demand-progress').text(d.demand_progress);
            $widget.find('.fo-demand-sum').text(d.demand_sum_formatted);
            $widget.find('.fo-demand-bar').css('width', d.demand_progress + '%');

            $widget.find('.fo-total-collection-count').text(d.collection_count || 0);
            $widget.find('.fo-collection-progress').text(d.collection_progress);
            $widget.find('.fo-collection-sum').text(d.collection_sum_formatted);
            $widget.find('.fo-collection-bar').css('width', d.collection_progress + '%');

            $widget.find('.fo-total-awaiting-count').text(d.awaiting_count || 0);
            $widget.find('.fo-awaiting-progress').text(d.awaiting_progress);
            $widget.find('.fo-awaiting-sum').text(d.awaiting_sum_formatted);
            $widget.find('.fo-awaiting-bar').css('width', d.awaiting_progress + '%');

            $widget.find('.fo-total-cfdemand-count').text(d.cfdemand_count || 0);
            $widget.find('.fo-cfdemand-progress').text(d.cfdemand_progress);
            $widget.find('.fo-cfdemand-sum').text(d.cfdemand_sum_formatted);
            $widget.find('.fo-cfdemand-bar').css('width', d.cfdemand_progress + '%');

            $widget.find('.fo-total-cfcollection-count').text(d.cfcollection_count || 0);
            $widget.find('.fo-cfcollection-progress').text(d.cfcollection_progress);
            $widget.find('.fo-cfcollection-sum').text(d.cfcollection_sum_formatted);
            $widget.find('.fo-cfcollection-bar').css('width', d.cfcollection_progress + '%');

            $widget.find('.fo-total-cfbalance-count').text(d.cfbalance_count || 0);
            $widget.find('.fo-cfbalance-progress').text(d.cfbalance_progress);
            $widget.find('.fo-cfbalance-sum').text(d.cfbalance_sum_formatted);
            $widget.find('.fo-cfbalance-bar').css('width', d.cfbalance_progress + '%');

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
<script type="text/javascript">
    $(document).ready(function() {
        var $headcount = $('#student-headcount-widget');
        if (!$headcount.length) {
            return;
        }

        var url = $headcount.data('url');
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
            $headcount.find('.shc-total').text(d.total_students_heads || 0);

            $headcount.find('.shc-male-count').text(d.male_students || 0);
            $headcount.find('.shc-male-percent').text(d.male_percent || 0);
            $headcount.find('.shc-male-bar').css('width', (d.male_percent || 0) + '%');

            $headcount.find('.shc-female-count').text(d.female_students || 0);
            $headcount.find('.shc-female-percent').text(d.female_percent || 0);
            $headcount.find('.shc-female-bar').css('width', (d.female_percent || 0) + '%');

            if ((d.other_students || 0) > 0) {
                $headcount.find('.shc-others').show();
                $headcount.find('.shc-other-count').text(d.other_students || 0);
                $headcount.find('.shc-other-percent').text(d.other_percent || 0);
                $headcount.find('.shc-other-bar').css('width', (d.other_percent || 0) + '%');
            } else {
                $headcount.find('.shc-others').hide();
            }

            $headcount.find('.fo-skeleton').removeClass('fo-skeleton');
        }).fail(function() {
            $headcount.find('.fo-skeleton').removeClass('fo-skeleton');
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $studentAttendance = $('#student-attendance-widget');
        if ($studentAttendance.length) {
            var url = $studentAttendance.data('url');
            if (url) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var d = resp.data;
                    $studentAttendance.find('.sta-present-count').text(d.total_present || 0);
                    $studentAttendance.find('.sta-present-percent').text(d.present || '0%');
                    $studentAttendance.find('.sta-present-bar').css('width', d.present || '0%');

                    $studentAttendance.find('.sta-late-count').text(d.total_late || 0);
                    $studentAttendance.find('.sta-late-percent').text(d.late || '0%');
                    $studentAttendance.find('.sta-late-bar').css('width', d.late || '0%');

                    $studentAttendance.find('.sta-absent-count').text(d.total_absent || 0);
                    $studentAttendance.find('.sta-absent-percent').text(d.absent || '0%');
                    $studentAttendance.find('.sta-absent-bar').css('width', d.absent || '0%');

                    $studentAttendance.find('.sta-halfday-count').text(d.total_half_day || 0);
                    $studentAttendance.find('.sta-halfday-percent').text(d.half_day || '0%');
                    $studentAttendance.find('.sta-halfday-bar').css('width', d.half_day || '0%');

                    $studentAttendance.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $studentAttendance.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $staffAttendance = $('#staff-attendance-widget');
        if ($staffAttendance.length) {
            var staffUrl = $staffAttendance.data('url');
            if (staffUrl) {
                $.ajax({
                    url: staffUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var d = resp.data;
                    $staffAttendance.find('.sfa-present-count').text(d.total_present || 0);
                    $staffAttendance.find('.sfa-present-percent').text(d.present || 0);
                    $staffAttendance.find('.sfa-present-bar').css('width', (d.present || 0) + '%');

                    $staffAttendance.find('.sfa-late-count').text(d.total_late || 0);
                    $staffAttendance.find('.sfa-late-percent').text(d.late || 0);
                    $staffAttendance.find('.sfa-late-bar').css('width', (d.late || 0) + '%');

                    $staffAttendance.find('.sfa-absent-count').text(d.total_absent || 0);
                    $staffAttendance.find('.sfa-absent-percent').text(d.absent || 0);
                    $staffAttendance.find('.sfa-absent-bar').css('width', (d.absent || 0) + '%');

                    $staffAttendance.find('.sfa-halfday-count').text(d.total_half_day || 0);
                    $staffAttendance.find('.sfa-halfday-percent').text(d.half_day || 0);
                    $staffAttendance.find('.sfa-halfday-bar').css('width', (d.half_day || 0) + '%');

                    $staffAttendance.find('.sfa-permission-count').text(d.total_permission || 0);
                    $staffAttendance.find('.sfa-permission-percent').text(d.permission || 0);
                    $staffAttendance.find('.sfa-permission-bar').css('width', (d.permission || 0) + '%');

                    $staffAttendance.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $staffAttendance.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $enquiryOverview = $('#enquiry-overview-widget');
        if ($enquiryOverview.length) {
            var enquiryUrl = $enquiryOverview.data('url');
            if (enquiryUrl) {
                $.ajax({
                    url: enquiryUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var d = resp.data;
                    $enquiryOverview.find('.eo-active-count').text(d.active || 0);
                    $enquiryOverview.find('.eo-active-percent').text(d.active_progress || 0);
                    $enquiryOverview.find('.eo-active-bar').css('width', (d.active_progress || 0) + '%');

                    $enquiryOverview.find('.eo-won-count').text(d.won || 0);
                    $enquiryOverview.find('.eo-won-percent').text(d.won_progress || 0);
                    $enquiryOverview.find('.eo-won-bar').css('width', (d.won_progress || 0) + '%');

                    $enquiryOverview.find('.eo-passive-count').text(d.passive || 0);
                    $enquiryOverview.find('.eo-passive-percent').text(d.passive_progress || 0);
                    $enquiryOverview.find('.eo-passive-bar').css('width', (d.passive_progress || 0) + '%');

                    $enquiryOverview.find('.eo-lost-count').text(d.lost || 0);
                    $enquiryOverview.find('.eo-lost-percent').text(d.lost_progress || 0);
                    $enquiryOverview.find('.eo-lost-bar').css('width', (d.lost_progress || 0) + '%');

                    $enquiryOverview.find('.eo-dead-count').text(d.dead || 0);
                    $enquiryOverview.find('.eo-dead-percent').text(d.dead_progress || 0);
                    $enquiryOverview.find('.eo-dead-bar').css('width', (d.dead_progress || 0) + '%');

                    $enquiryOverview.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $enquiryOverview.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $libraryOverview = $('#library-overview-widget');
        if ($libraryOverview.length) {
            var libraryUrl = $libraryOverview.data('url');
            if (libraryUrl) {
                $.ajax({
                    url: libraryUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }

                    var d = resp.data;
                    $libraryOverview.find('.lib-dueforreturn').text(d.dueforreturn || 0);
                    $libraryOverview.find('.lib-dueforreturn-bar').css('width', (d.dueforreturn || 0) + '%');

                    $libraryOverview.find('.lib-forreturn').text(d.forreturn || 0);
                    $libraryOverview.find('.lib-forreturn-bar').css('width', (d.forreturn || 0) + '%');

                    $libraryOverview.find('.lib-total-issued').text(d.total_issued || 0);
                    $libraryOverview.find('.lib-total').text(d.total || 0);
                    $libraryOverview.find('.lib-issued-progress').text(d.issued_progress || 0);
                    $libraryOverview.find('.lib-issued-bar').css('width', (d.issued_progress || 0) + '%');

                    $libraryOverview.find('.lib-availble').text(d.availble || 0);
                    $libraryOverview.find('.lib-availble-progress').text(d.availble_progress || 0);
                    $libraryOverview.find('.lib-availble-bar').css('width', (d.availble_progress || 0) + '%');

                    $libraryOverview.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $libraryOverview.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $monthlyFees = $('#monthly-fees-collection-widget');
        if ($monthlyFees.length) {
            var feesUrl = $monthlyFees.data('url');
            if (feesUrl) {
                $.ajax({
                    url: feesUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $monthlyFees.find('.mfc-amount').text(resp.data.amount_formatted || '');
                    $monthlyFees.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $monthlyFees.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $monthlyIncome = $('#monthly-income-widget');
        if ($monthlyIncome.length) {
            var incomeUrl = $monthlyIncome.data('url');
            if (incomeUrl) {
                $.ajax({
                    url: incomeUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $monthlyIncome.find('.mi-amount').text(resp.data.amount_formatted || '');
                    $monthlyIncome.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $monthlyIncome.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $monthlyExpense = $('#monthly-expense-widget');
        if ($monthlyExpense.length) {
            var expenseUrl = $monthlyExpense.data('url');
            if (expenseUrl) {
                $.ajax({
                    url: expenseUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $monthlyExpense.find('.me-amount').text(resp.data.amount_formatted || '');
                    $monthlyExpense.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $monthlyExpense.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var $staffLeave = $('#staff-approved-leave-widget');
        if ($staffLeave.length) {
            var staffLeaveUrl = $staffLeave.data('url');
            if (staffLeaveUrl) {
                $.ajax({
                    url: staffLeaveUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $staffLeave.find('.sal-approved').text(resp.data.approved || 0);
                    $staffLeave.find('.sal-total').text(resp.data.total || 0);
                    $staffLeave.find('.sal-progress').css('width', (resp.data.percent || 0) + '%');
                    $staffLeave.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $staffLeave.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $studentLeave = $('#student-approved-leave-widget');
        if ($studentLeave.length) {
            var studentLeaveUrl = $studentLeave.data('url');
            if (studentLeaveUrl) {
                $.ajax({
                    url: studentLeaveUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $studentLeave.find('.stl-approved').text(resp.data.approved || 0);
                    $studentLeave.find('.stl-total').text(resp.data.total || 0);
                    $studentLeave.find('.stl-progress').css('width', (resp.data.percent || 0) + '%');
                    $studentLeave.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $studentLeave.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }

        var $convertedLeads = $('#converted-leads-widget');
        if ($convertedLeads.length) {
            var convertedUrl = $convertedLeads.data('url');
            if (convertedUrl) {
                $.ajax({
                    url: convertedUrl,
                    method: 'GET',
                    dataType: 'json'
                }).done(function(resp) {
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        return;
                    }
                    $convertedLeads.find('.cl-complete').text(resp.data.complete || 0);
                    $convertedLeads.find('.cl-total').text(resp.data.total || 0);
                    $convertedLeads.find('.cl-progress').css('width', (resp.data.percent || 0) + '%');
                    $convertedLeads.find('.fo-skeleton').removeClass('fo-skeleton');
                }).fail(function() {
                    $convertedLeads.find('.fo-skeleton').removeClass('fo-skeleton');
                });
            }
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        function loadBirthdayWidget($widget, countSelector) {
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
                if (!resp || resp.status !== 'success') {
                    return;
                }

                if (typeof resp.count !== 'undefined') {
                    $widget.find(countSelector).text(resp.count);
                }

                if (resp.html) {
                    $widget.find('.birthday-widget-body').html(resp.html);
                    if (typeof window.updateTickerAnimation === 'function') {
                        window.updateTickerAnimation();
                    }
                }
            });
        }

        loadBirthdayWidget($('#student-birthday-widget'), '.student-birthday-count');
        loadBirthdayWidget($('#staff-birthday-widget'), '.staff-birthday-count');
    });
</script>