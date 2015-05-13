<?php
/**
* @version		$Id: mod_extlogin.php 7692 2007-06-08 20:41:29Z tcp $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');
$params->def('greeting', 1);
$params->def('login', 1);
$params->def('logout', 1);


// die('<pre>'.print_r($params,true).'</pre>');

$type 	= @modExtLoginHelper::getType();
$return	= @modExtLoginHelper::getReturnURL($params, $type);

$user = JFactory::getUser();
			
require(JModuleHelper::getLayoutPath('mod_extlogin'));

// init vars
$style                 = $params->get('style', 'default');
$pretext               = $params->get('pretext', '');
$posttext              = $params->get('posttext', '');
$text_mode             = $params->get('text_mode', 'input');
$login_button          = $params->get('login_button', 'icon');
$logout_button         = $params->get('logout_button', 'text');
$auto_remember         = $params->get('auto_remember', '1');
$lost_password         = $params->get('lost_password', '1');
$lost_username         = $params->get('lost_username', '1');
$registration          = $params->get('registration', '1');
$update_profile		   = $params->get('update_profile','1');
?>