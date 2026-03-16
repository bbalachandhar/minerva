<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Inventoryprocurement extends Admin_Controller
{
    private function ensurePOFallbackSettingColumns()
    {
        $required = [
            'po_fallback_use_department_head_l1' => 'TINYINT(1) NOT NULL DEFAULT 1',
            'po_fallback_l2_staff_id' => 'INT(11) NULL',
            'po_fallback_superadmin_can_override_l1' => 'TINYINT(1) NOT NULL DEFAULT 1',
        ];

        $existing_rows = $this->db->query("SHOW COLUMNS FROM sch_settings")->result_array();
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

    private function getPOFallbackSettings()
    {
        $this->ensurePOFallbackSettingColumns();

        $row = $this->db
            ->select('po_fallback_use_department_head_l1, po_fallback_l2_staff_id, po_fallback_superadmin_can_override_l1')
            ->from('sch_settings')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        return [
            'use_department_head_l1' => isset($row['po_fallback_use_department_head_l1']) ? (int) $row['po_fallback_use_department_head_l1'] === 1 : true,
            'l2_staff_id' => isset($row['po_fallback_l2_staff_id']) ? (int) $row['po_fallback_l2_staff_id'] : 0,
            'superadmin_can_override_l1' => isset($row['po_fallback_superadmin_can_override_l1']) ? (int) $row['po_fallback_superadmin_can_override_l1'] === 1 : true,
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

    private function buildConfiguredFallbackApprovalPlan($indent, $requested_l1_staff_id = 0)
    {
        $settings = $this->getPOFallbackSettings();
        $l2_staff = $this->getActiveStaffById((int) $settings['l2_staff_id']);

        if (empty($l2_staff)) {
            return [
                'plan' => [],
                'mode' => 'configured_fallback',
                'error' => 'PO Approver L2 fallback is not configured. Update System Settings > PO Approval Fallback.',
            ];
        }

        $l1_staff = null;
        if (!empty($settings['use_department_head_l1']) && !empty($indent['department_id'])) {
            $l1_staff = $this->getActiveDepartmentHeadByDepartmentId((int) $indent['department_id']);
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
            'mode' => 'configured_fallback',
            'error' => null,
        ];
    }

    private function ensurePOApprovalRulesTable()
    {
        return $this->db->table_exists('inv_po_approval_rules');
    }

    private function ensurePOApprovalTable()
    {
        return $this->db->table_exists('inv_po_approvals');
    }

    private function getActivePOApprovalRules($department_id, $total_amount)
    {
        if (!$this->ensurePOApprovalRulesTable()) {
            return [];
        }

        $qb = $this->db
            ->select('*')
            ->from('inv_po_approval_rules')
            ->where('is_active', 1)
            ->where('min_amount <=', (float) $total_amount)
            ->group_start()
                ->where('max_amount IS NULL', null, false)
                ->or_where('max_amount >=', (float) $total_amount)
            ->group_end();

        if (!empty($department_id)) {
            $qb->group_start()
                ->where('department_id IS NULL', null, false)
                ->or_where('department_id', (int) $department_id)
            ->group_end();
        }

        return $qb
            ->order_by('approval_level', 'ASC')
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
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
            ->get()
            ->row_array();

        return !empty($row) ? $row : null;
    }

    private function getStaffByRoleForRule($role_id, $department_id = null)
    {
        $role_id = (int) $role_id;
        if ($role_id <= 0) {
            return null;
        }

        $qb = $this->db
            ->select('staff.id, staff.department')
            ->from('staff')
            ->join('staff_roles', 'staff_roles.staff_id = staff.id', 'inner')
            ->where('staff.is_active', 1)
            ->where('staff_roles.role_id', $role_id);

        if (!empty($department_id)) {
            $qb->where('staff.department', (int) $department_id);
        }

        return $qb
            ->order_by('staff.id', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();
    }

    private function buildApprovalPlanFromRules($department_id, $total_amount)
    {
        $rules = $this->getActivePOApprovalRules($department_id, $total_amount);
        if (empty($rules)) {
            return [];
        }

        $plan_by_level = [];
        $used_staff = [];

        foreach ($rules as $rule) {
            $level = (int) ($rule['approval_level'] ?? 0);
            if ($level <= 0 || isset($plan_by_level[$level])) {
                continue;
            }

            $approver_type = strtolower((string) ($rule['approver_type'] ?? 'staff'));
            $resolved_staff = null;

            if ($approver_type === 'role') {
                $resolved_staff = $this->getStaffByRoleForRule((int) ($rule['approver_role_id'] ?? 0), $rule['department_id'] ?? null);
            } else {
                $resolved_staff = $this->getActiveStaffById((int) ($rule['approver_staff_id'] ?? 0));
            }

            if (empty($resolved_staff)) {
                continue;
            }

            $staff_id = (int) $resolved_staff['id'];
            if (isset($used_staff[$staff_id])) {
                continue;
            }

            $plan_by_level[$level] = [
                'approval_level' => $level,
                'approver_staff_id' => $staff_id,
            ];
            $used_staff[$staff_id] = true;
        }

        if (empty($plan_by_level)) {
            return [];
        }

        ksort($plan_by_level);
        return array_values($plan_by_level);
    }

    private function ensureProcurementTables()
    {
        return $this->db->table_exists('inv_purchase_orders')
            && $this->db->table_exists('inv_purchase_order_items')
            && $this->db->table_exists('inv_goods_receipts')
            && $this->db->table_exists('inv_goods_receipt_items');
    }

    private function getPoItemsWithBalance($po_id)
    {
        $rows = $this->db
            ->select('pi.id, pi.po_id, pi.item_id, pi.item_name, pi.quantity, pi.uom, pi.unit_price, pi.line_total, COALESCE(SUM(gri.received_qty), 0) as already_received_qty', false)
            ->from('inv_purchase_order_items pi')
            ->join('inv_goods_receipt_items gri', 'gri.po_item_id = pi.id', 'left')
            ->where('pi.po_id', (int) $po_id)
            ->group_by('pi.id')
            ->order_by('pi.id', 'ASC')
            ->get()
            ->result_array();

        foreach ($rows as &$row) {
            $ordered = (float) ($row['quantity'] ?? 0);
            $received = (float) ($row['already_received_qty'] ?? 0);
            $row['remaining_qty'] = max(0, $ordered - $received);
        }
        unset($row);

        return $rows;
    }

    private function createAssetsForReceiptLine($grn_no, $po, $po_line, $accepted_qty, $grn_date, $item_stock_id = null)
    {
        if (!$this->db->table_exists('inv_assets') || empty($po_line['item_id']) || $accepted_qty <= 0) {
            return;
        }

        $item = $this->db
            ->select('item.id, item.name, item.item_category_id, item_category.is_asset, item_category.asset_tracking_mode')
            ->from('item')
            ->join('item_category', 'item_category.id = item.item_category_id', 'left')
            ->where('item.id', (int) $po_line['item_id'])
            ->get()
            ->row_array();

        if (empty($item) || (int) ($item['is_asset'] ?? 0) !== 1) {
            return;
        }

        $qty_for_assets = (int) floor((float) $accepted_qty);
        if ($qty_for_assets <= 0) {
            return;
        }

        $tracking_mode = strtolower((string) ($item['asset_tracking_mode'] ?? 'bulk'));
        $asset_units = $tracking_mode === 'bulk' ? 1 : $qty_for_assets;
        $base_tag = 'AST-' . date('YmdHis') . '-' . (int) $po_line['item_id'];

        for ($i = 1; $i <= $asset_units; $i++) {
            $asset_tag = $tracking_mode === 'bulk' ? ($base_tag . '-B') : ($base_tag . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));

            $insert = [
                'asset_tag' => $asset_tag,
                'asset_name' => (string) ($item['name'] ?? $po_line['item_name']),
                'item_id' => (int) $po_line['item_id'],
                'item_stock_id' => !empty($item_stock_id) ? (int) $item_stock_id : null,
                'category_id' => !empty($item['item_category_id']) ? (int) $item['item_category_id'] : null,
                'supplier_id' => !empty($po['supplier_id']) ? (int) $po['supplier_id'] : null,
                'purchase_order_id' => (int) $po['id'],
                'purchase_date' => $grn_date,
                'purchase_cost' => (float) ($po_line['unit_price'] ?? 0),
                'capitalization_date' => $grn_date,
                'current_status' => 'in_stock',
                'remarks' => 'Auto created from GRN ' . $grn_no,
            ];

            if ($tracking_mode === 'bulk') {
                $insert['remarks'] .= ' (bulk quantity: ' . $qty_for_assets . ')';
            }

            $this->db->insert('inv_assets', $insert);
        }
    }

    public function purchaseorders()
    {
        if (!$this->rbac->hasPrivilege('purchase_orders', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventoryprocurement/purchaseorders');

        $data = [];
        $data['title'] = 'Purchase Orders';

        if ($this->ensureProcurementTables()) {
            $data['rows'] = $this->db
            ->select('p.id, p.po_no, p.po_date, p.supplier_id, p.status, p.subtotal, p.tax_amount, p.total_amount, s.item_supplier as supplier_name, i.indent_no')
            ->from('inv_purchase_orders p')
            ->join('item_supplier s', 's.id = p.supplier_id', 'left')
            ->join('inv_indents i', 'i.id = p.indent_id', 'left')
                ->order_by('id', 'DESC')
                ->limit(100)
                ->get()
                ->result_array();
        } else {
            $data['rows'] = [];
        }

        $data['has_po_approval'] = $this->ensurePOApprovalTable();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/purchase_orders', $data);
        $this->load->view('layout/footer', $data);
    }

    public function createpo()
    {
        if (!$this->rbac->hasPrivilege('purchase_orders', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureProcurementTables()) {
            show_error('Procurement tables are missing. Please run latest db_updates migration.', 500);
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventoryprocurement/purchaseorders');

        $data = [];
        $data['title'] = 'Create Purchase Order';
        $data['suppliers'] = $this->itemsupplier_model->get();
        $data['approved_indents'] = $this->db
            ->select('id, indent_no, request_date, total_estimated_cost, department_id')
            ->from('inv_indents')
            ->where('status', 'approved')
            ->order_by('id', 'DESC')
            ->limit(200)
            ->get()
            ->result_array();
        $data['approvers'] = $this->db
            ->select('id, employee_id, name, surname')
            ->from('staff')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->limit(500)
            ->get()
            ->result_array();
        $data['has_rule_engine'] = $this->ensurePOApprovalRulesTable();
        $data['is_super_admin'] = $this->isCurrentUserSuperAdmin();
        $data['po_fallback_settings'] = $this->getPOFallbackSettings();
        $data['configured_l2_approver'] = $this->getActiveStaffById((int) $data['po_fallback_settings']['l2_staff_id']);

        $department_ids = [];
        foreach ($data['approved_indents'] as $indent_row) {
            if (!empty($indent_row['department_id'])) {
                $department_ids[] = (int) $indent_row['department_id'];
            }
        }
        $data['department_head_map'] = $this->getDepartmentHeadMapByDepartmentIds($department_ids);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/purchase_order_create', $data);
        $this->load->view('layout/footer', $data);
    }

    public function storepo()
    {
        if (!$this->rbac->hasPrivilege('purchase_orders', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureProcurementTables()) {
            show_error('Procurement tables are missing. Please run latest db_updates migration.', 500);
        }

        $this->form_validation->set_rules('po_date', 'PO Date', 'trim|required');
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'trim|required|integer');
        $this->form_validation->set_rules('indent_id', 'Indent', 'trim|required|integer');

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please fill all required PO fields.</div>');
            redirect('admin/inventoryprocurement/createpo');
        }

        $indent_id = (int) $this->input->post('indent_id');
        $supplier_id = (int) $this->input->post('supplier_id');
        $tax_percent = (float) $this->input->post('tax_percent');
        $approver_staff_id = (int) $this->input->post('approver_staff_id');

        $indent = $this->db->where('id', $indent_id)->get('inv_indents')->row_array();
        if (empty($indent) || strtolower((string) $indent['status']) !== 'approved') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Selected indent must be in approved status.</div>');
            redirect('admin/inventoryprocurement/createpo');
        }

        $indent_items = $this->db->where('indent_id', $indent_id)->get('inv_indent_items')->result_array();
        if (empty($indent_items)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No line items found in selected indent.</div>');
            redirect('admin/inventoryprocurement/createpo');
        }

        $subtotal = 0.00;
        foreach ($indent_items as $line) {
            $subtotal += (float) ($line['estimated_total_cost'] ?? 0);
        }

        $tax_amount = round(($subtotal * $tax_percent) / 100, 2);
        $total_amount = round($subtotal + $tax_amount, 2);

        $approval_mode = 'configured_fallback';
        $approval_plan = [];
        if ($this->ensurePOApprovalTable()) {
            $approval_plan = $this->buildApprovalPlanFromRules($indent['department_id'] ?? null, $total_amount);
            if (empty($approval_plan)) {
                $fallback_plan = $this->buildConfiguredFallbackApprovalPlan($indent, $approver_staff_id);
                if (!empty($fallback_plan['error'])) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($fallback_plan['error']) . '</div>');
                    redirect('admin/inventoryprocurement/createpo');
                }
                $approval_plan = $fallback_plan['plan'];
            } else {
                $approval_mode = 'auto_matrix';
            }
        } else {
            $approval_mode = 'direct_approval';
        }

        if ($this->ensurePOApprovalTable() && empty($approval_plan)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No approver found. Configure approval rules or PO fallback settings.</div>');
            redirect('admin/inventoryprocurement/createpo');
        }

        $po_no = 'PO-' . date('YmdHis') . rand(100, 999);
        $po_date = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('po_date')));
        $expected_delivery_raw = trim((string) $this->input->post('expected_delivery_date'));
        $expected_delivery_date = $expected_delivery_raw !== '' ? date('Y-m-d', $this->customlib->datetostrtotime($expected_delivery_raw)) : null;

        $created_by = (int) $this->customlib->getStaffID();
        $created_by = $created_by > 0 ? $created_by : null;

        $this->db->trans_start();

        $this->db->insert('inv_purchase_orders', [
            'po_no' => $po_no,
            'po_date' => $po_date,
            'indent_id' => $indent_id,
            'supplier_id' => $supplier_id,
            'status' => $this->ensurePOApprovalTable() ? 'pending_approval' : 'approved',
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $tax_amount,
            'total_amount' => $total_amount,
            'expected_delivery_date' => $expected_delivery_date,
            'notes' => (string) $this->input->post('notes'),
            'created_by' => $created_by,
            'approved_by' => $this->ensurePOApprovalTable() ? null : $created_by,
            'approved_at' => $this->ensurePOApprovalTable() ? null : date('Y-m-d H:i:s'),
        ]);
        $po_id = (int) $this->db->insert_id();

        foreach ($indent_items as $line) {
            $qty = (float) ($line['quantity'] ?? 0);
            $unit_price = (float) ($line['estimated_unit_cost'] ?? 0);
            $line_subtotal = round($qty * $unit_price, 2);
            $line_tax = round(($line_subtotal * $tax_percent) / 100, 2);
            $line_total = round($line_subtotal + $line_tax, 2);

            $this->db->insert('inv_purchase_order_items', [
                'po_id' => $po_id,
                'indent_item_id' => (int) $line['id'],
                'item_id' => !empty($line['item_id']) ? (int) $line['item_id'] : null,
                'item_name' => (string) $line['item_name'],
                'quantity' => $qty,
                'uom' => (string) ($line['uom'] ?? ''),
                'unit_price' => $unit_price,
                'tax_percent' => $tax_percent,
                'tax_amount' => $line_tax,
                'line_total' => $line_total,
            ]);
        }

        if ($this->ensurePOApprovalTable()) {
            foreach ($approval_plan as $idx => $plan_row) {
                $this->db->insert('inv_po_approvals', [
                    'po_id' => $po_id,
                    'approval_level' => (int) $plan_row['approval_level'],
                    'approver_staff_id' => (int) $plan_row['approver_staff_id'],
                    'decision' => $idx === 0 ? 'pending' : 'queued',
                ]);
            }

            $this->db->where('id', $indent_id)->update('inv_indents', ['status' => 'po_pending_approval']);
        } else {
            $this->db->where('id', $indent_id)->update('inv_indents', ['status' => 'po_created']);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to create purchase order.</div>');
            redirect('admin/inventoryprocurement/createpo');
        }

        $approval_mode_label = $approval_mode === 'auto_matrix' ? ' (auto matrix)' : ' (configured fallback)';
        $po_message = $this->ensurePOApprovalTable()
            ? 'Purchase Order created and sent for approval' . $approval_mode_label . ': '
            : 'Purchase Order created successfully: ';
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $po_message . html_escape($po_no) . '</div>');
        redirect('admin/inventoryprocurement/purchaseorders');
    }

    public function poapprovals()
    {
        if (!$this->rbac->hasPrivilege('po_approvals', 'can_view')) {
            access_denied();
        }

        if (!$this->ensurePOApprovalTable()) {
            show_error('PO approval table is missing. Please run latest db_updates migration.', 500);
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventoryprocurement/poapprovals');

        $staff_id = (int) $this->customlib->getStaffID();

        $data = [];
        $data['title'] = 'PO Approvals';
        $data['rows'] = $this->db
            ->select('a.id, a.po_id, a.approval_level, a.approver_staff_id, a.decision, a.decision_date, a.comments, p.po_no, p.po_date, p.total_amount, s.item_supplier as supplier_name')
            ->from('inv_po_approvals a')
            ->join('inv_purchase_orders p', 'p.id = a.po_id', 'inner')
            ->join('item_supplier s', 's.id = p.supplier_id', 'left')
            ->where('a.approver_staff_id', $staff_id)
            ->order_by('a.id', 'DESC')
            ->limit(200)
            ->get()
            ->result_array();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/purchase_order_approvals', $data);
        $this->load->view('layout/footer', $data);
    }

    public function podecision($approval_id = null)
    {
        if (!$this->rbac->hasPrivilege('po_approvals', 'can_edit')) {
            access_denied();
        }

        if (!$this->ensurePOApprovalTable()) {
            show_error('PO approval table is missing. Please run latest db_updates migration.', 500);
        }

        $approval_id = (int) $approval_id;
        $decision = strtolower(trim((string) $this->input->post('decision')));
        $comments = trim((string) $this->input->post('comments'));

        if (!in_array($decision, ['approved', 'rejected'], true)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Invalid decision provided.</div>');
            redirect('admin/inventoryprocurement/poapprovals');
        }

        $approval = $this->db->where('id', $approval_id)->get('inv_po_approvals')->row_array();
        if (empty($approval) || strtolower((string) ($approval['decision'] ?? '')) !== 'pending') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Approval row not found or already processed.</div>');
            redirect('admin/inventoryprocurement/poapprovals');
        }

        $staff_id = (int) $this->customlib->getStaffID();
        if ($staff_id <= 0 || $staff_id !== (int) $approval['approver_staff_id']) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">You are not authorized to decide this approval request.</div>');
            redirect('admin/inventoryprocurement/poapprovals');
        }

        $po = $this->db->where('id', (int) $approval['po_id'])->get('inv_purchase_orders')->row_array();
        if (empty($po)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Related purchase order not found.</div>');
            redirect('admin/inventoryprocurement/poapprovals');
        }

        $this->db->trans_start();

        $this->db->where('id', $approval_id)->update('inv_po_approvals', [
            'decision' => $decision,
            'decision_date' => date('Y-m-d H:i:s'),
            'comments' => $comments,
        ]);

        if ($decision === 'approved') {
            $next_queued = $this->db
                ->where('po_id', (int) $approval['po_id'])
                ->where('decision', 'queued')
                ->order_by('approval_level', 'ASC')
                ->limit(1)
                ->get('inv_po_approvals')
                ->row_array();

            if (!empty($next_queued)) {
                $this->db->where('id', (int) $next_queued['id'])->update('inv_po_approvals', [
                    'decision' => 'pending',
                ]);
            }

            $open_count = (int) $this->db
                ->where('po_id', (int) $approval['po_id'])
                ->where_in('decision', ['pending', 'queued'])
                ->count_all_results('inv_po_approvals');

            if ($open_count === 0) {
                $approved_by = $staff_id > 0 ? $staff_id : null;

                $this->db->where('id', (int) $approval['po_id'])->update('inv_purchase_orders', [
                    'status' => 'approved',
                    'approved_by' => $approved_by,
                    'approved_at' => date('Y-m-d H:i:s'),
                ]);

                if (!empty($po['indent_id'])) {
                    $this->db->where('id', (int) $po['indent_id'])->update('inv_indents', ['status' => 'po_created']);
                }
            }
        } else {
            $this->db->where('id', (int) $approval['po_id'])->update('inv_purchase_orders', [
                'status' => 'rejected',
                'approved_by' => null,
                'approved_at' => null,
            ]);

            if (!empty($po['indent_id'])) {
                $this->db->where('id', (int) $po['indent_id'])->update('inv_indents', ['status' => 'po_rejected']);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to save PO approval decision.</div>');
            redirect('admin/inventoryprocurement/poapprovals');
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">PO decision updated successfully.</div>');
        redirect('admin/inventoryprocurement/poapprovals');
    }

    public function goodsreceipts()
    {
        if (!$this->rbac->hasPrivilege('goods_receipts', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventoryprocurement/goodsreceipts');

        $data = [];
        $data['title'] = 'Goods Receipts (GRN)';

        if ($this->ensureProcurementTables()) {
            $data['rows'] = $this->db
            ->select('g.id, g.grn_no, g.grn_date, g.po_id, g.received_by, g.status, p.po_no, CONCAT_WS(" ", s.name, s.surname) as receiver_name')
            ->from('inv_goods_receipts g')
            ->join('inv_purchase_orders p', 'p.id = g.po_id', 'left')
            ->join('staff s', 's.id = g.received_by', 'left')
                ->order_by('id', 'DESC')
                ->limit(100)
                ->get()
                ->result_array();
        } else {
            $data['rows'] = [];
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/goods_receipts', $data);
        $this->load->view('layout/footer', $data);
    }

    public function creategrn()
    {
        if (!$this->rbac->hasPrivilege('goods_receipts', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureProcurementTables()) {
            show_error('Procurement tables are missing. Please run latest db_updates migration.', 500);
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'inventoryprocurement/goodsreceipts');

        $data = [];
        $data['title'] = 'Create GRN';
        $data['stores'] = $this->itemstore_model->get();
        $data['selected_po_id'] = (int) $this->input->get('po_id');
        $data['po_rows'] = $this->db
            ->select('id, po_no, po_date, status, total_amount')
            ->from('inv_purchase_orders')
            ->where_in('status', ['approved', 'issued', 'partially_received'])
            ->order_by('id', 'DESC')
            ->limit(200)
            ->get()
            ->result_array();
        $data['po_lines'] = [];

        if ($data['selected_po_id'] > 0) {
            $lines = $this->getPoItemsWithBalance($data['selected_po_id']);
            foreach ($lines as $line) {
                if ((float) ($line['remaining_qty'] ?? 0) > 0) {
                    $data['po_lines'][] = $line;
                }
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/inventory/goods_receipt_create', $data);
        $this->load->view('layout/footer', $data);
    }

    public function poitems($po_id = null)
    {
        if (!$this->rbac->hasPrivilege('goods_receipts', 'can_view')) {
            access_denied();
        }

        if (!$this->ensureProcurementTables()) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Procurement tables are missing.']));
            return;
        }

        $po_id = (int) $po_id;
        $lines = $po_id > 0 ? $this->getPoItemsWithBalance($po_id) : [];

        $payload = [];
        foreach ($lines as $line) {
            if ((float) ($line['remaining_qty'] ?? 0) <= 0) {
                continue;
            }
            $payload[] = [
                'po_item_id' => (int) $line['id'],
                'item_id' => !empty($line['item_id']) ? (int) $line['item_id'] : null,
                'item_name' => (string) ($line['item_name'] ?? ''),
                'uom' => (string) ($line['uom'] ?? ''),
                'ordered_qty' => (float) ($line['quantity'] ?? 0),
                'already_received_qty' => (float) ($line['already_received_qty'] ?? 0),
                'remaining_qty' => (float) ($line['remaining_qty'] ?? 0),
                'unit_price' => (float) ($line['unit_price'] ?? 0),
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'rows' => $payload]));
    }

    public function storegrn()
    {
        if (!$this->rbac->hasPrivilege('goods_receipts', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureProcurementTables()) {
            show_error('Procurement tables are missing. Please run latest db_updates migration.', 500);
        }

        $this->form_validation->set_rules('grn_date', 'GRN Date', 'trim|required');
        $this->form_validation->set_rules('po_id', 'Purchase Order', 'trim|required|integer');

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please fill all required GRN fields.</div>');
            redirect('admin/inventoryprocurement/creategrn');
        }

        $po_id = (int) $this->input->post('po_id');
        $store_id = (int) $this->input->post('store_id');
        $po = $this->db->where('id', $po_id)->get('inv_purchase_orders')->row_array();

        if (empty($po)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Selected PO not found.</div>');
            redirect('admin/inventoryprocurement/creategrn');
        }

        $po_items = $this->db->where('po_id', $po_id)->get('inv_purchase_order_items')->result_array();
        if (empty($po_items)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No PO line items found.</div>');
            redirect('admin/inventoryprocurement/creategrn');
        }

        $line_ids = (array) $this->input->post('po_item_id');
        $received_qty_list = (array) $this->input->post('received_qty');
        $accepted_qty_list = (array) $this->input->post('accepted_qty');
        $rejected_qty_list = (array) $this->input->post('rejected_qty');
        $remarks_list = (array) $this->input->post('line_remarks');

        if (empty($line_ids)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please add at least one GRN line item.</div>');
            redirect('admin/inventoryprocurement/creategrn?po_id=' . $po_id);
        }

        $po_balance_lines = $this->getPoItemsWithBalance($po_id);
        $line_lookup = [];
        foreach ($po_balance_lines as $line) {
            $line_lookup[(int) $line['id']] = $line;
        }

        $grn_no = 'GRN-' . date('YmdHis') . rand(100, 999);
        $grn_date = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('grn_date')));
        $invoice_date_raw = trim((string) $this->input->post('invoice_date'));
        $invoice_date = $invoice_date_raw !== '' ? date('Y-m-d', $this->customlib->datetostrtotime($invoice_date_raw)) : null;
        $received_by = (int) $this->customlib->getStaffID();
        $received_by = $received_by > 0 ? $received_by : 1;

        $this->db->trans_start();

        $this->db->insert('inv_goods_receipts', [
            'grn_no' => $grn_no,
            'grn_date' => $grn_date,
            'po_id' => $po_id,
            'received_by' => $received_by,
            'store_id' => $store_id > 0 ? $store_id : null,
            'invoice_no' => (string) $this->input->post('invoice_no'),
            'invoice_date' => $invoice_date,
            'status' => 'received',
            'notes' => (string) $this->input->post('notes'),
        ]);
        $grn_id = (int) $this->db->insert_id();

        $accepted_total_for_grn = 0;
        $created_rows = 0;

        foreach ($line_ids as $idx => $line_id) {
            $po_item_id = (int) $line_id;
            $received_qty = (float) ($received_qty_list[$idx] ?? 0);
            $accepted_qty = (float) ($accepted_qty_list[$idx] ?? 0);
            $rejected_qty = (float) ($rejected_qty_list[$idx] ?? 0);
            $line_remarks = trim((string) ($remarks_list[$idx] ?? ''));

            if ($received_qty <= 0) {
                continue;
            }

            if (!isset($line_lookup[$po_item_id])) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Invalid PO line submitted for GRN.</div>');
                redirect('admin/inventoryprocurement/creategrn?po_id=' . $po_id);
            }

            $line = $line_lookup[$po_item_id];
            $remaining_qty = (float) ($line['remaining_qty'] ?? 0);

            if ($received_qty - $remaining_qty > 0.0001) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Received quantity exceeds remaining quantity for one or more lines.</div>');
                redirect('admin/inventoryprocurement/creategrn?po_id=' . $po_id);
            }

            if ($accepted_qty < 0 || $rejected_qty < 0 || (($accepted_qty + $rejected_qty) - $received_qty) > 0.0001) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Accepted + Rejected should be less than or equal to Received for each line.</div>');
                redirect('admin/inventoryprocurement/creategrn?po_id=' . $po_id);
            }

            $qc_status = 'accepted';
            if ($accepted_qty <= 0 && $rejected_qty > 0) {
                $qc_status = 'rejected';
            } elseif ($accepted_qty > 0 && $rejected_qty > 0) {
                $qc_status = 'partial';
            }

            $line_total = round($accepted_qty * (float) ($line['unit_price'] ?? 0), 2);

            $this->db->insert('inv_goods_receipt_items', [
                'grn_id' => $grn_id,
                'po_item_id' => $po_item_id,
                'item_id' => !empty($line['item_id']) ? (int) $line['item_id'] : null,
                'received_qty' => $received_qty,
                'accepted_qty' => $accepted_qty,
                'rejected_qty' => $rejected_qty,
                'unit_cost' => (float) ($line['unit_price'] ?? 0),
                'line_total' => $line_total,
                'qc_status' => $qc_status,
                'remarks' => $line_remarks !== '' ? $line_remarks : null,
            ]);

            if (!empty($line['item_id']) && $accepted_qty > 0) {
                $stock_qty = (int) floor($accepted_qty);
                $item_stock_id = null;
                if ($stock_qty > 0) {
                    $this->db->insert('item_stock', [
                        'item_id' => (int) $line['item_id'],
                        'supplier_id' => !empty($po['supplier_id']) ? (int) $po['supplier_id'] : null,
                        'store_id' => $store_id > 0 ? $store_id : null,
                        'symbol' => '+',
                        'quantity' => $stock_qty,
                        'purchase_price' => (float) ($line['unit_price'] ?? 0),
                        'date' => $grn_date,
                        'attachment' => null,
                        'description' => 'Auto inward from GRN ' . $grn_no,
                    ]);

                    $item_stock_id = (int) $this->db->insert_id();
                }

                $this->createAssetsForReceiptLine($grn_no, $po, $line, $accepted_qty, $grn_date, $item_stock_id);
            }

            $accepted_total_for_grn += $accepted_qty;
            $created_rows++;
        }

        if ($created_rows === 0) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No valid GRN lines found with received quantity greater than zero.</div>');
            redirect('admin/inventoryprocurement/creategrn?po_id=' . $po_id);
        }

        $remaining_summary = $this->getPoItemsWithBalance($po_id);
        $remaining_total = 0;
        foreach ($remaining_summary as $line) {
            $remaining_total += (float) ($line['remaining_qty'] ?? 0);
        }

        $po_status = $remaining_total > 0 ? 'partially_received' : 'received';
        $grn_status = $po_status === 'received' ? 'received' : 'partially_received';

        $this->db->where('id', $grn_id)->update('inv_goods_receipts', ['status' => $grn_status]);
        $this->db->where('id', $po_id)->update('inv_purchase_orders', ['status' => $po_status]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to create GRN.</div>');
            redirect('admin/inventoryprocurement/creategrn');
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">GRN created successfully: ' . html_escape($grn_no) . ' (Accepted Qty: ' . html_escape((string) $accepted_total_for_grn) . ')</div>');
        redirect('admin/inventoryprocurement/goodsreceipts');
    }
}
