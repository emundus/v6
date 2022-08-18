<?php
/**
 * Created by eMundus.
 * User: brivalland
 * Date: 23/05/14
 * Time: 11:39
 * @package        Joomla
 * @subpackage    eMundus
 * @link        http://www.emundus.fr
 * @copyright    Copyright (C) 2016 eMundus. All rights reserved.
 * @license        GNU/GPL
 * @author        Benjamin Rivalland
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');

class EmundusModelUsers extends JModelList {
    var $_total = null;
    var $_pagination = null;

    protected $data;

    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct() {
        parent::__construct();

        $session = JFactory::getSession();
        if (!$session->has('filter_order')) {
            $session->set('filter_order', 'id');
            $session->set('filter_order_Dir', 'desc');
        }

        $mainframe = JFactory::getApplication();

        if (!$session->has('limit')) {
            $limit = $mainframe->getCfg('list_limit');
            $limitstart = 0;
            $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

            $session->set('limit', $limit);
            $session->set('limitstart', $limitstart);
        } else {
            $limit      = intval($session->get('limit'));
            $limitstart = intval($session->get('limitstart'));
            $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

            $session->set('limit', $limit);
            $session->set('limitstart', $limitstart);
        }
    }

    public function _buildContentOrderBy() {
        $session = JFactory::getSession();
        $params = $session->get('filt_params');
        $filter_order     = @$params['filter_order'];
        $filter_order_Dir = @$params['filter_order_Dir'];

        $can_be_ordering = array ('user', 'id', 'lastname', 'firstname', 'username', 'email', 'profile', 'block', 'lastvisitDate', 'registerDate', 'newsletter', 'groupe', 'university');

        if (!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering))
            $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        else
            $orderby = ' ORDER BY u.id DESC';

        return $orderby;
    }

    public function _buildQuery() {
        $session        = JFactory::getSession();
        $params         = $session->get('filt_params');
        $db             = JFactory::getDBO();

        $final_grade    = @$params['finalgrade'];
        $search         = @$params['s'];
        $programme      = @$params['programme'];
        $campaigns      = @$params['campaign'];
        $schoolyears    = @$params['schoolyears'];
        $groupEval      = @$params['evaluator_group'];
        $spam_suspect   = @$params['spam_suspect'];
        $profile        = @$params['profile'];
        $oprofiles      = @$params['o_profiles'];
        $newsletter     = @$params['newsletter'];
        $group          = @$params['group'];
        $institution    = @$params['institution'];

        $uid = JRequest::getVar('rowid', null, 'GET', 'none', 0);
        $edit = JRequest::getVar('edit', 0, 'GET', 'none', 0);
        $list_user="";

        if (!empty($schoolyears) && (empty($campaigns) || $campaigns[0]=='%') && $schoolyears[0]!='%') {
            $list_user = "";
            $applicant_schoolyears = $this->getUserListWithSchoolyear($schoolyears);
            $i = 0;
            $nb_element = count($applicant_schoolyears);
            if ($nb_element == 0) {
                $list_user.="EMPTY";
            } else {
                foreach ($applicant_schoolyears as $applicant) {
                    if (++$i === $nb_element)
                        $list_user .= $applicant;
                    elseif ($applicant!=NULL)
                        $list_user .= $applicant.", ";
                }
            }
        } elseif (!empty($campaigns) && $campaigns[0]!='%' && (empty($schoolyears) || $schoolyears[0]=='%')) {

            $list_user = "";
            $applicant_campaigns = $this->getUserListWithCampaign($campaigns);
            $i = 0;
            $nb_element = count($applicant_campaigns);
            if ($nb_element == 0) {
                $list_user.="EMPTY";
            } else {
                foreach ($applicant_campaigns as $applicant) {
                    if (++$i === $nb_element)
                        $list_user .= $applicant;
                    else if ($applicant != NULL)
                        $list_user .= $applicant.", ";
                }
            }
        } elseif (!empty($campaigns) && $campaigns[0]!='%' &&  !empty($schoolyears) && $schoolyears[0]!='%') {
            //$applicant_schoolyears = $this->getUserListWithSchoolyear($schoolyears);
            $i = 0;
            $list_user = '';
            foreach ($schoolyears as $schoolyear) {
                foreach ($campaigns as $campaign) {
                    $compare = $this->compareCampaignANDSchoolyear($campaign,$schoolyear);
                    if ($compare != 0) {
                        $applicant_campaigns = $this->getUserListWithCampaign($campaign);
                        //$nb_element = count($applicant_campaigns);
                        foreach ($applicant_campaigns as $applicant) {
                            $list_user.=$applicant.", ";
                        }
                    }
                }
            }
            if ($list_user == '') {
                $list_user = 'EMPTY';
            } else {
                $taille = strlen($list_user);
                $list_user = substr($list_user, 0, $taille-2);
            }
        }

        if (!empty($groupEval)) {
            $list_user = "";
            $applicant_groupEval = $this->getUserListWithGroupsEval($groupEval);
            $i = 0;
            $nb_element = count($applicant_groupEval);
            if ($nb_element==0) {
                $list_user.="EMPTY";
            } else {
                foreach ($applicant_groupEval as $applicant) {
                    if (++$i === $nb_element)
                        $list_user .= $applicant;
                    elseif ($applicant != NULL)
                        $list_user .= $applicant.", ";
                }
            }
        }

        $eMConfig           = JComponentHelper::getParams('com_emundus');
        $showUniversities   = $eMConfig->get('showUniversities');
        $showNewsletter     = $eMConfig->get('showNewsletter');

        $query = 'SELECT DISTINCT(u.id), e.lastname, e.firstname, u.email, u.username,  espr.label as profile, ';

        if ($showNewsletter == 1)
            $query .= 'up.profile_value as newsletter, ';

        $query .= 'u.registerDate, u.lastvisitDate,  GROUP_CONCAT( DISTINCT esgr.label SEPARATOR "<br>") as groupe, ';

        if ($showUniversities == 1)
            $query .= 'cat.title as university,';

        $query .= 'u.activation as active,u.block as block
                    FROM #__users AS u
                    LEFT JOIN #__emundus_users AS e ON u.id = e.user_id
                    LEFT JOIN #__emundus_users_profiles AS eup ON e.user_id = eup.user_id
                    LEFT JOIN #__emundus_groups AS egr ON egr.user_id = u.id
                    LEFT JOIN #__emundus_setup_groups AS esgr ON esgr.id = egr.group_id
                    LEFT JOIN #__emundus_setup_profiles AS espr ON espr.id = e.profile
                    LEFT JOIN #__emundus_personal_detail AS epd ON u.id = epd.user
                    LEFT JOIN #__categories AS cat ON cat.id = e.university_id
                    LEFT JOIN #__user_profiles AS up ON ( u.id = up.user_id AND up.profile_key like "emundus_profiles.newsletter")';

        if (isset($programme) && !empty($programme) && $programme[0] != '%') {
            $query .= ' LEFT JOIN #__emundus_campaign_candidature AS ecc ON u.id = ecc.applicant_id
                        LEFT JOIN #__emundus_setup_campaigns as esc ON ecc.campaign_id=esc.id ';
        }

        if (isset($final_grade) && !empty($final_grade)) {
            $query .= 'LEFT JOIN #__emundus_final_grade AS efg ON u.id = efg.student_id ';
        }

        $query .= ' where 1=1 AND u.id NOT IN (1,62) ';

        if (isset($programme) && !empty($programme) && $programme[0] != '%') {
            $query .= ' AND ( esc.training IN ("'.implode('","', $programme).'")
                            OR u.id IN (
                                select _eg.user_id
                                from #__emundus_groups as _eg
                                left join #__emundus_setup_groups_repeat_course as _esgr on _esgr.parent_id=_eg.group_id
                                where _esgr.course IN ("'.implode('","', $programme).'")
                                )
                            )';
        }

        if (isset($group) && !empty($group) && $group[0] != '%')
            $query .= ' AND u.id IN( SELECT jeg.user_id FROM #__emundus_groups as jeg WHERE jeg.group_id IN ('.implode(',', $group).')) ';

        if (isset($institution) && !empty($institution) && $institution[0] != '%')
            $query .= ' AND u.id IN( SELECT jeu.user_id FROM #__emundus_users as jeu WHERE jeu.university_id IN ('.implode(',', $institution).')) ';

        if ($edit == 1) {
            $query.= ' u.id='.(int)$uid;
        } else {
            $and = true;
            /*var_dump($this->filts_details['profile']);
            if(isset($this->filts_details['profile']) && !empty($this->filts_details['profile'])){
                $query.= ' AND e.profile IN ('.implode(',', $this->filts_details['profile']).') ';
                $and = true;
            }*/
            if (isset($profile) && !empty($profile) && is_numeric($profile)) {
                $query.= ' AND e.profile = '.$profile;
                $and = true;
            }
            if (isset($oprofiles) && !empty($oprofiles) ) {
                $query.= ' AND eup.profile_id IN ("'.implode('","', $oprofiles).'")';
                $and = true;
            }
            if (isset($final_grade) && !empty($final_grade)) {
                if ($and) $query .= ' AND ';
                else { $and = true;  $query .='WHERE '; }

                $query.= 'efg.Final_grade = "'.$final_grade.'"';
                $and = true;
            }
            if (isset($search) && !empty($search)) {

                if ($and) {
                    $query .= ' AND ';
                } else {
                    $and = true;
                    $query .= ' ';
                }

                $q = '';
                foreach ($search as $str) {
                    $val = explode(': ', $str);

                    if ($val[0] == "ALL") {
                        $q .= ' OR e.lastname LIKE '.$db->Quote('%'.$val[1].'%').'
                        OR e.firstname LIKE '.$db->Quote('%'.$val[1].'%').'
                        OR u.email LIKE '.$db->Quote('%'.$val[1].'%').'
                        OR e.schoolyear LIKE '.$db->Quote('%'.$val[1].'%').'
                        OR u.username LIKE '.$db->Quote('%'.$val[1].'%').'
                        OR u.id = '.$db->Quote($val[1]);
                    }

                    if ($val[0] == "ID")
                        $q .= ' OR u.id = '.$db->Quote($val[1]);

                    if ($val[0] == "EMAIL")
                        $q .= ' OR u.email LIKE '.$db->Quote('%'.$val[1].'%');

                    if ($val[0] == "USERNAME")
                        $q .= ' OR u.username LIKE '.$db->Quote('%'.$val[1].'%');

                    if ($val[0] == "LAST_NAME")
                        $q .= ' OR e.lastname LIKE '.$db->Quote('%'.$val[1].'%');

                    if ($val[0] == "FIRST_NAME")
                        $q .= ' OR e.firstname LIKE '.$db->Quote('%'.$val[1].'%');

                }

                $q = substr($q, 3);
                $query .= '('.$q.')' ;
            }
            /*if(isset($schoolyears) &&  !empty($schoolyears)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                $query.= 'e.schoolyear="'.$schoolyears.'"';
            }*/
            if (isset($spam_suspect) &&  !empty($spam_suspect) && $spam_suspect == 1) {
                if ($and) {
                    $query .= ' AND ';
                } else {
                    $and = true;
                    $query .= ' ';
                }

                $query .= 'u.lastvisitDate="0000-00-00 00:00:00" AND TO_DAYS(NOW()) - TO_DAYS(u.registerDate) > 7';
            }

            if (!empty($list_user)) {
                if ($and) {
                    $query .= ' AND ';
                } else {
                    $and = true;
                    $query .= ' ';
                }

                if ($list_user == 'EMPTY')
                    $query .= 'u.id IN (null) ';
                else
                    $query .= 'u.id IN ( '.$list_user.' )';
            }

            if (isset($newsletter) &&  !empty($newsletter)) {
                if ($and) {
                    $query .= ' AND ';
                } else {
                    $query .=' ';
                }

                $query .= 'profile_value like "%'.$newsletter.'%"';
            }
        }

        $query .= " GROUP BY u.id ";
        return $query;
    }

    public function getUsers($limit_start = null, $limit = null) {
        $session = JFactory::getSession();

        if ($limit_start === null) {
            $limit_start = $session->get('limitstart');
        }
        if ($limit === null) {
            $limit = $session->get('limit');
        }

        // Lets load the data if it doesn't already exist
        try {

            $query = $this->_buildQuery();
            $query .= $this->_buildContentOrderBy();
            return $this->_getList($query, $limit_start, $limit);

        } catch(Exception $e) {
            throw new $e;
        }
    }

    public function getProfiles() {
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.published, caag.lft
        FROM #__emundus_setup_profiles esp
        INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
        where esp.status=1 AND esp.id > 1
        ORDER BY esp.acl_aro_groups, esp.label';
        $db->setQuery($query);
        return $db->loadObjectList('id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getProfilesByIDs($ids) {
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.published, caag.lft
        FROM #__emundus_setup_profiles esp
        INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
        WHERE esp.status=1 AND esp.id IN (' . implode(',', $ids) . ')
        ORDER BY caag.lft, esp.label';
        $db->setQuery($query);
        return $db->loadObjectList('id');
    }

    public function getEditProfiles() {
        $db = JFactory::getDBO();
        $current_user = JFactory::getUser();
        $current_group = 0;
        foreach ($current_user->groups as $group) {
            if ($group > $current_group) $current_group = $group;
        }
        $query ='SELECT id, label FROM #__emundus_setup_profiles WHERE '.$current_group.' >= acl_aro_groups GROUP BY id';
        $db->setQuery($query);
        return $db->loadObjectList('id');
    }

    public function getApplicantProfiles() {
        $db = JFactory::getDBO();
        $query ='SELECT * FROM #__emundus_setup_profiles WHERE published=1';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getUsersProfiles() {
        $user = JFactory::getUser();
        $uid = JRequest::getVar('rowid', $user->id, 'get','int');
        $db = JFactory::getDBO();
        $query = 'SELECT eup.profile_id FROM #__emundus_users_profiles eup WHERE eup.user_id='.$uid;
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getUserByEmail($email) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__users WHERE email like "'.$email.'"';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getEmundusUserByEmail($email) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_users WHERE email like "'.$email.'"';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getProfileIDByCampaignID($cid) {
        $db = JFactory::getDBO();
        $query = 'SELECT `profile_id` FROM `#__emundus_setup_campaigns` WHERE id='.$cid;
        $db->setQuery($query);
        return $db->loadResult();
    }

    public function getCurrentUserProfile($uid) {
        $db = JFactory::getDBO();
        $query = 'SELECT eu.profile FROM #__emundus_users eu WHERE eu.user_id='.$uid;
        $db->setQuery($query);
        return $db->loadResult();
    }

    public function changeCurrentUserProfile($uid, $pid) {
        $db = JFactory::getDBO();
        $query = 'UPDATE #__emundus_users SET profile ="'.(int)$pid.'" WHERE user_id='.(int)$uid;
        $db->setQuery($query);
        $db->execute() or die($db->getErrorMsg());
    }

    public function getUniversities() {
        $db = JFactory::getDBO();
        $query = 'SELECT c.id, c.title
        FROM #__categories as c
        WHERE c.published=1 AND c.extension like "com_contact"
        order by note desc,lft asc';
        $db->setQuery($query);
        return $db->loadObjectList('id');
    }

    public function getGroups() {
        $db = JFactory::getDBO();
        $query = 'SELECT esg.id, esg.label
        FROM #__emundus_setup_groups esg
        WHERE esg.published=1
        ORDER BY esg.label';
        $db->setQuery($query);
        return $db->loadObjectList('id');
    }

    public function getCampaigns() {
        $db = JFactory::getDBO();
        $query = 'SELECT sc.id, cc.applicant_id, sc.start_date, sc.end_date, sc.label, sc.year
        FROM #__emundus_setup_campaigns AS sc
        LEFT JOIN #__emundus_campaign_candidature AS cc ON cc.campaign_id = sc.id
        WHERE sc.published=1';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getCampaignsPublished() {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_campaigns AS sc WHERE sc.published=1 ORDER BY sc.start_date DESC, sc.label ASC';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getAllCampaigns() {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_campaigns AS sc ORDER BY sc.start_date DESC, sc.label ASC';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

   /* public function getAllOprofiles()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_profiles AS sp ORDER BY sp.start_date DESC, sc.label ASC';
        //echo str_replace('#_','jos',$query);
        $db->setQuery( $query );
        return $db->loadObjectList();
    }*/

    public function getCampaignsCandidature($aid = 0) {
        $db = JFactory::getDBO();
        $uid = ($aid!=0)?$aid:JRequest::getVar('rowid', null, 'GET', 'none', 0);
        $query = 'SELECT * FROM #__emundus_campaign_candidature AS cc  WHERE applicant_id='.$uid;
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getUserListWithSchoolyear($schoolyears) {
        $year = is_string($schoolyears)?$schoolyears:"'".implode("','", $schoolyears)."'";
        $db = JFactory::getDBO();
        $query = 'SELECT cc.applicant_id
        FROM #__emundus_campaign_candidature AS cc
        LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
        WHERE sc.published=1 AND sc.year IN ('.$year.') ORDER BY sc.year DESC';
        $db->setQuery($query);
        return $db->loadColumn();
    }

    public function getUserListWithCampaign($campaign) {
        /*$list_campaign ="";
        $i=0;
        $nb_element = count($campaign);
        foreach($campaign as $c){
            if(++$i === $nb_element){
                $list_campaign .= $c;
            }else{
                $list_campaign .= $c.", ";
            }
        }*/

        $db = JFactory::getDBO();
        if (!is_array($campaign)) {
            $query = 'SELECT cc.applicant_id
            FROM #__emundus_campaign_candidature AS cc
            LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
            WHERE sc.published=1 AND sc.id IN ('.$campaign.')';
        } else {
            $query = 'SELECT cc.applicant_id
            FROM #__emundus_campaign_candidature AS cc
            LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
            WHERE sc.published=1 AND sc.id IN ('.implode(",", $campaign).')';
        }
        $db->setQuery($query);
        return $db->loadColumn();
    }

    public function compareCampaignANDSchoolyear($campaign,$schoolyear) {
        $db = JFactory::getDBO();
        $query = 'SELECT COUNT(*)
        FROM #__emundus_setup_campaigns AS sc
        WHERE id='.$campaign.' AND year="'.$schoolyear.'"';
        $db->setQuery($query);
        return $db->loadResult();
    }

    public function getCurrentCampaign() {
        return EmundusHelperFilters::getCurrentCampaign();
    }

    public function getCurrentCampaignsID() {
        return EmundusHelperFilters::getCurrentCampaignsID();
    }

    public function getCurrentCampaigns() {
        $config = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
		$now = JFactory::getDate()->setTimezone($timezone);

        $db = JFactory::getDBO();
        $query = 'SELECT sc.id, sc;label
        FROM #__emundus_setup_campaigns AS sc
        WHERE sc.published=1 AND end_date > "'.$now.'"';
        $db->setQuery($query);
        return $db->loadColumn();
    }

    public function getProgramme() {
        try {
            $db = JFactory::getDBO();
            $query = 'SELECT sp.code, sp.label FROM #__emundus_setup_programmes AS sp ORDER BY sp.label ASC';
            $db->setQuery( $query );
            return $db->loadAssocList();

        } catch(Exception $e) {
            return null;
        }
    }

    public function getNewsletter() {
        $db = JFactory::getDBO();
        $query = 'SELECT user_id, profile_value
        FROM #__user_profiles
        WHERE profile_key = "emundus_profile.newsletter"';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getGroupEval() {
        $db = JFactory::getDBO();
        $query = 'SELECT esg.id, eu.user_id, eu.firstname, eu.lastname, u.email, esg.label
                FROM #__emundus_setup_groups as esg
                LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
                LEFT JOIN #__emundus_users as eu on eu.user_id=eg.user_id
                LEFT JOIN #__users as u on u.id=eu.user_id
                WHERE esg.published=1';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getGroupsEval() {
        $db = JFactory::getDBO();
        $query = 'SELECT ege.id, ege.applicant_id, ege.user_id, ege.group_id, esg.label
        FROM #__emundus_groups_eval as ege
        LEFT JOIN #__emundus_setup_groups as esg ON esg.id = ege.group_id
        WHERE esg.published=1';
        $db->setQuery($query);
        return $db->loadObjectList('applicant_id');
    }

    public function getUserListWithGroupsEval($groups) {
        $db = JFactory::getDBO();
        $query = 'SELECT eg.user_id
        FROM #__emundus_groups as eg
        LEFT JOIN #__emundus_setup_groups as esg ON esg.id=eg.group_id
        WHERE esg.published=1 AND eg.group_id='.$groups;
        $db->setQuery($query);
        return $db->loadColumn();
    }

    public function getUsersGroups() {
        $db = JFactory::getDBO();
        $query = 'SELECT eg.user_id, eg.group_id
        FROM #__emundus_groups eg';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getSchoolyears() {
        $db = JFactory::getDBO();
        $query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1 GROUP BY schoolyear';
        $db->setQuery($query);
        return $db->loadColumn();
    }

    public function getTotal() {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    public function getPagination() {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $session = JFactory::getSession();
            $this->_pagination = new JPagination($this->getTotal(), $session->get('limitstart'), $session->get('limit'));

        }
        return $this->_pagination;
    }

    /**
     * Method to get the registration form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param   array   $data       An optional array of data for the form to interogate.
     * @param   boolean $loadData   True if the form is to load its own data (default case), false if not.
     * @return  JForm   A JForm object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = JForm::getInstance('com_emundus.registration', JPATH_COMPONENT.DS.'models'.DS.'forms'.DS.'registration.xml', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     * @since   1.6
     */
    protected function loadFormData() {
        return $this->getData();
    }

    /**
     * Method to get the registration form data.
     *
     * The base form data is loaded and then an event is fired
     * for users plugins to extend the data.
     *
     * @return  mixed       Data object on success, false on failure.
     * @since   1.6
     */
    public function getData() {
        if ($this->data === null) {

            $this->data = new stdClass();
            $app    = JFactory::getApplication();
            $params = JComponentHelper::getParams('com_users');

            // Override the base user data with any data in the session.
            $temp = (array)$app->getUserState('com_users.registration.data', array());
            foreach ($temp as $k => $v) {
                $this->data->$k = $v;
            }

            // Get the groups the user should be added to after registration.
            $this->data->groups = array();

            // Get the default new user group, Registered if not specified.
            $system = $params->get('new_usertype', 2);

            $this->data->groups[] = $system;

            // Unset the passwords.
            unset($this->data->password1);
            unset($this->data->password2);

            // Get the dispatcher and load the users plugins.
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('user');

            // Trigger the data preparation event.
            $results = $dispatcher->trigger('onContentPrepareData', array('com_users.registration', $this->data));

            // Check for errors encountered while preparing the data.
            if (count($results) && in_array(false, $results, true)) {
                $this->setError($dispatcher->getError());
                $this->data = false;
            }
        }

        return $this->data;
    }

	/** Adds a user to Joomla as well as the eMundus tables.
	 * @param $user
	 * @param $other_params
	 *
	 * @return array|bool
	 */
    public function adduser($user, $other_params) {

    	$db = JFactory::getDBO();

        // add to jos_emundus_users; jos_users; jos_emundus_groups; jos_users_profiles; jos_users_profiles_history
        try {

            if (!$user->save()) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_EMUNDUS_USERS_CAN_NOT_SAVE_USER').'<BR />'.$user->getError(), 'error');
                $res = array('msg' => $user->getError());
                return $res;
            } else {
                $query = 'UPDATE `#__users` SET block=0 WHERE id='.$user->id;
                $db->setQuery($query);
                $db->execute();

                $this->addEmundusUser($user->id, $other_params);
                return $user->id;
            }

        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function addEmundusUser($user_id, $params) {

        $db = JFactory::getDBO();
        $config     = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
        $now = JFactory::getDate()->setTimezone($timezone);

	    JPluginHelper::importPlugin('emundus');
	    $dispatcher = JEventDispatcher::getInstance();

        $firstname = $params['firstname'];
        $lastname = $params['lastname'];
        $profile = $params['profile'];
        $oprofiles = $params['em_oprofiles'];
        $groups = $params['em_groups'];
        $campaigns = $params['em_campaigns'];
        $news = $params['news'];
        $univ_id = $params['univ_id'];

        if(!empty($params['id_ehesp'])){
            $id_ehesp = $params['id_ehesp'];
        }

        $dispatcher->trigger('onBeforeSaveEmundusUser', [$user_id, $params]);
        $dispatcher->trigger('callEventHandler', ['onBeforeSaveEmundusUser', ['user_id' => $user_id, 'params' => $params]]);

        if(!empty($id_ehesp)){
            $query = "INSERT INTO `#__emundus_users` (id, user_id, registerDate, firstname, lastname, profile, schoolyear, disabled, disabled_date, cancellation_date, cancellation_received, university_id,id_ehesp) VALUES ('',".$user_id.",'".$now."',".$db->quote($firstname).",".$db->quote($lastname).",".$profile.",'',0,'','','','".$univ_id."','".$id_ehesp."')";
            $db->setQuery($query);
            $db->execute();
        }
        elseif (empty($univ_id)) {
            $query = "INSERT INTO `#__emundus_users` (id, user_id, registerDate, firstname, lastname, profile, schoolyear, disabled, disabled_date, cancellation_date, cancellation_received, university_id) VALUES ('',".$user_id.",'".$now."',".$db->quote($firstname).",".$db->quote($lastname).",".$profile.",'',0,'','','',0)";
            $db->setQuery($query);
            $db->execute();
        }
        else {
            $query = "INSERT INTO `#__emundus_users` (id, user_id, registerDate, firstname, lastname, profile, schoolyear, disabled, disabled_date, cancellation_date, cancellation_received, university_id) VALUES ('',".$user_id.",'".$now."',".$db->quote($firstname).",".$db->quote($lastname).",".$profile.",'',0,'','','','".$univ_id."')";
            $db->setQuery($query);
            $db->execute();
        }
	    $dispatcher->trigger('onAfterSaveEmundusUser', [$user_id, $params]);
        $dispatcher->trigger('callEventHandler', ['onAfterSaveEmundusUser', ['user_id' => $user_id, 'params' => $params]]);

        if (!empty($groups)) {
            foreach ($groups as $group) {
	            $dispatcher->trigger('onBeforeAddUserToGroup', [$user_id, $group]);
                $dispatcher->trigger('callEventHandler', ['onBeforeAddUserToGroup', ['user_id' => $user_id, 'group' => $group]]);

                $query = "INSERT INTO `#__emundus_groups` VALUES ('',".$user_id.",".$group.")";
                $db->setQuery($query);
                $db->execute();

	            $dispatcher->trigger('onAfterAddUserToGroup', [$user_id, $group]);
                $dispatcher->trigger('callEventHandler', ['onAfterAddUserToGroup', ['user_id' => $user_id, 'group' => $group]]);
            }
        }

        if (!empty($campaigns)) {
            $connected = JFactory::getUser()->id;
            foreach ($campaigns as $campaign) {
	            $dispatcher->trigger('onBeforeCampaignCandidature', [$user_id, $connected, $campaign]);
                $dispatcher->trigger('callEventHandler', ['onBeforeCampaignCandidature', ['user_id' => $user_id, 'connected' => $connected, 'campaign' => $campaign]]);

                $query = 'INSERT INTO `#__emundus_campaign_candidature` (`applicant_id`, `user_id`, `campaign_id`, `fnum`)
                                    VALUES ('.$user_id.', '. $connected .','.$campaign.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'), LPAD(`applicant_id`, 7, \'0\')))';
                $db->setQuery($query);
                $db->execute();

	            $dispatcher->trigger('onAfterCampaignCandidature', [$user_id, $connected, $campaign]);
                $dispatcher->trigger('callEventHandler', ['onAfterCampaignCandidature', ['user_id' => $user_id, 'connected' => $connected, 'campaign' => $campaign]]);

            }
        }

	    $dispatcher->trigger('onBeforeAddUserProfile', [$user_id, $profile]);
        $dispatcher->trigger('callEventHandler', ['onBeforeAddUserProfile', ['user_id' => $user_id, 'profile' => $profile]]);

        $query="INSERT INTO `#__emundus_users_profiles`
                        VALUES ('','".$now."',".$user_id.",".$profile.",'','')";
        $db->setQuery($query);
        $db->execute() or die($db->getErrorMsg());

	    $dispatcher->trigger('onAfterAddUserProfile', [$user_id, $profile]);
        $dispatcher->trigger('callEventHandler', ['onAfterAddUserProfile', ['user_id' => $user_id, 'profile' => $profile]]);


        if (!empty($oprofiles)) {
            foreach ($oprofiles as $profile) {
	            $dispatcher->trigger('onBeforeAddUserProfile', [$user_id, $profile]);
                $dispatcher->trigger('callEventHandler', ['onBeforeAddUserProfile', ['user_id' => $user_id, 'profile' => $profile]]);

                $query = "INSERT INTO `#__emundus_users_profiles`
                                VALUES ('','".$now."',".$user_id.",".$profile.",'','')";
                $db->setQuery($query);
                $db->execute();

	            $dispatcher->trigger('onAfterAddUserProfile', [$user_id, $profile]);
                $dispatcher->trigger('callEventHandler', ['onAfterAddUserProfile', ['user_id' => $user_id, 'profile' => $profile]]);

                $query = 'SELECT `acl_aro_groups` FROM `#__emundus_setup_profiles` WHERE id='.(int)$profile;
                $db->setQuery($query);
                $group = $db->loadColumn();

                JUserHelper::addUserToGroup($user_id, $group[0]);
            }
        }

        if ($news == 1) {
            $query = "INSERT INTO `#__user_profiles` (`user_id`, `profile_key`, `profile_value`, `ordering`)
                            VALUES (".$user_id.", 'emundus_profile.newsletter', '1', 4)";
            $db->setQuery($query);
            $db->execute() or die($db->getErrorMsg());
        }

        $query = "INSERT INTO `#__user_profiles`
                        VALUES (".$user_id.",'emundus_profile.firstname', ".$db->Quote('"'.$firstname.'"').", '2'),
                               (".$user_id.",'emundus_profile.lastname', ".$db->Quote('"'.$lastname.'"').", '1')";
        $db->setQuery($query);
        $db->execute() or die($db->getErrorMsg());
    }

    public function found_usertype($acl_aro_groups) {
        $db = JFactory::getDBO();
        $query="SELECT title FROM #__usergroups WHERE id=".$acl_aro_groups;
        $db->setQuery($query);
        return $db->loadResult();
    }

    public function getDefaultGroup($pid) {
        $db = JFactory::getDBO();
        $query="SELECT acl_aro_groups FROM #__emundus_setup_profiles WHERE id=".$pid;
        $db->setQuery($query);
        return $db->loadColumn();
    }

    public function login($uid) {
        $app     = JFactory::getApplication();
        $db      = JFactory::getDBO();
        $session = JFactory::getSession();

        $instance   = JFactory::getUser($uid);

       // $userarray = array();
        //$userarray['username'] = $instance->username;
        //$userarray['password'] = $instance->password;
        //$app->login($userarray);

        $instance->set('guest', 0);

        // Register the needed session variables
        $session->set('user', $instance);

        // Check to see the the session already exists.
        $app->checkSession();

        // Update the user related fields for the Joomla sessions table.
        $query = 'UPDATE #__session
                    SET guest='.$db->quote($instance->get('guest')).',
                        username = '.$db->quote($instance->get('username')).',
                        userid = '.(int) $instance->get('id').'
                        WHERE session_id like '.$db->quote($session->getId());
        $db->setQuery($query);
        $db->execute();

        // Hit the user last visit field
        $instance->setLastVisit();

        // Trigger OnUserLogin
        JPluginHelper::importPlugin('user', 'emundus');
        $dispatcher = JEventDispatcher::getInstance();
        $options = array('action' => 'core.login.site', 'remember' => false);

        $dispatcher->trigger( 'onUserLogin', $instance );
        $dispatcher->trigger('callEventHandler', ['onUserLogin', ['instance' => $instance]]);

        return $instance;

    }


	/**
	 *
	 * PLAIN LOGIN
	 *
	 * @param     $credentials
	 * @param int $redirect
	 *
	 * @return bool|JException
	 * @throws Exception
	 */
    public function plainLogin($credentials, $redirect = 1) {
        // Get the application object.
        $app = JFactory::getApplication();

        // Get the log in credentials.
        /*   $credentials = array();
        $credentials['username'] = $this->_user;
        $credentials['password'] = $this->_passw;*/

        $options = array();
        $options['redirect'] = $redirect;
        return $app->login($credentials, $options);

    }

	/**
	 *
	 * ENCRYPT LOGIN
	 *
	 * @param $credentials
	 * @param int $redirect
	 *
	 * @throws Exception
	 */
    public function encryptLogin($credentials, $redirect = 1) {
        // Get the application object.
        $app = JFactory::getApplication();

        $db =& JFactory::getDBO();
        $query = 'SELECT `id`, `username`, `password`'
            . ' FROM `#__users`'
            . ' WHERE username=' . $db->Quote( $credentials['username'] )
            . '   AND password=' . $db->Quote( $credentials['password'] )
        ;
        $db->setQuery($query);
        $result = $db->loadObject();

        if ($result) {
            JPluginHelper::importPlugin('user');

            $options = array();
            $options['action'] = 'core.login.site';
            $options['redirect'] = $redirect;

            $response['username'] = $result->username;
            $app->triggerEvent('onUserLogin', array((array)$response, $options));
        }

    }

    /*
     * Function to get fnums associated to users groups or user
     * @param   $action_id  int     Allowed action ID
     * @param   $crud       array   Array of type access (create, read, update, delete)
     */
    public function getFnumsAssoc($action_id, $crud = array()) {
        $current_user = JFactory::getUser();
        $crud_where = '';
        foreach ($crud as $key=>$value) {
            $crud_where .= ' AND '.$key.'='.$value;
        }

        try {
            $db = $this->getDbo();
            $query = 'SELECT DISTINCT fnum
                        FROM #__emundus_group_assoc
                        WHERE action_id = '.$action_id.' '.$crud_where.' AND group_id IN ('.implode(',', $current_user->groups).')';
            $db->setQuery($query);
            $fnum_1 = $db->loadColumn();

            $query = 'SELECT DISTINCT fnum
                        FROM #__emundus_users_assoc
                        WHERE action_id = '.$action_id.' '.$crud_where.' AND user_id = '.$current_user->id;
            $db->setQuery($query);
            $fnum_2 = $db->loadColumn();

            return (count($fnum_1>0) && count($fnum_2))?array_merge($fnum_1, $fnum_2):((count($fnum_1)>0)?$fnum_1:$fnum_2);
        } catch(Exception $e) {
            throw $e;
        }
    }


    /*
     * Function to get fnums associated to group
     * @param   $group_id   int     Allowed action ID
     * @param   $action_id  int     Allowed action ID
     * @param   $crud       array   Array of type access (create, read, update, delete)
     */
    public function getFnumsGroupAssoc($group_id, $action_id, $crud = array()) {
        $crud_where = '';
        foreach ($crud as $key=>$value) {
            $crud_where .= ' AND '.$key.'='.$value;
        }

        try {
            $db = $this->getDbo();
            $query = 'SELECT DISTINCT fnum
                        FROM #__emundus_group_assoc
                        WHERE action_id = '.$action_id.' '.$crud_where.' AND group_id ='.$group_id;
            $db->setQuery($query);
            $fnum = $db->loadColumn();
            return $fnum;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /*
 * Function to get fnums associated to user
 * @param   $action_id  int     Allowed action ID
 * @param   $crud       array   Array of type access (create, read, update, delete)
 */
    public function getFnumsUserAssoc($action_id, $crud = array()) {
        $current_user = JFactory::getUser();
        $crud_where = '';
        foreach ($crud as $key=>$value) {
            $crud_where .= ' AND '.$key.'='.$value;
        }

        try {
            $db = $this->getDbo();
            $query = 'SELECT DISTINCT fnum
                        FROM #__emundus_users_assoc
                        WHERE action_id = '.$action_id.' '.$crud_where.' AND user_id = '.$current_user->id;
            $db->setQuery($query);
            $fnum = $db->loadColumn();

            return $fnum;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /*
     * Function to get Evaluators Infos for the mailing evaluators
     */
    public function getEvalutorByFnums($fnums) {
        include_once(JPATH_SITE.'/components/com_emundus/models/files.php');
        $files = new EmundusModelFiles;

        $fnums_info = $files->getFnumsInfos($fnums);

        $training = array();
        foreach ($fnums_info as $key => $value) {
            $training[] = $value['training'];
        }
        try {
            $db = $this->getDbo();
            // All manually associated applicant to user with action evaluation set to create=1
            $query = 'SELECT DISTINCT u.id, u.name, u.email
                        FROM #__emundus_users_assoc eua
                        LEFT JOIN #__users u on u.id=eua.user_id
                        WHERE eua.action_id=5 AND eua.c=1 AND eua.fnum in ("'.implode('","', $fnums).'")';

            $db->setQuery($query);
            $user_assoc = $db->loadAssocList();

            // get group with evaluation access
            $query = 'SELECT DISTINCT group_id
                        FROM #__emundus_group_assoc
                        WHERE action_id=5 AND c=1 AND fnum IN ("'.implode('","', $fnums).'")';
            $db->setQuery($query);
            $groups_1 = $db->loadColumn();

            $groups_2 = array();
            if (count($training) > 0) {
                // get group with evaluation access
                $query = 'SELECT DISTINCT ea.group_id
                            FROM #__emundus_acl ea
                            LEFT JOIN #__emundus_setup_groups esg ON esg.id = ea.group_id
                            LEFT JOIN #__emundus_setup_groups_repeat_course esgrc ON esgrc.parent_id=esg.id
                            WHERE ea.action_id=5 AND ea.c=1 AND esgrc.course IN ("'.implode('","', $training).'")';
                $db->setQuery($query);
                $groups_2 = $db->loadColumn();
            }

            $groups = (count($groups_1>0) && count($groups_2))?array_merge($groups_1, $groups_2):((count($groups_1)>0)?$groups_1:$groups_2);

            $group_assoc = array();
            if (count($groups) > 0) {
                // All user from groups
                $query = 'SELECT DISTINCT u.id, u.name, u.email
                            FROM #__emundus_groups eg
                            LEFT JOIN #__users u on u.id=eg.user_id
                            WHERE eg.group_id in ("'.implode('","', $groups).'")';

                $db->setQuery($query);
                $group_assoc = $db->loadAssocList();
            }

            return (count($user_assoc>0) && count($group_assoc))?array_merge($user_assoc, $group_assoc):((count($user_assoc)>0)?$user_assoc:$group_assoc);
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function getActions($actions = '') {
        //$usersGroups = JFactory::getUser()->groups;
        $usersGroups = $this->getUserGroups(JFactory::getUser()->id);

        $groups = array();
        foreach ($usersGroups as $key => $value) {
            $groups[] = $key;
        }

        $query = 'SELECT distinct act.*
                    FROM #__emundus_setup_actions as act
                    LEFT JOIN #__emundus_acl as acl on acl.action_id = act.id
                    WHERE act.status >= 1
                    AND acl.group_id in ('. implode(',', $groups) . ') AND ((acl.c = 1) OR (acl.u = 1))
                    ORDER BY act.ordering';
        $db = $this->getDbo();
        try {
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    // update actions rights for a group
    public function setGroupRight($id, $action, $value) {
        $db = $this->getDbo();
        try {
            $query = 'UPDATE `#__emundus_acl` SET `'.$action.'`='.$value.' WHERE `id`='.$id;
            $db->setQuery($query);
            return $db->execute();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

	/**
	 * @param $gname
	 * @param $gdesc
	 * @param $actions
	 * @param $progs
	 *
	 * @return bool|mixed|null
	 *
	 * @since version
	 */
    public function addGroup($gname, $gdesc, $actions, $progs, $returnGid = false) {
        $db = $this->getDbo();
        $query = "insert into #__emundus_setup_groups (`label`,`description`, `published`) values (".$db->quote($gname).", ".$db->quote($gdesc).", 1)";

        try {
            $db->setQuery($query);
            $db->execute();
            $gid = $db->insertid();
            $str = "";
        } catch(Exception $e) {
	        JLog::add('Error on adding group: '.$e->getMessage().' at query -> '.$query, JLog::ERROR, 'com_emundus');
	        return null;
        }

        foreach ($progs as $prog) {
            $str .= "($gid, '$prog'),";
        }
        $str = rtrim($str, ",");
        $query = "insert into #__emundus_setup_groups_repeat_course (`parent_id`, `course`) values $str";

        try {
	        $db->setQuery($query);
	        $db->execute();
	        $str = "";
        } catch(Exception $e) {
	        JLog::add('Error on adding group: '.$e->getMessage().' at query -> '.$query, JLog::ERROR, 'com_emundus');
	        return null;
        }

        if (!empty($actions)) {
	        foreach ($actions as $action) {
		        $act = (array) $action;
		        $str .= "($gid, ".implode(',', $act)."),";
	        }
	        $str   = rtrim($str, ",");
	        $query = "insert into #__emundus_acl (`group_id`, `action_id`, `c`, `r`, `u`, `d`) values $str";
	        $db->setQuery($query);

	        try {
	        	if (!$returnGid) {
			        return $db->execute();
		        }
	        } catch (Exception $e) {
		        JLog::add('Error on adding group: '.$e->getMessage().' at query -> '.$query, JLog::ERROR, 'com_emundus');
		        return null;
	        }
        }

        require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'actions.php');
        $m_actions = new EmundusModelActions();
        $m_actions->syncAllActions(false);

        return $gid;
    }

    public function changeBlock($users, $state) {

        try {
            $db = $this->getDbo();
            foreach ($users as $uid) {
                $uid = intval($uid);
                $query = "UPDATE #__users SET block = ".$state." WHERE id =". $uid;

                $db->setQuery($query);
                $db->execute();
                if ($state == 0) {
	                $db->setQuery('UPDATE #__emundus_users SET disabled  = '.$state.' WHERE user_id = '.$uid);
                } else {
	                $db->setQuery('UPDATE #__emundus_users SET disabled  = '.$state.', disabled_date = NOW() WHERE user_id = '.$uid);
                }

                $res = $db->execute();
	            EmundusModelLogs::log(JFactory::getUser()->id, $uid, null, 20, 'u', 'COM_EMUNDUS_ADD_USER_UPDATE');
            }

            return $res;

        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function changeActivation($users, $state) {

        try {
            $db = $this->getDbo();
            foreach ($users as $uid) {
                $uid = intval($uid);
                $query = "UPDATE #__users SET activation = ".$state." WHERE id =". $uid;

                $db->setQuery($query);
                $res = $db->execute();

                EmundusModelLogs::log(JFactory::getUser()->id, $uid, null, 20, 'u', 'COM_EMUNDUS_ADD_USER_UPDATE');
            }

            return $res;

        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
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
	public function createParam($param, $user_id) {

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

    public function getNonApplicantId($users) {
        try {
            $db = $this->getDbo();
            $db->setQuery("select eu.user_id from #__emundus_users as eu left join #__emundus_setup_profiles as esp on esp.id = eu.profile WHERE esp.published != 1 and eu.user_id in (".implode(',',$users).")");
            $res = $db->loadAssocList();
            return $res;

        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function affectToGroups($users, $groups) {
        try {
            if (count($users) > 0) {
                $db = $this->getDbo();
                $str = "";

                foreach ($users as $user) {
                    foreach ($groups as $gid) {
                        $str .= "(".$user['user_id'].", $gid),";
                    }
                }
                $str = rtrim($str, ",");

                $query = "insert into #__emundus_groups (`user_id`, `group_id`) values $str";

                $db->setQuery($query);
                $res = $db->execute();
                return $res;
            } else
                return 0;

        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getUserInfos($uid) {
        try {
            $query = 'select u.username as login, u.email, eu.firstname, eu.lastname, eu.profile, eu.university_id, up.profile_value as newsletter
                      from #__users as u
                      left join #__emundus_users as eu on eu.user_id = u.id
                      left join #__user_profiles as up on (up.user_id = u.id and up.profile_key like "emundus_profile.newsletter")
                      where u.id = ' .$uid;
            //var_dump($query);die;
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssoc();
        } catch(Exception $e) {
            return false;
        }
    }


    // Get a list of user IDs that are currently connected
    public function getOnlineUsers() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("userid")
            ->from("#__session");

        $db->setQuery($query);
        try {
            return $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('Error getting online users in model/users at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }

    }

    // Get groups of user
    public function getUserGroups($uid, $return = 'AssocList') {
        try {
            $query = "SELECT esg.id, esg.label
                      from #__emundus_groups as g
                      left join #__emundus_setup_groups as esg on g.group_id = esg.id
                      where g.user_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);
            if ($return == 'Column') {
	            return $db->loadColumn();
            } else {
	            return $db->loadAssocList('id', 'label');
            }
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * getUserGroupsProgramme
     *
     * @param  mixed $uid
     * @return array
     */
    public function getUserGroupsProgramme(int $uid) : array {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName('esgc.course'))
            ->from($db->quoteName('#__emundus_groups', 'g'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups','esg').' ON '.$db->quoteName('g.group_id').' = '.$db->quoteName('esg.id'))
            ->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course','esgc').' ON '.$db->quoteName('esgc.parent_id').' = '.$db->quoteName('esg.id'))
            ->where($db->quoteName('g.user_id') . ' = ' . $uid);

        $db->setQuery($query);
        try {
            return $db->loadColumn();
        } catch(Exception $e) {
            return [];
        }
    }

    // get user ACL
    public function getUserACL($uid = null, $fnum = null) {
        try {
            $user = JFactory::getSession()->get('emundusUser');
            if (is_null($uid)) {
            	$uid = $user->id;
            }
            $acl = array();
            if ($fnum === null) {
                $query = "SELECT esc.training as course, eua.action_id, eua.c, eua.r, eua.u, eua.d, g.group_id
                        FROM #__emundus_users_assoc AS eua
                        LEFT JOIN #__emundus_campaign_candidature AS ecc on ecc.fnum = eua.fnum
                        LEFT JOIN #__emundus_setup_campaigns AS esc on esc.id = ecc.campaign_id
                        LEFT JOIN #__emundus_groups as g on g.user_id = eua.user_id
                        WHERE eua.user_id = " .$uid;
                $db = $this->getDbo();
                $db->setQuery($query);
                $userACL =  $db->loadAssocList();

                if (count($userACL) > 0) {
                    foreach ($userACL as $value) {
                        if (isset($acl[$value['action_id']])) {
                            $acl[$value['action_id']]['c'] = max($acl[$value['action_id']]['c'],$value['c']);
                            $acl[$value['action_id']]['r'] = max($acl[$value['action_id']]['r'],$value['r']);
                            $acl[$value['action_id']]['u'] = max($acl[$value['action_id']]['d'],$value['u']);
                            $acl[$value['action_id']]['d'] = max($acl[$value['action_id']]['d'],$value['d']);
                        } else {
                            $acl[$value['action_id']]['c'] = $value['c'];
                            $acl[$value['action_id']]['r'] = $value['r'];
                            $acl[$value['action_id']]['u'] = $value['u'];
                            $acl[$value['action_id']]['d'] = $value['d'];
                        }
                    }
                }
                if (!empty($user->emGroups)) {
                    $query = "SELECT esgc.course, acl.action_id, acl.c, acl.r, acl.u, acl.d, acl.group_id
                        FROM #__emundus_setup_groups_repeat_course AS esgc
                        LEFT JOIN #__emundus_acl AS acl ON acl.group_id = esgc.parent_id
                        WHERE acl.group_id in (".implode(',', $user->emGroups).")";
                    $db = $this->getDbo();
                    $db->setQuery($query);
                    $userACL =  $db->loadAssocList();
                    if (count($userACL) > 0) {
                        foreach ($userACL as $value) {
                            if (isset($acl[$value['action_id']])) {
                                $acl[$value['action_id']]['c'] = max($acl[$value['action_id']]['c'],$value['c']);
                                $acl[$value['action_id']]['r'] = max($acl[$value['action_id']]['r'],$value['r']);
                                $acl[$value['action_id']]['u'] = max($acl[$value['action_id']]['d'],$value['u']);
                                $acl[$value['action_id']]['d'] = max($acl[$value['action_id']]['d'],$value['d']);
                            } else {
                                $acl[$value['action_id']]['c'] = $value['c'];
                                $acl[$value['action_id']]['r'] = $value['r'];
                                $acl[$value['action_id']]['u'] = $value['u'];
                                $acl[$value['action_id']]['d'] = $value['d'];
                            }
                        }
                    }
                }

                return $acl;
            } else {
                $db = $this->getDbo();
                $query = "SELECT eua.action_id, eua.c, eua.r, eua.u, eua.d
                        FROM #__emundus_users_assoc AS eua
                        WHERE fnum like ".$db->quote($fnum)."  and  eua.user_id = " .$uid;
                $db->setQuery($query);
                return $db->loadAssocList();
            }

        } catch(Exception $e) {
            return false;
        }
    }

    // get programme associated to user groups
    public function getUserGroupsProgrammeAssoc($uid) {
        try {
            $query = "SELECT DISTINCT (esgc.course)
                      FROM #__emundus_groups as g
                      LEFT JOIN #__emundus_setup_groups AS esg ON g.group_id = esg.id
                      LEFT JOIN #__emundus_setup_groups_repeat_course AS esgc ON esgc.parent_id=esg.id
                      WHERE g.user_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadColumn();
        } catch(Exception $e) {
            return false;
        }
    }

    /*
     *   Get application fnums associated to a groups
     *   @param gid     array of groups ID
     *   @return array
    */
    public function getApplicationsAssocToGroups($gid) {

        if (count($gid) == 0) {
            return array();
        }
        try {
            $query = 'SELECT DISTINCT (ga.fnum)
                      FROM #__emundus_group_assoc as ga
                      WHERE ga.group_id IN ('.implode(',', $gid).')';
            $db = $this->getDbo();
            $db->setQuery($query);

            return $db->loadColumn();
        } catch(Exception $e) {
            return false;
        }
    }


    // get applicants associated to a user
    public function getApplicantsAssoc($uid) {
        try {
            $query = "SELECT DISTINCT (eua.fnum)
                      FROM #__emundus_users_assoc as eua
                      WHERE eua.user_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);

            return $db->loadColumn();
        } catch(Exception $e) {
            return false;
        }
    }

    public function getUserCampaigns($uid) {
        try {
            $query = "select esc.id, esc.label
                      from #__emundus_setup_campaigns as esc
                      left join #__emundus_campaign_candidature as ecc on ecc.campaign_id = esc.id
                      where ecc.applicant_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList('id', 'label');
        } catch(Exception $e) {
            return false;
        }
    }

    public function getUserOprofiles($uid) {
        try {
            $query = "select esp.id, esp.label
                      from #__emundus_setup_profiles as esp
                      left join #__emundus_users_profiles as eup on eup.profile_id = esp.id
                      where eup.user_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList('id', 'label');
        } catch(Exception $e) {
            return false;
        }
    }

    public function countUserEvaluations($uid) {
        try {
            $query = "select count(*) from #__emundus_evaluations
                      where user = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadResult();
        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

	/**
	 * @param $uid Int User id
	 * @param $pid Int Profile id
	 *
	 * @return bool
	 *
	 * @since version
	 */
    public function addProfileToUser($uid, $pid) {
        $config     = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
        $now = JFactory::getDate()->setTimezone($timezone);

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'))
	        ->from($db->quoteName('#__emundus_users_profiles'))
	        ->where($db->quoteName('user_id').' = '.$uid.' AND '.$db->quoteName('profile_id').' = '.$pid);
	    $db->setQuery($query);
	    try {
		    if (!empty($db->loadResult())) {
		    	// User already has the profile.
		    	return true;
		    }
	    } catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
	    }

        $query = "INSERT INTO `#__emundus_users_profiles` VALUES ('','".$now."',".$uid.",".$pid.",'','')";
        $db->setQuery($query);
        try {
	        $db->execute();
        } catch(Exception $e) {
	        error_log($e->getMessage(), 0);
	        return false;
        }

        $query = 'SELECT `acl_aro_groups` FROM `#__emundus_setup_profiles` WHERE id='.$pid;
        $db->setQuery($query);
	    try {
		    $group = $db->loadResult();
	    } catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
	    }

        return JUserHelper::addUserToGroup($uid, $group);
    }


    public function editUser($user) {

        $u = JFactory::getUser($user['id']);

        if (!$u->bind($user)) {
            return array('msg' => $u->getError());
        }
        if (!$u->save()) {
            return array('msg' =>$u->getError());
        }

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__emundus_users'))
            ->set('firstname = ' . $db->quote($user['firstname']))
            ->set('lastname = ' . $db->quote($user['lastname']))
            ->set('profile = ' . $db->quote((int)$user['profile']));

        if (!empty($user['university_id'])) {
            $query->set('university_id = ' . $db->quote($user['university_id']));
        }

        $query->where('user_id = ' . $db->quote($user['id']));

        $db->setQuery($query);

	    try {
            $db->execute();
	    } catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
	    }

        $db->setQuery('UPDATE #__user_profiles SET profile_value = '.$db->Quote($user['firstname']).' WHERE user_id = '.(int)$user['id'] .' and profile_key like "emundus_profile.firstname"');
	    try {
		    $db->execute();
	    } catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
	    }

        $db->setQuery('UPDATE #__user_profiles SET profile_value = '.$db->Quote($user['lastname']).' WHERE user_id = '.(int)$user['id'] .' and profile_key like "emundus_profile.lastname"');
	    try {
		    $db->execute();
	    } catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
	    }

        $db->setQuery('delete from #__emundus_groups where user_id = '. (int)$user['id']);
	    try {
		    $db->execute();
	    } catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
	    }

        $db->setQuery('delete from #__user_profiles where user_id = ' .(int)$user['id'].' and profile_key like "emundus_profile.newsletter"');
	    try {
		    $db->execute();
	    } catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
	    }

        $db->setQuery('delete from #__emundus_users_profiles WHERE user_id='.(int)$user['id']);
	    try {
		    $db->execute();
	    } catch(Exception $e) {
		    error_log($e->getMessage(), 0);
		    return false;
	    }

        $this->addProfileToUser($user['id'], $user['profile']);

        if (!empty($user['em_groups'])) {
            $groups = explode(',', $user['em_groups']);
            foreach ($groups as $group) {
                $query="INSERT INTO `#__emundus_groups` VALUES ('',".$user['id'].",".$group.")";
                $db->setQuery($query);
	            try {
		            $db->execute();
	            } catch(Exception $e) {
		            error_log($e->getMessage(), 0);
		            return false;
	            }
            }
        }

        if (!empty($user['em_campaigns'])) {
            $connected = JFactory::getUser()->id;
            $campaigns = explode(',', $user['em_campaigns']);

            $query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user['id'];
            $db->setQuery($query);
            $campaigns_id = $db->loadColumn();

            $query = 'SELECT profile_id FROM #__emundus_users_profiles WHERE user_id='.$user['id'];
            $db->setQuery($query);
            $profiles_id = $db->loadColumn();
            foreach ($campaigns as $campaign) {

            	//insert profile******
                $profile = $this->getProfileIDByCampaignID($campaign);
                if (!in_array($profile, $profiles_id)) {
	                $this->addProfileToUser($user['id'], $profile);
                }

                if (!in_array($campaign, $campaigns_id)) {
                    $query = 'INSERT INTO `#__emundus_campaign_candidature` (`applicant_id`, `user_id`, `campaign_id`, `fnum`) VALUES ('.$user['id'].', '. $connected .','.$campaign.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
                    $db->setQuery($query);
	                try {
		                $db->execute();
	                } catch(Exception $e) {
		                error_log($e->getMessage(), 0);
		                return false;
	                }
                }
            }
        }

        if (!empty($user['em_oprofiles'])) {
            $oprofiles = explode(',', $user['em_oprofiles']);
            $query = 'SELECT profile_id FROM #__emundus_users_profiles WHERE user_id='.$user['id'];
            $db->setQuery($query);
            $profiles_id = $db->loadColumn();
            foreach ($oprofiles as $profile) {
                if (!in_array($profile, $profiles_id)) {
                    $this->addProfileToUser($user['id'],$profile);
                }
            }
        }

        if ($user['news'] == "1") {
            $query = "INSERT INTO `#__user_profiles` (`user_id`, `profile_key`, `profile_value`, `ordering`) VALUES (".$user['id'].", 'emundus_profile.newsletter', '\"1\"', 4)";
            $db->setQuery($query);
	        try {
		        $db->execute();
	        } catch(Exception $e) {
		        error_log($e->getMessage(), 0);
		        return false;
	        }
        }

        return true;
    }

	/**
	 * @param $gid
	 *
	 * @return bool|mixed
	 *
	 * @since version
	 */
    public function getGroupProgs($gid) {
        try {
            $query = "select prg.id, prg.label, esg.label as group_label
                      from #__emundus_setup_groups as esg
                      left join #__emundus_setup_groups_repeat_course as esgrc on esgrc.parent_id = esg.id
                      left join #__emundus_setup_programmes as prg on prg.code = esgrc.course
                      where esg.id = " .$gid;
//echo str_replace('#_', 'jos', $query);
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

	/**
	 * @param $gid
	 *
	 * @return array|bool|mixed
	 *
	 * @since version
	 */
    public function getGroupsAcl($gid) {
    	if (!empty($gid)) {
    		try {
    			if (is_array($gid)) {
	                $query = "select esa.label, ea.*, esa.c as is_c, esa.r as is_r, esa.u as is_u, esa.d as is_d
	                      from #__emundus_acl as ea
	                      left join #__emundus_setup_actions as esa on esa.id = ea.action_id
	                      where ea.group_id in (" .implode(',', $gid).")";
                } else {
	                $query = "select esa.label, ea.*, esa.c as is_c, esa.r as is_r, esa.u as is_u, esa.d as is_d
	                      from #__emundus_acl as ea
	                      left join #__emundus_setup_actions as esa on esa.id = ea.action_id
	                      where ea.group_id = " .$gid ." order by esa.ordering asc";
                }
	            $db = $this->getDbo();
	            $db->setQuery($query);
	            return $db->loadAssocList();
	        } catch(Exception $e) {
	            error_log($e->getMessage(), 0);
	            return false;
	        }
    	} else {
    		return array();
    	}
    }

	/** This function returns the groups which are linked to the fnum's program OR NO PROGRAM AT ALL.
	 * @param $group_ids array
	 * @param $fnum string
	 *
	 * @return bool|mixed
	 *
	 * @since version
	 */
	public function getEffectiveGroupsForFnum($group_ids, $fnum) {

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('sg.id'))
			->from($db->quoteName('#__emundus_setup_groups', 'sg'))
			->leftJoin($db->quoteName('#__emundus_setup_groups_repeat_course', 'grc').' ON '.$db->quoteName('grc.parent_id').' = '.$db->quoteName('sg.id'))
			->leftJoin($db->quoteName('#__emundus_setup_programmes', 'sp').' ON '.$db->quoteName('sp.code').' = '.$db->quoteName('grc.course'))
			->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'sc').' ON '.$db->quoteName('sp.code').' = '.$db->quoteName('sc.training'))
			->leftJoin($db->quoteName('#__emundus_campaign_candidature', 'cc').' ON '.$db->quoteName('cc.campaign_id').' = '.$db->quoteName('sc.id'))
			->where($db->quoteName('sg.id').' IN ('.implode(',', $group_ids).') AND ('.$db->quoteName('cc.fnum').' LIKE '.$db->quote($fnum).' OR '.$db->quoteName('sp.code').' IS NULL)');

		$db->setQuery($query);
		try {
			return $db->loadColumn();
		} catch(Exception $e) {
			error_log($e->getMessage(), 0);
			return false;
		}
	}

	/**
	 * @param $gid
	 *
	 * @return bool|mixed
	 *
	 * @since version
	 */
    public function getGroupUsers($gid) {
        try {
            $query = "select eu.*
                      from #__emundus_groups as eg
                      left join #__emundus_users as eu on eu.user_id = eg.user_id
                      where eg.group_id = " .$gid ." order by eu.lastname asc";
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList();
        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getMenuList($params) {
        return EmundusHelperFiles::getMenuList($params);
    }

    public function getActionsACL() {
        return EmundusHelperFiles::getMenuActions();
    }

	/**
	 * @param $aid
	 * @param $fnum
	 * @param $uid
	 * @param $crud
	 *
	 * @return mixed
	 *
	 * @since version
	 */
    public function getUserActionByFnum($aid, $fnum, $uid, $crud) {
        $dbo = $this->getDbo();
        $query = "select ".$crud." from #__emundus_users_assoc where action_id = ".$aid." and user_id = ".$uid." and fnum like ".$dbo->quote($fnum);
        $dbo->setQuery($query);
        return $dbo->loadResult();
    }

	/**
	 * @param $gids
	 * @param $fnum
	 * @param $aid
	 * @param $crud
	 *
	 * @return mixed
	 *
	 * @since version
	 */
    public function getGroupActions($gids, $fnum, $aid, $crud) {
        $dbo = $this->getDbo();
        $query = "select ".$crud." from #__emundus_group_assoc where action_id = ".$aid." and group_id in (".implode(',', $gids).") and fnum like ".$dbo->quote($fnum);
        $dbo->setQuery($query);
        return $dbo->loadAssocList();
    }

    /**
     * @param $uid  int user id
     * @param $passwd   string  password to set
     * @return mixed
     */
    public function setNewPasswd($uid, $passwd) {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__users SET password = '.$dbo->Quote($passwd).' WHERE id='.$uid;
        $dbo->setQuery($query);
        return $dbo->execute();
    }

    /**
     * Connect to LDAP
     * @return mixed
     */
    public function searchLDAP ($search) {
        // Create LDAP object using params entered in plugin
        $plugin = JPluginHelper::getPlugin('authentication', 'ldap');
        $params = new JRegistry($plugin->params);
        $ldap = new JLDAP($params);

        if (!$ldap->connect())
            return false;

        if (!$ldap->bind())
            return false;

        // filters are different based on the LDAP tree, therefore we put them in the eMundus params.
        $params = JComponentHelper::getParams('com_emundus');
        $ldapFilters = $params->get('ldapFilters');

        // Filters come in a list separated by commas, but are fed into the LDAP object as an array.
        // The area to put the search term is defined as [SEARCH] in the param.
        $filters = explode(',', str_replace('[SEARCH]', $search, $ldapFilters));

        $ret = new stdClass();
        $ret->status = true;
        $ret->users = $ldap->search($filters);
        return $ret;
    }

    public function getUserById($uid) { // user of emundus
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('eu.*, case when u.password = ' . $db->quote('') . ' then ' . $db->quote('external') . ' else ' . $db->quote('internal') . ' end as login_type')
            ->from('#__emundus_users as eu')
            ->leftJoin('#__users as u on u.id = eu.user_id')
            ->where('eu.user_id = '.$uid);
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getUsersById($id) { //user of application
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__users WHERE id = '.$id;
        $db->setQuery($query);
        return $db->loadObjectList();
    }

	public function getUsersByIds($ids) { //users of application
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__users WHERE id IN ('.implode(',', $ids).')';
		$db->setQuery($query);
		return $db->loadObjectList();
	}


	/**
	 * Method to start the password reset process. Taken from Joomla and modified to send email using template.
	 *
	 * @param   array  $data  The data expected for the form.
	 *
	 * @return  Object
	 *
	 * @throws Exception
	 * @since  3.9.11
	 */
	public function passwordReset($data) {

		$config = JFactory::getConfig();

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

        $m_emails = new EmundusModelEmails();

		// Load the com_users language tags in order to call the Joomla user JText.
		$language =& JFactory::getLanguage();
		$extension = 'com_users';
		$base_dir = JPATH_SITE;
		$language_tag = $language->getTag(); // loads the current language-tag
		$language->load($extension, $base_dir, $language_tag, true);

		$return = new stdClass();

		$data['email'] = filter_var(JStringPunycode::emailToPunycode($data['email']), FILTER_VALIDATE_EMAIL);

		// Check the validation results.
		if (empty($data['email'])) {
			$return->message = JText::_('COM_USERS_DESIRED_USERNAME');
			$return->status = false;
			return $return;
		}

		// Find the user id for the given email address.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('email') . ' = ' . $db->quote($data['email']));

		// Get the user object.
		$db->setQuery($query);

		try {
			$userId = $db->loadResult();
		} catch (RuntimeException $e) {
			$return->message = JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage());
			$return->status = false;
			return $return;
		}

		// Check for a user.
		if (empty($userId)) {
			$return->message = JText::_('COM_USERS_INVALID_EMAIL');
			$return->status = false;
			return $return;
		}

		// Get the user object.
		$user = JUser::getInstance($userId);

		// Make sure the user isn't blocked.
		if ($user->block) {
			$return->message = JText::_('COM_USERS_USER_BLOCKED');
			$return->status = false;
			return $return;
		}

		// Make sure the user isn't a Super Admin.
		if ($user->authorise('core.admin')) {
			$return->message = JText::_('COM_USERS_REMIND_SUPERADMIN_ERROR');
			$return->status = false;
			return $return;
		}

		include_once (JPATH_SITE.DS.'components'.DS.'com_users'.DS.'models'.DS.'reset.php');
		$m_juser_reset = new UsersModelReset();

		// Make sure the user has not exceeded the reset limit
		if (!$m_juser_reset->checkResetLimit($user)) {
			$resetLimit = (int) JFactory::getApplication()->getParams()->get('reset_time');
			$return->message = JText::plural('COM_USERS_REMIND_LIMIT_ERROR_N_HOURS', $resetLimit);
			$return->status = false;
			return $return;
		}

		// Set the confirmation token.
		$token = JApplicationHelper::getHash(JUserHelper::genRandomPassword());
		$hashedToken = JUserHelper::hashPassword($token);

		$user->activation = $hashedToken;

		// Save the user to the database.
		if (!$user->save(true)) {
			throw new JException(JText::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()), 500);
		}

		// Assemble the password reset confirmation link.
		$mode = $config->get('force_ssl', 0) == 2 ? 1 : (-1);
		$link = 'index.php?option=com_users&view=reset&layout=confirm&token=' . $token;

        $mailer = JFactory::getMailer();

		// Put together the email template data.
		$data = $user->getProperties();
		$data['sitename'] = $config->get('sitename');
		$data['link_text'] = JRoute::_($link, false, $mode);
		$data['link_html'] = '<a href='.JRoute::_($link, true, $mode).'> '.JRoute::_($link, true, $mode).'</a>';
		$data['token'] = $token;

		// Build the translated email.
		$subject = JText::sprintf('COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT', $data['sitename']);
		$body = JText::sprintf('COM_USERS_EMAIL_PASSWORD_RESET_BODY', $data['sitename'], $data['token'], $data['link_html']);

        $post = [
            'USER_NAME' => $user->name,
            'SITE_URL' => JURI::base(),
            'USER_EMAIL' => $user->email
        ];

        $tags = $m_emails->setTags($user->id, $post, null, '', $subject.$body);

        $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);

		// Get and apply the template.
		$query->clear()
			->select($db->quoteName('Template'))
			->from($db->quoteName('#__emundus_email_templates'))
			->where($db->quoteName('id').' = 1');
		$db->setQuery($query);

		try {
			$template = $db->loadResult();
		} catch (RuntimeException $e) {
			$return->message = JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage());
			$return->status = false;
			return $return;
		}

        $body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/", "/\[SITE_NAME\]/"], [$subject, $body, $data['sitename']], $template);
        $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

        // Set sender
        $sender = [
            $config->get('mailfrom'),
            $config->get('fromname')
        ];

        $mailer->setSender($sender);
        $mailer->addReplyTo($config->get('mailfrom'), $config->get('fromname'));
        $mailer->addRecipient($user->email);
        $mailer->setSubject($subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

		// Send the password reset request email.
		$send = $mailer->Send();

		// Check for an error.
		if ($send !== true) {
			throw new JException(JText::_('COM_USERS_MAIL_FAILED'), 500);
		}

		$return->status = true;
		return $return;
	}

    public function getProfileForm(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('form_id')
                ->from($db->quoteName('#__emundus_setup_formlist'))
                ->where($db->quoteName('type') . ' LIKE ' . $db->quote('profile'))
                ->andWhere($db->quoteName('published') . ' = 1');
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot get profile form for edit user : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return 0;
        }
    }

    public function getProfileGroups($formid){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('fg.*')
                ->from($db->quoteName('#__fabrik_groups','fg'))
                ->leftJoin($db->quoteName('#__fabrik_formgroup','ff').' ON '.$db->quoteName('ff.group_id').' = '.$db->quoteName('fg.id'))
                ->where($db->quoteName('ff.form_id') . ' = ' . $db->quote($formid))
                ->andWhere($db->quoteName('fg.published') . ' = 1');
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot get profile groups with formid : ' . $formid . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return [];
        }
    }

    public function getProfileElements($group){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('fe.*,fj.action,fj.code,fj.params as jsparams')
                ->from($db->quoteName('#__fabrik_elements','fe'))
                ->leftJoin($db->quoteName('#__fabrik_jsactions','fj').' ON '.$db->quoteName('fj.element_id').' = '.$db->quoteName('fe.id'));
            if(is_array($group)){
                $query->where($db->quoteName('fe.group_id') . ' IN (' . implode(',',$group) . ')');
            } else {
                $query->where($db->quoteName('fe.group_id') . ' = ' . $db->quote($group));
            }
            $query->andWhere($db->quoteName('fe.published') . ' = 1')
                ->order($db->quoteName('fe.ordering'));
            $db->setQuery($query);
            $elements = $db->loadObjectList();

            foreach ($elements as $element) {
                $params = json_decode($element->params);

                $element->label = JText::_($element->label);
                $params->rollover = JText::_($params->rollover);

                $element->params = json_encode($params);
            }

            return $elements;
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot get elements of group '.$groupid.' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return [];
        }
    }

    public function saveUser($user,$uid){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $columns = array();

        $formid = $this->getProfileForm();
        $groups = $this->getProfileGroups($formid);
        $ids_groups = array_map(function($group){
            return $group->id;
        },$groups);
        $elements = $this->getProfileElements($ids_groups);

        $user_keys = array_keys(get_object_vars($user));
        foreach ($elements as $element) {
            if(in_array($element->name,$user_keys) && $element->name != 'id'){
                $columns[] = $element->name;
            }
        }

        try {
            $query->update($db->quoteName('#__emundus_users'));
            foreach ($columns as $column) {
                $query->set($db->quoteName($column) . ' = ' . $db->quote($user->{$column}));
            }
            $query->where($db->quoteName('user_id') . ' = ' . $db->quote($uid));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot update user '.$uid.' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    public function getProfileAttachments($user_id,$fnum = null){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('esa.*,eua.expires_date,eua.date_time,eua.filename,eua.id as default_id')
                ->from($db->quoteName('#__emundus_users_attachments','eua'))
                ->leftJoin($db->quoteName('#__emundus_setup_attachments','esa').' ON '.$db->quoteName('esa.id').' = '.$db->quoteName('eua.attachment_id'));
            if(!empty($fnum)){
                $query->where($db->quoteName('eua.attachment_id') . ' NOT IN (SELECT distinct attachment_id FROM #__emundus_uploads WHERE fnum = '.$db->quote($fnum).')');
            }
            $query->where($db->quoteName('eua.user_id') . ' = ' . $db->quote($user_id))
                ->andWhere($db->quoteName('eua.published') . ' = 1');
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot get default attachments uploaded by the applicant : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return [];
        }
    }

    public function getProfileAttachmentsAllowed(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('*')
                ->from($db->quoteName('#__emundus_setup_attachments'))
                ->where($db->quoteName('default_attachment') . ' = 1');
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot get attachments allowed to default values : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return [];
        }
    }

    public function addDefaultAttachment($user_id,$attachment_id,$filename){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $six_month_in_future = strtotime(date('Y-m-d') . "+6 month");

        try {
            $query->insert($db->quoteName('#__emundus_users_attachments'))
                ->set($db->quoteName('date_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
                ->set($db->quoteName('attachment_id') . ' = ' . $db->quote($attachment_id))
                ->set($db->quoteName('filename') . ' = ' . $db->quote($filename))
                ->set($db->quoteName('expires_date') . ' = ' . $db->quote(date('Y-m-d',$six_month_in_future)));
            $db->setQuery($query);
            $result = $db->execute();

            JPluginHelper::importPlugin('emundus');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onAfterProfileAttachmentUpload', [$user_id, (int)$attachment_id, $filename]);
            $dispatcher->trigger('callEventHandler', ['onAfterProfileAttachmentUpload', ['user_id' => $user_id, 'attachment_id' => (int)$attachment_id, 'file' => $filename]]);

            return $result;
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot insert default documents : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    public function deleteProfileAttachment($id){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->delete($db->quoteName('#__emundus_users_attachments'))
                ->where($db->quoteName('id') . ' = ' . $db->quote($id));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot delete document from jos_emundus_users_attachments with id ' . $id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    public function uploadProfileAttachmentToFile($fnum,$aids,$uid){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('eua.*')
                ->from($db->quoteName('#__emundus_users_attachments','eua'))
                ->where($db->quoteName('eua.attachment_id') . ' IN (' . $aids . ')')
                ->andWhere($db->quoteName('eua.user_id') . ' = ' . $db->quote($uid));
            $db->setQuery($query);
            $attachments_to_copy = $db->loadObjectList();

            foreach ($attachments_to_copy as $attachment) {
                $query->clear()
                    ->select('count(eu.id)')
                    ->from($db->quoteName('#__emundus_uploads', 'eu'))
                    ->where($db->quoteName('eu.attachment_id') . ' = ' . $db->quote($attachment->attachment_id))
                    ->andWhere($db->quoteName('eu.fnum') . ' = ' . $db->quote($fnum));
                $db->setQuery($query);
                $attachment_already_copy = $db->loadResult();

                if ($attachment_already_copy == 0) {
                    $root_dir = "images/emundus/files/" . $uid;

                    if(!file_exists($root_dir)){
                        mkdir($root_dir);
                    }

                    $file = explode('/',$attachment->filename);
                    $file = explode('.',end($file));
                    $ext = end($file);
                    $file = $file[0];
                    $pos = strrpos($file, '-');
                    $file = substr($file, 0, $pos);

                    $target_file = $file . '-'  . time() . '.' . $ext;

                    copy($attachment->filename, $root_dir . '/' . $target_file);
                    $columns = array('user_id', 'fnum', 'attachment_id', 'filename');
                    $values = array($uid, $fnum, $attachment->attachment_id, $target_file);

                    $query->clear()
                        ->insert($db->quoteName('#__emundus_uploads'))
                        ->columns(implode(',', $db->quoteName($columns)))
                        ->values(implode(',', $db->quote($values)));
                    $db->setQuery($query);
                    $db->execute();
                }
            }

            return true;
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot copy profile document ' . $aid . ' to fnum ' . $fnum . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    public function uploadFileAttachmentToProfile($fnum,$aid,$uid){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('eu.*')
                ->from($db->quoteName('#__emundus_uploads','eu'))
                ->where($db->quoteName('eu.attachment_id') . ' = ' . $db->quote($aid))
                ->andWhere($db->quoteName('eu.fnum') . ' = ' . $db->quote($fnum))
                ->order('eu.id DESC');
            $db->setQuery($query);
            $attachment_to_copy = $db->loadObject();

            if(!empty($attachment_to_copy)) {
                $query->clear()
                    ->select('count(eua.id)')
                    ->from($db->quoteName('#__emundus_users_attachments', 'eua'))
                    ->where($db->quoteName('eua.attachment_id') . ' = ' . $db->quote($attachment_to_copy->attachment_id))
                    ->andWhere($db->quoteName('eua.user_id') . ' = ' . $db->quote($uid));
                $db->setQuery($query);
                $attachment_already_copy = $db->loadResult();

                if ($attachment_already_copy == 0) {
                    $file_dir = "images/emundus/files/" . $uid;
                    $root_dir = "images/emundus/files/" . $uid . '/default_attachments';

                    if (!file_exists($root_dir)) {
                        mkdir($root_dir);
                    }

                    $file = explode('/', $attachment_to_copy->filename);
                    $file = explode('.', end($file));
                    $ext = end($file);
                    $file = $file[0];
                    $pos = strrpos($file, '-');
                    $file = substr($file, 0, $pos);

                    $target_file = $file . '-' . time() . '.' . $ext;

                    copy($file_dir . '/' . $attachment_to_copy->filename, $root_dir . '/' . $target_file);
                    $columns = array('date_time','user_id', 'attachment_id', 'filename','published');
                    $values = array(date('Y-m-d H:i:s'), $uid, $attachment_to_copy->attachment_id, $root_dir . '/' . $target_file, 1);

                    $query->clear()
                        ->insert($db->quoteName('#__emundus_users_attachments'))
                        ->columns(implode(',', $db->quoteName($columns)))
                        ->values(implode(',', $db->quote($values)));
                    $db->setQuery($query);
                    $db->execute();

                    JPluginHelper::importPlugin('emundus');
                    $dispatcher = JEventDispatcher::getInstance();
                    $dispatcher->trigger('onAfterProfileAttachmentUpload', [$uid, (int)$attachment_to_copy->attachment_id, $root_dir . '/' . $target_file]);
                    $dispatcher->trigger('callEventHandler', ['onAfterProfileAttachmentUpload', ['user_id' => $uid, 'attachment_id' => (int)$attachment_to_copy->attachment_id, 'file' => $root_dir . '/' . $target_file]]);
                }
            }

            return true;
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot copy profile document ' . $aid . ' to fnum ' . $fnum . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    public function updateProfilePicture($user_id,$target_file){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->update('#__emundus_users')
                ->set('profile_picture = ' . $db->quote($target_file))
                ->where('user_id = ' . $db->quote($user_id));
            $db->setQuery($query);
            return $db->execute();
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot update profile picture for user ' . $user_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }

    public function addApplicantProfile($user_id){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->select('id,label,published,status')
                ->from($db->quoteName('#__emundus_setup_profiles'))
                ->where($db->quoteName('published') . ' = 1');
            $db->setQuery($query);
            $app_profile = $db->loadResult();

            $query->clear()
                ->insert('#__emundus_users_profile')
                ->set($db->quoteName('date_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                ->set($db->quoteName('user_id') . ' = ' . $db->quote($user_id))
                ->set($db->quoteName('profile_id') . ' = ' . $db->quote($app_profile->id));
            $db->setQuery($query);
            $db->execute();

            return $app_profile;
        } catch (Exception $e) {
            JLog::add(' com_emundus/models/users.php | Cannot add applicant profile for user ' . $user_id . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            return false;
        }
    }
}
