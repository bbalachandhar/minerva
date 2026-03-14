<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Inventoryindent extends Admin_Controller
{
    private function ensureIndentFallbackSettingColumns()
    {
        $required = [
            'indent_fallback_use_department_head_l1' => 'TINYINT(1) NOT NULL DEFAULT 1',
            'indent_fallback_l2_staff_id' => 'INT(11) NULL',
            'indent_fallback_superadmin_can_override_l1' => 'TINYINT(1) NOT NULL DEFAULT 1',
        ];

        $existing_rows = $this->db->query('SHOW COLUMNS FROM sch_settings')->result_array();
        $existing_cols = [];
        foreach ($existing_rows as $row) {
            $existing_cols[] = $row['Field'];
        }

        foreach ($required as $column => $definition) {
            if (!in_array($column, $existing_cols, true)) {
                $this->db->query("ALTER TABLE sch_settings ADD COLUMN {$column} {$definition}");
            }
        }
    }

    private function getIndentFallbackSettings()
    {
        $this->ensureIndentFallbackSettingColumns();

        $row = $this->db
            ->select('indent_fallback_use_department_head_l1, indent_fallback_l2_staff_id, indent_fallback_superadmin_can_override_l1')
            ->from('sch_settings')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        return [
            'use_department_head_l1' => isset($row['indent_fallback_use_department_head_l1']) ? (int) $row['indent_fallback_use_department_head_l1'] === 1 : true,
            'l2_staff_id' => isset($row['indent_fallback_l2_staff_id']) ? (int) $row['indent_fallback_l2_staff_id'] : 0,
            'superadmin_can_override_l1' => isset($row['indent_fallback_superadmin_can_override_l1']) ? (int) $row['indent_fallback_superadmin_can_override_l1'] === 1 : true,
        ];
    }

    private function isCurrentUserSuperAdmin()
    {
        $role = json_decode((string) $this->customlib->getStaffRole());
        if (empty($role)) {
            return false;
        }

        $role_name = strtolower(trim((string) ($role->name ?? '')));
        return (int) ($role->id ?? 0) === 7 || $role_name === 'super admin';
    }

    private function getActiveStaffById($staff_id)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0) {
            return null;
        }

        $row = $this->db
            ->select('id, employee_id, name, surname, department, is_active')
            ->from('staff')
            ->where('id', $staff_id)
            ->where('is_active', 1)
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row) ? $row : null;
    }

    private function getActiveDepartmentHeadByDepartmentId($department_id)
    {
        $department_id = (int) $department_id;
        if ($department_id <= 0) {
            return null;
        }

        $row = $this->db
            ->select('staff.id, staff.employee_id, staff.name, staff.surname, staff.department')
            ->from('department')
            ->join('staff', 'staff.id = department.dept_head_id', 'inner')
            ->where('department.id', $department_id)
            ->where('staff.is_active', 1)
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row) ? $row : null;
    }

    private function getDepartmentHeadMapByDepartmentIds($department_ids)
    {
        $department_ids = array_values(array_unique(array_filter(array_map('intval', (array) $department_ids))));
        if (empty($department_ids)) {
            return [];
        }

        $rows = $this->db
            ->select('department.id as department_id, staff.id, staff.employee_id, staff.name, staff.surname')
            ->from('department')
            ->join('staff', 'staff.id = department.dept_head_id', 'inner')
            ->where_in('department.id', $department_ids)
            ->where('staff.is_active', 1)
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['department_id']] = [
                'id' => (int) ($row['id'] ?? 0),
                'employee_id' => (string) ($row['employee_id'] ?? ''),
                'name' => trim((string) (($row['name'] ?? '') . ' ' . ($row['surname'] ?? ''))),
            ];
        }

        return $map;
    }

    private function buildConfiguredIndentApprovalPlan($department_id, $requested_l1_staff_id = 0)
    {
        $settings = $this->getIndentFallbackSettings();
        $l2_staff = $this->getActiveStaffById((int) $settings['l2_staff_id']);

        if (empty($l2_staff)) {
            return [
                'plan' => [],
                'error' => 'Indent Approver L2 fallback is not configured. Update System Settings > Indent Approval Fallback.',
            ];
        }

        $l1_staff = null;
        if (!empty($settings['use_department_head_l1']) && !empty($department_id)) {
            $l1_staff = $this->getActiveDepartmentHeadByDepartmentId((int) $department_id);
        }

        if ($this->isCurrentUserSuperAdmin() && !empty($settings['superadmin_can_override_l1']) && (int) $requested_l1_staff_id > 0) {
            $override_staff = $this->getActiveStaffById((int) $requested_l1_staff_id);
            if (!empty($override_staff)) {
                $l1_staff = $override_staff;
            }
        }

        if (empty($l1_staff)) {
            $l1_staff = $l2_staff;
        }

        $plan = [
            [
                'approval_level' => 1,
                'approver_staff_id' => (int) $l1_staff['id'],
            ],
        ];

        if ((int) $l2_staff['id'] !== (int) $l1_staff['id']) {
            $plan[] = [
                'approval_level' => 2,
                'approver_staff_id' => (int) $l2_staff['id'],
            ];
        }

        return [
            'plan' => $plan,
            'error' => null,
        ];
    }

    private function ensureIndentTables()
    {
        return $this->db->table_exists('inv_indents')
            && $this->db->table_exists('inv_indent_items')
            && $this->db->table_exists('inv_indent_approvals');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventoryindent/index');

        $data = [];
        $data['title'] = 'Inventory Indents';

        if ($this->ensureIndentTables()) {
            $data['indent_rows'] = $this->db
            ->select('i.id, i.indent_no, i.requested_by, i.request_date, i.status, i.priority, i.total_estimated_cost, CONCAT_WS(" ", s.name, s.surname) as requested_by_name')
            ->from('inv_indents i')
            ->join('staff s', 's.id = i.requested_by', 'left')
                ->order_by('id', 'DESC')
                ->limit(100)
                ->get()
                ->result_array();
        } else {
            $data['indent_rows'] = [];
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/indent_list', $data);
        $this->load->view('layout/footer', $data);
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_add')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventoryindent/index');

        if (!$this->ensureIndentTables()) {
            show_error('Inventory indent tables are missing. Please run latest db_updates migration.', 500);
        }

        $data = [];
        $data['title'] = 'Raise Indent';
        $data['itemcatlist'] = $this->itemcategory_model->get();
        $data['staff_list'] = $this->db
            ->select('id, employee_id, name, surname')
            ->from('staff')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->order_by('surname', 'ASC')
            ->get()
            ->result_array();
        $data['is_super_admin'] = $this->isCurrentUserSuperAdmin();
        $data['indent_fallback_settings'] = $this->getIndentFallbackSettings();
        $data['configured_l2_approver'] = $this->getActiveStaffById((int) $data['indent_fallback_settings']['l2_staff_id']);

        $requested_by = (int) $this->customlib->getStaffID();
        $requested_by = $requested_by > 0 ? $requested_by : 1;
        $requester = $this->db
            ->select('department')
            ->from('staff')
            ->where('id', $requested_by)
            ->limit(1)
            ->get()
            ->row_array();
        $requester_department_id = !empty($requester['department']) ? (int) $requester['department'] : 0;
        $data['requester_department_id'] = $requester_department_id;
        $data['department_head_map'] = $this->getDepartmentHeadMapByDepartmentIds([$requester_department_id]);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/indent_create', $data);
        $this->load->view('layout/footer', $data);
    }

    public function store()
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureIndentTables()) {
            show_error('Inventory indent tables are missing. Please run latest db_updates migration.', 500);
        }

        $this->form_validation->set_rules('request_date', 'Request Date', 'trim|required');
        $this->form_validation->set_rules('priority', 'Priority', 'trim|required');
        $this->form_validation->set_rules('quantity', 'Quantity', 'trim|required|numeric|greater_than[0]');

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please provide all mandatory fields.</div>');
            redirect('admin/inventoryindent/create');
        }

        $requested_by = (int) $this->customlib->getStaffID();
        $requested_by = $requested_by > 0 ? $requested_by : 1;
        $requester = $this->db
            ->select('department')
            ->from('staff')
            ->where('id', $requested_by)
            ->limit(1)
            ->get()
            ->row_array();
        $department_id = !empty($requester['department']) ? (int) $requester['department'] : null;

        $request_date = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('request_date')));
        $required_by_date_raw = trim((string) $this->input->post('required_by_date'));
        $required_by_date = $required_by_date_raw !== '' ? date('Y-m-d', $this->customlib->datetostrtotime($required_by_date_raw)) : null;

        $item_id = (int) $this->input->post('item_id');
        $item_name = trim((string) $this->input->post('item_name'));
        $item_category_id = (int) $this->input->post('item_category_id');
        $quantity = (float) $this->input->post('quantity');
        $uom = trim((string) $this->input->post('uom'));
        $estimated_unit_cost = (float) $this->input->post('estimated_unit_cost');
        $estimated_total_cost = round($quantity * $estimated_unit_cost, 2);
        $requested_l1_staff_id = max(0, (int) $this->input->post('approver_staff_id'));

        if ($item_id > 0) {
            $item = $this->db->select('name, unit')->where('id', $item_id)->get('item')->row_array();
            if (!empty($item)) {
                $item_name = (string) ($item['name'] ?? $item_name);
                if ($uom === '') {
                    $uom = (string) ($item['unit'] ?? '');
                }
            }
        }

        if ($item_name === '') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Item name is required.</div>');
            redirect('admin/inventoryindent/create');
        }

        $indent_no = 'IND-' . date('YmdHis') . rand(100, 999);

        $indent_data = [
            'indent_no' => $indent_no,
            'request_date' => $request_date,
            'required_by_date' => $required_by_date,
            'requested_by' => $requested_by,
            'department_id' => !empty($department_id) ? $department_id : null,
            'priority' => trim((string) $this->input->post('priority')),
            'status' => 'submitted',
            'remarks' => (string) $this->input->post('remarks'),
            'total_estimated_cost' => $estimated_total_cost,
        ];

        $approval_plan_result = $this->buildConfiguredIndentApprovalPlan($department_id, $requested_l1_staff_id);
        if (!empty($approval_plan_result['error'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape((string) $approval_plan_result['error']) . '</div>');
            redirect('admin/inventoryindent/create');
        }

        $approval_plan = (array) ($approval_plan_result['plan'] ?? []);
        if (empty($approval_plan)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Unable to resolve indent approvers from current settings.</div>');
            redirect('admin/inventoryindent/create');
        }

        $initial_status = count($approval_plan) > 1 ? 'queued' : 'pending';

        $this->db->trans_start();

        $this->db->insert('inv_indents', $indent_data);
        $indent_id = (int) $this->db->insert_id();

        $line_data = [
            'indent_id' => $indent_id,
            'item_category_id' => $item_category_id > 0 ? $item_category_id : null,
            'item_id' => $item_id > 0 ? $item_id : null,
            'item_name' => $item_name,
            'spec' => (string) $this->input->post('spec'),
            'quantity' => $quantity,
            'uom' => $uom,
            'estimated_unit_cost' => $estimated_unit_cost,
            'estimated_total_cost' => $estimated_total_cost,
        ];
        $this->db->insert('inv_indent_items', $line_data);

        foreach ($approval_plan as $index => $plan_row) {
            $approval_data = [
                'indent_id' => $indent_id,
                'approver_staff_id' => (int) $plan_row['approver_staff_id'],
                'approval_level' => (int) $plan_row['approval_level'],
                'decision' => $index === 0 ? 'pending' : 'queued',
                'comments' => null,
            ];
            $this->db->insert('inv_indent_approvals', $approval_data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to save indent. Please try again.</div>');
            redirect('admin/inventoryindent/create');
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Indent created successfully: ' . html_escape($indent_no) . '</div>');
        redirect('admin/inventoryindent');
    }

    public function approvals()
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventoryindent/approvals');

        $data = [];
        $data['title'] = 'Indent Approvals';

        $staff_id = (int) $this->customlib->getStaffID();

        if ($this->ensureIndentTables()) {
            $data['approval_rows'] = $this->db
            ->select('a.id, a.indent_id, a.approver_staff_id, a.approval_level, a.decision, a.decision_date, a.comments, i.indent_no, i.status as indent_status, i.request_date, i.total_estimated_cost, CONCAT_WS(" ", s.name, s.surname) as approver_name, CONCAT_WS(" ", rs.name, rs.surname) as requested_by_name')
                ->from('inv_indent_approvals a')
                ->join('inv_indents i', 'i.id = a.indent_id', 'left')
                ->join('staff s', 's.id = a.approver_staff_id', 'left')
                ->join('staff rs', 'rs.id = i.requested_by', 'left')
                ->where('a.approver_staff_id', $staff_id)
                ->order_by('a.id', 'DESC')
                ->limit(100)
                ->get()
                ->result_array();
        } else {
            $data['approval_rows'] = [];
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/indent_approvals', $data);
        $this->load->view('layout/footer', $data);
    }

    public function decision($approval_id)
    {
        if (!$this->rbac->hasPrivilege('item_stock', 'can_edit')) {
            access_denied();
        }

        if (!$this->ensureIndentTables()) {
            show_error('Inventory indent tables are missing. Please run latest db_updates migration.', 500);
        }

        $approval_id = (int) $approval_id;
        if ($approval_id <= 0) {
            redirect('admin/inventoryindent/approvals');
        }

        $decision = strtolower(trim((string) $this->input->post('decision')));
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Invalid decision.</div>');
            redirect('admin/inventoryindent/approvals');
        }

        $approval = $this->db->where('id', $approval_id)->get('inv_indent_approvals')->row_array();
        if (empty($approval) || strtolower((string) ($approval['decision'] ?? '')) !== 'pending') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Approval entry not found or already processed.</div>');
            redirect('admin/inventoryindent/approvals');
        }

        $staff_id = (int) $this->customlib->getStaffID();
        if ($staff_id <= 0 || $staff_id !== (int) $approval['approver_staff_id']) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">You are not authorized to decide this indent approval request.</div>');
            redirect('admin/inventoryindent/approvals');
        }

        $this->db->trans_start();

        $this->db->where('id', $approval_id)->update('inv_indent_approvals', [
            'decision' => $decision,
            'decision_date' => date('Y-m-d H:i:s'),
            'comments' => (string) $this->input->post('comments'),
        ]);

        if ($decision === 'approved') {
            $next_queued = $this->db
                ->where('indent_id', (int) $approval['indent_id'])
                ->where('decision', 'queued')
                ->order_by('approval_level', 'ASC')
                ->limit(1)
                ->get('inv_indent_approvals')
                ->row_array();

            if (!empty($next_queued)) {
                $this->db->where('id', (int) $next_queued['id'])->update('inv_indent_approvals', [
                    'decision' => 'pending',
                ]);

                $this->db->where('id', (int) $approval['indent_id'])->update('inv_indents', [
                    'status' => 'pending',
                ]);
            } else {
                $this->db->where('id', (int) $approval['indent_id'])->update('inv_indents', [
                    'status' => 'approved',
                ]);
            }
        } else {
            $this->db->where('indent_id', (int) $approval['indent_id'])
                ->where('decision', 'queued')
                ->update('inv_indent_approvals', [
                    'decision' => 'rejected',
                    'decision_date' => date('Y-m-d H:i:s'),
                    'comments' => 'Auto-rejected because a previous approval level rejected the indent.',
                ]);

            $this->db->where('id', (int) $approval['indent_id'])->update('inv_indents', [
                'status' => 'rejected',
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to update decision.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Indent ' . html_escape($decision) . ' successfully.</div>');
        }

        redirect('admin/inventoryindent/approvals');
    }
}
