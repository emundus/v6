<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Cipher;

defined('JPATH_PLATFORM') or die;

/**
 * Crypt cipher for Blowfish encryption, decryption and key generation.
 *
 * @since       3.0.0
 * @deprecated  4.0   Without replacement use CryptoCipher
 */
class BlowfishCipher extends McryptCipher
{
	/**
	 * @var    integer  The mcrypt cipher constant.
	 * @link   https://www.php.net/manual/en/mcrypt.ciphers.php
	 * @since  3.0.0
	 */
	protected $type = MCRYPT_BLOWFISH;

	/**
	 * @var    integer  The mcrypt block cipher mode.
	 * @link   https://www.php.net/manual/en/mcrypt.constants.php
	 * @since  3.0.0
	 */
	protected $mode = MCRYPT_MODE_CBC;

	/**
	 * @var    string  The JCrypt key type for validation.
	 * @since  3.0.0
	 */
	protected $keyType = 'blowfish';
}
