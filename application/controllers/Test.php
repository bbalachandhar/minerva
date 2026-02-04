<?php
// application/controllers/Test.php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Test extends Admin_Controller {
    public function department_headcount() {
        $this->load->helper('department_headcount_helper');
        $result = get_department_headcount($this);
        echo '<pre>';
        print_r($result);
        echo '</pre>';
        exit;
    }
}
