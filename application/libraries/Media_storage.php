<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Media_storage
{

    private $_CI;

    public function __construct()
    {
        $this->_CI = &get_instance();
        $this->_CI->load->library('customlib');

    }

    public function fileupload($media_name, $upload_path = "")
    {
        if (!isset($_FILES[$media_name]) || !is_array($_FILES[$media_name]) || $_FILES[$media_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return array('status' => false, 'message' => 'No file was uploaded.');
        }

        $error_code = $_FILES[$media_name]['error'];
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return array('status' => false, 'message' => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.');
            case UPLOAD_ERR_FORM_SIZE:
                return array('status' => false, 'message' => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
            case UPLOAD_ERR_PARTIAL:
                return array('status' => false, 'message' => 'The uploaded file was only partially uploaded.');
            case UPLOAD_ERR_NO_TMP_DIR:
                return array('status' => false, 'message' => 'Missing a temporary folder.');
            case UPLOAD_ERR_CANT_WRITE:
                return array('status' => false, 'message' => 'Failed to write file to disk.');
            case UPLOAD_ERR_EXTENSION:
                return array('status' => false, 'message' => 'A PHP extension stopped the file upload.');
        }

        $name        = $_FILES[$media_name]['name'];
        $file_name   = time() . "-" . uniqid(rand()) . "-" . $name;
        // ensure upload path ends with a slash
        $upload_path = rtrim($upload_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $destination = $this->_CI->customlib->getFolderPath() . $upload_path . $file_name;

        // Ensure destination directory exists and is writable. Create it if missing and set permissions.
        $destination_dir = dirname($destination);
        if (!is_dir($destination_dir)) {
            if (!@mkdir($destination_dir, 0755, true)) {
                return array('status' => false, 'message' => 'Failed to create upload directory: ' . $destination_dir);
            }
            @chmod($destination_dir, 0755);
        }

        // Attempt to make writable if not already
        if (!is_writable($destination_dir)) {
            @chmod($destination_dir, 0755);
        }
        if (!is_writable($destination_dir)) {
            @chmod($destination_dir, 0777);
        }
        if (!is_writable($destination_dir)) {
            return array('status' => false, 'message' => 'Upload directory is not writable: ' . $destination_dir);
        }

        if (move_uploaded_file($_FILES[$media_name]["tmp_name"], $destination)) {
            return array('status' => true, 'message' => $file_name);
        } else {
            return array('status' => false, 'message' => 'File upload failed: Could not move uploaded file to destination.');
        }
    }

    public function filedownload($file_name, $download_path = "")
    {

        $file_url           = $this->_CI->customlib->getFolderPath() . $download_path . "/" . $file_name;
        $download_file_name = substr($file_name, (strpos($file_name, '!') + 1));
        $this->_CI->load->helper('download');
        $data = file_get_contents($file_url);
        force_download($download_file_name, $data);

    }

    public function fileview($file_name)
    {
        if (!IsNullOrEmptyString($file_name)) {

            $download_file_name = substr($file_name, (strpos($file_name, '!') + 1));
            return $download_file_name;
        }
        return null;

    }

    public function getImageURL($file_name)
    {
        if (!IsNullOrEmptyString($file_name)) {

            $download_file_name = $this->_CI->customlib->getBaseUrl() . $file_name . img_time();
            return $download_file_name;
        }
        return null;

    }

    public function filedelete($file_name, $path = "")
    {
        if (!IsNullOrEmptyString($file_name)) {

            $url = $this->_CI->customlib->getFolderPath() . $path . "/" . $file_name;

            if (file_exists($url)) {

                if (unlink($url)) {
                    return true;
                }

            }
        }

        return false;
    }

    public function getTmpFileSize($media_name)
    {
        if (!isset($_FILES[$media_name]) || !is_array($_FILES[$media_name])) {
            return 0;
        }

        $bytes = isset($_FILES[$media_name]['size']) ? (float) $_FILES[$media_name]['size'] : 0;
        if ($bytes <= 0) {
            return 0;
        }

        return $bytes / 1024 / 1024;
    }

    public function getUploadedFileSize($file_name, $path = "")
    {
        if (IsNullOrEmptyString($file_name)) {
            return 0;
        }

        $file_path = rtrim($this->_CI->customlib->getFolderPath() . $path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file_name;
        if (!file_exists($file_path)) {
            return 0;
        }

        $bytes = (float) @filesize($file_path);
        if ($bytes <= 0) {
            return 0;
        }

        return $bytes / 1024 / 1024;
    }

}
