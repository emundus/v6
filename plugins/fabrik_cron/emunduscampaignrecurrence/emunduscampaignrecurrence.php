<?php

/**
 * A cron task to email a recall to incomplet applications
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to export to PDF files to a local directory
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusexportpdf
 * @since       3.0
 */

class PlgFabrik_Cronemunduscampaignrecurrence extends PlgFabrik_Cron {

	/**
	 * Check if the user can use the plugin
	 *
	 * @param   string  $location  To trigger plugin on
	 * @param   string  $event     To trigger plugin on
	 *
	 * @return  bool can use or not
	 */
	public function canUse($location = null, $event = null) {
		return true;
	}


	/**
	 * Do the plugin action
	 *
	 * @param array  &$data data
	 *
	 * @return  int  number of records updated
	 * @throws Exception
	 */
	public function process(&$data, &$listModel) {
		$campaigns_duplicated = 0;

		// We are going to check if campaign with recurrence is active and if it is time to duplicate it
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$current_date = EmundusHelperDate::getCurrentDate('Y-m-d H:i:s', true);

		$query->select('*')
			->from('#__emundus_setup_campaigns')
			->where('published = 1')
			->andWhere('start_date <= ' . $db->quote($current_date))
			->andWhere('end_date >= ' . $db->quote($current_date))
			->andWhere('params IS NOT NULL AND JSON_EXTRACT(params, "$.is_recurring") = "1"');

		try {
			$db->setQuery($query);
			$campaigns = $db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting campaigns in plugin emunduscampaignrecurrence at query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
		}

		foreach ($campaigns as $campaign) {
			// does campaign N+1 exist ?
			$query->clear()
				->select('*')
				->from('#__emundus_setup_campaigns')
				->where('published = 1')
				->andWhere('params IS NOT NULL and JSON_EXTRACT(params, "$.recurring_campaign_id") = '.$campaign->id);

			try {
				$db->setQuery($query);
				$campaigns_recurring = $db->loadObjectList();
			} catch (Exception $e) {
				JLog::add('Error getting campaigns in plugin emunduscampaignrecurrence at query : '.$query->__toString(), JLog::ERROR, 'com_emundus.error');
			}

			if (empty($campaigns_recurring)) {
				// new start date is end date + params->recurring_delay (in days)
				$params = json_decode($campaign->params);
				$recurring_delay = $params->recurring_delay;
				$start_date = date('Y-m-d H:i:s', strtotime($campaign->end_date . ' + '.$recurring_delay.' days'));

				// duration is interval between start and end date
				$duration = date_diff(date_create($campaign->start_date), date_create($campaign->end_date));
				$duration = $duration->format('%a');

				// new end date is start date + duration
				$end_date = date('Y-m-d', strtotime($start_date . ' + '.$duration.' days'));
				$end_date = date('Y-m-d', strtotime($end_date)) . ' ' . date('H:i:s', strtotime($campaign->end_date));

				// create new campaign
				$old_campaign_id = $campaign->id;
				$campaign_copy = $campaign;
				$campaign_copy->id = null;
				$campaign_copy->start_date = $start_date;
				$campaign_copy->end_date = $end_date;

				$start_date_year = date('Y', strtotime($campaign_copy->start_date));
				$end_date_year = date('Y', strtotime($campaign_copy->end_date));
				$campaign_copy->year = $start_date_year == $end_date_year ? $start_date_year : $start_date_year.'-'.$end_date_year;

				$label = str_replace(date('Y', strtotime($campaign->start_date)), date('Y', strtotime($start_date)), $campaign->label);
				$campaign_copy->label = $label;

				$campaign_copy->params = json_decode($campaign_copy->params);
				$campaign_copy->params->recurring_campaign_id = $old_campaign_id;
				$campaign_copy->params = json_encode($campaign_copy->params);

				$query->clear()
					->insert('#__emundus_setup_campaigns')
					->columns($db->quoteName(array_keys(get_object_vars($campaign_copy))))
					->values(implode(',', $db->quote(get_object_vars($campaign_copy))));

				try {
					$db->setQuery($query);
					$inserted = $db->execute();

					if ($inserted) {
						$campaigns_duplicated++;

						$params->is_recurring = 0;
						$query->clear()
							->update('#__emundus_setup_campaigns')
							->set('params = ' . $db->quote(json_encode($params)))
							->where('id = '.$old_campaign_id);

						$db->setQuery($query);
						$db->execute();
					}
				} catch (Exception $e) {
					JLog::add('Error duplicating campaign in plugin emunduscampaignrecurrence at query : '.$query->__toString(), JLog::ERROR, 'com_emundus.error');
				}
			} else {
				// remove recurring params if campaign N+1 already exists
				$params = json_decode($campaign->params);
				$params->is_recurring = 0;

				$query->clear()
					->update('#__emundus_setup_campaigns')
					->set('params = ' . $db->quote(json_encode($params)))
					->where('id = '.$campaign->id);

				try {
					$db->setQuery($query);
					$db->execute();
				} catch (Exception $e) {
					JLog::add('Error updating campaign in plugin emunduscampaignrecurrence at query : '.$query->__toString(), JLog::ERROR, 'com_emundus.error');
				}
			}
		}

		return $campaigns_duplicated;
	}
}
