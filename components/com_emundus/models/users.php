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
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');

class EmundusModelUsers extends JModelList
{
    var $_total = null;
    var $_pagination = null;
    //var $filts_details = null;

    protected $data;

    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct()
    {
        parent::__construct();

        $session = JFactory::getSession();
        if (!$session->has('filter_order'))
        {
            $session->set('filter_order', 'id');
            $session->set('filter_order_Dir', 'desc');
        }
        $mainframe = JFactory::getApplication();

        if(!$session->has('limit'))
        {
            $limit = $mainframe->getCfg('list_limit');
            $limitstart = 0;
            $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

            $session->set('limit', $limit);
            $session->set('limitstart', $limitstart);
        }
        else
        {
            $limit      = intval($session->get('limit'));
            $limitstart = intval($session->get('limitstart'));
            $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

            $session->set('limit', $limit);
            $session->set('limitstart', $limitstart);
        }
    }

    public function _buildContentOrderBy()
    {
        $session = JFactory::getSession();
        $params = $session->get('filt_params');
        $filter_order     = @$params['filter_order'];
        $filter_order_Dir = @$params['filter_order_Dir'];

        $can_be_ordering = array ('user', 'id', 'lastname', 'firstname', 'username', 'email', 'profile', 'block', 'lastvisitDate', 'registerDate', 'newsletter', 'groupe', 'university');

        if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
            $orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        } else
            $orderby = ' ORDER BY u.id DESC';

