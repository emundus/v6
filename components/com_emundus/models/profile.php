<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelProfile extends JModelList {
    var $_db = null;
    /**
     * Constructor
     *
     * @since 1.5
     */
    function __construct() {
        parent::__construct();
        $this->_db = JFactory::getDBO();
    }

    /**
     * Gets the greeting
     * @return string The greeting to be displayed to the user
     */
    function getProfile($p) {
        $query = 'SELECT * FROM #__emundus_setup_profiles WHERE id='.(int)$p;

        try {
            $this->_db->setQuery( $query );
            return $this->_db->loadObject();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getApplicantsProfiles() {
        $db = JFactory::getDBO();
        $query = 'SELECT *
        			FROM #__emundus_setup_profiles esp
                 	WHERE esp.published=1  AND status=1
                  	ORDER BY esp.label';
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    /**
     * @return array of profile_id for all applicant profiles
     */
    public function getApplicantsProfilesArray() {
        $obj_profiles = $this->getApplicantsProfiles();
        $array_p = array();
        foreach ($obj_profiles as $profile){
            array_push($array_p, $profile->id);
        }
        return $array_p;
    }

    function getUserProfiles($uid) {
        $db = JFactory::getDBO();
        $query = 'SELECT DISTINCT esp.id , esp.label
		FROM #__emundus_setup_profiles esp
		LEFT JOIN #__emundus_users_profiles eup on eup.profile_id = esp.id
		WHERE eup.user_id = '.$uid;
        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getProfileByApplicant($aid) {
        $query = 'SELECT eu.firstname, eu.lastname, eu.profile, eu.university_id,
							esp.label AS profile_label, esp.menutype, esp.published
						FROM #__emundus_users AS eu
						LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile
						WHERE eu.user_id = '.$aid;

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssoc();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

	/**
	 * This is used to replace getProfileByApplicant when using an fnum.
	 * @param $fnum
	 *
	 * @return array
	 */
	function getFullProfileByFnum($fnum) : array {

		$query = $this->_db->getQuery(true);
		// This is the part in common between the two queries.
		$query->select('eu.firstname, eu.lastname, esp.id AS profile, eu.university_id, esp.label, esp.menutype, esp.published')
			->from($this->_db->quoteName('jos_emundus_campaign_candidature', 'cc'))
			->leftJoin($this->_db->quoteName('jos_emundus_users', 'eu').' ON '.$this->_db->quoteName('eu.user_id').' = '.$this->_db->quoteName('cc.applicant_id'))
			->leftJoin($this->_db->quoteName('jos_emundus_setup_status', 'ss').' ON '.$this->_db->quoteName('ss.step').' = '.$this->_db->quoteName('cc.status'))
			->leftJoin($this->_db->quoteName('jos_emundus_setup_profiles', 'esp').' ON '.$this->_db->quoteName('esp.id').' = '.$this->_db->quoteName('ss.profile'))
			->where($this->_db->quoteName('cc.fnum').' LIKE '.$this->_db->quote($fnum));

		try {

			$this->_db->setQuery($query);
			$res = $this->_db->loadAssoc();

			if (!empty($res['profile'])) {
				return $res;
			} else {

				// Here we build the other query based on the root query defined at the start.
				$query->clear()
					->select('eu.firstname, eu.lastname, esp.id AS profile, eu.university_id, esp.label, esp.menutype, esp.published')
					->from($this->_db->quoteName('jos_emundus_campaign_candidature', 'cc'))
					->leftJoin($this->_db->quoteName('jos_emundus_users', 'eu').' ON '.$this->_db->quoteName('eu.user_id').' = '.$this->_db->quoteName('cc.applicant_id'))
					->leftJoin($this->_db->quoteName('jos_emundus_setup_campaigns', 'c').' ON '.$this->_db->quoteName('c.id').' = '.$this->_db->quoteName('cc.campaign_id'))
					->leftJoin($this->_db->quoteName('jos_emundus_setup_profiles', 'esp').' ON '.$this->_db->quoteName('esp.id').' = '.$this->_db->quoteName('c.profile_id'))
					->where($this->_db->quoteName('cc.fnum').' LIKE '.$this->_db->quote($fnum));

				$this->_db->setQuery($query);
				return $this->_db->loadAssoc();
			}

		} catch(Exception $e) {
			JLog::add('Error on query profile Model function getProfileByFnum => '.$query->__toString(), JLog::ERROR, 'com_emundus');
			return [];
		}
	}

    function getProfileById($id) {
        $query = 'SELECT label, menutype, acl_aro_groups from jos_emundus_setup_profiles
						WHERE id ='.$id;

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssoc();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    // We are getting the profile in setup status table
    function getProfileByFnum($fnum) {
        $query = 'SELECT ss.profile from jos_emundus_setup_status ss
                  LEFT JOIN jos_emundus_campaign_candidature cc ON cc.status = ss.step
						WHERE cc.fnum LIKE "'.$fnum.'"';

        try {
            $this->_db->setQuery($query);
            $res = $this->_db->loadResult();
            if (!empty($res)) {
                return $res;
            } else {
                $query = 'SELECT esc.profile_id from jos_emundus_setup_campaigns esc
                  LEFT JOIN jos_emundus_campaign_candidature cc ON cc.campaign_id = esc.id
						WHERE cc.fnum LIKE "'.$fnum.'"';
                $this->_db->setQuery($query);
                return $this->_db->loadResult();
            }
        } catch(Exception $e) {
            JLog::add('Error on query profile Model function getProfileByFnum => '.$query, JLog::ERROR, 'com_emundus');
            return null;
        }
    }

    function getCurrentProfile($aid) {
        $query = 'SELECT eu.*,  esp.*
						FROM #__emundus_users AS eu
						LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile
						WHERE eu.user_id = '.$aid;

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssoc();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getAttachments($p) {
        $query = 'SELECT attachment.*, profile.id AS selected, profile.displayed, profile.mandatory, profile.bank_needed
					FROM #__emundus_setup_attachments AS attachment
					LEFT JOIN #__emundus_setup_attachment_profiles AS profile ON profile.attachment_id = attachment.id AND profile.profile_id='.(int)$p.'
					WHERE attachment.published=1
					ORDER BY attachment.ordering';

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getForms($p) {
        $query = 'SELECT fbtable.id, fbtable.label, menu.id>0 AS selected, menu.lft AS `order` FROM #__fabrik_lists AS fbtable
					LEFT JOIN #__menu AS menu ON fbtable.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("listid=",menu.link)+7, 3), "&", 1)
					AND menu.menutype=(SELECT profile.menutype FROM #__emundus_setup_profiles AS profile WHERE profile.id = '.(int)$p.')
					WHERE fbtable.created_by_alias = "form" ORDER BY selected DESC, menu.lft ASC, fbtable.label ASC';

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function isProfileUserSet($uid) {
        $query = 'SELECT count(user_id) as cpt, profile FROM #__emundus_users WHERE user_id = '.$uid. ' GROUP BY user_id';

        try {
            $this->_db->setQuery($query);
            $res = $this->_db->loadAssocList();

            return $res[0];
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function updateProfile($uid, $campaign) {
        $query = 'UPDATE #__emundus_users SET profile='.$campaign->profile_id.', schoolyear="'.$campaign->year.'" WHERE user_id='.$uid;

        try {
            $this->_db->setQuery($query);
            return $this->_db->execute();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getCurrentCampaignByApplicant($uid) {
        $query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id = '.$uid. ' ORDER BY date_time DESC';

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadResult();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getCurrentIncompleteCampaignByApplicant($uid) {
        $query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE (submitted=0 OR submitted IS NULL) AND applicant_id = '.$uid. ' ORDER BY date_time DESC';

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadResult();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getCurrentCompleteCampaignByApplicant($uid) {
        $query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE submitted=1 AND applicant_id = '.$uid. ' ORDER BY date_time DESC';

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadResult();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getCurrentCampaignInfoByApplicant($uid) {
        $query = 'SELECT esc.*, ecc.date_time, ecc.submitted, ecc.date_submitted, ecc.fnum, esc.profile_id, esp.label, esp.menutype, ecc.submitted, ecc.status
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON ecc.campaign_id = esc.id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.applicant_id = '.$uid. ' ORDER BY ecc.date_time DESC';

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssoc();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getCampaignInfoByFnum($fnum) {
        $query = 'SELECT esc.*, ecc.date_time, ecc.submitted, ecc.date_submitted, ecc.fnum, esc.profile_id, esp.label, esp.menutype, ecc.submitted, ecc.status
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON ecc.campaign_id = esc.id
					LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = esc.profile_id
					WHERE ecc.fnum LIKE '.$fnum. ' ORDER BY ecc.date_time DESC';

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssoc();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getCampaignById($id) {
        $query = 'SELECT * FROM  #__emundus_setup_campaigns AS esc WHERE id='.$id;

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssoc();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getProfileByCampaign($id) {
        $query = 'SELECT esp.*, esc.*
                    FROM  #__emundus_setup_profiles AS esp
                    LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.profile_id = esp.id
                    WHERE esc.id='.$id;

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssoc();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    /**
     * @description : Get profile by status
     * @param   int $step application file status
     * @return  array
     **/
    function getProfileByStatus($step) {
        $query = 'SELECT esp.id as profile_id, esp.label, esp.menutype 
                    FROM  #__emundus_setup_profiles AS esp
                    LEFT JOIN #__emundus_setup_status AS ess ON ess.profile = esp.id
                    WHERE ess.step='.$step;

        try {
            $this->_db->setQuery($query);
            return $this->_db->loadAssoc();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }


	/**
	 * Gets the list of profiles from array of programmes
	 *
	 * @param array $code    list of programmes code
	 * @param array $camps   list of campaigns
	 *
	 * @return array The profile IDs found
	 */
    function getProfileIDByCourse($code = array(), $camps = array()) : array {

        if (!empty($code)>0 && isset($camps[0]) && $camps[0] != 0) {

            $query = 'SELECT DISTINCT(esc.profile_id)
					FROM  #__emundus_setup_campaigns AS esc
					WHERE esc.published = 1 AND esc.training IN ('.implode(",", $this->_db->quote($code)).') AND esc.id IN ('.implode(",", $camps).')';

            try {
                $this->_db->setQuery($query);
                $res = $this->_db->loadColumn();
            } catch(Exception $e) {
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
                $res = [];
                JError::raiseError(500, $e->getMessage());
            }

        } elseif (!empty($code)>0) {

            $query = 'SELECT DISTINCT(esc.profile_id)
						FROM  #__emundus_setup_campaigns AS esc
						WHERE esc.published = 1 AND esc.training IN ("'.implode("','", $code).'") LIMIT 1';

            try {
                $this->_db->setQuery($query);
                $res = $this->_db->loadColumn();
            } catch(Exception $e) {
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
	            $res = [];
                JError::raiseError(500, $e->getMessage());
            }
        } else {
            $res = $code;
        }

        return $res;
    }

	/**
	 * Gets the list of profiles from array of programmes
	 *
	 * @param array $campaign_id
	 *
	 * @return array The profile list for the campaigns
	 */
    function getProfileIDByCampaign(array $campaign_id) : array {

        $res = [];

        if (!empty($campaign_id)) {
            if (in_array('%', $campaign_id)) {
            	$where = '';
            } else {
            	$where = 'WHERE esc.id IN ('.implode(',', $campaign_id).')';
            }

            $query = 'SELECT DISTINCT(esc.profile_id)
						FROM  #__emundus_setup_campaigns AS esc '.$where;

            try {
                $this->_db->setQuery($query);
                $res = $this->_db->loadColumn();
            } catch(Exception $e) {
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
                return [];
            }
        }

        return $res;
    }

    function getFnumDetails($fnum) {
        $query = 'SELECT ecc.*, esc.*, ess.*, epd.profile as profile_id_form
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id
					LEFT JOIN #__emundus_setup_status as ess ON ess.step = ecc.status
					LEFT JOIN #__emundus_personal_detail as epd on epd.fnum = ecc.fnum
					WHERE ecc.fnum like '.$this->_db->Quote($fnum);
        try {
            $this->_db->setQuery($query);
            $res = $this->_db->loadAssoc();
        } catch(Exception $e) {
            $query = 'SELECT ecc.*, esc.*, ess.*
					FROM #__emundus_campaign_candidature AS ecc
					LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id
					LEFT JOIN #__emundus_setup_status as ess ON ess.step = ecc.status
					LEFT JOIN #__emundus_personal_detail as epd on epd.fnum = ecc.fnum
					WHERE ecc.fnum like '.$this->_db->Quote($fnum);
            try {
                $this->_db->setQuery($query);
                $res = $this->_db->loadAssoc();
            } catch(Exception $e) {
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
                JError::raiseError(500, $e->getMessage());
            }
        }

        return $res;
    }

    function getCandidatureByFnum($fnum) {
        return $this->getFnumDetails($fnum);
    }

    function isApplicationDeclared($aid) {
        $query = 'SELECT COUNT(*) FROM #__emundus_declaration WHERE user = '.$aid;

        try {
            $this->_db->setQuery($query);
            $res = $this->_db->loadResult();
            return $res>0;
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }


    /**
     * Get fnums for applicants
     * @param int $aid               Applicant ID
     * @param int $submitted         Submitted application
     * @param date $start_date       campaigns as started after
     * @param date $end_date         campaigns as ended before
     * @return array
     * @throws Exception
     */
    public function getApplicantFnums(int $aid, $submitted = null, $start_date = null, $end_date = null) {
        $db = JFactory::getDBO();

        $query = 'SELECT ecc.*, esc.label, esc.start_date, esc.end_date, esc.admission_start_date, esc.admission_end_date, esc.training, esc.year, esc.profile_id
                    FROM #__emundus_campaign_candidature as ecc
                    LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id=ecc.campaign_id
                    WHERE ecc.published=1 AND ecc.applicant_id='.$aid;
        $query .= (!empty($submitted))?' AND ecc.submitted='.$submitted:'';
        $query .= (!empty($start_date))?' AND esc.start_date<='.$db->Quote($start_date):'';
        $query .= (!empty($end_date))?' AND esc.end_date>='.$db->Quote($end_date):'';

        try {
            $db->setQuery($query);
            return $db->loadObjectList('fnum');
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: fct : getAttachmentsById :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
        }
    }

	/**
	 * Creates an object in the session that acts as a replacement for the default Joomla user
	 *
	 * @param null $fnum
	 */
    public function initEmundusSession($fnum = null) {
        include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');
        include_once(JPATH_SITE.'/components/com_emundus/models/users.php');
        include_once(JPATH_SITE.'/components/com_emundus/models/admission.php');

        $m_users = new EmundusModelUsers;
        $current_user = JFactory::getUser();
        $session = JFactory::getSession();
        $app = JFactory::getApplication();

        if (empty($fnum)) {
	        $profile = $this->getProfileByApplicant($current_user->id);
        } else {
	        $profile = $this->getFullProfileByFnum($fnum);
        }

        $emundusSession = new stdClass();

        foreach ($session->get('user') as $key => $value) {
            $emundusSession->{$key} = $value;
        }

        $emundusSession->firstname = $profile["firstname"];
        $emundusSession->lastname = strtoupper($profile["lastname"]);
        $emundusSession->emGroups = array_keys($m_users->getUserGroups($current_user->id));
        $emundusSession->emProfiles = $this->getUserProfiles($current_user->id);

        $profiles = $m_users->getApplicantProfiles();
        $profile_array = array();
        foreach ($profiles as $pf) {
            array_push($profile_array, $pf->id);
        }

        if (empty($fnum)) {
	        $profile = $this->getCurrentProfile($current_user->id);
        }

        if (in_array($profile['profile'], $profile_array)) {

            // Get the current user profile
	        if (empty($fnum)) {
		        $campaign = $this->getCurrentCampaignInfoByApplicant($current_user->id);
	        } else {
		        $campaign = $this->getCampaignInfoByFnum($fnum);
	        }

            /*if (!empty($campaign)) {
                $profile = $this->getProfileByCampaign($campaign["id"]);
            }*/

            // If the user is admitted then we fill the session with information about the admitted file
            // regardeless of the current campaign
            $emundusSession->fnum = $campaign["fnum"];
            $emundusSession->fnums = $this->getApplicantFnums($current_user->id);
            $emundusSession->campaign_id = $campaign["id"];
            $emundusSession->status = @$campaign["status"];
            $emundusSession->candidature_incomplete = ($campaign['status']==0)?0:1;
            $emundusSession->profile = !empty($profile["profile_id"]) ? $profile["profile_id"] : $profile["profile"];
            $emundusSession->profile_label = $profile["label"];
            $emundusSession->menutype = $profile["menutype"];
            $emundusSession->university_id = null;
            $emundusSession->applicant = 1;
            $emundusSession->start_date = $campaign["start_date"];
            $emundusSession->end_date = $campaign["end_date"];
            $emundusSession->candidature_start = $campaign["start_date"];
            $emundusSession->candidature_end = $campaign["end_date"];
            $emundusSession->admission_start_date = $campaign["admission_start_date"];
            $emundusSession->admission_end_date = $campaign["admission_end_date"];
            $emundusSession->candidature_posted = (@$profile["date_submitted"] == "0000-00-00 00:00:00" || @$profile["date_submitted"] == 0  || @$profile["date_submitted"] == NULL)?0:1;
            $emundusSession->schoolyear = $campaign["year"];
            $emundusSession->code = $campaign["training"];
            $emundusSession->campaign_name = $campaign["label"];

        } else {
            $emundusSession->profile                = $profile["profile"];
            $emundusSession->profile_label          = $profile["profile_label"];
            $emundusSession->menutype               = $profile["menutype"];
            $emundusSession->university_id          = $profile["university_id"];
            $emundusSession->applicant              = 0;
        }

        $session->set('emundusUser', $emundusSession);

        if (isset($admissionInfo)) {
            $app->redirect("index.php?option=com_fabrik&view=form&formid=".$admissionInfo->form_id."&Itemid='.$admissionInfo->item_id.'&usekey=fnum&rowid=".$campaign['fnum']);
        }
    }


	/**
	 * Returns an object based on supplied user_id that acts as a replacement for the default Joomla user method
	 *
	 * @param $user_id
	 *
	 * @return stdClass
	 * @throws Exception
	 */
    public function getEmundusUser($user_id) {
        include_once(JPATH_SITE.'/components/com_emundus/helpers/access.php');
        include_once(JPATH_SITE.'/components/com_emundus/models/users.php');

        $app = JFactory::getApplication();

        $m_users = new EmundusModelUsers;
        $current_user = JFactory::getUser($user_id);
        $profile = $this->getProfileByApplicant($current_user->id);
        $emundus_user = new stdClass();
        foreach ($current_user as $key => $value) {
            $emundus_user->{$key} = $value;
        }

        $emundus_user->firstname = $profile["firstname"];
        $emundus_user->lastname = strtoupper($profile["lastname"]);
        $emundus_user->emGroups = array_keys($m_users->getUserGroups($current_user->id));
        $emundus_user->emProfiles = $this->getUserProfiles($current_user->id);

        $profiles = $m_users->getApplicantProfiles();
        $profile_array = array();
        foreach ($profiles as $pf) {
            array_push($profile_array, $pf->id);
        }

        $profile = $this->getCurrentProfile($current_user->id);

        if (in_array($profile['profile'], $profile_array)) {
            $campaign = $this->getCurrentCampaignInfoByApplicant($current_user->id);
            $incomplete = $this->getCurrentIncompleteCampaignByApplicant($current_user->id);
            $p = $this->isProfileUserSet($current_user->id);

            if (empty($p['profile']) || empty($campaign["id"]) || !isset($p['profile']) || !isset($campaign["id"])) {
            	$app->redirect(JRoute::_('index.php?option=com_fabrik&view=form&formid=102&random=0'));
            }

            $profile = $this->getProfileByCampaign($campaign["id"]);

            $emundus_user->profile = $profile["profile_id"];
            $emundus_user->profile_label = $profile["label"];
            $emundus_user->menutype = $profile["menutype"];
            $emundus_user->university_id = null;
            $emundus_user->applicant = 1;
            $emundus_user->start_date = $profile["start_date"];
            $emundus_user->end_date = $profile["end_date"];
            $emundus_user->candidature_start = $profile["start_date"];
            $emundus_user->candidature_end = $profile["end_date"];
            $emundus_user->candidature_posted = (@$profile["date_submitted"] == "0000-00-00 00:00:00" || @$profile["date_submitted"] == 0  || @$profile["date_submitted"] == NULL)?0:1;
            $emundus_user->candidature_incomplete = (!is_array($incomplete) || count($incomplete) == 0)?0:1;
            $emundus_user->schoolyear = $profile["year"];
            $emundus_user->code = $profile["training"];
            $emundus_user->campaign_id = $campaign["id"];
            $emundus_user->campaign_name = $profile["label"];
            $emundus_user->fnum = $campaign["fnum"];
            $emundus_user->fnums = $this->getApplicantFnums($current_user->id, null, $profile["start_date"], $profile["end_date"]);
            $emundus_user->status = @$campaign["status"];
        } else {
            $emundus_user->profile = $profile["profile"];
            $emundus_user->profile_label = $profile["label"];
            $emundus_user->menutype = $profile["menutype"];
            $emundus_user->university_id = $profile["university_id"];
            $emundus_user->applicant = 0;
        }
        return $emundus_user;
    }


    public function getHikashopMenu($profile) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('m.link'))
            ->from($db->quoteName('#__menu', 'm'))
            ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp').' ON '.$db->quoteName('m.menutype').' = '.$db->quoteName('esp.menutype').' AND '.$db->quoteName('link').' <> "" AND '.$db->quoteName('link').' <> "#"')
            ->where($db->quoteName('esp.id').' = ' . $profile . ' AND ' . $db->quoteName('m.link') . ' LIKE ' . $db->quote('%com_hikashop%') . ' AND ' . $db->quoteName('m.published') . ' = 1');
        $db->setQuery($query);

        try {
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add('Error getting first page of application at model/application in query : '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}
