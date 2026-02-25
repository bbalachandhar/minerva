<?php

class Online_admission_universities_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->table = 'online_admission_universities';
    }

    /**
     * Get all active universities, sorted alphabetically
     */
    public function get_all_active() {
        return $this->db->where('status', 1)
            ->order_by('name', 'asc')
            ->get($this->table)
            ->result_array();
    }

    /**
     * Get university by ID
     */
    public function get($id) {
        return $this->db->where('id', $id)
            ->get($this->table)
            ->row_array();
    }

    /**
     * Add new university
     */
    public function add($data) {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update university
     */
    public function edit($data) {
        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            return $this->db->where('id', $id)
                ->update($this->table, $data);
        }
        return false;
    }

    /**
     * Delete university
     */
    public function delete($id) {
        // Soft delete - set status to 0
        return $this->db->where('id', $id)
            ->update($this->table, array('status' => 0));
    }

    /**
     * Get all universities (including inactive)
     */
    public function get_all() {
        return $this->db->order_by('status', 'desc')
            ->order_by('name', 'asc')
            ->get($this->table)
            ->result_array();
    }

    /**
     * Check if university name already exists
     */
    public function check_duplicate($name, $exclude_id = null) {
        $this->db->where('name', $name);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }
}
?>
