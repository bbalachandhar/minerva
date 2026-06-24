<!DOCTYPE html>
<html <?php echo $this->customlib->getRTL(); ?>>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo (isset($title) && $title != '') ? $title : $this->customlib->getAppName(); ?></title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta http-equiv="Cache-control" content="no-cache">
        <meta name="theme-color" content="#424242" />
        <link href="<?php echo $this->customlib->getBaseUrl(); ?>uploads/school_content/admin_small_logo/<?php echo $this->setting_model->getAdminsmalllogo();?>" rel="shortcut icon" type="image/x-icon">

        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/toast-alert/toastr.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/sweet-alert/sweetalert2.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/jquery.mCustomScrollbar.min.css">
        <?php
$this->load->view('layout/theme');
?>

        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/ss-print.css">

        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/ionicons.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/iCheck/flat/blue.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/morris/morris.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/datepicker/datepicker3.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/datepicker/css/bootstrap-datetimepicker.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/colorpicker/bootstrap-colorpicker.css">

        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/daterangepicker/daterangepicker-bs3.css">
        <!-- Old timepicker CSS removed -->

        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/custom_style.css">


        <!--file dropify-->
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/dropify.min.css">
        <!--file nprogress-->
        <link href="<?php echo base_url(); ?>backend/dist/css/nprogress.css" rel="stylesheet">

        <!--print table-->
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/dataTables.bootstrap.min.css" rel="stylesheet">
        <!--print table mobile support-->
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/responsive.dataTables.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/rowReorder.dataTables.min.css" rel="stylesheet">
        <!--language css-->
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/0.8.2/css/flag-icon.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>backend/dist/css/bootstrap-select.min.css">
        <script src="<?php echo base_url(); ?>backend/custom/jquery.min.js"></script>
        <!-- Load Bootstrap JS early so it's available for school-custom.js -->
        <script src="<?php echo base_url(); ?>backend/bootstrap/js/bootstrap.min.js"></script>
        <script src="<?php echo base_url(); ?>backend/dist/js/moment.min.js"></script>
        <script type="text/javascript">
            var currentLanguage = <?php echo json_encode(isset($language_name) ? $language_name : 'en'); ?>;
            moment.locale(currentLanguage);
        </script>
        <script src="<?php echo base_url(); ?>backend/datepicker/js/bootstrap-datetimepicker.js"></script>


        <script src="<?php echo base_url(); ?>backend/dist/js/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="<?php echo base_url(); ?>backend/js/school-custom.js"></script>
        <script src="<?php echo base_url(); ?>backend/js/school-admin-custom.js?v=<?php echo time(); ?>"></script>
        <script src="<?php echo base_url(); ?>backend/toast-alert/toastr.js"></script>
        <script src="<?php echo base_url(); ?>backend/sweet-alert/sweetalert2.min.js"></script>
        <script src="<?php echo base_url(); ?>backend/js/sstoast.js"></script>
        <script src="<?php echo base_url(); ?>backend/js/export_lib.js?v=2"></script>

        <!-- fullCalendar -->
        <link rel="stylesheet" href="<?php echo base_url() ?>backend/fullcalendar/dist/fullcalendar.min.css">
        <link rel="stylesheet" href="<?php echo base_url() ?>backend/fullcalendar/dist/fullcalendar.print.min.css" media="print">
        <script type="text/javascript">
            var baseurl = "<?php echo base_url(); ?>";
            var start_week=<?php echo $this->customlib->getStartWeek(); ?>;
            // var chk_validate="<?php echo $this->config->item('SSLK') ?>"; // Commented out for debugging
        </script>

  <style type="text/css">
        span.flag-icon.flag-icon-us{text-orientation: mixed;}
  </style>
                    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/select2/css/select2.min.css">
                    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/modern-override.css">
                    </head>    <body class="skin-blue fixed sidebar-mini">

<!-- ========== Minerva Modern Header Styles ========== -->
<style>
/* --- Main Header Override --- */
.mn-header.main-header {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    min-height: 56px;
    height: 56px;
    overflow: visible;
    z-index: 1030;
}
.mn-header.main-header .logo {
    background: #ffffff !important;
    border-right: 1px solid #e5e7eb;
    height: 56px;
    line-height: 56px;
    padding: 0 12px;
}
.mn-header.main-header .logo:hover {
    background: #f9fafb !important;
}
.mn-header.main-header .logo .logo-mini img,
.mn-header.main-header .logo .logo-lg img {
    max-height: 38px;
    vertical-align: middle;
}
.mn-header .navbar {
    background: #ffffff !important;
    min-height: 56px;
    margin-left: 0;
}

