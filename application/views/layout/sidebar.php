<style>
/* ── Modern Sidebar ── */
.mn-sidebar.main-sidebar {
    background: #ffffff;
    border-right: 1px solid #e5e7eb;
    box-shadow: 2px 0 8px rgba(0,0,0,0.03);
    padding-top: 0;
    z-index: 1040;
}
.skin-blue .mn-sidebar.main-sidebar { background: #ffffff; }

/* Sidebar scrollable area — fill below header.
   In AdminLTE fixed mode, sidebar is position:fixed and starts at top:0.
   The header (with logo) is also fixed at top:0, height 56px.
   We need to push sidebar content below the header. */
.mn-sidebar.main-sidebar {
    padding-top: 0;
}
.mn-sidebar .sidebar {
    height: calc(100vh - 56px);
    overflow-y: auto;
    overflow-x: hidden;
}

/* Hide the old search form inside sidebar */
.mn-sidebar .navbar-form.search-form2 { display: none; }

/* Session bar (top_sidemenu) */
.mn-sidebar .sessionul.fixedmenu {
    margin: 0; padding: 0; list-style: none;
    border-bottom: 1px solid #f3f4f6;
}

/* ── Logo visibility fix ── */
/* AdminLTE .logo sits inside header but visually appears above the sidebar.
   Ensure it's not hidden by overflow or height constraints. */
.mn-header.main-header { overflow: visible !important; }
/* Content area background */
.skin-blue .content-wrapper { background: #f8f9fb !important; }

.skin-blue .main-sidebar .sidebar-menu > li.header {
    background: transparent;
    color: #9ca3af;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 22px 6px;
}

/* Sidebar section */
.mn-sidebar .sidebar {
    padding: 8px 0 20px;
    background: transparent;
}

/* ── Menu list ── */
.mn-sidebar .sidebar-menu {
    list-style: none; margin: 0; padding: 0;
}
.mn-sidebar .sidebar-menu > li {
    margin: 1px 8px; border-radius: 8px; overflow: hidden;
    transition: background 0.15s;
}
.mn-sidebar .sidebar-menu > li > a {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px; color: #4b5563;
    font-size: 13px; font-weight: 500; letter-spacing: 0.01em;
    text-decoration: none; border-radius: 8px;
    transition: all 0.15s;
    line-height: 1.4;
}
.mn-sidebar .sidebar-menu > li > a > i:first-child {
    width: 20px; text-align: center; font-size: 15px; color: #9ca3af;
    flex-shrink: 0; transition: color 0.15s;
}
.mn-sidebar .sidebar-menu > li > a > span {
    flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.mn-sidebar .sidebar-menu > li > a > .pull-right,
.mn-sidebar .sidebar-menu > li > a > i.fa-angle-left {
    font-size: 12px; color: #d1d5db; transition: transform 0.2s, color 0.15s;
    margin-left: auto; flex-shrink: 0;
}

/* Hover */
.mn-sidebar .sidebar-menu > li > a:hover {
    background: #f3f4f6; color: #1f2937;
}
.mn-sidebar .sidebar-menu > li > a:hover > i:first-child { color: #6366f1; }

/* Active parent */
.mn-sidebar .sidebar-menu > li.active > a {
    background: #eef2ff; color: #4338ca; font-weight: 600;
}
.mn-sidebar .sidebar-menu > li.active > a > i:first-child { color: #6366f1; }
.mn-sidebar .sidebar-menu > li.active > a > .pull-right,
.mn-sidebar .sidebar-menu > li.active > a > i.fa-angle-left { color: #6366f1; }

/* Open treeview — rotate arrow */
.mn-sidebar .sidebar-menu > li.treeview.active > a > i.fa-angle-left,
.mn-sidebar .sidebar-menu > li.treeview.menu-open > a > i.fa-angle-left {
    transform: rotate(-90deg);
}

/* ── Submenu ── */
.mn-sidebar .treeview-menu {
    list-style: none; margin: 0; padding: 2px 0 6px 0;
    background: transparent;
}
.mn-sidebar .treeview-menu > li { margin: 0; }
.mn-sidebar .treeview-menu > li > a {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 14px 7px 46px; color: #6b7280;
    font-size: 12.5px; font-weight: 400;
    text-decoration: none; border-radius: 6px;
    margin: 0 8px; transition: all 0.15s;
    line-height: 1.4;
    position: relative;
}
/* Subtle dot instead of double-right arrow */
.mn-sidebar .treeview-menu > li > a > i.fa-angle-double-right {
    display: none;
}
.mn-sidebar .treeview-menu > li > a::before {
    content: '';
    width: 5px; height: 5px; border-radius: 50%;
    background: #d1d5db; flex-shrink: 0;
    transition: background 0.15s, transform 0.15s;
}

/* Submenu hover */
.mn-sidebar .treeview-menu > li > a:hover {
    background: #f9fafb; color: #1f2937;
}
.mn-sidebar .treeview-menu > li > a:hover::before { background: #6366f1; }

/* Submenu active */
.mn-sidebar .treeview-menu > li.active > a {
    color: #4338ca; font-weight: 600; background: #eef2ff;
}
.mn-sidebar .treeview-menu > li.active > a::before {
    background: #6366f1; transform: scale(1.3);
}

/* Badge in submenu */
.mn-sidebar .treeview-menu > li > a > .label {
    margin-left: auto; font-size: 10px; padding: 2px 7px;
    border-radius: 10px; font-weight: 600; line-height: 1.4;
}

/* ── Scrollbar ── */
.mn-sidebar .sidebar::-webkit-scrollbar { width: 4px; }
.mn-sidebar .sidebar::-webkit-scrollbar-track { background: transparent; }
.mn-sidebar .sidebar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
.mn-sidebar .sidebar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

/* ── Collapsed sidebar (mini) ── */
.sidebar-collapse .mn-sidebar.main-sidebar {
    width: 50px;
}
.sidebar-collapse .mn-sidebar .sidebar-menu > li > a > span,
.sidebar-collapse .mn-sidebar .sidebar-menu > li > a > .pull-right,
.sidebar-collapse .mn-sidebar .sidebar-menu > li > a > i.fa-angle-left {
    display: none;
}
.sidebar-collapse .mn-sidebar .sidebar-menu > li > a {
    justify-content: center; padding: 12px 0;
}
.sidebar-collapse .mn-sidebar .sidebar-menu > li > a > i:first-child {
    font-size: 17px; width: auto; margin: 0;
}
.sidebar-collapse .mn-sidebar .sidebar-menu > li { margin: 1px 4px; }

/* Hover flyout for collapsed state */
.sidebar-collapse .mn-sidebar .sidebar-menu > li:hover > .treeview-menu {
    display: block !important;
    position: absolute; left: 50px; top: 0;
    min-width: 220px; background: #fff;
    border: 1px solid #e5e7eb; border-radius: 8px;
    box-shadow: 4px 4px 16px rgba(0,0,0,0.08);
    padding: 6px 0; z-index: 1050;
}
.sidebar-collapse .mn-sidebar .treeview-menu > li > a {
    padding-left: 16px; margin: 0 4px;
}

/* ── AdminLTE skin-blue overrides (must use !important to beat minified skin) ── */
.skin-blue .wrapper,
.skin-blue .main-sidebar,
.skin-blue .mn-sidebar.main-sidebar,
.skin-blue .left-side { background-color: #ffffff !important; }

.skin-blue .sidebar-menu > li > a { border-left: none !important; }
.skin-blue .sidebar-menu > li:hover > a,
.skin-blue .sidebar-menu > li.active > a { background: #eef2ff !important; color: #4338ca !important; border-left-color: transparent !important; }
.skin-blue .sidebar a { color: #4b5563 !important; }
.skin-blue .sidebar a:hover { color: #1f2937 !important; text-decoration: none !important; }
.skin-blue .sidebar-menu > li > .treeview-menu { margin: 0 !important; background: transparent !important; }
.skin-blue .sidebar-menu > li.header { display: none !important; }
.skin-blue .treeview-menu > li > a { color: #6b7280 !important; }
.skin-blue .treeview-menu > li > a:hover { color: #1f2937 !important; }
.skin-blue .treeview-menu > li.active > a { color: #4338ca !important; }

/* Override dark sidebar text colors */
.skin-blue .main-sidebar .sidebar-menu > li > a,
.skin-blue .main-sidebar .sidebar-menu > li > a > span { color: inherit !important; }
.skin-blue .main-sidebar .sidebar-menu > li > a > i { color: #9ca3af !important; }
.skin-blue .main-sidebar .sidebar-menu > li.active > a > i { color: #6366f1 !important; }
.skin-blue .main-sidebar .sidebar-menu > li:hover > a > i { color: #6366f1 !important; }

/* Logo area override — hide AdminLTE header logo, we render our own in sidebar */
.skin-blue .main-header .logo { display: none !important; }
/* navbar margin-left handled by modern-override.css */

/* Sidebar logo */
.mn-sidebar-logo {
    display: flex; align-items: center; justify-content: center;
    height: 56px; border-bottom: 1px solid #e5e7eb; background: #ffffff;
    padding: 0 12px; text-decoration: none; overflow: hidden;
}
.mn-sidebar-logo img { max-height: 42px; max-width: 180px; }
.mn-sidebar-logo img.mn-logo-full { display: inline-block; }
.mn-sidebar-logo img.mn-logo-mini { display: none; max-height: 32px; max-width: 32px; }
.sidebar-collapse .mn-sidebar-logo { padding: 0 4px; width: 50px; }
.sidebar-collapse .mn-sidebar-logo img.mn-logo-full { display: none !important; }
.sidebar-collapse .mn-sidebar-logo img.mn-logo-mini { display: inline-block !important; }

/* ── Mobile ── */
@media (max-width: 767px) {
    .mn-sidebar.main-sidebar {
        box-shadow: 4px 0 20px rgba(0,0,0,0.1);
    }
}
</style>

<aside class="main-sidebar mn-sidebar" id="alert2">
    <a href="<?php echo base_url(); ?>admin/admin/dashboard" class="mn-sidebar-logo">
        <img class="mn-logo-full" src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php echo $this->setting_model->getAdminlogo(); ?>" alt="<?php echo $this->customlib->getAppName(); ?>">
        <img class="mn-logo-mini" src="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php echo $this->setting_model->getAdminsmalllogo(); ?>" alt="<?php echo $this->customlib->getAppName(); ?>">
    </a>
    <?php if ($this->rbac->hasPrivilege('student', 'can_view')) {?>
        <form class="navbar-form navbar-left search-form2" role="search" action="<?php echo site_url('admin/admin/search'); ?>" method="POST">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="input-group">
                <input type="text" name="search_text" class="form-control search-form" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
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
                    'admin/leaverequest/recommender_leave_requests'
                ], true)) {
                    $module_access = true;
                    break;
                }
            }
        }
        if ($module_access) {
            if ($side_list_value->short_code === 'cbseexam' && $this->sch_setting_detail->institution_type === 'college') {
                continue;
            }
            if ($side_list_value->short_code === 'coe' && $this->sch_setting_detail->institution_type !== 'college') {
                continue;
            }
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
                            'admin/complaint'
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
        <!-- //==================sidebar dynamic======================= -->

        </ul>
    </section>
</aside>
