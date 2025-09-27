<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Book extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('encoding_lib');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'book/index');

        $data['title']      = 'Add Book';
        $data['title_list'] = 'Book Details';
        $listbook           = $this->book_model->listbook();
        $data['listbook']   = $listbook;
        $this->load->view('layout/header');
        $this->load->view('admin/book/createbook', $data);
        $this->load->view('layout/footer');
    }

    public function getall()
    {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'book/getall');

        $this->load->model('bookissue_model');

        $data['total_books'] = $this->book_model->count_all_books();
        $data['issued_books'] = $this->bookissue_model->count_issued_books();
        $data['available_books'] = $data['total_books'] - $data['issued_books'];

        $this->load->view('layout/header');
        $this->load->view('admin/book/getall', $data);
        $this->load->view('layout/footer');
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('books', 'can_add')) {
            access_denied();
        }
        $data['title']      = 'Add Book';
        $data['title_list'] = 'Book Details';
        $this->form_validation->set_rules('book_title', $this->lang->line('book_title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('perunitcost', $this->lang->line('book_price'), 'numeric');
        
        if ($this->form_validation->run() == false) {
            $listbook         = $this->book_model->listbook();
            $data['listbook'] = $listbook;
            $this->load->view('layout/header');
            $this->load->view('admin/book/createbook', $data);
            $this->load->view('layout/footer');
        } else {
            
            if($this->input->post('perunitcost')){
                $perunitcost    =   convertCurrencyFormatToBaseAmount($this->input->post('perunitcost'));
            }else{
                $perunitcost    = '';
            }
            
            $data = array(
                'book_title'       => $this->input->post('book_title'),
                'book_no'          => $this->input->post('book_no'),
                'isbn_no'          => $this->input->post('isbn_no'),
                'subject'          => $this->input->post('subject'),
                'rack_no'          => $this->input->post('rack_no'),
                'publish'          => $this->input->post('publish'),
                'author'           => $this->input->post('author'),
                'perunitcost'      => $perunitcost,
                'description'      => $this->input->post('description'),
                'barcode'          => $this->input->post('barcode'),
                'category_name'    => $this->input->post('category_name'),
                'subcategory_name' => $this->input->post('subcategory_name'),
                'author2'          => $this->input->post('author2'),
                'edition'          => $this->input->post('edition'),
                'medium'           => $this->input->post('medium'),
                'book_type'        => $this->input->post('book_type'),
                'shelf_id'         => $this->input->post('shelf_id'),
                'class_no'         => $this->input->post('class_no'),
                'edition_type'     => $this->input->post('edition_type'),
                'publish_year'     => $this->input->post('publish_year'),
                'bill_no'          => $this->input->post('bill_no'),
                'pages'            => $this->input->post('pages'),
                'department'       => $this->input->post('department'),
            );

            if (isset($_POST['postdate']) && $_POST['postdate'] != '') {
                $data['postdate'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('postdate')));
            } else {
                $data['postdate'] = null;
            }

            if (isset($_POST['purchase_date']) && $_POST['purchase_date'] != '') {
                $data['purchase_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('purchase_date')));
            } else {
                $data['purchase_date'] = null;
            }

            if (isset($_POST['bill_date']) && $_POST['bill_date'] != '') {
                $data['bill_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('bill_date')));
            } else {
                $data['bill_date'] = null;
            }
            $this->book_model->addbooks($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/book/getall');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('books', 'can_edit')) {
            access_denied();
        }

        $data['title']      = 'Edit Book';
        $data['title_list'] = 'Book Details';
        $data['id']         = $id;
        $editbook           = $this->book_model->get($id);
        $data['editbook']   = $editbook;
        $this->form_validation->set_rules('book_title', $this->lang->line('book_title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('perunitcost', $this->lang->line('book_price'), 'numeric');

        
        if ($this->form_validation->run() == false) {
            $listbook         = $this->book_model->listbook();
            $data['listbook'] = $listbook;
            $this->load->view('layout/header');
            $this->load->view('admin/book/editbook', $data);
            $this->load->view('layout/footer');
        } else {
            
            if($this->input->post('perunitcost')){
                $perunitcost    =   convertCurrencyFormatToBaseAmount($this->input->post('perunitcost'));
            }else{
                $perunitcost    = '';
            }
            
            $data = array(
                'id'               => $this->input->post('id'),
                'book_title'       => $this->input->post('book_title'),
                'book_no'          => $this->input->post('book_no'),
                'isbn_no'          => $this->input->post('isbn_no'),
                'subject'          => $this->input->post('subject'),
                'rack_no'          => $this->input->post('rack_no'),
                'publish'          => $this->input->post('publish'),
                'author'           => $this->input->post('author'),
                'perunitcost'      => $perunitcost,
                'description'      => $this->input->post('description'),
                'barcode'          => $this->input->post('barcode'),
                'category_name'    => $this->input->post('category_name'),
                'subcategory_name' => $this->input->post('subcategory_name'),
                'author2'          => $this->input->post('author2'),
                'edition'          => $this->input->post('edition'),
                'medium'           => $this->input->post('medium'),
                'book_type'        => $this->input->post('book_type'),
                'shelf_id'         => $this->input->post('shelf_id'),
                'class_no'         => $this->input->post('class_no'),
                'edition_type'     => $this->input->post('edition_type'),
                'publish_year'     => $this->input->post('publish_year'),
                'bill_no'          => $this->input->post('bill_no'),
                'pages'            => $this->input->post('pages'),
                'department'       => $this->input->post('department'),
            );

            if (isset($_POST['postdate']) && $_POST['postdate'] != '') {
                $data['postdate'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('postdate')));
            } else {
                $data['postdate'] = null;
            }

            if (isset($_POST['purchase_date']) && $_POST['purchase_date'] != '') {
                $data['purchase_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('purchase_date')));
            } else {
                $data['purchase_date'] = null;
            }

            if (isset($_POST['bill_date']) && $_POST['bill_date'] != '') {
                $data['bill_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('bill_date')));
            } else {
                $data['bill_date'] = null;
            }

            $this->book_model->addbooks($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/book/getall');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('books', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $this->book_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/book/getall');
    }

    public function bookdetail($id)
    {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'library/book');
        $book = $this->book_model->get($id);
        $data['book'] = $book;
        $data['title'] = 'Book Details - ' . $book['book_title'];

        $this->load->model('bookissue_model'); // Load bookissue_model

        $total_count = 0;
        $available_count = 0;

        if (!empty($book)) {
            $matching_books = array();
            if (!empty($book['isbn_no'])) {
                // Search by ISBN
                $matching_books = $this->book_model->getBooksByISBN($book['isbn_no']);
            } else {
                // Search by title and author
                $matching_books = $this->book_model->getBooksByTitleAuthor($book['book_title'], $book['author']);
            }

            $total_count = count($matching_books);
            $issued_count = 0;

            foreach ($matching_books as $matching_book) {
                $issued_count += $this->bookissue_model->countIssuedBookById($matching_book['id']);
            }
            $available_count = $total_count - $issued_count;
        }

        $data['total_count'] = $total_count;
        $data['available_count'] = $available_count;

        $this->load->view('layout/header');
        $this->load->view('admin/book/bookdetail', $data);
        $this->load->view('layout/footer');
    }

    public function getAvailQuantity()
    {
        $book_id   = $this->input->post('book_id');
        $available = 0;
        if ($book_id != "") {
            $result    = $this->bookissue_model->getAvailQuantity($book_id);
            $available = $result->qty - $result->total_issue;
        }
        $result_final = array('status' => '1', 'qty' => $available);
        echo json_encode($result_final);
    }

    public function import()
    {
        $data['fields'] = array('book_title', 'book_no', 'barcode', 'category_name', 'subcategory_name', 'isbn_no', 'subject', 'rack_no', 'shelf_id', 'class_no', 'publish', 'author', 'author2', 'edition', 'edition_type', 'medium', 'book_type', 'publish_year', 'perunitcost', 'purchase_date', 'bill_no', 'bill_date', 'pages', 'department', 'description', 'available', 'is_active');
        $this->form_validation->set_rules('file', $this->lang->line('images'), 'callback_handle_csv_upload');
        if ($this->form_validation->run() == false) {

            $this->load->view('layout/header');
            $this->load->view('admin/book/import', $data);
            $this->load->view('layout/footer');
        } else {
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {
                    $file = $_FILES['file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file);

                    $rowcount = 0;
                    if (!empty($result)) {
                        foreach ($result as $r_key => $r_value) {
                            
                            if($this->encoding_lib->toUTF8($result[$r_key]['perunitcost'])){
                                $perunitcost = convertCurrencyFormatToBaseAmount($this->encoding_lib->toUTF8($result[$r_key]['perunitcost']));
                            }else{
                                $perunitcost = 0;
                            }
                            
                            $result[$r_key]['book_title']  = $this->encoding_lib->toUTF8($result[$r_key]['book_title']);
                            $result[$r_key]['book_no']     = $this->encoding_lib->toUTF8($result[$r_key]['book_no']);
                            $result[$r_key]['isbn_no']     = $this->encoding_lib->toUTF8($result[$r_key]['isbn_no']);
                            $result[$r_key]['subject']     = $this->encoding_lib->toUTF8($result[$r_key]['subject']);
                            $result[$r_key]['rack_no']     = $this->encoding_lib->toUTF8($result[$r_key]['rack_no']);
                            $result[$r_key]['publish']     = $this->encoding_lib->toUTF8($result[$r_key]['publish']);
                            $result[$r_key]['author']      = $this->encoding_lib->toUTF8($result[$r_key]['author']);
                            $result[$r_key]['perunitcost'] = $perunitcost;
                            $result[$r_key]['postdate']    = $this->encoding_lib->toUTF8($result[$r_key]['postdate']);
                            $result[$r_key]['description'] = $this->encoding_lib->toUTF8($result[$r_key]['description']);
                            $result[$r_key]['available']   = $this->encoding_lib->toUTF8($result[$r_key]['available']);
                            $result[$r_key]['is_active']   = $this->encoding_lib->toUTF8($result[$r_key]['is_active']);
                            
                            // New fields
                            $result[$r_key]['barcode']          = $this->encoding_lib->toUTF8($result[$r_key]['barcode']);
                            $result[$r_key]['category_name']    = $this->encoding_lib->toUTF8($result[$r_key]['category_name']);
                            $result[$r_key]['subcategory_name'] = $this->encoding_lib->toUTF8($result[$r_key]['subcategory_name']);
                            $result[$r_key]['author2']          = $this->encoding_lib->toUTF8($result[$r_key]['author2']);
                            $result[$r_key]['edition']          = $this->encoding_lib->toUTF8($result[$r_key]['edition']);
                            $result[$r_key]['medium']           = $this->encoding_lib->toUTF8($result[$r_key]['medium']);
                            $result[$r_key]['book_type']        = $this->encoding_lib->toUTF8($result[$r_key]['book_type']);
                            $result[$r_key]['shelf_id']         = $this->encoding_lib->toUTF8($result[$r_key]['shelf_id']);
                            $result[$r_key]['class_no']         = $this->encoding_lib->toUTF8($result[$r_key]['class_no']);
                            $result[$r_key]['edition_type']     = $this->encoding_lib->toUTF8($result[$r_key]['edition_type']);
                            $result[$r_key]['publish_year']     = $this->encoding_lib->toUTF8($result[$r_key]['publish_year']);
                            
                            // Handle date fields
                            if (isset($result[$r_key]['purchase_date']) && $result[$r_key]['purchase_date'] != '') {
                                $result[$r_key]['purchase_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->encoding_lib->toUTF8($result[$r_key]['purchase_date'])));
                            } else {
                                $result[$r_key]['purchase_date'] = null;
                            }
                            
                            $result[$r_key]['bill_no']          = $this->encoding_lib->toUTF8($result[$r_key]['bill_no']);
                            
                            if (isset($result[$r_key]['bill_date']) && $result[$r_key]['bill_date'] != '') {
                                $result[$r_key]['bill_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->encoding_lib->toUTF8($result[$r_key]['bill_date'])));
                            } else {
                                $result[$r_key]['bill_date'] = null;
                            }
                            
                            $result[$r_key]['pages']            = $this->encoding_lib->toUTF8($result[$r_key]['pages']);
                            $result[$r_key]['department']       = $this->encoding_lib->toUTF8($result[$r_key]['department']);
                            
                            $rowcount++;
                        }

                        $this->db->insert_batch('books', $result);
                    }
                    $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('records_found_in_csv_file_total') . ' ' . $rowcount . ' ' . $this->lang->line('records_imported_successfully'));
                }
            } else {
                $msg = array(
                    'e' => $this->lang->line('the_file_field_is_required'),
                );
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">' . $this->lang->line('total') . ' ' . count($result) . "  " . $this->lang->line('records_found_in_csv_file_total') . " " . $rowcount . ' ' . $this->lang->line('records_imported_successfully') . '</div>');
            redirect('admin/book/import');
        }
    }

    public function import_new()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            if ($ext == 'csv') {
                $file = $_FILES['file']['tmp_name'];
                $this->load->library('CSVReader');
                $result = $this->csvreader->parse_file($file);

                $rowcount = 0;
                if (!empty($result)) {
                    foreach ($result as $r_key => $r_value) {
                        $result[$r_key]['book_title']  = $this->encoding_lib->toUTF8($result[$r_key]['book_title']);
                        $result[$r_key]['book_no']     = $this->encoding_lib->toUTF8($result[$r_key]['book_no']);
                        $result[$r_key]['isbn_no']     = $this->encoding_lib->toUTF8($result[$r_key]['isbn_no']);
                        $result[$r_key]['subject']     = $this->encoding_lib->toUTF8($result[$r_key]['subject']);
                        $result[$r_key]['rack_no']     = $this->encoding_lib->toUTF8($result[$r_key]['rack_no']);
                        $result[$r_key]['publish']     = $this->encoding_lib->toUTF8($result[$r_key]['publish']);
                        $result[$r_key]['author']      = $this->encoding_lib->toUTF8($result[$r_key]['author']);
                        $result[$r_key]['qty']         = $this->encoding_lib->toUTF8($result[$r_key]['qty']);
                        $result[$r_key]['perunitcost'] = convertCurrencyFormatToBaseAmount($this->encoding_lib->toUTF8($result[$r_key]['perunitcost']));
                        $result[$r_key]['postdate']    = $this->encoding_lib->toUTF8($result[$r_key]['postdate']);
                        $result[$r_key]['description'] = $this->encoding_lib->toUTF8($result[$r_key]['description']);
                        $rowcount++;
                    }

                    $this->db->insert_batch('books', $result);
                }
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('records_found_in_csv_file_total') . $rowcount . $this->lang->line('records_imported_successfully'));
            }
        } else {
            $msg = array(
                'e' => $this->lang->line('the_file_field_is_required'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        }

        echo json_encode($array);
    }

    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array('text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt');
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
        $filepath = "./backend/import/import_book_sample_file.csv";
        $data     = file_get_contents($filepath);
        $name     = 'import_book_sample_file.csv';
        force_download($name, $data);
    }

    public function issue_report()
    {
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'Library/book/issue_report');
        $data['title']        = 'Add Teacher';
        $teacher_result       = $this->teacher_model->getLibraryTeacher();
        $data['teacherlist']  = $teacher_result;
        $genderList           = $this->customlib->getGender();
        $data['genderList']   = $genderList;
        $issued_books         = $this->bookissue_model->getissueMemberBooks();
        $data['issued_books'] = $issued_books;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/book/issuereport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function issue_returnreport()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/library');
        $this->session->set_userdata('subsub_menu', 'Reports/library/issue_returnreport');
        $data['title']        = 'Add Teacher';
        $teacher_result       = $this->teacher_model->getLibraryTeacher();
        $data['searchlist']   = $this->customlib->get_searchtype();        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/book/issue_returnreport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getbooklist()
    {
        $listbook        = $this->book_model->getbooklist();
        $m               = json_decode($listbook);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $editbtn   = '';
                $deletebtn = '';

                if ($this->rbac->hasPrivilege('books', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "admin/book/edit/" . $value->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }

                if ($this->rbac->hasPrivilege('books', 'can_delete')) {
                    $deletebtn = "<a onclick='return confirm(" . '"' . $this->lang->line('delete_confirm') . '"' . "  )' href='" . base_url() . "admin/book/delete/" . $value->id . "' class='btn btn-default btn-xs' title='" . $this->lang->line('delete') . "' data-toggle='tooltip'><i class='fa fa-trash'></i></a>";
                }

                $row   = array();
                $row[] = $value->book_title;
                if ($value->description == "") {
                    $row[] = $this->lang->line('no_description');
                } else {
                    $row[] = $value->description;
                }
                $row[]     = $value->book_no;
                $row[]     = $value->isbn_no;
                $row[]     = $value->publish;
                $row[]     = $value->author;
                $row[]     = $value->subject;
                $row[]     = $value->rack_no;
                
                
                $row[]     = $currency_symbol . amountFormat($value->perunitcost);
                $row[]     = $this->customlib->dateformat($value->postdate);
                $row[]     = $editbtn . ' ' . $deletebtn . ' ' . "<a href='" . base_url() . "admin/book/bookdetail/" . $value->id . "' class='btn btn-default btn-xs'  data-toggle='tooltip' title='View Details'><i class='fa fa-reorder'></i></a>";
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /* function to get book inventory report by using datatable */
    public function dtbookissuereturnreportlist()
    {
        /* search code start from here */
        $search_type = $this->input->post('search_type');
        $date_from   = $this->input->post('date_from');
        $date_to     = $this->input->post('date_to');
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }
        $sch_setting = $this->sch_setting_detail;
        $start_date    = date('Y-m-d', strtotime($dates['from_date']));
        $end_date      = date('Y-m-d', strtotime($dates['to_date']));
        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

        /* search code ends here */
        $issued_books = $this->bookissue_model->getissuereturnMemberBooks(' ', $start_date, $end_date);

        $resultlist = json_decode($issued_books);
        $dt_data    = array();

        if (!empty($resultlist->data)) {

            $editbtn   = "";
            $deletebtn = "";

            $getStaffRole = $this->customlib->getStaffRole();
            $staffrole    = json_decode($getStaffRole);

            foreach ($resultlist->data as $resultlist_key => $value) {

                $row = array();

                $row[] = $value->book_title;
                $row[] = $value->book_no;
                $row[] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->issue_date));
                $row[] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->return_date));
                $row[] = $value->members_id;
                $row[] = $value->library_card_no;
                
                if ($value->admission) {
                    $admission = ' (' . $value->admission . ')';                    
                } else {
                    $admission = '';                    
                }              
                
                $row[] = $this->customlib->getFullName($value->fname, $value->mname, $value->lname, $sch_setting->middlename, $sch_setting->lastname) . $admission;               
                
                $row[] = $this->lang->line($value->member_type);

                $dt_data[] = $row;
            }
        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

}
