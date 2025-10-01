<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Classes extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Department_model');
        $this->load->model('category_model');
        $this->load->model('section_model');
        $this->load->library('encoding_lib');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('class', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'classes/index');
        $data['title']      = 'Add Class';
        $data['title_list'] = 'Class List';

        $data['sch_setting'] = $this->sch_setting_detail;
        $data['department_list'] = $this->Department_model->getDepartmentType();

        $this->form_validation->set_rules(
            'class', $this->lang->line('class'), array(
                'required',
                array('class_exists', array($this->class_model, 'class_exists')),
            )
        );
        $this->form_validation->set_rules('sections[]', $this->lang->line('section'), 'trim|required|xss_clean');

        if ($this->sch_setting_detail->institution_type == 'college') {
            $this->form_validation->set_rules('department_id', $this->lang->line('department'), 'trim|required|xss_clean');
        }

        if ($this->form_validation->run() == false) {

        } else {
            $class       = $this->input->post('class');
            $class_array = array(
                'class' => $this->input->post('class'),
                'department_id' => $this->input->post('department_id'),
            );
            $sections = $this->input->post('sections');
            $this->classsection_model->add($class_array, $sections);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('classes');
        }
        $vehicle_result       = $this->section_model->get();
        $data['vehiclelist']  = $vehicle_result;
        $vehroute_result      = $this->classsection_model->getByID();
        $data['vehroutelist'] = $vehroute_result;
        $this->load->view('layout/header', $data);
        $this->load->view('class/classList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('class', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $this->class_model->remove($id);

        $student_delete=$this->student_model->getUndefinedStudent();
        if(!empty($student_delete)){
            $delte_student_array=array();
            foreach ($student_delete as $student_key => $student_value) {

                $delte_student_array[]=$student_value->id;
            }
            $this->student_model->bulkdelete($delte_student_array);
        }
     
        redirect('classes');
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('class', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'classes/index');
        $data['title']      = 'Edit Class';
        $data['id']         = $id;
        $vehroute           = $this->classsection_model->getByID($id);
        $data['vehroute']   = $vehroute;
        $data['title_list'] = 'Fees Master List';

        $this->form_validation->set_rules(
            'class', $this->lang->line('class'), array(
                'required',
                array('class_exists', array($this->class_model, 'class_exists')),
            )
        );
        $this->form_validation->set_rules('sections[]', $this->lang->line('sections'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $vehicle_result       = $this->section_model->get();
            $data['vehiclelist']  = $vehicle_result;
            $routeList            = $this->route_model->get();
            $data['routelist']    = $routeList;
            $vehroute_result      = $this->classsection_model->getByID();
            $data['vehroutelist'] = $vehroute_result;
            $this->load->view('layout/header', $data);
            $this->load->view('class/classEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $sections      = $this->input->post('sections');
            $prev_sections = $this->input->post('prev_sections');
            $route_id      = $this->input->post('route_id');
            $class_id      = $this->input->post('pre_class_id');
            if (!isset($prev_sections)) {
                $prev_sections = array();
            }
            $add_result    = array_diff($sections, $prev_sections);
            $delete_result = array_diff($prev_sections, $sections);
            if (!empty($add_result)) {
                $vehicle_batch_array = array();
                $class_array         = array(
                    'id'    => $class_id,
                    'class' => $this->input->post('class'),
                );
                foreach ($add_result as $vec_add_key => $vec_add_value) {
                    $vehicle_batch_array[] = $vec_add_value;
                }
                $this->classsection_model->add($class_array, $vehicle_batch_array);
            } else {
                $class_array = array(
                    'id'    => $class_id,
                    'class' => $this->input->post('class'),
                );
                $this->classsection_model->update($class_array);
            }

            if (!empty($delete_result)) {
                $classsection_delete_array = array();
                foreach ($delete_result as $vec_delete_key => $vec_delete_value) {
                    $classsection_delete_array[] = $vec_delete_value;
                }

                $this->classsection_model->remove($class_id, $classsection_delete_array);
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('classes/index');
        }
    }

    public function get_section($id)
    {
        $data['sections'] = $this->class_model->get_section($id);
        $this->load->view('class/_section_list', $data);
    }

    public function getClassesByDepartment()
    {
        $department_id = $this->input->get('department_id');
        $data = $this->class_model->getClassesByDepartment($department_id);
        echo json_encode($data);
    }

    public function import()
    {
        if (!$this->rbac->hasPrivilege('import_class', 'can_view')) {
            access_denied();
        }
        $data['title']      = 'Import Class';
        $data['title_list'] = 'Recently Added Class';
        $session            = $this->setting_model->getCurrentSession();
        $class              = $this->class_model->get('', $classteacher = 'yes');
        $data['classlist']  = $class;
        $userdata           = $this->customlib->getUserData();

        $category = $this->category_model->get();

        if ($this->sch_setting_detail->institution_type == 'college') {
            $fields = array('class', 'section', 'department');
        } else {
            $fields = array('class', 'section');
        }

        $data["fields"]       = $fields;
        $data['categorylist'] = $category;
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_csv_upload');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('class/import', $data);
            $this->load->view('layout/footer', $data);
        } else {

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {
                    $file = $_FILES['file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file);
                    if (!empty($result)) {
                        $rowcount = 0;
                        for ($i = 1; $i <= count($result); $i++) {
                            $class_data = array();
                            $n                = 0;
                            foreach ($result[$i] as $key => $value) {
                                $class_data[$fields[$n]] = $this->encoding_lib->toUTF8($result[$i][$key]);
                                $n++;
                            }

                            $class_name = $class_data["class"];
                            $department_id = null;
                            if ($this->sch_setting_detail->institution_type == 'college') {
                                $department_name = $class_data["department"];
                                if (empty($department_name)) {
                                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Department is required for college.</div>');
                                    redirect('classes/import');
                                    exit();
                                } else {
                                    $department = $this->Department_model->getDepartmentByName($department_name);
                                    if ($department) {
                                        $department_id = $department['id'];
                                    } else {
                                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Department not found: ' . $department_name . '</div>');
                                        redirect('classes/import');
                                        exit();
                                    }
                                }
                            }

                            $class_exists = $this->class_model->check_data_exists($class_name);

                            if ($class_exists) {
                                $class_array = array(
                                    'id' => $class_exists->id,
                                    'class' => $class_name,
                                    'department_id' => $department_id,
                                );
                            } else {
                                $class_array = array(
                                    'class' => $class_name,
                                    'department_id' => $department_id,
                                );
                            }

                            $sections = !empty($class_data["section"]) ? explode("," , $class_data["section"]) : array();
                            $section_ids = array();
                            foreach ($sections as $section_name) {
                                $section = $this->section_model->getSectionByName(trim($section_name));
                                if ($section) {
                                    $section_ids[] = $section['id'];
                                } else {
                                    $section_id = $this->section_model->add(array('section' => trim($section_name)));
                                    $section_ids[] = $section_id;
                                }
                            }
                            $this->classsection_model->add($class_array, $section_ids);
                            $rowcount++;
                        }
                        $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">' . $this->lang->line('total') . ' ' . count($result) . ' ' . $this->lang->line('records_found_in_csv_file_total') . ' ' . $rowcount . ' ' . $this->lang->line('records_imported_successfully') . '</div>');
                    } else {
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('no_record_found') . '</div>');
                    }
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('please_upload_csv_file_only') . '</div>');
                }
            }

            redirect('classes/import');
        }
    }

    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array('text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext', 'application/octet-stream', 'application/txt');
            $temp      = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($error == "") {
                return true;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
            return false;
        }
    }

    public function exportformat()
    {
        $this->load->helper('download');
        if ($this->sch_setting_detail->institution_type == 'college') {
            $filepath = "backend/import/import_class_college_sample_file.csv";
        } else {
            $filepath = "backend/import/import_class_school_sample_file.csv";
        }
        $data     = file_get_contents($filepath);
        $name     = 'import_class_sample_file.csv';
        force_download($name, $data);
    }
}

