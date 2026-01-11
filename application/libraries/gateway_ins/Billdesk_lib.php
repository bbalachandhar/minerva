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

        $jwe = $jweBuilder
            ->create()
            ->withPayload(json_encode($payload))
            ->withSharedProtectedHeader([
                'alg' => 'dir', // Direct Key Agreement
                'enc' => 'A256GCM',
                'clientid' => $this->clientid,
                'kid' => $this->key_id
            ])
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

        $jws = $jwsBuilder
            ->create()
            ->withPayload($jwe_token)
            ->addSignature($jwk, ['alg' => 'HS256', 'kid' => $this->key_id, 'clientid' => $this->clientid])
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
}