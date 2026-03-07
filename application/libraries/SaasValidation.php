<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SaasValidation
{
    public $CI;
    public $sass_enabled;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('ResourceQuota');
        $this->CI->load->library('customlib');
        $this->CI->load->config('saas-config');
        $this->sass_enabled = $this->CI->config->item('saas_enabled');
      
    }

    /**
     * Backward-compatible callback used by CBSE controllers.
     * Validates whether current uploaded files can fit in the storage quota.
     */
    public function validateCanUploadFile($str, $params_array)
    {
        if (!$this->sass_enabled) {
            return true;
        }

        if (!is_array($params_array)) {
            $params_array = array_map('trim', explode(',', (string) $params_array));
        }

        $uploaded_size = 0;
        foreach ($params_array as $field_name) {
            if (isset($_FILES[$field_name]) && !empty($_FILES[$field_name]['name']) && (int) $_FILES[$field_name]['size'] > 0) {
                $uploaded_size += ((float) $_FILES[$field_name]['size']) / 1024 / 1024;
            }
        }

        if ($uploaded_size <= 0) {
            return true;
        }

        return $this->getStorageLimit('storage', $uploaded_size);
    }

    /**
     * Backward-compatible helper used by CBSE controllers.
     * Reserves storage quota for currently uploaded files.
     */
    public function updateStorageLimit($resource, $fields)
    {
        if (!$this->sass_enabled) {
            return true;
        }

        if (!is_array($fields)) {
            $fields = array_map('trim', explode(',', (string) $fields));
        }

        $uploaded_size = 0;
        foreach ($fields as $field_name) {
            if (isset($_FILES[$field_name]) && !empty($_FILES[$field_name]['name']) && (int) $_FILES[$field_name]['size'] > 0) {
                $uploaded_size += ((float) $_FILES[$field_name]['size']) / 1024 / 1024;
            }
        }

        if ($uploaded_size <= 0) {
            return true;
        }

        return $this->updateResouceQuota($resource, $uploaded_size);
    }


    public function getResourceLimit($resource, $insert_qty)
    {
        if ($this->sass_enabled) { // check is saas is enabled 
            $detail = $this->CI->resourcequota->getResourceLimit($resource);
            $result_decode = json_decode($detail);


            // Check if decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg());
            }

            // Check the status in the response
            if ($result_decode->status) {
                // Perform necessary actions if status is true

                if ($result_decode->detail->resource_limit  >=  ($result_decode->detail->resource_usage + $insert_qty)) {
                    return TRUE;
                }else{
                    return FALSE;
                }

             
            } else {
                // Handle error scenario
 
                throw new Exception($result_decode->message);
            }
        }
        return TRUE;  // return true when saas is not enabled
    }



    public function getStorageLimit($resource, $uploaded_size)
    {

        if ($this->sass_enabled) { // check is saas is enabled 
            $detail = $this->CI->resourcequota->getResourceLimit($resource);
            $result_decode = json_decode($detail);


            // Check if decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg());
            }

            // Check the status in the response
            if ($result_decode->status) {
                // Perform necessary actions if status is true

                if ($result_decode->detail->resource_limit  >=  ($result_decode->detail->resource_usage + $uploaded_size)) {
                    return TRUE;
                }else{
                    return FALSE;
                }

             
            } else {
                // Handle error scenario
 
                throw new Exception($result_decode->message);
            }
        }
        return TRUE;  // return true when saas is not enabled
    }



    public function updateResouceQuota($resource, $resource_usage)
    {

        if ($this->sass_enabled) { // check is saas is enabled 

            $result = $this->CI->resourcequota->updateResourceLimit($resource, $resource_usage, 'add');
            $result_decode = json_decode($result);

            // Check if decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg());
            }

            // Check the status in the response
            if ($result_decode->status) {
                // Perform necessary actions if status is true
                return true; // Assuming success implies returning true
            } else {
                // Handle error scenario
                throw new Exception($result_decode->message);
            }
        }
        return TRUE; // return true when saas is not enabled
    }

    public function deleteResouceQuota($resource, $resource_usage)
    {

        if ($this->sass_enabled) { // check is saas is enabled 

            $result = $this->CI->resourcequota->updateResourceLimit($resource, $resource_usage, 'delete');
            $result_decode = json_decode($result);

            // Check if decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg());
            }

            // Check the status in the response
            if ($result_decode->status) {
                // Perform necessary actions if status is true
                return true; // Assuming success implies returning true
            } else {
                // Handle error scenario
                throw new Exception($result_decode->message);
            }
        }
        return TRUE; // return true when saas is not enabled
    }

    public function calculateUploadFileSize($input_array,$path=NULL)
    {
        $uploaded_size = 0;
          
        foreach ($input_array as $input_key => $input_value) {    
			 
			$filename = $this->CI->customlib->getFolderPath() . $path . "/" . $input_value;	
			$file_size = filesize($filename);		
			$file_size_kb = $file_size / 1024; // Size in KB
			$file_size_mb = $file_size_kb / 1024; // Size in MB
			$uploaded_size += $file_size_mb;
        }			 

		return $uploaded_size;
    }

    // public function calculateUploadFileSizeInMb($storage_params_json)
    // {
        // $storage_params = json_decode($storage_params_json);

        // $fields   = explode(',', ($storage_params->fields));  // e.g., ['file', 'father_pic', ...]

        // foreach ($fields as $field_key => $field_value) {
            // if (isset($_FILES[$field_value]) && !empty($_FILES[$field_value]['name']) && $_FILES[$field_value]['size'] > 0) {

                // $file_size = $_FILES[$field_value]['size'];
                // if ($file_size > 0) {
                    
                    // $file_size_kb = $file_size / 1024; // Size in KB
                    // $file_size_mb = $file_size_kb / 1024; // Size in MB
                    // $uploaded_size += $file_size_mb;
                // }
            // }
        // } 			 

		// return $uploaded_size;
    // }
    
}
