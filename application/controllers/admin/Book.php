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
        $data['fields'] = array('book_title', 'book_no', 'barcode', 'category_name', 'subcategory_name', 'isbn_no', 'subject', 'rack_name', 'shelf_name', 'class_no', 'publisher_name', 'author', 'author2', 'edition', 'edition_type', 'medium', 'book_type_name', 'publish_year', 'perunitcost', 'purchase_date', 'bill_no', 'bill_date', 'pages', 'department', 'description', 'available', 'is_active', 'publish', 'postdate', 'vendor');
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

                            if (array_key_exists('perunitcost', $result[$r_key]) && $this->encoding_lib->toUTF8($result[$r_key]['perunitcost'])) {
                                $perunitcost = convertCurrencyFormatToBaseAmount($this->encoding_lib->toUTF8($result[$r_key]['perunitcost']));
                            } else {
                                $perunitcost = 0;
                            }

                            $book_data = array(
                                'book_title'  => array_key_exists('book_title', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['book_title']) : null,
                                'book_no'     => array_key_exists('book_no', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['book_no']) : null,
                                'isbn_no'     => array_key_exists('isbn_no', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['isbn_no']) : null,
                                'author'      => array_key_exists('author', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['author']) : null,
                                'author2'     => array_key_exists('author2', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['author2']) : null,
                                'edition'     => array_key_exists('edition', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['edition']) : null,
                                'medium'      => array_key_exists('medium', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['medium']) : null,
                                'class_no'    => array_key_exists('class_no', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['class_no']) : null,
                                'edition_type' => array_key_exists('edition_type', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['edition_type']) : null,
                                'publish_year' => array_key_exists('publish_year', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['publish_year']) : null,
                                'perunitcost' => $perunitcost,
                                'postdate'    => array_key_exists('postdate', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['postdate']) : null,
                                'description' => array_key_exists('description', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['description']) : null,
                                'available'   => array_key_exists('available', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['available']) : null,
                                'is_active'   => array_key_exists('is_active', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['is_active']) : null,
                                'barcode'     => array_key_exists('barcode', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['barcode']) : null,
                                'bill_no'          => array_key_exists('bill_no', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['bill_no']) : null,
                                'pages'            => array_key_exists('pages', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['pages']) : null,
                                'department'       => array_key_exists('department', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['department']) : null,
                                'publish'          => array_key_exists('publish', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['publish']) : null,
                                'vendor'           => array_key_exists('vendor', $result[$r_key]) ? $this->encoding_lib->toUTF8($result[$r_key]['vendor']) : null
                            );

                            if (array_key_exists('postdate', $result[$r_key]) && !empty($result[$r_key]['postdate'])) {
                                $date_string = $this->encoding_lib->toUTF8($result[$r_key]['postdate']);
                                $valid_date = null;
                                // Try common date formats
                                $formats = array('Y-m-d', 'm-d-Y', 'd-m-Y', 'Y/m/d', 'm/d/Y', 'd/m/Y', 'Y.m.d', 'm.d.Y', 'd.m.Y');
                                foreach ($formats as $format) {
                                    $dt = DateTime::createFromFormat($format, $date_string);
                                    if ($dt && $dt->format($format) === $date_string) {
                                        $valid_date = $dt->format('Y-m-d');
                                        break;
                                    }
                                }
                                if ($valid_date) {
                                    $book_data['postdate'] = $valid_date;
                                } else {
                                    // Fallback to original datetostrtotime if custom validation fails, but still guard it
                                    try {
                                        $book_data['postdate'] = date('Y-m-d', $this->customlib->datetostrtotime($date_string));
                                    } catch (Exception $e) {
                                        $book_data['postdate'] = null;
                                    }
                                }
                            } else {
                                $book_data['postdate'] = null;
                            }

                            // Handle category_name
                            $category_name = trim($this->encoding_lib->toUTF8(array_key_exists('category_name', $result[$r_key]) ? $result[$r_key]['category_name'] : ''));
                            $category_id = null;
                            if (!empty($category_name)) {
                                $this->load->model('librarycategory_model');
                                $category = $this->librarycategory_model->get_category_by_name_case_insensitive($category_name);
                                if ($category) {
                                    $category_id = $category->id;
                                } else {
                                    $new_category_data = array('category_name' => $category_name);
                                    $category_id = $this->librarycategory_model->add($new_category_data);
                                }
                            }
                            $book_data['category_name'] = $category_name;

                            // Handle subcategory_name
                            $subcategory_name = trim($this->encoding_lib->toUTF8(array_key_exists('subcategory_name', $result[$r_key]) ? $result[$r_key]['subcategory_name'] : ''));
                            $subcategory_id = null;
                            if (!empty($subcategory_name) && !empty($category_id)) {
                                $this->load->model('librarysubcategory_model');
                                $subcategory = $this->librarysubcategory_model->get_subcategory_by_name_and_category_id_case_insensitive($subcategory_name, $category_id);
                                if ($subcategory) {
                                    $subcategory_id = $subcategory->id;
                                } else {
                                    $new_subcategory_data = array('subcategory_name' => $subcategory_name, 'category_id' => $category_id);
                                    $subcategory_id = $this->librarysubcategory_model->add($new_subcategory_data);
                                }
                            }
                            $book_data['subcategory_name'] = $subcategory_name;

                            // Handle publisher_name
                            $publisher_name = trim($this->encoding_lib->toUTF8(array_key_exists('publisher_name', $result[$r_key]) ? $result[$r_key]['publisher_name'] : ''));
                            $publisher_id = null;
                            if (!empty($publisher_name)) {
                                $this->load->model('librarypublisher_model');
                                $publisher = $this->librarypublisher_model->get_publisher_by_name_case_insensitive($publisher_name);
                                if ($publisher) {
                                    $publisher_id = $publisher->id;
                                } else {
                                    $new_publisher_data = array('publisher_name' => $publisher_name);
                                    $publisher_id = $this->librarypublisher_model->add($new_publisher_data);
                                }
                            }
                            $book_data['publish'] = $publisher_name;

                            // Handle rack_name (Position Rack)
                            $rack_name = trim($this->encoding_lib->toUTF8(array_key_exists('rack_name', $result[$r_key]) ? $result[$r_key]['rack_name'] : ''));
                            $rack_id = null;
                            if (!empty($rack_name)) {
                                $this->load->model('librarypositionrack_model');
                                $rack = $this->librarypositionrack_model->get_rack_by_name_case_insensitive($rack_name);
                                if ($rack) {
                                    $rack_id = $rack->id;
                                } else {
                                    $new_rack_data = array('rack_name' => $rack_name);
                                    $rack_id = $this->librarypositionrack_model->add($new_rack_data);
                                }
                            }
                            $book_data['rack_no'] = $rack_name;

                            // Handle shelf_name (Position Shelf)
                            $shelf_name = trim($this->encoding_lib->toUTF8(isset($result[$r_key]['shelf_name']) ? $result[$r_key]['shelf_name'] : ''));
                            $shelf_id = null;
                            if (!empty($shelf_name) && !empty($rack_id)) {
                                $this->load->model('librarypositionshelf_model');
                                $shelf = $this->librarypositionshelf_model->get_shelf_by_name_and_rack_id_case_insensitive($shelf_name, $rack_id);
                                if ($shelf) {
                                    $shelf_id = $shelf->id;
                                } else {
                                    $new_shelf_data = array('shelf_name' => $shelf_name, 'rack_id' => $rack_id);
                                    $shelf_id = $this->librarypositionshelf_model->add($new_shelf_data);
                                }
                            }
                            $book_data['shelf_id'] = $shelf_name;

                            // Handle book_type
                            $book_type_name = trim($this->encoding_lib->toUTF8(array_key_exists('book_type_name', $result[$r_key]) ? $result[$r_key]['book_type_name'] : ''));
                            $book_type_id = null;
                            if (!empty($book_type_name)) {
                                $this->load->model('librarybooktype_model');
                                $book_type = $this->librarybooktype_model->get_book_type_by_name_case_insensitive($book_type_name);
                                if ($book_type) {
                                    $book_type_id = $book_type->id;
                                } else {
                                    $new_book_type_data = array('book_type_name' => $book_type_name);
                                    $book_type_id = $this->librarybooktype_model->add($new_book_type_data);
                                }
                            }
                            $book_data['book_type'] = $book_type_name;

                            // Handle subject
                            $subject_name = trim($this->encoding_lib->toUTF8(array_key_exists('subject', $result[$r_key]) ? $result[$r_key]['subject'] : ''));
                            $subject_id = null;
                            if (!empty($subject_name)) {
                                $this->load->model('librarysubject_model');
                                $subject = $this->librarysubject_model->get_subject_by_name_case_insensitive($subject_name);
                                if ($subject) {
                                    $subject_id = $subject->id;
                                } else {
                                    $new_subject_data = array('subject_name' => $subject_name);
                                    $added_subject_id = $this->librarysubject_model->add($new_subject_data);
                                    if ($added_subject_id) {
                                        $subject_id = $added_subject_id;
                                    } else {
                                        $subject_id = null; // Failed to add subject to master table
                                    }
                                }
                            }
                            $book_data['subject'] = $subject_name;

                            // Handle vendor
                            $vendor_name = trim($this->encoding_lib->toUTF8(array_key_exists('vendor', $result[$r_key]) ? $result[$r_key]['vendor'] : ''));
                            $vendor_id = null;
                            if (!empty($vendor_name)) {
                                $this->load->model('libraryvendor_model');
                                $vendor = $this->libraryvendor_model->get_vendor_by_name_case_insensitive($vendor_name);
                                if ($vendor) {
                                    $vendor_id = $vendor->id;
                                } else {
                                    $new_vendor_data = array('vendor_name' => $vendor_name);
                                    $added_vendor_id = $this->libraryvendor_model->add($new_vendor_data);
                                    if ($added_vendor_id) {
                                        $vendor_id = $added_vendor_id;
                                    } else {
                                        $vendor_id = null; // Failed to add vendor to master table
                                    }
                                }
                            }
                            $book_data['vendor'] = $vendor_name;

                            // Handle date fields
                            if (array_key_exists('purchase_date', $result[$r_key]) && !empty($result[$r_key]['purchase_date'])) {
                                $date_string = $this->encoding_lib->toUTF8($result[$r_key]['purchase_date']);
                                $valid_date = null;
                                // Try common date formats
                                $formats = array('Y-m-d', 'm-d-Y', 'd-m-Y', 'Y/m/d', 'm/d/Y', 'd/m/Y', 'Y.m.d', 'm.d.Y', 'd.m.Y');
                                foreach ($formats as $format) {
                                    $dt = DateTime::createFromFormat($format, $date_string);
                                    if ($dt && $dt->format($format) === $date_string) {
                                        $valid_date = $dt->format('Y-m-d');
                                        break;
                                    }
                                }
                                if ($valid_date) {
                                    $book_data['purchase_date'] = $valid_date;
                                } else {
                                    // Fallback to original datetostrtotime if custom validation fails, but still guard it
                                    try {
                                        $book_data['purchase_date'] = date('Y-m-d', $this->customlib->datetostrtotime($date_string));
                                    } catch (Exception $e) {
                                        $book_data['purchase_date'] = null;
                                    }
                                }
                            } else {
                                $book_data['purchase_date'] = null;
                            }

                            $book_data['bill_no']          = $this->encoding_lib->toUTF8($result[$r_key]['bill_no']);

                            if (array_key_exists('bill_date', $result[$r_key]) && !empty($result[$r_key]['bill_date'])) {
                                $date_string = $this->encoding_lib->toUTF8($result[$r_key]['bill_date']);
                                $valid_date = null;
                                // Try common date formats
                                $formats = array('Y-m-d', 'm-d-Y', 'd-m-Y', 'Y/m/d', 'm/d/Y', 'd/m/Y', 'Y.m.d', 'm.d.Y', 'd.m.Y');
                                foreach ($formats as $format) {
                                    $dt = DateTime::createFromFormat($format, $date_string);
                                    if ($dt && $dt->format($format) === $date_string) {
                                        $valid_date = $dt->format('Y-m-d');
                                        break;
                                    }
                                }
                                if ($valid_date) {
                                    $book_data['bill_date'] = $valid_date;
                                } else {
                                    // Fallback to original datetostrtotime if custom validation fails, but still guard it
                                    try {
                                        $book_data['bill_date'] = date('Y-m-d', $this->customlib->datetostrtotime($date_string));
                                    } catch (Exception $e) {
                                        $book_data['bill_date'] = null;
                                    }
                                }
                            } else {
                                $book_data['bill_date'] = null;
                            }

                            $book_data['pages']            = $this->encoding_lib->toUTF8($result[$r_key]['pages']);
                            $book_data['department']       = $this->encoding_lib->toUTF8($result[$r_key]['department']);

                            $rowcount++;
                        }

                        $this->db->insert('books', $book_data);
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
                $row[] = !empty($value->book_title) ? $value->book_title : 'N/A';
                $row[] = !empty($value->description) ? $value->description : 'N/A';
                $row[] = !empty($value->book_no) ? $value->book_no : 'N/A';
                $row[] = !empty($value->isbn_no) ? $value->isbn_no : 'N/A';
                $row[] = !empty($value->publish) ? $value->publish : 'N/A';
                $row[] = !empty($value->author) ? $value->author : 'N/A';
                $row[] = !empty($value->subject) ? $value->subject : 'N/A';
                $row[] = !empty($value->rack_no) ? $value->rack_no : 'N/A';
                $row[] = !empty($value->shelf_id) ? $value->shelf_id : 'N/A'; // Added shelf_id
                
                $row[] = $currency_symbol . amountFormat($value->perunitcost);
                $row[] = !empty($value->postdate) ? $this->customlib->dateformat($value->postdate) : 'N/A';
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
