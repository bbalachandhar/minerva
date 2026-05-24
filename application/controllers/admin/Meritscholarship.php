<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin: Merit exam marks entry and bulk scholarship assignment.
 *
 * MAT-SET tier mapping (scholarship_types IDs 16–20, total exam = 100 marks):
 *   Cat 1 (ID 16): 90–100% → ₹50,000
 *   Cat 2 (ID 17): 75–89%  → ₹30,000
 *   Cat 3 (ID 18): 60–74%  → ₹20,000
 *   Cat 4 (ID 19): 50–59%  → ₹10,000
 *   Cat 5 (ID 20):  0–49%  → ₹5,000  (standard – all who appeared below 50%)
 *
 * URL: admin/meritscholarship
 */
class Meritscholarship extends Admin_Controller
{
    /** MAT-SET scholarship type IDs keyed by tier ID. */
    private static $tiers = [
        16 => ['min' =>  90.00, 'max' => 100.00, 'amount' => 50000, 'label' => 'Cat 1 (90-100%)', 'color' => 'success'],
        17 => ['min' =>  75.00, 'max' =>  89.99, 'amount' => 30000, 'label' => 'Cat 2 (75-89%)',  'color' => 'primary'],
        18 => ['min' =>  60.00, 'max' =>  74.99, 'amount' => 20000, 'label' => 'Cat 3 (60-74%)',  'color' => 'info'],
        19 => ['min' =>  50.00, 'max' =>  59.99, 'amount' => 10000, 'label' => 'Cat 4 (50-59%)',  'color' => 'warning'],
        20 => ['min' =>   1.00, 'max' =>  49.99, 'amount' =>  5000, 'label' => 'Cat 5 (1-49%)',   'color' => 'default'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Scholarship_application_model');
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Determine tier scholarship_type_id from a percentage.
     * Returns null for score = 0 (exam not attended).
     * Returns 16–20 for scores 1–100.
     */
    private function _tier_id(float $pct): ?int
    {
        if ($pct <= 0) {
            return null; // 0% = did not attend — no scholarship
        }
        foreach (self::$tiers as $tid => $range) {
            if ($pct >= $range['min'] && $pct <= $range['max']) {
                return $tid;
            }
        }
        return 20; // safety fallback: Cat 5
    }

    // ── List / index ───────────────────────────────────────────────────────────

    public function index()
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Admissions');
        $this->session->set_userdata('sub_menu', 'admin/scholarshipapplication');

        $filter = $this->input->get('filter') ?: 'all';

        $q = $this->db
            ->select('oa.id, oa.reference_no, oa.firstname, oa.lastname, oa.email, oa.mobileno,
                      oa.mat_exam_score, oa.mat_exam_percentage,
                      sa.id AS sch_app_id, sa.status AS sch_status,
                      st.id AS sch_type_id, st.name AS sch_type_name, st.amount AS sch_amount')
            ->from('online_admissions oa')
            ->join(
                'scholarship_applications sa',
                'sa.online_admission_id = oa.id AND sa.scholarship_type_id IN (16,17,18,19,20)',
                'left'
            )
            ->join('scholarship_types st', 'st.id = sa.scholarship_type_id', 'left')
            ->where('oa.is_enroll', 0);

        if ($filter === 'with_score') {
            $q->where('oa.mat_exam_percentage IS NOT NULL');
        } elseif ($filter === 'unassigned') {
            $q->where('oa.mat_exam_percentage IS NOT NULL')->where('sa.id IS NULL');
        } elseif ($filter === 'assigned') {
            $q->where('sa.id IS NOT NULL');
        }

        $applicants = $q->order_by('oa.mat_exam_percentage IS NULL ASC, oa.mat_exam_percentage DESC', NULL, FALSE)
                        ->get()->result_array();

        // Annotate each row with computed tier (only if score present, > 0, and not yet assigned)
        foreach ($applicants as &$row) {
            if ($row['mat_exam_percentage'] !== null && (float)$row['mat_exam_percentage'] > 0 && $row['sch_app_id'] === null) {
                $tid            = $this->_tier_id((float) $row['mat_exam_percentage']);
                $row['tier_id'] = $tid;
                $row['tier']    = $tid !== null ? self::$tiers[$tid] : null;
            } else {
                $row['tier_id'] = null;
                $row['tier']    = null;
            }
        }
        unset($row);

        // Stats
        $with_score_cnt = count(array_filter($applicants, fn($r) => $r['mat_exam_percentage'] !== null));
        $assigned_cnt   = count(array_filter($applicants, fn($r) => !empty($r['sch_app_id'])));

        $data['applicants'] = $applicants;
        $data['filter']     = $filter;
        $data['tiers']      = self::$tiers;
        $data['stats']      = [
            'all'        => count($applicants),
            'with_score' => $with_score_cnt,
            'assigned'   => $assigned_cnt,
            'unassigned' => max(0, $with_score_cnt - $assigned_cnt),
        ];
        $data['title'] = 'Merit Scholarship – Exam Marks & Assignment';

        $this->load->view('layout/header', $data);
        $this->load->view('admin/scholarship/merit_exam_assign', $data);
        $this->load->view('layout/footer', $data);
    }

    // ── AJAX: save (or clear) a single applicant's score ──────────────────────

    public function save_score()
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $id    = (int) $this->input->post('id');
        $score = $this->input->post('score');

        if (!$id) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid applicant ID']);
            return;
        }

        if ($score === '' || $score === null || $score === false) {
            $this->db->where('id', $id)->update('online_admissions', [
                'mat_exam_score'      => null,
                'mat_exam_percentage' => null,
            ]);
            echo json_encode(['status' => 'ok', 'cleared' => true]);
            return;
        }

        $score = (float) $score;
        if ($score < 0 || $score > 100) {
            echo json_encode(['status' => 'error', 'msg' => 'Score must be between 0 and 100']);
            return;
        }

        $this->db->where('id', $id)->update('online_admissions', [
            'mat_exam_score'      => $score,
            'mat_exam_percentage' => $score, // total marks = 100, so % equals the score
        ]);

        $tid = $this->_tier_id($score);
        echo json_encode([
            'status'     => 'ok',
            'tier_id'    => $tid,
            'tier_label' => self::$tiers[$tid]['label'],
            'tier_color' => self::$tiers[$tid]['color'],
            'amount'     => self::$tiers[$tid]['amount'],
        ]);
    }

