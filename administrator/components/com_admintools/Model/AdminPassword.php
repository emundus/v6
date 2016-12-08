<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use FOF30\Model\Model;
use JFile;
use JUserHelper;

class AdminPassword extends Model
{
	/**
	 * The username for the administrator password protection
	 *
	 * @var  string
	 */
	public $username = '';

	/**
	 * The password for the administrator password protection
	 *
	 * @var  string
	 */
	public $password = '';

	/**
	 * Applies the back-end protection, creating an appropriate .htaccess and
	 * .htpasswd file in the administrator directory.
	 *
	 * @return  bool
	 */
	public function protect()
	{
		\JLoader::import('joomla.filesystem.file');

		$cryptpw      = $this->apacheEncryptPassword();
		$htpasswd     = $this->username . ':' . $cryptpw . "\n";
		$htpasswdPath = JPATH_ADMINISTRATOR . '/.htpasswd';
		$htaccessPath = JPATH_ADMINISTRATOR . '/.htaccess';

		if (!@file_put_contents($htpasswdPath, $htpasswd))
		{
			if (!JFile::write($htpasswdPath, $htpasswd))
			{
				return false;
			}
		}

		$path     = rtrim(JPATH_ADMINISTRATOR, '/\\') . '/';
		$htaccess = <<<ENDHTACCESS
AuthUserFile "$path.htpasswd"
AuthName "Restricted Area"
AuthType Basic
require valid-user

RewriteEngine On
RewriteRule \.htpasswd$ - [F,L]
ENDHTACCESS;

		$status = @file_put_contents($htaccessPath, $htaccess);

		if (!$status)
		{
			$status = JFile::write($htaccessPath, $htaccess);
		}

		if (!$status || !is_file($path . '/.htpasswd'))
		{
			if (!@unlink($htpasswdPath))
			{
				JFile::delete($htpasswdPath);
			}

			return false;
		}

		return true;
	}

	/**
	 * Removes the administrator protection by removing both the .htaccess and
	 * .htpasswd files from the administrator directory
	 *
	 * @return bool
	 */
	public function unprotect()
	{
		$htaccessPath = JPATH_ADMINISTRATOR . '/.htaccess';
		$htpasswdPath = JPATH_ADMINISTRATOR . '/.htpasswd';

		if (!@unlink($htaccessPath))
		{
			if (!JFile::delete($htaccessPath))
			{
				return false;
			}
		}

		if (!@unlink($htpasswdPath))
		{
			if (!JFile::delete($htpasswdPath))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true if both a .htpasswd and .htaccess file exist in the back-end
	 *
	 * @return bool
	 */
	public function isLocked()
	{
		$htaccessPath = JPATH_ADMINISTRATOR . '/.htaccess';
		$htpasswdPath = JPATH_ADMINISTRATOR . '/.htpasswd';

		return @file_exists($htpasswdPath) && @file_exists($htaccessPath);
	}

	protected function apacheEncryptPassword()
	{
		$os = strtoupper(PHP_OS);
		$isWindows = substr($os, 0, 3) == 'WIN';

		$encryptedPassword = null;

		// First try to use bCrypt on Apache 2.4 TODO Reliably detect Apache 2.4
		/*
			if (defined('PASSWORD_BCRYPT') && version_compare(PHP_VERSION, '5.3.10', 'ge'))
			{
				$encryptedPassword = password_hash($password, PASSWORD_BCRYPT);
			}
		*/

		// Iterated and salted MD5 (APR1)
		$salt = JUserHelper::genRandomPassword(4);
		$encryptedPassword = $this->apr1_hash($this->password, $salt, 1000);

		// SHA-1 encrypted – should never run
		if (empty($encryptedPassword) && function_exists('base64_encode') && function_exists('sha1'))
		{
			$encryptedPassword = '{SHA}' . base64_encode(sha1($this->password, true));
		}

		// Traditional crypt(3) – should never run
		if (empty($encryptedPassword) && function_exists('crypt') && !$isWindows)
		{
			$salt              = JUserHelper::genRandomPassword(2);
			$encryptedPassword = crypt($this->password, $salt);
		}

		// If all else fails use plain text passwords (only happens on Windows)
		if (empty($encryptedPassword))
		{
			$encryptedPassword = $this->password;
		}

		return $encryptedPassword;
	}

	/**
	 * Perform the hashing of the password
	 *
	 * @param   string  $password    The plain text password to hash
	 * @param   string  $salt        The 8 byte salt to use
	 * @param   int     $iterations  The number of iterations to use
	 *
	 * @return  string  The hashed password
	 */
	protected function apr1_hash($password, $salt, $iterations)
	{
		$len  = strlen($password);
		$text = $password . '$apr1$' . $salt;
		$bin  = md5($password . $salt . $password, true);

		for ($i = $len; $i > 0; $i -= 16)
		{
			$text .= substr($bin, 0, min(16, $i));
		}

		for ($i = $len; $i > 0; $i >>= 1)
		{
			$text .= ($i & 1) ? chr(0) : $password[0];
		}

		$bin = $this->apr1_iterate($text, $iterations, $salt, $password);

		return $this->apr1_convertToHash($bin, $salt);
	}

	protected function apr1_iterate($text, $iterations, $salt, $password)
	{
		$bin = md5($text, true);

		for ($i = 0; $i < $iterations; $i++)
		{
			$new = ($i & 1) ? $password : $bin;

			if ($i % 3)
			{
				$new .= $salt;
			}

			if ($i % 7)
			{
				$new .= $password;
			}

			$new .= ($i & 1) ? $bin : $password;
			$bin = md5($new, true);
		}

		return $bin;
	}

	protected function apr1_convertToHash($bin, $salt)
	{
		$tmp = '$apr1$' . $salt . '$';

		$tmp .= $this->apr1_to64(
			(ord($bin[0]) << 16) | (ord($bin[6]) << 8) | ord($bin[12]),
			4
		);

		$tmp .= $this->apr1_to64(
			(ord($bin[1]) << 16) | (ord($bin[7]) << 8) | ord($bin[13]),
			4
		);

		$tmp .= $this->apr1_to64(
			(ord($bin[2]) << 16) | (ord($bin[8]) << 8) | ord($bin[14]),
			4
		);

		$tmp .= $this->apr1_to64(
			(ord($bin[3]) << 16) | (ord($bin[9]) << 8) | ord($bin[15]),
			4
		);

		$tmp .= $this->apr1_to64(
			(ord($bin[4]) << 16) | (ord($bin[10]) << 8) | ord($bin[5]),
			4
		);

		$tmp .= $this->apr1_to64(
			ord($bin[11]),
			2
		);

		return $tmp;
	}

	/**
	 * Convert the input number to a base64 number of the specified size
	 *
	 * @param   int  $num   The number to convert
	 * @param   int  $size  The size of the result string
	 *
	 * @return  string  The converted representation
	 */
	protected function apr1_to64($num, $size)
	{
		static $seed = '';

		if (empty($seed))
		{
			$seed = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
				'abcdefghijklmnopqrstuvwxyz';
		}

		$result = '';

		while (--$size >= 0)
		{
			$result .= $seed[$num & 0x3f];
			$num >>= 6;
		}

		return $result;
	}
}