/* --- Sidebar Toggle --- */
.mn-header .mn-sidebar-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    border-radius: 8px;
    color: #6b7280;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
    margin-right: 4px;
    flex-shrink: 0;
}
.mn-header .mn-sidebar-toggle:hover {
    background: #f3f4f6;
    color: #1f2937;
}
.mn-header .mn-sidebar-toggle .icon-bar {
    display: block;
    width: 20px;
    height: 2px;
    background-color: #6b7280;
    border-radius: 1px;
    transition: background 0.15s ease;
}
.mn-header .mn-sidebar-toggle .icon-bar + .icon-bar {
    margin-top: 4px;
}
.mn-header .mn-sidebar-toggle:hover .icon-bar {
    background-color: #1f2937;
}

/* --- Navbar Inner Layout --- */
.mn-header .mn-navbar-inner {
    display: flex;
    align-items: center;
    height: 56px;
    padding: 0 12px;
    gap: 8px;
}

/* --- Institution Name --- */
.mn-header .mn-institution-name {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 320px;
    line-height: 56px;
    flex-shrink: 1;
    min-width: 0;
}

/* --- Spacer --- */
.mn-header .mn-spacer {
    flex: 1 1 auto;
    min-width: 8px;
}

/* --- Search Bar --- */
.mn-header .mn-search-form {
    position: relative;
    flex-shrink: 0;
}
.mn-header .mn-search-form .mn-search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.mn-header .mn-search-form .mn-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
    pointer-events: none;
    z-index: 2;
    transition: color 0.15s ease;
}
.mn-header .mn-search-form .mn-search-input {
    width: 260px;
    height: 36px;
    padding: 0 14px 0 36px;
    background: #f3f4f6;
    border: 2px solid transparent;
    border-radius: 20px;
    font-size: 13px;
    color: #1f2937;
    outline: none;
    transition: all 0.2s ease;
}
.mn-header .mn-search-form .mn-search-input::placeholder {
    color: #9ca3af;
}
.mn-header .mn-search-form .mn-search-input:focus {
    background: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}
.mn-header .mn-search-form .mn-search-input:focus + .mn-search-icon,
.mn-header .mn-search-form .mn-search-input:focus ~ .mn-search-icon {
    color: #3b82f6;
}
.mn-header .mn-search-form .mn-search-submit {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    color: #6b7280;
    transition: background 0.15s ease, color 0.15s ease;
}
.mn-header .mn-search-form .mn-search-submit:hover {
    background: #e5e7eb;
    color: #1f2937;
}

/* --- Action Icons Area --- */
.mn-header .mn-actions {
    display: flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
}

/* --- Icon Button Base --- */
.mn-header .mn-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: transparent;
    color: #6b7280;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
    position: relative;
    border: none;
    text-decoration: none !important;
    padding: 0;
}
.mn-header .mn-icon-btn:hover,
.mn-header .mn-icon-btn:focus {
    background: #f3f4f6;
    color: #1f2937;
    text-decoration: none !important;
}
.mn-header a.mn-icon-btn:hover,
.mn-header a.mn-icon-btn:focus {
    text-decoration: none !important;
}

/* --- Badge --- */
.mn-header .mn-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    background: #ef4444;
    color: #ffffff;
    font-size: 10px;
    font-weight: 600;
    line-height: 16px;
    text-align: center;
    border-radius: 8px;
    pointer-events: none;
}

/* --- Currency/Language Switcher Containers --- */
.mn-header .mn-switcher {
    display: inline-flex;
    align-items: center;
    margin: 0 2px;
}
.mn-header .mn-switcher .languageselectpicker,
.mn-header .mn-switcher select {
    border: none;
    background: #f3f4f6;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 13px;
    color: #374151;
    height: 32px;
    outline: none;
    cursor: pointer;
    transition: background 0.15s ease;
}
.mn-header .mn-switcher .languageselectpicker:hover,
.mn-header .mn-switcher select:hover {
    background: #e5e7eb;
}

/* --- Dropdown Menus --- */
.mn-header .mn-dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.mn-header .mn-dropdown .dropdown-menu {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08), 0 4px 10px rgba(0,0,0,0.04);
    padding: 0;
    margin-top: 8px;
    overflow: hidden;
    min-width: 220px;
}
.mn-header .mn-dropdown .dropdown-menu > li > a {
    padding: 10px 16px;
    font-size: 13px;
    color: #374151;
    transition: background 0.12s ease;
}
.mn-header .mn-dropdown .dropdown-menu > li > a:hover {
    background: #f3f4f6;
    color: #1f2937;
}

