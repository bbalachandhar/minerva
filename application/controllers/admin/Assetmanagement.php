<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Assetmanagement extends Admin_Controller
{
    public function register()
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'assetmanagement/register');

        $data = [];
        $data['title'] = 'Asset Register';

        if ($this->db->table_exists('inv_assets')) {
            $data['rows'] = $this->db
                ->select('a.id, a.asset_tag, a.asset_name, a.item_id, a.serial_no, a.current_status, l.location_name')
                ->from('inv_assets a')
                ->join('inv_asset_locations l', 'l.id = a.current_location_id', 'left')
                ->order_by('a.id', 'DESC')
                ->limit(200)
                ->get()
                ->result_array();
        } else {
            $data['rows'] = [];
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/asset_register', $data);
        $this->load->view('layout/footer', $data);
    }

    public function assignment()
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'assetmanagement/assignment');

        $data = [];
        $data['title'] = 'Asset Assignment';

        if ($this->db->table_exists('inv_asset_assignments')) {
            $data['rows'] = $this->db
                ->select('id, asset_id, assignee_type, assignee_id, assigned_on, returned_on, status')
                ->from('inv_asset_assignments')
                ->order_by('id', 'DESC')
                ->limit(200)
                ->get()
                ->result_array();
        } else {
            $data['rows'] = [];
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/asset_assignment', $data);
        $this->load->view('layout/footer', $data);
    }

    public function transfer()
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'assetmanagement/transfer');

        $data = [];
        $data['title'] = 'Asset Transfer';

        if ($this->db->table_exists('inv_asset_transfers')) {
            $data['rows'] = $this->db
                ->select('id, asset_id, from_location_id, to_location_id, transfer_date, transferred_by, status')
                ->from('inv_asset_transfers')
                ->order_by('id', 'DESC')
                ->limit(200)
                ->get()
                ->result_array();
        } else {
            $data['rows'] = [];
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/asset_transfer', $data);
        $this->load->view('layout/footer', $data);
    }

    public function maintenance()
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'assetmanagement/maintenance');

        $data = [];
        $data['title'] = 'Asset Maintenance';

        if ($this->db->table_exists('inv_asset_maintenance_logs')) {
            $data['rows'] = $this->db
                ->select('id, asset_id, maintenance_type, vendor_name, opened_on, closed_on, status, cost_amount')
                ->from('inv_asset_maintenance_logs')
                ->order_by('id', 'DESC')
                ->limit(200)
                ->get()
                ->result_array();
        } else {
            $data['rows'] = [];
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/asset_maintenance', $data);
        $this->load->view('layout/footer', $data);
    }
}
