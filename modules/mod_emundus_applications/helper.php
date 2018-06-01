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
	static function getApplications($layout)
	{
		$user = JFactory::getUser();
		$db	= JFactory::getDbo();

		$query = 'SELECT ecc.*, esc.*, ess.step, ess.value, ess.class ';

		// Hesam layout needs to get the title from the information about the project.
		if ($layout == '_:hesam')
			$query .= ', pro.titre ';

		$query .= ' FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id
					LEFT JOIN #__emundus_setup_status AS ess ON ess.step=ecc.status ';

		if ($layout == '_:hesam')
			$query .= ' LEFT JOIN #__emundus_projet AS pro ON pro.fnum=ecc.fnum ';

		$query .= ' WHERE ecc.applicant_id ='.$user->id.'
					ORDER BY esc.end_date DESC';
//echo str_replace('#_', 'jos', $query);
		$db->setQuery($query);
		$result = $db->loadObjectList('fnum');
		return (array) $result;
	}

	// get poll id ofthe appllicant
	static function getPoll()
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

	static function getOtherCampaigns($uid) {

		$db = JFactory::getDbo();

		$query = 'SELECT count(id)
					FROM #__emundus_setup_campaigns
					WHERE published = 1
					AND end_date >= NOW()
					AND start_date <= NOW()
					AND id NOT IN (
						select campaign_id
						from #__emundus_campaign_candidature
						where applicant_id='. $uid .'
					)';

		try {

			$db->setQuery($query);

			if ($db->loadResult() > 0)
				return true;
			else
				return false;

		} catch (Exception $e) {
			JLog::add("Error at query : ".$query, JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	static function getFutureYearCampaigns($uid) {

		$db = JFactory::getDbo();

		$query = 'SELECT count(id)
					FROM #__emundus_setup_campaigns
					WHERE published = 1
					AND end_date >= NOW()
					AND start_date <= NOW()
					AND year NOT IN (
						select sc.year
						from #__emundus_campaign_candidature as cc
						LEFT JOIN #__emundus_setup_campaigns as sc ON sc.id = cc.campaign_id
						where applicant_id='. $uid .'
					)';

		try {

			$db->setQuery($query);

			if ($db->loadResult() > 0)
				return true;
			else
				return false;

		} catch (Exception $e) {
			JLog::add("Error at query : ".$query, JLog::ERROR, 'com_emundus');
			return false;
		}
	}
}
