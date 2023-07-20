<?php
/**
 * @version		$Id: application.php 14401 2022-09-09 14:10:00Z brice.hubinet@emundus.fr $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2022 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

/**
 * Emundus Component Application Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperApplication {

	public static function getCcidByFnum($fnum): int
	{
		$candidature_id = 0;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		try
		{
			$query->select('id')
				->from($db->quoteName('#__emundus_campaign_candidature'))
				->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
			$db->setQuery($query);
			$candidature_id = $db->loadResult();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $candidature_id;
	}
}