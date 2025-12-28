<!DOCTYPE html>
<html lang="en" class="layout-static">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo (isset($title) && $title != '') ? $title : $this->customlib->getAppName(); ?></title>

    <!-- Global stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="<?php echo base_url(); ?>backend/assets/limitless/css/icons/icomoon/styles.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo base_url(); ?>backend/assets/limitless/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- /global stylesheets -->

    <!-- Core JS files -->
    <script src="<?php echo base_url(); ?>backend/assets/limitless/js/jquery.min.js"></script>
    <script src="<?php echo base_url(); ?>backend/assets/limitless/js/bootstrap.bundle.min.js"></script>
    <!-- /core JS files -->

    <!-- Theme JS files -->
    <script src="<?php echo base_url(); ?>backend/assets/limitless/js/app.js"></script>
    <!-- /theme JS files -->
     <script type="text/javascript">
            var baseurl = "<?php echo base_url(); ?>";
            var start_week=<?php echo $this->customlib->getStartWeek(); ?>;
     </script>

</head>

<body>

    <!-- Main navbar -->
    <div class="navbar navbar-expand-lg navbar-light navbar-static">
        <div class="d-flex flex-1 d-lg-none">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
                <i class="icon-paragraph-justify3"></i>
            </button>
            <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
                <i class="icon-transmission"></i>
            </button>
        </div>

        <div class="navbar-brand text-center text-lg-left">
            <a href="<?php echo base_url(); ?>admin/admin/dashboard" class="d-inline-block">
                <img src="<?php echo $this->customlib->getBaseUrl(); ?>uploads/school_content/admin_logo/<?php echo $this->setting_model->getAdminlogo() . img_time();?>" class="d-none d-sm-block" alt="Logo">
                <img src="<?php echo $this->customlib->getBaseUrl(); ?>uploads/school_content/admin_small_logo/<?php echo $this->setting_model->getAdminsmalllogo() . img_time();?>" class="d-sm-none" alt="Logo">
            </a>
        </div>

        <div class="collapse navbar-collapse order-2 order-lg-1" id="navbar-mobile">
            <span class="badge badge-success my-3 my-lg-0 ml-lg-3">
                <?php echo $this->setting_model->getCurrentSchoolName(); ?>
            </span>
        </div>

        <ul class="navbar-nav flex-row order-1 order-lg-2 flex-1 flex-lg-0 justify-content-end align-items-center">
            <?php if ($this->rbac->hasPrivilege('student', 'can_view')) {?>
                <li class="nav-item">
                    <form id="header_search_form" class="navbar-form navbar-left search-form" role="search"  action="<?php echo site_url('admin/admin/search'); ?>" method="POST">
                         <?php echo $this->customlib->getCSRF(); ?>
                        <div class="input-group">
                            <input type="text" value="<?php echo set_value('search_text1'); ?>" name="search_text1" id="search_text1" class="form-control" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                            <div class="input-group-append">
                                <button type="submit" name="search" id="search-btn" class="btn btn-light"><i class="icon-search4"></i></button>
                            </div>
                        </div>
                    </form>
                </li>
            <?php }?>

            <!-- Language Switcher -->
            <?php if ($this->rbac->hasPrivilege('language_switcher', 'can_view')) { ?>
                <li class="nav-item nav-item-dropdown-lg dropdown">
                    <a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-language mr-2"></i>
                        <span class="d-none d-lg-inline-block"><?php echo $this->lang->line('language') ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?php $this->load->view('admin/language/languageSwitcher')?>
                    </div>
                </li>
            <?php } ?>

            <!-- Currency Switcher -->
            <?php if ($this->rbac->hasPrivilege('currency_switcher', 'can_view')) { ?>
                <li class="nav-item nav-item-dropdown-lg dropdown">
                    <a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-cash3 mr-2"></i>
                        <span class="d-none d-lg-inline-block"><?php echo $this->lang->line('currency') ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?php $this->load->view('admin/currency/currencySwitcher')?>
                    </div>
                </li>
            <?php } ?>

            <!-- Calendar Link -->
            <?php if ($this->module_lib->hasActive('calendar_to_do_list')) {
                if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_view')) {
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo base_url() ?>admin/calendar/events" class="navbar-nav-link" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('calendar') ?>">
                            <i class="icon-calendar3"></i>
                            <span class="d-lg-none ml-3"><?php echo $this->lang->line('calendar') ?></span>
                        </a>
                    </li>
                    <?php
                }
            }
            ?>

            <!-- Chat Link -->
            <?php
            if ($this->module_lib->hasActive('chat')) {
                if ($this->rbac->hasPrivilege('chat', 'can_view')) {
                    ?>
                    <li class="nav-item">
                        <a href="<?php echo base_url() ?>admin/chat" class="navbar-nav-link" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('chat') ?>">
                            <i class="icon-bubbles"></i>
                            <span class="d-lg-none ml-3"><?php echo $this->lang->line('chat') ?></span>
                        </a>
                    </li>
                    <?php
                }
            }
            ?>

            <?php
                $userdata = $this->customlib->getUserData();
                $file   = "";
                $role = $this->customlib->getStaffRole();
                $image = $userdata["image"];
                $role  = json_decode($role)->name;
                $id    = $userdata["id"];
                if (!empty($image)) {
                    $file = "uploads/staff_images/" . $image . img_time();
                } else {
                    if ($userdata['gender'] == 'Female') {
                        $file = "uploads/staff_images/default_female.jpg" . img_time();
                    } else {
                        $file = "uploads/staff_images/default_male.jpg" . img_time();
                    }
                }
            ?>
            <li class="nav-item nav-item-dropdown-lg dropdown dropdown-user h-100">
                <a href="#" class="navbar-nav-link navbar-nav-link-toggler dropdown-toggle d-inline-flex align-items-center h-100" data-toggle="dropdown">
                    <img src="<?php echo base_url($file); ?>" class="rounded-pill mr-lg-2" height="34" alt="">
                    <span class="d-none d-lg-inline-block"><?php echo $this->customlib->getAdminSessionUserName(); ?></span>
                </a>

                <div class="dropdown-menu dropdown-menu-right">
                    <a href="<?php echo base_url() . "admin/staff/profile/" . $id ?>" class="dropdown-item"><i class="icon-user-plus"></i> <?php echo $this->lang->line('my_profile'); ?></a>
                    <a href="<?php echo base_url(); ?>admin/admin/changepass" class="dropdown-item"><i class="icon-cog5"></i> <?php echo $this->lang->line('change_password'); ?></a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo base_url(); ?>site/logout" class="dropdown-item"><i class="icon-switch2"></i> <?php echo $this->lang->line('logout'); ?></a>
                </div>
            </li>
        </ul>
    </div>
    <!-- /main navbar -->


    <!-- Page content -->
    <div class="page-content">
        <?php $this->load->view('layout/sidebar'); ?>

        <!-- Main content -->
        <div class="content-wrapper">



            <!-- Content area -->
            <div class="content">