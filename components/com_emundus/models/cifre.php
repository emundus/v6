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
	 * @param null $fnum String The fnum of the offer.
	 * @return Mixed An array of objects.
	 */
	function getOffer($fnum) {
		//$eMConfig = JComponentHelper::getParams('com_emundus');
		//$listID = $eMConfig->get('fabrikListID');

		if (empty($fnum))
			return false;

		$query = $this->db->getQuery(true);
		$query
			->select(['p.*', $this->db->quoteName('r.id', 'search_engine_page')])
			->from($this->db->quoteName('#__emundus_projet','p'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'r').' ON '.$this->db->quoteName('p.fnum').' LIKE '.$this->db->quoteName('r.fnum'))
			->where($this->db->quoteName('p.fnum').' LIKE "' . $fnum . '"');

		$this->db->setQuery($query);
		try {
			return $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting offers by user in m/cifre at query '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	/**
	 * @param $user_id Int The ID of the user who's offers we are getting.
	 * @param null $fnum String If any of the offers are linked to this fnum, do not get them.
	 * @return Mixed An array of objects.
	 */
	function getOffersByUser($user_id, $fnum = null) {
		
		if (empty($fnum))
			return false;
		
		// This is custom code, we need to make this able to work for everyone.
		$query = $this->db->getQuery(true);

		$query
			->select($this->db->quoteName(['cc.fnum','p.titre']))
			->from($this->db->quoteName('#__emundus_campaign_candidature','cc'))
			->join('LEFT', $this->db->quoteName('#__emundus_projet', 'p') . ' ON (' . $this->db->quoteName('p.fnum') . ' = ' . $this->db->quoteName('cc.fnum') . ')')
			->where($this->db->quoteName('cc.user_id') . ' = '.$user_id . ' AND ' . $this->db->quoteName('cc.status') . ' = 1');

			$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			return false;
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

		$columns = ['user_to', 'user_from', 'fnum_to', 'state'];
		$values = [$user_to, $user_from, $this->db->quote($fnum_to), 1];

		if (!empty($fnum_from)) {
			$columns[] = 'fnum_from';
			$values[] = $this->db->quote($fnum_from);
		}

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


	/**
	 * Gets the laboratory information linked to the user passed in the params or the currently logged in user if not.
	 *
	 * @param $user_id Int The user ID of the person to check the lab.
	 * @return Mixed
	 */
	function getUserLaboratory($user_id = null) {

		if (empty($user_id))
			$user_id = JFactory::getUser()->id;

		// First step is to get the user in question and make sure his profile is correct.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('profile').', '.$this->db->quoteName('laboratoire'))->from($this->db->quoteName('#__emundus_users'))->where('user_id = '.$user_id);
		$this->db->setQuery($query);
		try {
			$user = $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting emundus user info in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}

		// Do not continue if the user is not a researcher.
		if ($user->profile != '1007')
			return false;

		// Get the lab details from the DB.
		$query = $this->db->getQuery(true);
		$query->select('*')->from($this->db->quoteName('em_laboratoire'))->where('id = '.$user->laboratoire);
		$this->db->setQuery($query);
		try {
			return $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting lab info in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	/**
	 * Gets the institution information linked to the user passed in the params or the currently logged in user if not.
	 *
	 * @param $user_id Int The user ID of the person to check the lab.
	 * @return Mixed
	 */
	function getUserInstitution($user_id = null) {

		if (empty($user_id))
			$user_id = JFactory::getUser()->id;

		// First step is to get the user in question and make sure his profile is correct.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('profile').', '.$this->db->quoteName('nom_de_structure'))->from($this->db->quoteName('#__emundus_users'))->where('user_id = '.$user_id);
		$this->db->setQuery($query);
		try {
			$user = $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting emundus user info in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}

		// Do not continue if the user is not linked to a municipality.
		if ($user->profile != '1008')
			return false;

		// Get the lab details from the DB.
		$query = $this->db->getQuery(true);
		$query->select('*')->from($this->db->quoteName('em_municipalitees'))->where('id = '.$user->nom_de_structure);
		$this->db->setQuery($query);
		try {
			return $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting lab info in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}
}