<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Opaq extends Admin_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model("book_model"); // Will need this for book data
        $this->load->model("librarymanagement_model"); // May need this
    }

    function index() {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) { // Using 'books' privilege for now
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'library/opaq'); // New submenu item
        $data['title'] = $this->lang->line('opaq'); // Using the lang key you provided

        // Load necessary models for dropdowns
        $this->load->model('book_model');

        // Fetch data for dropdowns
        $data['book_titles'] = $this->book_model->get_all_book_titles();
        $data['authors'] = $this->book_model->get_all_authors();
        $data['publishers'] = $this->book_model->get_all_publishers();
        $data['subjects'] = $this->book_model->get_all_subjects();

        $this->load->view('layout/header');
        $this->load->view('admin/book/opaq_list', $data); // New view file
        $this->load->view('layout/footer');
    }

    // This method will handle the AJAX request for the DataTable
    function getopaqlist() {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) {
            echo json_encode(array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array(),
                "error" => "Access Denied"
            ));
            exit(); // Exit after sending JSON error
        }

        $this->load->model('book_model');
        $this->load->model('bookissue_model');

        $book_title = $this->input->post('book_title');
        $author = $this->input->post('author');
        $barcode = $this->input->post('barcode');
        $accession_no = $this->input->post('accession_no');
        $publisher = $this->input->post('publisher');
        $subject = $this->input->post('subject');

        $search_params = array(
            'book_title' => $book_title,
            'author' => $author,
            'barcode' => $barcode,
            'accession_no' => $accession_no,
            'publisher' => $publisher,
            'subject' => $subject,
        );

        $listbook = $this->book_model->getOpaqBooklist($search_params); // New model method
        echo $listbook;
    }
}
