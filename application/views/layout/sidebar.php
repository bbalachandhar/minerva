<div class="sidebar sidebar-light sidebar-main sidebar-expand-lg">

    <!-- Sidebar content -->
    <div class="sidebar-content">

        


        <div class="sidebar-section sidebar-control sidebar-section-light">
            <div class="sidebar-section-body d-flex justify-content-center">
                <h5 class="sidebar-resize-hide flex-grow-1 my-auto">Navigation</h5>

                <div>
                    <button type="button" class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
                        <i class="fa fa-arrows-alt-h"></i> <!-- Font Awesome equivalent -->
                    </button>

                    <button type="button" class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-mobile-main-toggle d-lg-none">
                        <i class="fa fa-times"></i> <!-- Font Awesome equivalent -->
                    </button>
                </div>
            </div>
        </div>
        <!-- Main navigation -->
        <div class="sidebar-section">
            <ul class="nav nav-sidebar" data-nav-type="accordion">



                <?php
                $side_list = side_menu_list(1);

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
                        if ($module_access) {
                            if ($this->module_lib->hasModule($side_list_value->short_code) && $this->module_lib->hasActive($side_list_value->short_code)) {
                ?>
                                <li class="nav-item <?php if(!empty($side_list_value->submenus)) echo 'nav-item-submenu'; ?> <?php echo activate_main_menu($side_list_value->activate_menu); ?>">

                                    <a href="#" class="nav-link">
                                        <i class="<?php echo $side_list_value->icon; ?>"></i>
                                        <span><?php echo $this->lang->line($side_list_value->lang_key); ?></span>
                                    </a>

                                    <?php
                                    if (!empty($side_list_value->submenus)) {
                                    ?>
                                        <ul class="nav nav-group-sub" data-submenu-title="<?php echo $this->lang->line($side_list_value->lang_key); ?>">
                                            <?php
                                            foreach ($side_list_value->submenus as $submenu_key => $submenu_value) {

                                                $sidebar_permission = access_permission_sidebar_remove_pipe($submenu_value->access_permissions);
                                                $sidebar_access     = false;

                                                if (!empty($sidebar_permission)) {
                                                    foreach ($sidebar_permission as $sidebar_permission_key => $sidebar_permission_value) {
                                                        $sidebar_cat_permission = access_permission_remove_comma($sidebar_permission_value);

                                                        if ($submenu_value->addon_permission != "") {
                                                            if ($this->rbac->hasPrivilege($sidebar_cat_permission[0], $sidebar_cat_permission[1])
                                                                && $this->auth->addonchk($submenu_value->addon_permission, false)) {
                                                                $sidebar_access = true;
                                                                break;
                                                            }
                                                        } else {
                                                            if ($this->rbac->hasPrivilege($sidebar_cat_permission[0], $sidebar_cat_permission[1])) {
                                                                $sidebar_access = true;
                                                                break;
                                                            }
                                                        }
                                                    }
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
                                                    <li class="nav-item <?php echo activate_submenu($submenu_value->activate_controller, explode(',', $submenu_value->activate_methods)); ?>">
                                                        <a href="<?php echo site_url($submenu_value->url); ?>" class="nav-link">
                                                            <i class="fa fa-angle-double-right"></i>
                                                            <?php echo $this->lang->line($submenu_value->lang_key); ?>
                                                        </a>
                                                    </li>                                            <?php
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
            </ul>
        </div>
        <!-- /main navigation -->

    </div>
    <!-- /sidebar content -->
</div>
