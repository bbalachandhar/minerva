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
        $this->load->library('media_storage');
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



    private function _getWorkingDays()
    {
        $days        = $this->customlib->getDaysnameWithoutLang();
        $settings    = $this->setting_model->getSetting();
        $weekend_str = isset($settings->weekend_days) ? (string) $settings->weekend_days : '';
        if ($weekend_str !== '') {
            $dow_map = [0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];
            foreach (array_map('intval', explode(',', $weekend_str)) as $dow) {
                if (isset($dow_map[$dow])) unset($days[$dow_map[$dow]]);
            }
        }
        return $days;
    }

    private function _targetMonday(int $week_offset): int
    {
        $today       = mktime(0, 0, 0, (int)date('n'), (int)date('j'), (int)date('Y'));
        $iso_dow     = (int)date('N', $today);
        $this_monday = $today - ($iso_dow - 1) * 86400;
        return $this_monday + $week_offset * 7 * 86400;
    }

    private function _calcWeekDates(array $days, int $week_offset = 0): array
    {
        $name_dow = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7];
        $monday   = $this->_targetMonday($week_offset);
        $dates    = [];
        foreach (array_keys($days) as $day_name) {
            $offset = ($name_dow[$day_name] ?? 1) - 1;
            $dates[$day_name] = date('d M', $monday + $offset * 86400);
        }
        return $dates;
    }

    private function _calcWeekFullDates(array $days, int $week_offset = 0): array
    {
        $name_dow = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7];
        $monday   = $this->_targetMonday($week_offset);
        $dates    = [];
        foreach (array_keys($days) as $day_name) {
            $offset = ($name_dow[$day_name] ?? 1) - 1;
            $dates[$day_name] = date('Y-m-d', $monday + $offset * 86400);
        }
        return $dates;
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
        $load_classes = $this->db->select('DISTINCT class_id, section_id', false)
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

        // Teacher workload quick-check
        $workload_totals     = $this->_getTeacherWorkloadTotals($session_id);
        $workload_overloaded = 0;
        if (!empty($workload_totals)) {
            $wl_constraints = $this->Tt_teacher_model->getAllConstraintsMap($session_id);
            foreach ($workload_totals as $tid => $ppw) {
                $cap = isset($wl_constraints[$tid]) ? (int)$wl_constraints[$tid]->max_periods_per_week : 36;
                if ($ppw > $cap) $workload_overloaded++;
            }
        }

        $data = [
            'session_id'          => $session_id,
            'period_count'        => $period_count,
            'break_count'         => $break_count,
            'room_count'          => $room_count,
            'batch_count'         => $batch_count,
            'teacher_const_ct'    => $teacher_const_ct,
            'load_class_count'    => $load_class_count,
            'total_load_rows'     => $total_load_rows,
            'missing_teacher'     => $missing_teacher,
            'colored_subjects'    => $colored_subjects,
            'last_gen'            => $last_gen,
            'last_confirmed'      => $last_confirmed,
            'workload_teachers'   => count($workload_totals),
            'workload_overloaded' => $workload_overloaded,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/dashboard', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // LESSON BROWSER
    // =========================================================================
    // INSTRUCTIONS
    // =========================================================================

    public function instructions()
    {
        $this->_setMenu();
        $data = $this->_baseData();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/instructions', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================

    public function lesson_browser()
    {
        if (!$this->rbac->hasPrivilege('tt_lesson_browser', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $session_id = $this->setting_model->getCurrentSession();

        $data = $this->_baseData();
        $data['departments'] = $this->department_model->getDepartmentsForSession($data['session_id']);
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

        // Periods + working days for the Fixed Slot picker
        $data['periods'] = $this->Tt_period_model->getAllNonBreak($session_id);
        $all_days     = $this->customlib->getDaysnameWithoutLang();
        $sch_settings = $this->setting_model->getSetting();
        $weekend_str  = isset($sch_settings->weekend_days) ? (string) $sch_settings->weekend_days : '';
        if ($weekend_str !== '') {
            $dow_map = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
            foreach (array_map('intval', explode(',', $weekend_str)) as $dow) {
                if (isset($dow_map[$dow])) unset($all_days[$dow_map[$dow]]);
            }
        }
        $data['days'] = $all_days;

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
        $teacher_ids_raw = $this->input->post('teacher_ids') ?: [];
        if (!is_array($teacher_ids_raw)) $teacher_ids_raw = [$teacher_ids_raw];
        $teacher_ids = array_values(array_filter(array_map('intval', $teacher_ids_raw)));

        // Fixed Slot(s): sanitize to [{placement:int, day:string, period_ids:[int,...]}, ...]
        $fixed_slots_raw = json_decode((string) $this->input->post('fixed_slots_json'), true);
        $fixed_slots     = [];
        if (is_array($fixed_slots_raw)) {
            foreach ($fixed_slots_raw as $fs) {
                if (empty($fs['day']) || empty($fs['period_ids']) || !is_array($fs['period_ids'])) continue;
                $fixed_slots[] = [
                    'placement'  => (int) ($fs['placement'] ?? 0),
                    'day'        => (string) $fs['day'],
                    'period_ids' => array_values(array_map('intval', $fs['period_ids'])),
                ];
            }
        }

        $data = [
            'id'                    => (int) $this->input->post('id'),
            'name'                  => trim($this->input->post('name')),
            'subject_id'            => (int) $this->input->post('subject_id'),
            'room_id'               => $this->input->post('room_id') ?: null,
            'periods_per_week'      => (int) $this->input->post('periods_per_week')    ?: 1,
            'consecutive_periods'   => (int) $this->input->post('consecutive_periods') ?: 1,
            'max_per_day'           => (int) $this->input->post('max_per_day')         ?: 1,
            'distribute_evenly'     => $this->input->post('distribute_evenly') ? 1 : 0,
            'priority'              => (int) $this->input->post('priority')            ?: 5,
            'notes'                 => $this->input->post('notes'),
            'all_teachers_required' => $this->input->post('all_teachers_required') ? 1 : 0,
            'fixed_slots'           => !empty($fixed_slots) ? json_encode($fixed_slots) : null,
        ];
        if (empty($data['name']) || empty($data['subject_id'])) {
            echo json_encode(['status' => '0', 'message' => 'Name and subject are required.']); return;
        }

        // VALIDATION 1: Each class must have enough slots for this joint
        $total_slots = $this->_getTotalSlots($session_id);
        $ppw = $data['periods_per_week'];
        $existing_id = $data['id'];
        foreach ($classes_raw as $cs) {
            $cid = (int)$cs['class_id']; $sid = (int)$cs['section_id'];
            $current_demand = $this->_getClassTotalPPW($session_id, $cid, $sid);
            if ($existing_id > 0) {
                $old_ppw = $this->db->select('periods_per_week')
                    ->where('id', $existing_id)->get('tt_joint_lessons')->row();
                if ($old_ppw) {
                    $old_in_class = $this->db->where('joint_lesson_id', $existing_id)
                        ->where('class_id', $cid)->where('section_id', $sid)
                        ->count_all_results('tt_joint_lesson_classes');
                    if ($old_in_class > 0) $current_demand -= (int)$old_ppw->periods_per_week;
                }
            }
            $new_demand = $current_demand + $ppw;
            if ($new_demand > $total_slots) {
                $cls_name = $this->_getClassName($cid, $sid);
                echo json_encode([
                    'status' => '0',
                    'message' => "{$cls_name}: Adding this joint lesson ({$ppw}ppw) would make total "
                        . "{$new_demand} periods, exceeding {$total_slots} available slots. "
                        . "Remove this class from the joint or reduce other subject loads."
                ]);
                return;
            }
        }

        // VALIDATION 2: Each teacher must have capacity
        foreach ($teacher_ids as $tid) {
            $current_load = $this->_getTeacherWorkload($session_id, $tid);
            if ($existing_id > 0) {
                $was_teacher = $this->db->where('joint_lesson_id', $existing_id)
                    ->where('staff_id', $tid)->count_all_results('tt_joint_lesson_teachers');
                if ($was_teacher > 0) {
                    $old_ppw_row = $this->db->select('periods_per_week')
                        ->where('id', $existing_id)->get('tt_joint_lessons')->row();
                    if ($old_ppw_row) $current_load -= (int)$old_ppw_row->periods_per_week;
                }
            }
            $new_load = $current_load + $ppw;
            $tc = $this->_getTeacherConstraint($session_id, $tid);
            if ($new_load > $tc['max_per_week']) {
                $tname = $this->_getTeacherName($tid);
                echo json_encode([
                    'status' => '0',
                    'message' => "{$tname}: Adding this joint ({$ppw}ppw) would make total {$new_load}/week, "
                        . "exceeding max {$tc['max_per_week']}. "
                        . "Increase their Max Per Week or remove them from this joint."
                ]);
                return;
            }
        }

        $id = $this->Tt_joint_model->save($session_id, $data, $classes_raw, $teacher_ids);
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
        $data['departments'] = $this->department_model->getDepartmentsForSession($data['session_id']);
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
        $data['departments'] = $this->department_model->getDepartmentsForSession($data['session_id']);
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
        $data['departments'] = $this->department_model->getDepartmentsForSession($data['session_id']);
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

        $loads    = $this->Tt_subjectload_model->getForClassSection($session_id, $class_id, $section_id);
        $staff    = $this->staff_model->getStaffbyrole(2);
        $batches  = $this->Tt_batch_model->getForClassSection($session_id, $class_id, $section_id);
        $rooms    = $this->Tt_room_model->getAll();

        $load_map = [];
        foreach ($loads as $l) {
            $key = $l->subject_group_subject_id . '_' . ($l->batch_id ?: '0');
            $load_map[$key] = $l;
        }

        // Only show subjects that have an active load row for this specific section;
        // this prevents deleted subjects from reappearing via the class-wide subject group.
        $subjects = $this->Tt_subjectload_model->getSubjectsForClass($session_id, $class_id);
        $subjects = array_values(array_filter($subjects, function ($sub) use ($load_map) {
            return isset($load_map[$sub->subject_group_subject_id . '_0'])
                || isset($load_map[$sub->subject_group_subject_id . '_' . ($sub->batch_id ?? '0')]);
        }));

        // Available picker: subjects not yet assigned to this section's load
        $loaded_subject_ids = array_unique(array_map(fn($l) => (int)$l->subject_id, $loads));
        $all_subjects       = $this->db->where('is_active', 'yes')->order_by('name')->get('subjects')->result();
        $available_subjects = array_values(array_filter($all_subjects, fn($s) => !in_array((int)$s->id, $loaded_subject_ids)));

        $data = [
            'subjects'           => $subjects,
            'load_map'           => $load_map,
            'staff'              => $staff,
            'batches'            => $batches,
            'rooms'              => $rooms,
            'class_id'           => $class_id,
            'section_id'         => $section_id,
            'available_subjects' => $available_subjects,
        ];
        $html = $this->load->view('admin/tt/_subject_load_rows', $data, true);
        echo json_encode(['status' => '1', 'html' => $html]);
    }

    public function add_subjects_to_load()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_load', 'can_add')) {
            echo json_encode(['status' => '0', 'error' => 'Access denied']); return;
        }
        $session_id  = $this->setting_model->getCurrentSession();
        $class_id    = (int) $this->input->post('class_id');
        $section_id  = (int) $this->input->post('section_id');
        $subject_ids = $this->input->post('subject_ids');

        if (empty($subject_ids) || !is_array($subject_ids)) {
            echo json_encode(['status' => '0', 'error' => 'No subjects selected']); return;
        }

        // Default priority by subject type: practical/integrated subjects need
        // labs/specific rooms and are harder to schedule, so they go first.
        $subject_types = [];
        foreach ($this->db->select('id, type')->where_in('id', array_map('intval', $subject_ids))->get('subjects')->result() as $tr) {
            $subject_types[(int) $tr->id] = $tr->type;
        }

        // Find or create subject_group for this class+session
        $sg = $this->db->where('class_id', $class_id)->where('session_id', $session_id)
                        ->get('subject_groups')->row();
        if (!$sg) {
            $cls = $this->db->where('id', $class_id)->get('classes')->row();
            $this->db->insert('subject_groups', [
                'name'       => ($cls ? $cls->class : 'Class '.$class_id).' – '.$session_id,
                'session_id' => $session_id,
                'class_id'   => $class_id,
            ]);
            $sg_id = $this->db->insert_id();
        } else {
            $sg_id = $sg->id;
        }

        foreach ($subject_ids as $sid) {
            $sid = (int) $sid;
            // Find or create subject_group_subjects entry
            $sgs = $this->db->where('subject_group_id', $sg_id)
                             ->where('subject_id', $sid)
                             ->where('session_id', $session_id)
                             ->get('subject_group_subjects')->row();
            if (!$sgs) {
                $this->db->insert('subject_group_subjects', [
                    'subject_group_id' => $sg_id,
                    'session_id'       => $session_id,
                    'subject_id'       => $sid,
                ]);
                $sgs_id = $this->db->insert_id();
            } else {
                $sgs_id = $sgs->id;
            }

            // Insert tt_subject_load if not already there
            $exists = $this->db->where('session_id', $session_id)
                                ->where('class_id', $class_id)
                                ->where('section_id', $section_id)
                                ->where('subject_group_subject_id', $sgs_id)
                                ->where('batch_id IS NULL', null, false)
                                ->count_all_results('tt_subject_load');
            if (!$exists) {
                $default_priority = in_array($subject_types[$sid] ?? '', ['practical', 'integrated']) ? 8 : 5;
                $this->db->insert('tt_subject_load', [
                    'session_id'               => $session_id,
                    'class_id'                 => $class_id,
                    'section_id'               => $section_id,
                    'subject_group_id'         => $sg_id,
                    'subject_group_subject_id' => $sgs_id,
                    'staff_id'                 => 0,
                    'periods_per_week'         => 4,
                    'consecutive_periods'      => 1,
                    'preferred_room_type'      => 'any',
                    'priority'                 => $default_priority,
                    'max_per_day'              => 2,
                    'distribute_evenly'        => 1,
                    'min_per_day'              => 0,
                ]);
            }
        }
        echo json_encode(['status' => '1']);
    }

    public function delete_subject_load_row()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_load', 'can_delete')) {
            echo json_encode(['status' => '0', 'error' => 'Access denied']); return;
        }
        $id = (int) $this->input->post('id');

        $exists = $this->db->where('id', $id)->count_all_results('tt_subject_load');
        if (!$exists) {
            echo json_encode(['status' => '0', 'error' => 'Not found']); return;
        }

        $this->Tt_subjectload_model->delete($id);

        echo json_encode(['status' => '1']);
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
        $rows = $this->input->post('rows');

        if (empty($rows)) {
            echo json_encode(['status' => '0', 'message' => 'No data received.']);
            return;
        }

        // Strip rows that are managed by a joint lesson (view marks them with _skip_joint)
        foreach ($rows as $key => $row) {
            if (!empty($row['_skip_joint'])) unset($rows[$key]);
        }

        // VALIDATION 1: Class capacity — total periods must fit in available slots
        $total_slots = $this->_getTotalSlots($session_id);
        $new_regular_ppw = 0;
        foreach ($rows as $row) {
            $new_regular_ppw += (int)($row['periods_per_week'] ?? 0);
        }
        $joint_ppw = $this->db->query(
            "SELECT COALESCE(SUM(jl.periods_per_week),0) as t FROM tt_joint_lessons jl
             JOIN tt_joint_lesson_classes jlc ON jlc.joint_lesson_id=jl.id
             WHERE jl.session_id=? AND jlc.class_id=? AND jlc.section_id=?",
            [$session_id, $class_id, $section_id])->row()->t ?? 0;
        $total_demand = $new_regular_ppw + (int)$joint_ppw;
        if ($total_demand > $total_slots) {
            $cls_name = $this->_getClassName($class_id, $section_id);
            echo json_encode([
                'status' => '0',
                'message' => "{$cls_name}: Total periods ({$total_demand}) exceeds available slots ({$total_slots}). "
                    . "You have {$new_regular_ppw} regular + {$joint_ppw} joint lesson periods. "
                    . "Please reduce by " . ($total_demand - $total_slots) . " period(s)."
            ]);
            return;
        }

        // VALIDATION 2: Teacher capacity — each teacher's total load must fit
        foreach ($rows as $sgs_key => $row) {
            $teacher_ids = [];
            if (!empty($row['teacher_ids']) && is_array($row['teacher_ids'])) {
                $teacher_ids = array_map('intval', $row['teacher_ids']);
            }
            $ppw = (int)($row['periods_per_week'] ?? 0);
            $load_id = (int)($row['load_id'] ?? 0);
            foreach ($teacher_ids as $tid) {
                if (!$tid) continue;
                $current_total = $this->_getTeacherWorkload($session_id, $tid);
                $old_ppw = 0;
                if ($load_id > 0) {
                    $old = $this->db->where('id', $load_id)->get('tt_subject_load')->row();
                    if ($old) $old_ppw = (int)$old->periods_per_week;
                }
                $new_total = $current_total - $old_ppw + $ppw;
                $tc = $this->_getTeacherConstraint($session_id, $tid);
                if ($new_total > $tc['max_per_week']) {
                    $tname = $this->_getTeacherName($tid);
                    echo json_encode([
                        'status' => '0',
                        'message' => "{$tname}: Total load would be {$new_total} periods/week, "
                            . "but max allowed is {$tc['max_per_week']}. "
                            . "Either reduce this teacher's subjects or increase their Max Per Week in Teacher Constraints."
                    ]);
                    return;
                }
            }
        }

        $result = $this->Tt_subjectload_model->saveRows($session_id, $class_id, $section_id, $rows);

        $warning = null;
        if ($result) {
            $cls_name = $this->_getClassName($class_id, $section_id);
            if ($total_demand == $total_slots) {
                $warning = "{$cls_name}: This class uses ALL {$total_slots} slots ({$new_regular_ppw} regular + {$joint_ppw} joint). "
                    . "Zero free periods — every subject must fit perfectly for 100% timetable generation.";
            }
            // Check tight teachers
            $tight_teachers = [];
            foreach ($rows as $row) {
                $tids = (!empty($row['teacher_ids']) && is_array($row['teacher_ids'])) ? array_map('intval', $row['teacher_ids']) : [];
                foreach ($tids as $tid) {
                    if (!$tid || isset($tight_teachers[$tid])) continue;
                    $load = $this->_getTeacherWorkload($session_id, $tid);
                    $tc = $this->_getTeacherConstraint($session_id, $tid);
                    $slack = $tc['max_per_week'] - $load;
                    if ($slack <= 4 && $load >= 28) {
                        $tight_teachers[$tid] = $this->_getTeacherName($tid) . " ({$load}/{$tc['max_per_week']}ppw, {$slack} slack)";
                    }
                }
            }
            if ($tight_teachers) {
                $warning = ($warning ? $warning . "\n\n" : '') . "Near-capacity teachers: " . implode(', ', $tight_teachers)
                    . ". These teachers may cause scheduling conflicts.";
            }
        }

        $resp = ['status' => $result ? '1' : '0'];
        if ($warning) $resp['warning'] = $warning;
        echo json_encode($resp);
    }

    public function diagnose_joint_lessons()
    {
        if (!$this->rbac->hasPrivilege('tt_generate', 'can_view')) { access_denied(); }

        $session_id = $this->setting_model->getCurrentSession();
        $this->load->model('Tt_joint_model');
        $this->load->model('Tt_teacher_model');
        $this->load->library('Customlib');

        $joints = $this->Tt_joint_model->getAllForGeneration($session_id);
        $unavail_map = $this->Tt_teacher_model->getUnavailabilityMap($session_id);
        $constraints = $this->Tt_teacher_model->getAllConstraintsMap($session_id);

        // Load class unavailability
        $class_unavail = [];
        foreach ($this->db->where('session_id', $session_id)->get('tt_class_unavail')->result() as $r) {
            $class_unavail[(int)$r->class_id][(int)$r->section_id][$r->day][(int)$r->period_id] = true;
        }

        // Load locked entries
        $locked = [];
        foreach ($this->db->where('session_id', $session_id)->where('is_locked', 1)->get('tt_entries')->result() as $e) {
            $locked[(int)$e->class_id][(int)$e->section_id][$e->day][(int)$e->period_id] = true;
        }

        // Load periods and working days
        $periods = $this->db->where('session_id', $session_id)->where('is_break', 0)
            ->order_by('start_time', 'ASC')->get('tt_periods')->result();
        $days_map = $this->customlib->getDaysnameWithoutLang();
        $working_days = array_filter(array_keys($days_map), fn($d) => $d !== 'Sunday');

        $report = [];
        foreach ($joints as $jl) {
            $classes = $jl->classes ?? [];
            if (count($classes) < 2) continue;
            $t_ids = $jl->teacher_ids ?? [];
            $consec = (int)($jl->consecutive_periods ?? 1);
            $ppw = (int)($jl->periods_per_week ?? 1);
            $class_labels = implode(' + ', array_map(fn($cs) => "C{$cs->class_id}/S{$cs->section_id}", $classes));

            $slot_analysis = [];
            $total_slots = 0; $free_slots = 0;
            foreach ($working_days as $day) {
                foreach ($periods as $p) {
                    $pid = (int)$p->id;
                    $total_slots++;
                    $blockers = [];

                    foreach ($classes as $cs) {
                        $cid = (int)$cs->class_id; $sid = (int)$cs->section_id;
                        if (!empty($class_unavail[$cid][$sid][$day][$pid])) {
                            $blockers[] = "C{$cid}/S{$sid} class_unavail";
                        }
                        if (!empty($locked[$cid][$sid][$day][$pid])) {
                            $blockers[] = "C{$cid}/S{$sid} locked_entry";
                        }
                    }
                    foreach ($t_ids as $tid) {
                        if (!empty($unavail_map[$tid][$day][$pid])) {
                            $t = $this->db->select('name,surname')->where('id', $tid)->get('staff')->row();
                            $blockers[] = "Teacher " . ($t ? "{$t->name} {$t->surname}" : $tid) . " unavailable";
                        }
                    }

                    if (empty($blockers)) {
                        $free_slots++;
                    } else {
                        $slot_analysis[] = "{$day} P{$pid} ({$p->name}): " . implode(', ', $blockers);
                    }
                }
            }

            $placements_needed = ($consec > 1) ? (int)ceil($ppw / $consec) : $ppw;
            $report[] = [
                'name' => $jl->name,
                'classes' => $class_labels,
                'sections' => count($classes),
                'ppw' => $ppw,
                'consecutive' => $consec,
                'placements_needed' => $placements_needed,
                'total_slots' => $total_slots,
                'free_slots' => $free_slots,
                'blocked_slots' => $slot_analysis,
                'feasible' => $free_slots >= $placements_needed,
            ];
        }

        echo json_encode(['status' => '1', 'joints' => $report], JSON_PRETTY_PRINT);
    }

    public function get_teacher_capacity_data()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_load', 'can_view')) { access_denied(); }

        $session_id = $this->setting_model->getCurrentSession();
        $this->load->model('Tt_teacher_model');
        $constraints = $this->Tt_teacher_model->getAllConstraintsMap($session_id);
        $unavail_map = $this->Tt_teacher_model->getUnavailabilityMap($session_id);
        $totals      = $this->_getTeacherWorkloadTotals($session_id);

        $this->load->library('Customlib');
        $days_map     = $this->customlib->getDaysnameWithoutLang();
        $working_days = array_filter(array_keys($days_map), fn($d) => $d !== 'Sunday' && $d !== 'Saturday');
        $day_count    = count($working_days);
        $period_count = $this->db->where('session_id', $session_id)->where('is_break', 0)->count_all_results('tt_periods');
        $slot_count   = $day_count * $period_count;

        $data = [];
        $staff_rows = $this->db->select('staff.id, staff.name, staff.surname')
            ->from('staff')
            ->join('staff_roles', 'staff_roles.staff_id = staff.id')
            ->where('staff_roles.role_id', 2)
            ->group_by('staff.id')
            ->get()->result();
        foreach ($staff_rows as $sr) {
            $tid = (int)$sr->id;
            $max_week = isset($constraints[$tid]) ? (int)$constraints[$tid]->max_periods_per_week : 36;
            $max_day  = isset($constraints[$tid]) ? (int)$constraints[$tid]->max_periods_per_day : 6;
            $unavail  = 0;
            if (isset($unavail_map[$tid])) {
                foreach ($unavail_map[$tid] as $periods) $unavail += count($periods);
            }
            $data[$tid] = [
                'name'       => "{$sr->name} {$sr->surname}",
                'total_ppw'  => $totals[$tid] ?? 0,
                'max_week'   => $max_week,
                'max_day'    => $max_day,
                'unavail'    => $unavail,
                'avail_slots'=> $slot_count - $unavail,
                'slot_count' => $slot_count,
                'day_count'  => $day_count,
            ];
        }

        echo json_encode(['status' => '1', 'data' => $data]);
    }

    // =========================================================================
    // TEACHER WORKLOAD DASHBOARD (pre-generation planning)
    // =========================================================================

    /**
     * Total committed periods/week per teacher.
     *
     * A joint lesson gets one tt_subject_load row synced per participating
     * class-section, each carrying the lesson's full periods_per_week — so
     * summing tt_subject_load directly overcounts a teacher's real time by
     * however many sections share that single weekly slot. Joint contribution
     * is summed separately, once per joint lesson, from tt_joint_lessons.
     */
    private function _getTeacherWorkloadTotals($session_id)
    {
        $totals = [];
        foreach ($this->db->query("
            SELECT slt.staff_id, SUM(sl.periods_per_week) AS ppw
            FROM tt_subject_load sl
            JOIN tt_subject_load_teachers slt ON slt.subject_load_id = sl.id
            WHERE sl.session_id = ? AND sl.joint_lesson_id IS NULL
              AND EXISTS (SELECT 1 FROM subject_group_subjects sgs WHERE sgs.id = sl.subject_group_subject_id)
            GROUP BY slt.staff_id
        ", [$session_id])->result() as $r) {
            $totals[(int) $r->staff_id] = (int) $r->ppw;
        }
        foreach ($this->db->query("
            SELECT jlt.staff_id, SUM(jl.periods_per_week) AS ppw
            FROM tt_joint_lessons jl
            JOIN tt_joint_lesson_teachers jlt ON jlt.joint_lesson_id = jl.id
            WHERE jl.session_id = ?
            GROUP BY jlt.staff_id
        ", [$session_id])->result() as $r) {
            $tid = (int) $r->staff_id;
            $totals[$tid] = ($totals[$tid] ?? 0) + (int) $r->ppw;
        }
        return $totals;
    }

    public function teacher_workload_dashboard()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_load', 'can_view')) {
            access_denied();
        }
        $this->_setMenu();
        $data = $this->_baseData();
        $data['staff_list'] = $this->staff_model->getStaffbyrole(2);
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/workload_dashboard', $data);
        $this->load->view('layout/footer', $data);
    }

    public function get_pregeneration_workload()
    {
        $session_id  = $this->setting_model->getCurrentSession();
        $constraints = $this->Tt_teacher_model->getAllConstraintsMap($session_id);
        $default_cap = 36;

        $totals = $this->_getTeacherWorkloadTotals($session_id);
        if (empty($totals)) {
            echo json_encode(['status' => '1', 'data' => []]);
            return;
        }

        $staff_rows = $this->db->select('id, name, surname, employee_id')
            ->where_in('id', array_keys($totals))
            ->get('staff')->result();

        $details = $this->db->query("
            SELECT sl.id AS load_id, sl.periods_per_week, sl.joint_lesson_id,
                   sl.class_id, sl.section_id,
                   sub.name AS subject_name, sub.code AS subject_code,
                   c.class AS class_name, sec.section AS section_name,
                   slt.staff_id AS teacher_id
            FROM tt_subject_load sl
            JOIN tt_subject_load_teachers slt ON slt.subject_load_id = sl.id
            JOIN subject_group_subjects sgs ON sgs.id = sl.subject_group_subject_id
            JOIN subjects sub ON sub.id = sgs.subject_id
            JOIN classes c ON c.id = sl.class_id
            JOIN sections sec ON sec.id = sl.section_id
            WHERE sl.session_id = ?
            ORDER BY slt.staff_id, sub.name ASC
        ", [$session_id])->result();

        $detail_map = [];
        foreach ($details as $d) {
            $detail_map[(int)$d->teacher_id][] = $d;
        }

        $result = [];
        foreach ($staff_rows as $t) {
            $tid = (int) $t->id;
            $cap = isset($constraints[$tid])
                ? (int)$constraints[$tid]->max_periods_per_week
                : $default_cap;
            $assignments = [];
            foreach ($detail_map[$tid] ?? [] as $a) {
                $assignments[] = [
                    'load_id'  => (int)$a->load_id,
                    'class'    => $a->class_name . ' ' . $a->section_name,
                    'subject'  => $a->subject_name . ($a->subject_code ? " ({$a->subject_code})" : ''),
                    'ppw'      => (int)$a->periods_per_week,
                    'is_joint' => !empty($a->joint_lesson_id),
                ];
            }
            $result[] = [
                'staff_id'    => $tid,
                'name'        => trim($t->name . ' ' . ($t->surname ?? '')),
                'employee_id' => $t->employee_id,
                'total_ppw'   => $totals[$tid] ?? 0,
                'cap'         => $cap,
                'assignments' => $assignments,
            ];
        }

        usort($result, fn($a, $b) => $b['total_ppw'] <=> $a['total_ppw']);

        echo json_encode(['status' => '1', 'data' => $result]);
    }

    public function reassign_subject_teacher()
    {
        if (!$this->rbac->hasPrivilege('tt_subject_load', 'can_add')) {
            echo json_encode(['status' => '0', 'message' => 'Access denied']);
            return;
        }
        $session_id  = $this->setting_model->getCurrentSession();
        $load_id     = (int)$this->input->post('load_id');
        $new_teacher = (int)$this->input->post('new_teacher_id');
        $old_teacher = (int)$this->input->post('old_teacher_id');

        if (!$load_id || !$new_teacher || !$old_teacher) {
            echo json_encode(['status' => '0', 'message' => 'Invalid input']);
            return;
        }

        $row = $this->db->where('id', $load_id)->where('session_id', $session_id)
                        ->where('joint_lesson_id IS NULL', null, false)
                        ->get('tt_subject_load')->row();
        if (!$row) {
            echo json_encode(['status' => '0', 'message' => 'Record not found or is a joint lesson']);
            return;
        }

        $this->db->trans_start();
        if ((int)$row->staff_id === $old_teacher) {
            $this->db->where('id', $load_id)->update('tt_subject_load', ['staff_id' => $new_teacher]);
        }
        $this->db->where('subject_load_id', $load_id)->where('staff_id', $old_teacher)
                 ->update('tt_subject_load_teachers', ['staff_id' => $new_teacher]);
        $this->db->trans_complete();

        echo json_encode(['status' => $this->db->trans_status() ? '1' : '0']);
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
            'avoid_first_period'          => (int) $this->input->post('avoid_first_period'),
            'avoid_last_period'           => (int) $this->input->post('avoid_last_period'),
            'exclude_from_substitution'   => (int) $this->input->post('exclude_from_substitution'),
            'exclude_from_timetable'      => (int) $this->input->post('exclude_from_timetable'),
            'max_consecutive_periods'     => max(0, (int) $this->input->post('max_consecutive_periods')),
            'min_break_after_consec'      => max(1, (int) $this->input->post('min_break_after_consec') ?: 1),
        ];
        if ($id > 0) {
            $data['id'] = $id;
        }

        // VALIDATION: Current load must fit within new constraints
        $staff_id = $data['staff_id'];
        $current_load = $this->_getTeacherWorkload($session_id, $staff_id);
        $tname = $this->_getTeacherName($staff_id);

        if ($data['max_periods_per_week'] > 0 && $current_load > $data['max_periods_per_week']) {
            echo json_encode([
                'status' => '0',
                'message' => "{$tname} currently has {$current_load} periods/week assigned. "
                    . "Cannot set max to {$data['max_periods_per_week']}. "
                    . "Either set max to at least {$current_load}, or reduce this teacher's subject load first."
            ]);
            return;
        }
        $day_count = $this->_getWorkingDayCount();
        if ($data['max_periods_per_day'] > 0 && $current_load > $data['max_periods_per_day'] * $day_count) {
            $max_possible = $data['max_periods_per_day'] * $day_count;
            echo json_encode([
                'status' => '0',
                'message' => "{$tname} has {$current_load} periods/week, but max {$data['max_periods_per_day']}/day × "
                    . "{$day_count} days = {$max_possible} possible. "
                    . "Either increase Max Per Day to " . ceil($current_load / $day_count)
                    . " or reduce this teacher's load."
            ]);
            return;
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

        $all_days     = $this->customlib->getDaysnameWithoutLang();
        $sch_settings = $this->setting_model->getSetting();
        $weekend_str  = isset($sch_settings->weekend_days) ? (string) $sch_settings->weekend_days : '';
        if ($weekend_str !== '') {
            $dow_map = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
            foreach (array_map('intval', explode(',', $weekend_str)) as $dow) {
                if (isset($dow_map[$dow])) unset($all_days[$dow_map[$dow]]);
            }
        }
        $data['days'] = $all_days;

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

        // VALIDATION: Teacher must have enough available slots for their load
        $total_slots = $this->_getTotalSlots($session_id);
        $blocked_count = is_array($slots) ? count($slots) : 0;
        $available = $total_slots - $blocked_count;
        $current_load = $this->_getTeacherWorkload($session_id, $staff_id);

        if ($current_load > $available) {
            $tname = $this->_getTeacherName($staff_id);
            echo json_encode([
                'status' => '0',
                'message' => "{$tname} has {$current_load} periods/week assigned, "
                    . "but blocking {$blocked_count} slots leaves only {$available} available. "
                    . "Remove some unavailable slots or reduce this teacher's load by "
                    . ($current_load - $available) . " period(s) first."
            ]);
            return;
        }

        // Also check daily feasibility
        $tc = $this->_getTeacherConstraint($session_id, $staff_id);
        $max_day = $tc['max_per_day'] ?: 6;
        $day_count = $this->_getWorkingDayCount();
        $blocked_per_day = [];
        $period_count = $this->db->where('session_id', $session_id)->where('is_break', 0)
            ->count_all_results('tt_periods');
        if (is_array($slots)) {
            foreach ($slots as $sl) {
                $d = $sl['day'] ?? '';
                $blocked_per_day[$d] = ($blocked_per_day[$d] ?? 0) + 1;
            }
        }
        $fully_blocked_days = 0;
        foreach ($blocked_per_day as $d => $cnt) {
            if ($cnt >= $period_count) $fully_blocked_days++;
        }
        $effective_days = $day_count - $fully_blocked_days;
        $max_possible = $max_day * $effective_days;
        if ($current_load > $max_possible && $effective_days < $day_count) {
            $tname = $this->_getTeacherName($staff_id);
            echo json_encode([
                'status' => '0',
                'message' => "{$tname} has {$current_load} periods/week but can only fit "
                    . "{$max_day}/day × {$effective_days} available days = {$max_possible}. "
                    . "Don't block entire days, or reduce this teacher's load."
            ]);
            return;
        }

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
        $data['departments'] = $this->department_model->getDepartmentsForSession($data['session_id']);
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
        $class_scope   = json_decode($this->input->post('class_scope'), true);
        $time_limit    = max(60, min(600, (int) $this->input->post('time_limit') ?: 180));
        set_time_limit($time_limit + 120);
        $settings      = [
            'allow_saturday'           => (int) $this->input->post('allow_saturday'),
            'max_same_subject_day'     => (int) $this->input->post('max_same_subject_day') ?: 1,
            'spread_evenly'            => (int) $this->input->post('spread_evenly'),
            'fill_free_periods'        => (int) $this->input->post('fill_free_periods'),
            'respect_soft_constraints' => 1,
            'time_limit'               => $time_limit,
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

        // Enrich no_slot conflicts with live teacher booking diagnostics
        $no_slot_staff_ids = [];
        foreach ($data['conflicts'] as $c) {
            $is_no_slot = ($c['type'] ?? '') === 'no_slot'
                || (isset($c['placement']) && $c['placement'] !== 'On1' && strpos($c['reason'] ?? '', 'slot') !== false);
            if ($is_no_slot && !empty($c['staff_id'])) {
                $no_slot_staff_ids[] = (int)$c['staff_id'];
            }
        }
        $no_slot_staff_ids = array_unique($no_slot_staff_ids);

        $teacher_diagnostics = [];
        if (!empty($no_slot_staff_ids)) {
            $sid = $data['session_id'];
            // Real total committed periods/week — NOT summed from the query
            // below, which lists one row per participating section of a
            // joint lesson and would overcount a shared teacher's true time.
            $correct_totals = $this->_getTeacherWorkloadTotals($sid);

            // All subject loads for these teachers in this session
            $load_rows = $this->db->select('tslt.staff_id, c.class as class_name, sec.section as section_name, s.name as subject_name, tsl.periods_per_week, tsl.class_id, tsl.section_id')
                ->from('tt_subject_load tsl')
                ->join('tt_subject_load_teachers tslt', 'tslt.subject_load_id = tsl.id')
                ->join('classes c', 'c.id = tsl.class_id')
                ->join('sections sec', 'sec.id = tsl.section_id')
                ->join('subject_group_subjects sgs', 'sgs.id = tsl.subject_group_subject_id')
                ->join('subjects s', 's.id = sgs.subject_id')
                ->where('tsl.session_id', $sid)
                ->where_in('tslt.staff_id', $no_slot_staff_ids)
                ->get()->result();

            foreach ($load_rows as $row) {
                $tid = (int)$row->staff_id;
                $teacher_diagnostics[$tid]['assignments'][] = $row;
            }
            foreach ($no_slot_staff_ids as $tid) {
                $teacher_diagnostics[$tid]['total_ppw'] = $correct_totals[$tid] ?? 0;
            }

            // Teacher name and constraint
            $staff_rows = $this->db->select('staff.id, staff.name, staff.surname, tc.max_periods_per_week')
                ->from('staff')
                ->join('tt_teacher_constraints tc', "tc.staff_id = staff.id AND tc.session_id = {$sid}", 'left')
                ->where_in('staff.id', $no_slot_staff_ids)
                ->get()->result();
            foreach ($staff_rows as $sr) {
                $teacher_diagnostics[$sr->id]['name']    = $sr->name . ' ' . $sr->surname;
                $teacher_diagnostics[$sr->id]['max_ppw'] = $sr->max_periods_per_week;
            }
        }
        $data['teacher_diagnostics'] = $teacher_diagnostics;

        $all_days    = $this->customlib->getDaysnameWithoutLang();
        $sch_settings = $this->setting_model->getSetting();
        $weekend_str  = isset($sch_settings->weekend_days) ? (string) $sch_settings->weekend_days : '';
        if ($weekend_str !== '') {
            $dow_map = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
            foreach (array_map('intval', explode(',', $weekend_str)) as $dow) {
                if (isset($dow_map[$dow])) unset($all_days[$dow_map[$dow]]);
            }
        }
        $data['days'] = $all_days;
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
            $sync = $this->_doSyncToAttendance();
            $this->session->set_flashdata('msg',
                '<div class="alert alert-success text-center">Timetable confirmed and saved successfully. ' .
                'Attendance system synced: ' . $sync['inserted'] . ' new, ' . $sync['updated'] . ' updated.</div>');
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
    // SYNC TO ATTENDANCE SYSTEM
    // =========================================================================

    /**
     * Sync tt_entries → subject_timetable so teachers can mark period-wise attendance.
     * Uses tt_entry_id as a stable link: UPDATE if the row already exists, INSERT otherwise.
     * Never deletes existing subject_timetable rows (preserves FK → student_subject_attendances).
     */
    public function sync_to_attendance()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_add')) {
            echo json_encode(['status' => '0', 'msg' => 'Access denied']); return;
        }
        $result = $this->_doSyncToAttendance();
        echo json_encode($result);
    }

    private function _doSyncToAttendance()
    {
        $session_id = $this->setting_model->getCurrentSession();

        $entries = $this->db
            ->select('te.id AS tt_entry_id, te.session_id, te.class_id, te.section_id,
                      te.subject_group_subject_id, te.staff_id, te.day,
                      sgs.subject_group_id,
                      tp.start_time, tp.end_time,
                      COALESCE(tr.room_number, tr.name, "") AS room_no')
            ->from('tt_entries te')
            ->join('tt_periods tp',           'tp.id = te.period_id',                   'left')
            ->join('tt_rooms tr',             'tr.id = te.room_id',                     'left')
            ->join('subject_group_subjects sgs', 'sgs.id = te.subject_group_subject_id','left')
            ->where('te.session_id',    $session_id)
            ->where('te.is_free_period', 0)
            ->where('tp.is_break',       0)
            ->where('te.subject_group_subject_id IS NOT NULL', null, false)
            ->order_by('tp.sort_order', 'ASC')
            ->get()->result();

        $inserted = 0;
        $updated  = 0;

        foreach ($entries as $e) {
            $time_from = $e->start_time ? date('h:i A', strtotime($e->start_time)) : '';
            $time_to   = $e->end_time   ? date('h:i A', strtotime($e->end_time))   : '';

            $row = [
                'session_id'               => $e->session_id,
                'class_id'                 => $e->class_id,
                'section_id'               => $e->section_id,
                'subject_group_id'         => $e->subject_group_id,
                'subject_group_subject_id' => $e->subject_group_subject_id,
                'staff_id'                 => $e->staff_id,
                'day'                      => $e->day,
                'time_from'                => $time_from,
                'time_to'                  => $time_to,
                'start_time'               => $e->start_time,
                'end_time'                 => $e->end_time,
                'room_no'                  => $e->room_no,
                'tt_entry_id'              => $e->tt_entry_id,
            ];

            $existing = $this->db->where('tt_entry_id', $e->tt_entry_id)
                                  ->get('subject_timetable')->row();
            if ($existing) {
                $this->db->where('id', $existing->id)->update('subject_timetable', $row);
                $updated++;
            } else {
                $this->db->insert('subject_timetable', $row);
                $inserted++;
            }
        }

        return ['status' => '1', 'inserted' => $inserted, 'updated' => $updated,
                'msg' => "Sync complete: {$inserted} new periods added, {$updated} updated."];
    }

    /**
     * Sync a single tt_entry → subject_timetable row.
     * Called automatically after every save_cell / delete_cell so that
     * period-wise subject attendance always has up-to-date timetable data.
     *
     * On delete ($delete=true): removes the subject_timetable row only when
     * no attendance records reference it (preserves historical attendance FKs).
     */
    private function _syncOneEntryToAttendance($tt_entry_id, $delete = false)
    {
        if ($delete) {
            // Only delete if no attendance is linked to this subject_timetable row
            $st = $this->db->where('tt_entry_id', $tt_entry_id)
                           ->get('subject_timetable')->row();
            if ($st) {
                $has_attendance = $this->db->where('subject_timetable_id', $st->id)
                                           ->count_all_results('student_subject_attendence');
                if ($has_attendance == 0) {
                    $this->db->where('id', $st->id)->delete('subject_timetable');
                }
                // If attendance exists: leave row intact — historical data preserved
            }
            return;
        }

        // Fetch the entry with period times and room
        $entry = $this->db
            ->select('te.id AS tt_entry_id, te.session_id, te.class_id, te.section_id,
                      te.subject_group_subject_id, te.staff_id, te.day,
                      sgs.subject_group_id,
                      tp.start_time, tp.end_time,
                      COALESCE(tr.room_number, tr.name, "") AS room_no')
            ->from('tt_entries te')
            ->join('tt_periods tp',              'tp.id = te.period_id',                   'left')
            ->join('tt_rooms tr',                'tr.id = te.room_id',                     'left')
            ->join('subject_group_subjects sgs', 'sgs.id = te.subject_group_subject_id',   'left')
            ->where('te.id', $tt_entry_id)
            ->where('te.is_free_period', 0)
            ->where('te.subject_group_subject_id IS NOT NULL', null, false)
            ->get()->row();

        if (!$entry) {
            // Entry is a free period or has no subject — remove from subject_timetable if safe
            $this->_syncOneEntryToAttendance($tt_entry_id, true);
            return;
        }

        $row = [
            'session_id'               => $entry->session_id,
            'class_id'                 => $entry->class_id,
            'section_id'               => $entry->section_id,
            'subject_group_id'         => $entry->subject_group_id,
            'subject_group_subject_id' => $entry->subject_group_subject_id,
            'staff_id'                 => $entry->staff_id,
            'day'                      => $entry->day,
            'time_from'                => $entry->start_time ? date('h:i A', strtotime($entry->start_time)) : '',
            'time_to'                  => $entry->end_time   ? date('h:i A', strtotime($entry->end_time))   : '',
            'start_time'               => $entry->start_time,
            'end_time'                 => $entry->end_time,
            'room_no'                  => $entry->room_no,
            'tt_entry_id'              => $entry->tt_entry_id,
        ];

        $existing = $this->db->where('tt_entry_id', $tt_entry_id)
                              ->get('subject_timetable')->row();
        if ($existing) {
            $this->db->where('id', $existing->id)->update('subject_timetable', $row);
        } else {
            $this->db->insert('subject_timetable', $row);
        }
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
        $data['departments'] = $this->department_model->getDepartmentsForSession($data['session_id']);
        $data['periods']     = $this->Tt_period_model->getAll($data['session_id']);
        $data['entries']     = [];
        $data['subjects']    = [];
        $data['staff_list']  = [];
        $data['rooms']       = $this->Tt_room_model->getActive();
        $data['days']        = $this->_getWorkingDays();
        $data['class_id']    = '';
        $data['section_id']  = '';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/class_grid', $data);
        $this->load->view('layout/footer', $data);
    }

    public function load_class_grid()
    {
        $session_id  = $this->setting_model->getCurrentSession();
        $class_id    = (int) $this->input->post('class_id');
        $section_id  = (int) $this->input->post('section_id');
        $week_offset = (int) $this->input->post('week_offset');

        $periods   = $this->Tt_period_model->getAll($session_id);
        $entries   = $this->Tt_entry_model->getGridEntries($session_id, $class_id, $section_id);
        $subjects  = $this->_getSubjectsForGrid($session_id, $class_id, $section_id);
        $staff     = $this->staff_model->getStaffbyrole(2);
        $rooms     = $this->Tt_room_model->getActive();
        $batches   = $this->Tt_batch_model->getForClassSection($session_id, $class_id, $section_id);
        $days           = $this->_getWorkingDays();
        $day_dates      = $this->_calcWeekDates($days, $week_offset);
        $day_full_dates = $this->_calcWeekFullDates($days, $week_offset);

        $entry_map = [];
        $placed_counts = [];
        foreach ($entries as $e) {
            $batch_key = $e->batch_id ?: 0;
            $entry_map[$e->day][$e->period_id][$batch_key] = $e;
            if (!empty($e->subject_group_subject_id) && empty($e->is_free_period)) {
                $placed_counts[(int)$e->subject_group_subject_id] =
                    ($placed_counts[(int)$e->subject_group_subject_id] ?? 0) + 1;
            }
        }

        // Enrich subjects with placed/needed counts for the cell editor
        $loads_map = [];
        $sl = $this->Tt_subjectload_model->getForClassSection($session_id, $class_id, $section_id);
        if (!empty($sl)) {
            foreach ($sl as $l) {
                $loads_map[(int)$l->subject_group_subject_id] = (int)$l->periods_per_week;
            }
        }
        if (!empty($subjects)) {
            foreach ($subjects as $idx => $subj) {
                $sgs = (int)$subj->subject_group_subject_id;
                $subjects[$idx]->periods_per_week = $loads_map[$sgs] ?? 0;
                $subjects[$idx]->placed           = $placed_counts[$sgs] ?? 0;
                $subjects[$idx]->remaining        = max(0, $subjects[$idx]->periods_per_week - $subjects[$idx]->placed);
            }
        }

        $subst_list = $this->Tt_substitution_model->getForClassWeek($session_id, $class_id, $section_id, array_values($day_full_dates));
        $subst_map  = [];
        foreach ($subst_list as $s) {
            $subst_map[$s->date][$s->period_id] = $s;
        }

        $joint_teacher_map = $this->Tt_joint_model->getTeacherMapForClass($session_id, $class_id, $section_id);
        $data = compact('periods','entry_map','subjects','staff','rooms','batches','days','day_dates','day_full_dates','subst_map','class_id','section_id','session_id','joint_teacher_map');
        $html = $this->load->view('admin/tt/_grid_table', $data, true);

        // Build flat data rows for client-side Excel/PDF export (no colspan issues)
        $cls_row   = $this->db->select('class')->where('id', $class_id)->get('classes')->row();
        $sec_row   = $this->db->select('section')->where('id', $section_id)->get('sections')->row();
        $cls_label = ($cls_row ? $cls_row->class : '') . ' ' . ($sec_row ? $sec_row->section : '');
        $day_names = array_keys($days);
        $flat_rows = [];
        foreach ($periods as $period) {
            $row = [
                htmlspecialchars($period->name),
                date('h:i', strtotime($period->start_time)) . '-' . date('h:i', strtotime($period->end_time)),
            ];
            foreach ($day_names as $dk) {
                if ($period->is_break) {
                    $row[] = $period->break_label ?: 'Break';
                } else {
                    $e = $entry_map[$dk][$period->id][0] ?? null;
                    if ($e) {
                        if ($e->is_free_period) {
                            $row[] = $e->free_period_label ?: 'Free';
                        } else {
                            $abbr  = !empty($e->tt_abbr) ? $e->tt_abbr : ($e->subject_code ?: $e->subject_name);
                            $sgs   = $e->subject_group_subject_id ?? 0;
                            $tname = !empty($joint_teacher_map[$sgs])
                                   ? $joint_teacher_map[$sgs]
                                   : trim(($e->staff_name ?? '') . ' ' . ($e->staff_surname ?? ''));
                            $row[] = $abbr . ($tname ? ' (' . $tname . ')' : '');
                        }
                    } else {
                        $row[] = '';
                    }
                }
            }
            $flat_rows[] = $row;
        }

        echo json_encode([
            'status'    => '1',
            'html'      => $html,
            'subjects'  => $subjects,
            'staff'     => $staff,
            'rooms'     => $rooms,
            'batches'   => $batches,
            'flat_rows' => $flat_rows,
            'flat_cols' => array_merge(['Period', 'Time'], $day_names),
            'cls_label' => $cls_label,
        ]);
    }

    // -------------------------------------------------------------------------
    // Class grid export helpers (shared data loader)
    // -------------------------------------------------------------------------

    private function _loadClassGridData($class_id, $section_id)
    {
        $session_id = $this->setting_model->getCurrentSession();
        $periods    = $this->Tt_period_model->getAll($session_id);
        $entries    = $this->Tt_entry_model->getGridEntries($session_id, $class_id, $section_id);
        $days       = $this->_getWorkingDays();
        $entry_map  = [];
        foreach ($entries as $e) {
            $entry_map[$e->day][$e->period_id][$e->batch_id ?: 0] = $e;
        }
        $cls = $this->db->select('class')->where('id', $class_id)->get('classes')->row();
        $sec = $this->db->select('section')->where('id', $section_id)->get('sections')->row();
        $header_img     = $this->setting_model->get_general_purpose_header();
        $header_img_url = $header_img
            ? $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/' . $header_img)
            : null;
        $joint_teacher_map = $this->Tt_joint_model->getTeacherMapForClass($session_id, $class_id, $section_id);
        return [
            'session_id'        => $session_id,
            'class_id'          => $class_id,
            'section_id'        => $section_id,
            'class_label'       => $cls ? $cls->class : "Class $class_id",
            'section_label'     => $sec ? $sec->section : "Section $section_id",
            'periods'           => $periods,
            'entry_map'         => $entry_map,
            'days'              => $days,
            'header_img_url'    => $header_img_url,
            'joint_teacher_map' => $joint_teacher_map,
        ];
    }

    public function print_class_grid()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_view')) { access_denied(); }
        $class_id   = (int) $this->input->get('class_id');
        $section_id = (int) $this->input->get('section_id');
        $data = $this->_loadClassGridData($class_id, $section_id);
        $data['for_print'] = true;
        $this->load->view('admin/tt/print_class_grid', $data);
    }

    public function export_class_grid_pdf()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_view')) { access_denied(); }
        $class_id   = (int) $this->input->get('class_id');
        $section_id = (int) $this->input->get('section_id');
        $d = $this->_loadClassGridData($class_id, $section_id);
        $day_names   = array_keys($d['days']);
        $school_name = isset($this->sch_setting_detail->name) ? $this->sch_setting_detail->name : '';

        // Local path for header image — mpdf reads files directly (no HTTP fetch)
        $header_img = $this->setting_model->get_general_purpose_header();
        $img_local  = null;
        if ($header_img) {
            $p = FCPATH . 'uploads/print_headerfooter/general_purpose/' . $header_img;
            if (file_exists($p)) $img_local = $p;
        }

        // Build mpdf-safe HTML (avoid object-fit / max-height / inline-block — unsupported in mpdf)
        $type_bg = ['theory'=>'#3498db','practical'=>'#e74c3c','project'=>'#f39c12','other'=>'#7f8c8d'];
        $n       = count($day_names);

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body{font-family:dejavusans,Arial,sans-serif;font-size:9pt;color:#333;margin:0;padding:0;}
table{width:100%;border-collapse:collapse;}
th{background:#3c8dbc;color:#fff;padding:5px 3px;text-align:center;border:1px solid #2980b9;font-size:9pt;}
td{border:1px solid #bbb;padding:4px 3px;vertical-align:middle;text-align:center;}
.tc{background:#f4f4f4;text-align:left;width:68pt;}
.brk td{background:#fffde7;color:#888;font-style:italic;}
.stag{color:#fff;font-weight:bold;font-size:8pt;padding:1px 4px;border-radius:3px;}
.stn{font-size:7pt;color:#555;display:block;margin-top:1px;}
</style></head><body>';

        // Header image (explicit mm height so mpdf doesn't render at natural pixel size)
        if ($img_local) {
            $html .= '<div style="border-bottom:2px solid #3c8dbc;margin-bottom:8pt;padding-bottom:6pt;">'
                   . '<img src="' . $img_local . '" style="width:100%;height:28mm;display:block;" alt="">'
                   . '</div>';
        } elseif ($school_name) {
            $html .= '<div style="text-align:center;border-bottom:2px solid #3c8dbc;margin-bottom:8pt;padding-bottom:6pt;">'
                   . '<span style="font-size:15pt;font-weight:bold;color:#2c3e50;">' . htmlspecialchars($school_name) . '</span>'
                   . '</div>';
        }

        $html .= '<h2 style="text-align:center;font-size:12pt;margin:6pt 0 8pt;color:#2c3e50;">'
               . 'Class Timetable &mdash; ' . htmlspecialchars($d['class_label'] . ' ' . $d['section_label'])
               . '</h2>';

        $html .= '<table><thead><tr><th class="tc">Period / Time</th>';
        foreach ($day_names as $dn) { $html .= '<th>' . htmlspecialchars($dn) . '</th>'; }
        $html .= '</tr></thead><tbody>';

        foreach ($d['periods'] as $period) {
            $t = date('h:i', strtotime($period->start_time)) . ' - ' . date('h:i', strtotime($period->end_time));
            if ($period->is_break) {
                $html .= '<tr class="brk"><td class="tc"><b>' . htmlspecialchars($period->name) . '</b><br><small>' . $t . '</small></td>'
                       . '<td colspan="' . $n . '">' . htmlspecialchars($period->break_label ?: $period->name) . '</td></tr>';
            } else {
                $html .= '<tr><td class="tc"><b>' . htmlspecialchars($period->name) . '</b><br><small>' . $t . '</small></td>';
                foreach ($day_names as $dn) {
                    $e = $d['entry_map'][$dn][$period->id][0] ?? null;
                    if ($e) {
                        if ($e->is_free_period) {
                            $html .= '<td><span class="stag" style="background:#27ae60;">' . htmlspecialchars($e->free_period_label ?: 'Free') . '</span></td>';
                        } else {
                            $abbr  = !empty($e->tt_abbr) ? $e->tt_abbr : ($e->subject_code ?: $e->subject_name);
                            $sgs   = $e->subject_group_subject_id ?? 0;
                            $tname = !empty($d['joint_teacher_map'][$sgs])
                                   ? $d['joint_teacher_map'][$sgs]
                                   : trim(($e->staff_name ?? '') . ' ' . ($e->staff_surname ?? ''));
                            $bg    = !empty($e->tt_color) ? $e->tt_color : ($type_bg[strtolower($e->subject_type ?? 'other')] ?? '#7f8c8d');
                            $html .= '<td><span class="stag" style="background:' . $bg . ';">' . htmlspecialchars($abbr) . '</span>'
                                   . ($tname ? '<span class="stn">' . htmlspecialchars($tname) . '</span>' : '')
                                   . '</td>';
                        }
                    } else {
                        $html .= '<td></td>';
                    }
                }
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';
        $html .= '<p style="text-align:right;font-size:7pt;color:#aaa;margin-top:6pt;">Printed: ' . date('d M Y, h:i A') . '</p>';
        $html .= '</body></html>';

        $this->load->library('m_pdf');
        $mpdf = $this->m_pdf->load([
            'tempDir'      => sys_get_temp_dir(),
            'mode'         => 'utf-8',
            'default_font' => 'dejavusans',
            'margin_left'  => 8,
            'margin_right' => 8,
            'margin_top'   => 6,
            'margin_bottom'=> 6,
            'format'       => 'A4-L',
        ]);
        $mpdf->WriteHTML($html);
        $fname = 'timetable_' . preg_replace('/[^a-zA-Z0-9]/', '_',
            $d['class_label'] . '_' . $d['section_label']) . '_' . date('Ymd') . '.pdf';
        $mpdf->Output($fname, 'D');
        exit;
    }

    public function export_class_grid_excel()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_view')) { access_denied(); }
        $class_id   = (int) $this->input->get('class_id');
        $section_id = (int) $this->input->get('section_id');
        $d         = $this->_loadClassGridData($class_id, $section_id);
        $cls_label = $d['class_label'] . ' ' . $d['section_label'];
        $day_names = array_keys($d['days']);
        $n_days    = count($day_names);
        $col_span  = 2 + $n_days;

        $hdr_bg  = '#3C8DBC';
        $hdr_col = '#FFFFFF';
        $brk_bg  = '#FFFDE7';

        $rows  = '';

        // Title rows
        $school_name = isset($this->sch_setting_detail->name) ? $this->sch_setting_detail->name : '';
        if ($school_name) {
            $rows .= '<tr><td colspan="' . $col_span . '" style="font-size:15pt;font-weight:bold;text-align:center;background:#2C3E50;color:#FFFFFF;">'
                   . htmlspecialchars($school_name) . '</td></tr>';
        }
        $rows .= '<tr><td colspan="' . $col_span . '" style="font-size:13pt;font-weight:bold;text-align:center;background:#F0F0F0;">'
               . htmlspecialchars('Class Timetable — ' . $cls_label) . '</td></tr>';

        // Column header row
        $rows .= '<tr>'
               . '<td style="background:' . $hdr_bg . ';color:' . $hdr_col . ';font-weight:bold;width:90pt;">Period</td>'
               . '<td style="background:' . $hdr_bg . ';color:' . $hdr_col . ';font-weight:bold;width:80pt;">Time</td>';
        foreach ($day_names as $dn) {
            $rows .= '<td style="background:' . $hdr_bg . ';color:' . $hdr_col . ';font-weight:bold;width:100pt;">' . htmlspecialchars($dn) . '</td>';
        }
        $rows .= '</tr>';

        foreach ($d['periods'] as $period) {
            $time_str = date('h:i', strtotime($period->start_time)) . ' - ' . date('h:i', strtotime($period->end_time));

            if ($period->is_break) {
                $rows .= '<tr>'
                       . '<td style="background:' . $brk_bg . ';font-style:italic;">' . htmlspecialchars($period->name) . '</td>'
                       . '<td style="background:' . $brk_bg . ';font-size:9pt;color:#888;">' . $time_str . '</td>'
                       . '<td colspan="' . $n_days . '" style="background:' . $brk_bg . ';text-align:center;color:#888;font-style:italic;">'
                       . htmlspecialchars($period->break_label ?: $period->name) . '</td>'
                       . '</tr>';
            } else {
                $rows .= '<tr>'
                       . '<td style="font-weight:bold;">' . htmlspecialchars($period->name) . '</td>'
                       . '<td style="font-size:9pt;color:#555;">' . $time_str . '</td>';

                foreach ($day_names as $dn) {
                    $entry = $d['entry_map'][$dn][$period->id][0] ?? null;
                    if ($entry) {
                        if ($entry->is_free_period) {
                            $cell = '<span style="background:#27ae60;color:#fff;padding:1px 5px;">'
                                  . htmlspecialchars($entry->free_period_label ?: 'Free') . '</span>';
                        } else {
                            $abbr  = !empty($entry->tt_abbr) ? $entry->tt_abbr : ($entry->subject_code ?: $entry->subject_name);
                            $sgs   = $entry->subject_group_subject_id ?? 0;
                            $tname = !empty($d['joint_teacher_map'][$sgs])
                                   ? $d['joint_teacher_map'][$sgs]
                                   : trim(($entry->staff_name ?? '') . ' ' . ($entry->staff_surname ?? ''));
                            $cell  = '<strong>' . htmlspecialchars($abbr) . '</strong>'
                                   . ($tname ? '<br><small>' . htmlspecialchars($tname) . '</small>' : '');
                        }
                    } else {
                        $cell = '';
                    }
                    $rows .= '<td style="vertical-align:top;">' . $cell . '</td>';
                }
                $rows .= '</tr>';
            }
        }

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" '
              . 'xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">'
              . '<head><meta charset="UTF-8">'
              . '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>'
              . '<x:Name>Timetable</x:Name></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->'
              . '</head><body>'
              . '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;font-family:Arial;font-size:10pt;">'
              . $rows . '</table></body></html>';

        $fname = 'timetable_' . preg_replace('/[^a-zA-Z0-9]/', '_', $cls_label) . '_' . date('Ymd') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fname . '"');
        header('Cache-Control: max-age=0');
        echo $html;
        exit;
    }

    public function print_teacher_grid()
    {
        if (!$this->rbac->hasPrivilege('tt_teacher_view', 'can_view')) { access_denied(); }
        $session_id  = $this->setting_model->getCurrentSession();
        $staff_id    = (int) $this->input->get('staff_id');
        $week_offset = (int) $this->input->get('week_offset');

        $staff      = $this->db->select('name, surname, employee_id')->where('id', $staff_id)->get('staff')->row();
        $periods    = $this->Tt_period_model->getAll($session_id);
        $entries    = $this->Tt_entry_model->getTeacherEntries($session_id, $staff_id);
        $days       = $this->_getWorkingDays();
        $day_dates  = $this->_calcWeekDates($days, $week_offset);

        $entry_map = [];
        foreach ($entries as $e) {
            $entry_map[$e->day][$e->period_id] = $e;
        }

        $header_img     = $this->setting_model->get_general_purpose_header();
        $header_img_url = $header_img
            ? $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/' . $header_img)
            : null;

        $data = [
            'staff'          => $staff,
            'periods'        => $periods,
            'entry_map'      => $entry_map,
            'days'           => $days,
            'day_dates'      => $day_dates,
            'header_img_url' => $header_img_url,
            'school_name'    => $this->sch_setting_detail->name ?? '',
        ];
        $this->load->view('admin/tt/print_teacher_grid', $data);
    }

    public function print_all_teacher_grids()
    {
        if (!$this->rbac->hasPrivilege('tt_teacher_view', 'can_view')) { access_denied(); }
        $session_id  = $this->setting_model->getCurrentSession();
        $week_offset = (int) $this->input->get('week_offset');
        $days        = $this->_getWorkingDays();
        $day_dates   = $this->_calcWeekDates($days, $week_offset);
        $periods     = $this->Tt_period_model->getAll($session_id);

        $header_img     = $this->setting_model->get_general_purpose_header();
        $header_img_url = $header_img
            ? $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/' . $header_img)
            : null;

        $staff_list = $this->staff_model->getStaffbyrole(2);
        $teachers = [];
        foreach ($staff_list as $st) {
            $staff_id = (int)$st['id'];
            $entries  = $this->Tt_entry_model->getTeacherEntries($session_id, $staff_id);
            $entry_map = [];
            foreach ($entries as $e) {
                $entry_map[$e->day][$e->period_id] = $e;
            }
            if (empty($entries)) continue;
            $teachers[] = [
                'staff'     => (object)$st,
                'entry_map' => $entry_map,
            ];
        }

        $data = [
            'teachers'       => $teachers,
            'periods'        => $periods,
            'days'           => $days,
            'day_dates'      => $day_dates,
            'header_img_url' => $header_img_url,
            'school_name'    => $this->sch_setting_detail->name ?? '',
        ];
        $this->load->view('admin/tt/print_all_teacher_grids', $data);
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
        if ($result) {
            // Sync the saved entry → subject_timetable so period attendance stays current
            $this->_syncOneEntryToAttendance((int)$result);
        }
        echo json_encode(['status' => $result ? '1' : '0']);
    }

    public function delete_cell($id)
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_delete')) {
            access_denied();
        }
        $id = (int)$id;
        // Sync-delete before the tt_entry row is gone (we still need tt_entry_id to find st row)
        $this->_syncOneEntryToAttendance($id, true);
        $this->Tt_entry_model->deleteCell($id);
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

    public function upload_csv_timetable()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_add')) {
            echo json_encode(['status' => '0', 'error' => 'Access denied']); return;
        }

        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');

        if (!$class_id || !$section_id) {
            echo json_encode(['status' => '0', 'error' => 'Class and Section are required']); return;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            echo json_encode(['status' => '0', 'error' => 'No CSV file uploaded']); return;
        }

        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$file) {
            echo json_encode(['status' => '0', 'error' => 'Cannot read CSV file']); return;
        }

        $header = fgetcsv($file);
        if (!$header) {
            fclose($file);
            echo json_encode(['status' => '0', 'error' => 'Empty CSV file']); return;
        }
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $required = ['day', 'period_no', 'subject_name'];
        foreach ($required as $col) {
            if (!in_array($col, $header)) {
                fclose($file);
                echo json_encode(['status' => '0', 'error' => "Missing required column: $col"]); return;
            }
        }

        $periods = $this->Tt_period_model->getAll($session_id);
        $period_map = [];
        $sort = 1;
        foreach ($periods as $p) {
            if (!$p->is_break) {
                $period_map[$sort] = $p->id;
                $sort++;
            }
        }

        $class_row = $this->db->where('id', $class_id)->get('classes')->row();
        $dept_id   = $class_row ? $class_row->department_id : null;

        $sg = $this->db->where('class_id', $class_id)->where('session_id', $session_id)
            ->get('subject_groups')->row();
        if (!$sg) {
            $this->db->insert('subject_groups', [
                'name'       => ($class_row ? $class_row->class : 'Class ' . $class_id) . ' – ' . $session_id,
                'session_id' => $session_id,
                'class_id'   => $class_id,
            ]);
            $sg_id = $this->db->insert_id();
        } else {
            $sg_id = $sg->id;
        }

        $all_staff = $this->db->select('id, name, surname, employee_id')->where('is_active', 1)->get('staff')->result();
        $staff_by_empid = [];
        $staff_by_name  = [];
        foreach ($all_staff as $s) {
            if (!empty($s->employee_id)) $staff_by_empid[strtolower(trim($s->employee_id))] = $s->id;
            $staff_by_name[strtolower(trim($s->name . ' ' . $s->surname))] = $s->id;
            $staff_by_name[strtolower(trim($s->name))] = $s->id;
        }

        // --- Pass 1: read all rows and group by day+period to detect batch splits ---
        $csv_rows = [];
        $line = 1;
        while (($row = fgetcsv($file)) !== false) {
            $line++;
            if (count($row) < count($header)) $row = array_pad($row, count($header), '');
            $data = array_combine($header, array_map('trim', $row));
            $data['_line'] = $line;
            $csv_rows[] = $data;
        }
        fclose($file);

        $slot_counts = [];
        foreach ($csv_rows as $data) {
            $day       = ucfirst(strtolower($data['day'] ?? ''));
            $period_no = (int) ($data['period_no'] ?? 0);
            $key = $day . '_' . $period_no;
            $slot_counts[$key] = ($slot_counts[$key] ?? 0) + 1;
        }

        $needs_batches = false;
        foreach ($slot_counts as $cnt) {
            if ($cnt > 1) { $needs_batches = true; break; }
        }

        $batch_map = [];
        $created_batches = 0;
        if ($needs_batches) {
            $existing_batches = $this->db->where('session_id', $session_id)
                ->where('class_id', $class_id)->where('section_id', $section_id)
                ->order_by('id', 'ASC')->get('tt_batches')->result();

            $max_splits = max($slot_counts);
            $batch_labels = ['A', 'B', 'C', 'D', 'E', 'F'];

            for ($i = 0; $i < $max_splits; $i++) {
                if (isset($existing_batches[$i])) {
                    $batch_map[$i] = (int) $existing_batches[$i]->id;
                } else {
                    $label = 'Batch ' . ($batch_labels[$i] ?? ($i + 1));
                    $this->db->insert('tt_batches', [
                        'session_id'    => $session_id,
                        'class_id'      => $class_id,
                        'section_id'    => $section_id,
                        'batch_name'    => $label,
                        'student_count' => 0,
                    ]);
                    $batch_map[$i] = $this->db->insert_id();
                    $created_batches++;
                }
            }
        }

        // --- Pass 2: process rows and create entries ---
        $created_subjects = 0;
        $created_rooms    = 0;
        $entries_created  = 0;
        $warnings         = [];
        $slot_batch_idx   = [];
        $subject_cache    = [];
        $sgs_cache        = [];
        $room_cache       = [];

        foreach ($csv_rows as $data) {
            $line       = $data['_line'];
            $day        = ucfirst(strtolower($data['day'] ?? ''));
            $period_no  = (int) ($data['period_no'] ?? 0);
            $subj_name  = trim($data['subject_name'] ?? '');

            if (empty($day) || !$period_no || empty($subj_name)) continue;

            if (!isset($period_map[$period_no])) {
                $warnings[] = "Row $line: period_no $period_no not found";
                continue;
            }
            $period_id = $period_map[$period_no];
            $slot_key  = $day . '_' . $period_no;

            $is_batch_slot = ($slot_counts[$slot_key] ?? 1) > 1;
            $batch_id = null;
            if ($is_batch_slot) {
                $idx = $slot_batch_idx[$slot_key] ?? 0;
                $batch_id = $batch_map[$idx] ?? null;
                $slot_batch_idx[$slot_key] = $idx + 1;
            }

            if (strtoupper($subj_name) === 'FREE') {
                $existing = $this->db->where('session_id', $session_id)
                    ->where('class_id', $class_id)->where('section_id', $section_id)
                    ->where('day', $day)->where('period_id', $period_id)
                    ->where('batch_id', $batch_id)
                    ->count_all_results('tt_entries');
                if (!$existing) {
                    $this->db->insert('tt_entries', [
                        'session_id' => $session_id, 'class_id' => $class_id, 'section_id' => $section_id,
                        'day' => $day, 'period_id' => $period_id, 'is_free_period' => 1,
                        'free_period_label' => 'Free Period', 'entry_type' => 'manual',
                        'batch_id' => $batch_id,
                    ]);
                    $entries_created++;
                }
                continue;
            }

            $subj_code = trim($data['subject_code'] ?? '');
            $subj_type = strtolower(trim($data['subject_type'] ?? 'theory'));
            if (!in_array($subj_type, ['theory', 'practical', 'project', 'other'])) $subj_type = 'theory';

            $cache_key = strtolower($subj_code ?: $subj_name);
            if (isset($subject_cache[$cache_key])) {
                $subject = $subject_cache[$cache_key];
            } else {
                $subject = null;
                if (!empty($subj_code)) {
                    $subject = $this->db->where('code', $subj_code)->get('subjects')->row();
                }
                if (!$subject) {
                    $subject = $this->db->where('name', $subj_name)->get('subjects')->row();
                }
                if (!$subject) {
                    $this->db->insert('subjects', [
                        'name' => $subj_name, 'code' => $subj_code, 'type' => $subj_type,
                        'is_active' => 'yes', 'department_id' => $dept_id,
                    ]);
                    $subject = (object) ['id' => $this->db->insert_id()];
                    $created_subjects++;
                }
                $subject_cache[$cache_key] = $subject;
            }

            $sgs_key = $sg_id . '_' . $subject->id;
            if (isset($sgs_cache[$sgs_key])) {
                $sgs_id = $sgs_cache[$sgs_key];
            } else {
                $sgs = $this->db->where('subject_group_id', $sg_id)
                    ->where('subject_id', $subject->id)->where('session_id', $session_id)
                    ->get('subject_group_subjects')->row();
                if (!$sgs) {
                    $this->db->insert('subject_group_subjects', [
                        'subject_group_id' => $sg_id, 'session_id' => $session_id, 'subject_id' => $subject->id,
                    ]);
                    $sgs_id = $this->db->insert_id();
                } else {
                    $sgs_id = $sgs->id;
                }
                $sgs_cache[$sgs_key] = $sgs_id;
            }

            $staff_id = null;
            $teacher_empid = strtolower(trim($data['teacher_employee_id'] ?? ''));
            $teacher_name  = strtolower(trim($data['teacher_name'] ?? ''));
            if (!empty($teacher_empid) && isset($staff_by_empid[$teacher_empid])) {
                $staff_id = $staff_by_empid[$teacher_empid];
            } elseif (!empty($teacher_name) && isset($staff_by_name[$teacher_name])) {
                $staff_id = $staff_by_name[$teacher_name];
            } elseif (!empty($teacher_name)) {
                $warnings[] = "Row $line: teacher '" . $data['teacher_name'] . "' not found";
            }

            $room_id  = null;
            $room_str = trim($data['room'] ?? '');
            if (!empty($room_str)) {
                $room_key = strtolower($room_str);
                if (isset($room_cache[$room_key])) {
                    $room_id = $room_cache[$room_key];
                } else {
                    $room = $this->db->where('name', $room_str)->or_where('room_number', $room_str)
                        ->get('tt_rooms')->row();
                    if (!$room) {
                        $this->db->insert('tt_rooms', [
                            'name' => $room_str, 'room_number' => $room_str,
                            'capacity' => 60, 'room_type' => 'classroom', 'is_active' => 1,
                        ]);
                        $room_id = $this->db->insert_id();
                        $created_rooms++;
                    } else {
                        $room_id = $room->id;
                    }
                    $room_cache[$room_key] = $room_id;
                }
            }

            $dup_check = $this->db->where('session_id', $session_id)
                ->where('class_id', $class_id)->where('section_id', $section_id)
                ->where('day', $day)->where('period_id', $period_id);
            if ($batch_id) {
                $dup_check->where('batch_id', $batch_id);
            } else {
                $dup_check->where('batch_id IS NULL', null, false);
            }
            if ($dup_check->count_all_results('tt_entries') > 0) {
                $warnings[] = "Row $line: $day period $period_no already has an entry, skipped";
                continue;
            }

            $this->db->insert('tt_entries', [
                'session_id'               => $session_id,
                'class_id'                 => $class_id,
                'section_id'               => $section_id,
                'subject_group_id'         => $sg_id,
                'subject_group_subject_id' => $sgs_id,
                'staff_id'                 => $staff_id,
                'period_id'                => $period_id,
                'day'                      => $day,
                'room_id'                  => $room_id,
                'batch_id'                 => $batch_id,
                'is_free_period'           => 0,
                'entry_type'               => 'manual',
            ]);
            $entries_created++;
        }

        // Sync all new entries for this class/section → subject_timetable
        if ($entries_created > 0) {
            $this->_doSyncToAttendance();
        }

        echo json_encode([
            'status'           => '1',
            'created_subjects' => $created_subjects,
            'created_rooms'    => $created_rooms,
            'created_batches'  => $created_batches,
            'entries_created'  => $entries_created,
            'warnings'         => implode('<br>', $warnings),
        ]);
    }

    public function fill_empty_cells()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_edit')) {
            echo json_encode(['status' => '0', 'message' => 'Access denied']); return;
        }
        $session_id = $this->setting_model->getCurrentSession();
        $class_id   = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');

        if (!$class_id || !$section_id) {
            echo json_encode(['status' => '0', 'message' => 'No class selected']); return;
        }

        $result = $this->Tt_generator_model->fillEmptyCellsLive($session_id, $class_id, $section_id);
        echo json_encode($result);
    }

    public function fill_all_classes()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_edit')) {
            echo json_encode(['status' => '0', 'message' => 'Access denied']); return;
        }
        $session_id = $this->setting_model->getCurrentSession();

        $classes = $this->db->select('class_id, section_id, COUNT(*) as cnt')
            ->where('session_id', $session_id)
            ->where('is_free_period', 0)
            ->group_by('class_id, section_id')
            ->get('tt_entries')->result();

        $periods = $this->Tt_period_model->getAll($session_id);
        $tc = 0; foreach ($periods as $p) { if (!$p->is_break) $tc++; }
        $min_entries = count($this->_getWorkingDays()) * $tc / 2;

        $total_swapped = 0; $total_filled = 0; $classes_fixed = 0;
        foreach ($classes as $cs) {
            if ((int)$cs->cnt < $min_entries) continue;
            $r = $this->Tt_generator_model->fillEmptyCellsLive($session_id, (int)$cs->class_id, (int)$cs->section_id);
            $sw = $r['cross_swapped'] ?? 0;
            $fi = $r['filled_subject'] ?? 0;
            if ($sw + $fi > 0) $classes_fixed++;
            $total_swapped += $sw;
            $total_filled  += $fi;
        }
        echo json_encode([
            'status'        => '1',
            'classes_total' => count($classes),
            'classes_fixed' => $classes_fixed,
            'cross_swapped' => $total_swapped,
            'filled_subject'=> $total_filled,
        ]);
    }

    public function gaps_overview()
    {
        if (!$this->rbac->hasPrivilege('tt_class_grid', 'can_view')) {
            echo json_encode(['status' => '0']); return;
        }
        $session_id = $this->setting_model->getCurrentSession();

        $periods = $this->Tt_period_model->getAll($session_id);
        $teaching_count = 0;
        foreach ($periods as $p) { if (!$p->is_break) $teaching_count++; }

        $days = $this->_getWorkingDays();
        $total_slots = count($days) * $teaching_count;

        $entries = $this->db->select('class_id, section_id, is_free_period, COUNT(*) as cnt')
            ->where('session_id', $session_id)
            ->group_by('class_id, section_id, is_free_period')
            ->get('tt_entries')->result();

        $counts = [];
        foreach ($entries as $e) {
            $k = $e->class_id . '_' . $e->section_id;
            if (!isset($counts[$k])) $counts[$k] = ['filled' => 0, 'free' => 0];
            if ($e->is_free_period) $counts[$k]['free'] += (int)$e->cnt;
            else $counts[$k]['filled'] += (int)$e->cnt;
        }

        $class_sections = $this->db->select('classes.id as class_id, classes.class, sections.id as section_id, sections.section')
            ->from('tt_entries')
            ->join('classes','classes.id = tt_entries.class_id')
            ->join('sections','sections.id = tt_entries.section_id')
            ->where('tt_entries.session_id', $session_id)
            ->group_by('tt_entries.class_id, tt_entries.section_id')
            ->order_by('classes.class','ASC')->order_by('sections.section','ASC')
            ->get()->result();

        $rows = [];
        $total_gaps = 0; $total_free = 0; $total_complete = 0;
        foreach ($class_sections as $cs) {
            $k = $cs->class_id . '_' . $cs->section_id;
            $filled = $counts[$k]['filled'] ?? 0;
            $free   = $counts[$k]['free'] ?? 0;
            $empty  = max(0, $total_slots - $filled - $free);
            if ($filled + $free === 0) continue;
            if ($filled < $total_slots / 2) continue;
            if ($empty === 0 && $free === 0) { $total_complete++; continue; }
            $total_gaps += $empty;
            $total_free += $free;
            $rows[] = [
                'class' => $cs->class . ' ' . $cs->section,
                'class_id' => (int)$cs->class_id,
                'section_id' => (int)$cs->section_id,
                'filled' => $filled,
                'free' => $free,
                'empty' => $empty,
                'total' => $total_slots,
            ];
        }
        echo json_encode([
            'status' => '1', 'rows' => $rows,
            'total_slots' => $total_slots,
            'total_complete' => $total_complete,
            'total_gaps' => $total_gaps,
            'total_free' => $total_free,
        ]);
    }

    public function get_available_teachers()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $day        = $this->input->post('day');
        $period_id  = (int) $this->input->post('period_id');

        if (!$day || !$period_id) {
            echo json_encode(['status' => '0']); return;
        }

        // All teaching staff
        $all_staff = $this->staff_model->getStaffbyrole(2);

        // Who's busy teaching at this exact slot?
        $busy = $this->db->select('staff_id')
            ->where('session_id', $session_id)->where('day', $day)->where('period_id', $period_id)
            ->get('tt_entries')->result();
        $busy_ids = array_column($busy, 'staff_id');

        // Who has a time-off block at this slot?
        $blocked = $this->db->select('staff_id')
            ->where('session_id', $session_id)->where('day', $day)->where('period_id', $period_id)
            ->get('tt_teacher_unavail')->result();
        $blocked_ids = array_column($blocked, 'staff_id');

        $unavail = array_unique(array_merge(array_map('intval', $busy_ids), array_map('intval', $blocked_ids)));

        $result = [];
        foreach ($all_staff as $s) {
            $id = (int) $s['id'];
            $free = !in_array($id, $unavail);
            $result[] = [
                'id'   => $id,
                'name' => trim($s['name'] . ' ' . ($s['surname'] ?? '')),
                'free' => $free,
            ];
        }

        echo json_encode(['status' => '1', 'teachers' => $result]);
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
        $data['days']       = $this->_getWorkingDays();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/tt/teacher_view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function load_teacher_grid()
    {
        $session_id  = $this->setting_model->getCurrentSession();
        $staff_id    = (int) $this->input->post('staff_id');
        $week_offset = (int) $this->input->post('week_offset');
        $periods     = $this->Tt_period_model->getAll($session_id);
        $entries     = $this->Tt_entry_model->getTeacherEntries($session_id, $staff_id);
        $days           = $this->_getWorkingDays();
        $day_dates      = $this->_calcWeekDates($days, $week_offset);
        $day_full_dates = $this->_calcWeekFullDates($days, $week_offset);

        $entry_map = [];
        foreach ($entries as $e) {
            $entry_map[$e->day][$e->period_id] = $e;
        }

        $subst_data  = $this->Tt_substitution_model->getForTeacherWeek($session_id, $staff_id, array_values($day_full_dates));
        $absent_map  = [];
        foreach ($subst_data['absent'] as $s) {
            $absent_map[$s->date][$s->period_id] = $s;
        }
        $covering_map = [];
        foreach ($subst_data['covering'] as $s) {
            $covering_map[$s->date][$s->period_id] = $s;
        }

        $data = compact('periods','entry_map','days','day_dates','day_full_dates','absent_map','covering_map','staff_id','session_id');
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
        $date           = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date')));
        $day            = date('l', strtotime($date));

        $staff_ids_raw = $this->input->post('absent_staff_ids');
        if (is_array($staff_ids_raw)) {
            $absent_ids = array_map('intval', $staff_ids_raw);
        } else {
            $absent_ids = [(int) $this->input->post('absent_staff_id')];
        }
        $absent_ids = array_filter($absent_ids);

        $all_slots = [];
        foreach ($absent_ids as $absent_staff) {
            $slots    = $this->Tt_entry_model->getStaffSlotsForDay($session_id, $absent_staff, $day);
            $existing = $this->Tt_substitution_model->getByDateStaff($session_id, $absent_staff, $date);

            $existing_map = [];
            foreach ($existing as $ex) {
                $existing_map[$ex->tt_entry_id] = $ex;
            }

            foreach ($slots as &$slot) {
                $slot->substitution = $existing_map[$slot->id] ?? null;
                $slot->absent_staff_id = $absent_staff;
                $slot->available_teachers = $this->Tt_entry_model->getAvailableTeachers($session_id, $day, $slot->period_id, $absent_ids);
            }
            $all_slots = array_merge($all_slots, $slots);
        }

        echo json_encode(['status' => '1', 'day' => $day, 'slots' => $all_slots]);
    }

    public function bulk_auto_assign()
    {
        set_time_limit(60);
        if (!$this->rbac->hasPrivilege('tt_substitution', 'can_add')) {
            echo json_encode(['status' => '0', 'message' => 'Access denied']);
            return;
        }
        $session_id = $this->setting_model->getCurrentSession();
        $created_by = $this->customlib->getStaffID();
        $date       = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date')));
        $day        = date('l', strtotime($date));

        $staff_ids_raw = $this->input->post('absent_staff_ids');
        $absent_ids    = array_filter(array_map('intval', is_array($staff_ids_raw) ? $staff_ids_raw : []));
        if (empty($absent_ids)) {
            echo json_encode(['status' => '0', 'message' => 'No teachers selected']);
            return;
        }

        $all_slots = [];
        foreach ($absent_ids as $sid) {
            $slots = $this->Tt_entry_model->getStaffSlotsForDay($session_id, $sid, $day);
            $existing = $this->Tt_substitution_model->getByDateStaff($session_id, $sid, $date);
            $existing_map = [];
            foreach ($existing as $ex) $existing_map[$ex->tt_entry_id] = $ex;
            foreach ($slots as $s) {
                if (isset($existing_map[$s->id])) continue;
                $s->absent_staff_id = $sid;
                $all_slots[] = $s;
            }
        }

        // Build teacher subject/department index for priority matching
        $teacher_subjects = [];
        $teacher_depts = [];
        $staff_rows = $this->staff_model->getStaffbyrole(2);
        foreach ($staff_rows as $sr) {
            $teacher_depts[(int)$sr['id']] = (int)($sr['department_id'] ?? 0);
        }
        $load_rows = $this->db->select('tt_subject_load_teachers.staff_id, tt_subject_load.subject_group_subject_id')
            ->from('tt_subject_load_teachers')
            ->join('tt_subject_load', 'tt_subject_load.id = tt_subject_load_teachers.subject_load_id')
            ->where('tt_subject_load.session_id', $session_id)
            ->get()->result();
        foreach ($load_rows as $lr) {
            $teacher_subjects[(int)$lr->staff_id][(int)$lr->subject_group_subject_id] = true;
        }

        // Pre-fetch available teachers per period (one DB query each, not N²)
        $period_ids = array_unique(array_map(function($s) { return $s->period_id; }, $all_slots));
        $avail_by_period = [];
        foreach ($period_ids as $pid) {
            $avail_by_period[$pid] = $this->Tt_entry_model->getAvailableTeachers($session_id, $day, $pid, $absent_ids);
        }

        // Sort slots by fewer available teachers first (most constrained first)
        usort($all_slots, function($a, $b) use ($avail_by_period) {
            return count($avail_by_period[$a->period_id] ?? []) - count($avail_by_period[$b->period_id] ?? []);
        });

        $batch_assigned = [];
        $results = [];
        $assigned_count = 0;

        foreach ($all_slots as $slot) {
            $available = array_filter($avail_by_period[$slot->period_id] ?? [], function($t) use ($slot, &$batch_assigned) {
                return empty($batch_assigned[$t->id][$slot->period_id]);
            });

            $sgs_id = (int)($slot->subject_group_subject_id ?? 0);
            $slot_dept = $teacher_depts[$slot->staff_id] ?? 0;
            $ranked = [];
            foreach ($available as $t) {
                $tid = (int)$t->id;
                $score = 0;
                if ($sgs_id && !empty($teacher_subjects[$tid][$sgs_id])) $score += 100;
                $t_dept = $teacher_depts[$tid] ?? 0;
                if ($t_dept > 0 && $t_dept === $slot_dept) $score += 10;
                $ranked[] = ['teacher' => $t, 'score' => $score];
            }
            usort($ranked, function($a, $b) { return $b['score'] - $a['score']; });

            $best = !empty($ranked) ? $ranked[0]['teacher'] : null;
            $match_type = 'none';
            if ($best && !empty($ranked[0]['score'])) {
                $match_type = $ranked[0]['score'] >= 100 ? 'subject' : 'department';
            } elseif ($best) {
                $match_type = 'any_free';
            }

            if ($best) {
                $data = [
                    'session_id'               => $session_id,
                    'absent_staff_id'          => $slot->absent_staff_id,
                    'substitute_staff_id'      => $best->id,
                    'tt_entry_id'              => $slot->id,
                    'date'                     => $date,
                    'day'                      => $day,
                    'period_id'                => $slot->period_id,
                    'class_id'                 => $slot->class_id,
                    'section_id'               => $slot->section_id,
                    'subject_group_subject_id' => $slot->subject_group_subject_id,
                    'room_id'                  => $slot->room_id,
                    'substitution_type'        => 'auto_bulk',
                    'status'                   => 'confirmed',
                    'note'                     => 'Bulk auto-assigned (' . $match_type . ' match)',
                    'created_by'               => $created_by,
                ];
                $this->Tt_substitution_model->save($data);
                $batch_assigned[$best->id][$slot->period_id] = true;
                $assigned_count++;
                $results[] = [
                    'entry_id'   => $slot->id,
                    'substitute' => $best->name . ' ' . $best->surname,
                    'match_type' => $match_type,
                    'status'     => 'assigned',
                ];
            } else {
                $results[] = [
                    'entry_id'   => $slot->id,
                    'substitute' => null,
                    'match_type' => 'none',
                    'status'     => 'no_substitute',
                ];
            }
        }

        echo json_encode([
            'status'   => '1',
            'assigned' => $assigned_count,
            'total'    => count($all_slots),
            'results'  => $results,
        ]);
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
        $date               = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date')));
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
        $dept_id     = (int) $this->input->post('dept_id') ?: null;

        // Resolve dept_id → class_ids for filtering
        $class_ids = null;
        if ($dept_id) {
            $rows = $this->db->select('classes.id')->from('classes')
                ->join('student_session','student_session.class_id=classes.id')
                ->where('classes.department_id', $dept_id)
                ->where('student_session.session_id', $session_id)
                ->group_by('classes.id')->get()->result_array();
            $class_ids = array_column($rows, 'id') ?: [-1];
        }

        $data = $this->Tt_substitution_model->getReport($session_id, $from_date, $to_date, $staff_id, $class_ids);
        echo json_encode(['status' => '1', 'data' => $data]);
    }

    public function duty_chart()
    {
        if (!$this->rbac->hasPrivilege('tt_substitution', 'can_view')) {
            access_denied();
        }
        $session_id = $this->setting_model->getCurrentSession();
        $raw        = $this->input->get('date') ?: date('Y-m-d');
        $ts         = $this->customlib->datetostrtotime($raw);
        $date       = $ts ? date('Y-m-d', $ts) : $raw;

        $substitutions = $this->Tt_substitution_model->getByDate($session_id, $date);

        $by_period = [];
        foreach ($substitutions as $s) {
            $by_period[$s->period_sort ?? 0][] = $s;
        }
        ksort($by_period);

        $school_name    = $this->sch_setting_detail->name ?? '';
        $header_img     = $this->setting_model->get_general_purpose_header();
        $header_img_url = $header_img
            ? $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/' . $header_img)
            : null;

        $data = [
            'date'           => $date,
            'substitutions'  => $substitutions,
            'by_period'      => $by_period,
            'school_name'    => $school_name,
            'header_img_url' => $header_img_url,
        ];
        $this->load->view('admin/tt/duty_chart_print', $data);
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
        $data['departments'] = $this->department_model->getDepartmentsForSession($data['session_id']);
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

        // Department filter: resolve dept_id → class IDs for this session
        $dept_id = (int) $this->input->post('dept_id');
        if ($dept_id > 0) {
            $rows = $this->db
                ->select('classes.id')
                ->from('classes')
                ->join('student_session', 'student_session.class_id = classes.id')
                ->where('classes.department_id', $dept_id)
                ->where('student_session.session_id', $session_id)
                ->group_by('classes.id')
                ->get()->result_array();
            $class_ids = array_column($rows, 'id');
            if (empty($class_ids)) {
                echo json_encode(['status' => '1', 'html' => '<div class="alert alert-info">No timetable entries found for this department.</div>']);
                return;
            }
        }

        $data = $this->Tt_entry_model->getMasterReport($session_id, $class_ids);
        $periods = $this->Tt_period_model->getAll($session_id);
        $days    = $this->_getWorkingDays();
        $html = $this->load->view('admin/tt/_report_master', compact('data','periods','days'), true);
        echo json_encode(['status' => '1', 'html' => $html]);
    }

    // ── PDF export for master timetable report ──────────────────────────────
    public function export_report_pdf()
    {
        if (!$this->rbac->hasPrivilege('tt_reports', 'can_view')) { access_denied(); }

        $session_id  = $this->setting_model->getCurrentSession();
        $school_name = $this->sch_setting_detail->name ?? '';
        $dept_id     = (int) $this->input->get('dept_id');

        // Resolve class_ids from department filter (same logic as get_master_report)
        $class_ids = null;
        if ($dept_id > 0) {
            $rows = $this->db
                ->select('classes.id')
                ->from('classes')
                ->join('student_session', 'student_session.class_id = classes.id')
                ->where('classes.department_id', $dept_id)
                ->where('student_session.session_id', $session_id)
                ->group_by('classes.id')
                ->get()->result_array();
            $class_ids = array_column($rows, 'id');
        }

        $all_entries = $this->Tt_entry_model->getMasterReport($session_id, $class_ids);
        $periods     = $this->Tt_period_model->getAll($session_id);
        $days        = $this->_getWorkingDays();
        $day_names   = array_keys($days);
        $n_days      = count($day_names);

        // Header image
        $header_img = $this->setting_model->get_general_purpose_header();
        $img_local  = null;
        if ($header_img) {
            $p = FCPATH . 'uploads/print_headerfooter/general_purpose/' . $header_img;
            if (file_exists($p)) $img_local = $p;
        }

        $type_bg = ['theory'=>'#3498db','practical'=>'#e74c3c','project'=>'#f39c12','other'=>'#7f8c8d'];

        // Group entries by class+section
        $grouped = [];
        foreach ($all_entries as $r) {
            $key = $r->class_id . '_' . $r->section_id;
            if (!isset($grouped[$key])) {
                $grouped[$key] = ['class'=>$r->class_name,'section'=>$r->section_name,'entries'=>[]];
            }
            $grouped[$key]['entries'][] = $r;
        }

        if (empty($grouped)) {
            echo '<div class="alert alert-warning">No timetable data found.</div>'; return;
        }

        // Build PDF HTML
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body  { font-family:dejavusans,Arial,sans-serif; font-size:8.5pt; color:#333; margin:0; padding:0; }
h2    { text-align:center; font-size:11pt; margin:5pt 0 6pt; color:#2c3e50; }
h3    { text-align:center; font-size:9pt;  margin:0 0 4pt;  color:#555; font-weight:normal; }
table { width:100%; border-collapse:collapse; }
th    { background:#3c8dbc; color:#fff; padding:4px 3px; text-align:center;
        border:1px solid #2980b9; font-size:8pt; white-space:nowrap; }
td    { border:1px solid #ccc; padding:4px 3px; vertical-align:middle;
        text-align:center; min-height:42pt; }
.tc   { background:#f4f4f4; text-align:left; width:60pt; }
.brk td { background:#fffde7; color:#999; font-style:italic; }
.stag { color:#fff; font-weight:bold; font-size:7.5pt; padding:1px 4px; border-radius:3px; }
.subj { font-size:7pt; color:#444; display:block; margin-top:2pt; line-height:1.2; }
.tchr { font-size:7pt; color:#666; display:block; }
.hdr  { border-bottom:2px solid #3c8dbc; margin-bottom:6pt; padding-bottom:5pt; text-align:center; }
.foot { text-align:right; font-size:6.5pt; color:#aaa; margin-top:4pt; }
</style></head><body>';

        $first = true;
        foreach ($grouped as $class_data) {
            if (!$first) {
                $html .= '<pagebreak orientation="landscape" />';
            }
            $first = false;

            // Per-class entry map
            $entry_map = [];
            foreach ($class_data['entries'] as $e) {
                $entry_map[$e->day][$e->period_id][] = $e;
            }

            // Header
            if ($img_local) {
                $html .= '<div class="hdr"><img src="' . $img_local . '" style="width:100%;height:22mm;" alt=""></div>';
            } elseif ($school_name) {
                $html .= '<div class="hdr"><span style="font-size:13pt;font-weight:bold;color:#2c3e50;">'
                       . htmlspecialchars($school_name) . '</span></div>';
            }

            $html .= '<h2>' . htmlspecialchars($class_data['class'] . ' &mdash; Section ' . $class_data['section']) . '</h2>';
            $html .= '<h3>Academic Timetable &nbsp;|&nbsp; Generated: ' . date('d M Y') . '</h3>';

            // Table
            $html .= '<table><thead><tr><th class="tc">Period</th>';
            foreach ($day_names as $dn) { $html .= '<th>' . htmlspecialchars($dn) . '</th>'; }
            $html .= '</tr></thead><tbody>';

            foreach ($periods as $p) {
                $t = date('g:i', strtotime($p->start_time)) . '–' . date('g:i A', strtotime($p->end_time));
                if ($p->is_break) {
                    $html .= '<tr class="brk"><td class="tc"><b>' . htmlspecialchars($p->name) . '</b><br><small>' . $t . '</small></td>'
                           . '<td colspan="' . $n_days . '">' . htmlspecialchars($p->break_label ?: $p->name) . '</td></tr>';
                    continue;
                }
                $html .= '<tr><td class="tc"><b>' . htmlspecialchars($p->name) . '</b><br><small>' . $t . '</small></td>';
                foreach ($day_names as $dn) {
                    $entries_cell = $entry_map[$dn][$p->id] ?? [];
                    if (empty($entries_cell)) { $html .= '<td></td>'; continue; }
                    $html .= '<td>';
                    foreach ($entries_cell as $e) {
                        if ($e->is_free_period) {
                            $html .= '<span class="stag" style="background:#27ae60;">'
                                   . htmlspecialchars(mb_substr($e->free_period_label ?: 'Free', 0, 20)) . '</span>';
                        } else {
                            $badge = !empty($e->tt_abbr) ? $e->tt_abbr : ($e->subject_code ?: $e->subject_name);
                            $bg    = !empty($e->tt_color) ? $e->tt_color
                                   : ($type_bg[strtolower($e->subject_type ?? 'other')] ?? '#7f8c8d');
                            $lum   = isset($bg[1]) ? (hexdec(substr($bg,1,2))*0.299 + hexdec(substr($bg,3,2))*0.587 + hexdec(substr($bg,5,2))*0.114)/255 : 0;
                            $txt   = $lum > 0.55 ? '#222' : '#fff';
                            $sname = mb_strlen($e->subject_name ?: '') > 22 ? mb_substr($e->subject_name,0,21).'…' : ($e->subject_name ?: '');
                            $tname = mb_strtoupper(mb_substr($e->staff_name ?? '', 0, 10));
                            $html .= '<span class="stag" style="background:' . $bg . ';color:' . $txt . ';">'
                                   . htmlspecialchars($badge) . '</span>';
                            if ($sname) $html .= '<span class="subj">' . htmlspecialchars($sname) . '</span>';
                            if ($tname) $html .= '<span class="tchr">' . htmlspecialchars($tname) . '</span>';
                        }
                    }
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            $html .= '<p class="foot">Printed: ' . date('d M Y, h:i A') . '</p>';
        }

        $html .= '</body></html>';

        $this->load->library('m_pdf');
        $mpdf = $this->m_pdf->load([
            'tempDir'       => sys_get_temp_dir() . '/mpdf',
            'mode'          => 'utf-8',
            'default_font'  => 'dejavusans',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 6,
            'margin_bottom' => 6,
            'format'        => 'A4-L',
            'orientation'   => 'L',
        ]);
        $mpdf->WriteHTML($html);
        $dept_label = $dept_id > 0 ? '_dept' . $dept_id : '_all';
        $mpdf->Output('timetable_report' . $dept_label . '_' . date('Ymd') . '.pdf', 'D');
        exit;
    }

    public function get_room_utilization()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $dept_id    = (int) $this->input->post('dept_id') ?: null;
        $data    = $this->Tt_entry_model->getRoomUtilization($session_id, $dept_id);
        $periods = $this->Tt_period_model->getAllNonBreak($session_id);
        $rooms   = $this->Tt_room_model->getActive();
        $days    = $this->_getWorkingDays();
        $html = $this->load->view('admin/tt/_report_rooms', compact('data','periods','rooms','days'), true);
        echo json_encode(['status' => '1', 'html' => $html]);
    }

    public function get_teacher_workload()
    {
        $session_id = $this->setting_model->getCurrentSession();
        $dept_id    = (int) $this->input->post('dept_id') ?: null;
        $data       = $this->Tt_entry_model->getTeacherWorkload($session_id, $dept_id);
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
        $data['departments'] = $this->department_model->getDepartmentsForSession($data['session_id']);
        $data['periods']     = $this->Tt_period_model->getAllNonBreak($data['session_id']);
        $all_days    = $this->customlib->getDaysnameWithoutLang();
        $sch_settings = $this->setting_model->getSetting();
        $weekend_str  = isset($sch_settings->weekend_days) ? (string) $sch_settings->weekend_days : '';
        if ($weekend_str !== '') {
            $dow_map = [0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];
            foreach (array_map('intval', explode(',', $weekend_str)) as $dow) {
                if (isset($dow_map[$dow])) unset($all_days[$dow_map[$dow]]);
            }
        }
        $data['days'] = $all_days;
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
        $rows = $this->Tt_class_unavail_model->getForClassSection($session_id, $class_id, $section_id);
        echo json_encode(['status' => '1', 'data' => $rows]);
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

        $all_days    = $this->customlib->getDaysnameWithoutLang();
        $sch_settings = $this->setting_model->getSetting();
        $weekend_str  = isset($sch_settings->weekend_days) ? (string) $sch_settings->weekend_days : '';
        if ($weekend_str !== '') {
            $dow_map = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
            foreach (array_map('intval', explode(',', $weekend_str)) as $dow) {
                if (isset($dow_map[$dow])) unset($all_days[$dow_map[$dow]]);
            }
        }
        $data['days'] = $all_days;
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
        $session_id  = $this->setting_model->getCurrentSession();
        $class_scope = json_decode($this->input->post('class_scope'), true);
        $time_limit  = max(60, min(600, (int) $this->input->post('time_limit') ?: 180));
        set_time_limit($time_limit + 120);
        $settings    = [
            'allow_saturday'           => (int) $this->input->post('allow_saturday'),
            'max_same_subject_day'     => (int) $this->input->post('max_same_subject_day') ?: 1,
            'spread_evenly'            => (int) $this->input->post('spread_evenly'),
            'fill_free_periods'        => (int) $this->input->post('fill_free_periods'),
            'respect_soft_constraints' => 1,
            'time_limit'               => $time_limit,
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
                $cls_row   = $this->db->select('class')->where('id', $cs['class_id'])->get('classes')->row();
                $sec_row   = $this->db->select('section')->where('id', $cs['section_id'])->get('sections')->row();
                $cls_label = ($cls_row && $sec_row) ? "{$cls_row->class} {$sec_row->section}" : "Class {$cs['class_id']}";

                $total_ppw   = 0;
                $missing_teacher = 0;
                $orphaned = 0;
                foreach ($loads as $l) {
                    if ($l->batch_id) continue;
                    // subject_id comes from a LEFT JOIN through subject_group_subjects —
                    // null means that row was deleted from the class's curriculum (e.g.
                    // via Subject Groups) but the tt_subject_load row referencing it was
                    // never cleaned up. Ignore it rather than count/schedule a ghost.
                    if (empty($l->subject_id)) { $orphaned++; continue; }
                    $total_ppw += (int)$l->periods_per_week;
                    // teacher_ids is populated by _enrichWithTeachers; fall back to staff_id
                    $t_ids = !empty($l->teacher_ids) ? $l->teacher_ids : (!empty($l->staff_id) ? [$l->staff_id] : []);
                    if (empty($t_ids)) $missing_teacher++;
                    // Just collect which teachers are relevant here — their actual
                    // total comes from _getTeacherWorkloadTotals() below, which
                    // correctly counts joint lessons once instead of once per
                    // participating section.
                    foreach ($t_ids as $tid) {
                        $teacher_totals[$tid] = true;
                    }
                }
                $load_ok = ($total_ppw > 0 && $total_ppw <= $slot_count && $missing_teacher === 0 && $orphaned === 0);
                if (!$load_ok) $overall_ok = false;
                $msg = "{$cls_label}: {$total_ppw}/{$slot_count} slots assigned";
                if ($missing_teacher > 0) $msg .= " — {$missing_teacher} subject(s) missing teacher";
                if ($orphaned > 0) $msg .= " — {$orphaned} row(s) reference a subject removed from the curriculum — delete from Subject Load";
                if ($total_ppw > $slot_count) $msg .= " — OVERFLOW: more loads than slots";
                $items[] = ['ok' => $load_ok, 'msg' => $msg];
            }

            // 4. Teacher deep feasibility checks
            if (!empty($teacher_totals)) {
                $this->load->model('Tt_teacher_model');
                $constraints     = $this->Tt_teacher_model->getAllConstraintsMap($session_id);
                $unavail_map     = $this->Tt_teacher_model->getUnavailabilityMap($session_id);
                $correct_totals  = $this->_getTeacherWorkloadTotals($session_id);

                // Staff name cache
                $staff_names = [];
                $staff_rows = $this->db->select('id, name, surname')->where_in('id', array_keys($teacher_totals))->get('staff')->result();
                foreach ($staff_rows as $sr) $staff_names[(int)$sr->id] = "{$sr->name} {$sr->surname}";

                // Count unavailable slots per teacher
                $teacher_unavail_count = [];
                foreach ($unavail_map as $tid => $days) {
                    $cnt = 0;
                    foreach ($days as $periods) $cnt += count($periods);
                    $teacher_unavail_count[$tid] = $cnt;
                }

                // Count how many classes each teacher is assigned to
                $teacher_class_list = [];
                foreach ($class_scope as $cs) {
                    $loads = $this->Tt_subjectload_model->getForClassSection($session_id, (int)$cs['class_id'], (int)$cs['section_id']);
                    $cls_row = $this->db->select('class')->where('id', $cs['class_id'])->get('classes')->row();
                    $sec_row = $this->db->select('section')->where('id', $cs['section_id'])->get('sections')->row();
                    $lbl = ($cls_row && $sec_row) ? "{$cls_row->class} {$sec_row->section}" : "C{$cs['class_id']}";
                    foreach ($loads as $l) {
                        if ($l->batch_id || empty($l->subject_id)) continue;
                        $t_ids = !empty($l->teacher_ids) ? $l->teacher_ids : (!empty($l->staff_id) ? [(int)$l->staff_id] : []);
                        foreach ($t_ids as $tid) {
                            $teacher_class_list[(int)$tid][] = $lbl;
                        }
                    }
                }

                foreach (array_keys($teacher_totals) as $tid) {
                    $total    = $correct_totals[$tid] ?? 0;
                    $max_week = isset($constraints[$tid]) ? (int)$constraints[$tid]->max_periods_per_week : 36;
                    $max_day  = isset($constraints[$tid]) ? (int)$constraints[$tid]->max_periods_per_day : 6;
                    $unavail  = $teacher_unavail_count[$tid] ?? 0;
                    $avail_slots = $slot_count - $unavail;
                    $tname = $staff_names[$tid] ?? "Staff #{$tid}";
                    $classes = $teacher_class_list[$tid] ?? [];
                    $n_classes = count(array_unique($classes));

                    // 4a. Weekly overload: assigned > max_per_week
                    if ($total > $max_week) {
                        $overall_ok = false;
                        $over = $total - $max_week;
                        $items[] = ['ok' => false, 'msg' => "<b>{$tname}</b>: assigned {$total} periods/week but max is {$max_week} — <b>reduce {$over} period(s)</b> or increase weekly cap"];
                    }

                    // 4b. Available slots too few: unavailability eats into capacity
                    if ($total > $avail_slots && $avail_slots < $slot_count) {
                        $overall_ok = false;
                        $items[] = ['ok' => false, 'msg' => "<b>{$tname}</b>: {$unavail} unavailable slots leaves only {$avail_slots} free — but assigned {$total} periods. Reduce load or remove unavailability"];
                    }

                    // 4c. Per-day feasibility: total > max_per_day × working_days
                    $day_capacity = $max_day * $day_count;
                    if ($total > $day_capacity) {
                        $overall_ok = false;
                        $items[] = ['ok' => false, 'msg' => "<b>{$tname}</b>: assigned {$total} periods but max {$max_day}/day × {$day_count} days = {$day_capacity} capacity — increase max/day or reduce load"];
                    }

                    // 4d. High sharing warning (not an error, but a risk flag)
                    if ($n_classes >= 6 && $total >= $max_week * 0.8) {
                        $pct = round($total / $max_week * 100);
                        $cls_str = implode(', ', array_unique($classes));
                        $items[] = ['ok' => true, 'msg' => "<b>{$tname}</b>: shared across {$n_classes} classes at {$pct}% capacity ({$total}/{$max_week}) — <small class='text-muted'>{$cls_str}</small>"];
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

    // =========================================================================
    // TIMETABLE VALIDATION HELPERS
    // =========================================================================

    private function _getWorkingDayCount()
    {
        $this->load->library('Customlib');
        $days_map = $this->customlib->getDaysnameWithoutLang();
        return count(array_filter(array_keys($days_map), fn($d) => $d !== 'Sunday'));
    }

    private function _getTotalSlots($session_id)
    {
        $period_count = $this->db->where('session_id', $session_id)
            ->where('is_break', 0)->count_all_results('tt_periods');
        return $this->_getWorkingDayCount() * $period_count;
    }

    private function _getClassTotalPPW($session_id, $class_id, $section_id)
    {
        $regular = $this->db->select_sum('periods_per_week')
            ->where('session_id', $session_id)
            ->where('class_id', $class_id)->where('section_id', $section_id)
            ->where('joint_lesson_id IS NULL')
            ->get('tt_subject_load')->row()->periods_per_week ?? 0;

        $joint = 0;
        $joint_rows = $this->db->query("
            SELECT SUM(jl.periods_per_week) as total
            FROM tt_joint_lessons jl
            JOIN tt_joint_lesson_classes jlc ON jlc.joint_lesson_id = jl.id
            WHERE jl.session_id = ? AND jlc.class_id = ? AND jlc.section_id = ?
        ", [$session_id, $class_id, $section_id])->row();
        if ($joint_rows) $joint = (int)($joint_rows->total ?? 0);

        return (int)$regular + $joint;
    }

    private function _getTeacherWorkload($session_id, $staff_id)
    {
        $regular = $this->db->query("
            SELECT COALESCE(SUM(sl.periods_per_week), 0) as total
            FROM tt_subject_load sl
            JOIN tt_subject_load_teachers slt ON slt.subject_load_id = sl.id
            WHERE sl.session_id = ? AND slt.staff_id = ? AND sl.joint_lesson_id IS NULL
        ", [$session_id, $staff_id])->row()->total ?? 0;

        $joint = $this->db->query("
            SELECT COALESCE(SUM(jl.periods_per_week), 0) as total
            FROM tt_joint_lessons jl
            JOIN tt_joint_lesson_teachers jlt ON jlt.joint_lesson_id = jl.id
            WHERE jl.session_id = ? AND jlt.staff_id = ?
        ", [$session_id, $staff_id])->row()->total ?? 0;

        return (int)$regular + (int)$joint;
    }

    private function _getTeacherConstraint($session_id, $staff_id)
    {
        $row = $this->db->where('session_id', $session_id)->where('staff_id', $staff_id)
            ->get('tt_teacher_constraints')->row();
        return [
            'max_per_day'  => $row ? (int)$row->max_periods_per_day : 6,
            'max_per_week' => $row ? (int)$row->max_periods_per_week : 36,
        ];
    }

    private function _getTeacherUnavailCount($session_id, $staff_id)
    {
        return $this->db->where('session_id', $session_id)->where('staff_id', $staff_id)
            ->count_all_results('tt_teacher_unavail');
    }

    private function _getTeacherName($staff_id)
    {
        $r = $this->db->select('name, surname')->where('id', $staff_id)->get('staff')->row();
        return $r ? trim($r->name . ' ' . $r->surname) : "Staff #{$staff_id}";
    }

    private function _getClassName($class_id, $section_id)
    {
        $c = $this->db->select('class')->where('id', $class_id)->get('classes')->row();
        $s = $this->db->select('section')->where('id', $section_id)->get('sections')->row();
        return ($c ? $c->class : '') . ' ' . ($s ? $s->section : '');
    }

    private function _getSubjectsForGrid($session_id, $class_id, $section_id)
    {
        return $this->db->query("
            SELECT sgs.id as subject_group_subject_id, sgs.subject_group_id,
                   sgs.subject_id, sub.name as subject_name, sub.code as subject_code,
                   sub.type as subject_type, sg.name as group_name
            FROM subject_group_subjects sgs
            INNER JOIN subjects sub ON sub.id = sgs.subject_id
            INNER JOIN subject_groups sg ON sg.id = sgs.subject_group_id
            WHERE sg.class_id = ? AND sg.session_id = ? AND sgs.session_id = ?
            ORDER BY sub.name ASC
        ", [$class_id, $session_id, $session_id])->result();
    }
}
