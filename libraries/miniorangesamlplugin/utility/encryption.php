<?php


class AESEncryption
{
    public static function encrypt_data($lp, $uf)
    {
        return base64_encode(openssl_encrypt($lp, "\141\145\x73\55\61\62\x38\x2d\145\x63\142", $uf, OPENSSL_RAW_DATA));
    }
    public static function decrypt_data($lp, $uf)
    {
        return openssl_decrypt(base64_decode($lp), "\141\x65\x73\55\x31\x32\x38\x2d\145\x63\142", $uf, OPENSSL_RAW_DATA);
    }
}
