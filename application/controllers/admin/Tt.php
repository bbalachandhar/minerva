<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tt extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tt_period_model');
        $this->load->model('Tt_room_model');
        $this->load->model('Tt_batch_model');
        $this->load->model('Tt_subjectload_model');
        $this->load->model('Tt_teacher_model');
        $this->load->model('Tt_entry_model');
        $this->load->model('Tt_generator_model');
        $this->load->model('Tt_joint_model');
        $this->load->model('Tt_substitution_model');
        $this->load->model('department_model');
        $this->load->model('staff_model');
        $this->load->model('subjectgroup_model');
    }

    private function _setMenu()
    {
        $this->session->set_userdata('top_menu', 'Auto Timetable');
        $this->session->set_userdata('sub_menu', 'tt');
    }

    private function _baseData()
    {
        return [
            'session_id' => $this->setting_model->getCurrentSession(),
        ];
    }

    // =========================================================================
    // DASHBOARD
    // =========================================================================

    public function dashboard()
    {
        if (!$this->rbac->hasPrivilege('tt_dashboard', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $session_id = $this->setting_model->getCurrentSession();

        $period_count     = $this->db->where('session_id', $session_id)->where('is_break', 0)->count_all_results('tt_periods');
        $break_count      = $this->db->where('session_id', $session_id)->where('is_break', 1)->count_all_results('tt_periods');
        $room_count       = $this->db->count_all_results('tt_rooms');
        $batch_count      = $this->db->where('session_id', $session_id)->count_all_results('tt_batches');
        $teacher_const_ct = $this->db->where('session_id', $session_id)->count_all_results('tt_teacher_constraints');

        // Subject loads: count distinct configured class-sections
        $load_classes = $this->db->select('DISTINCT class_id, section_id')
            ->where('session_id', $session_id)->get('tt_subject_load')->result();
        $load_class_count  = count($load_classes);
        $total_load_rows   = $this->db->where('session_id', $session_id)->count_all_results('tt_subject_load');
        $missing_teacher   = $this->db->where('session_id', $session_id)->where('staff_id IS NULL', null, false)->count_all_results('tt_subject_load');

        // Subject colors
        $colored_subjects = $this->db->where('tt_color !=', '')->count_all_results('subjects');

        // Last generation
        $last_gen = $this->db->where('session_id', $session_id)->order_by('id', 'DESC')->limit(1)->get('tt_gen_log')->row();

        // Recent confirmed timetable (last confirmed_at)
        $last_confirmed = $this->db->where('session_id', $session_id)->where('confirmed_at IS NOT NULL', null, false)->order_by('confirmed_at', 'DESC')->limit(1)->get('tt_gen_log')->row();

        $data = [
            'session_id'       => $session_id,
            'period_count'     => $period_count,
            'break_count'      => $break_count,
            'room_count'       => $room_count,
            'batch_count'      => $batch_count,
            'teacher_const_ct' => $teacher_const_ct,
            'load_class_count' => $load_class_count,
            'total_load_rows'  => $total_load_rows,
            'missing_teacher'  => $missing_teacher,
            'colored_subjects' => $colored_subjects,
            'last_gen'         => $last_gen,
            'last_confirmed'   => $last_confirmed,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/dashboard', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // LESSON BROWSER
    // =========================================================================

    public function lesson_browser()
    {
        if (!$this->rbac->hasPrivilege('tt_lesson_browser', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $session_id = $this->setting_model->getCurrentSession();

        $data = $this->_baseData();
        $data['departments'] = $this->department_model->getAllDepartment();
        $data['staff_list']  = $this->staff_model->getStaffbyrole(2);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/lesson_browser', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_lesson_browser_data()
    {
        $session_id  = $this->setting_model->getCurrentSession();
        $dept_id     = (int) $this->input->post('dept_id');
        $staff_id    = (int) $this->input->post('staff_id');
        $subject_id  = (int) $this->input->post('subject_id');

        $this->db->select('tt_subject_load.*, subjects.name as subject_name, subjects.code as subject_code, subjects.type as subject_type,
                            staff.name as staff_name, staff.surname as staff_surname, staff.employee_id,
                            classes.class as class_name, sections.section as section_name,
                            tt_batches.batch_name,
                            subject_groups.name as subject_group_name')
            ->from('tt_subject_load')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_subject_load.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('staff', 'staff.id = tt_subject_load.staff_id', 'left')
            ->join('classes', 'classes.id = tt_subject_load.class_id', 'left')
            ->join('sections', 'sections.id = tt_subject_load.section_id', 'left')
            ->join('tt_batches', 'tt_batches.id = tt_subject_load.batch_id', 'left')
            ->join('subject_groups', 'subject_groups.id = tt_subject_load.subject_group_id', 'left')
            ->where('tt_subject_load.session_id', $session_id);

        if ($dept_id) $this->db->where('classes.department_id', $dept_id);
        if ($staff_id) $this->db->where('tt_subject_load.staff_id', $staff_id);
        if ($subject_id) $this->db->where('subject_group_subjects.subject_id', $subject_id);

        $rows = $this->db->order_by('classes.class', 'ASC')
            ->order_by('sections.section', 'ASC')
            ->order_by('subjects.name', 'ASC')
            ->get()->result();

        $html = $this->load->view('admin/tt/_lesson_browser_rows', ['rows' => $rows], true);
        echo json_encode(['status' => '1', 'html' => $html, 'count' => count($rows)]);
    }

    // =========================================================================
    // JOINT / CROSS-CLASS LESSONS
    // =========================================================================

    public function joint_lessons()
    {
        if (!$this->rbac->hasPrivilege('tt_joint_lessons', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $this->load->model('Tt_joint_model');
        $session_id = $this->setting_model->getCurrentSession();

        // Build classlist with sections pre-loaded for the picker
        $this->load->model('section_model');
        $raw_classes = $this->class_model->get();
        $classlist   = [];
        foreach ($raw_classes as $cls) {
            $sections = $this->section_model->getClassBySection($cls['id']);
            if (!empty($sections)) {
                $cls['sections'] = $sections;
                $classlist[]     = $cls;
            }
        }

        $data = $this->_baseData();
        $data['joint_lessons'] = $this->Tt_joint_model->getAll($session_id);
        $data['classlist']     = $classlist;
        $data['subjects']      = $this->db->select('id, name, code, tt_color, tt_abbr')
                                    ->where('is_active', 'yes')
                                    ->order_by('name', 'ASC')
                                    ->get('subjects')->result();
        $data['staff_list']    = $this->staff_model->getStaffbyrole(2);
        $data['rooms']         = $this->Tt_room_model->getAll();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/joint_lessons', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_joint_lesson()
    {
        if (!$this->rbac->hasPrivilege('tt_joint_lessons', 'can_view')) { access_denied(); }
        $this->load->model('Tt_joint_model');
        $id     = (int) $this->input->post('id');
        $lesson = $this->Tt_joint_model->getById($id);
        if (!$lesson) { echo json_encode(['status' => '0']); return; }
        echo json_encode(['status' => '1', 'lesson' => $lesson]);
    }

    public function save_joint_lesson()
    {
        if (!$this->rbac->hasPrivilege('tt_joint_lessons', 'can_add')) { access_denied(); }
        $this->load->model('Tt_joint_model');
        $session_id  = $this->setting_model->getCurrentSession();
        $classes_raw = json_decode($this->input->post('classes_json'), true);
        if (empty($classes_raw)) {
            echo json_encode(['status' => '0', 'message' => 'No class-sections selected.']); return;
        }
        $data = [
            'id'                 => (int) $this->input->post('id'),
            'name'               => trim($this->input->post('name')),
            'subject_id'         => (int) $this->input->post('subject_id'),
            'staff_id'           => $this->input->post('staff_id')     ?: null,
            'alt_staff_id'       => $this->input->post('alt_staff_id') ?: null,
            'room_id'            => $this->input->post('room_id')      ?: null,
            'periods_per_week'   => (int) $this->input->post('periods_per_week')    ?: 1,
            'consecutive_periods'=> (int) $this->input->post('consecutive_periods') ?: 1,
            'max_per_day'        => (int) $this->input->post('max_per_day')         ?: 1,
            'distribute_evenly'  => $this->input->post('distribute_evenly') ? 1 : 0,
            'priority'           => (int) $this->input->post('priority')            ?: 5,
            'notes'              => $this->input->post('notes'),
        ];
        if (empty($data['name']) || empty($data['subject_id'])) {
            echo json_encode(['status' => '0', 'message' => 'Name and subject are required.']); return;
        }
        $id = $this->Tt_joint_model->save($session_id, $data, $classes_raw);
        echo json_encode($id ? ['status' => '1', 'id' => $id] : ['status' => '0', 'message' => 'Error saving.']);
    }

    public function delete_joint_lesson($id)
    {
        if (!$this->rbac->hasPrivilege('tt_joint_lessons', 'can_delete')) { access_denied(); }
        $this->load->model('Tt_joint_model');
        $result = $this->Tt_joint_model->delete((int) $id);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    // =========================================================================
    // PERIOD SETUP
    // =========================================================================

    public function periods()
    {
        if (!$this->rbac->hasPrivilege('tt_periods', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['periods'] = $this->Tt_period_model->getAll($data['session_id']);
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/periods', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_period()
    {
        if (!$this->rbac->hasPrivilege('tt_periods', 'can_add')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $id         = (int) $this->input->post('id');
        $data = [
            'session_id'  => $session_id,
            'name'        => $this->input->post('name'),
            'start_time'  => $this->input->post('start_time'),
            'end_time'    => $this->input->post('end_time'),
            'is_break'    => (int) $this->input->post('is_break'),
            'break_label' => $this->input->post('break_label'),
            'sort_order'  => (int) $this->input->post('sort_order'),
        ];
        if ($id > 0) {
            $data['id'] = $id;
        }
        $result = $this->Tt_period_model->save($data);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    public function delete_period($id)
    {
        if (!$this->rbac->hasPrivilege('tt_periods', 'can_delete')) {
            access_denied();
        }
        $this->Tt_period_model->delete($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Deleted successfully.</div>');
        redirect('admin/tt/periods');
    }

    public function reorder_periods()
    {
        $order = $this->input->post('order');
        if (!empty($order)) {
            foreach ($order as $sort => $pid) {
                $this->Tt_period_model->updateOrder((int)$pid, (int)$sort);
            }
        }
        echo json_encode(['status' => '1']);
    }

    // =========================================================================
    // ROOMS
    // =========================================================================

    public function rooms()
    {
        if (!$this->rbac->hasPrivilege('tt_rooms', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['rooms']       = $this->Tt_room_model->getAll();
        $data['departments'] = $this->department_model->getDepartmentType();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/rooms', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_room()
    {
        if (!$this->rbac->hasPrivilege('tt_rooms', 'can_add')) {
            access_denied();
        }
        $id = (int) $this->input->post('id');
        $data = [
            'name'          => $this->input->post('name'),
            'room_number'   => $this->input->post('room_number'),
            'capacity'      => (int) $this->input->post('capacity'),
            'room_type'     => $this->input->post('room_type'),
            'department_id' => (int) $this->input->post('department_id') ?: null,
            'is_shared'     => (int) $this->input->post('is_shared'),
            'is_active'     => 1,
        ];
        if ($id > 0) {
            $data['id'] = $id;
        }
        $result = $this->Tt_room_model->save($data);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    public function delete_room($id)
    {
        if (!$this->rbac->hasPrivilege('tt_rooms', 'can_delete')) {
            access_denied();
        }
        $this->Tt_room_model->delete($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Deleted successfully.</div>');
        redirect('admin/tt/rooms');
    }

    // =========================================================================
    // BATCHES
    // =========================================================================

    public function batches()
    {
        if (!$this->rbac->hasPrivilege('tt_batches', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['classlist']   = $this->class_model->get();
        $data['departments'] = $this->department_model->getDepartmentType();
        $data['batches']     = $this->Tt_batch_model->getAllWithNames($data['session_id']);
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/batches', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_batch()
    {
        if (!$this->rbac->hasPrivilege('tt_batches', 'can_add')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $id = (int) $this->input->post('id');
        $data = [
            'session_id'    => $session_id,
            'class_id'      => (int) $this->input->post('class_id'),
            'section_id'    => (int) $this->input->post('section_id'),
            'batch_name'    => strtoupper(trim($this->input->post('batch_name'))),
            'student_count' => (int) $this->input->post('student_count'),
        ];
        if ($id > 0) {
            $data['id'] = $id;
        }
        $result = $this->Tt_batch_model->save($data);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    public function delete_batch($id)
    {
        if (!$this->rbac->hasPrivilege('tt_batches', 'can_delete')) {
            access_denied();
        }
        $this->Tt_batch_model->delete($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Deleted successfully.</div>');
        redirect('admin/tt/batches');
    }

    // =========================================================================
    // SUBJECT LOAD
    // =========================================================================

    public function subject_load()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_load', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['classlist']   = $this->class_model->get();
        $data['departments'] = $this->department_model->getDepartmentType();
        $data['rooms']       = $this->Tt_room_model->getAll();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/subject_load', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_subject_load_data()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');

        $subjects  = $this->subjectgroup_model->getGroupsubjectsByClassSection($class_id, $section_id, $session_id);
        $loads     = $this->Tt_subjectload_model->getForClassSection($session_id, $class_id, $section_id);
        $staff     = $this->staff_model->getStaffbyrole(2);
        $batches   = $this->Tt_batch_model->getForClassSection($session_id, $class_id, $section_id);
        $rooms     = $this->Tt_room_model->getAll();

        $load_map = [];
        foreach ($loads as $l) {
            $key = $l->subject_group_subject_id . '_' . ($l->batch_id ?: '0');
            $load_map[$key] = $l;
        }

        $data = [
            'subjects'  => $subjects,
            'load_map'  => $load_map,
            'staff'     => $staff,
            'batches'   => $batches,
            'rooms'     => $rooms,
            'class_id'  => $class_id,
            'section_id'=> $section_id,
        ];
        $html = $this->load->view('admin/tt/_subject_load_rows', $data, true);
        echo json_encode(['status' => '1', 'html' => $html]);
    }

    public function get_subject_load_raw()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');
        $loads      = $this->Tt_subjectload_model->getForClassSection($session_id, $class_id, $section_id);
        if (empty($loads)) {
            echo json_encode(['status' => '0']);
            return;
        }
        $data = [];
        foreach ($loads as $l) {
            if ($l->batch_id) continue; // skip batch rows for copy
            $data[$l->subject_group_subject_id] = [
                'periods_per_week'    => (int) $l->periods_per_week,
                'consecutive_periods' => (int) $l->consecutive_periods,
                'max_per_day'         => (int) $l->max_per_day,
                'min_per_day'         => (int) $l->min_per_day,
                'distribute_evenly'   => (int) $l->distribute_evenly,
                'priority'            => (int) $l->priority,
            ];
        }
        echo json_encode(['status' => '1', 'data' => $data]);
    }

    public function save_subject_load()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_load', 'can_add')) {
            access_denied();
        }
        $session_id  = $this->setting_model->getCurrentSession();
        $class_id    = (int) $this->input->post('class_id');
        $section_id  = (int) $this->input->post('section_id');
        $rows        = $this->input->post('rows');

        if (empty($rows)) {
            echo json_encode(['status' => '0', 'message' => 'No data received.']);
            return;
        }

        $result = $this->Tt_subjectload_model->saveRows($session_id, $class_id, $section_id, $rows);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    // =========================================================================
    // TEACHER CONSTRAINTS
    // =========================================================================

    public function teacher_constraints()
    {
        if (!$this->rbac->hasPrivilege('tt_teacher_constr', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['staff_list']   = $this->staff_model->getStaffbyrole(2);
        $data['rooms']        = $this->Tt_room_model->getActive();
        $data['constraints']  = $this->Tt_teacher_model->getAllConstraints($data['session_id']);
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/teacher_constraints', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_teacher_constraint()
    {
        if (!$this->rbac->hasPrivilege('tt_teacher_constr', 'can_add')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $id = (int) $this->input->post('id');
        $data = [
            'session_id'           => $session_id,
            'staff_id'             => (int) $this->input->post('staff_id'),
            'max_periods_per_day'  => (int) $this->input->post('max_periods_per_day'),
            'max_periods_per_week' => (int) $this->input->post('max_periods_per_week'),
            'min_free_per_day'     => (int) $this->input->post('min_free_per_day'),
            'max_gap_per_day'      => $this->input->post('max_gap_per_day') !== '' ? (int)$this->input->post('max_gap_per_day') : null,
            'preferred_room_id'    => (int) $this->input->post('preferred_room_id') ?: null,
            'preferred_start_time' => $this->input->post('preferred_start_time') ?: null,
            'preferred_end_time'   => $this->input->post('preferred_end_time') ?: null,
            'avoid_first_period'   => (int) $this->input->post('avoid_first_period'),
            'avoid_last_period'    => (int) $this->input->post('avoid_last_period'),
        ];
        if ($id > 0) {
            $data['id'] = $id;
        }
        $result = $this->Tt_teacher_model->saveConstraint($data);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    public function delete_teacher_constraint($id)
    {
        if (!$this->rbac->hasPrivilege('tt_teacher_constr', 'can_delete')) {
            access_denied();
        }
        $this->Tt_teacher_model->deleteConstraint($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Deleted successfully.</div>');
        redirect('admin/tt/teacher_constraints');
    }

    // =========================================================================
    // TEACHER UNAVAILABILITY
    // =========================================================================

    public function teacher_unavail()
    {
        if (!$this->rbac->hasPrivilege('tt_teacher_avail', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['staff_list'] = $this->staff_model->getStaffbyrole(2);
        $data['periods']    = $this->Tt_period_model->getAllNonBreak($data['session_id']);
        $data['days']       = $this->customlib->getDaysnameWithoutLang();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/teacher_unavail', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_teacher_unavail()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $staff_id   = (int) $this->input->post('staff_id');
        $data = $this->Tt_teacher_model->getUnavailability($session_id, $staff_id);
        echo json_encode(['status' => '1', 'data' => $data]);
    }

    public function save_teacher_unavail()
    {
        if (!$this->rbac->hasPrivilege('tt_teacher_avail', 'can_add')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $staff_id   = (int) $this->input->post('staff_id');
        $slots      = $this->input->post('slots'); // array of {day, period_id}
        $result = $this->Tt_teacher_model->saveUnavailability($session_id, $staff_id, $slots);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    // =========================================================================
    // AUTO GENERATE
    // =========================================================================

    public function generate()
    {
        if (!$this->rbac->hasPrivilege('tt_generate', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['classlist']   = $this->class_model->get();
        $data['departments'] = $this->department_model->getDepartmentType();
        $data['gen_logs']    = $this->Tt_generator_model->getRecentLogs($data['session_id'], 5);
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/generate', $data);
        $this->load->view('layout/footer', $data);
    }

    public function run_generate()
    {
        if (!$this->rbac->hasPrivilege('tt_generate', 'can_add')) {
            access_denied();
        }
        $session_id    = $this->setting_model->getCurrentSession();
        $staff_id      = $this->customlib->getStaffID();
        $class_scope   = json_decode($this->input->post('class_scope'), true); // array of {class_id, section_id}
        $gen_size_raw       = $this->input->post('gen_size');
        $gen_strictness_raw = $this->input->post('gen_strictness');
        $valid_sizes        = ['normal', 'large', 'huge'];
        $valid_strict       = ['relaxed', 'normal', 'strict'];
        $settings      = [
            'allow_saturday'           => (int) $this->input->post('allow_saturday'),
            'max_same_subject_day'     => (int) $this->input->post('max_same_subject_day') ?: 1,
            'spread_evenly'            => (int) $this->input->post('spread_evenly'),
            'fill_free_periods'        => (int) $this->input->post('fill_free_periods'),
            'respect_soft_constraints' => 1,
            'gen_size'                 => in_array($gen_size_raw, $valid_sizes) ? $gen_size_raw : 'normal',
            'gen_strictness'           => in_array($gen_strictness_raw, $valid_strict) ? $gen_strictness_raw : 'normal',
        ];

        $result = $this->Tt_generator_model->generate($session_id, $staff_id, $class_scope, $settings);
        echo json_encode($result);
    }

    public function preview($gen_log_id)
    {
        if (!$this->rbac->hasPrivilege('tt_generate', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $log  = $this->Tt_generator_model->getLog((int)$gen_log_id);
        if (!$log) {
            show_404();
        }
        $data['log']         = $log;
        $data['draft']       = $this->Tt_generator_model->getDraftGrouped((int)$gen_log_id);
        $data['conflicts']   = json_decode($log->conflict_details, true) ?: [];
        $data['periods']     = $this->Tt_period_model->getAll($data['session_id']);
        $data['days']        = $this->customlib->getDaysnameWithoutLang();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/preview', $data);
        $this->load->view('layout/footer', $data);
    }

    public function confirm_draft($gen_log_id)
    {
        if (!$this->rbac->hasPrivilege('tt_generate', 'can_add')) {
            access_denied();
        }
        $gen_log_id = (int) $gen_log_id;
        $staff_id   = $this->customlib->getStaffID();
        $result     = $this->Tt_generator_model->confirmDraft($gen_log_id, $staff_id);
        if ($result) {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Timetable confirmed and saved successfully.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Something went wrong. Please try again.</div>');
        }
        redirect('admin/tt/class_grid');
    }

    public function discard_draft($gen_log_id)
    {
        $gen_log_id = (int) $gen_log_id;
        $this->Tt_generator_model->discardDraft($gen_log_id);
        $this->session->set_flashdata('msg', '<div class="alert alert-info text-center">Draft discarded.</div>');
        redirect('admin/tt/generate');
    }

    // =========================================================================
    // CLASS TIMETABLE GRID (manual view/edit)
    // =========================================================================

    public function class_grid()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['classlist']   = $this->class_model->get();
        $data['departments'] = $this->department_model->getDepartmentType();
        $data['periods']     = [];
        $data['entries']     = [];
        $data['subjects']    = [];
        $data['staff_list']  = [];
        $data['rooms']       = $this->Tt_room_model->getActive();
        $data['days']        = $this->customlib->getDaysnameWithoutLang();
        $data['class_id']    = '';
        $data['section_id']  = '';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/class_grid', $data);
        $this->load->view('layout/footer', $data);
    }

    public function load_class_grid()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');

        $periods   = $this->Tt_period_model->getAll($session_id);
        $entries   = $this->Tt_entry_model->getGridEntries($session_id, $class_id, $section_id);
        $subjects  = $this->subjectgroup_model->getGroupsubjectsByClassSection($class_id, $section_id, $session_id);
        $staff     = $this->staff_model->getStaffbyrole(2);
        $rooms     = $this->Tt_room_model->getActive();
        $batches   = $this->Tt_batch_model->getForClassSection($session_id, $class_id, $section_id);
        $days      = $this->customlib->getDaysnameWithoutLang();

        $entry_map = [];
        foreach ($entries as $e) {
            $batch_key = $e->batch_id ?: 0;
            $entry_map[$e->day][$e->period_id][$batch_key] = $e;
        }

        $data = compact('periods','entry_map','subjects','staff','rooms','batches','days','class_id','section_id','session_id');
        $html = $this->load->view('admin/tt/_grid_table', $data, true);
        echo json_encode([
            'status'   => '1',
            'html'     => $html,
            'subjects' => $subjects,
            'staff'    => $staff,
            'rooms'    => $rooms,
            'batches'  => $batches,
        ]);
    }

    public function save_cell()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_add')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $data = [
            'session_id'               => $session_id,
            'class_id'                 => (int) $this->input->post('class_id'),
            'section_id'               => (int) $this->input->post('section_id'),
            'subject_group_id'         => (int) $this->input->post('subject_group_id') ?: null,
            'subject_group_subject_id' => (int) $this->input->post('subject_group_subject_id') ?: null,
            'staff_id'                 => (int) $this->input->post('staff_id') ?: null,
            'period_id'                => (int) $this->input->post('period_id'),
            'day'                      => $this->input->post('day'),
            'room_id'                  => (int) $this->input->post('room_id') ?: null,
            'batch_id'                 => (int) $this->input->post('batch_id') ?: null,
            'is_free_period'           => (int) $this->input->post('is_free_period'),
            'free_period_label'        => $this->input->post('free_period_label'),
            'entry_type'               => 'manual',
        ];
        $cell_id = (int) $this->input->post('cell_id');

        // Check for conflicts before saving
        $conflict = $this->Tt_entry_model->checkConflict($data, $cell_id);
        if ($conflict) {
            echo json_encode(['status' => '0', 'message' => $conflict]);
            return;
        }

        $result = $this->Tt_entry_model->saveCell($data, $cell_id);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    public function delete_cell($id)
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_delete')) {
            access_denied();
        }
        $this->Tt_entry_model->deleteCell((int)$id);
        echo json_encode(['status' => '1']);
    }

    public function toggle_lock()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_edit')) {
            access_denied();
        }
        $id      = (int) $this->input->post('id');
        $locked  = (int) $this->input->post('locked');
        $this->Tt_entry_model->setLock($id, $locked);
        echo json_encode(['status' => '1']);
    }

    // =========================================================================
    // TEACHER TIMETABLE VIEW
    // =========================================================================

    public function teacher_view()
    {
        if (!$this->rbac->hasPrivilege('tt_teacher_view', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['staff_list'] = $this->staff_model->getStaffbyrole(2);
        $data['periods']    = [];
        $data['entries']    = [];
        $data['days']       = $this->customlib->getDaysnameWithoutLang();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/teacher_view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function load_teacher_grid()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $staff_id   = (int) $this->input->post('staff_id');
        $periods    = $this->Tt_period_model->getAll($session_id);
        $entries    = $this->Tt_entry_model->getTeacherEntries($session_id, $staff_id);
        $days       = $this->customlib->getDaysnameWithoutLang();

        $entry_map = [];
        foreach ($entries as $e) {
            $entry_map[$e->day][$e->period_id] = $e;
        }

        $data = compact('periods','entry_map','days','staff_id','session_id');
        $html = $this->load->view('admin/tt/_teacher_grid_table', $data, true);
        echo json_encode(['status' => '1', 'html' => $html]);
    }

    // =========================================================================
    // SUBSTITUTION / RESCHEDULING
    // =========================================================================

    public function substitution()
    {
        if (!$this->rbac->hasPrivilege('tt_substitution', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['staff_list'] = $this->staff_model->getStaffbyrole(2);
        $data['periods']    = $this->Tt_period_model->getAllNonBreak($data['session_id']);
        $data['days']       = $this->customlib->getDaysnameWithoutLang();
        $data['recent']     = $this->Tt_substitution_model->getRecent($data['session_id'], 30);
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/substitution', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_absent_slots()
    {
        $session_id     = $this->setting_model->getCurrentSession();
        $absent_staff   = (int) $this->input->post('absent_staff_id');
        $date           = $this->input->post('date');
        $day            = date('l', strtotime($date));

        $slots    = $this->Tt_entry_model->getStaffSlotsForDay($session_id, $absent_staff, $day);
        $existing = $this->Tt_substitution_model->getByDateStaff($session_id, $absent_staff, $date);

        $existing_map = [];
        foreach ($existing as $ex) {
            $existing_map[$ex->tt_entry_id] = $ex;
        }

        foreach ($slots as &$slot) {
            $slot->substitution = $existing_map[$slot->id] ?? null;
            // Find available substitute teachers for this slot
            $slot->available_teachers = $this->Tt_entry_model->getAvailableTeachers($session_id, $day, $slot->period_id, $absent_staff);
        }

        echo json_encode(['status' => '1', 'day' => $day, 'slots' => $slots]);
    }

    public function save_substitution()
    {
        if (!$this->rbac->hasPrivilege('tt_substitution', 'can_add')) {
            access_denied();
        }
        $session_id         = $this->setting_model->getCurrentSession();
        $created_by         = $this->customlib->getStaffID();
        $absent_staff_id    = (int) $this->input->post('absent_staff_id');
        $substitute_id      = (int) $this->input->post('substitute_staff_id') ?: null;
        $tt_entry_id        = (int) $this->input->post('tt_entry_id');
        $date               = $this->input->post('date');
        $day                = date('l', strtotime($date));
        $sub_type           = $substitute_id ? 'manual' : 'auto_suggested';

        $entry = $this->Tt_entry_model->getById($tt_entry_id);
        if (!$entry) {
            echo json_encode(['status' => '0', 'message' => 'Entry not found.']);
            return;
        }

        // Auto-assign best substitute if not manually chosen
        if (!$substitute_id) {
            $available = $this->Tt_entry_model->getAvailableTeachers($session_id, $day, $entry->period_id, $absent_staff_id);
            $substitute_id = !empty($available) ? $available[0]->id : null;
            $sub_type = 'auto_suggested';
        }

        $data = [
            'session_id'               => $session_id,
            'absent_staff_id'          => $absent_staff_id,
            'substitute_staff_id'      => $substitute_id,
            'tt_entry_id'              => $tt_entry_id,
            'date'                     => $date,
            'day'                      => $day,
            'period_id'                => $entry->period_id,
            'class_id'                 => $entry->class_id,
            'section_id'               => $entry->section_id,
            'subject_group_subject_id' => $entry->subject_group_subject_id,
            'room_id'                  => $entry->room_id,
            'substitution_type'        => $sub_type,
            'status'                   => 'confirmed',
            'note'                     => $this->input->post('note'),
            'created_by'               => $created_by,
        ];

        $existing_id = (int) $this->input->post('substitution_id');
        if ($existing_id > 0) {
            $data['id'] = $existing_id;
        }

        $result = $this->Tt_substitution_model->save($data);
        echo json_encode([
            'status'       => $result ? '1' : '0',
            'substitute_id'=> $substitute_id,
        ]);
    }

    public function cancel_substitution($id)
    {
        if (!$this->rbac->hasPrivilege('tt_substitution', 'can_edit')) {
            access_denied();
        }
        $this->Tt_substitution_model->cancel((int)$id);
        echo json_encode(['status' => '1']);
    }

    public function get_substitution_report()
    {
        $session_id  = $this->setting_model->getCurrentSession();
        $from_date   = $this->input->post('from_date');
        $to_date     = $this->input->post('to_date');
        $staff_id    = (int) $this->input->post('staff_id') ?: null;
        $data = $this->Tt_substitution_model->getReport($session_id, $from_date, $to_date, $staff_id);
        echo json_encode(['status' => '1', 'data' => $data]);
    }

    // =========================================================================
    // REPORTS
    // =========================================================================

    public function reports()
    {
        if (!$this->rbac->hasPrivilege('tt_reports', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['classlist']   = $this->class_model->get();
        $data['departments'] = $this->department_model->getDepartmentType();
        $data['staff_list']  = $this->staff_model->getStaffbyrole(2);
        $data['periods']     = $this->Tt_period_model->getAllNonBreak($data['session_id']);
        $data['rooms']       = $this->Tt_room_model->getActive();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/reports', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_master_report()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $class_ids  = $this->input->post('class_ids');
        $data = $this->Tt_entry_model->getMasterReport($session_id, $class_ids);
        $periods = $this->Tt_period_model->getAll($session_id);
        $days    = $this->customlib->getDaysnameWithoutLang();
        $html = $this->load->view('admin/tt/_report_master', compact('data','periods','days'), true);
        echo json_encode(['status' => '1', 'html' => $html]);
    }

    public function get_room_utilization()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $data    = $this->Tt_entry_model->getRoomUtilization($session_id);
        $periods = $this->Tt_period_model->getAllNonBreak($session_id);
        $rooms   = $this->Tt_room_model->getActive();
        $days    = $this->customlib->getDaysnameWithoutLang();
        $html = $this->load->view('admin/tt/_report_rooms', compact('data','periods','rooms','days'), true);
        echo json_encode(['status' => '1', 'html' => $html]);
    }

    public function get_teacher_workload()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $data       = $this->Tt_entry_model->getTeacherWorkload($session_id);
        $html = $this->load->view('admin/tt/_report_workload', compact('data'), true);
        echo json_encode(['status' => '1', 'html' => $html]);
    }

    // =========================================================================
    // SUBJECT COLORS & ABBREVIATIONS
    // =========================================================================

    public function subject_colors()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_colors', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['subjects'] = $this->db->select('subjects.id, subjects.name, subjects.code, subjects.type, subjects.tt_color, subjects.tt_abbr')
            ->from('subjects')
            ->where('subjects.is_active !=', 'no')
            ->order_by('subjects.name', 'ASC')
            ->get()->result();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/subject_colors', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_subject_colors()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_colors', 'can_add')) {
            access_denied();
        }
        $subjects = $this->input->post('subjects'); // array: subject_id => {tt_color, tt_abbr}
        if (empty($subjects)) {
            echo json_encode(['status' => '0', 'message' => 'No data received.']);
            return;
        }
        $this->db->trans_start();
        foreach ($subjects as $subject_id => $val) {
            $this->db->where('id', (int)$subject_id)->update('subjects', [
                'tt_color' => !empty($val['tt_color']) ? $val['tt_color'] : null,
                'tt_abbr'  => !empty($val['tt_abbr'])  ? substr(trim($val['tt_abbr']), 0, 10) : null,
            ]);
        }
        $this->db->trans_complete();
        echo json_encode(['status' => $this->db->trans_status() ? '1' : '0']);
    }

    // =========================================================================
    // CLASS UNAVAILABILITY
    // =========================================================================

    public function class_unavail()
    {
        if (!$this->rbac->hasPrivilege('tt_class_avail', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['classlist']   = $this->class_model->get();
        $data['departments'] = $this->department_model->getDepartmentType();
        $data['periods']     = $this->Tt_period_model->getAllNonBreak($data['session_id']);
        $data['days']        = $this->customlib->getDaysnameWithoutLang();
        $this->load->model('Tt_class_unavail_model');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/class_unavail', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_class_unavail()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');
        $this->load->model('Tt_class_unavail_model');
        $data = $this->Tt_class_unavail_model->getForClassSection($session_id, $class_id, $section_id);
        $map = [];
        foreach ($data as $row) {
            $map[$row->day . '_' . $row->period_id] = true;
        }
        echo json_encode(['status' => '1', 'map' => $map]);
    }

    public function save_class_unavail()
    {
        if (!$this->rbac->hasPrivilege('tt_class_avail', 'can_add')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');
        $slots      = $this->input->post('slots'); // array of {day, period_id, reason}
        $this->load->model('Tt_class_unavail_model');
        $result = $this->Tt_class_unavail_model->saveUnavailability($session_id, $class_id, $section_id, $slots ?: []);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    // =========================================================================
    // ROOM UNAVAILABILITY
    // =========================================================================

    public function room_unavail()
    {
        if (!$this->rbac->hasPrivilege('tt_room_avail', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['rooms']   = $this->Tt_room_model->getAll();
        $data['periods'] = $this->Tt_period_model->getAllNonBreak($data['session_id']);
        $data['days']    = $this->customlib->getDaysnameWithoutLang();
        $this->load->model('Tt_room_unavail_model');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/room_unavail', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_room_unavail()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $room_id    = (int) $this->input->post('room_id');
        $this->load->model('Tt_room_unavail_model');
        $data = $this->Tt_room_unavail_model->getForRoom($session_id, $room_id);
        $map  = [];
        foreach ($data as $row) {
            $map[$row->day . '_' . $row->period_id] = true;
        }
        echo json_encode(['status' => '1', 'map' => $map]);
    }

    public function save_room_unavail()
    {
        if (!$this->rbac->hasPrivilege('tt_room_avail', 'can_add')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $room_id    = (int) $this->input->post('room_id');
        $slots      = $this->input->post('slots') ?: [];
        $this->load->model('Tt_room_unavail_model');
        $result = $this->Tt_room_unavail_model->saveUnavailability($session_id, $room_id, $slots);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    // =========================================================================
    // SUBJECT UNAVAILABILITY
    // =========================================================================

    public function subject_unavail()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_avail', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['subjects'] = $this->db->select('subjects.id, subjects.name, subjects.code, subjects.type')
            ->from('subjects')
            ->where('subjects.is_active !=', 'no')
            ->order_by('subjects.name', 'ASC')
            ->get()->result();
        $data['periods'] = $this->Tt_period_model->getAllNonBreak($data['session_id']);
        $data['days']    = $this->customlib->getDaysnameWithoutLang();
        $this->load->model('Tt_subject_unavail_model');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/subject_unavail', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_subject_unavail()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $subject_id = (int) $this->input->post('subject_id');
        $this->load->model('Tt_subject_unavail_model');
        $data = $this->Tt_subject_unavail_model->getForSubject($session_id, $subject_id);
        $map  = [];
        foreach ($data as $row) {
            $map[$row->day . '_' . $row->period_id] = true;
        }
        echo json_encode(['status' => '1', 'map' => $map]);
    }

    public function save_subject_unavail()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_avail', 'can_add')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $subject_id = (int) $this->input->post('subject_id');
        $slots      = $this->input->post('slots') ?: [];
        $this->load->model('Tt_subject_unavail_model');
        $result = $this->Tt_subject_unavail_model->saveUnavailability($session_id, $subject_id, $slots);
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    // =========================================================================
    // TEST GENERATE (Dry Run)
    // =========================================================================

    public function test_generate()
    {
        if (!$this->rbac->hasPrivilege('tt_generate', 'can_view')) {
            access_denied();
        }
        $session_id         = $this->setting_model->getCurrentSession();
        $class_scope        = json_decode($this->input->post('class_scope'), true);
        $gen_size_raw       = $this->input->post('gen_size');
        $gen_strictness_raw = $this->input->post('gen_strictness');
        $valid_sizes        = ['normal', 'large', 'huge'];
        $valid_strict       = ['relaxed', 'normal', 'strict'];
        $settings    = [
            'allow_saturday'           => (int) $this->input->post('allow_saturday'),
            'max_same_subject_day'     => (int) $this->input->post('max_same_subject_day') ?: 1,
            'spread_evenly'            => (int) $this->input->post('spread_evenly'),
            'fill_free_periods'        => (int) $this->input->post('fill_free_periods'),
            'respect_soft_constraints' => 1,
            'gen_size'                 => in_array($gen_size_raw, $valid_sizes) ? $gen_size_raw : 'normal',
            'gen_strictness'           => in_array($gen_strictness_raw, $valid_strict) ? $gen_strictness_raw : 'normal',
        ];
        $result = $this->Tt_generator_model->testGenerate($session_id, $class_scope, $settings);
        echo json_encode($result);
    }

    // =========================================================================
    // VERIFY CONSTRAINTS
    // =========================================================================

    public function verify_constraints()
    {
        if (!$this->rbac->hasPrivilege('tt_generate', 'can_view')) { access_denied(); }

        $session_id  = $this->setting_model->getCurrentSession();
        $class_scope = json_decode($this->input->post('class_scope'), true);
        $allow_sat   = (int) $this->input->post('allow_saturday');
        $items       = [];
        $overall_ok  = true;

        // 1. Periods configured?
        $period_count = $this->db->where('session_id', $session_id)->where('is_break', 0)->count_all_results('tt_periods');
        $break_count  = $this->db->where('session_id', $session_id)->where('is_break', 1)->count_all_results('tt_periods');
        $items[] = ['ok' => $period_count > 0, 'msg' => "Periods configured: {$period_count} teaching + {$break_count} break slots"];
        if ($period_count === 0) $overall_ok = false;

        // 2. Working days
        $this->load->library('Customlib');
        $days_map    = $this->customlib->getDaysnameWithoutLang();
        $working_days = array_filter(array_keys($days_map), fn($d) => $d !== 'Sunday' && !($d === 'Saturday' && !$allow_sat));
        $day_count   = count($working_days);
        $slot_count  = $day_count * $period_count;
        $items[] = ['ok' => $day_count > 0, 'msg' => "Working days: {$day_count} (" . implode(', ', array_values($working_days)) . ")"];

        // 3. Per class-section checks
        if (!empty($class_scope)) {
            $this->load->model('Tt_subjectload_model');
            $teacher_totals = [];
            foreach ($class_scope as $cs) {
                $loads = $this->Tt_subjectload_model->getForClassSection($session_id, (int)$cs['class_id'], (int)$cs['section_id']);
                $label = $this->db->select('classes.class, sections.section')
                    ->from('classes')->join('sections','sections.class_id=classes.id','left')
                    ->where('classes.id', $cs['class_id'])->where('sections.id', $cs['section_id'])
                    ->get()->row();
                $cls_label = $label ? "{$label->class} {$label->section}" : "Class {$cs['class_id']}";

                $total_ppw   = 0;
                $missing_teacher = 0;
                foreach ($loads as $l) {
                    if ($l->batch_id) continue;
                    $total_ppw += (int)$l->periods_per_week;
                    if (empty($l->staff_id)) $missing_teacher++;
                    if (!empty($l->staff_id)) {
                        $teacher_totals[$l->staff_id] = ($teacher_totals[$l->staff_id] ?? 0) + (int)$l->periods_per_week;
                    }
                }
                $load_ok = ($total_ppw > 0 && $total_ppw <= $slot_count && $missing_teacher === 0);
                if (!$load_ok) $overall_ok = false;
                $msg = "{$cls_label}: {$total_ppw}/{$slot_count} slots assigned";
                if ($missing_teacher > 0) $msg .= " — {$missing_teacher} subject(s) missing teacher";
                if ($total_ppw > $slot_count) $msg .= " — OVERFLOW: more loads than slots";
                $items[] = ['ok' => $load_ok, 'msg' => $msg];
            }

            // 4. Teacher overload check
            if (!empty($teacher_totals)) {
                $this->load->model('Tt_teacher_model');
                $constraints = $this->Tt_teacher_model->getAllConstraintsMap($session_id);
                foreach ($teacher_totals as $tid => $total) {
                    $max_week = isset($constraints[$tid]) ? (int)$constraints[$tid]->max_periods_per_week : $slot_count;
                    if ($total > $max_week) {
                        $overall_ok = false;
                        $t = $this->db->select('name, surname')->where('id', $tid)->get('staff')->row();
                        $tname = $t ? "{$t->name} {$t->surname}" : "Staff #{$tid}";
                        $items[] = ['ok' => false, 'msg' => "Teacher {$tname}: assigned {$total} periods/week but max is {$max_week}"];
                    }
                }
            }
        }

        echo json_encode(['ok' => $overall_ok, 'items' => $items]);
    }

    // =========================================================================
    // AJAX HELPERS
    // =========================================================================

    public function get_sections_by_class()
    {
        $class_id = (int) $this->input->post('class_id');
        $data = $this->section_model->getClassBySection($class_id);
        echo json_encode($data);
    }

    public function get_batches_by_class_section()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');
        $data = $this->Tt_batch_model->getForClassSection($session_id, $class_id, $section_id);
        echo json_encode($data);
    }

    public function get_subjects_by_class_section()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');
        $data = $this->subjectgroup_model->getGroupsubjectsByClassSection($class_id, $section_id, $session_id);
        echo json_encode($data);
    }

    public function get_all_subjects()
    {
        $rows = $this->db->select('id, name, code')->where('is_active', 'yes')->order_by('name', 'ASC')->get('subjects')->result();
        echo json_encode($rows);
    }
}
