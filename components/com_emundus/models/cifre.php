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
	var $db = null;

	public function __construct(array $config = array()) {

		require_once(JPATH_ROOT.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');

		// Load class variables
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
			JLog::add('Error getting cifre links in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		}

		if (!empty($state)) {
			return $state;
		}

		// If a link was not found, we need to look the other way, the link could have been formed in the other direction.
		$query->clear()
			->select($this->db->quoteName('state'))
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('fnum_from').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('user_to').'='.$user_id);
		$this->db->setQuery($query);

		try {
			$state = $this->db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting cifre links in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}

		// If the state is 1, that means that the OTHER person has contacted the current user.
		// Therefore we return -1 to indicate that a contact request exists but in the other direction.
		if ($state == 1) {
			return -1;
		} else {
			return $state;
		}

	}


	/**
	 * @param null $fnum String The fnum of the offer.
	 * @return Mixed An array of objects.
	 */
	function getOffer($fnum) {

		if (empty($fnum)) {
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select(['p.*', $this->db->quoteName('r.id', 'search_engine_page')])
			->from($this->db->quoteName('#__emundus_projet','p'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'r').' ON '.$this->db->quoteName('p.fnum').' LIKE '.$this->db->quoteName('r.fnum'))
			->where($this->db->quoteName('p.fnum').' LIKE "' . $fnum . '"');

		$this->db->setQuery($query);
		try {
			return $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting offers by user in m/cifre at query '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/** Gets the list of all offers made by a user, can also provide an fnum and avoid that offer.
	 *
	 * @param $user_id Int The ID of the user who's offers we are getting.
	 * @param null $fnum String If any of the offers are linked to this fnum, do not get them.
	 * @return Mixed An array of objects, indexed by fnum in order to avoid duplicates.
	 */
	function getOffersByUser($user_id, $fnum = null) {

		// This is custom code, we need to make this able to work for everyone.
		$query = $this->db->getQuery(true);

		$query->select(array($this->db->quoteName('esp.id', 'profile_id'), $this->db->quoteName('esp.label', 'profile'), $this->db->quoteName('cc.fnum'), $this->db->quoteName('p.titre')))
			->from($this->db->quoteName('#__emundus_campaign_candidature','cc'))
			->leftJoin($this->db->quoteName('#__emundus_projet', 'p') . ' ON (' . $this->db->quoteName('p.fnum') . ' = ' . $this->db->quoteName('cc.fnum') . ')')
			->leftJoin($this->db->quoteName('#__emundus_users', 'eu') . ' ON (' . $this->db->quoteName('eu.user_id') . ' = ' . $this->db->quoteName('cc.applicant_id') . ')')
			->leftJoin($this->db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON (' . $this->db->quoteName('esp.id') . ' = ' . $this->db->quoteName('eu.profile') . ')');

		if (!empty($fnum)) {
			$query->leftJoin($this->db->quoteName('#__emundus_cifre_links', 'cl').' ON ('.$this->db->quoteName('cl.fnum_to').' LIKE '.$this->db->quoteName('cc.fnum').' OR '.$this->db->quoteName('cl.fnum_from').' LIKE '.$this->db->quoteName('cc.fnum').')');
		}

		$where = $this->db->quoteName('cc.user_id') . ' = '.$user_id . ' AND ' . $this->db->quoteName('cc.status') . ' IN (1,3)';
		if (!empty($fnum)) {
			$where .= ' AND '.$this->db->quoteName('cl.id').' NOT IN (SELECT '.$this->db->quoteName('c.id').' FROM '.$this->db->quoteName('jos_emundus_cifre_links', 'c').' WHERE ( '.$this->db->quoteName('cl.fnum_to').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('cl.user_from').'='.$user_id.' ) OR ( '.$this->db->quoteName('cl.fnum_from').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('cl.user_to').'='.$user_id.' ))';
		}
		$query->where($where);

		$this->db->setQuery($query);
		try {
			return $this->db->loadObjectList('fnum');
		} catch (Exception $e) {
			return false;
		}
	}


	/** Gets list of offers based on their connections to the fnums.
	 *
	 * @param   array  $fnums
	 *
	 * @return array|bool|mixed
	 *
	 * @since version
	 */
	public function getContactsByFnums($fnums = array()) {

		$query = $this->db->getQuery(true);

		// First we need to see if they are in contact and or are working together.
		// If the state is 1, that means that the OTHER person has contacted the current user.
		$query->select([$this->db->quoteName('cl.state'), $this->db->quoteName('cl.fnum_from', 'linked_fnum'), $this->db->quoteName('cl.fnum_to','fnum'), '1 AS direction', $this->db->quoteName('cl.user_to_favorite','favorite'), $this->db->quoteName('cl.user_to_notify','notify'), $this->db->quoteName('eu.profile', 'profile_id'), $this->db->quoteName('r.id', 'search_engine_page'), $this->db->quoteName('cl.user_from', 'applicant_id'), $this->db->quoteName('p.titre'), $this->db->quoteName('cc.status'), $this->db->quoteName('cl.id', 'link_id')])
			->from($this->db->quoteName('#__emundus_cifre_links', 'cl'))
			->leftJoin($this->db->quoteName('#__emundus_users', 'eu').' ON '.$this->db->quoteName('cl.user_from').' = '.$this->db->quoteName('eu.user_id'))
			->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$this->db->quoteName('cl.fnum_from').' = '.$this->db->quoteName('cc.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$this->db->quoteName('cc.campaign_id').' = '.$this->db->quoteName('c.id'))
			->leftJoin($this->db->quoteName('#__emundus_projet', 'p').' ON '.$this->db->quoteName('p.fnum').' = '.$this->db->quoteName('cc.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'r').' ON '.$this->db->quoteName('cc.fnum').' = '.$this->db->quoteName('r.fnum'))
			->where($this->db->quoteName('cl.fnum_to').' IN ('.implode(',',$fnums).')');
		$this->db->setQuery($query);

		try {
			$contact = $this->db->loadAssocList();
		} catch (Exception $e) {
			JLog::add('Error getting cifre links in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			$contact = [];
		}

		// If a link was not found, we need to look the other way, the link could have been formed in the other direction.
		// We return -1 to indicate that a contact request exists but in the other direction.
		$query->clear()
			->select([$this->db->quoteName('cl.state'), $this->db->quoteName('cl.fnum_to', 'linked_fnum'), $this->db->quoteName('cl.fnum_from','fnum'), '-1 AS direction', $this->db->quoteName('cl.user_from_favorite','favorite'), $this->db->quoteName('cl.user_from_notify','notify'), $this->db->quoteName('c.profile_id'), $this->db->quoteName('r.id', 'search_engine_page'), $this->db->quoteName('cc.applicant_id'), $this->db->quoteName('p.titre'), $this->db->quoteName('cc.status'), $this->db->quoteName('cl.id', 'link_id')])
			->from($this->db->quoteName('#__emundus_cifre_links', 'cl'))
			->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$this->db->quoteName('cl.fnum_to').' = '.$this->db->quoteName('cc.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$this->db->quoteName('cc.campaign_id').' = '.$this->db->quoteName('c.id'))
			->leftJoin($this->db->quoteName('#__emundus_projet', 'p').' ON '.$this->db->quoteName('p.fnum').' = '.$this->db->quoteName('cc.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'r').' ON '.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('r.fnum'))
			->where($this->db->quoteName('cl.fnum_from').' IN ('.implode(',',$fnums).')');
		$this->db->setQuery($query);

		try {
			$contact = array_merge($contact, $this->db->loadAssocList());
		} catch (Exception $e) {
			JLog::add('Error getting cifre links in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}

		$return = [];
		foreach ($contact as $ct) {
			$return[$ct['fnum']][] = $ct;
		}

		return $return;
	}


	/** Gets list of offers based on their connections to the fnums.
	 *
	 * @param $user
	 *
	 * @return array|bool|mixed
	 *
	 * @since version
	 */
	public function getChatRequestsByUser($user) {

		if (empty($user) || !is_numeric($user)) {
			return false;
		}

		$query = $this->db->getQuery(true);

		// If a link was not found, we need to look the other way, the link could have been formed in the other direction.
		// We return -1 to indicate that a contact request exists but in the other direction.
		$query->select([$this->db->quoteName('cl.state'), $this->db->quoteName('cl.fnum_to', 'linked_fnum'), $this->db->quoteName('cl.user_from_notify','notify'), $this->db->quoteName('c.profile_id'), $this->db->quoteName('r.id', 'search_engine_page'), $this->db->quoteName('cc.applicant_id'), $this->db->quoteName('p.titre'), $this->db->quoteName('cc.status'), $this->db->quoteName('cl.id', 'link_id')])
			->from($this->db->quoteName('#__emundus_cifre_links', 'cl'))
			->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$this->db->quoteName('cl.fnum_to').' = '.$this->db->quoteName('cc.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_setup_campaigns', 'c').' ON '.$this->db->quoteName('cc.campaign_id').' = '.$this->db->quoteName('c.id'))
			->leftJoin($this->db->quoteName('#__emundus_projet', 'p').' ON '.$this->db->quoteName('p.fnum').' = '.$this->db->quoteName('cc.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'r').' ON '.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('r.fnum'))
			->where($this->db->quoteName('user_from').' = '.$user.' AND '.$this->db->quoteName('fnum_from').' IS NULL');
		$this->db->setQuery($query);

		try {
			return $this->db->loadAssocList();
		} catch (Exception $e) {
			JLog::add('Error getting cifre links in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * This function gets all of the contact requests that are destined to the user.
	 * @param null $user Int The user who has received the contact requests.
	 * @return Mixed An array of objects.
	 */
	function getContactToUser($user) {

		if (empty($user)) {
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select([$this->db->quoteName('esp.id', 'profile_id'), $this->db->quoteName('esp.label', 'profile'), $this->db->quoteName('cl.id','link_id'), 'cl.*', 'p.*', $this->db->quoteName('r.id', 'search_engine_page')])
			->from($this->db->quoteName('#__emundus_cifre_links', 'cl'))
			->leftJoin($this->db->quoteName('#__emundus_projet', 'p').' ON '.$this->db->quoteName('p.fnum').' LIKE '.$this->db->quoteName('cl.fnum_to'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'r').' ON '.$this->db->quoteName('cl.fnum_to').' LIKE '.$this->db->quoteName('r.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('r.fnum'))
			->join('LEFT', $this->db->quoteName('#__emundus_users', 'eu') . ' ON (' . $this->db->quoteName('eu.user_id') . ' = ' . $this->db->quoteName('cc.applicant_id') . ')')
			->join('LEFT', $this->db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON (' . $this->db->quoteName('esp.id') . ' = ' . $this->db->quoteName('eu.profile') . ')')
			->where($this->db->quoteName('cl.user_to').' = ' . $user);

		$this->db->setQuery($query);
		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting offers to user in m/cifre at query '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * This function gets all of the contact requests that are sent by the user.
	 * @param null $user Int The user who has sent the contact requests.
	 * @return Mixed An array of objects.
	 */
	function getContactFromUser($user) {

		if (empty($user)) {
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select([$this->db->quoteName('esp.id', 'profile_id'), $this->db->quoteName('esp.label', 'profile'), $this->db->quoteName('cl.id','link_id'), 'cl.*', 'p.*', $this->db->quoteName('r.id', 'search_engine_page')])
			->from($this->db->quoteName('#__emundus_cifre_links', 'cl'))
			->leftJoin($this->db->quoteName('#__emundus_projet', 'p').' ON '.$this->db->quoteName('p.fnum').' LIKE '.$this->db->quoteName('cl.fnum_to'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'r').' ON '.$this->db->quoteName('cl.fnum_to').' LIKE '.$this->db->quoteName('r.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('r.fnum'))
			->join('LEFT', $this->db->quoteName('#__emundus_users', 'eu') . ' ON (' . $this->db->quoteName('eu.user_id') . ' = ' . $this->db->quoteName('cc.applicant_id') . ')')
			->join('LEFT', $this->db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON (' . $this->db->quoteName('esp.id') . ' = ' . $this->db->quoteName('eu.profile') . ')')
			->where($this->db->quoteName('cl.user_from').' = ' . $user);

		$this->db->setQuery($query);
		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting offers to user in m/cifre at query '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * @param $user1
	 * @param $user2
	 *
	 * @return array|bool|mixed
	 *
	 * @since version
	 */
	function getOffersBetweenUsers($user1, $user2) {

		if (empty($user1) || empty($user2)) {
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select(['p.*', $this->db->quoteName('r.id', 'search_engine_page')])
			->from($this->db->quoteName('#__emundus_cifre_links', 'cl'))
			->leftJoin($this->db->quoteName('#__emundus_projet', 'p').' ON '.$this->db->quoteName('p.fnum').' LIKE '.$this->db->quoteName('cl.fnum_to'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'r').' ON '.$this->db->quoteName('cl.fnum_to').' LIKE '.$this->db->quoteName('r.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('r.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_users', 'eu') . ' ON (' . $this->db->quoteName('eu.user_id') . ' = ' . $this->db->quoteName('cc.applicant_id') . ')')
			->where('('.$this->db->quoteName('cl.user_from').' = '.$user1.' AND '.$this->db->quoteName('cl.user_to').' = '.$user2.') OR ('.$this->db->quoteName('cl.user_from').' = '.$user2.' AND '.$this->db->quoteName('cl.user_to').' = '.$user1.')');

		$this->db->setQuery($query);
		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting offers between two users in m/cifre at query '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Create contact offer.
	 * This creates the link in the database between a user and a cifre offer. Has option of joining one of their offers along as well.
	 *
	 * @param      $user_to   Int The user who created the offer being contacted.
	 * @param      $user_from Int The user who is contacting the other.
	 * @param      $fnum_to   String The fnum of the offer being contacted.
	 * @param null $fnum_from String The optional fnum of the offer the person contacting may want to put forward.
	 * @param null $message
	 * @param null $motivation
	 * @param null $cv
	 * @param null $doc
	 *
	 * @return Boolean
	 */
	function createContactRequest($user_to, $user_from, $fnum_to, $fnum_from = null, $message = null, $motivation  = null, $cv  = null, $doc  = null) {

		JPluginHelper::importPlugin('emundus');


		$query = $this->db->getQuery(true);

		$columns = ['user_to', 'user_from', 'fnum_to','time_date_created', 'state', 'message', 'motivation', 'cv', 'document'];
		$values = [$user_to, $user_from, $this->db->quote($fnum_to), 'NOW()', 1, $this->db->quote($message), $this->db->quote($motivation), $this->db->quote($cv), $this->db->quote($doc)];

		if (!empty($fnum_from)) {
			$columns[] = 'fnum_from';
			$values[] = $this->db->quote($fnum_from);
		}

		$query->insert($this->db->quoteName('#__emundus_cifre_links'))
			->columns($this->db->quoteName($columns))
			->values(implode(',', $values));

		$this->db->setQuery($query);
		try {

			JFactory::getApplication()->triggerEvent('onBeforeNewContactRequest', [$user_to, $user_from, $fnum_to, $fnum_from]);
            JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeNewContactRequest', ['user_to' => $user_to, 'user_from' => $user_from, 'fnum_to' => $fnum_to, 'fnum_from' => $fnum_from]]);

            $this->db->execute();

			JFactory::getApplication()->triggerEvent('onAfterNewContactRequest', [$user_to, $user_from, $fnum_to, $fnum_from]);
            JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterNewContactRequest', ['user_to' => $user_to, 'user_from' => $user_from, 'fnum_to' => $fnum_to, 'fnum_from' => $fnum_from]]);

            return true;
		} catch (Exception $e) {
			JLog::add('Error adding cifre link in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * @param $user_to
	 * @param $user_from
	 * @param $fnum_to
	 *
	 * @return bool|mixed|null
	 *
	 * @since version
	 */
	function getContactRequestID($user_to, $user_from, $fnum_to) {

		$query = $this->db->getQuery(true);

		$query->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('user_to').' = '.$user_to.' AND '.$this->db->quoteName('user_from').' = '.$user_from.' AND '.$this->db->quoteName('fnum_to').' LIKE '.$this->db->quote($fnum_to));
		$this->db->setQuery($query);

		try {
			return $this->db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting cifre link in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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

		JPluginHelper::importPlugin('emundus');


		$query = $this->db->getQuery(true);

		$query->update($this->db->quoteName('#__emundus_cifre_links'))
			->set([$this->db->quoteName('state').' = 2'])
			->where('(('.$this->db->quoteName('user_to').'='.$user1.' AND '.$this->db->quoteName('user_from').'='.$user2.' ) OR ('.$this->db->quoteName('user_to').'='.$user2.' AND '.$this->db->quoteName('user_from').'='.$user1.' ) AND ('.$this->db->quoteName('fnum_to').' LIKE '.$this->db->quote($fnum).' OR '.$this->db->quoteName('fnum_from').' LIKE '.$this->db->quote($fnum).'))');

		$this->db->setQuery($query);
		try {
			JFactory::getApplication()->triggerEvent('onBeforeAcceptContactRequest', [$user1, $user2, $fnum]);
            JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeAcceptContactRequest', ['user1' => $user1, 'user2' => $user2, 'fnum' => $fnum]]);

            $this->db->execute();

			JFactory::getApplication()->triggerEvent('onAfterAcceptContactRequest', [$user1, $user2, $fnum]);
            JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterAcceptContactRequest', ['user1' => $user1, 'user2' => $user2, 'fnum' => $fnum]]);

            return true;
		} catch (Exception $e) {
			JLog::add('Error updating cifre link in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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

		JPluginHelper::importPlugin('emundus');


		$query = $this->db->getQuery(true);

		$query->delete($this->db->quoteName('#__emundus_cifre_links'))
			->where('(('.$this->db->quoteName('user_to').'='.$user1.' AND '.$this->db->quoteName('user_from').'='.$user2.' ) OR ('.$this->db->quoteName('user_to').'='.$user2.' AND '.$this->db->quoteName('user_from').'='.$user1.' ) AND ('.$this->db->quoteName('fnum_to').' LIKE '.$this->db->quote($fnum).' OR '.$this->db->quoteName('fnum_from').' LIKE '.$this->db->quote($fnum).'))');

		$this->db->setQuery($query);

		try {
			JFactory::getApplication()->triggerEvent('onBeforeDeleteContactRequest', [$user1, $user2, $fnum]);
            JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onBeforeDeleteContactRequest', ['user1' => $user1, 'user2' => $user2, 'fnum' => $fnum]]);

            $this->db->execute();
			JFactory::getApplication()->triggerEvent('onAfterDeleteContactRequest', [$user1, $user2, $fnum]);
            JFactory::getApplication()->triggerEvent('onCallEventHandler', ['onAfterDeleteContactRequest', ['user1' => $user1, 'user2' => $user2, 'fnum' => $fnum]]);

            return true;
		} catch (Exception $e) {
			JLog::add('Error deleting cifre link in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
		}

		// First step is to get the user in question and make sure his profile is correct.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('profile').', '.$this->db->quoteName('laboratoire'))
			->from($this->db->quoteName('#__emundus_users'))
			->where('user_id = '.$user_id);
		$this->db->setQuery($query);
		try {
			$user = $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting emundus user info in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}

		// Do not continue if the user is not a researcher.
		if ($user->profile != '1007') {
			return false;
		}

		// Get the lab details from the DB.
		$query->clear()
			->select('*')
			->from($this->db->quoteName('em_laboratoire'))
			->where('id = '.$user->laboratoire);
		$this->db->setQuery($query);
		try {
			return $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting lab info in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


    /**
	 * Gets the Masters information linked to the user passed in the params or the currently logged in user if not.
	 *
	 * @param $user_id Int The user ID of the person to check the MsC.
	 * @return Mixed
	 */
	function getUserMasters($user_id = null) {

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
		}

		// First step is to get the user in question and make sure his profile is correct.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('profile').', '.$this->db->quoteName('master_2_intitule').', '.$this->db->quoteName('master_2_etablissement').', '.$this->db->quoteName('master_2_annee'))
			->from($this->db->quoteName('#__emundus_users'))
			->where('user_id = '.$user_id);
		$this->db->setQuery($query);

		try {
			$master = $this->db->loadObject();
			// Do not continue if the user is not a PhD.
			if ($master->profile != '1006') {
				return false;
			} else {
				return $master;
			}

		} catch (Exception $e) {
			JLog::add('Error getting emundus user info in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Gets the title of the user's doctoral school.
	 *
	 * @param   null  $user_id
	 *
	 * @return bool
	 */
    function getDoctorale($user_id = null) {

        if (empty($user_id)) {
            $user_id = JFactory::getUser()->id;
        }

        $query = $this->db->getQuery(true);
        $query->select($this->db->quoteName('titre_ecole_doctorale'))
	        ->from($this->db->quoteName('#__emundus_users'))
	        ->where('user_id = '.$user_id);
        $this->db->setQuery($query);

        try {
            return $this->db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting emundus user info in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
		}

		// First step is to get the user in question and make sure his profile is correct.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('profile').', '.$this->db->quoteName('nom_de_structure'))
			->from($this->db->quoteName('#__emundus_users'))
			->where('user_id = '.$user_id);
		$this->db->setQuery($query);
		try {
			$user = $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting emundus user info in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}

		// Do not continue if the user is not linked to a municipality.
		if ($user->profile != '1008') {
			return false;
		}

		// Get the lab details from the DB.
		$query->clear()
			->select('*')
			->from($this->db->quoteName('em_municipalitees'))
			->where('id = '.$user->nom_de_structure);
		$this->db->setQuery($query);
		try {
			return $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting lab info in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Gets the CIFRE link by ID.
	 *
	 * @param Int $id the ID of the link to get.
	 * @return Mixed
	 */
	public function getLinkByID($id) {

		$query = $this->db->getQuery(true);
		$query->select('*')
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('id').'='.$id);
		$this->db->setQuery($query);

		try {
			return $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting CIFRE link by ID in m/CIFRE at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}

	}


	/**
	 * Gets the CIFRE link by ID.
	 *
	 * @param Int $id the ID of the link to get.
	 * @param Int $state The state to set the link to.
	 * @return Boolean
	 */
	public function setLinkState($id, $state = 0) {

		$query = $this->db->getQuery(true);

		$query->update($this->db->quoteName('#__emundus_cifre_links'))
			->set([$this->db->quoteName('state').' = '.$state])
			->where([$this->db->quoteName('id').'='.$id]);
		$this->db->setQuery($query);

		try {
			$this->db->execute();
			return true;
		} catch (Exception $e) {
			JLog::add('Error updating CIFRE link state in m/CIFRE at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * @param   String      $fnum       The fnum to check favorites for.
	 * @param   String|Int  $profile    The profile to check has/is favorited.
	 * @param   Int         $user
	 *
	 * @return mixed|null
	 * @since version
	 */
	public function checkForFavorites($fnum, $profile, $user) {

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('c.id'))
			->from($this->db->quoteName('#__emundus_cifre_links', 'c'))
			->leftJoin($this->db->quoteName('#__emundus_users', 'eu').' ON (('.$this->db->quoteName('eu.user_id').' = '.$this->db->quoteName('c.user_from').' AND '.$this->db->quoteName('c.user_to').' = '.$user.') OR ('.$this->db->quoteName('eu.user_id').' = '.$this->db->quoteName('c.user_to').'AND '.$this->db->quoteName('c.user_to').' = '.$user.'))')
			->where($this->db->quoteName('eu.profile').' = '.$profile.' AND (('.$this->db->quoteName('c.fnum_from').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('c.user_from_favorite').' = 1) OR ('.$this->db->quoteName('c.fnum_to').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('c.user_to_favorite').' = 1))');
		$this->db->setQuery($query);

		try {
			return $this->db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting checking for favorites : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return null;
		}
	}


	/**
	 * @param   String      $fnum       The fnum to check favorites for.
	 * @param   Int         $user
	 *
	 * @return mixed|null
	 * @since version
	 */
	public function checkForTwoFavorites($fnum, $user) {

		$query = $this->db->getQuery(true);
		$query->select(['DISTINCT c.id', 'c.user_to', 'c.user_from'])
			->from($this->db->quoteName('#__emundus_cifre_links', 'c'))
			->leftJoin($this->db->quoteName('#__emundus_users', 'eu').' ON ('.$this->db->quoteName('eu.user_id').' = '.$this->db->quoteName('c.user_from').' OR '.$this->db->quoteName('eu.user_id').' = '.$this->db->quoteName('c.user_to').')')
			->where('('.$this->db->quoteName('c.user_from').' = '.$user.' AND '.$this->db->quoteName('c.fnum_from').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('c.user_from_favorite').' = 1) OR ('.$this->db->quoteName('c.user_to').' = '.$user.' AND '.$this->db->quoteName('c.fnum_to').' LIKE '.$this->db->quote($fnum).' AND '.$this->db->quoteName('c.user_to_favorite').' = 1)');
		$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting checking for favorites : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return null;
		}
	}


	/**
	 * @param   Int  $link_id
	 * @param   Int  $direction  The direction to look in for the favorite (1 is user_to_favorite, -1 is user_from_favorite).
	 *
	 *
	 * @return bool
	 * @since version
	 */
	public function unfavorite($link_id, $direction = 1) {

		if (!is_numeric($link_id)) {
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__emundus_cifre_links'))
			->set($this->db->quoteName(($direction === 1)?'user_to_favorite':'user_from_favorite').' = 0')
			->where($this->db->quoteName('id').' = '.$link_id);
		$this->db->setQuery($query);

		try {
			return $this->db->execute();
		} catch (Exception $e) {
			JLog::add('Error getting removing favorite : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * @param   Int  $link_id
	 * @param   Int  $direction  The direction to look in for the favorite (1 is user_to_favorite, -1 is user_from_favorite).
	 *
	 *
	 * @return bool
	 * @since version
	 */
	public function favorite($link_id, $direction = 1) {

		if (!is_numeric($link_id)) {
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__emundus_cifre_links'))
			->set($this->db->quoteName(($direction === 1)?'user_to_favorite':'user_from_favorite').' = 1')
			->where($this->db->quoteName('id').' = '.$link_id);
		$this->db->setQuery($query);

		try {
			return $this->db->execute();
		} catch (Exception $e) {
			JLog::add('Error getting adding favorite : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * @param   Int  $link_id
	 * @param   Int  $direction  The direction to look in for the notify (1 is user_to_notify, -1 is user_from_notify).
	 *
	 *
	 * @return bool
	 * @since version
	 */
	public function unnotify($link_id, $direction = 1) {

		if (!is_numeric($link_id)) {
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__emundus_cifre_links'))
			->set($this->db->quoteName(($direction === 1)?'user_to_notify':'user_from_notify').' = 0')
			->where($this->db->quoteName('id').' = '.$link_id);
		$this->db->setQuery($query);

		try {
			return $this->db->execute();
		} catch (Exception $e) {
			JLog::add('Error getting removing notify : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * @param   Int  $link_id
	 * @param   Int  $direction  The direction to look in for the notification (1 is user_to_notify, -1 is user_from_notify).
	 *
	 *
	 * @return bool
	 * @since version
	 */
	public function notify($link_id, $direction = 1) {

		if (!is_numeric($link_id)) {
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__emundus_cifre_links'))
			->set($this->db->quoteName(($direction === 1)?'user_to_notify':'user_from_notify').' = 1')
			->where($this->db->quoteName('id').' = '.$link_id);
		$this->db->setQuery($query);

		try {
			return $this->db->execute();
		} catch (Exception $e) {
			JLog::add('Error getting adding notify : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * @param $sender
	 * @param $receiver
	 *
	 *
	 * @since version
	 */
	public function checkNotify($sender, $receiver) {

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('user_to_notify'))
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('user_from').' = '.$sender.' AND '.$this->db->quoteName('user_to').' = '.$receiver.' AND '.$this->db->quoteName('user_to_notify').' = 1');
		$this->db->setQuery($query);

		try {
			$res = $this->db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting checking notify : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}

		if (!empty($res)) {
			return $res;
		}

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('user_from_notify'))
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('user_to').' = '.$sender.' AND '.$this->db->quoteName('user_from').' = '.$receiver.' AND '.$this->db->quoteName('user_from_notify').' = 1');
		$this->db->setQuery($query);

		try {
			return $this->db->loadResult();
		} catch (Exception $e) {
			JLog::add('Error getting checking notify : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			return false;
		}

	}


	/**
	 * Gets suggestions of potential offers that may interest the user
	 *
	 * @param   Int       $user_id       The ID of the user we are getting suggestions for.
	 * @param   Int       $user_profile  The profile of the user
	 * @param   DateTime  $time_ago      Minimum publish date for the offers.
	 * @param   int       $nb_res        Number of results to display.
	 *
	 * @return Mixed
	 * @since 6.9.1
	 */
	public function getSuggestions($user_id, $user_profile, $time_ago = null, $nb_res = 4) {

		if (empty($user_id) || empty($user_profile)) {
			return false;
		}

		// Using the information about the users location or thematics that he has chosen.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('dep.department'))
			->from($this->db->quoteName('#__emundus_users', 'eu'))
			->leftJoin($this->db->quoteName('#__emundus_users_597_repeat', 'eur').' ON '.$this->db->quoteName('eur.parent_id').' = '.$this->db->quoteName('eu.id'))
			->leftJoin($this->db->quoteName('#__emundus_users_597_repeat_repeat_department', 'dep').' ON '.$this->db->quoteName('dep.parent_id').' = '.$this->db->quoteName('eur.id'))
			->where($this->db->quoteName('eu.user_id').' = '.$user_id.' AND dep.department IS NOT NULL');
		$this->db->setQuery($query);

		try {
			$departments = $this->db->loadColumn();
		} catch (Exception $e) {
			JLog::add('Error getting departments in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		}

		$query->clear()
			->select($this->db->quoteName('t.thematic','thematics'))
			->from($this->db->quoteName('#__emundus_users', 'eu'))
			->leftJoin($this->db->quoteName('#__emundus_users_600_repeat', 't').' ON '.$this->db->quoteName('t.parent_id').' = '.$this->db->quoteName('eu.id'))
			->where($this->db->quoteName('eu.user_id').' = '.$user_id.' AND t.thematic IS NOT NULL');
		$this->db->setQuery($query);

		try {
			$thematics = $this->db->loadColumn();
		} catch (Exception $e) {
			JLog::add('Error getting thematics in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		}

		// Dynamically build a WHERE based on information about the user.
		$fallbackWhere = $this->db->quoteName('eu.profile').' != '.$user_profile.' AND '.$this->db->quoteName('cc.status').' = 1';

		if (!empty($time_ago)) {
		    $fallbackWhere .= ' AND '.$this->db->quoteName('cc.date_submitted').' >= '.$this->db->quote(date('Y-m-d H:i:s', $time_ago));
        }

		// Dynamically add a WHERE clause that can allow for the retrieval of offers, this where can change if not enough results are loaded.
		$thematicsOrLocations = '';
		if ((!empty($thematics) && $thematics[0] !== null) || (!empty($departments) && $departments[0] !== null)) {

			$thematicsOrLocations .= ' AND ( ';

			if (!empty($thematics) && $thematics[0] !== null) {
				$thematicsOrLocations .= $this->db->quoteName('t.themes') . ' IN (' . implode(',', $thematics) . ') ';
				if (!empty($departments) && $departments[0] !== null) {
					$thematicsOrLocations .= ' OR ' . $this->db->quoteName('dep.department') . ' IN (' . implode(',', $departments) . ') ';
				}
			}
			elseif (!empty($departments) && $departments[0] !== null) {
				$thematicsOrLocations .= $this->db->quoteName('dep.department') . ' IN (' . implode(',', $departments) . ') ';
			}

			$thematicsOrLocations .= ' )';
		}

		$where = $fallbackWhere.' '.$thematicsOrLocations;

		$query->clear()
			->select(['cc.fnum', 'ep.titre', 'er.id AS search_engine_page', 'p.label AS profile' , 'GROUP_CONCAT(t.themes) AS themes', 'GROUP_CONCAT(dep.department) as department', 'er.futur_doctorant_yesno', 'er.acteur_public_yesno', 'er.equipe_de_recherche_codirection_yesno', 'er.equipe_de_recherche_direction_yesno'])
			->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
            ->leftJoin($this->db->quoteName('#__emundus_cifre_links', 'cl').' ON ('.$this->db->quoteName('cc.applicant_id').' LIKE '.$this->db->quoteName('cl.user_to').' OR '.$this->db->quoteName('cc.applicant_id').' LIKE '.$this->db->quoteName('cl.user_from').')')
			->leftJoin($this->db->quoteName('#__emundus_users', 'eu').' ON '.$this->db->quoteName('eu.user_id').' = '.$this->db->quoteName('cc.user_id'))
			->leftJoin($this->db->quoteName('#__emundus_setup_profiles', 'p').' ON '.$this->db->quoteName('p.id').' = '.$this->db->quoteName('eu.profile'))
			->leftJoin($this->db->quoteName('#__emundus_projet', 'ep').' ON '.$this->db->quoteName('ep.fnum').' LIKE '.$this->db->quoteName('cc.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_projet_620_repeat', 't').' ON '.$this->db->quoteName('t.parent_id').' = '.$this->db->quoteName('ep.id'))
			->leftJoin($this->db->quoteName('#__emundus_recherche', 'er').' ON '.$this->db->quoteName('er.fnum').' LIKE '.$this->db->quoteName('cc.fnum'))
			->leftJoin($this->db->quoteName('#__emundus_recherche_630_repeat', 'err').' ON '.$this->db->quoteName('err.parent_id').' = '.$this->db->quoteName('er.id'))
			->leftJoin($this->db->quoteName('#__emundus_recherche_630_repeat_repeat_department', 'dep').' ON '.$this->db->quoteName('dep.parent_id').' = '.$this->db->quoteName('err.id'))
			->where($where)
			->group([$this->db->quoteName('cc.fnum'), $this->db->quoteName('ep.titre'), $this->db->quoteName('er.id')]);

		$this->db->setQuery($query);

		try {
			$results = $this->db->loadObjectList();
			shuffle($results);
		} catch (Exception $e) {
			JLog::add('Error getting cifre suggestions in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			$results = [];
		}

		// If we have not gotten enough (or any) results, we need to rerun the query but with less constraints on the WHERE.
		// Why? Because we want to suggest the results with the most pertinance to the user, and if there arent enough: suggest something anyways
		if (is_array($results) && sizeof($results) < $nb_res) {

			// Here we are making sure that we do not get the same fnums are before, to avoid duplicates.
			$noDuplicates = array();
			if (sizeof($results) > 0) {
				foreach ($results as $result) {
					$noDuplicates[] = $result->fnum;
				}
			}

			// If we have found results in the previous query: append them to the WHERE to avoid getting them again.
			if (!empty($noDuplicates)) {
				$where = $fallbackWhere.' AND '.$this->db->quoteName('cc.fnum').' NOT IN ('.implode(',', $noDuplicates).')';
			} else {
				$where = $fallbackWhere;
			}

			// Same query except we are using JUST the fallback where, this means that we are getting more results but less related to the user's situation.
			$query->clear()
				->select(['cc.fnum', 'ep.titre', 'er.id AS search_engine_page', 'p.label AS profile' , 'GROUP_CONCAT(t.themes) AS themes', 'GROUP_CONCAT(dep.department) as department', 'er.futur_doctorant_yesno', 'er.acteur_public_yesno', 'er.equipe_de_recherche_codirection_yesno', 'er.equipe_de_recherche_direction_yesno'])
				->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
				->leftJoin($this->db->quoteName('#__emundus_cifre_links', 'cl').' ON ('.$this->db->quoteName('cc.applicant_id').' LIKE '.$this->db->quoteName('cl.user_to').' OR '.$this->db->quoteName('cc.applicant_id').' LIKE '.$this->db->quoteName('cl.user_from').')')
				->leftJoin($this->db->quoteName('#__emundus_users', 'eu').' ON '.$this->db->quoteName('eu.user_id').' = '.$this->db->quoteName('cc.user_id'))
				->leftJoin($this->db->quoteName('#__emundus_setup_profiles', 'p').' ON '.$this->db->quoteName('p.id').' = '.$this->db->quoteName('eu.profile'))
				->leftJoin($this->db->quoteName('#__emundus_projet', 'ep').' ON '.$this->db->quoteName('ep.fnum').' LIKE '.$this->db->quoteName('cc.fnum'))
				->leftJoin($this->db->quoteName('#__emundus_projet_620_repeat', 't').' ON '.$this->db->quoteName('t.parent_id').' = '.$this->db->quoteName('ep.id'))
				->leftJoin($this->db->quoteName('#__emundus_recherche', 'er').' ON '.$this->db->quoteName('er.fnum').' LIKE '.$this->db->quoteName('cc.fnum'))
				->leftJoin($this->db->quoteName('#__emundus_recherche_630_repeat', 'err').' ON '.$this->db->quoteName('err.parent_id').' = '.$this->db->quoteName('er.id'))
				->leftJoin($this->db->quoteName('#__emundus_recherche_630_repeat_repeat_department', 'dep').' ON '.$this->db->quoteName('dep.parent_id').' = '.$this->db->quoteName('err.id'))
				->where($where)
				->group([$this->db->quoteName('cc.fnum'), $this->db->quoteName('ep.titre'), $this->db->quoteName('er.id')]);
			$this->db->setQuery($query);

			try {
				$res = $this->db->loadObjectList();
				shuffle($res);
				$results = array_merge($results, $res);
			} catch (Exception $e) {
				JLog::add('Error getting cifre suggestions in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			}
		}

		// return a randomized, $nb_res element long array.
		$results = array_slice($results, 0, $nb_res);
		shuffle($results);
		return $results;
	}


	/**
	 * @param $id
	 *
	 * @return bool|mixed|null
	 *
	 * @since version
	 */
	public function getDepartmentsByRegion($id) {

        $query = $this->db->getQuery(true);

        $query->select(array($this->db->quoteName('departement_id'), $this->db->quoteName('departement_nom')))
            ->from($this->db->quoteName('data_departements'))
            ->where($this->db->quoteName('region_id') . " = " . $id);

        $this->db->setQuery($query);

        try {
            return $this->db->loadObject();
        } catch (Exception $e) {
            JLog::add('Error getting cifre suggestions in m/cifre at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
	}
}
