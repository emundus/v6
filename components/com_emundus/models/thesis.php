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
class EmundusModelThesis extends JModelItem
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
			$id = JFactory::getApplication()->getUserState('com_emundus.edit.thesis.id');
		}
		else {
			$id = JFactory::getApplication()->input->get('id');
			JFactory::getApplication()->setUserState('com_emundus.edit.thesis.id', $id);
		}
		$this->setState('thesis.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();
		if (isset($params_array['item_id'])) {
			$this->setState('thesis.id', $params_array['item_id']);
		}
		$this->setState('params', $params);
	}

	/**
	 * Get applied thesis
	 * @return int  id of the thesis proposal aplied
	 */
	public function getApplied()
	{
		$db           = JFactory::getDbo();
		$current_user = JFactory::getSession()->get('emundusUser');
		try {
			$query = "SELECT *
                      FROM #__emundus_thesis_candidat etc
                      LEFT JOIN #__emundus_campaign_candidature ecc ON ecc.fnum = etc.fnum
                      WHERE etc.fnum like \"$current_user->fnum\"
                      AND ecc.campaign_id = $current_user->campaign_id";
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (Exception $e) {
			throw $e;
		}
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

			if (empty($id))
				$id = $this->getState('thesis.id');

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id)) {
				// Check published state.
				if ($published = $this->getState('filter.published')) {
					if ($table->state != $published)
						return $this->_item;
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->_item = JArrayHelper::toObject($properties, 'JObject');
			}
			elseif ($error = $table->getError()) {
				$this->setError($error);
			}
		}


		if (isset($this->_item->user))
			$this->_item->user_name = JFactory::getUser($this->_item->user)->name;

		if (isset($this->_item->doctoral_school) && $this->_item->doctoral_school != '') {
			if (is_object($this->_item->doctoral_school))
				$this->_item->doctoral_school = JArrayHelper::fromObject($this->_item->doctoral_school);
			$values = (is_array($this->_item->doctoral_school)) ? $this->_item->doctoral_school : explode(',', $this->_item->doctoral_school);

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
				if ($results)
					$textValue[] = $results->title;
			}

			$this->_item->doctoral_school = !empty($textValue) ? implode(', ', $textValue) : $this->_item->doctoral_school;

		}

		return $this->_item;
	}

	public function getTable($type = 'Thesis', $prefix = 'EmundusTable', $config = array())
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
		$id = (!empty($id)) ? $id : (int) $this->getState('thesis.id');

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
		$id = (!empty($id)) ? $id : (int) $this->getState('thesis.id');

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
	 * Method to cancel application to a thesis
	 *
	 * @param   integer     The id of the user.
	 * @param   string     The fnum of an application.
	 *
	 * @return  boolean     True on success, false on failure.
	 * @since   1.6
	 */
	public function cancel($user_id, $fnum)
	{
		$user = JFactory::getUser($user_id);
		$db   = JFactory::getDbo();
		/*
				$query = 'SELECT * FROM #__emundus_uploads WHERE user_id='.$user->id.' AND fnum like '.$db->Quote($fnum);
				$db->setQuery($query);
				$attachments = $db->loadObjectList();

				if(count($attachments)>0){
					foreach($attachments as $attachment){
						unlink(EMUNDUS_PATH_ABS.$attachment->user_id.DS.$attachment->filename);
					}
				}
		*/
		try {
			$query = 'DELETE FROM #__emundus_thesis_candidat WHERE fnum like ' . $db->Quote($fnum) . ' AND user=' . $user->id;
			$db->setQuery($query);
			$db->execute();

			$query = 'DELETE FROM #__emundus_declaration WHERE fnum like ' . $db->Quote($fnum) . ' AND user=' . $user->id;
			$db->setQuery($query);
			$db->execute();

			$query = 'DELETE FROM #__emundus_users_assoc WHERE fnum like ' . $db->Quote($fnum);
			$db->setQuery($query);
			$db->execute();

			return true;
		}
		catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Method to apply to a thesis
	 *
	 * @param   integer     The id of the user.
	 * @param   integer     The id of the thesis.
	 *
	 * @return  boolean     True on success, false on failure.
	 * @since   1.6
	 */
	public function apply($user_id, $thesis_id)
	{
		include_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
		$modelProfile = new EmundusModelProfile;
		$user         = $modelProfile->getEmundusUser($user_id);
		$current_user = JFactory::getUser();
		$db           = JFactory::getDbo();

		// 0. Get the thesis infos
		$query = "SELECT * FROM #__emundus_thesis WHERE id=$thesis_id";
		$db->setQuery($query);
		$thesis = $db->loadObject();
		if ($thesis->campaign_id > 0 && $thesis->state == 1 && $thesis->published == 1) {
			// 1. Check if a fnum exist without thesis
			$query = "SELECT ecc.fnum 
                        FROM #__emundus_campaign_candidature as ecc
                        LEFT JOIN #__emundus_thesis_candidat as etc on etc.fnum = ecc.fnum
                        WHERE ecc.applicant_id=" . $user_id . " AND etc.id is null
                        order by ecc.date_time DESC";
			$db->setQuery($query);
			$fnum = $db->loadResult();

			// 2. Create a new fnum for campaign link to the thesis ID
			if (!isset($fnum) || empty($fnum)) {
				$fnum = @EmundusHelperFiles::createFnum($thesis->campaign_id, $user->id);

				try {

					$query = "INSERT INTO #__emundus_campaign_candidature (`date_time` ,`applicant_id` ,`user_id` ,`campaign_id` ,`submitted` ,`date_submitted` ,`cancelled` ,`fnum` ,`status` ,`published`)
                              VALUES(NOW(), $user->id, $current_user->id, $thesis->campaign_id, 0, NULL, 0, '$fnum', 0, 1)";
					$db->setQuery($query);
					$db->execute();
					$insertid = $db->insertid();

				}
				catch (Exception $e) {
					return false;
				}
			}
			// 3. Insert a new line in #__emundus_thesis_candidature to link user/fnum/thesis_ib
			//if($insertid > 0) {
			try {
				// 4. Get infos from previous submission
				$query = "SELECT * FROM #__emundus_thesis_candidat WHERE user=$user->id order by date_time DESC";
				$db->setQuery($query);
				$lastthesis = $db->loadAssoc();

				if (count($lastthesis) > 0) {
					$column = "";
					$values = "";
					foreach ($lastthesis as $key => $value) {
						if ($key != 'id' && $key != 'date_time' && $key != 'doctoral_school' && $key != 'thesis_proposal' && $key != 'fnum') {
							$column .= $key . ',';
							$values .= $db->Quote($value) . ',';
						}
					}
					$column .= 'date_time, doctoral_school, thesis_proposal, fnum, thesis_proposal, supervisor_thesis_proposal, supervisor_email_thesis_proposal';
					$values .= "NOW(), $thesis->doctoral_school, $thesis_id, '$fnum', '$thesis->thesis_supervisor', '$thesis->thesis_supervisor_email'";
					$query  = "INSERT INTO #__emundus_thesis_candidat ($column) VALUES($values)";
				}
				else {
					$query = "INSERT INTO #__emundus_thesis_candidat (`date_time` ,`user` ,`fnum` ,`thesis_proposal`, `supervisor_thesis_proposal`, `supervisor_email_thesis_proposal`)
                          VALUES(NOW(), $user->id, $db->Quote($fnum), $thesis_id, '$thesis->thesis_supervisor', '$thesis->thesis_supervisor_email')";
				}

				$db->setQuery($query);
				$db->execute();
				$insertid = $db->insertid();

			}
			catch (Exception $e) {
				return false;
			}

			if ($insertid > 0) {
				// 3. Set user session session
				$user->fnum        = $fnum;
				$user->campaign_id = $thesis->campaign_id;

				return $fnum;
			}
			// }
		}
		else return false;
	}

	/**
	 * Method to get last thesis selected by applicant.
	 *
	 * @param   string The fnum of user.
	 *
	 * @return  mixed   Object on success, false on failure.
	 */
	public function getLastThesisApplied($fnum)
	{
		$db = JFactory::getDBO();

		$query = 'SELECT etc.*, et.*, eu.*, c.title 
                    FROM #__emundus_thesis_candidat AS etc 
                    LEFT JOIN #__emundus_thesis as et ON et.id=etc.thesis_proposal
                    LEFT JOIN #__emundus_users as eu ON eu.user_id=et.user
                    LEFT JOIN #__categories as c on c.id=et.doctoral_school
                    WHERE etc.fnum like ' . $db->Quote($fnum) . ' ORDER BY etc.id DESC';
		$db->setQuery($query);

		return $db->loadObject();
	}

}
