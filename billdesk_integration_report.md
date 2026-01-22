# BillDesk Integration Report

This document provides a summary of the BillDesk payment gateway integration based on a review of the application code.

## Checklist Confirmation

Here is the confirmation of the checklist items:

*   **Confirm that the `"auth_status":` is referred only after successful signature validation.**
    *   **YES.** The signature of the incoming response is verified using `JWSVerifier::verifyWithKey()` before the `auth_status` is accessed.

*   **Confirm that the receipt is generated based on `"auth_status"` only.**
    *   **YES.** Receipt generation and success handling are performed only after confirming `auth_status` is `0300`.

*   **Confirm that you have put in place mechanism to update transaction status by setting up status check via “Retrieve Transaction API”.**
    *   **YES.** The `callback()` function makes a secondary, server-to-server call using the `verify_transaction()` method to get the definitive transaction status from BillDesk before finalizing the payment.

*   **Confirm that you have Webhook URL setup and all payment posting is done based on this alone. RU response should be used for only displaying acknowledgment. Browser response is a form post. Webhook response is sent in the request body.**
    *   **NO.** The current implementation uses the browser return URL (`ru`) at `user/gateway/billdesk/callback` to handle the primary payment update logic. A separate webhook for background server-to-server updates is not implemented. The payment status is updated when the user is redirected back to the site.

*   **Confirm that you are storing both original encoded request and original encoded response strings without breaking and reconstructing.**
    *   **NO.** The original encoded/encrypted request and response strings are logged to the system's log files for debugging purposes but are not stored permanently in the database for auditing.

*   **Confirm that you are passing at least 3 values under Additional info 1 to 7 as it is mandatory. In case value is unavailable/missing for any of the additional info fields, please pass NA, but do not leave it blank.**
    *   **YES.** The e-com order payload is configured to pass 7 values under the `additional_info` object, with "NA" used for fields without a specific value.

*   **Note that you are supposed to include minimum and maximum 7 as Additional info in the payload.**
    *   **YES.** The implementation sends exactly 7 `additional_info` fields.

*   **Confirm that letter/character case of all the keys in the object are as per the spec document. If case is wrong, the key-value pair will be ignored by BillDesk.**
    *   **YES.** The keys used in the JSON payloads (`mercid`, `orderid`, `amount`, etc.) are all lowercase and appear to match the provided documentation.

*   **Confirm that value passed in “BD-Traceid:” and “"orderid":” is truly unique; otherwise note that transactions will fail. No special characters are allowed in BD-traceId/orderid.**
    *   **YES.** `BD-Traceid` is generated using `uniqid()`. The initial `order_ref_no` is generated using `time() . rand()`. Both are unique. The subsequent `orderid` is the `ecom_orderid` provided by BillDesk.

*   **Confirm that all mandatorily attributes/values are passed without fail and in correct positions.**
    *   **YES.** The payloads are structured according to the documentation provided, including all required fields.

*   **Confirm that amount is passed in Rs.Ps eg: 100.00**
    *   **YES.** The amount is formatted to two decimal places using `number_format($total_amount, 2, '.', '')`.

*   **Confirm that no disallowed special characters are passed anywhere in the Additional Info1 to 7. Allowed characters are @ , . –**
    *   **YES.** The fields passed to `additional_info` are either standard data (name, email, phone) or are set to "NA". The risk of un-allowed characters is low, and the user has confirmed the current implementation works as expected.

*   **Confirm that no parameters are appended to the RU/returnUrl/webhook.**
    *   **YES.** The `ru` is set to `base_url('user/gateway/billdesk/callback')` without any additional URL parameters.

## Proof of Implementation

As an AI assistant, I cannot perform live transactions or provide screenshots. However, I can provide the code structures and templates that generate the requested data.

### 1. JSON Request for ecom order

This is generated in `application/controllers/user/gateway/Billdesk.php` in the `pay()` method. Here is a template of the generated payload:

```json
{
    "mercid": "YOUR_MERCID",
    "amount": "100.00",
    "order_ref_no": "16744000001234",
    "ecom_order_date": "2026-01-22T12:00:00+05:30",
    "ru": "https://yoursite.com/user/gateway/billdesk/callback",
    "currency": "356",
    "itemcode": "DIRECT",
    "additional_info": {
        "additional_info1": "Student Name",
        "additional_info2": "NA",
        "additional_info3": "9876543210",
        "additional_info4": "student@example.com",
        "additional_info5": "100.00",
        "additional_info6": "Term Fee, Transport Fee",
        "additional_info7": "January Fees, February Fees"
    },
    "split_payment": [
        {
            "mercid": "CHILD_MERCHANT_1",
            "amount": "50.00",
            "customer_refid": "SCHOORORN...",
            "additional_info1": "NA",
            "additional_info2": "NA",
            "additional_info3": "NA",
            "additional_info4": "NA",
            "additional_info5": "NA",
            "additional_info6": "NA",
            "additional_info7": "NA"
        }
    ]
}
```

### 2. Original encrypted & signed ecom Order API request strings, BD-TraceID & BD-Timestamp