    // ── CSV bulk upload ────────────────────────────────────────────────────────

    /**
     * Expected CSV format (with or without header row):
     *   reference_no, score
     * Score is out of 100. Example:
     *   MCE2025001, 78
     *   MCE2025002, 45.5
     */
    public function bulk_upload()
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            access_denied();
        }

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('admin/meritscholarship');
            return;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">No file uploaded.</div>');
            redirect('admin/meritscholarship');
            return;
        }

        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if (!$handle) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Could not read uploaded file.</div>');
            redirect('admin/meritscholarship');
            return;
        }

        $updated = 0;
        $skipped = 0;
        $errors  = [];
        $line_no = 0;

        while (($cols = fgetcsv($handle)) !== false) {
            $line_no++;

            // Skip header row if present
            if ($line_no === 1 && in_array(strtolower(trim($cols[0])), ['reference_no', 'ref_no', 'referenceno', 'ref'], true)) {
                continue;
            }

            if (count($cols) < 2) {
                $skipped++;
                continue;
            }

            $ref_no    = trim($cols[0]);
            $score_raw = trim($cols[1]);

            if ($ref_no === '') {
                $skipped++;
                continue;
            }

            if (!is_numeric($score_raw)) {
                $errors[] = "Line {$line_no}: non-numeric score '{$score_raw}' for '{$ref_no}'";
                $skipped++;
                continue;
            }

            $score = (float) $score_raw;
            if ($score < 0 || $score > 100) {
                $errors[] = "Line {$line_no}: score {$score} out of range [0–100] for '{$ref_no}'";
                $skipped++;
                continue;
            }

            $applicant = $this->db
                ->select('id')
                ->where('reference_no', $ref_no)
                ->get('online_admissions')
                ->row_array();

            if (!$applicant) {
                $errors[] = "Line {$line_no}: reference_no '{$ref_no}' not found";
                $skipped++;
                continue;
            }

            $this->db->where('id', $applicant['id'])->update('online_admissions', [
                'mat_exam_score'      => $score,
                'mat_exam_percentage' => $score,
            ]);
            $updated++;
        }
        fclose($handle);

        $msg_parts  = ["Updated <strong>{$updated}</strong> applicant(s). Skipped: {$skipped}."];
        if (!empty($errors)) {
            $shown = array_slice($errors, 0, 10);
            $msg_parts[] = '<br><strong>Errors:</strong><br>' . implode('<br>', $shown);
            $extra = count($errors) - 10;
            if ($extra > 0) {
                $msg_parts[] = "… and {$extra} more error(s)";
            }
        }

        $alert_type = ($skipped > 0 || !empty($errors)) ? 'warning' : 'success';
        $this->session->set_flashdata(
            'msg',
            "<div class='alert alert-{$alert_type}'>" . implode('', $msg_parts) . '</div>'
        );
        redirect('admin/meritscholarship?filter=with_score');
    }

    // ── AJAX: assign scholarship to a single applicant ────────────────────────

    public function assign_single()
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $id = (int) $this->input->post('id');
        if (!$id) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid applicant ID']);
            return;
        }

        $applicant = $this->db
            ->select('id, mat_exam_percentage, firstname, lastname')
            ->where('id', $id)
            ->get('online_admissions')
            ->row_array();

        if (!$applicant) {
            echo json_encode(['status' => 'error', 'msg' => 'Applicant not found']);
            return;
        }

        if ($applicant['mat_exam_percentage'] === null) {
            echo json_encode(['status' => 'error', 'msg' => 'No exam score recorded – enter score first']);
            return;
        }

        if ((float) $applicant['mat_exam_percentage'] <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Score is 0% — applicant did not attend the exam. No scholarship applicable.']);
            return;
        }

        // One scholarship per applicant – check for any existing application
        $existing = $this->db
            ->where('online_admission_id', $id)
            ->count_all_results('scholarship_applications');

        if ($existing > 0) {
            echo json_encode(['status' => 'error', 'msg' => 'This applicant already has a scholarship application']);
            return;
        }

        $tid         = $this->_tier_id((float) $applicant['mat_exam_percentage']);
        $approver_id = (int) $this->session->userdata('id');
        $now         = date('Y-m-d H:i:s');

        $this->Scholarship_application_model->insert([
            'online_admission_id' => $id,
            'scholarship_type_id' => $tid,
            'status'              => 'approved',
            'approver_id'         => $approver_id,
            'approved_at'         => $now,
            'applicant_remarks'   => 'Auto-assigned via Merit Exam score (' . $applicant['mat_exam_percentage'] . '%)',
        ]);

        echo json_encode([
            'status'     => 'ok',
            'tier_id'    => $tid,
            'tier_label' => self::$tiers[$tid]['label'],
            'tier_color' => self::$tiers[$tid]['color'],
            'amount'     => self::$tiers[$tid]['amount'],
            'msg'        => 'Scholarship assigned successfully',
        ]);
    }

    // ── Bulk assign all eligible applicants ───────────────────────────────────

    public function assign_all()
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_add')) {
            access_denied();
        }

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('admin/meritscholarship');
            return;
        }

        // Eligible: have exam score > 0 (attended exam) AND have NO existing scholarship_applications row
        $eligible = $this->db
            ->select('oa.id, oa.mat_exam_percentage')
            ->from('online_admissions oa')
            ->join('scholarship_applications sa', 'sa.online_admission_id = oa.id', 'left')
            ->where('oa.mat_exam_percentage IS NOT NULL')
            ->where('oa.mat_exam_percentage >', 0)
            ->where('oa.is_enroll', 0)
            ->where('sa.id IS NULL')
            ->get()
            ->result_array();

        $count       = 0;
        $approver_id = (int) $this->session->userdata('id');
        $now         = date('Y-m-d H:i:s');

        foreach ($eligible as $row) {
            $tid = $this->_tier_id((float) $row['mat_exam_percentage']);
            $this->Scholarship_application_model->insert([
                'online_admission_id' => (int) $row['id'],
                'scholarship_type_id' => $tid,
                'status'              => 'approved',
                'approver_id'         => $approver_id,
                'approved_at'         => $now,
                'applicant_remarks'   => 'Auto-assigned via Merit Exam score (' . $row['mat_exam_percentage'] . '%)',
            ]);
            $count++;
        }

        $this->session->set_flashdata(
            'msg',
            "<div class='alert alert-success'><i class='fa fa-check'></i> Scholarship assigned to <strong>{$count}</strong> applicant(s).</div>"
        );
        redirect('admin/meritscholarship?filter=assigned');
    }

    // ── Sample CSV download ───────────────────────────────────────────────────

    /**
     * Streams a sample CSV file pre-populated with all current applicant
     * reference numbers (and a blank score column) so staff only need to
     * fill in the score column and re-upload.
     */
    public function sample_csv()
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_view')) {
            access_denied();
        }

        // Fetch all applicants who have not yet been assigned a scholarship
        $rows = $this->db
            ->select('oa.reference_no, oa.firstname, oa.lastname')
            ->from('online_admissions oa')
            ->join('scholarship_applications sa', 'sa.online_admission_id = oa.id AND sa.scholarship_type_id IN (16,17,18,19,20)', 'left')
            ->where('oa.is_enroll', 0)
            ->where('sa.id IS NULL')
            ->order_by('oa.reference_no', 'ASC')
            ->get()
            ->result_array();

        $filename = 'merit_exam_scores_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // Header row
        fputcsv($out, ['reference_no', 'score', 'applicant_name']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['reference_no'],
                '',   // blank score – to be filled by staff
                trim($row['firstname'] . ' ' . $row['lastname']),
            ]);
        }

        // If no applicants, add an example row
        if (empty($rows)) {
            fputcsv($out, ['MCE2025001', '78', 'Example Applicant']);
        }

        fclose($out);
        exit;
    }
}
