<?php
namespace Omnipay\RedSys;

final class Encryptor
{
	private $secretKey;

	public function __construct($secretKey)
	{
		$this->secretKey = base64_decode($secretKey);
	}

	public function encrypt($message)
	{
		if (function_exists('openssl_encrypt'))
		{
			return self::encrypt3DESOpenSSL($message, $this->secretKey);
		}

		return self::encrypt3DESMcrypt($message, $this->secretKey);
	}

	protected static function encrypt3DESOpenSSL($message, $key)
	{
		$l       = ceil(strlen($message) / 8) * 8;
		$message = $message . str_repeat("\0", $l - strlen($message));

		return substr(openssl_encrypt($message, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, "\0\0\0\0\0\0\0\0"), 0, $l);
	}

	protected static function encrypt3DESMcrypt($message, $key)
	{
		$iv = implode(array_map('chr', array(0, 0, 0, 0, 0, 0, 0, 0)));

		return mcrypt_encrypt(MCRYPT_3DES, $key, $message, MCRYPT_MODE_CBC, $iv);
	}
}