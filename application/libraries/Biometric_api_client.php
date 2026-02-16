<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Biometric_api_client {

    private $CI;
    private $api_endpoint;
    private $serial_number;
    private $username;
    private $password;

    public function __construct() {
        $this->CI = & get_instance();
    }

    public function initialize($config) {
        $this->api_endpoint = $config['api_endpoint'];
        $this->serial_number = $config['serial_number'];
        $this->username = $config['username'];
        $this->password = $config['password'];
    }

    public function getAttendanceLogs($fromDateTime, $toDateTime) {
        log_message('debug', "Biometric API: Attempting to fetch logs - Endpoint: {$this->api_endpoint}, From: {$fromDateTime}, To: {$toDateTime}, Serial: {$this->serial_number}, Username: {$this->username}");
        
        $soap_request = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                          <soap:Body>
                            <GetTransactionsLog xmlns="http://tempuri.org/">
                              <FromDateTime>' . $fromDateTime . '</FromDateTime>
                              <ToDateTime>' . $toDateTime . '</ToDateTime>
                              <SerialNumber>' . $this->serial_number . '</SerialNumber>
                              <UserName>' . $this->username . '</UserName>
                              <UserPassword>' . $this->password . '</UserPassword>
                            </GetTransactionsLog>
                          </soap:Body>
                        </soap:Envelope>';

        log_message('debug', "Biometric API: SOAP Request prepared, length: " . strlen($soap_request) . " bytes");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: ' . strlen($soap_request)
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minute timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 30 second connection timeout

        log_message('debug', "Biometric API: Initiating cURL request to {$this->api_endpoint}");
        $start_time = microtime(true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_errno = curl_errno($ch);
        $err = curl_error($ch);
        
        $elapsed_time = round(microtime(true) - $start_time, 2);
        curl_close($ch);

        log_message('debug', "Biometric API: cURL completed in {$elapsed_time}s - HTTP Status: {$http_code}, cURL Error Code: {$curl_errno}");

        if ($err) {
            log_message('error', "Biometric API: cURL Error #{$curl_errno}: {$err}");
            return false;
        }
        
        if ($http_code !== 200) {
            log_message('error', "Biometric API: HTTP Error - Status Code: {$http_code}");
            log_message('debug', "Biometric API: Response (first 500 chars): " . substr($response, 0, 500));
            return false;
        }

        log_message('debug', "Biometric API: Received response, length: " . strlen($response) . " bytes");
        log_message('debug', "Biometric API: Response preview (first 500 chars): " . substr($response, 0, 500));
        
        return $this->parseSoapResponse($response);
    }

    private function parseSoapResponse($response) {
        log_message('debug', "=== BIOMETRIC API PARSING v2.0 - NEW CODE ACTIVE ===");
        log_message('debug', "Biometric API: Starting SOAP response parsing");
        
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            log_message('error', "Biometric API: Failed to parse SOAP response XML");
            log_message('debug', "Biometric API: Invalid XML response (first 1000 chars): " . substr($response, 0, 1000));
            return false;
        }

        log_message('debug', "Biometric API: XML parsed successfully");
        
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('tempuri', 'http://tempuri.org/');

        $strDataList_nodes = $xml->xpath('//tempuri:strDataList');
        log_message('debug', "Biometric API: Found " . count($strDataList_nodes) . " strDataList node(s)");

        if (empty($strDataList_nodes)) {
            log_message('error', "Biometric API: strDataList not found in SOAP response");
            log_message('debug', "Biometric API: Full response for debugging: " . substr($response, 0, 2000));
            return [];
        }

        $strDataList = (string) $strDataList_nodes[0];
        log_message('debug', "Biometric API: Raw strDataList content (first 500 chars): " . substr($strDataList, 0, 500));
        
        // Handle different line endings: \r\n (Windows), \n (Unix), \r (Mac)
        $strDataList = str_replace("\r\n", "\n", $strDataList);
        $strDataList = str_replace("\r", "\n", $strDataList);
        $lines = explode("\n", trim($strDataList));
        log_message('debug', "Biometric API: Found " . count($lines) . " line(s) in strDataList");

        $punches = [];
        $line_num = 0;
        $empty_count = 0;
        $too_short_count = 0;
        $invalid_datetime_count = 0;
        
        foreach ($lines as $line) {
            $line_num++;
            $line = trim($line); // Trim each line to remove any hidden characters like \r or \n
            
            if (empty($line)) {
                $empty_count++;
                continue;
            }

            // Datetime format: YYYY-MM-DD HH:MM:SS (19 characters)
            // Extract last 19 chars as timestamp, everything before as staff_id
            if (strlen($line) >= 19) {
                $punch_time_str = substr($line, -19);
                $staff_id = trim(substr($line, 0, -19));

                if ($line_num <= 5 || $line_num % 500 == 0) {
                    log_message('debug', "Biometric API: Line {$line_num}: staff_id='{$staff_id}', time='{$punch_time_str}'");
                }

                // Validate datetime format
                $d = DateTime::createFromFormat('Y-m-d H:i:s', $punch_time_str);
                if ($d && $d->format('Y-m-d H:i:s') === $punch_time_str && !empty($staff_id)) {
                    $punches[] = [
                        'staff_id' => $staff_id,
                        'punch_time' => $punch_time_str,
                    ];
                } else {
                    $invalid_datetime_count++;
                    if ($invalid_datetime_count <= 3) {
                        log_message('error', "Biometric API: Invalid datetime format or empty staff_id in line {$line_num}: " . $line);
                    }
                }
            } else {
                $too_short_count++;
                if ($too_short_count <= 3) {
                    log_message('error', "Biometric API: Malformed data line {$line_num} (too short, < 19 chars): " . $line);
                }
            }
        }
        
        log_message('debug', "Biometric API: Parse summary - Total: {$line_num}, Empty: {$empty_count}, Too short: {$too_short_count}, Invalid datetime: {$invalid_datetime_count}, Success: " . count($punches));
        log_message('debug', "Biometric API: Successfully parsed " . count($punches) . " punch(es) from response");
        return $punches;
    }

}