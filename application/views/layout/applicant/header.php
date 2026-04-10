<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($sch_name) ? htmlspecialchars($sch_name) : 'Applicant Portal'; ?> | Applicant Portal</title>
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
    <style>
        .applicant-sidebar-user {
            padding: 10px;
            background: rgba(255,255,255,0.1);
            margin-bottom: 10px;
        }
        .applicant-sidebar-user .ref-no {
            font-size: 11px;
            color: rgba(255,255,255,0.6);
        }
    </style>
</head>
<body class="hold-transition skin-blue fixed sidebar-mini">
<script>
    function collapseSidebar() {
        var body = document.getElementsByTagName('body')[0];
        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed1'))) {
            sessionStorage.setItem('sidebar-toggle-collapsed1', '');
        } else {
            sessionStorage.setItem('sidebar-toggle-collapsed1', '1');
        }
    }
    function checksidebar() {
        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed1'))) {
            var body = document.getElementsByTagName('body')[0];
            body.className = body.className + ' sidebar-collapse';
        }
    }
    checksidebar();
</script>
<div class="wrapper">
    <header class="main-header">
        <a href="<?php echo base_url('public_admission/applicant_dashboard'); ?>" class="logo">
            <span class="logo-mini"><b>AP</b></span>
            <span class="logo-lg"><b><?php echo isset($sch_name) ? htmlspecialchars($sch_name) : 'Applicant Portal'; ?></b></span>
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
                                $applicant_info_hdr = isset($applicant_info) ? $applicant_info : null;
                                echo $applicant_info_hdr ? htmlspecialchars($applicant_info_hdr->firstname . ' ' . $applicant_info_hdr->lastname) : 'Applicant';
                                ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header" style="background-color:#367fa9;">
                                <i class="fa fa-user-circle fa-5x"></i>
                                <p>
                                    <?php echo $applicant_info_hdr ? htmlspecialchars($applicant_info_hdr->firstname . ' ' . $applicant_info_hdr->lastname) : 'Applicant'; ?>
                                    <small>Ref: <?php echo $applicant_info_hdr ? htmlspecialchars($applicant_info_hdr->reference_no) : ''; ?></small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                </div>
                                <div class="pull-right">
                                    <a href="<?php echo base_url('public_admission/applicant_logout'); ?>" class="btn btn-default btn-flat">
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

    <aside class="main-sidebar">
        <section class="sidebar">
            <!-- Applicant info box -->
            <div class="applicant-sidebar-user">
                <strong class="text-light"><?php echo $applicant_info_hdr ? htmlspecialchars($applicant_info_hdr->firstname . ' ' . $applicant_info_hdr->lastname) : 'Applicant'; ?></strong><br>
                <span class="ref-no">Ref: <?php echo $applicant_info_hdr ? htmlspecialchars($applicant_info_hdr->reference_no) : ''; ?></span>
            </div>

            <ul class="sidebar-menu" id="sibe-box">
                <li class="header">APPLICANT PORTAL</li>

                <li class="<?php echo (strpos(uri_string(), 'applicant_dashboard') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('public_admission/applicant_dashboard'); ?>">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>

                <?php if (isset($applicant_info_hdr) && $applicant_info_hdr): ?>
                <li class="<?php echo (strpos(uri_string(), 'online_admission_review') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('welcome/online_admission_review/' . $applicant_info_hdr->reference_no); ?>">
                        <i class="fa fa-file-text-o"></i> <span>Application Form</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="<?php echo (strpos(uri_string(), 'exam_list') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('public_admission/exam_list'); ?>">
                        <i class="fa fa-pencil-square-o"></i> <span>Online Exams</span>
                    </a>
                </li>

                <li class="<?php echo (strpos(uri_string(), 'payment_history') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('public_admission/payment_history'); ?>">
                        <i class="fa fa-credit-card"></i> <span>Payment History</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo base_url('public_admission/applicant_logout'); ?>">
                        <i class="fa fa-sign-out"></i> <span>Logout</span>
                    </a>
                </li>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