        return $orderby;
    }

    public function _buildQuery()
    {
        $session        = JFactory::getSession();
        $params         = $session->get('filt_params');

        $final_grade    = @$params['finalgrade'];
        $search         = @$params['s'];
        $programme      = @$params['programme'];
        $campaigns      = @$params['campaign'];
        $schoolyears    = @$params['schoolyears'];
        $groupEval      = @$params['evaluator_group'];
        $spam_suspect   = @$params['spam_suspect'];
        $profile        = @$params['profile'];
        $newsletter     = @$params['newsletter'];

        $uid = JRequest::getVar('rowid', null, 'GET', 'none', 0);
        $edit = JRequest::getVar('edit', 0, 'GET', 'none', 0);
        $list_user="";

        if(!empty($schoolyears) && (empty($campaigns) || $campaigns[0]=='%') && $schoolyears[0]!='%'){
            $list_user="";
            $applicant_schoolyears = $this->getUserListWithSchoolyear($schoolyears);
            $i=0;
            $nb_element = count($applicant_schoolyears);
            if($nb_element==0){
                $list_user.="EMPTY";
            }else{
                foreach($applicant_schoolyears as $applicant){
                    if(++$i === $nb_element){
                        $list_user.=$applicant;
                    }else if($applicant!=NULL){
                        $list_user.=$applicant.", ";
                    }
                }
            }
        }else if(!empty($campaigns) && $campaigns[0]!='%' && (empty($schoolyears) || $schoolyears[0]=='%') ){
            $list_user="";
            $applicant_campaigns = $this->getUserListWithCampaign($campaigns);
            $i=0;
            $nb_element = count($applicant_campaigns);
            if($nb_element==0){
                $list_user.="EMPTY";
            }else{
                foreach($applicant_campaigns as $applicant){
                    if(++$i === $nb_element){
                        $list_user.=$applicant;
                    }else if($applicant!=NULL){
                        $list_user.=$applicant.", ";
                    }
                }
            }
        }else if(!empty($campaigns) && $campaigns[0]!='%' &&  !empty($schoolyears) && $schoolyears[0]!='%'){
            //$applicant_schoolyears = $this->getUserListWithSchoolyear($schoolyears);
            $i=0;
            $list_user='';
            foreach($schoolyears as $schoolyear){
                foreach($campaigns as $campaign){
                    $compare = $this->compareCampaignANDSchoolyear($campaign,$schoolyear);
                    // var_dump($compare.$campaign);
                    if($compare!=0){
                        $applicant_campaigns = $this->getUserListWithCampaign($campaign);
                        //$nb_element = count($applicant_campaigns);
                        foreach($applicant_campaigns as $applicant){
                            $list_user.=$applicant.", ";
                        }
                    }
                }
            }
            if($list_user==''){
                $list_user='EMPTY';
            }else{
                $taille = strlen($list_user);
                $list_user=substr($list_user,0,$taille-2);
            }
        }

        if(!empty($groupEval))
        {
            $list_user="";
            $applicant_groupEval = $this->getUserListWithGroupsEval($groupEval);
            $i=0;
            $nb_element = count($applicant_groupEval);
            if($nb_element==0){
                $list_user.="EMPTY";
            }else{
                foreach($applicant_groupEval as $applicant){
                    if(++$i === $nb_element){
                        $list_user.=$applicant;
                    }else if($applicant!=NULL){
                        $list_user.=$applicant.", ";
                    }
                }
            }
        }

        $query = 'SELECT DISTINCT(u.id), e.lastname, e.firstname, u.email, u.username,  espr.label as profile, up.profile_value as newsletter, u.registerDate, u.lastvisitDate,  GROUP_CONCAT( DISTINCT esgr.label SEPARATOR "<br>") as groupe, cat.title as university, u.block as active
                    FROM #__users AS u 
                    LEFT JOIN #__emundus_users AS e ON u.id = e.user_id 
                    LEFT JOIN #__emundus_groups AS egr ON egr.user_id = u.id
                    LEFT JOIN #__emundus_setup_groups AS esgr ON esgr.id = egr.group_id
                    LEFT JOIN #__emundus_setup_profiles AS espr ON espr.id = e.profile
                    LEFT JOIN #__emundus_personal_detail AS epd ON u.id = epd.user
                    LEFT JOIN #__categories AS cat ON cat.id = e.university_id
                    LEFT JOIN #__user_profiles AS up ON ( u.id = up.user_id AND up.profile_key like "emundus_profiles.newsletter")';

        if(isset($programme) && !empty($programme) && $programme[0] != '%') {
            $query .= ' LEFT JOIN #__emundus_campaign_candidature AS ecc ON u.id = ecc.applicant_id
                        LEFT JOIN #__emundus_setup_campaigns as esc ON ecc.campaign_id=esc.id ';
        }

        if(isset($final_grade) && !empty($final_grade)) {
            $query .= 'LEFT JOIN #__emundus_final_grade AS efg ON u.id = efg.student_id ';
        }

        $query .= ' where 1=1 AND u.id != 1 ';

        if(isset($programme) && !empty($programme) && $programme[0] != '%') {
            $query .= ' AND ( esc.training IN ("'.implode('","', $programme).'")
                            OR u.id IN (
                                select _eg.user_id
                                from #__emundus_groups as _eg
                                left join #__emundus_setup_groups_repeat_course as _esgr on _esgr.parent_id=_eg.group_id
                                where _esgr.course IN ("'.implode('","', $programme).'")
                                )
                            )';
        }

        if($edit==1)
            $query.= ' u.id='.mysql_real_escape_string($uid);
        else
        {
            $and = true;
            /*var_dump($this->filts_details['profile']);
            if(isset($this->filts_details['profile']) && !empty($this->filts_details['profile'])){
                $query.= ' AND e.profile IN ('.implode(',', $this->filts_details['profile']).') ';
                $and = true;
            }*/
            if(isset($profile) && !empty($profile) && is_numeric($profile))
            {
                $query.= ' AND e.profile = '.mysql_real_escape_string($profile);
                $and = true;
            }
            if(isset($final_grade) && !empty($final_grade))
            {
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                $query.= 'efg.Final_grade = "'.mysql_real_escape_string($final_grade).'"';
                $and = true;
            }
            if(isset($search) && !empty($search)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .=' '; }
                $query .= '(e.lastname LIKE "%'.mysql_real_escape_string($search).'%"
                            OR e.firstname LIKE "%'.mysql_real_escape_string($search).'%" 
                            OR u.email LIKE "%'.mysql_real_escape_string($search).'%" 
                            OR e.schoolyear LIKE "%'.mysql_real_escape_string($search).'%" 
                            OR u.username LIKE "%'.mysql_real_escape_string($search).'%" 
                            OR u.id LIKE "%'.mysql_real_escape_string($search).'%")';
            }
            /*if(isset($schoolyears) &&  !empty($schoolyears)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .='WHERE '; }
                $query.= 'e.schoolyear="'.mysql_real_escape_string($schoolyears).'"';
            }*/
            if(isset($spam_suspect) &&  !empty($spam_suspect) && $spam_suspect == 1) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .=' '; }
                $query .= 'u.lastvisitDate="0000-00-00 00:00:00" AND TO_DAYS(NOW()) - TO_DAYS(u.registerDate) > 7';
            }
            if(!empty($list_user)) {
                if($and) {
                    $query .= ' AND ';
                }else{
                    $and = true; $query .=' ';
                }

                if($list_user=='EMPTY'){
                    $query.= 'u.id IN (null) ';
                }else{
                    $query.= 'u.id IN ( '.$list_user.' )';
                }
            }

            if(isset($newsletter) &&  !empty($newsletter)) {
                if($and) $query .= ' AND ';
                else { $and = true; $query .=' '; }
                $query .= 'profile_value like "%'.$newsletter.'%"';
            }
        }

        $query .= " GROUP BY u.id ";

        return $query;
    }

    public function getUsers()
    {
        $session = JFactory::getSession();
        // Lets load the data if it doesn't already exist
        try
        {
            $query = $this->_buildQuery();
            $query .= $this->_buildContentOrderBy();
//echo '<hr>'.str_replace ('#_', 'jos', $query);
            return $this->_getList( $query ,$session->get('limitstart'), $session->get('limit'));
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getProfiles()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.published, caag.lft FROM #__emundus_setup_profiles esp
        INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
        where esp.id > 1
        ORDER BY esp.acl_aro_groups, esp.label';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getProfilesByIDs($ids)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, esp.published, caag.lft
        FROM #__emundus_setup_profiles esp
        INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
        WHERE esp.id IN (' . implode(',', $ids) . ')
        ORDER BY caag.lft, esp.label';
        $db->setQuery($query);
        return $db->loadObjectList('id');
    }

    public function getEditProfiles(){
        $db = JFactory::getDBO();
        $current_user = JFactory::getUser();
        $current_group = 0;
        foreach ($current_user->groups as $group) {
            if ($group > $current_group) $current_group = $group;
        }
        $query ='SELECT id, label FROM #__emundus_setup_profiles WHERE '.$current_group.' >= acl_aro_groups GROUP BY id';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public function getApplicantProfiles(){
        $db = JFactory::getDBO();
        $query ='SELECT * FROM #__emundus_setup_profiles WHERE published=1';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }


    public function getUsersProfiles(){
        $user = JFactory::getUser();
        $uid = JRequest::getVar('rowid', $user->id, 'get','int');
        $db = JFactory::getDBO();
        $query = 'SELECT eup.profile_id FROM #__emundus_users_profiles eup WHERE eup.user_id='.$uid;
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getCurrentUserProfile($uid){
        $db = JFactory::getDBO();
        $query = 'SELECT eu.profile FROM #__emundus_users eu WHERE eu.user_id='.$uid;
        $db->setQuery( $query );
        return $db->loadResult();
    }

    public function getUniversities()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT c.id, c.title
        FROM #__categories as c
        WHERE c.published=1 AND c.extension like "com_contact" 
        order by note desc,lft asc';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public function getGroups()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT esg.id, esg.label
        FROM #__emundus_setup_groups esg
        WHERE esg.published=1 
        ORDER BY esg.label';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public function getCampaigns()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT sc.id, cc.applicant_id, sc.start_date, sc.end_date, sc.label, sc.year
        FROM #__emundus_setup_campaigns AS sc 
        LEFT JOIN #__emundus_campaign_candidature AS cc ON cc.campaign_id = sc.id
        WHERE sc.published=1';
        //echo str_replace('#_','jos',$query);
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getCampaignsPublished()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_campaigns AS sc WHERE sc.published=1 ORDER BY sc.start_date DESC, sc.label ASC';
        //echo str_replace('#_','jos',$query);
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getAllCampaigns()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_campaigns AS sc ORDER BY sc.start_date DESC, sc.label ASC';
        //echo str_replace('#_','jos',$query);
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getCampaignsCandidature($aid = 0)
    {
        $db = JFactory::getDBO();
        $uid = ($aid!=0)?$aid:JRequest::getVar('rowid', null, 'GET', 'none', 0);
        $query = 'SELECT * FROM #__emundus_campaign_candidature AS cc  WHERE applicant_id='.$uid;
        //echo str_replace('#_','jos',$query);
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getUserListWithSchoolyear($schoolyears)
    {
        $year = is_string($schoolyears)?$schoolyears:"'".implode("','", $schoolyears)."'";
        $db = JFactory::getDBO();
        $query = 'SELECT cc.applicant_id
        FROM #__emundus_campaign_candidature AS cc 
        LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
        WHERE sc.published=1 AND sc.year IN ('.$year.') ORDER BY sc.year DESC';
//echo str_replace('#_','jos',$query);
        $db->setQuery( $query );
        return $db->loadColumn();
    }

    public function getUserListWithCampaign($campaign)
    {
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
        if(!is_array($campaign)){
            $query = 'SELECT cc.applicant_id
            FROM #__emundus_campaign_candidature AS cc 
            LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
            WHERE sc.published=1 AND sc.id IN ('.$campaign.')';
        }else{
            $query = 'SELECT cc.applicant_id
            FROM #__emundus_campaign_candidature AS cc 
            LEFT JOIN #__emundus_setup_campaigns AS sc ON cc.campaign_id = sc.id
            WHERE sc.published=1 AND sc.id IN ('.implode(",", $campaign).')';
        }
        $db->setQuery( $query );
        return $db->loadColumn();
    }

    public function compareCampaignANDSchoolyear($campaign,$schoolyear){
        $db = JFactory::getDBO();
        $query = 'SELECT COUNT(*)
        FROM #__emundus_setup_campaigns AS sc 
        WHERE id='.$campaign.' AND year="'.$schoolyear.'"';
        // echo str_replace('#_','jos',$query);
        $db->setQuery( $query );
        return $db->loadResult();
    }

    public function getCurrentCampaign(){
        return EmundusHelperFilters::getCurrentCampaign();
    }

    public function getCurrentCampaignsID(){
        return EmundusHelperFilters::getCurrentCampaignsID();
    }

    public function getCurrentCampaigns()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT sc.id, sc;label
        FROM #__emundus_setup_campaigns AS sc
        WHERE sc.published=1 AND end_date > NOW()';
        $db->setQuery( $query );
        // echo str_replace('#_','jos',$query);
        return $db->loadColumn();
    }

    public function getProgramme()
    {
        try
        {
            $db = JFactory::getDBO();
            $query = 'SELECT sp.code, sp.label FROM #__emundus_setup_programmes AS sp ORDER BY sp.label ASC';
            $db->setQuery( $query );
            return $db->loadAssocList();
        }
        catch(Exception $e)
        {
            return null;
        }
    }

    public function getNewsletter()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT user_id, profile_value
        FROM #__user_profiles
        WHERE profile_key = "emundus_profile.newsletter"';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getGroupEval()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT esg.id, eu.user_id, eu.firstname, eu.lastname, u.email, esg.label
                FROM #__emundus_setup_groups as esg
                LEFT JOIN #__emundus_groups as eg on esg.id=eg.group_id
                LEFT JOIN #__emundus_users as eu on eu.user_id=eg.user_id
                LEFT JOIN #__users as u on u.id=eu.user_id
                WHERE esg.published=1';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getGroupsEval()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT ege.id, ege.applicant_id, ege.user_id, ege.group_id, esg.label
        FROM #__emundus_groups_eval as ege
        LEFT JOIN #__emundus_setup_groups as esg ON esg.id = ege.group_id 
        WHERE esg.published=1';
        $db->setQuery( $query );
        return $db->loadObjectList('applicant_id');
    }

    public function getUserListWithGroupsEval($groups)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT eg.user_id
        FROM #__emundus_groups as eg
        LEFT JOIN #__emundus_setup_groups as esg ON esg.id=eg.group_id
        WHERE esg.published=1 AND eg.group_id='.$groups;
        $db->setQuery( $query );
        return $db->loadColumn();
    }

    public function getUsersGroups()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT eg.user_id, eg.group_id
        FROM #__emundus_groups eg';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getSchoolyears()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1 GROUP BY schoolyear';
        $db->setQuery( $query );
        return $db->loadColumn();
    }

    public function getTotal()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }
        return $this->_total;
    }

    public function getPagination()
    {
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
    public function getForm($data = array(), $loadData = true)
    {
        //die( JPATH_COMPONENT.DS.'forms'.DS.'registration.xml' );
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
    protected function loadFormData()
    {
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
    public function getData()
    {
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

    public function adduser($user, $other_params)
    {
        // add to jos_emundus_users; jos_users; jos_emundus_groups; jos_users_profiles; jos_users_profiles_history
        $mainframe  = JFactory::getApplication();
        $db         = JFactory::getDBO();
        $pathway    = $mainframe->getPathway();
        $config     = JFactory::getConfig();
        $authorize  = JFactory::getACL();
        $document   = JFactory::getDocument();


        if ( !$user->save() )
        {
            JFactory::getApplication()->enqueueMessage(JText::_('CAN_NOT_SAVE_USER').'<BR />'.$user->getError(), 'error');
            $res = array('msg' => $user->getError());
            return $res;
        }else{
            $firstname=$other_params['firstname'];
            $lastname=$other_params['lastname'];
            $profile = $other_params['profile'];
            $groups = $other_params['em_groups'];
            $campaigns = $other_params['em_campaigns'];
            $news = $other_params['news'];
            $univ_id=$other_params['univ_id'];

            if(empty($univ_id)){
                $query="INSERT INTO `#__emundus_users` (id, user_id, registerDate, firstname, lastname, profile, schoolyear, disabled, disabled_date, cancellation_date, cancellation_received, university_id) VALUES ('',".$user->id.",'".date('Y-m-d H:i:s')."','".$firstname."','".$lastname."',".$profile.",'',0,'','','',0)";
                $db->setQuery($query);
                $db->Query() or die($db->getErrorMsg());
            }else{
                $query="INSERT INTO `#__emundus_users` (id, user_id, registerDate, firstname, lastname, profile, schoolyear, disabled, disabled_date, cancellation_date, cancellation_received, university_id) VALUES ('',".$user->id.",'".date('Y-m-d H:i:s')."','".$firstname."','".$lastname."',".$profile.",'',0,'','','','".$univ_id."')";
                $db->setQuery($query);
                $db->Query() or die($db->getErrorMsg());
            }

            if(!empty($groups))
            {
                foreach($groups as $group)
                {
                    $query="INSERT INTO `#__emundus_groups` VALUES ('',".$user->id.",".$group.")";
                    $db->setQuery($query);
                    $db->Query() or die($db->getErrorMsg());
                }
            }

            if(!empty($campaigns))
            {
                $connected = JFactory::getUser()->id;
                foreach($campaigns as $campaign)
                {
                    $query = 'INSERT INTO `#__emundus_campaign_candidature` (`applicant_id`, `user_id`, `campaign_id`, `fnum`) VALUES ('.$user->id.', '. $connected .','.$campaign.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
                    $db->setQuery( $query );
                    $db->Query();
                }
            }

            if($news == 1)
            {
                $query="INSERT INTO `#__user_profiles` (`user_id`, `profile_key`, `profile_value`, `ordering`) VALUES (".$user->id.", 'emundus_profiles.newsletter', '1', 4)";
                $db->setQuery($query);
                $db->query() or die($db->getErrorMsg());
            }

            $query="INSERT INTO `#__emundus_users_profiles` VALUES ('','".date('Y-m-d H:i:s')."',".$user->id.",".$profile.",'','')";
            $db->setQuery($query);
            $db->Query() or die($db->getErrorMsg());

            $query="INSERT INTO `#__emundus_users_profiles_history` VALUES ('','".date('Y-m-d H:i:s')."',".$user->id.",".$profile.",'profile')";
            $db->setQuery($query);
            $db->Query() or die($db->getErrorMsg());

            $query="INSERT INTO `#__user_profiles`
                    VALUES  (".$user->id.",'emundus_profile.firstname', ".$db->Quote('"'.$firstname.'"').", '2'),
                            (".$user->id.",'emundus_profile.lastname', ".$db->Quote('"'.$lastname.'"').", '1')";
            $db->setQuery($query);
            $db->Query() or die($db->getErrorMsg());

            JFactory::getApplication()->enqueueMessage(JText::_('USER_SUCCESSFULLY_ADDED'), 'message');

            return $user->id;
        }

    }

    public function found_usertype($acl_aro_groups){
        $db = JFactory::getDBO();
        $query="SELECT title FROM #__usergroups WHERE id=".$acl_aro_groups;
        $db->setQuery($query);
        return $db->loadResult();
    }

    public function getDefaultGroup($pid){
        $db = JFactory::getDBO();
        $query="SELECT acl_aro_groups FROM #__emundus_setup_profiles WHERE id=".$pid;
        $db->setQuery($query);
        return $db->loadColumn();
    }

    /**
     *
     * PLAIN LOGIN
     *
     */
    public function plainLogin($credentials) {
        // Get the application object.
        $app = JFactory::getApplication();

        // Get the log in credentials.
        /*   $credentials = array();
        $credentials['username'] = $this->_user;
        $credentials['password'] = $this->_passw;*/

        $options = array();
        return $app->login($credentials, $options);

    }

    /**
     *
     * ENCRYPT LOGIN
     *
     */
    public function encryptLogin($credentials) {
        // Get the application object.
        $app = JFactory::getApplication();

        $db =& JFactory::getDBO();
        $query = 'SELECT `id`, `username`, `password`'
            . ' FROM `#__users`'
            . ' WHERE username=' . $db->Quote( $credentials['username'] )
            . '   AND password=' . $db->Quote( $credentials['password'] )
        ;
        $db->setQuery( $query );
        $result = $db->loadObject();

        if($result) {
            JPluginHelper::importPlugin('user');

            $options = array();
            $options['action'] = 'core.login.site';

            $response['username'] = $result->username;
            $result = $app->triggerEvent('onUserLogin', array((array)$response, $options));
        }

    }

    /*
     * Function to get fnums associated to users groups or user
     * @param   $action_id  int     Allowed action ID
     * @param   $crud       array   Array of type access (create, read, update, delete)
     */
    public function getFnumsAssoc($action_id, $crud = array())
    {
        $current_user = JFactory::getUser();
        $crud_where = '';
        foreach($crud as $key=>$value){
            $crud_where .= ' AND '.$key.'='.$value;
        }

        try
        {
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
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }


    /*
     * Function to get fnums associated to group
     * @param   $group_id   int     Allowed action ID
     * @param   $action_id  int     Allowed action ID
     * @param   $crud       array   Array of type access (create, read, update, delete)
     */
    public function getFnumsGroupAssoc($group_id, $action_id, $crud = array())
    {
        $crud_where = '';
        foreach($crud as $key=>$value){
            $crud_where .= ' AND '.$key.'='.$value;
        }

        try
        {
            $db = $this->getDbo();
            $query = 'SELECT DISTINCT fnum
                        FROM #__emundus_group_assoc
                        WHERE action_id = '.$action_id.' '.$crud_where.' AND group_id ='.$group_id;
            $db->setQuery($query);
            $fnum = $db->loadColumn();

            return $fnum;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /*
 * Function to get fnums associated to user
 * @param   $action_id  int     Allowed action ID
 * @param   $crud       array   Array of type access (create, read, update, delete)
 */
    public function getFnumsUserAssoc($action_id, $crud = array())
    {
        $current_user = JFactory::getUser();
        $crud_where = '';
        foreach($crud as $key=>$value){
            $crud_where .= ' AND '.$key.'='.$value;
        }

        try
        {
            $db = $this->getDbo();
            $query = 'SELECT DISTINCT fnum
                        FROM #__emundus_users_assoc
                        WHERE action_id = '.$action_id.' '.$crud_where.' AND user_id = '.$current_user->id;
            $db->setQuery($query);
            $fnum = $db->loadColumn();

            return $fnum;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /*
     * Function to get Evaluators Infos for the mailing evaluators
     */
    public function getEvalutorByFnums($fnums)
    {
        include_once(JPATH_BASE.'/components/com_emundus/models/files.php');
        $files = new EmundusModelFiles;

        $fnums_info = $files->getFnumsInfos($fnums);

        $training = array();
        foreach ($fnums_info as $key => $value) {
            $training[] = $value['training'];
        }
        try
        {
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
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function getActions($actions)
    {
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

        try
        {
            $db->setQuery($query);
            return $db->loadAssocList();
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }

    // update actions rights for a group
    public function setGroupRight($id, $action, $value){
        $db = $this->getDbo();
        try
        {
            $query = 'UPDATE `#__emundus_acl` SET `'.$action.'`='.$value.' WHERE `id`='.$id;
            $db->setQuery($query);
            return $db->query();
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function addGroup($gname, $gdesc, $actions, $progs)
    {
        try
        {
            $query = "insert into #__emundus_setup_groups (`label`,`description`, `published`) values ('$gname', '$gdesc', 1)";
            $db = $this->getDbo();

            try
            {
                $db->setQuery($query);
                $db->query();
                $gid = $db->insertid();
                $str = "";
                foreach ($progs as $prog)
                {
                    $str .= "($gid, '$prog'),";
                }
                $str = rtrim($str, ",");
                $query = "insert into #__emundus_setup_groups_repeat_course (`parent_id`, `course`) values $str";
                $db->setQuery($query);
                $db->query();
                $str = "";
                foreach ($actions as $action)
                {
                    $act = (array) $action;
                    $str .= "($gid, ".implode(',', $act)."),";
                }
                $str = rtrim($str, ",");
                $query = "insert into #__emundus_acl (`group_id`, `action_id`, `c`, `r`, `u`, `d`) values $str";
                $db->setQuery($query);
                return $db->query();
            }
            catch(Exception $e)
            {
                echo $e->getMessage();
                return null;
            }
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    public function changeBlock($users, $state)
    {
        try
        {
            $db = $this->getDbo();
            foreach ($users as $uid)
            {
                $uid = intval($uid);
                $query = "UPDATE #__users SET block = ".$state." WHERE id =". $uid;
                $db->setQuery($query);
                $db->query();
                if($state == 0)
                {
                    $db->setQuery('UPDATE #__emundus_users SET disabled  = '.$state.' WHERE user_id = '.$uid);
                }
                else
                {
                    $db->setQuery('UPDATE #__emundus_users SET disabled  = '.$state.', disabled_date = NOW() WHERE user_id = '.$uid);
                }
                $res = $db->query();
            }
            return $res;

        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getNonApplicantId($users)
    {
        try
        {
            $db = $this->getDbo();
            $db->setQuery("select eu.user_id from #__emundus_users as eu left join #__emundus_setup_profiles as esp on esp.id = eu.profile WHERE esp.published != 1 and eu.user_id in (".implode(',',$users).")");
            $res = $db->loadAssocList();
            return $res;

        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function affectToGroups($users, $groups)
    {
        try
        {
            if (count($users) > 0) {
                $db = $this->getDbo();
                $str = "";
                foreach($users as $user)
                {
                    foreach($groups as $gid)
                    {
                        $str .= "(".$user['user_id'].", $gid),";
                    }
                }
                $str = rtrim($str, ",");

                $query = "insert into #__emundus_groups (`user_id`, `group_id`) values $str";

                $db->setQuery($query);
                $res = $db->query();
                return $res;
            } else
                return 0;

        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getUserInfos($uid)
    {
        try
        {
            $query = 'select u.username as login, u.email, eu.firstname, eu.lastname, eu.profile, eu.university_id, up.profile_value as newsletter
                      from #__users as u
                      left join #__emundus_users as eu on eu.user_id = u.id
                      left join #__user_profiles as up on (up.user_id = u.id and up.profile_key like "emundus_profiles.newsletter")
                      where u.id = ' .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssoc();
        }
        catch(Exeption $e)
        {
            return false;
        }
    }

    // Get groups of user
    public function getUserGroups($uid, $return = 'AssocList')
    {
        try
        {
            $query = "SELECT esg.id, esg.label
                      from #__emundus_groups as g
                      left join #__emundus_setup_groups as esg on g.group_id = esg.id
                      where g.user_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);
            if ($return == 'Column') 
                return $db->loadColumn();
            else
                return $db->loadAssocList('id', 'label');
        }
        catch(Exeption $e)
        {
            return false;
        }
    }

    // get programme associated to user groups
    public function getUserGroupsProgramme($uid, $index = 'id')
    {
        try
        {
            $query = "SELECT esg.id, esg.label, esgc.course
                      FROM #__emundus_groups as g
                      LEFT JOIN #__emundus_setup_groups AS esg ON g.group_id = esg.id
                      LEFT JOIN #__emundus_setup_groups_repeat_course AS esgc ON esgc.parent_id=esg.id
                      WHERE g.user_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);

            return $db->loadAssocList($index);
        }
        catch(Exeption $e)
        {
            return false;
        }
    }

    // get user ACL
    public function getUserACL($uid = null, $fnum = null)
    {
        try
        {
            $user = JFactory::getUser();
            if(is_null($uid))
            {
                $uid = $user->id;
            }
            $acl = array();
            if($fnum === null)
            {
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
                    foreach ($userACL as $key => $value)
                    {
                        if(isset($acl[$value['action_id']]))
                        {
                            $acl[$value['action_id']]['c'] = max($acl[$value['action_id']]['c'],$value['c']);
                            $acl[$value['action_id']]['r'] = max($acl[$value['action_id']]['r'],$value['r']);
                            $acl[$value['action_id']]['u'] = max($acl[$value['action_id']]['d'],$value['u']);
                            $acl[$value['action_id']]['d'] = max($acl[$value['action_id']]['d'],$value['d']);
                        }
                        else
                        {
                            $acl[$value['action_id']]['c'] = $value['c'];
                            $acl[$value['action_id']]['r'] = $value['r'];
                            $acl[$value['action_id']]['u'] = $value['u'];
                            $acl[$value['action_id']]['d'] = $value['d'];
                        }
                    }
                }
                if(!empty($user->emGroups))
                {
                    $query = "SELECT esgc.course, acl.action_id, acl.c, acl.r, acl.u, acl.d, acl.group_id
                        FROM #__emundus_setup_groups_repeat_course AS esgc
                        LEFT JOIN #__emundus_acl AS acl ON acl.group_id = esgc.parent_id
                        WHERE acl.group_id in (".implode(',', $user->emGroups).")";
                    $db = $this->getDbo();
                    $db->setQuery($query);
                    $userACL =  $db->loadAssocList();
                    if (count($userACL) > 0)
                    {
                        foreach ($userACL as $key => $value)
                        {
                            if(isset($acl[$value['action_id']]))
                            {
                                $acl[$value['action_id']]['c'] = max($acl[$value['action_id']]['c'],$value['c']);
                                $acl[$value['action_id']]['r'] = max($acl[$value['action_id']]['r'],$value['r']);
                                $acl[$value['action_id']]['u'] = max($acl[$value['action_id']]['d'],$value['u']);
                                $acl[$value['action_id']]['d'] = max($acl[$value['action_id']]['d'],$value['d']);
                            }
                            else
                            {
                                $acl[$value['action_id']]['c'] = $value['c'];
                                $acl[$value['action_id']]['r'] = $value['r'];
                                $acl[$value['action_id']]['u'] = $value['u'];
                                $acl[$value['action_id']]['d'] = $value['d'];
                            }
                        }
                    }
                }

                return $acl;
            }
            else
            {
                $db = $this->getDbo();
                $query = "SELECT eua.action_id, eua.c, eua.r, eua.u, eua.d
                        FROM #__emundus_users_assoc AS eua
                        WHERE fnum like ".$db->quote($fnum)."  and  eua.user_id = " .$uid;
                $db->setQuery($query);
                $acl =  $db->loadAssocList();

                return $acl;
            }


        }
        catch(Exeption $e)
        {
            return false;
        }

    }

    // get programme associated to user groups
    public function getUserGroupsProgrammeAssoc($uid)
    {
        try
        {
            $query = "SELECT DISTINCT (esgc.course)
                      FROM #__emundus_groups as g
                      LEFT JOIN #__emundus_setup_groups AS esg ON g.group_id = esg.id
                      LEFT JOIN #__emundus_setup_groups_repeat_course AS esgc ON esgc.parent_id=esg.id
                      WHERE g.user_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);

            return $db->loadColumn();
        }
        catch(Exeption $e)
        {
            return false;
        }
    }

    /*
     *   Get application fnums associated to a groups
     *   @param gid     array of groups ID
     *   @return array
    */
    public function getApplicationsAssocToGroups($gid)
    {

        if (count($gid) == 0) {
            return array();
        }
        try
        {
            $query = 'SELECT DISTINCT (ga.fnum)
                      FROM #__emundus_group_assoc as ga
                      WHERE ga.group_id IN ('.implode(',', $gid).')';
            $db = $this->getDbo();
            $db->setQuery($query);

            return $db->loadColumn();
        }
        catch(Exeption $e)
        {
            return false;
        }
    }


    // get applicants associated to a user
    public function getApplicantsAssoc($uid)
    {
        try
        {
            $query = "SELECT DISTINCT (eua.fnum)
                      FROM #__emundus_users_assoc as eua
                      WHERE eua.user_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);

            return $db->loadColumn();
        }
        catch(Exeption $e)
        {
            return false;
        }
    }

    public function getUserCampaigns($uid)
    {
        try
        {
            $query = "select esc.id, esc.label
                      from #__emundus_setup_campaigns as esc
                      left join #__emundus_campaign_candidature as ecc on ecc.campaign_id = esc.id
                      where ecc.applicant_id = " .$uid;
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList('id', 'label');
        }
        catch(Exeption $e)
        {
            return false;
        }
    }

    public function editUser($user)
    {
        try
        {
            $u = JFactory::getUser($user['id']);

            if (!$u->bind( $user ))
            {
                $res = array('msg' => $u->getError());
                return $res;
            }
            if ( !$u->save() )
            {
                $res = array('msg' =>$u->getError());
                return $res;
            }

            $db = JFactory::getDBO();

            $db->setQuery('UPDATE #__emundus_users SET firstname = "'.mysql_real_escape_string($user['firstname']).'",
                                                        lastname = "'.mysql_real_escape_string($user['lastname']).'",
                                                        profile = "'.mysql_real_escape_string($user['profile']).'",
                                                        university_id = "'.mysql_real_escape_string($user['university_id']).'"
                                                        WHERE user_id = '.mysql_real_escape_string($user['id']));
            $db->query();

            $db->setQuery('delete from #__emundus_groups where user_id = '. $user['id']);
            $db->query();

            $db->setQuery('delete from #__user_profiles where user_id = ' .$user['id'].' and profile_key like "emundus_profiles.newsletter"');
            $db->query();
            if(!empty($user['em_groups']))
            {
                $groups = explode(',', $user['em_groups']);
                foreach($groups as $group)
                {
                    $query="INSERT INTO `#__emundus_groups` VALUES ('',".$user['id'].",".$group.")";
                    $db->setQuery($query);
                    $db->query() or die($db->getErrorMsg());
                }
            }
            if(!empty($user['em_campaigns']))
            {
                $connected = JFactory::getUser()->id;
                $campaigns = explode(',', $user['em_campaigns']);

                $query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user['id'];
                $db->setQuery($query);
                $campaigns_id = $db->loadColumn();

                foreach($campaigns as $campaign)
                {
                    if (!in_array($campaign, $campaigns_id)) {
                        $query = 'INSERT INTO `#__emundus_campaign_candidature` (`applicant_id`, `user_id`, `campaign_id`, `fnum`) VALUES ('.$user['id'].', '. $connected .','.$campaign.', CONCAT(DATE_FORMAT(NOW(),\'%Y%m%d%H%i%s\'),LPAD(`campaign_id`, 7, \'0\'),LPAD(`applicant_id`, 7, \'0\')))';
                        $db->setQuery( $query );
                        $db->query();
                    }
                }
            }

            if($user['news'] == 1)
            {
                $query="INSERT INTO `#__user_profiles` (`user_id`, `profile_key`, `profile_value`, `ordering`) VALUES (".$user['id'].", 'emundus_profiles.newsletter', '1', 4)";
                $db->setQuery($query);
                $db->query();
            }
            return true;

        }
        catch(Exeption $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getGroupProgs($gid)
    {
        try
        {
            $query = "select prg.id, prg.label, esg.label as group_label
                      from #__emundus_setup_groups as esg
                      left join #__emundus_setup_groups_repeat_course as esgrc on esgrc.parent_id = esg.id
                      left join #__emundus_setup_programmes as prg on prg.code = esgrc.course
                      where esg.id = " .$gid;
//echo str_replace('#_', 'jos', $query);
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getGroupsAcl($gid)
    {
	  if(count($gid) > 0)
      {
        try
        {
            if(is_array($gid))
            {
                $query = "select esa.label, ea.*, esa.c as is_c, esa.r as is_r, esa.u as is_u, esa.d as is_d
                      from #__emundus_acl as ea
                      left join #__emundus_setup_actions as esa on esa.id = ea.action_id
                      where ea.group_id in (" .implode(',', $gid).")";
            }
            else
            {
                $query = "select esa.label, ea.*, esa.c as is_c, esa.r as is_r, esa.u as is_u, esa.d as is_d
                      from #__emundus_acl as ea
                      left join #__emundus_setup_actions as esa on esa.id = ea.action_id
                      where ea.group_id = " .$gid ." order by esa.ordering asc";
            }
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
	  } else
		return array();
    }

    public function getGroupUsers($gid)
    {
        try
        {
            $query = "select eu.*
                      from #__emundus_groups as eg
                      left join #__emundus_users as eu on eu.user_id = eg.user_id
                      where eg.group_id = " .$gid ." order by eu.lastname asc";
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadAssocList();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getMenuList($params)
    {
        return EmundusHelperFiles::getMenuList($params);
    }

    public function getActionsACL()
    {
        return EmundusHelperFiles::getMenuActions();
    }

    public function getUserActionByFnum($aid, $fnum, $uid, $crud)
    {
        $dbo = $this->getDbo();
        $query = "select ".$crud." from #__emundus_users_assoc where action_id = ".$aid." and user_id = ".$uid." and fnum like ".$dbo->quote($fnum);
        $dbo->setQuery($query);
        $res = $dbo->loadResult();
        return $res;
    }

    public function getGroupActions($gids, $fnum, $aid, $crud)
    {
        $dbo = $this->getDbo();
        $query = "select ".$crud." from #__emundus_group_assoc where action_id = ".$aid." and group_id in (".implode(',', $gids).") and fnum like ".$dbo->quote($fnum);
        $dbo->setQuery($query);
        $res = $dbo->loadAssocList();
        return $res;
    }

    /**
     * @param $uid  int user id
     * @param $passwd   string  password to set
     * @return mixed
     */
    public function setNewPasswd($uid, $passwd)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__users SET password = '.$dbo->Quote($passwd).' WHERE id='.$uid;
        $dbo->setQuery($query);
        return $dbo->execute();
    }



}