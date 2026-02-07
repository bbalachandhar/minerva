<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rbac
{

    private $userRoles = array();
    protected $permissions;
    public $perm_category;

    public function __construct()
    {

        $this->CI          = &get_instance();
        $this->permissions = array();
        $this->CI->config->load('mailsms');
        $this->perm_category = $this->CI->config->item('perm_category');
      
    }
 
    public function hasPrivilege($category = null, $permission = null)
    {    
        $roles_data = $this->CI->customlib->getStaffRole();
        $logged_user_role = json_decode($roles_data)->name;

        if ($logged_user_role == 'Super Admin') {
            return true;
        }

        $admin = $this->CI->session->userdata('admin');

        if (!$admin || !isset($admin['roles']) || empty($admin['roles'])) {
            return false;
        }

        $role_ids = $admin['roles'];
        foreach ($role_ids as $role_id) {
            $role_perm = $this->CI->rolepermission_model->getPermissionByRoleandCategory($role_id, trim($category));
            if ($role_perm && array_key_exists($permission, $role_perm) && $role_perm[$permission]) {
                return true;
            }
        }
        return false;
    }

  
    public function module_permission($module_name)
    {
        $module_perm = $this->CI->Module_model->getPermissionByModulename($module_name);
        return $module_perm;
    }

    public function unautherized()
    {
        $this->CI->load->view('layout/header');
        $this->CI->load->view('unauthorized');
        $this->CI->load->view('layout/footer');
    }

}
