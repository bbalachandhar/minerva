<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Biometric_api_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Fetches transaction logs from the biometric device API.
     *
     * @param array $device_details Contains api_endpoint, serial_number, username, password
     * @param string $from_datetime Start date and time for logs (YYYY-MM-DD HH:MM:SS)
     * @param string $to_datetime End date and time for logs (YYYY-MM-DD HH:MM:SS)
     * @return array An array of parsed punch data (staff_id, punch_time) or empty array on failure.
     */
    public function get_punches_from_api($device_details, $from_datetime, $to_datetime) {
        $punches = [];

        try {
            $this->load->helper('custom');

            $formatted_from_datetime = date('Y-m-d H:i:s', strtotime($from_datetime));
            $formatted_to_datetime = date('Y-m-d H:i:s', strtotime($to_datetime));
            $xml_body = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><GetTransactionsLog xmlns="http://tempuri.org/"><FromDateTime>' . $formatted_from_datetime . '</FromDateTime><ToDateTime>' . $formatted_to_datetime . '</ToDateTime><SerialNumber>' . $device_details['serial_number'] . '</SerialNumber><UserName>' . $device_details['username'] . '</UserName><UserPassword>' . $device_details['password'] . '</UserPassword></GetTransactionsLog></soap:Body></soap:Envelope>';

            log_message('debug', 'Raw SOAP Request: ' . $xml_body);
            $soap_action = 'http://tempuri.org/GetTransactionsLog';
            $response = send_soap_request_curl($device_details['api_endpoint'], $soap_action, $xml_body);

            if ($response === false) {
                throw new Exception('cURL Error: Unable to send SOAP request.');
            }

            log_message('debug', 'Raw SOAP Response: ' . $response);

            // Parse the XML response
                        $xml_response = simplexml_load_string($response);
                        if ($xml_response === false) {
                            throw new Exception('Failed to parse XML response.');
                        }
            
                        $xml_response->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
                        $xml_response->registerXPathNamespace('ns1', 'http://tempuri.org/');
                        $str_data_list_elements = $xml_response->xpath('//ns1:strDataList');
            
                        if (empty($str_data_list_elements)) {
                            throw new Exception('SOAP Fault: strDataList not found in response or empty.');
                        }
            
                        $str_data_list = (string)$str_data_list_elements[0];
                        $lines = explode("\n", trim($str_data_list));

                foreach ($lines as $line) {
                    $line = trim($line);
                    log_message('debug', 'Biometric API: Processing line: ' . $line);

                    // Regex to capture staff ID and punch time (YYYY-MM-DD HH:MM:SS)
                    if (preg_match('/^(\S+)\t(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\s*$/', $line, $matches)) {
                        $staff_id_biometric = $matches[1];
                        $punch_time_str = $matches[2];

                        log_message('debug', 'Biometric API: Extracted Staff ID: ' . $staff_id_biometric . ', Punch Time: ' . $punch_time_str);

                        // The regex already validates the datetime format, so we can be more confident
                        $punches[] = [
                            'staff_id' => $staff_id_biometric,
                            'punch_time' => $punch_time_str,
                        ];
                    } else if (!empty($line)) { // Log only if line is not empty
                        log_message('error', 'Biometric API: Malformed data line (regex failed): ' . $line);
                    }
                }

        } catch (SoapFault $e) {
            log_message('error', 'Biometric API SOAP Fault: ' . $e->getMessage() . ' Request: ' . $soap_client->__getLastRequest());
            return false;
        } catch (Exception $e) {
            log_message('error', 'Biometric API Error: ' . $e->getMessage());
            return false;
        }

        return $punches;
    }
}
