<?php

/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2018 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      eMundus SAS - Jérémy LEGENDRE
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Joomla User plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  User.emundus
 * @since       1.35.0
 */
class plgAuthenticationEmundus extends JPlugin
{
    public function onUserAuthenticate(&$credentials, $options, &$response)
    {
		if (!empty($credentials['username']) && filter_var($credentials['username'], FILTER_VALIDATE_EMAIL)) {
			require_once JPATH_ROOT . '/components/com_emundus/models/user.php';
			$m_user = new EmundusModelUser();

			$username = $m_user->getUsernameByEmail($credentials['username']);
			if (!empty($username)) {
				$credentials['username'] = $username;
			}
		}
	}
}
