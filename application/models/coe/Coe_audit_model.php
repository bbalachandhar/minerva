<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Coe_audit_model
 * Writes entries to coe_audit_log for every CoE write operation.
 * Called by Coe_setup, Coe_application, Coe_eligibility (and future CoE controllers).
 */
class Coe_audit_model extends CI_Model {

    /**
     * Log a CoE action.
     *
     * @param string      $action     Short verb: 'create', 'update', 'delete', 'run', 'override', etc.
     * @param string      $table      Target table name (e.g. 'coe_exam_regulations').
     * @param int|null    $record_id  PK of the affected row (null for multi-row ops).
     * @param mixed       $old_data   Value before the change (array/object/null) — stored as JSON.
     * @param mixed       $new_data   Value after  the change (array/object/null) — stored as JSON.
     * @return int|bool  Insert ID on success, FALSE on failure.
     */
    public function log($action, $table, $record_id = null, $old_data = null, $new_data = null)
    {
        $staff_id = null;
        if ($this->session->userdata('staff_id')) {
            $staff_id = (int) $this->session->userdata('staff_id');
        }

        $ip = $this->input->ip_address();

        $row = [
            'action'       => substr((string) $action, 0, 100),
            'entity'       => substr((string) $table,  0, 50),
            'entity_id'    => ($record_id !== null) ? (int) $record_id : null,
            'old_value'    => ($old_data  !== null) ? json_encode($old_data,  JSON_UNESCAPED_UNICODE) : null,
            'new_value'    => ($new_data  !== null) ? json_encode($new_data,  JSON_UNESCAPED_UNICODE) : null,
            'performed_by' => $staff_id ?? 0,
            'ip_address'   => $ip,
        ];

        $this->db->insert('coe_audit_log', $row);
        return $this->db->insert_id();
    }

    /**
     * Fetch recent audit entries for a given table / record, newest first.
     *
     * @param string   $table     Table name to filter by.
     * @param int|null $record_id Optionally filter to a specific record.
     * @param int      $limit     Max rows to return.
     * @return array
     */
    public function getLog($table, $record_id = null, $limit = 100)
    {
        $this->db->where('entity', $table);
        if ($record_id !== null) {
            $this->db->where('entity_id', (int) $record_id);
        }
        $this->db->order_by('performed_at', 'DESC');
        $this->db->limit((int) $limit);
        return $this->db->get('coe_audit_log')->result();
    }
}