/* --- Leave Notification Dropdown --- */
.mn-header .mn-leave-dropdown .dropdown-menu {
    min-width: 300px;
    right: 0;
    left: auto;
}
.mn-header .mn-leave-header {
    padding: 12px 16px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}
.mn-header .mn-leave-dropdown .todolist {
    list-style: none;
    padding: 0;
    margin: 0;
}
.mn-header .mn-leave-dropdown .todolist li a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    font-size: 13px;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.12s ease;
    text-decoration: none;
}
.mn-header .mn-leave-dropdown .todolist li a:hover {
    background: #f3f4f6;
}
.mn-header .mn-leave-dropdown .todolist li a .label {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
}

/* --- Tasks/Todo Dropdown --- */
.mn-header .mn-todo-dropdown .dropdown-menu {
    min-width: 320px;
    right: 0;
    left: auto;
}
.mn-header .mn-todo-header {
    padding: 12px 16px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13px;
    color: #374151;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mn-header .mn-todo-header a {
    color: #3b82f6;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
}
.mn-header .mn-todo-header a:hover {
    text-decoration: underline;
}
.mn-header .mn-todo-dropdown .todolist {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 280px;
    overflow-y: auto;
}
.mn-header .mn-todo-dropdown .todolist li {
    padding: 8px 16px;
    border-bottom: 1px solid #f3f4f6;
}
.mn-header .mn-todo-dropdown .todolist li:last-child {
    border-bottom: none;
}
.mn-header .mn-todo-dropdown .todolist li .checkbox {
    margin: 0;
}
.mn-header .mn-todo-dropdown .todolist li .checkbox label {
    font-size: 13px;
    color: #374151;
    padding-left: 4px;
}

/* --- Mobile Ellipsis Dropdown --- */
.mn-header .mn-ellipsis-dropdown .dropdown-menu {
    min-width: 160px;
    right: 0;
    left: auto;
}
.mn-header .mn-ellipsis-dropdown .dropdown-menu li a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    color: #374151;
    font-size: 14px;
    text-decoration: none;
    transition: background 0.12s ease;
}
.mn-header .mn-ellipsis-dropdown .dropdown-menu li a:hover {
    background: #f3f4f6;
}

/* --- WhatsApp Icon --- */
.mn-header .mn-whatsapp-btn {
    background: #25d366;
    color: #ffffff !important;
}
.mn-header .mn-whatsapp-btn:hover {
    background: #1da851 !important;
    color: #ffffff !important;
}
.mn-header .mn-whatsapp-btn svg path[style*="fill:#fff"] {
    fill: #ffffff;
}

/* --- User Avatar Dropdown --- */
.mn-header .mn-user-dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
    margin-left: 4px;
}
.mn-header .mn-user-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
    border: none;
    background: none;
    border-radius: 50%;
    transition: box-shadow 0.15s ease;
}
.mn-header .mn-user-trigger:hover {
    box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    border-radius: 50%;
}
.mn-header .mn-user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
    transition: border-color 0.15s ease;
}
.mn-header .mn-user-trigger:hover .mn-user-avatar {
    border-color: #3b82f6;
}
.mn-header .mn-user-icon-circle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    border-radius: 50%;
    border: 2px solid #e5e7eb;
    transition: border-color 0.15s ease;
}
.mn-header .mn-user-trigger:hover .mn-user-icon-circle {
    border-color: #3b82f6;
}
.mn-header .mn-user-icon-circle i {
    font-size: 16px;
    color: #ffffff;
}
.mn-header .mn-user-dropdown .dropdown-menu {
    right: 0;
    left: auto;
    min-width: 280px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1), 0 4px 10px rgba(0,0,0,0.05);
    padding: 0;
    margin-top: 8px;
    overflow: hidden;
}
.mn-header .mn-user-card {
    padding: 20px 16px 16px;
    text-align: center;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
.mn-header .mn-user-card-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e5e7eb;
    margin-bottom: 8px;
}
.mn-header .mn-user-card-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    background: #f3f4f6;
    border-radius: 50%;
    margin-bottom: 8px;
}
.mn-header .mn-user-card-icon i {
    font-size: 24px;
    color: #9ca3af;
}
.mn-header .mn-user-card h4 {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 2px;
    text-transform: capitalize;
}
.mn-header .mn-user-card h5 {
    font-size: 12px;
    font-weight: 400;
    color: #6b7280;
    margin: 0;
}
.mn-header .mn-user-links {
    padding: 8px 0;
}
.mn-header .mn-user-links a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    font-size: 13px;
    color: #374151;
    text-decoration: none;
    transition: background 0.12s ease;
}
.mn-header .mn-user-links a:hover {
    background: #f3f4f6;
    color: #1f2937;
}
.mn-header .mn-user-links a i {
    width: 16px;
    text-align: center;
    color: #6b7280;
    font-size: 14px;
}
.mn-header .mn-user-links .mn-logout-link {
    border-top: 1px solid #e5e7eb;
    margin-top: 4px;
    padding-top: 12px;
    color: #ef4444;
}
.mn-header .mn-user-links .mn-logout-link:hover {
    background: #fef2f2;
    color: #dc2626;
}
.mn-header .mn-user-links .mn-logout-link i {
    color: #ef4444;
}

