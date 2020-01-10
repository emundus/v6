<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$cookieLogin = $this->user->get('cookieLogin');
$app = JFactory::getApplication();
$jinput = JFactory::getApplication()->input;
$redirect = base64_decode($jinput->get->getBase64('redirect'));
if (!empty($cookieLogin) || $this->user->get('guest'))
{
    // Get campaign ID and course from url
    $this->campaign = $jinput->get->get('cid');
    $this->course   = $jinput->get->get('course');

	// The user is not logged in or needs to provide a password.
	echo $this->loadTemplate('login');
}
else
{
    if (!empty($redirect))
        $app->redirect(JRoute::_($redirect));
	// The user is already logged in.
	echo $this->loadTemplate('logout');
}
