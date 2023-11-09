<?php
/**
 * @package       eMundus
 * @version       6.6.5
 * @author        eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license       GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusAurion_sync_setup_programs extends JPlugin
{

	var $db;
	var $query;

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->db    = JFactory::getDbo();
		$this->query = $this->db->getQuery(true);

		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.syncSetupPrograms.php'), JLog::ALL, array('com_emundus_syncSetupPrograms'));
	}

	/**
	 * Sync data from a given Aurion import table an insert it into jos_emundus_setup_programmes
	 * @return bool
	 *
	 * @since version
	 */
	function setupProgrammeSync()
	{

		$au_id_programmes = $this->params->get('au_ids_programme');
		if (!empty($au_id_programmes)) {

			// Get all of the mapping values defined in the param.
			$programme_id                      = str_replace('.', '_', $this->params->get('programme_id'));
			$programme_code                    = str_replace('.', '_', $this->params->get('programme_code'));
			$programme_label                   = str_replace('.', '_', $this->params->get('programme_label'));
			$programme_label_en                = str_replace('.', '_', $this->params->get('programme_label_en'));
			$programme_category                = str_replace('.', '_', $this->params->get('programme_category'));
			$programme_eval_gid                = $this->params->get('programme_eval_gid', 'NULL');
			$programme_decision_gid            = $this->params->get('programme_decision_gid', 'NULL');
			$programme_admission_gid           = $this->params->get('programme_admission_gid', 'NULL');
			$programme_applicant_admission_gid = $this->params->get('programme_applicant_admission_gid', 'NULL');

			$prog_data_select = [$programme_id, $programme_code, $programme_label];
			$insert_data      = [
				'id'                                  => '',
				'code'                                => '',
				'label'                               => '',
				'published'                           => 1,
				'apply_online'                        => 1,
				'programmes'                          => $this->db->quote('aurion'),
				'fabrik_group_id'                     => $programme_eval_gid,
				'fabrik_decision_group_id'            => $programme_decision_gid,
				'fabrik_admission_group_id'           => $programme_admission_gid,
				'fabrik_applicant_admission_group_id' => $programme_applicant_admission_gid
			];

			if (!empty($programme_label_en)) {
				$prog_data_select[]      = $programme_label_en;
				$insert_data['label_en'] = '';
			}

			if (!empty($programme_category)) {
				$prog_data_select[] = $programme_category;
			}

			$au_id_programmes = explode(',', $au_id_programmes);
			foreach ($au_id_programmes as $au_id_programme) {

				// SELECT from the $au_prog table in question all of the IDs.
				$this->query
					->clear()
					->select($this->db->quoteName($prog_data_select))
					->from($this->db->quoteName('data_aurion_' . $au_id_programme))
					->where($this->db->quoteName('published') . ' = 1');
				$this->db->setQuery($this->query);
				try {
					$db_au_prog_data = $this->db->loadAssocList($programme_id);
				}
				catch (Exception $e) {
					JLog::add('Could not get the program IDs from the prog table. -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupPrograms');

					return false;
				}

				// Get all of the currently inserted prog IDs in eMundus.
				// We are running this Query in the foreach loop in case that mutiple Aurion tables are entered and the same prog ID is found in two of the tables, so we don't insert two programmes.
				$this->query
					->clear()
					->select($this->db->quoteName('id'))
					->from($this->db->quoteName('#__emundus_setup_programmes'));
				$this->db->setQuery($this->query);
				try {
					$db_em_prog_ids = $this->db->loadColumn();
				}
				catch (Exception $e) {
					JLog::add('Could not get the program IDs from the prog table. -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupPrograms');

					return false;
				}

				// Split the IDs based on those that need to be INSERTED or UPDATED.
				$db_au_prog_ids = array_keys($db_au_prog_data);
				$to_insert      = array_diff($db_au_prog_ids, $db_em_prog_ids);
				$to_update      = array_intersect($db_em_prog_ids, $db_au_prog_ids);
				// NOTE: We don't manage the case of a program NOT being in Aurion anymore, it remains published.

				// If not exists, INSERT the object.
				if (!empty($to_insert)) {

					$insert_values = [];
					foreach ($to_insert as $insert) {

						$insert = $db_au_prog_data[$insert];

						// build the data insertion.
						$insert_data['id']    = $insert[$programme_id];
						$insert_data['code']  = $this->db->quote($insert[$programme_code]);
						$insert_data['label'] = $this->db->quote($insert[$programme_label]);

						if (!empty($programme_label_en)) {
							$insert_data['label_en'] = $this->db->quote($insert[$programme_label_en]);
						}
						if (!empty($programme_category)) {
							$insert_data['programmes'] = $this->db->quote($insert[$programme_category]);
						}

						$insert_values[] = implode(',', $insert_data);
					}

					$this->query
						->clear()
						->insert($this->db->quoteName('#__emundus_setup_programmes'))
						->columns($this->db->quoteName(array_keys($insert_data)))
						->values($insert_values);
					$this->db->setQuery($this->query);
					try {
						$this->db->execute();
					}
					catch (Exception $e) {
						JLog::add('Could not INSERT data into jos_emundus_setup_programmes. -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupPrograms');

						return false;
					}
				}

				// If exists, UPDATE the object.
				if (!empty($to_update)) {

					foreach ($to_update as $update) {

						$update = $db_au_prog_data[$update];

						$fields = [
							$this->db->quoteName('code') . ' = ' . $this->db->quote($update[$programme_code]),
							$this->db->quoteName('label') . ' = ' . $this->db->quote($update[$programme_label]),
						];

						if (!empty($programme_label_en)) {
							$fields[] = $this->db->quoteName('label_en') . ' = ' . $this->db->quote($update[$programme_label_en]);
						}
						if (!empty($programme_category)) {
							$fields[] = $this->db->quoteName('programmes') . ' = ' . $this->db->quote($update[$programme_category]);
						}

						$this->query
							->clear()
							->update($this->db->quoteName('#__emundus_setup_programmes'))
							->set($fields)
							->where($this->db->quoteName('id') . ' = ' . $update[$programme_id]);
						$this->db->setQuery($this->query);
						try {
							$this->db->execute();
						}
						catch (Exception $e) {
							JLog::add('Could not UPDATE data into jos_emundus_setup_programmes. -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus_syncSetupPrograms');

							return false;
						}
					}
				}
			}

			return true;
		}

		return false;
	}
}
