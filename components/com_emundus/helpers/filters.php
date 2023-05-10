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
class EmundusHelperFilters {

	/*
	* @param 			query results
	* @param 	array 	values to extract and insert
	*/
	public static function insertValuesInQueryResult($results, $options) {
		foreach ($results as $key => $result) {
			if (array_key_exists('params', $result)) {
				if (is_array($result)) {

					$results[$key]['table_label'] = JText::_($results[$key]['table_label']);
					$results[$key]['group_label'] = JText::_($results[$key]['group_label']);
					$results[$key]['element_label'] = JText::_($results[$key]['element_label']);

					$params = json_decode($result['params']);
					foreach ($options as $option) {
						if (property_exists($params, 'sub_options') && array_key_exists($option, $params->sub_options)) {
							$results[$key][$option] = implode('|', $params->sub_options->$option);
						} else {
							$results[$key][$option] = '';
						}
					}
				} else {

					$results[$key]->table_label = JText::_($results[$key]->table_label);
					$results[$key]->group_label = JText::_($results[$key]->group_label);
					$results[$key]->element_label = JText::_($results[$key]->element_label);

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
		$config = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
		$now = JFactory::getDate()->setTimezone($timezone);

		$db = JFactory::getDBO();
		$query = 'SELECT DISTINCT year as schoolyear
		FROM #__emundus_setup_campaigns
		WHERE published = 1 AND end_date > DATE_ADD("'.$now.'", INTERVAL -'.$nb_months_registration_period_access.' MONTH) ORDER BY schoolyear DESC';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	public static function getCurrentCampaignsID() {
		$eMConfig = JComponentHelper::getParams('com_emundus');
		$nb_months_registration_period_access = $eMConfig->get('nb_months_registration_period_access', '11');
		$config     = JFactory::getConfig();

        $timezone = new DateTimeZone( $config->get('offset') );
		$now = JFactory::getDate()->setTimezone($timezone);

		$db = JFactory::getDBO();
		$query = 'SELECT id
		FROM #__emundus_setup_campaigns
		WHERE published = 1 AND end_date > DATE_ADD("'.$now.'", INTERVAL -'.$nb_months_registration_period_access.' MONTH)
		ORDER BY year DESC';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	public function getCampaigns() {
		$db = JFactory::getDBO();
		$query = 'SELECT id, label, year  FROM #__emundus_setup_campaigns WHERE published=1 ORDER BY year DESC';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	public function getProgrammes() {
		$db = JFactory::getDBO();
		$query = 'SELECT *  FROM #__emundus_setup_programmes WHERE published=1 ORDER BY label,ordering ASC';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	public function getCampaign()
	{
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

	public function getApplicants() {
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label
		FROM #__emundus_setup_profiles esp
		WHERE esp.published =1';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}

	function getProfiles() {
		$db = JFactory::getDBO();
		$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft
		FROM #__emundus_setup_profiles esp
		INNER JOIN #__usergroups caag on esp.acl_aro_groups=caag.id
		ORDER BY caag.lft, esp.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}

	function getEvaluators() {
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

	function getGroupsEval() {
		$db = JFactory::getDBO();
		$query = 'SELECT ege.group_id
		FROM #__emundus_groups_eval ege
		ORDER BY ege.group_id';
	//echo str_replace("#_", "jos", $query);
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	public static function getGroups() {
		$db = JFactory::getDBO();
		$query = 'SELECT esg.id, esg.label
		FROM #__emundus_setup_groups esg
		WHERE esg.published=1
		ORDER BY esg.label';
		$db->setQuery( $query );
		return $db->loadObjectList('id');
	}

	function getSchoolyears() {
		$db = JFactory::getDBO();
		$query = 'SELECT DISTINCT(year) as schoolyear
			FROM #__emundus_setup_campaigns
			ORDER BY schoolyear DESC';
		//echo str_replace("#_", "jos", $query);
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	public static function getFinal_grade() {
		$db = JFactory::getDBO();
		$query = 'SELECT name, params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
		$db->setQuery( $query );
		return @EmundusHelperFilters::insertValuesInQueryResult($db->loadAssocList('name'), array("sub_values", "sub_labels"));
	}

	function getMissing_doc() {
		$db = JFactory::getDBO();
		$query = 'SELECT DISTINCT(esap.attachment_id), esa.value
				FROM #__emundus_setup_attachment_profiles esap
				LEFT JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id';
			$db->setQuery( $query );
			return $db->loadObjectList();
	}

	public static function getEvaluation_doc($status) {
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

	function setEvaluationList ($status) {
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

	public static function getElements() {
		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
		require_once(JPATH_COMPONENT.DS.'models'.DS.'users.php');
		require_once(JPATH_COMPONENT.DS.'models'.DS.'profile.php');

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$export_pdf = $eMConfig->get('export_pdf');

		$h_menu 	= new EmundusHelperMenu;
		$user 		= new EmundusModelUsers;
		$profile 	= new EmundusModelProfile;


		$session 	= JFactory::getSession();
		$params 	= $session->get('filt_params');
		$programme 	= $params['programme'];

		$profiles 	= $user->getApplicantProfiles();
		$plist 		= $profile->getProfileIDByCourse($programme);

		foreach ($profiles as $profile) {
			if (count($plist)==0 || (count($plist)>0 && in_array($profile->id, $plist))) {
				$menu_list = $h_menu->buildMenuQuery($profile->id);
				foreach ($menu_list as $m) {
					$fl[] = $m->table_id;
				}
			}
		}

		$db = JFactory::getDBO();
		$query = 'SELECT distinct(concat_ws("_",tab.db_table_name,element.name)), element.name AS element_name, element.label AS element_label, element.plugin AS element_plugin, element.id, groupe.id AS group_id, groupe.label AS group_label, element.params AS element_attribs,
				INSTR(groupe.params,\'"repeat_group_button":"1"\') AS group_repeated, tab.id AS table_id, tab.db_table_name AS table_name, tab.label AS table_label, tab.created_by_alias, menu.title
				FROM #__fabrik_elements element
				INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
				INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				INNER JOIN #__fabrik_forms AS form ON tab.form_id = form.id
				INNER JOIN #__menu AS menu ON form.id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 4), "&", 1)
				WHERE tab.published = 1
					AND (tab.id IN ( '.implode(',', $fl).' ) OR tab.id IN ( '.$export_pdf.' ) )
					AND element.published=1
					AND element.hidden=0
					AND element.label!=" "
					AND element.label!=""
				ORDER BY menu.lft, formgroup.ordering, groupe.id, element.ordering';

		try {

			$db->setQuery( $query );
			return $db->loadObjectList('id');

		} catch (Exception $e) {
			throw $e;
		}
	}


	/**
	* Get list of elements declared in a list of Fabrik groups AND groupe.id IN (551,580,581)
	* @param 	string 	List of Fabrik groups comma separated
	* @param 	int 	Does the element are shown in Fabrik list ; if 1, show only item displayed in Fabrik List ?
	* @param 	int 	Does the element are hidden in Fabrik list ; if 0, show only displayed Fabrik Items ?
	* @return   array 	list of Fabrik element ID used in evaluation form
	**/
	static function getElementsByGroups($groups, $show_in_list_summary=1, $hidden=0) {
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
					AND element.label != " "
					AND element.label != ""
					AND element.plugin != "display"
				ORDER BY formgroup.ordering, element.ordering';
		try {
			//die(str_replace("#_", "jos", $query));
			$db->setQuery($query);
			return $db->loadObjectList();

		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	* Get list of ALL elements declared in a list of Fabrik groups
	* @param 	string 	List of Fabrik groups comma separated
	* @param 	int 	Does the element are shown in Fabrik list ?
	* @param 	int 	Does the element are an hidden element ?
	* @return   array 	list of Fabrik element ID used in evaluation form
	**/
	function getAllElementsByGroups($groups, $show_in_list_summary=null, $hidden=null) {
		$elements = [];

		if (!empty($groups)) {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('jfe.name, jfe.label, jfe.plugin, jfe.id as element_id, jfg.id, jfg.label AS group_label, jfe.params, INSTR(jfg.params,\'"repeat_group_button":"1"\') AS group_repeated, jfl.id AS table_id, jfl.db_table_name AS table_name, jfl.label AS table_label, jfl.created_by_alias')
				->from($db->qn('#__fabrik_elements', 'jfe'))
				->join('inner', $db->qn('#__fabrik_groups', 'jfg') . ' ON jfg.id = jfe.group_id')
				->join('inner', $db->qn('#__fabrik_formgroup', 'jffg') . ' ON jfg.id = jffg.group_id')
				->join('inner', $db->qn('#__fabrik_lists', 'jfl') . ' ON jfl.form_id = jffg.form_id')
				->join('inner', $db->qn('#__fabrik_forms', 'jff') . ' ON jff.id = jfl.form_id')
				->where('jfe.group_id IN (' . $groups .  ')')
				->andWhere('jfl.published = 1')
				->andWhere('jfe.published = 1');

			if ($show_in_list_summary !== null) {
				$query->andWhere('jfe.show_in_list_summary = ' . $show_in_list_summary);
			}

			if ($hidden !== null) {
				$query->andWhere('jfe.hidden = ' . $hidden);
			}
			$query->order('find_in_set(jfg.id, "'. $groups . '"), jfe.ordering');
			try {
				$db->setQuery($query);
				$elements = $db->loadObjectList();
			} catch (Exception $e) {
				JLog::add('Failed to get fabrik elements by group id ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $elements;
	}

	public static function getElementsOther($tables) {
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
				}
				else $query .= ', '.$table;
			}
			$query .= ') AND ';
		}
		else $query .= ' WHERE ';
		$query .= 'element.name NOT IN ("id", "time_date", "user", "student_id", "type_grade", "final_grade")
				ORDER BY group_id';
		$db->setQuery($query);
//		die(str_replace("#_", "jos", $query));
		return $db->loadObjectList();
	}

	function getElementsValuesOther($element_id) {
		//jimport( 'joomla.registry.format.json' );
		$db = JFactory::getDBO();
		$query = 'SELECT params FROM #__fabrik_elements element WHERE id='.$element_id;
		$db->setQuery($query);
		$res = $db->loadResult();
		$sub = json_decode($res);//JRegistryFormatJson::stringToObject($res);

		return $sub->sub_options;
	}

	function getElementsName($elements_id) {
		$db = JFactory::getDBO();
		$query = 'SELECT element.name AS element_name, element.id, tab.db_table_name AS tab_name, tab.created_by_alias AS created_by_alias
				FROM #__fabrik_elements element
				INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
				INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
				INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
				WHERE element.id IN ('.$elements_id.')
            	ORDER BY find_in_set(element.ordering, "'. $elements_id . '")';

		$db->setQuery($query);
		return  $db->loadObjectList();
	}

	function buildOptions($element_name, $params) {
		if (!empty($params->join_key_column)) {
			$db = JFactory::getDBO();
			if ($element_name == 'result_for')
				$query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$params->join_val_column.' AS elt_val FROM '.$params->join_db_name.' WHERE published=1';
			elseif ($element_name == 'campaign_id')
				$query = 'SELECT '.$params->join_key_column.' AS elt_key, '.$params->join_val_column.' AS elt_val FROM '.$params->join_db_name;
			elseif ($element_name=='training_id')
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
	function setWhere($search, $search_values, &$query) {
		if (isset($search) && !empty($search)) {
			$i = 0;
			foreach ($search as $s) {
				if ((!empty($search_values[$i]) || isset($search_values[$i])) && $search_values[$i]!="") {
					$tab = explode('.', $s);
					if (count($tab)>1) {
						if ($tab[0]=='jos_emundus_training'){
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
	function setSearchBox($selected, $search_value, $elements_values) {
		jimport( 'joomla.html.parameter' );
//echo "<hr>".$selected->element_plugin;
//echo " : ".$search_value;
		$current_filter = "";
		if (!empty($selected)) {
			if ($selected->element_plugin == "databasejoin"){
				$query_paramsdefs = JPATH_SITE.DS.'plugins'.DS.'fabrik_element'.DS.'databasejoin'.DS.'field.xml';
				$query_params = new JParameter($selected->element_attribs, $query_paramsdefs);
				$query_params = json_decode($query_params);
				$option_list =  @EmundusHelperFilters::buildOptions($selected->element_name, $query_params);
				$current_filter .= '<select name="'.$elements_values.'[]" id="'.$elements_values.'" onChange="document.adminForm.task.value=\'\'; javascript:submit()">
				<option value="">'.JText::_('COM_EMUNDUS_PLEASE_SELECT').'</option>';
				if (!empty($option_list)) {
					foreach ($option_list as $value) {
						$current_filter .= '<option value="'.$value->elt_key.'"';
						if ($value->elt_key == $search_value)
							$current_filter .= ' selected';
						$current_filter .= '>'.$value->elt_val.'</option>';
					}
				}
				$current_filter .= '</select>';
			} elseif($selected->element_plugin == "checkbox" || $selected->element_plugin == "radiobutton" || $selected->element_plugin == "dropdown"){
				$query_paramsdefs = JPATH_SITE.DS.'plugins'.DS.'fabrik_element'.DS.$selected->element_plugin.DS.'field.xml';
				$query_params = new JParameter($selected->element_attribs, $query_paramsdefs);
				$query_params = json_decode($query_params);
				$option_list =  @EmundusHelperFilters::buildOptions($selected->element_name, $query_params);
				$current_filter .= '<select name="'.$elements_values.'[]" id="'.$elements_values.'" onChange="document.adminForm.task.value=\'\'; javascript:submit()">
				<option value="">'.JText::_('COM_EMUNDUS_PLEASE_SELECT').'</option>';
				if (!empty($option_list)) {
					foreach ($option_list as $value) {
						$current_filter .= '<option value="'.$value->elt_key.'"';
						if ($value->elt_key == $search_value)
							$current_filter .= ' selected';
						$current_filter .= '>'.$value->elt_val.'</option>';
					}
				}
				$current_filter .= '</select>';
			} else
				$current_filter .= '<input name="'.$elements_values.'[]" width="30" value="'.$search_value.'" />';
		}

		return $current_filter;
	}

	function getEmundusFilters() {
		$itemid = JFactory::getApplication()->input->get('Itemid', null, 'GET', 'none',0);
		if (isset($itemid) && !empty($itemid)) {
			$user = JFactory::getUser();
			$db = JFactory::getDBO();
			$query = 'SELECT * FROM #__emundus_filters WHERE user='.$user->id.' AND item_id='.$itemid;
			$db->setQuery( $query );
			return $db->loadObjectlist();
		}
		else return array();
	}
}
?>