/* --- Responsive --- */
@media (max-width: 991px) {
    .mn-header .mn-institution-name {
        max-width: 180px;
        font-size: 13px;
    }
    .mn-header .mn-search-form {
        display: none !important;
    }
}
@media (max-width: 767px) {
    .mn-header .mn-institution-name {
        display: none !important;
    }
    .mn-header .mn-navbar-inner {
        padding: 0 8px;
    }
    .mn-header .mn-search-form {
        display: none !important;
    }
    .mn-header.main-header .logo {
        padding: 0 8px;
    }
}

/* --- AdminLTE Overrides for .mn-header --- */
.mn-header.main-header .navbar-static-top {
    background: transparent !important;
}
.mn-header .navbar-nav > li > a {
    color: #6b7280;
    padding: 0;
}
.mn-header .navbar-nav > li > a:hover,
.mn-header .navbar-nav > li > a:focus {
    color: #1f2937;
    background: transparent;
}
.skin-blue .mn-header.main-header .logo {
    background: #ffffff !important;
    color: #1f2937;
    border-bottom: none;
}
.skin-blue .mn-header.main-header .logo:hover {
    background: #f9fafb !important;
}
.skin-blue .mn-header.main-header .navbar {
    background: #ffffff !important;
}
.skin-blue .mn-header.main-header .navbar .nav > li > a {
    color: #6b7280;
}
.skin-blue .mn-header.main-header .navbar .nav > li > a:hover {
    color: #1f2937;
    background: transparent;
}
.skin-blue .mn-header.main-header .sidebar-toggle {
    color: #6b7280;
}
.skin-blue .mn-header.main-header .sidebar-toggle:hover {
    color: #1f2937;
    background: transparent;
}
/* Hide AdminLTE default sidebar toggle since we use custom one */
.mn-header .navbar > .sidebar-toggle {
    display: none !important;
}
</style>

<script>

    function collapseSidebar() {

        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed'))) {
        sessionStorage.setItem('sidebar-toggle-collapsed', '');
        } else {
        sessionStorage.setItem('sidebar-toggle-collapsed', '1');
        }

        }

    function checksidebar() {
        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed'))) {
        var body = document.getElementsByTagName('body')[0];
        body.className = body.className + ' sidebar-collapse';
        }
    }

    checksidebar();

