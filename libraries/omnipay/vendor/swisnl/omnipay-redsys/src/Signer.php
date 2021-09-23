<?php

namespace Omnipay\RedSys;

use Sarciszewski\PHPFuture\Security;

final class Signer
{
    private $encryptor;

    public function __construct($secretKey)
    {
        $this->encryptor = new Encryptor($secretKey);
    }

    public function generateSignature($data, $keyData)
    {
        $key = $this->encryptor->encrypt($keyData);

        $res = hash_hmac('sha256', $data, $key, true);

        return base64_encode($res);
    }

    public function validateSignature($signature, $data, $keyData)
    {
        $expectedSignature = $this->generateSignature($data, $keyData);

        // Constant Time String Comparison @see http://php.net/hash_equals
        if (function_exists('hash_equals')) {
            return hash_equals($signature, $expectedSignature); // (PHP 5 >= 5.6.0, PHP 7)
        }
        else {
            // Polyfill for PHP < 5.6.
            return Security::hashEquals($signature, $expectedSignature);
        }
    }
}
