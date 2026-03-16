<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Inventorydashboard extends Admin_Controller
{
    public function index()
    {
        if (!$this->rbac->hasPrivilege('inventory_dashboard', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventorydashboard/index');

        $data = [];
        $data['title'] = 'Inventory Dashboard';

        $item_count = $this->db->count_all('item');
        $stock_entry_count = $this->db->count_all('item_stock');
        $stock_rows = $this->db->select('IFNULL(SUM(quantity),0) as total_qty')->get('item_stock')->row_array();
        $issue_rows = $this->db->select('IFNULL(SUM(quantity),0) as total_qty')->where('is_returned', 1)->get('item_issue')->row_array();

        $data['kpis'] = [
            'item_count' => (int) $item_count,
            'stock_inward_qty' => (int) ($stock_rows['total_qty'] ?? 0),
            'stock_inward_entries' => (int) $stock_entry_count,
            'issued_not_returned_qty' => (int) ($issue_rows['total_qty'] ?? 0),
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/dashboard', $data);
        $this->load->view('layout/footer', $data);
    }

    public function guide()
    {
        if (!$this->rbac->hasPrivilege('inventory_dashboard', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventorydashboard/index');

        $data = [];
        $data['title'] = 'Inventory System Guide';

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/system_guide', $data);
        $this->load->view('layout/footer', $data);
    }
}
