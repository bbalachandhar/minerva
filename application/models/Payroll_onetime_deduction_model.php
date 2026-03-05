<?php

class Payroll_onetime_deduction_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function isTableReady()
    {
        return $this->db->table_exists('staff_onetime_deductions');
    }

    public function upsertDeduction(array $data)
    {
        if (!$this->isTableReady()) {
            return false;
        }

        $staff_id = (int) ($data['staff_id'] ?? 0);
        $month = (int) ($data['month'] ?? 0);
        $year = (int) ($data['year'] ?? 0);
        $deduction_type = strtoupper(trim((string) ($data['deduction_type'] ?? '')));
        $amount = (float) ($data['amount'] ?? 0);
        $remarks = isset($data['remarks']) ? trim((string) $data['remarks']) : null;
        $admin_id = isset($data['admin_user_id']) ? (int) $data['admin_user_id'] : null;

        if ($staff_id <= 0 || $month < 1 || $month > 12 || $year <= 0 || $deduction_type === '' || $amount <= 0) {
            return false;
        }

        $existing = $this->db->select('id')
            ->from('staff_onetime_deductions')
            ->where('staff_id', $staff_id)
            ->where('month', $month)
            ->where('year', $year)
            ->where('deduction_type', $deduction_type)
            ->where('is_active', 1)
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($existing['id'])) {
            $this->db->where('id', (int) $existing['id'])->update('staff_onetime_deductions', [
                'amount' => $amount,
                'remarks' => $remarks,
                'updated_by' => $admin_id,
                'approval_status' => 'Pending',
                'approved_by' => null,
                'approved_at' => null,
            ]);

            return (int) $existing['id'];
        }

        $this->db->insert('staff_onetime_deductions', [
            'staff_id' => $staff_id,
            'month' => $month,
            'year' => $year,
            'deduction_type' => $deduction_type,
            'amount' => $amount,
            'remarks' => $remarks,
            'is_active' => 1,
            'approval_status' => 'Pending',
            'created_by' => $admin_id,
            'updated_by' => $admin_id,
        ]);

        return (int) $this->db->insert_id();
    }

    public function getByStaffMonth($staff_id, $month, $year)
    {
        if (!$this->isTableReady()) {
            return [];
        }

        return $this->db->select('id, staff_id, month, year, deduction_type, amount, remarks')
            ->from('staff_onetime_deductions')
            ->where('staff_id', (int) $staff_id)
            ->where('month', (int) $month)
            ->where('year', (int) $year)
            ->where('is_active', 1)
            ->where('approval_status', 'Approved')
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
    }

    public function getPendingDeductions()
    {
        if (!$this->isTableReady()) {
            return [];
        }

        return $this->db->select('d.*, s.name, s.employee_id')
            ->from('staff_onetime_deductions d')
            ->join('staff s', 's.id = d.staff_id', 'inner')
            ->where('d.is_active', 1)
            ->where('d.approval_status', 'Pending')
            ->order_by('d.created_at', 'ASC')
            ->get()
            ->result_array();
    }

    public function approveDeduction($id, $admin_id)
    {
        if (!$this->isTableReady()) {
            return false;
        }

        $this->db->where('id', (int) $id)->where('is_active', 1)->update('staff_onetime_deductions', [
            'approval_status' => 'Approved',
            'approved_by' => (int) $admin_id,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_by' => (int) $admin_id,
        ]);

        return true;
    }

    public function rejectDeduction($id, $admin_id)
    {
        if (!$this->isTableReady()) {
            return false;
        }

        $this->db->where('id', (int) $id)->where('is_active', 1)->update('staff_onetime_deductions', [
            'approval_status' => 'Rejected',
            'approved_by' => (int) $admin_id,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_by' => (int) $admin_id,
        ]);

        return true;
    }
}
