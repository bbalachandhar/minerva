<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Assignhostel extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('student_model');
        $this->load->model('hostel_model');
        $this->load->model('hostelroom_model');
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'Hostel');
        $this->session->set_userdata('sub_menu', 'admin/assignhostel');
        $this->load->view('layout/header');
        $this->load->view('admin/hostel/assign_hostel_view');
        $this->load->view('layout/footer');
    }

        public function assign_students()

        {

            $log_file = FCPATH . 'assign_hostel_log.txt';

            file_put_contents($log_file, "Starting hostel assignment process...\n", FILE_APPEND);

            file_put_contents($log_file, "FCPATH: " . FCPATH . "\n", FILE_APPEND);

    

            $student_admission_numbers = [

                'MCE2025CSE068', 'MCE2025IT007', 'MCE2025IT010', 'MCE2025IT056', 'MCE2025ECE056',

                'MCE2025CSBS060', 'MCE2025ECE104', 'MCE2025ECE109', 'MCE2025AIML009', 'MCE2025AIML018',

                'MCE2025AIML038', 'MCE2025AIDS018', 'MCE2025ME014', 'MCE2025CE016', 'MCE2025IT107',

                '311425251018', '311445002', 'MCE2025ECE121', '311424106009', '311424106014',

                '311424106016', '311424106019', '311424106026', '311424106053', '311424106054',

                '311424104006', '311424104075', '311424104057', '311424104082', '311424106039',

                '311424106071', '311424148017', '311424148028', '311424148046', '311424205019',

                '311424205036', '311424205043', '311424205064', '311424205077', '311424205104',

                '311424107003', '311424107004', '311424251006', '311424251011', '311424251013',

                '311424631035', '311424622023', '311423205007', '311423251007', '311423104014',

                '311423243002', '311423243024', '311423243025', '311423243028', '311423243047',

                '311423104049', '311423104075', '311423106003', '311423106016', '311423106028',

                '311423106043', '311423148003', '311423148004', '311423148009', '311423148018',

                '311423148019', '311423106088', '311423205065', '311422205015', '311422205026',

                '311422205027', '311422205048', '311422205050', '311422205064', '311422205095',

                '311422103001', '311422251013', '311422106004', '311422106006', '311422106025',

                '311422106031', '311422106033', '311422106039', '311422104009', '311422104055',

                '311422104302', '311422104062', '311421251007'

            ];

    

            $boys_hostel_name = 'ANR Boys Hostel';

            $girls_hostel_name = 'MCE Girls Hostel';

    

            // Directly query the database for hostel details

            $this->load->database(); // Ensure database library is loaded

    

            $query_boys = $this->db->get_where('hostel', array('hostel_name' => $boys_hostel_name));

            $boys_hostel = $query_boys->result_array();

    

            $query_girls = $this->db->get_where('hostel', array('hostel_name' => $girls_hostel_name));

            $girls_hostel = $query_girls->result_array();

    

            file_put_contents($log_file, "Boys Hostel (direct DB query): " . print_r($boys_hostel, true) . "\n", FILE_APPEND);

            file_put_contents($log_file, "Girls Hostel (direct DB query): " . print_r($girls_hostel, true) . "\n", FILE_APPEND);

    

            if (empty($boys_hostel)) {

                file_put_contents($log_file, "Hostel not found: " . $boys_hostel_name . "\n", FILE_APPEND);

                return;

            }

    

            if (empty($girls_hostel)) {

                file_put_contents($log_file, "Hostel not found: " . $girls_hostel_name . "\n", FILE_APPEND);

                return;

            }

    

            $boys_hostel_id = $boys_hostel[0]['id'];

            $girls_hostel_id = $girls_hostel[0]['id'];

    

            foreach ($student_admission_numbers as $admission_no) {

                $student = $this->student_model->get_student_by_admission_no($admission_no);

    

                if ($student) {

                    $student_id = $student->id;

                    $gender = $student->gender;

    

                    if (strtolower($gender) == 'male') {

                        $hostel_id = $boys_hostel_id;

                        $hostel_name = $boys_hostel_name;

                    } else if (strtolower($gender) == 'female') {

                        $hostel_id = $girls_hostel_id;

                        $hostel_name = $girls_hostel_name;

                    } else {

                        file_put_contents($log_file, "Student " . $admission_no . " has unknown gender: " . $gender . "\n", FILE_APPEND);

                        continue;

                    }

    

                    // Find a free room

                    $free_room = $this->hostelroom_model->get_free_room($hostel_id);

    

                    if ($free_room) {

                        $room_id = $free_room['id'];

                        $this->student_model->add(array('id' => $student_id, 'hostel_room_id' => $room_id));

                        file_put_contents($log_file, "Assigned student " . $admission_no . " (Gender: " . $gender . ") to room " . $free_room['room_no'] . " in hostel " . $hostel_name . "\n", FILE_APPEND);

                    } else {

                        file_put_contents($log_file, "No free room found for student " . $admission_no . " (Gender: " . $gender . ") in hostel " . $hostel_name . "\n", FILE_APPEND);

                    }

                } else {

                    file_put_contents($log_file, "Student not found: " . $admission_no . "\n", FILE_APPEND);

                }

            }

            file_put_contents($log_file, "Hostel assignment process completed.\n", FILE_APPEND);

        }
}