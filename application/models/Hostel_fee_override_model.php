<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hostel_fee_override_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get hostel fee type IDs for the current session.
     * Hostel fee is identified by feetype.id = 3 (HOSTEL FEES).
     */
    public function getHostelFeetypeIds() {
        $sql = "SELECT fgft.id as fee_groups_feetype_id, fgft.amount as base_amount, fg.name as fee_group_name, fgft.fee_groups_id, fgft.fee_session_group_id
                FROM fee_groups_feetype fgft
                JOIN fee_groups fg ON fg.id = fgft.fee_groups_id
                JOIN feetype ft ON ft.id = fgft.feetype_id
                JOIN fee_session_groups fsg ON fsg.id = fgft.fee_session_group_id
                WHERE ft.id = 3 AND fsg.session_id = " . $this->db->escape($this->setting_model->getCurrentSession());
        return $this->db->query($sql)->result();
    }

    /**
     * Get all students assigned to a hostel fee group with their paid amounts and existing overrides.
     * Filters by fee_session_group_id (one hostel fee group).
     */
    public function getStudentsWithHostelFee($fee_session_group_id) {
        $session_id = $this->setting_model->getCurrentSession();
        $sql = "SELECT
                    ss.id as student_session_id,
                    s.admission_no,
                    s.firstname, s.middlename, s.lastname,
                    c.class, sec.section,
                    fgft.id as fee_groups_feetype_id,
                    fgft.amount as base_amount,
                    fg.name as fee_group_name,
                    COALESCE(sfo.override_amount, fgft.amount) as effective_amount,
                    sfo.override_amount,
                    sfo.id as override_id,
                    sfo.note as override_note,
                    (
                        SELECT COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(sfd.amount_detail, '$.amount')) AS DECIMAL(10,2))), 0)
                        FROM student_fees_deposite sfd
                        WHERE sfd.student_fees_master_id = sfm.id
                          AND sfd.fee_groups_feetype_id = fgft.id
                    ) as paid_amount
                FROM student_fees_master sfm
                JOIN student_session ss ON ss.id = sfm.student_session_id
                JOIN students s ON s.id = ss.student_id
                JOIN classes c ON c.id = ss.class_id
                JOIN sections sec ON sec.id = ss.section_id
                JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                JOIN fee_groups fg ON fg.id = fsg.fee_groups_id
                JOIN fee_groups_feetype fgft ON fgft.fee_session_group_id = sfm.fee_session_group_id
                JOIN feetype ft ON ft.id = fgft.feetype_id AND ft.id = 3
                LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = ss.id AND sfo.fee_groups_feetype_id = fgft.id
                WHERE sfm.fee_session_group_id = " . $this->db->escape($fee_session_group_id) . "
                  AND ss.session_id = " . $this->db->escape($session_id) . "
                  AND s.is_active = 'yes'
                ORDER BY s.firstname, s.lastname";
        return $this->db->query($sql)->result();
    }

    /**
     * Get total paid amount for a student+fee_groups_feetype combo.
     * amount_detail is a JSON object with 'amount' key per deposit.
     */
    public function getPaidAmount($student_session_id, $fee_groups_feetype_id) {
        $sql = "SELECT COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(sfd.amount_detail, '$.amount')) AS DECIMAL(10,2))), 0) as paid_amount
                FROM student_fees_deposite sfd
                JOIN student_fees_master sfm ON sfm.id = sfd.student_fees_master_id
                WHERE sfm.student_session_id = " . $this->db->escape($student_session_id) . "
                  AND sfd.fee_groups_feetype_id = " . $this->db->escape($fee_groups_feetype_id);
        $row = $this->db->query($sql)->row();
        return $row ? (float)$row->paid_amount : 0;
    }

    /**
     * Get existing override for a student+feetype.
     */
    public function getOverride($student_session_id, $fee_groups_feetype_id) {
        return $this->db->get_where('student_fee_overrides', [
            'student_session_id'     => $student_session_id,
            'fee_groups_feetype_id'  => $fee_groups_feetype_id,
        ])->row();
    }

    /**
     * Save (insert or update) an override.
     */
    public function saveOverride($student_session_id, $fee_groups_feetype_id, $override_amount, $note, $user_id) {
        $existing = $this->getOverride($student_session_id, $fee_groups_feetype_id);
        if ($existing) {
            $this->db->update('student_fee_overrides', [
                'override_amount' => $override_amount,
                'note'            => $note,
                'updated_by'      => $user_id,
            ], [
                'student_session_id'    => $student_session_id,
                'fee_groups_feetype_id' => $fee_groups_feetype_id,
            ]);
        } else {
            $this->db->insert('student_fee_overrides', [
                'student_session_id'    => $student_session_id,
                'fee_groups_feetype_id' => $fee_groups_feetype_id,
                'override_amount'       => $override_amount,
                'note'                  => $note,
                'created_by'            => $user_id,
                'updated_by'            => $user_id,
            ]);
        }
        return $this->db->affected_rows() > 0;
    }

    /**
     * Remove override (only when paid = 0).
     */
    public function deleteOverride($student_session_id, $fee_groups_feetype_id) {
        $this->db->delete('student_fee_overrides', [
            'student_session_id'    => $student_session_id,
            'fee_groups_feetype_id' => $fee_groups_feetype_id,
        ]);
        return $this->db->affected_rows() > 0;
    }
}
