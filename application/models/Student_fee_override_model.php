<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_fee_override_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all sessions available (for filter dropdown).
     */
    public function getSessions() {
        return $this->db->select('id, session')
            ->from('sessions')
            ->order_by('id', 'DESC')
            ->get()->result();
    }

    /**
     * Get classes that have at least one student assigned to a fee group
     * in the given session.
     */
    public function getClassesForSession($session_id) {
        $sql = "SELECT DISTINCT c.id, c.class
                FROM classes c
                JOIN student_session ss ON ss.class_id = c.id
                JOIN student_fees_master sfm ON sfm.student_session_id = ss.id
                JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                WHERE ss.session_id = " . $this->db->escape($session_id) . "
                ORDER BY c.class";
        return $this->db->query($sql)->result();
    }

    /**
     * Get all students in a class (and optionally section) for a session,
     * with all their fee types, existing overrides and paid amounts.
     * Returns one row per student per fee_groups_feetype row.
     */
    public function getStudentsWithFeeOverrides($session_id, $class_id, $section_id = null) {
        $section_filter = '';
        if ($section_id) {
            $section_filter = ' AND ss.section_id = ' . $this->db->escape($section_id);
        }
        $sql = "SELECT
                    ss.id        AS student_session_id,
                    s.admission_no,
                    s.firstname,
                    s.middlename,
                    s.lastname,
                    c.class,
                    sec.section,
                    ft.code       AS feetype_code,
                    ft.type       AS feetype_name,
                    fgft.id       AS fee_groups_feetype_id,
                    fgft.amount   AS base_amount,
                    fg.name       AS fee_group_name,
                    COALESCE(sfo.override_amount, fgft.amount) AS effective_amount,
                    sfo.override_amount,
                    sfo.id        AS override_id,
                    sfo.note      AS override_note,
                    (
                        SELECT COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(sfd.amount_detail, '$.amount')) AS DECIMAL(10,2))), 0)
                        FROM student_fees_deposite sfd
                        WHERE sfd.student_fees_master_id = sfm.id
                          AND sfd.fee_groups_feetype_id  = fgft.id
                    ) AS paid_amount
                FROM student_fees_master sfm
                JOIN student_session ss     ON ss.id  = sfm.student_session_id
                JOIN students s             ON s.id   = ss.student_id
                JOIN classes c              ON c.id   = ss.class_id
                JOIN sections sec           ON sec.id = ss.section_id
                JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                JOIN fee_groups fg          ON fg.id  = fsg.fee_groups_id
                JOIN fee_groups_feetype fgft ON fgft.fee_session_group_id = sfm.fee_session_group_id
                JOIN feetype ft             ON ft.id  = fgft.feetype_id
                LEFT JOIN student_fee_overrides sfo
                    ON sfo.student_session_id    = ss.id
                   AND sfo.fee_groups_feetype_id = fgft.id
                WHERE ss.session_id = " . $this->db->escape($session_id) . "
                  AND ss.class_id   = " . $this->db->escape($class_id) . "
                  AND s.is_active   = 'yes'
                  AND (ss.is_alumni = 0 OR ss.is_alumni IS NULL)
                  AND ft.id NOT IN (3, 4, 5, 6, 7)
                  " . $section_filter . "
                ORDER BY s.firstname, s.lastname, ft.id";
        return $this->db->query($sql)->result();
    }

    /**
     * Get total paid amount for a student + fee_groups_feetype combo.
     */
    public function getPaidAmount($student_session_id, $fee_groups_feetype_id) {
        $sql = "SELECT COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(sfd.amount_detail, '$.amount')) AS DECIMAL(10,2))), 0) AS paid_amount
                FROM student_fees_deposite sfd
                JOIN student_fees_master sfm ON sfm.id = sfd.student_fees_master_id
                WHERE sfm.student_session_id   = " . $this->db->escape($student_session_id) . "
                  AND sfd.fee_groups_feetype_id = " . $this->db->escape($fee_groups_feetype_id);
        $row = $this->db->query($sql)->row();
        return $row ? (float)$row->paid_amount : 0.0;
    }

    /**
     * Get existing override row for a student + feetype.
     */
    public function getOverride($student_session_id, $fee_groups_feetype_id) {
        return $this->db->get_where('student_fee_overrides', [
            'student_session_id'    => $student_session_id,
            'fee_groups_feetype_id' => $fee_groups_feetype_id,
        ])->row();
    }

    /**
     * Save (INSERT or UPDATE) an override.
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
     * Delete override — only allowed when paid_amount = 0.
     */
    public function deleteOverride($student_session_id, $fee_groups_feetype_id) {
        $this->db->delete('student_fee_overrides', [
            'student_session_id'    => $student_session_id,
            'fee_groups_feetype_id' => $fee_groups_feetype_id,
        ]);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Bulk import overrides from CSV rows.
     *
     * Each $row must have keys: admission_no, fee_type_code, override_amount, note
     * Looks up student_session_id and fee_groups_feetype_id from admission_no + feetype code.
     *
     * Returns array: ['imported' => int, 'failed' => [['row' => ..., 'reason' => ...]]]
     */
    public function bulkImportOverrides(array $rows, $session_id, $user_id) {
        $imported = 0;
        $failed   = [];

        foreach ($rows as $idx => $row) {
            $admission_no    = isset($row['admission_no'])    ? trim($row['admission_no'])    : '';
            $feetype_code    = isset($row['fee_type_code'])   ? trim($row['fee_type_code'])   : '';
            $override_amount = isset($row['override_amount']) ? trim($row['override_amount']) : '';
            $note            = isset($row['note'])            ? trim($row['note'])            : '';

            // Basic validation
            if ($admission_no === '' || $feetype_code === '' || $override_amount === '') {
                $failed[] = ['row' => $row, 'reason' => 'Missing required field(s)'];
                continue;
            }

            if (!is_numeric($override_amount) || (float)$override_amount <= 0) {
                $failed[] = ['row' => $row, 'reason' => 'override_amount must be a positive number'];
                continue;
            }

            $override_amount = (float)$override_amount;

            // Resolve student_session_id
            $ss_sql = "SELECT ss.id AS student_session_id
                       FROM student_session ss
                       JOIN students s ON s.id = ss.student_id
                       WHERE s.admission_no = " . $this->db->escape($admission_no) . "
                         AND ss.session_id  = " . $this->db->escape($session_id) . "
                       LIMIT 1";
            $ss_row = $this->db->query($ss_sql)->row();
            if (!$ss_row) {
                $failed[] = ['row' => $row, 'reason' => 'Student not found in session (admission_no: ' . htmlspecialchars($admission_no) . ')'];
                continue;
            }
            $student_session_id = (int)$ss_row->student_session_id;

            // Resolve fee_groups_feetype_id
            $fgft_sql = "SELECT fgft.id AS fee_groups_feetype_id
                         FROM fee_groups_feetype fgft
                         JOIN feetype ft          ON ft.id  = fgft.feetype_id
                         JOIN fee_session_groups fsg ON fsg.id = fgft.fee_session_group_id
                         JOIN student_fees_master sfm ON sfm.fee_session_group_id = fsg.id
                                                    AND sfm.student_session_id   = " . $this->db->escape($student_session_id) . "
                         WHERE ft.code      = " . $this->db->escape($feetype_code) . "
                           AND fsg.session_id = " . $this->db->escape($session_id) . "
                         LIMIT 1";
            $fgft_row = $this->db->query($fgft_sql)->row();
            if (!$fgft_row) {
                $failed[] = ['row' => $row, 'reason' => 'Fee type code "' . htmlspecialchars($feetype_code) . '" not assigned to this student'];
                continue;
            }
            $fee_groups_feetype_id = (int)$fgft_row->fee_groups_feetype_id;

            // Validate against paid amount
            $paid = $this->getPaidAmount($student_session_id, $fee_groups_feetype_id);
            if ($override_amount < $paid) {
                $failed[] = ['row' => $row, 'reason' => 'Override amount (' . $override_amount . ') is less than already-paid amount (' . $paid . ')'];
                continue;
            }

            // Save override (upsert)
            $this->saveOverride($student_session_id, $fee_groups_feetype_id, $override_amount, $note, $user_id);
            $imported++;
        }

        return ['imported' => $imported, 'failed' => $failed];
    }
}
