<?php
/**
 * @version        $Id: query.php 14401 2010-01-26 14:10:00Z guillossou $
 * @package        Joomla
 * @subpackage     Emundus
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

use Joomla\CMS\Factory;

/**
 * Content Component Query Helper
 *
 * @static
 * @package        Joomla
 * @subpackage     Content
 * @since          1.5
 */
class EmundusHelperUsers
{
	/**
	 * @param $length
	 * @param $add_dashes
	 * @param $available_sets
	 * Available sets : l = lowercase, u = uppercase, d = digits, s = symbols
	 *
	 * @return string
	 *
	 * @since version
	 */
	static function generateStrongPassword($length = 8, $add_dashes = false, $available_sets = 'luds')
	{
		$sets = array();
		if (strpos($available_sets, 'l') !== false)
			$sets[] = 'abcdefghjkmnpqrstuvwxyz';
		if (strpos($available_sets, 'u') !== false)
			$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		if (strpos($available_sets, 'd') !== false)
			$sets[] = '123456789';
		if (strpos($available_sets, 's') !== false)
			$sets[] = '!@#%&*?';

		$all      = '';
		$password = '';
		foreach ($sets as $set) {
			$password .= $set[array_rand(str_split($set))];
			$all      .= $set;
		}

		$all = str_split($all);
		for ($i = 0; $i < $length - count($sets); $i++)
			$password .= $all[array_rand($all)];

		$password = str_shuffle($password);

		if (!$add_dashes)
			return $password;

		$dash_len = floor(sqrt($length));
		$dash_str = '';
		while (strlen($password) > $dash_len) {
			$dash_str .= substr($password, 0, $dash_len) . '-';
			$password = substr($password, $dash_len);
		}
		$dash_str .= $password;

		return $dash_str;
	}

	static function generateToken($length = 16)
	{
		$rand_token = openssl_random_pseudo_bytes($length);

		return bin2hex($rand_token);
	}

	static function getEmundusUser()
	{
		$app = Factory::getApplication();
		if (version_compare(JVERSION, '4.0', '>')) {
			$session = $app->getSession();
		}
		else {
			$session = Factory::getSession();
		}

		return $session->get('emundusUser', null);
	}
}
