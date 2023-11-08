<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusCalendarAddHelper
{

	public function getPrograms()
	{

		try {

			$db = JFactory::getDbo();
			$db->setQuery('SELECT code, label FROM #__emundus_setup_programmes');

			return $db->loadObjectList();

		}
		catch (Exception $e) {
			die($e->getMessage());
		}

	}

}

?>