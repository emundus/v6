<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_users_latest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modemundusApplicationsHelper
{
	// get users sorted by activation date
	static function getApplications($params)
	{
		$user 	= JFactory::getUser();
		$db		= JFactory::getDbo();

		$query = 'SELECT ecc.*, esc.*, ess.step, ess.value, ess.class
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id
					LEFT JOIN #__emundus_setup_status AS ess ON ess.step=ecc.status
					WHERE ecc.applicant_id ='.$user->id.' 
					ORDER BY esc.end_date DESC';
//echo str_replace('#_', 'jos', $query);
		$db->setQuery($query);
		$result = $db->loadObjectList();
		return (array) $result;
	}
}
