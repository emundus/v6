<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/* @DEPRECATED */

defined('_JEXEC') or die;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

require_once dirname(__FILE__, 2) . '/assignment.php';

class RLAssignmentsCookieConfirm extends RLAssignment
{
	public function passCookieConfirm()
	{
		require_once JPATH_PLUGINS . '/system/cookieconfirm/core.php';
		$pass = PlgSystemCookieconfirmCore::getInstance()->isCookiesAllowed();

		return $this->pass($pass);
	}
}