</script>
       <div class="wrapper">
			 <?php $result = $this->customlib->getLoggedInUserData();
			  ?>
            <header class="main-header mn-header" id="alert">
                <a href="<?php echo base_url(); ?>admin/admin/dashboard" class="logo">
                    <span class="logo-mini"><img src="<?php echo $this->customlib->getBaseUrl(); ?>uploads/school_content/admin_small_logo/<?php echo $this->setting_model->getAdminsmalllogo() . img_time();?>" alt="<?php echo $this->customlib->getAppName() ?>" /></span>
                    <span class="logo-lg"><img src="<?php echo $this->customlib->getBaseUrl(); ?>uploads/school_content/admin_logo/<?php echo $this->setting_model->getAdminlogo() . img_time();?>" alt="<?php echo $this->customlib->getAppName() ?>" /></span>
                </a>
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Hidden AdminLTE toggle (kept for AdminLTE JS compatibility) -->
                    <a onclick="collapseSidebar()" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only"><?php echo $this->lang->line('toggle_navigation'); ?></span>
                    </a>

                    <div class="mn-navbar-inner">
                        <!-- Hamburger Toggle -->
                        <button type="button" class="mn-sidebar-toggle" onclick="collapseSidebar(); $('.sidebar-toggle').click();" title="<?php echo $this->lang->line('toggle_navigation'); ?>">
                            <span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </span>
                        </button>

                        <!-- Institution Name -->
                        <span class="mn-institution-name">
                            <?php echo $this->setting_model->getCurrentSchoolName(); ?>
                        </span>

                        <!-- Spacer -->
                        <div class="mn-spacer"></div>

                        <!-- Search Bar -->
                        <?php if ($this->rbac->hasPrivilege('student', 'can_view')) {?>
                            <form id="header_search_form" class="mn-search-form" role="search" action="<?php echo site_url('admin/admin/search'); ?>" method="POST">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="mn-search-wrapper">
                                    <input type="text" value="<?php echo set_value('search_text1'); ?>" name="search_text1" id="search_text1" class="mn-search-input" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                                    <i class="fa fa-search mn-search-icon"></i>
                                    <button type="submit" name="search" id="search-btn" onclick="getstudentlist()" class="mn-search-submit"><i class="fa fa-arrow-right"></i></button>
                                </div>
                            </form>
                        <?php }?>

                        <!-- Action Icons -->
                        <div class="mn-actions">

                            <!-- Currency Switcher -->
                            <?php if ($this->rbac->hasPrivilege('currency_switcher', 'can_view')) {
    ?>
                                <div class="mn-switcher" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('currency') ?>">
                                    <select class="languageselectpicker" type="text" id="currencySwitcher" >

                                       <?php $this->load->view('admin/currency/currencySwitcher')?>

                                    </select>
                                </div>
                                <?php
}?>

                            <!-- Language Switcher -->
                            <?php if ($this->rbac->hasPrivilege('language_switcher', 'can_view')) {
    ?>
                                <div class="mn-switcher" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('language') ?>">
                                    <select class="languageselectpicker" onchange="set_languages(this.value)" type="text" id="languageSwitcher" >

                                       <?php $this->load->view('admin/language/languageSwitcher')?>

                                    </select>
                                </div>
                                <?php
}?>

                            <!-- Multi-Branch Switch -->
                            <?php $userdata = $this->customlib->getUserData();
                            if($userdata["role_id"] ==7){
                                if (($this->module_lib->hasModule('multi_branch') && $this->module_lib->hasActive('multi_branch')) || $this->db->multi_branch) { ?>

                                    <a href="#" class="mn-icon-btn" data-toggle="modal" data-target="#multiBranchSwitchModal" data-placement="bottom" title="<?php echo $this->lang->line('switch_branch'); ?>"><i class="fa fa-exchange" aria-hidden="true"></i></a>

                            <?php }
                            }?>

                            <!-- Current Session Switcher -->
                            <?php if ($this->rbac->hasPrivilege('quick_session_change', 'can_view')) { ?>
                                <a href="#" class="mn-icon-btn" data-toggle="modal" data-target="#currentSessionModal" data-placement="bottom" title="<?php echo $this->lang->line('current_session'); ?>"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></a>
                            <?php } ?>

                            <!-- Leave Notification Bell -->
                            <?php
                            $leave_approve_pending_count = 0;
                            $leave_recommender_pending_count = 0;
                            $leave_total_pending_count = 0;
                            $leave_staff_id = (int) $this->customlib->getStaffID();
                            $leave_role_info = json_decode($this->customlib->getStaffRole());
                            $leave_role_name = strtolower(trim((string) ($leave_role_info->name ?? '')));
                            $is_leave_admin_or_super_admin = in_array($leave_role_name, ['admin', 'super admin'], true);
                            $CI = &get_instance();
                            if ($leave_staff_id > 0 && $this->rbac->hasPrivilege('approve_leave_request', 'can_view')) {
                                $CI->load->model('leaverequest_model');
                                if ($is_leave_admin_or_super_admin) {
                                    $leave_approve_pending_count = $CI->leaverequest_model->count_all_approver_pending_leave_requests();
                                    $leave_recommender_pending_count = $CI->leaverequest_model->count_all_recommender_pending_leave_requests();
                                } else {
                                    $leave_approve_pending_count = $CI->leaverequest_model->count_approver_pending_leave_requests($leave_staff_id);
                                    $leave_recommender_pending_count = $CI->leaverequest_model->count_recommender_pending_leave_requests($leave_staff_id);
                                }
                                $leave_total_pending_count = $leave_approve_pending_count + $leave_recommender_pending_count;
                            }
                            if ($leave_total_pending_count > 0) {
                            ?>
                            <div class="mn-dropdown mn-leave-dropdown dropdown">
                                <a href="#" class="mn-icon-btn dropdown-toggle" data-toggle="dropdown" data-placement="bottom" title="Leave Requests">
                                    <i class="fa fa-bell-o"></i>
                                    <span class="mn-badge"><?php echo $leave_total_pending_count; ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="mn-leave-header">
                                        You have <?php echo $leave_total_pending_count; ?> pending leave request action(s)
                                    </li>
                                    <li>
                                        <ul class="todolist">
                                            <?php if ($leave_approve_pending_count > 0) { ?>
                                            <li>
                                                <a href="<?php echo site_url('admin/leaverequest/leaverequest'); ?>">
                                                    <?php echo $this->lang->line('approve_leave_request'); ?>
                                                    <small class="label pull-right bg-red"><?php echo $leave_approve_pending_count; ?></small>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if ($leave_recommender_pending_count > 0) { ?>
                                            <li>
                                                <a href="<?php echo site_url('admin/leaverequest/recommender_leave_requests'); ?>">
                                                    Recommender Leave Requests
                                                    <small class="label pull-right bg-red"><?php echo $leave_recommender_pending_count; ?></small>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <?php } ?>

                            <!-- Calendar Icon -->
                            <?php
if ($this->module_lib->hasActive('calendar_to_do_list')) {
    if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_view')) {
        ?>
                                <a class="mn-icon-btn d-sm-none" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('calendar') ?>" href="<?php echo base_url() ?>admin/calendar/events"><i class="fa fa-calendar"></i></a>
                                <?php
}
}
?>

                            <!-- Tasks/Todo Dropdown -->
                            <?php
if ($this->module_lib->hasActive('calendar_to_do_list')) {
    if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_view')) {
        ?>
                                <div class="mn-dropdown mn-todo-dropdown dropdown" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('task') ?>">
                                    <a href="#" class="mn-icon-btn dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-check-square-o"></i>
                                        <?php
$userdata = $this->customlib->getUserData();
        $count    = $this->customlib->countincompleteTask($userdata["id"],$userdata["role_id"]);
        if ($count > 0) {
            ?>
                                            <span class="mn-badge"><?php echo $count ?></span>
                                        <?php }?>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="mn-todo-header">
                                            <span><?php echo $this->lang->line('today_you_have'); ?> <?php echo $count; ?> <?php echo $this->lang->line('pending_task'); ?></span>
                                            <a href="<?php echo base_url() ?>admin/calendar/events"><?php echo $this->lang->line('view_all'); ?></a>
                                        </li>
                                        <li>
                                            <ul class="todolist">
                                                <?php
$tasklist = $this->customlib->getincompleteTask($userdata["id"],$userdata["role_id"]);
        foreach ($tasklist as $key => $value) {
            ?>
                                                    <li><div class="checkbox">
                                                            <label><input type="checkbox" id="newcheck<?php echo $value["id"] ?>" onclick="markc('<?php echo $value["id"] ?>')" name="eventcheck"  value="<?php echo $value["id"]; ?>"><?php echo $value["event_title"] ?></label>
                                                        </div></li>
                                                <?php }?>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>

                            <!-- Mobile Ellipsis Dropdown -->
                            <div class="mn-dropdown mn-ellipsis-dropdown dropdown d-lg-none d-sm-block">
                                <a class="mn-icon-btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-ellipsis-v"></i></a>
                                <ul class="dropdown-menu">
                                  <li><a href="<?php echo base_url() ?>admin/calendar/events"><i class="fa fa-calendar"></i> <?php echo $this->lang->line('calendar') ?></a></li>
                                  <li><a href="<?php echo base_url() ?>admin/chat"><i class="fa fa-comment-o"></i> <?php echo $this->lang->line('chat') ?></a></li>

<?php
	if($result['admin_panel_whatsapp']){
	$waurl = "https://wa.me/";
	$mobile = $result['admin_panel_whatsapp_mobile'];
	$url = $waurl.$mobile;
	$today = strtotime(date("H:i:s"));
	$show_hide = 1;

	if($result['admin_panel_whatsapp_from'] != '' && $result['admin_panel_whatsapp_to'] != ''){

		$admin_panel_whatsapp_from = strtotime($result['admin_panel_whatsapp_from']);
		$admin_panel_whatsapp_to = strtotime($result['admin_panel_whatsapp_to']);

		if($today>=$admin_panel_whatsapp_from && $today<=$admin_panel_whatsapp_to){
			$show_hide = 1;
		}else{
			$show_hide = 0;
		}

	}

	if($show_hide){
?>

<li><a href="<?php echo $url; ?>" target="_blank" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('whatsapp_link') ?>">
<svg height="16px" width="16px" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" style="vertical-align: middle; margin-right: 4px;">
<path style="fill:#25d366;" d="M0,512l35.31-128C12.359,344.276,0,300.138,0,254.234C0,114.759,114.759,0,255.117,0
    S512,114.759,512,254.234S395.476,512,255.117,512c-44.138,0-86.51-14.124-124.469-35.31L0,512z"></path>
<path style="fill:#25d366;" d="M137.71,430.786l7.945,4.414c32.662,20.303,70.621,32.662,110.345,32.662
    c115.641,0,211.862-96.221,211.862-213.628S371.641,44.138,255.117,44.138S44.138,137.71,44.138,254.234
    c0,40.607,11.476,80.331,32.662,113.876l5.297,7.945l-20.303,74.152L137.71,430.786z"></path>
<path style="fill:#fff;" d="M187.145,135.945l-16.772-0.883c-5.297,0-10.593,1.766-14.124,5.297
    c-7.945,7.062-21.186,20.303-24.717,37.959c-6.179,26.483,3.531,58.262,26.483,90.041s67.09,82.979,144.772,105.048
    c24.717,7.062,44.138,2.648,60.028-7.062c12.359-7.945,20.303-20.303,22.952-33.545l2.648-12.359
    c0.883-3.531-0.883-7.945-4.414-9.71l-55.614-25.6c-3.531-1.766-7.945-0.883-10.593,2.648l-22.069,28.248
    c-1.766,1.766-4.414,2.648-7.062,1.766c-15.007-5.297-65.324-26.483-92.69-79.448c-0.883-2.648-0.883-5.297,0.883-7.062
    l21.186-23.834c1.766-2.648,2.648-6.179,1.766-8.828l-25.6-57.379C193.324,138.593,190.676,135.945,187.145,135.945"></path>
</svg> <?php echo $this->lang->line('whatsapp_link') ?></a></li>
<?php } } ?>

                                </ul>
                              </div>
                                <?php
}
}
?>

                            <!-- Chat Icon -->
                            <?php
