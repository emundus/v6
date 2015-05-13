<?php

/**
* @version		2.5.0
* @package		Joomla
* @copyright	Copyright (C) 2008 - 2010 Décision Pulique. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class plgSystemComUsersOverride extends JPlugin {

	public function __construct(&$subject, $config = array()) {
		die('test');
    	parent::__construct($subject, $config);
	}
 
	public function onAfterRoute() {
		$app = JFactory::getApplication();
		if('com_content' == JRequest::getCMD('option') && !$app->isAdmin()) {
			require_once(dirname(__FILE__) . DS . 'models' . DS . 'my_users_login_model.php');
		}
	}
}
?>