<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Naac extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function configuration()
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/configuration');
        $data['title'] = $this->lang->line('naac_configuration');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/configuration', $data);
        $this->load->view('layout/footer', $data);
    }

    public function iiqa()
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/iiqa');
        $data['title'] = $this->lang->line('naac_iiqa');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/iiqa', $data);
        $this->load->view('layout/footer', $data);
    }

    public function ssr()
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/ssr');
        $data['title'] = $this->lang->line('naac_ssr');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/ssr', $data);
        $this->load->view('layout/footer', $data);
    }

    public function aqar()
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/aqar');
        $data['title'] = $this->lang->line('naac_aqar');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/aqar', $data);
        $this->load->view('layout/footer', $data);
    }
}
