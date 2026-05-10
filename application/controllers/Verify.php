<?php
/**
 * Public Hall Ticket Verification Controller
 * No authentication required — accessible by anyone with the QR hash URL.
 * URL: /verify/<qr_hash>
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Verify extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('coe/Coe_hallticket_model');
        $this->load->helper('url');
    }

    /**
     * Public verification page — called when QR code is scanned.
     * @param string $qr_hash  The 64-char SHA-256 HMAC stored in coe_hall_tickets.qr_hash
     */
    public function index($qr_hash = '')
    {
        // Sanitise — only allow hex characters (SHA-256 HMAC output)
        $qr_hash = preg_replace('/[^a-f0-9]/i', '', (string)$qr_hash);

        if (strlen($qr_hash) !== 64) {
            $this->_show_invalid('Invalid verification link.');
            return;
        }

        $ht = $this->Coe_hallticket_model->verifyByHash($qr_hash);

        if (!$ht) {
            $this->_show_invalid('Hall ticket not found. The link may be incorrect or expired.');
            return;
        }

        $data = [
            'ht'       => $ht,
            'is_valid' => (bool)($ht->is_valid ?? false),
        ];

        $this->load->view('public/hallticket_verify', $data);
    }

    private function _show_invalid($message)
    {
        $this->load->view('public/hallticket_verify', [
            'ht'       => null,
            'is_valid' => false,
            'error'    => $message,
        ]);
    }
}
