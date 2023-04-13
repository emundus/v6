<?php


class AESEncryption
{
    public static function encrypt_data($ni, $BI)
    {
        return base64_encode(openssl_encrypt($ni, "\141\145\x73\55\x31\x32\x38\x2d\145\x63\x62", $BI, OPENSSL_RAW_DATA));
    }
    public static function decrypt_data($ni, $BI)
    {
        return openssl_decrypt(base64_decode($ni), "\x61\x65\163\x2d\61\x32\x38\x2d\145\x63\x62", $BI, OPENSSL_RAW_DATA);
    }
}
