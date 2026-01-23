<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . 'third_party/vendor/autoload.php';

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256GCMKW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\Dir;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;

class Billdesk_lib
{
    private $mercid;
    private $clientid;
    private $key_id;
    private $encryption_password;
    private $signing_password;
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('paymentsetting_model');
        $payment_setting = $this->CI->paymentsetting_model->getActiveMethod();
        $this->mercid = $payment_setting->api_secret_key;
        $this->clientid = $payment_setting->api_publishable_key;
        $this->key_id = $payment_setting->api_password;
        $this->encryption_password = $payment_setting->salt;
        $this->signing_password = $payment_setting->api_signature;

    }

    public function getClientid()
    {
        return $this->clientid;
    }

    public function create_jwe($payload)
    {
        // The key encryption algorithm manager with the Dir algorithm.
        $keyEncryptionAlgorithmManager = new AlgorithmManager([
            new Dir(), // Key Management Algorithm
        ]);

        // The content encryption algorithm manager with the A256GCM algorithm.
        $contentEncryptionAlgorithmManager = new AlgorithmManager([
            new A256GCM(), // Content Encryption Algorithm
        ]);

        // The compression method manager with the DEF (Deflate) method.
        $compressionMethodManager = new CompressionMethodManager([
            new Deflate(),
        ]);

        // We instantiate our JWE Builder.
        $jweBuilder = new JWEBuilder(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
            $compressionMethodManager
        );

        // The shared secret for direct encryption is the encryption_password itself
        $shared_secret_jwk = JWKFactory::createFromSecret(
            $this->encryption_password,
            [
                'use' => 'enc',
            ]
        );

        $protected_header = [
            'alg' => 'dir', // Direct Key Agreement
            'enc' => 'A256GCM',
            'clientid' => $this->clientid,
            'kid' => $this->key_id
        ];

        log_message('error', 'JWE Protected Header being built: ' . json_encode($protected_header));

        $jwe = $jweBuilder
            ->create()
            ->withPayload(json_encode($payload))
            ->withSharedProtectedHeader($protected_header)
            ->addRecipient($shared_secret_jwk) // Add recipient with the shared secret JWK
            ->build();

        $serializer = new \Jose\Component\Encryption\Serializer\CompactSerializer();
        return $serializer->serialize($jwe, 0);
    }
    
    public function create_jws($jwe_token)
    {
        // The algorithm manager with the HS256 algorithm.
        $algorithmManager = new AlgorithmManager([
            new HS256(),
        ]);

        // We instantiate our JWS Builder.
        $jwsBuilder = new JWSBuilder(
            $algorithmManager
        );
        
        $jwk = JWKFactory::createFromSecret(
            $this->signing_password,
            [
                'alg' => 'HS256',
                'use' => 'sig',
            ]
        );

        $signature_header = ['alg' => 'HS256', 'kid' => $this->key_id, 'clientid' => $this->clientid];

        log_message('error', 'JWS Signature Header being built: ' . json_encode($signature_header));

        $jws = $jwsBuilder
            ->create()
            ->withPayload($jwe_token)
            ->addSignature($jwk, $signature_header)
            ->build();
        
        $serializer = new CompactSerializer();
        return $serializer->serialize($jws, 0);
    }

    public function verify_response($jws_token) {
        $jws_serializer = new \Jose\Component\Signature\Serializer\CompactSerializer();
        $jws = $jws_serializer->unserialize($jws_token);

        $jwk = JWKFactory::createFromSecret(
            $this->signing_password,
            [
                'alg' => 'HS256',
                'use' => 'sig',
            ]
        );
        
        $jws_verifier = new \Jose\Component\Signature\JWSVerifier(
            new AlgorithmManager([new HS256()])
        );

        if (!$jws_verifier->verifyWithKey($jws, $jwk, 0)) {
            throw new \Exception('Invalid JWS signature');
        }

        return $jws->getPayload();
    }

    public function decrypt_response($jwe_token) {
        $jwe_serializer = new \Jose\Component\Encryption\Serializer\CompactSerializer();
        $jwe = $jwe_serializer->unserialize($jwe_token);

        $jwk = JWKFactory::createFromSecret(
            $this->encryption_password,
            [
                'use' => 'enc',
            ]
        );

        $keyEncryptionAlgorithmManager = new AlgorithmManager([
            new Dir(), // Matching the encryption algorithm
        ]);

        $contentEncryptionAlgorithmManager = new AlgorithmManager([
            new A256GCM(),
        ]);

        $compressionMethodManager = new CompressionMethodManager([
            new Deflate(),
        ]);

        // Create JWEDecrypter first
        $jweDecrypter = new \Jose\Component\Encryption\JWEDecrypter(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
            $compressionMethodManager
        );

        $serializerManager = new \Jose\Component\Encryption\Serializer\JWESerializerManager([
            new \Jose\Component\Encryption\Serializer\CompactSerializer(),
        ]);

        $jweLoader = new \Jose\Component\Encryption\JWELoader(
            $serializerManager,
            $jweDecrypter,
            null // Argument #3: ?Jose\Component\Checker\HeaderCheckerManager - setting to null as it's optional
        );

        $recipient_index = 0;
        $decrypted_jwe = $jweLoader->loadAndDecryptWithKey($jwe_token, $jwk, $recipient_index);

        return json_decode($decrypted_jwe->getPayload(), true);
    }

    public function verify_transaction($orderid) {
        $verify_payload = [
            'mercid' => $this->mercid,
            'orderid' => $orderid,
            'refund_details' => 'true'
        ];

        log_message('error', '--- VERIFY PAYLOAD ---');
        log_message('error', json_encode($verify_payload));
        log_message('error', 'BILLDESK_UAT_DATA: 8. JSON Request for Retrieve Transaction API: ' . json_encode($verify_payload, JSON_PRETTY_PRINT));

        $verify_jwe_token = $this->create_jwe($verify_payload);
        $verify_jws_token = $this->create_jws($verify_jwe_token);

        $verify_headers = [
            'Content-Type: application/jose',
            'Accept: application/jose',
            'BD-Traceid: ' . uniqid(),
            'BD-Timestamp: ' . date('YmdHis'),
        ];
        log_message('error', 'BILLDESK_UAT_DATA: 8. Original encrypted & signed Retrieve Transaction API request string, BD-TraceID & BD-Timestamp: Request String=' . $verify_jws_token);
        log_message('error', 'BILLDESK_UAT_DATA: 8. Retrieve Transaction Request Headers: ' . json_encode($verify_headers));

        $ch_verify = curl_init();
        curl_setopt($ch_verify, CURLOPT_URL, "https://uat1.billdesk.com/u2/payments/ve1_2/transactions/get");
        curl_setopt($ch_verify, CURLOPT_POST, 1);
        curl_setopt($ch_verify, CURLOPT_POSTFIELDS, $verify_jws_token);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $verify_headers);

        $verify_response = curl_exec($ch_verify);
        $verify_response_headers_sent = curl_getinfo($ch_verify, CURLINFO_HEADER_OUT); // Headers sent
        $verify_response_http_code = curl_getinfo($ch_verify, CURLINFO_HTTP_CODE); // HTTP status code
        $verify_response_headers_recv = '';
        if (curl_getinfo($ch_verify, CURLINFO_HEADER_SIZE) > 0) { // Check if headers were included in response
            $verify_response_headers_recv = substr($verify_response, 0, curl_getinfo($ch_verify, CURLINFO_HEADER_SIZE)); // Received headers
        }
        log_message('error', 'BILLDESK_UAT_DATA: 9. Retrieve Transaction Request Headers Sent via cURL: ' . $verify_response_headers_sent);
        log_message('error', 'BILLDESK_UAT_DATA: 9. Retrieve Transaction HTTP Response Code: ' . $verify_response_http_code);
        log_message('error', 'BILLDESK_UAT_DATA: 9. Retrieve Transaction Response Headers Received via cURL: ' . $verify_response_headers_recv);
        $verify_err = curl_error($ch_verify);
        curl_close($ch_verify);

        if ($verify_err) {
            throw new Exception("cURL Error (Verify): " . $verify_err);
        }

        $verify_response_jwe = $this->verify_response($verify_response);
        $decrypted_verify_response = $this->decrypt_response($verify_response_jwe);

        log_message('error', '--- VERIFY RESPONSE (Base64 Encoded) ---');
        log_message('error', 'DECODE THIS STRING TO SEE THE FULL VERIFY RESPONSE: ' . base64_encode(json_encode($decrypted_verify_response)));
        log_message('error', 'BILLDESK_UAT_DATA: 9. Original encoded Retrieve Transaction API response string: ' . $verify_response);
        log_message('error', 'BILLDESK_UAT_DATA: 9. Original decoded Retrieve Transaction API response string: ' . json_encode($decrypted_verify_response, JSON_PRETTY_PRINT));

        return $decrypted_verify_response;
    }
}