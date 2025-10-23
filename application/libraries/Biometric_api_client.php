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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soap_request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: ' . strlen($soap_request)
        ));

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', "Biometric API cURL Error #:". $err);
            return false;
        } else {
            return $this->parseSoapResponse($response);
        }
    }

    private function parseSoapResponse($response) {
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            log_message('error', "Failed to parse SOAP response XML.");
            return false;
        }

        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('tempuri', 'http://tempuri.org/');

        $strDataList_nodes = $xml->xpath('//tempuri:strDataList');

        if (empty($strDataList_nodes)) {
            log_message('error', "strDataList not found in SOAP response.");
            return [];
        }

        $strDataList = (string) $strDataList_nodes[0];
        $lines = explode("\n", trim($strDataList));

        $punches = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Assuming format: STAFFIDYYYY-MM-DD HH:MM:SS
            // Extract staff_id and timestamp
            // This regex assumes staff_id is alphanumeric and ends before the date.
            // It captures everything before the first digit of the year as staff_id
            // and the rest as timestamp.
            if (preg_match('/^([A-Z0-9]+)(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})$/', $line, $matches)) {
                $staff_id = $matches[1];
                $timestamp = $matches[2];
                $punches[] = [
                    'staff_id' => $staff_id,
                    'punch_time' => $timestamp
                ];
            } else {
                log_message('warning', "Could not parse biometric log line: " . $line);
            }
        }
        return $punches;
    }

}