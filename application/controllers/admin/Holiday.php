<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Holiday extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("holiday_model");
		$this->sch_setting_detail  = $this->setting_model->getSetting();
    }

    public function index()
    {  	
		if (!$this->rbac->hasPrivilege('annual_calendar', 'can_view')) {
            access_denied();
        }
		
        $data['title']       	        =	$this->lang->line('select_criteria');
        $data["search_holiday_type"]	=	"";

        if (isset($_POST['search_holiday_type']) && $_POST['search_holiday_type'] != '') {
            $search_holiday_type            =   $_POST['search_holiday_type'];
			$data["search_holiday_type"]	=	$_POST['search_holiday_type'];
        }         
        $this->form_validation->set_rules('search_holiday_type', $this->lang->line('type'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) { 
            $holidaylist   =   $this->holiday_model->get(null,null);
        } else {
            $holidaylist   =   $this->holiday_model->get(null, $search_holiday_type);
        }

        $data["holidaylist"]  	         = $holidaylist; 
		$data['superadmin_restriction']  = $this->sch_setting_detail->superadmin_restriction;
		$getStaffRole                     = $this->customlib->getStaffRole();
        $data['staffrole']                = json_decode($getStaffRole);
		$data['login_staff_id']           = $this->customlib->getStaffID();
        $data['holiday_type']             = $this->holiday_model->get_holiday_type();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/holiday/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add()
    {  		 
        $holiday_type=$this->input->post('holiday_type');       
		
		$this->form_validation->set_rules('from_date', $this->lang->line('from_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('to_date', $this->lang->line('to_date'), 'trim|required|xss_clean');
		$this->form_validation->set_rules('holiday_type', $this->lang->line('type'), 'trim|required|xss_clean');

        $this->form_validation->set_rules('description', $this->lang->line('description'), 'trim|required|xss_clean');     
        if ($this->form_validation->run() == false) {            
            $msg = array(
                'holiday_type'   =>     form_error('holiday_type'),
                'from_date'      =>     form_error('from_date'),
                'to_date'        =>     form_error('to_date'),
                'description'    =>     form_error('description')            
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {

            // IF THERE IS SINGLE DATE THEN IT WILL BE SAME FOR BOTH COLUMNS - FROM_DATE AND TO_DATE //
                 
            $from_date   =    $this->input->post('from_date');
            $to_date     =    $this->input->post('to_date');

            $holiday_color = '#008000';
            $comp_type = $this->holiday_model->get_holiday_type_by_name('Compensation');
            if (!empty($comp_type) && (int)$holiday_type === (int)$comp_type['id']) {
                $settings = $this->setting_model->getSetting();
                $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
                $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
                $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;
                $isFourthSaturdayWeekend = isset($settings->isFourthSaturdayHoliday) ? (int)$settings->isFourthSaturdayHoliday : 0;

                $from_dt = new DateTime(date('Y-m-d', $this->customlib->datetostrtotime($from_date)));
                $to_dt = new DateTime(date('Y-m-d', $this->customlib->datetostrtotime($to_date)));
                $current = clone $from_dt;
                $invalid_date = null;

                while ($current <= $to_dt) {
                    $dayOfWeek = (int)$current->format('w');
                    $is_second_saturday = $isSecondSaturdayWeekend && $dayOfWeek === 6 && $this->isNthSaturday($current, 2);
                    $is_fourth_saturday = $isFourthSaturdayWeekend && $dayOfWeek === 6 && $this->isNthSaturday($current, 4);
                    $is_weekend = in_array($dayOfWeek, $weekendDays, true) || $is_second_saturday || $is_fourth_saturday;
                    if (!$is_weekend) {
                        $invalid_date = $current->format('Y-m-d');
                        break;
                    }
                    $current->modify('+1 day');
                }

                if ($invalid_date !== null) {
                    $msg = array(
                        'holiday_type'   => $this->lang->line('compensation_weekend_only'),
                        'from_date'      => '',
                        'to_date'        => '',
                        'description'    => ''
                    );
                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                    echo json_encode($array);
                    return;
                }

                $holiday_color = '#ff9800';
            }

			if($this->input->post('front_site')){
				$front_site	=	1;
			}else{
				$front_site	=	0;
			}  

            $data = array(
                'id'                =>     $this->input->post('id'),
                'holiday_type'      =>     $this->input->post('holiday_type'),
                'from_date'         =>     date('Y-m-d', $this->customlib->datetostrtotime($from_date)),
                'to_date'           =>     date('Y-m-d 23:59:00', $this->customlib->datetostrtotime($to_date)),
                'description'       =>     $this->input->post('description'),
                'front_site'        =>     $front_site,
                'created_by'        =>     $this->customlib->getStaffID(),
                'holiday_color'     =>     $holiday_color,
                'session_id'    	=>     $this->setting_model->getCurrentSession()                
            );

            $edit_id= $this->input->post('id');
            if($edit_id>0){
                $data['updated_at']      =   date('Y-m-d') ;   
            }else{
                $data['created_at']      =   date('Y-m-d H:i:s') ;  
            }

            $this->holiday_model->add($data);      
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
    }     
        echo json_encode($array);
    }


    public function delete_holiday()
    {
		if (!$this->rbac->hasPrivilege('annual_calendar', 'can_delete')) {
            access_denied();
        }
		
        $this->holiday_model->delete_holiday($_POST['id']);
        $array = array('status' => 1, 'success' => $this->lang->line('delete_message'));
        echo json_encode($array);
    }

    private function isNthSaturday(DateTime $dateObj, $n)
    {
        $month_start = new DateTime($dateObj->format('Y-m-01'));
        $count = 0;
        while ($month_start <= $dateObj) {
            if ((int) $month_start->format('w') === 6) {
                $count++;
            }
            if ($month_start->format('Y-m-d') === $dateObj->format('Y-m-d')) {
                break;
            }
            $month_start->modify('+1 day');
        }
        return $count === $n;
    }
	
	public function getholiday()
    {
        $id                  = $this->input->post("id");        
        $result              = $this->holiday_model->get($id);
		$result['from_date'] = date($this->customlib->getSchoolDateFormat(),strtotime($result['from_date']));
		$result['to_date']   = date($this->customlib->getSchoolDateFormat(),strtotime($result['to_date']));
        $json_array          = array('status' => '1', 'error' => '', 'result' => $result);
        echo json_encode($json_array);
    }

    public function holidaytype()
    {
        $data["title"]        = $this->lang->line("add_holiday_type");
        $holiday_type         = $this->holiday_model->get_holiday_type();
        $data["holiday_type"] = $holiday_type;
		$data['can_add_edit'] = 'can_add';
        $this->load->view("layout/header");
        $this->load->view("admin/holiday/holidaytype", $data);
        $this->load->view("layout/footer");
    }

    public function add_holiday_type()
    {
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules(
            'type', $this->lang->line('name'), array('required',array('check_exists', array($this->holiday_model, 'valid_holiday_type')),
            )
        );	
		$data['can_add_edit'] = 'can_add';
        $id = $this->input->post("id");
        if ($this->form_validation->run()) {
            $type = $this->input->post("type");
            if (!empty($id)) {
                $data = array('type' => $type,'id' => $id);
            }else {
                $data = array('type' => $type);
            }
            $insert_id = $this->holiday_model->add_holiday_type($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
            redirect("admin/holiday/holidaytype");
        } else {
            $data["title"]        = $this->lang->line("add_holiday_type");
            $holiday_type         = $this->holiday_model->get_holiday_type();
            $data["holiday_type"] = $holiday_type;
            $this->load->view("layout/header");
            $this->load->view("admin/holiday/holidaytype", $data);
            $this->load->view("layout/footer");
        }
    }

    public function editholidaytype($id)
    {
        $data["title"]          = $this->lang->line("edit_holiday_type");
        $result                 = $this->holiday_model->get_holiday_type($id);
        $data["result"]         = $result;
        $holiday_type           = $this->holiday_model->get_holiday_type();
        $data["holiday_type"]   = $holiday_type;
		
		$data['can_add_edit'] = 'can_edit';
		
        $this->load->view("layout/header");
        $this->load->view("admin/holiday/holidaytype", $data);
        $this->load->view("layout/footer");
    }

    public function delete_holiday_type($id)
    {
        $this->holiday_model->delete_holiday_type($id);
        redirect('admin/holiday/holidaytype');
    }
    
    public function importIndianHolidays()
    {
        if (!$this->rbac->hasPrivilege('annual_calendar', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('import_year', $this->lang->line('year'), 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('import_region', $this->lang->line('region_state_optional'), 'trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'import_year'   => form_error('import_year'),
                'import_region' => form_error('import_region'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $year = $this->input->post('import_year');
            $region = $this->input->post('import_region');

            // --- Placeholder for fetching holidays ---
            // In a real-world scenario, you would integrate with an external API
            // or parse a local data source here.
            // For now, we'll use a dummy data structure.
            $indianHolidays = $this->getDummyIndianHolidays($year, $region);
            // --- End Placeholder ---

            $holidaysToAdd = [];
            $existingHolidaysCount = 0;
            $addedHolidaysCount = 0;

            $session_id = $this->setting_model->getCurrentSession();
            $staff_id = $this->customlib->getStaffID();
            $default_holiday_type_id = null; // Assuming a default holiday type for imported holidays

            // Try to find a default holiday type, e.g., 'Holiday' or 'National Holiday'
            $holidayTypes = $this->holiday_model->get_holiday_type();
            foreach ($holidayTypes as $type) {
                if (strtolower($type['type']) == 'holiday' || strtolower($type['type']) == 'national holiday' || strtolower($type['type']) == 'national') { // Added 'national' as another check
                    $default_holiday_type_id = $type['id'];
                    break;
                }
            }

            // If no suitable default type is found, we might need to create one or log an error
            if (is_null($default_holiday_type_id)) {
                // As a fallback, use the first available holiday type, or create a new one
                if (!empty($holidayTypes)) {
                    $default_holiday_type_id = $holidayTypes[0]['id'];
                } else {
                     // Handle case where no holiday types exist, perhaps create one programmatically
                     // For this example, we'll just return an error
                     $array = array('status' => 'fail', 'error' => array('message' => $this->lang->line('no_holiday_types_found_please_create_one')), 'message' => $this->lang->line('no_holiday_types_found_please_create_one'));
                     echo json_encode($array);
                     return;
                }
            }


            foreach ($indianHolidays as $holiday) {
                $from_date = $holiday['date']; // Assuming date is YYYY-MM-DD
                $to_date = $holiday['date'];   // Assuming single-day holidays for now
                $description = $holiday['name'];
                $holiday_type_id = (isset($holiday['type_id']) && $holiday['type_id']) ? $holiday['type_id'] : $default_holiday_type_id;

                if (!$this->holiday_model->checkHolidayExists($from_date, $description)) {
                    $holidaysToAdd[] = [
                        'holiday_type'  => $holiday_type_id,
                        'from_date'     => $from_date,
                        'to_date'       => $to_date,
                        'description'   => $description,
                        'front_site'    => 1, // Assume imported holidays are visible on front site
                        'created_by'    => $staff_id,
                        'holiday_color' => '#FF0000', // Red for imported holidays? Or any default.
                        'session_id'    => $session_id,
                        'created_at'    => date('Y-m-d H:i:s'),
                    ];
                    $addedHolidaysCount++;
                } else {
                    $existingHolidaysCount++;
                }
            }

            if (!empty($holidaysToAdd)) {
                $this->holiday_model->addBatch($holidaysToAdd); // New batch add method in model
            }

            // Using language line for success message
            $message = sprintf($this->lang->line('holidays_imported_successfully_added_s_existing_s'), $addedHolidaysCount, $existingHolidaysCount);
            $array = array('status' => 'success', 'error' => '', 'message' => $message);
        }

        echo json_encode($array);
    }

    // --- Placeholder for fetching holidays ---
    private function getDummyIndianHolidays($year, $region = '')
    {
        // This function would normally make an API call or parse a file.
        // For demonstration, returning some fixed data.
        $holidays = [
            ['date' => $year . '-01-26', 'name' => 'Republic Day', 'type_id' => null],
            ['date' => $year . '-08-15', 'name' => 'Independence Day', 'type_id' => null],
            ['date' => $year . '-10-02', 'name' => 'Gandhi Jayanti', 'type_id' => null],
        ];

        // Add some regional holidays based on a dummy region filter
        if (strtolower($region) == 'maharashtra') {
            $holidays[] = ['date' => $year . '-05-01', 'name' => 'Maharashtra Day', 'type_id' => null];
        }

        return $holidays;
    }
    // --- End Placeholder ---
}

?>
