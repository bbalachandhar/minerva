<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Whatsappconfig extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        // ensure whatsapp settings submenu exists under System Settings
        // load sidebarmenu model if not already
        $this->load->model('sidebarmenu_model');
        $this->createSidebarSubmenu();
    }

    /**
     * Add an entry to the system settings sidebar so users can navigate here
     * without depending on the SMS page button. Runs once per request but will
     * early exit if the entry already exists.
     */
    private function createSidebarSubmenu()
    {
        // identify "System Settings" parent menu record by language key
        $parent = $this->db->get_where('sidebar_menus', ['lang_key' => 'system_settings'])->row();
        if (!$parent) {
            return; // parent menu not available yet
        }
        $exists = $this->db->get_where('sidebar_sub_menus', ['url' => 'whatsappconfig'])->row();
        if ($exists) {
            return; // already created
        }
        // determine position: place directly below SMS Setting if found
        $sms_sub = $this->db->get_where('sidebar_sub_menus', ['sidebar_menu_id' => $parent->id, 'url' => 'smsconfig'])->row();
        $level = ($sms_sub && isset($sms_sub->level)) ? ($sms_sub->level + 1) : 1;
        $insert = [
            'sidebar_menu_id'     => $parent->id,
            'url'                 => 'whatsappconfig',
            'lang_key'            => 'whatsapp_setting',
            'menu'                => $this->lang->line('whatsapp_setting'),
            'activate_controller' => 'whatsappconfig',
            'activate_methods'    => 'index',
            'access_permissions'  => 'sms_setting',
            'level'               => $level,
        ];
        $this->sidebarmenu_model->addSubMenu($insert);
    }

    /**
     * Show list of WhatsApp service configurations (AskEva, Twilio etc.)
     */
    public function index()
    {
        if (!$this->rbac->hasPrivilege('sms_setting', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'whatsappconfig/index');
        $data['title'] = 'WhatsApp Config';

        // load all configs (we will filter in view)
        // get all entries and then migrate any legacy whatsapp type to our new vendor key
        $sms_result = $this->smsconfig_model->get();
        // if there is a record with type exactly 'whatsapp' treat it as AskEva (and update the db)
        foreach ($sms_result as $row) {
            if ($row->type === 'whatsapp') {
                // update database so future loads use the proper type
                $this->db->where('id', $row->id);
                $this->db->update('sms_config', array('type' => 'whatsapp_askeva'));
                $row->type = 'whatsapp_askeva';
            }
        }
        $data['whatsapplist'] = $sms_result;
        $data['statuslist'] = $this->customlib->getStatus();

        $this->load->view('layout/header', $data);
        $this->load->view('whatsappconfig/whatsappList', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Save AskEva whatsapp configuration
     */
    public function askeva()
    {
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('askeva_token', $this->lang->line('whatsapp_token'), 'required');
        $this->form_validation->set_rules('askeva_sender', $this->lang->line('whatsapp_sender'), 'required');
        $this->form_validation->set_rules('askeva_status', $this->lang->line('status'), 'required');

        if ($this->form_validation->run()) {
            $data = array(
                'type'      => 'whatsapp_askeva',
                'api_id'    => $this->input->post('askeva_token'),
                'contact'   => $this->input->post('askeva_sender'),
                'is_active' => $this->input->post('askeva_status'),
            );
            $this->smsconfig_model->add($data);
            echo json_encode(array('st' => 0, 'msg' => $this->lang->line('update_message')));
        } else {
            $data = array(
                'askeva_token'  => form_error('askeva_token'),
                'askeva_sender' => form_error('askeva_sender'),
                'askeva_status' => form_error('askeva_status'),
            );
            echo json_encode(array('st' => 1, 'msg' => $data));
        }
    }
}
