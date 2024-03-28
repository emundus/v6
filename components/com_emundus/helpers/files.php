<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 eMundus. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

/**
 * eMundus Component Query Helper
 *
 * @static
 * @package     Joomla
 * @subpackage  eMundus
 * @since 1.5
 */
class EmundusHelperFiles
{

    /*
    ** @description Clear session and reinit values by default
    */
    public static function clear()
    {
        $session = JFactory::getSession();
        $session->set('filt_params', array());
        $session->set('select_filter',null);
        $session->set('adv_cols', array());
        $session->clear('filter_order');
        $session->clear('filter_order_Dir');
        $limit = JFactory::getApplication()->getCfg('list_limit');
        $limitstart = 0;
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $session->set('limit', $limit);
        $session->set('limitstart', $limitstart);

        //@EmundusHelperFiles::resetFilter();

    }

    /*
    ** @description Clear session and reinit values by default
    */
    public static function clearfilter()
    {
        $session = JFactory::getSession();
        $session->set('filt_params', array());
        $session->set('select_filter',null);
        $session->clear('filter_order');
        $session->clear('filter_order_Dir');

        $limit = JFactory::getApplication()->getCfg('list_limit');
        $limitstart = 0;
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $session->set('limit', $limit);
        $session->set('limitstart', $limitstart);

    }

    public function setMenuFilter() {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

        $current_user   = JFactory::getUser();
        $menu           = @JFactory::getApplication()->getMenu();
        $current_menu   = $menu->getActive();
        $Itemid         = JFactory::getApplication()->input->getInt('Itemid', @$current_menu->id);
        $menu_params    = $menu->getParams($Itemid);
        $m_files        = new EmundusModelFiles();

        $session = JFactory::getSession();
        $params = $session->get('filt_params');

        //Filters
        $tables         = explode(',', $menu_params->get('em_tables_id'));
        $filts_names    = explode(',', $menu_params->get('em_filters_names'));
        $filts_values   = explode(',', $menu_params->get('em_filters_values'));
        $filts_types    = explode(',', $menu_params->get('em_filters_options'));

        // All types of filters
        $filts_details  = [
            'profile'           => NULL,
            'evaluator'         => NULL,
            'evaluator_group'   => NULL,
            'schoolyear'        => NULL,
            'campaign'          => NULL,
            'programme'         => NULL,
            'missing_doc'       => NULL,
            'complete'          => NULL,
            'finalgrade'        => NULL,
            'validate'          => NULL,
            'other'             => NULL,
            'status'            => NULL,
            'published'         => NULL,
            'adv_filter'        => NULL,
            'newsletter'        => NULL,
            'group'             => NULL,
            'institution'       => NULL,
            'spam_suspect'      => NULL,
            'not_adv_filter'    => NULL,
            'tag'               => NULL
        ];
        $filts_options  = [
            'profile'           => NULL,
            'evaluator'         => NULL,
            'evaluator_group'   => NULL,
            'schoolyear'        => NULL,
            'campaign'          => NULL,
            'programme'         => NULL,
            'missing_doc'       => NULL,
            'complete'          => NULL,
            'finalgrade'        => NULL,
            'validate'          => NULL,
            'other'             => NULL,
            'status'            => NULL,
            'published'         => NULL,
            'adv_filter'        => NULL,
            'newsletter'        => NULL,
            'group'             => NULL,
            'institution'       => NULL,
            'spam_suspect'      => NULL,
            'not_adv_filter'    => NULL,
            'tag'               => NULL
        ];

        $filter_multi_list = array('schoolyear', 'campaign', 'programme', 'status', 'profile_users', 'group', 'institution', 'tag');

        foreach ($filts_names as $key => $filt_name) {

            if (isset($filts_values[$key]) && empty($params[$filt_name])) {
                if (in_array($filt_name, $filter_multi_list)) {
                    $params[$filt_name] = explode('|', $filts_values[$key]);
                    $params[$filt_name] = array_unique($params[$filt_name]);
                } else {
                    $params[$filt_name] = $filts_values[$key];
                }
            }

            if (array_key_exists($key, $filts_values)) {
                if (in_array($filt_name, $filter_multi_list)) {
                    $filts_details[$filt_name] = explode('|', $filts_values[$key]);
                } else {
                    $filts_details[$filt_name] = $filts_values[$key];
                }
            } else {
                $filts_details[$filt_name] = '';
            }

            if (array_key_exists($key, $filts_types)) {
                if ($filts_types[$key] == "hidden") {
                    if (in_array($filt_name, $filter_multi_list)) {
                        $params[$filt_name] = explode('|', $filts_values[$key]);
                    } else {
                        $params[$filt_name] = $filts_values[$key];
                    }
                }
                $filts_options[$filt_name] = $filts_types[$key];
            } else {
                $filts_options[$filt_name] = '';
            }

        }

        if (is_array($filts_details['group']) && count($filts_details['group']) > 0 && isset($filts_details['group'][0]) && !empty($filts_details['group'][0])) {
            $fd_with_param          = $params['group'] + $filts_details['group'];
            $params['group']        = $filts_details['group'];
            $filts_details['group'] = $fd_with_param;
        }

        if (is_array($filts_details['institution']) && count($filts_details['institution']) > 0 && isset($filts_details['institution'][0]) && !empty($filts_details['institution'][0])) {
            $fd_with_param = $params['institution'] + $filts_details['institution'];
            $params['institution'] = $filts_details['institution'];
            $filts_details['institution'] = $fd_with_param;
        }

        // Else statement is present due to the fact that programmes are group limited
        if ((is_array($filts_details['programme']) && count($filts_details['programme']) > 0) && isset($filts_details['programme'][0]) && !empty($filts_details['programme'][0])) {
            $fd_with_param = $params['programme'] + $filts_details['programme'];
            $params['programme'] = $filts_details['programme'];
            $filts_details['programme'] = $fd_with_param;
        } else {
            $codes = $m_files->getAssociatedProgrammes($current_user->id);

            // ONLY FILES LINKED TO MY GROUP
            $programme = null;
            if (!empty($filts_details['programme']) || $filts_details['programme'] !== NULL) {
                $programme = !empty($m_files->code) ? $m_files->code : '';
            }

            if ((is_array($filts_details['programme']) && !empty($params['programme']))) {
                $params['programme'] = $programme;
            }
            $filts_details['programme'] = $programme;

            // TODO: code is never set so we were never passing here ? What was it used for ?
            /*if ((is_array($codes) && count($codes)) > 0 && isset($code)) {
                $params['programme'] = array_merge($params['programme'], $codes);
                $filts_details['programme'] = array_merge($filts_details['programme'], $codes);
            }*/
        }

        // Used for adding default columns when no programme is loaded.
        if (empty($params['programme'])) {
            $params['programme'] = ["%"];
        }

        // If there is no campaign value, set the campaign param as an empty array, for real
        if ((is_array($params['campaign']) && count($params['campaign']) == 1 && $params['campaign'][0] == '') || (is_string($params['campaign']) && empty($params['campaign']))) {
            $params['campaign'] = [];
        }

        $session->set('filt_params', $params);
        $session->set('filt_menu', $filts_details);
        $filters['filts_details'] = $filts_details;
        $filters['filts_options'] = $filts_options;

        $filters['tables'] = $tables;
        return $filters;
    }

    /*
    ** @description Clear session and reinit values by default
    */
    public static function resetFilter() {
        $h_files = new EmundusHelperFiles;
        $filters = $h_files->setMenuFilter();
        return $h_files->createFilterBlock($filters['filts_details'], $filters['filts_options'], $filters['tables']);
    }


    /*
    * @param            query results
    * @param    array   values to extract and insert
    */
    public static function insertValuesInQueryResult($results, $options) {
        foreach ($results as $key => $result) {
            if (array_key_exists('params', $result)) {
                if (is_array($result)) {
                    $params = json_decode($result['params']);
                    foreach ($options as $option) {
                        if (property_exists($params, 'sub_options') && array_key_exists($option, $params->sub_options)) {
	                        $results[$key][$option] = implode('|', $params->sub_options->$option);
                        } else {
	                        $results[$key][$option] = '';
                        }
                    }
                } else {
                    $params = json_decode($result->params);
                    foreach ($options as $option) {
                        if (property_exists($params, 'sub_options') && array_key_exists($option, $params->sub_options)) {
	                        $results[$key]->$option = implode('|', $params->sub_options->$option);
                        } else {
	                        $results[$key]->$option = '';
                        }
                    }
                }
            }
        }
        return $results;
    }

