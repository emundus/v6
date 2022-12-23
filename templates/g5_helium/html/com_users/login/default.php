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

$eMConfig = JComponentHelper::getParams('com_emundus');

if (!empty($cookieLogin) || $this->user->get('guest'))
{
    // Get campaign ID and course from url
    $this->campaign = $jinput->get('cid');
    $this->course   = $jinput->get('course');
    $this->displayRegistration   = $eMConfig->get('display_registration_link',1);
    $this->registrationLink   = $eMConfig->get('registration_link','');
	$this->displayForgotten   = $eMConfig->get('display_forgotten_password_link',1);
	$this->forgottenLink   = $eMConfig->get('forgotten_password_link','index.php?option=com_users&view=reset') ?: 'index.php?option=com_users&view=reset';

	if(empty($this->registrationLink)){
		if(!empty($this->campaign) && !empty($this->course)){
			$this->registrationLink = 'index.php?option=com_users&view=registration&course=' . $this->course . '&cid=' . $this->campaign;
		} else {
			$this->registrationLink = 'index.php?option=com_users&view=registration';
		}
	}
    JFactory::getSession()->set('cid',$this->campaign);
    JFactory::getSession()->set('course', $this->course);

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
