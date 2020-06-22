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
    public function clear()
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
    public  function clearfilter()
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
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

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

            if (isset($filts_values[$key]) && !is_null($filts_values[$key]) && empty($params[$filt_name])) {
                if (in_array($filt_name, $filter_multi_list)) {
                    $params[$filt_name] = array();
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
        /*
        // on force avec la valeur du filtre défini dans les options de menu
        if (count($filts_details['status'])>0 && isset($filts_details['status'][0]) && !empty($filts_details['status'][0])) {
            $fd_with_param = $params['status'] + $filts_details['status'];
            $params['status'] = $filts_details['status'];
            $filts_details['status'] = $fd_with_param;
        }
        */
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
            if ((is_array($filts_details['programme']) && count($filts_details['programme']) > 0) || $filts_details['programme'] !== NULL) {
	            $programme = !empty($m_files->code) ? $m_files->code : '';
            }
           
            //////////////////////////////////////////
            if ((is_array($filts_details['programme']) && count(@$params['programme']) == 0) || @$params['programme'][0] == '%') {
                $params['programme'] = $programme;
                $filts_details['programme'] = $programme;
            } elseif ((is_array($filts_details['programme']) && count($filts_details['programme']) == 0) || empty($filts_details['programme'])) {
                $filts_details['programme'] = $programme;
            }
            if ((is_array($codes) && count($codes)) > 0 && isset($code)) {
                $params['programme'] = array_merge($params['programme'], $codes);
                $filts_details['programme'] = array_merge($filts_details['programme'], $codes);
            }
        }


        // Used for adding default columns when no programme is loaded.
        if (empty($params['programme'])) {
	        $params['programme'] = ["%"];
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
    public function resetFilter() {
        $h_files = new EmundusHelperFiles;
        $filters = $h_files->setMenuFilter();
        return $h_files->createFilterBlock($filters['filts_details'], $filters['filts_options'], $filters['tables']);
    }


    /*
    * @param            query results
    * @param    array   values to extract and insert
    */
    public function insertValuesInQueryResult($results, $options) {
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

    public function getCurrentCampaign() {
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

    public function getCurrentCampaignsID() {
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
                require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
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

    public function getStatus() {
        $db = JFactory::getDBO();
        $query = 'SELECT *  FROM #__emundus_setup_status ORDER BY ordering';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public function getCampaign() {
        $db = JFactory::getDBO();
        $query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
        $db->setQuery( $query );
        $syear = $db->loadRow();

        return $syear[0];
    }

    public function getCampaignByID($id) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_campaigns WHERE id='.$id;
        $db->setQuery( $query );

        return $db->loadAssoc();
    }

    public function getApplicants() {
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label
        FROM #__emundus_setup_profiles esp
        WHERE esp.status=1';
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
        $query = 'SELECT DISTINCT(year) as schoolyear
            FROM #__emundus_setup_campaigns
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
        $db = JFactory::getDBO();
        $query = 'SELECT *
                FROM #__emundus_setup_attachments WHERE id IN (SELECT attachment_id FROM #__emundus_setup_attachment_profiles WHERE profile_id = '.$pid.')
                ORDER BY ordering ASC';
        $db->setQuery( $query );
        return $db->loadObjectList();
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
	 *
	 * @return array
	 */
    public static function getElements($code = array(), $camps = array(), $fabrik_elements = array()) {
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
        require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

        $h_menu = new EmundusHelperMenu;
        $m_user = new EmundusModelUsers;
        $m_profile = new EmundusModelProfile;
        $m_campaign = new EmundusModelCampaign;

        $db = JFactory::getDBO();

        if (empty($code)) {
            $params = JFactory::getSession()->get('filt_params');
            $programme = $params['programme'];
            $campaigns = @$params['campaign'];

            if (empty($programme) && empty($campaigns)) {
	            $programme = $m_campaign->getLatestCampaign();
            }
            
            // get profiles for selected programmes or campaigns
            $plist = $m_profile->getProfileIDByCourse((array)$programme);
            $plist = count($plist) == 0 ? $m_profile->getProfileIDByCampaign($campaigns) : $plist;
            
        } else {
            $plist = $m_profile->getProfileIDByCourse($code, $camps);
        }
		
        if ($plist) {
            // get Fabrik list ID for profile_id
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

            if (count($fl) == 0) {
	            return array();
            }

            $query = 'SELECT distinct(concat_ws("_",tab.db_table_name,element.name)) as fabrik_element, element.id, element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin, element.id, groupe.id AS group_id, groupe.label AS group_label, element.params AS element_attribs,
                    INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, tab.label AS table_label, tab.created_by_alias, joins.table_join, menu.title,
                    p.label, p.id as profil_id
                    FROM #__fabrik_elements element';
            $join = 'INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                    INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                    INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
                    INNER JOIN #__fabrik_forms AS form ON tab.form_id = form.id
                    LEFT JOIN #__fabrik_joins AS joins ON (tab.id = joins.list_id AND (groupe.id=joins.group_id OR element.id=joins.element_id))
                    INNER JOIN #__menu AS menu ON form.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
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
                        AND menu.menutype IN ( "' . implode('","', $menutype) . '" )';
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
                    return '<img class="img-responsive" src="'.$folder . '/tn_'. $photo['filename'] . '" width="60" /></img>';
                }

            } else {

                $photos = $m_files->getPhotos();
                foreach ($photos as $photo) {
                    $folder = JURI::base().EMUNDUS_PATH_REL.$photo['user_id'];
                    $pictures[$photo['fnum']] = '<img class="img-responsive" src="'.$folder . '/tn_'. $photo['filename'] . '" width="60" /></img>';
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

    public function getElementsName($elements_id) {
        if (!empty($elements_id) && !empty(ltrim($elements_id))) {

            $db = JFactory::getDBO();
            $query = 'SELECT element.id, element.name AS element_name, element.label as element_label, element.params AS element_attribs, element.plugin as element_plugin, element.hidden as element_hidden, forme.id as form_id, forme.label as form_label, groupe.id as group_id, groupe.label as group_label, groupe.params as group_attribs,tab.db_table_name AS tab_name, tab.created_by_alias AS created_by_alias, joins.table_join
                    FROM #__fabrik_elements element
                    INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                    INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                    INNER JOIN #__fabrik_forms AS forme ON formgroup.form_id = forme.id
                    INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
                    LEFT JOIN #__fabrik_joins AS joins ON (tab.id = joins.list_id AND (groupe.id=joins.group_id OR element.id=joins.element_id))
                    WHERE element.id IN ('.ltrim($elements_id, ',').')
                    ORDER BY formgroup.ordering, element.ordering ';
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
            return $elementsIdTab;
        } else {
        	return array();
        }
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

        if (!empty($params->join_key_column)) {
            $db = JFactory::getDBO();

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
                $params->database_join_where_sql = (strpos($params->database_join_where_sql, '{rowid}') === false) ? $params->database_join_where_sql : '';
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$join_val.' AS elt_val FROM '.$params->join_db_name.' '.str_replace('{thistable}', $params->join_db_name, preg_replace('{shortlang}', substr(JFactory::getLanguage()->getTag(), 0 , 2), $params->database_join_where_sql));
            }
            $db->setQuery($query);
            $result = $db->loadObjectList();

        } else {
            $i = 0;
            foreach ($params->sub_options->sub_values as $value) {
                $result[] = (object) array('elt_key'=>$value, 'elt_val'=>$params->sub_options->sub_labels[$i]);
                $i++;
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
    public function setWhere($search, $search_values, &$query) {
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
            if ($selected->element_plugin == "databasejoin") {
                $query_params = json_decode($selected->element_attribs);
                $option_list =  $h_files->buildOptions($selected->element_name, $query_params);
                $current_filter .= '<br/><select class="chzn-select em-filt-select" id="em-adv-fil-'.$i.'"  name="'.$elements_values.'" id="'.$elements_values.'">
                <option value="">'.JText::_('PLEASE_SELECT').'</option>';
                if (!empty($option_list)) {
                    foreach ($option_list as $value) {
                        $current_filter .= '<option value="'.$value->elt_key.'"';

                        if ($value->elt_key == $search_value) {
	                        $current_filter .= ' selected';
                        }

                        $current_filter .= '>'.$value->elt_val.'</option>';
                    }
                }
                $current_filter .= '</select>';
            }

            elseif ($selected->element_plugin == "checkbox" || $selected->element_plugin == "radiobutton" || $selected->element_plugin == "dropdown") {
                $query_params = json_decode($selected->element_attribs);
                $option_list =  $h_files->buildOptions($selected->element_name, $query_params);
                $current_filter .= '<br/><select class="chzn-select em-filt-select" id="em-adv-fil-'.$i.'" name="'.$elements_values.'" id="'.$elements_values.'">
                <option value="">'.JText::_('PLEASE_SELECT').'</option>';
                if (!empty($option_list)) {
                    foreach ($option_list as $value) {
                        $current_filter .= '<option value="'.$value->elt_key.'"';

                        if ($value->elt_key == $search_value) {
	                        $current_filter .= ' selected';
                        }

                        $current_filter .= '>'.$value->elt_val.'</option>';
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

        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
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
        $quick = '<div id="filters">
                    <div id="quick" class="form-group">
                        <input type="text" id="input-tags" class="input-tags demo-default" value="'.$cs.'" placeholder="'.JText::_('SEARCH').' ...">'.
                    '</div>
                </div>';
       
        $filters .= $quick;
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
                    </script>
                    <button type="button" class="btn btn-xs" id="showhide" style="width:100%"><i class="icon-chevron-up"></i> ' . JText::_('HIDE_FILTERS') . '</button><br>
					<script>
                        $("#showhide").click(function() {
                            if ($("#showhide i").hasClass("icon-chevron-up")) {
	                            $(".em_filters_filedset").toggle(400);
	                            $("#showhide").html('."'".'<i class="icon-chevron-down"></i> ' . JText::_('MORE_FILTERS')."'".');
                            } else {
	                            $(".em_filters_filedset").toggle(400);
	                            $("#showhide").html('."'".'<i class="icon-chevron-up"></i> ' . JText::_('HIDE_FILTERS')."'".');
                            }
                        });   
                    </script>';

        $filters .= '<fieldset class="em_filters_filedset">';

        if (@$params['profile'] !== NULL) {
            $profile = '';
            $hidden = $types['profile'] != 'hidden' ? false : true;

            if (!$hidden) {
                $profile .= '<div class="form-group em-filter" id="profile">
                	            <div class="em_label">
                	            	<label class="control-label em-filter-label">'.JText::_('PROFILE').'</label>
                                </div>';
            }

            $profile .= ' <select class="search_test em-filt-select" id="select_profile" name="profile" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'">
                         	<option value="0">'.JText::_('ALL').'</option>';

            $profiles = $h_files->getApplicants();
            foreach ($profiles as $prof) {
                $profile .= '<option value="'.$prof->id.'"';
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
                                    	<label class="control-label em-filter-label">'.JText::_('OTHER_PROFILES').'&ensp; <a href="javascript:clearchosen(\'#select_oprofiles\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a></label> 
                                    </div>';
            }
                                    
            $profile .= ' <select class="testSelAll em-filt-select" id="select_oprofiles" multiple="multiple" name="o_profiles" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

            foreach ($profiles as $prof) {
                $profile .= '<option value="'.$prof->id.'"';
                $profile .= '>'.$prof->label.'</option>';
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
                                    	<label class="control-label em-filter-label">'.JText::_('PROFILE_FILTER').'</label>
                                    </div>';
            }

            $profile_user .= ' <select class="search_test em-filt-select" id="select_profile_users" name="profile_users" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                         		<option value="0">'.JText::_('ALL').'</option>';

            $profile_users = $h_files->getProfiles();
            $prefilter = count($filt_params['profile_users']) > 0;

            foreach ($profile_users as $profu) {
                if (!$prefilter || ($prefilter && in_array($profu->id, $params['profile_users']))) {
                    $profile_user .= '<option value="' . $profu->id . '"';
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
                               		<label class="control-label em-filter-label">' . JText::_('ASSESSOR_USER_FILTER') . '</label>
                                </div>
                               <div class="em_filtersElement">';
            }

            $eval .= '<select class="search_test em-filt-select" id="select_user" name="user" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                      	<option value="0">'.JText::_('ALL').'</option>';

            $evaluators = $h_files->getEvaluators();
            foreach ($evaluators as $evaluator) {
                $eval .= '<option value="'.$evaluator->id.'"';
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
                                   		<label class="control-label em-filter-label">' . JText::_('GROUP_FILTER') . '</label>
                                    </div>
                                    <div class="em_filtersElement">';
            }

            $group_eval .= '<select class="search_test em-filt-select" id="select_groups" name="evaluator_group" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '"" ').'>
                            <option value="0">'.JText::_('ALL').'</option>';

            $groups = $h_files->getGroups();
            foreach ($groups as $group) {
                $group_eval .= '<option value="'.$group->id.'"';
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
                                    <div class="em_label"><label class="control-label">' . JText::_('FINAL_GRADE_FILTER') . '</label></div>
                                    <div class="em_filtersElement">';
            }

            $final_grade .= '<select class="search_test em-filt-select" id="select_finalgrade" name="finalgrade" '.($types['finalgrade'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                             <option value="0">'.JText::_('PLEASE_SELECT').'</option>';
            $groupe = "";
            for ($i = 0; $i < count($final_gradeList); $i++) {
                $val = substr($p_grade[$i], 1, 1);
                $final_grade .= '<option value="'.$val.'"';
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
                                        <label>'.JText::_('MISSING_DOC').'</label>
                                    </div>
                                    <div class="em_filtersElement">';
            }

            $missing_doc .= '<select class="search_test em-filt-select" id="select_missing_doc" name="missing_doc" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                                <option value="0">'.JText::_('ALL').'</option>';

	        $missing_docList = $h_files->getMissing_doc();
            foreach ($missing_docList as $md) {
                $missing_doc .= '<option value="'.$md->attachment_id.'"';
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
                                	<label class="control-label em-filter-label">'.JText::_('COMPLETE_APPLICATION').'</label>
                                </div>
                                <div class="em_filtersElement">';
            }

            $complete .= '<select class="search_test em-filt-select" id="select_complete" name="complete" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                            <option value="0">'.JText::_('ALL').'</option>';

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
                                <div class="em_label"><label class="control-label em-filter-label">'.JText::_('VALIDATED_APPLICATION').'</label></div>
                                <div class="em_filtersElement">';
            }

            $validate .= '<select class="search_test em-filt-select" id="select_validate" name="validate" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
                            <option value="0">'.JText::_('ALL').'</option>';

            $validate .= '<option value="1"';
            if ($validate_application == 1) {
	            $validate .= ' selected="true"';
            }
            $validate .= '>'.JText::_('VALIDATED').'</option>';

            $validate .= '<option value="2"';
            if ($validate_application == 2) {
	            $validate .= ' selected="true"';
            }
            $validate .= '>'.JText::_('UNVALIDATED').'</option>';

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
                            		<label class="control-label em-filter-label">'.JText::_('CAMPAIGN').'&ensp; <a href="javascript:clearchosen(\'#select_multiple_campaigns\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a></label>
                            	</div>
                          		<div class="em_filtersElement">';
            }
            $campaign .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_campaigns" name="campaign" multiple=multiple"" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $campaignList = $h_files->getCampaigns();
            foreach ($campaignList as $c) {
                $campaign .= '<option value="'.$c->id.'"';
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
                                    	<label class="control-label em-filter-label">'.JText::_('SCHOOLYEARS').' &ensp;<a href="javascript:clearchosen(\'#select_multiple_schoolyears\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a></label>
                                    </div>
                                   <div class="em_filtersElement">';
            }

            $schoolyear .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_schoolyears" name="schoolyear" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $schoolyearList = $h_files->getSchoolyears();
            foreach ($schoolyearList as $key => $value) {
                $schoolyear .= '<option value="'.$value->schoolyear.'"';
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
                    	<label class="control-label em-filter-label">'.JText::_('PROGRAMME').'&ensp;<a href="javascript:clearchosen(\'#select_multiple_programmes\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a></label>
                    </div>
                    <div class="em_filtersElement">';
            }
            $programme .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_programmes" name="programme" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $programmeList = $h_files->getProgrammes($params['programme']);
            foreach ($programmeList as $p) {
                $programme .= '<option value="'.$p->code.'"';
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
                    	<label class="control-label em-filter-label">'.JText::_('STATUS').'&ensp; <a href="javascript:clearchosen(\'#select_multiple_status\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a></label>
                    </div>
                    <div class="em_filtersElement">';
            }

            $status .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select" ' : '').' id="select_multiple_status" name="status" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

            foreach ($statusList as $p) {
                $status .= '<option value="'.$p->step.'"';
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
                					<label class="control-label em-filter-label">'.JText::_('PUBLISH').'</label>
                				</div>
                				<div class="em_filtersElement">
                					<select class="search_test em-filt-select" id="select_published" name="published" '.($hidden ? 'style="visibility:hidden" ' : '').'>
                                        <option value="1"';

                if ($current_published == '1') {
	                $published .= "selected='true'";
                }

                $published .= '>'.JText::_("PUBLISHED").'</option>
                        <option value="0"';

                if ($current_published == '0') {
	                $published .= "selected='true'";
                }
                $published .= '>'. JText::_("ARCHIVED").'</option>
                        <option value="-1"';

                if ($current_published == '-1') {
	                $published .= "selected='true'";
                }
                $published .='>'.JText::_("TRASHED").'</option>
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
                    			<label class="control-label em-filter-label">'.JText::_('TAG').'&ensp; <a href="javascript:clearchosen(\'#select_multiple_tags\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a></label>
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
			        $tag .= '<option value="'.$p['id'].'"';
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
                    			<label class="control-label em-filter-label">'.JText::_('COM_EMUNDUS_ASSOCIATED_GROUPS').'&ensp; <a href="javascript:clearchosen(\'#select_multiple_group_assoc\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a></label>
                            </div>
                    		<div class="em_filtersElement">';
		    }
		    $group_assoc .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_group_assoc" name="group_assoc" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

		    $groupList = $m_files->getUserAssocGroups();
		    foreach ($groupList as $p) {
			    $group_assoc .= '<option value="'.$p['id'].'"';
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
        
        //Advance filter builtin
        if (@$params['adv_filter'] !== NULL) {
            $filters .= '</fieldset><fieldset class="em_filters_adv_filter">';
            $elements = $h_files->getElements();

            // the button is disabled by default. It needs a selected campaign ->> look at em_files.js at the #select_multiple_campaigns on change function
	        $disabled = empty($current_campaign) ? 'disabled' : "";

	        $search_nb = !empty($search)?count($search):0;
            $adv_filter = '<div class="em_filters em-filter" id="em_adv_filters">
								<label class="control-label editlinktip hasTip em_filters_adv_filter_label" title="'.JText::_('NOTE').'::'.JText::_('FILTER_HELP').'">'.JText::_('ELEMENT_FILTER').'</label>
								<div class="em_filters_adv_filter_addColumn" title="'.JText::_('SELECT_CAMPAIGN').'">
									<button class="btn btn-default btn-sm" type="button" id="add-filter" '.$disabled.' ><span class="glyphicon glyphicon-th-list"></span> '.JText::_('ADD_FILTER_COLUMN').'</button>
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

                	$adv_filter .= '<fieldset id="em-adv-father-'.$i.'" class="em-nopadding">
										<select class="chzn-select em-filt-select" id="elements" name="elements">
                                            <option value="">'.JText::_('PLEASE_SELECT').'</option>';
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
                        
                    $adv_filter .= '<button class="btn btn-danger btn-xs" id="suppr-filt"><span class="glyphicon glyphicon-trash" ></span></button>';
                    $i++;
                    $adv_filter .= '</fieldset>';
                }
            }
            $adv_filter .= '</div></div>';

            $filters .= $adv_filter;
        }

        //Other filters builtin
        if (@$params['other'] !== NULL && !empty($tables) && $tables[0] != "") {

            $filters .= '</fieldset><fieldset class="em_filters_other">';

            $other_elements = $h_files->getElementsOther($tables);
            $other_filter = '<div class="em_filters" id="em_other_filters">
								<a href="javascript:addElementOther();">
									<span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('FILTER_HELP').'">'.JText::_('OTHER_FILTERS').'</span>
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
                            				<option value="">'.JText::_('PLEASE_SELECT').'</option>';

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
                        		<label class="control-label em_filters_other_label">'.JText::_('NEWSLETTER').'</label>
                            </div>
                            <div class="em_filtersElement">
                        		<select class="search_test em-filt-select" id="select_newsletter" name="newsletter" '.($hidden ? 'style="visibility:hidden" ' : '').'>
                                    <option value="0"';

            if (@$newsletter == 0) {
	            $filters .= ' selected';
            }

            $filters .= '>'.JText::_("ALL").'</option>
                            <option value="1"';

            if (@$newsletter == 1) {
	            $filters .= ' selected';
            }

            $filters .= '>'.JText::_("JYES").'</option>
                        </select>
                    </div>
                </div>';
        }

        if (@$params['group'] !== NULL) {

            $hidden = $types['group'] == 'hidden';

            $group = '';
            if (!$hidden) {
                $group .= '<div id="group">
                    		<div class="em_label">
                    			<label class="control-label em_filters_other_label">'.JText::_('GROUP').' &ensp;
                    				<a href="javascript:clearchosen(\'#select_multiple_groups\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a>
                    			</label>
                            </div>
                    		<div class="em_filtersElement">';
            }
            $group .= '<select '.(!$hidden ? 'class="testSelAll em-filt-select"' : '').' id="select_multiple_groups" name="group" multiple="multiple" '.($hidden ? 'style="height: 100%;visibility:hidden;max-height:0px;width:0px;" >' : 'style="height: 100%;">');

	        $groupList = $m_files->getAllGroups();
            foreach ($groupList as $p) {
                $group .= '<option value="'.$p['id'].'"';
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
                    					<label class="control-label em_filters_other_label">'.JText::_('UNIVERSITY').' &ensp; 
                    						<a href="javascript:clearchosen(\'#select_multiple_institutions\')"><span class="glyphicon glyphicon-ban-circle" title="'.JText::_('CLEAR').'"></span></a>
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

        if (@$params['spam_suspect'] !== NULL) {
            $hidden = $types['spam_suspect'] == 'hidden';

            $filters.= '<div class="em_filters" id="spam_suspect">
							<div class="em_label">
								<label class="control-label em_filters_other_label">'.JText::_('SPAM_SUSPECT').'</label>
							</div>
							<div class="em_filtersElement">
                        		<select class="search_test" id="select_spam-suspect" name="spam_suspect" '.($hidden ? 'style="visibility:hidden" ' : '').'>
                                    <option value="0"';

            if (@$spam_suspect == 0) {
	            $filters .= ' selected';
            }
            $filters.='>'.JText::_("ALL").'</option>
                            <option value="1"';

            if (@$spam_suspect == 1) {
	            $filters .= ' selected';
            }
            $filters .= '>'.JText::_("JYES").'</option>
                        </select>
                    </div>
                </div>';
        }
       
        // Buttons
        $filters .=' </fieldset>';

        // User filter
        $research_filters = $h_files->getEmundusFilters();
        $filters .='<fieldset id="em_select_filter" class="em-user-personal-filter">
                        <label for="select_filter" class="control-label em-user-personal-filter-label">'.JText::_('SELECT_FILTER').'</label>
                        
                            <select class="chzn-select" id="select_filter" style="width:95%" name="select_filter" > 
                                <option value="0" selected="true" >'.JText::_('CHOOSE_FILTER').'</option>';
        if (!empty($research_filters)) {
            foreach ($research_filters as $filter) {
                if ($select_id == $filter->id) {
	                $filters .= '<option value="'.$filter->id.'" selected="true" >'.$filter->name.'</option>';
                } else {
	                $filters .= '<option value="'.$filter->id.'">'.$filter->name.'</option>';
                }
            }
        }
        $filters .= '</select>
					
						<button class="btn btn-xs" id="del-filter" title="'.JText::_('DELETE').'"><i class="glyphicon glyphicon-trash"></i></button>
                            <div class="alert alert-dismissable alert-success em-alert-filter" id="saved-filter">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>'.JText::_('FILTER_SAVED').'</strong>
                            </div>
                            <div class="alert alert-dismissable alert-success em-alert-filter" id="deleted-filter">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>'.JText::_('FILTER_DELETED').'</strong>
                            </div>
                            <div class="alert alert-dismissable alert-danger em-alert-filter" id="error-filter">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>'.JText::_('SQL_ERROR').'</strong>
                            </div>
                        </fieldset>
                		<script type="text/javascript" >'.EmundusHelperJavascript::getPreferenceFilters().EmundusHelperJavascript::clearAdvanceFilter().'</script>
                    </fieldset>
                    <script>
                        $(document).ready(function() {

                            $(".search_test").SumoSelect({search: true, searchText: "'.JText::_('ENTER_HERE').'"});
                            $(".testSelAll").SumoSelect({selectAll:true,search:true, searchText: "'.JText::_('ENTER_HERE').'"});

                            if ($("#select_multiple_programmes").val() != null || $("#select_multiple_campaigns").val() != null) {
                                $("#em_adv_filters").show();
                            } else {
                                $("#em_adv_filters").hide();
                            }
                            
	                        $("#select_filter").chosen({width:"95%"});
            
                        });
                    </script>';

        return $filters;
    }

    public function getEmundusFilters($id = null) {
        $itemid = JFactory::getApplication()->input->get('Itemid');
        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        if (is_null($id) && !empty($itemid)) {
            $query = 'SELECT * FROM #__emundus_filters WHERE user='.$user->id.' AND constraints LIKE "%col%" AND item_id='.$itemid;
            $db->setQuery( $query );
            return $db->loadObjectlist();
        } elseif (!empty($id)) {
            $query = 'SELECT * FROM #__emundus_filters WHERE id='.$id.' AND constraints LIKE "%col%"';
            $db->setQuery( $query );
            return $db->loadObject();
        } else {
        	return array();
        }
    }

    public function createTagsList($tags) {
        $tagsList = array();
        foreach ($tags as $tag) {
            $fnum = $tag['fnum'];
            if (!isset($tagsList[$fnum])) {
	            $tagsList[$fnum] = '<a class="item"><div class="ui mini '.$tag['class'].' horizontal label">'.$tag['label'].'</div></a> ';
            } else {
	            $tagsList[$fnum] .= '<a class="item"><div class="ui mini '.$tag['class'].' horizontal label">'.$tag['label'].'</div></a> ';
            }
        }
        return $tagsList;
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

	    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
	    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
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

			    $tags = $m_emails->setTags($fnum['applicant_id'], $post);
			    $htmlList[$fnum['fnum']] = preg_replace($tags['patterns'], $tags['replacements'], $html);
			    $htmlList[$fnum['fnum']] = $m_emails->setTagsFabrik($htmlList[$fnum['fnum']], [$fnum['fnum']]);
		    }
		}
		return $htmlList;
    }

    public function createEvaluatorList($join, $model) {
        $evaluators = array();
        $groupEval = $model->getEvaluatorsFromGroup();

        $evaluatorsDB = $model->getEvaluators();
        $title = "";
        foreach ($groupEval as $k => $group) {

            if (!array_key_exists($group['fnum'], $evaluators)) {
                $title = $group['name'];
                $evaluators[$group['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="glyphicon glyphicon-eye-open"></span>  <span class="em-evaluator editlinktip hasTip"  title="'.$title.'">'.$group['title'].'</span><button class="btn btn-danger btn-xs group" id="'.$group['fnum'].'-'.$group['id'].'">X</button></li></ul>';
            } else {
                if ((strcmp($group['fnum'], $groupEval[$k + 1]['fnum'])  == 0) && (strcmp($groupEval[$k + 1]['title'], $group['title']) == 0 )) {
                    $title .= ' '.$group['name'];
                } else {
                    $title .= ' '.$group['name'];
                    $evaluators[$group['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="glyphicon glyphicon-eye-open"></span>  <span class="em-evaluator editlinktip hasTip"  title="'.$title.'">'.$group['title'].'</span><button class="btn btn-danger btn-xs group" id="'.$group['fnum'].'-'.$group['id'].'">X</button></li></ul>';
                    $title = '';
                }
            }
        }

        foreach ($evaluatorsDB as $ev) {
            if (!array_key_exists($ev['fnum'], $evaluators)) {
                $evaluators[$ev['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="glyphicon glyphicon-eye-open"></span>  <span class="em-evaluator">'.$ev['name'].'</span><button class="btn btn-danger btn-xs">X</button></li></ul>';
            } else {
                $evaluators[$ev['fnum']] = substr($evaluators[$ev['fnum']], 0, count($evaluators[$ev['fnum']]) - 6) . '<li class="em-list-evaluator-item"><span class="glyphicon glyphicon-eye-open"></span>  <span class="em-evaluator">'.$ev['name'].'</span><button class="btn btn-danger btn-xs" id="'.$ev['fnum'].'-'.$ev['id'].'">X</button></li></ul>';
            }
        }
        return $evaluators;
    }

    // Get object of a Joomla Menu
    public function getMenuList($params, $fnum = null) {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
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
                    $note = explode('|', $item->note);
                    if (count($note) > 1) {
                        if (EmundusHelperAccess::asAccessAction($note[0], $note[1], $user->id, $fnum)) {
                            $actions[$note[0]]['multi'] = @$note[2];
                            $actions[$note[0]]['grud'] = @$note[1];
                            $item->action = $actions[$note[0]];
                        } else {
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
                    continue;
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
    function getEvaluation($format='html', $fnums) {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

        $m_evaluation   = new EmundusModelEvaluation();
        $m_files        = new EmundusModelFiles;
        $h_files        = new EmundusHelperFiles;

        if (!is_array($fnums)) {
            $fnumInfo = $m_files->getFnumInfos($fnums);
            $fnums = array($fnums);
        } else {
            $fnumInfo = $m_files->getFnumInfos($fnums[0]);
        }

        $element_id = $m_evaluation->getAllEvaluationElements(1, $fnumInfo['training']);
        $elements = $h_files->getElementsName(implode(',',$element_id));
        $evaluations = $m_files->getFnumArray($fnums, $elements);

        $data = array();
        foreach ($evaluations as $eval) {

            if ($eval['jos_emundus_evaluations___user_raw'] > 0) {

                $str = '<br><hr>';
                $str .= '<em>'.JText::_('EVALUATED_ON').' : '.JHtml::_('date', $eval['jos_emundus_evaluations___time_date'], JText::_('DATE_FORMAT_LC')).' - '.$fnumInfo['name'].'</em>';
                $str .= '<h1>'.JText::_('EVALUATOR').': '.JFactory::getUser($eval['jos_emundus_evaluations___user_raw'])->name.'</h1>';
                $str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

                foreach ($elements as $element) {
                    if($element->table_join == null){
                        $k = $element->tab_name.'___'.$element->element_name;
                    }
                    else{
                        $k = $element->table_join.'___'.$element->element_name;
                    }

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
                        $element->element_name != 'parent_id' &&
                        $element->element_hidden == 0 &&
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

    // getDecision
    function getDecision($format='html', $fnums) {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

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
	                        $str .= '<td colspan="2"><b>'.$element->element_label.'</b> <br>'.JText::_($eval[$k]).'</td>';
                        } else {
	                        $str .= '<td width="70%"><b>'.$element->element_label.'</b> </td><td width="30%">'.JText::_($eval[$k]).'</td>';
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

    // Get Admission
    function getAdmission($format='html', $fnums, $name = null) {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'admission.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

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
            $elements       = $h_files->getElementsName(implode(',',$element_id));
            $admissions     = $m_files->getFnumArray($fnums, $elements);

            $data = array();

            foreach ($admissions as $adm) {
                $str = '<br><hr>';
                $str .= '<h1>Institutional Admission</h1>';
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

        // Get information from application form filled out by the student
        $element_id     = $m_admission->getAllApplicantAdmissionElements(1, $fnumInfo['training']);
        $elements       = $h_files->getElementsName(implode(',',$element_id));
        $admissions     = $m_files->getFnumArray($fnums, $elements);

        foreach ($admissions as $adm) {
           
            $str = '<br><hr>';
            $str .= '<h1>Student Admission</h1>';
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

        return $data;
    }

    // getInterview
    function getInterview($format='html', $fnums) {
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'interview.php');
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

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
                $str .= '<em>'.JText::_('EVALUATED_ON').' : '.JHtml::_('date', $eval['jos_emundus_evaluations___time_date'], JText::_('DATE_FORMAT_LC')).' - '.$fnumInfo['name'].'</em>';
                $str .= '<h1>'.JText::_('EVALUATOR').': '.JFactory::getUser($eval['jos_emundus_evaluations___user_raw'])->name.'</h1>';
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
    public function createFnum($campaign_id, $user_id){
        return date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user_id, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Checks if a table exists in the database.
     *
     * @since 3.8.6
     * @param String Table name
     * @return Bool True if table found, else false.
     */
    public function tableExists($table_name) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // The strategy is simple: if there's an error, the table probably doesn't exist.
        $query->select($db->quoteName('id'))->from($db->quoteName($table_name))->setLimit('1');

        try {
            $db->setQuery($query);
            return !empty($db->loadResult());
        } catch (Exception $e) {
            return false;
        }
    }

    public function saveExcelFilter($user_id, $name, $constraints, $time_date, $itemid) {
        $db = JFactory::getDBO();
	    $query = $db->getQuery(true);

        try {
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

    public function getExportExcelFilter($user_id) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        try {
        	$query->select('*')
		        ->from($db->quoteName('#__emundus_filters'))
		        ->where($db->quoteName('user').' = '.$user_id.' AND constraints LIKE '.$db->quote('%excelfilter%'));
            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
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
    
}
