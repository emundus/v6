<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');

if (!defined('DS')) {
    define('DS',DIRECTORY_SEPARATOR);
}

class plgUserEmundus_cci_drh_form extends JPlugin {
	
	function _construct(& $subject, $config) {
		parent::__construct($subject, $config);

		$this->loadLanguage();
		$user = JFactory::getUser();

		if (!$user->guest) {

			// need to load fresh instance
			$table = JTable::getInstance('user', 'JTable');
			$table->load($user->id);

			// Reidrect the user if the param is present.
			$user_params = new JRegistry($table->params);
			if ($user_params->get('needs_company', 'false') == "true") {
				$application = JFactory::getApplication();
				$application->enqueueMessage('Afin de pouvoir continuer en tant que Décideur RH, vous devez déclarer votre entreprise.');
				$application->redirect($this->params->get('url', null));
			}
		}

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

		$current_user = JFactory::getUser($userid);
 		$lastvisitdate = $current_user->lastvisitDate;
		$block		   = $current_user->block;

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

				// Set the user param to notify him of the fact that he needs a company.
				$current_user->setParam('needs_company', 'true');

				// Get the raw User Parameters
				$params = $current_user->getParameters();

				// Set the user table instance to include the new token.
				$table = JTable::getInstance('user', 'JTable');
				$table->load($userid);
				$table->params = $params->toString();
			}

		}
	}
}
