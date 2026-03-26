<aside class="main-sidebar" id="alert2">
    <?php if ($this->rbac->hasPrivilege('student', 'can_view')) {?>
        <form class="navbar-form navbar-left search-form2" role="search"  action="<?php echo site_url('admin/admin/search'); ?>" method="POST">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="input-group ">
                <input type="text"  name="search_text" class="form-control search-form" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" style="padding: 3px 12px !important;border-radius: 0px 30px 30px 0px; background: #fff;" class="btn btn-flat"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </form>
    <?php }?>
    <section class="sidebar" id="sibe-box">
        <?php $this->load->view('layout/top_sidemenu');?>

        <ul class="sidebar-menu verttop">	
					
<!-- //==================sidebar dynamic======================= -->

<?php
$side_list = side_menu_list(1);

$CI = &get_instance();
$leave_badges = [
    'approve_leave_request' => 0,
    'recommender_leave_requests' => 0,
];
$current_staff_id = (int) $this->customlib->getStaffID();
$role_info = json_decode($this->customlib->getStaffRole());
$role_name = strtolower(trim((string) ($role_info->name ?? '')));
$is_admin_or_super_admin = in_array($role_name, ['admin', 'super admin'], true);
if ($current_staff_id > 0) {
    $CI->load->model('leaverequest_model');
    if ($is_admin_or_super_admin) {
        $leave_badges['approve_leave_request'] = $CI->leaverequest_model->count_all_approver_pending_leave_requests();
        $leave_badges['recommender_leave_requests'] = $CI->leaverequest_model->count_all_recommender_pending_leave_requests();
    } else {
        $leave_badges['approve_leave_request'] = $CI->leaverequest_model->count_approver_pending_leave_requests($current_staff_id);
        $leave_badges['recommender_leave_requests'] = $CI->leaverequest_model->count_recommender_pending_leave_requests($current_staff_id);
    }
}

if (!empty($side_list)) {
    foreach ($side_list as $side_list_key => $side_list_value) {

        $module_permission = access_permission_sidebar_remove_pipe($side_list_value->access_permissions);
        $module_access     = false;
        if (!empty($module_permission)) {
            foreach ($module_permission as $m_permission_key => $m_permission_value) {
                $cat_permission = access_permission_remove_comma($m_permission_value);

                if ($this->rbac->hasPrivilege($cat_permission[0], $cat_permission[1])) {
                    $module_access = true;
                    break;
                }
            }
        }

        if (!$module_access && !empty($side_list_value->submenus)) {
            foreach ($side_list_value->submenus as $submenu_probe) {
                if (in_array((string) ($submenu_probe->url ?? ''), [
                    'admin/staff/leaverequest',
                    'admin/leaverequest/leaverequest',
                    'admin/leaverequest/recommender_leave_requests',
                    'admin/leaverequest/applyleave'
                ], true)) {
                    $module_access = true;
                    break;
                }
            }
        }
        if ($module_access) {
            if ($this->module_lib->hasModule($side_list_value->short_code) && $this->module_lib->hasActive($side_list_value->short_code)) {

                ?>

                    <li class="treeview <?php echo activate_main_menu($side_list_value->activate_menu); ?>">

                        <a href="#">
                            <?php $menu_label = $this->lang->line($side_list_value->lang_key); ?>
                            <i class="<?php echo $side_list_value->icon; ?>"></i> <span><?php echo !empty($menu_label) ? $menu_label : $side_list_value->menu; ?></span> <i class="fa fa-angle-left pull-right"></i>
                        </a>

                                                    <?php
if (!empty($side_list_value->submenus)) {
                    ?>
                        <ul class="treeview-menu">
                            <?php
foreach ($side_list_value->submenus as $submenu_key => $submenu_value) {

                        $sidebar_permission = access_permission_sidebar_remove_pipe($submenu_value->access_permissions);
                        $sidebar_access     = false;

                        if (!empty($sidebar_permission)) {
                            foreach ($sidebar_permission as $sidebar_permission_key => $sidebar_permission_value) {
                                $sidebar_cat_permission = access_permission_remove_comma($sidebar_permission_value);

                                if ($this->rbac->hasPrivilege($sidebar_cat_permission[0], $sidebar_cat_permission[1])) {
                                    $sidebar_access = true;
                                    break;
                                }
                            }
                        }

                        if (!$sidebar_access && in_array((string) ($submenu_value->url ?? ''), [
                            'admin/staff/leaverequest',
                            'admin/leaverequest/leaverequest',
                            'admin/leaverequest/recommender_leave_requests',
                            'admin/leaverequest/applyleave'
                        ], true)) {
                            $sidebar_access = true;
                        }

                        if ($sidebar_access) {
                            if (!empty($submenu_value->permission_group_id)) {
                                if (!$this->module_lib->hasActive($submenu_value->short_code)) {
                                    continue;
                                }
                            }
                            if ($submenu_value->url == 'admin/subjectgroup' && $this->sch_setting_detail->institution_type != 'college') {
                                continue;
                            }

                            ?>

                        <li class="<?php echo activate_submenu($submenu_value->activate_controller, explode(',', $submenu_value->activate_methods)); ?>">
                            <?php
                            $menu_url = site_url($submenu_value->url);
                            if ($submenu_value->lang_key == 'setting' && $submenu_value->url == 'admin/multibranch/branch') {
                                $menu_url = site_url('admin/multibranch/branch/setting');
                            }
                            ?>
                            <?php
                            $badge_count = 0;
                            if ($submenu_value->lang_key === 'approve_leave_request') {
                                $badge_count = (int) ($leave_badges['approve_leave_request'] ?? 0);
                            } elseif ($submenu_value->lang_key === 'recommender_leave_requests') {
                                $badge_count = (int) ($leave_badges['recommender_leave_requests'] ?? 0);
                            }
                            ?>
                            <?php $submenu_label = $this->lang->line($submenu_value->lang_key); ?>
                            <a href="<?php echo $menu_url; ?>"><i class="fa fa-angle-double-right"></i><?php echo !empty($submenu_label) ? $submenu_label : $submenu_value->menu; ?><?php if ($badge_count > 0) { ?><small class="label pull-right bg-red"><?php echo $badge_count; ?></small><?php } ?></a>
                        </li>

                          <?php
}

                    }

                    ?>
                        </ul>
                            <?php

                }
                ?>
                                </li>
                            <?php
}
        }
    }
}
?>
?>
        <!-- //==================sidebar dynamic======================= -->

        </ul>
    </section>
</aside>