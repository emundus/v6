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
if (!empty($cookieLogin) || $this->user->get('guest'))
{
    // Get campaign ID and course from url
    $jinput = JFactory::getApplication()->input;
    $this->campaign = $jinput->get->get('cid');
    $this->course   = $jinput->get->get('course');
    $this->redirect   = $jinput->get->getBase64('redirect');

    if (!empty($this->redirect) && $this->user->get('guest'))
        $app->redirect(JRoute::_($redirecturl));
	// The user is not logged in or needs to provide a password.
	echo $this->loadTemplate('login');
}
else
{
	// The user is already logged in.
	echo $this->loadTemplate('logout');
}