if ($this->module_lib->hasActive('chat')) {
    if ($this->rbac->hasPrivilege('chat', 'can_view')) {
        ?>
                                <a class="mn-icon-btn d-sm-none" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('chat') ?>" href="<?php echo base_url() ?>admin/chat"><i class="fa fa-comment-o"></i></a>
                                <?php
}
    ?>

                            <?php }

/* ---- WhatsApp Desktop Icon ---- */
$file   = "";

$role = $this->customlib->getStaffRole();


$image = $result["image"];
$role  = json_decode($role)->name;
$id    = $result["id"];

// Determine gender-based icon for fallback
$gender_icon = 'fa-user'; // Default
if (!empty($result['gender'])) {
    if (strtolower($result['gender']) === 'male') {
        $gender_icon = 'fa-male';
    } elseif (strtolower($result['gender']) === 'female') {
        $gender_icon = 'fa-female';
    }
}

if (!empty($image)) {
    $normalized_image = ltrim($image, '/');
    if (strpos($normalized_image, 'uploads/staff_images/') !== 0) {
        $normalized_image = 'uploads/staff_images/' . $normalized_image;
    }
    $file = $normalized_image . img_time();
} else {
    $file = null; // No image, will use icon instead
}
?>

<?php
	if($result['admin_panel_whatsapp']){
	$waurl = "https://wa.me/";
	$mobile = $result['admin_panel_whatsapp_mobile'];
	$url = $waurl.$mobile;
	$today = strtotime(date("H:i:s"));

	$show_hide = 1;
	if($result['admin_panel_whatsapp_from'] != '' && $result['admin_panel_whatsapp_to'] != ''){

		$admin_panel_whatsapp_from = strtotime($result['admin_panel_whatsapp_from']);
		$admin_panel_whatsapp_to = strtotime($result['admin_panel_whatsapp_to']);

		if($today>=$admin_panel_whatsapp_from && $today<=$admin_panel_whatsapp_to){
			$show_hide = 1;
		}else{
			$show_hide = 0;
		}
	}

	if($show_hide){
?>

<a class="mn-icon-btn mn-whatsapp-btn d-sm-none" target="_blank" href="<?php echo $url; ?>" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('whatsapp_link') ?>">
<svg height="18px" width="18px" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
     viewBox="0 0 512 512" xml:space="preserve">
<path style="fill:#fff;" d="M0,512l35.31-128C12.359,344.276,0,300.138,0,254.234C0,114.759,114.759,0,255.117,0
    S512,114.759,512,254.234S395.476,512,255.117,512c-44.138,0-86.51-14.124-124.469-35.31L0,512z"/>
<path style="fill:#55CD6C;" d="M137.71,430.786l7.945,4.414c32.662,20.303,70.621,32.662,110.345,32.662
    c115.641,0,211.862-96.221,211.862-213.628S371.641,44.138,255.117,44.138S44.138,137.71,44.138,254.234
    c0,40.607,11.476,80.331,32.662,113.876l5.297,7.945l-20.303,74.152L137.71,430.786z"/>
<path style="fill:#fff;" d="M187.145,135.945l-16.772-0.883c-5.297,0-10.593,1.766-14.124,5.297
    c-7.945,7.062-21.186,20.303-24.717,37.959c-6.179,26.483,3.531,58.262,26.483,90.041s67.09,82.979,144.772,105.048
    c24.717,7.062,44.138,2.648,60.028-7.062c12.359-7.945,20.303-20.303,22.952-33.545l2.648-12.359
    c0.883-3.531-0.883-7.945-4.414-9.71l-55.614-25.6c-3.531-1.766-7.945-0.883-10.593,2.648l-22.069,28.248
    c-1.766,1.766-4.414,2.648-7.062,1.766c-15.007-5.297-65.324-26.483-92.69-79.448c-0.883-2.648-0.883-5.297,0.883-7.062
    l21.186-23.834c1.766-2.648,2.648-6.179,1.766-8.828l-25.6-57.379C193.324,138.593,190.676,135.945,187.145,135.945"/>
</svg></a>

<?php } } ?>

                            <!-- User Avatar Dropdown -->
                            <div class="mn-user-dropdown dropdown">
                                <a class="mn-user-trigger dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false" title="<?php echo $this->customlib->getAdminSessionUserName(); ?>">
                                    <?php if ($file) { ?>
                                        <img src="<?php echo base_url($file); ?>" class="mn-user-avatar" alt="User Image">
                                    <?php } else { ?>
                                        <span class="mn-user-icon-circle">
                                            <i class="fa <?php echo $gender_icon; ?>"></i>
                                        </span>
                                    <?php } ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <div class="mn-user-card">
                                            <a href="<?php echo base_url() . "admin/staff/profile/" . $id ?>">
                                                <?php if ($file) { ?>
                                                    <img src="<?php echo base_url($file); ?>" class="mn-user-card-avatar" alt="User Image">
                                                <?php } else { ?>
                                                    <div class="mn-user-card-icon">
                                                        <i class="fa <?php echo $gender_icon; ?>"></i>
                                                    </div>
                                                <?php } ?>
                                            </a>
                                            <h4><?php echo $this->customlib->getAdminSessionUserName(); ?></h4>
                                            <h5><?php echo $role; ?></h5>
                                        </div>
                                        <div class="mn-user-links">
                                            <a href="<?php echo base_url() . "admin/staff/profile/" . $id ?>" data-toggle="tooltip" title="<?php echo $this->lang->line('my_profile'); ?>">
                                                <i class="fa fa-user"></i> <?php echo $this->lang->line('profile'); ?>
                                            </a>
                                            <a href="<?php echo base_url(); ?>admin/admin/changepass" data-toggle="tooltip" title="<?php echo $this->lang->line('change_password'); ?>">
                                                <i class="fa fa-key"></i> <?php echo $this->lang->line('password'); ?>
                                            </a>
                                            <a class="mn-logout-link" href="<?php echo base_url(); ?>site/logout">
                                                <i class="fa fa-sign-out"></i> <?php echo $this->lang->line('logout'); ?>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                        </div><!-- /.mn-actions -->
                    </div><!-- /.mn-navbar-inner -->
                </nav>
            </header>

            <?php $this->load->view('layout/sidebar');?>
<script>
    function set_languages(lang_id){
        $.ajax({
        type: "POST",
        url: base_url + "admin/language/user_language/"+lang_id,
        data: {},
        success: function (data) {
            successMsg("<?php echo $this->lang->line('status_change_successfully'); ?>");
            window.location.reload('true');
        }
        });
    }
</script>