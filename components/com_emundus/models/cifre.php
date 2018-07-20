<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @copyright  eMundus
 * @author     Hugo Moracchini
 * @since      3.8.8
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelCifre extends JModelList {

	// Initialize class variables.
	var $user = null;
	var $db = null;

	public function __construct(array $config = array()) {

		require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');

		// Load class variables
		$this->user = JFactory::getSession()->get('emundusUser');
		$this->db = JFactory::getDbo();

		parent::__construct($config);
	}

	/**
	 * @param $user_id Int The ID of the user who we are checking if he has contacted or been contacted.
	 * @param $fnum String The fnum of the file to verify.
	 * @return Int
	 */
	function getContactStatus($user_id, $fnum) {

		$query = $this->db->getQuery(true);

		// First we need to see if they are in contact and or are working together.
		$query->select($this->db->quoteName('state'))
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('fnum_to').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('user_from').'='.$user_id);
		$this->db->setQuery($query);

		try {
			$state = $this->db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting cifre links in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
		}

		if (!empty($state))
			return $state;

		// If a link was not found, we need to look the other way, the link could have been formed in the other direction.
		$query = $this->db->getQuery(true);

		$query->select($this->db->quoteName('state'))
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('fnum_from').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('user_to').'='.$user_id);
		$this->db->setQuery($query);

		try {
			$state = $this->db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting cifre links in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}

		// If the state is 1, that means that the OTHER person has contacted the current user.
		// Therefore we return -1 to indicate that a contact request exists but in the other direction.
		if ($state == 1)
			return -1;
		else
			return $state;

	}

	/**
	 * @param $user_id Int The ID of the user who's offers we are getting.
	 * @param null $fnum String If any of the offers are linked to this fnum, do not get them.
	 * @return Mixed An array of objects.
	 */
	function getOffersByUser($user_id, $fnum = null) {

		$query = $this->db->getQuery(true);

		// This tricky function does something a bit complex when an fnum is defined.
		// If the user has ANY link to the fnum in question, don't return the result.
		// This is tricky to do because we have to look at links in both directions.
		$where = $this->db->quoteName('cc.user_id').'='.$user_id.' AND '.$this->db->quoteName('cc.status').'!= 0 ';
		if (!empty($fnum))
			$where .= ' AND NOT (( '.$this->db->quoteName('cl.fnum_to').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('cl.user_from').'='.$user_id.' ) OR ( '.$this->db->quoteName('cl.fnum_from').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('cl.user_to').'='.$user_id.' ))';

		$query->select($this->db->quoteName('fnum'))
			->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'));

		if (!empty($fnum))
			$query->leftJoin($this->db->quoteName('#__emundus_cifre_links', 'cl').' ON ('.$this->db->quoteName('cl.fnum_to').' LIKE '.$this->db->quoteName('cc.fnum').' OR '.$this->db->quoteName('cl.fnum_from').' LIKE '.$this->db->quoteName('cc.fnum').')');

		$query->where($where);

		$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting offers by user in m/cifre at query '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return null;
		}
	}


	/**
	 * Create contact offer.
	 * This creates the link in the database between a user and a cifre offer. Has option of joining one of their offers along as well.
	 *
	 * @param $user_to Int The user who created the offer being contacted.
	 * @param $user_from Int The user who is contacting the other.
	 * @param $fnum_to String The fnum of the offer being contacted.
	 * @param null $fnum_from String The optional fnum of the offer the person contacting may want to put forward.
	 * @return Boolean
	 */
	function createContactRequest($user_to, $user_from, $fnum_to, $fnum_from = null) {

		$query = $this->db->getQuery(true);

		$columns = ['user_to', 'user_from', 'fnum_to', 'fnum_from', 'state'];
		$values = [$user_to, $user_from, $this->db->quote($fnum_to), $this->db->quote($fnum_from), 1];

		$query->insert($this->db->quoteName('#__emundus_cifre_links'))
			->columns($this->db->quoteName($columns))
			->values(implode(',', $values));

		$this->db->setQuery($query);

		try {
			$this->db->execute();
			return true;
		} catch (Exception $e) {
			JLog::add('Error adding cifre link in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	/**
	 * Accept a contact request, this function is unilateral and accepts contact offers both in the inbound and outbound directions.
	 *
	 * @param $user1 Int The user ID of the user that can be both the user_from or the user_to
	 * @param $user2 Int The user ID of the other user, must be the user on the other end of the request.
	 * @param $fnum String The fnum of the file in the request, can be either fnum_to or from.
	 * @return Boolean
	 */
	function acceptContactRequest($user1, $user2, $fnum) {

		$query = $this->db->getQuery(true);

		$fields = [$this->db->quoteName('state').' = 2'];
		$where = '(('.$this->db->quoteName('user_to').'='.$user1.' AND '.$this->db->quoteName('user_from').'='.$user2.' ) OR ('.$this->db->quoteName('user_to').'='.$user2.' AND '.$this->db->quoteName('user_from').'='.$user1.' ) AND ('.$this->db->quoteName('fnum_to').' LIKE '.$this->db->quote($fnum).' OR '.$this->db->quoteName('fnum_from').' LIKE '.$this->db->quote($fnum).'))';

		$query->update($this->db->quoteName('#__emundus_cifre_links'))->set($fields)->where($where);

		$this->db->setQuery($query);

		try {
			$this->db->execute();
			return true;
		} catch (Exception $e) {
			JLog::add('Error updating cifre link in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	/**
	 * Delete a contact request, this function is unilateral and accepts contact offers both in the inbound and outbound directions.
	 *
	 * @param $user1 Int The user ID of the user that can be both the user_from or the user_to
	 * @param $user2 Int The user ID of the other user, must be the user on the other end of the request.
	 * @param $fnum String The fnum of the file in the request, can be either fnum_to or from.
	 * @return Boolean
	 */
	function deleteContactRequest($user1, $user2, $fnum) {

		$query = $this->db->getQuery(true);

		$where = '(('.$this->db->quoteName('user_to').'='.$user1.' AND '.$this->db->quoteName('user_from').'='.$user2.' ) OR ('.$this->db->quoteName('user_to').'='.$user2.' AND '.$this->db->quoteName('user_from').'='.$user1.' ) AND ('.$this->db->quoteName('fnum_to').' LIKE '.$this->db->quote($fnum).' OR '.$this->db->quoteName('fnum_from').' LIKE '.$this->db->quote($fnum).'))';
		$query->delete($this->db->quoteName('#__emundus_cifre_links'))->where($where);

		$this->db->setQuery($query);

		try {
			$this->db->execute();
			return true;
		} catch (Exception $e) {
			JLog::add('Error deleting cifre link in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}
}