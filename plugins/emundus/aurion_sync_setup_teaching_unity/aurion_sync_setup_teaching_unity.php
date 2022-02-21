<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusAurion_sync_setup_teaching_unity extends JPlugin {

	var $db;
	var $query;

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		$this->db = JFactory::getDbo();
		$this->query = $this->db->getQuery(true);

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.syncSetupTeachingUnity.php'), JLog::ALL, array('com_emundus_syncSetupTeachingUnity'));
	}

	/**
	 * Sync data from a given Aurion import table an insert it into jos_emundus_setup_teaching_unity
	 * @return bool
	 *
	 * @since version
	 */
	function setupTeachingUnitySync() {

		$au_id_years = $this->params->get('au_ids_years');
		if (!empty($au_id_years)) {

			// Get all of the mapping values defined in the param.
			$year_programme_id = str_replace('.', '_', $this->params->get('year_programme_id'));
			$year_programme_code = str_replace('.', '_', $this->params->get('year_programme_code'));
			$year_schoolyear = str_replace('.', '_', $this->params->get('year_schoolyear'));
			$year_label = str_replace('.', '_', $this->params->get('year_label'));
			$year_label_en = str_replace('.', '_', $this->params->get('year_label_en'));

			if (!empty($year_programme_code) || !empty($year_programme_id)) {

				$year_data_select = [$year_schoolyear, $year_label];

				// The program code is critical to the eMundus data struct.
				if (!empty($year_programme_code)) {
					$year_data_select[] = $year_programme_code;
					$prog_selector = $year_programme_code;
				} else {
					$year_data_select[] = $year_programme_id;
					$prog_selector = $year_programme_id;
				}

				$insert_data = [
					'code' => '',
					'label' => '',
					'schoolyear' => '',
					'published' => '1',
					'programmes' => '',
					'date_start' => 'NOW()',
					'date_end' => 'NOW()',
				];

				if (!empty($year_label_en)) {
					$year_data_select[] = $year_label_en;
					$insert_data['label_en'] = '';
				}

				$au_id_years = explode(',', $au_id_years);
				foreach ($au_id_years as $au_id_year) {

					// SELECT from the $au_year table in question all of the IDs.
					$this->query
						->clear()
						->select($this->db->quoteName($year_data_select))
						->from($this->db->quoteName('data_aurion_'.$au_id_year))
						->where($this->db->quoteName('published').' = 1');
					$this->db->setQuery($this->query);
					try {
						$db_au_year_data = $this->db->loadAssocList();
					} catch (Exception $e) {
						JLog::add('Could not get the year info from the Aurion years table. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupTeachingUnity');
						return false;
					}

					// The DB may not be linked in a way which gives the Years query an ID to the program and not a code, so we get it.
					if (empty($year_programme_code)) {
						$this->query
							->clear()
							->select($this->db->quoteName(['id', 'code']))
							->from($this->db->quoteName('#__emundus_setup_programmes'));
						$this->db->setQuery($this->query);
						try {
							$db_em_prog_code_id = $this->db->loadAssocList('id');
						} catch (Exception $e) {
							JLog::add('Could not get the program Codes from the prog table by ID in order to feed the years table. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupTeachingUnity');
							return false;
						}

						// Here we replace what used to be the programme ID by the associated programme code for each year that we will be inserting/updating.
						foreach ($db_au_year_data as $key => $year_data) {
							$db_au_year_data[$key][$year_programme_id] = $db_em_prog_code_id[$year_data[$year_programme_id]]['code'];
						}
					}

					// Get all of the currently inserted year codes/schoolyears in eMundus.
					// We are running this Query in the foreach loop in case that mutiple Aurion tables are entered and the same year ID is found in two of the tables, so we don't insert two schoolyears.
					$this->query
						->clear()
						->select($this->db->quoteName(['code', 'schoolyear']))
						->from($this->db->quoteName('#__emundus_setup_teaching_unity'));
					$this->db->setQuery($this->query);
					try {
						$db_em_year_primary = $this->db->loadAssocList();
					} catch (Exception $e) {
						JLog::add('Could not get the year schoolyear/code the TU table. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupTeachingUnity');
						return false;
					}

					// Split the IDs based on those that need to be INSERTED or UPDATED and remove duplicates.
					$present_values = [];
					$to_update = [];
					$to_insert = [];
					foreach ($db_au_year_data as $key => $year_data) {

						// Remove duplicates by concatenating the two values that should be together.
						if (empty($year_data[$year_schoolyear]) || empty($year_data[$prog_selector]) || in_array($year_data[$prog_selector].$year_data[$year_schoolyear], $present_values)) {
							unset($db_au_year_data[$key]);
							continue;
						}

						// Get all the values IN years that have the same code/year combo as in the Aurion years table.
						foreach ($db_em_year_primary as $db_em_years) {
							if ($year_data[$prog_selector] == $db_em_years['code'] && $year_data[$year_schoolyear] == $db_em_years['schoolyear']) {
								$to_update[] = $year_data;
							}
						}

						$present_values[] = $year_data[$prog_selector].$year_data[$year_schoolyear];
					}


					// Get all the values NOT in years with the YEAR/CODE combo of values in the Aurion years table.
					if (empty($to_update)) {
						// Small optimization to avoid having to loop again.
						$to_insert = $db_au_year_data;
					} else {
						foreach ($db_au_year_data as $year_data) {
							foreach ($to_update as $update) {
								if ($year_data[$prog_selector] == $update[$prog_selector] && $year_data[$year_schoolyear] == $update[$year_schoolyear]) {
									continue 2;
								}
							}
							$to_insert[] = $year_data;
						}
					}
					// NOTE: We don't manage the case of a year NOT being in Aurion anymore, it remains published.

					// Get the program category in order to insert/update the right one.
					$this->query
						->clear()
						->select($this->db->quoteName(['programmes', 'code']))
						->from($this->db->quoteName('#__emundus_setup_programmes'));
					$this->db->setQuery($this->query);
					try {
						$programme_categories = $this->db->loadAssocList('code', 'programmes');
					} catch (Exception $e) {
						JLog::add('Could not get the programme categories organized by code. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupTeachingUnity');
						return false;
					}

					// If not exists, INSERT the object.
					if (!empty($to_insert)) {

						$insert_values = [];
						foreach ($to_insert as $insert) {

							// build the data insertion.
							$insert_data['code'] = $this->db->quote($insert[$prog_selector]);
							$insert_data['label'] = $this->db->quote($insert[$year_label]);
							$insert_data['schoolyear'] = $this->db->quote($insert[$year_schoolyear]);
							$insert_data['programmes'] = $this->db->quote($programme_categories[$insert[$prog_selector]]);

							if (!empty($year_label_en)) {
								$insert_data['label_en'] = $this->db->quote($insert[$year_label_en]);
							}

							$insert_values[] = implode(',', $insert_data);
						}

						$this->query
							->clear()
							->insert($this->db->quoteName('#__emundus_setup_teaching_unity'))
							->columns($this->db->quoteName(array_keys($insert_data)))
							->values($insert_values);
						$this->db->setQuery($this->query);
						try {
							$this->db->execute();
						} catch (Exception $e) {
							JLog::add('Could not INSERT data into jos_emundus_setup_teaching_unity. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupTeachingUnity');
							return false;
						}
					}

					// If exists, UPDATE the object.
					if (!empty($to_update)) {

						foreach ($to_update as $update) {

							$fields = [
								$this->db->quoteName('label') . ' = '.$this->db->quote($update[$year_label]),
								$this->db->quoteName('programmes').' = '.$this->db->quote($programme_categories[$update[$prog_selector]])
							];

							if (!empty($year_label_en)) {
								$fields[] = $this->db->quoteName('label_en') . ' = '.$this->db->quote($update[$year_label_en]);
							}

							$this->query->clear()
								->update($this->db->quoteName('#__emundus_setup_teaching_unity'))
								->set($fields)
								->where([$this->db->quoteName('code').' LIKE '.$this->db->quote($update[$prog_selector]), $this->db->quoteName('schoolyear').' LIKE '.$this->db->quote($update[$year_schoolyear])]);
							$this->db->setQuery($this->query);
							try {
								$this->db->execute();
							} catch (Exception $e) {
								JLog::add('Could not UPDATE data into jos_emundus_setup_teaching_unity. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupTeachingUnity');
								return false;
							}
						}
					}
				}
			}
			return true;
		}
		return false;
	}
}
