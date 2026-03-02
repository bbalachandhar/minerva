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
        $admin = $this->CI->session->userdata('admin');

        if (!$admin || !isset($admin['roles']) || empty($admin['roles'])) {
            return false;
        }

        $roles = $admin['roles'];
        foreach ($roles as $role_name => $role_id) {
            if ($role_name === 'Super Admin' || (int) $role_id === 7) {
                return true;
            }

            $role_perm = $this->CI->rolepermission_model->getPermissionByRoleandCategory((int) $role_id, trim($category));
            if ($role_perm && array_key_exists($permission, $role_perm) && (int) $role_perm[$permission] === 1) {
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
