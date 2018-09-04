<?php
/**
 * A cron task to email records to a give set of users
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to update eMundus DB with data from GesCOF JSON files.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.migalFTP
 * @since       3.0
 */
class PlgFabrik_CronmigalFTP extends PlgFabrik_Cron {
	/**
	 * Check if the user can use the plugin
	 *
	 * @param   string $location To trigger plugin on
	 * @param   string $event    To trigger plugin on
	 *
	 * @return  bool can use or not
	 */
	public function canUse($location = null, $event = null) {
		return true;
	}

	/**
	 * Do the plugin action
	 *
	 * @param   array &$data data
	 * @param   object  &$listModel  List model
	 */
	public function process(&$data, &$listModel) {

		$db = JFactory::getDbo();

		$params = $this->getParams();
		$product_url = $params->get('productURL', null);
		$session_url = $params->get('sessionURL', null);

		if (empty($product_url) || empty($session_url))
			return false;

		// Hash the distant file in order to compare its contents with those of the past.
		$product_json = file_get_contents($product_url);
		$product_md5 = md5($product_json);

		//TODO: Add Fabrik logs to this.

		//TODO : Question to client: are we getting the participants from GesCOF, if so, are we inserting them into eMundus?

		// Get the list of files which are porduct JSON files.
		$product_files = glob(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.'/products-*.json');

		// If the MD5 of the distant file is different than the MD5 of the last saved file.
		if (empty($product_files) || $product_md5 != substr(explode('-', max($product_files))[2], 0, -5)) {

			// Write the distant file to the local disk and name it as so : products-YYYYMMDD-MD5.json
			$product_filename = 'products-'.date('Ymd').'-'.$product_md5.'.json';
			file_put_contents(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.$product_filename, $product_json);

			// Delete the oldest file (if there are 5).
			if (sizeof($product_files) >= 5)
				unlink(min($product_files));

			$parsed_json = json_decode($product_json);
			if (empty($parsed_json))
				return false;

			// TODO: Begin the DB queries here.

		}

		// Hash the distant file in order to compare its contents with those of the past.
		$session_json = file_get_contents($session_url);
		$session_md5 = md5($session_json);

		// Get the list of files which are session JSON files.
		$session_files = glob(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.'/sessions-*.json');

		// Compare MD5 of the file for sessions.
		if (empty($session_files) || $session_md5 != substr(explode('-', max($session_files))[2], 0, -5)) {

			// Write the distant file to the local disk and name it as so : sessions-YYYYMMDD-MD5.json
			$session_filename = 'sessions-'.date('Ymd').'-'.$product_md5.'.json';
			file_put_contents(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.$session_filename, $session_json);

			// Delete the oldest file (if there are 5).
			if (sizeof($session_files) >= 5)
				unlink(min($session_files));

			$query = $db->getQuery(true);

			// Get all results from DB and compare them with JSON results.
			$query
				->select('*')
				->from($db->quoteName('#__emundus_setup_teaching_unity'));

			$db->setQuery($query);

			try {
				$db_array = $db->loadAssocList();
			} catch (Exception $e) {
				//TODO: Handle errors.
			}

			$json_array = json_decode($session_json, true);

			// If the format is invalid then the value will be null.
			// This helps catch potential issues with the Migal system.
			if (empty($parsed_json))
				return false;


			// To separate the data into 3 parts we need to organize the objects.
			$to_create = array();
			$to_update = array();
			$to_delete = $db_array;

			foreach ($json_array as $json_item) {

				// If the item is in the JSON but not in the DB: mark as CREATE.
				$create = true;

				$i = 0;
				foreach ($db_array as $db_item) {

					// We need to see if a value already exists in the DB for the data.
					// If so then it is an UPDATE and also not a CREATE.
					if ($db_item['session_code'] == $json_item['numsession']) {
						$to_update[$db_item['session_code']] = $json_item;
						$create = false;
						unset($to_delete[$i]);
					}
					$i++;

				}

				if ($create)
					$to_create[] = $json_item;

			}


			// Now that we have all the data sorted into the correct slots it's time to build the MEATY queries.
			// UNPUBLISH
			if (!empty($to_delete)) {

				// Deleting consists of simply setting published to 0.
				$query = $db->getQuery(true);

				$in = array();
				foreach ($to_delete as $item) {
					$in[] = $item['numsession'];
				}

				// Unpublish teaching unit.
				$query
					->update($db->quoteName('#__emundus_setup_teaching_unity'))
					->set($db->quote('published').' = 0')
					->where($db->quoteName('session_code').' IN ('.implode(',', $in).')');

				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					// TODO: Handle errors.
					return false;
				}

				// TODO: Add session_code to setup campaigns.
				// Unpublish registration period.
				$query
					->update($db->quoteName('#__emundus_setup_campaigns'))
					->set($db->quote('published').' = 0')
					->where($db->quoteName('session_code').' IN ('.implode(',', $in).')');

				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					// TODO: Handle errors.
					return false;
				}

			}



