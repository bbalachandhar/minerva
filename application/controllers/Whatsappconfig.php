<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Whatsappconfig extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
		
		$this->load->model('whatsappconfig_model');
		
		 
    }

    public function index()
    {         
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'whatsappconfig/index');
        $data['title']      = 'SMS Config List';
        $result         = $this->whatsappconfig_model->get();
        $data['statuslist'] = $this->customlib->getStatus();
        $data['list']    = $result;
        $this->load->view('layout/header', $data);
        $this->load->view('whatsappconfig/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function instructions()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'whatsappconfig/index');
        $data['title'] = 'WhatsApp Integration Guide';
        $this->load->view('layout/header', $data);
        $this->load->view('whatsappconfig/instructions', $data);
        $this->load->view('layout/footer', $data);
    }

    public function twilio()
    {
        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_rules('twilio_account_sid', $this->lang->line('twilio_account_sid'), 'required');
        $this->form_validation->set_rules('twilio_auth_token', $this->lang->line('authentication_token'), 'required');
        $this->form_validation->set_rules('twilio_sender_phone_number', $this->lang->line('registered_phone_number'), 'required');
        $this->form_validation->set_rules('twilio_status', $this->lang->line('status'), 'required');

        if ($this->form_validation->run()) {

            $data = array(
                'type'      => 'twilio',
                'name'      => '',
                'api_id'    => '',
                'authkey'   => '',
                'senderid'  => '',
                'username'  => $this->input->post('twilio_account_sid'),
                'password'  => $this->input->post('twilio_auth_token'),
                'contact'   => $this->input->post('twilio_sender_phone_number'),
                'is_active' => $this->input->post('twilio_status'),
            );
            $this->whatsappconfig_model->add($data);
            echo json_encode(array('st' => 0, 'msg' => $this->lang->line('update_message')));
        } else {

            $data = array(
                'twilio_account_sid'         => form_error('twilio_account_sid'),
                'twilio_auth_token'          => form_error('twilio_auth_token'),
                'twilio_sender_phone_number' => form_error('twilio_sender_phone_number'),
                'twilio_status'              => form_error('twilio_status'),
            );

            echo json_encode(array('st' => 1, 'msg' => $data));
        }
    }


    public function metawhatsapp()
    {
        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_rules('meta_access_token', "Access Token", 'required');
		$this->form_validation->set_rules('meta_sender_phone_number', $this->lang->line('registered_phone_number'), 'required');
        $this->form_validation->set_rules('meta_language', $this->lang->line('language'), 'required');        
        $this->form_validation->set_rules('meta_status', $this->lang->line('status'), 'required');

        if ($this->form_validation->run()) {

            $data = array(
                'type'      => 'meta',
                'name'      => '',
                'api_id'    => '',
                'senderid'  => '',
                'language'  => $this->input->post('meta_language'),
                'authkey'   => $this->input->post('meta_access_token'),
                'contact'   => $this->input->post('meta_sender_phone_number'),
                'waba_id'   => $this->input->post('meta_waba_id'),
                'is_active' => $this->input->post('meta_status'),
            );
            $this->whatsappconfig_model->add($data);
            echo json_encode(array('st' => 0, 'msg' => $this->lang->line('update_message')));
        } else {

            $data = array(
                'meta_language'         		=> form_error('meta_language'),
                'meta_access_token'          	=> form_error('meta_access_token'),
                'meta_sender_phone_number' 		=> form_error('meta_sender_phone_number'),
                'meta_status'              		=> form_error('meta_status'),
            );

            echo json_encode(array('st' => 1, 'msg' => $data));
        }
    }

    public function activate_phone_number()
    {
        $phone_number_id = $this->input->post('phone_number_id');
        $pin             = $this->input->post('pin');

        if (empty($phone_number_id) || !preg_match('/^\d{6}$/', $pin)) {
            echo json_encode(['st' => 1, 'msg' => 'Invalid Phone Number ID or PIN.']);
            return;
        }

        // Retrieve the saved System User token from config
        $result = $this->whatsappconfig_model->get();
        $meta   = null;
        foreach ($result as $row) {
            if ($row->type === 'meta') { $meta = $row; break; }
        }

        if (empty($meta->authkey)) {
            echo json_encode(['st' => 1, 'msg' => 'Access Token not saved yet. Save your Meta config first.']);
            return;
        }

        $url     = 'https://graph.facebook.com/v19.0/' . urlencode($phone_number_id) . '/register';
        $payload = json_encode(['messaging_product' => 'whatsapp', 'pin' => $pin]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $meta->authkey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($http_code === 200 && !empty($decoded['success'])) {
            echo json_encode(['st' => 0, 'msg' => 'Phone number activated successfully.']);
        } else {
            $error = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Unknown error (HTTP ' . $http_code . ')';
            echo json_encode(['st' => 1, 'msg' => $error]);
        }
    }

     
}