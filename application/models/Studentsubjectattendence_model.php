<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Studentsubjectattendence_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date    = $this->setting_model->getDateYmd();
    }

    public function add($insert_array, $update_array)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        if (!empty($insert_array)) {
            $this->db->insert_batch('student_subject_attendances', $insert_array);
        }
        if (!empty($update_array)) {
            $this->db->update_batch('student_subject_attendances', $update_array, 'id');
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }    

    public function addorUpdate($attendances)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);      

        if(!empty($attendances)){
            foreach ($attendances as $attendance_key => $attendance_value) {                                       
                $this->db->where('student_session_id',  $attendance_value['student_session_id']);
                $this->db->where('subject_timetable_id',  $attendance_value['subject_timetable_id']);
                $this->db->where('date', $attendance_value['date']);
                $query = $this->db->get('student_subject_attendances');                
                if ($query->num_rows() > 0) {
                    // Record exists, update it
                    $this->db->where('id', $query->row()->id);
                    $this->db->update('student_subject_attendances', $attendance_value);
                } else {
                    // Record does not exist, insert a new one
                    $this->db->insert('student_subject_attendances', $attendance_value);
                }

                }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }    
    }

    public function searchAttendenceClassSection($class_id, $section_id, $subject_timetable_id, $date)
    {
        $sql   = "SELECT  IFNULL(student_subject_attendances.id, '0') as student_subject_attendance_id,student_subject_attendances.subject_timetable_id,student_subject_attendances.attendence_type_id, IFNULL(student_subject_attendances.date, 'xxx') as date,student_subject_attendances.remark,students.*,student_session.id as student_session_id FROM students INNER JOIN student_session on students.id=student_session.student_id and student_session.class_id=" . $this->db->escape($class_id) . " and student_session.section_id =" . $this->db->escape($section_id) . "  AND student_session.session_id=" . $this->db->escape($this->current_session) . " AND (student_session.is_alumni = 0 OR student_session.is_alumni IS NULL) LEFT JOIN student_subject_attendances on student_session.id=student_subject_attendances.student_session_id and student_subject_attendances.subject_timetable_id=" . $this->db->escape($subject_timetable_id) . " and date=" . $this->db->escape($date) . " where `students`.`is_active`='yes'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getStudentMontlyAttendence($class_id, $section_id, $from_date, $to_date, $student_id,$subject_id, $department_id = null)
    {
        $student_array = array();
        $student_array['students_attendances'] = array();   
             for ($i = strtotime($from_date); $i <= strtotime($to_date); $i+=86400) {

             $date_no=$date = date('d',$i);
            $date = date('Y-m-d',$i);
            $day = date('l', strtotime($date));

            $students_time_table = $this->searchByStudentAttendanceByDate($class_id, $section_id, $day, $date, $student_id,$subject_id, $department_id);
            $a                   = array();
            $a['date']           = $this->customlib->dateformat($date);
            $a['day']            = $day;
            $a['subjects']       = array();
            $a['attendances']    = array();

            if (!empty($students_time_table)) {
                $students_time_table = json_decode($students_time_table);

                $a['subjects'] = ($students_time_table->subjects);
                foreach ($students_time_table->student_record as $students_time_table_key => $students_time_table_value) {
                    $a['attendances'] = ($students_time_table->student_record[$students_time_table_key]);
                }
            }
            $student_array['students_attendances'][$date_no] = $a;
        }
        return $student_array;
    }

    public function searchByStudentAttendanceByDate($class_id, $section_id, $day, $date, $student_id,$subject_id, $department_id = null)
    {
        $sql = "SELECT subject_timetable.*,subjects.id as `subject_id`,subjects.name,subjects.code,subjects.type FROM `subject_timetable` INNER JOIN subject_group_subjects on subject_group_subjects.id=subject_timetable.subject_group_subject_id INNER JOIN subjects on subjects.id=subject_group_subjects.subject_id INNER JOIN classes ON subject_timetable.class_id = classes.id WHERE subject_timetable.class_id=" . $this->db->escape($class_id) . " AND subject_timetable.section_id=" . $this->db->escape($section_id) . " and subject_timetable.session_id=" . $this->db->escape($this->current_session) . " and subject_timetable.day=" . $this->db->escape($day);
         if($subject_id !=""){
            $sql .=" AND subjects.id=".$subject_id;
        }       
        if($department_id != null){
            $sql .=" AND classes.department_id=".$this->db->escape($department_id);
        }

        $query    = $this->db->query($sql);
        $subjects = $query->result();

        if (!empty($subjects)) {
            $count        = 1;
            $append_sql   = "";
            $append_param = "";
            foreach ($subjects as $subject_key => $subject_value) {
                $append_param .= ",student_subject_attendances_" . $count . ".attendence_type_id as attendence_type_id_" . $count;
                $append_sql .= " LEFT JOIN student_subject_attendances as student_subject_attendances_" . $count . " on  student_subject_attendances_" . $count . ".student_session_id=student_session.id and student_subject_attendances_" . $count . ".subject_timetable_id=" . $this->db->escape($subject_value->id) . " and student_subject_attendances_" . $count . ".date=" . $this->db->escape($date);
                $count++;
            }
            $sql_student_record = "SELECT students.id,students.firstname" . $append_param . " FROM `students` INNER JOIN student_session on students.id=student_session.student_id and student_session.class_id=" . $this->db->escape($class_id) . " AND student_session.section_id=" . $this->db->escape($section_id) . " AND student_session.session_id=" . $this->db->escape($this->current_session) . " INNER JOIN classes ON student_session.class_id = classes.id " . $append_sql . " WHERE students.id=" . $student_id;
            
            if($department_id != null){
                $sql_student_record .=" AND classes.department_id=".$this->db->escape($department_id);
            }

            $query              = $this->db->query($sql_student_record);
            $student_record     = $query->result();
            return json_encode(array('subjects' => $subjects, 'student_record' => $student_record));
        }

        return false;
    }

    public function studentAttendanceByDate($class_id, $section_id, $day, $date, $student_session_id)
    {
        $sql        = "SELECT subject_timetable.*,subject_group_subjects.subject_group_id,subjects.id as `subject_id`,subjects.name,subjects.code,subjects.type,student_subject_attendances.student_session_id,student_subject_attendances.attendence_type_id,student_subject_attendances.date,student_subject_attendances.remark,student_subject_attendances.id as `student_subject_attendance_id`,student_subject_attendances.date  FROM `subject_timetable` INNER JOIN subject_group_subjects on subject_group_subjects.id = subject_timetable.subject_group_subject_id and subject_group_subjects.session_id=" . $this->current_session . " INNER JOIN subjects on subjects.id=subject_group_subjects.subject_id LEFT JOIN student_subject_attendances on student_subject_attendances.subject_timetable_id=subject_timetable.id and student_subject_attendances.student_session_id=" . $this->db->escape($student_session_id) . " WHERE subject_timetable.class_id=" . $this->db->escape($class_id) . " AND subject_timetable.section_id=" . $this->db->escape($section_id) . " and subject_timetable.day=" . $this->db->escape($day) . "and student_subject_attendances.date=" . $this->db->escape($date);
        $query      = $this->db->query($sql);
        $attendance = $query->result();
        return $attendance;
    }

    public function getStudentsMontlyAttendence($class_id, $section_id, $from_date, $to_date,$subject_id, $department_id = null)
    {
        $student_array                   = array();
        $student_array['class_students'] = $this->student_model->searchByClassSectionWithSession($class_id, $section_id, $this->current_session, $department_id); // Pass department_id

        $student_array['students_attendances'] = array();
        for ($i = strtotime($from_date); $i <= strtotime($to_date); $i+=86400) {
            $date_no=$date = date('d',$i);
            $date = date('Y-m-d',$i);
            $day = date('l', strtotime($date));
            $students_time_table = $this->searchByStudentsAttendanceByDate($class_id, $section_id, $day, $date,$subject_id, $department_id); // Pass department_id
            $a             = array();
            $a['date']     = $date;
            $a['day']      = $day;
            $a['subjects'] = array();
            $a['students'] = array();

            if (!empty($students_time_table)) {
                $students_time_table = json_decode($students_time_table);

                $a['subjects'] = ($students_time_table->subjects);
                foreach ($students_time_table->student_record as $students_time_table_key => $students_time_table_value) {
                    $a['students'][$students_time_table_value->id] = ($students_time_table->student_record[$students_time_table_key]);
                }
            }
            $student_array['students_attendances'][$date_no] = $a;
        }

        return $student_array;
    }

    public function searchByStudentsAttendanceByDate($class_id, $section_id, $day, $date,$subject_id, $department_id = null)
    {
        $sql = "SELECT subject_timetable.*,subjects.id as `subject_id`,subjects.name,subjects.code,subjects.type FROM `subject_timetable` INNER JOIN subject_group_subjects on subject_group_subjects.id=subject_timetable.subject_group_subject_id INNER JOIN subjects on subjects.id=subject_group_subjects.subject_id INNER JOIN classes ON subject_timetable.class_id = classes.id WHERE subject_timetable.class_id=" . $this->db->escape($class_id) . " AND subject_timetable.section_id=" . $this->db->escape($section_id) . " and subject_timetable.session_id=" . $this->db->escape($this->current_session) . " and subject_timetable.day=" . $this->db->escape($day);
        if($subject_id !=""){
            $sql .=" AND subjects.id=".$subject_id;
        }       
        if($department_id != null){
            $sql .=" AND classes.department_id=".$this->db->escape($department_id);
        }

        $query = $this->db->query($sql);

        $subjects = $query->result();

        if (!empty($subjects)) {
            $count        = 1;
            $append_sql   = "";
            $append_param = "";
            foreach ($subjects as $subject_key => $subject_value) {
                $append_param .= ",student_subject_attendances_" . $count . ".attendence_type_id as attendence_type_id_" . $count;
                $append_sql .= " LEFT JOIN student_subject_attendances as student_subject_attendances_" . $count . " on  student_subject_attendances_" . $count . ".student_session_id=student_session.id and student_subject_attendances_" . $count . ".subject_timetable_id=" . $this->db->escape($subject_value->id) . " and student_subject_attendances_" . $count . ".date=" . $this->db->escape($date);
                $count++;
            }
            $sql_student_record = "SELECT students.id,students.firstname,students.middlename,students.lastname,students.admission_no " . $append_param . " FROM `students` INNER JOIN student_session on students.id=student_session.student_id and student_session.class_id=" . $this->db->escape($class_id) . " AND student_session.section_id=" . $this->db->escape($section_id) . " AND student_session.session_id=" . $this->db->escape($this->current_session) . " INNER JOIN classes ON student_session.class_id = classes.id " . $append_sql . "where students.is_active = 'yes' AND (student_session.is_alumni = 0 OR student_session.is_alumni IS NULL)";
            
            if($department_id != null){
                $sql_student_record .=" AND classes.department_id=".$this->db->escape($department_id);
            }

            $query              = $this->db->query($sql_student_record);
            $student_record     = $query->result();
            return json_encode(array('subjects' => $subjects, 'student_record' => $student_record));
        }

        return false;
    }

    // ── Report: Class Day Matrix (all students × all periods for a date) ──

    public function getClassDayMatrix($class_id, $section_id, $date, $session)
    {
        $day = date('l', strtotime($date));

        // 1. Periods scheduled today for this class-section
        $period_sql = "SELECT DISTINCT st.id, st.time_from, st.time_to, st.start_time,
                              subj.name AS subject_name, subj.code AS subject_code,
                              TRIM(CONCAT(stf.name,' ',IFNULL(stf.surname,''))) AS teacher_name
                       FROM subject_timetable st
                       JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                       JOIN subjects subj ON subj.id = sgs.subject_id
                       LEFT JOIN staff stf ON stf.id = st.staff_id
                       WHERE st.class_id = " . $this->db->escape($class_id) . "
                         AND st.section_id = " . $this->db->escape($section_id) . "
                         AND st.day = " . $this->db->escape($day) . "
                         AND st.session_id = " . $this->db->escape($session) . "
                       ORDER BY st.start_time ASC";
        $periods = $this->db->query($period_sql)->result_array();

        // 2. All students in this class-section
        $student_sql = "SELECT ss.id AS student_session_id, s.id AS student_id,
                               s.firstname, s.middlename, s.lastname,
                               s.roll_no, s.admission_no
                        FROM student_session ss
                        JOIN students s ON s.id = ss.student_id AND s.is_active = 'yes'
                        WHERE ss.class_id = " . $this->db->escape($class_id) . "
                          AND ss.section_id = " . $this->db->escape($section_id) . "
                          AND ss.session_id = " . $this->db->escape($session) . "
                          AND (ss.is_alumni = 0 OR ss.is_alumni IS NULL)
                        ORDER BY CAST(s.roll_no AS UNSIGNED) ASC, s.admission_no ASC";
        $students = $this->db->query($student_sql)->result_array();

        // 3. All attendance records for this date / class / section
        $att_sql = "SELECT ssa.student_session_id, ssa.subject_timetable_id, ssa.attendence_type_id, ssa.remark
                    FROM student_subject_attendances ssa
                    JOIN student_session ss ON ss.id = ssa.student_session_id
                    WHERE ss.class_id = " . $this->db->escape($class_id) . "
                      AND ss.section_id = " . $this->db->escape($section_id) . "
                      AND ss.session_id = " . $this->db->escape($session) . "
                      AND ssa.date = " . $this->db->escape($date);
        $att_rows = $this->db->query($att_sql)->result_array();

        // 4. Build lookup: [student_session_id][subject_timetable_id] => type_id
        $att_map = [];
        foreach ($att_rows as $a) {
            $att_map[$a['student_session_id']][$a['subject_timetable_id']] = $a['attendence_type_id'];
        }

        return compact('periods', 'students', 'att_map');
    }

    // ── Report: subject attendance matrix (reportbymonth redesign) ─

    public function getStudentSubjectMatrix($class_id, $section_id, $from_date, $to_date, $subject_id = null, $department_id = null)
    {
        $subject_filter = $subject_id ? " AND subj.id = " . $this->db->escape($subject_id) : "";
        $dept_filter    = $department_id ? " AND ss.department_id = " . $this->db->escape($department_id) : "";

        $sql = "SELECT
            ss.id AS student_session_id,
            students.firstname, students.middlename, students.lastname,
            students.admission_no, students.roll_no,
            subj.id   AS subject_id,
            subj.name AS subject_name,
            subj.code AS subject_code,
            COUNT(DISTINCT ssa.id) AS total_periods,
            SUM(CASE WHEN ssa.attendence_type_id = 1 THEN 1 ELSE 0 END) AS present_count,
            SUM(CASE WHEN ssa.attendence_type_id = 4 THEN 1 ELSE 0 END) AS absent_count,
            ROUND(SUM(CASE WHEN ssa.attendence_type_id = 1 THEN 100.0 ELSE 0 END) / NULLIF(COUNT(DISTINCT ssa.id),0), 1) AS pct
          FROM student_session ss
          JOIN students ON students.id = ss.student_id AND students.is_active = 'yes'
          JOIN student_subject_attendances ssa ON ssa.student_session_id = ss.id
            AND ssa.date >= " . $this->db->escape($from_date) . "
            AND ssa.date <= " . $this->db->escape($to_date) . "
          JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
          JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
          JOIN subjects subj ON subj.id = sgs.subject_id
          WHERE ss.session_id = " . $this->db->escape($this->current_session) . "
            AND ss.class_id   = " . $this->db->escape($class_id) . "
            AND ss.section_id = " . $this->db->escape($section_id) . "
            AND (ss.is_alumni = 0 OR ss.is_alumni IS NULL)
            $subject_filter
            $dept_filter
          GROUP BY ss.id, subj.id
          ORDER BY students.admission_no ASC, subj.name ASC";

        $rows = $this->db->query($sql)->result_array();

        // Reshape into matrix structure
        $students = []; $subjects = []; $matrix = []; $subject_totals = [];

        foreach ($rows as $r) {
            $sid = $r['student_session_id'];
            $xid = $r['subject_id'];

            if (!isset($students[$sid])) {
                $students[$sid] = [
                    'student_session_id' => $sid,
                    'firstname'  => $r['firstname'],
                    'middlename' => $r['middlename'],
                    'lastname'   => $r['lastname'],
                    'admission_no' => $r['admission_no'],
                    'roll_no'    => $r['roll_no'],
                ];
            }
            if (!isset($subjects[$xid])) {
                $subjects[$xid] = ['subject_id' => $xid, 'name' => $r['subject_name'], 'code' => $r['subject_code']];
            }

            $matrix[$sid][$xid] = [
                'total'   => (int) $r['total_periods'],
                'present' => (int) $r['present_count'],
                'absent'  => (int) $r['absent_count'],
                'pct'     => (float) $r['pct'],
            ];

            if (!isset($subject_totals[$xid])) {
                $subject_totals[$xid] = ['total' => 0, 'present' => 0];
            }
            $subject_totals[$xid]['total']   += (int) $r['total_periods'];
            $subject_totals[$xid]['present'] += (int) $r['present_count'];
        }

        foreach ($subject_totals as $xid => &$t) {
            $t['pct'] = $t['total'] > 0 ? round($t['present'] * 100 / $t['total'], 1) : 0;
        }

        return compact('students', 'subjects', 'matrix', 'subject_totals');
    }

    // ── Report: teacher marking coverage ────────────────────────────

    public function getTeacherMarkingCoverage($session, $from_date, $to_date, $staff_id = null)
    {
        $staff_filter = $staff_id ? " AND st.staff_id = " . $this->db->escape($staff_id) : "";

        $sql = "SELECT
            st.staff_id,
            TRIM(CONCAT(s.name,' ',IFNULL(s.surname,''))) AS teacher_name,
            s.employee_id, s.image,
            subj.name AS subject_name, subj.code AS subject_code,
            cl.class AS class_name, sec.section AS section_name,
            COUNT(DISTINCT CONCAT(st.id,'-',cal.dt)) AS scheduled_periods,
            COUNT(DISTINCT CASE WHEN ssa_check.subject_timetable_id IS NOT NULL
                THEN CONCAT(st.id,'-',cal.dt) END)   AS marked_periods
          FROM subject_timetable st
          JOIN staff s ON s.id = st.staff_id AND s.is_active = 1
          JOIN classes cl ON cl.id = st.class_id
          JOIN sections sec ON sec.id = st.section_id
          JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
          JOIN subjects subj ON subj.id = sgs.subject_id
          JOIN (
            SELECT DATE_ADD(" . $this->db->escape($from_date) . ", INTERVAL (t4*10000 + t3*1000 + t2*100 + t1*10 + t0) DAY) dt
            FROM
              (SELECT 0 t0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
               UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t0,
              (SELECT 0 t1 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
               UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
              (SELECT 0 t2 UNION SELECT 1 UNION SELECT 2) t2,
              (SELECT 0 t3) t3, (SELECT 0 t4) t4
            WHERE DATE_ADD(" . $this->db->escape($from_date) . ", INTERVAL (t4*10000 + t3*1000 + t2*100 + t1*10 + t0) DAY)
              BETWEEN " . $this->db->escape($from_date) . " AND " . $this->db->escape($to_date) . "
          ) cal ON DAYNAME(cal.dt) = st.day
          LEFT JOIN (
            SELECT DISTINCT subject_timetable_id, date FROM student_subject_attendances
            WHERE date BETWEEN " . $this->db->escape($from_date) . " AND " . $this->db->escape($to_date) . "
          ) ssa_check ON ssa_check.subject_timetable_id = st.id AND ssa_check.date = cal.dt
          WHERE st.session_id = " . $this->db->escape($session) . "
            $staff_filter
          GROUP BY st.staff_id, subj.id, st.class_id, st.section_id
          ORDER BY teacher_name, class_name, subject_name";

        return $this->db->query($sql)->result_array();
    }

    // ── Dashboard: period-wise analytics ──────────────────────────

    public function getDashboardTodayCoverage($session)
    {
        $day  = date('l');
        $date = date('Y-m-d');
        $sql  = "SELECT
            COUNT(DISTINCT st.id)                                                         AS total_periods,
            COUNT(DISTINCT CASE WHEN ssa_c.subject_timetable_id IS NOT NULL THEN st.id END) AS marked_periods,
            COUNT(DISTINCT CONCAT(st.class_id,'-',st.section_id))                        AS total_sections,
            COUNT(DISTINCT CASE WHEN ssa_c.subject_timetable_id IS NOT NULL THEN CONCAT(st.class_id,'-',st.section_id) END) AS marked_sections
          FROM subject_timetable st
          LEFT JOIN (
            SELECT DISTINCT subject_timetable_id FROM student_subject_attendances WHERE date = " . $this->db->escape($date) . "
          ) ssa_c ON ssa_c.subject_timetable_id = st.id
          WHERE st.day = " . $this->db->escape($day) . " AND st.session_id = " . $this->db->escape($session);
        return $this->db->query($sql)->row_array();
    }

    public function getDashboardTodayPresent($session)
    {
        $date = date('Y-m-d');
        $sql  = "SELECT
            COUNT(*) AS total_marked,
            SUM(CASE WHEN ssa.attendence_type_id = 1 THEN 1 ELSE 0 END) AS present_count
          FROM student_subject_attendances ssa
          JOIN student_session ss ON ss.id = ssa.student_session_id
          WHERE ssa.date = " . $this->db->escape($date) . " AND ss.session_id = " . $this->db->escape($session);
        return $this->db->query($sql)->row_array();
    }

    public function getDashboardTeacherStatus($session)
    {
        $day  = date('l');
        $date = date('Y-m-d');
        $sql  = "SELECT
            st.staff_id,
            TRIM(CONCAT(s.name,' ',IFNULL(s.surname,''))) AS teacher_name,
            s.image,
            COUNT(DISTINCT st.id)                                                              AS total_periods,
            COUNT(DISTINCT CASE WHEN ssa_c.subject_timetable_id IS NOT NULL THEN st.id END)   AS marked_periods,
            GROUP_CONCAT(
              CASE WHEN ssa_c.subject_timetable_id IS NULL
                THEN CONCAT(cl.class,' ',sec.section,' — ',subj.name,' (',st.time_from,')')
              END ORDER BY st.start_time SEPARATOR '|'
            ) AS pending_detail
          FROM subject_timetable st
          JOIN staff s ON s.id = st.staff_id AND s.is_active = 1
          JOIN classes cl ON cl.id = st.class_id
          JOIN sections sec ON sec.id = st.section_id
          JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
          JOIN subjects subj ON subj.id = sgs.subject_id
          LEFT JOIN (
            SELECT DISTINCT subject_timetable_id FROM student_subject_attendances WHERE date = " . $this->db->escape($date) . "
          ) ssa_c ON ssa_c.subject_timetable_id = st.id
          WHERE st.day = " . $this->db->escape($day) . " AND st.session_id = " . $this->db->escape($session) . "
          GROUP BY st.staff_id
          ORDER BY marked_periods ASC, total_periods DESC";
        return $this->db->query($sql)->result_array();
    }

    public function getDashboardHeatmap($session)
    {
        $day  = date('l');
        $date = date('Y-m-d');
        $sql  = "SELECT
            cl.id AS class_id, cl.class AS class_name,
            sec.id AS section_id, sec.section AS section_name,
            COUNT(DISTINCT st.id)                                                              AS total_periods,
            COUNT(DISTINCT CASE WHEN ssa_c.subject_timetable_id IS NOT NULL THEN st.id END)   AS marked_periods
          FROM subject_timetable st
          JOIN classes cl ON cl.id = st.class_id
          JOIN sections sec ON sec.id = st.section_id
          LEFT JOIN (
            SELECT DISTINCT subject_timetable_id FROM student_subject_attendances WHERE date = " . $this->db->escape($date) . "
          ) ssa_c ON ssa_c.subject_timetable_id = st.id
          WHERE st.day = " . $this->db->escape($day) . " AND st.session_id = " . $this->db->escape($session) . "
          GROUP BY st.class_id, st.section_id
          ORDER BY cl.id, sec.id";
        return $this->db->query($sql)->result_array();
    }

    public function getDashboardWeeklyTrend($session)
    {
        $sql = "SELECT
            ssa.date,
            COUNT(*) AS total_marked,
            SUM(CASE WHEN ssa.attendence_type_id = 1 THEN 1 ELSE 0 END) AS present_count,
            ROUND(SUM(CASE WHEN ssa.attendence_type_id = 1 THEN 100.0 ELSE 0 END) / COUNT(*), 1) AS pct
          FROM student_subject_attendances ssa
          JOIN student_session ss ON ss.id = ssa.student_session_id
          WHERE ssa.date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            AND ssa.date <= CURDATE()
            AND ss.session_id = " . $this->db->escape($session) . "
          GROUP BY ssa.date
          ORDER BY ssa.date";
        return $this->db->query($sql)->result_array();
    }

    public function getDashboardLowAttendance($session, $threshold = 75)
    {
        $sql = "SELECT
            students.id AS student_id,
            students.firstname, students.lastname, students.admission_no,
            cl.class AS class_name, sec.section AS section_name,
            ss.id AS student_session_id,
            COUNT(DISTINCT ssa.id) AS total_records,
            SUM(CASE WHEN ssa.attendence_type_id = 1 THEN 1 ELSE 0 END) AS present_count,
            ROUND(SUM(CASE WHEN ssa.attendence_type_id = 1 THEN 100.0 ELSE 0 END) / COUNT(DISTINCT ssa.id), 1) AS pct
          FROM student_session ss
          JOIN students ON students.id = ss.student_id AND students.is_active = 'yes'
          JOIN classes cl ON cl.id = ss.class_id
          JOIN sections sec ON sec.id = ss.section_id
          JOIN student_subject_attendances ssa ON ssa.student_session_id = ss.id
          WHERE ss.session_id = " . $this->db->escape($session) . "
            AND MONTH(ssa.date) = MONTH(CURDATE())
            AND YEAR(ssa.date)  = YEAR(CURDATE())
            AND (ss.is_alumni = 0 OR ss.is_alumni IS NULL)
          GROUP BY ss.id
          HAVING pct < " . (int) $threshold . "
          ORDER BY pct ASC
          LIMIT 500";
        return $this->db->query($sql)->result_array();
    }

    // ── End dashboard methods ──────────────────────────────────────

    public function attendanceYearCount()
    {
        $query = $this->db->select("distinct year(date) as year")->get("student_subject_attendances");
        return $query->result_array();
    }

    public function is_biometricAttendence()
    {
        $this->db->select('sch_settings.id,sch_settings.student_biometric,sch_settings.attendence_type,sch_settings.is_rtl,sch_settings.timezone,
          sch_settings.name,sch_settings.email,sch_settings.student_biometric,sch_settings.biometric_device,sch_settings.phone,languages.language,          sch_settings.address,sch_settings.dise_code,sch_settings.date_format,sch_settings.currency,sch_settings.currency_symbol,sch_settings.start_month,sch_settings.session_id,sch_settings.image,sch_settings.theme,sessions.session'
        );

        $this->db->from('sch_settings');
        $this->db->join('sessions', 'sessions.id = sch_settings.session_id');
        $this->db->join('languages', 'languages.id = sch_settings.lang_id');
        $this->db->order_by('sch_settings.id');
        $query  = $this->db->get();
        $result = $query->row();

        if ($result->student_biometric) {
            return true;
        }

        return false;
    }

}
