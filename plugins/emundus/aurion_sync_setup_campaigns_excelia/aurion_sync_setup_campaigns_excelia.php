<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusAurion_sync_setup_campaigns_excelia extends JPlugin {

	var $db;
	var $query;

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		$this->db = JFactory::getDbo();
		$this->query = $this->db->getQuery(true);

		jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.syncSetupCampaigns.php'), JLog::ALL, array('com_emundus_syncSetupCampaigns'));
	}

	/**
	 * Sync data from a given Aurion import table an insert it into jos_emundus_setup_campaigns
	 * @return bool
	 *
	 * @since version
	 */
	function setupCampaignSync() {
		
		$au_ids_camps = $this->params->get('au_ids_camps');
		if (!empty($au_ids_camps)) {

			// Get all of the mapping values defined in the param.
			$camp_year = str_replace('.', '_', $this->params->get('camp_year'));
			$camp_programme_id = str_replace('.', '_', $this->params->get('camp_programme_id'));
			$camp_prog_label = str_replace('.', '_', $this->params->get('camp_prog_label'));
			$camp_label = str_replace('.', '_', $this->params->get('camp_label'));
			$camp_label_en = str_replace('.', '_', $this->params->get('camp_label_en'));
			$camp_aurion_id = str_replace('.', '_', $this->params->get('camp_aurion_id'));

			// There are two campaign end dates per row in the Aurion table, one for International students and the other for French students.
			$camp_end_date_fr = str_replace('.', '_', $this->params->get('camp_end_date_fr'));
			$camp_end_date_int = str_replace('.', '_', $this->params->get('camp_end_date_int'));

			// Three columns have been added to setup_campaigns: aurion_id (= block id), int_fr, label_en
			$insert_data = [
				'user' => 63,
				'training' => '',
				'label' => '',
				'year' => '',
				'published' => 1,
				'profile_id' => '',
				'start_date' => 'NOW()',
				'end_date' => '',
				'int_fr' => '',
				'label_en' => '',
				'aurion_id' => ''
			];

			foreach (explode(',', $au_ids_camps) as $au_ids_camp) {

				// SELECT from the $au_year table in question all of the information.
				$this->query
					->clear()
					->select($this->db->quoteName([$camp_year, $camp_label, $camp_programme_id, $camp_end_date_fr, $camp_end_date_int, $camp_label_en, $camp_aurion_id, $camp_prog_label]))
					->from($this->db->quoteName('data_aurion_'.$au_ids_camp))
					->where($this->db->quoteName('published').' = 1');
				$this->db->setQuery($this->query);
				try {
					$db_au_camp_data = $this->db->loadAssocList($camp_aurion_id);
				} catch (Exception $e) {
					JLog::add('Could not get the campaign info from the Aurion campaign table. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupCampaigns');
					return false;
				}

				// Get the prog IDs in order to match the CODE based on that.
				$this->query
					->clear()
					->select($this->db->quoteName(['id', 'code']))
					->from($this->db->quoteName('#__emundus_setup_programmes'));
				$this->db->setQuery($this->query);
				try {
					$db_em_prog_code_id = $this->db->loadAssocList('id');
				} catch (Exception $e) {
					JLog::add('Could not get the program Codes from the prog table by ID in order to feed the campaign table. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupCampaigns');
					return false;
				}

				// Here we replace what used to be the programme ID by the associated programme code for each campaign that we will be inserting/updating.
				foreach ($db_au_camp_data as $key => $camp_data) {
					$db_au_camp_data[$key][$camp_programme_id] = $db_em_prog_code_id[$camp_data[$camp_programme_id]]['code'];
				}

				// Get all of the currently inserted campaign IDs in eMundus.
				// We are running this Query in the foreach loop in case that mutiple Aurion tables are entered and the same campaign ID is found in two of the tables, so we don't insert two campaigns.
				$this->query
					->clear()
					->select($this->db->quoteName('aurion_id'))
					->from($this->db->quoteName('#__emundus_setup_campaigns'));
				$this->db->setQuery($this->query);
				try {
					$db_em_camp_aurion_ids = array_unique($this->db->loadColumn());
				} catch (Exception $e) {
					JLog::add('Could not get aurion ID from the campaigns table. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupCampaigns');
					return false;
				}

				// Split the IDs based on those that need to be INSERTED or UPDATED.
				// NOTE: We don't manage the case of a campaign NOT being in Aurion anymore, it remains published.
				$db_au_camp_ids = array_keys($db_au_camp_data);
				$to_insert = array_diff($db_au_camp_ids, $db_em_camp_aurion_ids);
				$to_update = array_intersect($db_em_camp_aurion_ids, $db_au_camp_ids);

				// If not exists, INSERT the object.
				if (!empty($to_insert)) {

					$insert_values = [];
					foreach ($to_insert as $insert) {

						$insert = $db_au_camp_data[$insert];

						if (empty($insert) || (empty($insert[$camp_end_date_fr]) && empty($insert[$camp_end_date_int]))) {
							continue;
						}

						// build the data insertion.
						$insert_data['training'] = $this->db->quote($insert[$camp_programme_id]);
						$insert_data['label'] = $this->db->quote($insert[$camp_label]);
						$insert_data['label_en'] = $this->db->quote($insert[$camp_label_en]);
						$insert_data['year'] = $this->db->quote($insert[$camp_year]);
						$insert_data['aurion_id'] = $this->db->quote($insert[$camp_aurion_id]);


						// Each insert can potentially become two lines in the DB as there is one for Fr and one for INT, if the dat is present.
						if (!empty($insert[$camp_end_date_fr]) && new DateTime($insert[$camp_end_date_fr]) > new DateTime()) {

							// Profile is different based on if it's a bachelors or not.
							// We can find this out by looking at the programme label.
							if (strpos(strtolower($insert[$camp_prog_label]), 'msc') !== false) {
								$insert_data['profile_id'] = 1000;
							} else {
								$insert_data['profile_id'] = 1001;
							}

							$insert_data['end_date'] = $this->db->quote(date("Y-m-d H:i:s", strtotime($insert[$camp_end_date_fr])));
							$insert_data['int_fr'] = $this->db->quote('fr');
							$insert_values[] = implode(',', $insert_data);
						}

						if (!empty($insert[$camp_end_date_int]) && new DateTime($insert[$camp_end_date_int]) > new DateTime()) {
							$insert_data['end_date'] = $this->db->quote(date("Y-m-d H:i:s", strtotime($insert[$camp_end_date_int])));
							$insert_data['int_fr'] = $this->db->quote('int');
							$insert_data['profile_id'] = 1002;
							$insert_values[] = implode(',', $insert_data);
						}
					}

					if (!empty($insert_values)) {
						$this->query
							->clear()
							->insert($this->db->quoteName('#__emundus_setup_campaigns'))
							->columns($this->db->quoteName(array_keys($insert_data)))
							->values($insert_values);
						$this->db->setQuery($this->query);
						try {
							$this->db->execute();
						} catch (Exception $e) {
							JLog::add('Could not INSERT data into jos_emundus_setup_teaching_unity. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupCampaigns');
							return false;
						}
					}
				}

				// If exists, UPDATE the object.
				if (!empty($to_update)) {

					foreach ($to_update as $update) {

						$update = $db_au_camp_data[$update];
						$where = null;

						$fields = [
							$this->db->quoteName('label') . ' = '.$this->db->quote($update[$camp_label]),
							$this->db->quoteName('label_en') . ' = '.$this->db->quote($update[$camp_label_en]),
							$this->db->quoteName('training') . ' = '.$this->db->quote($update[$camp_programme_id]),
							$this->db->quoteName('year') . ' = '.$this->db->quote($update[$camp_year])
						];

						// There is potentially two updates to do, one for FR and one for INT.
						if (!empty($update[$camp_end_date_fr])) {
							$fields[] = $this->db->quoteName('end_date') . ' = '.$this->db->quote(date("Y-m-d H:i:s", strtotime($update[$camp_end_date_fr])));
							$where = [$this->db->quoteName('aurion_id').' LIKE '.$this->db->quote($update[$camp_aurion_id]), $this->db->quoteName('int_fr').' LIKE '.$this->db->quote('fr')];

							$this->query->clear()
								->update($this->db->quoteName('#__emundus_setup_campaigns'))
								->set($fields)
								->where($where);
							$this->db->setQuery($this->query);
							try {
								$this->db->execute();
							} catch (Exception $e) {
								JLog::add('Could not UPDATE data into jos_emundus_setup_campaign. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupCampaigns');
								return false;
							}
						}

						if (!empty($update[$camp_end_date_int])) {
							$fields[] = $this->db->quoteName('end_date') . ' = '.$this->db->quote(date("Y-m-d H:i:s", strtotime($update[$camp_end_date_int])));
							$where = [$this->db->quoteName('aurion_id').' LIKE '.$this->db->quote($update[$camp_aurion_id]), $this->db->quoteName('int_fr').' LIKE '.$this->db->quote('int')];

							$this->query
								->clear()
								->update($this->db->quoteName('#__emundus_setup_campaigns'))
								->set($fields)
								->where($where);
							$this->db->setQuery($this->query);
							try {
								$this->db->execute();
							} catch (Exception $e) {
								JLog::add('Could not UPDATE data into jos_emundus_setup_campaign. -> '.$e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupCampaigns');
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
