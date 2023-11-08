<?php
defined('_JEXEC') or die('Access Deny');

class EmundusRegister
{
	static function getCode($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('pr.code');
		$query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
		$query->where('ca.training = pr.code AND ca.published=1 AND ca.id=' . $id);
		$db->setQuery($query);

		return $db->loadAssoc();
	}
}

?>
