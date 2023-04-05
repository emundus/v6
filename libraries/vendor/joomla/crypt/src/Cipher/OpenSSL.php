<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt\Cipher;

use Joomla\Crypt\CipherInterface;
use Joomla\Crypt\Exception\DecryptionException;
use Joomla\Crypt\Exception\EncryptionException;
use Joomla\Crypt\Exception\InvalidKeyException;
use Joomla\Crypt\Exception\InvalidKeyTypeException;
use Joomla\Crypt\Key;

/**
 * Joomla cipher for encryption, decryption and key generation via the openssl extension.
 *
 * @since  2.0.0
 */
class OpenSSL implements CipherInterface
{
	/**
	 * Initialisation vector for key generator method.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	private $iv;

	/**
	 * Method to use for encryption.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	private $method;

	/**
	 * Instantiate the cipher.
	 *
	 * @param   string  $iv      The initialisation vector to use
	 * @param   string  $method  The encryption method to use
	 *
	 * @since   2.0.0
	 */
	public function __construct(string $iv, string $method)
	{
		$this->iv     = $iv;
		$this->method = $method;
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 * @param   Key     $key   The key object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   2.0.0
	 * @throws  DecryptionException if the data cannot be decrypted
	 * @throws  InvalidKeyTypeException if the key is not valid for the cipher
	 */
	public function decrypt($data, Key $key)
	{
		// Validate key.
		if ($key->getType() !== 'openssl')
		{
			throw new InvalidKeyTypeException('openssl', $key->getType());
		}

		$cleartext = openssl_decrypt($data, $this->method, $key->getPrivate(), true, $this->iv);

		if ($cleartext === false)
		{
			throw new DecryptionException('Failed to decrypt data');
		}

		return $cleartext;
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 * @param   Key     $key   The key object to use for encryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   2.0.0
	 * @throws  EncryptionException if the data cannot be encrypted
	 * @throws  InvalidKeyTypeException if the key is not valid for the cipher
	 */
	public function encrypt($data, Key $key)
	{
		// Validate key.
		if ($key->getType() !== 'openssl')
		{
			throw new InvalidKeyTypeException('openssl', $key->getType());
		}

		$encrypted = openssl_encrypt($data, $this->method, $key->getPrivate(), true, $this->iv);

		if ($encrypted === false)
		{
			throw new EncryptionException('Unable to encrypt data');
		}

		return $encrypted;
	}

	/**
	 * Method to generate a new encryption key object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  Key
	 *
	 * @since   2.0.0
	 * @throws  InvalidKeyException if the key cannot be generated
	 */
	public function generateKey(array $options = [])
	{
		$passphrase = $options['passphrase'] ?? false;

		if ($passphrase === false)
		{
			throw new InvalidKeyException('Missing passphrase file');
		}

		return new Key('openssl', $passphrase, 'unused');
	}

	/**
	 * Check if the cipher is supported in this environment.
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public static function isSupported(): bool
	{
		return \extension_loaded('openssl');
	}
}
