<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APPPATH . "third_party/vendor/autoload.php";

use Mpdf\Mpdf;

class M_pdf
{

    public function __construct()
    {
        $CI = &get_instance();
        log_message('Debug', 'mPDF class is loaded.');
    }

    public function load($param = NULL)
    {
        $tmpDir = __DIR__ . '/../tmp';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        if (!is_dir($tmpDir . '/mpdf')) {
            @mkdir($tmpDir . '/mpdf', 0775, true);
        }
        if (!is_dir($tmpDir . '/mpdf/ttfontdata')) {
            @mkdir($tmpDir . '/mpdf/ttfontdata', 0775, true);
        }

        if($param ==NULL){
            return  new Mpdf([
                'tempDir' => $tmpDir,
                'mode' => 'utf-8',
                'default_font' => 'roboto',
                'margin_left' => 2,
                'margin_right' => 2,
                'margin_top' => 2,
                'margin_bottom' => 2,
                'format' => 'Legal'
            ]);
        }else{
            if (!isset($param['tempDir'])) {
                $param['tempDir'] = $tmpDir;
            }
            return  new Mpdf($param);
        }

    }
}
