<?php
/**
 * Created by PhpStorm.
 * User: James Dean
 * Date: 2018-12-27
 * Time: 12:12
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelFormations extends JModelLegacy {

	public function __construct(array $config = array()) {
		JLog::addLogger(array('text_file' => 'com_emundus.emundus-formations.php'), JLog::ALL, array('com_emundus'));
		parent::__construct($config);
	}

	/**
	 * @param      $cid
	 * @param null $user
	 * @param int  $profile
	 *
	 * @return mixed
	 */
	public function checkHR($cid, $user = null, $profile = 1002) {
        $db = JFactory::getDBO();

        if (empty($user)) {
        	$user = JFactory::getUser()->id;
	        if (empty($user)) {
		        return false;
	        }
        }

        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__emundus_user_entreprise'))
            ->where($db->quoteName('user') . ' = ' . $user . ' AND ' . $db->quoteName('profile') .' = '  . $profile . ' AND cid = ' . $cid);
        $db->setQuery($query);
        try {
            return $db->loadResult();
        } catch(Exception $e) {
            JLog::add('Error checking HR at m/formation in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function deleteCompany($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__emundus_entreprise'))
            ->where('id =' . $id);

        $db->setQuery($query);

        try {
            $db->execute();
        } catch(Exception $e) {
            JLog::add('Error deleting company at m/formation in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
        }
    }

	/** Deletes a user from a company.
	 * @param      $user_id int the user to deassociate from company.
	 * @param      $cid int company id
	 * @param      $hr_user int user id of the HR
	 *
	 * @return bool
	 */
    public function deleteAssociate($user_id, $cid, $hr_user) {

    	if (empty($hr_user)) {
		    $hr_user = JFactory::getSession()->get('emundusUser')->id;
	    }

    	// If any param is empty or the user is not an HR of the company then return false.
    	if (!empty($hr_user) && !empty($user_id) && !empty($cid) && !empty($this->checkHR($cid, $hr_user))) {

		    $db = JFactory::getDbo();
		    $query = $db->getQuery(true);

		    $query->delete($db->quoteName('#__emundus_user_entreprise'))
			    ->where([$db->quoteName('cid').' = '.$cid, $db->quoteName('user').' = '.$user_id]);
		    $db->setQuery($query);

		    try {
			    $db->execute();
			    return true;
		    } catch(Exception $e) {
			    JLog::add('Error deleting associate at m/formation in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		    }
	    }

	    return false;
    }


	/**
	 * @param      $user_hr Int The user who is supposedly a DRH
	 * @param null $user_intern Int The user who is supposedly an intern._
	 * @param int  $profile Int The profile which determines if a user is DRH.
	 *
	 * @return Bool
	 */
	public function checkHRUser($user_hr, $user_intern = null, $profile = 1002) {

		$db = JFactory::getDbo();

		$query = 'SELECT '.$db->quoteName('user')
			.' FROM '.$db->quoteName('#__emundus_user_entreprise')
			.' WHERE '.$db->quoteName('cid').' IN (
				  SELECT'.$db->quoteName('cid').' FROM '.$db->quoteName('#__emundus_user_entreprise')
				.'WHERE '.$db->quoteName('user').' = '.$user_hr.' AND '.$db->quoteName('profile').' = '.$profile
			.')';
		$db->setQuery($query);

		try {
			return in_array($user_intern, $db->loadColumn());
		} catch(Exception $e) {
			JLog::add('Error checking if we are DHR of user at m/formation in query: '.$query, JLog::ERROR, 'com_emundus');
			return false;
		}

	}


	/**
	 * @param      $user Int The user who we are checking.
	 * @param      $company Int The company the user may be in._
	 *
	 * @return Bool
	 */
	public function checkCompanyUser($user, $company) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__emundus_user_entreprise'))
			->where($db->quoteName('user').' = '.$user.' AND '.$db->quoteName('cid').' = '.$company);
		$db->setQuery($query);

		try {
			return !empty($db->loadResult());
		} catch(Exception $e) {
			JLog::add('Error checking if user is in a company at m/formation in query: '.$query, JLog::ERROR, 'com_emundus');
			return false;
		}

	}

	/** Gets all applicants to a session in which the user is a DRH of the company they are signed up as.
	 *
	 * @param      $campaign
	 * @param null $user
	 *
	 * @return bool|mixed
	 */
	public function getApplicantsInSessionForDRH($campaign, $user = null) {

		if ($user == null) {
			$user = JFactory::getUser()->id;
		}

		if ($user == null) {
			return false;
		}

		$companies = $this->getCompaniesDRH($user);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select([$db->quoteName('e.id'), $db->quoteName('e.raison_sociale', 'company'), $db->quoteName('u.user_id'), $db->quoteName('u.birthday'), $db->quoteName('u.civility'), $db->quoteName('u.firstname'), $db->quoteName('u.lastname'), $db->quoteName('cc.fnum'), $db->quoteName('eu.position')])
			->from($db->quoteName('#__emundus_users','u'))
			->leftJoin($db->quoteName('#__emundus_campaign_candidature','cc').' ON '.$db->quoteName('cc.applicant_id').' = '.$db->quoteName('u.user_id'))
			->leftJoin($db->quoteName('#__emundus_user_entreprise','eu').' ON '.$db->quoteName('eu.user').' = '.$db->quoteName('cc.applicant_id'))
			->leftJoin($db->quoteName('#__emundus_entreprise','e').' ON '.$db->quoteName('e.id').' = '.$db->quoteName('cc.company_id'))
			->where($db->quoteName('cc.campaign_id').' = '.$campaign.' AND '.$db->quoteName('cc.company_id').' IN ('.implode(',', $companies).')')
            ->group([$db->quoteName('u.user_id'), $db->quoteName('e.id')]);
		$db->setQuery($query);

		try {
			return $db->loadObjectList();
		} catch (Exception $e) {
			JLog::add('Error getting sessions for DRH at m/formation in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return null;
		}

	}


	/**
	 * @param null $user_id
	 *
	 * @return bool
	 */
	public function getCompaniesDRH($user_id = null) {

		if ($user_id == null) {
			$user_id = JFactory::getUser()->id;
            if ($user_id == null) {
                return false;
            }
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('cid'))
			->from($db->quoteName('#__emundus_user_entreprise'))
			->where($db->quoteName('user').' = '.$user_id.' AND '.$db->quoteName('profile').' = 1002');
		$db->setQuery($query);

		try {
			return $db->loadColumn();
		} catch (Exception $e) {
			JLog::add('Error getting user companies at m/formation in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}

	}


	/**
     * this function returns all the formations the user is signed up to by the DRH
     * @param      $campaign
     * @param null $user_id
     * @return mixed
     */
	public function getUserFormationByRH($user_id = null, $user_rh = null) {
        if ($user_id == null) {
            return null;
        }

        if($user_rh == null) {
            $user_rh = JFactory::getUser()->id;
            if ($user_rh == null) {
                return null;
            }
        }

        $user_companies = $this->getCompaniesDRH($user_rh);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select([$db->quoteName('ecc.fnum'), $db->quoteName('esp.id', 'program_id'), $db->quoteName('estu.label', 'label'), $db->quoteName('estu.price'), $db->quoteName('estu.notes'), $db->quoteName('estu.date_start'), $db->quoteName('estu.date_end'), $db->quoteName('estu.location_title'), $db->quoteName('estu.location_address'), $db->quoteName('estu.location_zip'), $db->quoteName('estu.location_city'), $db->quoteName('estu.session_code'), $db->quoteName('estu.hours'), $db->quoteName('estu.hours'), $db->quoteName('estu.code'), $db->quoteName('ess.value'), $db->quoteName('ess.class')])
            ->from($db->quoteName('#__emundus_users', 'eu'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('ecc.applicant_id') . ' = ' . $db->quoteName('eu.user_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'))
            ->leftJoin($db->quoteName('#__emundus_setup_programmes', 'esp') . ' ON ' . $db->quoteName('esp.code') . ' = ' . $db->quoteName('esc.training'))
            ->leftJoin($db->quoteName('#__emundus_setup_teaching_unity', 'estu') . ' ON ' . $db->quoteName('estu.session_code') . ' = ' . $db->quoteName('esc.session_code'))
            ->leftJoin($db->quoteName('#__emundus_setup_status', 'ess') . ' ON ' . $db->quoteName('ess.step') . ' = ' . $db->quoteName('ecc.status'))
            ->where($db->quoteName('eu.user_id') . " = " . $user_id . ' AND ' . $db->quoteName('ecc.company_id') . ' IN (' . implode(', ', $user_companies) . ')');
        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error getting user companies at m/formation in query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return null;
        }


    }

}
