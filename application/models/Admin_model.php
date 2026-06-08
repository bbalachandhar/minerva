<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Admin_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->current_session      = $this->setting_model->getCurrentSession();
        $this->current_session_name = $this->setting_model->getCurrentSessionName();
        $this->start_month          = $this->setting_model->getStartMonth();
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get($id = null)
    {
        $this->db->select()->from('admin');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    /**
     * This function will delete the record based on the id
     * @param $id
     */
    public function remove($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('admin');
    }

    /**
     * This function will take the post data passed from the controller
     * If id is present, then it will do an update
     * else an insert. One function doing both add and edit.
     * @param $data
     */
    public function add($data)
    {
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('admin', $data);
        } else {
            $this->db->insert('admin', $data);
        }
    }

    public function checkLogin($data)
    {
        $this->db->select('id, username, password');
        $this->db->from('admin');
        $this->db->where('email', $data['username']);
        $this->db->where('password', MD5($data['password']));
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function read_user_information($email)
    {
        $condition = "email =" . "'" . $email . "'";
        $this->db->select('*');
        $this->db->from('admin');
        $this->db->where($condition);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function readByEmail($email)
    {
        $condition = "email =" . "'" . $email . "'";
        $this->db->select('*');
        $this->db->from('admin');
        $this->db->where($condition);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function updateVerCode($data)
    {
        $this->db->where('id', $data['id']);
        $query = $this->db->update('admin', $data);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function getAdminByCode($ver_code)
    {
        $condition = "verification_code =" . "'" . $ver_code . "'";
        $this->db->select('*');
        $this->db->from('admin');
        $this->db->where($condition);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function change_password($data)
    {
        $condition = "id =" . "'" . $data['id'] . "'";
        $this->db->select('password');
        $this->db->from('admin');
        $this->db->where($condition);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function checkOldPass($data)
    {
        $this->db->where('id', $data['user_id']);
        $this->db->where('email', $data['user_email']);
        $query = $this->db->get('staff');

        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function saveNewPass($data)
    {
        $this->db->where('id', $data['id']);
        $query = $this->db->update('staff', $data);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function saveForgotPass($data)
    {
        $this->db->where('email', $data['email']);
        $query = $this->db->update('admin', $data);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function addReceipt($data)
    {
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('fee_receipt_no', $data);
        } else {
            $this->db->insert('fee_receipt_no', $data);
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }
    }

    public function getMonthlyCollection()
    {
        $data        = explode("-", $this->current_session_name);
        $data_first  = $data[0];
        $data_second = substr($data_first, 0, 2) . $data[1];
        $this->start_month;
        $sql   = "SELECT SUM(amount+amount_fine-amount_discount) as amount,MONTH(date) as month ,YEAR(date) as year FROM student_fees where YEAR(date) BETWEEN " . $this->db->escape($data_first) . " and " . $this->db->escape($data_second) . " GROUP BY MONTH(date)";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getMonthlyExpense()
    {
        $data        = explode("-", $this->current_session_name);
        $data_first  = $data[0];
        $data_second = substr($data_first, 0, 2) . $data[1];
        $this->start_month;
        $sql   = "SELECT SUM(amount) as amount,MONTH(date) as month ,YEAR(date) as year FROM expenses where YEAR(date) BETWEEN " . $this->db->escape($data_first) . " and " . $this->db->escape($data_second) . " GROUP BY MONTH(date)";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getCollectionbyDay($date)
    {
        $sql   = 'SELECT SUM(amount+amount_fine-amount_discount) as amount FROM student_fees where date=' . $this->db->escape($date);
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getExpensebyDay($date)
    {
        $sql = 'SELECT SUM(amount) as amount FROM expenses where date=' . $this->db->escape($date);
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getAllEnquiryCount($start_date = null, $end_date = null)
    {
        $session_id = $this->setting_model->getOnlineAdmissionSessionId();
        $this->db->select("SUM(CASE WHEN status = 'application_done' THEN 1  ELSE 0 END) AS 'complete',SUM(CASE WHEN status = 'active' THEN 1  ELSE 0 END) AS 'active',SUM(CASE WHEN status = 'passive' THEN 1  ELSE 0 END) AS 'passive',SUM(CASE WHEN status = 'dead' THEN 1  ELSE 0 END) AS 'dead',SUM(CASE WHEN status = 'lost' THEN 1  ELSE 0 END) AS 'lost',count(*) as total")->from('enquiry');
        $this->db->where('session_id', $session_id);

        if (!empty($start_date) && !empty($end_date)) {
            $condition = " date_format(date,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
            $this->db->where($condition);
        }

        return $this->db->get()->row_array();
    }

    public function getOnlineStudentPaymentOverview()
    {
        $session_id = $this->setting_model->getOnlineAdmissionSessionId();
        $students = $this->db->select('reference_no, course_fee_total, paid_status')
            ->from('online_admissions')
            ->where('session_id', $session_id)
            ->where("COALESCE(admission_status, 'active') IN ('active', 'waiting_list')", null, false)
            ->get()
            ->result_array();

        // Count revoked and waiting_list separately
        $revoked_count = (int) $this->db
            ->where('session_id', $session_id)
            ->where("COALESCE(admission_status, 'active') = 'cancelled'", null, false)
            ->count_all_results('online_admissions');

        $waiting_list_count = (int) $this->db
            ->where('session_id', $session_id)
            ->where("admission_status = 'waiting_list'", null, false)
            ->count_all_results('online_admissions');

        if (empty($students)) {
            return array(
                'applications_total'      => 0,
                'fully_paid'              => 0,
                'partially_paid'          => 0,
                'not_paid'                => 0,
                'fully_paid_progress'     => 0,
                'partially_paid_progress' => 0,
                'not_paid_progress'       => 0,
                'revoked'                 => $revoked_count,
                'waiting_list'            => $waiting_list_count,
            );
        }

        $reference_nos = array();
        foreach ($students as $student) {
            $ref = preg_replace('/\s+/', '', (string) ($student['reference_no'] ?? ''));
            if ($ref !== '') {
                $reference_nos[] = $ref;
            }
        }
        $reference_nos = array_values(array_unique($reference_nos));

        // Find reference_nos where APPLICATION FEE has been paid
        $app_fee_refs = array();
        if (!empty($reference_nos)) {
            $escaped_refs = array_map(array($this->db, 'escape'), $reference_nos);
            $in_list = implode(', ', $escaped_refs);
            $app_fee_rows = $this->db
                ->select('REPLACE(incidental_fee_collections.application_ref_no, " ", "") as app_ref', false)
                ->from('incidental_fee_collections')
                ->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_collections.incidental_fee_type_id', 'left')
                ->where("REPLACE(incidental_fee_collections.application_ref_no, ' ', '') IN ($in_list)", null, false)
                ->where('incidental_fee_collections.application_ref_no IS NOT NULL', null, false)
                ->where('incidental_fee_collections.application_ref_no !=', '')
                ->where('LOWER(incidental_fee_types.title) LIKE "%application fee%"', null, false)
                ->where('incidental_fee_collections.amount_collected >', 0)
                ->group_by('REPLACE(incidental_fee_collections.application_ref_no, " ", "")', false)
                ->get();
            $app_fee_rows = $app_fee_rows ? $app_fee_rows->result_array() : array();

            foreach ($app_fee_rows as $row) {
                $app_fee_refs[] = (string) $row['app_ref'];
            }
            $app_fee_refs = array_values(array_unique($app_fee_refs));
        }

        // Build tuition/other fee paid_map for ALL reference_nos
        $paid_map = array();
        if (!empty($reference_nos)) {
            $escaped_refs = $escaped_refs ?? array_map(array($this->db, 'escape'), $reference_nos);
            $in_list = $in_list ?? implode(', ', $escaped_refs);
            $paid_rows = $this->db
                ->select('REPLACE(incidental_fee_collections.application_ref_no, " ", "") as app_ref, SUM(incidental_fee_collections.amount_collected) as paid_amount', false)
                ->from('incidental_fee_collections')
                ->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_collections.incidental_fee_type_id', 'left')
                ->where("REPLACE(incidental_fee_collections.application_ref_no, ' ', '') IN ($in_list)", null, false)
                ->where('incidental_fee_collections.application_ref_no IS NOT NULL', null, false)
                ->where('incidental_fee_collections.application_ref_no !=', '')
                ->where('(LOWER(incidental_fee_types.title) LIKE "%tuition%" OR LOWER(incidental_fee_types.title) LIKE "%tution%" OR LOWER(incidental_fee_types.title) LIKE "%other fee%")', null, false)
                ->group_by('REPLACE(incidental_fee_collections.application_ref_no, " ", "")', false)
                ->get();
            $paid_rows = $paid_rows ? $paid_rows->result_array() : array();

            foreach ($paid_rows as $row) {
                $paid_map[(string) $row['app_ref']] = (float) $row['paid_amount'];
            }
        }

        // Bucket all students into the 4 Course Fee Status categories
        $applications_total = count($students);
        $fully_paid   = 0;
        $partially_paid = 0;
        $applied      = 0; // app fee paid, no course fee paid yet
        $not_paid     = 0; // nothing paid at all

        foreach ($students as $student) {
            $ref         = preg_replace('/\s+/', '', (string) ($student['reference_no'] ?? ''));
            $course_fee  = (isset($student['course_fee_total']) && $student['course_fee_total'] !== null && $student['course_fee_total'] !== '') ? (float) $student['course_fee_total'] : 0;
            $paid_amount = isset($paid_map[$ref]) ? (float) $paid_map[$ref] : 0;
            $app_is_paid = in_array($ref, $app_fee_refs, true)
                        || (int) ($student['paid_status'] ?? 0) === 1;

            if ($course_fee > 0 && $paid_amount >= $course_fee) {
                $fully_paid++;
            } elseif ($paid_amount > 0) {
                $partially_paid++;
            } elseif ($app_is_paid) {
                $applied++;
            } else {
                $not_paid++;
            }
        }

        return array(
            'applications_total'      => $applications_total,
            'fully_paid'              => $fully_paid,
            'partially_paid'          => $partially_paid,
            'applied'                 => $applied,
            'not_paid'                => $not_paid,
            'revoked'                 => $revoked_count,
            'waiting_list'            => $waiting_list_count,
            'fully_paid_progress'     => $applications_total > 0 ? round(($fully_paid * 100) / $applications_total, 2) : 0,
            'partially_paid_progress' => $applications_total > 0 ? round(($partially_paid * 100) / $applications_total, 2) : 0,
            'applied_progress'        => $applications_total > 0 ? round(($applied * 100) / $applications_total, 2) : 0,
            'not_paid_progress'       => $applications_total > 0 ? round(($not_paid * 100) / $applications_total, 2) : 0,
        );
    }

    /**
     * Return application counts and payment breakdown for a date range.
     * total           : number of applications received
     * paid_count      : number with any payment recorded
     * full_paid_count : number whose paid_amount >= online_admission_amount setting
     */
    public function getApplicationStats($start_date, $end_date)
    {
        // determine required amount from settings
        $setting = $this->setting_model->getSetting();
        $required = isset($setting->online_admission_amount) ? (float)$setting->online_admission_amount : 0;
        // subquery to sum payments per admission
        $sub = $this->db->select('online_admission_id, SUM(paid_amount) AS paid_amount', false)
                        ->from('online_admission_payment')
                        ->group_by('online_admission_id')
                        ->get_compiled_select();
        $condition = " date_format(online_admissions.created_at,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
        $row = $this->db->select("COUNT(*) AS total,
                                    SUM(CASE WHEN p.paid_amount > 0 THEN 1 ELSE 0 END) AS paid_count,
                                    SUM(CASE WHEN p.paid_amount >= " . $this->db->escape($required) . " THEN 1 ELSE 0 END) AS full_paid_count",
                                    false)
                        ->from('online_admissions')
                        ->join('(' . $sub . ') p', 'p.online_admission_id = online_admissions.id', 'left')
                        ->where($condition)
                        ->get()->row_array();
        return $row ? $row : ['total'=>0,'paid_count'=>0,'full_paid_count'=>0];
    }

}
