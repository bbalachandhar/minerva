<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('active_link')) {

    function activate_menu($controller, $action)
    {
        $CI     = get_instance();
        $method = $CI->router->fetch_method();
        $class  = $CI->router->fetch_class();
        return ($method == $action && $controller == $class) ? 'active' : '';
    }

    function set_Topmenu($top_menu_name)
    {
        $CI               = get_instance();
        $session_top_menu = $CI->session->userdata('top_menu');
        if ($session_top_menu == $top_menu_name) {
            return 'active';
        }
        return "";
    }

    function set_Submenu($sub_menu_name)
    {
        $CI               = get_instance();
        $session_sub_menu = $CI->session->userdata('sub_menu');
        if ($session_sub_menu == $sub_menu_name) {
            return 'active';
        }
        return "";
    }

    function set_SubSubmenu($sub_menu_name)
    {
        $CI               = get_instance();
        $session_sub_menu = $CI->session->userdata('subsub_menu');
        if ($session_sub_menu == $sub_menu_name) {
            return 'active';
        }
        return "";
    }

}

function access_denied()
{
    redirect('admin/unauthorized');
}

function update_config_installed()
{
    $CI          = &get_instance();
    $config_path = APPPATH . 'config/config.php';
    $CI->load->helper('file');
    @chmod($config_path, FILE_WRITE_MODE);
    $config_file = read_file($config_path);
    $config_file = trim($config_file);
    $config_file = str_replace("\$config['installed'] = false;", "\$config['installed'] = true;", $config_file);
    $config_file = str_replace("\$config['base_url'] = '';", "\$config['base_url'] = '" . site_url() . "';", $config_file);
    if (!$fp = fopen($config_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
        return false;
    }
    flock($fp, LOCK_EX);
    fwrite($fp, $config_file, strlen($config_file));
    flock($fp, LOCK_UN);
    fclose($fp);
    @chmod($config_path, FILE_READ_MODE);
    return true;
}

function update_autoload_installed()
{
    $CI            = &get_instance();
    $autoload_path = APPPATH . 'config/autoload.php';
    $CI->load->helper('file');
    @chmod($autoload_path, FILE_WRITE_MODE);
    $autoload_file = read_file($autoload_path);
    $autoload_file = trim($autoload_file);
    $autoload_file = str_replace("\$autoload['libraries'] = array('database', 'session', 'form_validation')", "\$autoload['libraries'] = array('email','session', 'form_validation', 'upload', 'pagination','Customlib')", $autoload_file);
    if (!$fp = fopen($autoload_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
        return false;
    }
    flock($fp, LOCK_EX);
    fwrite($fp, $autoload_file, strlen($autoload_file));
    flock($fp, LOCK_UN);
    fclose($fp);
    @chmod($config_path, FILE_READ_MODE);
    return true;
}

function delete_dir($dirPath)
{
    if (!is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            delete_dir($file);
        } else {
            unlink($file);
        }
    }
    if (rmdir($dirPath)) {
        return true;
    }
    return false;
}

function admin_url($url = '')
{
    if ($url == '') {
        return site_url() . 'site/login';
    } else {
        return site_url() . 'site/login';
    }
}

if (!function_exists('main_menu_array')) {

    function main_menu_array($find_array)
    {  
        $array = array(

            'admissions' => array(
                'enquiry'                => array('index'),
                'onlineadmission'        => array('index','edit'),
                'onlinestudent'          => array('index','edit'),
                'admission_cancellation' => array('index'),
                'waiting_list'           => array('index'),
                'scholarshipexam'        => array('index','candidates'),
                'scholarshipapplication' => array('index','view','verify','approve','settings','settings_ajax'),
                'scholarshiptype'        => array('index','edit'),
                'meritscholarship'       => array('index','save_score','bulk_upload','assign_single','assign_all','sample_csv'),
            ),
            
            'front_office' => array(
                'visitors'        => array('index'),
                'generalcall'     => array('index','edit'),
                'dispatch'        => array('index','editdispatch'),
                'receive'         => array('index','editreceive'),
                'complaint'       => array('index','edit'),
                'visitorspurpose' => array('index','edit'),
                'complainttype'   => array('index','editcomplainttype'),
                'source'          => array('index','edit'),
                'reference'       => array('index','edit'),
            ),
            
            'student_information' => array(                
                'student'         => array('search','create','import','disablestudentslist','multiclass','bulkdelete','view','edit','birthdays'),       
                'category'        => array('index','edit'),               
                'schoolhouse'     => array('index','edit'),               
                'disable_reason'  => array('index','edit'),
                'birthday'        => array('birthday_list'),
            ),
            
            'fees_collection' => array(                             
                'studentfee'     => array('index','addfee','searchpayment','feesearch'),                            
                'feemaster'      => array('index','assign','edit'),                               
                'feegroup'       => array('index','edit'),                               
                'feetype'        => array('index','edit'),                               
                'feediscount'    => array('index','edit','assign'),                               
                'feesforward'    => array('index'),                               
                'feereminder'    => array('setting'), 
                'offlinepayment' => array('index'), 
				'customfeesmaster'  => array('index'),
                'incidental_fee_type' => array('index', 'edit'),
                'assign_incidental_fee' => array('index'),
                'collect_incidental_fee' => array('index', 'searchStudent', 'receipt', 'revert', 'findApplicationByReference'),
                'financereports'          => array('incidental_fee_report'),
                'student_fee_override'    => array('index', 'save', 'delete', 'bulk_import', 'exportformat', 'handle_csv_upload'),
            ), 
            
            'income' => array(                                 
                'income'        => array('index','edit','incomesearch'),             
                'incomehead'    => array('index','edit'),             
            ),
            
            'expense' => array(                                 
                'expense'       => array('index','edit','expensesearch'),             
                'expensehead'   => array('index','edit'),                             
            ),
            
            'examinations' => array(                                 
                'examgroup'     => array('index','edit','addexam'),                  
                'exam_schedule' => array('index'),                  
                'examresult'    => array('index','admitcard','marksheet'),                  
                'admitcard'     => array('index','edit'),                  
                'marksheet'     => array('index','edit'),                  
                'grade'         => array('index','edit'),                  
                'marksdivision'         => array('index','edit'),                  
            ),
            
            'attendance' => array(
                'approve_leave'        => array('index'),
                'stuattendence'        => array('index','edit','attendencereport'),
                'subjectattendence'    => array('index','reportbydate'),
                'attendancedashboard'  => array('index'),

            ),
            
            'online_examinations' => array(                                 
                'onlineexam'    => array('index','evalution','assign'),                  
                'question'      => array('index','read'),                  
            ), 
            
            'lesson_plan' => array(                                 
                'syllabus'      => array('index','status'),                
                'lessonplan'    => array('lesson','topic','copylesson','edittopic','editlesson'),                
            ), 
            
            'academics' => array(                                 
                'timetable'     => array('classreport','mytimetable','create','grid'),                 
                'teacher'       => array('assign_class_teacher','update_class_teacher'),                 
                'stdtransfer'   => array('index'),                 
                'subjectgroup'  => array('index','edit'),                 
                'subject'       => array('index','edit'),                 
                'classes'       => array('index','edit'),                 
                'sections'      => array('index','edit'),                 
            ), 
            
            'human_resource' => array(                   
                'staff'             => array('index','profile','edit','leaverequest','rating','disablestafflist','create'),             
                'staffattendance'   => array('index'),                 
                'payroll'           => array('index','edit','create'),                 
                'leaverequest'      => array('leaverequest','applyleave','claimleave'),  
                'leavetypes'        => array('index','leaveedit','createleavetype'),  
                'department'        => array('department','departmentedit'),  
                'designation'       => array('designation','designationedit'),
                'specialattendance' => array('index','search','generate_attendance','get_employees_by_department'),
                'leave_balance_setup' => array('index','ajax_save_balances','ajax_get_staff_balances'),
                'attendance_exceptions' => array('index','resolve','get_punch_context'),
                'update_leave_balance' => array('index'),
            ), 
            
            'communicate' => array(          
                'notification'      => array('index','edit','add'),             
                'mailsms'           => array('compose','compose_sms','index','schedule','email_template','sms_template','edit_schedule'),      
                'student'           => array('bulkmail'),             
            ), 
            
            'download_center' => array(          
                'contenttype'       => array('index','edit'),              
                'content'           => array('list','upload'),              
                'video_tutorial'    => array('index'),              
            ), 
            
            'homework' => array(               
                'homework'      => array('index','dailyassignment'),              
            ), 
            
            'library' => array(               
                'book'      => array('getall','edit','index','import'),    
                'member'    => array('index','issue','student','teacher'), 
                'librarycategory'       => array('index','create','edit','delete'),
                'librarysubcategory'    => array('index','create','edit','delete'),
                'librarypublisher'      => array('index','create','edit','delete'),
                'libraryvendor'         => array('index','create','edit','delete'),
                'librarybooktype'       => array('index','create','edit','delete'),
                'librarysubject'        => array('index','create','edit','delete'),
                'librarypositionrack'   => array('index','create','edit','delete'),
                'librarypositionshelf'  => array('index','create','edit','delete'),
                'opaq'      => array('index','getopaqlist'), // Added OPAQ controller and its methods
                'library_checkin_checkout' => array('index'),
                'library_checkout_pending' => array('index'),
            ), 
            
            'inventory' => array(               
                'issueitem'      => array('index','create'),    
                'itemstock'      => array('index','edit'),    
                'item'           => array('index','edit'),    
                'itemcategory'   => array('index','edit'),    
                'itemstore'      => array('index','edit','create'),    
                'itemsupplier'   => array('index','edit','create'),    
                'inventorydashboard' => array('index'),
                'inventoryindent' => array('index','approvals'),
                'inventoryprocurement' => array('purchaseorders','goodsreceipts','createpo','storepo','creategrn','storegrn','poapprovals','podecision','poitems'),
                'assetmanagement' => array('register','assignment','transfer','maintenance'),
            ), 
             
            'transport' => array(               
                'transport'      => array('feemaster'),      
                'pickuppoint'    => array('index','assign','student_fees'),      
                'route'    => array('index','edit'),      
                'vehicle'    => array('index'),      
                'vehroute'    => array('index','edit'),        
                'assign_transport_fee' => array('index', 'search', 'assign', 'unassign', 'save_assignments'),
            ), 
            
            'hostel' => array(               
                'hostelroom'  => array('index','edit'),      
                'roomtype'    => array('index','edit'),      
                'hostel'      => array('index','edit'),      
            ), 
            
            'certificate' => array(               
                'certificate'           => array('index','edit'),      
                'generatecertificate'   => array('index','search'),      
                'studentidcard'         => array('index','edit'),      
                'generateidcard'        => array('search'),      
                'staffidcard'           => array('index','edit'),    
                'generatestaffidcard'   => array('index','search'),    
            ),
            
            'front_cms' => array(               
                'events'        => array('index','edit','create'),      
                'gallery'       => array('index','edit','create'),      
                'notice'        => array('index','edit','create'),      
                'media'         => array('index'),      
                'page'          => array('index','edit','create'),        
                'menus'         => array('index','additem'),        
                'banner'        => array('index'),        
            ),
            
            'alumni' => array(               
                'alumni'        => array('alumnilist','events'),       
            ),            
            
            'reports' => array(  
                'report'            => array('alumnireport','inventory','issueinventory','additem','inventorystock','library','studentbookissuereport','bookduereport','bookinventory','human_resource','staff_report','lesson_plan','teachersyllabusstatus','onlineexamrank','onlineexamattend','onlineexams','attendance','studentinformation','studentreport','communitybasedreport','online_admission_report','student_teacher_ratio','boys_girls_ratio','student_profile','sibling_report','admission_report','class_subject','classsectionreport','guardianreport','admissionreport','logindetailreport','parentlogindetailreport'),                
                'attendencereports' => array('attendance','classattendencereport','attendancereport','daily_attendance_report','staffattendancereport','staffattendancewithpunchreport','biometric_attlog','reportbymonthstudent','reportbymonth','staffdaywiseattendancereport','daywiseattendancereport'), 
                'payroll'           => array('payrollreport'), 
                'onlineexam'        => array('report'),  
                'examresult'        => array('rankreport','examinations'),  
                'book'              => array('issue_returnreport'), 
                'homework'          => array('homeworkreport','evaluation_report'),                
                'route'             => array('studenttransportdetails'), 
                'hostelroom'        => array('studenthosteldetails'), 
                'userlog'           => array('index'), 
                'audit'             => array('index'),
                'financereports'    => array('finance','reportduefees','reportdailycollection','reportbyname','studentacademicreport','collection_report','onlinefees_report','duefeesremark','income','expense','payroll','incomegroup','expensegroup','onlineadmission','incomeexpensebalancereport'),                
                'homework'          => array('homeworkordailyassignmentreport','homeworkreport','evaluation_report','dailyassignmentreport'),             
            ),            
            
            'system_settings' => array(  
                'schsettings'           => array('index','logo','miscellaneous','backendtheme','mobileapp','studentguardianpanel','fees','idautogeneration','attendancetype','maintenance','whatsappsettings'),                     
                'finalyearclasses'      => array('index','save'),
                'sessions'              => array('index','edit'),                     
                'notification'          => array('setting'),                     
                'smsconfig'             => array('index'),                     
                'whatsappconfig'        => array('index'),                     
                'emailconfig'           => array('index'),                     
                'paymentsettings'       => array('index'),                     
                'print_headerfooter'    => array('index'),                     
                'frontcms'              => array('index'),                     
                'roles'                 => array('index','permission'),                     
                'admin'                 => array('backup','filetype'),                     
                'language'              => array('index','create'),                     
                'currency'              => array('index'),                     
                'users'                 => array('index'),                     
                'module'                => array('index'),                     
                'customfield'           => array('index','edit'),                     
                'captcha'               => array('index'),                     
                'systemfield'           => array('index'),                     
                'student'               => array('profilesetting'),                     
                'onlineadmission'       => array('admissionsetting'),                  
                'updater'               => array('index'),                  
                'sidemenu'              => array('index'),                  
                'thermalprint'         => array('index'),                  
            ),

            'gmeet_live_classes' => array(               
                'gmeet'        => array('timetable','meeting','class_report','meeting_report','index'),               
            ),
                
            'zoom_live_classes' => array(               
                'conference'        => array('timetable','meeting','class_report','meeting_report','index'),               
            ),
            
            'behaviour_records' => array(               
                'studentincidents'  => array('index'),               
                'incidents' => array('index'),               
                'report'    => array('index','studentincidentreport','studentbehaviorsrankreport','classwiserankreport','classsectionwiserank','housewiserank','incidentwisereport'),               
                'setting'   => array('index'),               
            ),
            
            'multi_branch' => array(               
                'branch'    => array('overview','index'),               
                'finance'   => array('dailycollectionreport','payroll','incomelist','expenselist','incomereport','expensereport','userlogreport','index'),               
            ),
            
            'two_factor_authentication' => array(               
                'admin'        => array('setup','index'),               
            ),
            
            'online_course' => array(               
                'course'        => array('index','setting'),               
                'coursecategory'  => array('categoryadd','categoryedit'),               
                'coursereport'   => array('report','coursepurchase','coursesellreport','trendingreport','completereport','courseratingreport','guestlist','quizperformance','course_assignment_report','course_exam_result_report','course_exam_report','course_exam_attempt_report'),         
                'offlinepayment'   => array('payment'),               
                'courseexamquestion'   => array('index'),               
                'coursetag'   => array('index'),               
            ),
            
                        'cbse_exam' => array(               
                            'exam'          => array('index','examtimetable','examwiserank','templatewiserank'),               
                            'result'        => array('marksheet'),               
                            'grade'         => array('gradelist'),               
                            'observation'   => array('index','assign'),               
                            'observationparameter' => array('index','edit'),               
                            'assessment'    => array('index'),               
                            'term'          => array('index'),               
                            'template'      => array('index','templatewiserank'),               
                            'report'        => array('index','templatewise','examsubject'),               
                            'setting'       => array('index'),
                            'cbsecategory'  => array('index'),
                            'cbseadmitcard' => array('admitcard','index','edit'),
                            'schedule'      => array('index'),
                            'chart'         => array('index'),
                        ),
                        
                        'hall_management' => array(
                            'hall' => array('hall_master', 'add', 'edit', 'delete', 'bookings', 'book', 'approval_config', 'approve_booking', 'reject_booking'),
                        ),
            
                        'qr_code_attendance' => array(             
                                           
                            'attendance'    => array('index'),                
                            'setting'       => array('index'),                              
                        ),
                            
                        'holiday' => array(                            
                            'holiday'        => array('index','holidaytype','editholidaytype'),               
                        ),
                            
                        'student_cv' => array(                            
                            'resume'        => array('index','download','resume_setting','student_resume_details'),               
                        ),
            'naac' => array(
                            'naac' => array('configuration', 'iiqa', 'ssr', 'aqar'),
                        ),

            'coe' => array(
                'coe_dashboard'    => array('index'),
                'coe_setup'        => array('index', 'add', 'edit'),
                'coe_application'  => array('index', 'add', 'edit'),
                'coe_eligibility'  => array('index'),
                'coe_hallticket'   => array('index', 'generate'),
                'coe_nominalroll'  => array('index'),
                'coe_schedule'     => array('index', 'manage', 'save_schedule', 'delete_schedule'),
                'coe_seating'      => array('index', 'generate', 'manage', 'create_room', 'auto_assign', 'view_room', 'print_seating', 'clear_room', 'delete_room', 'halls', 'save_hall', 'delete_hall'),
                'coe_invigilation' => array('index', 'assign'),
                'coe_qpd'          => array('index', 'manage', 'upload', 'download', 'delete'),
                'coe_attendance'   => array('index', 'rooms', 'sheet', 'save', 'qr_scan'),
                'coe_ufm'          => array('index', 'listing', 'report', 'save', 'view', 'review', 'delete'),
                'coe_flyingsquad'  => array('index', 'manage', 'save_visit', 'delete_visit'),
                'coe_answer_scripts' => array('index', 'listing', 'upload', 'save_upload', 'view', 'update_status', 'delete'),
                'coe_osm'            => array('index', 'dashboard', 'create_from_scripts', 'assign', 'mark', 'save_marks', 'submit', 'lock'),
                'coe_revaluation'    => array('index', 'listing', 'add', 'save_request', 'view', 'update_payment', 'assign', 'save_evaluation', 'reject'),
                'coe_moderation'     => array('index', 'listing', 'save_rule', 'preview', 'apply', 'delete'),
                'coe_marks'          => array('index', 'listing', 'enter', 'save_marks', 'configure_subjects', 'save_config', 'compute_sgpa', 'recompute_grades', 'student_card'),
                'coe_results'        => array('index', 'listing', 'publish', 'unpublish', 'student_result', 'export', 'tabulation', 'merit_list'),
                'coe_arrear'         => array('index', 'student'),
                'coe_event'          => array('index', 'add', 'save', 'edit', 'update', 'delete', 'manage', 'save_batch', 'update_batch', 'delete_batch'),
            ),
            'tt' => array(
                'tt' => array(
                    'periods', 'save_period', 'delete_period', 'reorder_periods',
                    'rooms', 'save_room', 'delete_room',
                    'batches', 'save_batch', 'delete_batch',
                    'subject_load', 'get_subject_load_data', 'get_subject_load_raw', 'save_subject_load',
                    'teacher_constraints', 'save_teacher_constraint', 'delete_teacher_constraint',
                    'teacher_unavail', 'get_teacher_unavail', 'save_teacher_unavail',
                    'generate', 'run_generate', 'test_generate', 'verify_constraints', 'preview', 'confirm_draft', 'discard_draft',
                    'class_grid', 'load_class_grid', 'save_cell', 'delete_cell', 'toggle_lock', 'upload_csv_timetable',
                    'teacher_view', 'load_teacher_grid',
                    'substitution', 'get_absent_slots', 'save_substitution', 'cancel_substitution', 'get_substitution_report',
                    'reports', 'get_master_report', 'get_room_utilization', 'get_teacher_workload',
                    'subject_colors', 'save_subject_colors',
                    'class_unavail', 'get_class_unavail', 'save_class_unavail',
                    'room_unavail', 'get_room_unavail', 'save_room_unavail',
                    'subject_unavail', 'get_subject_unavail', 'save_subject_unavail',
                    'get_sections_by_class', 'get_batches_by_class_section', 'get_subjects_by_class_section', 'get_all_subjects',
                    'dashboard', 'instructions',
                    'lesson_browser', 'get_lesson_browser_data',
                    'joint_lessons', 'get_joint_lesson', 'save_joint_lesson', 'delete_joint_lesson',
                ),
            ),
                            
                        
                        
                    );        if (array_key_exists($find_array, $array)) {
            return $array[$find_array];
        }
        return false;
    }

}

if (!function_exists('activate_main_menu')) {

    function activate_main_menu($menu, $class_active = "active menu-open")
    {
        $CI     = get_instance();
        $class  = $CI->router->fetch_class();
        $method = $CI->router->fetch_method();

        $return_array = main_menu_array($menu);
        if ($return_array) {
            if (array_key_exists($class, $return_array)) {
                $methods = $return_array[$class];
                if (in_array('*', (array) $methods, true) || in_array($method, (array) $methods, true)) {
                    return $class_active;
                }
            }
        }
        return "";
    }
}

if (!function_exists("activate_submenu")) {

    function activate_submenu($arg_class = "", $arg_methods = array(), $class_active = "active")
    {
        $CI = get_instance();

        $class  = strtolower($CI->router->fetch_class());
        $method = $CI->router->fetch_method();

        if ($class == strtolower($arg_class)) {
            // Wildcard: any method of this controller activates the item
            if (in_array('*', (array) $arg_methods, true)) {
                return $class_active;
            }
            if (is_array($arg_methods)) {
                foreach ($arg_methods as $arg_methods_value) {
                    if ($method == $arg_methods_value) {
                        return $class_active;
                    }
                }
            }
        }
        return "";
    }

}

function side_menu_list($list = -1)
{

    $CI = &get_instance();
    $CI->load->model('sidebarmenu_model');
    $result = $CI->sidebarmenu_model->getMenuwithSubmenus($list);
    return $result;

}

function access_permission_sidebar_remove_pipe($access_permissions)
{
    // remove pipe sign ||
    $module_permission = array_map('trim', explode('||', preg_replace('/\(\'|\'|\)/', '', $access_permissions)));

    return $module_permission;
}

function access_permission_remove_comma($m_permission_value)
{
    // remove pipe sign ||
    $module_permission_seprated = array_map('trim', explode(',', preg_replace('/\s+/', '', $m_permission_value)));
    return $module_permission_seprated;
}