			// UPDATE or DO NOTHING
			if (!empty($to_update)) {

			}



			// INSERT
			if (!empty($to_create)) {

				// Get the highest ID for the campaigns table, this will be used to establish the foreign key between campaigns and teaching units.
				$query = $db->getQuery(true);
				$query
					->select('MAX(id)')
					->from($db->quoteName('#__emundus_setup_campaigns'));
				$db->setQuery($query);
				try {
					$campaign_id = $db->loadResult();
				} catch (Exception $e) {
					return false;
				}

				// DB table struct for different tables.
				$programme_columns = ['code', 'label', 'notes', 'published', 'programmes', 'apply_online', 'url'];
				$campaign_columns = ['session_code', 'label', 'description', 'short_description', 'start_date', 'end_date', 'profile_id', 'training', 'published'];
				$teaching_columns = ['code', 'session_code', 'label', 'notes', 'published', 'price', 'date_start', 'date_end', 'registration_periode', 'days', 'hours', 'hours_per_day', 'min_occupants', 'max_occupants', 'occupants', 'seo_title', 'location_title', 'location_address', 'location_zip', 'location_city', 'location_region'];

				// Build all value lists for the different inserts at once, this avoids having to loop multiple times.
				$programme_values = array();
				$campaign_values = array();
				$teaching_values = array();
				foreach ($to_create as $item) {

					$programme_values[] = implode(',', [
						$db->quote($item['codeproduit']),
						$db->quote($item['intituleproduit']),
						$db->quote($item['observations']),
						'1',
						'FORMATION',
						$db->quote($item['familleproduits']),
						'1',
						$db->quote($item['libellestageurl'])
					]);

					$campaign_values[] = implode(',', [
						$db->quote($item['numsession']),
						$db->quote($item['libellestage']),
						$db->quote($item['observations']),
						$db->quote($item['observations']),
						'NOW()',
						$db->quote($item['datedebutsession']['date']),
						'1001',
						$db->quote($item['codeproduit']),
						'1'
					]);

					$teaching_values[] = implode(',', [
						$db->quote($item['codeproduit']),
						$db->quote($item['numsession']),
						$db->quote($item['libellestage']),
						$db->quote($item['observations']),
						'1',
						$db->quote($item['coutsession']),
						$db->quote($item['datedebutsession']['date']),
						$db->quote($item['datefinsession']['date']),
						++$campaign_id,
						$db->quote($item['nbjours']),
						$db->quote($item['nbheures']),
						$db->quote($item['nbheuresjoursession']),
						$db->quote($item['effectif_mini']),
						$db->quote($item['effectif_maxi']),
						$db->quote($item['placedispo']['nbInscrit']),
						$db->quote($item['titleseo']),
						$db->quote($item['libellelieu']),
						$db->quote($item['adresse1lieu'].' '.$item['adresse2lieu']),
						$db->quote($item['cplieu']),
						$db->quote($item['villelieu']),
						$db->quote($item['region'])
					]);

				}

				// Create a new programme for the session / product.
				$query = $db->getQuery(true);
				$query
					->insert($db->quoteName('#__emundus_setup_programmes'))
					->columns($programme_columns)
					->values($programme_values);
				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					// TODO: Handle errors.
					return false;
				}

				// Create a new registration period for the session.
				// This period will run from now until the session starts.
				$query = $db->getQuery(true);
				$query
					->insert($db->quoteName('#__emundus_setup_campaigns'))
					->columns($campaign_columns)
					->values($campaign_values);
				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					// TODO: Handle errors.
					return false;
				}

				// Here we add the meatiest part of the data to the teaching_unity table.
				// This will contain things like occupants, location, price, etc...
				$query = $db->getQuery(true);
				$query
					->insert($db->quoteName('#__emundus_setup_teaching_unity'))
					->columns($teaching_columns)
					->values($teaching_values);
				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					// TODO: Handle errors.
					return false;
				}

			}
		}
	}
}
