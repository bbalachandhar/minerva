<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($sch_name) ? htmlspecialchars($sch_name) : 'Scholarship Portal'; ?> | Scholarship Exam Portal</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php echo $this->setting_model->getAdminsmalllogo(); ?>" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
    <?php $this->load->view('layout/theme'); ?>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/ionicons.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/sweet-alert/sweetalert2.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/custom_style.css">
    <link href="<?php echo base_url(); ?>backend/toast-alert/toastr.css" rel="stylesheet"/>
    <script src="<?php echo base_url(); ?>backend/custom/jquery.min.js"></script>
    <script src="<?php echo base_url(); ?>backend/js/school-custom.js"></script>
    <script src="<?php echo base_url(); ?>backend/js/sstoast.js"></script>
</head>
<body class="hold-transition skin-purple fixed sidebar-mini">
<script>
    function collapseSidebar() {
        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed1'))) {
            sessionStorage.setItem('sidebar-toggle-collapsed1', '');
        } else {
            sessionStorage.setItem('sidebar-toggle-collapsed1', '1');
        }
    }
    function checksidebar() {
        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed1'))) {
            document.getElementsByTagName('body')[0].className += ' sidebar-collapse';
        }
    }
    checksidebar();
</script>
<div class="wrapper">
    <header class="main-header">
        <a href="<?php echo base_url('scholarship_dashboard'); ?>" class="logo" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
            <span class="logo-mini"><i class="fa fa-graduation-cap"></i></span>
            <span class="logo-lg"><i class="fa fa-graduation-cap"></i> <?php echo isset($sch_name) ? htmlspecialchars($sch_name) : 'Scholarship'; ?></span>
        </a>
        <nav class="navbar navbar-static-top" role="navigation">
            <a onclick="collapseSidebar()" href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-user-circle-o"></i>
                            <span class="hidden-xs">
                                <?php
                                $ai = isset($applicant_info) ? $applicant_info : null;
                                echo $ai ? htmlspecialchars($ai->firstname . ' ' . ($ai->lastname ?? '')) : 'Applicant';
                                ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                                <i class="fa fa-user-circle fa-5x"></i>
                                <p>
                                    <?php echo $ai ? htmlspecialchars($ai->firstname . ' ' . ($ai->lastname ?? '')) : 'Applicant'; ?>
                                    <small>Ref: <?php echo $ai ? htmlspecialchars($ai->reference_no) : ''; ?></small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-right">
                                    <a href="<?php echo base_url('scholarship_dashboard/logout'); ?>" class="btn btn-default btn-flat">
                                        <i class="fa fa-sign-out"></i> Logout
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar" style="background:#1a1a2e;">
        <section class="sidebar">
            <div style="padding:14px 12px;background:rgba(255,255,255,.05);margin-bottom:8px;">
                <strong class="text-light" style="color:#e0e0e0;"><?php echo $ai ? htmlspecialchars($ai->firstname . ' ' . ($ai->lastname ?? '')) : 'Applicant'; ?></strong><br>
                <span style="font-size:11px;color:rgba(255,255,255,.5);">Ref: <?php echo $ai ? htmlspecialchars($ai->reference_no) : ''; ?></span>
            </div>

            <ul class="sidebar-menu" id="sibe-box">
                <li class="header" style="color:rgba(255,255,255,.4);">SCHOLARSHIP EXAM PORTAL</li>

                <li class="<?php echo (uri_string() == 'scholarship_dashboard' || uri_string() == 'scholarship_dashboard/index') ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('scholarship_dashboard'); ?>">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>

                <li class="<?php echo (strpos(uri_string(), 'scholarship_dashboard/exam_list') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('scholarship_dashboard/exam_list'); ?>">
                        <i class="fa fa-pencil-square-o"></i> <span>My Exams</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo base_url('scholarship_dashboard/logout'); ?>">
                        <i class="fa fa-sign-out"></i> <span>Logout</span>
                    </a>
                </li>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
