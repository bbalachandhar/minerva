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
            $soap_client = new SoapClient(null, array(
                'location'   => $device_details['api_endpoint'],
                'uri'        => 'http://tempuri.org/', // Namespace from the sample request
                'trace'      => 1,
                'exceptions' => true,
                'soap_version' => SOAP_1_1, // Assuming SOAP 1.1 based on sample
            ));

            $request_params = array(
                'FromDateTime' => $from_datetime,
                'ToDateTime'   => $to_datetime,
                'SerialNumber' => $device_details['serial_number'],
                'UserName'     => $device_details['username'],
                'UserPassword' => $device_details['password'],
            );

            // Call the GetTransactionsLog method
            $response = $soap_client->__soapCall('GetTransactionsLog', array('parameters' => $request_params));

            // Check if strDataList exists in the response
            // The structure is $response->GetTransactionsLogResult->strDataList based on the sample XML
            if (isset($response->GetTransactionsLogResult->strDataList)) {
                $str_data_list = $response->GetTransactionsLogResult->strDataList;
                $lines = explode("
", trim($str_data_list));

                foreach ($lines as $line) {
                    // Example line: MCE2001LIB0012025-10-22 09:22:59
                    // Staff ID is variable length, followed by fixed length datetime
                    // Datetime format: YYYY-MM-DD HH:MM:SS (19 characters)
                    if (strlen($line) > 19) {
                        $punch_time_str = substr($line, -19);
                        $staff_id_biometric = substr($line, 0, -19);

                        // Validate datetime format
                        $d = DateTime::createFromFormat('Y-m-d H:i:s', $punch_time_str);
                        if ($d && $d->format('Y-m-d H:i:s') === $punch_time_str) {
                            $punches[] = [
                                'staff_id' => $staff_id_biometric, // Use 'staff_id' as it maps directly
                                'punch_time' => $punch_time_str,
                            ];
                        } else {
                            log_message('error', 'Biometric API: Invalid datetime format in line: ' . $line);
                        }
                    } else if (!empty($line)) { // Log only if line is not empty
                        log_message('error', 'Biometric API: Malformed data line: ' . $line);
                    }
                }
            } else {
                log_message('error', 'Biometric API: strDataList not found in response or empty.');
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
