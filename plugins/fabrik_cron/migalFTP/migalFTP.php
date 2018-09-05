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

		// LOGGER
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.migal.info.php'], JLog::INFO);
		JLog::addLogger(['text_file' => 'com_emundus.migal.error.php'], JLog::ERROR);

		$db = JFactory::getDbo();

		$rows_updated = 0;

		$params = $this->getParams();
		$product_url = $params->get('productURL', null);
		$session_url = $params->get('sessionURL', null);

		if (empty($product_url) && empty($session_url))
			return false;

		if (!empty($product_url)) {
			// Hash the distant file in order to compare its contents with those of the past.
			$product_json = file_get_contents($product_url);
			$product_md5  = md5($product_json);

			//TODO: Add Fabrik logs to this.

			//TODO : Question to client: are we getting the participants from GesCOF, if so, are we inserting them into eMundus?

			// Get the list of files which are porduct JSON files.
			$product_files = glob(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.'/products-*.json');

			// If the MD5 of the distant file is different than the MD5 of the last saved file.
			if (empty($product_files) || $product_md5 != substr(explode('-', max($product_files))[2], 0, -5)) {

				// Write the distant file to the local disk and name it as so : products-YYYYMMDD-MD5.json
				$product_filename = 'products-'.date('Ymd').'-'.$product_md5.'.json';
				file_put_contents(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.$product_filename, $product_json);

				JLog::add('Created NEW product JSON file with filename: '.$product_filename, JLog::INFO, 'com_emundus');

				// Delete the oldest file (if there are 5).
				if (sizeof($product_files) >= 5)
					unlink(min($product_files));

				$parsed_json = json_decode($product_json);
				if (!empty($parsed_json))
					/* TODO : Product JSON DB Queries? */ echo '';
				else
					JLog::add('Product JSON is empty or invalid', JLog::ERROR, 'com_emundus');

			}
		}

		if (!empty($session_url)) {
			// Hash the distant file in order to compare its contents with those of the past.
			$session_json = file_get_contents($session_url);
			$session_md5 = md5($session_json);

			// Get the list of files which are session JSON files.
			$session_files = glob(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.'sessions-*.json');

			// Compare MD5 of the file for sessions.
			if (empty($session_files) || $session_md5 != substr(explode('-', max($session_files))[2], 0, -5)) {

				// Write the distant file to the local disk and name it as so : sessions-YYYYMMDD-MD5.json
				$session_filename = 'sessions-'.date('Ymd').'-'.$session_md5.'.json';
				file_put_contents(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'migalFTP'.DS.'files'.DS.$session_filename, $session_json);

				JLog::add('Created NEW session JSON file with filename: '.$session_filename, JLog::INFO, 'com_emundus');

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
					JLog::add('Error getting teaching units in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
					return false;
				}

				$json_array = json_decode($session_json, true);

				// If the format is invalid then the value will be null.
				// This helps catch potential issues with the Migal system.
				if (empty($json_array)) {
					JLog::add('Error parsing session JSON.', JLog::ERROR, 'com_emundus');
					return false;
				}


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

				JLog::add('Differential analysis complete: CREATE('.sizeof($to_create).') UPDATE('.sizeof($to_update).') DELETE('.sizeof($to_delete).')', JLog::INFO, 'com_emundus');

				// Now that we have all the data sorted into the correct slots it's time to build the MEATY queries.
				// UNPUBLISH
				if (!empty($to_delete)) {

					// Deleting consists of simply setting published to 0.
					$in = array();
					foreach ($to_delete as $item) {
						$in[] = $item['numsession'];
					}

					// Unpublish teaching unit.
					$query = $db->getQuery(true);
					$query
						->update($db->quoteName('#__emundus_setup_teaching_unity'))
						->set($db->quote('published').' = 0')
						->where($db->quoteName('session_code').' IN ('.implode(',', $in).')');
					$db->setQuery($query);
					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Error unpublishing teaching units in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
					}
					JLog::add('DELETED teaching units in query: '.$query->__toString(), JLog::INFO, 'com_emundus');
					$rows_updated += $db->getAffectedRows();

					// Unpublish registration period.
					$db->setQuery($query);
					$query
						->update($db->quoteName('#__emundus_setup_campaigns'))
						->set($db->quote('published').' = 0')
						->where($db->quoteName('session_code').' IN ('.implode(',', $in).')');
					$db->setQuery($query);
					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Error unpublishing campaigns in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
					}
					$rows_updated += $db->getAffectedRows();
					JLog::add('DELETED campaigns in query: '.$query->__toString(), JLog::INFO, 'com_emundus');

				}


				// UPDATE or DO NOTHING
				if (!empty($to_update)) {

					$query = $db->getQuery(true);
					$query
						->select(['t.*', $db->quoteName('p.label', 'product_name'), $db->quoteName('p.url'), $db->quoteName('p.programmes', 'categ')])
						->from($db->quoteName('#__emundus_setup_teaching_unity','t'))
						->leftJoin($db->quoteName('#__emundus_setup_programmes','p').' ON t.code = p.code')
						->where($db->quoteName('t.session_code').' IN ('.implode(',', $db->quote(array_keys($to_update))).')');
					$db->setQuery($query);
					try {
						$db_array = $db->loadAssocList();
					} catch (Exception $e) {
						JLog::add('Error getting data for update comparisons at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
					}

					foreach ($db_array as $db_item) {

						$update_item = $to_update[$db_item['session_code']];
						$fields = array();

						// In case the programme has been unpublished (has gone through the DELETE procedure): republish it.
						if ($db_item['published'] == '0') {
							$fields[] = $db->quoteName('p.published').' = 1';
							$fields[] = $db->quoteName('c.published').' = 1';
							$fields[] = $db->quoteName('t.published').' = 1';
						}

						// Compare each field and update those which have differences.
						// Product name = programme label
						if ($db_item['product_name'] != $update_item['intituleproduit'])
							$fields[] = $db->quoteName('p.label').' = '.$db->quote($update_item['intituleproduit']);

						// Product url = programme url
						if ($db_item['url'] != $update_item['libellestageurl'])
							$fields[] = $db->quoteName('p.url').' = '.$db->quote($update_item['libellestageurl']);

						// Product family = programme programmes
						if ($db_item['categ'] != $update_item['familleproduits'])
							$fields[] = $db->quoteName('p.programmes').' = '.$db->quote($update_item['familleproduits']);

						// Product code = programme code
						if ($db_item['code'] != $update_item['codeproduit']) {
							$fields[] = $db->quoteName('p.code').' = '.$db->quote($update_item['codeproduit']);
							$fields[] = $db->quoteName('c.training').' = '.$db->quote($update_item['codeproduit']);
							$fields[] = $db->quoteName('t.code').' = '.$db->quote($update_item['codeproduit']);
						}

						// Product observations = programme notes, campaign description, teaching_unit notes
						if ($db_item['notes'] != $update_item['observations']) {
							$fields[] = $db->quoteName('p.notes').' = '.$db->quote($update_item['observations']);
							$fields[] = $db->quoteName('c.description').' = '.$db->quote($update_item['observations']);
							$fields[] = $db->quoteName('c.short_description').' = '.$db->quote($update_item['observations']);
							$fields[] = $db->quoteName('t.notes').' = '.$db->quote($update_item['observations']);
						}

						// Session price = teaching unit price
						if ($db_item['price'] != $update_item['coutsession'])
							$fields[] = $db->quoteName('t.price').' = '.$db->quote($update_item['coutsession']);

						// Session start date = teaching unit start date and campaign end date (reminder: cmapaigns run until the session starts)
						if ($db_item['date_start'].'.000000' != $update_item['datedebutsession']['date']) {
							$fields[] = $db->quoteName('c.end_date').' = '.$db->quote($update_item['datedebutsession']['date']);
							$fields[] = $db->quoteName('t.date_start').' = '.$db->quote($update_item['datedebutsession']['date']);
						}

						// Session end date = teaching unit end date
						if ($db_item['date_end'].'.000000' != $update_item['datefinsession']['date'])
							$fields[] = $db->quoteName('t.date_end').' = '.$db->quote($update_item['datefinsession']['date']);

						// Session days = teaching unit days
						if ($db_item['days'] != $update_item['nbjours'])
							$fields[] = $db->quoteName('t.days').' = '.$db->quote($update_item['nbjours']);

						// Session hours = teaching unit hours
						if ($db_item['hours'] != $update_item['nbheures'])
							$fields[] = $db->quoteName('t.hours').' = '.$db->quote($update_item['nbheures']);

						// Session hours per day = teaching unit hours per day
						if ($db_item['hours_per_day'] != $update_item['nbheuresjoursession'])
							$fields[] = $db->quoteName('t.hours_per_day').' = '.$db->quote($update_item['nbheuresjoursession']);

						// Session minimum occupants = teaching unit minimum occupants
						if ($db_item['min_occupants'] != $update_item['effectif_mini'])
							$fields[] = $db->quoteName('t.min_occupants').' = '.$db->quote($update_item['effectif_mini']);

						// Session maximum occupants = teaching unit maximum occupants
						if ($db_item['max_occupants'] != $update_item['effectif_maxi'])
							$fields[] = $db->quoteName('t.max_occupants').' = '.$db->quote($update_item['effectif_maxi']);

						// Session occupants = teaching unit occupants
						if ($db_item['occupants'] != $update_item['placedispo']['nbInscrit'])
							$fields[] = $db->quoteName('t.occupants').' = '.$db->quote($update_item['placedispo']['nbInscrit']);

						// Session seo title = teaching unit seo title
						if ($db_item['seo_title'] != $update_item['titleseo'])
							$fields[] = $db->quoteName('t.seo_title').' = '.$db->quote($update_item['titleseo']);

						// Session address name = teaching unit address name
						if ($db_item['location_title'] != $update_item['libellelieu'])
							$fields[] = $db->quoteName('t.location_title').' = '.$db->quote($update_item['libellelieu']);

						// Session address1 address2 = teaching unit address
						if ($db_item['location_address'] != $update_item['adresse1lieu'].' '.$update_item['adresse2lieu'])
							$fields[] = $db->quoteName('t.location_address').' = '.$db->quote($update_item['adresse1lieu'].' '.$update_item['adresse2lieu']);

						// Session address zip = teaching unit address zip
						if ($db_item['location_zip'] != $update_item['cplieu'])
							$fields[] = $db->quoteName('t.location_zip').' = '.$db->quote($update_item['cplieu']);

						// Session address city = teaching unit address city
						if ($db_item['location_city'] != $update_item['villelieu'])
							$fields[] = $db->quoteName('t.location_city').' = '.$db->quote($update_item['villelieu']);

						// Session address city = teaching unit address city
						if ($db_item['location_region'] != $update_item['region'])
							$fields[] = $db->quoteName('t.location_region').' = '.$db->quote($update_item['region']);

						// Session prerequisites = teaching unit prerequisites
						if ($db_item['prerequisite'] != $update_item['prerequis'])
							$fields[] = $db->quoteName('t.prerequisite').' = '.$db->quote($update_item['prerequis']);

						// Session prerequisites = teaching unit prerequisites
						if ($db_item['audience'] != $update_item['typepublic'])
							$fields[] = $db->quoteName('t.audience').' = '.$db->quote($update_item['typepublic']);

						// If any of the fields are different, we must run the UPDATE query.
						if (!empty($fields)) {

							$db = JFactory::getDbo();

							// JDatabase PDO does not support multiple table updates, we must resort to standard SQL.
							$query = 'UPDATE '.$db->quoteName('#__emundus_setup_campaigns', 'c').' '.$db->quoteName('#__emundus_setup_programmes', 'p').' '.$db->quoteName('#__emundus_setup_teaching_unity', 't').
									' SET '.implode(',', $fields).
									' WHERE p.code LIKE '.$db->quote($db_item['code']).' AND c.session_code LIKE '.$db->quote($db_item['session_code']).' AND t.session_code LIKE '.$db->quote($db_item['session_code']);
							$db->setQuery($query);

							try {
								$db->execute();
							} catch (Exception $e) {
								JLog::add('Error updating data in query: '.$query, JLog::ERROR, 'com_emundus');
							}
							$rows_updated += $db->getAffectedRows();
							JLog::add('UPDATED data for DB item ['.$db_item['session_code'].'] in query: '.$query, JLog::INFO, 'com_emundus');

						} else {
							JLog::add('No data to update for item ID ['.$db_item['session_code'].']', JLog::INFO, 'com_emundus');
						}
					}
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
						JLog::add('Error getting max ID in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
					}

					if (empty($campaign_id))
						$campaign_id = 0;

					// DB table struct for different tables.
					$programme_columns  = ['code', 'label', 'notes', 'published', 'programmes', 'apply_online', 'url'];
					$campaign_columns   = ['session_code', 'label', 'description', 'short_description', 'start_date', 'end_date', 'profile_id', 'training', 'published'];
					$teaching_columns   = ['code', 'session_code', 'label', 'notes', 'published', 'price', 'date_start', 'date_end', 'registration_periode', 'days', 'hours', 'hours_per_day', 'min_occupants', 'max_occupants', 'occupants', 'seo_title', 'location_title', 'location_address', 'location_zip', 'location_city', 'location_region', 'prerequisite', 'audience'];

					// Build all value lists for the different inserts at once, this avoids having to loop multiple times.
					$programme_values = array();
					$campaign_values = array();
					$teaching_values = array();
					foreach ($to_create as $item) {

						if (!array_key_exists($db->quote($item['codeproduit']), $programme_values)) {
							$programme_values[$item['codeproduit']] = implode(',', [
								$db->quote($item['codeproduit']),
								$db->quote($item['intituleproduit']),
								$db->quote($item['observations']),
								'1',
								$db->quote($item['familleproduits']),
								'1',
								$db->quote($item['libellestageurl'])
							]);
						}

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
							$db->quote($item['region']),
							$db->quote($item['prerequis']),
							$db->quote($item['typepublic'])
						]);

					}

					// Create a new programme for the session / product.
					$query
						->insert($db->quoteName('#__emundus_setup_programmes'))
						->columns($programme_columns)
						->values($programme_values);
					$db->setQuery($query);
					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Error inserting programme data in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
					}
					$rows_updated += $db->getAffectedRows();
					JLog::add('INSERT programme data with query: '.$query->__toString(), JLog::INFO, 'com_emundus');

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
						JLog::add('Error inserting session data in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
					}
					$rows_updated += $db->getAffectedRows();
					JLog::add('INSERT session data with query: '.$query->__toString(), JLog::INFO, 'com_emundus');

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
						JLog::add('Error inserting teaching unit data in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
					}
					$rows_updated += $db->getAffectedRows();
					JLog::add('INSERT teaching unit data with query: '.$query->__toString(), JLog::INFO, 'com_emundus');

				}
			}
		}
		return $rows_updated;
	}
}
