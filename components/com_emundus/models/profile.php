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
                 	WHERE esp.published=1
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
        $query = 'SELECT DISTINCT esp.id , esp.label, esp.published, esp.status
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

    function affectNoProfile($aid){
        $query = $this->_db->getQuery(true);

        try {
            $query->select('id')
                ->from($this->_db->quoteName('#__emundus_setup_profiles'))
                ->where($this->_db->quoteName('label') . ' = ' . $this->_db->quote('noprofile'));
            $this->_db->setQuery($query);
            $noprofile = $this->_db->loadResult();

            if(!isset($noprofile)){
                $query->clear()
                    ->insert($this->_db->quoteName('#__emundus_setup_profiles'));
                $query->set($this->_db->quoteName('label') . ' = ' . $this->_db->quote('noprofile'))
                    ->set($this->_db->quoteName('published') . ' = 1')
                    ->set($this->_db->quoteName('acl_aro_groups') . ' = 2')
                    ->set($this->_db->quoteName('status') . ' = 0');
                $this->_db->setQuery($query);
                $this->_db->execute();
                $noprofile = $this->_db->insertid();
            }

            $query->clear()
                ->update($this->_db->quoteName('#__emundus_users'))
                ->set($this->_db->quoteName('profile') . ' = ' . $noprofile)
                ->where($this->_db->quoteName('user_id') . ' = ' . $aid);

            $this->_db->setQuery($query);
            return $this->_db->execute();
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

		try {
            $res = $this->getProfileByStatus($fnum);

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

    function getAttachments($p, $mandatory = false) {
        $query = $this->_db->getQuery(true);

        $query
            ->select(['attachment.*', $this->_db->quoteName('profile.id', 'selected'), $this->_db->quoteName('profile.mandatory'), $this->_db->quoteName('profile.bank_needed'), $this->_db->quoteName('profile.displayed')])
            ->from($this->_db->quoteName('#__emundus_setup_attachments', 'attachment'))
            ->leftJoin($this->_db->quoteName('#__emundus_setup_attachment_profiles','profile').' ON '.$this->_db->quoteName('profile.attachment_id').' = '.$this->_db->quoteName('attachment.id') . ' AND ' . $this->_db->quoteName('profile.profile_id').' = '. (int)$p)
            ->where($this->_db->quoteName('attachment.published') . ' = 1')
            ->order($this->_db->quoteName('attachment.ordering'));

        if ($mandatory) {
            $query
                ->andWhere($this->_db->quoteName('profile.mandatory') . ' = 1');
        }

        $this->_db->setQuery($query);

        try {
            return $this->_db->loadObjectList();
        } catch(Exception $e) {
            JLog::add(' Error getting list  of attachments by profile at model/profile in query  -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
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
        $query = 'SELECT esc.*, esc.label as campaign_label, ecc.date_time, ecc.submitted, ecc.date_submitted, ecc.fnum, esc.profile_id, esp.label, esp.menutype, ecc.submitted, ecc.status
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
    function getProfileByStatus($fnum) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $res = array();

        try {
            /* get profiles by workflow -- based on actual status */
            $res = $this->getProfileByWorkflow($fnum);

            unset($res['step']);
            unset($res['type']);

            if(empty($res['profile'])) {
                $query->select('eu.firstname, eu.lastname, esp.id AS profile, eu.university_id, esp.label, esp.menutype, esp.published, cc.campaign_id as campaign_id')
                    ->from($this->_db->quoteName('jos_emundus_campaign_candidature', 'cc'))
                    ->leftJoin($this->_db->quoteName('jos_emundus_users', 'eu') . ' ON ' . $this->_db->quoteName('eu.user_id') . ' = ' . $this->_db->quoteName('cc.applicant_id'))
                    ->leftJoin($this->_db->quoteName('jos_emundus_campaign_workflow', 'ecw') . ' ON ' . $this->_db->quoteName('ecw.campaign') . ' = ' . $this->_db->quoteName('cc.campaign_id') . ' AND ' . $this->_db->quoteName('ecw.status') . ' = ' . $this->_db->quoteName('cc.status'))
                    ->leftJoin($this->_db->quoteName('jos_emundus_setup_profiles', 'esp') . ' ON ' . $this->_db->quoteName('esp.id') . ' = ' . $this->_db->quoteName('ecw.profile'))
                    ->where($this->_db->quoteName('cc.fnum') . ' LIKE ' . $fnum);

                $this->_db->setQuery($query);
                $res = $this->_db->loadAssoc();

                if (empty($res['profile'])) {
                    $query->clear()
                        ->select('eu.firstname, eu.lastname, esp.id AS profile, eu.university_id, esp.label, esp.menutype, esp.published, cc.campaign_id as campaign_id')
                        ->from($this->_db->quoteName('jos_emundus_campaign_candidature', 'cc'))
                        ->leftJoin($this->_db->quoteName('jos_emundus_users', 'eu') . ' ON ' . $this->_db->quoteName('eu.user_id') . ' = ' . $this->_db->quoteName('cc.applicant_id'))
                        ->leftJoin($this->_db->quoteName('jos_emundus_setup_status', 'ss') . ' ON ' . $this->_db->quoteName('ss.step') . ' = ' . $this->_db->quoteName('cc.status'))
                        ->leftJoin($this->_db->quoteName('jos_emundus_setup_profiles', 'esp') . ' ON ' . $this->_db->quoteName('esp.id') . ' = ' . $this->_db->quoteName('ss.profile'))
                        ->where($this->_db->quoteName('cc.fnum') . ' LIKE ' . $fnum);

                    $this->_db->setQuery($query);
                    $res = $this->_db->loadAssoc();

                    if (empty($res['profile'])) {
                        $query->clear()
                            ->select('eu.firstname, eu.lastname, esp.id AS profile, eu.university_id, esp.label, esp.menutype, esp.published, cc.campaign_id as campaign_id')
                            ->from($this->_db->quoteName('jos_emundus_campaign_candidature', 'cc'))
                            ->leftJoin($this->_db->quoteName('jos_emundus_users', 'eu') . ' ON ' . $this->_db->quoteName('eu.user_id') . ' = ' . $this->_db->quoteName('cc.applicant_id'))
                            ->leftJoin($this->_db->quoteName('jos_emundus_setup_campaigns', 'sc') . ' ON ' . $this->_db->quoteName('sc.id') . ' = ' . $this->_db->quoteName('cc.campaign_id'))
                            ->leftJoin($this->_db->quoteName('jos_emundus_setup_profiles', 'esp') . ' ON ' . $this->_db->quoteName('esp.id') . ' = ' . $this->_db->quoteName('sc.profile_id'))
                            ->where($this->_db->quoteName('cc.fnum') . ' LIKE ' . $fnum);

                        $this->_db->setQuery($query);
                        $res = $this->_db->loadAssoc();
                    }
                }
            }
            return $res;
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            JError::raiseError(500, $e->getMessage());
        }
    }

    function getProfileByStep($fnum,$step){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        try {
            $query->select('esp.id AS profile')
                ->from($this->_db->quoteName('jos_emundus_campaign_candidature', 'cc'))
                ->leftJoin($this->_db->quoteName('jos_emundus_users', 'eu') . ' ON ' . $this->_db->quoteName('eu.user_id') . ' = ' . $this->_db->quoteName('cc.applicant_id'))
                ->leftJoin($this->_db->quoteName('jos_emundus_campaign_workflow', 'ecw') . ' ON ' . $this->_db->quoteName('ecw.campaign') . ' = ' . $this->_db->quoteName('cc.campaign_id'))
                ->leftJoin($this->_db->quoteName('jos_emundus_setup_profiles', 'esp') . ' ON ' . $this->_db->quoteName('esp.id') . ' = ' . $this->_db->quoteName('ecw.profile'))
                ->where($this->_db->quoteName('cc.fnum') . ' LIKE ' . $fnum)
                ->andWhere($this->_db->quoteName('ecw.step') . ' = ' . $step);

            $this->_db->setQuery( $query );
            return $this->_db->loadResult();
        } catch(Exception $e) {
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /// get profile from menutype
    public function getProfileByMenu($menu) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            /// another way, if $menu has the regular expression "menu-profile" --> will see

            $query->clear()
                ->select('#__emundus_setup_profiles.*')
                ->from($db->quoteName('#__emundus_setup_profiles'))
                ->where($db->quoteName('#__emundus_setup_profiles.menutype') . '=' . $db->quote($menu))
                ->andWhere($db->quoteName('#__emundus_setup_profiles.published') . '=' . 1);
            $db->setQuery($query);
            return $db->loadObject();

        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /// get fabrik list by ids
    public function getFabrikListByIds($flist) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($flist)) {
            try {
                $query->clear()
                    ->select('jfl.*')
                    ->from($db->quoteName('#__fabrik_lists', 'jfl'))
                    ->where($db->quoteName('jfl.id') . 'IN (' . $flist . ' )');

                $db->setQuery($query);

                return $db->loadObjectList();
            } catch(Exception $e) {
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    // get fabrik form by list
    public function getFabrikFormByList($list) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($list)) {
            try {
                $query->clear()
                    ->select('jff.id, jff.label')
                    ->from($db->quoteName('#__fabrik_forms', 'jff'))
                    ->leftJoin($db->quoteName('#__fabrik_lists', 'jfl') . ' ON ' . $db->quoteName('jfl.form_id') . ' = ' . $db->quoteName('jff.id'))
                    ->where($db->quoteName('jfl.id') . '=' . $list );

                $db->setQuery($query);
                return $db->loadObject();

            } catch(Exception $e) {

            }
        } else {
            return false;
        }
    }

    /// get fabrik groups by ids
    public function getFabrikGroupByList($glist) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($glist)) {
            try {
                $query->clear()
                    ->select('jfg.*')
                    ->from($db->quoteName('#__fabrik_groups', 'jfg'))
                    ->where($db->quoteName('jfg.id') . '=' . $glist);

                $db->setQuery($query);
                return $db->loadObjectList();

            } catch(Exception $e) {
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /// get fabrik elements by ids
    public function getFabrikElementById($eid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($eid)) {
            $query->clear()
                ->select('jfe.id, jfe.name, jfe.label, jfe.group_id')
                ->from($db->quoteName('#__fabrik_elements', 'jfe'))
                ->where($db->quoteName('jfe.id') . '=' . (int)$eid);

            $db->setQuery($query);
            return $db->loadObject();       // return element
        } else {
            return false;
        }
    }

    /// get data from element name
    public function getDataFromElementName($element, $fnum, $user) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($element) and !empty($fnum) and !empty($user)) {
            try {
                /// step 1 --> get table name
                $query->clear()
                    ->select('jfl.*')
                    ->from($db->quoteName('#__fabrik_lists', 'jfl'))
                    ->leftJoin($db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON ' . $db->quoteName('jffg.form_id') . '=' . $db->quoteName('jfl.form_id'))
                    ->leftJoin($db->quoteName('#__fabrik_elements', 'jfe') . ' ON ' . $db->quoteName('jffg.group_id') . '=' . $db->quoteName('jfe.group_id'))
                    ->where($db->quoteName('jfe.id') . '=' . (int)$element->id)
                    ->andWhere($db->quoteName('jfe.name') . '=' . $db->quote($element->name))
                    ->andWhere($db->quoteName('jfe.group_id') . '=' . (int)$element->group_id);

                $db->setQuery($query);

                $_table_name = $db->loadObject();

                /// step 2 --> from table name --> get element data from element name (element_name == column) with ::fnum and ::user
                /// input params :: $db->quote($element->name) + $_table_name->db_tale_name
                ///
                $query->clear()
                    ->select($_table_name->db_table_name . '.' . $element->name)
                    ->from($_table_name->db_table_name)
                    ->where($_table_name->db_table_name . '.fnum' . '=' . $db->quote($fnum))
                    ->andWhere($_table_name->db_table_name . '.user' . '=' . $db->quote($user));

                $db->setQuery($query);
                $_element_data = $db->loadResult();

                return $_element_data;

            } catch(Exception $e) {
                return $e->getMessage();
            }
        } else {
            return false;
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

    /**
     * Gets the list of profiles from array of programmes
     *
     * @param array $campaign_id
     *
     * @return array The profile list for the campaigns
     */
    function getProfilesIDByCampaign(array $campaign_id) : array {
        $res = [];

        if (!empty($campaign_id)) {
            $jesp_datas = ' jesp.label, jesp.description, jesp.published, jesp.schoolyear, jesp.candidature_start, jesp.candidature_end, jesp.menutype, jesp.acl_aro_groups, jesp.is_evaluator, jesp.evaluation_start, 
                            jesp.evaluation_end, jesp.evaluation, jesp.status, jesp.class ';
            if (in_array('%', $campaign_id)) {
                $where = '';
                $where_jecw = '';
                $where_jeswspr = '';
                $where_jeswsrr = '';
            } else {
                $where = 'WHERE esc.id IN ('.implode(',', $campaign_id).')';
                $where_jecw = 'WHERE jecw.campaign IN ('.implode(',', $campaign_id).')';
                $where_jeswspr = 'WHERE jesc.id IN ('.implode(',', $campaign_id).') AND jeswspr.profile is not null';
                $where_jeswsrr = 'WHERE jesc.id IN ('.implode(',', $campaign_id).') AND jeswsrr.profile is not null';
            }

            $query = 'SELECT DISTINCT (esc.profile_id) AS pid,
                        jesp.label, jesp.description, jesp.published, jesp.schoolyear, jesp.candidature_start, jesp.candidature_end, jesp.menutype, 
                        jesp.acl_aro_groups, jesp.is_evaluator, jesp.evaluation_start, jesp.evaluation_end, jesp.evaluation, jesp.status, jesp.class, null AS step, null AS phase, null AS lbl

                        FROM #__emundus_setup_campaigns AS esc 
                        LEFT JOIN #__emundus_setup_profiles AS jesp ON jesp.id = esc.profile_id
                     '
                . $where .

                ' UNION 
                        SELECT DISTINCT (jecw.profile) AS pid, 
                        jesp.label, jesp.description, jesp.published, jesp.schoolyear, jesp.candidature_start, jesp.candidature_end, jesp.menutype, 
                        jesp.acl_aro_groups, jesp.is_evaluator, jesp.evaluation_start, jesp.evaluation_end, jesp.evaluation, jesp.status, jesp.class, step AS step, null AS phase, null AS lbl
                        
                        FROM #__emundus_campaign_workflow AS jecw 
                        LEFT JOIN #__emundus_setup_profiles AS jesp ON jesp.id = jecw.profile        
                '
                . $where_jecw .

                ' UNION
                    SELECT DISTINCT(jeswspr.profile) as pid,
                        jesp.label, jesp.description, jesp.published, jesp.schoolyear, jesp.candidature_start, jesp.candidature_end, jesp.menutype, 
                        jesp.acl_aro_groups, jesp.is_evaluator, jesp.evaluation_start, jesp.evaluation_end, jesp.evaluation, jesp.status, jesp.class, null AS step, jesws.id AS phase, jesws.label as lbl
                        
                        FROM #__emundus_setup_workflow_step_profiles_repeat AS jeswspr
                        LEFT JOIN #__emundus_setup_workflow_step AS jesws ON jesws.id = jeswspr.parent_id
                        LEFT JOIN #__emundus_setup_workflow AS jesw ON jesw.id = jesws.workflow
                        LEFT JOIN #__emundus_setup_campaigns AS jesc ON jesc.workflow = jesw.id 
                        LEFT JOIN #__emundus_setup_profiles AS jesp ON jesp.id = jeswspr.profile
                    ' . $where_jeswspr .

                ' UNION
                    SELECT DISTINCT(jeswsrr.profile) AS pid,
                        jesp.label, jesp.description, jesp.published, jesp.schoolyear, jesp.candidature_start, jesp.candidature_end, jesp.menutype, 
                        jesp.acl_aro_groups, jesp.is_evaluator, jesp.evaluation_start, jesp.evaluation_end, jesp.evaluation, jesp.status, jesp.class, jeswsrr.ordering AS step, jesws.id as phase, jesws.label as lbl

                        FROM #__emundus_setup_workflow_step_rules_repeat AS jeswsrr
                        LEFT JOIN #__emundus_setup_workflow_step AS jesws ON jesws.id = jeswsrr.parent_id
                        LEFT JOIN #__emundus_setup_workflow AS jesw ON jesw.id = jesws.workflow
                        LEFT JOIN #__emundus_setup_campaigns AS jesc ON jesc.workflow = jesw.id
                        LEFT JOIN #__emundus_setup_profiles AS jesp ON jesp.id = jeswsrr.profile
                ' . $where_jeswsrr . ' ORDER BY step';
            try {
                $this->_db->setQuery($query);
                $res = $this->_db->loadObjectList();
            } catch(Exception $e) {
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus');
                return [];
            }
        }

        return $res;
    }

    public function getProfileIDByCampaigns($campaigns, $codes) {
        $query = $this->_db->getQuery(true);
        if(!empty($campaigns)) {
            if(!empty($codes)) {
                try {
                    $query->clear()
                        ->select('#__emundus_setup_campaigns.*')
                        ->from($this->_db->quoteName('#__emundus_setup_campaigns'))
                        ->where($this->_db->quoteName('#__emundus_setup_campaigns.id') . 'IN (' . implode(',', $campaigns) . ')')
                        ->andWhere($this->_db->quoteName('#__emundus_setup_campaigns.training') . 'IN ("' . implode(',', $codes) . '")');

                    $this->_db->setQuery($query);
                    $_firstResult = $this->_db->loadObjectList();

                    $firstProfile = array();
                    foreach ($_firstResult as $key => $value) {
                        $firstProfile[] = $value->profile_id;
                    }

                    $query->clear()
                        ->select('#__emundus_campaign_workflow.*')
                        ->from($this->_db->quoteName('#__emundus_campaign_workflow'))
                        ->where($this->_db->quoteName('#__emundus_campaign_workflow.campaign') . 'IN (' . implode(',', $campaigns) . ')')
                        ->order('step');

                    $this->_db->setQuery($query);
                    $_secondResult = $this->_db->loadObjectList();

                    $secondProfile = array();
                    foreach ($_secondResult as $key => $value) {
                        $secondProfile[] = $value->profile;
                    }

                    $profileIds = array_unique(array_merge($firstProfile, $secondProfile));

                    $query->clear()
                        ->select('#__emundus_setup_profiles.*')
                        ->from($this->_db->quoteName('#__emundus_setup_profiles'))
                        ->where($this->_db->quoteName('#__emundus_setup_profiles.id') . 'IN (' . implode(',', $profileIds) . ')');

                    $this->_db->setQuery($query);

                    $_profilesLabels = $this->_db->loadObjectList();

                    foreach ($_profilesLabels as $key => $value) {
                        $profileLabels[] = $value->label;
                        $profileMenuType[] = $value->menutype;
                    }

                    return ['profile_id' => $profileIds, 'profile_label' => $profileLabels, 'profile_menu_type' => $profileMenuType];

                } catch(Exception $e) {
                    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(). ' : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                    return false;
                }
            } else {
                try {
                    $query->clear()
                        ->select('#__emundus_campaign_workflow.*')
                        ->from($this->_db->quoteName('#__emundus_campaign_workflow'))
                        ->where($this->_db->quoteName('#__emundus_campaign_workflow.campaign') . 'IN (' . implode(',', $campaigns) . ')');

                    $this->_db->setQuery($query);

                    $_firstResult = $this->_db->loadObjectList();

                    foreach ($_firstResult as $key => $value) {
                        $firstProfile[] = $value->profile;
                    }

                    $query->clear()
                        ->select('#__emundus_setup_campaigns.*')
                        ->from($this->_db->quoteName('#__emundus_setup_campaigns'))
                        ->where($this->_db->quoteName('#__emundus_setup_campaigns.id') . 'IN (' . implode(',', $campaigns) . ')');

                    $this->_db->setQuery($query);
                    $_secondResult = $this->_db->loadObjectList();

                    foreach ($_secondResult as $key => $value) {
                        $secondProfile[] = $value->profile_id;
                    }

                    $_profileIds = array_unique(array_merge($firstProfile,$secondProfile));

                    $query->clear()
                        ->select('#__emundus_setup_profiles.*')
                        ->from($this->_db->quoteName('#__emundus_setup_profiles'))
                        ->where($this->_db->quoteName('#__emundus_setup_profiles.id') . 'IN (' . implode(',', $_profileIds) . ')');

                    $this->_db->setQuery($query);

                    $_profilesLabels = $this->_db->loadObjectList();

                    foreach ($_profilesLabels as $key => $value) {
                        $profileLabels[] = $value->label;
                    }

                    return ['profile_id' => $_profileIds, 'profile_label' => $profileLabels];

                } catch(Exception $e) {
                    JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$query->__toString(). ' : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                    return false;
                }
            }
        } else {
            return false;
        }
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

        if (empty($profile["profile"])) {
            $this->affectNoProfile($current_user->id);
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
            $emundusSession->campaign_name = $campaign["campaign_label"];

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

    public function getProfileByWorkflow($fnum) {
        require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS. 'models' . DS . 'files.php');
        $mFile = new EmundusModelFiles();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $res = $this->getSecondaryProfileByFnum($fnum);

        if(!empty($res['profile'])) {
            unset($res['astatus']);
            return $res;
        } else {

            $query->clear()
                ->select('eu.firstname, eu.lastname, eswspr.profile AS profile, eu.university_id, esp.label, esp.menutype, esp.published, cc.campaign_id as campaign_id, esws.id as step, eswspr.type')
                ->from($db->quoteName('#__emundus_setup_workflow_step_profiles_repeat', 'eswspr'))
                ->leftJoin($db->quoteName('#__emundus_setup_workflow_step', 'esws') . ' ON ' . $db->quoteName('eswspr.parent_id') . ' = ' . $db->quoteName('esws.id'))
                ->leftJoin($db->quoteName('#__emundus_setup_workflow', 'esw') . ' ON ' . $db->quoteName('esw.id') . ' = ' . $db->quoteName('esws.workflow'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.workflow') . ' = ' . $db->quoteName('esw.id'))
                ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
                ->leftJoin($this->_db->quoteName('jos_emundus_setup_profiles', 'esp') . ' ON ' . $this->_db->quoteName('esp.id') . ' = ' . $this->_db->quoteName('eswspr.profile'))
                ->leftJoin($this->_db->quoteName('jos_emundus_users', 'eu') . ' ON ' . $this->_db->quoteName('eu.user_id') . ' = ' . $this->_db->quoteName('cc.applicant_id'))
                ->where($this->_db->quoteName('cc.fnum') . ' LIKE ' . $db->quote($fnum));

            $db->setQuery($query);
            $raw = $db->loadAssocList();        /* many rowa */

            /* get fnum info */
            $fnum_raw = $mFile->getFnumsInfos([$fnum]);
            $fnum_status = $fnum_raw[$fnum]['step'];

            foreach ($raw as $k => $v) {
                /* find profile for this step */
                $query->clear()
                    ->select('group_concat(eswssr.status) as inputs')
                    ->from($db->quoteName('#__emundus_setup_workflow_step_status_repeat', 'eswssr'))
                    ->leftJoin($db->quoteName('#__emundus_setup_workflow_step', 'esws') . ' ON ' . $db->quoteName('eswssr.parent_id') . ' = ' . $db->quoteName('esws.id'))
                    ->where($this->_db->quoteName('eswssr.parent_id') . ' = ' . $db->quote($v['step']))
                    ->andWhere($this->_db->quoteName('eswssr.type') . ' = 1');

                $db->setQuery($query);
                $ins = $db->loadAssoc();

                $inputs = explode(',', $ins['inputs']);

                //////
                $query->clear()
                    ->select('group_concat(eswssr.status) as outputs')
                    ->from($db->quoteName('#__emundus_setup_workflow_step_status_repeat', 'eswssr'))
                    ->leftJoin($db->quoteName('#__emundus_setup_workflow_step', 'esws') . ' ON ' . $db->quoteName('eswssr.parent_id') . ' = ' . $db->quoteName('esws.id'))
                    ->where($this->_db->quoteName('eswssr.parent_id') . ' = ' . $db->quote($v['step']))
                    ->andWhere($this->_db->quoteName('eswssr.type') . ' = 0');

                $db->setQuery($query);
                $outs = $db->loadAssoc();

                $outputs = explode(',', $outs['outputs']);

                /* if:
                    1. profile type not 1 (not default)) --> skip
                    2. actual status doesn't exist in workflow --> skip
                */
                if (in_array($fnum_status, $inputs) and $v['type'] === '1') {
                    return $raw[$k];
                } else if ($v['type'] != '1' or (!in_array($fnum_status, $inputs) and !in_array($fnum_status, $outputs))) {
                    unset($raw[$k]);
                }
            }

            return current($raw);
        }
    }

    /* get secondary profiles */
    public function getSecondaryProfileByFnum($fnum) {
        require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS. 'models' . DS . 'files.php');
        $mFile = new EmundusModelFiles();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        /* when candidat opens the dossier --> find exact profile by his/her status */
        $query->clear()
            ->select('eu.firstname, eu.lastname, eswsrr.profile AS profile, eu.university_id, esp.label, esp.menutype, esp.published, cc.campaign_id as campaign_id, esws.id as step, eswsrr.status as astatus')

            ->from($db->quoteName('#__emundus_setup_workflow_step_rules_repeat', 'eswsrr'))
            ->leftJoin($db->quoteName('#__emundus_setup_workflow_step', 'esws') .  ' ON ' . $db->quoteName('eswsrr.parent_id') . ' = ' . $db->quoteName('esws.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_workflow', 'esw') . ' ON ' . $db->quoteName('esw.id') . ' = ' . $db->quoteName('esws.workflow'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.workflow') . ' = ' . $db->quoteName('esw.id'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
            ->leftJoin($this->_db->quoteName('jos_emundus_setup_profiles', 'esp').' ON '.$this->_db->quoteName('esp.id').' = '.$this->_db->quoteName('eswsrr.profile'))
            ->leftJoin($this->_db->quoteName('jos_emundus_users', 'eu').' ON '.$this->_db->quoteName('eu.user_id').' = '.$this->_db->quoteName('cc.applicant_id'))
            ->where($this->_db->quoteName('cc.fnum') . ' LIKE ' . $db->quote($fnum));

        $db->setQuery($query);
        $raw = $db->loadAssocList();

        /* get fnum info */
        $fnum_raw = $mFile->getFnumsInfos([$fnum]);
        $fnum_status = $fnum_raw[$fnum]['step'];
        
        foreach($raw as $k=>$v) {
            if($fnum_status !== $v['astatus']) { unset($raw[$k]); }
        }

        return current($raw);
    }

    /* get last page (id) of selected form */
    public function getLastPage($menutype) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($menutype)) {
            try {
                $query->clear()
                    ->select('m.*')
                    ->from($db->quoteName('#__menu', 'm'))
                    ->where($db->quoteName('m.menutype') . '=' . '"' . $menutype . '"');
                $db->setQuery($query);
                $rawData = $db->loadObjectList();

                $orderList = array('left' => array(), 'right' => array());

                /// find the max lft and rgt from $_rawData
                foreach ($rawData as $key => $value) {
                    $orderList['left'][$value->id] = $value->lft;
                    $orderList['right'][$value->id] = $value->rgt;
                }

                $isLastPage = (array_search(max(array_values($orderList['left'])), $orderList['left']) == array_search(max(array_values($orderList['right'])), $orderList['right'])) ? true : false;

                if ($isLastPage) {
                    $lastPage = array_search(max(array_values($orderList['left'])), $orderList['left']);

                    foreach ($rawData as $_key => $_value) {
                        if ($_value->id == $lastPage) {
                            return $_value;
                        }
                    }
                } else {
                    return false;
                }
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    /* get step by fnum */
    public function getStepByFnum($fnum) {
        require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS. 'models' . DS . 'files.php');
        $mFile = new EmundusModelFiles();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        /* avoid to use session in unit test */
        $fnum_raw = $mFile::getFnumInfos($fnum);
        $fnum_status = $fnum_raw['status'];
        $fnum_campaign = $fnum_raw['campaign_id'];

        /* get workflow id by campaign */
        $query->clear()
            ->select('esw.*')
            ->from($db->quoteName('#__emundus_setup_workflow', 'esw'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esw.id') . ' = ' . $db->quoteName('esc.workflow'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'ecc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('ecc.campaign_id'))
            ->where($db->quoteName('ecc.fnum') . ' = ' . $db->quote($fnum));

        $db->setQuery($query);
        $workflow = $db->loadObject();

        $all_status = $this->getStatusByWorkflow($workflow->id);
        /* find all steps by the last $workflow */
        $query->clear()
            ->select('esws.id as step, esws.start_date, esws.end_date, esws.ordering')
            ->from($db->quoteName('#__emundus_setup_workflow_step', 'esws'))
            ->leftJoin($db->quoteName('#__emundus_setup_workflow', 'esw') . ' ON ' . $db->quoteName('esw.id') . ' = ' . $db->quoteName('esws.workflow'))
            ->where($db->quoteName('esws.workflow') . ' = ' . $db->quote($workflow->id));

        $db->setQuery($query);
        $steps = $db->loadObjectList();

        /* find fnum_status in each $steps above */
        $res = new stdClass();

        foreach ($steps as $step) {
            $input_status = array();
            $output_status = array();

            $status_list = $this->getStatusByStep($step->step);

            /* extract input, output status */
            foreach($status_list as $status) {
                if($status->type == '1') { $input_status[] = $status->status; }
                else { $output_status[] = $status->status; }
            }

            if(!in_array($fnum_status, explode(',', $all_status->allstatus))) {
                $res->step = null;
                $res->editable_status = [];
                $res->output_status = [];

                if(!is_null($this->getProfileByStatus($fnum)['profile'])) {
                    $query->clear()
                        ->select('esc.*')
                        ->from($db->quoteName('#__emundus_setup_campaigns', 'esc'))
                        ->where($db->quoteName('esc.id') . '=' . $db->quote($this->getProfileByStatus($fnum)['campaign_id']));

                    $db->setQuery($query);
                    $raw_camp = $db->loadAssoc();

                    $res->profile = $this->getProfileByStatus($fnum)['profile'];
                    $res->menutype = $this->getProfileById($res->profile)['menutype'];
                    
                    $res->start_date = $raw_camp['start_date'];
                    $res->end_date = $raw_camp['end_date'];
                } else {
                    return false;
                }
                $res->msg = '*** Always Read-only ***';
                $res->ordering = null;
            } else {
                $profile = $this->getProfileByStatus($fnum)['profile'];
                if (in_array($fnum_status, $input_status)) {
                    $res->step = $step->step;
                    $res->editable_status = $input_status;
                    $res->output_status = $output_status;

                    $res->profile = $profile;
                    $res->menutype = $this->getProfileById($profile)['menutype'];

                    $res->start_date = $step->start_date;
                    $res->end_date = $step->end_date;
                    $res->msg = '*** Potentially Edit ***';
                    $res->ordering = $step->ordering;
                    break;
                } else {
                    if (in_array($fnum_status, $output_status)) {
                        $res->step = $step->step;
                        $res->editable_status = [];
                        $res->output_status = [];

                        $res->profile = $profile;
                        $res->menutype = $this->getProfileById($profile)['menutype'];

                        $res->start_date = $step->start_date;
                        $res->end_date = $step->end_date;
                        $res->msg = '*** Always Read-only ***';
                        $res->ordering = null;
                        break;
                    }
                }
            }
        }
        return $res;
    }

    /* get status (2 types) by step */
    public function getStatusByStep($step) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->select('eswssr.*')
            ->from($db->quoteName('#__emundus_setup_workflow_step_status_repeat', 'eswssr'))
            ->where($db->quoteName('eswssr.parent_id') . ' = ' . $db->quote($step));

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /* get all status by workflow */
    public function getStatusByWorkflow($wid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->select('eswssr.*, group_concat(eswssr.status) as allstatus')
            ->from($db->quoteName('#__emundus_setup_workflow_step_status_repeat', 'eswssr'))
            ->leftJoin($db->quoteName('#__emundus_setup_workflow_step', 'esws') . ' ON ' . $db->quoteName('eswssr.parent_id') . ' = ' . $db->quoteName('esws.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_workflow', 'esw') . ' ON ' . $db->quoteName('esw.id') . ' = ' . $db->quoteName('esws.workflow'))
            ->where($db->quoteName('esw.id') . ' = ' . $db->quote($wid));

        $db->setQuery($query);
        return $db->loadObject();       // set key is status
    }

    /* TODO : get next profile (if any) */
    /* public function getNextProfile($step, $fnum) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->select('jesws.id as id, jesws.ordering')
            ->from($db->quoteName('#__emundus_setup_workflow_step', 'jesws'))
            ->leftJoin($db->quoteName('#__emundus_setup_workflow','jesw') .  ' ON ' . $db->quoteName('jesws.workflow') . ' = ' . $db->quoteName('jesw.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'jesc') . ' ON ' . $db->quoteName('jesw.id') . ' = ' . $db->quoteName('jesc.workflow'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'jecc') . ' ON ' . $db->quoteName('jesc.id') . ' = ' . $db->quoteName('jecc.campaign_id'))
            ->where($db->quoteName('jecc.fnum') . ' LIKE ' . $db->quote($fnum));

        $db->setQuery($query);
        $raw = $db->loadObjectList();

        $psteps = array();

        if($step->ordering !== null) {
            foreach ($raw as $key => $value) {
                if ((int)$value->ordering > (int)$step->ordering) {
                    $psteps[] = $raw[$key];
                }
            }
        }
        
        if($psteps == null or count($psteps) == 0) {
            return false;
        } else {
            $next_step = current($psteps)->id;

            require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS. 'models' . DS . 'files.php');
            $mFile = new EmundusModelFiles();

            $fnumRaw = $mFile::getFnumInfos($fnum);
            $fnum_status = $fnumRaw['status'];

            $next_profile_raw = $this->getProfileByStatus($fnum);

            $next_profile_id = $next_profile_raw['id'];
            $next_profile_menutype = $next_profile_raw['menutype'];

            $session = JFactory::getSession();
            $aid = $session->get('emundusUser');
            
            unset($aid->profile);
            unset($aid->menutype);

            $_session = new stdClass();
            $_session->profile = $next_profile_id;
            $_session->menutype = $next_profile_menutype;
            
            $session->set('emundusUser',$_session);

            require_once(JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS. 'models' . DS . 'application.php');
            $m_application 	= new EmundusModelApplication();

            $next_page = $m_application->getFirstPage() . '&usekey=fnum&rowid=' . $fnum;

            return $next_page;
        }
    } */

}
