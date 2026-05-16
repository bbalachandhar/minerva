<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Coe_flyingsquad_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getVisits($batch_exam_id, $filters = [])
    {
        $this->db
            ->select([
                'fsv.*',
                "CONCAT(sf.name) AS observer_name",
                'sf.designation',
            ])
            ->from('coe_flying_squad_visits fsv')
            ->join('staff sf', 'sf.id = fsv.observer_staff_id', 'left')
            ->where('fsv.exam_group_class_batch_exam_id', (int) $batch_exam_id);

        if (!empty($filters['visit_date'])) {
            $this->db->where('fsv.visit_date', $filters['visit_date']);
        }
        if (!empty($filters['severity'])) {
            $this->db->where('fsv.severity', $filters['severity']);
        }

        return $this->db->order_by('fsv.visit_date DESC, fsv.visit_time DESC')->get()->result();
    }

    public function getById($id)
    {
        return $this->db
            ->select(['fsv.*', "CONCAT(sf.name) AS observer_name"])
            ->from('coe_flying_squad_visits fsv')
            ->join('staff sf', 'sf.id = fsv.observer_staff_id', 'left')
            ->where('fsv.id', (int) $id)
            ->get()->row();
    }

    public function insert($data)
    {
        $this->db->insert('coe_flying_squad_visits', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', (int) $id)->update('coe_flying_squad_visits', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('coe_flying_squad_visits');
    }

    public function getSeveritySummary($batch_exam_id)
    {
        return $this->db
            ->select('severity, COUNT(*) AS cnt')
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->group_by('severity')
            ->get('coe_flying_squad_visits')->result();
    }

    public function getStaff()
    {
        return $this->db
            ->select('id, name, designation')
            ->where('is_active', 'yes')
            ->order_by('name ASC')
            ->get('staff')->result();
    }
}
