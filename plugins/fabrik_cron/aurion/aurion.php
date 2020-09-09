<?php
/**
 * A cron task to update eMundus DB with data from Aurion API information.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.aurion
 * @copyright   Copyright (C) 2017-2019  eMundus - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

/**
 * A cron task to update eMundus DB with data from Aurion API information.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.aurion
 * @since       3.9.12
 */
class PlgFabrik_Cronaurion extends PlgFabrik_Cron {

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
	 * @param   array &  $data       data
	 * @param   object  &$listModel  List model
	 *
	 * @return bool|int
	 */
	public function process(&$data, &$listModel) {

		// LOGGER
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.aurion.info.php'], JLog::INFO, 'com_emundus');
		JLog::addLogger(['text_file' => 'com_emundus.aurion.error.php'], JLog::ERROR, 'com_emundus');

		$params = $this->getParams();
		$aurion_url = $params->get('url', null);
		$aurion_login = $params->get('login', null);
		$aurion_pass = $params->get('password', null);
		$aurion_post = $params->get('data', null);
		$aurion_ids = explode(',', $params->get('ids', null));

		if (empty($aurion_url) || empty($aurion_login) || empty($aurion_pass) || empty($aurion_post) || empty($aurion_ids)) {
			JLog::add('Could not run plugin, missing param', JLog::ERROR, 'com_emundus');
			return false;
		}

		$db = JFactory::getDbo();
		$rows_updated = 0;

		$em_db_table_list = $db->getTableList();

		$lock_files = glob(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'aurion'.DS.'files'.DS.'*.lock');
		
		if (!empty($lock_files)) {

			// Only get the first one, if there are more that's an issue.
			$lock_file = $lock_files[0];
			$lock_au_id = substr(explode('aurion'.DS.'files'.DS, $lock_file)[1], 0, -5);
			
			if (in_array($lock_au_id, $aurion_ids)) {
				
				$lock_line = file_get_contents($lock_file);

				if (!empty($lock_line)) {

					$lock_line = (int)$lock_line;
					JLog::add('Found lock file ('.$lock_file.') resuming from line number : '.$lock_line.'.', JLog::INFO, 'com_emundus');

					// Run a scan of the files and check if our script timed out while running, if so, start over from there.
					foreach ($aurion_ids as $key => $au_id) {
						if ($au_id != $lock_au_id) {
							unset($aurion_ids[$key]);
						} else {
							break;
						}
					}
				}
			}
			unlink($lock_file);
		}

		foreach ($aurion_ids as $au_id) {

			$http = new JHttp();

			// The data being posted to the API is being build with the Aurion ID being inserted
			$post_data = str_replace('[AURION_ID]', $au_id, $aurion_post);
			$request_body = [
				'login' => $aurion_login,
				'password' => $aurion_pass,
				'data' => $post_data
			];

			$response = $http->post($aurion_url, $request_body, ['Content-Type' => 'application/x-www-form-urlencoded']);
			if ($response->code === 200) {

				// The API almost always responds with a 200OK, however certain errors are in HTML
				$data = simplexml_load_string($response->body);
				if ($data === false) {
					JLog::add('
						Error parsing XML: this could be an error in the request \n 
						URL: '.$aurion_url.' \n
						POST DATA: '.$post_data.' \n
						RESPONSE BODY: '.$response->body.'
					', JLog::ERROR, 'com_emundus');
					return false;
				}

				if ($data->getName() === 'erreur') {
					JLog::add('
						Error detected: \n 
						URL: '.$aurion_url.' \n
						POST DATA: '.$post_data.' \n
						ERROR MESSAGE: '.$data->body.'
					', JLog::ERROR, 'com_emundus');
					return false;
				}

				$em_db_table_name = 'data_aurion_'.$au_id;
				
				// Parse out the keys from the XML row and use them as the table columns.
				$data_rows = str_replace('.', '_', array_keys((array)$data->row[0]));

				if (in_array($em_db_table_name, $em_db_table_list)) {

					// IF TABLE EXISTS Check if all columns in the table are present and add those that aren't.
					$em_db_table_columns = array_keys($db->getTableColumns($em_db_table_name));
					$columns_to_add = array_diff($data_rows, $em_db_table_columns, ['id', 'date_time', 'published']);

					if (!empty($columns_to_add)) {

						$query = 'ALTER TABLE '.$db->quoteName($em_db_table_name);
						foreach ($columns_to_add as $col) {
							$query .= ' ADD COLUMN '.$db->quoteName($col).' VARCHAR(255),';
						}
						$query = rtrim($query, ',');

						$db->setQuery($query);
						try {
							$db->execute();
							JLog::add('Added new columns ('.implode(',', $columns_to_add).') into table '.$em_db_table_name, JLog::INFO, 'com_emundus');
						} catch (Exception $e) {
							JLog::add('Error: could not create DB columns -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
							return false;
						}
					}

				} else {

					// IF TABLE !EXISTS Create table.
					$query = 'CREATE TABLE IF NOT EXISTS '.$db->quoteName($em_db_table_name).' (
						 id INT AUTO_INCREMENT PRIMARY KEY,
						 date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
						 published TINYINT NOT NULL,
					';
					foreach ($data_rows as $col) {
						$query .= ' '.$db->quoteName($col).' VARCHAR(255),';
					}
					$query = rtrim($query, ',').')';

					$db->setQuery($query);
					try {
						$db->execute();
						JLog::add('Added new table '.$em_db_table_name.' with columns ('.implode(',', $data_rows).')', JLog::INFO, 'com_emundus');
					} catch (Exception $e) {
						JLog::add('Error: could not create DB table -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
						return false;
					}
				}

				// Write the .lock file
				$lock_file = JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'aurion'.DS.'files'.DS.$au_id.'.lock';
				if (!file_exists($lock_file)) {
					file_put_contents($lock_file, '');
				}

				$unchanged_ids = [];
				foreach ($data->row as $row) {
					
					$row = (array)$row;
					$cols = array_keys($row);

					// Build a search filter using all of the data of the current row.
					$where = [];
					
					// This little optimization removes a LOT of redundant querying from the DB.
					if (!empty($unchanged_ids)) {
						$where[] = $db->quoteName('id').' NOT IN ('.implode(',',$unchanged_ids).')';
					}

					foreach ($cols as $col) {
						$where[] = $db->quoteName(str_replace('.', '_', $col)).' LIKE '.$db->quote($row[$col]);
					}
					$cols = str_replace('.', '_', $cols);

					// Find out if a row matched the data present in the XML, if so this row can be ignored from depublishing.
					$query = $db->getQuery(true);
					$query->select($db->quoteName('id'))
						->from($db->quoteName($em_db_table_name))
						->where($where);
					$db->setQuery($query);
					try {
						$unchanged_id = $db->loadResult();
						if (!empty($unchanged_id)) {
							$unchanged_ids[] = $unchanged_id;
							continue;
						}
					} catch (Exception $e) {
						JLog::add('Could not run differential analysis on rows present in XML vs DB -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
						return false;
					}

					// If we are still here, we have not entered into the continue GOTO which means the line in the XML is not in the DB.
					$query->clear()
						->insert($db->quoteName($em_db_table_name))
						->columns($db->quoteName(array_merge($cols,['published'])))
						->values(implode(',',$db->quote($row)).', 1');
					$db->setQuery($query);
					try {
						$db->execute();
						$unchanged_ids[] = $db->insertid();
						$rows_updated++;
						JLog::add('Inserted new rows ('.implode(',', $cols).') values ('.implode(',', $row).') into table '.$em_db_table_name, JLog::NOTICE, 'com_emundus');
					} catch (Exception $e) {
						JLog::add('Could not insert rows present in XML but not in DB -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
						return false;
					}
				}

				$query = $db->getQuery(true);
				if (!empty($unchanged_ids)) {
					// Now unpublish all of the ones that were changed.
					$query->clear()
						->update($db->quoteName($em_db_table_name))
						->set($db->quoteName('published').' = 0')
						->where($db->quoteName('id').' NOT IN ('.implode(',',$unchanged_ids).')');
					$db->setQuery($query);

					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Could not unpublish rows that are present in the DB but not the XML -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
						return false;
					}

					// This is a minor patch, PUBLISH all of the other IDs, it helps rare cases where things get unpublished permanently when they shouldn't be.
					$query->clear()
						->update($db->quoteName($em_db_table_name))
						->set($db->quoteName('published').' = 1')
						->where($db->quoteName('id').' IN ('.implode(',',$unchanged_ids).')');
					$db->setQuery($query);

					try {
						$db->execute();
					} catch (Exception $e) {
						JLog::add('Could not publish rows that are present in both DB and XML -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
						return false;
					}

                    // This request is only to log ID's that were unpublished.
                    $query->clear()
                        ->select($db->quoteName('id'))
                        ->from($db->quoteName($em_db_table_name))
                        ->where($db->quoteName('id').' NOT IN ('.implode(',',$unchanged_ids).')');
                    $db->setQuery($query);
                    try {
                        $changed_ids = $db->loadColumn();
                        JLog::add('Unpublished Ids ('.implode(',',$changed_ids).') in table '.$em_db_table_name, JLog::INFO, 'com_emundus');
                    } catch (Exception $e) {
                        JLog::add('Could not get IDs that were unchanged. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                        return false;
                    }

				}

				// Delete .lock file when done with this Aurion ID.
				unlink($lock_file);

			} else {
				JLog::add('
						HTTP ERROR: Response not 200 OK \n 
						URL: '.$aurion_url.' \n
						POST DATA: '.$post_data.' \n
						RESPONSE CODE: '.$response->code.' \n
						RESPONSE BODY: '.$response->body.'
					', JLog::ERROR, 'com_emundus');
				return false;
			}
		}

		// Now that the sync is done, we can start the mapping process to the eMundus tables.
		// Due to the potentially very custom nature of all this, we are doing it in separate plugins.
		JPluginHelper::importPlugin('emundus');
		$dispatcher = JEventDispatcher::getInstance();
		if ($params->get('sync_setup_programs', false)) {
			$dispatcher->trigger('setupProgrammeSync');
		}
		if ($params->get('sync_setup_years', false)) {
			$dispatcher->trigger('setupTeachingUnitySync');
		}
		if ($params->get('sync_setup_campaigns', false)) {
			$dispatcher->trigger('setupCampaignSync');
		}

		return $rows_updated;
	}
}
