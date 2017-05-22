<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundus_applications
 * @copyright	Copyright (C) 2016 eMundus SAS. All rights reserved.
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
		$result = $db->loadObjectList('fnum');
		return (array) $result;
	}

	// get poll id of the applicant
	static function getPoll($params)
	{
		$user 	= JFactory::getUser();
		$db		= JFactory::getDbo();

		$query = 'SELECT id
					FROM #__emundus_survey AS es
					WHERE es.user ='.$user->id;
//echo str_replace('#_', 'jos', $query);
		$db->setQuery($query);
		$id = $db->loadResult();
		return $id>0?$id:0;
	}
}
