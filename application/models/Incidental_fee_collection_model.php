<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Incidental_fee_collection_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function add($data) {
        $this->db->insert('incidental_fee_collections', $data);
        return $this->db->insert_id();
    }

    public function get($id = null) {
        $this->db->select('incidental_fee_collections.*, incidental_fee_types.title as fee_type_title, incidental_fee_types.description as fee_type_description, students.firstname, students.lastname, students.admission_no, classes.class as class_name, sessions.session as session_name, staff.name as collected_by_name');
        $this->db->from('incidental_fee_collections');
        $this->db->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_collections.incidental_fee_type_id', 'left');
        $this->db->join('students', 'students.id = incidental_fee_collections.student_id', 'left');
        $this->db->join('incidental_fee_assignments', 'incidental_fee_assignments.id = incidental_fee_collections.incidental_fee_assignment_id', 'left');
        $this->db->join('classes', 'classes.id = incidental_fee_assignments.class_id', 'left'); // Join via assignment for class name
        $this->db->join('sessions', 'sessions.id = incidental_fee_collections.session_id', 'left');
        $this->db->join('staff', 'staff.id = incidental_fee_collections.collected_by', 'left'); // Join with staff table

        if ($id) {
            $this->db->where('incidental_fee_collections.id', $id);
            return $this->db->get()->row_array();
        }
        return $this->db->get()->result_array();
    }

    public function get_receipt_no() {
        $prefix = "IFC-"; // Incidental Fee Collection
        $last_receipt = $this->db->select('receipt_no')->order_by('id', 'DESC')->limit(1)->get('incidental_fee_collections')->row();
        if ($last_receipt) {
            $last_num = (int) str_replace($prefix, '', $last_receipt->receipt_no);
            $new_num = $last_num + 1;
        } else {
            $new_num = 1;
        }
        return $prefix . str_pad($new_num, 6, '0', STR_PAD_LEFT);
    }

    // Method to get collections for reporting
    public function get_collections_report($filters = array()) {
        $this->db->select('incidental_fee_collections.*, incidental_fee_types.title as fee_type_title, students.firstname, students.lastname, students.admission_no, classes.class as class_name, sections.section, sessions.session as session_name, staff.name as collected_by_name');
        $this->db->from('incidental_fee_collections');
        $this->db->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_collections.incidental_fee_type_id', 'left');
        $this->db->join('students', 'students.id = incidental_fee_collections.student_id', 'left');
        $this->db->join('student_session', 'student_session.student_id = students.id', 'left');
        $this->db->join('classes', 'classes.id = student_session.class_id', 'left');
        $this->db->join('sections', 'sections.id = student_session.section_id', 'left');
        $this->db->join('sessions', 'sessions.id = incidental_fee_collections.session_id', 'left');
        $this->db->join('staff', 'staff.id = incidental_fee_collections.collected_by', 'left'); // Join with staff table

        if (!empty($filters['session_id'])) {
            $this->db->where('incidental_fee_collections.session_id', $filters['session_id']);
        }
        if (!empty($filters['fee_type_id'])) {
            $this->db->where('incidental_fee_collections.incidental_fee_type_id', $filters['fee_type_id']);
        }
        if (!empty($filters['student_id'])) {
            $this->db->where('incidental_fee_collections.student_id', $filters['student_id']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $this->db->where('incidental_fee_collections.date_collected >=', $filters['start_date']);
            $this->db->where('incidental_fee_collections.date_collected <=', $filters['end_date'] . ' 23:59:59');
        }

        $this->db->order_by('incidental_fee_collections.date_collected', 'DESC');
        return $this->db->get()->result_array();
    }

    public function revert($collection_id) {
        if (!$collection_id) {
            return false;
        }

        $this->db->trans_start();

        // Get the collection details before deleting
        $collection = $this->get($collection_id);

        if ($collection) {
            // If this collection was for an assignment, update the assignment status
            if (!empty($collection['incidental_fee_assignment_id'])) {
                // Revert the status to 'unpaid'. More complex logic for partial payments could be added here.
                $this->db->where('id', $collection['incidental_fee_assignment_id']);
                $this->db->update('incidental_fee_assignments', ['status' => 'unpaid']);
            }

            // Delete the collection record
            $this->db->where('id', $collection_id);
            $this->db->delete('incidental_fee_collections');
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}