    public static function getCurrentCampaign() {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $nb_months_registration_period_access = $eMConfig->get('nb_months_registration_period_access', '11');
        $config     = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
		$now = JFactory::getDate()->setTimezone($timezone);

        $db = JFactory::getDBO();
        $query = 'SELECT DISTINCT year as schoolyear
        FROM #__emundus_setup_campaigns
        WHERE published = 1 AND end_date > DATE_ADD("'.$now.'", INTERVAL -'.$nb_months_registration_period_access.' MONTH) ORDER BY schoolyear DESC';
        $db->setQuery( $query );
        try {
            return $db->loadColumn();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getCurrentCampaignsID() {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $nb_months_registration_period_access = $eMConfig->get('nb_months_registration_period_access', '11');
        $config = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
		$now = JFactory::getDate()->setTimezone($timezone);

        $db = JFactory::getDBO();
        $query = 'SELECT id
        FROM #__emundus_setup_campaigns
        WHERE published = 1 AND end_date > DATE_ADD("'.$now.'", INTERVAL -'.$nb_months_registration_period_access.' MONTH)
        ORDER BY year DESC';
        $db->setQuery( $query );
        try {
            return $db->loadColumn();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCampaigns() {
        $session    = JFactory::getSession();
        $params     = $session->get('filt_params');
        $filt_menu  = $session->get('filt_menu'); // came from menu filter (see EmundusHelperFiles::resetFilter)

        if (isset($filt_menu['programme'][0]) && ($filt_menu['programme'][0] == "%" || $filt_menu['programme'][0] == "")) {
            $where = '1=1';
        } elseif ((is_array($filt_menu['programme']) && count($filt_menu['programme']) > 0) && isset($filt_menu['programme'][0]) && !empty($filt_menu['programme'][0])) {
            $where = ' training IN ("'.implode('","', $filt_menu['programme']).'") ';
        } else {

            if (!empty($params) && !empty($params['programme']) && (is_array($params['programme']) && count($params['programme']) > 0) && $params['programme'][0] != '%') {
                $code = implode('","', $params['programme']);
                $where = 'training IN ("'.$code.'")';
            } else
                $where = '1=1';
        }
        $db = JFactory::getDBO();
        $query = 'SELECT id, label, year  FROM #__emundus_setup_campaigns WHERE published=1 AND '.$where.' ORDER BY year DESC';

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getProgramCampaigns($code) {
        $db = JFactory::getDBO();

        $query = 'SELECT *  FROM #__emundus_setup_campaigns WHERE published=1 AND training  LIKE "'.$code.'" ORDER BY year DESC';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function getProgrammes($code = array()) {
        $db = JFactory::getDBO();
        if (!empty($code) && is_array($code)) {
            if ($code[0] == '%' || $code[0] == '') {
                // ONLY FILES LINKED TO MY GROUPS
                require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
                $m_users = new EmundusModelUsers();
                $user = JFactory::getUser();
                $code = $m_users->getUserGroupsProgrammeAssoc($user->id);
                $where = 'code IN ("'.implode('","', $code).'")';
                //////////////////////////////////
            } else {
	            $where = 'code IN ("'.implode('","', $code).'")';
            }
        } else {
	        $where = '1=1';
        }
        $query = 'SELECT *  FROM #__emundus_setup_programmes WHERE published=1 AND '.$where.' ORDER BY label,ordering ASC';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public static function getStatus() {
        $db = JFactory::getDBO();
        $query = 'SELECT *  FROM #__emundus_setup_status ORDER BY ordering';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public static function getCampaign() {
        $db = JFactory::getDBO();
        $query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
        $db->setQuery( $query );
        $syear = $db->loadRow();

        return $syear[0];
    }

    public static function getCampaignByID($id) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_campaigns WHERE id='.$id;
        $db->setQuery( $query );

        return $db->loadAssoc();
    }

    public static function getApplicants() {
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label, esp.published
        FROM #__emundus_setup_profiles esp
        WHERE esp.status=1 and esp.id <> 1';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public function getProfiles() {
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft
        FROM #__emundus_setup_profiles esp
        INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
        WHERE esp.status=1
        ORDER BY caag.lft, esp.label';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public function getEvaluators(){
        $db = JFactory::getDBO();
        $query = 'SELECT u.id, u.name
        FROM #__users u
        LEFT JOIN #__emundus_users eu ON u.id = eu.user_id
        LEFT JOIN #__emundus_users_profiles eup ON u.id=eup.user_id
        LEFT JOIN #__emundus_setup_profiles esp ON (esp.id=eup.profile_id OR esp.id=eu.profile)
        WHERE esp.is_evaluator=1 AND esp.status=1
        ORDER BY u.name';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public function getGroupsEval() {
        $db = JFactory::getDBO();
        $query = 'SELECT ege.group_id
        FROM #__emundus_groups_eval ege
        ORDER BY ege.group_id';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getGroups() {
        $db = JFactory::getDBO();
        $query = 'SELECT esg.id, esg.label
        FROM #__emundus_setup_groups esg
        WHERE esg.published=1
        ORDER BY esg.label';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public function getSchoolyears() {
        $db = JFactory::getDBO();
        $query = 'SELECT DISTINCT(schoolyear)
            FROM #__emundus_setup_teaching_unity
            WHERE published = 1
            ORDER BY schoolyear DESC';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getFinal_grade() {
        $db = JFactory::getDBO();
        $query = 'SELECT name, params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
        $db->setQuery( $query );
        $h_files = new EmundusHelperFiles;
        return $h_files->insertValuesInQueryResult($db->loadAssocList('name'), array("sub_values", "sub_labels"));
    }

    public function getMissing_doc() {
        $db = JFactory::getDBO();
        $query = 'SELECT DISTINCT(esap.attachment_id), esa.value
                FROM #__emundus_setup_attachment_profiles esap
                LEFT JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getAttachmentsTypesByProfileID ($pid) {
        $attachments_by_profile = [];

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('attachment_id')
            ->from($db->quoteName('#__emundus_setup_attachment_profiles'))
            ->where($db->quoteName('profile_id') . ' IN (' . implode(',', $pid) . ')');
        $db->setQuery($query);
        $attachments = $db->loadColumn();

        if (!empty($attachments)) {
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_setup_attachments'))
                ->where($db->quoteName('id') . ' IN (' . implode(',', $attachments) . ')')
                ->order('ordering');

            $db->setQuery($query);
            $attachments_by_profile = $db->loadObjectList();
        }

        return $attachments_by_profile;
    }

	public function getEvaluation_doc($status) {
        $db = JFactory::getDBO();
        $query = 'SELECT *
                FROM #__emundus_setup_attachments esa
                WHERE id IN (
                    SELECT distinct(esl.attachment_id)
                    FROM #__emundus_setup_letters esl
                    LEFT JOIN #__emundus_setup_letters_repeat_status eslr ON eslr.parent_id=esl.id
                    WHERE esl.status='.$status.'
                    )
                ORDER BY esa.value';
            $db->setQuery( $query );
            return $db->loadObjectList();
    }

    public function setEvaluationList($status) {
        $option_list =  @EmundusHelperFilters::getEvaluation_doc($status);
        $current_filter = '';
        if (!empty($option_list)){
            $current_filter = '<select name="attachment_id" id="attachment_id">';
            foreach ($option_list as $value){
                $current_filter .= '<option value="'.$value->id.'">'.$value->value.'</option>';
            }
            $current_filter .= '</select>';
        }
        return $current_filter;
    }

	/**
	 * @param array $code
	 * @param array $camps
	 * @param array $fabrik_elements
     * @param int $profile --> to get all form elems of a profile
	 *
	 * @return array
	 */

    public static function getElements($code = array(), $camps = array(), $fabrik_elements = array(), $profile=null) : array {
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

        $h_menu = new EmundusHelperMenu;
        $m_user = new EmundusModelUsers;
        $m_profile = new EmundusModelProfile;
        $m_campaign = new EmundusModelCampaign;

        $db = JFactory::getDBO();

        if (empty($code)) {
            $params = JFactory::getSession()->get('filt_params');
            $programme = $params['programme'];
            $campaigns = $params['campaign'];

            if (empty($programme) && empty($campaigns)) {
                $programme = $m_campaign->getLatestCampaign();
            }

            // get profiles for selected programmes or campaigns
            $plist = $m_profile->getProfilesIDByCampaign((array)$campaigns) ?: $m_profile->getProfileIDByCourse((array)$programme);

        } else {
            $plist = $m_profile->getProfileIDByCourse($code, $camps);
        }

        if(!is_null($profile)) {
            /// get profile id from $profile --> using split

            $profiles = $m_user->getApplicantProfiles();

            foreach($profiles as$k=>$v) {
                if($v->menutype == $profile) {
                    $prid = $v->id;
                }
            }

            $menu_list = $h_menu->buildMenuQuery($prid);

            $fl = array();

            foreach ($menu_list as $m) {
                $fl[] = $m->table_id;
            }

            if (empty($fl)) {
                return array();
            }

            $query = 'SELECT distinct(concat_ws("___",tab.db_table_name,element.name)) as fabrik_element, element.id, element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin, element.id, groupe.id AS group_id, groupe.label AS group_label, element.params AS element_attribs,
                    INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, form.label AS table_label, tab.created_by_alias, joins.table_join, menu.id as menu_id, menu.title,
                    p.label, p.id as profil_id
                    FROM #__fabrik_elements element';
            $join = 'INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                    INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                    INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
                    INNER JOIN #__fabrik_forms AS form ON tab.form_id = form.id
                    LEFT JOIN #__fabrik_joins AS joins ON (tab.id = joins.list_id AND (groupe.id=joins.group_id OR element.id=joins.element_id))
                    INNER JOIN #__menu AS menu ON form.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)
                    INNER JOIN #__emundus_setup_profiles as p on p.menutype=menu.menutype ';
            $where = 'WHERE tab.published = 1 AND groupe.published = 1 AND p.id = ' . $prid;

            if (count($fabrik_elements) > 0) {
                $where .= ' AND element.id IN (' . implode(',', $fabrik_elements) . ') ';
                $order = '';
            } else {
                $where .= ' AND (tab.id IN ( ' . implode(',', $fl) . ' ))         
                        AND element.published=1
                        AND element.hidden=0
                        AND element.label!=" "
                        AND element.label!=""
                        AND menu.menutype = ' . '"' . $profile .'"' . '
                        AND element.plugin!="display"';
                $order = 'ORDER BY menu.lft, formgroup.ordering, element.ordering';
            }

            $query .= ' ' . $join . ' ' . $where . ' ' . $order;
            try {

                $db->setQuery($query);
                $elements = $db->loadObjectList('id');

                $elts = array();
                $allowed_groups = EmundusHelperAccess::getUserFabrikGroups(JFactory::getUser()->id);
                if (count($elements) > 0) {
                    foreach ($elements as $key => $value) {
                        if ($allowed_groups !== true && is_array($allowed_groups) && !in_array($value->group_id, $allowed_groups)) {
                            continue;
                        }
                        $value->id = $key;
	                    $value->table_label = JText::_($value->table_label);
	                    $value->group_label = JText::_($value->group_label);
	                    $value->element_label = JText::_($value->element_label);
                        $elts[] = $value;
                    }
                }
				
                return $elts;

            } catch (Exception $e) {
                echo $e->getMessage();
                return array();
            }
        } else if ($plist) {
            // get Fabrik list ID for profile_id$where .= ' AND (tab.id IN ( ' . implode(',', $fl) . ' ))
            $fl = array();
            $menutype = array();
            // get all profiles
            $profiles = $m_user->getApplicantProfiles();

            foreach ($profiles as $profile) {
                if (is_array($plist) && count($plist) == 0 || (count($plist) > 0 && in_array($profile->id, $plist))) {
                    $menu_list = $h_menu->buildMenuQuery($profile->id);
                    foreach ($menu_list as $m) {
                        $fl[] = $m->table_id;
                        $menutype[$profile->id] = $m->menutype;
                    }
                }
            }

            if (empty($fl)) {
                return array();
            }

            $query = 'SELECT distinct(concat_ws("___",tab.db_table_name,element.name)) as fabrik_element, element.id, element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin, element.id, groupe.id AS group_id, groupe.label AS group_label, element.params AS element_attribs,
                    INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, form.label AS table_label, tab.created_by_alias, joins.table_join,menu.id as menu_id, menu.title,
                    p.label, p.id as profil_id
                    FROM #__fabrik_elements element';
            $join = 'INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                    INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                    INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
                    INNER JOIN #__fabrik_forms AS form ON tab.form_id = form.id
                    LEFT JOIN #__fabrik_joins AS joins ON (tab.id = joins.list_id AND (groupe.id=joins.group_id OR element.id=joins.element_id))
                    INNER JOIN #__menu AS menu ON form.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)
                    INNER JOIN #__emundus_setup_profiles as p on p.menutype=menu.menutype ';
            $where = 'WHERE tab.published = 1 AND groupe.published = 1 ';

            if (is_array($plist) && count($plist) > 0) {
                $where .= ' AND p.id IN (' . implode(',', $plist) . ') ';
            }

            if (count($fabrik_elements) > 0 ) {

                $where .= ' AND element.id IN (' . implode(',', $fabrik_elements) . ') ';
                $order ='';

            } else {

                $where .= ' AND (tab.id IN ( ' . implode(',', $fl) . ' ))
                        AND element.published=1
                        AND element.hidden=0
                        AND element.label!=" "
                        AND element.label!=""
                        AND menu.menutype IN ( "' . implode('","', $menutype) . '" ) 
                        AND element.plugin!="display"';
                $order = 'ORDER BY menu.lft, formgroup.ordering, element.ordering';
            }

            $query .= ' ' . $join . ' ' . $where . ' ' . $order;
            try {

                $db->setQuery($query);
                $elements = $db->loadObjectList('id');

                $elts = array();
                $allowed_groups = EmundusHelperAccess::getUserFabrikGroups(JFactory::getUser()->id);
                if (count($elements) > 0) {
                    foreach ($elements as $key => $value) {
                        if ($allowed_groups !== true && is_array($allowed_groups) && !in_array($value->group_id, $allowed_groups)) {
                            continue;
                        }
                        $value->id = $key;
	                    $value->table_label = JText::_($value->table_label);
	                    $value->group_label = JText::_($value->group_label);
	                    $value->element_label = JText::_($value->element_label);
                        $elts[] = $value;
                    }
                }
                return $elts;

            } catch (Exception $e) {
                echo $e->getMessage();
                return array();
            }

        } else {
            return array();
        }
    }

    /**
     * Get Fabrik element by ID
     * @param   int     Fabrik ID ?
     * @return  Object  Fabrik element
     **/
    function getElementById($element_id) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__fabrik_elements element WHERE id='.$element_id;
        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * @param $fnum
     *
     * @return array|false|string|void
     *
     * @since version
     */
    public function getPhotos($fnum = null) {

        $m_files = new EmundusModelFiles;

        try {

            $fnums = array();
            $pictures = array();

            if ($fnum != null) {

                $fnums[] = $fnum;
                $photos = $m_files->getPhotos($fnums);

                foreach ($photos as $photo) {
                    $folder = JURI::base().EMUNDUS_PATH_REL.$photo['user_id'];
                    if(file_exists($folder . '/tn_'. $photo['filename'])) {
                        return '<img class="img-responsive" alt="photo" src="' . $folder . '/tn_' . $photo['filename'] . '" width="60" /></img>';
                    } else {
                        return '<img class="img-responsive" alt="photo" src="' . $folder . DS. $photo['filename'] . '" width="60" /></img>';
                    }
                }

            } else {

                $photos = $m_files->getPhotos();
                foreach ($photos as $photo) {
                    $folder = JURI::base().EMUNDUS_PATH_REL.$photo['user_id'];

                    if(file_exists($folder . '/tn_'. $photo['filename'])) {
                        $pictures[$photo['fnum']] = '<img class="img-responsive" alt="photo" src="'.$folder . '/tn_'. $photo['filename'] . '" width="60" /></img>';
                    } else {
                        $pictures[$photo['fnum']] = '<img class="img-responsive" alt="photo" src="'.$folder . DS . $photo['filename'] . '" width="60" /></img>';
                    }
                }
                return $pictures;

            }

        } catch (Exception $e) {
            return false;
        }
    }



    /**
     * Get list of elements declared in a list of Fabrik groups
     * @param   string  List of Fabrik groups comma separated
     * @param   int     Does the element are shown in Fabrik list ?
     * @return   array  list of Fabrik element ID used in evaluation form
     **/
    function getElementsByGroups($groups, $show_in_list_summary=1, $hidden=0){
        $db = JFactory::getDBO();
        $query = 'SELECT element.name, element.label, element.plugin, element.id as element_id, groupe.id, groupe.label AS group_label, element.params,
                INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, tab.label AS table_label, tab.created_by_alias
                FROM #__fabrik_elements element
                INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
                INNER JOIN #__fabrik_forms AS form ON tab.form_id = form.id
                WHERE tab.published = 1 ';

        $query .= $show_in_list_summary==1?' AND element.show_in_list_summary = 1 ':'';
        $query .= $hidden==0?' AND element.hidden = 0 ':'';
        $query .= ' AND element.published=1
                    AND groupe.id IN ('.$groups.')
                    AND element.label!=" "
                    AND element.label!=""
                ORDER BY formgroup.ordering, groupe.id, element.ordering';
        //die(str_replace("#_", "jos", $query));
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function getElementsOther($tables){
        $db = JFactory::getDBO();

        $query = 'SELECT distinct(concat_ws("_",tab.db_table_name,element.name)), element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin, element.id, groupe.id as group_id, groupe.label AS group_label, element.params AS element_attribs,
            INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, tab.label AS table_label
                FROM #__fabrik_elements element
                INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id';

        if (!empty($tables) && !empty($tables[0])) {
            $query .= ' WHERE tab.id IN(';
            $first = true;
            foreach ($tables as $table) {
                if ($first) {
                    $query .= $table;
                    $first = false;
                } else {
                	$query .= ', '.$table;
                }
            }
            $query .= ') AND ';
        } else {
	        $query .= ' WHERE ';
        }

        $query .= 'element.name NOT IN ("id", "time_date", "user", "student_id", "type_grade", "final_grade")
                ORDER BY group_id';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getElementsValuesOther($element_id) {
        //jimport( 'joomla.registry.format.json' );
        $db = JFactory::getDBO();
        $query = 'SELECT params FROM #__fabrik_elements element WHERE id='.$element_id;
        $db->setQuery($query);
        $res = $db->loadResult();
        $sub = json_decode($res);//JRegistryFormatJson::stringToObject($res);

        return $sub->sub_options;
    }

	/**
	 * @param $elements_id string of elements id separated by comma
	 * @return array|false
	 */
    public static function getElementsName($elements_id) {
		$elements = [];

        if (!empty($elements_id) && !empty(ltrim($elements_id))) {
            $db = JFactory::getDBO();

	        $query = $db->getQuery(true);
			$query->select('element.id, element.name AS element_name, element.label as element_label, element.params AS element_attribs, element.plugin as element_plugin, element.hidden as element_hidden, forme.id as form_id, forme.label as form_label, groupe.id as group_id, groupe.label as group_label, groupe.params as group_attribs,tab.db_table_name AS tab_name, tab.id as table_list_id, tab.created_by_alias AS created_by_alias, joins.join_from_table, joins.table_join, joins.table_key, joins.table_join_key')
				->from('#__fabrik_elements AS element')
				->join('INNER', '#__fabrik_groups AS groupe ON element.group_id = groupe.id')
				->join('INNER', '#__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id')
				->join('INNER', '#__fabrik_forms AS forme ON formgroup.form_id = forme.id')
				->join('INNER', '#__fabrik_lists AS tab ON tab.form_id = formgroup.form_id')
				->join('LEFT', '#__fabrik_joins AS joins ON (tab.id = joins.list_id AND (groupe.id=joins.group_id OR element.id=joins.element_id))')
				->where('element.id IN ('.ltrim($elements_id, ',').')')
				->order('find_in_set(element.id, "' . ltrim($elements_id, ',') . '")');

			try {
                $db->setQuery($query);
                $res = $db->loadObjectList('id');
            } catch (Exception $e) {
            	JLog::add('Could not get Evaluation elements name in query -> '.$query, JLog::ERROR, 'com_emundus');
                return false;
            }

            $elementsIdTab = array();
            foreach ($res as $kId => $r) {
                $elementsIdTab[$kId] = $r;
            }
	        $elements = $elementsIdTab;
        }

		return $elements;
    }

    /*
     *  @description    replace a tag like {fabrik_element_id} by the application form value for current application file
     *  @param          $fum            string   application file number
     *  @param          $element_id     string   Fabrik element ID
     *  @return         array           array of application file elements value
     */
    public function getFabrikElementValue($fnum, $element_id) {
        $db = JFactory::getDBO();
        $h_files = new EmundusHelperFiles();

        $element_details = $h_files->getElementsDetailsByID($element_id);

        $query = 'SELECT '.$element_details[0]->element_name.' FROM '.$element_details[0]->tab_name.' WHERE fnum like '.$db->Quote($fnum);
        $db->setQuery($query);
        return $db->loadResult();
    }

    /*
     *  @description    replace tags like {fabrik_element_id} by the application form value for current application file
     *  @param          $fum            string  application file number
     *  @param          $element_ids    array   Fabrik element ID
     *  @return         array           array of application file elements values
     */
    public function getFabrikElementValues($fnum, $element_ids) {
        $db = JFactory::getDBO();
	    $h_files = new EmundusHelperFiles();

        $element_details = $h_files->getElementsDetailsByID('"'.implode('","', $element_ids).'"');

        foreach ($element_details as $value) {
            $query = 'SELECT '.$value->element_name.' FROM '.$value->tab_name.' WHERE fnum like '.$db->Quote($fnum);
            $db->setQuery($query);
            $element_values[$value->element_id] = $db->loadResult();
        }

        return $element_values;
    }

    /*
     * @description Get Fabrik elements detail from elements Fabrik ID
     * @param   string  $elements   list of Fabrik element comma separated.
     * @return  array   Array of Fabrik element params.
     */
    function getElementsDetailsByID($elements) {
        $db = JFactory::getDBO();

        $query = 'SELECT element.name AS element_name, element.label AS element_label, element.id AS element_id, tab.db_table_name AS tab_name, element.plugin AS element_plugin,
                element.params AS params, element.params, tab.group_by AS tab_group_by
                FROM #__fabrik_elements element
                INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
                WHERE element.id IN ('.$elements.')';
        $db->setQuery($query);

        return @EmundusHelperFilters::insertValuesInQueryResult($db->loadObjectList(), array("sub_values", "sub_labels", "element_value"));
    }

    public function buildOptions($element_name, $params) {
        $db = JFactory::getDBO();

        if (!empty($params->join_key_column)) {
            $join_val = $params->join_val_column;
            if (!empty($params->join_val_column_concat)) {
                $join_val = '( SELECT CONCAT('.str_replace('{thistable}', $params->join_db_name, preg_replace('#{shortlang}#', substr(JFactory::getLanguage()->getTag(), 0 , 2), $params->join_val_column_concat)).'))';
            }

            if ($element_name == 'result_for') {
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$join_val.' AS elt_val FROM '.$params->join_db_name.' WHERE published=1';
            } elseif ($element_name == 'campaign_id') {
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$join_val.' AS elt_val FROM '.$params->join_db_name;
            } elseif ($element_name=='training_id') {
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$join_val.' AS elt_val FROM '.$params->join_db_name.' ORDER BY '.str_replace('{thistable}', $params->join_db_name, $params->join_db_name.'.date_start ');
            } else {
                $params->database_join_where_sql =  (strpos($params->database_join_where_sql, '{rowid}') === false && strpos($params->database_join_where_sql, 'raw') === false ) ? $params->database_join_where_sql : '';
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$join_val.' AS elt_val FROM '.$params->join_db_name.' '.str_replace('{thistable}', $params->join_db_name, preg_replace('{shortlang}', substr(JFactory::getLanguage()->getTag(), 0 , 2), $params->database_join_where_sql));
            }
            $db->setQuery($query);
            $result = $db->loadObjectList();

        } else {
            if(!empty($params->cascadingdropdown_id)) {
                $r1 = explode('___', $params->cascadingdropdown_id);
                $r2 = explode('___', $params->cascadingdropdown_label);
                $where = (isset($params->cascadingdropdown_filter) && !empty($params->cascadingdropdown_filter)) ? ' WHERE '.$params->cascadingdropdown_filter :'';
                $query = 'SELECT '.$r1[1].' AS elt_key, '.$r2[1].' AS elt_val FROM '.$r1[0].$where;
                $db->setQuery($query);
                $result = $db->loadObjectList();
            } else {
                $i = 0;
                foreach ($params->sub_options->sub_values as $value) {
                    $result[] = (object)array('elt_key' => $value, 'elt_val' => $params->sub_options->sub_labels[$i]);
                    $i++;
                }
            }
        }
        return $result;
    }

    /*
    ** @description Create the WHERE query.
    ** @param array $search Liste of search element.
    ** @param array $search_values Liste of search values.
    ** @param string $query Name for HTML tag.
    ** @return string The query WHERE.
    */
    public static function setWhere($search, $search_values, &$query) {
        if (isset($search) && !empty($search)) {
            $i = 0;
            foreach ($search as $s) {
                if ((!empty($search_values[$i]) || isset($search_values[$i])) && $search_values[$i] != "" ) {
                    $tab = explode('.', $s);
                    if (count($tab)>1) {
                        if ($tab[0] == 'jos_emundus_training'){
                            $query .= ' AND ';
                            $query .= ' search_'.$tab[0].'.id like "%'.$search_values[$i].'%"';
                        } else {
                            $query .= ' AND ';
                            $query .= $tab[0].'.'.$tab[1].' like "%'.$search_values[$i].'%"';
                        }
                    }
                }
                $i++;
            }
        }
        return $query;
    }

    /*
    ** @description Create the search options for Advance filter and Other filter.
    ** @param array $selected Selected Fabrik component element.
    ** @param array $search_value Search values for selected elements.
    ** @param string $elements_values Name for HTML tag.
    ** @return string HTML to display for filters options.
    */
    public function setSearchBox($selected, $search_value, $elements_values, $i = 0) {
        jimport('joomla.html.parameter');
	    $h_files = new EmundusHelperFiles();

        $current_filter = "";
        if (!empty($selected)) {
            if ($selected->element_plugin == "databasejoin" || $selected->element_plugin == "cascadingdropdown") {
                $query_params = json_decode($selected->element_attribs);
                $option_list =  $h_files->buildOptions($selected->element_name, $query_params);
                $current_filter .= '<br/><select class="chzn-select em-filt-select" id="em-adv-fil-'.$i.'"  name="'.$elements_values.'" id="'.$elements_values.'">
                <option value="">'.JText::_('COM_EMUNDUS_PLEASE_SELECT').'</option>';
                if (!empty($option_list)) {
                    foreach ($option_list as $value) {
                        $current_filter .= '<option value="'.$value->elt_key.'"';

                        if ($value->elt_key == $search_value) {
	                        $current_filter .= ' selected';
                        }

                        $current_filter .= '>'.JText::_($value->elt_val).'</option>';
                    }
                }
                $current_filter .= '</select>';
            }

            elseif ($selected->element_plugin == "checkbox" || $selected->element_plugin == "radiobutton" || $selected->element_plugin == "dropdown") {
                $query_params = json_decode($selected->element_attribs);
                $option_list =  $h_files->buildOptions($selected->element_name, $query_params);
                $current_filter .= '<br/><select class="chzn-select em-filt-select" id="em-adv-fil-'.$i.'" name="'.$elements_values.'" id="'.$elements_values.'">
                <option value="">'.JText::_('COM_EMUNDUS_PLEASE_SELECT').'</option>';
                if (!empty($option_list)) {
                    foreach ($option_list as $value) {
                        $current_filter .= '<option value="'.$value->elt_key.'"';

                        if ($value->elt_key == $search_value) {
	                        $current_filter .= ' selected';
                        }

                        $current_filter .= '>'.JText::_($value->elt_val).'</option>';
                    }
                }
                $current_filter .= '</select>';
            } else {
	            $current_filter .= '<br/><input type="text" id="em-adv-fil-'.$i.'" class="form-control" name="'.$elements_values.'" value="'.$search_value.'" />';
            }
        }

        return $current_filter;
    }

    /*
    ** @description Create a fieldset of filter boxes
    ** @param array $params Filters values indexed by filters names (profile / evaluator / evaluator_group / finalgrade / schoolyear / missing_doc / complete / validate / other / tag).
    ** @param array $types Filters options indexed by filters names.
    ** @param array $tables List of the tables contained in "Other filters" dropbox.
    ** @return string HTML to display in page for filter block.
    */  //$filts_details, $filts_options, $tables
    public function createFilterBlock($params, $types, $tables) {

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $m_files = new EmundusModelFiles;
        $h_files = new EmundusHelperFiles;

        $session = JFactory::getSession();
        $filt_menu = $session->get('filt_menu');
        $filt_params = $session->get('filt_params');
        $select_id = $session->get('select_filter');

        if (!is_null($select_id)) {
            $research_filter = $h_files->getEmundusFilters($select_id);
            $filter =  json_decode($research_filter->constraints, true);
            $filt_params = $filter['filter'];
        }

        $current_s              = @$filt_params['s'];
        $current_profile        = @$filt_params['profile'];
        $current_eval           = @$filt_params['user'];
        $miss_doc               = @$filt_params['missing_doc'];
        $current_finalgrade     = @$filt_params['finalgrade'];
        $current_schoolyear     = @$filt_params['schoolyear'];
        $current_campaign       = @$filt_params['campaign'];
        $current_programme      = @$filt_params['programme'];
        $search                 = @$filt_params['elements'];
        $search_other           = @$filt_params['elements_other'];
        $complete_application   = @$filt_params['complete'];
        $validate_application   = @$filt_params['validate'];
        $current_status         = @$filt_params['status'];
        $current_published      = @$filt_params['published'];
        $current_tag            = @$filt_params['tag'];
        $current_group_eval     = @$filt_params['evaluator_group'];
        $current_user_profile   = @$filt_params['profile_users'];
        $newsletter             = @$filt_params['newsletter'];
        $current_group          = @$filt_params['group'];
        $current_institution    = @$filt_params['institution'];
        $spam_suspect           = @$filt_params['spam_suspect'];
        $current_group_assoc    = @$filt_params['group_assoc'];

        $filters = '';

        $cs = '';
        if (!empty($current_s)) {
            foreach ($current_s as $c) {
                $cs .= $c .',';
            }
            $cs = rtrim($cs, ',');
        }


        // Quick filter
       $filters = '<div id="filters">
                 <p>'.JText::_('COM_EMUNDUS_FILTERS_RAPID_SEARCH').'</p>
                    <div id="quick" class="form-group flex items-center gap-2">
                        <input type="text" id="input-tags" class="input-tags demo-default" value="'.$cs.'" placeholder="'.JText::_('COM_EMUNDUS_ACTIONS_SEARCH').' ...">
                       <input value="&#xf002" type="button" class="btn btn-sm btn-info" id="search" style="font-family: \'FontAwesome\';" title="'.JText::_('COM_EMUNDUS_ACTIONS_SEARCH_BTN').'"/>'.

		        '</div>
	        </div>';
        $filters .= '<script type="text/javascript">
                        $("#input-tags").selectize({
                            plugins: ["remove_button"],
                            persist: false,
                            create: true,
                            render: {
                                item: function(data, escape) {
                                    return "<div>" + escape(data.text) + "</div>";
                                }
                            },
                            onDelete: function() {
                                setTimeout(search, 100);
                                return true;
                            }
                        });
                    </script>';

        // User filter
        $research_filters = $h_files->getEmundusFilters();
        if (empty($research_filters)) {
            $display = 'style = "display: none"';
        } else {
            $display = '';
        }
            $filters .= '<fieldset id="em_select_filter" class="em-user-personal-filter" ' . $display . '>
                            <label for="select_filter" class="control-label em-user-personal-filter-label">' . JText::_('COM_EMUNDUS_FILTERS_SELECT_FILTER') . '</label>
                            <div class="em_select_filter_rapid_search">
                                <select class="chzn-select" id="select_filter" style="width:95%" name="select_filter" >
                                    <option value="0" selected="true" style="font-style: italic;">' . JText::_('COM_EMUNDUS_FILTERS_CHOOSE_FILTER') . '</option>';

            foreach ($research_filters as $filter) {
                if ($select_id == $filter->id) {
                    $filters .= '<option value="' . $filter->id . '" selected="true" >' . $filter->name . '</option>';
                } else {
                    $filters .= '<option value="' . $filter->id . '">' . $filter->name . '</option>';
                }
            }
            $filters .= '</select>

						<button class="btn btn-xs" id="del-filter" title="' . JText::_('COM_EMUNDUS_ACTIONS_DELETE') . '"><span class="material-icons">delete_outline</span></button></div>
                            <div class="alert alert-dismissable alert-success em-alert-filter" id="saved-filter">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>' . JText::_('COM_EMUNDUS_FILTERS_FILTER_SAVED') . '</strong>
                            </div>
                            <div class="alert alert-dismissable alert-success em-alert-filter" id="deleted-filter">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>' . JText::_('COM_EMUNDUS_FILTERS_FILTER_DELETED') . '</strong>
                            </div>
                            <div class="alert alert-dismissable alert-danger em-alert-filter" id="error-filter">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>' . JText::_('COM_EMUNDUS_ERROR_SQL_ERROR') . '</strong>
                            </div>
                        </fieldset>
                		<script type="text/javascript" >' . EmundusHelperJavascript::getPreferenceFilters() . EmundusHelperJavascript::clearAdvanceFilter() . '</script>
                    </fieldset>';
        $filters .= '<script>
                        $(document).ready(function() {

                            $(".search_test").SumoSelect({search: true, searchText: "'.JText::_('COM_EMUNDUS_FILES_ENTER_HERE').'"});
                            $(".testSelAll").SumoSelect({selectAll:true,search:true, searchText: "'.JText::_('COM_EMUNDUS_FILES_ENTER_HERE').'"});

                            if ($("#select_multiple_programmes").val() != null || $("#select_multiple_campaigns").val() != null) {
                                $("#em_adv_filters").show();
                            } else {
                                $("#em_adv_filters").hide();
                            }

	                        $("#select_filter").chosen({width:"95%"});

                        });
                    </script>';



        $filters .= '<fieldset class="em_filters_filedset">
                        <p>'.JText::_('COM_EMUNDUS_FILTERS_CUSTOM_SEARCH').'</p>';

        if (@$params['profile'] !== NULL) {
            $profile = '';
            $hidden = $types['profile'] != 'hidden' ? false : true;

            if (!$hidden) {
                $profile .= '<div class="form-group em-filter" id="profile">
                	            <div class="em_label">
                	            	<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_PROFILE').'</label>
                                </div>';
            }

            $profile .= ' <select class="search_test em-filt-select" id="select_profile" name="profile" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'">
                         	<option value="0">'.JText::_('COM_EMUNDUS_ACTIONS_ALL').'</option>';

            $profiles = $h_files->getApplicants();
			$profiles = array_filter($profiles, function($profile) {
				return $profile->published == 0;
			});

            foreach ($profiles as $prof) {
                $profile .= '<option title="' . $prof->label . '" value="' . $prof->id . '"';
                if (!empty($current_profile) && (in_array($prof->id, $current_profile) || $prof->id == $current_profile)) {
	                $profile .= ' selected="true"';
                }
                $profile .= '>'.$prof->label.'</option>';
            }

            $profile .= '</select>';
            if (!$hidden) {
	            $profile .= '</div>';
            }
            $filters .= $profile;

            // Other profiles
            $profile = '';
            $hidden = $types['o_profiles'] == 'hidden';
            if (!$hidden) {
                $profile .= '<div class="form-group em-filter" id="o_profiles">
                                    <div class="em_label">
                                    	<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_O_PROFILES').'&ensp; <a href="javascript:clearchosen(\'#select_oprofiles\')"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a></label>
                                    </div>';
            }

            $profile .= ' <select class="testSelAll em-filt-select" id="select_oprofiles" multiple="multiple" name="o_profiles" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

            foreach ($profiles as $prof) {
                $profile .= '<option title="' . $prof->label . '" value="'.$prof->id.'" >'.$prof->label.'</option>';
            }
            $profile .= '</select>';

            if (!$hidden) {
	            $profile .= '</div>';
            }

            $filters .= $profile;
        }

        if (@$params['profile_users'] !== NULL) {

            $hidden = $types['profile_users'] == 'hidden';
            $profile_user = '';

            if (!$hidden) {
                $profile_user .= '<div class="form-group em-filter" id="profile_users">
									<div class="em_label">
                                    	<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_USERS_PROFILE_FILTER').'</label>
                                    </div>';
            }

            $profile_user .= ' <select class="search_test em-filt-select" id="select_profile_users" name="profile_users" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                         		<option value="0">'.JText::_('COM_EMUNDUS_ACTIONS_ALL').'</option>';

            $profile_users = $h_files->getProfiles();
            $prefilter = count($filt_params['profile_users']) > 0;

            foreach ($profile_users as $profu) {
                if (!$prefilter || ($prefilter && in_array($profu->id, $params['profile_users']))) {
                    $profile_user .= '<option title="' . $profu->label . '" value="' . $profu->id . '"';
                    if ($current_user_profile == $profu->id)
                        $profile_user .= ' selected="true"';
                    $profile_user .= '>' . $profu->label . '</option>';
                }
            }
            $profile_user .= '</select>';

            if (!$hidden) {
	            $profile_user .= '</div>';
            }
            $filters .= $profile_user;
        }

        if (@$params['evaluator'] !== NULL) {
            $eval = '';
            $hidden = $types['evaluator'] == 'hidden';

            if (!$hidden) {
	            $eval .= '<div class="em_filters em-filter" id="evaluator">
						  		<div class="em_label">
                               		<label class="control-label em-filter-label">' . JText::_('COM_EMUNDUS_GROUPS_ASSESSOR_USER_FILTER') . '</label>
                                </div>
                               <div class="em_filtersElement">';
            }

            $eval .= '<select class="search_test em-filt-select" id="select_user" name="user" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                      	<option value="0">'.JText::_('COM_EMUNDUS_ACTIONS_ALL').'</option>';

            $evaluators = $h_files->getEvaluators();
            foreach ($evaluators as $evaluator) {
                $eval .= '<option title="' . $evaluator->name . '" value="'.$evaluator->id.'"';
                if ($current_eval == $evaluator->id) {
	                $eval .= ' selected="true"';
                }
                $eval .= '>'.$evaluator->name.'</option>';
            }
            $eval .= '</select>';

            if (!$hidden) {
	            $eval .= '</div></div>';
            }

            $filters .= $eval;
        }

        if ($params['evaluator_group'] !== NULL) {
            $group_eval = '';
            $hidden = $types['evaluator_group'] == 'hidden';

            if (!$hidden) {
	            $group_eval .= '<div class="em_filters em-filter" id="gp_evaluator">
									<div class="em_label">
                                   		<label class="control-label em-filter-label">' . JText::_('COM_EMUNDUS_USERS_GROUP_FILTER') . '</label>
                                    </div>
                                    <div class="em_filtersElement">';
            }

            $group_eval .= '<select class="search_test em-filt-select" id="select_groups" name="evaluator_group" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '"" ').'>
                            <option value="0">'.JText::_('COM_EMUNDUS_ACTIONS_ALL').'</option>';

            $groups = $h_files->getGroups();
            foreach ($groups as $group) {
                $group_eval .= '<option title="' . $group->label . '" value="'.$group->id.'"';
                if ($current_group_eval == $group->id) {
	                $group_eval .= ' selected="true"';
                }
                $group_eval .= '>'.$group->label.'</option>';
            }
            $group_eval .= '</select>';

            if (!$hidden) {
	            $group_eval .= '</div></div>';
            }

            $filters .= $group_eval;
        }

        if (@$params['finalgrade'] !== NULL) {
            $hidden = $types['finalgrade'] == 'hidden';
            $finalgrade = $h_files->getFinal_grade();
            $final_gradeList = explode('|', $finalgrade['final_grade']['sub_labels']);
            $sub_values = explode('|', $finalgrade['final_grade']['sub_values']);
            foreach ($sub_values as $sv) {
	            $p_grade[] = "/" . $sv . "/";
            }
            unset($sub_values);
            $final_grade = '';

            if (!$hidden) {
	            $final_grade .= '<div class="em_filters em-filter" id="finalgrade">
                                    <div class="em_label"><label class="control-label">' . JText::_('COM_EMUNDUS_USERS_FINAL_GRADE_FILTER') . '</label></div>
                                    <div class="em_filtersElement">';
            }

            $final_grade .= '<select class="search_test em-filt-select" id="select_finalgrade" name="finalgrade" '.($types['finalgrade'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                             <option value="0">'.JText::_('COM_EMUNDUS_PLEASE_SELECT').'</option>';
            $groupe = "";
            for ($i = 0; $i < count($final_gradeList); $i++) {
                $val = substr($p_grade[$i], 1, 1);
                $final_grade .= '<option title="' . $final_gradeList[$i] . '" value="'.$val.'"';
                if ($val == $current_finalgrade) {
	                $final_grade .= ' selected="true"';
                }
                $final_grade .= '>'.$final_gradeList[$i].'</option>';
            }
            unset($val); unset($i);
            $final_grade .= '</select>';
            if ($types['finalgrade'] != 'hidden') {
	            $final_grade .= '</div></div>';
            }
            $filters .= $final_grade;
        }

        if (@$params['missing_doc'] !== NULL) {
            $hidden = $types['missing_doc'] == 'hidden';
            $missing_doc = '';

            if (!$hidden) {
                $missing_doc .= '<div class="em_filters em-filter" id="missing_doc">
                                    <div class="em_label">
                                        <label>'.JText::_('COM_EMUNDUS_ATTACHMENTS_MISSING_DOC').'</label>
                                    </div>
                                    <div class="em_filtersElement">';
            }

            $missing_doc .= '<select class="search_test em-filt-select" id="select_missing_doc" name="missing_doc" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                                <option value="0">'.JText::_('COM_EMUNDUS_ACTIONS_ALL').'</option>';

	        $missing_docList = $h_files->getMissing_doc();
            foreach ($missing_docList as $md) {
                $missing_doc .= '<option title="' . $md->value . '"  value="'.$md->attachment_id.'"';
                if ($miss_doc == $md->attachment_id) {
	                $missing_doc .= ' selected="true"';
                }
                $missing_doc .= '>'.$md->value.'</option>';
            }
            $missing_doc .= '</select>';

            if (!$hidden) {
                $missing_doc .= '</div></div>';
            }
            $filters .= $missing_doc;
        }

        if (@$params['complete'] !== NULL) {
            $complete = '';
            $hidden = $types['complete'] == 'hidden';

            if (!$hidden) {
                $complete .= '<div class="em_filters em-filter" id="complete">
                                <div class="em_label">
                                	<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_APPLICATION_COMPLETE_APPLICATION').'</label>
                                </div>
                                <div class="em_filtersElement">';
            }

            $complete .= '<select class="search_test em-filt-select" id="select_complete" name="complete" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                            <option value="0">'.JText::_('COM_EMUNDUS_ACTIONS_ALL').'</option>';

            $complete .= '<option value="1"';
            if ($complete_application == 1) {
	            $complete .= ' selected="true"';
            }
            $complete .= '>'.JText::_('YES').'</option>';

            $complete .= '<option value="2"';
            if ($complete_application == 2) {
	            $complete .= ' selected="true"';
            }
            $complete .= '>'.JText::_('NO').'</option>';

            $complete .= '</select>';
            if (!$hidden) {
	            $complete .= '</div></div>';
            }

            $filters .= $complete;
        }

        if (@$params['validate'] !== NULL) {
            $validate = '';
            $hidden = $types['validate'] == 'hidden';

            if (!$hidden) {
                $validate .= '<div class="em_filters em-filter" id="validate">
                                <div class="em_label"><label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_APPLICATION_VALIDATED_APPLICATION').'</label></div>
                                <div class="em_filtersElement">';
            }

            $validate .= '<select class="search_test em-filt-select" id="select_validate" name="validate" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                            <option value="0">'.JText::_('COM_EMUNDUS_ACTIONS_ALL').'</option>';

            $validate .= '<option value="1"';
            if ($validate_application == 1) {
	            $validate .= ' selected="true"';
            }
            $validate .= '>'.JText::_('COM_EMUNDUS_FORMS_VALIDATED').'</option>';

            $validate .= '<option value="2"';
            if ($validate_application == 2) {
	            $validate .= ' selected="true"';
            }
            $validate .= '>'.JText::_('COM_EMUNDUS_FORMS_UNVALIDATED').'</option>';

            $validate .= '</select>';
            if (!$hidden) {
	            $validate .= '</div></div>';
            }
            $filters .= $validate;
        }

        if (@$params['campaign'] !== NULL) {
            $hidden = $types['campaign'] == 'hidden';
            $campaign = '';

            if (!$hidden) {
                $campaign .= '<div id="campaign" class="em-filter">
                            	<div class="em_label">
                            		<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_CAMPAIGN').'&ensp; <a href="javascript:clearchosen(\'#select_multiple_campaigns\')" onclick="clearchosen(\'#select_multiple_campaigns\');setFiltersSumo(event)"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a></label>
                            	</div>
                          		<div class="em_filtersElement">';
            }
            $campaign .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_campaigns" name="campaign" multiple=multiple"" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $campaignList = $h_files->getCampaigns();
            foreach ($campaignList as $c) {
                $campaign .= '<option title="' . $c->label.' - '.$c->year . '" value="'.$c->id.'"';
                if (!empty($current_campaign) && in_array($c->id, $current_campaign)) {
	                $campaign .= ' selected="true"';
                }
                $campaign .= '>'.$c->label.' - '.$c->year.'</option>';
            }

            $campaign .= '</select>';
            if (!$hidden) {
                $campaign .= '</div></div>';
            }

            $filters .= $campaign;
        }

        if ($params['schoolyear'] !== NULL) {
            $schoolyear = '';
            $hidden = $types['schoolyear'] == 'hidden';

            if (!$hidden) {
                $schoolyear .= '<div id="schoolyear" class="em-filter">
                                    <div class="em_label">
                                    	<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_PROFILES_SCHOOLYEARS').' &ensp;<a href="javascript:clearchosen(\'#select_multiple_schoolyears\')" onclick="clearchosen(\'#select_multiple_schoolyears\');setFiltersSumo(event)"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a></label>
                                    </div>
                                   <div class="em_filtersElement">';
            }

            $schoolyear .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_schoolyears" name="schoolyear" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $schoolyearList = $h_files->getSchoolyears();
            foreach ($schoolyearList as $key => $value) {
                $schoolyear .= '<option title="' . $value->schoolyear . '" value="'.$value->schoolyear.'"';
                if (!empty($current_schoolyear) && in_array($value->schoolyear, $current_schoolyear)) {
	                $schoolyear .= ' selected="true"';
                }
                $schoolyear .= '>'.$value->schoolyear.'</option>';
            }
            $schoolyear .= '</select>';
            if (!$hidden) {
                $schoolyear .= '</div></div>';
            }
            $filters .= $schoolyear;

        }

        if (@$params['programme'] !== NULL) {
            $hidden = $types['programme'] == 'hidden';
            $programme = '';

            if (!$hidden) {
                $programme .= '<div id="programme" class="em-filter">
                    <div class="em_label">
                    	<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_PROGRAMME').'&ensp;<a href="javascript:clearchosen(\'#select_multiple_programmes\')" onclick="clearchosen(\'#select_multiple_programmes\');setFiltersSumo(event)"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a></label>
                    </div>
                    <div class="em_filtersElement">';
            }
            $programme .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_programmes" name="programme" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $programmeList = $h_files->getProgrammes($params['programme']);
            foreach ($programmeList as $p) {
                $programme .= '<option title="' . $p->label . ' - ' . $p->code . '" value="'.$p->code.'"';
                if (!empty($current_programme) && in_array($p->code, $current_programme)){
                    $programme .= ' selected="true"';
                    $program_selected = true;
                }
                $programme .= '>'.$p->label.' - '.$p->code.'</option>';
            }

            $programme .= '</select>';
            if (!$hidden) {
                $programme .= '</div></div>';
            }

            $filters .= $programme;
        }

        if (@$params['status'] !== NULL) {
            $hidden = $types['status'] == 'hidden';
            $statusList = $h_files->getStatus();

            if (isset($filt_menu['status'][0]) && !empty($filt_menu['status'][0])) {
                foreach ($statusList as $key => $step) {
                    if (!in_array($step->step, $filt_menu['status'])) {
	                    unset($statusList[$key]);
                    }
                }
            }

            $status = '';
            if (!$hidden) {
                $status .= '<div class="em_filters em-filter" id="status">
                    <div class="em_label">
                    	<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_STATUS').'&ensp; <a href="javascript:clearchosen(\'#select_multiple_status\')" onclick="clearchosen(\'#select_multiple_status\');setFiltersSumo(event)"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a></label>
                    </div>
                    <div class="em_filtersElement">';
            }

            $status .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select" ' : '').' id="select_multiple_status" name="status" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

            foreach ($statusList as $p) {
                $status .= '<option title="' . $p->value . '" value="'.$p->step.'"';
                if (!empty($current_status) && in_array($p->step, $current_status)) {
	                $status .= ' selected="true"';
                }
                $status .= '>'.$p->value.'</option>';
            }
            $status .= '</select>';
            if (!$hidden) {
                $status .= '</div></div>';
            }
            $filters .= $status;
        }

        if ($params['published'] !== NULL) {
            $hidden = $types['published'] == 'hidden';
            $published = '';

            if (!$hidden) {
                $published .= '<div class="em_filters em-filter" id="published">
                				<div class="em_label">
                					<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_APPLICATION_PUBLISH').'</label>
                				</div>
                				<div class="em_filtersElement">
                					<select class="search_test em-filt-select" id="select_published" name="published" '.($hidden ? 'style="visibility:hidden" ' : '').'>
                                        <option value="1"';

                if ($current_published == '1') {
	                $published .= "selected='true'";
                }

                $published .= '>'.JText::_("COM_EMUNDUS_APPLICATION_PUBLISHED").'</option>
                        <option value="0"';

                if ($current_published == '0') {
	                $published .= "selected='true'";
                }
                $published .= '>'. JText::_("COM_EMUNDUS_APPLICATION_ARCHIVED").'</option>
                        <option value="-1"';

                if ($current_published == '-1') {
	                $published .= "selected='true'";
                }
                $published .='>'.JText::_("COM_EMUNDUS_APPLICATION_TRASHED").'</option>
                </select>';
                $published .='</div></div>';
            }

            $filters .= $published;
        }

        if (@$params['tag'] !== NULL) {
	        $hidden = $types['tag'] == 'hidden';
	        $tag    = '';

	        if (!$hidden) {
		        $tag .= '<div id="tag" class="em-filter">
                    		<div class="em_label">
                    			<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_TAGS_TAG').'&ensp; <a href="javascript:clearchosen(\'#select_multiple_tags\')" onclick="clearchosen(\'#select_multiple_tags\');setFiltersSumo(event)"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a></label>
                            </div>
                    		<div class="em_filtersElement">';
	        }
	        $tag .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_tags" name="tag" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $tagList = $m_files->getAllTags();

	        if (!empty($current_tag)) {
		        // This allows hiding of files by tag.
		        $not_in = array_filter($current_tag, function($e) {
			        return strpos($e, '!') === 0;
		        });

		        if (!empty($not_in)) {
			        $current_tag = array_diff($current_tag, $not_in);
			        $not_in = array_map(function($v) {
				        return ltrim($v, '!');
			        }, $not_in);
		        }
	        }

	        foreach ($tagList as $p) {
	            if (empty($not_in) || !in_array($p['id'], $not_in)) {
			        $tag .= '<option title="' . $p['label'] . '"  value="'.$p['id'].'"';
			        if (!empty($current_tag) && in_array($p['id'], (array) $current_tag)) {
				        $tag .= ' selected="true"';
			        }
			        $tag .= '>'.$p['label'].'</option>';
		        }
	        }
	        $tag .= '</select>';
            if (!$hidden) {
                $tag .= '</div></div>';
            }

            $filters .= $tag;
        }

	    if (@$params['group_assoc'] !== NULL) {
		    $hidden = $types['group_assoc'] == 'hidden';
		    $group_assoc = '';

		    if (!$hidden) {
			    $group_assoc .= '<div id="group_assoc" class="em-filter">
                    		<div class="em_label">
                    			<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_ASSOCIATED_GROUPS').'&ensp; <a href="javascript:clearchosen(\'#select_multiple_group_assoc\')" onclick="clearchosen(\'#select_multiple_group_assoc\');setFiltersSumo(event)"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a></label>
                            </div>
                    		<div class="em_filtersElement">';
		    }
		    $group_assoc .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_group_assoc" name="group_assoc" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

		    $groupList = $m_files->getUserAssocGroups();
		    foreach ($groupList as $p) {
			    $group_assoc .= '<option title="' . $p['label'] . '" value="'.$p['id'].'"';
			    if (!empty($current_group_assoc) && in_array($p['id'], (array)$current_group_assoc)) {
				    $group_assoc .= ' selected="true"';
			    }
			    $group_assoc .= '>'.$p['label'].'</option>';
		    }
		    $group_assoc .= '</select>';
		    if (!$hidden) {
			    $group_assoc .= '</div></div>';
		    }

		    $filters .= $group_assoc;
	    }

        //Other filters builtin
        if (@$params['other'] !== NULL && !empty($tables) && $tables[0] != "") {

            $filters .= '</fieldset><fieldset class="em_filters_other">';

            $other_elements = $h_files->getElementsOther($tables);
            $other_filter = '<div class="em_filters" id="em_other_filters">
								<a href="javascript:addElementOther();">
									<span class="editlinktip hasTip" title="'.JText::_('COM_EMUNDUS_FILES_NOTE').'::'.JText::_('COM_EMUNDUS_FILTERS_FILTER_HELP').'">'.JText::_('COM_EMUNDUS_FILTERS_OTHER_FILTERS').'</span>
									<input type="hidden" value="0" id="theValue_other" />
									<img src="'.JURI::base(true).'media/com_emundus/images/icones/viewmag+_16x16.png" alt="'.JText::_('ADD_SEARCH_ELEMENT').'" id="add_filt"/>
								</a>
								<div id="otherDiv">';

            if (count($search_other) > 0 && isset($search_other) && is_array($search_other)) {
                $i = 0;
                $selected_other = "";

                foreach ($search_other as $sf) {

                    $other_filter .= '<div id="filter_other'.$i.'">
										<select class="search_test em-filt-select" id="elements-others" name="elements_other" id="elements_other" >
                            				<option value="">'.JText::_('COM_EMUNDUS_PLEASE_SELECT').'</option>';

                    $groupe = "";
                    $length = 50;

                    if (!empty($other_elements)) {

                        foreach ($other_elements as $element_other) {
                            $groupe_tmp = $element_other->group_label;
                            $dot_grp = strlen($groupe_tmp)>=$length?'...':'';
                            $dot_elm = strlen($element_other->element_label)>=$length?'...':'';

                            if ($groupe != $groupe_tmp) {
                                $other_filter .= '<option class="emundus_search_grp" disabled="disabled" value="">'.substr(strtoupper($groupe_tmp), 0, $length).$dot_grp.'</option>';
                                $groupe = $groupe_tmp;
                            }

                            $other_filter .= '<option class="emundus_search_elm_other" value="'.$element_other->table_name.'.'.$element_other->element_name.'"'; // = result_for; engaged; scholarship...

	                        if ($element_other->table_name.'.'.$element_other->element_name == $sf){
                                $other_filter .= ' selected';
                                $selected_other = $element_other;
                            }
                            $other_filter .= '>'.substr($element_other->element_label, 0, $length).$dot_elm.'</option>';
                        }
                    }
                    $other_filter .= '</select>';

                    if (!isset($search_values_other[$i])) {
	                    $search_values_other[$i] = "";
                    }

                    if ($selected_other != "") {
	                    $other_filter .= $h_files->setSearchBox($selected_other, $search_values_other[$i], "elements_values_other", $i);
                    }

                    $other_filter .= '<a href="javascript:clearAdvanceFilter(\'filter_other'.$i.'\'); javascript:removeElement(\'filter_other'.$i.'\', 2);"><img src="'.JURI::base().'media/com_emundus/images/icones/viewmag-_16x16.png" alt="'.JText::_('REMOVE_SEARCH_ELEMENT').'" id="add_filt"/></a>';
                    $i++;
                    $other_filter .= '</div>';
                }
            }
            $other_filter .= '</div></div>';

            $filters .= $other_filter;
        }

        if (@$params['newsletter'] !== NULL) {

        	$hidden = $types['newsletter'] == 'hidden';

            $filters .= '<div class="em_filters" id="newsletter">
                        	<div class="em_label">
                        		<label class="control-label em_filters_other_label">'.JText::_('COM_EMUNDUS_USERS_NEWSLETTER').'</label>
                            </div>
                            <div class="em_filtersElement">
                        		<select class="search_test em-filt-select" id="select_newsletter" name="newsletter" '.($hidden ? 'style="visibility:hidden" ' : '').'>
                                    <option value="0"';

            if (@$newsletter == 0) {
	            $filters .= ' selected';
            }

            $filters .= '>'.JText::_("COM_EMUNDUS_ACTIONS_ALL").'</option>
                            <option value="1"';

            if (@$newsletter == 1) {
	            $filters .= ' selected';
            }

            $filters .= '>'.JText::_("JYES").'</option>
                <option value="2"';

            if (@$newsletter == 2) {
                $filters .= ' selected';
            }

            $filters .= '>'.JText::_("JNO").'</option>
                        </select>
                    </div>
                </div>';
        }

        if (@$params['group'] !== NULL) {

            $hidden = $types['group'] == 'hidden';

            $group = '';
            if (!$hidden) {
                $group .= '<div id="group" class="em-filter">
                    		<div class="em_label">
                    			<label class="control-label em_filter_label">'.JText::_('COM_EMUNDUS_USERS_GROUP_FILTER').' &ensp;
                    				<a href="javascript:clearchosen(\'#select_multiple_groups\')"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a>
                    			</label>
                            </div>
                    		<div class="em_filtersElement">';
            }
            $group .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_groups" name="group" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $groupList = $m_files->getAllGroups();
            foreach ($groupList as $p) {
                $group .= '<option title="' . $p['label'] . '" value="'.$p['id'].'"';
                if (!empty($current_group) && in_array($p['id'], $current_group)) {
	                $group .= ' selected="true"';
                }
                $group .= '>'.$p['label'].'</option>';
            }

            $group .= '</select>';
            if (!$hidden) {
                $group .= '</div></div>';
            }

            $filters .= $group;
        }

        if (@$params['institution'] !== NULL) {
            $hidden = $types['institution'] == 'hidden';

            $institution = '';
            if (!$hidden) {
                $institution .= '<div id="group">
                    				<div class="em_label">
                    					<label class="control-label em_filters_other_label">'.JText::_('COM_EMUNDUS_FILES_UNIVERSITY').' &ensp;
                    						<a href="javascript:clearchosen(\'#select_multiple_institutions\')"><span class="fas fa-redo" title="'.JText::_('COM_EMUNDUS_FILTERS_CLEAR').'"></span></a>
                    					</label>
                                    </div>
                    				<div class="em_filtersElement">';
            }
            $institution .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_institutions" name="institution" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $institutionList = $m_files->getAllInstitutions();
            foreach ($institutionList as $p) {
                $institution .= '<option value="'.$p['id'].'"';
                if (!empty($current_institution) && in_array($p['id'], $current_institution)) {
	                $institution .= ' selected="true"';
                }
                $institution .= '>'.$p['title'].'</option>';
            }
            $institution .= '</select>';
            if (!$hidden) {
                $institution .= '</div></div>';
            }

            $filters .= $institution;
        }

        if (!empty(@$params['spam_suspect'])) {
            $hidden = $types['spam_suspect'] == 'hidden';

            $filters.= '<div class="em_filters" id="spam_suspect">
							<div class="em_label">
								<label class="control-label em_filters_other_label">'.JText::_('COM_EMUNDUS_EMAILS_SPAM_SUSPECT').'</label>
							</div>
							<div class="em_filtersElement">
                        		<select class="search_test" id="select_spam-suspect" name="spam_suspect" '.($hidden ? 'style="visibility:hidden" ' : '').'>
                                    <option value="0"';

            if (@$spam_suspect == 0) {
	            $filters .= ' selected';
            }
            $filters.='>'.JText::_("COM_EMUNDUS_ACTIONS_ALL").'</option>
                            <option value="1"';

            if (@$spam_suspect == 1) {
	            $filters .= ' selected';
            }
            $filters .= '>'.JText::_("JYES").'</option>
                        </select>
                    </div>
                </div>';
        }
        //Advance filter builtin
        if (@$params['adv_filter'] !== NULL) {
            $filters .= '</fieldset><fieldset class="em_filters_adv_filter">';
            $elements = $h_files->getElements();

            // the button is disabled by default. It needs a selected campaign ->> look at em_files.js at the #select_multiple_campaigns on change function
            $disabled = empty($current_campaign) ? 'disabled' : "";

            $search_nb = !empty($search)?count($search):0;
            $adv_filter = '<div class="em_filters em-filter" id="em_adv_filters">
								<label class="control-label editlinktip hasTip em_filters_adv_filter_label" title="'.JText::_('COM_EMUNDUS_FILES_NOTE').'::'.JText::_('COM_EMUNDUS_FILTERS_FILTER_HELP').'">'.JText::_('COM_EMUNDUS_FILTERS_ELEMENT_FILTER').'</label>
								<div class="em_filters_adv_filter_addColumn" title="'.JText::_('COM_EMUNDUS_FILTERS_SELECT_CAMPAIGN').'">
									<button class="btn btn-default btn-sm" type="button" id="add-filter" '.$disabled.' ><span class="glyphicon glyphicon-th-list"></span> '.JText::_('COM_EMUNDUS_FILTERS_ADD_FILTER_COLUMN').'</button>
								</div>
								<br/>
								<input type="hidden" value="'.$search_nb.'" id="nb-adv-filter" />
								<div id="advanced-filters" class="form-group">';

            if (!empty($search)) {

                $i = 1;
                $selected_adv = "";
                foreach ($search as $key => $val) {

                    if (isset($val['value'])) {
                        $val = $val['value'];
                    }

                    $adv_filter .= '<fieldset id="em-adv-father-'.$i.'" class="em-nopadding em-flex-align-start em-flex-column">
									<a id="suppr-filt" class="em-mb-4 em-flex-start">
									<span class="em-font-size-14 em-red-500-color em-pointer">' . JText::_('COM_EMUNDUS_DELETE_ADVANCED_FILTERS') . '</span></a>
										<select class="chzn-select em-filt-select" id="elements" name="elements">
                                            <option value="">'.JText::_('COM_EMUNDUS_PLEASE_SELECT').'</option>';
                    $menu = "";
                    $groupe = "";

                    foreach ($elements as $element) {
                        $menu_tmp = $element->title;

                        if ($menu != $menu_tmp) {
                            $adv_filter .= '<optgroup label="________________________________"><option disabled class="emundus_search_elm" value="-">'.strtoupper($menu_tmp).'</option></optgroup>';
                            $menu = $menu_tmp;
                        }

                        if (isset($groupe_tmp) && ($groupe != $groupe_tmp)) {
                            $adv_filter .= '</optgroup>';
                        }

                        $groupe_tmp = $element->group_label;

                        if ($groupe != $groupe_tmp) {
                            $adv_filter .= '<optgroup label=">> '.$groupe_tmp.'">';
                            $groupe = $groupe_tmp;
                        }

                        $adv_filter .= '<option class="emundus_search_elm" value="'.$element->id.'"';
                        $table_name = (isset($element->table_join)?$element->table_join:$element->table_name);
                        if ($table_name.'.'.$element->element_name == $key) {
                            $selected_adv = $element;
                            $adv_filter .= ' selected=true ';
                        }
                        $adv_filter .= '>'.$element->element_label.'</option>';
                    }
                    $adv_filter .= '</select> ';

                    if ($selected_adv != "") {
                        $adv_filter .= $h_files->setSearchBox($selected_adv, $val, $key, $i);
                    }

                    $i++;
                    $adv_filter .= '</fieldset>';
                }
            }
            $adv_filter .= '</div>

            <div class="em_save_filter">
                <input value="'.JText::_('COM_EMUNDUS_FILES_SAVE_FILTER').'" class="btn btn-sm btn-warning" title="'.JText::_('COM_EMUNDUS_FILES_SAVE_FILTER').'" type="button" id="save-filter">
            </div>
            </div>';

            $filters .= $adv_filter;
        }

        // Buttons
        $filters .=' </fieldset>';

        return $filters;
    }

    public static function getEmundusFilters($id = null) {
        $itemid = JFactory::getApplication()->input->get('Itemid');
        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        if (is_null($id) && !empty($itemid)) {
            $query = 'SELECT * FROM #__emundus_filters WHERE user='.$user->id.' AND constraints LIKE "%col%" AND item_id='.$itemid.' ORDER BY name';
            $db->setQuery( $query );
            return $db->loadObjectlist();
        } elseif (!empty($id)) {
            $query = 'SELECT * FROM #__emundus_filters WHERE id='.$id.' AND constraints LIKE "%col%" ORDER BY name';
            $db->setQuery( $query );
            return $db->loadObject();
        } else {
        	return array();
        }
    }

    public static function createTagsList($tags) {
        $tagsList = array();
        foreach ($tags as $tag) {
            $fnum = $tag['fnum'];
			$class = str_replace('label-','',$tag['class']);
            if (!isset($tagsList[$fnum])) {
                $tagsList[$fnum] = '<div class="flex sticker label-border-'.$class.'"><span class="circle '.$tag['class'].'"></span><span class="label-text-'.$class.'">'.$tag['label'].'</span></div>';
            } else {
                $tagsList[$fnum] .= '<div class="flex sticker label-border-'.$class.'"><span class="circle '.$tag['class'].'"></span><span class="label-text-'.$class.'">'.$tag['label'].'</span></div>';
            }
        }
        return $tagsList;
    }

    public function createFormProgressList($formsprogress) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
        $m_application = new EmundusModelApplication();

        $formsprogressList = array();
        foreach ($formsprogress as $form_progress) {
            $fnum = $form_progress['fnum'];
            if (!isset($formsprogressList[$fnum])) {
                if($form_progress['form_progress'] != null) {
                    $formsprogressList[$fnum] = $form_progress['form_progress'].' %';
                } else {
                    $result = $m_application->getFormsProgress($form_progress['fnum']);
                    $formsprogressList[$fnum] = $result.' %';
                }
            }
        }
        return $formsprogressList;
    }

    public function createAttachmentProgressList($attachmentsprogress) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
        $m_application = new EmundusModelApplication();

        $attachmentsprogressList = array();
        foreach ($attachmentsprogress as $attachmentprogress) {
            $fnum = $attachmentprogress['fnum'];
            if (!isset($attachmentsprogressList[$fnum])) {
                if($attachmentprogress['attachment_progress'] != null) {
                    $attachmentsprogressList[$fnum] = $attachmentprogress['attachment_progress'] . ' %';
                } else {
                    $result = $m_application->getAttachmentsProgress($attachmentprogress['fnum']);
                    $attachmentsprogressList[$fnum] = $result.' %';
                }
            }
        }
        return $attachmentsprogressList;
    }


    public function createUnreadMessageList($unread_messages) {
        $unreadmessagesList = array();

        foreach ($unread_messages as $unread_message) {

            $fnum = $unread_message['fnum'];

            if (!isset($unreadmessagesList[$fnum])) {
                    $unreadmessagesList[$fnum] = '<p class="messenger__notifications_counter">'. $unread_message['nb'] .'</p> ';
            }
        }
        return $unreadmessagesList;
    }

	/** Create a list of HTML text using the tag system.
	 * This function replaces the tags found in an HTML block with information from the fnums.
	 *
	 * @param $html  String The block of text containing the tags to be replaced.
	 * @param $fnums array The list of fnums to use for the tags.
	 *
	 * @return array
	 */
    public function createHTMLList($html, $fnums) {

	    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
	    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
	    $m_emails = new EmundusModelEmails();
	    $m_files = new EmundusModelFiles();

    	$htmlList = array();
    	foreach ($fnums as $fnum) {
    		if (!isset($htmlList[$fnum])) {

	            $fnum = $m_files->getFnumInfos($fnum);
			    $post = [
				    'FNUM'           => $fnum['fnum'],
				    'USER_NAME'      => $fnum['name'],
				    'CAMPAIGN_LABEL' => $fnum['label'],
				    'SITE_URL'       => JURI::base(),
				    'USER_EMAIL'     => $fnum['email']
			    ];

			    $tags = $m_emails->setTags($fnum['applicant_id'], $post, $fnum['fnum'], '',$html);
			    $htmlList[$fnum['fnum']] = preg_replace($tags['patterns'], $tags['replacements'], $html);
			    $htmlList[$fnum['fnum']] = $m_emails->setTagsFabrik($htmlList[$fnum['fnum']], [$fnum['fnum']]);
		    }
		}
		return $htmlList;
    }

    public static function createEvaluatorList($join, $model) {
        $evaluators = array();
        $groupEval = $model->getEvaluatorsFromGroup();

        $evaluatorsDB = $model->getEvaluators();
        $title = "";
        foreach ($groupEval as $k => $group) {

            if (!array_key_exists($group['fnum'], $evaluators)) {
                $title = $group['name'];
                $evaluators[$group['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="material-icons">visibility</span>  <span class="em-evaluator editlinktip hasTip"  title="'.$title.'">'.$group['title'].'</span><button class="btn btn-danger btn-xs group" id="'.$group['fnum'].'-'.$group['id'].'">X</button></li></ul>';
            } else {
                if ((strcmp($group['fnum'], $groupEval[$k + 1]['fnum'])  == 0) && (strcmp($groupEval[$k + 1]['title'], $group['title']) == 0 )) {
                    $title .= ' '.$group['name'];
                } else {
                    $title .= ' '.$group['name'];
                    $evaluators[$group['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="material-icons">visibility</span>  <span class="em-evaluator editlinktip hasTip"  title="'.$title.'">'.$group['title'].'</span><button class="btn btn-danger btn-xs group" id="'.$group['fnum'].'-'.$group['id'].'">X</button></li></ul>';
                    $title = '';
                }
            }
        }

        foreach ($evaluatorsDB as $ev) {
            if (!array_key_exists($ev['fnum'], $evaluators)) {
                $evaluators[$ev['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="material-icons">visibility</span>  <span class="em-evaluator">'.$ev['name'].'</span><button class="btn btn-danger btn-xs">X</button></li></ul>';
            } else {
                $evaluators[$ev['fnum']] = substr($evaluators[$ev['fnum']], 0, count($evaluators[$ev['fnum']]) - 6) . '<li class="em-list-evaluator-item"><span class="material-icons">visibility</span>  <span class="em-evaluator">'.$ev['name'].'</span><button class="btn btn-danger btn-xs" id="'.$ev['fnum'].'-'.$ev['id'].'">X</button></li></ul>';
            }
        }
        return $evaluators;
    }

    // Get object of a Joomla Menu
    public static function getMenuList($params, $fnum = null) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
        $m_users = new EmundusModelUsers();

        $menu = @JFactory::getApplication()->getMenu();
        // If no active menu, use default
        $active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();

        $user = JFactory::getUser();
        if ($fnum === null) {
            $actions = $m_users->getUserACL($user->id);
        } else {
            $actions = $m_users->getUserACL($user->id, $fnum);
        }
        $levels = $user->getAuthorisedViewLevels();
        asort($levels);

        $key = 'menu_items'.$params.implode(',', $levels).'.'.$active->id;
        $cache = JFactory::getCache('mod_menu', '');

        if (!($items = $cache->get($key))) {
            // Initialise variables.
            $path = $active->tree;
            $start = 0;
            $end = 3;
            $showAll = 1;
            $items = $menu->getItems('menutype', $params->get('em_actions'));
            $lastitem   = 0;
            if ($items) {
                foreach ($items as $i => $item) {
					$access_actions = explode(',', $item->note);
	                $has_access = true;

					if (!empty($access_actions)) {

						foreach($access_actions as $access_action) {
							list($action, $crud, $multi) = explode('|', $access_action);

							if (!empty($action) && !empty($crud)) {
								$has_access = false;

								if (EmundusHelperAccess::asAccessAction($action, $crud, $user->id, $fnum)) {
									$actions[$action]['multi'] = !empty($multi) ? $multi : 0;
									$actions[$action]['grud'] = $crud;
									$item->action = $actions[$action];
									$has_access = true;
									break;
								}
							}
						}

						if (!$has_access) {
							unset($items[$i]);
							continue;
						}
					}

                    if (($start && $start > $item->level) || ($end && $item->level > $end) || (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path)) || ($start > 1 && !in_array($item->tree[$start-2], $path))) {
                        unset($items[$i]);
                        continue;
                    }

                    $item->deeper = false;
                    $item->shallower = false;
                    $item->level_diff = 0;

                    if (isset($items[$lastitem])) {
                        $items[$lastitem]->deeper = ($item->level > $items[$lastitem]->level);
                        $items[$lastitem]->shallower = ($item->level < $items[$lastitem]->level);
                        $items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
                    }

                    $item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);
                    $lastitem = $i;
                    $item->active = false;
                    $item->flink = $item->link;

                    // Reverted back for CMS version 2.5.6
                    switch ($item->type) {
                        case 'separator':
                            // No further action needed.
                            continue 2;

                        case 'url':
                            if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
                                // If this is an internal Joomla link, ensure the Itemid is set.
                                $item->flink = $item->link.'&Itemid='.$item->id;
                            }
                            break;

                        case 'alias':
                            // If this is an alias use the item id stored in the parameters to make the link.
                            $item->flink = 'index.php?Itemid='.$item->params->get('aliasoptions');
                            break;

                        default:
                            $router = @JSite::getRouter();
                            if ($router->getMode() == JROUTER_MODE_SEF) {
                                $item->flink = 'index.php?Itemid='.$item->id;
                            } else {
                                $item->flink .= '&Itemid='.$item->id;
                            }
                            break;
                    }

                    if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
                        $item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
                    } else {
                        $item->flink = JRoute::_($item->flink);
                    }

                    $item->title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_css = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->menu_image = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
                }

                if (isset($items[$lastitem])) {
                    $items[$lastitem]->deeper = (($start?$start:1) > $items[$lastitem]->level);
                    $items[$lastitem]->shallower = (($start?$start:1) < $items[$lastitem]->level);
                    $items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start?$start:1));
                }
            }

            // delete parent without children
            foreach ($items as $i => $item) {
                if ($item->level == 1 && $item->level_diff == 0) {
                    unset($items[$i]);
                }
            }

            $cache->store($items, $key);
        }
        return $items;
    }

    // get emundus groups for user
    public function getUserGroups($uid) {
        $db = JFactory::getDbo();

        $query = 'select distinct(group_id)
                    from #__emundus_groups
                    where user_id='.$uid;

        try {
            $db->setQuery($query);
            return $db->loadColumn();
        } catch (Exception $e) {
            throw $e;
        }
    }

    // getEvaluation
	public static function getEvaluation($format = 'html', $fnums = [])
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'evaluation.php');
		require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'files.php');

		$eMConfig          = JComponentHelper::getParams('com_emundus');
		$show_empty_fields = $eMConfig->get('show_empty_fields', 1);

		$m_evaluation = new EmundusModelEvaluation();
		$m_files      = new EmundusModelFiles;
		$h_files      = new EmundusHelperFiles;

		if (!is_array($fnums))
		{
			$fnumInfo = $m_files->getFnumInfos($fnums);
			$fnums    = array($fnums);
		}
		else
		{
			$fnumInfo = $m_files->getFnumInfos($fnums[0]);
		}

		$element_id  = $m_evaluation->getAllEvaluationElements(1, $fnumInfo['training']);
		$elements    = $h_files->getElementsName(implode(',', $element_id));
		$evaluations = $m_files->getFnumArray($fnums, $elements, 0, 0, 0, 0);

		$data = array();
		foreach ($evaluations as $eval)
		{

			$str = '<br><hr>';
			$str .= '<p><em style="font-size: 14px">' . JText::_('COM_EMUNDUS_EVALUATION_EVALUATED_ON') . ' : ' . JHtml::_('date', $eval['jos_emundus_evaluations___time_date'], JText::_('DATE_FORMAT_LC')) . ' - ' . $fnumInfo['name'] . '</em></p>';
			$str .= '<h2>' . JText::_('COM_EMUNDUS_EVALUATION_EVALUATOR') . ': ' . JFactory::getUser($eval['jos_emundus_evaluations___user'])->name . '</h2>';
			$str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

			foreach ($elements as $element)
			{
				if ($element->table_join == null)
				{
					$k = $element->tab_name . '___' . $element->element_name;
				}
				else
				{
					$k = $element->table_join . '___' . $element->element_name;
				}

				if ($element->element_name != 'id' &&
					$element->element_name != 'time_date' &&
					$element->element_name != 'campaign_id' &&
					$element->element_name != 'student_id' &&
					$element->element_name != 'user' &&
					$element->element_name != 'fnum' &&
					$element->element_name != 'email' &&
					$element->element_name != 'label' &&
					$element->element_name != 'code' &&
					$element->element_name != 'spacer' &&
					$element->element_name != 'parent_id' &&
					$element->element_hidden == 0 &&
					array_key_exists($k, $eval))
				{
					if ($show_empty_fields == 0 && empty($eval[$k]))
					{
						$str .= '';
					}
					else
					{
						$str .= '<tr>';
						if (strpos($element->element_name, 'comment') !== false)
						{
							$str .= '<td colspan="2"><b>' . JText::_(trim($element->element_label)) . '</b> <br>' . JText::_($eval[$k]) . '</td>';
						}
						else
						{
							if($element->element_plugin == 'emundus_fileupload' && EmundusHelperAccess::asAccessAction(4,'r',JFactory::getUser()->id,$eval['fnum'])) {
								$filepath = '';
								$params = json_decode($element->element_attribs,true);

								if(!empty($params['attachmentId'])) {
									$query->clear()
										->select('filename')
										->from($db->quoteName('#__emundus_uploads'))
										->where($db->quoteName('attachment_id') . ' = ' . $db->quote($params['attachmentId']));
									$db->setQuery($query);
									$filename = $db->loadResult();

									if(!empty($filename)) {
										$filepath = EMUNDUS_PATH_REL.$eval['jos_emundus_evaluations___student_id'].'/'.$filename;
									}
								}

								if(!empty($filepath))
								{
									$str .= '<td width="30%"><b>' . JText::_(trim($element->element_label)) . '</b> </td><td width="70%"><a href="/' . $filepath . '" target="_blank">' . JText::_($eval[$k]) . '</a></td>';
								}
							} else {
								$str .= '<td width="30%"><b>' . JText::_(trim($element->element_label)) . '</b> </td><td width="70%">' . JText::_($eval[$k]) . '</td>';
							}
						}
						$str .= '</tr>';
					}
				}
			}

			$str .= '</table>';
			$str .= '<p></p><hr>';

			if ($format != 'html')
			{
				$str = str_replace('<br>', chr(10), $str);
				$str = str_replace('<br />', chr(10), $str);
				$str = str_replace('<h1>', '* ', $str);
				$str = str_replace('</h1>', ' : ', $str);
				$str = str_replace('<b>', chr(10), $str);
				$str = str_replace('</b>', ' : ', $str);
				$str = str_replace('&nbsp;', ' ', $str);
				$str = strip_tags($str, '<h1>');
			}

			if ($format == "simple")
			{
				$str = $eval['label'] . ' : ' . JHtml::_('date', $eval['jos_emundus_evaluations___time_date'], JText::_('DATE_FORMAT_LC')) . ' - ' . JFactory::getUser($eval['jos_emundus_evaluations___user_raw'])->name;
			}

			$data[$eval['fnum']][$eval['jos_emundus_evaluations___user']] = $str;
		}

		return $data;
	}

    // getDecision
    static function getDecision($format='html', $fnums = []) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

        $m_evaluation   = new EmundusModelEvaluation();
        $m_files        = new EmundusModelFiles;
        $h_files        = new EmundusHelperFiles;

        if (!is_array($fnums)) {
            $fnumInfo = $m_files->getFnumInfos($fnums);
            $fnums = array($fnums);
        } else {
            $fnumInfo = $m_files->getFnumInfos($fnums[1]);
        }

        $element_id = $m_evaluation->getAllDecisionElements(1, $fnumInfo['training']);
        $elements = $h_files->getElementsName(implode(',',$element_id));
        $evaluations = $m_files->getFnumArray($fnums, $elements);

        $data = array();

        foreach ($evaluations as $eval) {
            if ($eval['jos_emundus_final_grade___user'] > 0) {
                $str = '<br><hr>';
                $str .= '<em>'.JHtml::_('date', $eval['jos_emundus_final_grade___time_date'], JText::_('DATE_FORMAT_LC')).'</em>';
                $str .= '<h1>'.JFactory::getUser($eval['jos_emundus_final_grade___user'])->name.'</h1>';
                $str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

                foreach ($elements as $element) {
                    $k = $element->tab_name.'___'.$element->element_name;

                    if ($element->element_name != 'id' &&
                        $element->element_name != 'time_date' &&
                        $element->element_name != 'campaign_id' &&
                        $element->element_name != 'student_id'&&
                        $element->element_name != 'user' &&
                        $element->element_name != 'fnum' &&
                        $element->element_name != 'email' &&
                        $element->element_name != 'label' &&
                        $element->element_name != 'code' &&
                        array_key_exists($k, $eval))
                    {
                        $str .= '<tr>';
                        if (strpos($element->element_plugin, 'textarea') !== false) {
	                        $str .= '<td colspan="2"><b>'.JText::_($element->element_label).'</b> <br>'.JText::_($eval[$k]).'</td>';
                        } else {
	                        $str .= '<td width="70%"><b>'.JText::_($element->element_label).'</b> </td><td width="30%">'.JText::_($eval[$k]).'</td>';
                        }
                        $str .= '</tr>';
                    }
                }

                $str .= '</table>';
                $str .= '<p></p><hr>';

                if ($format != 'html') {
                    $str = str_replace('<br>', chr(10), $str);
                    $str = str_replace('<br />', chr(10), $str);
                    $str = str_replace('<h1>', '* ', $str);
                    $str = str_replace('</h1>', ' : ', $str);
                    $str = str_replace('<b>', chr(10), $str);
                    $str = str_replace('</b>', ' : ', $str);
                    $str = str_replace('&nbsp;', ' ', $str);
                    $str = strip_tags($str, '<h1>');
                }

                $data[$eval['fnum']][$eval['jos_emundus_final_grade___user']] = $str;
            }
        }

        return $data;
    }

    function getDecisionFormUrl($fnum, $user_id)
    {
        $url_form = '';

        if (!empty($fnum)) {
            require_once(JPATH_SITE . '/components/com_emundus/models/files.php');
            require_once(JPATH_SITE . '/components/com_emundus/models/evaluation.php');
            $m_files = new EmundusModelFiles;
            $m_evaluation = new EmundusModelEvaluation();

            $fnumInfos = $m_files->getFnumInfos($fnum);
            $my_decision = $m_evaluation->getDecisionFnum($fnum);
            $formid = $m_evaluation->getDecisionFormByProgramme($fnumInfos['training']);

            $url_form = '';
            if (!empty($formid)) {
                $url_form = 'index.php?option=com_fabrik&c=form';

                if (!empty($my_decision)) {
                    if (EmundusHelperAccess::asAccessAction(29, 'u', $user_id, $fnum)) {
                        $url_form .= '&view=form&formid=' . $formid . '&rowid=' . $my_decision[0]->id;
                    } elseif (EmundusHelperAccess::asAccessAction(29, 'r', $user_id, $fnum)) {
                        $url_form .= '&view=details&formid=' . $formid . '&rowid=' . $my_decision[0]->id;
                    }
                } else {
                    if (EmundusHelperAccess::asAccessAction(29, 'c', $user_id, $fnum)) {
                        $url_form .= '&view=form&formid=' . $formid;
                    } elseif (EmundusHelperAccess::asAccessAction(29, 'r', $user_id, $fnum)) {
                        $url_form .= '&view=details&formid=' . $formid . '&rowid=' . $my_decision[0]->id;
                    }
                }

                $url_form .= '&jos_emundus_final_grade___student_id[value]=' . $fnumInfos['applicant_id'] . '&jos_emundus_final_grade___campaign_id[value]=' . $fnumInfos['campaign_id'] . '&jos_emundus_final_grade___fnum[value]=' . $fnum . '&student_id=' . $fnumInfos['applicant_id'] . '&tmpl=component&iframe=1';
            }
        }

        return $url_form;
    }

    // Get Admission
    function getAdmission($format='html', $fnums = [], $name = null) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'admission.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

        $m_admission = new EmundusModelAdmission();
        $m_files = new EmundusModelFiles;
        $h_files = new EmundusHelperFiles;

        $user = JFactory::getUser();
        if (!is_array($fnums)) {
            $fnumInfo = $m_files->getFnumInfos($fnums);
            $fnums = array($fnums);
        } else {
            $fnumInfo = $m_files->getFnumInfos($fnums[1]);
        }

        // Get information from the applicant form filled out by the coordinator
        if (EmundusHelperAccess::asAccessAction(8, 'c', $user->id, $fnumInfo['fnum'])) {

            $element_id     = $m_admission->getAllAdmissionElements(1, $fnumInfo['training']);

            if (!empty($element_id)) {
                $elements       = $h_files->getElementsName(implode(',',$element_id));

                $admissions     = $m_files->getFnumArray($fnums, $elements);

                $data = array();

                foreach ($admissions as $adm) {
                    $str = '<br><hr>';
                    $str .= '<h1>' . JText::_('INSTITUTIONAL_ADMISSION') . '</h1>';
                    foreach ($elements as $element) {

                        if ($element->element_name == 'time_date') {
                            $str .= '<em>'.JHtml::_('date', $adm[$element->tab_name.'___'.$element->element_name], JText::_('DATE_FORMAT_LC')).'</em>';
                        }

                        if ($element->element_name == 'user') {
                            $str .= '<h2>'.JFactory::getUser($adm[$element->tab_name.'___'.$element->element_name])->name.'</h2>';
                        }
                    }

                    $str .= '<br><hr>';
                    $str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

                    foreach ($elements as $element) {
                        $k = $element->tab_name.'___'.$element->element_name;

                        if ($element->element_name != 'id' &&
                            $element->element_name != 'time_date' &&
                            $element->element_name != 'date_time' &&
                            $element->element_name != 'campaign_id' &&
                            $element->element_name != 'student_id'&&
                            $element->element_name != 'user' &&
                            $element->element_name != 'fnum' &&
                            $element->element_name != 'email' &&
                            $element->element_name != 'label' &&
                            $element->element_name != 'code' &&
                            array_key_exists($k, $adm))
                        {
                            $str .= '<tr>';
                            if (strpos($element->element_plugin, 'textarea') !== false) {
                                $str .= '<td colspan="2"><b>'.$element->element_label.'</b> <br>'.JText::_($adm[$k]).'</td>';
                            } else {
                                $str .= '<td width="70%"><b>'.$element->element_label.'</b> </td><td width="30%">'.JText::_($adm[$k]).'</td>';
                            }
                            $str .= '</tr>';
                        }
                    }

                    $str .= '</table>';
                    $str .= '<p></p><hr>';

                    if ($format != 'html') {
                        $str = str_replace('<br>', chr(10), $str);
                        $str = str_replace('<br />', chr(10), $str);
                        $str = str_replace('<h1>', '* ', $str);
                        $str = str_replace('</h1>', ' : ', $str);
                        $str = str_replace('<b>', chr(10), $str);
                        $str = str_replace('</b>', ' : ', $str);
                        $str = str_replace('&nbsp;', ' ', $str);
                        $str = strip_tags($str, '<h1>');
                    }

                    $data[$adm['fnum']][0] = $str;
                }
            }
        }

        // Get information from application form filled out by the student
        $element_id = $m_admission->getAllApplicantAdmissionElements(1, $fnumInfo['training']);
        if(!empty($element_id)) {
            $elements       = $h_files->getElementsName(implode(',',$element_id));
            $admissions     = $m_files->getFnumArray($fnums, $elements);

            foreach ($admissions as $adm) {

                $str = '<br><hr>';
                $str .= '<h1>' . JText::_('STUDENT_ADMISSION') . '</h1>';
                if (isset($name)) {
                    $str .= '<h2>'.$name.'</h2>';
                }

                $str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

                foreach ($elements as $element) {
                    $k = $element->tab_name.'___'.$element->element_name;

                    if ($element->element_name != 'id' &&
                        $element->element_name != 'time_date' &&
                        $element->element_name != 'date_time' &&
                        $element->element_name != 'campaign_id' &&
                        $element->element_name != 'student_id'&&
                        $element->element_name != 'user' &&
                        $element->element_name != 'fnum' &&
                        $element->element_name != 'email' &&
                        $element->element_name != 'label' &&
                        $element->element_name != 'code' &&
                        array_key_exists($k, $adm))
                    {
                        $str .= '<tr>';
                        if (strpos($element->element_plugin, 'textarea') !== false) {
                            $str .= '<td colspan="2"><b>'.JText::_($element->element_label).'</b> <br>'.JText::_($adm[$k]).'</td>';
                        } else {
                            $str .= '<td width="70%"><b>'.JText::_($element->element_label).'</b> </td><td width="30%">'.JText::_($adm[$k]).'</td>';
                        }
                        $str .= '</tr>';
                    }
                }

                $str .= '</table>';
                $str .= '<p></p><hr>';

                if ($format != 'html') {
                    $str = str_replace('<br>', chr(10), $str);
                    $str = str_replace('<br />', chr(10), $str);
                    $str = str_replace('<h1>', '* ', $str);
                    $str = str_replace('</h1>', ' : ', $str);
                    $str = str_replace('<b>', chr(10), $str);
                    $str = str_replace('</b>', ' : ', $str);
                    $str = str_replace('&nbsp;', ' ', $str);
                    $str = strip_tags($str, '<h1>');
                }

                $data[$adm['fnum']][1] = $str;
            }
        }


        return $data;
    }

    // getInterview
    function getInterview($format='html', $fnums = []) {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'interview.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

        $m_interview   = new EmundusModelInterview();
        $m_files        = new EmundusModelFiles;
        $h_files        = new EmundusHelperFiles;

        if (!is_array($fnums)) {
            $fnumInfo = $m_files->getFnumInfos($fnums);
            $fnums = array($fnums);
        } else {
            $fnumInfo = $m_files->getFnumInfos($fnums[0]);
        }

        $element_id = $m_interview->getAllInterviewElements(1, $fnumInfo['training']);
        $elements = $h_files->getElementsName(implode(',',$element_id));
        $evaluations = $m_files->getFnumArray($fnums, $elements);

        $data = array();
        foreach ($evaluations as $eval) {

            if ($eval['jos_emundus_evaluations___user_raw'] > 0) {

                $str = '<br><hr>';
                $str .= '<em>'.JText::_('COM_EMUNDUS_EVALUATION_EVALUATED_ON').' : '.JHtml::_('date', $eval['jos_emundus_evaluations___time_date'], JText::_('DATE_FORMAT_LC')).' - '.$fnumInfo['name'].'</em>';
                $str .= '<h1>'.JText::_('COM_EMUNDUS_EVALUATION_EVALUATOR').': '.JFactory::getUser($eval['jos_emundus_evaluations___user_raw'])->name.'</h1>';
                $str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

                foreach ($elements as $element) {
                    $k = $element->tab_name.'___'.$element->element_name;

                    if ($element->element_name != 'id' &&
                        $element->element_name != 'time_date' &&
                        $element->element_name != 'campaign_id' &&
                        $element->element_name != 'student_id'&&
                        $element->element_name != 'user' &&
                        $element->element_name != 'fnum' &&
                        $element->element_name != 'email' &&
                        $element->element_name != 'label' &&
                        $element->element_name != 'code' &&
                        $element->element_name != 'spacer' &&
                        array_key_exists($k, $eval))
                    {
                        $str .= '<tr>';
                        if (strpos($element->element_name, 'comment') !== false) {
                            $str .= '<td colspan="2"><b>'.JText::_(trim($element->element_label)).'</b> <br>'.JText::_($eval[$k]).'</td>';
                        } else {
                            $str .= '<td width="70%"><b>'.JText::_(trim($element->element_label)).'</b> </td><td width="30%">'.JText::_($eval[$k]).'</td>';
                        }
                        $str .= '</tr>';
                    }
                }

                $str .= '</table>';
                $str .= '<p></p><hr>';

                if ($format != 'html') {
                    $str = str_replace('<br>', chr(10), $str);
                    $str = str_replace('<br />', chr(10), $str);
                    $str = str_replace('<h1>', '* ', $str);
                    $str = str_replace('</h1>', ' : ', $str);
                    $str = str_replace('<b>', chr(10), $str);
                    $str = str_replace('</b>', ' : ', $str);
                    $str = str_replace('&nbsp;', ' ', $str);
                    $str = strip_tags($str, '<h1>');
                }

                if ($format == "simple") {
                    $str = $eval['label'].' : '.JHtml::_('date', $eval['jos_emundus_evaluations___time_date'], JText::_('DATE_FORMAT_LC')).' - '.JFactory::getUser($eval['jos_emundus_evaluations___user_raw'])->name;
                }

                $data[$eval['fnum']][$eval['jos_emundus_evaluations___user_raw']] = $str;
            }
        }
        return $data;
    }

    /**
     * Function to create a new FNUM
     *
     * @param   integer     The id of the campaign.
     * @param   integer     The id of the user.
     * @return  string      FNUM for application.
     * @since   1.6
     */
    public static function createFnum($campaign_id, $user_id, $redirect = true){
		$fnum = '';
	    $app = JFactory::getApplication();
	    JLog::addLogger(array('text_file' => 'com_emundus.fnum.php'), JLog::ALL, 'com_emundus.fnum');

		if (empty($user_id)) {
			$user_id = JFactory::getUser()->id;
			JLog::add('User ID is empty, using current user ID : '. $user_id, JLog::INFO, 'com_emundus.fnum');
		}

		if (!empty($user_id)){
			if (!empty($campaign_id)) {
				$fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user_id, 7, '0', STR_PAD_LEFT);

				if (!empty($fnum)) {
					JLog::add('FNUM created : '.$fnum, JLog::INFO, 'com_emundus.fnum');
				}
			} else {
				JLog::add('Error creating FNUM, campaign_id is empty', JLog::WARNING, 'com_emundus.fnum');
				$app->enqueueMessage(JText::_('COM_EMUNDUS_FAILED_TO_CREATE_FNUM_NO_CAMPAIGN'), 'error');
			}
		} else {
			$ip = !empty($_SERVER) && !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
			JLog::add('Error creating FNUM, user_id is empty for ip : ' . $ip, JLog::WARNING, 'com_emundus.fnum');
			$app->enqueueMessage(JText::_('COM_EMUNDUS_FAILED_TO_CREATE_FNUM_NO_USER'), 'error');

			if ($redirect) {
				$app->redirect('index.php');
			}
		}

		return $fnum;
    }

    /**
     * Checks if a table exists in the database.
     *
     * @since 3.8.6
     * @param String Table name
     * @return Bool True if table found, else false.
     */
    public function tableExists($table_name) {
		$exists = false;

		if (!empty($table_name)) {
			$db = JFactory::getDbo();

			try {
				$exists = $db->setQuery('SHOW TABLE STATUS WHERE Name LIKE ' . $db->quote($table_name))->loadResult();
			} catch (Exception $e) {
				$exists = false;
			}
		}

		return $exists;
    }

    public function saveExcelFilter($user_id, $name, $constraints, $time_date, $itemid) {
        $db = JFactory::getDBO();
	    $query = $db->getQuery(true);

        try {
            /// check if the model name exists
            $raw_query = 'SELECT #__emundus_filters.name
                            FROM #__emundus_filters 
                            WHERE #__emundus_filters.name = ' . $db->quote($name) .
                            ' AND SUBSTRING(#__emundus_filters.constraints, 3, 11) =' . $db->quote('excelfilter');

            $db->setQuery($raw_query);
            $isExistModel = $db->loadObjectList();

            if(!empty($isExistModel)) {
                $name = $name . '_' . date('d-m-Y-H:i:s');
            }

            $query->insert($db->quoteName('#__emundus_filters'))
		        ->columns($db->quoteName(['time_date', 'user', 'name', 'constraints', 'item_id']))
		        ->values($db->quote($time_date).",".$user_id.",".$db->quote($name).",".$db->quote($constraints).",".$itemid);
            $db->setQuery($query);
            $db->execute();

            $query->clear()
	            ->select([$db->quoteName('id'), $db->quoteName('name')])
                ->from($db->quoteName('#__emundus_filters'))
	            ->where($db->quoteName('time_date').' = '.$db->quote($time_date).' AND '.$db->quoteName('user').' = '.$user_id.' AND '.$db->quoteName('item_id').' = '.$itemid);
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /// params :: user_id, $mode = "pdf", selected_elements = []
    public function savePdfFilter($params) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($params)) {
            try {
                // step 1 --> check if the model name exists
                $raw_query = 'SELECT #__emundus_filters.name 
                                FROM #__emundus_filters 
                                WHERE #__emundus_filters.name = ' . $db->quote($params['name']) . ' AND #__emundus_filters.mode = ' . $db->quote('pdf');
                $db->setQuery($raw_query);
                $isExistModel = $db->loadObjectList();

                // step 2 :: insert data here
                if(!empty($isExistModel)) {
                    $params['name'] = $params['name'] . '_' . date('d-m-Y-H:i:s');
                }

                $query->clear()
                    ->insert($db->quoteName('#__emundus_filters'))
                    ->columns($db->quoteName(array_keys($params)))
                    ->values(implode(',', $db->quote(array_values($params))));

                $db->setQuery($query);
                $inserted = $db->execute();

				if ($inserted) {
					return array('id' => $db->insertid(), 'name' => $params['name']);
				}
            } catch (Exception $e) {
                JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            }
        }

		return false;
    }

    // delete pdf filter
    public function deletePdfFilter($fid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($fid)) {
            $query->clear()
                ->delete($db->quoteName('#__emundus_filters'))
                ->where($db->quoteName('#__emundus_filters.id') . '=' . (int)$fid);
            $db->setQuery($query);
            $db->execute();
            return (object)['message' => true];
        } else {
            return false;
        }
    }

	/**
	 * if empty $user_id, then it will return false
	 * if not empty $user_id, then it will return all the filters of the user, empty array if no filters
	 * @param $user_id
	 * @return array|false|mixed
	 */
    public function getExportExcelFilter($user_id) {
	    $filters = false;

		if (!empty($user_id)) {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			try {
				$query->select('*')
					->from($db->quoteName('#__emundus_filters'))
					->where($db->quoteName('user').' = '.$user_id.' AND constraints LIKE '.$db->quote('%excelfilter%'));
				$db->setQuery($query);

				$filters = $db->loadObjectList();
			} catch (Exception $e) {
				echo $e->getMessage();
				JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
				$filters = false;
			}
		}

	    return $filters;
    }

    //// get profile from elements IDs
    public function getAllExportPdfFilter($user_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($user_id)) {
            try {
                $query->clear()
                    ->select('#__emundus_filters.*')
                    ->from($db->quoteName('#__emundus_filters'))
                    ->where($db->quoteName('#__emundus_filters.user').' = '.$user_id.' AND constraints LIKE '.$db->quote('%pdffilter%'))
                    ->andWhere($db->quoteName('#__emundus_filters.mode') . ' = ' . $db->quote('pdf'));

                $db->setQuery($query);
                return $db->loadObjectList();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }

    public function getExportPdfFilterById($model_id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($model_id)) {
            try {
                $query->clear()
                    ->select('#__emundus_filters.*')
                    ->from($db->quoteName('#__emundus_filters'))
                    ->where($db->quoteName('#__emundus_filters.id') . '=' . (int)$model_id . ' AND constraints LIKE '.$db->quote('%pdffilter%'))
                    ->andWhere($db->quoteName('#__emundus_filters.mode') . ' = ' . $db->quote('pdf'));

                $db->setQuery($query);
                return $db->loadObject();
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
        } else {
            return false;
        }
    }

    /// get profiles from element list

    /**
     * @param $elements
     *
     * @return array|false|void
     *
     * @since version
     */
    public function getFabrikDataByListElements($elements) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($elements)) {
            try {
                /// first one --> get fabrik_groups (distinct) from list elements
                $query->clear()
                    ->select('distinct jfg.id')
                    ->from($db->quoteName('#__fabrik_groups', 'jfg'))
                    ->leftJoin($db->quoteName('#__fabrik_elements', 'jfe') . ' ON ' . $db->quoteName('jfg.id') . '=' . $db->quoteName('jfe.group_id'))
                    ->where($db->quoteName('jfe.id') . ' IN (' . $elements . ' )');
                $db->setQuery($query);
                $_groups = $db->loadObjectList();

                $_groupList = "";
                foreach($_groups as $k=>$v) {
                    $_groupList .= $v->id . ',';
                }

                $_groupList = mb_substr($_groupList, 0, -1);

                /// second one --> get fabrik_forms (distinct) from elements
                $query->clear()
                    ->select('distinct jff.id')
                    ->from($db->quoteName('#__fabrik_forms', 'jff'))
                    ->leftJoin($db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON ' . $db->quoteName('jff.id') . '=' . $db->quoteName('jffg.form_id'))
                    ->leftJoin($db->quoteName('#__fabrik_groups', 'jfg') . ' ON ' . $db->quoteName('jffg.group_id') . '=' . $db->quoteName('jfg.id'))
                    ->where($db->quoteName('jfg.id') . ' IN (' . $_groupList . ' )');
                $db->setQuery($query);
                $_forms =$db->loadObjectList();

                $_formList = "";
                foreach($_forms as $k=>$v) {
                    $_formList .= $v->id . ',';
                }

                $_formList = mb_substr($_formList, 0, -1);

                /// third one --> get fabrik_lists (distinct) from forms
                $query->clear()
                    ->select('distinct jfl.id')
                    ->from($db->quoteName('#__fabrik_lists', 'jfl'))
                    ->leftJoin($db->quoteName('#__fabrik_forms', 'jff') . ' ON ' . $db->quoteName('jfl.form_id') . '=' . $db->quoteName('jff.id'))
                    ->where($db->quoteName('jff.id') . ' IN (' . $_formList . ' )');
                $db->setQuery($query);
                $_lists =$db->loadObjectList();

                $_listList = "";
                foreach($_lists as $k=>$v) {
                    $_listList .= $v->id . ',';
                }

                $_listList = mb_substr($_listList, 0, -1);

                /// four one --> get menutype (distinct) menutype
                $query = "SELECT DISTINCT #__menu.menutype 
                            FROM #__menu 
                            WHERE SUBSTRING_INDEX(SUBSTRING(#__menu.link, LOCATE('formid=',jos_menu.link)+7, 4), '&', 1) IN ($_formList)";
                $db->setQuery($query);
                $_menus = $db->loadObjectList();

                $_menuList = "";
                foreach($_menus as $k=>$v) {
                    $_menuList .= '"' . $v->menutype . '"' . ',';
                }

                $_menuList = mb_substr($_menuList, 1, -2);

                /// last one --> get profile (distinct) jos_emundus_setup_profiles
                $queryProfiles = $db->getQuery(true);
                $queryProfiles->clear()
                    ->select('distinct jesp.id')
                    ->from($db->quoteName('#__emundus_setup_profiles', 'jesp'))
                    ->where($db->quoteName('jesp.menutype') . ' IN ("' . $_menuList . '")')
                    ->andWhere($db->quoteName('jesp.published') . '=' . 1);

                $db->setQuery($queryProfiles);

                $_profiles = $db->loadObjectList();

                return array('groups' => $_groups, 'forms' => $_forms, 'lists' => $_lists, 'profiles' => $_profiles);

            } catch(Exception $e) {

            }
        } else {
            return false;
        }
    }

    public function getExportExcelFilterById($fid) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        if(!empty($fid)) {
            $query->clear()
                ->select('#__emundus_filters.*')
                ->from($db->quoteName('#__emundus_filters'))
                ->where($db->quoteName('#__emundus_filters.id') . '=' . (int)$fid);
            $db->setQuery($query);
            return $db->loadObject();
        } else {
            return false;
        }
    }

    public function getAllLetters() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {
            $query->clear()
                ->select('#__emundus_setup_letters.*')
                ->from($db->quoteName('#__emundus_setup_letters'));
            $db->setQuery($query);
            return $db->loadObjectList();

        } catch(Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getExcelLetterById($lid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($lid)) {
            try {
                $query->clear()
                    ->select('#__emundus_setup_letters.*')
                    ->from($db->quoteName('#__emundus_setup_letters'))
                    ->where($db->quoteName('#__emundus_setup_letters.id') . '=' . (int)$lid)
                    ->andWhere($db->quoteName('#__emundus_setup_letters.template_type') . '=' . 4);

                $db->setQuery($query);
                return $db->loadObject();
            } catch(Exception $e) {
                echo $e->getMessage();
                return false;
            }
        } else {
            return false;
        }
    }

    public function checkadmission() {
        $db = JFactory::getDBO();

        try {
            $query = 'SELECT * from #__emundus_admission limit 1';
            $db->setQuery($query);
            $db->query();
            return true;

        } catch (Exception $e) {
           return false;
        }
    }

    /// this code will be fixed in the future
    public function getSelectedElements($selectedElts) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {

            $_string = implode(',', $selectedElts);
            $_find_in_set = "'" . $_string . "'";

            $query = "SELECT jfe.* FROM #__fabrik_elements AS jfe WHERE jfe.id IN ($_string) ORDER BY find_in_set(jfe.id, $_find_in_set)";
            $db->setQuery($query);
            $selected_elts = $db->loadObjectList();
            return array('selected_elements' => $selected_elts);
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }


	/**
	 * @param array $tableAlias
	 * @return array
	 */
	public function _buildWhere($tableAlias = array(), $caller = 'files', $caller_params = array()) {
		$session = JFactory::getSession();
		$params = $session->get('filt_params', []); // came from search box
		$filt_menu = $session->get('filt_menu', []); // came from menu filter (see EmundusHelperFiles::resetFilter)

		$db = JFactory::getDBO();

		if (!is_numeric(@$params['published']) || is_null(@$params['published'])) {
			$params['published'] = 1;
		}

		if (!isset($params['programme'])) {
			$params['programme'] = [];
		}

		$query = array('q' => '', 'join' => '');
		if (!empty($params)) {
			foreach ($params as $key => $value) {

				switch ($key) {
					case 'elements':
						if (!empty($value)) {
							$index = 0;
							foreach ($value as $k => $v) {
								$tab = explode('.', $k);

								if (isset($v['select'])) {
									$adv_select = $v['select'];
								}

								if (isset($v['value'])) {
									$v = $v['value'];
								}

								if (count($tab)>1 && !empty($v)) {

									if ($tab[0] == 'jos_emundus_training') {

										// Do not do LIKE %% search on elements that come from a <select>, we should get the exact value.
										if (isset($adv_select) && $adv_select) {
											$query['q'] .= ' AND search_'.$tab[0].'.id like "'.$v.'"';
										} else {
											$query['q'] .= ' AND search_'.$tab[0].'.id like "%'.$v.'%"';
										}

									} else {
										$query['q'] .= ' AND ';
										// Check if it is a join table
										$sql = 'SELECT join_from_table, table_key, table_join_key FROM #__fabrik_joins WHERE table_join like '.$db->Quote($tab[0]);
										$db->setQuery($sql);
										$join_from_table = $db->loadObject();

										if (!empty($join_from_table->join_from_table)) {
											$table = $join_from_table->join_from_table;
											$table_join = $tab[0];

											// Do not do LIKE %% search on elements that come from a <select>, we should get the exact value.
											if (isset($adv_select) && $adv_select) {
												$query['q'] .= $table_join.'.'.$tab[1].' like "' . $v . '"';
											} else {
												$query['q'] .= $table_join.'.'.$tab[1].' like "%' . $v . '%"';
											}

											/*if (!isset($query[$table])) {
												$query[$table] = true;
												if (!array_key_exists($table, $tableAlias) && !in_array($table, $tableAlias)) {
													$query['join'] .= ' left join '.$table.' on ' .$table.'.fnum like jecc.fnum ';
												}
											}*/

											if (!isset($query[$table_join])) {
												$query[$table_join] = true;
												try {

													if (!array_key_exists($table_join, $tableAlias) && !in_array($table_join, $tableAlias)) {
														$query['join'] .= ' left join '. $table_join .' on ' . $table . '.id=' . $table_join . '.parent_id';
													}
												} catch(Exception $e) {
													if (!array_key_exists($table_join, $tableAlias) && !in_array($table_join, $tableAlias)) {
														$query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like jecc.fnum ';
													}
												}
											}

											$query['join'] .= ' left join ' . $table . ' as ' . $table . '_' . $index . ' on ' . $table . '_' . $index  . '.' . $join_from_table->table_key .' like ' . $table_join . '.' . $join_from_table->table_join_key;
										} else {

											$sql = 'SELECT plugin FROM #__fabrik_elements WHERE name like '.$db->Quote($tab[1]);
											$db->setQuery($sql);
											$res = $db->loadResult();
											if ($res == "radiobutton" || $res == "dropdown" || $res == "databasejoin" || (isset($adv_select) && $adv_select)) {
												$query['q'] .= $tab[0].'.'.$tab[1].' like "' . $v . '"';
											} else {
												$query['q'] .= $tab[0].'.'.$tab[1].' like "%' . $v . '%"';
											}

											if (!isset($query[$tab[0]])) {
												$query[$tab[0]] = true;
												if (!array_key_exists($tab[0], $tableAlias) && !in_array($tab[0], $tableAlias)) {
													$query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like jecc.fnum ';
												}
											}
										}
									}
								}

								$index++;
							}
						}
						break;

					case 'elements_other':
						if (!empty($value)) {
							foreach ($value as $k => $v) {
								if (!empty($v)) {
									$tab = explode('.', $k);
									if (count($tab) > 1) {
										if ($tab[0] == 'jos_emundus_training') {
											$query['q'] .= ' AND ';
											$query['q'] .= ' search_'.$tab[0].'.id like "%' . $v . '%"';
										} else {
											$query['q'] .= ' AND ';
											$query['q'] .= $tab[0].'.'.$tab[1].' like "%' . $v . '%"';

											if (!isset($query[$tab[0]])) {
												$query[$tab[0]] = true;
												if (!array_key_exists($tab[0], $tableAlias)) {
													$query['join'] .= ' left join '.$tab[0].' on ' .$tab[0].'.fnum like jecc.fnum ';
												}
											}
										}
									}
								}
							}
						}
						break;

					case 's':
						if (!empty($value)) {
							$q = $this->_buildSearch($value, $tableAlias, $caller_params);
							foreach ($q['q'] as $v) {
								$query['q'] .= $v;
							}

							foreach ($q['join'] as $u) {
								$query['join'] .= $u;
							}

							if (isset($q['users'])) {
								$query['users'] = true;
							}
							if (isset($q['em_user'])) {
								$query['em_user'] = true;
							}
						}
						break;

					case 'admission':
						if (!empty($value)) {

							$query['q'] .= ' and ad.admission like "%' . $value . '%"';
							if (!isset($query['admission'])) {

								$query['admission'] = true;
								if (!array_key_exists('jos_emundus_admission', $tableAlias))
									$query['join'] .=' left join #__emundus_admission as ad on ad.fnum like c.fnum ';

							}
						}
						break;

					case 'finalgrade':
						if (!empty($value)) {
							$query['q'] .= ' and fg.final_grade like "%' . $value . '%"';
							if (!isset($query['final_g'])) {
								$query['final_g'] = true;
								if (!array_key_exists('jos_emundus_final_grade', $tableAlias)) {
									$query['join'] .= ' left join #__emundus_final_grade as fg on fg.fnum like jecc.fnum ';
								}
							}
						}
						break;

					case 'schoolyear':
						if (!empty($value)) {
							if (($value[0] == "%") || empty($value[0])) {
								$query['q'] .= '';
							} else {
								$query['q'] .= ' and esc.year IN ("' . implode('","', $value) . '") ';
							}
						}
						break;

					case 'programme':
						if (!empty($value)) {
							if ($value[0] == "%" || empty($value[0])) {
								$query['q'] .= ' ';
							} else {
								$query['q'] .= ' and sp.code IN ("' . implode('","', $value) . '") ';
							}
						}
						break;

					case 'campaign':
						if ($value) {
							$query['q'] .= ' AND esc.published=1 ';

							if ($value[0] == "%" || empty($value[0])) {
								$query['q'] .= ' ';
							} else {
								$query['q'] .= ' AND esc.id IN (' . implode(',', $value) . ') ';
							}
						}
						break;

					case 'groups':
						if (!empty($value)) {
							$query['q'] .= ' and  (ge.group_id=' . $db->Quote($value) . ' OR ge.user_id IN (select user_id FROM #__emundus_groups WHERE group_id=' .$db->Quote($value) . ')) ';

							if (!isset($query['group_eval'])) {
								$query['group_eval'] = true;
								if (!array_key_exists('jos_emundus_groups_eval', $tableAlias)) {
									$query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = jecc.applicant_id and ge.campaign_id = jecc.campaign_id ';
								}
							}
						}
						break;

					case 'group_assoc':
						if (!empty($value)) {
							$query['join'] .= ' 
	                            LEFT JOIN #__emundus_group_assoc as ga on ga.fnum = jecc.fnum 
	                            LEFT JOIN #__emundus_setup_groups_repeat_course as grc on grc.course LIKE esc.training 
	                            LEFT JOIN #__emundus_setup_groups as sg on grc.parent_id = sg.id ';
							$query['q'] .= ' and (ga.group_id IN ('.implode(',', $value).') OR sg.id IN ('.implode(',', $value).')) ';

						}
						break;

					case 'user':
						if (!empty($value)) {
							$query['q'] .= ' and (ge.user_id=' . $db->Quote($value) .
								' OR ge.group_id IN (select e.group_id FROM #__emundus_groups e WHERE e.user_id=' .
								$db->Quote($value) . '))';
							if (!isset($query['group_eval'])) {
								$query['group_eval'] = true;
								if (!array_key_exists('jos_emundus_groups_eval', $tableAlias)) {
									$query['join'] .= ' left join #__emundus_groups_eval as ge on ge.applicant_id = jecc.applicant_id and ge.campaign_id = jecc.campaign_id ';
								}
							}
						}
						break;

					case 'missing_doc':
						if (!empty($value)) {
							$query['q'] .=' and (' . $value . ' NOT IN (SELECT attachment_id FROM #__emundus_uploads eup WHERE #__emundus_uploads.user_id = u.id)) ';
							if (!array_key_exists('jos_emundus_uploads', $tableAlias)) {
								$query['join'] = ' left join #__emundus_uploads on #__emundus_uploads.user_id = jecc.applicant_id ';
							}
						}
						break;

					case 'complete':
						if (!empty($value)) {
							if ($value == 1) {
								$query['q'] .= 'and #__users.id IN (SELECT user FROM #__emundus_declaration ed WHERE #__emundus_declaration.user = #__users.id) ';
							} else {
								$query['q'] .= 'and #__users.id NOT IN (SELECT user FROM #__emundus_declaration ed WHERE #__emundus_declaration.user = #__users.id) ';
							}
						}
						break;

					case 'validate':
						if (!empty($value)) {
							if ($value == 1) {
								$query['q'] .= ' and #__emundus_declaration.validated = 1 ';
							} else {
								$query['q'] .= ' and #__emundus_declaration.validated = 0 ';
							}
						}
						break;

					case 'status':
						if ($value) {
							$filt_menu_defined = (isset($filt_menu['status'][0]) && $filt_menu['status'][0] != '' && $filt_menu['status'] != "%" );

							// session filter is empty
							if ($value[0] == "%" || !isset($value[0]) || $value[0] == '') {
								if (!$filt_menu_defined) {
									$query['q'] .= ' ';
								} else {
									$query['q'] .= ' and jecc.status IN (' . implode(',', $filt_menu['status']) . ') ';
								}

							} else {

								// Check if session filter exist in menu filter, if at least one session filter not in menu filter, reset to menu filter
								$diff = array();
								if (is_array($value) && $filt_menu_defined) {
									$diff = array_diff($value, $filt_menu['status']);
								}

								if (count($diff) == 0) {
									$query['q'] .= ' and jecc.status IN (' . implode(',', $value) . ') ';
								} else {
									$query['q'] .= ' and jecc.status IN (' . implode(',', $filt_menu['status']) . ') ';
								}
							}
						}
						break;

					case 'tag':
						if ($value) {
							if ($value[0] == "%" || !isset($value[0]) || $value[0] === '') {
								$query['q'] .= ' ';
							} else {

								if (isset($filt_menu['tag'][0]) && $filt_menu['tag'][0] != '' && $filt_menu['tag'] != "%") {
									// This allows hiding of files by tag.
									$filt_menu_not = array_filter($filt_menu['tag'], function($e) {
										return strpos($e, '!') === 0;
									});
								}

								// This allows hiding of files by tag.
								$not_in = array_filter($value, function($e) {
									return strpos($e, '!') === 0;
								});

								if (is_array($not_in) && !empty($filt_menu_not)) {
									$not_in = array_unique(array_merge($not_in, $filt_menu_not));
								}

								if (!empty($not_in)) {
									$value = array_diff($value, $not_in);
									$not_in = array_map(function($v) {
										return ltrim($v, '!');
									}, $not_in);
									$query['q'] .= ' and jecc.fnum NOT IN (SELECT cc.fnum FROM jos_emundus_campaign_candidature AS cc LEFT JOIN jos_emundus_tag_assoc as ta ON ta.fnum = cc.fnum WHERE ta.id_tag IN (' . implode(',', $not_in) . ')) ';
								}

                                if (!empty($value)) {
                                    $query['q'] .= ' and eta.id_tag IN ('.implode(',', $value).') ';
                                }
                            }
                        }
                        break;

					case 'newsletter' :
                            if ($value[0] == "1") {
                                $query['q'] .= ' and eu.newsletter LIKE \'["1"]\' OR eu.newsletter = 1 ';
                            }
                            elseif ($value[0] == "2") {
                                $query['q'] .= ' and eu.newsletter LIKE \'[""]\' OR eu.newsletter = \'\' ';
                            }
                            break;

					case 'published':
						if ($value == "-1") {
							$query['q'] .= ' and jecc.published=-1 ';
						} elseif ($value == 0) {
							$query['q'] .= ' and jecc.published=0 ';
						} else {
							$query['q'] .= ' and jecc.published=1 ';
						}
						break;
					default:
						break;
				}
			}
		}

		// force menu filter
		if (!empty($filt_menu['status']) && is_array($filt_menu['status']) && !empty($filt_menu['status'][0]) && $filt_menu['status'][0] != "%") {
			$query['q'] .= ' AND jecc.status IN ("' . implode('","', $filt_menu['status']) . '") ';
		}

		$and = ' AND ';
        $sql_code = '1=1';
		if (!empty($filt_menu['programme'])) {
			if (!empty($filt_menu['programme'][0]) && $filt_menu['programme'][0] == "%") {
				$sql_code = '1=1';
			} elseif (!empty($filt_menu['programme'][0])) {
				// ONLY FILES LINKED TO MY GROUPS OR TO MY ACCOUNT
				$sql_code = ' sp.code IN ("'.implode('","', $caller_params['code']).'") ';
				$and = ' OR ';
			} else {
				if ($filt_menu['programme'][0] != "" && count($filt_menu['programme']) > 0) {
					$sql_code = ' sp.code in ("'.implode('","', $filt_menu['programme']).'") ';
				}
			}
		} elseif (!empty($caller_params['code'])) {
            // ONLY FILES LINKED TO MY GROUPS OR TO MY ACCOUNT
            $sql_code = ' sp.code IN ("'.implode('","', $caller_params['code']).'") ';
            $and = ' OR ';
        }

		$sql_fnum = '';
		if (!empty($caller_params['fnum_assoc'])) {
			$sql_fnum = $and.' jecc.fnum IN ("'.implode('","', $caller_params['fnum_assoc']).'") ';
		}

		if (!empty($sql_code) || !empty($sql_fnum)) {
			$query['q'] .= ' AND (' . $sql_code . ' ' . $sql_fnum . ') ';
			$query['q'] .= ' AND esc.published > 0';
		}
		// WARNING!
		else if (!empty($params['programme']) && ($params['programme'][0] == "%" || empty($params['programme'][0])) || empty(array_intersect($params['programme'], array_filter($caller_params['code'])))) {
			$query['q'] .= ' AND 1=2 ';
		}
		return $query;
	}

    /**
     * @param array $already_joined
     * @param string $caller
     * @param array $caller_params
     * @return array containing 'q' the where clause and 'join' the join clause
     */
    public function _moduleBuildWhere($already_joined = array(), $caller = 'files', $caller_params = [], $filters_to_exclude = []) {
		$where = ['q' => '', 'join' => ''];

	    $db = JFactory::getDbo();

		// First we filter on files that are associated to us (either by programme or by fnum)
		$andor = '';
		$programme_where_cond = '';
	    if (!empty($caller_params) && !empty($caller_params['code'])) {
		    $programme_where_cond .= ' sp.code IN (' . implode(',', $db->quote($caller_params['code'])) . ')';
			$andor = ' OR ';
	    }

	    $fnum_assoc_where_cond = '';
	    if (!empty($caller_params) && !empty($caller_params['fnum_assoc'])) {
			$fnum_assoc_where_cond .= $andor . ' jecc.fnum IN (' . implode(',', $db->quote($caller_params['fnum_assoc'])) . ')';
	    }

		if (!empty($programme_where_cond) || !empty($fnum_assoc_where_cond)) {
			$where['q'] .= ' AND (' . $programme_where_cond . $fnum_assoc_where_cond . ') ';
		}

        if ($caller == 'files') {
            $where['q'] .= ' AND esc.published > 0';
        }

	    $menu = JFactory::getApplication()->getMenu();
		if (!empty($menu)) {
			$active = $menu->getActive();

			if (!empty($active)) {
				$menu_params = $active->params;
				$filter_menu_values = $menu_params->get('em_filters_values', '');
				$filter_menu_values = explode(',', $filter_menu_values);

				if (!empty($filter_menu_values)) {
					$filter_names = $menu_params->get('em_filters_names', '');
					$filter_names = explode(',', $filter_names);

					foreach($filter_names as $key => $filter_name) {
						if (isset($filter_menu_values[$key]) && $filter_menu_values[$key] != '') {
							$values = explode('|', $filter_menu_values[$key]);

							switch ($filter_name) {
								case 'status':
									$where['q'] .= ' AND jecc.status IN ("' . implode('","', $values) . '") ';
									break;
								case 'programme':
									$where['q'] .= ' AND sp.id IN ("' . implode('","', $values) . '") ';
									break;
								case 'campaign':
									$where['q'] .= ' AND esc.id IN ("' . implode('","', $values) . '") ';
									break;
							}
						}
					}
				}
			}
		}

		// Now we handle session filters (if any)
	    $session = JFactory::getSession();
	    $session_filters = $session->get('em-applied-filters', []);
	    $quick_search_filters = $session->get('em-quick-search-filters', []);
	    if (!empty($session_filters) || !empty($quick_search_filters)) {
		    if (empty($already_joined)) {
			    $already_joined = [
				    'jecc' => 'jos_emundus_campaign_candidature',
				    'ss' => 'jos_emundus_setup_status',
				    'esc' => 'jos_emundus_setup_campaigns',
				    'sp' => 'jos_emundus_setup_programmes',
				    'u' => 'jos_users',
				    'eu' => 'jos_emundus_users',
				    'eta' => 'jos_emundus_tag_assoc'
			    ];
		    }

		    if (!empty($quick_search_filters)) {
			    $quick_search_where = ' AND (';

			    $at_least_one = false;

                $campaign_candidature_alias = array_search('jos_emundus_campaign_candidature', $already_joined);
                $emundus_users_alias = array_search('jos_emundus_users', $already_joined);
                if (empty($emundus_users_alias)) {
                    $emundus_users_alias = 'eu';
                    $where['join'] .= ' LEFT JOIN ' . $db->quoteName('jos_emundus_users', $emundus_users_alias) . ' ON ' . $db->quoteName($emundus_users_alias.'.id') . ' = ' . $db->quoteName($campaign_candidature_alias.'.applicant_id');
                    $already_joined[$emundus_users_alias] = 'jos_emundus_users';
                }

                $users_alias = array_search('jos_users', $already_joined);
                if (empty($users_alias)) {
                    $users_alias = 'u';
                    $where['join'] .= ' LEFT JOIN ' . $db->quoteName('jos_users', $users_alias) . ' ON ' . $db->quoteName($users_alias.'.id') . ' = ' . $db->quoteName($emundus_users_alias.'.user_id');
                    $already_joined['u'] = 'jos_users';
                }

			    $scopes = [
                    $campaign_candidature_alias . '.applicant_id' => 'eu.applicant_id',
                    $campaign_candidature_alias . '.fnum' => 'jecc.fnum',
                    $users_alias . '.username' => 'u.username',
                    $emundus_users_alias . '.firstname' =>  'eu.firstname',
                    $emundus_users_alias . '.lastname' => 'eu.lastname',
                    $users_alias . '.email' => 'u.email',
                ];
			    foreach ($quick_search_filters as $index => $filter) {
				    if (!empty($filter['scope'])) {
					    if ($filter['scope'] === 'everywhere') {
						    $at_least_one = true;

                            $scope_index = 0;
						    foreach ($scopes as $scope_alias => $scope) {
							    if ($index > 0 || $scope_index > 0) {
								    $quick_search_where .= ' OR ';
							    }
							    $quick_search_where .= $this->writeQueryWithOperator($scope_alias, $filter['value'], 'LIKE');
                                $scope_index++;
						    }
					    } else if (in_array($filter['scope'], $scopes)) {
                            $scope_alias = array_search($filter['scope'], $scopes);
						    $at_least_one = true;
						    if ($index > 0) {
							    $quick_search_where .= ' OR ';
						    }
						    $quick_search_where .= $this->writeQueryWithOperator($scope_alias, $filter['value'], '=');
					    }
				    }
			    }

			    if ($at_least_one) {
				    $quick_search_where .= ')';
				    $where['q'] .= $quick_search_where;
			    }
		    }

            if (!empty($session_filters)) {
                foreach ($session_filters as $filter) {
                    if (in_array($filter['uid'], $filters_to_exclude)) {
                        continue;
                    }

                    if ((!is_array($filter['value']) || !in_array('all', $filter['value'], true)) && (!empty($filter['value']) || $filter['value'] == '0')) {
                        $filter_id = str_replace(['filter-', 'default-filter-'], '', $filter['id']);

                        if (is_numeric($filter_id)) {
                            $filter_id = (int)$filter_id;
                            $fabrik_element_data = $this->getFabrikElementData($filter_id);
                            if (!empty($fabrik_element_data['name']) && !empty($fabrik_element_data['db_table_name'])) {
                                $mapped_to_fnum = $this->isTableLinkedToCampaignCandidature($fabrik_element_data['db_table_name']);

                                // if element is not directly mapped to fnum, we try to find a join table
                                if (!$mapped_to_fnum) {
                                    $query = $db->getQuery(true);

                                    if (!in_array($fabrik_element_data['db_table_name'], $already_joined)) {
                                        foreach ($already_joined as $already_join_alias => $already_joined_table_name) {
                                            $already_join_alias = !is_numeric($already_join_alias) ? $already_join_alias : $already_joined_table_name;

                                            if ($fabrik_element_data['plugin'] === 'databasejoin' && in_array($fabrik_element_data['element_params']['database_join_display_type'], ['checklist', 'multilist'])) {
                                                $query->clear()
                                                    ->select('*')
                                                    ->from('#__fabrik_joins')
                                                    ->where('table_join = ' . $db->quote($fabrik_element_data['db_table_name']))
                                                    ->andWhere('join_from_table = ' . $db->quote($already_joined_table_name))
                                                    ->andWhere('element_id = ' . $db->quote($fabrik_element_data['element_id']));

                                                $db->setQuery($query);
                                                $join_informations = $db->loadAssoc();

                                                if (!empty($join_informations)) {
                                                    $join_informations['params'] = json_decode($join_informations['params'], true);
                                                    $already_joined[] = $fabrik_element_data['db_table_name'];
                                                    $where['join'] .= ' LEFT JOIN ' . $db->quoteName($join_informations['table_join']) . ' ON ' . $db->quoteName($join_informations['table_join'] . '.parent_id') . ' = ' . $db->quoteName($already_join_alias . '.id');
                                                    $mapped_to_fnum = true;
                                                    break;
                                                }
                                            } else {
                                                $query->clear()
                                                    ->select('*')
                                                    ->from('#__fabrik_joins')
                                                    ->where('table_join = ' . $db->quote($already_joined_table_name))
                                                    ->andWhere('join_from_table = ' . $db->quote($fabrik_element_data['db_table_name']))
                                                    ->andWhere('table_key = ' . $db->quote('id'))
                                                    ->andWhere('list_id = ' . $db->quote($fabrik_element_data['list_id']));

                                                $db->setQuery($query);
                                                $join_informations = $db->loadAssoc();

                                                if (!empty($join_informations)) {
                                                    $already_joined[] = $fabrik_element_data['db_table_name'];

                                                    $where['join'] .= ' LEFT JOIN ' . $db->quoteName($join_informations['join_from_table']) . ' ON ' . $db->quoteName($join_informations['join_from_table'] . '.id') . ' = ' . $db->quoteName($already_join_alias . '.' . $join_informations['table_join_key']);
                                                    $mapped_to_fnum = true;
                                                    break;
                                                }
                                            }
                                        }
                                    } else {
                                        $mapped_to_fnum = true;
                                    }
                                }

                                if ($mapped_to_fnum) {
                                    if ($fabrik_element_data['group_params']['repeat_group_button'] == 1) {
                                        $join_informations = $this->getJoinInformations($filter_id, $fabrik_element_data['group_id'], $fabrik_element_data['list_id']);

                                        if (!empty($join_informations)) {
                                            $parent_table_alias = '';
                                            $parent_table = $join_informations['join_from_table'];

                                            if (in_array($parent_table, $already_joined) || !$this->isTableLinkedToCampaignCandidature($parent_table)) {
                                                if (!in_array($parent_table, $already_joined)) {
                                                    $already_joined[] = $parent_table;
                                                    $where['join'] .= ' LEFT JOIN ' . $db->quoteName($parent_table) . ' ON ' . $parent_table . '.fnum = jecc.fnum ';
                                                } else {
                                                    $parent_table_alias = array_search($parent_table, $already_joined);
                                                }

                                                $parent_table_alias = !empty($parent_table_alias) && !is_numeric($parent_table_alias) ? $parent_table_alias : $parent_table;
                                            }

                                            if (!empty($parent_table_alias)) {
                                                $child_table_alias = '';
                                                $child_table = $join_informations['table_join'];
                                                if (!in_array($child_table, $already_joined)) {
                                                    $already_joined[] = $child_table;

                                                    $join_informations['params'] = json_decode($join_informations['params'], true);
                                                    if (!empty($join_informations['params']) && $join_informations['params']['type'] == 'repeatElement') {
                                                        $where['join'] .= ' LEFT JOIN ' . $db->quoteName($child_table) . ' ON ' . $child_table . '.' . $join_informations['table_join_key'] . ' = ' . $parent_table_alias . '.id';
                                                    } else {
                                                        $where['join'] .= ' LEFT JOIN ' . $db->quoteName($child_table) . ' ON ' . $child_table . '.' . $join_informations['table_join_key'] . ' = ' . $parent_table_alias . '.' . $join_informations['table_key'];
                                                    }
                                                } else {
                                                    $child_table_alias = array_search($child_table, $already_joined);
                                                }
                                                $child_table_alias = !empty($child_table_alias) && !is_numeric($child_table_alias) ? $child_table_alias : $child_table;

                                                $where['q'] .= ' AND ' . $this->writeQueryWithOperator($child_table_alias . '.' . $fabrik_element_data['name'], $filter['value'], $filter['operator'], $filter['type'], $fabrik_element_data);
                                            }
                                        } else {
                                            JLog::add('Could not handle repeat group for element ' . $filter_id . ' in ' . $caller . ' with params ' . json_encode($caller_params), JLog::WARNING, 'com_emundus.error');
                                        }
                                    }
                                    else {
                                        $db_table_name_alias = '';
                                        if (!in_array($fabrik_element_data['db_table_name'], $already_joined)) {
                                            $already_joined[] = $fabrik_element_data['db_table_name'];
                                            $where['join'] .= ' LEFT JOIN ' . $db->quoteName($fabrik_element_data['db_table_name']) . ' ON ' . $fabrik_element_data['db_table_name'] . '.fnum = jecc.fnum ';
                                        } else {
                                            $db_table_name_alias = array_search($fabrik_element_data['db_table_name'], $already_joined);
                                        }
                                        $db_table_name_alias = !empty($db_table_name_alias) && !is_numeric($db_table_name_alias) ? $db_table_name_alias : $fabrik_element_data['db_table_name'];

                                        $where['q'] .= ' AND ' . $this->writeQueryWithOperator($db_table_name_alias . '.' . $fabrik_element_data['name'], $filter['value'], $filter['operator'], $filter['type'], $fabrik_element_data);
                                    }
                                }
                            }
                        } else {
                            if (sizeof($filter['value']) == 1) {
                                $filter['value'] = $filter['value'][0];
                            }

                            switch($filter_id) {
                                case 'status':
                                    $where['q'] .= ' AND ' . $this->writeQueryWithOperator('jecc.status', $filter['value'], $filter['operator']);
                                    break;
                                case 'campaigns':
                                    $where['q'] .= ' AND ' . $this->writeQueryWithOperator('jecc.campaign_id', $filter['value'], $filter['operator']);
                                    break;
                                case 'years':
                                    $where['q'] .= ' AND ' . $this->writeQueryWithOperator('esc.year', $filter['value'], $filter['operator']);

                                    break;
                                case 'programs':
                                    $where['q'] .= ' AND ' . $this->writeQueryWithOperator('sp.id', $filter['value'], $filter['operator']);
                                    break;
                                case 'published':
                                    $where['q'] .= ' AND ' . $this->writeQueryWithOperator('jecc.published', $filter['value'], $filter['operator']);
                                    break;
                                case 'tags':
                                    $where['q'] .= ' AND ( ' . $this->writeQueryWithOperator('eta.id_tag', $filter['value'], $filter['operator']) . ' )';
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
            } else if (!in_array('published', $filters_to_exclude)) {
                $where['q'] .= ' AND ' . $this->writeQueryWithOperator('jecc.published', 1, '=');
            }
	    } else if (!in_array('published', $filters_to_exclude)) {
            $where['q'] .= ' AND ' . $this->writeQueryWithOperator('jecc.published', 1, '=');
        }

        return $where;
    }

	public function getFabrikElementData(int $element_id): array
	{
		$data = [];

		if (!empty($element_id)) {
			/**
			 * I need, list id, form id, groupd id and group params, table name and element name
			 */
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('jfe.id as element_id, jfe.name, jfe.plugin, jfe.params as element_params, jfg.id as group_id, jfg.params as group_params, jffg.form_id, jfl.id as list_id, jfl.db_table_name, jfj.table_join as fabrik_table_join')
				->from('#__fabrik_elements AS jfe')
				->leftJoin('#__fabrik_groups AS jfg ON jfg.id = jfe.group_id')
				->leftJoin('#__fabrik_formgroup AS jffg ON jffg.group_id = jfg.id')
				->leftJoin('#__fabrik_lists AS jfl ON jfl.form_id = jffg.form_id')
				->leftJoin('#__fabrik_joins AS jfj ON jfj.list_id = jfl.id AND (jfj.element_id = ' . $element_id . ' OR jfj.group_id = jfg.id)')
				->where('jfe.id = ' . $element_id);

			try {
				$db->setQuery($query);
				$data = $db->loadAssoc();
			} catch(Exception $e) {
				JLog::add('Failed to retreive fabrik element data in filter context ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}

			if (!empty($data['fabrik_table_join'])) {
				$data['db_table_name'] = $data['fabrik_table_join'];
			}

			if (!empty($data['group_params'])) {
				$data['group_params'] = json_decode($data['group_params'], true);
			}

			if (!empty($data['element_params'])) {
				$data['element_params'] = json_decode($data['element_params'], true);
			}
		}

		return $data;
	}

	public function getJoinInformations($element_id, $group_id = 0, $list_id = 0): array
	{
		$data = [];

        if (!empty($element_id)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if (!empty($element_id)) {
                $query->select('*')
                    ->from('#__fabrik_joins')
                    ->where('element_id = ' . $element_id);

                try {
                    $db->setQuery($query);
                    $data = $db->loadAssoc();
                } catch(Exception $e) {
                    JLog::add('Failed to retreive join informations in filter context ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                }
            }

            if ((empty($data) || empty($data['join_from_table']))  && !empty($group_id)) {
                $query->clear()
                    ->select('*')
                    ->from('#__fabrik_joins')
                    ->where('group_id = ' . $group_id);

                if (!empty($list_id)) {
                    $query->where('list_id = ' . $list_id);
                }

                try {
                    $db->setQuery($query);
                    $data = $db->loadAssoc();
                } catch(Exception $e) {
                    JLog::add('Failed to retreive join informations in filter context ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                }
            }
        } else if (!empty($group_id)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*')
				->from('#__fabrik_joins')
				->where('group_id = ' . $group_id)
				->andWhere('element_id = 0');

			if (!empty($list_id)) {
				$query->where('list_id = ' . $list_id);
			}

			try {
				$db->setQuery($query);
				$data = $db->loadAssoc();
			} catch(Exception $e) {
				JLog::add('Failed to retreive join informations in filter context ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
        }

		return is_array($data) ? $data : [];
	}

	/**
	 * @param string $searched_table
	 * @param string $base_table
	 * @param int $i, the iteration number
	 *
	 * if the array is empty, it means that the tables are not linked
	 * @return array
	 */
	public function findJoinsBetweenTablesRecursively($searched_table, $base_table, $i = 0): array
	{
		$joins = [];

		if (!empty($searched_table) && !empty($base_table) && $searched_table != $base_table) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->clear()
				->select('COLUMN_NAME as table_key, REFERENCED_COLUMN_NAME as table_join_key, TABLE_NAME as join_from_table, REFERENCED_TABLE_NAME as table_join')
				->from($db->quoteName('INFORMATION_SCHEMA.KEY_COLUMN_USAGE'))
				->where('TABLE_NAME IN (' . $db->quote($searched_table) . ', ' . $db->quote($base_table) . ')')
				->andWhere('REFERENCED_TABLE_NAME IN (' . $db->quote($base_table) . ', ' . $db->quote($searched_table) . ')');

			try {
				$db->setQuery($query);
				$join = $db->loadAssoc();
			} catch(Exception $e) {
				JLog::add('Failed to retreive join informations in filter context ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}

			if (empty($join)) {
				// look into fabrik tables
				$query->clear()
					->select('DISTINCT table_key, table_join_key, join_from_table, table_join, params')
					->from($db->quoteName('#__fabrik_joins'))
					->where('table_join = ' . $db->quote($base_table))
					->andWhere('table_join_key IN ("id", "parent_id")');

				try {
					$db->setQuery($query);
					$leftJoin = $db->loadAssoc();
				} catch(Exception $e) {
					JLog::add('Failed to retreive join informations in filter context ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}

				$next_index = $i + 1;
				$joins[] = $leftJoin;
				$joins = array_merge($joins, $this->findJoinsBetweenTablesRecursively($searched_table, $leftJoin['join_from_table'], $next_index));
			} else {
				$joins[] = $join;
			}
		}

		if ($i === 0) {
			if (!empty($joins)) {
				$joins = array_reverse($joins);

				// if last join is not the searched table, then it's not a valid join, we weren't able to find a path between the two tables
				// so we return an empty array
				if ($joins[0]['join_from_table'] !== $searched_table && $joins[0]['table_join'] !== $searched_table) {
					$joins = [];
				} else {
					$joins = array_map(function($join) {
						if (!empty($join['params'])) {
							$join['params'] = json_decode($join['params'], true);
						}
						return $join;
					}, $joins);
				}
			}
		}

		return $joins;
	}

	/**
	 * @param $found_joins array the joins found by findJoinsBetweenTablesRecursively, ordered from the searched table to the base table
	 * @param $already_joined_tables array referenced array
	 * @return string
	 */
	public function writeJoins($found_joins, &$already_joined_tables, $create_alias = false) {
		$left_joins = '';

		if (!empty($found_joins)) {
			$dbo = JFactory::getDbo();
			foreach($found_joins as $element_join) {
				if (!in_array($element_join['table_join'], $already_joined_tables)) {
					$table_join_alias = $element_join['table_join'];
					if ($create_alias) {
						$table_join_alias = 'table_join_' . sizeof($already_joined_tables);
						$already_joined_tables[$table_join_alias] = $element_join['table_join'];
					} else {
						$already_joined_tables[] = $element_join['table_join'];
					}

					$join_from_table_alias = $element_join['join_from_table'];

					if (in_array($element_join['join_from_table'], $already_joined_tables)) {
						$found_alias = array_search($element_join['join_from_table'], $already_joined_tables);

						if (!is_numeric($found_alias)) {
							$join_from_table_alias = $found_alias;
						}
					}

					if (!empty($element_join['params']) && $element_join['params']['type'] === 'repeatElement') {
						if ($create_alias) {
							$left_joins .= ' LEFT JOIN ' . $dbo->quoteName($element_join['table_join']) . ' AS ' . $dbo->quoteName($table_join_alias) . ' ON ' . $dbo->quoteName($table_join_alias . '.parent_id') . ' = ' . $dbo->quoteName($join_from_table_alias. '.id');
						} else {
							$left_joins .= ' LEFT JOIN ' . $dbo->quoteName($element_join['table_join']) . ' ON ' . $dbo->quoteName($element_join['table_join'] . '.parent_id') . ' = ' . $dbo->quoteName($join_from_table_alias . '.id');
						}
					} else {
						if ($create_alias) {
							$left_joins .= ' LEFT JOIN ' . $dbo->quoteName($element_join['table_join']) . ' AS ' . $dbo->quoteName($table_join_alias) . ' ON ' . $dbo->quoteName($table_join_alias . '.' . $element_join['table_join_key']) . ' = ' . $dbo->quoteName($join_from_table_alias. '.' . $element_join['table_key']);
						} else {
							$left_joins .= ' LEFT JOIN ' . $dbo->quoteName($element_join['table_join']) . ' ON ' . $dbo->quoteName($element_join['table_join'] . '.' . $element_join['table_join_key']) . ' = ' . $dbo->quoteName($join_from_table_alias. '.' . $element_join['table_key']);
						}
					}
				} else if (!in_array($element_join['join_from_table'], $already_joined_tables)) {
					$table_join_alias = $element_join['table_join'];
					$join_from_table_alias = $element_join['join_from_table'];
					if ($create_alias) {
						$join_from_table_alias = 'table_join_' . sizeof($already_joined_tables);
						$already_joined_tables[$join_from_table_alias] = $element_join['join_from_table'];
					} else {
						$already_joined_tables[] = $element_join['join_from_table'];
					}


					if (in_array($element_join['table_join'], $already_joined_tables)) {
						$found_alias = array_search($element_join['table_join'], $already_joined_tables);

						if (!is_numeric($found_alias)) {
							$table_join_alias = $found_alias;
						}
					}

					if (!empty($element_join['params']) && $element_join['params']['type'] === 'repeatElement') {
						if ($create_alias) {
							$left_joins .= ' LEFT JOIN ' . $dbo->quoteName($element_join['join_from_table']) . ' AS ' . $join_from_table_alias . ' ON ' . $dbo->quoteName($join_from_table_alias . '.parent_id') . ' = ' . $dbo->quoteName($table_join_alias. '.id');

						} else {
							$left_joins .= ' LEFT JOIN ' . $dbo->quoteName($element_join['join_from_table']) . ' ON ' . $dbo->quoteName($element_join['join_from_table'] . '.parent_id') . ' = ' . $dbo->quoteName($table_join_alias. '.id');
						}
					} else {
						if ($create_alias) {
							$left_joins .= ' LEFT JOIN ' . $dbo->quoteName($element_join['join_from_table']) . ' AS ' . $join_from_table_alias . ' ON ' . $dbo->quoteName($join_from_table_alias. '.' . $element_join['table_key']) . ' = ' . $dbo->quoteName($table_join_alias. '.' . $element_join['table_join_key']);
						} else {
							$left_joins .= ' LEFT JOIN ' . $dbo->quoteName($element_join['join_from_table']) . ' ON ' . $dbo->quoteName($element_join['join_from_table'] . '.' . $element_join['table_key']) . ' = ' . $dbo->quoteName($table_join_alias. '.' . $element_join['table_join_key']);
						}
					}
				}
			}
		}

		return $left_joins;
	}

	public function writeQueryWithOperator($element, $values, $operator, $type = 'select', $fabrik_element_data = null) {
		$query = '1=1';


		if (!empty($element) && (!empty($values) || $values == '0') && !empty($operator)) {
			$db = JFactory::getDbo();

			if ($type === 'date' || $type === 'time') {
				$from = $values[0];
				if (!empty($from)) {
					$to = !empty($values[1]) ? $values[1] : null;

					switch ($operator) {
						case '=':
							$query = $element . ' = ' . $db->quote($from);

							if ($type === 'date' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
								$query = $element . ' LIKE ' . $db->quote($from . '%');
							}
							break;
						case '!=':
							$query = $element . ' != ' . $db->quote($from);

							if ($type === 'date' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
								$query = $element . ' NOT LIKE ' . $db->quote($from . '%');
							}
							break;
						case 'superior':
							$query = $element . ' > ' . $db->quote($from);
							break;
						case 'superior_or_equal':
							$query = $element . ' >= ' . $db->quote($from);
							break;
						case 'inferior':
							$query = $element . ' < ' . $db->quote($from);
							break;
						case 'inferior_or_equal':
							$query = $element . ' <= ' . $db->quote($from);

							if ($type === 'date' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
								$query = '(' . $element . ' < ' .$db->quote($from) . ' OR ' . $element . ' LIKE ' . $db->quote($from . '%') . ')';
							}
							break;
						case 'between':
							if (!empty($to)) {
								$query = $element . ' BETWEEN ' . $db->quote($from) . ' AND ' . $db->quote($to);
							} else {
								$query = $element . ' >= ' . $db->quote($from);
							}
							break;
						case '!between':
							if (!empty($to)) {
								$query = $element . ' NOT BETWEEN ' . $db->quote($from) . ' AND ' . $db->quote($to);
							} else {
								$query = $element . ' < ' . $db->quote($from);
							}
							break;
						default:
							break;
					}
				}
			} else {
				switch($operator) {
					case '=':
						if (is_array($values)) {
							$_values = implode(',', $db->quote($values));
							$query = $element . ' IN (' . $_values . ')';
						} else {
							$query = $element . ' = ' . $db->quote($values);
						}
						break;
					case '!=':
						if (is_array($values)) {
							$_values = implode(',', $db->quote($values));
							$query = '(' . $element . ' NOT IN (' . $_values . ')' . ' OR ' . $element . ' IS NULL ) ';
						} else {
							$query = '(' . $element . ' != ' . $db->quote($values) . ' OR ' . $element . ' IS NULL ) ';
						}
						break;
					case 'LIKE':
						if (is_array($values)) {
							$_values = implode(',', $db->quote($values));
							$query = $element . ' IN (' . $_values . ')';
						} else {
							$query = $element . ' LIKE ' . $db->quote('%'.$values.'%');
						}
						break;
					case 'NOT LIKE':
						if (is_array($values)) {
							$_values = implode(',', $db->quote($values));
							$query = $element . ' NOT IN (' . $_values . ')';
						} else {
							$query = '(' . $element . ' NOT LIKE ' . $db->quote('%'.$values.'%') . ' OR ' . $element . ' IS NULL ) ';
						}
						break;
					case 'IN':
                        if ($fabrik_element_data['plugin'] === 'checkbox') { // value is stored as a serialized array

                            if (is_array($values)) {
                                foreach($values as $key => $value) {
                                    if ($key == 0) {
                                        $query = $element . ' LIKE ' . $db->quote('%"' . $value . '"%');
                                    } else {
                                        $query .= ' OR ' . $element . ' LIKE ' . $db->quote('%"' . $value . '"%');
                                    }
                                }
                            } else {
                                $query = $element . ' LIKE ' . $db->quote('%"' . $values . '"%');
                            }
                        } else {
                            if (is_array($values)) {
                                $values = implode(',', $db->quote($values));
                            } else {
                                $values = $db->quote($values);
                            }
                            $query = $element . ' IN (' . $values . ')';
                        }
						break;
					case 'NOT IN':
						$query = $this->notInQuery($element, $values, $fabrik_element_data);
						break;
				}
			}
		}

		return $query;
	}

	private function notInQuery($element, $values, $fabrik_element_data) {
		$query = '1=1';
		$simple_case = false;

		$db = JFactory::getDbo();

        if ($fabrik_element_data['plugin'] === 'checkbox') {
            if (is_array($values)) {
                $values = implode(',', $values);
            }
        } else {
            if (is_array($values)) {
                $values = implode(',', $db->quote($values));
            } else {
                $values = $db->quote($values);
            }
        }

		// if it is not given, we assume that we are not creating a filter from a fabrik element so it is a simple case
		if (empty($fabrik_element_data)) {
			$simple_case = true;
		} else {
			/**
			 * Si l'élément est dans un groupe répétable, ou dans un database join multi-select, ou checkbox
			 * La condition ne peut pas être écrite simplement
			 * Pour retrouver les dossiers qui ne contiennent pas la valeur, il faut faire une sous requête
			 * qui va chercher les dossiers pour laquelle la ou les valeurs ne sont jamais présentes dans aucune des lignes rattachées
			 */
			if ($fabrik_element_data['group_params']['repeat_group_button'] == 1 || ($fabrik_element_data['plugin'] === 'databasejoin' && in_array($fabrik_element_data['element_params']['database_join_display_type'], ['checkbox', 'multilist']))) {
				$searched_table = 'jos_emundus_campaign_candidature';
				$from_base_table = $fabrik_element_data['fabrik_table_join'];
				$subquery = '';

				if ($fabrik_element_data['group_params']['repeat_group_button'] == 1) {
					$repeat_join_infos = $this->getJoinInformations($fabrik_element_data['element_id']);
					$from_base_table = $repeat_join_infos['table_join'];
				}

				$joins = $this->findJoinsBetweenTablesRecursively($searched_table, $from_base_table);

				// we've found an array of joins between the two tables
				if (!empty($joins)) {
					$subquery = ' SELECT DISTINCT jos_emundus_campaign_candidature.id FROM jos_emundus_campaign_candidature ';

					$already_joined_tables = [];
					$subquery .= $this->writeJoins($joins, $already_joined_tables);

                    if ($fabrik_element_data['plugin'] === 'checkbox') {
                        $values = explode(',', $values);

                        foreach ($values as $key => $value) {
                            if ($key == 0) {
                                $subquery .= ' WHERE ' . $element .  ' LIKE ' . $db->quote('%"' . $value . '"%');
                            } else {
                                $subquery .= ' OR ' . $element .  ' LIKE ' . $db->quote('%"' . $value . '"%');
                            }
                        }

                    } else {
                        $subquery .= ' WHERE ' . $element . ' IN (' . $values . ')';
                    }
				}

				if (!empty($subquery)) {
					$query = ' jecc.id NOT IN (' . $subquery . ')';
				}
			} else {
				$simple_case = true;
			}
		}

		if ($simple_case) {
            if ($fabrik_element_data['plugin'] === 'checkbox') {
                $values = explode(',', $values);
                foreach ($values as $key => $value) {
                    if ($key == 0) {
                        $query = '(' . $element .  ' NOT LIKE ' . $db->quote("%\"$value\"%") . ' ';

                    } else {
                        $query .= ' AND ' . $element . ' NOT LIKE ' . $db->quote("%\"$value\"%") . ' ';
                    }
                }

                $query .= ' OR ' . $element . ' IS NULL) ';
            } else {
                $query = '(' . $element .  ' NOT IN (' . $values . ')' . ' OR ' . $element . ' IS NULL) ';
            }
        }

		return $query;
	}

    /*
     *
     */
    public function setFiltersValuesAvailability($applied_filters): array
    {
        $applied_filters = empty($applied_filters) ? [] : $applied_filters;

        if (!empty($applied_filters)) {
			$user = JFactory::getUser();

			require_once (JPATH_ROOT . '/components/com_emundus/models/users.php');
			$m_users = new EmundusModelUsers;
	        $user_programmes = array_filter($m_users->getUserGroupsProgrammeAssoc($user->id));
	        $groups = $m_users->getUserGroups($user->id, 'Column');
	        $fnum_assoc_to_groups = $m_users->getApplicationsAssocToGroups($groups);
	        $fnum_assoc_to_user = $m_users->getApplicantsAssoc($user->id);
	        $user_fnums_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc_to_user);

            foreach($applied_filters as $applied_filter_key => $applied_filter) {
                if (!empty($applied_filter) && $applied_filter['type'] === 'select') {
                    $leftJoins = '';
                    $already_joined = [
                        'jecc' => 'jos_emundus_campaign_candidature',
                        'ss' => 'jos_emundus_setup_status',
                        'esc' => 'jos_emundus_setup_campaigns',
                        'sp' => 'jos_emundus_setup_programmes',
                        'u' => 'jos_users',
                        'eu' => 'jos_emundus_users',
                        'eta' => 'jos_emundus_tag_assoc'
                    ];

                    $table_column_to_count = null;
                    if (!is_numeric($applied_filter['id'])) {
                        switch ($applied_filter['uid']) {
                            case 'status':
                                $table_column_to_count = 'jecc.status';
                                break;
                            case 'campaigns':
                                $table_column_to_count = 'jecc.campaign_id';
                                break;
                            case 'programmes':
                            case 'programs':
                                $table_column_to_count = 'sp.id';
                                break;
                            case 'tags':
                                $table_column_to_count = 'eta.id_tag';
                                break;
                            case 'years':
                                $table_column_to_count = 'esc.year';
                                break;
                            case 'published':
                                $table_column_to_count = 'jecc.published';
                                break;
                        }
                    } else {
                        $fabrik_element_data = $this->getFabrikElementData($applied_filter['id']);

                        if (!empty($fabrik_element_data)) {
                            if ($fabrik_element_data['group_params']['repeat_group_button'] == 1) {
                                $join_informations = $this->getJoinInformations($fabrik_element_data['element_id']);

                                if (!empty($join_informations)) {
                                    $fabrik_element_data['db_table_name'] = $join_informations['table_join'];
                                }
                            }

                            $table_alias = $fabrik_element_data['db_table_name'];
	                        if (!in_array($fabrik_element_data['db_table_name'], $already_joined)) {
		                        $table_column_to_count = $table_alias . '.' . $fabrik_element_data['name'];
		                        $joins = $this->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', $fabrik_element_data['db_table_name']);

		                        if (!empty($joins)) {
			                        $leftJoins = $this->writeJoins($joins, $already_joined);
		                        } else {
			                        if (!empty($fabrik_element_data['group_params']) && $fabrik_element_data['group_params']['repeat_group_button'] == 1) {
				                        $group_join_informations = $this->getJoinInformations(0, $fabrik_element_data['group_id']);
				                        $joins = $this->findJoinsBetweenTablesRecursively('jos_emundus_campaign_candidature', $group_join_informations['table_join']);

				                        if (!empty($joins)) {
					                        $leftJoins .= $this->writeJoins($joins, $already_joined);

					                        // get joins last entry table_join
					                        $table_to_join = end($joins)['table_join'];
					                        $joins = $this->findJoinsBetweenTablesRecursively($table_to_join, $fabrik_element_data['db_table_name']);

					                        if (!empty($joins)) {
						                        $leftJoins .= $this->writeJoins($joins, $already_joined);

						                        foreach($joins as $join) {
							                        if ($join['table_join'] === $fabrik_element_data['db_table_name']) {
								                        $table_column_to_count = $join['table_join'] . '.' . $join['table_join_key'];
							                        }
						                        }
					                        } else {
						                        if (!empty($join_informations['params'])) {
							                        $join_informations['params'] = json_decode($join_informations['params'], true);

							                        if ($join_informations['params']['type'] === 'element') {
								                        $joins = [
									                        [
										                        'table_key' => 'id',
										                        'join_from_table' => $fabrik_element_data['db_table_name'],
										                        'table_join' => $table_to_join,
										                        'table_join_key' => $join_informations['params']['join-label']
									                        ]
								                        ];
								                        $leftJoins .= $this->writeJoins($joins, $already_joined);

								                        $table_column_to_count = $table_to_join . '.' .  $join_informations['params']['join-label'];
							                        }
						                        }
					                        }
				                        }
			                        }
		                        }
	                        } else {
                                $key = array_search($fabrik_element_data['db_table_name'], $already_joined);

                                if (!is_numeric($key)) {
                                    $table_alias = $key;
                                }
                            }

                            $table_column_to_count = $table_alias . '.' . $fabrik_element_data['name'];
                        }
                    }

                    if (!empty($table_column_to_count)) {
                        $db = JFactory::getDbo();

                        if ($applied_filter['plugin'] === 'checkbox') { // checkbox is a specific case because data is registered as following example '["x", "y",... "n"]', so we can not count like other columns
                            $query = 'SELECT ' . $table_column_to_count . '
                            FROM #__emundus_campaign_candidature as jecc
                            LEFT JOIN #__emundus_setup_status as ss on ss.step = jecc.status
                            LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = jecc.campaign_id
                            LEFT JOIN #__emundus_setup_programmes as sp on sp.code = esc.training
                            LEFT JOIN #__users as u on u.id = jecc.applicant_id
                            LEFT JOIN #__emundus_users as eu on eu.user_id = jecc.applicant_id
                            LEFT JOIN #__emundus_tag_assoc as eta on eta.fnum=jecc.fnum ';

                            if (!empty($leftJoins)) {
                                $query .= $leftJoins;
                            }

                            $whereConditions = $this->_moduleBuildWhere($already_joined, 'files', ['code' => $user_programmes, 'fnum_assoc' => $user_fnums_assoc], [$applied_filter['uid']]);

                            $query .= $whereConditions['join'];
                            $query .= ' WHERE u.block=0 ' . $whereConditions['q'];
                            $query .= ' GROUP BY ' . $table_column_to_count . ' ORDER BY ' . $table_column_to_count . ' ASC';

                            try {
                                $db->setQuery($query);
                                $all_values = $db->loadColumn();
                            } catch (Exception $e) {
                                JLog::add('Failed to get available values for filter ' . $applied_filter['uid'] . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
                            }


                            $available_values = [];
                            foreach($all_values as $value) {
                                $row_values = json_decode($value);

                                if (!empty($row_values)) {
                                    foreach($row_values as $row_value) {
                                        if (!isset($available_values[$row_value])) {
                                            $available_values[$row_value] = 1;
                                        } else {
                                            $available_values[$row_value]++;
                                        }
                                    }
                                }
                            }
                        }
                        else {
                            $query = 'SELECT ' . $table_column_to_count . ' as count_value, COUNT(DISTINCT jecc.fnum) as count
                            FROM #__emundus_campaign_candidature as jecc
                            LEFT JOIN #__emundus_setup_status as ss on ss.step = jecc.status
                            LEFT JOIN #__emundus_setup_campaigns as esc on esc.id = jecc.campaign_id
                            LEFT JOIN #__emundus_setup_programmes as sp on sp.code = esc.training
                            LEFT JOIN #__users as u on u.id = jecc.applicant_id
                            LEFT JOIN #__emundus_users as eu on eu.user_id = jecc.applicant_id
                            LEFT JOIN #__emundus_tag_assoc as eta on eta.fnum=jecc.fnum ';

                            if (!empty($leftJoins)) {
                                $query .= $leftJoins;
                            }

                            $whereConditions = $this->_moduleBuildWhere($already_joined, 'files', ['code' => $user_programmes, 'fnum_assoc' => $user_fnums_assoc], [$applied_filter['uid']]);

                            $query .= $whereConditions['join'];
                            $query .= ' WHERE u.block=0 ' . $whereConditions['q'];
                            $query .= ' GROUP BY ' . $table_column_to_count . ' ORDER BY ' . $table_column_to_count . ' ASC';

                            try {
                                $db->setQuery($query);
                                $available_values = $db->loadAssocList('count_value');
                            } catch (Exception $e) {
                                JLog::add('Failed to get available values for filter ' . $applied_filter['uid'] . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.filters.error');
	                            $available_values = [];
                            }
                        }

	                    if (!empty($available_values)) {
		                    if (empty($applied_filter['values'])) {
			                    if (!class_exists('EmundusFiltersFiles')) {
				                    require_once(JPATH_ROOT . '/components/com_emundus/classes/filters/EmundusFiltersFiles.php');
			                    }

			                    if (!isset($filters_files)) {
									try {
										$filters_files = new EmundusFiltersFiles([], true);
									} catch(Exception $e) {
										// exception means that the user is not logged in or has not enough access, should have never happened
										JLog::add('Failed to get available values for filter ' . $applied_filter['uid'] . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
										return [];
									}
			                    }

			                    $applied_filter['values'] = $filters_files->getFabrikElementValuesFromElementId($applied_filter['id']);
		                    }

		                    if (!empty($applied_filter['values'])) {
			                    foreach($applied_filter['values'] as $key => $value) {
				                    if (isset($available_values[$value['value']])) {
					                    $applied_filters[$applied_filter_key]['values'][$key]['count'] = $available_values[$value['value']]['count'];
				                    } else {
					                    $applied_filters[$applied_filter_key]['values'][$key]['count'] = 0;
				                    }
			                    }
		                    }
	                    }
                    }
                }
            }
        }

        return $applied_filters;
    }

    /**
     * @param       $str_array
     * @param array $tableAlias
     *
     * @return array
     */
    private function _buildSearch($str_array, $tableAlias = array(), $caller_aprams = array()) {

        $q = array('q' => array(), 'join' => array());
        $queryGroups = [
            'all' => '',
            'fnum' => '',
            'id' => '',
            'email' => '',
            'username' => '',
            'lastname' => '',
            'firstname' => ''
        ];
        $first = true;

        foreach ($str_array as $str) {

            $val = explode(': ', $str);

            if ($val[0] == "ALL") {

                if (is_numeric($val[1])) {

                    //possibly fnum ou uid
                    if (!empty($queryGroups['all'])) {
                        $queryGroups['all'] .= ' or (u.id = ' . $val[1] . ' or jecc.fnum like "'.$val[1].'%") ';
                    } else {
                        if ($first) {
                            $queryGroups['all'] .= ' and (((u.id = ' . $val[1] . ' or jecc.fnum like "'.$val[1].'%") ';
                            $first = false;
                        } else {
                            $queryGroups['all'] .= ' and ((u.id = ' . $val[1] . ' or jecc.fnum like "'.$val[1].'%") ';
                        }

                    }

                    if (!in_array('jos_users', $tableAlias)) {
                        $q['join'][] .= ' left join #__users as u on u.id = jecc.applicant_id ';
                    }

                    $q['users'] = true;

                } else {

                    if (filter_var($val[1], FILTER_VALIDATE_EMAIL) !== false) {
                        //the request is an email
                        if (!empty($queryGroups['all'])) {
                            $queryGroups['all'] .= ' or (u.email = "'.$val[1].'") ';
                        } else {
                            if ($first) {
                                $queryGroups['all'] .= ' and (((u.email = "'.$val[1].'") ';
                                $first = false;
                            } else {
                                $queryGroups['all'] .= ' and ((u.email = "'.$val[1].'") ';
                            }

                        }

                        if (!in_array('jos_users', $tableAlias)) {
                            $q['join'][] .= ' left join #__users as u on u.id = jecc.applicant_id ';
                        }

                        $q['users'] = true;

                    } else {

                        if (!empty($queryGroups['all'])) {
                            $queryGroups['all'] .= ' or (eu.lastname LIKE "%' . ($val[1]) . '%" OR eu.firstname LIKE "%' . ($val[1]) . '%" OR u.email LIKE "%' . ($val[1]) . '%" OR u.username LIKE "%' . ($val[1]) . '%" ) ';
                        } else {
                            if ($first) {
                                $queryGroups['all'] .= ' and (((eu.lastname LIKE "%' . ($val[1]) . '%" OR eu.firstname LIKE "%' . ($val[1]) . '%" OR u.email LIKE "%' . ($val[1]) . '%" OR u.username LIKE "%' . ($val[1]) . '%" ) ';
                                $first = false;
                            } else {
                                $queryGroups['all'] .= ' and ((eu.lastname LIKE "%' . ($val[1]) . '%" OR eu.firstname LIKE "%' . ($val[1]) . '%" OR u.email LIKE "%' . ($val[1]) . '%" OR u.username LIKE "%' . ($val[1]) . '%" ) ';
                            }

                        }

                        if (!in_array('jos_users', $tableAlias)) {
                            $q['join'][] .= ' left join #__users as u on u.id = jecc.applicant_id';
                            $q['users'] = true;
                        }

                        if (!in_array('jos_emundus_users', $tableAlias)){
                            $q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = jecc.applicant_id ';
                            $q['em_user'] = true;
                        }
                    }
                }
            }


            if ($val[0] == "FNUM" && is_numeric($val[1])) {
                //possibly fnum ou uid
                if (!empty($queryGroups['fnum'])) {
                    $queryGroups['fnum'] .= ' or (jecc.fnum like "'.$val[1].'%") ';
                } else {
                    if ($first) {
                        $queryGroups['fnum'] .= ' and (((jecc.fnum like "'.$val[1].'%") ';
                        $first = false;
                    } else {
                        $queryGroups['fnum'] .= ' and ((jecc.fnum like "'.$val[1].'%") ';
                    }
                }

                if (!in_array('jos_users', $tableAlias)) {
                    $q['join'][] = ' left join #__users as u on u.id = jecc.applicant_id ';
                }
                $q['users'] = true;
            }


            if ($val[0] == "ID" && is_numeric($val[1])) {
                //possibly fnum ou uid
                if (!empty($queryGroups['id'])) {
                    $queryGroups['id'] .= ' or (u.id = ' . $val[1] . ') ';
                } else {
                    if ($first) {
                        $queryGroups['id'] .= ' and (((u.id = ' . $val[1] . ') ';
                        $first = false;
                    } else {
                        $queryGroups['id'] .= ' and ((u.id = ' . $val[1] . ') ';
                    }
                }

                if (!in_array('jos_users', $tableAlias)) {
                    $q['join'][] = ' left join #__users as u on u.id = jecc.applicant_id ';
                }
                $q['users'] = true;
            }


            if ($val[0] == "EMAIL") {
                //the request is an email
                if (!empty($queryGroups['email'])) {
                    $queryGroups['email'] .= ' or (u.email like "%'.$val[1].'%") ';
                } else {
                    if ($first) {
                        $queryGroups['email'] .= ' and (((u.email like "%'.$val[1].'%") ';
                        $first = false;
                    } else {
                        $queryGroups['email'] .= ' and ((u.email like "%'.$val[1].'%") ';
                    }
                }

                if (!in_array('jos_users', $tableAlias)) {
                    $q['join'][] = ' left join #__users as u on u.id = jecc.applicant_id ';
                }

                $q['users'] = true;
            }


            if ($val[0] == "USERNAME") {
                //the request is an username
                if (!empty($queryGroups['username'])) {
                    $queryGroups['username'] .= ' or (u.username LIKE "%' . ($val[1]) . '%" ) ';
                } else {
                    if ($first) {
                        $queryGroups['username'] .= ' and (((u.username LIKE "%' . ($val[1]) . '%" ) ';
                        $first = false;
                    } else {
                        $queryGroups['username'] .= ' and ((u.username LIKE "%' . ($val[1]) . '%" ) ';
                    }
                }

                if (!in_array('jos_users', $tableAlias)) {
                    $q['join'][] = ' left join #__users as u on u.id = jecc.applicant_id ';
                }
                $q['users'] = true;
            }

            if ($val[0] == "LAST_NAME") {
                //the request is a lastname
                if (!empty($queryGroups['lastname'])) {
                    $queryGroups['lastname'] .= ' or (eu.lastname LIKE "%' . ($val[1]) . '%" ) ';
                } else {
                    if ($first) {
                        $queryGroups['lastname'] .= ' and (((eu.lastname LIKE "%' . ($val[1]) . '%" ) ';
                        $first = false;
                    } else {
                        $queryGroups['lastname'] .= ' and ((eu.lastname LIKE "%' . ($val[1]) . '%" ) ';
                    }
                }

                if (!in_array('jos_emundus_users', $tableAlias)){
                    $q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = jecc.applicant_id ';
                    $q['em_user'] = true;
                }
            }

            if ($val[0] == "FIRST_NAME") {
                //the request is a firstname
                if (!empty($queryGroups['firstname'])) {
                    $queryGroups['firstname'] .= ' or (eu.firstname LIKE "%' . ($val[1]) . '%" ) ';
                } else {
                    if ($first) {
                        $queryGroups['firstname'] .= ' and (((eu.firstname LIKE "%' . ($val[1]) . '%" ) ';
                        $first = false;
                    } else {
                        $queryGroups['firstname'] .= ' and ((eu.firstname LIKE "%' . ($val[1]) . '%" ) ';
                    }
                }

                if (!in_array('jos_emundus_users', $tableAlias)) {
                    $q['join'][] .= ' left join #__emundus_users as eu on eu.user_id = jecc.applicant_id ';
                    $q['em_user'] = true;
                }
            }
        }

        // Close all group parentheses.
        foreach ($queryGroups as $k => $v) {
            if (!empty($v)) {
                $queryGroups[$k] .= ')';
            }
        }
        $query = $queryGroups['all'].$queryGroups['fnum'].$queryGroups['id'].$queryGroups['email'].$queryGroups['username'].$queryGroups['lastname'].$queryGroups['firstname'];

        if (!empty($query)) {
            $query .= ')';
        }

        $q['q'][] = $query;
        return $q;
    }

    public function getEncryptedTables()
    {
        $table_names = [];

        $db = JFactory::getDBO();
        $query= $db->getQuery(true);

        $query->select($db->quoteName('fl.db_table_name'))
            ->from($db->quoteName('#__fabrik_lists', 'fl'))
            ->leftJoin($db->quoteName('#__fabrik_forms', 'fm') . ' ON fl.form_id = fm.id')
            ->where('JSON_EXTRACT(fm.params, "$.note") = "encrypted"');

        try {
            $db->setQuery($query);
            $table_names = $db->loadColumn();
        } catch (Exception $e) {
            JLog::add('Failed to get encrypted table names ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
        }

        return $table_names;
    }

	public function getApplicantFnums(int $aid, $submitted = null, $start_date = null, $end_date = null) {
		$fnums = [];

		if (!empty($aid)) {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select('ecc.*, esc.label, esc.start_date, esc.end_date, esc.admission_start_date, esc.admission_end_date, esc.training, esc.year, esc.profile_id')
				->from($db->quoteName('#__emundus_campaign_candidature', 'ecc'))
				->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON esc.id = ecc.campaign_id')
				->where('ecc.published = 1')
				->where('ecc.applicant_id = ' . $aid);

			if ($submitted !== null) {
				$query->where('ecc.submitted = ' . $db->quote($submitted));
			}

			if ($start_date !== null) {
				$query->where('esc.start_date <= ' . $db->quote($start_date));
			}

			if ($end_date !== null) {
				$query->where('esc.end_date >= ' . $db->quote($end_date));
			}

			try {
				$db->setQuery($query);
				$fnums = $db->loadObjectList('fnum');
			} catch(Exception $e) {
				JLog::add(JUri::getInstance().' :: fct : getAttachmentsById :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus.error');
			}
		}

		return $fnums;
	}

	public function isTableLinkedToCampaignCandidature($table_name) {
		$linked = false;

		if (!empty($table_name)) {
			// check if table has a column named 'fnum'
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select('column_name')
				->from($db->quoteName('information_schema.columns'))
				->where('table_name = ' . $db->quote($table_name))
				->where('column_name = ' . $db->quote('fnum'));

			try {
				$db->setQuery($query);
				$column_name = $db->loadResult();

				if (!empty($column_name)) {
					$linked = true;
				}
			} catch(Exception $e) {
				JLog::add(JUri::getInstance().' :: fct : isTableLinkedToCampaignCandidature :: USER ID : '.JFactory::getUser()->id.' -> '.$query, JLog::ERROR, 'com_emundus.error');
			}
		}

		return $linked;
	}
}

