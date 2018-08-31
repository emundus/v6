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
			if (!empty($to_delete)) {

				// Deleting consists of simply setting published to 0.
				$query = $db->getQuery(true);

				$in = array();
				foreach ($to_delete as $item) {
					$in[] = $item['session_code'];
				}

				$query
					->update($db->quoteName('#__emundus_setup_teaching_unity'))
					->set($db->quote('published').' = 0')
					->where($db->quoteName('session_code').' IN ('.implode(',', $in).')');

				$db->setQuery($query);
				try {
					$db->execute();
				} catch (Exception $e) {
					// TODO: Handle errors.
				}

			}

			if (!empty($to_update)) {

			}

			if (!empty($to_create)) {

			}
		}
	}
}
