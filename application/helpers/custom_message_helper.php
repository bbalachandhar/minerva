<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('message_box'))
{
    function message_box($type = '', $message = '')
    {
        $CI =& get_instance();
        $output = '';
        if ($CI->session->flashdata('success')) {
            $output .= '<div class="alert alert-success">' . $CI->session->flashdata('success') . '</div>';
        }
        if ($CI->session->flashdata('error')) {
            $output .= '<div class="alert alert-danger">' . $CI->session->flashdata('error') . '</div>';
        }
        return $output;
    }
}
