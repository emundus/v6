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
	}

	public function onUserAfterSave($user, $isnew, $result, $error) {

 		// Check for first login
		if ($isnew) {

			// Check of the user is DRH.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName('profile'))
				->from($db->quoteName('#__emundus_users'))
				->where($db->quoteName('user_id').' = '.$user['id']);
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
					->where($db->quoteName('user').' = '.$user['id'].' AND '.$db->quoteName('profile').' = 1002');
				$db->setQuery($query);

				try {
					 if (!empty($db->loadResult())) {
					 	return;
					 }
				} catch (Exception $e) {
					return;
				}

				$current_user = JFactory::getUser($user['id']);

				// Set the user param to notify him of the fact that he needs a company.
				$current_user->setParam('needs_company', 'true');

				// Get the raw User Parameters
				$params = $current_user->getParameters();

				// Set the user table instance to include the new token.
				$table = JTable::getInstance('user', 'JTable');
				$table->load($user['id']);
				$table->params = $params->toString();
			}

		}
	}
}
