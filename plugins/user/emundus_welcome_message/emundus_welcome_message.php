<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');

if (!defined('DS')) {
    define('DS',DIRECTORY_SEPARATOR);
}

class plgUserEmundus_welcome_message extends JPlugin {
	
	function _construct(& $subject, $config) {
		parent::__construct($subject, $config);
	}
	
	function onLoginUser($user, $option) {
		$this->afterLogin($user, $option);
	}
	
	//in J1.7 this event is called
	function onUserLogin($user,$option) {
		$this->afterLogin($user, $option);
	}
	
	function editMessage($userid, $message) {
		$mainframe = JFactory::getApplication();
		$name 	   = JFactory::getUser($userid)->name;
		$username  = JFactory::getUser($userid)->username;
		$siteurl   = JURI::base();
		$sitename  = $mainframe->getCfg('sitename');
		
		$pattern = array("/\[NAME\]/", "/\[USERNAME\]/", "/\[SITEURL\]/", "/\[SITENAME\]/");
		$replace = array($name, $username, $siteurl, $sitename);
		$message = preg_replace($pattern, $replace, $message);
		return $message;
	}
	
	function sendMessage($userid, $subject, $message, $emessage, $email) {
		$mainframe = JFactory::getApplication();

		if ($emessage) {
			$mainframe->enqueueMessage($message);
		}

		if ($email) {
			$mailer	= JFactory::getMailer();
			$email  = JFactory::getUser($userid)->email;
			$mailer->addRecipient($email);
			$mailer->setSubject($subject);
			$mailer->setBody($message);					
			$mailer->send();
		}
	}
	
	function afterLogin($user, $option) {
		$username  = JFactory::getApplication()->input->get('username');
		
		$mainframe = JFactory::getApplication();
		$userid    = JUserHelper::getUserId($username);
		
		// For Guest, do nothing and just return, let the joomla handle it
		if (!$userid)
			return;
			
		$plugin = JPluginHelper::getPlugin('user', 'emundus_welcome_message');
		
 		$params = new JRegistry($plugin->params);
 	
 		$lastvisitdate = JFactory::getUser($userid)->lastvisitDate;
		$block		   = JFactory::getUser($userid)->block;

 		// Check for first login
		if ($lastvisitdate == "0000-00-00 00:00:00" && $block == 0) {
			$subject    = $params->get('subject', '');
			$message    = $params->get('message', '');
			$emessage   = $params->get('EnqueueMessage', false);
			$email      = $params->get('Email', false);
			$subject    = $this->editMessage($userid,$subject);
			$message    = $this->editMessage($userid,$message);
 			$this->sendMessage($userid, $subject, $message, $emessage, $email);
		}
	}
}
