<?php

/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

/**
 * Emundus model.
 */
class EmundusModelJob extends JModelItem
{

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_emundus');

		// Load state from the request userState on edit or from the passed variable on default
		if (JFactory::getApplication()->input->get('layout') == 'edit') {
			$id = JFactory::getApplication()->getUserState('com_emundus.edit.job.id');
		}
		else {
			$id = JFactory::getApplication()->input->get('id');
			JFactory::getApplication()->setUserState('com_emundus.edit.job.id', $id);
		}
		$this->setState('job.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();
		if (isset($params_array['item_id'])) {
			$this->setState('job.id', $params_array['item_id']);
		}
		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer The id of the object to get.
	 *
	 * @return  mixed   Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if ($this->_item === null) {
			$this->_item = false;

			if (empty($id)) {
				$id = $this->getState('job.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id)) {
				// Check published state.
				if ($published = $this->getState('filter.published')) {
					if ($table->state != $published) {
						return $this->_item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->_item = JArrayHelper::toObject($properties, 'JObject');
			}
			elseif ($error = $table->getError()) {
				$this->setError($error);
			}
		}


		if (isset($this->_item->user)) {
			$this->_item->user_name = JFactory::getUser($this->_item->user)->name;
		}

		if (isset($this->_item->etablissement) && $this->_item->etablissement != '') {
			if (is_object($this->_item->etablissement)) {
				$this->_item->etablissement = JArrayHelper::fromObject($this->_item->etablissement);
			}
			$values = (is_array($this->_item->etablissement)) ? $this->_item->etablissement : explode(',', $this->_item->etablissement);

			$textValue = array();
			foreach ($values as $value) {
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query
					->select('title')
					->from('`#__categories`')
					->where('id = ' . $db->quote($db->escape($value)));
				$db->setQuery($query);
				$results = $db->loadObject();
				if ($results) {
					$textValue[] = $results->title;
				}
			}

			$this->_item->etablissement = !empty($textValue) ? implode(', ', $textValue) : $this->_item->etablissement;

		}

		return $this->_item;
	}

	public function getTable($type = 'Job', $prefix = 'EmundusTable', $config = array())
	{
		$this->addTablePath(JPATH_BASE . DS . 'components' . DS . 'com_emundus'_ADMINISTRATOR . '/tables');
        return JTable::getInstance($type, $prefix, $config);
    }

	/**
	 * Method to check in an item.
	 *
	 * @param   integer     The id of the row to check out.
	 *
	 * @return  boolean     True on success, false on failure.
	 * @since   1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('job.id');

		if ($id) {

			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin')) {
				if (!$table->checkin($id)) {
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer     The id of the row to check out.
	 *
	 * @return  boolean     True on success, false on failure.
	 * @since   1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('job.id');

		if ($id) {

			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout')) {
				if (!$table->checkout($user->get('id'), $id)) {
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	public function getCategoryName($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('title')
			->from('#__categories')
			->where('id = ' . $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	public function publish($id, $state)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->state = $state;

		return $table->store();
	}

	public function delete($id)
	{
		$table = $this->getTable();

		return $table->delete($id);
	}

	/**
	 * Method to cancel application to a job
	 *
	 * @param   integer  $user_id  The id of the user.
	 * @param   string   $fnum     The fnum of an application.
	 *
	 * @return  boolean     True on success, false on failure.
	 * @since   1.6
	 */
	public function cancel($user_id, $fnum)
	{
		$user = JFactory::getUser($user_id);
		$db   = JFactory::getDbo();

		$query = 'SELECT * FROM #__emundus_uploads WHERE user_id=' . $user->id . ' AND fnum like ' . $db->Quote($fnum);
		$db->setQuery($query);
		$attachments = $db->loadObjectList();

		if (count($attachments) > 0) {
			foreach ($attachments as $attachment) {
				unlink(EMUNDUS_PATH_ABS . $attachment->user_id . DS . $attachment->filename);
			}
		}

		try {
			$query = 'DELETE FROM #__emundus_campaign_candidature WHERE fnum like ' . $db->Quote($fnum) . ' AND applicant_id=' . $user->id;
			$db->setQuery($query);
			$db->execute();

			return true;
		}
		catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Method to apply to a job
	 *
	 * @param   integer     The id of the user.
	 * @param   integer     The id of the job.
	 *
	 * @return  boolean     True on success, false on failure.
	 * @since   1.6
	 */
	public function apply($user_id, $job_id)
	{
		$eMConfig     = JComponentHelper::getParams('com_emundus');
		$program_code = $eMConfig->get('program_code', 'utc-dfp-dri');
		include_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
		$modelProfile = new EmundusModelProfile;
		$user         = $modelProfile->getEmundusUser($user_id);
		$current_user = JFactory::getUser();
		$db           = JFactory::getDbo();

		// 0. Get the job infos
		$query = "SELECT * FROM #__emundus_emploi_etudiant WHERE id=$job_id";
		$db->setQuery($query);
		$job = $db->loadObject();

		if ($job->campaign_id > 0 && $job->state == 1 && $job->published == 1) {
			// 1. Check if a fnum exist without job
			$query = "SELECT ecc.fnum 
                        FROM #__emundus_campaign_candidature as ecc
                        LEFT JOIN #__emundus_emploi_etudiant_candidat as eeec on eeec.fnum = ecc.fnum
                        WHERE ecc.applicant_id=" . $user_id . " AND eeec.id is null AND ecc.campaign_id IN (select id from #__emundus_setup_campaigns where training like '" . $program_code . "')
                        order by ecc.date_time DESC";
			$db->setQuery($query);
			$fnum = $db->loadResult();

			// 2. Create a new fnum for campaign link to the job ID
			if (!isset($fnum) || empty($fnum)) {
				$fnum = @EmundusHelperFiles::createFnum($job->campaign_id, $user->id);

				try {

					$query = "INSERT INTO #__emundus_campaign_candidature (`date_time` ,`applicant_id` ,`user_id` ,`campaign_id` ,`submitted` ,`date_submitted` ,`cancelled` ,`fnum` ,`status` ,`published`)
                              VALUES(NOW(), $user->id, $current_user->id, $job->campaign_id, 0, NULL, 0, '$fnum', 0, 1)";
					$db->setQuery($query);
					$db->execute();
					$insertid = $db->insertid();

				}
				catch (Exception $e) {
					JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

					return false;
				}
			}
			// 3. Insert a new line in #__emundus_emploi_etudiant_candidature to link user/fnum/job_ib
			//if($insertid > 0) {
			try {
				// 4. Get infos from previous submission
				$query = "SELECT * FROM #__emundus_emploi_etudiant_candidat WHERE user=$user->id order by date_time DESC";
				$db->setQuery($query);
				$lastjob = $db->loadAssoc();

				if (count($lastjob) > 0) {
					$column = "";
					$values = "";
					foreach ($lastjob as $key => $value) {
						if ($key != 'id' && $key != 'date_time' && $key != 'etablissement' && $key != 'fiche_emploi' && $key != 'fnum') {
							$column .= $key . ',';
							$values .= $db->Quote($value) . ',';
						}
					}
					$column .= 'date_time, etablissement, fiche_emploi, fnum';
					$values .= "NOW(), $job->etablissement, $job_id, '$fnum'";
					$query  = "INSERT INTO #__emundus_emploi_etudiant_candidat ($column) VALUES($values)";
				}
				else {
					$query = "INSERT INTO #__emundus_emploi_etudiant_candidat (`date_time` ,`user` ,`fnum` ,`etablissement` ,`fiche_emploi`)
                          VALUES(NOW(), $user->id, '$fnum', $job->etablissement, $job_id)";
				}

				$db->setQuery($query);
				$db->execute();
				$insertid = $db->insertid();
			}
			catch (Exception $e) {
				JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');

				return false;
			}
			if ($insertid > 0) {
				// 3. Set user session session
				$user->fnum        = $fnum;
				$user->campaign_id = $job->campaign_id;

				return $fnum;
			}
			// }
		}
		else {
			JLog::add(JUri::getInstance() . ' :: USER ID : ' . JFactory::getUser()->id . ' -> cid=' . $job->campaign_id . ' Job state=' . $job->state . ' published=' . $job->published . ' valide_comite=' . $job->valide_comite, JLog::ERROR, 'com_emundus');

			return false;
		}
	}

}
