<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Assetmanagement extends Admin_Controller
{
    private function ensureAssetLifecycleTables()
    {
        return $this->db->table_exists('inv_assets')
            && $this->db->table_exists('inv_asset_assignments')
            && $this->db->table_exists('inv_asset_transfers')
            && $this->db->table_exists('inv_asset_maintenance_logs');
    }

    private function currentStaffId()
    {
        $staff_id = (int) $this->customlib->getStaffID();
        return $staff_id > 0 ? $staff_id : 1;
    }

    private function parseDateValue($value, $default_today = false)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $default_today ? date('Y-m-d') : null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        $school_timestamp = $this->customlib->datetostrtotime($value);
        if (!empty($school_timestamp)) {
            return date('Y-m-d', $school_timestamp);
        }

        $ts = strtotime($value);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    private function getAssetRowsForForm()
    {
        if (!$this->db->table_exists('inv_assets')) {
            return [];
        }

        return $this->db
            ->select('id, asset_tag, asset_name, current_status, current_location_id, assigned_to_staff_id')
            ->from('inv_assets')
            ->order_by('asset_tag', 'ASC')
            ->get()
            ->result_array();
    }

    private function getLocationRowsForForm()
    {
        if (!$this->db->table_exists('inv_asset_locations')) {
            return [];
        }

        return $this->db
            ->select('id, location_code, location_name')
            ->from('inv_asset_locations')
            ->where('is_active', 1)
            ->order_by('location_name', 'ASC')
            ->get()
            ->result_array();
    }

    private function getStaffRowsForForm()
    {
        return $this->db
            ->select('id, employee_id, name, surname')
            ->from('staff')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->order_by('surname', 'ASC')
            ->get()
            ->result_array();
    }

    private function getCsvRowsFromUpload($field_name)
    {
        if (empty($_FILES[$field_name]['tmp_name']) || !is_uploaded_file($_FILES[$field_name]['tmp_name'])) {
            return [null, 'Please upload a valid CSV file.'];
        }

        $handle = fopen($_FILES[$field_name]['tmp_name'], 'r');
        if ($handle === false) {
            return [null, 'Unable to read uploaded file.'];
        }

        $header = fgetcsv($handle);
        if (empty($header)) {
            fclose($handle);
            return [null, 'CSV header is missing.'];
        }

        $header = array_map(function ($val) {
            return strtolower(trim((string) $val));
        }, $header);

        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            if (empty(array_filter($line, function ($v) {
                return trim((string) $v) !== '';
            }))) {
                continue;
            }

            $assoc = [];
            foreach ($header as $idx => $key) {
                $assoc[$key] = isset($line[$idx]) ? trim((string) $line[$idx]) : '';
            }
            $rows[] = $assoc;
        }

        fclose($handle);
        return [$rows, null];
    }

    public function register()
    {
        if (!$this->rbac->hasPrivilege('asset_register', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'assetmanagement/register');

        $data = [];
        $data['title'] = 'Asset Register';
        $data['location_rows'] = $this->getLocationRowsForForm();
        $data['staff_rows'] = $this->getStaffRowsForForm();

        $data['filters'] = [
            'status' => trim((string) $this->input->get('status')),
            'assignee_type' => trim((string) $this->input->get('assignee_type')),
            'location_id' => (int) $this->input->get('location_id'),
            'staff_id' => (int) $this->input->get('staff_id'),
            'warranty_state' => trim((string) $this->input->get('warranty_state')),
            'maintenance_open' => trim((string) $this->input->get('maintenance_open')),
            'license_state' => trim((string) $this->input->get('license_state')),
        ];

        if ($this->db->table_exists('inv_assets')) {
            $query = $this->db
                ->select('a.id, a.asset_tag, a.asset_name, a.item_id, a.serial_no, a.current_status, a.assigned_to_type, a.warranty_start, a.warranty_end, a.current_location_id, a.item_stock_id, l.location_code, l.location_name, s.employee_id as assigned_employee_id, s.name as assigned_name, s.surname as assigned_surname, st.license_key, st.license_valid_from, st.license_valid_till, (SELECT COUNT(1) FROM inv_asset_maintenance_logs m WHERE m.asset_id = a.id AND m.status <> "closed") as open_maintenance_count', false)
                ->from('inv_assets a')
                ->join('inv_asset_locations l', 'l.id = a.current_location_id', 'left')
                ->join('staff s', 's.id = a.assigned_to_staff_id', 'left')
                ->join('item_stock st', 'st.id = a.item_stock_id', 'left');

            if ($data['filters']['status'] !== '') {
                $query->where('a.current_status', $data['filters']['status']);
            }

            if ($data['filters']['assignee_type'] === 'staff') {
                $query->where('a.assigned_to_type', 'staff');
            } elseif ($data['filters']['assignee_type'] === 'place') {
                $query->where('a.assigned_to_type', 'place');
            } elseif ($data['filters']['assignee_type'] === 'unassigned') {
                $query->where('(a.assigned_to_type IS NULL OR a.assigned_to_type = "")', null, false);
            }

            if ($data['filters']['location_id'] > 0) {
                $query->where('a.current_location_id', $data['filters']['location_id']);
            }

            if ($data['filters']['staff_id'] > 0) {
                $query->where('a.assigned_to_staff_id', $data['filters']['staff_id']);
            }

            if ($data['filters']['warranty_state'] === 'expired') {
                $query->where('a.warranty_end IS NOT NULL', null, false);
                $query->where('a.warranty_end <', date('Y-m-d'));
            } elseif ($data['filters']['warranty_state'] === 'due_30') {
                $query->where('a.warranty_end IS NOT NULL', null, false);
                $query->where('a.warranty_end >=', date('Y-m-d'));
                $query->where('a.warranty_end <=', date('Y-m-d', strtotime('+30 days')));
            } elseif ($data['filters']['warranty_state'] === 'active') {
                $query->where('a.warranty_end IS NOT NULL', null, false);
                $query->where('a.warranty_end >=', date('Y-m-d'));
            } elseif ($data['filters']['warranty_state'] === 'missing') {
                $query->where('a.warranty_end IS NULL', null, false);
            }

            if ($data['filters']['maintenance_open'] === 'yes') {
                $query->where('EXISTS (SELECT 1 FROM inv_asset_maintenance_logs m2 WHERE m2.asset_id = a.id AND m2.status <> "closed")', null, false);
            } elseif ($data['filters']['maintenance_open'] === 'no') {
                $query->where('NOT EXISTS (SELECT 1 FROM inv_asset_maintenance_logs m2 WHERE m2.asset_id = a.id AND m2.status <> "closed")', null, false);
            }

            if ($data['filters']['license_state'] === 'missing') {
                $query->where('(st.license_key IS NULL OR st.license_key = "")', null, false);
            } elseif ($data['filters']['license_state'] === 'active') {
                $query->where('st.license_key IS NOT NULL', null, false);
                $query->where('st.license_key <>', '');
                $query->where('st.license_valid_till IS NOT NULL', null, false);
                $query->where('st.license_valid_till >=', date('Y-m-d'));
            } elseif ($data['filters']['license_state'] === 'expired') {
                $query->where('st.license_key IS NOT NULL', null, false);
                $query->where('st.license_key <>', '');
                $query->where('st.license_valid_till IS NOT NULL', null, false);
                $query->where('st.license_valid_till <', date('Y-m-d'));
            } elseif ($data['filters']['license_state'] === 'due_30') {
                $query->where('st.license_key IS NOT NULL', null, false);
                $query->where('st.license_key <>', '');
                $query->where('st.license_valid_till IS NOT NULL', null, false);
                $query->where('st.license_valid_till >=', date('Y-m-d'));
                $query->where('st.license_valid_till <=', date('Y-m-d', strtotime('+30 days')));
            }

            $data['rows'] = $query
                ->order_by('a.id', 'DESC')
                ->limit(500)
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
        if (!$this->rbac->hasPrivilege('asset_assignment', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'assetmanagement/assignment');

        $data = [];
        $data['title'] = 'Asset Assignment';
        $data['asset_rows'] = $this->getAssetRowsForForm();
        $data['staff_rows'] = $this->getStaffRowsForForm();
        $data['location_rows'] = $this->getLocationRowsForForm();

        if ($this->db->table_exists('inv_asset_assignments')) {
            $data['rows'] = $this->db
            ->select('a.id, a.asset_id, a.assignee_type, a.assignee_id, a.assigned_on, a.returned_on, a.status, s.name, s.surname, s.employee_id, x.asset_tag, x.asset_name, l.location_code as assignee_location_code, l.location_name as assignee_location_name')
                ->from('inv_asset_assignments a')
                ->join('staff s', 's.id = a.assignee_id', 'left')
                ->join('inv_assets x', 'x.id = a.asset_id', 'left')
            ->join('inv_asset_locations l', 'l.id = a.assignee_id', 'left')
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

    public function storeassignment()
    {
        if (!$this->rbac->hasPrivilege('asset_assignment', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureAssetLifecycleTables()) {
            show_error('Asset lifecycle tables are missing. Please run latest db_updates migration.', 500);
        }

        $asset_id = (int) $this->input->post('asset_id');
        $assignee_type = trim((string) $this->input->post('assignee_type'));
        if (!in_array($assignee_type, ['staff', 'place'], true)) {
            $assignee_type = 'staff';
        }

        $staff_assignee_id = (int) $this->input->post('assignee_id');
        $place_assignee_id = (int) $this->input->post('place_location_id');
        $assignee_id = $assignee_type === 'place' ? $place_assignee_id : $staff_assignee_id;
        $assigned_on = $this->parseDateValue($this->input->post('assigned_on'), true);
        $assigned_by = $this->currentStaffId();

        if ($asset_id <= 0 || $assignee_id <= 0 || empty($assigned_on)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Asset, target and assigned date are required.</div>');
            redirect('admin/assetmanagement/assignment');
        }

        $asset = $this->db->where('id', $asset_id)->get('inv_assets')->row_array();
        if (empty($asset)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Selected asset not found.</div>');
            redirect('admin/assetmanagement/assignment');
        }

        $this->db->trans_start();

        $this->db->insert('inv_asset_assignments', [
            'asset_id' => $asset_id,
            'assignee_type' => $assignee_type,
            'assignee_id' => $assignee_id,
            'assigned_on' => $assigned_on,
            'assigned_by' => $assigned_by,
            'status' => 'assigned',
        ]);

        $asset_update = [
            'current_status' => 'assigned',
            'assigned_to_type' => $assignee_type,
            'assigned_to_staff_id' => $assignee_type === 'staff' ? $assignee_id : null,
        ];
        if ($assignee_type === 'place') {
            $asset_update['current_location_id'] = $assignee_id;
        }

        $this->db->where('id', $asset_id)->update('inv_assets', [
            'current_status' => $asset_update['current_status'],
            'assigned_to_staff_id' => $asset_update['assigned_to_staff_id'],
            'assigned_to_type' => $asset_update['assigned_to_type'],
            'current_location_id' => isset($asset_update['current_location_id']) ? $asset_update['current_location_id'] : $asset['current_location_id'],
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to save asset assignment.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Asset assigned successfully.</div>');
        }

        redirect('admin/assetmanagement/assignment');
    }

    public function bulkassignment()
    {
        if (!$this->rbac->hasPrivilege('asset_assignment', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureAssetLifecycleTables()) {
            show_error('Asset lifecycle tables are missing. Please run latest db_updates migration.', 500);
        }

        list($rows, $error) = $this->getCsvRowsFromUpload('assignment_csv');
        if ($error !== null) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($error) . '</div>');
            redirect('admin/assetmanagement/assignment');
        }

        $inserted = 0;
        $failed = 0;
        foreach ((array) $rows as $row) {
            $asset_id = (int) ($row['asset_id'] ?? 0);
            $assignee_type = strtolower(trim((string) ($row['assignee_type'] ?? 'staff')));
            if (!in_array($assignee_type, ['staff', 'place'], true)) {
                $assignee_type = 'staff';
            }
            $assignee_id = (int) ($row['assignee_id'] ?? 0);
            if ($assignee_type === 'place' && $assignee_id <= 0) {
                $assignee_id = (int) ($row['place_location_id'] ?? 0);
            }
            $assigned_on = $this->parseDateValue($row['assigned_on'] ?? '', true);
            $assigned_by = (int) ($row['assigned_by'] ?? 0);
            if ($assigned_by <= 0) {
                $assigned_by = $this->currentStaffId();
            }

            if ($asset_id <= 0 || $assignee_id <= 0 || empty($assigned_on)) {
                $failed++;
                continue;
            }

            $asset = $this->db->where('id', $asset_id)->get('inv_assets')->row_array();
            if (empty($asset)) {
                $failed++;
                continue;
            }

            $exists = $this->db
                ->where('asset_id', $asset_id)
                ->where('assignee_type', $assignee_type)
                ->where('assignee_id', $assignee_id)
                ->where('status', 'assigned')
                ->get('inv_asset_assignments')
                ->row_array();

            if (!empty($exists)) {
                continue;
            }

            $this->db->trans_start();
            $this->db->insert('inv_asset_assignments', [
                'asset_id' => $asset_id,
                'assignee_type' => $assignee_type,
                'assignee_id' => $assignee_id,
                'assigned_on' => $assigned_on,
                'assigned_by' => $assigned_by,
                'status' => 'assigned',
            ]);

            $this->db->where('id', $asset_id)->update('inv_assets', [
                'current_status' => 'assigned',
                'assigned_to_staff_id' => $assignee_type === 'staff' ? $assignee_id : null,
                'assigned_to_type' => $assignee_type,
                'current_location_id' => $assignee_type === 'place' ? $assignee_id : $asset['current_location_id'],
            ]);
            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $failed++;
            } else {
                $inserted++;
            }
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-info text-left">Bulk assignment completed. Inserted: ' . (int) $inserted . ', Failed: ' . (int) $failed . '.</div>');
        redirect('admin/assetmanagement/assignment');
    }

    public function markreturn()
    {
        if (!$this->rbac->hasPrivilege('asset_assignment', 'can_edit')) {
            access_denied();
        }

        $assignment_id = (int) $this->input->post('assignment_id');
        if ($assignment_id <= 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Invalid assignment record.</div>');
            redirect('admin/assetmanagement/assignment');
        }

        $assignment = $this->db->where('id', $assignment_id)->get('inv_asset_assignments')->row_array();
        if (empty($assignment)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Assignment record not found.</div>');
            redirect('admin/assetmanagement/assignment');
        }

        $this->db->trans_start();
        $this->db->where('id', $assignment_id)->update('inv_asset_assignments', [
            'returned_on' => date('Y-m-d'),
            'status' => 'returned',
        ]);

        $this->db->where('id', (int) $assignment['asset_id'])->update('inv_assets', [
            'current_status' => 'in_stock',
            'assigned_to_staff_id' => null,
            'assigned_to_type' => null,
        ]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to mark return.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Asset return recorded successfully.</div>');
        }

        redirect('admin/assetmanagement/assignment');
    }

    public function transfer()
    {
        if (!$this->rbac->hasPrivilege('asset_transfer', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'assetmanagement/transfer');

        $data = [];
        $data['title'] = 'Asset Transfer';
        $data['asset_rows'] = $this->getAssetRowsForForm();
        $data['location_rows'] = $this->getLocationRowsForForm();
        $data['staff_rows'] = $this->getStaffRowsForForm();

        if ($this->db->table_exists('inv_asset_transfers')) {
            $data['rows'] = $this->db
            ->select('t.id, t.asset_id, t.from_location_id, t.to_location_id, t.transfer_date, t.transferred_by, t.status, t.remarks, a.asset_tag, fl.location_name as from_location_name, tl.location_name as to_location_name')
            ->from('inv_asset_transfers t')
            ->join('inv_assets a', 'a.id = t.asset_id', 'left')
            ->join('inv_asset_locations fl', 'fl.id = t.from_location_id', 'left')
            ->join('inv_asset_locations tl', 'tl.id = t.to_location_id', 'left')
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

    public function storetransfer()
    {
        if (!$this->rbac->hasPrivilege('asset_transfer', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureAssetLifecycleTables()) {
            show_error('Asset lifecycle tables are missing. Please run latest db_updates migration.', 500);
        }

        $asset_id = (int) $this->input->post('asset_id');
        $to_location_id = (int) $this->input->post('to_location_id');
        $target_type = trim((string) $this->input->post('target_type'));
        if (!in_array($target_type, ['staff', 'place'], true)) {
            $target_type = 'place';
        }
        $to_assignee_id = (int) $this->input->post('to_assignee_id');
        $transfer_date = $this->parseDateValue($this->input->post('transfer_date'), true);
        $transferred_by = $this->currentStaffId();
        $approved_by = $transferred_by;
        $remarks = trim((string) $this->input->post('remarks'));

        if ($asset_id <= 0 || $to_location_id <= 0 || empty($transfer_date) || ($target_type === 'staff' && $to_assignee_id <= 0)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Asset, destination location, target and transfer date are required.</div>');
            redirect('admin/assetmanagement/transfer');
        }

        $asset = $this->db->where('id', $asset_id)->get('inv_assets')->row_array();
        if (empty($asset)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Selected asset not found.</div>');
            redirect('admin/assetmanagement/transfer');
        }

        $from_location_id = !empty($asset['current_location_id']) ? (int) $asset['current_location_id'] : null;
        $from_assignee_type = !empty($asset['assigned_to_type']) ? (string) $asset['assigned_to_type'] : null;
        $from_assignee_id = !empty($asset['assigned_to_staff_id']) ? (int) $asset['assigned_to_staff_id'] : null;

        $to_assignee_type = $target_type === 'staff' ? 'staff' : 'place';
        if ($to_assignee_type === 'place') {
            $to_assignee_id = $to_location_id;
        }
        $new_status = 'assigned';

        $this->db->trans_start();
        $this->db->insert('inv_asset_transfers', [
            'asset_id' => $asset_id,
            'from_location_id' => $from_location_id,
            'to_location_id' => $to_location_id,
            'from_assignee_type' => $from_assignee_type,
            'from_assignee_id' => $from_assignee_id,
            'to_assignee_type' => $to_assignee_type,
            'to_assignee_id' => $to_assignee_id,
            'transfer_date' => $transfer_date,
            'transferred_by' => $transferred_by,
            'approved_by' => $approved_by,
            'status' => 'completed',
            'remarks' => $remarks !== '' ? $remarks : null,
        ]);

        $this->db->where('id', $asset_id)->update('inv_assets', [
            'current_location_id' => $to_location_id,
            'assigned_to_type' => $to_assignee_type,
            'assigned_to_staff_id' => $to_assignee_type === 'staff' ? $to_assignee_id : null,
            'current_status' => $new_status,
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to save transfer.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Asset transfer recorded successfully.</div>');
        }

        redirect('admin/assetmanagement/transfer');
    }

    public function bulktransfer()
    {
        if (!$this->rbac->hasPrivilege('asset_transfer', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureAssetLifecycleTables()) {
            show_error('Asset lifecycle tables are missing. Please run latest db_updates migration.', 500);
        }

        list($rows, $error) = $this->getCsvRowsFromUpload('transfer_csv');
        if ($error !== null) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($error) . '</div>');
            redirect('admin/assetmanagement/transfer');
        }

        $inserted = 0;
        $failed = 0;
        foreach ((array) $rows as $row) {
            $asset_id = (int) ($row['asset_id'] ?? 0);
            $to_location_id = (int) ($row['to_location_id'] ?? 0);
            $target_type = strtolower(trim((string) ($row['target_type'] ?? 'place')));
            if (!in_array($target_type, ['staff', 'place'], true)) {
                $target_type = 'place';
            }
            $to_assignee_id = (int) ($row['to_assignee_id'] ?? 0);
            $transfer_date = $this->parseDateValue($row['transfer_date'] ?? '', true);
            $transferred_by = (int) ($row['transferred_by'] ?? 0);
            if ($transferred_by <= 0) {
                $transferred_by = $this->currentStaffId();
            }
            $approved_by = (int) ($row['approved_by'] ?? 0);
            if ($approved_by <= 0) {
                $approved_by = $transferred_by;
            }
            $status = trim((string) ($row['status'] ?? 'completed'));
            if ($status === '') {
                $status = 'completed';
            }
            $remarks = trim((string) ($row['remarks'] ?? ''));

            if ($target_type === 'place') {
                $to_assignee_id = $to_location_id;
            }

            if ($asset_id <= 0 || $to_location_id <= 0 || empty($transfer_date) || ($target_type === 'staff' && $to_assignee_id <= 0)) {
                $failed++;
                continue;
            }

            $asset = $this->db->where('id', $asset_id)->get('inv_assets')->row_array();
            if (empty($asset)) {
                $failed++;
                continue;
            }

            $from_location_id = !empty($asset['current_location_id']) ? (int) $asset['current_location_id'] : null;
            $from_assignee_type = !empty($asset['assigned_to_type']) ? (string) $asset['assigned_to_type'] : null;
            $from_assignee_id = !empty($asset['assigned_to_staff_id']) ? (int) $asset['assigned_to_staff_id'] : null;
            $to_assignee_type = $target_type === 'staff' ? 'staff' : 'place';
            $new_status = 'assigned';

            $this->db->trans_start();
            $this->db->insert('inv_asset_transfers', [
                'asset_id' => $asset_id,
                'from_location_id' => $from_location_id,
                'to_location_id' => $to_location_id,
                'from_assignee_type' => $from_assignee_type,
                'from_assignee_id' => $from_assignee_id,
                'to_assignee_type' => $to_assignee_type,
                'to_assignee_id' => $to_assignee_id,
                'transfer_date' => $transfer_date,
                'transferred_by' => $transferred_by,
                'approved_by' => $approved_by,
                'status' => $status,
                'remarks' => $remarks !== '' ? $remarks : null,
            ]);

            $this->db->where('id', $asset_id)->update('inv_assets', [
                'current_location_id' => $to_location_id,
                'assigned_to_type' => $to_assignee_type,
                'assigned_to_staff_id' => $to_assignee_type === 'staff' ? $to_assignee_id : null,
                'current_status' => $new_status,
            ]);
            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $failed++;
            } else {
                $inserted++;
            }
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-info text-left">Bulk transfer completed. Inserted: ' . (int) $inserted . ', Failed: ' . (int) $failed . '.</div>');
        redirect('admin/assetmanagement/transfer');
    }

    public function maintenance()
    {
        if (!$this->rbac->hasPrivilege('asset_maintenance', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'assetmanagement/maintenance');

        $data = [];
        $data['title'] = 'Asset Maintenance';
        $data['asset_rows'] = $this->getAssetRowsForForm();

        if ($this->db->table_exists('inv_asset_maintenance_logs')) {
            $data['rows'] = $this->db
                ->select('m.id, m.asset_id, m.maintenance_type, m.vendor_name, m.opened_on, m.closed_on, m.status, m.cost_amount, m.issue_description, m.resolution_note, a.asset_tag, a.asset_name')
                ->from('inv_asset_maintenance_logs m')
                ->join('inv_assets a', 'a.id = m.asset_id', 'left')
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

    public function storemaintenance()
    {
        if (!$this->rbac->hasPrivilege('asset_maintenance', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureAssetLifecycleTables()) {
            show_error('Asset lifecycle tables are missing. Please run latest db_updates migration.', 500);
        }

        $asset_id = (int) $this->input->post('asset_id');
        $maintenance_type = trim((string) $this->input->post('maintenance_type'));
        $vendor_name = trim((string) $this->input->post('vendor_name'));
        $opened_on = $this->parseDateValue($this->input->post('opened_on'), true);
        $status = trim((string) $this->input->post('status'));
        $issue_description = trim((string) $this->input->post('issue_description'));
        $cost_amount = (float) $this->input->post('cost_amount');
        $next_due_date = $this->parseDateValue($this->input->post('next_due_date'), false);
        $created_by = $this->currentStaffId();

        if ($asset_id <= 0 || $maintenance_type === '' || empty($opened_on) || $status === '') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Asset, maintenance type, opened date and status are required.</div>');
            redirect('admin/assetmanagement/maintenance');
        }

        $closed_on = null;
        $resolution_note = null;
        if ($status === 'closed') {
            $closed_on = $this->parseDateValue($this->input->post('closed_on'), true);
            $resolution_note = trim((string) $this->input->post('resolution_note'));
        }

        $this->db->trans_start();
        $this->db->insert('inv_asset_maintenance_logs', [
            'asset_id' => $asset_id,
            'maintenance_type' => $maintenance_type,
            'vendor_name' => $vendor_name !== '' ? $vendor_name : null,
            'opened_on' => $opened_on,
            'closed_on' => $closed_on,
            'status' => $status,
            'issue_description' => $issue_description !== '' ? $issue_description : null,
            'resolution_note' => $resolution_note,
            'cost_amount' => $cost_amount,
            'next_due_date' => $next_due_date,
            'created_by' => $created_by,
        ]);

        $asset_status = in_array($status, ['open', 'in_progress'], true) ? 'under_maintenance' : 'in_stock';
        $this->db->where('id', $asset_id)->update('inv_assets', [
            'current_status' => $asset_status,
        ]);

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to save maintenance record.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Maintenance record saved successfully.</div>');
        }

        redirect('admin/assetmanagement/maintenance');
    }

    public function completemaintenance()
    {
        if (!$this->rbac->hasPrivilege('asset_maintenance', 'can_edit')) {
            access_denied();
        }

        $maintenance_id = (int) $this->input->post('maintenance_id');
        $resolution_note = trim((string) $this->input->post('resolution_note'));
        $cost_amount = (float) $this->input->post('cost_amount');
        $closed_on = $this->parseDateValue($this->input->post('closed_on'), true);

        if ($maintenance_id <= 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Invalid maintenance record.</div>');
            redirect('admin/assetmanagement/maintenance');
        }

        $row = $this->db->where('id', $maintenance_id)->get('inv_asset_maintenance_logs')->row_array();
        if (empty($row)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Maintenance record not found.</div>');
            redirect('admin/assetmanagement/maintenance');
        }

        $this->db->trans_start();
        $this->db->where('id', $maintenance_id)->update('inv_asset_maintenance_logs', [
            'closed_on' => $closed_on,
            'status' => 'closed',
            'resolution_note' => $resolution_note !== '' ? $resolution_note : null,
            'cost_amount' => $cost_amount,
        ]);

        $this->db->where('id', (int) $row['asset_id'])->update('inv_assets', [
            'current_status' => 'in_stock',
        ]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to close maintenance record.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Maintenance record closed successfully.</div>');
        }

        redirect('admin/assetmanagement/maintenance');
    }

    public function bulkmaintenance()
    {
        if (!$this->rbac->hasPrivilege('asset_maintenance', 'can_add')) {
            access_denied();
        }

        if (!$this->ensureAssetLifecycleTables()) {
            show_error('Asset lifecycle tables are missing. Please run latest db_updates migration.', 500);
        }

        list($rows, $error) = $this->getCsvRowsFromUpload('maintenance_csv');
        if ($error !== null) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . html_escape($error) . '</div>');
            redirect('admin/assetmanagement/maintenance');
        }

        $inserted = 0;
        $failed = 0;
        foreach ((array) $rows as $row) {
            $asset_id = (int) ($row['asset_id'] ?? 0);
            $maintenance_type = trim((string) ($row['maintenance_type'] ?? 'breakdown'));
            $vendor_name = trim((string) ($row['vendor_name'] ?? ''));
            $opened_on = $this->parseDateValue($row['opened_on'] ?? '', true);
            $closed_on = $this->parseDateValue($row['closed_on'] ?? '', false);
            $status = trim((string) ($row['status'] ?? 'open'));
            if ($status === '') {
                $status = 'open';
            }
            $issue_description = trim((string) ($row['issue_description'] ?? ''));
            $resolution_note = trim((string) ($row['resolution_note'] ?? ''));
            $cost_amount = (float) ($row['cost_amount'] ?? 0);
            $next_due_date = $this->parseDateValue($row['next_due_date'] ?? '', false);
            $created_by = (int) ($row['created_by'] ?? 0);
            if ($created_by <= 0) {
                $created_by = $this->currentStaffId();
            }

            if ($asset_id <= 0 || $maintenance_type === '' || empty($opened_on)) {
                $failed++;
                continue;
            }

            $asset = $this->db->where('id', $asset_id)->get('inv_assets')->row_array();
            if (empty($asset)) {
                $failed++;
                continue;
            }

            $this->db->trans_start();
            $this->db->insert('inv_asset_maintenance_logs', [
                'asset_id' => $asset_id,
                'maintenance_type' => $maintenance_type,
                'vendor_name' => $vendor_name !== '' ? $vendor_name : null,
                'opened_on' => $opened_on,
                'closed_on' => $closed_on,
                'status' => $status,
                'issue_description' => $issue_description !== '' ? $issue_description : null,
                'resolution_note' => $resolution_note !== '' ? $resolution_note : null,
                'cost_amount' => $cost_amount,
                'next_due_date' => $next_due_date,
                'created_by' => $created_by,
            ]);

            $asset_status = in_array($status, ['open', 'in_progress'], true) ? 'under_maintenance' : 'in_stock';
            $this->db->where('id', $asset_id)->update('inv_assets', ['current_status' => $asset_status]);
            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $failed++;
            } else {
                $inserted++;
            }
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-info text-left">Bulk maintenance completed. Inserted: ' . (int) $inserted . ', Failed: ' . (int) $failed . '.</div>');
        redirect('admin/assetmanagement/maintenance');
    }
}
