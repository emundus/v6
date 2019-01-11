<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');

if (!defined('DS')) {
    define('DS',DIRECTORY_SEPARATOR);
}

class plgUserCci_drh_form extends JPlugin {
	
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
	
	function afterLogin($user, $option) {

		$username  = JRequest::getVar('username');
		$userid    = JUserHelper::getUserId($username);
		
		// For Guest, do nothing and just return, let joomla handle it
		if (!$userid) {
			return;
		}
			
		$plugin = JPluginHelper::getPlugin('user', 'emundus_cci_drh_form');
 		$params = new JRegistry($plugin->params);

 		$lastvisitdate = JFactory::getUser($userid)->lastvisitDate;
		$block		   = JFactory::getUser($userid)->block;

 		// Check for first login
		if ($lastvisitdate == "0000-00-00 00:00:00" && $block == 0) {

			// Check of the user is DRH.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName('profile'))
				->from($db->quoteName('#__emundus_users'))
				->where($db->quoteName('user_id').' = '.$userid);
			$db->setQuery($query);

			try {
				$profile = $db->loadResult();
			} catch (Exception $e) {
				$profile = null;
			}

			if ($profile == 1002) {

				// If the user is DRH: check if he has a company already.
				$query->clear()
					->select($db->quoteName('id'))
					->from($db->quoteName('#__emundus_user_entreprise'))
					->where($db->quoteName('user').' = '.$userid.' AND '.$db->quoteName('profile').' = 1002');
				$db->setQuery($query);

				try {
					 if (!empty($db->loadResult())) {
					 	return;
					 }
				} catch (Exception $e) {
					return;
				}

				JFactory::getApplication()->redirect($params->get('url'));
			}

		}
	}
}
