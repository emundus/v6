<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2020 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusHesam_tutorial_events extends JPlugin {

	var $db;

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		$this->db = JFactory::getDbo();

		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.hesamTutorialEvents.php'], JLog::ALL, ['com_emundus.hesam']);
	}


	/**
	 * Add a user param when it's the user's first contact request.
	 *
	 * @param         $user_to
	 * @param         $user_from
	 * @param         $fnum_to
	 * @param   null  $fnum_from
	 *
	 * @return bool
	 */
	function onAfterNewContactRequest($user_to, $user_from, $fnum_to, $fnum_from = null) {

		$query = $this->db->getQuery(true);

		if (empty($fnum_from)) {
			// Get all contact requests FROM user where NO FNUM is joined (firstReqNoOffer)
			$query->select('count(id)')
				->from($this->db->quoteName('#__emundus_cifre_links'))
				->where($this->db->quoteName('user_from').' = '.$user_from.' AND '.$this->db->quoteName('fnum_from').' IS NULL');
			$this->db->setQuery($query);

			try {
				if ($this->db->loadResult() === '1') {
					$this->createParam('firstReqNoOffer', $user_from);
				}
			} catch (Exception $e) {
				JLog::add('Error getting contact requests -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.hesam');
				return false;
			}

		} else {

			// Get all contact requests FROM user where an fnum is joined (firstReqSent)
			$query->clear()
				->select('count(id)')
				->from($this->db->quoteName('#__emundus_cifre_links'))
				->where($this->db->quoteName('user_from').' = '.$user_from.' AND '.$this->db->quoteName('fnum_from').' IS NOT NULL');
			$this->db->setQuery($query);

			try {
				if ($this->db->loadResult() === '1') {
					$this->createParam('firstReqSent', $user_from);
				}
			} catch (Exception $e) {
				JLog::add('Error getting contact requests -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.hesam');
				return false;
			}
		}

		// Get all contact requests TO user (firstReqReceived)
		$query->clear()
			->select('count(id)')
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('user_to').' = '.$user_to);
		$this->db->setQuery($query);

		try {
			if ($this->db->loadResult() === '1') {
				return $this->createParam('firstReqReceived', $user_to);
			}
			return true;
		} catch (Exception $e) {
			JLog::add('Error getting contact requests -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.hesam');
			return false;
		}

	}


	/**
	 * Add a user param if it's their first accepted relation.
	 *
	 * @param $user1
	 * @param $user2
	 *
	 * @return bool
	 */
	function onAfterAcceptContactRequest($user1, $user2) {

		$query = $this->db->getQuery(true);

		// Get all sent contact requests concerning user1 (firstReqAccepted)
		$query->select('count(id)')
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('user_from').' = '.$user1.' AND state = 2');
		$this->db->setQuery($query);

		try {
			if ($this->db->loadResult() === '1') {
				$this->createParam('firstReqAccepted', $user1);
			}
		} catch (Exception $e) {
			JLog::add('Error getting contact requests -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.hesam');
			return false;
		}

		// Get all sent contact requests concerning user2 (firstReqAccepted)
		$query->clear()
			->select('count(id)')
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('user_from').' = '.$user2.' AND state = 2');
		$this->db->setQuery($query);

		try {
			if ($this->db->loadResult() === '1') {
				return $this->createParam('firstReqAccepted', $user2);
			}
			return true;
		} catch (Exception $e) {
			JLog::add('Error getting contact requests -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.hesam');
			return false;
		}
	}


	/**
	 * Add a user param when he submits his first file.
	 *
	 * @param $user_id
	 *
	 *
	 * @return bool
	 * @since version
	 */
	function onAfterSubmitFile($user_id) {

		$query = $this->db->getQuery(true);
		$query->select('count(id)')
			->from($this->db->quoteName('#__emundus_campaign_candidature'))
			->where($this->db->quoteName('applicant_id').' = '.$user_id);
		$this->db->setQuery($query);

		try {
			if ($this->db->loadResult() === '1') {
				return $this->createParam('firstFile', $user_id);
			}
			return true;
		} catch (Exception $e) {
			JLog::add('Error getting candidatures -> '.$e->getMessage(), JLog::ERROR, 'com_emundus.hesam');
			return false;
		}
	}


	/**
	 * @param         $param String The param to be saved in the user account.
	 *
	 * @param   null  $user_id
	 *
	 * @return bool
	 * @since version
	 */
	private function createParam($param, $user_id) {

		$user = JFactory::getUser($user_id);

		$table = JTable::getInstance('user', 'JTable');
		$table->load($user->id);

		// Check if the param exists but is false, this avoids accidetally resetting a param.
		$params = $user->getParameters();
		if (!$params->get($param, true)) {
			return true;
		}

		// Store token in User's Parameters
		$user->setParam($param, true);

		// Get the raw User Parameters
		$params = $user->getParameters();

		// Set the user table instance to include the new token.
		$table->params = $params->toString();

		// Save user data
		if (!$table->store()) {
			JLog::add('Error saving params : '.$table->getError(), JLog::ERROR, 'mod_emundus.hesam');
			return false;
		}
		return true;
	}

}
