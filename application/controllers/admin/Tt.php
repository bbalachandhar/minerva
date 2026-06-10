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
        $settings      = [
            'allow_saturday'        => (int) $this->input->post('allow_saturday'),
            'max_same_subject_day'  => (int) $this->input->post('max_same_subject_day') ?: 1,
            'spread_evenly'         => (int) $this->input->post('spread_evenly'),
            'fill_free_periods'     => (int) $this->input->post('fill_free_periods'),
            'respect_soft_constraints' => 1,
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
}
