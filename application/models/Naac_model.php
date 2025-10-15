<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Naac_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    // --- Functions for naac_c1_1_curriculum_design ---
    public function get_c1_1_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c1_1_curriculum_design')->row_array();
        }
        return $this->db->get('naac_c1_1_curriculum_design')->result_array();
    }

    public function add_c1_1_data($data) {
        $this->db->insert('naac_c1_1_curriculum_design', $data);
        return $this->db->insert_id();
    }

    public function update_c1_1_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c1_1_curriculum_design', $data);
        return $this->db->affected_rows();
    }

    public function delete_c1_1_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c1_1_curriculum_design');
        return $this->db->affected_rows();
    }

    // Report functions for C1.1
    public function get_c1_1_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c1_1_curriculum_design')->result_array();
    }

    // --- Functions for naac_c1_2_academic_flexibility ---
    public function get_c1_2_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c1_2_academic_flexibility')->row_array();
        }
        return $this->db->get('naac_c1_2_academic_flexibility')->result_array();
    }

    public function add_c1_2_data($data) {
        $this->db->insert('naac_c1_2_academic_flexibility', $data);
        return $this->db->insert_id();
    }

    public function update_c1_2_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c1_2_academic_flexibility', $data);
        return $this->db->affected_rows();
    }

    public function delete_c1_2_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c1_2_academic_flexibility');
        return $this->db->affected_rows();
    }

    // Report functions for C1.2
    public function get_c1_2_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c1_2_academic_flexibility')->result_array();
    }

    // --- Functions for naac_c1_3_curriculum_enrichment ---
    public function get_c1_3_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c1_3_curriculum_enrichment')->row_array();
        }
        return $this->db->get('naac_c1_3_curriculum_enrichment')->result_array();
    }

    public function add_c1_3_data($data) {
        $this->db->insert('naac_c1_3_curriculum_enrichment', $data);
        return $this->db->insert_id();
    }

    public function update_c1_3_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c1_3_curriculum_enrichment', $data);
        return $this->db->affected_rows();
    }

    public function delete_c1_3_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c1_3_curriculum_enrichment');
        return $this->db->affected_rows();
    }

    // Report functions for C1.3
    public function get_c1_3_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c1_3_curriculum_enrichment')->result_array();
    }

    // --- Functions for naac_c1_4_feedback_system ---
    public function get_c1_4_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c1_4_feedback_system')->row_array();
        }
        return $this->db->get('naac_c1_4_feedback_system')->result_array();
    }

    public function add_c1_4_data($data) {
        $this->db->insert('naac_c1_4_feedback_system', $data);
        return $this->db->insert_id();
    }

    public function update_c1_4_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c1_4_feedback_system', $data);
        return $this->db->affected_rows();
    }

    public function delete_c1_4_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c1_4_feedback_system');
        return $this->db->affected_rows();
    }

    // Report functions for C1.4
    public function get_c1_4_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c1_4_feedback_system')->result_array();
    }

    // --- Functions for naac_c2_1_student_enrollment ---
    public function get_c2_1_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c2_1_student_enrollment')->row_array();
        }
        return $this->db->get('naac_c2_1_student_enrollment')->result_array();
    }

    public function add_c2_1_data($data) {
        $this->db->insert('naac_c2_1_student_enrollment', $data);
        return $this->db->insert_id();
    }

    public function update_c2_1_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c2_1_student_enrollment', $data);
        return $this->db->affected_rows();
    }

    public function delete_c2_1_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c2_1_student_enrollment');
        return $this->db->affected_rows();
    }

    // Report functions for C2.1
    public function get_c2_1_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c2_1_student_enrollment')->result_array();
    }

    // --- Functions for naac_c2_2_student_diversity ---
    public function get_c2_2_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c2_2_student_diversity')->row_array();
        }
        return $this->db->get('naac_c2_2_student_diversity')->result_array();
    }

    public function add_c2_2_data($data) {
        $this->db->insert('naac_c2_2_student_diversity', $data);
        return $this->db->insert_id();
    }

    public function update_c2_2_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c2_2_student_diversity', $data);
        return $this->db->affected_rows();
    }

    public function delete_c2_2_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c2_2_student_diversity');
        return $this->db->affected_rows();
    }

    // Report functions for C2.2
    public function get_c2_2_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c2_2_student_diversity')->result_array();
    }

    // --- Functions for naac_c2_3_teaching_learning_process ---
    public function get_c2_3_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c2_3_teaching_learning_process')->row_array();
        }
        return $this->db->get('naac_c2_3_teaching_learning_process')->result_array();
    }

    public function add_c2_3_data($data) {
        $this->db->insert('naac_c2_3_teaching_learning_process', $data);
        return $this->db->insert_id();
    }

    public function update_c2_3_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c2_3_teaching_learning_process', $data);
        return $this->db->affected_rows();
    }

    public function delete_c2_3_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c2_3_teaching_learning_process');
        return $this->db->affected_rows();
    }

    // Report functions for C2.3
    public function get_c2_3_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c2_3_teaching_learning_process')->result_array();
    }

    // --- Functions for naac_c2_4_teacher_profile_quality ---
    public function get_c2_4_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c2_4_teacher_profile_quality')->row_array();
        }
        return $this->db->get('naac_c2_4_teacher_profile_quality')->result_array();
    }

    public function add_c2_4_data($data) {
        $this->db->insert('naac_c2_4_teacher_profile_quality', $data);
        return $this->db->insert_id();
    }

    public function update_c2_4_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c2_4_teacher_profile_quality', $data);
        return $this->db->affected_rows();
    }

    public function delete_c2_4_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c2_4_teacher_profile_quality');
        return $this->db->affected_rows();
    }

    // Report functions for C2.4
    public function get_c2_4_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c2_4_teacher_profile_quality')->result_array();
    }

    // --- Functions for naac_c2_5_evaluation_process ---
    public function get_c2_5_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c2_5_evaluation_process')->row_array();
        }
        return $this->db->get('naac_c2_5_evaluation_process')->result_array();
    }

    public function add_c2_5_data($data) {
        $this->db->insert('naac_c2_5_evaluation_process', $data);
        return $this->db->insert_id();
    }

    public function update_c2_5_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c2_5_evaluation_process', $data);
        return $this->db->affected_rows();
    }

    public function delete_c2_5_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c2_5_evaluation_process');
        return $this->db->affected_rows();
    }

    // Report functions for C2.5
    public function get_c2_5_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c2_5_evaluation_process')->result_array();
    }

    // --- Functions for naac_c2_6_student_performance ---
    public function get_c2_6_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c2_6_student_performance')->row_array();
        }
        return $this->db->get('naac_c2_6_student_performance')->result_array();
    }

    public function add_c2_6_data($data) {
        $this->db->insert('naac_c2_6_student_performance', $data);
        return $this->db->insert_id();
    }

    public function update_c2_6_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c2_6_student_performance', $data);
        return $this->db->affected_rows();
    }

    public function delete_c2_6_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c2_6_student_performance');
        return $this->db->affected_rows();
    }

    // Report functions for C2.6
    public function get_c2_6_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c2_6_student_performance')->result_array();
    }

    // --- Functions for naac_c2_7_student_satisfaction_survey ---
    public function get_c2_7_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c2_7_student_satisfaction_survey')->row_array();
        }
        return $this->db->get('naac_c2_7_student_satisfaction_survey')->result_array();
    }

    public function add_c2_7_data($data) {
        $this->db->insert('naac_c2_7_student_satisfaction_survey', $data);
        return $this->db->insert_id();
    }

    public function update_c2_7_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c2_7_student_satisfaction_survey', $data);
        return $this->db->affected_rows();
    }

    public function delete_c2_7_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c2_7_student_satisfaction_survey');
        return $this->db->affected_rows();
    }

    // Report functions for C2.7
    public function get_c2_7_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c2_7_student_satisfaction_survey')->result_array();
    }

    // --- Functions for naac_c3_1_research_promotion ---
    public function get_c3_1_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c3_1_research_promotion')->row_array();
        }
        return $this->db->get('naac_c3_1_research_promotion')->result_array();
    }

    public function add_c3_1_data($data) {
        $this->db->insert('naac_c3_1_research_promotion', $data);
        return $this->db->insert_id();
    }

    public function update_c3_1_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c3_1_research_promotion', $data);
        return $this->db->affected_rows();
    }

    public function delete_c3_1_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c3_1_research_promotion');
        return $this->db->affected_rows();
    }

    // Report functions for C3.1
    public function get_c3_1_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c3_1_research_promotion')->result_array();
    }

    // --- Functions for naac_c3_2_resource_mobilization ---
    public function get_c3_2_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c3_2_resource_mobilization')->row_array();
        }
        return $this->db->get('naac_c3_2_resource_mobilization')->result_array();
    }

    public function add_c3_2_data($data) {
        $this->db->insert('naac_c3_2_resource_mobilization', $data);
        return $this->db->insert_id();
    }

    public function update_c3_2_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c3_2_resource_mobilization', $data);
        return $this->db->affected_rows();
    }

    public function delete_c3_2_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c3_2_resource_mobilization');
        return $this->db->affected_rows();
    }

    // Report functions for C3.2
    public function get_c3_2_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c3_2_resource_mobilization')->result_array();
    }

    // --- Functions for naac_c3_3_innovation_ecosystem ---
    public function get_c3_3_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c3_3_innovation_ecosystem')->row_array();
        }
        return $this->db->get('naac_c3_3_innovation_ecosystem')->result_array();
    }

    public function add_c3_3_data($data) {
        $this->db->insert('naac_c3_3_innovation_ecosystem', $data);
        return $this->db->insert_id();
    }

    public function update_c3_3_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c3_3_innovation_ecosystem', $data);
        return $this->db->affected_rows();
    }

    public function delete_c3_3_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c3_3_innovation_ecosystem');
        return $this->db->affected_rows();
    }

    // Report functions for C3.3
    public function get_c3_3_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c3_3_innovation_ecosystem')->result_array();
    }

    // --- Functions for naac_c3_4_research_publications_awards ---
    public function get_c3_4_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c3_4_research_publications_awards')->row_array();
        }
        return $this->db->get('naac_c3_4_research_publications_awards')->result_array();
    }

    public function add_c3_4_data($data) {
        $this->db->insert('naac_c3_4_research_publications_awards', $data);
        return $this->db->insert_id();
    }

    public function update_c3_4_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c3_4_research_publications_awards', $data);
        return $this->db->affected_rows();
    }

    public function delete_c3_4_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c3_4_research_publications_awards');
        return $this->db->affected_rows();
    }

    // Report functions for C3.4
    public function get_c3_4_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c3_4_research_publications_awards')->result_array();
    }

    // --- Functions for naac_c3_5_consultancy ---
    public function get_c3_5_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c3_5_consultancy')->row_array();
        }
        return $this->db->get('naac_c3_5_consultancy')->result_array();
    }

    public function add_c3_5_data($data) {
        $this->db->insert('naac_c3_5_consultancy', $data);
        return $this->db->insert_id();
    }

    public function update_c3_5_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c3_5_consultancy', $data);
        return $this->db->affected_rows();
    }

    public function delete_c3_5_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c3_5_consultancy');
        return $this->db->affected_rows();
    }

    // Report functions for C3.5
    public function get_c3_5_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c3_5_consultancy')->result_array();
    }

    // --- Functions for naac_c3_6_extension_activities ---
    public function get_c3_6_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c3_6_extension_activities')->row_array();
        }
        return $this->db->get('naac_c3_6_extension_activities')->result_array();
    }

    public function add_c3_6_data($data) {
        $this->db->insert('naac_c3_6_extension_activities', $data);
        return $this->db->insert_id();
    }

    public function update_c3_6_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c3_6_extension_activities', $data);
        return $this->db->affected_rows();
    }

    public function delete_c3_6_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c3_6_extension_activities');
        return $this->db->affected_rows();
    }

    // Report functions for C3.6
    public function get_c3_6_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c3_6_extension_activities')->result_array();
    }

    // --- Functions for naac_c3_7_collaboration ---
    public function get_c3_7_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c3_7_collaboration')->row_array();
        }
        return $this->db->get('naac_c3_7_collaboration')->result_array();
    }

    public function add_c3_7_data($data) {
        $this->db->insert('naac_c3_7_collaboration', $data);
        return $this->db->insert_id();
    }

    public function update_c3_7_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c3_7_collaboration', $data);
        return $this->db->affected_rows();
    }

    public function delete_c3_7_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c3_7_collaboration');
        return $this->db->affected_rows();
    }

    // Report functions for C3.7
    public function get_c3_7_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c3_7_collaboration')->result_array();
    }

    // --- Functions for naac_c4_1_physical_facilities ---
    public function get_c4_1_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c4_1_physical_facilities')->row_array();
        }
        return $this->db->get('naac_c4_1_physical_facilities')->result_array();
    }

    public function add_c4_1_data($data) {
        $this->db->insert('naac_c4_1_physical_facilities', $data);
        return $this->db->insert_id();
    }

    public function update_c4_1_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c4_1_physical_facilities', $data);
        return $this->db->affected_rows();
    }

    public function delete_c4_1_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c4_1_physical_facilities');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c4_2_library_resources ---
    public function get_c4_2_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c4_2_library_resources')->row_array();
        }
        return $this->db->get('naac_c4_2_library_resources')->result_array();
    }

    public function add_c4_2_data($data) {
        $this->db->insert('naac_c4_2_library_resources', $data);
        return $this->db->insert_id();
    }

    public function update_c4_2_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c4_2_library_resources', $data);
        return $this->db->affected_rows();
    }

    public function delete_c4_2_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c4_2_library_resources');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c4_3_it_infrastructure ---
    public function get_c4_3_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c4_3_it_infrastructure')->row_array();
        }
        return $this->db->get('naac_c4_3_it_infrastructure')->result_array();
    }

    public function add_c4_3_data($data) {
        $this->db->insert('naac_c4_3_it_infrastructure', $data);
        return $this->db->insert_id();
    }

    public function update_c4_3_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c4_3_it_infrastructure', $data);
        return $this->db->affected_rows();
    }

    public function delete_c4_3_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c4_3_it_infrastructure');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c4_4_campus_maintenance ---
    public function get_c4_4_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c4_4_campus_maintenance')->row_array();
        }
        return $this->db->get('naac_c4_4_campus_maintenance')->result_array();
    }

    public function add_c4_4_data($data) {
        $this->db->insert('naac_c4_4_campus_maintenance', $data);
        return $this->db->insert_id();
    }

    public function update_c4_4_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c4_4_campus_maintenance', $data);
        return $this->db->affected_rows();
    }

    public function delete_c4_4_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c4_4_campus_maintenance');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c5_1_student_support ---
    public function get_c5_1_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c5_1_student_support')->row_array();
        }
        return $this->db->get('naac_c5_1_student_support')->result_array();
    }

    public function add_c5_1_data($data) {
        $this->db->insert('naac_c5_1_student_support', $data);
        return $this->db->insert_id();
    }

    public function update_c5_1_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c5_1_student_support', $data);
        return $this->db->affected_rows();
    }

    public function delete_c5_1_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c5_1_student_support');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c5_2_student_progression ---
    public function get_c5_2_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c5_2_student_progression')->row_array();
        }
        return $this->db->get('naac_c5_2_student_progression')->result_array();
    }

    public function add_c5_2_data($data) {
        $this->db->insert('naac_c5_2_student_progression', $data);
        return $this->db->insert_id();
    }

    public function update_c5_2_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c5_2_student_progression', $data);
        return $this->db->affected_rows();
    }

    public function delete_c5_2_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c5_2_student_progression');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c5_3_student_participation ---
    public function get_c5_3_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c5_3_student_participation')->row_array();
        }
        return $this->db->get('naac_c5_3_student_participation')->result_array();
    }

    public function add_c5_3_data($data) {
        $this->db->insert('naac_c5_3_student_participation', $data);
        return $this->db->insert_id();
    }

    public function update_c5_3_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c5_3_student_participation', $data);
        return $this->db->affected_rows();
    }

    public function delete_c5_3_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c5_3_student_participation');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c5_4_alumni_engagement ---
    public function get_c5_4_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c5_4_alumni_engagement')->row_array();
        }
        return $this->db->get('naac_c5_4_alumni_engagement')->result_array();
    }

    public function add_c5_4_data($data) {
        $this->db->insert('naac_c5_4_alumni_engagement', $data);
        return $this->db->insert_id();
    }

    public function update_c5_4_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c5_4_alumni_engagement', $data);
        return $this->db->affected_rows();
    }

    public function delete_c5_4_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c5_4_alumni_engagement');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c6_1_vision_leadership ---
    public function get_c6_1_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c6_1_vision_leadership')->row_array();
        }
        return $this->db->get('naac_c6_1_vision_leadership')->result_array();
    }

    public function add_c6_1_data($data) {
        $this->db->insert('naac_c6_1_vision_leadership', $data);
        return $this->db->insert_id();
    }

    public function update_c6_1_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c6_1_vision_leadership', $data);
        return $this->db->affected_rows();
    }

    public function delete_c6_1_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c6_1_vision_leadership');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c6_2_strategy_deployment ---
    public function get_c6_2_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c6_2_strategy_deployment')->row_array();
        }
        return $this->db->get('naac_c6_2_strategy_deployment')->result_array();
    }

    public function add_c6_2_data($data) {
        $this->db->insert('naac_c6_2_strategy_deployment', $data);
        return $this->db->insert_id();
    }

    public function update_c6_2_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c6_2_strategy_deployment', $data);
        return $this->db->affected_rows();
    }

    public function delete_c6_2_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c6_2_strategy_deployment');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c6_3_faculty_empowerment ---
    public function get_c6_3_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c6_3_faculty_empowerment')->row_array();
        }
        return $this->db->get('naac_c6_3_faculty_empowerment')->result_array();
    }

    public function add_c6_3_data($data) {
        $this->db->insert('naac_c6_3_faculty_empowerment', $data);
        return $this->db->insert_id();
    }

    public function update_c6_3_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c6_3_faculty_empowerment', $data);
        return $this->db->affected_rows();
    }

    public function delete_c6_3_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c6_3_faculty_empowerment');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c6_4_financial_management ---
    public function get_c6_4_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c6_4_financial_management')->row_array();
        }
        return $this->db->get('naac_c6_4_financial_management')->result_array();
    }

    public function add_c6_4_data($data) {
        $this->db->insert('naac_c6_4_financial_management', $data);
        return $this->db->insert_id();
    }

    public function update_c6_4_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c6_4_financial_management', $data);
        return $this->db->affected_rows();
    }

    public function delete_c6_4_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c6_4_financial_management');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c6_5_iqas ---
    public function get_c6_5_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c6_5_iqas')->row_array();
        }
        return $this->db->get('naac_c6_5_iqas')->result_array();
    }

    public function add_c6_5_data($data) {
        $this->db->insert('naac_c6_5_iqas', $data);
        return $this->db->insert_id();
    }

    public function update_c6_5_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c6_5_iqas', $data);
        return $this->db->affected_rows();
    }

    public function delete_c6_5_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c6_5_iqas');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c7_1_values_social_responsibilities ---
    public function get_c7_1_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c7_1_values_social_responsibilities')->row_array();
        }
        return $this->db->get('naac_c7_1_values_social_responsibilities')->result_array();
    }

    public function add_c7_1_data($data) {
        $this->db->insert('naac_c7_1_values_social_responsibilities', $data);
        return $this->db->insert_id();
    }

    public function update_c7_1_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c7_1_values_social_responsibilities', $data);
        return $this->db->affected_rows();
    }

    public function delete_c7_1_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c7_1_values_social_responsibilities');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c7_2_best_practices ---
    public function get_c7_2_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c7_2_best_practices')->row_array();
        }
        return $this->db->get('naac_c7_2_best_practices')->result_array();
    }

    public function add_c7_2_data($data) {
        $this->db->insert('naac_c7_2_best_practices', $data);
        return $this->db->insert_id();
    }

    public function update_c7_2_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c7_2_best_practices', $data);
        return $this->db->affected_rows();
    }

    public function delete_c7_2_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c7_2_best_practices');
        return $this->db->affected_rows();
    }

    // --- Functions for naac_c7_3_institutional_distinctiveness ---
    public function get_c7_3_data($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('naac_c7_3_institutional_distinctiveness')->row_array();
        }
        return $this->db->get('naac_c7_3_institutional_distinctiveness')->result_array();
    }

    public function add_c7_3_data($data) {
        $this->db->insert('naac_c7_3_institutional_distinctiveness', $data);
        return $this->db->insert_id();
    }

    public function update_c7_3_data($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_c7_3_institutional_distinctiveness', $data);
        return $this->db->affected_rows();
    }

    public function delete_c7_3_data($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_c7_3_institutional_distinctiveness');
        return $this->db->affected_rows();
    }

    // Report functions for C4.1
    public function get_c4_1_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c4_1_physical_facilities')->result_array();
    }

    // Report functions for C4.2
    public function get_c4_2_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c4_2_library_resources')->result_array();
    }

    // Report functions for C4.3
    public function get_c4_3_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c4_3_it_infrastructure')->result_array();
    }

    // Report functions for C4.4
    public function get_c4_4_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c4_4_campus_maintenance')->result_array();
    }

    // Report functions for C5.1
    public function get_c5_1_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c5_1_student_support')->result_array();
    }

    // Report functions for C5.2
    public function get_c5_2_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c5_2_student_progression')->result_array();
    }

    // Report functions for C5.3
    public function get_c5_3_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c5_3_student_participation')->result_array();
    }

    // Report functions for C5.4
    public function get_c5_4_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c5_4_alumni_engagement')->result_array();
    }

    // Report functions for C6.1
    public function get_c6_1_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c6_1_vision_leadership')->result_array();
    }

    // Report functions for C6.2
    public function get_c6_2_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c6_2_strategy_deployment')->result_array();
    }

    // Report functions for C6.3
    public function get_c6_3_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c6_3_faculty_empowerment')->result_array();
    }

    // Report functions for C6.4
    public function get_c6_4_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c6_4_financial_management')->result_array();
    }

    // Report functions for C6.5
    public function get_c6_5_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c6_5_iqas')->result_array();
    }

    // Report functions for C7.1
    public function get_c7_1_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c7_1_values_social_responsibilities')->result_array();
    }

    // Report functions for C7.2
    public function get_c7_2_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c7_2_best_practices')->result_array();
    }

    // Report functions for C7.3
    public function get_c7_3_report_data($academic_year = null) {
        if ($academic_year) {
            $this->db->where('academic_year', $academic_year);
        }
        return $this->db->get('naac_c7_3_institutional_distinctiveness')->result_array();
    }
}
