<?php
/**
 * @package        Joomla.Site
 * @subpackage     mod_emundus_send_application
 * @copyright      Copyright (C) 20018 eMundus All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modemundusSendApplicationHelper
{

	// get users sorted by activation date
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

	static function getSearchEngineId($fnum)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery('true');

		$query
			->select($db->quoteName('id'))
			->from($db->quoteName('#__emundus_recherche'))
			->where($db->quoteName('fnum') . ' LIKE "' . $fnum . '"');

		try {
			$db->setQuery($query);

			return $db->loadResult();

		}
		catch (Exception $e) {
			JLog::add("Error at query : " . $query, JLog::ERROR, 'com_emundus');

			return false;
		}
	}
}
