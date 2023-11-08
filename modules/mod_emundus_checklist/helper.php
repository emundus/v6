<?php
defined('_JEXEC') or die('Access Deny');

class modEmundusChecklistHelper
{
	static function getApplication($fnum)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(['ecc.*', 'esc.*', 'ess.step', 'ess.value', 'ess.class'])
			->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
			->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'))
			->leftJoin($db->quoteName('#__emundus_setup_status', 'ess') . ' ON ' . $db->quoteName('ess.step') . ' = ' . $db->quoteName('ecc.status'))
			->where($db->quoteName('ecc.fnum') . ' LIKE ' . $db->quote($fnum))
			->order($db->quoteName('esc.end_date') . ' DESC');
		$db->setQuery($query);

		return $db->loadObject();
	}
}
