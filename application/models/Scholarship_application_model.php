<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scholarship_application_model extends CI_Model
{
    /**
     * Get all applications with applicant name and scholarship type name joined.
     */
    public function getAll($status = null, $type_id = null)
    {
        $this->db->select('sa.*, st.name AS scholarship_name, st.verifier_id AS type_verifier_id,
            oa.firstname, oa.lastname, oa.reference_no, oa.email, oa.mobileno,
            CONCAT(vs.name, " ", vs.surname) AS verifier_name,
            CONCAT(ap.name, " ", ap.surname) AS approver_name,
            CONCAT(tv.name, " ", tv.surname) AS type_verifier_name');
        $this->db->from('scholarship_applications sa');
        $this->db->join('scholarship_types st', 'st.id = sa.scholarship_type_id', 'left');
        $this->db->join('online_admissions oa', 'oa.id = sa.online_admission_id', 'left');
        $this->db->join('staff vs', 'vs.id = sa.verifier_id', 'left');
        $this->db->join('staff ap', 'ap.id = sa.approver_id', 'left');
        $this->db->join('staff tv', 'tv.id = st.verifier_id', 'left');
        if ($status !== null) {
            $this->db->where('sa.status', $status);
        }
        if ($type_id !== null) {
            $this->db->where('sa.scholarship_type_id', (int) $type_id);
        }
        $this->db->order_by('sa.id', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Get single application by ID.
     */
    public function get($id)
    {
        $this->db->select('sa.*, st.name AS scholarship_name, st.verifier_id AS type_verifier_id,
            oa.firstname, oa.lastname, oa.reference_no, oa.email, oa.mobileno,
            CONCAT(vs.name, " ", vs.surname) AS verifier_name,
            CONCAT(ap.name, " ", ap.surname) AS approver_name,
            CONCAT(tv.name, " ", tv.surname) AS type_verifier_name');
        $this->db->from('scholarship_applications sa');
        $this->db->join('scholarship_types st', 'st.id = sa.scholarship_type_id', 'left');
        $this->db->join('online_admissions oa', 'oa.id = sa.online_admission_id', 'left');
        $this->db->join('staff vs', 'vs.id = sa.verifier_id', 'left');
        $this->db->join('staff ap', 'ap.id = sa.approver_id', 'left');
        $this->db->join('staff tv', 'tv.id = st.verifier_id', 'left');
        $this->db->where('sa.id', $id);
        return $this->db->get()->row_array();
    }

    /**
     * Get all applications for a specific applicant (online_admissions.id).
     */
    public function getByApplicant($online_admission_id)
    {
        $this->db->select('sa.*, st.name AS scholarship_name');
        $this->db->from('scholarship_applications sa');
        $this->db->join('scholarship_types st', 'st.id = sa.scholarship_type_id', 'left');
        $this->db->where('sa.online_admission_id', $online_admission_id);
        $this->db->order_by('sa.id', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Check if an applicant already has an application for a given scholarship type.
     */
    public function alreadyApplied($online_admission_id, $scholarship_type_id)
    {
        return $this->db
            ->where('online_admission_id', $online_admission_id)
            ->where('scholarship_type_id', $scholarship_type_id)
            ->count_all_results('scholarship_applications') > 0;
    }

    public function insert($data)
    {
        $this->db->insert('scholarship_applications', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('scholarship_applications', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id)->delete('scholarship_applications');
    }

    // ── Settings helpers ─────────────────────────────────────────────────────

    public function getSettings()
    {
        return $this->db->get('scholarship_settings')->row_array();
    }

    public function saveSettings($data)
    {
        // Only approver_id is stored globally; verifier is per scholarship type
        $save = ['approver_id' => $data['approver_id'] ?? null];
        $this->db->where('id', 1);
        if ($this->db->count_all_results('scholarship_settings') > 0) {
            $this->db->where('id', 1)->update('scholarship_settings', $save);
        } else {
            $save['id'] = 1;
            $this->db->insert('scholarship_settings', $save);
        }
    }
}