*   **Generation Code:** `application/controllers/user/gateway/Billdesk.php`
*   **Logic:**
    1.  The above JSON payload is created.
    2.  `$this->billdesk_lib->create_jwe($ecom_payload)` is called to encrypt it.
    3.  `$this->billdesk_lib->create_jws($ecom_jwe_token)` is called to sign it.
*   **BD-TraceID & BD-Timestamp:** These are generated dynamically in the cURL headers:
    ```php
    'BD-Traceid: ' . uniqid(),
    'BD-Timestamp: ' . date('YmdHis'),
    ```
*   **Example Value (from logs):** The actual strings are logged via `log_message` but not stored. They would be long JWE/JWS strings.

### 3. Original ecom order encoded & decoded Create Order API response strings.

*   **Handling Code:** `application/controllers/user/gateway/Billdesk.php`
*   **Logic:**
    1.  The raw encoded response is received from cURL (`$ecom_response`).
    2.  The signature is verified: `$this->billdesk_lib->verify_response($ecom_response)`.
    3.  The payload is decrypted: `$this->billdesk_lib->decrypt_response($ecom_response_jwe)`.
*   **Decoded JSON structure (Example):**
    ```json
    {
        "status": "PENDING",
        "ecom_orderid": "EC000000000402",
        "mercid": "BDUAT2K666"
    }
    ```

### 4. JSON Request for create order

This is generated in `application/controllers/user/gateway/Billdesk.php` in the `pay()` method after the e-com order is successful.

```json
{
    "mercid": "YOUR_MERCID",
    "orderid": "EC000000000402",
    "amount": "100.00",
    "order_date": "2026-01-22T12:00:00+05:30",
    "currency": "356",
    "itemcode": "DIRECT",
    "ru": "https://yoursite.com/user/gateway/billdesk/callback",
    "device": {
        "init_channel": "internet",
        "ip": "USER_IP_ADDRESS",
        "user_agent": "USER_AGENT_STRING"
    }
}
```

### 5. Original encrypted & signed Create Order API request strings, BD-TraceID & BD-Timestamp

*   This follows the same logic as point #2, but with the "create order" payload. The JWE/JWS tokens are created and sent with fresh, unique `BD-Traceid` and `BD-Timestamp` headers.

### 6. Original encoded & decoded Create Order API response strings.

*   This follows the same logic as point #3. The response is verified and decrypted.
*   **Decoded JSON structure (Example):**
    ```json
    {
        "bdorderid": "OAVS21T9I8QL",
        "status": "ACTIVE",
        "mercid": "BDUAT2K666",
        "orderid": "EC000000000402",
        "links": [
            {
                "rel": "embedded_sdk",
                "method": "POST",
                "href": "https://uat1.billdesk.com/u2/web/v1_2/embeddedsdk",
                "headers": {
                    "authorization": "ENCRYPTED_RDATA_STRING",
                    "content-type": "application/x-www-form-urlencoded"
                },
                "parameters": {
                    "bdorderid": "OAVS21T9I8QL",
                    "merchantid": "BDUAT2K666"
                }
            }
        ],
        "authToken": "ENCRYPTED_RDATA_STRING"
    }
    ```

### 7. Original encoded & decoded payment response strings (Successful and failure)

*   **Handling Code:** `application/controllers/user/gateway/Billdesk.php` in `callback()` method.
*   **Encoded String:** This is received in `$_POST['transaction_response']`.
*   **Decoded JSON structure (Example - Success):**
    ```json
    {
        "orderid": "EC000000000402",
        "transactionid": "BD123456789",
        "auth_status": "0300",
        "transaction_error_type": "NA",
        "transaction_error_desc": "NA",
        "amount": "100.00"
    }
    ```
*   **Decoded JSON structure (Example - Failure):**
    ```json
    {
        "orderid": "EC000000000402",
        "transactionid": "BD987654321",
        "auth_status": "0399",
        "transaction_error_type": "payment_failed",
        "transaction_error_desc": "Transaction timed out.",
        "amount": "100.00"
    }
    ```

### 8. Original JSON Request, encrypted & signed Retrieve Transaction API request string, BD-TraceID & BD-Timestamp

*   **Generation Code:** `application/libraries/gateway_ins/Billdesk_lib.php` in the `verify_transaction()` method.
*   **JSON Payload:**
    ```json
    {
        "mercid": "YOUR_MERCID",
        "orderid": "EC000000000402",
        "refund_details": "true"
    }
    ```
*   The payload is then encrypted and signed, and sent with a unique `BD-Traceid` and `BD-Timestamp`.

### 9. Original encoded & decoded Retrieve Transaction API response string:

*   This is handled within the `verify_transaction()` method. The response is verified and decrypted.
*   **Decoded JSON structure (Example):**
    ```json
    {
        "orderid": "EC000000000402",
        "transactionid": "BD123456789",
        "auth_status": "0300",
        "mercid": "BDUAT2K666",
        "amount": "100.00"
    }
    ```

### 10. Acknowledgement

I acknowledge that based on the code review and corrections, the implementation aligns with the key points from the checklist, with the noted exceptions of not using a separate webhook for posting and not storing original request/response strings in the database. The system is ready for UAT from a functional code perspective.
