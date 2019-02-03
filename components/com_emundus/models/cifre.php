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
			->select($this->db->quoteName('esp.id', 'profile_id'), $this->db->quoteName('esp.label', 'profile'), $this->db->quoteName('cc.fnum'), $this->db->quoteName('p.titre'))
			->from($this->db->quoteName('#__emundus_campaign_candidature','cc'))
			->join('LEFT', $this->db->quoteName('#__emundus_projet', 'p') . ' ON (' . $this->db->quoteName('p.fnum') . ' = ' . $this->db->quoteName('cc.fnum') . ')')
			->join('LEFT', $this->db->quoteName('#__emundus_users', 'eu') . ' ON (' . $this->db->quoteName('eu.user_id') . ' = ' . $this->db->quoteName('cc.applicant_id') . ')')
			->join('LEFT', $this->db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON (' . $this->db->quoteName('esp.id') . ' = ' . $this->db->quoteName('eu.profile') . ')')
			->where($this->db->quoteName('cc.user_id') . ' = '.$user_id . ' AND ' . $this->db->quoteName('cc.status') . ' = 1');

			$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * This function gets all of the contact requests that are destined to the user.
	 * @param null $user Int The user who has received the contact requests.
	 * @return Mixed An array of objects.
	 */
	function getContactToUser($user) {

		if (empty($user))
			return false;

		$query = $this->db->getQuery(true);
		$query
			->select([$this->db->quoteName('esp.id', 'profile_id'), $this->db->quoteName('esp.label', 'profile'), $this->db->quoteName('cl.id','link_id'), 'cl.*', 'p.*', $this->db->quoteName('r.id', 'search_engine_page')])
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
			JLog::add('Error getting offers to user in m/cifre at query '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

	/**
	 * This function gets all of the contact requests that are sent by the user.
	 * @param null $user Int The user who has sent the contact requests.
	 * @return Mixed An array of objects.
	 */
	function getContactFromUser($user) {

		if (empty($user))
			return false;

		$query = $this->db->getQuery(true);
		$query
			->select([$this->db->quoteName('esp.id', 'profile_id'), $this->db->quoteName('esp.label', 'profile'), $this->db->quoteName('cl.id','link_id'), 'cl.*', 'p.*', $this->db->quoteName('r.id', 'search_engine_page')])
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
			JLog::add('Error getting offers to user in m/cifre at query '.$query->__toString(), JLog::ERROR, 'com_emundus');
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
	 * Gets the Masters information linked to the user passed in the params or the currently logged in user if not.
	 *
	 * @param $user_id Int The user ID of the person to check the MsC.
	 * @return Mixed
	 */
	function getUserMasters($user_id = null) {

		if (empty($user_id))
			$user_id = JFactory::getUser()->id;

		// First step is to get the user in question and make sure his profile is correct.
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('profile').', '.$this->db->quoteName('master_2_intitule').', '.$this->db->quoteName('master_2_etablissement').', '.$this->db->quoteName('master_2_annee'))
			->from($this->db->quoteName('#__emundus_users'))
			->where('user_id = '.$user_id);
		$this->db->setQuery($query);
		try {

			$master = $this->db->loadObject();

			// Do not continue if the user is not a PhD.
			if ($master->profile != '1006')
				return false;
			else
				return $master;

		} catch (Exception $e) {
			JLog::add('Error getting emundus user info in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
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

	/**
	 * Gets the CIFRE link by ID.
	 *
	 * @param Int $id the ID of the link to get.
	 * @return Mixed
	 */
	public function getLinkByID($id) {

		$query = $this->db->getQuery(true);
		$query
			->select('*')
			->from($this->db->quoteName('#__emundus_cifre_links'))
			->where($this->db->quoteName('id').'='.$id);
		$this->db->setQuery($query);


		try {
			return $this->db->loadObject();
		} catch (Exception $e) {
			JLog::add('Error getting CIFRE link by ID in m/CIFRE at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
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

		$query
			->update($this->db->quoteName('#__emundus_cifre_links'))
			->set([$this->db->quoteName('state').' = '.$state])
			->where([$this->db->quoteName('id').'='.$id]);
		$this->db->setQuery($query);

		try {
			$this->db->execute();
			return true;
		} catch (Exception $e) {
			JLog::add('Error updating CIFRE link state in m/CIFRE at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return false;
		}
	}


	/**
	 * Gets suggestions of potential offers that may interest the user
	 *
	 * @param Int $user_id The ID of the user we are getting suggestions for.
	 * @param Int $user_profile The profile of the user
	 * @return Mixed
	 */
	public function getSuggestions($user_id, $user_profile) {

		if (empty($user_id) || empty($user_profile))
			return false;

		// Using the information about the users location or thematics that he has chosen.
		$query = $this->db->getQuery(true);
		$query
			->select($this->db->quoteName('dep.department'))
			->from($this->db->quoteName('#__emundus_users', 'eu'))
			->leftJoin($this->db->quoteName('#__emundus_users_597_repeat', 'eur').' ON '.$this->db->quoteName('eur.parent_id').' = '.$this->db->quoteName('eu.id'))
			->leftJoin($this->db->quoteName('#__emundus_users_597_repeat_repeat_department', 'dep').' ON '.$this->db->quoteName('dep.parent_id').' = '.$this->db->quoteName('eur.id'))
			->where($this->db->quoteName('eu.user_id').' = '.$user_id);
		$this->db->setQuery($query);

		try {
			$departments = $this->db->loadColumn();
		} catch (Exception $e) {
			JLog::add('Error getting departments in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
		}

		$query = $this->db->getQuery(true);
		$query
			->select($this->db->quoteName('t.thematic','thematics'))
			->from($this->db->quoteName('#__emundus_users', 'eu'))
			->leftJoin($this->db->quoteName('#__emundus_users_600_repeat', 't').' ON '.$this->db->quoteName('t.parent_id').' = '.$this->db->quoteName('eu.id'))
			->where($this->db->quoteName('eu.user_id').' = '.$user_id);
		$this->db->setQuery($query);

		try {
			$thematics = $this->db->loadColumn();
		} catch (Exception $e) {
			JLog::add('Error getting thematics in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
		}

		// Dynamically build a WHERE based on information about the user.
		$fallbackWhere = $this->db->quoteName('eu.profile').' != '.$user_profile.' AND '.$this->db->quoteName('cl.user_to').' != '.$user_id.' AND '.$this->db->quoteName('cl.user_from').' != '.$user_id;
		if ($user_profile == 1006)
			$fallbackWhere .= ' AND '.$this->db->quoteName('er.futur_doctorant_yesno').' = 1 ';
		elseif ($user_profile == 1007)
			$fallbackWhere .= ' AND ('.$this->db->quoteName('er.equipe_recherche_direction_yesno').' = 1 OR '.$this->db->quoteName('er.equipe_recherche_codirection_yesno').' = 1) ';
		elseif ($user_profile == 1008)
			$fallbackWhere .= ' AND '.$this->db->quoteName('er.acteur_publique_yesno').' = 1 ';

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

		$query = $this->db->getQuery(true);
		$query
			->select([$this->db->quoteName('cc.fnum'), $this->db->quoteName('ep.titre'), $this->db->quoteName('er.id', 'search_engine_page')])
			->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
			->leftJoin($this->db->quoteName('#__emundus_cifre_links', 'cl').' ON ('.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('cl.fnum_to').' OR '.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('cl.fnum_from').')')
			->leftJoin($this->db->quoteName('#__emundus_users', 'eu').' ON '.$this->db->quoteName('eu.user_id').' = '.$this->db->quoteName('cc.user_id'))
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
			JLog::add('Error getting cifre suggestions in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			$results = [];
		}

		// If we have not gotten enough (or any) results, we need to rerun the query but with less constraints on the WHERE.
		// Why? Because we want to suggest the results with the most pertinance to the user, and if there arent enough: suggest something anyways
		if (is_array($results) && sizeof($results) < 4) {

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
			$query = $this->db->getQuery(true);
			$query
				->select([$this->db->quoteName('cc.fnum'), $this->db->quoteName('ep.titre'), $this->db->quoteName('er.id', 'search_engine_page')])
				->from($this->db->quoteName('#__emundus_campaign_candidature', 'cc'))
				->leftJoin($this->db->quoteName('#__emundus_cifre_links', 'cl').' ON ('.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('cl.fnum_to').' OR '.$this->db->quoteName('cc.fnum').' LIKE '.$this->db->quoteName('cl.fnum_from').')')
				->leftJoin($this->db->quoteName('#__emundus_users', 'eu').' ON '.$this->db->quoteName('eu.user_id').' = '.$this->db->quoteName('cc.user_id'))
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
				JLog::add('Error getting cifre suggestions in m/cifre at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
			}
		}

		// return a randomized, 4 element long array.
		$results = array_slice($results, 0, 4);
		shuffle($results);
		return $results;
	}
}