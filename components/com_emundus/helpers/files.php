<?php
/**
 * @version		$Id: filter.php 14401 2013-03-21 14:10:00Z brivalland $
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
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
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperFiles
{

    /*
    ** @description Clear session and reinit values by default
    */
    public  function clear()
    {
        JFactory::getSession()->set('filt_params', array());
        JFactory::getSession()->set('select_filter',null);
        JFactory::getSession()->set('adv_cols', array());
        JFactory::getSession()->set('filter_order', 'c.fnum');
        JFactory::getSession()->set('filter_order_Dir', 'desc');
        $limit = JFactory::getApplication()->getCfg('list_limit');
        $limitstart = 0;
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        JFactory::getSession()->set('limit', $limit);
        JFactory::getSession()->set('limitstart', $limitstart);

        //@EmundusHelperFiles::resetFilter();

    }

    /*
    ** @description Clear session and reinit values by default
    */
    public  function clearfilter()
    {
        JFactory::getSession()->set('filt_params', array());
        JFactory::getSession()->set('select_filter',null);
        JFactory::getSession()->set('filter_order', 'c.fnum');
        JFactory::getSession()->set('filter_order_Dir', 'desc');

        $limit = JFactory::getApplication()->getCfg('list_limit');
        $limitstart = 0;
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        JFactory::getSession()->set('limit', $limit);
        JFactory::getSession()->set('limitstart', $limitstart);

    }

    /*
    ** @description Clear session and reinit values by default
    */
    public  function resetFilter()
    {

        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

        $current_user = JFactory::getUser();

        $menu = @JSite::getMenu();
        $current_menu  = $menu->getActive();
        $menu_params = $menu->getParams(@$current_menu->id);

        $m_files = new EmundusModelFiles();

        if (!JFactory::getSession()->has('filt_params'))
        {
            JFactory::getSession()->set('filt_params', array());
        }

        $params = JFactory::getSession()->get('filt_params');

        //Filters
        $tables 		= explode(',', $menu_params->get('em_tables_id'));
        $filts_names 	= explode(',', $menu_params->get('em_filters_names'));
        $filts_values	= explode(',', $menu_params->get('em_filters_values'));
        $filts_types  	= explode(',', $menu_params->get('em_filters_options'));

        // All types of filters
        $filts_details 	= array('profile'			=> NULL,
                                  'evaluator'			=> NULL,
                                  'evaluator_group'	=> NULL,
                                  'schoolyear'			=> NULL,
                                  'campaign'			=> NULL,
                                  'programme'			=> NULL,
                                  'missing_doc'		=> NULL,
                                  'complete'			=> NULL,
                                  'finalgrade'			=> NULL,
                                  'validate'			=> NULL,
                                  'other'				=> NULL,
                                  'status'				=> NULL,
                                  'published'          => NULL,
                                  'adv_filter'		    => NULL,
                                  'newsletter'		    => NULL,
                                  'spam_suspect'	    => NULL,
                                  'not_adv_filter' 	=> NULL);
        $filts_options 	= array('profile'			=> NULL,
                                  'evaluator'			=> NULL,
                                  'evaluator_group'	=> NULL,
                                  'schoolyear'			=> NULL,
                                  'campaign'			=> NULL,
                                  'programme'			=> NULL,
                                  'missing_doc'		=> NULL,
                                  'complete'			=> NULL,
                                  'finalgrade'			=> NULL,
                                  'validate'			=> NULL,
                                  'other'				=> NULL,
                                  'status'				=> NULL,
                                  'published'          => NULL,
                                  'adv_filter'		    => NULL,
                                  'newsletter'		    => NULL,
                                  'spam_suspect'	    => NULL,
                                  'not_adv_filter'	    => NULL);
        /*	$validate_id  	= explode(',', $menu_params->get('em_validate_id'));
            $columnSupl = explode(',', $menu_params->get('em_actions'));*/

        $filter_multi_list = array('schoolyear', 'campaign', 'programme', 'status', 'profile_users');

        //$tab_params = array();
        foreach ($filts_names as $key => $filt_name)
        {
            if(!is_null($filts_values[$key]) && isset($filts_values[$key]) && empty($params[$filt_name])){

                if(in_array($filt_name, $filter_multi_list)) {
                    $params[$filt_name] = array();
                    $params[$filt_name] = explode('|', $filts_values[$key]);
                    $params[$filt_name] = array_unique($params[$filt_name]);
                }
                else {
                    $params[$filt_name] = $filts_values[$key];
                }
            }
            if (array_key_exists($key, $filts_values)) {
                if(in_array($filt_name, $filter_multi_list))
                    $filts_details[$filt_name] = explode('|', $filts_values[$key]);
                else
                    $filts_details[$filt_name] = $filts_values[$key];
            }
            else
                $filts_details[$filt_name] = '';
            if (array_key_exists($key, $filts_types)) {
                if ($filts_types[$key] == "hidden") {
                    if(in_array($filt_name, $filter_multi_list))
                        $params[$filt_name] = explode('|', $filts_values[$key]);
                    else
                        $params[$filt_name] = $filts_values[$key];
                }
                $filts_options[$filt_name] = $filts_types[$key];
            } else
                $filts_options[$filt_name] = '';
        }
        // ONLY FILES LINKED TO MY GROUP
        $programme = count($this->code)>0?$this->code:null;
        //////////////////////////////////////////
  //var_dump($params['programme']);
        if (count(@$params['programme']) == 0 || @$params['programme'][0] == '%') {
            $params['programme'] = $programme;
            $filts_details['programme'] = $programme;
        } elseif(count($filts_details['programme']) == 0 || empty($filts_details['programme'])) {
            $filts_details['programme'] = $programme;
        }
        $codes = $m_files->getAssociatedProgrammes($current_user->id);
        if(count($codes)>0) {
            $params['programme'] = array_merge($params['programme'], $codes);
            $filts_details['programme'] = array_merge($filts_details['programme'], $codes);
        }

        JFactory::getSession()->set('filt_params', $params);

        return @EmundusHelperFiles::createFilterBlock($filts_details, $filts_options, $tables);
    }


    /*
    * @param 			query results
    * @param 	array 	values to extract and insert
    */
    public  function insertValuesInQueryResult($results, $options)
    {
        foreach ($results as $key => $result)
        {
            if (array_key_exists('params', $result))
            {
                if (is_array($result))
                {
                    $params = json_decode($result['params']);
                    foreach ($options as $option)
                    {
                        if (property_exists($params, 'sub_options') && array_key_exists($option, $params->sub_options))
                            $results[$key][$option] = implode('|', $params->sub_options->$option);
                        else
                            $results[$key][$option] = '';
                    }
                }
                else
                {
                    $params = json_decode($result->params);
                    foreach ($options as $option)
                    {
                        if (property_exists($params, 'sub_options') && array_key_exists($option, $params->sub_options))
                            $results[$key]->$option = implode('|', $params->sub_options->$option);
                        else
                            $results[$key]->$option = '';
                    }
                }
            }
        }
        return $results;
    }

    public  function getCurrentCampaign(){
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $nb_months_registration_period_access = $eMConfig->get('nb_months_registration_period_access', '11');
        $db = JFactory::getDBO();
        $query = 'SELECT DISTINCT year as schoolyear
		FROM #__emundus_setup_campaigns 
		WHERE published = 1 AND end_date > DATE_ADD(NOW(), INTERVAL -'.$nb_months_registration_period_access.' MONTH) ORDER BY schoolyear DESC';
        $db->setQuery( $query );
        try
        {
            return $db->loadResultArray();
        }
        catch (Exception $e)
        {
            throw new   Exception;

        }
    }

    public  function getCurrentCampaignsID(){
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $nb_months_registration_period_access = $eMConfig->get('nb_months_registration_period_access', '11');
        $db = JFactory::getDBO();
        $query = 'SELECT id
		FROM #__emundus_setup_campaigns 
		WHERE published = 1 AND end_date > DATE_ADD(NOW(), INTERVAL -'.$nb_months_registration_period_access.' MONTH)
		ORDER BY year DESC';
        $db->setQuery( $query );
        try
        {
            return $db->loadResultArray();
        }
        catch (Exception $e)
        {
            throw new   Exception;
        }
    }

    public  function getCampaigns() {
        $params = JFactory::getSession()->get('filt_params');
        if (!empty($params) && !empty($params['programme']) && count($params['programme'] > 0)) {
            $code = implode('","', $params['programme']);
            $where = 'training IN ("'.$code.'")';
        } else
            $where = '1=1';
        $db = JFactory::getDBO();
        $query = 'SELECT id, label, year  FROM #__emundus_setup_campaigns WHERE published=1 AND '.$where.' ORDER BY year DESC';

        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function getProgrammes($code = array()) {
        $db = JFactory::getDBO();
        if (!empty($code) && is_array($code)) {
            if ($code[0] == '%') {
                // ONLY FILES LINKED TO MY GROUPS
                require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
                $userModel = new EmundusModelUsers();
                $user = JFactory::getUser();
                $code = $userModel->getUserGroupsProgrammeAssoc($user->id);
                $where = 'code IN ("'.implode('","', $code).'")';
                //////////////////////////////////
            } else
                $where = 'code IN ("'.implode('","', $code).'")';
        } else
            $where = '1=1';
        $query = 'SELECT *  FROM #__emundus_setup_programmes WHERE published=1 AND '.$where.' ORDER BY label,ordering ASC';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function getStatus() {
        $db = JFactory::getDBO();
        $query = 'SELECT *  FROM #__emundus_setup_status ORDER BY ordering';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function getCampaign()
    {
        $db = JFactory::getDBO();
        $query = 'SELECT year as schoolyear FROM #__emundus_setup_campaigns WHERE published=1';
        $db->setQuery( $query );
        $syear = $db->loadRow();

        return $syear[0];
    }

    public  function getCampaignByID($id)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_setup_campaigns WHERE id='.$id;
        $db->setQuery( $query );

        return $db->loadAssoc();
    }

    public  function getApplicants(){
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label
		FROM #__emundus_setup_profiles esp '; //WHERE esp.published =1';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public  function getProfiles(){
        $db = JFactory::getDBO();
        $query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft
		FROM #__emundus_setup_profiles esp 
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id 
		ORDER BY caag.lft, esp.label';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public  function getEvaluators(){
        $db = JFactory::getDBO();
        $query = 'SELECT u.id, u.name
		FROM #__users u
		LEFT JOIN #__emundus_users eu ON u.id = eu.user_id
		LEFT JOIN #__emundus_users_profiles eup ON u.id=eup.user_id
		LEFT JOIN #__emundus_setup_profiles esp ON (esp.id=eup.profile_id OR esp.id=eu.profile)
		WHERE esp.is_evaluator=1
		ORDER BY u.name';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public  function getGroupsEval(){
        $db = JFactory::getDBO();
        $query = 'SELECT ege.group_id
		FROM #__emundus_groups_eval ege
		ORDER BY ege.group_id';
        //echo str_replace("#_", "jos", $query);
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function getGroups(){
        $db = JFactory::getDBO();
        $query = 'SELECT esg.id, esg.label
		FROM #__emundus_setup_groups esg
		WHERE esg.published=1 
		ORDER BY esg.label';
        $db->setQuery( $query );
        return $db->loadObjectList('id');
    }

    public  function getSchoolyears(){
        $db = JFactory::getDBO();
        $query = 'SELECT DISTINCT(year) as schoolyear
			FROM #__emundus_setup_campaigns 
			ORDER BY schoolyear DESC';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function getFinal_grade(){
        $db = JFactory::getDBO();
        $query = 'SELECT name, params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
        $db->setQuery( $query );
        return @EmundusHelperFiles::insertValuesInQueryResult($db->loadAssocList('name'), array("sub_values", "sub_labels"));
    }

    public  function getMissing_doc(){
        $db = JFactory::getDBO();
        $query = 'SELECT DISTINCT(esap.attachment_id), esa.value
				FROM #__emundus_setup_attachment_profiles esap
				LEFT JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function getEvaluation_doc($result){
        $db = JFactory::getDBO();
        $query = 'SELECT *
				FROM #__emundus_setup_attachments esa
				WHERE id IN (
					SELECT distinct(esl.attachment_id) FROM #__emundus_setup_letters esl WHERE eligibility='.$result.'
					)
				ORDER BY esa.value';
        $db->setQuery( $query );
        return $db->loadObjectList();
    }

    public  function setEvaluationList ($result) {
        $option_list =  @EmundusHelperFiles::getEvaluation_doc($result);
        $current_filter = '<select class="chzn-select" name="attachment_id" id="attachment_id">';
        if(!empty($option_list)){
            foreach($option_list as $value){
                $current_filter .= '<option value="'.$value->id.'">'.$value->value.'</option>';
            }
        }
        $current_filter .= '</select>';

        return $current_filter;
    }

    public static function getElements($code = array())
    {
        require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
        require_once(JPATH_COMPONENT.DS.'models'.DS.'users.php');
        require_once(JPATH_COMPONENT.DS.'models'.DS.'profile.php');

        $menu 		= new EmundusHelperMenu;
        $user 		= new EmundusModelUsers;
        $profile 	= new EmundusModelProfile;

        $db         = JFactory::getDBO();
        // get all profiles
        $profiles = $user->getApplicantProfiles();

        if(count($code) == 0) {
            $params = JFactory::getSession()->get('filt_params');
            $programme = $params['programme'];
            $campaigns = @$params['campaign'];

            // get profiles for selected programmes or campaigns
            $plist = $profile->getProfileIDByCourse($programme);
            $plist = count($plist) == 0 ? $profile->getProfileIDByCampaign($campaigns) : $plist;
        } else {
            $plist = $profile->getProfileIDByCourse($code);
        }

        if ($plist) {
            // get Fabrik list ID for profile_id
            $fl = array();
            $menutype = array();
            foreach ($profiles as $profile) {
                if (is_array($plist)) {

                    if (count($plist) == 0 || (count($plist) > 0 && in_array($profile->id, $plist))) {
                        $menu_list = $menu->buildMenuQuery($profile->id);
                        foreach ($menu_list as $m) {
                            $fl[] = $m->table_id;
                            $menutype[$profile->id] = 'menu-profile' . $profile->id;
                        }
                    }
                }
            }

            if (count($fl) == 0) {
                return array();
            }

            $query = 'SELECT distinct(concat_ws("_",tab.db_table_name,element.name)), element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin, element.id, groupe.id AS group_id, groupe.label AS group_label, element.params AS element_attribs,
                    INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, tab.label AS table_label, tab.created_by_alias, joins.table_join, menu.title
                    FROM #__fabrik_elements element';
            $join = 'INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
                    INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
                    INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
                    INNER JOIN #__fabrik_forms AS form ON tab.form_id = form.id
                    LEFT JOIN #__fabrik_joins AS joins ON tab.id = joins.list_id AND groupe.id=joins.group_id
                    INNER JOIN #__menu AS menu ON form.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)';
            $where = 'WHERE tab.published = 1
                        AND (tab.id IN ( ' . implode(',', $fl) . ' ))
                        AND element.published=1
                        AND element.hidden=0
                        AND element.label!=" "
                        AND element.label!=""
                        AND menu.menutype IN ( "' . implode('","', $menutype) . '" )';
            $order = 'ORDER BY menu.lft, formgroup.ordering, element.ordering';

            $query .= ' ' . $join . ' ' . $where . ' ' . $order;
            //echo str_replace('#_', 'jos', $query);

            try {
                $db->setQuery($query);
                $elements = $db->loadObjectList('id');
                $elts = array();
                if (count($elements)>0) {
                    foreach ($elements as $key => $value) {
                        $value->id = $key;
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

    public  function getPhotos($model, $baseUrl)
    {
        try
        {
            $pictures = array();

            $photos = $model->getPhotos();
            foreach ($photos as $photo)
            {
                $folder = $baseUrl.EMUNDUS_PATH_REL.$photo['user_id'];
                $pictures[$photo['fnum']] = '<a href="'.$folder.'/'.$photo['filename'].'" target="_blank"><img class="img-responsive" src="'.$folder . '/tn_'. $photo['filename'] . '" width="60" /></a>';
            }


            return $pictures;
        }
        catch (Exception $e)
        {
            return false;
        }
    }


    /**
     * Get list of elements declared in a list of Fabrik groups
     * @param 	string 	List of Fabrik groups comma separated
     * @param 	int 	Does the element are shown in Fabrik list ?
     * @return   array 	list of Fabrik element ID used in evaluation form
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
        if(!empty($tables) && !empty($tables[0])) {
            $query .= ' WHERE tab.id IN(';
            $first = true;
            foreach($tables as $table){
                if ($first){
                    $query .= $table;
                    $first = false;
                }
                else $query .= ', '.$table;
            }
            $query .= ') AND ';
        }
        else
            $query .= ' WHERE ';
        $query .= 'element.name NOT IN ("id", "time_date", "user", "student_id", "type_grade", "final_grade")
				ORDER BY group_id';
        $db->setQuery($query);
//		die(str_replace("#_", "jos", $query));
        return $db->loadObjectList();
    }

    public  function getElementsValuesOther($element_id){
        //jimport( 'joomla.registry.format.json' );
        $db = JFactory::getDBO();
        $query = 'SELECT params FROM #__fabrik_elements element WHERE id='.$element_id;
        $db->setQuery($query);
        $res = $db->loadResult();
        $sub = json_decode($res);//JRegistryFormatJson::stringToObject($res);

        return $sub->sub_options;
    }

    public  function getElementsName($elements_id)
    {
        if (!empty($elements_id) && isset($elements_id)) {
            $db = JFactory::getDBO();
            $query = 'SELECT element.name AS element_name, element.label as element_label, element.params AS element_attribs, element.id, element.plugin as element_plugin, groupe.id as group_id, groupe.params as group_attribs,tab.db_table_name AS tab_name, tab.created_by_alias AS created_by_alias, joins.table_join
					FROM #__fabrik_elements element
					INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id 
					INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id 
					INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
					LEFT JOIN #__fabrik_joins AS joins ON tab.id = joins.list_id AND groupe.id=joins.group_id
					WHERE element.id IN ('.ltrim($elements_id, ',').')
					ORDER BY formgroup.ordering, element.ordering ';
            $db->setQuery($query);
//echo '<hr>'.str_replace('#_', 'jos', $query);
            //$elementsIdTab = array_fill_keys(explode(',', $elements_id), "");
            $elementsIdTab = array();
            //$res0 = $db->loadObjectList();
            $res = $db->loadObjectList('id');
            foreach($res as $kId => $r)
            {
                $elementsIdTab[$kId] = $r;
            }
            return $elementsIdTab;
        } else
            return array();
    }

    public  function buildOptions($element_name, $params)
    {
        if(!empty($params->join_key_column))
        {
            $db = JFactory::getDBO();
            if($element_name == 'result_for')
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$params->join_val_column.' AS elt_val FROM '.$params->join_db_name.' WHERE published=1';
            elseif($element_name == 'campaign_id')
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$params->join_val_column.' AS elt_val FROM '.$params->join_db_name;
            elseif($element_name=='training_id')
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$params->join_val_column.' AS elt_val FROM '.$params->join_db_name.' ORDER BY '.$params->join_db_name.'.date_start ';
            else
                $query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$params->join_val_column.' AS elt_val FROM '.$params->join_db_name.' '.$params->database_join_where_sql;
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
    public  function setWhere($search, $search_values, &$query) {
        if(isset($search) && !empty($search)) {
            $i = 0;
            foreach ($search as $s) {
                if( (!empty($search_values[$i]) || isset($search_values[$i])) && $search_values[$i]!="" ){
                    $tab = explode('.', $s);
                    if (count($tab)>1) {
                        if($tab[0]=='jos_emundus_training'){
                            $query .= ' AND ';
                            $query .= ' search_'.$tab[0].'.id like "%'.$search_values[$i].'%"';
                        }else{
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
    public  function setSearchBox($selected, $search_value, $elements_values, $i)
    {
        jimport( 'joomla.html.parameter' );
        $current_filter = "";
        if(!empty($selected))
        {
            if($selected->element_plugin == "databasejoin")
            {
                //$query_paramsdefs = JPATH_BASE.DS.'plugins'.DS.'fabrik_element'.DS.$selected->element_plugin.DS.$selected->element_plugin.'.xml';
                //$query_params = new JParameter($selected->element_attribs, $query_paramsdefs);
                $query_params = json_decode($selected->element_attribs);
                $option_list =  @EmundusHelperFiles::buildOptions($selected->element_name, $query_params);
                $current_filter .= '<select class="chzn-select em-filt-select" id="em-adv-fil-'.$i.'" class="chsn-select" name="'.$elements_values.'" id="'.$elements_values.'">
				<option value="">'.JText::_('PLEASE_SELECT').'</option>';
                if(!empty($option_list))
                {
                    foreach($option_list as $value)
                    {
                        $current_filter .= '<option value="'.$value->elt_key.'"';
                        if ($value->elt_key == $search_value)
                            $current_filter .= ' selected';
                        $current_filter .= '>'.$value->elt_val.'</option>';
                    }
                }
                $current_filter .= '</select>';
            }
            elseif($selected->element_plugin == "checkbox" || $selected->element_plugin == "radiobutton" || $selected->element_plugin == "dropdown")
            {
                //$query_paramsdefs = JPATH_BASE.DS.'plugins'.DS.'fabrik_element'.DS.$selected->element_plugin.DS.$selected->element_plugin.'.xml';
                //$query_params = new JParameter($selected->element_attribs, $query_paramsdefs);
                $query_params = json_decode($selected->element_attribs);
                $option_list =  @EmundusHelperFiles::buildOptions($selected->element_name, $query_params);
                $current_filter .= '<select class="chzn-select em-filt-select" id="em-adv-fil-'.$i.'" name="'.$elements_values.'" id="'.$elements_values.'">
				<option value="">'.JText::_('PLEASE_SELECT').'</option>';
                if(!empty($option_list))
                {
                    foreach($option_list as $value)
                    {
                        $current_filter .= '<option value="'.$value->elt_key.'"';
                        if ($value->elt_key == $search_value)
                            $current_filter .= ' selected';
                        $current_filter .= '>'.$value->elt_val.'</option>';
                    }
                }
                $current_filter .= '</select>';
            } else
                $current_filter .= '<input type="text" id="em-adv-fil-'.$i.'" class="form-control" name="'.$elements_values.'" value="'.$search_value.'" />';
        }

        return $current_filter;
    }

    /*
    ** @description Create a fieldset of filter boxes
    ** @param array $params Filters values indexed by filters names (profile / evaluator / evaluator_group / finalgrade / schoolyear / missing_doc / complete / validate / other / tag).
    ** @param array $types Filters options indexed by filters names.
    ** @param array $tables List of the tables contained in "Other filters" dropbox.
    ** @return string HTML to display in page for filter block.
    */	//$filts_details, $filts_options, $tables
    public  function createFilterBlock($params, $types, $tables){
        require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');
        $files = new EmundusModelFiles();

        /*$document = JFactory::getDocument();
        $document->addStyleSheet( JURI::base()."media/com_emundus/lib/chosen/chosen.min.css" );
        $document->addScript( JURI::base()."media/com_emundus/lib/jquery-1.10.2.min.js" );
        $document->addScript( JURI::base()."media/com_emundus/lib/chosen/chosen.jquery.min.js" );*/

        $session     = JFactory::getSession();
        $filt_params = $session->get('filt_params');
        $select_id	 = $session->get('select_filter');
        if (!is_null($select_id))
        {
            $research_filter = @EmundusHelperFiles::getEmundusFilters($select_id);
            $filter =  json_decode($research_filter->constraints, true);
            $filt_params = $filter['filter'];
        }

        $current_s 				= @$filt_params['s'];
        $current_profile		= @$filt_params['profile'];
        $current_eval			= @$filt_params['user'];
        //$current_group			= @$filt_params['group'];
        $miss_doc				= @$filt_params['missing_doc'];
        $current_finalgrade		= @$filt_params['finalgrade'];
        $current_schoolyear		= @$filt_params['schoolyear'];
        $current_campaign		= @$filt_params['campaign'];
        $current_programme		= @$filt_params['programme'];
        $search					= @$filt_params['elements'];
        $search_other		 	= @$filt_params['elements_other'];
        $complete_application	= @$filt_params['complete'];
        $validate_application	= @$filt_params['validate'];
        $current_status			= @$filt_params['status'];
        $current_published      = @$filt_params['published'];
        $current_tag			= @$filt_params['tag'];
        $current_group_eval     = @$filt_params['evaluator_group'];
        $current_user_profile	= @$filt_params['profile_users'];
        $newsletter	            = @$filt_params['newsletter'];
        $spam_suspect           = @$filt_params['spam_suspect'];
        $filters = '';
        // Quick filter
        $quick = '<div id="filters">
					<div id="quick" class="form-group">
						<label class="control-label editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('NAME_EMAIL_USERNAME').'">'.JText::_('QUICK_FILTER').'
							<a href="javascript:clearchosen(\'#text_s\')"><span class="glyphicon glyphicon-remove" title="'.JText::_('CLEAR').'"></span></a>
						</label>
						<input class="form-control" id="text_s" type="text" name="s" value="'.$current_s.'"/>
					</div> 
				   	<button type="button" class="btn btn-xs" id="shower"><i class="icon-chevron-down"></i> ' . JText::_('MORE_FILTERS') . '</button>
				   	<button type="button" class="btn btn-xs" id="hider"><i class="icon-chevron-up"></i> ' . JText::_('HIDE_FILTERS') . '</button>
			   	</div>';

        $filters .= $quick;

        $filters .= '<fieldset class="em_filters_filedset">';
        if(@$params['profile'] !== NULL){
            $profile = '';
            $hidden = $types['profile'] != 'hidden' ? false : true;
            if ($types['profile'] != 'hidden')
                $profile .= '<div class="form-group" id="profile">
									<label class="control-label">'.JText::_('PROFILE').'</label>';
            $profile .= '			<select class="chzn-select em-filt-select" id="select_profile" name="profile" '.($types['profile'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'">
						 <option value="0">'.JText::_('ALL').'</option>';
            $profiles = @EmundusHelperFiles::getApplicants();
            foreach($profiles as $prof)
            {
                $profile .= '<option value="'.$prof->id.'"';
                if(!empty($current_profile) && (in_array($prof->id, $current_profile) || $prof->id == $current_profile))
                    $profile .= ' selected="true"';
                $profile .= '>'.$prof->label.'</option>';
            }
            $profile .= '</select>';
            if ($types['profile'] != 'hidden') $profile .= '</div>';
            $filters .= $profile;
        }
        //if($debug==1) $div .= '<input name="view_calc" type="checkbox" onclick="document.pressed=this.name" value="1" '.$view_calc==1?'checked=checked':''.' />';

        if(@$params['profile_users'] !== NULL){
            $hidden = $types['profile_users'] != 'hidden' ? false : true;
            $profile_user = '';
            if ($types['profile_users'] != 'hidden')
                $profile_user .= '<div class="form-group" id="profile_users">
									<label class="control-label">'.JText::_('PROFILE_FILTER').'</label>';
            $profile_user .= '		<select class="chzn-select em-filt-select" id="select_profile_users" name="profile_users" '.($types['profile_users'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
						 <option value="0">'.JText::_('ALL').'</option>';
            $profile_users = @EmundusHelperFiles::getProfiles();
            $prefilter = count($filt_params['profile_users'])>0?true:false;

            foreach($profile_users as $profu) {
                if(!$prefilter || ($prefilter && in_array($profu->id, $params['profile_users']))) {
                    $profile_user .= '<option value="' . $profu->id . '"';
                    if($current_user_profile == $profu->id) $profile_user .= ' selected="true"';
                    $profile_user .= '>' . $profu->label . '</option>';
                }
            }
            $profile_user .= '</select>';
            if ($types['profile_users'] != 'hidden') $profile_user .= '</div><script>$(document).ready(function() {$("#select_profile_users").chosen({width: "75%"}); })</script>';
            $filters .= $profile_user;
        }

        if(@$params['evaluator'] !== NULL){
            $eval = '';
            $hidden = $types['evaluator'] != 'hidden' ? false : true;
            if ($types['evaluator'] != 'hidden')
                $eval .= '<div class="em_filters" id="evaluator">
							   <label class="control-label">'.JText::_('ASSESSOR_USER_FILTER').'</label>
							   <div class="em_filtersElement">';
            $eval .= '<select class="chzn-select em-filt-select" id="select_user" name="user" '.($types['evaluator'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
					  <option value="0">'.JText::_('ALL').'</option>';
            $evaluators = @EmundusHelperFiles::getEvaluators();
            foreach($evaluators as $evaluator) {
                $eval .= '<option value="'.$evaluator->id.'"';
                if($current_eval == $evaluator->id) $eval .= ' selected="true"';
                $eval .= '>'.$evaluator->name.'</option>';
            }
            $eval .= '</select>';
            if ($types['evaluator'] != 'hidden') $eval .= '</div></div>';
            $filters .= $eval;
        }

        if($params['evaluator_group'] !== NULL){
            $group_eval = '';
            $hidden = $types['evaluator_group'] != 'hidden' ? false : true;
            if ($types['evaluator_group'] != 'hidden')
                $group_eval .= '<div class="em_filters" id="gp_evaluator">
								   <label class="control-label">'.JText::_('GROUP_FILTER').'</label>
								   <div class="em_filtersElement">';
            $group_eval .= '<select class="chzn-select em-filt-select" id="select_groups" name="evaluator_group" '.($types['evaluator_group'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '"" ').'>
							<option value="0">'.JText::_('ALL').'</option>';
            $groups = @EmundusHelperFiles::getGroups();
            foreach($groups as $group) {
                $group_eval .= '<option value="'.$group->id.'"';
                if($current_group_eval == $group->id) $group_eval .= ' selected="true"';
                $group_eval .= '>'.$group->label.'</option>';
            }
            $group_eval .= '</select>';
            if ($types['evaluator_group'] != 'hidden') $group_eval .= '</div></div>';
            $filters .= $group_eval;
        }

        if(@$params['finalgrade'] !== NULL){
            $hidden = $types['finalgrade'] != 'hidden' ? false : true;
            $finalgrade = @EmundusHelperFiles::getFinal_grade();
            $final_gradeList = explode('|', $finalgrade['final_grade']['sub_labels']);
            $sub_values = explode('|', $finalgrade['final_grade']['sub_values']);
            foreach($sub_values as $sv) $p_grade[]="/".$sv."/";
            unset($sub_values);
            $final_grade = '';
            if ($types['finalgrade'] != 'hidden') $final_grade .= '<div class="em_filters" id="finalgrade">
																   <div class="em_label"><label class="control-label">'.JText::_('FINAL_GRADE_FILTER').'</label></div>
																   <div class="em_filtersElement">';
            $final_grade .= '<select class="chzn-select em-filt-select" id="select_finalgrade" name="finalgrade" '.($types['finalgrade'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
							 <option value="0">'.JText::_('PLEASE_SELECT').'</option>';
            $groupe ="";
            for($i=0; $i<count($final_gradeList); $i++) {
                $val = substr($p_grade[$i],1,1);
                $final_grade .= '<option value="'.$val.'"';
                if($val == $current_finalgrade) $final_grade .= ' selected="true"';
                $final_grade .= '>'.$final_gradeList[$i].'</option>';
            }
            unset($val); unset($i);
            $final_grade .= '</select>';
            if ($types['finalgrade'] != 'hidden') $final_grade .= '</div></div>';
            $filters .= $final_grade;
        }

        if(@$params['missing_doc'] !== NULL){
            $hidden = $types['missing_doc'] != 'hidden' ? false : true;
            $missing_docList = @EmundusHelperFiles::getMissing_doc();
            $missing_doc = '';
            if (@$types['missing_doc'] != 'hidden')
                $missing_doc .= '<div class="em_filters" id="missing_doc">
									<div class="em_label">
										<label>'.JText::_('MISSING_DOC').'</label>
									</div>
									<div class="em_filtersElement">';
            $missing_doc .= '<select class="chzn-select em-filt-select" id="select_missing_doc" name="missing_doc" '.(@$types['missing_doc'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
								<option value="0">'.JText::_('ALL').'</option>';
            foreach($missing_docList as $md)
            {
                $missing_doc .= '<option value="'.$md->attachment_id.'"';
                if($miss_doc == $md->attachment_id) $missing_doc .= ' selected="true"';
                $missing_doc .= '>'.$md->value.'</option>';
            }
            $missing_doc .= '</select>';
            if ($types['schoolyear'] != 'hidden') {
                $missing_doc .= '</div></div>';
                $missing_doc .= '<script>$(document).ready(function() {$("#select_missing_doc").chosen({width:"75%"});})</script>';
            }
            $filters .= $missing_doc;
        }

        if(@$params['complete'] !== NULL){
            $complete = '';
            $hidden = $types['complete'] != 'hidden' ? false : true;
            if ($types['complete'] != 'hidden')
                $complete .= '<div class="em_filters" id="complete">
								<div class="em_label"><label class="control-label">'.JText::_('COMPLETE_APPLICATION').'</label></div>
								<div class="em_filtersElement">';
            $complete .= '<select class="chzn-select em-filt-select" id="select_complete" name="complete" '.($types['complete'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
							<option value="0">'.JText::_('ALL').'</option>';
            $complete .= '<option value="1"';
            if($complete_application == 1) $complete .= ' selected="true"';
            $complete .= '>'.JText::_('YES').'</option>';
            $complete .= '<option value="2"';
            if($complete_application == 2) $complete .= ' selected="true"';
            $complete .= '>'.JText::_('NO').'</option>';
            $complete .= '</select>';
            if ($types['complete'] != 'hidden') $complete .= '</div></div>';
            $filters .= $complete;
        }

        if(@$params['validate'] !== NULL){
            $validate = '';
            $hidden = $types['validate'] != 'hidden' ? false : true;
            if ($types['validate'] != 'hidden') $validate .= '<div class="em_filters" id="validate">
															  <div class="em_label"><label class="control-label">'.JText::_('VALIDATED_APPLICATION').'</label></div>
															  <div class="em_filtersElement">';
            $validate .= '<select class="chzn-select em-filt-select" id="select_validate" name="validate" '.($types['validate'] == 'hidden' ? 'style="visibility:hidden;height:0px;width:0px;" ' : '').'>
							<option value="0">'.JText::_('ALL').'</option>';
            $validate .= '<option value="1"';
            if($validate_application == 1) $validate .= ' selected="true"';
            $validate .= '>'.JText::_('VALIDATED').'</option>';
            $validate .= '<option value="2"';
            if($validate_application == 2) $validate .= ' selected="true"';
            $validate .= '>'.JText::_('UNVALIDATED').'</option>';
            $validate .= '</select>';
            if ($types['validate'] != 'hidden') $validate .= '</div></div>';
            $filters .= $validate;
        }

        if(@$params['campaign'] !== NULL){
            $hidden = $types['campaign'] != 'hidden' ? false : true;
            $campaignList = @EmundusHelperFiles::getCampaigns();
            $campaign = '';
            if ($types['campaign'] != 'hidden') {
                $campaign .= '<div id="campaign">
							<div class="em_label">
							<label class="control-label">'.JText::_('CAMPAIGN').' 
								 <a href="javascript:clearchosen(\'#select_multiple_campaigns\')"><span class="glyphicon glyphicon-remove" title="'.JText::_('CLEAR').'"></span></a>
								</label>
							</div>
						  <div class="em_filtersElement">';
            }
            $campaign .= '<select '.(!$hidden ? 'class="chzn-select em-filt-select"' : '').' id="select_multiple_campaigns" name="campaign" multiple="" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;"> ' : '>');
            $campaign .= '<option value="%" ';
            if((@$current_campaign[0] == "%" || empty($current_campaign[0])) && count(@$current_campaign)<2) $campaign .= ' selected="true"';
            $campaign .= '>'.JText::_('ALL').'</option>';

            foreach($campaignList as $c) {
                $campaign .= '<option value="'.$c->id.'"';
                if(!empty($current_campaign) && in_array($c->id, $current_campaign))
                    $campaign .= ' selected="true"';
                $campaign .= '>'.$c->label.' - '.$c->year.'</option>';
            }
            $campaign .= '</select>';
            if (!$hidden) {
                $campaign .= '</div></div>';
                $campaign .= '<script>$(document).ready(function() {$("#select_multiple_campaigns").chosen({width:"75%"}); })</script>';
            }
            $filters .= $campaign;
        }

        if($params['schoolyear'] !== NULL){
            $schoolyearList = @EmundusHelperFiles::getSchoolyears();
            $schoolyear = '';
            $hidden = $types['schoolyear'] != 'hidden' ? false : true;
            if (!$hidden) {
                $schoolyear .= '<div id="schoolyear">
									<div class="em_label"><label class="control-label">'.JText::_('SCHOOLYEARS').' <a href="javascript:clearchosen(\'#select_multiple_schoolyears\')"><span class="glyphicon glyphicon-remove" title="'.JText::_('CLEAR').'"></span></a></label></div>
									<div class="em_filtersElement">';
            }

            $schoolyear .= '<select '.(!$hidden ? 'class="chzn-select em-filt-select"' : '').' id="select_multiple_schoolyears" name="schoolyear" multiple="" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;"> ' : '>');
            $schoolyear .= '<option value="%" ';
            if( ($current_schoolyear[0]=="%" || empty($current_schoolyear[0])) && count($current_schoolyear)<2 ) $schoolyear .= ' selected="true"';
            $schoolyear .= '>'.JText::_('ALL').'</option>';
            foreach($schoolyearList as $key=>$value) {
                $schoolyear .= '<option value="'.$value->schoolyear.'"';
                if( !empty($current_schoolyear) && in_array($value->schoolyear, $current_schoolyear) )
                    $schoolyear .= ' selected="true"';
                $schoolyear .= '>'.$value->schoolyear.'</option>';
            }
            $schoolyear .= '</select>';
            if (!$hidden) {
                $schoolyear .= '</div></div>';
                $schoolyear .= '<script>$(document).ready(function() {$("#select_multiple_schoolyears").chosen({width:"75%"});})</script>';
            }
            $filters .= $schoolyear;
        }

        if(@$params['programme'] !== NULL){
            $hidden = $types['programme'] != 'hidden' ? false : true;
            $programmeList = @EmundusHelperFiles::getProgrammes($params['programme']);
            $programme = '';
            if (!$hidden) {
                $programme .= '<div id="programme">
					<div class="em_label"><label class="control-label">'.JText::_('PROGRAMME').' <a href="javascript:clearchosen(\'#select_multiple_programmes\')"><span class="glyphicon glyphicon-remove" title="'.JText::_('CLEAR').'"></span></a></label></div>
					<div class="em_filtersElement">';
            }
            $programme .= '<select '.(!$hidden ? 'class="chzn-select em-filt-select"' : '').' id="select_multiple_programmes" name="programme" multiple="" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" >' : '>');
            $programme .= '<option value="%" ';
            if( (@$current_programme[0] == "%" || empty($current_programme[0])) && count(@$current_programme)<2 ) $programme .= ' selected="true"';
            $programme .= '>'.JText::_('ALL').'</option>';

            foreach($programmeList as $p) {
                $programme .= '<option value="'.$p->code.'"';
                if(!empty($current_programme) && in_array($p->code, $current_programme)) $programme .= ' selected="true"';
                $programme .= '>'.$p->label.' - '.$p->code.'</option>';
            }
            $programme .= '</select>';
            if (!$hidden) {
                $programme .= '</div></div>';
                $programme .= '<script>$(document).ready(function() {$("#select_multiple_programmes").chosen({width: "75%"});})</script>';
            }
            $filters .= $programme;
        }

        if(@$params['status'] !== NULL){
            $hidden = $types['status'] != 'hidden' ? false : true;
            $statusList = @EmundusHelperFiles::getStatus();
            $status = '';
            if (!$hidden) {
                $status .= '<div id="status">
					<div class="em_label"><label class="control-label">'.JText::_('STATUS').' <a href="javascript:clearchosen(\'#select_multiple_status\')"><span class="glyphicon glyphicon-remove" title="'.JText::_('CLEAR').'"></span></a></label></div>
					<div class="em_filtersElement">';
            }
            $status .= '<select '.(!$hidden ? 'class="chzn-select em-filt-select"' : '').' id="select_multiple_status" name="status" multiple="" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" >' : '>');
            $status .= '<option value="%" ';
            if( (@$current_status[0] == "%" || !isset($current_status[0])) && (count(@$current_status)<2 )) $status .= ' selected="true"';
            $status .= '>'.JText::_('ALL').'</option>';

            foreach($statusList as $p) {
                $status .= '<option value="'.$p->step.'"';
                if(!empty($current_status) && in_array($p->step, $current_status)) $status .= ' selected="true"';
                $status .= '>'.$p->value.'</option>';
            }
            $status .= '</select>';
            if (!$hidden) {
                $status .= '</div></div>';
                $status .= '<script>$(document).ready(function() {$("#select_multiple_status").chosen({width: "75%"});})</script>';
            }
            $filters .= $status;
        }

        if($params['published'] !== NULL){
            $hidden = $types['published'] != 'hidden' ? false : true;
            $published='';
            if (!$hidden) {
                $published.= '<div class="em_filters" id="published">
				<div class="em_label"><label class="control-label">'.JText::_('PUBLISH').'</label></div>';
                $published .= '<div class="em_filtersElement">';
                $published .= '<select class="chzn-select em-filt-select" id="select_published" name="published" '.($types['published'] == 'hidden' ? 'style="visibility:hidden" ' : '').'>
                        <option value="1"';
                if ($current_published=='1')
                    $published .= "selected='true'";
                $published .='>'.JText::_("PUBLISHED").'</option>
                        <option value="0"';
                if ($current_published=='0')
                    $published .= "selected='true'";
                $published .='>'. JText::_("ARCHIVED").'</option>
                        <option value="-1"';
                if ($current_published=='-1')
                    $published .= "selected='true'";
                $published .='>'.JText::_("TRASHED").'</option>
                </select>';
                $published .='</div></div>';
            }
            $filters .= $published;

        }

        if(@$params['tag'] !== NULL){
            $hidden = $types['tag'] != 'hidden' ? false : true;
            $tagList = $files->getAllTags();
            $tag = '';
            if (!$hidden) {
                $tag .= '<div id="tag">
					<div class="em_label"><label class="control-label">'.JText::_('TAG').' <a href="javascript:clearchosen(\'#select_multiple_tags\')"><span class="glyphicon glyphicon-remove" title="'.JText::_('CLEAR').'"></span></a></label></div>
					<div class="em_filtersElement">';
            }
            $tag .= '<select '.(!$hidden ? 'class="chzn-select em-filt-select"' : '').' id="select_multiple_tags" name="tag" multiple="" '.($hidden ? 'style="visibility:hidden;height:0px;width:0px;" >' : '>');
            $tag .= '<option value="%" ';
            if( (@$current_tag[0] == "%" || !isset($current_tag[0])) && (count(@$current_tag)<2 )) $tag .= ' selected="true"';
            $tag .= '>'.JText::_('ALL').'</option>';

            foreach($tagList as $p) {
                $tag .= '<option value="'.$p['id'].'"';
                if(!empty($current_tag) && in_array($p['id'], $current_tag)) $tag .= ' selected="true"';
                $tag .= '>'.$p['label'].'</option>';
            }
            $tag .= '</select>';
            if (!$hidden) {
                $tag .= '</div></div>';
                $tag .= '<script>$(document).ready(function() {$("#select_multiple_tags").chosen({width: "75%"});})</script>';
            }
            $filters .= $tag;
        }

        //Advance filter builtin
        if(@$params['adv_filter'] !== NULL){
            $filters .= '</fieldset><fieldset class="em_filters_adv_filter">';

            $hidden = $types['adv_filter'] != 'hidden' ? false : true;
            $elements = @EmundusHelperFiles::getElements();
            $adv_filter = '<div class="em_filters" id="em_adv_filters">
									<label class="control-label editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('FILTER_HELP').'">'.JText::_('ELEMENT_FILTER').'</label>';
            $adv_filter .= '<div><button class="btn btn-default btn-sm" type="button" id="add-filter"><span class="glyphicon glyphicon-th-list"></span> '.JText::_('ADD_FILTER_COLUMN').'</button></div><input type="hidden" value="'.count($search).'" id="nb-adv-filter" />';
            $adv_filter .= '<div id="advanced-filters" class="form-group">';

            if (!empty($search))
            {
                $i=1;
                $selected_adv = "";
                foreach($search as $key => $val)
                {
                    $adv_filter .= '<fieldset id="em-adv-father-'.$i.'">';
                    $adv_filter .= '<select class="chzn-select em-filt-select" id="elements" name="elements">
					<option value="">'.JText::_('PLEASE_SELECT').'</option>';
                    $menu ="";
                    $groupe ="";

                    foreach($elements as $element)
                    {
                        $menu_tmp = $element->title;

                        if ($menu != $menu_tmp)
                        {
                            $adv_filter .= '<optgroup label="________________________________"><option disabled class="emundus_search_elm" value="-">'.strtoupper($menu_tmp).'</option></optgroup>';
                            $menu = $menu_tmp;
                        }

                        if (isset($groupe_tmp) && ($groupe != $groupe_tmp))
                        {
                            $adv_filter .= '</optgroup>';
                        }

                        $groupe_tmp = $element->group_label;

                        if ($groupe != $groupe_tmp)
                        {
                            $adv_filter .= '<optgroup label=">> '.$groupe_tmp.'">';
                            $groupe = $groupe_tmp;
                        }



                        $adv_filter .= '<option class="emundus_search_elm" value="'.$element->id.'"';
                        $table_name = (isset($element->table_join)?$element->table_join:$element->table_name);
                        if($table_name.'.'.$element->element_name == $key)
                        {
                            $selected_adv = $element;
                            $adv_filter .= ' selected=true ';
                        }
                        $adv_filter .= '>'.$element->element_label.'</option>';
                    }
                    $adv_filter .= '</select> <button class="btn btn-danger btn-xs" id="suppr-filt"><span class="glyphicon glyphicon-trash" ></span></button>';

                    if($selected_adv != "")
                        $adv_filter .= @EmundusHelperFiles::setSearchBox($selected_adv, $val, $key, $i);

                    $i++;
                    $adv_filter .= '</fieldset>';
                }
            }
            $adv_filter .= '</div></div>';


            $filters .= $adv_filter;
        }

        //Other filters builtin
        if(@$params['other'] !== NULL && !empty($tables) && $tables[0] != ""){
            $filters .= '</fieldset><fieldset class="em_filters_other">';
            $hidden = $types['other'] != 'hidden' ? false : true;
            $other_elements = @EmundusHelperFiles::getElementsOther($tables);
            $other_filter = '<div class="em_filters" id="em_other_filters"><a href="javascript:addElementOther();"><span class="editlinktip hasTip" title="'.JText::_('NOTE').'::'.JText::_('FILTER_HELP').'">'.JText::_('OTHER_FILTERS').'</span>';
            $other_filter .= '<input type="hidden" value="0" id="theValue_other" />';
            $other_filter .= '<img src="'.JURI::Base().'media/com_emundus/images/icones/viewmag+_16x16.png" alt="'.JText::_('ADD_SEARCH_ELEMENT').'" id="add_filt"/></a>';
            $other_filter .= '<div id="otherDiv">';

            if (count($search_other)>0 && isset($search_other) && is_array($search_other)) {
                $i=0;
                $selected_other = "";
                foreach($search_other as $sf) {
                    $other_filter .= '<div id="filter_other'.$i.'">';
                    $other_filter .= '<select class="chzn-select em-filt-select" id="elements-others" name="elements_other" id="elements_other" >
                            <option value="">'.JText::_('PLEASE_SELECT').'</option>';
                    $groupe = "";
                    $length = 50;
                    if(!empty($other_elements))
                        foreach($other_elements as $element_other) {
                            $groupe_tmp = $element_other->group_label;
                            $dot_grp = strlen($groupe_tmp)>=$length?'...':'';
                            $dot_elm = strlen($element_other->element_label)>=$length?'...':'';
                            if ($groupe != $groupe_tmp) {
                                $other_filter .= '<option class="emundus_search_grp" disabled="disabled" value="">'.substr(strtoupper($groupe_tmp), 0, $length).$dot_grp.'</option>';
                                $groupe = $groupe_tmp;
                            }
                            $other_filter .= '<option class="emundus_search_elm_other" value="'.$element_other->table_name.'.'.$element_other->element_name.'"'; // = result_for; engaged; scholarship...
                            if($element_other->table_name.'.'.$element_other->element_name == $sf){
                                $other_filter .= ' selected';
                                $selected_other = $element_other;
                            }
                            $other_filter .= '>'.substr($element_other->element_label, 0, $length).$dot_elm.'</option>';
                        }
                    $other_filter .= '</select>';
                    if(!isset($search_values_other[$i])) $search_values_other[$i] = "";
                    if ($selected_other != "")
                        $other_filter .= @EmundusHelperFiles::setSearchBox($selected_other, $search_values_other[$i], "elements_values_other", $i);
                    $other_filter .= '<a href="javascript:clearAdvanceFilter(\'filter_other'.$i.'\'); javascript:removeElement(\'filter_other'.$i.'\', 2);"><img src="'.JURI::Base().'media/com_emundus/images/icones/viewmag-_16x16.png" alt="'.JText::_('REMOVE_SEARCH_ELEMENT').'" id="add_filt"/></a>';
                    $i++;
                    $other_filter .= '</div>';
                }
            }
            $other_filter .= '</div></div>';
            $filters .= $other_filter;
        }

        if(@$params['newsletter'] !== NULL){
            $hidden = $types['newsletter'] != 'hidden' ? false : true;
            $filters.= '<div class="em_filters" id="newsletter">
                        <div class="em_label"><label class="control-label">'.JText::_('NEWSLETTER').'</label></div>';
            $filters .= '<div class="em_filtersElement">
                        <select class="chzn-select em-filt-select" id="select_newsletter" name="newsletter" '.($types['newsletter'] == 'hidden' ? 'style="visibility:hidden" ' : '').'>
                            <option value="0"';
            if(@$newsletter == 0) $filters .= ' selected';
            $filters.='>'.JText::_("ALL").'</option>
                            <option value="1"';
            if(@$newsletter == 1) $filters .= ' selected';
            $filters.='>'.JText::_("JYES").'</option>
                        </select>
                    </div>';
            //$filters.='<div class="em_filtersElement"><input id="check_newsletter" name="newsletter" onMouseUp="if(this.checked==true){this.value=0;}else{this.value=1;}" type="checkbox" value="0" '.($newsletter==1?'checked=checked':'').' /></div>';
            $filters.='</div>';
        }

        if(@$params['spam_suspect'] !== NULL){
            $hidden = $types['spam_suspect'] != 'hidden' ? false : true;
            $filters.= '<div class="em_filters" id="spam_suspect"><div class="em_label"><label class="control-label">'.JText::_('SPAM_SUSPECT').'</label></div>';
            $filters .= '<div class="em_filtersElement">
                        <select class="chzn-select em-filt-select" id="select_spam-suspect" name="spam_suspect" '.($types['spam_suspect'] == 'hidden' ? 'style="visibility:hidden" ' : '').'>
                            <option value="0"';
            if(@$spam_suspect == 0) $filters .= ' selected';
            $filters.='>'.JText::_("ALL").'</option>
                            <option value="1"';
            if(@$spam_suspect == 1) $filters .= ' selected';
            $filters.='>'.JText::_("JYES").'</option>
                        </select>
                    </div>';
            // $filters.= '<div class="em_filtersElement"><input id="check_spam-suspect" name="spam_suspect" onMouseUp="if(this.checked==true){this.value=1;}else{this.value=0;}" type="checkbox" value="0" '.($spam_suspect==1?'checked=checked':'').' /></div>';
            $filters.= '</div>';
        }


        // Buttons
        $filters .='<div class="buttons">
                                <input type="button" class="btn btn-info btn-sm" name="search" id="search"  value="'.JText::_('SEARCH_BTN').'"/>';
        $filters .=' <input type="button" class="btn btn-sm btn-danger" name="clear-search" id="clear-search" value="'.JText::_('CLEAR_BTN').'"/> ';
        $filters .=' <button class="btn btn-warning" id="save-filter"><i class="icon-star"></i></button></div>';
        $filters .= '</fieldset>';
        $filters .= '<script>
                            $( "#hider" ).click(function() {
                                $( ".em_filters_filedset" ).hide( "slow" );
                                document.cookie="em_filters=hidden";
                            });
                            $( "#shower" ).click(function() {
                                $( ".em_filters_filedset" ).show( "slow" );
                                document.cookie="em_filters=displayed";
                            });
                            var cookies = document.cookie;
                            if(cookies.indexOf("em_filters=hidden")>=0)
                                    $( ".em_filters_filedset" ).hide("slow");
                            </script>';

        // User filter
        $research_filters = @EmundusHelperFiles::getEmundusFilters();
        $filters .='<fieldset>
                                <label for="select_filter" class="control-label">'.JText::_('SELECT_FILTER').'</label>
                                <select class="chzn-select" id="select_filter" name="select_filter" >
                                    <option value="0">'.JText::_('PLEASE_SELECT_FILTER').'</option>';
        if(!empty($research_filters))
        {
            foreach($research_filters as $filter)
            {
                if($select_id==$filter->id)
                {
                    $filters .= '<option value="'.$filter->id.'" selected="true" >'.$filter->name.'</option>';
                }
                else
                {
                    $filters .= '<option value="'.$filter->id.'">'.$filter->name.'</option>';
                }
            }
        }
        $filters .=	'</select>';
        $filters .=' <button class="btn btn-sm" id="del-filter" title="'.JText::_('DELETE').'"><i class="icon-trash"></i></button>
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
                <script type="text/javascript" >'.EmundusHelperJavascript::getPreferenceFilters().''.EmundusHelperJavascript::clearAdvanceFilter().'</script>';
        $filters .= '</fieldset>';

        return $filters;
    }

    public  function getEmundusFilters($id = null)
    {
        $itemid = JFactory::getApplication()->input->get('Itemid');
        $user = JFactory::getUser();
        $db = JFactory::getDBO();
        if (is_null($id) && !empty($itemid))
        {
            $query = 'SELECT * FROM #__emundus_filters WHERE user='.$user->id.' AND item_id='.$itemid;
            $db->setQuery( $query );
            return $db->loadObjectlist();
        }
        elseif(!empty($id))
        {
            $query = 'SELECT * FROM #__emundus_filters WHERE id='.$id;
            $db->setQuery( $query );
            return $db->loadObject();
        }
        else return array();
    }

    public  function createTagsList($tags)
    {
        $tagsList = array();
        foreach($tags as $tag){
            $tagsList[$tag['fnum']] .= '<a class="item"><div class="ui mini '.$tag['class'].' horizontal label">'.$tag['label'].'</div></a> ';
        }
        return $tagsList;
    }

    public  function createEvaluatorList($join, $model)
    {
        $evaluators = array();

        $groupEval = $model->getEvaluatorsFromGroup();

        $evaluatorsDB = $model->getEvaluators();
        $groups = array();
        $title = "";
        foreach($groupEval as $k => $group)
        {

            if(!array_key_exists($group['fnum'], $evaluators))
            {
                $title = $group['name'];
                $evaluators[$group['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="glyphicon glyphicon-eye-open"></span>  <span class="em-evaluator editlinktip hasTip"  title="'.$title.'">'.$group['title'].'</span><button class="btn btn-danger btn-xs group" id="'.$group['fnum'].'-'.$group['id'].'">X</button></li></ul>';
            }
            else
            {

                if((strcmp($group['fnum'], $groupEval[$k + 1]['fnum'])  == 0) && (strcmp($groupEval[$k + 1]['title'], $group['title']) == 0 ))
                {
                    $title .= ' '.$group['name'];
                }
                else
                {
                    $title .= ' '.$group['name'];
                    $evaluators[$group['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="glyphicon glyphicon-eye-open"></span>  <span class="em-evaluator editlinktip hasTip"  title="'.$title.'">'.$group['title'].'</span><button class="btn btn-danger btn-xs group" id="'.$group['fnum'].'-'.$group['id'].'">X</button></li></ul>';
                    $title = '';
                }
            }
        }
        foreach($evaluatorsDB as $ev)
        {
            if(!array_key_exists($ev['fnum'], $evaluators))
            {
                $evaluators[$ev['fnum']] = '<ul class="em-list-evaluator"><li class="em-list-evaluator-item"><span class="glyphicon glyphicon-eye-open"></span>  <span class="em-evaluator">'.$ev['name'].'</span><button class="btn btn-danger btn-xs">X</button></li></ul>';

            }
            else
            {
                $evaluators[$ev['fnum']] = substr($evaluators[$ev['fnum']], 0, count($evaluators[$ev['fnum']]) - 6) . '<li class="em-list-evaluator-item"><span class="glyphicon glyphicon-eye-open"></span>  <span class="em-evaluator">'.$ev['name'].'</span><button class="btn btn-danger btn-xs" id="'.$ev['fnum'].'-'.$ev['id'].'">X</button></li></ul>';
            }
        }
        return $evaluators;
    }

    // Get object of a Joomla Menu
    public function getMenuList($params, $fnum = null)
    {
        require_once (JPATH_COMPONENT.DS.'models'.DS.'users.php');
        $userModel = new EmundusModelUsers();


        $menu = @JSite::getMenu();
        // If no active menu, use default
        $active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();

        $user = JFactory::getUser();
        if($fnum === null)
        {
            $actions = $userModel->getUserACL($user->id);
        }
        else
        {
            $actions = $userModel->getUserACL($user->id, $fnum);
        }
        $levels = $user->getAuthorisedViewLevels();
        asort($levels);
        $key = 'menu_items'.$params.implode(',', $levels).'.'.$active->id;
        $cache = JFactory::getCache('mod_menu', '');

        if (!($items = $cache->get($key)))
        {
            // Initialise variables.
            $path		= $active->tree;
            $start		= 0;
            $end		= 3;
            $showAll	= 1;
            $items 		= $menu->getItems('menutype', $params->get('em_actions'));

            $lastitem	= 0;
            if ($items) {

                foreach($items as $i => $item)
                {
                    $note = explode('|', $item->note);
                    if (count($note)>1)
                    {
                        if(EmundusHelperAccess::asAccessAction($note[0], $note[1], $user->id, $fnum))
                        {
                            $actions[$note[0]]['multi'] = @$note[2];
                            $actions[$note[0]]['grud'] = @$note[1];
                            $item->action = $actions[$note[0]];
                        }
                        else
                        {
                            unset($items[$i]);
                            continue;
                        }
                    }

                    if (($start && $start > $item->level)
                        || ($end && $item->level > $end)
                        || (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path))
                        || ($start > 1 && !in_array($item->tree[$start-2], $path))
                    ) {
                        unset($items[$i]);
                        continue;
                    }

                    $item->deeper = false;
                    $item->shallower = false;
                    $item->level_diff = 0;

                    if (isset($items[$lastitem])) {
                        $items[$lastitem]->deeper		= ($item->level > $items[$lastitem]->level);
                        $items[$lastitem]->shallower	= ($item->level < $items[$lastitem]->level);
                        $items[$lastitem]->level_diff	= ($items[$lastitem]->level - $item->level);
                    }

                    $item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);
                    $lastitem			= $i;
                    $item->active		= false;
                    $item->flink = $item->link;

                    // Reverted back for CMS version 2.5.6
                    switch ($item->type)
                    {
                        case 'separator':
                            // No further action needed.
                            continue;

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
                            }
                            else {
                                $item->flink .= '&Itemid='.$item->id;
                            }
                            break;
                    }

                    if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
                        $item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
                    }
                    else {
                        $item->flink = JRoute::_($item->flink);
                    }

                    $item->title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->menu_image   = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
                }

                if (isset($items[$lastitem])) {
                    $items[$lastitem]->deeper		= (($start?$start:1) > $items[$lastitem]->level);
                    $items[$lastitem]->shallower	= (($start?$start:1) < $items[$lastitem]->level);
                    $items[$lastitem]->level_diff	= ($items[$lastitem]->level - ($start?$start:1));
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
    public function getUserGroups($uid)
    {
        $db			= JFactory::getDbo();

        $query = 'select distinct(group_id)
					from #__emundus_groups 
					where user_id='.$uid;

        try
        {
            $db->setQuery($query);
            return $db->loadColumn();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    // getEvaluation
    function getEvaluation($format='html', $fnums){
        require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

        $evaluation 	= new EmundusModelEvaluation();
        $files 			= new EmundusModelFiles;

        //$fnums = '2014103012343200000630002385';
        if (!is_array($fnums)) {
            $fnumInfo = $files->getFnumInfos($fnums);
            $fnums = array($fnums);
        } else {
            $fnumInfo = $files->getFnumInfos($fnums[1]);
        }
        $element_id = $evaluation->getAllEvaluationElements(1, $fnumInfo['training']);
        $elements = @EmundusHelperFiles::getElementsName(implode(',',$element_id));
        $evaluations = $files->getFnumArray($fnums, $elements);

        $data = array();
        //$i = 0;

        foreach($evaluations as $eval)
        {
            if ($eval['jos_emundus_evaluations___user'] > 0) {
                $str = '<br><hr>';
                $str .= '<em>'.JText::_('EVALUATED_ON').' : '.JHtml::_('date', $eval['jos_emundus_evaluations___time_date'], JText::_('DATE_FORMAT_LC')).' - '.$fnumInfo['name'].'</em>';
                $str .= '<h1>'.JFactory::getUser($eval['jos_emundus_evaluations___user'])->name.'</h1>';
                $str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

                foreach($elements as $element){
                    $k = $element->tab_name.'___'.$element->element_name;

                    if( $element->element_name != 'id' &&
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
                        if(strpos($element->element_name, 'comment') !== false)
                            $str .= '<td colspan="2"><b>' . $element->element_label . '</b> <br>' . $eval[$k] . '</td>';
                        else
                            $str .= '<td width="70%"><b>' . $element->element_label . '</b> </td><td width="30%">' . $eval[$k] . '</td>';
                        $str .= '</tr>';
                    }
                }

                $str .= '</table>';
                $str .= '<p></p><hr>';

                if ($format != 'html') {
                    //$str = str_replace('<hr>', chr(10).'------'.chr(10), $str);
                    $str = str_replace('<br>', chr(10), $str);
                    $str = str_replace('<br />', chr(10), $str);
                    $str = str_replace('<h1>', '* ', $str);
                    $str = str_replace('</h1>', ' : ', $str);
                    $str = str_replace('<b>', chr(10), $str);
                    $str = str_replace('</b>', ' : ', $str);
                    $str = str_replace('&nbsp;', ' ', $str);
                    $str = strip_tags($str, '<h1>');
                }

                $data[$eval['fnum']][$eval['jos_emundus_evaluations___user']] = $str;

                //$i++;
            }
        }

        return $data;
    }

    // getDecision
    function getDecision($format='html', $fnums){
        require_once (JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

        $evaluation    = new EmundusModelEvaluation();
        $files             = new EmundusModelFiles;

        //$fnums = '2014103012343200000630002385';
        if (!is_array($fnums)) {
            $fnumInfo = $files->getFnumInfos($fnums);
            $fnums = array($fnums);
        } else {
            $fnumInfo = $files->getFnumInfos($fnums[1]);
        }

        $element_id = $evaluation->getAllDecisionElements(1, $fnumInfo['training']);
        $elements = @EmundusHelperFiles::getElementsName(implode(',',$element_id));
        $evaluations = $files->getFnumArray($fnums, $elements);

        $data = array();

        foreach($evaluations as $eval)
        {
            if ($eval['jos_emundus_final_grade___user'] > 0) {
                $str = '<br><hr>';
                $str .= '<em>'.JHtml::_('date', $eval['jos_emundus_final_grade___time_date'], JText::_('DATE_FORMAT_LC')).'</em>';
                $str .= '<h1>'.JFactory::getUser($eval['jos_emundus_final_grade___user'])->name.'</h1>';
                $str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

                foreach($elements as $element){
                    $k = $element->tab_name.'___'.$element->element_name;

                    if( $element->element_name != 'id' &&
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
                        if(strpos($element->element_plugin, 'textarea') !== false)
                            $str .= '<td colspan="2"><b>' . $element->element_label . '</b> <br>' . $eval[$k] . '</td>';
                        else
                            $str .= '<td width="70%"><b>' . $element->element_label . '</b> </td><td width="30%">' . $eval[$k] . '</td>';
                        $str .= '</tr>';
                    }
                }

                $str .= '</table>';
                $str .= '<p></p><hr>';

                if ($format != 'html') {
                    //$str = str_replace('<hr>', chr(10).'------'.chr(10), $str);
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

                //$i++;
            }
        }

        return $data;
    }

    /*// getComment
    function getComment($format='html', $fnums){
        require_once (JPATH_COMPONENT.DS.'models'.DS.'application.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'files.php');

        $app 	= new EmundusModelApplication();
        $files = new EmundusModelFiles;

        //$fnums = '2014103012343200000630002385';
        if (!is_array($fnums)) {
            $fnumInfo = $files->getFnumInfos($fnums);
            $fnums = array($fnums);
        } else {
            $fnumInfo = $files->getFnumInfos($fnums[1]);
        }

        $comments = $app->getFileComments($fnumInfo['training']);

        $data = array();
        //$i = 0;
        $str = '<br><hr>';

        foreach($comments as $comment)
        {
            $str .= '<li class="list-group-item" id="'.$comment->id.'">';
            $str .= '<div class="row">';
            $str .= '<div class="col-xs-10 col-md-11">';
            $str .= '<div>';
            $str .= ' <a href="#">'.$comment->reason.'</a>';
            $str .= '<div class="mic-info">';
            $str .= ' <a href="#">'. $comment->name.'</a> - ';
            $str .=  JHtml::_('date', $comment->date, JText::_('DATE_FORMAT_LC2'));
            $str .= ' </div>';
            $str .= '</div>';
            $str .= '<div class="comment-text">';
            $str .=  $comment->comment;
            $str .= '</div>';
            $str .= ' </div>';
            $str .= '</div>';
            $str .= '</li>';

            $str .= '<p></p><hr>';

            if ($format != 'html') {
                //$str = str_replace('<hr>', chr(10).'------'.chr(10), $str);
                $str = str_replace('<br>', chr(10), $str);
                $str = str_replace('<br />', chr(10), $str);
                $str = str_replace('<h1>', '* ', $str);
                $str = str_replace('</h1>', ' : ', $str);
                $str = str_replace('<b>', chr(10), $str);
                $str = str_replace('</b>', ' : ', $str);
                $str = str_replace('&nbsp;', ' ', $str);
                $str = strip_tags($str, '<h1>');
            }
            $data[$comment['fnum']] = $str;
        }

        return $data;
    }*/

    /**
     * Method to create a new FNUM
     *
     * @param	integer		The id of the campaign.
     * @param	integer		The id of the user.
     * @return	string		FNUM for application.
     * @since	1.6
     */
    public function createFnum($campaign_id, $user_id){
        $fnum	 = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user_id, 7, '0', STR_PAD_LEFT);
        return $fnum;
    }
}
